<?php
declare(strict_types=1);

$error = $error ?? null;
?>
<div class="page-header">
    <div>
        <h1>Acceso admin</h1>
        <p class="admin-page-note">Ingresa con tu usuario autorizado para administrar reservas y operacion.</p>
    </div>
</div>

<?php if (is_string($error) && $error !== ''): ?>
    <div class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="card admin-form-card">
    <form method="post" action="/admin/login">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrf_token, ENT_QUOTES, 'UTF-8') ?>">

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Contrasena</label>
            <input type="password" name="password" required>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Ingresar</button>
        </div>
    </form>
</div>
