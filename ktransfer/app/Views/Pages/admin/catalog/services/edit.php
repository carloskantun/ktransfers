<?php
$form = $form ?? [];
$errors = $errors ?? [];
?>
<div class="page-header">
    <h1>Editar Tipo de Servicio</h1>
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
    <form method="post" action="/admin/catalog/services/edit?id=<?= (int) ($form['id'] ?? 0) ?>">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrf_token, ENT_QUOTES, 'UTF-8') ?>">

        <div class="form-group">
            <label>Código</label>
            <input type="text" name="code" value="<?= htmlspecialchars((string) ($form['code'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="form-group">
            <label>Nombre (ES)</label>
            <input type="text" name="name_es" value="<?= htmlspecialchars((string) ($form['name_es'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="form-group">
            <label>Nombre (EN)</label>
            <input type="text" name="name_en" value="<?= htmlspecialchars((string) ($form['name_en'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="form-group">
            <label>Orden</label>
            <input type="number" name="sort_order" min="0" step="1" value="<?= htmlspecialchars((string) ($form['sort_order'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="is_active" value="1" <?= (int) ($form['is_active'] ?? 0) === 1 ? 'checked' : '' ?>>
                Servicio Activo
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="/admin/catalog/services" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
