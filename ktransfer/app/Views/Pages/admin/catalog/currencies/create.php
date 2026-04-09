<?php
$form = $form ?? [];
$errors = $errors ?? [];
?>
<div class="page-header">
    <h1>Nueva Moneda</h1>
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
    <form method="post" action="/admin/catalog/currencies/create">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrf_token, ENT_QUOTES, 'UTF-8') ?>">

        <div class="form-group">
            <label>Código (3 letras)</label>
            <input type="text" name="code" maxlength="3" value="<?= htmlspecialchars((string) ($form['code'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="name" value="<?= htmlspecialchars((string) ($form['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="form-group">
            <label>Símbolo (opcional)</label>
            <input type="text" name="symbol" value="<?= htmlspecialchars((string) ($form['symbol'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Crear Moneda</button>
            <a href="/admin/catalog/currencies" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
