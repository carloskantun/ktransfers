<?php
declare(strict_types=1);
namespace App\Http\Controllers\Admin;

use App\Core\Csrf;
use App\Core\DB;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;

class VehiclesController {
    public function index(Request $request): Response
    {
        $db = DB::connection();
        $stmt = $db->query(
            'SELECT id, code, name, max_pax, is_active FROM vehicles ORDER BY name ASC'
        );
        $vehicles = $stmt->fetchAll();

        return Response::view('admin/catalog/vehicles/index', [
            'title' => 'Vehicles',
            'vehicles' => $vehicles,
            'csrf_token' => Csrf::token(),
        ], 'admin');
    }

    public function create(Request $request): Response
    {
        if ($request->method() === 'GET') {
            return Response::view('admin/catalog/vehicles/create', [
                'title' => 'Create Vehicle',
                'csrf_token' => Csrf::token(),
                'errors' => [],
                'form' => ['code' => '', 'name' => '', 'max_pax' => ''],
            ], 'admin');
        }

        if (!Csrf::validate((string) $request->post('_csrf', ''))) {
            return Response::redirect('/admin/catalog/vehicles');
        }

        $form = [
            'code' => trim((string) $request->post('code', '')),
            'name' => trim((string) $request->post('name', '')),
            'max_pax' => trim((string) $request->post('max_pax', '')),
        ];

        $errors = Validator::required($form, ['code', 'name', 'max_pax']);
        if (!empty($errors)) {
            return Response::view('admin/catalog/vehicles/create', [
                'title' => 'Create Vehicle',
                'csrf_token' => Csrf::token(),
                'errors' => $errors,
                'form' => $form,
            ], 'admin');
        }

        $db = DB::connection();
        $stmt = $db->prepare(
            'INSERT INTO vehicles (code, name, max_pax, created_at) VALUES (:code, :name, :max_pax, NOW())'
        );
        $stmt->execute([
            'code' => $form['code'],
            'name' => $form['name'],
            'max_pax' => (int) $form['max_pax'],
        ]);

        return Response::redirect('/admin/catalog/vehicles');
    }

    public function edit(Request $request): Response
    {
        $id = (int) $request->query('id', 0);
        if ($id <= 0) {
            return Response::redirect('/admin/catalog/vehicles');
        }

        $db = DB::connection();
        $stmt = $db->prepare('SELECT id, code, name, max_pax, is_active FROM vehicles WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $vehicle = $stmt->fetch();

        if (!$vehicle) {
            return Response::redirect('/admin/catalog/vehicles');
        }

        if ($request->method() === 'GET') {
            return Response::view('admin/catalog/vehicles/edit', [
                'title' => 'Edit Vehicle',
                'csrf_token' => Csrf::token(),
                'errors' => [],
                'form' => [
                    'id' => (string) $vehicle['id'],
                    'code' => (string) $vehicle['code'],
                    'name' => (string) $vehicle['name'],
                    'max_pax' => (string) $vehicle['max_pax'],
                    'is_active' => (string) $vehicle['is_active'],
                ],
            ], 'admin');
        }

        if (!Csrf::validate((string) $request->post('_csrf', ''))) {
            return Response::redirect('/admin/catalog/vehicles');
        }

        $form = [
            'id' => (string) $id,
            'code' => trim((string) $request->post('code', '')),
            'name' => trim((string) $request->post('name', '')),
            'max_pax' => trim((string) $request->post('max_pax', '')),
            'is_active' => $request->post('is_active') !== null ? 1 : 0,
        ];

        $errors = Validator::required($form, ['code', 'name', 'max_pax']);
        if (!ctype_digit($form['max_pax']) || (int) $form['max_pax'] < 1) {
            $errors['max_pax'] = 'max_pax inválido.';
        }

        if (!empty($errors)) {
            return Response::view('admin/catalog/vehicles/edit', [
                'title' => 'Edit Vehicle',
                'csrf_token' => Csrf::token(),
                'errors' => $errors,
                'form' => $form,
            ], 'admin');
        }

        $updateStmt = $db->prepare(
            'UPDATE vehicles
             SET code = :code, name = :name, max_pax = :max_pax, is_active = :is_active
             WHERE id = :id'
        );
        $updateStmt->execute([
            'id' => $id,
            'code' => $form['code'],
            'name' => $form['name'],
            'max_pax' => (int) $form['max_pax'],
            'is_active' => $form['is_active'],
        ]);

        return Response::redirect('/admin/catalog/vehicles');
    }
}
