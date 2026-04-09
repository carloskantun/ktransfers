<?php
$form = $form ?? [];
$errors = $errors ?? [];
?>
<div class="page-header">
    <h1>Editar Aerolínea</h1>
</div>

<?php if (!empty($errors)): ?>
<div class="error">
    <ul>
        <?php foreach ($errors as $error): ?>
        <li><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="card">
    <form method="post" action="/admin/catalog/airlines/edit?id=<?= (int) ($form['id'] ?? 0) ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) $csrf_token, ENT_QUOTES, 'UTF-8') ?>">

        <div class="form-group">
            <label>Código IATA</label>
            <input type="text" name="code" value="<?= htmlspecialchars((string) ($form['code'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" maxlength="10" required>
        </div>

        <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="name" value="<?= htmlspecialchars((string) ($form['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="is_active" value="1" <?= (int) ($form['is_active'] ?? 0) === 1 ? 'checked' : '' ?>>
                Aerolínea Activa
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="/admin/catalog/airlines" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
