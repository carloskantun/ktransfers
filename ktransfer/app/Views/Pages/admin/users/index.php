<?php
// Vista: Usuarios
/** @var array $users */
$users = $users ?? [];
$roles = isset($roles) && is_array($roles) ? $roles : [];
$filters = isset($filters) && is_array($filters) ? $filters : [];
$search = (string) ($filters['q'] ?? '');
$selectedRole = (string) ($filters['role'] ?? '');
$active = (string) ($filters['active'] ?? '');
?>
<div class="page-header">
    <div>
        <h1>Usuarios</h1>
        <p class="admin-page-note">Accesos para administracion, operadores y agencias/agentes externos.</p>
    </div>
    <a href="/admin/users/create" class="btn btn-primary">Nuevo usuario</a>
</div>

<div class="card" style="margin-bottom: 14px;">
    <h2 class="admin-section-title">Altas rapidas</h2>
    <div class="form-actions">
        <a href="/admin/users/create?role=agency" class="btn btn-primary">Crear agencia/agente externo</a>
        <a href="/admin/users/create?role=operator" class="btn btn-secondary">Crear operador/chofer</a>
        <a href="/admin/users/create?role=sales" class="btn btn-secondary">Crear reservaciones</a>
    </div>
    <p class="admin-page-note">Las agencias solo crean y ven sus reservas; los operadores solo deben trabajar su operacion asignada.</p>
</div>

<div class="card">
    <form method="get" action="/admin/users" class="admin-filter-bar">
        <div>
            <label for="q">Buscar</label>
            <input id="q" name="q" type="text" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Nombre o email">
        </div>
        <div>
            <label for="role">Rol</label>
            <select id="role" name="role">
                <option value="">Todos</option>
                <?php foreach ($roles as $role): ?>
                    <?php $roleCode = (string) ($role['code'] ?? ''); ?>
                    <option value="<?= htmlspecialchars($roleCode, ENT_QUOTES, 'UTF-8') ?>" <?= $selectedRole === $roleCode ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string) ($role['name'] ?? $roleCode), ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="active">Estado</label>
            <select id="active" name="active">
                <option value="">Todos</option>
                <option value="1" <?= $active === '1' ? 'selected' : '' ?>>Activos</option>
                <option value="0" <?= $active === '0' ? 'selected' : '' ?>>Inactivos</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Filtrar</button>
        <a href="/admin/users" class="admin-row-action">Limpiar</a>
    </form>

    <table class="admin-card-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Activo</th>
                <th>Creado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td data-label="ID"><?= htmlspecialchars((string)($user['id'] ?? '')) ?></td>
                <td data-label="Nombre">
                    <strong><?= htmlspecialchars((string) ($user['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                </td>
                <td data-label="Email"><?= htmlspecialchars((string) ($user['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td data-label="Rol">
                    <?= htmlspecialchars((string) ($user['role_names'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                    <?php if (str_contains((string) ($user['role_codes'] ?? ''), 'agency')): ?>
                        <br><span class="admin-page-note">Reserva externa</span>
                        <br><span class="admin-page-note">Agencia: <?= htmlspecialchars((string) ($user['provider_name'] ?? 'Sin vincular'), ENT_QUOTES, 'UTF-8') ?></span>
                    <?php elseif (str_contains((string) ($user['role_codes'] ?? ''), 'operator')): ?>
                        <br><span class="admin-page-note">Operador asignable</span>
                    <?php endif; ?>
                </td>
                <td data-label="Activo"><?= (int)($user['is_active'] ?? 0) === 1 ? 'Sí' : 'No' ?></td>
                <td data-label="Creado"><?= htmlspecialchars((string) ($user['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td data-label="Acciones"><a class="admin-row-action" href="/admin/users/edit?id=<?= (int) ($user['id'] ?? 0) ?>">Editar</a></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="7">Aun no hay usuarios registrados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
