<?php
$form = $form ?? [];
$errors = $errors ?? [];
$zones = $zones ?? [];
?>
<div class="page-header">
    <h1>Editar Lugar</h1>
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

<div class="card admin-form-card">
    <form method="post" action="/admin/catalog/places/edit?id=<?= (int) ($form['id'] ?? 0) ?>">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrf_token, ENT_QUOTES, 'UTF-8') ?>">

        <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="name" value="<?= htmlspecialchars((string) ($form['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="form-group">
            <label>Zona</label>
            <select name="zone_id" required>
                <option value="">-- Seleccionar Zona --</option>
                <?php foreach ($zones as $zone): ?>
                <option value="<?= (int) ($zone['id'] ?? 0) ?>" <?= (int) ($form['zone_id'] ?? 0) === (int) ($zone['id'] ?? 0) ? 'selected' : '' ?>>
                    <?= htmlspecialchars((string) ($zone['name_es'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Tipo de Lugar</label>
            <select name="type" required>
                <option value="HOTEL" <?= ($form['type'] ?? '') === 'HOTEL' ? 'selected' : '' ?>>Hotel</option>
                <option value="AIRBNB" <?= ($form['type'] ?? '') === 'AIRBNB' ? 'selected' : '' ?>>Airbnb</option>
                <option value="POINT" <?= ($form['type'] ?? '') === 'POINT' ? 'selected' : '' ?>>Punto</option>
            </select>
        </div>

        <div class="form-group">
            <label>Dirección</label>
            <input type="text" name="address" value="<?= htmlspecialchars((string) ($form['address'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Opcional en hoteles; requerida en Airbnb y puntos">
        </div>

        <div class="form-group">
            <label>Ciudad (opcional)</label>
            <input type="text" name="city" value="<?= htmlspecialchars((string) ($form['city'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="form-group">
            <label class="admin-check">
                <input type="checkbox" name="is_active" value="1" <?= (int) ($form['is_active'] ?? 0) === 1 ? 'checked' : '' ?>>
                Lugar Activo
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="/admin/catalog/places" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
