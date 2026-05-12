<?php
// Vista: Contabilidad
/** @var array $payments_received */
/** @var array $provider_balances */
$payments_received = $payments_received ?? [];
$provider_balances = $provider_balances ?? [];
$agency_settlements = $agency_settlements ?? [];
$control_summary = isset($control_summary) && is_array($control_summary) ? $control_summary : [];
$filters = isset($filters) && is_array($filters) ? $filters : [];
$providers = isset($providers) && is_array($providers) ? $providers : [];
$currencies = isset($currencies) && is_array($currencies) ? $currencies : [];
$dateFrom = (string) ($filters['date_from'] ?? '');
$dateTo = (string) ($filters['date_to'] ?? '');
$selectedCurrency = (string) ($filters['currency_code'] ?? '');
$selectedProviderId = (int) ($filters['provider_id'] ?? 0);
$totalServices = (int) ($control_summary['total_services'] ?? 0);
$summaryByCurrency = isset($control_summary['by_currency']) && is_array($control_summary['by_currency'])
    ? $control_summary['by_currency']
    : [];
$filterQuery = http_build_query([
    'date_from' => $dateFrom,
    'date_to' => $dateTo,
    'currency_code' => $selectedCurrency,
    'provider_id' => $selectedProviderId,
]);
?>
<div class="page-header">
    <div>
        <h1>Contabilidad</h1>
        <p class="admin-page-note">Resumen rapido de dinero recibido y saldos por proveedor.</p>
    </div>
    <a class="btn btn-secondary" href="/admin/accounting/export?<?= htmlspecialchars($filterQuery, ENT_QUOTES, 'UTF-8') ?>">Descargar CSV</a>
</div>

<div class="card">
    <form method="get" action="/admin/accounting" class="admin-filter-bar">
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
            <label for="provider_id">Proveedor</label>
            <select id="provider_id" name="provider_id">
                <option value="0">Todos</option>
                <?php foreach ($providers as $provider): ?>
                    <?php $providerId = (int) ($provider['id'] ?? 0); ?>
                    <option value="<?= $providerId ?>" <?= $selectedProviderId === $providerId ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string) ($provider['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Filtrar</button>
        <a href="/admin/accounting" class="admin-row-action">Limpiar</a>
    </form>
</div>

<div class="admin-report-grid" style="margin-top: 14px;">
    <div class="card">
        <h2 class="admin-section-title">Servicios registrados</h2>
        <div class="kpi-value"><?= htmlspecialchars((string) $totalServices, ENT_QUOTES, 'UTF-8') ?></div>
        <p class="admin-page-note">Servicios no cancelados dentro del filtro.</p>
    </div>
    <div class="card">
        <h2 class="admin-section-title">Control por moneda</h2>
        <table class="admin-card-table">
            <thead>
                <tr>
                    <th>Moneda</th>
                    <th>Por pagar</th>
                    <th>Por cobrar</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($summaryByCurrency)): ?>
                    <tr>
                        <td class="admin-empty-row" colspan="3">Sin movimientos para el filtro actual.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($summaryByCurrency as $row): ?>
                    <tr>
                        <td data-label="Moneda"><?= htmlspecialchars((string) ($row['currency_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td data-label="Por pagar"><?= htmlspecialchars(number_format((float) ($row['to_pay'] ?? 0), 2), ENT_QUOTES, 'UTF-8') ?></td>
                        <td data-label="Por cobrar"><?= htmlspecialchars(number_format((float) ($row['to_collect'] ?? 0), 2), ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="admin-report-grid">
    <div class="card">
        <h2 class="admin-section-title">Pagos recibidos</h2>
        <table class="admin-card-table">
            <thead>
                <tr>
                    <th>Moneda</th>
                    <th>Total recibido</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payments_received)): ?>
                    <tr>
                        <td class="admin-empty-row" colspan="2">No hay pagos recibidos para los filtros actuales.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($payments_received as $row): ?>
                <tr>
                    <td data-label="Moneda"><?= htmlspecialchars($row['currency_code'] ?? '') ?></td>
                    <td data-label="Total recibido"><?= htmlspecialchars(number_format((float)($row['total_received'] ?? 0), 2)) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h2 class="admin-section-title">Saldos de proveedores</h2>
        <table class="admin-card-table">
            <thead>
                <tr>
                    <th>Proveedor</th>
                    <th>Moneda</th>
                    <th>Por pagar</th>
                    <th>Pagado</th>
                    <th>Saldo</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($provider_balances)): ?>
                    <tr>
                        <td class="admin-empty-row" colspan="5">No hay saldos pendientes para los filtros actuales.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($provider_balances as $row): ?>
                <tr>
                    <td data-label="Proveedor"><?= htmlspecialchars($row['provider_name'] ?? '') ?></td>
                    <td data-label="Moneda"><?= htmlspecialchars($row['currency_code'] ?? '') ?></td>
                    <td data-label="Por pagar"><?= htmlspecialchars(number_format((float)($row['total_payable'] ?? 0), 2)) ?></td>
                    <td data-label="Pagado"><?= htmlspecialchars(number_format((float)($row['total_paid'] ?? 0), 2)) ?></td>
                    <td data-label="Saldo"><?= htmlspecialchars(number_format((float)($row['balance'] ?? 0), 2)) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h2 class="admin-section-title">Resumen comercial de agencias</h2>
        <table class="admin-card-table">
            <thead>
                <tr>
                    <th>Agencia</th>
                    <th>Moneda</th>
                    <th>Reservas</th>
                    <th>Tarifa reporte</th>
                    <th>Cobro cliente</th>
                    <th>Ganancia estimada</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($agency_settlements)): ?>
                    <tr>
                        <td class="admin-empty-row" colspan="6">No hay informacion comercial de agencias para los filtros actuales.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($agency_settlements as $row): ?>
                <tr>
                    <td data-label="Agencia"><?= htmlspecialchars($row['agency_name'] ?? '') ?></td>
                    <td data-label="Moneda"><?= htmlspecialchars($row['currency_code'] ?? '') ?></td>
                    <td data-label="Reservas"><?= htmlspecialchars((string) ($row['total_bookings'] ?? 0)) ?></td>
                    <td data-label="Tarifa reporte"><?= htmlspecialchars(number_format((float)($row['total_report'] ?? 0), 2)) ?></td>
                    <td data-label="Cobro cliente"><?= htmlspecialchars(number_format((float)($row['total_receipt'] ?? 0), 2)) ?></td>
                    <td data-label="Ganancia estimada"><?= htmlspecialchars(number_format((float)($row['estimated_gain'] ?? 0), 2)) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
