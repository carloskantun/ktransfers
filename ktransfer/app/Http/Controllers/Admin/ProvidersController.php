<?php
declare(strict_types=1);
namespace App\Http\Controllers\Admin;

use App\Core\Csrf;
use App\Core\DB;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;

class ProvidersController {
    public function index(Request $request): Response
    {
        $db = DB::connection();
        $search = trim((string) $request->query('q', ''));
        $active = trim((string) $request->query('active', ''));
        $where = [];
        $params = [];
        if ($search !== '') {
            $where[] = '(p.name LIKE :search OR p.contact_name LIKE :search OR p.email LIKE :search OR p.phone LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }
        if ($active === '1' || $active === '0') {
            $where[] = 'p.is_active = :active';
            $params['active'] = (int) $active;
        }
        $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $db->prepare(
            "SELECT
                p.id,
                p.name,
                p.contact_name,
                p.email,
                p.phone,
                p.is_active,
                p.created_at,
                COUNT(a.id) AS assigned_services
             FROM providers p
             LEFT JOIN assignments a ON a.provider_id = p.id
             {$whereSql}
               GROUP BY p.id, p.name, p.contact_name, p.email, p.phone, p.is_active, p.created_at
             ORDER BY p.name ASC"
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
        $stmt->execute();

        return Response::view('admin/catalog/providers/index', [
            'title' => 'Proveedores',
            'providers' => $stmt->fetchAll(),
            'filters' => ['q' => $search, 'active' => $active],
            'csrf_token' => Csrf::token(),
        ], 'admin');
    }

    public function create(Request $request): Response
    {
        if ($request->method() === 'GET') {
            return Response::view('admin/catalog/providers/create', [
                'title' => 'Nuevo proveedor',
                'csrf_token' => Csrf::token(),
                'errors' => [],
                'form' => [
                    'name' => '',
                    'contact_name' => '',
                    'email' => '',
                    'phone' => '',
                    'is_active' => '1',
                ],
            ], 'admin');
        }

        if (!Csrf::validate((string) $request->post('_csrf', ''))) {
            return Response::redirect('/admin/catalog/providers');
        }

        $form = $this->formFromRequest($request);
        $errors = $this->validateForm($form);

        if (!empty($errors)) {
            return Response::view('admin/catalog/providers/create', [
                'title' => 'Nuevo proveedor',
                'csrf_token' => Csrf::token(),
                'errors' => $errors,
                'form' => $form,
            ], 'admin');
        }

        $db = DB::connection();
        $stmt = $db->prepare(
            'INSERT INTO providers (name, contact_name, email, phone, is_active, created_at)
             VALUES (:name, :contact_name, :email, :phone, :is_active, NOW())'
        );
        $stmt->execute([
            'name' => $form['name'],
            'contact_name' => $form['contact_name'],
            'email' => $form['email'] !== '' ? $form['email'] : null,
            'phone' => $form['phone'] !== '' ? $form['phone'] : null,
            'is_active' => (int) $form['is_active'],
        ]);

        return Response::redirect('/admin/catalog/providers');
    }

    public function edit(Request $request): Response
    {
        $id = (int) $request->query('id', 0);
        if ($id <= 0) {
            return Response::redirect('/admin/catalog/providers');
        }

        $db = DB::connection();
        $stmt = $db->prepare('SELECT id, name, contact_name, email, phone, is_active FROM providers WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $provider = $stmt->fetch();

        if (!$provider) {
            return Response::redirect('/admin/catalog/providers');
        }

        if ($request->method() === 'GET') {
            return Response::view('admin/catalog/providers/edit', [
                'title' => 'Editar proveedor',
                'csrf_token' => Csrf::token(),
                'errors' => [],
                'form' => [
                    'id' => (string) $provider['id'],
                    'name' => (string) $provider['name'],
                    'contact_name' => (string) ($provider['contact_name'] ?? ''),
                    'email' => (string) ($provider['email'] ?? ''),
                    'phone' => (string) ($provider['phone'] ?? ''),
                    'is_active' => (int) ($provider['is_active'] ?? 0) === 1 ? '1' : '0',
                ],
            ], 'admin');
        }

        if (!Csrf::validate((string) $request->post('_csrf', ''))) {
            return Response::redirect('/admin/catalog/providers/edit?id=' . $id);
        }

        $form = $this->formFromRequest($request);
        $form['id'] = (string) $id;
        $errors = $this->validateForm($form);

        if (!empty($errors)) {
            return Response::view('admin/catalog/providers/edit', [
                'title' => 'Editar proveedor',
                'csrf_token' => Csrf::token(),
                'errors' => $errors,
                'form' => $form,
            ], 'admin');
        }

        $updateStmt = $db->prepare(
            'UPDATE providers
             SET name = :name,
                 contact_name = :contact_name,
                 email = :email,
                 phone = :phone,
                 is_active = :is_active
             WHERE id = :id'
        );
        $updateStmt->execute([
            'id' => $id,
            'name' => $form['name'],
            'contact_name' => $form['contact_name'],
            'email' => $form['email'] !== '' ? $form['email'] : null,
            'phone' => $form['phone'] !== '' ? $form['phone'] : null,
            'is_active' => (int) $form['is_active'],
        ]);

        return Response::redirect('/admin/catalog/providers');
    }

    private function formFromRequest(Request $request): array
    {
        return [
            'name' => trim((string) $request->post('name', '')),
            'contact_name' => trim((string) $request->post('contact_name', '')),
            'email' => trim((string) $request->post('email', '')),
            'phone' => trim((string) $request->post('phone', '')),
            'is_active' => $request->post('is_active') !== null ? '1' : '0',
        ];
    }

    private function validateForm(array $form): array
    {
        $errors = Validator::required($form, ['name', 'contact_name']);

        if ($form['email'] !== '' && !filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email invalido.';
        }

        return $errors;
    }
}
