<?php
$form = $form ?? [];
$errors = $errors ?? [];
$group = $group ?? [];
$currencyRows = $currency_rows ?? [];

$oneWayForm = $form['one_way_price'] ?? [];
$roundTripForm = $form['round_trip_price'] ?? [];
$activeForm = $form['is_active'] ?? [];
?>
<div class="page-header">
    <div>
        <h1>Editar grupo de tarifas</h1>
        <p class="admin-page-note">Actualiza el precio base por moneda para este grupo.</p>
    </div>
</div>

<p class="admin-meta-line">
    Zona: <strong><?= htmlspecialchars((string) ($group['zone_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong> |
    Servicio: <strong><?= htmlspecialchars((string) ($group['service_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong> |
    PAX: <strong><?= htmlspecialchars((string) ($group['pax_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
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
    <form method="post" action="/admin/pricing/rate-rules/edit-group?zone_id=<?= (int) ($group['zone_id'] ?? 0) ?>&service_type_id=<?= (int) ($group['service_type_id'] ?? 0) ?>&pax_range_id=<?= (int) ($group['pax_range_id'] ?? 0) ?>">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrf_token, ENT_QUOTES, 'UTF-8') ?>">

        <p style="margin-bottom:12px; color:#475569; font-size:0.92rem;">
            Puedes activar o desactivar cada moneda. Si una moneda no tenía tarifa, al guardar se crea automáticamente.
        </p>

        <table class="admin-card-table">
            <thead>
                <tr>
                    <th>Moneda</th>
                    <th>Descripción</th>
                    <th>Solo ida</th>
                    <th>Round trip</th>
                    <th>Activa</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($currencyRows as $row): ?>
                <?php $code = (string) ($row['currency_code'] ?? ''); ?>
                <tr>
                    <td data-label="Moneda">
                        <strong><?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?></strong>
                    </td>
                    <td data-label="Descripcion">
                        <?= htmlspecialchars((string) ($row['currency_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                    </td>
                    <td data-label="Solo ida">
                        <input
                            type="number"
                            name="one_way_price[<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>]"
                            step="0.01"
                            min="0"
                            value="<?= htmlspecialchars((string) ($oneWayForm[$code] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                            required
                        >
                    </td>
                    <td data-label="Round trip">
                        <input
                            type="number"
                            name="round_trip_price[<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>]"
                            step="0.01"
                            min="0"
                            value="<?= htmlspecialchars((string) ($roundTripForm[$code] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                            required
                        >
                    </td>
                    <td data-label="Activa">
                        <label>
                            <input
                                type="checkbox"
                                name="is_active[<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>]"
                                value="1"
                                <?= isset($activeForm[$code]) ? 'checked' : '' ?>
                            >
                            Activa
                        </label>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="form-actions" style="margin-top:14px;">
            <button type="submit" class="btn btn-primary">Guardar grupo</button>
            <a href="/admin/pricing/rate-rules" class="btn btn-secondary">Volver</a>
        </div>
    </form>
</div>
