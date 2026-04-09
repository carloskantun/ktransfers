<?php
// Vista: Usuarios
/** @var array $users */
?>
<div class="page-header">
    <h1>Usuarios</h1>
    <a href="/admin/users/create" class="btn btn-primary">Nuevo Usuario</a>
</div>

<div class="card">
    <table>
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
                <td><?= htmlspecialchars((string)($user['id'] ?? '')) ?></td>
                <td><?= htmlspecialchars($user['name'] ?? '') ?></td>
                <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
                <td><?= htmlspecialchars($user['role_names'] ?? '') ?></td>
                <td><?= (int)($user['is_active'] ?? 0) === 1 ? 'Sí' : 'No' ?></td>
                <td><?= htmlspecialchars($user['created_at'] ?? '') ?></td>
                <td><a href="/admin/users/edit?id=<?= (int) ($user['id'] ?? 0) ?>">Editar</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
