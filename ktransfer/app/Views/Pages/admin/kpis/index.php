<?php
declare(strict_types=1);

$total_bookings = (int) ($total_bookings ?? 0);
$revenue_by_currency = is_array($revenue_by_currency ?? null) ? $revenue_by_currency : [];
$no_shows = (int) ($no_shows ?? 0);
$top_zones = is_array($top_zones ?? null) ? $top_zones : [];
$paid_bookings = (int) ($paid_bookings ?? 0);
$unpaid_bookings = (int) ($unpaid_bookings ?? 0);
?>
<div class="page-header">
    <h1>KPIs y Métricas</h1>
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
</div>

<div class="card" style="margin-top: 20px;">
    <h2>Ingresos por Moneda</h2>
    <table>
        <thead>
            <tr>
                <th>Moneda</th>
                <th>Total Ingresos</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($revenue_by_currency as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['currency_code'] ?? '') ?></td>
                <td><?= htmlspecialchars(number_format((float)($row['total_revenue'] ?? 0), 2)) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="card" style="margin-top: 20px;">
    <h2>Top 5 Zonas</h2>
    <table>
        <thead>
            <tr>
                <th>Zona</th>
                <th>Total Reservas</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($top_zones as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['zone_name'] ?? '') ?></td>
                <td><?= htmlspecialchars((string)($row['total'] ?? 0)) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
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
