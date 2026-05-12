<?php
declare(strict_types=1);
namespace App\Http\Controllers\Admin;

use App\Core\Csrf;
use App\Core\DB;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;

class AirlinesController {
    public function index(Request $request): Response
    {
        $db = DB::connection();
        $perPage = 100;
        $page = max(1, (int) $request->query('page', 1));
        $search = trim((string) $request->query('q', ''));
        $active = trim((string) $request->query('active', ''));
        $where = [];
        $params = [];
        if ($search !== '') {
            $where[] = '(code LIKE :search OR name LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }
        if ($active === '1' || $active === '0') {
            $where[] = 'is_active = :active';
            $params['active'] = (int) $active;
        }
        $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $countStmt = $db->prepare("SELECT COUNT(*) FROM airlines {$whereSql}");
        foreach ($params as $key => $value) {
            $countStmt->bindValue(':' . $key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();
        $totalPages = max(1, (int) ceil($total / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
        }
        $offset = ($page - 1) * $perPage;

        $stmt = $db->prepare("
            SELECT id, code, name, is_active, created_at
            FROM airlines
            {$whereSql}
            ORDER BY name ASC
            LIMIT :limit OFFSET :offset
        ");
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $airlines = $stmt->fetchAll();

        return Response::view('admin/catalog/airlines/index', [
            'title' => 'Airlines',
            'airlines' => $airlines,
            'filters' => ['q' => $search, 'active' => $active],
            'pagination' => ['page' => $page, 'total_pages' => $totalPages, 'total' => $total],
        ], 'admin');
    }

    public function export(Request $request): Response
    {
        $db = DB::connection();
        $search = trim((string) $request->query('q', ''));
        $active = trim((string) $request->query('active', ''));
        $where = [];
        $params = [];
        if ($search !== '') {
            $where[] = '(code LIKE :search OR name LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }
        if ($active === '1' || $active === '0') {
            $where[] = 'is_active = :active';
            $params['active'] = (int) $active;
        }
        $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $stmt = $db->prepare("SELECT id, code, name, is_active, created_at FROM airlines {$whereSql} ORDER BY name ASC");
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
        $stmt->execute();

        $handle = fopen('php://temp', 'r+');
        if ($handle === false) {
            $csv = '';
        } else {
            fputcsv($handle, ['ID', 'Codigo', 'Nombre', 'Activa', 'Creada']);
            foreach ($stmt->fetchAll() as $airline) {
                fputcsv($handle, [
                    (string) ($airline['id'] ?? ''),
                    (string) ($airline['code'] ?? ''),
                    (string) ($airline['name'] ?? ''),
                    (int) ($airline['is_active'] ?? 0) === 1 ? 'Si' : 'No',
                    (string) ($airline['created_at'] ?? ''),
                ]);
            }
            rewind($handle);
            $csv = stream_get_contents($handle);
            fclose($handle);
        }

        return new Response("\xEF\xBB\xBF" . (is_string($csv) ? $csv : ''), 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="aerolineas-' . date('Ymd-His') . '.csv"',
        ]);
    }

    public function create(Request $request): Response
    {
        if ($request->method() === 'GET') {
            return Response::view('admin/catalog/airlines/create', [
                'title' => 'Create Airline',
                'csrf_token' => Csrf::token(),
                'errors' => [],
                'form' => ['code' => '', 'name' => '', 'is_active' => '1'],
            ], 'admin');
        }

        // POST
        if (!Csrf::validate((string) $request->post('csrf_token', ''))) {
            return Response::redirect('/admin/catalog/airlines');
        }

        $form = [
            'code' => strtoupper(trim((string) $request->post('code', ''))),
            'name' => trim((string) $request->post('name', '')),
            'is_active' => $request->post('is_active') !== null ? 1 : 0,
        ];

        $errors = Validator::required($form, ['code', 'name']);
        if (!empty($errors)) {
            return Response::view('admin/catalog/airlines/create', [
                'title' => 'Create Airline',
                'csrf_token' => Csrf::token(),
                'errors' => $errors,
                'form' => $form,
            ], 'admin');
        }

        $db = DB::connection();
        $stmt = $db->prepare('
            INSERT INTO airlines (code, name, is_active, created_at)
            VALUES (:code, :name, :is_active, NOW())
        ');
        $stmt->execute([
            'code' => $form['code'],
            'name' => $form['name'],
            'is_active' => $form['is_active'],
        ]);

        return Response::redirect('/admin/catalog/airlines');
    }

    public function edit(Request $request): Response
    {
        $id = (int) $request->query('id', 0);
        if ($id <= 0) {
            return Response::redirect('/admin/catalog/airlines');
        }

        $db = DB::connection();
        $stmt = $db->prepare('SELECT id, code, name, is_active FROM airlines WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $airline = $stmt->fetch();

        if (!$airline) {
            return Response::redirect('/admin/catalog/airlines');
        }

        if ($request->method() === 'GET') {
            return Response::view('admin/catalog/airlines/edit', [
                'title' => 'Edit Airline',
                'csrf_token' => Csrf::token(),
                'errors' => [],
                'form' => [
                    'id' => (string) $airline['id'],
                    'code' => (string) $airline['code'],
                    'name' => (string) $airline['name'],
                    'is_active' => (string) $airline['is_active'],
                ],
            ], 'admin');
        }

        if (!Csrf::validate((string) $request->post('csrf_token', ''))) {
            return Response::redirect('/admin/catalog/airlines');
        }

        $form = [
            'id' => (string) $id,
            'code' => strtoupper(trim((string) $request->post('code', ''))),
            'name' => trim((string) $request->post('name', '')),
            'is_active' => $request->post('is_active') !== null ? 1 : 0,
        ];

        $errors = Validator::required($form, ['code', 'name']);
        if (!empty($errors)) {
            return Response::view('admin/catalog/airlines/edit', [
                'title' => 'Edit Airline',
                'csrf_token' => Csrf::token(),
                'errors' => $errors,
                'form' => $form,
            ], 'admin');
        }

        $updateStmt = $db->prepare(
            'UPDATE airlines SET code = :code, name = :name, is_active = :is_active WHERE id = :id'
        );
        $updateStmt->execute([
            'id' => $id,
            'code' => $form['code'],
            'name' => $form['name'],
            'is_active' => $form['is_active'],
        ]);

        return Response::redirect('/admin/catalog/airlines');
    }
}
