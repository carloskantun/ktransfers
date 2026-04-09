<?php
$form = $form ?? [];
$errors = $errors ?? [];
?>
<div class="page-header">
    <h1>Nuevo Rango PAX</h1>
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
    <form method="post" action="/admin/pricing/pax-ranges/create">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrf_token, ENT_QUOTES, 'UTF-8') ?>">

        <div class="form-group">
            <label>Etiqueta</label>
            <input type="text" name="label" value="<?= htmlspecialchars((string) ($form['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Ejemplo: 1-3" required>
        </div>

        <div class="form-group">
            <label>Mínimo pasajeros</label>
            <input type="number" name="min_pax" min="1" step="1" value="<?= htmlspecialchars((string) ($form['min_pax'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="form-group">
            <label>Máximo pasajeros</label>
            <input type="number" name="max_pax" min="1" step="1" value="<?= htmlspecialchars((string) ($form['max_pax'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="form-group">
            <label>Orden</label>
            <input type="number" name="sort_order" step="1" value="<?= htmlspecialchars((string) ($form['sort_order'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Crear Rango</button>
            <a href="/admin/pricing/pax-ranges" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
