<?php
declare(strict_types=1);
namespace App\Http\Controllers\Admin;

use App\Core\Csrf;
use App\Core\DB;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;

class ZonesController {
    public function index(Request $request): Response
    {
        $db = DB::connection();
        $stmt = $db->query(
            'SELECT id, code, name_es, name_en, is_active, sort_order FROM zones ORDER BY sort_order ASC, id ASC'
        );
        $zones = $stmt->fetchAll();

        return Response::view('admin/catalog/zones/index', [
            'title' => 'Zones',
            'zones' => $zones,
            'csrf_token' => Csrf::token(),
        ], 'admin');
    }

    public function create(Request $request): Response
    {
        if ($request->method() === 'GET') {
            return Response::view('admin/catalog/zones/create', [
                'title' => 'Create Zone',
                'csrf_token' => Csrf::token(),
                'errors' => [],
                'form' => [
                    'code' => '',
                    'name_es' => '',
                    'name_en' => '',
                    'sort_order' => '0',
                ],
            ], 'admin');
        }

        if (!Csrf::validate((string) $request->post('_csrf', ''))) {
            return Response::redirect('/admin/catalog/zones');
        }

        $form = [
            'code' => trim((string) $request->post('code', '')),
            'name_es' => trim((string) $request->post('name_es', '')),
            'name_en' => trim((string) $request->post('name_en', '')),
            'sort_order' => trim((string) $request->post('sort_order', '0')),
        ];

        $errors = Validator::required($form, ['code', 'name_es', 'name_en']);
        if (!empty($errors)) {
            return Response::view('admin/catalog/zones/create', [
                'title' => 'Create Zone',
                'csrf_token' => Csrf::token(),
                'errors' => $errors,
                'form' => $form,
            ], 'admin');
        }

        $db = DB::connection();
        $stmt = $db->prepare(
            'INSERT INTO zones (code, name_es, name_en, sort_order, created_at)
             VALUES (:code, :name_es, :name_en, :sort_order, NOW())'
        );
        $stmt->execute([
            'code' => $form['code'],
            'name_es' => $form['name_es'],
            'name_en' => $form['name_en'],
            'sort_order' => (int) $form['sort_order'],
        ]);

        return Response::redirect('/admin/catalog/zones');
    }

    public function edit(Request $request): Response
    {
        $id = (int) $request->query('id', 0);
        if ($id <= 0) {
            return Response::redirect('/admin/catalog/zones');
        }

        $db = DB::connection();
        $stmt = $db->prepare(
            'SELECT id, code, name_es, name_en, sort_order, is_active FROM zones WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $zone = $stmt->fetch();

        if (!$zone) {
            return Response::redirect('/admin/catalog/zones');
        }

        if ($request->method() === 'GET') {
            return Response::view('admin/catalog/zones/edit', [
                'title' => 'Edit Zone',
                'csrf_token' => Csrf::token(),
                'errors' => [],
                'form' => [
                    'id' => (string) $zone['id'],
                    'code' => (string) $zone['code'],
                    'name_es' => (string) $zone['name_es'],
                    'name_en' => (string) $zone['name_en'],
                    'sort_order' => (string) $zone['sort_order'],
                    'is_active' => (string) $zone['is_active'],
                ],
            ], 'admin');
        }

        if (!Csrf::validate((string) $request->post('_csrf', ''))) {
            return Response::redirect('/admin/catalog/zones');
        }

        $form = [
            'id' => (string) $id,
            'code' => trim((string) $request->post('code', '')),
            'name_es' => trim((string) $request->post('name_es', '')),
            'name_en' => trim((string) $request->post('name_en', '')),
            'sort_order' => trim((string) $request->post('sort_order', '0')),
            'is_active' => $request->post('is_active') !== null ? 1 : 0,
        ];

        $errors = Validator::required($form, ['code', 'name_es', 'name_en']);
        if ($form['sort_order'] !== '' && !preg_match('/^-?\d+$/', $form['sort_order'])) {
            $errors['sort_order'] = 'sort_order inválido.';
        }

        if (!empty($errors)) {
            return Response::view('admin/catalog/zones/edit', [
                'title' => 'Edit Zone',
                'csrf_token' => Csrf::token(),
                'errors' => $errors,
                'form' => $form,
            ], 'admin');
        }

        $updateStmt = $db->prepare(
            'UPDATE zones
             SET code = :code, name_es = :name_es, name_en = :name_en, sort_order = :sort_order, is_active = :is_active
             WHERE id = :id'
        );
        $updateStmt->execute([
            'id' => $id,
            'code' => $form['code'],
            'name_es' => $form['name_es'],
            'name_en' => $form['name_en'],
            'sort_order' => (int) $form['sort_order'],
            'is_active' => $form['is_active'],
        ]);

        return Response::redirect('/admin/catalog/zones');
    }
}
