<?php
declare(strict_types=1);
namespace App\Http\Controllers\Admin;

use App\Core\Csrf;
use App\Core\DB;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;

class ServicesController {
    public function index(Request $request): Response
    {
        $db = DB::connection();
        $stmt = $db->query(
            'SELECT id, code, name_es, name_en, is_active, sort_order FROM service_types ORDER BY sort_order ASC, id ASC'
        );
        $services = $stmt->fetchAll();

        return Response::view('admin/catalog/services/index', [
            'title' => 'Service Types',
            'services' => $services,
            'csrf_token' => Csrf::token(),
        ], 'admin');
    }

    public function edit(Request $request): Response
    {
        $id = (int) $request->query('id', 0);
        if ($id <= 0) {
            return Response::redirect('/admin/catalog/services');
        }

        $db = DB::connection();
        $stmt = $db->prepare('SELECT id, code, name_es, name_en, is_active, sort_order FROM service_types WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $service = $stmt->fetch();

        if (!$service) {
            return Response::redirect('/admin/catalog/services');
        }

        if ($request->method() === 'GET') {
            return Response::view('admin/catalog/services/edit', [
                'title' => 'Edit Service Type',
                'csrf_token' => Csrf::token(),
                'errors' => [],
                'form' => [
                    'id' => (string) $service['id'],
                    'code' => (string) $service['code'],
                    'name_es' => (string) $service['name_es'],
                    'name_en' => (string) $service['name_en'],
                    'sort_order' => (string) $service['sort_order'],
                    'is_active' => (string) $service['is_active'],
                ],
            ], 'admin');
        }

        if (!Csrf::validate((string) $request->post('_csrf', ''))) {
            return Response::redirect('/admin/catalog/services');
        }

        $form = [
            'id' => (string) $id,
            'code' => trim((string) $request->post('code', '')),
            'name_es' => trim((string) $request->post('name_es', '')),
            'name_en' => trim((string) $request->post('name_en', '')),
            'sort_order' => trim((string) $request->post('sort_order', '')),
            'is_active' => $request->post('is_active') !== null ? 1 : 0,
        ];

        $errors = Validator::required($form, ['code', 'name_es', 'name_en', 'sort_order']);
        if (!ctype_digit($form['sort_order']) || (int) $form['sort_order'] < 0) {
            $errors['sort_order'] = 'sort_order inválido.';
        }

        if (!empty($errors)) {
            return Response::view('admin/catalog/services/edit', [
                'title' => 'Edit Service Type',
                'csrf_token' => Csrf::token(),
                'errors' => $errors,
                'form' => $form,
            ], 'admin');
        }

        $updateStmt = $db->prepare(
            'UPDATE service_types
             SET code = :code,
                 name_es = :name_es,
                 name_en = :name_en,
                 sort_order = :sort_order,
                 is_active = :is_active
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

        return Response::redirect('/admin/catalog/services');
    }
}
