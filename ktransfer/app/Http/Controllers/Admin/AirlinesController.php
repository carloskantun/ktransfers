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
        $stmt = $db->query('
            SELECT id, code, name, is_active, created_at
            FROM airlines
            ORDER BY name ASC
        ');
        $airlines = $stmt->fetchAll();

        return Response::view('admin/catalog/airlines/index', [
            'title' => 'Airlines',
            'airlines' => $airlines,
        ], 'admin');
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
