<?php
declare(strict_types=1);

$total_bookings = (int) ($total_bookings ?? 0);
$revenue_by_currency = isset($revenue_by_currency) && is_array($revenue_by_currency) ? $revenue_by_currency : [];
$no_shows = (int) ($no_shows ?? 0);
$top_zones = isset($top_zones) && is_array($top_zones) ? $top_zones : [];
$top_vehicles = isset($top_vehicles) && is_array($top_vehicles) ? $top_vehicles : [];
$top_capacities = isset($top_capacities) && is_array($top_capacities) ? $top_capacities : [];
$most_requested_capacity = (int) ($most_requested_capacity ?? 0);
$most_requested_capacity_bookings = (int) ($most_requested_capacity_bookings ?? 0);
$paid_bookings = (int) ($paid_bookings ?? 0);
$unpaid_bookings = (int) ($unpaid_bookings ?? 0);
$agency_collected_bookings = (int) ($agency_collected_bookings ?? 0);
$agency_estimated_gain_by_currency = isset($agency_estimated_gain_by_currency) && is_array($agency_estimated_gain_by_currency) ? $agency_estimated_gain_by_currency : [];
$filters = isset($filters) && is_array($filters) ? $filters : [];
$zones = isset($zones) && is_array($zones) ? $zones : [];
$currencies = isset($currencies) && is_array($currencies) ? $currencies : [];
$dateFrom = (string) ($filters['date_from'] ?? '');
$dateTo = (string) ($filters['date_to'] ?? '');
$selectedCurrency = (string) ($filters['currency_code'] ?? '');
$selectedZoneId = (int) ($filters['zone_id'] ?? 0);
$filterQuery = http_build_query([
    'date_from' => $dateFrom,
    'date_to' => $dateTo,
    'currency_code' => $selectedCurrency,
    'zone_id' => $selectedZoneId,
]);
?>
<div class="page-header">
    <div>
        <h1>KPIs y metricas</h1>
        <p class="admin-page-note">Vista ejecutiva de reservas, pagos y zonas con mas movimiento.</p>
    </div>
    <a class="btn btn-secondary" href="/admin/kpis/export?<?= htmlspecialchars($filterQuery, ENT_QUOTES, 'UTF-8') ?>">Descargar CSV</a>
</div>

