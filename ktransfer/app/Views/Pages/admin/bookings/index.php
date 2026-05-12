<?php
declare(strict_types=1);

$bookings = $bookings ?? [];
$isAgencyScope = (bool) ($is_agency_scope ?? false);
$filters = isset($filters) && is_array($filters) ? $filters : [];
$bookingStatuses = $booking_statuses ?? [];
$paymentStatuses = $payment_statuses ?? [];
$serviceTypes = $service_types ?? [];
$zones = $zones ?? [];
$pagination = isset($pagination) && is_array($pagination) ? $pagination : [];

$search = trim((string) ($filters['q'] ?? ''));
$selectedStatus = (string) ($filters['status'] ?? '');
$selectedPaymentStatus = (string) ($filters['payment_status'] ?? '');
$selectedServiceTypeId = (int) ($filters['service_type_id'] ?? 0);
$selectedZoneId = (int) ($filters['zone_id'] ?? 0);
$dateFrom = (string) ($filters['date_from'] ?? '');
$dateTo = (string) ($filters['date_to'] ?? '');
$currentPage = (int) ($pagination['page'] ?? 1);
$totalPages = (int) ($pagination['total_pages'] ?? 1);
$totalBookings = (int) ($pagination['total'] ?? count($bookings));
$baseQuery = [];
foreach ($filters as $filterKey => $filterValue) {
    if ($filterValue === '' || $filterValue === 0 || $filterValue === null) {
        continue;
    }
    $baseQuery[$filterKey] = $filterValue;
}
$filterQuery = http_build_query($baseQuery);
$activeFilterCount = 0;
foreach ([$search, $selectedStatus, $selectedPaymentStatus, $dateFrom, $dateTo] as $filterValue) {
    if ($filterValue !== '') {
        $activeFilterCount++;
    }
}
foreach ([$selectedServiceTypeId, $selectedZoneId] as $filterValue) {
    if ($filterValue > 0) {
        $activeFilterCount++;
    }
}

$tripLabels = [
    'ONE_WAY' => 'Solo ida',
    'ROUND_TRIP' => 'Round trip',
];

$directionLabels = [
    'AIRPORT_TO_DESTINATION' => 'Aeropuerto a destino',
    'DESTINATION_TO_AIRPORT' => 'Destino a aeropuerto',
];

