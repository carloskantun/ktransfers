<?php
// Vista: Crear Aerolínea
/** @var array $errors */
/** @var array $form */
?>
<div class="page-header">
    <h1>Nueva Aerolínea</h1>
</div>

<?php if (!empty($errors)): ?>
<div class="error">
    <ul>
        <?php foreach ($errors as $error): ?>
        <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="card">
    <form method="post" action="/admin/catalog/airlines/create">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Csrf::token() ?>">

        <div class="form-group">
            <label>Código IATA (2-3 letras)</label>
            <input type="text" name="code" value="<?= htmlspecialchars($form['code'] ?? '') ?>" maxlength="10" required placeholder="Ej: AA, DL, AM">
            <small>Código de 2 o 3 letras de la aerolínea (IATA)</small>
        </div>

        <div class="form-group">
            <label>Nombre de la Aerolínea</label>
            <input type="text" name="name" value="<?= htmlspecialchars($form['name'] ?? '') ?>" required placeholder="Ej: American Airlines">
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="is_active" value="1" <?= (int)($form['is_active'] ?? 1) === 1 ? 'checked' : '' ?>>
                Aerolínea Activa
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Crear Aerolínea</button>
            <a href="/admin/catalog/airlines" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