<div class="card" style="margin-bottom: 20px;">
    <form method="get" action="/admin/kpis" class="admin-filter-bar">
        <div>
            <label for="date_from">Desde</label>
            <input id="date_from" type="date" name="date_from" value="<?= htmlspecialchars($dateFrom, ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div>
            <label for="date_to">Hasta</label>
            <input id="date_to" type="date" name="date_to" value="<?= htmlspecialchars($dateTo, ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div>
            <label for="currency_code">Moneda</label>
            <select id="currency_code" name="currency_code">
                <option value="">Todas</option>
                <?php foreach ($currencies as $currency): ?>
                    <?php $currencyCode = (string) ($currency['code'] ?? ''); ?>
                    <option value="<?= htmlspecialchars($currencyCode, ENT_QUOTES, 'UTF-8') ?>" <?= $selectedCurrency === $currencyCode ? 'selected' : '' ?>>
                        <?= htmlspecialchars($currencyCode, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
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
        <button type="submit" class="btn btn-primary">Filtrar</button>
        <a href="/admin/kpis" class="admin-row-action">Limpiar</a>
    </form>
</div>

<div class="kpi-grid">
    <div class="kpi-card">
        <h3>Total Reservas</h3>
        <div class="kpi-value"><?= htmlspecialchars((string)$total_bookings) ?></div>
    </div>

    <div class="kpi-card">
        <h3>No-Shows</h3>
        <div class="kpi-value"><?= htmlspecialchars((string)$no_shows) ?></div>
    </div>

    <div class="kpi-card">
        <h3>Reservas Pagadas</h3>
        <div class="kpi-value"><?= htmlspecialchars((string)$paid_bookings) ?></div>
    </div>

    <div class="kpi-card">
        <h3>Reservas Sin Pagar</h3>
        <div class="kpi-value"><?= htmlspecialchars((string)$unpaid_bookings) ?></div>
    </div>

    <div class="kpi-card">
        <h3>Cobradas por agencia</h3>
        <div class="kpi-value"><?= htmlspecialchars((string)$agency_collected_bookings) ?></div>
    </div>

    <div class="kpi-card">
        <h3>Capacidad mas reservada</h3>
        <div class="kpi-value"><?= htmlspecialchars((string)$most_requested_capacity) ?> pax</div>
        <div class="admin-page-note">
            <?= htmlspecialchars((string) $most_requested_capacity_bookings) ?> reservas
        </div>
    </div>
</div>

<div class="admin-report-grid" style="margin-top: 20px;">
    <div class="card">
        <h2 class="admin-section-title">Ingresos por moneda</h2>
        <table class="admin-card-table">
            <thead>
                <tr>
                    <th>Moneda</th>
                    <th>Total ingresos</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($revenue_by_currency as $row): ?>
                <tr>
                    <td data-label="Moneda"><?= htmlspecialchars($row['currency_code'] ?? '') ?></td>
                    <td data-label="Total ingresos"><?= htmlspecialchars(number_format((float)($row['total_revenue'] ?? 0), 2)) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h2 class="admin-section-title">Top 5 zonas</h2>
        <table class="admin-card-table">
            <thead>
                <tr>
                    <th>Zona</th>
                    <th>Total reservas</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($top_zones as $row): ?>
                <tr>
                    <td data-label="Zona"><?= htmlspecialchars($row['zone_name'] ?? '') ?></td>
                    <td data-label="Total reservas"><?= htmlspecialchars((string)($row['total'] ?? 0)) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h2 class="admin-section-title">Ganancia estimada de agencias por moneda</h2>
        <table class="admin-card-table">
            <thead>
                <tr>
                    <th>Moneda</th>
                    <th>Ganancia estimada</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($agency_estimated_gain_by_currency)): ?>
                <tr>
                    <td data-label="Moneda" colspan="2">Sin datos de cobro de agencias para este filtro.</td>
                </tr>
                <?php endif; ?>
                <?php foreach ($agency_estimated_gain_by_currency as $row): ?>
                <tr>
                    <td data-label="Moneda"><?= htmlspecialchars($row['currency_code'] ?? '') ?></td>
                    <td data-label="Ganancia estimada"><?= htmlspecialchars(number_format((float)($row['estimated_gain'] ?? 0), 2)) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h2 class="admin-section-title">Top 5 vehiculos mas pedidos</h2>
        <table class="admin-card-table">
            <thead>
                <tr>
                    <th>Vehiculo</th>
                    <th>Capacidad</th>
                    <th>Total reservas</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($top_vehicles)): ?>
                <tr>
                    <td data-label="Vehiculo" colspan="3">Sin datos de vehiculos para este filtro.</td>
                </tr>
                <?php endif; ?>
                <?php foreach ($top_vehicles as $row): ?>
                <tr>
                    <td data-label="Vehiculo"><?= htmlspecialchars((string) ($row['vehicle_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td data-label="Capacidad"><?= htmlspecialchars((string) ($row['max_pax'] ?? 0), ENT_QUOTES, 'UTF-8') ?> pax</td>
                    <td data-label="Total reservas"><?= htmlspecialchars((string) ($row['total'] ?? 0), ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h2 class="admin-section-title">Top 5 capacidades reservadas</h2>
        <table class="admin-card-table">
            <thead>
                <tr>
                    <th>Capacidad solicitada</th>
                    <th>Total reservas</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($top_capacities)): ?>
                <tr>
                    <td data-label="Capacidad solicitada" colspan="2">Sin datos de capacidad para este filtro.</td>
                </tr>
                <?php endif; ?>
                <?php foreach ($top_capacities as $row): ?>
                <tr>
                    <td data-label="Capacidad solicitada"><?= htmlspecialchars((string) ($row['total_pax'] ?? 0), ENT_QUOTES, 'UTF-8') ?> pax</td>
                    <td data-label="Total reservas"><?= htmlspecialchars((string) ($row['total'] ?? 0), ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}
.kpi-card {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    text-align: center;
}
.kpi-value {
    font-size: 2em;
    font-weight: bold;
    color: #3b82f6;
    margin-top: 10px;
}
</style>
