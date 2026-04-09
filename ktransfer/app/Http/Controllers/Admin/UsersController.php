<?php
declare(strict_types=1);
namespace App\Http\Controllers\Admin;

use App\Core\Csrf;
use App\Core\DB;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use PDO;

class UsersController {
    public function index(Request $request): Response
    {
        $db = DB::connection();
        $stmt = $db->query(
            'SELECT
                u.id,
                u.name,
                u.email,
                u.is_active,
                u.created_at,
                COALESCE(GROUP_CONCAT(DISTINCT r.name ORDER BY r.name SEPARATOR ", "), "Sin rol") AS role_names
             FROM users u
             LEFT JOIN user_roles ur ON ur.user_id = u.id
             LEFT JOIN roles r ON r.id = ur.role_id
             GROUP BY u.id, u.name, u.email, u.is_active, u.created_at
             ORDER BY u.name ASC'
        );
        $users = $stmt->fetchAll();

        return Response::view('admin/users/index', [
            'title' => 'Users',
            'users' => $users,
            'csrf_token' => Csrf::token(),
        ], 'admin');
    }

    public function create(Request $request): Response
    {
        $db = DB::connection();
        $roles = $this->loadRoles($db);

        if ($request->method() === 'GET') {
            return Response::view('admin/users/create', [
                'title' => 'Create User',
                'csrf_token' => Csrf::token(),
                'roles' => $roles,
                'errors' => [],
                'form' => [
                    'name' => '',
                    'email' => '',
                    'password' => '',
                    'role_id' => (string) ($roles[0]['id'] ?? ''),
                    'is_active' => '1',
                ],
            ], 'admin');
        }

        if (!Csrf::validate((string) $request->post('_csrf', ''))) {
            return Response::redirect('/admin/users');
        }

        $form = [
            'name' => trim((string) $request->post('name', '')),
            'email' => trim((string) $request->post('email', '')),
            'password' => (string) $request->post('password', ''),
            'role_id' => trim((string) $request->post('role_id', '')),
            'is_active' => $request->post('is_active') !== null ? '1' : '0',
        ];

        $errors = Validator::required($form, ['name', 'email', 'password']);
        if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email inválido.';
        }
        if (!$this->roleExists($roles, $form['role_id'])) {
            $errors['role_id'] = 'Rol inválido.';
        }

        if ($this->emailExists($db, $form['email'])) {
            $errors['email'] = 'Ya existe un usuario con este email.';
        }

        if (!empty($errors)) {
            return Response::view('admin/users/create', [
                'title' => 'Create User',
                'csrf_token' => Csrf::token(),
                'roles' => $roles,
                'errors' => $errors,
                'form' => $form,
            ], 'admin');
        }

        $passwordHash = password_hash($form['password'], PASSWORD_DEFAULT);

        $stmt = $db->prepare(
            'INSERT INTO users (name, email, password_hash, is_active, created_at) VALUES (:name, :email, :password_hash, :is_active, NOW())'
        );
        $stmt->execute([
            'name' => $form['name'],
            'email' => $form['email'],
            'password_hash' => $passwordHash,
            'is_active' => (int) $form['is_active'],
        ]);

        $this->syncUserRole($db, (int) $db->lastInsertId(), (int) $form['role_id']);

