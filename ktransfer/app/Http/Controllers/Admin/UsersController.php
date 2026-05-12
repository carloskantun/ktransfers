<?php
declare(strict_types=1);
namespace App\Http\Controllers\Admin;

use App\Core\ACL;
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
        $roles = $this->filterAssignableRoles($this->loadRoles($db));
        $search = trim((string) $request->query('q', ''));
        $roleCode = trim((string) $request->query('role', ''));
        $active = trim((string) $request->query('active', ''));
        $where = [];
        $having = [];
        $params = [];

        if ($search !== '') {
            $where[] = '(u.name LIKE :search OR u.email LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }
        if ($active === '1' || $active === '0') {
            $where[] = 'u.is_active = :active';
            $params['active'] = (int) $active;
        }
        if ($roleCode !== '') {
            $having[] = 'FIND_IN_SET(:role_code, role_codes) > 0';
            $params['role_code'] = $roleCode;
        }

        $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $havingSql = !empty($having) ? 'HAVING ' . implode(' AND ', $having) : '';

        $stmt = $db->prepare(
            "SELECT
                u.id,
                u.name,
                u.email,
                u.provider_id,
                p.name AS provider_name,
                u.is_active,
                u.created_at,
                COALESCE(GROUP_CONCAT(DISTINCT r.name ORDER BY r.name SEPARATOR ', '), 'Sin rol') AS role_names,
                COALESCE(GROUP_CONCAT(DISTINCT r.code ORDER BY r.code SEPARATOR ','), '') AS role_codes
             FROM users u
             LEFT JOIN providers p ON p.id = u.provider_id
             LEFT JOIN user_roles ur ON ur.user_id = u.id
             LEFT JOIN roles r ON r.id = ur.role_id
             {$whereSql}
             GROUP BY u.id, u.name, u.email, u.provider_id, p.name, u.is_active, u.created_at
             {$havingSql}
             ORDER BY u.name ASC"
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
        $stmt->execute();
        $users = $stmt->fetchAll();

        return Response::view('admin/users/index', [
            'title' => 'Usuarios',
            'users' => $users,
            'roles' => $roles,
            'filters' => [
                'q' => $search,
                'role' => $roleCode,
                'active' => $active,
            ],
            'csrf_token' => Csrf::token(),
        ], 'admin');
    }

    public function create(Request $request): Response
    {
        $db = DB::connection();
        $roles = $this->filterAssignableRoles($this->loadRoles($db));
        $providers = $this->loadProviders($db);
        $requestedRoleCode = trim((string) $request->query('role', ''));
        $defaultRoleId = $this->roleIdForCode($roles, $requestedRoleCode) ?? (string) ($roles[0]['id'] ?? '');

        if ($request->method() === 'GET') {
            return Response::view('admin/users/create', [
                'title' => 'Crear usuario',
                'csrf_token' => Csrf::token(),
                'roles' => $roles,
                'providers' => $providers,
                'errors' => [],
                'form' => [
                    'name' => '',
                    'email' => '',
                    'password' => '',
                    'role_id' => $defaultRoleId,
                    'provider_mode' => 'existing',
                    'provider_id' => '',
                    'provider_new_name' => '',
                    'provider_new_contact_name' => '',
                    'provider_new_email' => '',
                    'provider_new_phone' => '',
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
            'provider_mode' => trim((string) $request->post('provider_mode', 'existing')),
            'provider_id' => trim((string) $request->post('provider_id', '')),
            'provider_new_name' => trim((string) $request->post('provider_new_name', '')),
            'provider_new_contact_name' => trim((string) $request->post('provider_new_contact_name', '')),
            'provider_new_email' => trim((string) $request->post('provider_new_email', '')),
            'provider_new_phone' => trim((string) $request->post('provider_new_phone', '')),
            'is_active' => $request->post('is_active') !== null ? '1' : '0',
        ];

        $errors = Validator::required($form, ['name', 'email', 'password']);
        if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email inválido.';
        }
        if (!$this->roleExists($roles, $form['role_id'])) {
            $errors['role_id'] = 'Rol inválido.';
        }

        $selectedRoleCode = $this->roleCodeForId($roles, $form['role_id']);
        if ($selectedRoleCode === 'superadmin' && !ACL::currentUserHasRole('superadmin')) {
            $errors['role_id'] = 'Solo un superadmin puede crear otro superadmin.';
        }
        $isAgencyRole = $selectedRoleCode === 'agency';
        $providerId = null;

        if ($isAgencyRole) {
            if (!in_array($form['provider_mode'], ['existing', 'new'], true)) {
                $errors['provider_mode'] = 'Selecciona como vincular la agencia.';
            } elseif ($form['provider_mode'] === 'existing') {
                if (!ctype_digit($form['provider_id']) || (int) $form['provider_id'] <= 0) {
                    $errors['provider_id'] = 'Selecciona una agencia valida.';
                } elseif (!$this->providerExists($providers, (int) $form['provider_id'])) {
                    $errors['provider_id'] = 'La agencia seleccionada no existe.';
                } else {
                    $providerId = (int) $form['provider_id'];
                }
            } else {
                $providerCreateErrors = $this->validateProviderDraft($form);
                $errors = array_merge($errors, $providerCreateErrors);
            }
        }

        if ($this->emailExists($db, $form['email'])) {
            $errors['email'] = 'Ya existe un usuario con este email.';
        }

        if (!empty($errors)) {
            return Response::view('admin/users/create', [
                'title' => 'Crear usuario',
                'csrf_token' => Csrf::token(),
                'roles' => $roles,
                'providers' => $providers,
                'errors' => $errors,
                'form' => $form,
            ], 'admin');
        }

        if ($isAgencyRole && $form['provider_mode'] === 'new') {
            $providerId = $this->createProviderFromDraft($db, $form);
        }

        $passwordHash = password_hash($form['password'], PASSWORD_DEFAULT);

        $stmt = $db->prepare(
            'INSERT INTO users (name, email, password_hash, provider_id, is_active, created_at)
             VALUES (:name, :email, :password_hash, :provider_id, :is_active, NOW())'
        );
        $stmt->execute([
            'name' => $form['name'],
            'email' => $form['email'],
            'password_hash' => $passwordHash,
            'provider_id' => $isAgencyRole ? $providerId : null,
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
        $roles = $this->filterAssignableRoles($this->loadRoles($db));
        $providers = $this->loadProviders($db);

        $userStmt = $db->prepare(
            'SELECT
                u.id,
                u.name,
                u.email,
                u.provider_id,
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
                'title' => 'Editar usuario',
                'csrf_token' => Csrf::token(),
                'roles' => $roles,
                'providers' => $providers,
                'errors' => [],
                'form' => [
                    'id' => (string) $user['id'],
                    'name' => (string) $user['name'],
                    'email' => (string) $user['email'],
                    'password' => '',
                    'role_id' => (string) ($user['role_id'] ?? ''),
                    'provider_mode' => ((int) ($user['provider_id'] ?? 0) > 0) ? 'existing' : 'new',
                    'provider_id' => (string) ((int) ($user['provider_id'] ?? 0) > 0 ? (int) $user['provider_id'] : ''),
                    'provider_new_name' => '',
                    'provider_new_contact_name' => '',
                    'provider_new_email' => '',
                    'provider_new_phone' => '',
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
            'provider_mode' => trim((string) $request->post('provider_mode', 'existing')),
            'provider_id' => trim((string) $request->post('provider_id', '')),
            'provider_new_name' => trim((string) $request->post('provider_new_name', '')),
            'provider_new_contact_name' => trim((string) $request->post('provider_new_contact_name', '')),
            'provider_new_email' => trim((string) $request->post('provider_new_email', '')),
            'provider_new_phone' => trim((string) $request->post('provider_new_phone', '')),
            'is_active' => $request->post('is_active') !== null ? '1' : '0',
        ];

        $errors = Validator::required($form, ['name', 'email']);
        if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email inválido.';
        }
        if (!$this->roleExists($roles, $form['role_id'])) {
            $errors['role_id'] = 'Rol inválido.';
        }

        $selectedRoleCode = $this->roleCodeForId($roles, $form['role_id']);
        if ($selectedRoleCode === 'superadmin' && !ACL::currentUserHasRole('superadmin')) {
            $errors['role_id'] = 'Solo un superadmin puede asignar el rol superadmin.';
        }
        $isAgencyRole = $selectedRoleCode === 'agency';
        $providerId = null;

        if ($isAgencyRole) {
            if (!in_array($form['provider_mode'], ['existing', 'new'], true)) {
                $errors['provider_mode'] = 'Selecciona como vincular la agencia.';
            } elseif ($form['provider_mode'] === 'existing') {
                if (!ctype_digit($form['provider_id']) || (int) $form['provider_id'] <= 0) {
                    $errors['provider_id'] = 'Selecciona una agencia valida.';
                } elseif (!$this->providerExists($providers, (int) $form['provider_id'])) {
                    $errors['provider_id'] = 'La agencia seleccionada no existe.';
                } else {
                    $providerId = (int) $form['provider_id'];
                }
            } else {
                $providerCreateErrors = $this->validateProviderDraft($form);
                $errors = array_merge($errors, $providerCreateErrors);
            }
        }

        if ($this->emailExists($db, $form['email'], $id)) {
            $errors['email'] = 'Ya existe un usuario con este email.';
        }

        if (!empty($errors)) {
            return Response::view('admin/users/edit', [
                'title' => 'Editar usuario',
                'csrf_token' => Csrf::token(),
                'roles' => $roles,
                'providers' => $providers,
                'errors' => $errors,
                'form' => $form,
            ], 'admin');
        }

        if ($isAgencyRole && $form['provider_mode'] === 'new') {
            $providerId = $this->createProviderFromDraft($db, $form);
        }

        if ($form['password'] !== '') {
            $passwordHash = password_hash($form['password'], PASSWORD_DEFAULT);
            $updateStmt = $db->prepare(
                'UPDATE users
                 SET name = :name,
                     email = :email,
                     provider_id = :provider_id,
                     password_hash = :password_hash,
                     is_active = :is_active,
                     updated_at = NOW()
                 WHERE id = :id'
            );
            $updateStmt->execute([
                'id' => $id,
                'name' => $form['name'],
                'email' => $form['email'],
                'provider_id' => $isAgencyRole ? $providerId : null,
                'password_hash' => $passwordHash,
                'is_active' => (int) $form['is_active'],
            ]);
        } else {
            $updateStmt = $db->prepare(
                'UPDATE users
                 SET name = :name,
                     email = :email,
                     provider_id = :provider_id,
                     is_active = :is_active,
                     updated_at = NOW()
                 WHERE id = :id'
            );
            $updateStmt->execute([
                'id' => $id,
                'name' => $form['name'],
                'email' => $form['email'],
                'provider_id' => $isAgencyRole ? $providerId : null,
                'is_active' => (int) $form['is_active'],
            ]);
        }

        $this->syncUserRole($db, $id, (int) $form['role_id']);

        return Response::redirect('/admin/users');
    }

    private function loadRoles(PDO $db): array
    {
        $stmt = $db->query(
            'SELECT id, code, name
             FROM roles
             ORDER BY
                CASE code
                    WHEN "superadmin" THEN 0
                    WHEN "agency" THEN 1
                    WHEN "operator" THEN 2
                    WHEN "sales" THEN 3
                    WHEN "accounting" THEN 4
                    WHEN "admin" THEN 5
                    ELSE 99
                END,
                name ASC'
        );
        return $stmt->fetchAll();
    }

    private function filterAssignableRoles(array $roles): array
    {
        if (ACL::currentUserHasRole('superadmin')) {
            return $roles;
        }

        $filtered = [];
        foreach ($roles as $role) {
            if ((string) ($role['code'] ?? '') === 'superadmin') {
                continue;
            }
            $filtered[] = $role;
        }

        return $filtered;
    }

    private function roleIdForCode(array $roles, string $roleCode): ?string
    {
        if ($roleCode === '') {
            return null;
        }

        foreach ($roles as $role) {
            if ((string) ($role['code'] ?? '') === $roleCode) {
                return (string) ($role['id'] ?? '');
            }
        }

        return null;
    }

    private function roleCodeForId(array $roles, string $roleId): ?string
    {
        if (!ctype_digit($roleId) || (int) $roleId <= 0) {
            return null;
        }

        foreach ($roles as $role) {
            if ((int) ($role['id'] ?? 0) === (int) $roleId) {
                return (string) ($role['code'] ?? '');
            }
        }

        return null;
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

    private function loadProviders(PDO $db): array
    {
        $stmt = $db->query('SELECT id, name, contact_name, email, phone FROM providers WHERE is_active = 1 ORDER BY name ASC');
        return $stmt->fetchAll();
    }

    private function providerExists(array $providers, int $providerId): bool
    {
        foreach ($providers as $provider) {
            if ((int) ($provider['id'] ?? 0) === $providerId) {
                return true;
            }
        }

        return false;
    }

    private function validateProviderDraft(array $form): array
    {
        $errors = [];
        if (($form['provider_new_name'] ?? '') === '') {
            $errors['provider_new_name'] = 'Nombre de agencia requerido.';
        }
        if (($form['provider_new_contact_name'] ?? '') === '') {
            $errors['provider_new_contact_name'] = 'Contacto principal requerido.';
        }
        if (($form['provider_new_email'] ?? '') !== '' && !filter_var((string) $form['provider_new_email'], FILTER_VALIDATE_EMAIL)) {
            $errors['provider_new_email'] = 'Email de agencia invalido.';
        }

        return $errors;
    }

    private function createProviderFromDraft(PDO $db, array $form): int
    {
        $stmt = $db->prepare(
            'INSERT INTO providers (name, contact_name, email, phone, is_active, created_at)
             VALUES (:name, :contact_name, :email, :phone, 1, NOW())'
        );
        $stmt->execute([
            'name' => (string) $form['provider_new_name'],
            'contact_name' => (string) $form['provider_new_contact_name'],
            'email' => ($form['provider_new_email'] ?? '') !== '' ? (string) $form['provider_new_email'] : null,
            'phone' => ($form['provider_new_phone'] ?? '') !== '' ? (string) $form['provider_new_phone'] : null,
        ]);

        return (int) $db->lastInsertId();
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
