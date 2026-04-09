<?php
$form = $form ?? [];
$errors = $errors ?? [];
$rate = $rate ?? [];
?>
<div class="page-header">
    <h1>Editar Tarifa</h1>
</div>

<p>
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

<div class="card">
    <form method="post" action="/admin/pricing/rate-rules/edit?id=<?= (int) ($form['id'] ?? 0) ?>">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrf_token, ENT_QUOTES, 'UTF-8') ?>">

        <div class="form-group">
            <label>One Way Price</label>
            <input type="number" name="one_way_price" step="0.01" min="0" value="<?= htmlspecialchars((string) ($form['one_way_price'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="form-group">
            <label>Round Trip Price</label>
            <input type="number" name="round_trip_price" step="0.01" min="0" value="<?= htmlspecialchars((string) ($form['round_trip_price'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="is_active" value="1" <?= (int) ($form['is_active'] ?? 0) === 1 ? 'checked' : '' ?>>
                Regla Activa
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="/admin/pricing/rate-rules" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
