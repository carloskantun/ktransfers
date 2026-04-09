<?php
declare(strict_types=1);

$errors = $errors ?? [];
$form = $form ?? [];
$roles = $roles ?? [];
?>
<div class="page-header">
    <h1>Editar Usuario</h1>
</div>

<div class="card">
    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="/admin/users/edit?id=<?= (int) ($form['id'] ?? 0) ?>">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) ($csrf_token ?? ''), ENT_QUOTES, 'UTF-8') ?>">

        <div class="form-group">
            <label>Nombre Completo</label>
            <input type="text" name="name" value="<?= htmlspecialchars((string) ($form['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars((string) ($form['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="form-group">
            <label>Nueva contraseña</label>
            <input type="password" name="password" placeholder="Déjala vacía para conservar la actual">
        </div>

        <div class="form-group">
            <label>Rol principal</label>
            <select name="role_id" required>
                <option value="">Selecciona un rol</option>
                <?php foreach ($roles as $role): ?>
                    <?php $roleId = (string) ($role['id'] ?? ''); ?>
                    <option value="<?= htmlspecialchars($roleId, ENT_QUOTES, 'UTF-8') ?>" <?= (($form['role_id'] ?? '') === $roleId) ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string) ($role['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="is_active" value="1" <?= (($form['is_active'] ?? '0') === '1') ? 'checked' : '' ?>>
                Usuario Activo
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            <a href="/admin/users" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
