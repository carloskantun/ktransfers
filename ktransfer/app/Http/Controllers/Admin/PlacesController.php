<?php
declare(strict_types=1);
namespace App\Http\Controllers\Admin;

use App\Core\Csrf;
use App\Core\DB;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;

class PlacesController {
    public function index(Request $request): Response
    {
        $db = DB::connection();
        $perPage = 100;
        $page = max(1, (int) $request->query('page', 1));

        $search = trim((string) $request->query('q', ''));
        $zoneId = max(0, (int) $request->query('zone_id', 0));

        $where = [];
        $params = [];

        if ($search !== '') {
            $where[] = 'p.name LIKE :search';
            $params['search'] = '%' . $search . '%';
        }

        if ($zoneId > 0) {
            $where[] = 'p.zone_id = :zone_id';
            $params['zone_id'] = $zoneId;
        }

        $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $zonesStmt = $db->query('SELECT id, name_es FROM zones WHERE is_active = 1 ORDER BY name_es ASC');
        $zones = $zonesStmt->fetchAll();

        $countStmt = $db->prepare(
            "SELECT COUNT(*)
             FROM places p
             INNER JOIN zones z ON z.id = p.zone_id
             {$whereSql}"
        );
        foreach ($params as $key => $value) {
            $countStmt->bindValue(':' . $key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
        $countStmt->execute();
        $totalPlaces = (int) $countStmt->fetchColumn();
        $totalPages = max(1, (int) ceil($totalPlaces / $perPage));

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $offset = ($page - 1) * $perPage;

        $stmt = $db->prepare(" 
            SELECT p.id, p.name, p.type, p.city, p.is_active, z.name_es AS zone_name
            FROM places p
            INNER JOIN zones z ON z.id = p.zone_id
            {$whereSql}
            ORDER BY z.name_es ASC, p.name ASC
            LIMIT :limit OFFSET :offset
        ");
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $places = $stmt->fetchAll();

        return Response::view('admin/catalog/places/index', [
            'title' => 'Lugares',
            'places' => $places,
            'zones' => $zones,
            'filters' => [
                'q' => $search,
                'zone_id' => $zoneId,
            ],
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $totalPlaces,
                'total_pages' => $totalPages,
            ],
            'csrf_token' => Csrf::token(),
        ], 'admin');
    }

    public function export(Request $request): Response
    {
        $db = DB::connection();
        $search = trim((string) $request->query('q', ''));
        $zoneId = max(0, (int) $request->query('zone_id', 0));

        $where = [];
        $params = [];
        if ($search !== '') {
            $where[] = 'p.name LIKE :search';
            $params['search'] = '%' . $search . '%';
        }
        if ($zoneId > 0) {
            $where[] = 'p.zone_id = :zone_id';
            $params['zone_id'] = $zoneId;
        }
        $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $db->prepare("
            SELECT p.id, p.name, p.type, p.city, p.is_active, z.name_es AS zone_name
            FROM places p
            INNER JOIN zones z ON z.id = p.zone_id
            {$whereSql}
            ORDER BY z.name_es ASC, p.name ASC
        ");
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
        $stmt->execute();

        $handle = fopen('php://temp', 'r+');
        if ($handle === false) {
            $csv = '';
        } else {
            fputcsv($handle, ['ID', 'Nombre', 'Zona', 'Tipo', 'Ciudad', 'Activa']);
            foreach ($stmt->fetchAll() as $place) {
                fputcsv($handle, [
                    (string) ($place['id'] ?? ''),
                    (string) ($place['name'] ?? ''),
                    (string) ($place['zone_name'] ?? ''),
                    (string) ($place['type'] ?? ''),
                    (string) ($place['city'] ?? ''),
                    (int) ($place['is_active'] ?? 0) === 1 ? 'Si' : 'No',
                ]);
            }
            rewind($handle);
            $csv = stream_get_contents($handle);
            fclose($handle);
        }

        return new Response("\xEF\xBB\xBF" . (is_string($csv) ? $csv : ''), 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="lugares-' . date('Ymd-His') . '.csv"',
        ]);
    }

    public function create(Request $request): Response
    {
        $db = DB::connection();
        $zonesStmt = $db->query('SELECT id, name_es FROM zones WHERE is_active = 1 ORDER BY name_es ASC');
        $zones = $zonesStmt->fetchAll();

        if ($request->method() === 'GET') {
            return Response::view('admin/catalog/places/create', [
                'title' => 'Nuevo lugar',
                'csrf_token' => Csrf::token(),
                'zones' => $zones,
                'errors' => [],
                'form' => ['zone_id' => '', 'type' => 'HOTEL', 'name' => '', 'city' => ''],
            ], 'admin');
        }

        if (!Csrf::validate((string) $request->post('_csrf', ''))) {
            return Response::redirect('/admin/catalog/places');
        }

        $form = [
            'zone_id' => trim((string) $request->post('zone_id', '')),
            'type' => strtoupper(trim((string) $request->post('type', 'HOTEL'))),
            'name' => trim((string) $request->post('name', '')),
            'city' => trim((string) $request->post('city', '')),
        ];

        $errors = Validator::required($form, ['zone_id', 'type', 'name']);
        if (!empty($errors)) {
            return Response::view('admin/catalog/places/create', [
                'title' => 'Nuevo lugar',
                'csrf_token' => Csrf::token(),
                'zones' => $zones,
                'errors' => $errors,
                'form' => $form,
            ], 'admin');
        }

        $stmt = $db->prepare(
            'INSERT INTO places (zone_id, type, name, city, created_at) VALUES (:zone_id, :type, :name, :city, NOW())'
        );
        $stmt->execute([
            'zone_id' => (int) $form['zone_id'],
            'type' => $form['type'],
            'name' => $form['name'],
            'city' => $form['city'],
        ]);

        return Response::redirect('/admin/catalog/places');
    }

    public function edit(Request $request): Response
    {
        $id = (int) $request->query('id', 0);
        if ($id <= 0) {
            return Response::redirect('/admin/catalog/places');
        }

        $db = DB::connection();
        $zonesStmt = $db->query('SELECT id, name_es FROM zones WHERE is_active = 1 ORDER BY name_es ASC');
        $zones = $zonesStmt->fetchAll();

        $placeStmt = $db->prepare('SELECT id, zone_id, type, name, city, is_active FROM places WHERE id = :id LIMIT 1');
        $placeStmt->execute(['id' => $id]);
        $place = $placeStmt->fetch();

        if (!$place) {
            return Response::redirect('/admin/catalog/places');
        }

        if ($request->method() === 'GET') {
            return Response::view('admin/catalog/places/edit', [
                'title' => 'Editar lugar',
                'csrf_token' => Csrf::token(),
                'zones' => $zones,
                'errors' => [],
                'form' => [
                    'id' => (string) $place['id'],
                    'zone_id' => (string) $place['zone_id'],
                    'type' => (string) $place['type'],
                    'name' => (string) $place['name'],
                    'city' => (string) ($place['city'] ?? ''),
                    'is_active' => (string) $place['is_active'],
                ],
            ], 'admin');
        }

        if (!Csrf::validate((string) $request->post('_csrf', ''))) {
            return Response::redirect('/admin/catalog/places');
        }

        $form = [
            'id' => (string) $id,
            'zone_id' => trim((string) $request->post('zone_id', '')),
            'type' => strtoupper(trim((string) $request->post('type', 'HOTEL'))),
            'name' => trim((string) $request->post('name', '')),
            'city' => trim((string) $request->post('city', '')),
            'is_active' => $request->post('is_active') !== null ? 1 : 0,
        ];

        $errors = Validator::required($form, ['zone_id', 'type', 'name']);
        if (!in_array($form['type'], ['HOTEL', 'AIRBNB', 'POINT'], true)) {
            $errors['type'] = 'Tipo inválido.';
        }

        if (!empty($errors)) {
            return Response::view('admin/catalog/places/edit', [
                'title' => 'Editar lugar',
                'csrf_token' => Csrf::token(),
                'zones' => $zones,
                'errors' => $errors,
                'form' => $form,
            ], 'admin');
        }

        $updateStmt = $db->prepare(
            'UPDATE places
             SET zone_id = :zone_id, type = :type, name = :name, city = :city, is_active = :is_active
             WHERE id = :id'
        );
        $updateStmt->execute([
            'id' => $id,
            'zone_id' => (int) $form['zone_id'],
            'type' => $form['type'],
            'name' => $form['name'],
            'city' => $form['city'],
            'is_active' => $form['is_active'],
        ]);

        return Response::redirect('/admin/catalog/places');
    }
}