$bookingStatusLabels = \App\Core\StatusCatalog::bookingMap(true);
$paymentStatusLabels = \App\Core\StatusCatalog::paymentMap(true);
?>
<style>
    .bookings-table td {
        vertical-align: top;
    }
    .booking-meta {
        display: grid;
        gap: 4px;
    }
    .booking-meta strong {
        font-size: 0.96rem;
    }
    .booking-subtle {
        color: var(--muted);
        font-size: 0.88rem;
        line-height: 1.45;
    }
    .status-stack {
        display: grid;
        gap: 8px;
    }
    .status-chip {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 700;
        width: fit-content;
        background: #eef4ff;
        color: #1d4ed8;
    }
    .booking-mobile-list {
        display: none;
    }
    .booking-filter-card {
        margin-bottom: 16px;
    }
    .booking-filter-toggle,
    .booking-filter-label {
        display: none;
    }
    .booking-filter-card .admin-filter-bar {
        margin-bottom: 0;
    }
    .booking-filter-search {
        flex: 1 1 260px;
    }
    .booking-filter-actions {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }
    .booking-result-summary {
        margin: 10px 0 0;
        color: var(--muted);
        font-size: 0.9rem;
    }
    .booking-actions {
        display: grid;
        gap: 8px;
        min-width: 120px;
    }
    .booking-actions .btn {
        width: 100%;
        text-align: center;
    }
    .booking-drawer-toggle {
        position: fixed;
        opacity: 0;
        pointer-events: none;
    }
    .booking-mobile-card,
    .booking-drawer-panel,
    .booking-drawer-backdrop {
        display: none;
    }
    @media (max-width: 760px) {
        .bookings-table {
            display: none;
        }
        .booking-mobile-list {
            display: grid;
            gap: 12px;
        }
        .booking-filter-card {
            padding: 0;
            overflow: hidden;
        }
        .booking-filter-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px;
            color: #1f2937;
            font-weight: 800;
            cursor: pointer;
        }
        .booking-filter-label::after {
            content: '+';
            flex: 0 0 auto;
            width: 28px;
            height: 28px;
            display: grid;
            place-items: center;
            border-radius: 999px;
            background: #e2e8f0;
            color: #334155;
            font-size: 1.1rem;
            line-height: 1;
        }
        .booking-filter-toggle:checked + .booking-filter-label::after {
            content: '-';
        }
        .booking-filter-summary {
            display: block;
            margin-top: 4px;
            color: var(--muted);
            font-size: 0.82rem;
            font-weight: 600;
            line-height: 1.35;
        }
        .booking-filter-card .admin-filter-bar {
            display: none;
            padding: 0 14px 14px;
        }
        .booking-filter-toggle:checked ~ .admin-filter-bar {
            display: grid;
        }
        .booking-result-summary {
            margin: 0;
            padding: 0 14px 14px;
        }
        .booking-filter-actions {
            display: grid;
            grid-template-columns: 1fr;
        }
        .booking-mobile-card {
            display: grid;
            gap: 10px;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 14px;
            background: #fff;
            cursor: pointer;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
        }
        .booking-card-top {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            justify-content: space-between;
        }
        .booking-card-route {
            padding: 10px;
            border-radius: 12px;
            background: #f8fafc;
            color: #334155;
            font-size: 0.9rem;
            line-height: 1.45;
        }
        .booking-card-kpis {
            display: grid;
            gap: 8px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        .booking-card-kpi {
            border: 1px solid #e7edf5;
            border-radius: 11px;
            padding: 8px;
            min-width: 0;
        }
        .booking-card-kpi span {
            display: block;
            color: var(--muted);
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .booking-card-kpi strong {
            display: block;
            margin-top: 3px;
            font-size: 0.88rem;
            overflow-wrap: anywhere;
        }
        .booking-drawer-backdrop {
            position: fixed;
            inset: 0;
            z-index: 50;
            background: rgba(15, 23, 42, 0.44);
        }
        .booking-drawer-panel {
            position: fixed;
            left: 10px;
            right: 10px;
            bottom: 10px;
            z-index: 60;
            max-height: 86vh;
            overflow: auto;
            padding: 16px;
            border: 1px solid var(--border);
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.28);
        }
        .booking-drawer-toggle:checked ~ .booking-drawer-backdrop,
        .booking-drawer-toggle:checked ~ .booking-drawer-panel {
            display: block;
        }
        .booking-drawer-head {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 14px;
        }
        .booking-drawer-close {
            border: 0;
            border-radius: 999px;
            padding: 7px 10px;
            background: #e2e8f0;
            color: #1f2937;
            font-weight: 800;
            cursor: pointer;
        }
        .booking-detail-grid {
            display: grid;
            gap: 10px;
        }
        .booking-detail-row {
            padding: 10px;
            border: 1px solid #e7edf5;
            border-radius: 12px;
            background: #fbfdff;
        }
        .booking-detail-row span {
            display: block;
            margin-bottom: 4px;
            color: var(--muted);
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .booking-detail-row strong,
        .booking-detail-row p {
            margin: 0;
            overflow-wrap: anywhere;
        }
        .booking-drawer-actions {
            display: grid;
            gap: 8px;
            margin-top: 14px;
        }
        .booking-drawer-actions .btn {
            width: 100%;
            text-align: center;
        }
    }
</style>

<div class="page-header">
    <div>
        <h1>Reservas</h1>
        <p style="margin: 6px 0 0; color: var(--muted);">
            <?= $isAgencyScope
                ? 'Tus reservas creadas desde el portal. Administracion confirma precio final, logistica y seguimiento.'
                : 'Listado administrativo con fecha, cliente, ruta, servicio y estado operativo básico.' ?>
        </p>
    </div>
    <a href="/admin/bookings/create" class="btn btn-primary"><?= $isAgencyScope ? 'Nueva reserva' : 'Nueva reserva manual' ?></a>
</div>

<p class="form-actions">
    <a href="/admin" class="btn btn-secondary">Volver al dashboard</a>
    <a href="/admin/bookings/export<?= $filterQuery !== '' ? '?' . htmlspecialchars($filterQuery, ENT_QUOTES, 'UTF-8') : '' ?>" class="btn btn-secondary">Descargar CSV</a>
    <a href="/admin/bookings/print<?= $filterQuery !== '' ? '?' . htmlspecialchars($filterQuery, ENT_QUOTES, 'UTF-8') : '' ?>" class="btn btn-secondary" target="_blank" rel="noreferrer">PDF filtrado / imprimir</a>
</p>

<div class="card booking-filter-card">
    <input class="booking-filter-toggle" type="checkbox" id="booking-filter-toggle">
    <label class="booking-filter-label" for="booking-filter-toggle">
        <span>
            Ajustar filtros
            <span class="booking-filter-summary">
                <?= $activeFilterCount > 0
                    ? htmlspecialchars((string) $activeFilterCount . ' filtros activos', ENT_QUOTES, 'UTF-8')
                    : 'Todas las reservas' ?>
            </span>
        </span>
    </label>
    <form method="get" action="/admin/bookings" class="admin-filter-bar">
        <div class="booking-filter-search">
            <label for="q">Buscar reserva</label>
            <input id="q" name="q" type="text" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Codigo, cliente, email, telefono, vuelo o lugar">
        </div>

        <div>
            <label for="status">Estado reserva</label>
            <select id="status" name="status">
                <option value="">Todos</option>
                <?php foreach ($bookingStatuses as $statusOption): ?>
                    <option value="<?= htmlspecialchars((string) $statusOption, ENT_QUOTES, 'UTF-8') ?>" <?= $selectedStatus === $statusOption ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string) ($bookingStatusLabels[(string) $statusOption] ?? $statusOption), ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="payment_status">Pago</label>
            <select id="payment_status" name="payment_status">
                <option value="">Todos</option>
                <?php foreach ($paymentStatuses as $paymentStatusOption): ?>
                    <option value="<?= htmlspecialchars((string) $paymentStatusOption, ENT_QUOTES, 'UTF-8') ?>" <?= $selectedPaymentStatus === $paymentStatusOption ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string) ($paymentStatusLabels[(string) $paymentStatusOption] ?? $paymentStatusOption), ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="service_type_id">Servicio</label>
            <select id="service_type_id" name="service_type_id">
                <option value="0">Todos</option>
                <?php foreach ($serviceTypes as $serviceType): ?>
                    <?php $serviceTypeId = (int) ($serviceType['id'] ?? 0); ?>
                    <option value="<?= $serviceTypeId ?>" <?= $selectedServiceTypeId === $serviceTypeId ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string) ($serviceType['name_es'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
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

        <div>
            <label for="date_from">Desde</label>
            <input id="date_from" name="date_from" type="date" value="<?= htmlspecialchars($dateFrom, ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div>
            <label for="date_to">Hasta</label>
            <input id="date_to" name="date_to" type="date" value="<?= htmlspecialchars($dateTo, ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="booking-filter-actions">
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="/admin/bookings" class="admin-row-action">Limpiar</a>
        </div>
    </form>
    <p class="booking-result-summary">
        <?= count($bookings) ?> de <?= $totalBookings ?> reservas mostradas<?= $activeFilterCount > 0 ? ' con ' . $activeFilterCount . ' filtros activos' : '' ?>.
    </p>
</div>

<?php if (empty($bookings)): ?>
    <p>No se encontraron reservas con esos filtros.</p>
<?php else: ?>
    <div class="booking-mobile-list" aria-label="Reservas en tarjetas">
        <?php foreach ($bookings as $booking): ?>
            <?php
            $bookingId = (int) ($booking['id'] ?? 0);
            $bookingCode = (string) ($booking['booking_code'] ?? '');
            $customerName = trim((string) ($booking['customer_name'] ?? '') . ' ' . (string) ($booking['customer_last_name'] ?? ''));
            $serviceName = (string) ($booking['service_name'] ?? '');
            $zoneName = (string) ($booking['zone_name'] ?? '');
            $placeName = (string) ($booking['place_name'] ?? '');
            $tripLabel = $tripLabels[(string) ($booking['trip_type'] ?? '')] ?? (string) ($booking['trip_type'] ?? '');
            $directionLabel = $directionLabels[(string) ($booking['direction'] ?? '')] ?? (string) ($booking['direction'] ?? '');
            $arrivalLabel = (($booking['arrival_datetime'] ?? '') !== '') ? (string) date('d/m H:i', strtotime((string) $booking['arrival_datetime'])) : 'Sin llegada';
            $departureLabel = (($booking['departure_datetime'] ?? '') !== '') ? (string) date('d/m H:i', strtotime((string) $booking['departure_datetime'])) : 'Sin salida';
            $flightLabel = trim((string) ($booking['airline'] ?? '') . ' ' . (string) ($booking['flight_number'] ?? ''));
            $drawerId = 'booking-drawer-' . $bookingId;
            ?>
            <article class="booking-mobile-item">
                <input class="booking-drawer-toggle" type="checkbox" id="<?= htmlspecialchars($drawerId, ENT_QUOTES, 'UTF-8') ?>">
                <label class="booking-mobile-card" for="<?= htmlspecialchars($drawerId, ENT_QUOTES, 'UTF-8') ?>">
                    <span class="booking-card-top">
                        <span class="booking-meta">
                            <strong><?= htmlspecialchars($bookingCode, ENT_QUOTES, 'UTF-8') ?></strong>
                            <span class="booking-subtle"><?= htmlspecialchars($customerName !== '' ? $customerName : 'Sin cliente', ENT_QUOTES, 'UTF-8') ?></span>
                        </span>
                        <span class="status-chip"><?= htmlspecialchars(\App\Core\StatusCatalog::bookingLabel((string) ($booking['status'] ?? ''), true), ENT_QUOTES, 'UTF-8') ?></span>
                    </span>
                    <span class="booking-card-route">
                        <?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?><br>
                        <?= htmlspecialchars($zoneName !== '' ? $zoneName : 'Sin zona', ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($placeName !== '' ? $placeName : 'Sin lugar', ENT_QUOTES, 'UTF-8') ?>
                    </span>
                    <span class="booking-card-kpis">
                        <span class="booking-card-kpi">
                            <span>Pax</span>
                            <strong><?= htmlspecialchars((string) ($booking['total_pax'] ?? '0'), ENT_QUOTES, 'UTF-8') ?></strong>
                        </span>
                        <span class="booking-card-kpi">
                            <span>Total</span>
                            <strong><?= htmlspecialchars(number_format((float) ($booking['price_total'] ?? 0), 2) . ' ' . (string) ($booking['currency_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                        </span>
                        <span class="booking-card-kpi">
                            <span>Hora</span>
                            <strong><?= htmlspecialchars($arrivalLabel !== 'Sin llegada' ? $arrivalLabel : $departureLabel, ENT_QUOTES, 'UTF-8') ?></strong>
                        </span>
                    </span>
                </label>
                <label class="booking-drawer-backdrop" for="<?= htmlspecialchars($drawerId, ENT_QUOTES, 'UTF-8') ?>" aria-label="Cerrar detalle"></label>
                <aside class="booking-drawer-panel" role="dialog" aria-modal="true" aria-labelledby="<?= htmlspecialchars($drawerId, ENT_QUOTES, 'UTF-8') ?>-title">
                    <div class="booking-drawer-head">
                        <div>
                            <h2 id="<?= htmlspecialchars($drawerId, ENT_QUOTES, 'UTF-8') ?>-title" style="margin:0; font-size:1.15rem;">
                                <?= htmlspecialchars($bookingCode, ENT_QUOTES, 'UTF-8') ?>
                            </h2>
                            <p class="booking-subtle" style="margin:4px 0 0;"><?= htmlspecialchars($customerName !== '' ? $customerName : 'Sin cliente', ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <label class="booking-drawer-close" for="<?= htmlspecialchars($drawerId, ENT_QUOTES, 'UTF-8') ?>">Cerrar</label>
                    </div>
                    <div class="booking-detail-grid">
                        <div class="booking-detail-row">
                            <span>Servicio</span>
                            <strong><?= htmlspecialchars($serviceName, ENT_QUOTES, 'UTF-8') ?></strong>
                            <p class="booking-subtle"><?= htmlspecialchars($tripLabel . ' - ' . $directionLabel, ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <div class="booking-detail-row">
                            <span>Ruta</span>
                            <strong><?= htmlspecialchars($zoneName !== '' ? $zoneName : 'Sin zona', ENT_QUOTES, 'UTF-8') ?></strong>
                            <p class="booking-subtle"><?= htmlspecialchars($placeName !== '' ? $placeName : 'Sin lugar', ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <div class="booking-detail-row">
                            <span>Contacto</span>
                            <strong><?= htmlspecialchars((string) ($booking['customer_phone'] ?? 'Sin telefono'), ENT_QUOTES, 'UTF-8') ?></strong>
                            <p class="booking-subtle"><?= htmlspecialchars((string) ($booking['customer_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <div class="booking-detail-row">
                            <span>Fechas</span>
                            <strong>Llegada: <?= htmlspecialchars($arrivalLabel, ENT_QUOTES, 'UTF-8') ?></strong>
                            <p class="booking-subtle">Salida: <?= htmlspecialchars($departureLabel, ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <div class="booking-detail-row">
                            <span>Vuelo</span>
                            <strong><?= htmlspecialchars($flightLabel !== '' ? $flightLabel : 'Sin vuelo', ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>
                        <div class="booking-detail-row">
                            <span>Cobro</span>
                            <strong><?= htmlspecialchars(number_format((float) ($booking['price_total'] ?? 0), 2) . ' ' . (string) ($booking['currency_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                            <p class="booking-subtle"><?= htmlspecialchars(\App\Core\StatusCatalog::paymentLabel((string) ($booking['payment_status'] ?? ''), true), ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </div>
                    <?php if (!$isAgencyScope): ?>
                        <div class="booking-drawer-actions">
                            <a href="/admin/bookings/edit?id=<?= $bookingId ?>" class="btn btn-secondary">Ver / editar reserva</a>
                            <a href="/admin/bookings/service-order?id=<?= $bookingId ?>" class="btn btn-secondary" target="_blank" rel="noreferrer">Orden de servicio</a>
                            <a href="/admin/bookings/voucher?id=<?= $bookingId ?>" class="btn btn-secondary" target="_blank" rel="noreferrer">Voucher / ticket</a>
                        </div>
                    <?php else: ?>
                        <div class="booking-drawer-actions">
                            <a href="/admin/bookings/voucher?id=<?= $bookingId ?>" class="btn btn-secondary" target="_blank" rel="noreferrer">Voucher / ticket</a>
                        </div>
                    <?php endif; ?>
                </aside>
            </article>
        <?php endforeach; ?>
    </div>

    <table class="bookings-table">
        <thead>
            <tr>
                <th>Reserva</th>
                <th>Cliente</th>
                <th>Servicio</th>
                <th>Fechas</th>
                <th>Total</th>
                <th>Estados</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bookings as $booking): ?>
                <tr>
                    <td data-label="Reserva">
                        <div class="booking-meta">
                            <strong><?= htmlspecialchars((string) ($booking['booking_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                            <span class="booking-subtle"><?= htmlspecialchars($tripLabels[(string) ($booking['trip_type'] ?? '')] ?? (string) ($booking['trip_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="booking-subtle"><?= htmlspecialchars($directionLabels[(string) ($booking['direction'] ?? '')] ?? (string) ($booking['direction'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                    </td>
                    <td data-label="Cliente">
                        <div class="booking-meta">
                            <strong>
                                <?= htmlspecialchars(trim((string) ($booking['customer_name'] ?? '') . ' ' . (string) ($booking['customer_last_name'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                            </strong>
                            <span class="booking-subtle"><?= htmlspecialchars((string) ($booking['customer_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="booking-subtle"><?= htmlspecialchars((string) ($booking['customer_phone'] ?? 'Sin telefono'), ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                    </td>
                    <td data-label="Servicio">
                        <div class="booking-meta">
                            <strong><?= htmlspecialchars((string) ($booking['service_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                            <span class="booking-subtle"><?= htmlspecialchars((string) ($booking['zone_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="booking-subtle"><?= htmlspecialchars((string) ($booking['place_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="booking-subtle"><?= htmlspecialchars((string) (($booking['total_pax'] ?? '0') . ' pax'), ENT_QUOTES, 'UTF-8') ?></span>
                            <?php if (($booking['flight_number'] ?? '') !== '' || ($booking['airline'] ?? '') !== ''): ?>
                                <span class="booking-subtle">
                                    <?= htmlspecialchars(trim((string) ($booking['airline'] ?? '') . ' ' . (string) ($booking['flight_number'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td data-label="Fechas">
                        <div class="booking-meta">
                            <?php if (($booking['arrival_datetime'] ?? '') !== ''): ?>
                                <span class="booking-subtle">Llegada: <?= htmlspecialchars((string) date('d/m/Y H:i', strtotime((string) $booking['arrival_datetime'])), ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endif; ?>
                            <?php if (($booking['departure_datetime'] ?? '') !== ''): ?>
                                <span class="booking-subtle">Salida: <?= htmlspecialchars((string) date('d/m/Y H:i', strtotime((string) $booking['departure_datetime'])), ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endif; ?>
                            <span class="booking-subtle">Creada: <?= htmlspecialchars((string) date('d/m/Y H:i', strtotime((string) ($booking['created_at'] ?? 'now'))), ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                    </td>
                    <td data-label="Total">
                        <strong><?= htmlspecialchars(number_format((float) ($booking['price_total'] ?? 0), 2) . ' ' . (string) ($booking['currency_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                    </td>
                    <td data-label="Estados">
                        <div class="status-stack">
                            <span class="status-chip"><?= htmlspecialchars(\App\Core\StatusCatalog::bookingLabel((string) ($booking['status'] ?? ''), true), ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="status-chip" style="background: #ecfdf5; color: #047857;"><?= htmlspecialchars(\App\Core\StatusCatalog::paymentLabel((string) ($booking['payment_status'] ?? ''), true), ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                    </td>
                    <td data-label="Acciones">
                        <?php if ($isAgencyScope): ?>
                            <a href="/admin/bookings/voucher?id=<?= (int) ($booking['id'] ?? 0) ?>" class="btn btn-secondary" target="_blank" rel="noreferrer">Voucher / ticket</a>
                        <?php else: ?>
                            <div class="booking-actions">
                                <a href="/admin/bookings/edit?id=<?= (int) ($booking['id'] ?? 0) ?>" class="btn btn-secondary">Ver / editar</a>
                                <a href="/admin/bookings/service-order?id=<?= (int) ($booking['id'] ?? 0) ?>" class="btn btn-secondary" target="_blank" rel="noreferrer">Orden</a>
                                <a href="/admin/bookings/voucher?id=<?= (int) ($booking['id'] ?? 0) ?>" class="btn btn-secondary" target="_blank" rel="noreferrer">Voucher</a>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if ($totalPages > 1): ?>
        <div class="admin-pagination">
            <?php if ($currentPage > 1): ?>
                <?php $prevQuery = http_build_query(array_merge($baseQuery, ['page' => $currentPage - 1])); ?>
                <a href="/admin/bookings?<?= htmlspecialchars($prevQuery, ENT_QUOTES, 'UTF-8') ?>">Anterior</a>
            <?php endif; ?>
            <span>Pagina <?= $currentPage ?> de <?= $totalPages ?> (<?= $totalBookings ?> reservas)</span>
            <?php if ($currentPage < $totalPages): ?>
                <?php $nextQuery = http_build_query(array_merge($baseQuery, ['page' => $currentPage + 1])); ?>
                <a href="/admin/bookings?<?= htmlspecialchars($nextQuery, ENT_QUOTES, 'UTF-8') ?>">Siguiente</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
