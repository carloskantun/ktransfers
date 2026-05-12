<?php
$form = $form ?? [];
$errors = $errors ?? [];
$rate = $rate ?? [];
?>
<div class="page-header">
    <div>
        <h1>Editar tarifa</h1>
        <p class="admin-page-note">Ajusta el precio base de este servicio.</p>
    </div>
</div>

<p class="admin-meta-line">
    Zona: <strong><?= htmlspecialchars((string) ($rate['zone_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong> |
    Servicio: <strong><?= htmlspecialchars((string) ($rate['service_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong> |
    PAX: <strong><?= htmlspecialchars((string) ($rate['pax_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong> |
    Moneda: <strong><?= htmlspecialchars((string) ($rate['currency_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
</p>

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
    <form method="post" action="/admin/pricing/rate-rules/edit?id=<?= (int) ($form['id'] ?? 0) ?>">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrf_token, ENT_QUOTES, 'UTF-8') ?>">

        <div class="admin-form-grid">
            <div class="form-group">
                <label>Precio solo ida</label>
                <input type="number" name="one_way_price" step="0.01" min="0" value="<?= htmlspecialchars((string) ($form['one_way_price'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="form-group">
                <label>Precio round trip</label>
                <input type="number" name="round_trip_price" step="0.01" min="0" value="<?= htmlspecialchars((string) ($form['round_trip_price'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="form-group admin-form-full">
                <label class="admin-check">
                    <input type="checkbox" name="is_active" value="1" <?= (int) ($form['is_active'] ?? 0) === 1 ? 'checked' : '' ?>>
                    Tarifa activa
                </label>
            </div>
        </div>

        <div class="form-actions" style="margin-top:14px;">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="/admin/pricing/rate-rules" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