        return Response::redirect('/admin/users');
    }

    public function edit(Request $request): Response
    {
        $id = (int) $request->query('id', 0);
        if ($id <= 0) {
            return Response::redirect('/admin/users');
        }

        $db = DB::connection();
        $roles = $this->loadRoles($db);

        $userStmt = $db->prepare(
            'SELECT
                u.id,
                u.name,
                u.email,
                u.is_active,
                (
                    SELECT ur.role_id
                    FROM user_roles ur
                    WHERE ur.user_id = u.id
                    ORDER BY ur.role_id ASC
                    LIMIT 1
                ) AS role_id
             FROM users u
             WHERE u.id = :id
             LIMIT 1'
        );
        $userStmt->execute(['id' => $id]);
        $user = $userStmt->fetch();

        if (!$user) {
            return Response::redirect('/admin/users');
        }

        if ($request->method() === 'GET') {
            return Response::view('admin/users/edit', [
                'title' => 'Edit User',
                'csrf_token' => Csrf::token(),
                'roles' => $roles,
                'errors' => [],
                'form' => [
                    'id' => (string) $user['id'],
                    'name' => (string) $user['name'],
                    'email' => (string) $user['email'],
                    'password' => '',
                    'role_id' => (string) ($user['role_id'] ?? ''),
                    'is_active' => (int) ($user['is_active'] ?? 0) === 1 ? '1' : '0',
                ],
            ], 'admin');
        }

        if (!Csrf::validate((string) $request->post('_csrf', ''))) {
            return Response::redirect('/admin/users/edit?id=' . $id);
        }

        $form = [
            'id' => (string) $id,
            'name' => trim((string) $request->post('name', '')),
            'email' => trim((string) $request->post('email', '')),
            'password' => (string) $request->post('password', ''),
            'role_id' => trim((string) $request->post('role_id', '')),
            'is_active' => $request->post('is_active') !== null ? '1' : '0',
        ];

        $errors = Validator::required($form, ['name', 'email']);
        if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email inválido.';
        }
        if (!$this->roleExists($roles, $form['role_id'])) {
            $errors['role_id'] = 'Rol inválido.';
        }
        if ($this->emailExists($db, $form['email'], $id)) {
            $errors['email'] = 'Ya existe un usuario con este email.';
        }

        if (!empty($errors)) {
            return Response::view('admin/users/edit', [
                'title' => 'Edit User',
                'csrf_token' => Csrf::token(),
                'roles' => $roles,
                'errors' => $errors,
                'form' => $form,
            ], 'admin');
        }

        if ($form['password'] !== '') {
            $passwordHash = password_hash($form['password'], PASSWORD_DEFAULT);
            $updateStmt = $db->prepare(
                'UPDATE users
                 SET name = :name,
                     email = :email,
                     password_hash = :password_hash,
                     is_active = :is_active,
                     updated_at = NOW()
                 WHERE id = :id'
            );
            $updateStmt->execute([
                'id' => $id,
                'name' => $form['name'],
                'email' => $form['email'],
                'password_hash' => $passwordHash,
                'is_active' => (int) $form['is_active'],
            ]);
        } else {
            $updateStmt = $db->prepare(
                'UPDATE users
                 SET name = :name,
                     email = :email,
                     is_active = :is_active,
                     updated_at = NOW()
                 WHERE id = :id'
            );
            $updateStmt->execute([
                'id' => $id,
                'name' => $form['name'],
                'email' => $form['email'],
                'is_active' => (int) $form['is_active'],
            ]);
        }

        $this->syncUserRole($db, $id, (int) $form['role_id']);

        return Response::redirect('/admin/users');
    }

    private function loadRoles(PDO $db): array
    {
        $stmt = $db->query('SELECT id, code, name FROM roles ORDER BY name ASC');
        return $stmt->fetchAll();
    }

    private function roleExists(array $roles, string $roleId): bool
    {
        if (!ctype_digit($roleId) || (int) $roleId <= 0) {
            return false;
        }

        foreach ($roles as $role) {
            if ((int) ($role['id'] ?? 0) === (int) $roleId) {
                return true;
            }
        }

        return false;
    }

    private function emailExists(PDO $db, string $email, ?int $ignoreUserId = null): bool
    {
        $sql = 'SELECT id FROM users WHERE email = :email';
        $params = ['email' => $email];

        if ($ignoreUserId !== null) {
            $sql .= ' AND id <> :ignore_user_id';
            $params['ignore_user_id'] = $ignoreUserId;
        }

        $sql .= ' LIMIT 1';

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return (bool) $stmt->fetch();
    }

    private function syncUserRole(PDO $db, int $userId, int $roleId): void
    {
        $deleteStmt = $db->prepare('DELETE FROM user_roles WHERE user_id = :user_id');
        $deleteStmt->execute(['user_id' => $userId]);

        $insertStmt = $db->prepare('INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)');
        $insertStmt->execute([
            'user_id' => $userId,
            'role_id' => $roleId,
        ]);
    }
}
