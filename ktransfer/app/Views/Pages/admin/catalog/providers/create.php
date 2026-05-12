<?php
declare(strict_types=1);

$errors = $errors ?? [];
$form = $form ?? [];
?>
<div class="page-header">
    <div>
        <h1>Nuevo proveedor</h1>
        <p class="admin-page-note">Alta de proveedor externo para servicios asignados fuera de unidades propias.</p>
    </div>
</div>

<div class="card admin-form-card">
    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="/admin/catalog/providers/create">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) ($csrf_token ?? ''), ENT_QUOTES, 'UTF-8') ?>">

        <div class="form-group">
            <label>Nombre comercial</label>
            <input type="text" name="name" value="<?= htmlspecialchars((string) ($form['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="form-group">
            <label>Nombre de contacto</label>
            <input type="text" name="contact_name" value="<?= htmlspecialchars((string) ($form['contact_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="form-group">
            <label>Email operativo</label>
            <input type="email" name="email" value="<?= htmlspecialchars((string) ($form['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="form-group">
            <label>Telefono / WhatsApp</label>
            <input type="text" name="phone" value="<?= htmlspecialchars((string) ($form['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="form-group">
            <label class="admin-check">
                <input type="checkbox" name="is_active" value="1" <?= (($form['is_active'] ?? '1') === '1') ? 'checked' : '' ?>>
                Proveedor activo
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Crear proveedor</button>
            <a href="/admin/catalog/providers" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
