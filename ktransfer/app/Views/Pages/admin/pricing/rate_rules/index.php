<?php
// Vista: Tarifas
/** @var array $rate_groups */
/** @var array $currencies */
$rateGroups = $rate_groups ?? [];
$currencies = $currencies ?? [];
$allCurrencies = $all_currencies ?? $currencies;
$zones = $zones ?? [];
$services = $services ?? [];
$paxRanges = $pax_ranges ?? [];
$filters = is_array($filters ?? null) ? $filters : [];
$selectedZoneId = (int) ($filters['zone_id'] ?? 0);
$selectedServiceTypeId = (int) ($filters['service_type_id'] ?? 0);
$selectedPaxRangeId = (int) ($filters['pax_range_id'] ?? 0);
$selectedCurrencyCode = (string) ($filters['currency_code'] ?? '');
$selectedStatus = (string) ($filters['status'] ?? '');
?>
<div class="page-header">
    <div>
        <h1>Tarifas</h1>
        <p class="admin-page-note">Precios base por zona, tipo de servicio, rango de pasajeros y moneda.</p>
    </div>
</div>

<div class="card">
    <form method="get" action="/admin/pricing/rate-rules" class="admin-filter-bar">
        <div>
            <label for="zone_id">Zona</label>
            <select id="zone_id" name="zone_id">
                <option value="0">Todas</option>
                <?php foreach ($zones as $zone): ?>
                    <?php $zoneId = (int) ($zone['id'] ?? 0); ?>
                    <option value="<?= $zoneId ?>" <?= $selectedZoneId === $zoneId ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string) ($zone['name_es'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="service_type_id">Servicio</label>
            <select id="service_type_id" name="service_type_id">
                <option value="0">Todos</option>
                <?php foreach ($services as $service): ?>
                    <?php $serviceId = (int) ($service['id'] ?? 0); ?>
                    <option value="<?= $serviceId ?>" <?= $selectedServiceTypeId === $serviceId ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string) ($service['name_es'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="pax_range_id">PAX</label>
            <select id="pax_range_id" name="pax_range_id">
                <option value="0">Todos</option>
                <?php foreach ($paxRanges as $paxRange): ?>
                    <?php $paxRangeId = (int) ($paxRange['id'] ?? 0); ?>
                    <option value="<?= $paxRangeId ?>" <?= $selectedPaxRangeId === $paxRangeId ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string) ($paxRange['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="currency_code">Moneda</label>
            <select id="currency_code" name="currency_code">
                <option value="">Todas</option>
                <?php foreach ($allCurrencies as $currency): ?>
                    <?php $currencyCode = strtoupper((string) ($currency['code'] ?? '')); ?>
                    <option value="<?= htmlspecialchars($currencyCode, ENT_QUOTES, 'UTF-8') ?>" <?= $selectedCurrencyCode === $currencyCode ? 'selected' : '' ?>>
                        <?= htmlspecialchars($currencyCode, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="status">Estado</label>
            <select id="status" name="status">
                <option value="">Todos</option>
                <option value="ACTIVE" <?= $selectedStatus === 'ACTIVE' ? 'selected' : '' ?>>Completas</option>
                <option value="PARTIAL" <?= $selectedStatus === 'PARTIAL' ? 'selected' : '' ?>>Parciales</option>
                <option value="MISSING" <?= $selectedStatus === 'MISSING' ? 'selected' : '' ?>>Sin tarifa</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Filtrar</button>
        <a href="/admin/pricing/rate-rules" class="admin-row-action">Limpiar</a>
    </form>

    <p style="margin-bottom:12px; color:#475569; font-size:0.92rem;">
        Vista agrupada por <strong>Zona + Servicio + Rango de PAX</strong>. Edita todas las monedas activas en una sola pantalla.
    </p>
    <table class="admin-card-table">
        <thead>
            <tr>
                <th>Zona</th>
                <th>Servicio</th>
                <th>Grupo PAX</th>
                <?php foreach ($currencies as $currency): ?>
                <th><?= htmlspecialchars((string) ($currency['code'] ?? ''), ENT_QUOTES, 'UTF-8') ?> (ida / round trip)</th>
                <?php endforeach; ?>
                <th>Activa</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rateGroups)): ?>
            <tr>
                <td class="admin-empty-row" colspan="<?= 5 + count($currencies) ?>" style="text-align:center; color:#64748b;">No hay tarifas disponibles.</td>
            </tr>
            <?php else: ?>
                <?php foreach ($rateGroups as $group): ?>
                <?php
                    $formatPair = static function (?array $rate): string {
                        if ($rate === null) {
                            return '—';
                        }

                        $ow = number_format((float) ($rate['one_way_price'] ?? 0), 2);
                        $rt = number_format((float) ($rate['round_trip_price'] ?? 0), 2);
                        return $ow . ' / ' . $rt;
                    };
                ?>
                <tr>
                    <td data-label="Zona"><?= htmlspecialchars((string) ($group['zone_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td data-label="Servicio"><?= htmlspecialchars((string) ($group['service_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td data-label="Grupo PAX"><?= htmlspecialchars((string) ($group['pax_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <?php foreach ($currencies as $currency): ?>
                    <?php
                        $currencyCode = strtoupper((string) ($currency['code'] ?? ''));
                        $currencyRate = $group['currencies'][$currencyCode] ?? null;
                    ?>
                    <td data-label="<?= htmlspecialchars($currencyCode, ENT_QUOTES, 'UTF-8') ?> ida / redondo"><?= htmlspecialchars($formatPair($currencyRate), ENT_QUOTES, 'UTF-8') ?></td>
                    <?php endforeach; ?>
                    <td data-label="Activa"><?= !empty($group['all_active']) ? 'Sí' : (!empty($group['has_any_rate']) ? 'Parcial' : 'No') ?></td>
                    <td data-label="Acciones">
                        <a class="admin-row-action" href="/admin/pricing/rate-rules/edit-group?zone_id=<?= (int) ($group['zone_id'] ?? 0) ?>&service_type_id=<?= (int) ($group['service_type_id'] ?? 0) ?>&pax_range_id=<?= (int) ($group['pax_range_id'] ?? 0) ?>">
                            Configurar grupo
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
