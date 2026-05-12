<?php
declare(strict_types=1);

$booking = $booking ?? [];
$form = $form ?? [];
$errors = $errors ?? [];
$serviceTypes = $service_types ?? [];
$operators = $operators ?? [];
$providers = $providers ?? [];
$vehicles = $vehicles ?? [];
$currencies = $currencies ?? [];
$vehicleRecommendation = $vehicle_recommendation ?? null;
$isAgencyScope = (bool) ($is_agency_scope ?? false);
$bookingStatuses = $booking_statuses ?? [];
$paymentStatuses = $payment_statuses ?? [];
$bookingEditLogs = isset($booking_edit_logs) && is_array($booking_edit_logs) ? $booking_edit_logs : [];
$bookingDeleteRequests = isset($booking_delete_requests) && is_array($booking_delete_requests) ? $booking_delete_requests : [];
$canDeleteApprove = (bool) ($can_delete_approve ?? false);
$isAdminOrSuperAdmin = \App\Core\ACL::currentUserHasRole('admin') || \App\Core\ACL::currentUserHasRole('superadmin');
$canManageBookings = \App\Core\ACL::currentUserCan('bookings.manage');

$totalPax = max(0, (int) ($form['adults'] ?? 0) + (int) ($form['children'] ?? 0));
$selectedVehicleId = (int) ($form['vehicle_id'] ?? 0);
$selectedVehicle = $selected_vehicle ?? null;

$serviceTypesForJs = $service_types_for_js ?? [];
$vehiclesForJs = $vehicles_for_js ?? [];

$bookingStatusLabels = \App\Core\StatusCatalog::bookingMap(true);
$paymentStatusLabels = \App\Core\StatusCatalog::paymentMap(true);
$serviceStatusLabels = \App\Core\StatusCatalog::serviceMap(true);

$editFieldLabels = [
    'trip_type' => 'Tipo de viaje',
    'operation_type' => 'Tipo de operación',
    'direction' => 'Control operativo',
    'service_type_id' => 'Servicio comercial',
    'place_id' => 'Hotel / destino',
    'currency_code' => 'Moneda',
    'price_total' => 'Total',
    'status' => 'Estado de reserva',
    'payment_status' => 'Estado de pago',
    'arrival_datetime' => 'Llegada',
    'departure_datetime' => 'Salida',
    'airline' => 'Aerolínea',
    'flight_number' => 'Vuelo',
    'agency_name' => 'Agencia',
    'customer_name' => 'Nombre',
    'customer_last_name' => 'Apellido',
    'customer_email' => 'Email',
    'customer_phone' => 'Teléfono',
    'terminal' => 'Terminal',
    'origin_name' => 'Origen',
    'destination_name' => 'Destino',
    'pickup_notes' => 'Notas de pickup',
    'comments' => 'Comentarios',
    'adults' => 'Adults',
    'children' => 'Children',
    'work_order_notes' => 'Nota operativa',
];

$agendaDate = '';
if (($booking['arrival_datetime'] ?? '') !== '') {
    $agendaDate = date('Y-m-d', strtotime((string) $booking['arrival_datetime']));
} elseif (($booking['departure_datetime'] ?? '') !== '') {
    $agendaDate = date('Y-m-d', strtotime((string) $booking['departure_datetime']));
}

$bookingId = (int) ($booking['id'] ?? 0);
?>
<style>
    .manual-booking-shell {
        display: grid;
        grid-template-columns: minmax(0, 1.8fr) minmax(320px, 0.95fr);
        gap: 20px;
        align-items: start;
        min-width: 0;
    }
    .manual-booking-form {
        display: grid;
        gap: 18px;
        min-width: 0;
    }
    .manual-section {
        border: 1px solid var(--border);
        border-radius: 16px;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        padding: 18px;
        min-width: 0;
    }
    .manual-section-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 16px;
    }
    .manual-section-head h2 {
        margin: 0;
        font-size: 1.08rem;
    }
    .manual-section-head p {
        margin: 6px 0 0;
        color: var(--muted);
        line-height: 1.5;
    }
    .manual-form-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 14px;
    }
    .manual-span-12 { grid-column: span 12; }
    .manual-span-8 { grid-column: span 8; }
    .manual-span-6 { grid-column: span 6; }
    .manual-span-4 { grid-column: span 4; }
    .manual-span-3 { grid-column: span 3; }
    .manual-help {
        display: block;
        margin-top: 6px;
        color: var(--muted);
        font-size: 0.84rem;
        line-height: 1.45;
    }
    .manual-muted {
        color: var(--muted);
    }
    .manual-admin-only {
        display: <?= $isAgencyScope ? 'none' : 'block' ?>;
    }
    .manual-inline-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .manual-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 11px;
        border-radius: 999px;
        background: #eef4ff;
        color: #24417a;
        font-size: 0.82rem;
        font-weight: 700;
    }
    .manual-sidebar {
        display: grid;
        gap: 18px;
        position: sticky;
        top: 18px;
        min-width: 0;
    }
    .manual-summary-card,
    .manual-recommendation-card {
        border: 1px solid var(--border);
        border-radius: 16px;
        background: #fff;
        padding: 18px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
    }
    .manual-summary-card h2,
    .manual-recommendation-card h2 {
        margin: 0 0 12px;
        font-size: 1.05rem;
    }
    .manual-summary-list {
        display: grid;
        gap: 10px;
    }
    .manual-summary-row {
        display: grid;
        gap: 4px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e8eef6;
    }
    .manual-summary-row:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }
    .manual-summary-label {
        color: var(--muted);
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    .manual-summary-value {
        font-weight: 700;
        color: var(--text);
        overflow-wrap: anywhere;
    }
    .manual-recommendation-highlight {
        padding: 14px;
        border-radius: 14px;
        background: linear-gradient(135deg, #eef5ff 0%, #f8fbff 100%);
        border: 1px solid #d5e4fb;
        margin-bottom: 12px;
    }
    .manual-recommendation-highlight strong {
        display: block;
        font-size: 1rem;
    }
    .manual-recommendation-highlight span {
        display: block;
        margin-top: 6px;
        color: #36588d;
    }
    .manual-recommendation-list {
        margin: 0;
        padding-left: 18px;
        color: var(--muted);
        line-height: 1.5;
    }
    .manual-recommendation-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 12px;
    }
    .manual-recommendation-actions .btn {
        flex: 1;
        text-align: center;
    }
    .manual-rate-card {
        border: 1px solid #d5e4fb;
        border-radius: 12px;
        background: #f7fbff;
        padding: 12px;
        display: grid;
        gap: 8px;
    }
    .manual-rate-card strong {
        display: block;
    }
    .manual-rate-card span {
        color: var(--muted);
        line-height: 1.45;
    }
    .manual-rate-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .manual-actions-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
        margin-top: 2px;
    }
    .manual-actions-bar .manual-muted {
        max-width: 560px;
        line-height: 1.45;
    }
    .places-list {
        display: none;
        margin: 8px 0 0;
        padding: 0;
        list-style: none;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: #fff;
        max-height: 220px;
        overflow-y: auto;
        box-shadow: 0 12px 20px rgba(15, 23, 42, 0.08);
    }
    .places-list li + li {
        border-top: 1px solid #edf2f8;
    }
    .places-list-button {
        width: 100%;
        border: 0;
        background: transparent;
        padding: 12px 14px;
        text-align: left;
        cursor: pointer;
    }
    .places-list-button strong {
        display: block;
        color: var(--text);
    }
    .places-list-button span {
        display: block;
        margin-top: 4px;
        color: var(--muted);
        font-size: 0.84rem;
    }
    .places-list-empty {
        padding: 12px 14px;
        color: var(--muted);
    }
    .edit-logs-grid {
        display: grid;
        gap: 16px;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        margin-top: 18px;
    }
    .edit-logs-card {
        padding: 18px;
        border: 1px solid var(--border);
        border-radius: 16px;
        background: linear-gradient(180deg, #fff, #f8fbff);
    }
    .edit-logs-card h2 {
        margin: 0 0 12px;
        font-size: 1.05rem;
    }
    @media (max-width: 1180px) {
        .manual-booking-shell {
            grid-template-columns: 1fr;
        }
        .manual-sidebar {
            position: static;
        }
    }
    @media (max-width: 900px) {
        .edit-logs-grid {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 860px) {
        .manual-booking-shell {
            gap: 14px;
        }
        .manual-booking-form {
            gap: 14px;
        }
        .manual-sidebar {
            order: -1;
            gap: 12px;
        }
        .manual-section,
        .manual-summary-card,
        .manual-recommendation-card {
            border-radius: 10px;
            padding: 14px;
        }
        .manual-section-head {
            flex-direction: column;
            gap: 10px;
        }
        .manual-form-grid {
            grid-template-columns: 1fr;
            gap: 10px;
        }
        .manual-span-12,
        .manual-span-8,
        .manual-span-6,
        .manual-span-4,
        .manual-span-3 {
            grid-column: span 1;
        }
        .manual-actions-bar {
            flex-direction: column;
            align-items: flex-start;
        }
        .manual-inline-pills {
            gap: 6px;
        }
        .manual-chip {
            padding: 6px 9px;
            font-size: 0.78rem;
        }
    }
</style>

<div class="page-header">
    <div>
        <h1>Reserva <?= htmlspecialchars((string) ($booking['booking_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
        <p style="margin: 6px 0 0; color: var(--muted);">
            <?= $isAgencyScope
                ? 'Actualiza los datos de tu reserva. El precio y la operacion los confirma administracion.'
                : 'Edita la reserva. Los cambios quedan registrados en la bitacora de ediciones.' ?>
        </p>
    </div>
    <div class="form-actions">
        <?php if ($agendaDate !== ''): ?>
            <a href="/admin/operations/agenda?date=<?= htmlspecialchars($agendaDate, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-secondary">Ver en agenda</a>
        <?php endif; ?>
        <?php if ($canManageBookings): ?>
            <a href="/admin/bookings/service-order?id=<?= $bookingId ?>" class="btn btn-secondary" target="_blank" rel="noreferrer">Orden de servicio</a>
        <?php endif; ?>
        <a href="/admin/bookings/voucher?id=<?= $bookingId ?>" class="btn btn-secondary" target="_blank" rel="noreferrer">Voucher / ticket</a>
        <a href="/admin/bookings" class="btn btn-secondary">Volver</a>
    </div>
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

<div class="manual-booking-shell">
    <form method="post" action="/admin/bookings/update" class="manual-booking-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) \App\Core\Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="id" value="<?= $bookingId ?>">

        <!-- ── Sección 1: Cliente y canal ── -->
        <section class="manual-section">
            <div class="manual-section-head">
                <div>
                    <h2>Cliente y canal</h2>
                    <p>Datos del pasajero y canal comercial. Los cambios se guardan en la bitacora de ediciones.</p>
                </div>
                <div class="manual-inline-pills">
                    <span class="manual-chip"><?= htmlspecialchars((string) ($booking['booking_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </div>
            <div class="manual-form-grid">
                <div class="form-group manual-span-4">
                    <label for="customer_name">Nombre</label>
                    <input id="customer_name" name="customer_name" required value="<?= htmlspecialchars((string) ($form['customer_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group manual-span-4">
                    <label for="customer_last_name">Apellido</label>
                    <input id="customer_last_name" name="customer_last_name" value="<?= htmlspecialchars((string) ($form['customer_last_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group manual-span-4">
                    <label for="agency_name">Agencia</label>
                    <input
                        id="agency_name"
                        name="agency_name"
                        <?= $isAgencyScope ? '' : 'list="agency_name_suggestions"' ?>
                        value="<?= htmlspecialchars((string) ($form['agency_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                        placeholder="<?= $isAgencyScope ? 'Agencia vinculada a tu usuario' : 'Escribe o elige una agencia' ?>"
                        autocomplete="off"
                        <?= $isAgencyScope ? 'readonly' : '' ?>
                    >
                    <input type="hidden" name="agency_provider_id" value="<?= htmlspecialchars((string) ($form['agency_provider_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    <?php if (!$isAgencyScope): ?>
                        <datalist id="agency_name_suggestions">
                            <?php foreach ($providers as $provider): ?>
                                <?php $providerName = trim((string) ($provider['name'] ?? '')); ?>
                                <?php if ($providerName === '') { continue; } ?>
                                <option value="<?= htmlspecialchars($providerName, ENT_QUOTES, 'UTF-8') ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                    <?php endif; ?>
                    <span class="manual-help"><?= $isAgencyScope ? 'Tu agencia esta vinculada al usuario y no puede editarse desde este formulario.' : 'Escribe la agencia o concierge. El campo sugiere nombres del catalogo de proveedores.' ?></span>
                </div>
                <div class="form-group manual-span-6">
                    <label for="customer_email">Email</label>
                    <input id="customer_email" type="email" name="customer_email" required value="<?= htmlspecialchars((string) ($form['customer_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group manual-span-6">
                    <label for="customer_phone">Telefono</label>
                    <input id="customer_phone" name="customer_phone" value="<?= htmlspecialchars((string) ($form['customer_phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </div>
        </section>

        <!-- ── Sección 2: Servicio y ruta ── -->
        <section class="manual-section">
            <div class="manual-section-head">
                <div>
                    <h2>Servicio y ruta</h2>
                    <p>Tipo de operacion, hotel y servicio comercial. Si cambias el hotel la zona se actualiza automaticamente.</p>
                </div>
            </div>
            <div class="manual-form-grid">
                <div class="form-group manual-span-3">
                    <label for="trip_type">Tipo de viaje</label>
                    <select id="trip_type" name="trip_type" required>
                        <option value="ONE_WAY" <?= ($form['trip_type'] ?? '') === 'ONE_WAY' ? 'selected' : '' ?>>Solo ida</option>
                        <option value="ROUND_TRIP" <?= ($form['trip_type'] ?? '') === 'ROUND_TRIP' ? 'selected' : '' ?>>Round trip</option>
                    </select>
                </div>
                <div class="form-group manual-span-3">
                    <label for="operation_type">Tipo de operacion</label>
                    <select id="operation_type" name="operation_type" required>
                        <option value="AIRPORT" <?= ($form['operation_type'] ?? 'AIRPORT') === 'AIRPORT' ? 'selected' : '' ?>>Aeropuerto</option>
                        <option value="INTERHOTEL" <?= ($form['operation_type'] ?? '') === 'INTERHOTEL' ? 'selected' : '' ?>>Inter Hotel</option>
                    </select>
                </div>
                <div class="form-group manual-span-3" id="direction_group">
                    <label for="direction">Control operativo</label>
                    <select id="direction" name="direction" required>
                        <option value="AIRPORT_TO_DESTINATION" <?= ($form['direction'] ?? '') === 'AIRPORT_TO_DESTINATION' ? 'selected' : '' ?>>Llegada</option>
                        <option value="DESTINATION_TO_AIRPORT" <?= ($form['direction'] ?? '') === 'DESTINATION_TO_AIRPORT' ? 'selected' : '' ?>>Salida</option>
                    </select>
                </div>
                <div class="form-group manual-span-3">
                    <label for="service_type_id">Servicio comercial</label>
                    <select id="service_type_id" name="service_type_id" required>
                        <?php foreach ($serviceTypes as $serviceType): ?>
                            <?php $serviceTypeId = (int) ($serviceType['id'] ?? 0); ?>
                            <option
                                value="<?= $serviceTypeId ?>"
                                data-service-code="<?= htmlspecialchars((string) ($serviceType['code'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                <?= (int) ($form['service_type_id'] ?? 0) === $serviceTypeId ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars((string) ($serviceType['name_es'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group manual-span-6" id="origin_query_group" style="display:none;">
                    <label for="origin_query">Origen</label>
                    <input
                        id="origin_query"
                        type="search"
                        name="origin_query"
                        value="<?= htmlspecialchars((string) ($form['origin_query'] ?? $form['origin_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                        placeholder="Escribe el hotel o punto de origen"
                        autocomplete="off"
                    >
                    <input type="hidden" id="origin_name" name="origin_name" value="<?= htmlspecialchars((string) ($form['origin_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    <ul id="admin_origin_suggestions" class="places-list"></ul>
                    <span class="manual-help">Aplica sobre todo a inter hotel o traslados entre puntos.</span>
                </div>

                <div class="form-group manual-span-6">
                    <label for="admin_place_query" id="destination_query_label">Hotel / destino</label>
                    <input
                        id="admin_place_query"
                        type="search"
                        name="place_query"
                        value="<?= htmlspecialchars((string) ($form['place_query'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                        placeholder="Escribe el nombre del hotel"
                        autocomplete="off"
                        required
                    >
                    <input type="hidden" id="place_id" name="place_id" value="<?= htmlspecialchars((string) ($form['place_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" id="zone_id" name="zone_id" value="<?= htmlspecialchars((string) ($form['zone_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" id="destination_name" name="destination_name" value="<?= htmlspecialchars((string) ($form['destination_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    <ul id="admin_places_suggestions" class="places-list"></ul>
                    <span class="manual-help" id="destination_query_help">Busca el hotel y selecciona una opcion del listado.</span>
                </div>

                <div class="form-group manual-span-6">
                    <label for="zone_name">Zona</label>
                    <input
                        id="zone_name"
                        name="zone_name"
                        value="<?= htmlspecialchars((string) ($form['zone_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                        readonly
                        placeholder="Se completa al elegir hotel"
                    >
                </div>
            </div>
        </section>

        <!-- ── Sección 3: Horarios y datos de vuelo ── -->
        <section class="manual-section">
            <div class="manual-section-head">
                <div>
                    <h2>Horarios y datos de vuelo</h2>
                    <p>Fechas y vuelo para la hoja operativa diaria. Solo se muestran los campos que aplican al tipo de operacion.</p>
                </div>
            </div>
            <div class="manual-form-grid">
                <div class="form-group manual-span-6" id="arrival_group">
                    <label for="arrival_datetime" id="arrival_label">Llegada</label>
                    <input id="arrival_datetime" type="datetime-local" name="arrival_datetime" value="<?= htmlspecialchars((string) ($form['arrival_datetime'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group manual-span-6" id="departure_group">
                    <label for="departure_datetime" id="departure_label">Salida</label>
                    <input id="departure_datetime" type="datetime-local" name="departure_datetime" value="<?= htmlspecialchars((string) ($form['departure_datetime'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group manual-span-4 airport-only">
                    <label for="airline">Aerolinea</label>
                    <input
                        id="airline"
                        type="search"
                        name="airline"
                        value="<?= htmlspecialchars((string) ($form['airline'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                        placeholder="Escribe nombre o codigo"
                        autocomplete="off"
                    >
                    <ul id="admin_airlines_suggestions" class="places-list"></ul>
                    <span class="manual-help">Busca y selecciona una aerolinea del catalogo.</span>
                </div>
                <div class="form-group manual-span-4 airport-only">
                    <label for="flight_number">Vuelo</label>
                    <input id="flight_number" name="flight_number" value="<?= htmlspecialchars((string) ($form['flight_number'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group manual-span-4 airport-only">
                    <label for="terminal">Terminal</label>
                    <input id="terminal" name="terminal" value="<?= htmlspecialchars((string) ($form['terminal'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group manual-span-12">
                    <label for="pickup_notes">Notas de pickup</label>
                    <input id="pickup_notes" name="pickup_notes" value="<?= htmlspecialchars((string) ($form['pickup_notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </div>
        </section>

        <!-- ── Sección 4: Pasajeros y cobro ── -->
        <section class="manual-section">
            <div class="manual-section-head">
                <div>
                    <h2>Pasajeros y cobro</h2>
                    <p><?= $isAgencyScope
                        ? 'Ajusta los pasajeros. El precio de tarifa lo gestiona administracion.'
                        : 'Ajusta pasajeros, tarifa y estatus comercial. El sistema puede sugerir la tarifa base activa.' ?></p>
                </div>
            </div>
            <div class="manual-form-grid">
                <div class="form-group manual-span-3">
                    <label for="adults">Adults</label>
                    <input id="adults" type="number" min="1" name="adults" required value="<?= htmlspecialchars((string) ($form['adults'] ?? '1'), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group manual-span-3">
                    <label for="children">Children</label>
                    <input id="children" type="number" min="0" name="children" required value="<?= htmlspecialchars((string) ($form['children'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group manual-span-3">
                    <label for="currency_code">Moneda</label>
                    <select id="currency_code" name="currency_code" required <?= $isAgencyScope ? 'disabled' : '' ?>>
                        <?php if (!empty($currencies)): ?>
                            <?php foreach ($currencies as $currency): ?>
                                <?php $currencyCode = strtoupper((string) ($currency['code'] ?? '')); ?>
                                <option value="<?= $currencyCode ?>" <?= strtoupper((string) ($form['currency_code'] ?? 'USD')) === $currencyCode ? 'selected' : '' ?>>
                                    <?= $currencyCode ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="USD" selected>USD</option>
                        <?php endif; ?>
                    </select>
                    <?php if ($isAgencyScope): ?>
                        <input type="hidden" name="currency_code" value="<?= htmlspecialchars(strtoupper((string) ($form['currency_code'] ?? 'USD')), ENT_QUOTES, 'UTF-8') ?>">
                    <?php endif; ?>
                </div>
                <?php if ($isAgencyScope): ?>
                    <input type="hidden" name="price_total" value="<?= htmlspecialchars((string) ($form['price_total'] ?? '0.00'), ENT_QUOTES, 'UTF-8') ?>">
                    <div class="form-group manual-span-3">
                        <label>Tarifa de reporte</label>
                        <strong class="manual-summary-value"><?= htmlspecialchars((string) ($form['price_total'] ?? '0.00') . ' ' . strtoupper((string) ($form['currency_code'] ?? 'USD')), ENT_QUOTES, 'UTF-8') ?></strong>
                        <span class="manual-help">Gestionada por administracion. No editable.</span>
                    </div>
                <?php else: ?>
                    <div class="form-group manual-span-3">
                        <label for="price_total">Total</label>
                        <input id="price_total" type="number" min="0" step="0.01" name="price_total" required value="<?= htmlspecialchars((string) ($form['price_total'] ?? '0.00'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                <?php endif; ?>
                <?php if (!$isAgencyScope): ?>
                    <div class="manual-span-12">
                        <div class="manual-rate-card" id="rate_suggestion_card">
                            <strong id="rate_suggestion_title">Tarifa base pendiente</strong>
                            <span id="rate_suggestion_text">Selecciona hotel, pasajeros, moneda y servicio para consultar la tarifa base del sistema.</span>
                            <div class="manual-rate-actions">
                                <button type="button" class="btn btn-secondary" id="apply_rate_suggestion" disabled>Usar tarifa base</button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group manual-span-6 manual-admin-only">
                        <label for="status">Estado reserva</label>
                        <select id="status" name="status" required>
                            <?php foreach ($bookingStatuses as $bStatus): ?>
                                <option value="<?= htmlspecialchars((string) $bStatus, ENT_QUOTES, 'UTF-8') ?>" <?= ($form['status'] ?? 'PENDING') === $bStatus ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string) ($bookingStatusLabels[$bStatus] ?? $bStatus), ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group manual-span-6 manual-admin-only">
                        <label for="payment_status">Estado pago</label>
                        <select id="payment_status" name="payment_status" required>
                            <?php foreach ($paymentStatuses as $pStatus): ?>
                                <option value="<?= htmlspecialchars((string) $pStatus, ENT_QUOTES, 'UTF-8') ?>" <?= ($form['payment_status'] ?? 'UNPAID') === $pStatus ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string) ($paymentStatusLabels[$pStatus] ?? $pStatus), ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php else: ?>
                    <input type="hidden" name="status" value="<?= htmlspecialchars((string) ($form['status'] ?? 'PENDING'), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="payment_status" value="<?= htmlspecialchars((string) ($form['payment_status'] ?? 'UNPAID'), ENT_QUOTES, 'UTF-8') ?>">
                    <?php /* rate card placeholder so JS doesn't break */ ?>
                    <span id="rate_suggestion_card" style="display:none;"></span>
                    <span id="rate_suggestion_title" style="display:none;"></span>
                    <span id="rate_suggestion_text" style="display:none;"></span>
                    <button type="button" id="apply_rate_suggestion" style="display:none;" disabled></button>
                <?php endif; ?>
            </div>
        </section>

        <!-- ── Sección 5: Logística (solo admin) ── -->
        <section class="manual-section manual-admin-only">
            <div class="manual-section-head">
                <div>
                    <h2>Logistica operativa</h2>
                    <p>Asignacion de unidad, operador y proveedor. Solo visible para administracion. Los cambios se aplican al instante al guardar.</p>
                </div>
                <div class="manual-inline-pills">
                    <span class="manual-chip">Unidad = vehiculo</span>
                    <span class="manual-chip">Despacho</span>
                </div>
            </div>
            <div class="manual-form-grid">
                <div class="form-group manual-span-4">
                    <label for="mode">Modo de asignacion</label>
                    <select id="mode" name="mode">
                        <option value="INTERNAL" <?= ($form['mode'] ?? 'INTERNAL') === 'INTERNAL' ? 'selected' : '' ?>>Interno</option>
                        <option value="PROVIDER" <?= ($form['mode'] ?? '') === 'PROVIDER' ? 'selected' : '' ?>>Proveedor</option>
                    </select>
                </div>
                <div class="form-group manual-span-4" id="operator_group">
                    <label for="operator_user_id">Operador</label>
                    <select id="operator_user_id" name="operator_user_id">
                        <option value="">Sin asignar</option>
                        <?php foreach ($operators as $operator): ?>
                            <?php $opId = (int) ($operator['id'] ?? 0); ?>
                            <option value="<?= $opId ?>" <?= (int) ($form['operator_user_id'] ?? 0) === $opId ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) ($operator['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group manual-span-4" id="provider_group">
                    <label for="provider_id">Proveedor</label>
                    <select id="provider_id" name="provider_id">
                        <option value="">Sin proveedor</option>
                        <?php foreach ($providers as $provider): ?>
                            <?php $provId = (int) ($provider['id'] ?? 0); ?>
                            <option value="<?= $provId ?>" <?= (int) ($form['provider_id'] ?? 0) === $provId ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) ($provider['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group manual-span-4">
                    <label for="vehicle_id">Unidad</label>
                    <select id="vehicle_id" name="vehicle_id">
                        <option value="">Sin unidad asignada</option>
                        <?php foreach ($vehicles as $vehicle): ?>
                            <?php $vehId = (int) ($vehicle['id'] ?? 0); ?>
                            <option
                                value="<?= $vehId ?>"
                                data-max-pax="<?= (int) ($vehicle['max_pax'] ?? 0) ?>"
                                <?= $selectedVehicleId === $vehId ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars((string) ($vehicle['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="manual-help">La unidad sugerida se calcula por capacidad; puedes cambiarla.</span>
                </div>
                <div class="form-group manual-span-4">
                    <label for="service_status">Estado operativo</label>
                    <select id="service_status" name="service_status">
                        <?php foreach (['PENDING', 'ASSIGNED', 'IN_PROGRESS', 'DONE', 'NO_SHOW'] as $sStatus): ?>
                            <option value="<?= $sStatus ?>" <?= ($form['service_status'] ?? 'PENDING') === $sStatus ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) ($serviceStatusLabels[$sStatus] ?? $sStatus), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group manual-span-4">
                    <label for="work_date">Fecha operativa</label>
                    <input id="work_date" type="date" name="work_date" value="<?= htmlspecialchars((string) ($form['work_date'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    <span class="manual-help">Si la dejas vacia, se toma de la llegada o salida.</span>
                </div>
            </div>
        </section>

        <!-- ── Sección 6: Notas y control interno (solo admin) ── -->
        <section class="manual-section manual-admin-only">
            <div class="manual-section-head">
                <div>
                    <h2>Notas y control interno</h2>
                    <p>Campos utiles para el equipo administrativo y la hoja operativa.</p>
                </div>
            </div>
            <div class="manual-form-grid">
                <div class="form-group manual-span-6">
                    <label for="work_order_notes">Nota operativa</label>
                    <textarea id="work_order_notes" name="work_order_notes" rows="5"><?= htmlspecialchars((string) ($form['work_order_notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <div class="form-group manual-span-6">
                    <label for="comments">Comentarios internos</label>
                    <textarea id="comments" name="comments" rows="5"><?= htmlspecialchars((string) ($form['comments'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
            </div>
        </section>

        <div class="manual-actions-bar">
            <p class="manual-muted">
                <?= $isAgencyScope
                    ? 'Los cambios se guardan de inmediato. Administracion puede ajustar tarifa, estado y logistica.'
                    : 'Los cambios se auditan en la bitacora de ediciones. La logistica se aplica al instante al guardar.' ?>
            </p>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                <a href="/admin/bookings/edit?id=<?= $bookingId ?>" class="btn btn-secondary">Cancelar</a>
            </div>
        </div>
    </form>

    <!-- ── Sidebar ── -->
    <aside class="manual-sidebar">
        <div class="manual-summary-card">
            <h2>Resumen rapido</h2>
            <div class="manual-summary-list">
                <div class="manual-summary-row">
                    <span class="manual-summary-label">Control operativo</span>
                    <strong class="manual-summary-value" id="summary_operation">
                        <?php
                        $opType = (string) ($form['operation_type'] ?? 'AIRPORT');
                        $tripTypeVal = (string) ($form['trip_type'] ?? 'ONE_WAY');
                        $dirVal = (string) ($form['direction'] ?? 'AIRPORT_TO_DESTINATION');
                        if ($opType === 'INTERHOTEL') {
                            echo htmlspecialchars($tripTypeVal === 'ROUND_TRIP' ? 'Inter Hotel RT' : 'Inter Hotel', ENT_QUOTES, 'UTF-8');
                        } elseif ($tripTypeVal === 'ROUND_TRIP') {
                            echo 'Round trip';
                        } else {
                            echo htmlspecialchars($dirVal === 'DESTINATION_TO_AIRPORT' ? 'Salida' : 'Llegada', ENT_QUOTES, 'UTF-8');
                        }
                        ?>
                    </strong>
                </div>
                <div class="manual-summary-row">
                    <span class="manual-summary-label">Cliente</span>
                    <strong class="manual-summary-value" id="summary_customer">
                        <?php
                        $fullName = trim((string) ($form['customer_name'] ?? '') . ' ' . (string) ($form['customer_last_name'] ?? ''));
                        echo htmlspecialchars($fullName !== '' ? $fullName : 'Sin nombre aun', ENT_QUOTES, 'UTF-8');
                        ?>
                    </strong>
                </div>
                <div class="manual-summary-row">
                    <span class="manual-summary-label">Ruta</span>
                    <strong class="manual-summary-value" id="summary_route">
                        <?php
                        $origin = trim((string) ($form['origin_name'] ?? ''));
                        $dest = trim((string) ($form['destination_name'] ?? ''));
                        if ($origin !== '' || $dest !== '') {
                            echo htmlspecialchars(($origin !== '' ? $origin : 'Aeropuerto') . ' -> ' . ($dest !== '' ? $dest : 'Destino'), ENT_QUOTES, 'UTF-8');
                        } else {
                            echo htmlspecialchars((string) ($form['place_query'] ?? 'Pendiente de seleccionar hotel'), ENT_QUOTES, 'UTF-8');
                        }
                        ?>
                    </strong>
                </div>
                <div class="manual-summary-row">
                    <span class="manual-summary-label">Horario</span>
                    <strong class="manual-summary-value" id="summary_schedule">
                        <?php
                        $arr = (string) ($form['arrival_datetime'] ?? '');
                        $dep = (string) ($form['departure_datetime'] ?? '');
                        if ($arr !== '') {
                            echo htmlspecialchars('Llegada: ' . $arr, ENT_QUOTES, 'UTF-8');
                        } elseif ($dep !== '') {
                            echo htmlspecialchars('Salida: ' . $dep, ENT_QUOTES, 'UTF-8');
                        } else {
                            echo 'Pendiente';
                        }
                        ?>
                    </strong>
                </div>
                <div class="manual-summary-row">
                    <span class="manual-summary-label">Vuelo</span>
                    <strong class="manual-summary-value" id="summary_flight">
                        <?= htmlspecialchars((string) ($form['flight_number'] ?? '') !== '' ? (string) $form['flight_number'] : 'Pendiente / no aplica', ENT_QUOTES, 'UTF-8') ?>
                    </strong>
                </div>
                <div class="manual-summary-row">
                    <span class="manual-summary-label">Checklist rapido</span>
                    <strong class="manual-summary-value" id="summary_ready_status">Verificando datos...</strong>
                </div>
                <div class="manual-summary-row">
                    <span class="manual-summary-label">Pax</span>
                    <strong class="manual-summary-value" id="summary_pax"><?= (int) $totalPax ?> pax</strong>
                </div>
                <div class="manual-summary-row">
                    <span class="manual-summary-label">Unidad elegida</span>
                    <strong class="manual-summary-value" id="summary_vehicle">
                        <?= htmlspecialchars((string) ($selectedVehicle['name'] ?? 'Sin unidad asignada'), ENT_QUOTES, 'UTF-8') ?>
                    </strong>
                </div>
                <div class="manual-summary-row">
                    <span class="manual-summary-label">Tarifa actual</span>
                    <strong class="manual-summary-value">
                        <?= htmlspecialchars(number_format((float) ($form['price_total'] ?? 0), 2) . ' ' . strtoupper((string) ($form['currency_code'] ?? 'USD')), ENT_QUOTES, 'UTF-8') ?>
                    </strong>
                </div>
            </div>
        </div>

        <?php if (!$isAgencyScope): ?>
        <div class="manual-recommendation-card">
            <h2>Unidad sugerida</h2>
            <div class="manual-recommendation-highlight">
                <strong id="vehicle_recommendation_label">
                    <?= htmlspecialchars((string) ($vehicleRecommendation['label'] ?? 'Sin recomendacion disponible'), ENT_QUOTES, 'UTF-8') ?>
                </strong>
                <span id="vehicle_recommendation_meta">
                    <?php if (is_array($vehicleRecommendation)): ?>
                        Capacidad sugerida: <?= (int) ($vehicleRecommendation['max_pax'] ?? 0) ?> pax
                    <?php else: ?>
                        Captura pasajeros y servicio para generar una sugerencia.
                    <?php endif; ?>
                </span>
            </div>
            <ul class="manual-recommendation-list" id="vehicle_recommendation_notes">
                <?php if (is_array($vehicleRecommendation) && !empty($vehicleRecommendation['notes']) && is_array($vehicleRecommendation['notes'])): ?>
                    <?php foreach ($vehicleRecommendation['notes'] as $recommendationNote): ?>
                        <li><?= htmlspecialchars((string) $recommendationNote, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>La sugerencia se construye con la capacidad activa de las unidades registradas.</li>
                <?php endif; ?>
            </ul>
            <div class="manual-recommendation-actions">
                <button type="button" class="btn btn-secondary" id="apply_vehicle_recommendation">Usar sugerencia</button>
            </div>
        </div>
        <?php else: ?>
            <?php /* placeholders para que el JS no rompa */ ?>
            <span id="vehicle_recommendation_label" style="display:none;"></span>
            <span id="vehicle_recommendation_meta" style="display:none;"></span>
            <ul id="vehicle_recommendation_notes" style="display:none;"></ul>
            <button id="apply_vehicle_recommendation" style="display:none;" type="button"></button>
        <?php endif; ?>
    </aside>
</div>

<!-- ── Zona de peligro (solo admin/superadmin) ── -->
<?php if ($isAdminOrSuperAdmin): ?>
<div class="card" style="margin-top: 14px; border-left: 4px solid var(--danger);">
    <p style="margin-bottom: 10px;"><strong style="color: var(--danger);">Zona de peligro — Borrar reserva</strong></p>
    <p class="admin-page-note" style="margin-bottom: 12px;">Esta acción es irreversible. La reserva se eliminará de forma permanente.</p>
    <form method="post" action="/admin/bookings/delete" onsubmit="return confirm('¿Confirmas el borrado permanente de esta reserva? Esta acción no se puede deshacer.')">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) \App\Core\Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="id" value="<?= $bookingId ?>">
        <button type="submit" class="btn" style="background: var(--danger); color: #fff; border: none;">Borrar reserva definitivamente</button>
    </form>
</div>
<?php endif; ?>

<!-- ── Solicitudes de borrado y bitácora ── -->
<div class="edit-logs-grid">
    <section class="edit-logs-card">
        <h2>Solicitud de borrado</h2>
        <p class="admin-page-note">El borrado definitivo solo lo puede aprobar un admin o superadmin.</p>

        <form method="post" action="/admin/bookings/delete-request">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) \App\Core\Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="id" value="<?= htmlspecialchars((string) ($booking['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <div class="form-group">
                <label>Motivo de borrado</label>
                <textarea name="delete_reason" rows="3" placeholder="Motivo para solicitar el borrado de esta reserva"></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-secondary">Solicitar borrado</button>
            </div>
        </form>

        <?php if (!empty($bookingDeleteRequests)): ?>
            <hr style="margin: 14px 0; border: 0; border-top: 1px solid var(--border);">
            <h3 style="margin-bottom: 8px; font-size: 0.95rem;">Historial de solicitudes</h3>
            <?php foreach ($bookingDeleteRequests as $deleteRequest): ?>
                <?php
                $deleteStatus = (string) ($deleteRequest['status'] ?? 'PENDING');
                $canReviewThisRequest = $canDeleteApprove && $deleteStatus === 'PENDING';
                ?>
                <div style="border:1px solid var(--border); border-radius: 10px; padding: 10px; margin-bottom: 10px; background:#fff;">
                    <p style="margin:0 0 6px;"><strong>Estado:</strong> <?= htmlspecialchars($deleteStatus, ENT_QUOTES, 'UTF-8') ?></p>
                    <p style="margin:0 0 6px;"><strong>Solicitó:</strong> <?= htmlspecialchars((string) ($deleteRequest['requested_by_name'] ?? 'Usuario desconocido'), ENT_QUOTES, 'UTF-8') ?></p>
                    <p style="margin:0 0 6px;"><strong>Motivo:</strong> <?= htmlspecialchars((string) ($deleteRequest['reason'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                    <p style="margin:0 0 6px;"><strong>Fecha:</strong> <?= htmlspecialchars((string) ($deleteRequest['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                    <?php if ((string) ($deleteRequest['reviewed_by_name'] ?? '') !== ''): ?>
                        <p style="margin:0 0 6px;"><strong>Revisó:</strong> <?= htmlspecialchars((string) ($deleteRequest['reviewed_by_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                    <?php if ((string) ($deleteRequest['review_note'] ?? '') !== ''): ?>
                        <p style="margin:0 0 6px;"><strong>Nota de revisión:</strong> <?= htmlspecialchars((string) ($deleteRequest['review_note'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                    <?php if ($canReviewThisRequest): ?>
                        <form method="post" action="/admin/bookings/delete-review" style="margin-top: 8px;">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) \App\Core\Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="request_id" value="<?= (int) ($deleteRequest['id'] ?? 0) ?>">
                            <input type="hidden" name="booking_id" value="<?= $bookingId ?>">
                            <div class="form-group">
                                <label>Nota de revisión (opcional)</label>
                                <input type="text" name="review_note" value="">
                            </div>
                            <div class="form-actions">
                                <button type="submit" name="review_action" value="APPROVE" class="btn btn-primary">Aprobar y borrar</button>
                                <button type="submit" name="review_action" value="REJECT" class="btn btn-secondary">Rechazar</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <section class="edit-logs-card">
        <h2>Bitácora de ediciones</h2>
        <p class="admin-page-note">Se guarda la última edición y al menos dos historiales previos por control.</p>

        <?php if (empty($bookingEditLogs)): ?>
            <p class="admin-page-note">Aún no hay ediciones registradas para esta reserva.</p>
        <?php else: ?>
            <?php foreach ($bookingEditLogs as $index => $editLog): ?>
                <?php
                $changedFields = is_array($editLog['changed_fields'] ?? null) ? $editLog['changed_fields'] : [];
                $oldSnapshot = is_array($editLog['old_snapshot'] ?? null) ? $editLog['old_snapshot'] : [];
                $newSnapshot = is_array($editLog['new_snapshot'] ?? null) ? $editLog['new_snapshot'] : [];
                ?>
                <div style="border:1px solid var(--border); border-radius: 10px; padding: 10px; margin-bottom: 10px; background:#fff;">
                    <p style="margin:0 0 6px;">
                        <strong><?= $index === 0 ? 'Última edición' : 'Edición anterior' ?></strong>
                        · <?= htmlspecialchars((string) ($editLog['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        · por <?= htmlspecialchars((string) ($editLog['changed_by_name'] ?? 'Usuario desconocido'), ENT_QUOTES, 'UTF-8') ?>
                        (rol <?= htmlspecialchars((string) ($editLog['actor_role_code'] ?? 'unknown'), ENT_QUOTES, 'UTF-8') ?>)
                    </p>
                    <?php if (empty($changedFields)): ?>
                        <p class="admin-page-note">Sin campos detectados.</p>
                    <?php else: ?>
                        <ul style="margin: 0 0 0 16px;">
                            <?php foreach ($changedFields as $field): ?>
                                <?php
                                $fieldName = (string) $field;
                                $label = $editFieldLabels[$fieldName] ?? $fieldName;
                                $oldValue = (string) ($oldSnapshot[$fieldName] ?? '');
                                $newValue = (string) ($newSnapshot[$fieldName] ?? '');
                                ?>
                                <li>
                                    <strong><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>:</strong>
                                    <span style="color: var(--muted);"><?= htmlspecialchars($oldValue === '' ? '(vacío)' : $oldValue, ENT_QUOTES, 'UTF-8') ?></span>
                                    →
                                    <span><?= htmlspecialchars($newValue === '' ? '(vacío)' : $newValue, ENT_QUOTES, 'UTF-8') ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</div>

<script>
    (function () {
        var serviceTypes = <?= json_encode($serviceTypesForJs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        var vehicles = <?= json_encode($vehiclesForJs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        var operationType = document.getElementById('operation_type');
        var tripType = document.getElementById('trip_type');
        var directionGroup = document.getElementById('direction_group');
        var directionSelect = document.getElementById('direction');
        var placeQuery = document.getElementById('admin_place_query');
        var placeIdInput = document.getElementById('place_id');
        var zoneIdInput = document.getElementById('zone_id');
        var zoneNameInput = document.getElementById('zone_name');
        var suggestions = document.getElementById('admin_places_suggestions');
        var destinationNameInput = document.getElementById('destination_name');
        var destinationQueryLabel = document.getElementById('destination_query_label');
        var destinationQueryHelp = document.getElementById('destination_query_help');
        var originGroup = document.getElementById('origin_query_group');
        var originQuery = document.getElementById('origin_query');
        var originNameInput = document.getElementById('origin_name');
        var originSuggestions = document.getElementById('admin_origin_suggestions');
        var airlineInput = document.getElementById('airline');
        var airlineSuggestions = document.getElementById('admin_airlines_suggestions');
        var arrivalGroup = document.getElementById('arrival_group');
        var arrivalLabel = document.getElementById('arrival_label');
        var departureGroup = document.getElementById('departure_group');
        var departureLabel = document.getElementById('departure_label');
        var airportOnlyFields = Array.prototype.slice.call(document.querySelectorAll('.airport-only'));
        var modeSelect = document.getElementById('mode');
        var operatorGroup = document.getElementById('operator_group');
        var providerGroup = document.getElementById('provider_group');
        var operatorSelect = document.getElementById('operator_user_id');
        var providerSelect = document.getElementById('provider_id');
        var vehicleSelect = document.getElementById('vehicle_id');
        var adultsInput = document.getElementById('adults');
        var childrenInput = document.getElementById('children');
        var serviceTypeSelect = document.getElementById('service_type_id');
        var currencySelect = document.getElementById('currency_code');
        var priceInput = document.getElementById('price_total');
        var rateSuggestionTitle = document.getElementById('rate_suggestion_title');
        var rateSuggestionText = document.getElementById('rate_suggestion_text');
        var applyRateSuggestionButton = document.getElementById('apply_rate_suggestion');
        var applyRecommendationButton = document.getElementById('apply_vehicle_recommendation');
        var recommendationLabel = document.getElementById('vehicle_recommendation_label');
        var recommendationMeta = document.getElementById('vehicle_recommendation_meta');
        var recommendationNotes = document.getElementById('vehicle_recommendation_notes');
        var summaryOperation = document.getElementById('summary_operation');
        var summaryCustomer = document.getElementById('summary_customer');
        var summaryRoute = document.getElementById('summary_route');
        var summarySchedule = document.getElementById('summary_schedule');
        var summaryFlight = document.getElementById('summary_flight');
        var summaryReadyStatus = document.getElementById('summary_ready_status');
        var summaryPax = document.getElementById('summary_pax');
        var summaryVehicle = document.getElementById('summary_vehicle');
        var customerNameInput = document.getElementById('customer_name');
        var customerLastNameInput = document.getElementById('customer_last_name');
        var arrivalInput = document.getElementById('arrival_datetime');
        var departureInput = document.getElementById('departure_datetime');
        var flightNumberInput = document.getElementById('flight_number');
        var bookingForm = document.querySelector('.manual-booking-form');

        if (!operationType || !tripType || !directionGroup || !directionSelect || !placeQuery || !placeIdInput || !zoneIdInput || !zoneNameInput || !suggestions || !destinationNameInput || !originGroup || !originQuery || !originNameInput || !originSuggestions || !arrivalGroup || !arrivalLabel || !departureGroup || !departureLabel || !adultsInput || !childrenInput || !serviceTypeSelect || !summaryOperation || !summaryCustomer || !summaryRoute || !summaryPax) {
            return;
        }

        var rateSuggestion = null;
        var rateTimer = null;
        var rateRequestId = 0;

        function closeList(listNode) {
            listNode.innerHTML = '';
            listNode.style.display = 'none';
        }

        function renderMessage(listNode, message) {
            listNode.innerHTML = '';
            var li = document.createElement('li');
            li.className = 'places-list-empty';
            li.textContent = message;
            listNode.appendChild(li);
            listNode.style.display = 'block';
        }

        function setupPlacesAutocomplete(config) {
            var queryInput = config.queryInput;
            var listNode = config.listNode;
            var onSelect = config.onSelect;
            var debounceTimer;

            async function fetchPlaces() {
                var q = queryInput.value.trim();
                if (q.length < 1) {
                    closeList(listNode);
                    return;
                }
                try {
                    var response = await fetch('/api/places?q=' + encodeURIComponent(q));
                    var data = await response.json();
                    var items = Array.isArray(data.items) ? data.items : [];
                    listNode.innerHTML = '';
                    if (items.length === 0) {
                        renderMessage(listNode, 'No encontramos lugares con ese nombre.');
                        return;
                    }
                    items.forEach(function (item) {
                        var li = document.createElement('li');
                        var button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'places-list-button';
                        button.innerHTML = '<strong>' + item.name + '</strong><span>' + item.zone_name + '</span>';
                        button.addEventListener('click', function (event) {
                            event.preventDefault();
                            onSelect(item);
                            closeList(listNode);
                            syncSummary();
                        });
                        li.appendChild(button);
                        listNode.appendChild(li);
                    });
                    listNode.style.display = 'block';
                } catch (error) {
                    renderMessage(listNode, 'No se pudo consultar el catalogo de lugares.');
                }
            }

            queryInput.addEventListener('input', function () {
                if (typeof config.onInputReset === 'function') {
                    config.onInputReset();
                }
                syncSummary();
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(fetchPlaces, 250);
            });

            queryInput.addEventListener('focus', function () {
                if (queryInput.value.trim().length >= 1) {
                    fetchPlaces();
                }
            });

            document.addEventListener('click', function (event) {
                if (!queryInput.contains(event.target) && !listNode.contains(event.target)) {
                    closeList(listNode);
                }
            });
        }

        function setupAirlinesAutocomplete() {
            if (!airlineInput || !airlineSuggestions) { return; }
            var debounceTimer;

            async function fetchAirlines() {
                var q = airlineInput.value.trim();
                if (q.length < 1) {
                    closeList(airlineSuggestions);
                    return;
                }
                try {
                    var response = await fetch('/api/airlines?q=' + encodeURIComponent(q));
                    var data = await response.json();
                    var items = Array.isArray(data.items) ? data.items : [];
                    airlineSuggestions.innerHTML = '';
                    if (items.length === 0) {
                        renderMessage(airlineSuggestions, 'No encontramos aerolineas con ese criterio.');
                        return;
                    }
                    items.forEach(function (item) {
                        var li = document.createElement('li');
                        var button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'places-list-button';
                        button.innerHTML = '<strong>' + item.code + '</strong><span>' + item.name + '</span>';
                        button.addEventListener('click', function (event) {
                            event.preventDefault();
                            airlineInput.value = item.name;
                            closeList(airlineSuggestions);
                        });
                        li.appendChild(button);
                        airlineSuggestions.appendChild(li);
                    });
                    airlineSuggestions.style.display = 'block';
                } catch (error) {
                    renderMessage(airlineSuggestions, 'No se pudo consultar el catalogo de aerolineas.');
                }
            }

            airlineInput.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(fetchAirlines, 250);
            });

            airlineInput.addEventListener('focus', function () {
                if (airlineInput.value.trim().length >= 1) {
                    fetchAirlines();
                }
            });

            document.addEventListener('click', function (event) {
                if (!airlineInput.contains(event.target) && !airlineSuggestions.contains(event.target)) {
                    closeList(airlineSuggestions);
                }
            });
        }

        function getSelectedServiceType() {
            var selectedId = parseInt(serviceTypeSelect.value || '0', 10);
            return serviceTypes.find(function (item) { return item.id === selectedId; }) || null;
        }

        function getRecommendedVehicle() {
            var totalPax = Math.max(0, parseInt(adultsInput.value || '0', 10) + parseInt(childrenInput.value || '0', 10));
            var selectedService = getSelectedServiceType();
            var recommendedVehicle = null;

            if (totalPax > 0) {
                recommendedVehicle = vehicles.find(function (vehicle) {
                    return vehicle.max_pax >= totalPax;
                }) || null;
            }

            if (!recommendedVehicle && vehicles.length > 0) {
                recommendedVehicle = vehicles[vehicles.length - 1];
            }

            var notes = [];
            if (totalPax > 0) {
                notes.push('Recomendacion basada en capacidad para ' + totalPax + ' pax.');
            } else {
                notes.push('Captura pasajeros para generar una sugerencia confiable.');
            }

            if (selectedService && (selectedService.code === 'VIP' || selectedService.code === 'LUXURY')) {
                notes.push('Validar que la unidad cumpla el nivel premium del servicio ' + selectedService.name_es + '.');
            }

            if (recommendedVehicle && totalPax > recommendedVehicle.max_pax) {
                notes.push('La capacidad activa registrada no cubre todos los pasajeros. Se requiere revision manual.');
            }

            return { totalPax: totalPax, serviceType: selectedService, vehicle: recommendedVehicle, notes: notes };
        }

        function renderVehicleRecommendation() {
            if (!recommendationLabel || !recommendationMeta || !recommendationNotes) { return; }
            var recommendation = getRecommendedVehicle();

            if (!recommendation.vehicle) {
                recommendationLabel.textContent = 'Sin recomendacion disponible';
                recommendationMeta.textContent = 'No hay unidades activas para sugerir.';
                recommendationNotes.innerHTML = '<li>Revisa el catalogo de unidades activas.</li>';
                return;
            }

            recommendationLabel.textContent = recommendation.vehicle.name;
            recommendationMeta.textContent = 'Capacidad sugerida: ' + recommendation.vehicle.max_pax + ' pax';
            recommendationNotes.innerHTML = '';
            recommendation.notes.forEach(function (note) {
                var li = document.createElement('li');
                li.textContent = note;
                recommendationNotes.appendChild(li);
            });
        }

        function setRateSuggestionPending(message) {
            rateSuggestion = null;
            if (!rateSuggestionTitle || !rateSuggestionText || !applyRateSuggestionButton) { return; }
            rateSuggestionTitle.textContent = 'Tarifa base pendiente';
            rateSuggestionText.textContent = message;
            applyRateSuggestionButton.disabled = true;
        }

        function fetchRateSuggestion() {
            if (!priceInput || !rateSuggestionTitle || !rateSuggestionText || !applyRateSuggestionButton || !currencySelect) { return; }
            clearTimeout(rateTimer);
            rateTimer = setTimeout(async function () {
                var placeId = parseInt(placeIdInput.value || '0', 10);
                var adults = parseInt(adultsInput.value || '0', 10);
                var children = parseInt(childrenInput.value || '0', 10);
                var serviceTypeId = parseInt(serviceTypeSelect.value || '0', 10);
                var currencyCode = currencySelect.value || 'USD';
                var trip = tripType.value || 'ONE_WAY';
                var requestId = ++rateRequestId;

                if (!placeId || adults < 1 || children < 0 || !serviceTypeId) {
                    setRateSuggestionPending('Selecciona hotel, pasajeros y servicio para consultar la tarifa base del sistema.');
                    return;
                }

                rateSuggestionTitle.textContent = 'Consultando tarifa base...';
                rateSuggestionText.textContent = 'Buscando tarifa activa por zona, pax, moneda y servicio.';
                applyRateSuggestionButton.disabled = true;

                try {
                    var params = new URLSearchParams({
                        place_id: String(placeId),
                        adults: String(adults),
                        children: String(children),
                        currency_code: currencyCode,
                        trip_type: trip,
                        service_type_id: String(serviceTypeId)
                    });
                    var response = await fetch('/admin/bookings/quote?' + params.toString(), {
                        headers: { 'Accept': 'application/json' }
                    });
                    var data = await response.json();

                    if (requestId !== rateRequestId) { return; }

                    if (!response.ok || !data.ok) {
                        rateSuggestion = null;
                        rateSuggestionTitle.textContent = 'Sin tarifa base activa';
                        rateSuggestionText.textContent = data.message || 'No hay tarifa activa para esta combinacion.';
                        applyRateSuggestionButton.disabled = true;
                        syncSummary();
                        return;
                    }

                    rateSuggestion = data;
                    rateSuggestionTitle.textContent = 'Tarifa base: ' + data.price + ' ' + data.currency_code;
                    rateSuggestionText.textContent = data.service_type_name + ' - ' + data.pax_label + '. Referencia del sistema para la zona y servicio actuales.';
                    applyRateSuggestionButton.disabled = false;
                    syncSummary();
                } catch (error) {
                    if (requestId !== rateRequestId) { return; }
                    rateSuggestion = null;
                    rateSuggestionTitle.textContent = 'No se pudo consultar tarifa';
                    rateSuggestionText.textContent = 'Revisa la conexion o captura el total manualmente.';
                    applyRateSuggestionButton.disabled = true;
                }
            }, 250);
        }

        function syncOperationUi() {
            var isAirport = operationType.value === 'AIRPORT';
            var isRoundTrip = tripType.value === 'ROUND_TRIP';
            var providerMode = modeSelect ? modeSelect.value === 'PROVIDER' : false;

            originGroup.style.display = isAirport ? 'none' : '';
            directionGroup.style.display = isAirport && !isRoundTrip ? '' : 'none';

            airportOnlyFields.forEach(function (field) {
                field.style.display = isAirport ? '' : 'none';
            });

            if (providerGroup && operatorGroup && operatorSelect && providerSelect) {
                providerGroup.style.display = providerMode ? '' : 'none';
                operatorGroup.style.display = providerMode ? 'none' : '';
                if (providerMode) {
                    operatorSelect.value = '';
                } else {
                    providerSelect.value = '';
                }
            }

            if (isAirport) {
                destinationQueryLabel.textContent = 'Hotel / destino';
                destinationQueryHelp.textContent = 'Busca el hotel y selecciona una opcion del listado.';

                if (isRoundTrip) {
                    arrivalLabel.textContent = 'Llegada';
                    departureLabel.textContent = 'Salida';
                    arrivalGroup.style.display = '';
                    departureGroup.style.display = '';
                    directionSelect.value = 'AIRPORT_TO_DESTINATION';
                } else if (directionSelect.value === 'DESTINATION_TO_AIRPORT') {
                    arrivalGroup.style.display = 'none';
                    departureGroup.style.display = '';
                    departureLabel.textContent = 'Salida';
                } else {
                    arrivalGroup.style.display = '';
                    departureGroup.style.display = 'none';
                    arrivalLabel.textContent = 'Llegada';
                }

                originQuery.value = '';
                originNameInput.value = '';
            } else {
                destinationQueryLabel.textContent = 'Destino';
                destinationQueryHelp.textContent = 'Selecciona el hotel o punto de destino.';
                arrivalGroup.style.display = '';
                departureGroup.style.display = isRoundTrip ? '' : 'none';
                arrivalLabel.textContent = isRoundTrip ? 'Ida' : 'Servicio';
                departureLabel.textContent = 'Regreso';
            }
        }

        function resolveOperationSummary() {
            if (operationType.value === 'INTERHOTEL') {
                return tripType.value === 'ROUND_TRIP' ? 'Inter Hotel RT' : 'Inter Hotel';
            }
            if (tripType.value === 'ROUND_TRIP') { return 'Round trip'; }
            return directionSelect.value === 'DESTINATION_TO_AIRPORT' ? 'Salida' : 'Llegada';
        }

        function syncSummary() {
            var customerName = (customerNameInput ? customerNameInput.value || '' : '').trim();
            var customerLastName = (customerLastNameInput ? customerLastNameInput.value || '' : '').trim();
            var fullName = (customerName + ' ' + customerLastName).trim();
            var originDisplay = (originNameInput.value || '').trim();
            var destinationDisplay = (destinationNameInput.value || '').trim();
            var totalPax = Math.max(0, parseInt(adultsInput.value || '0', 10) + parseInt(childrenInput.value || '0', 10));

            if (operationType.value === 'AIRPORT' && !originDisplay) {
                originDisplay = directionSelect.value === 'DESTINATION_TO_AIRPORT'
                    ? (placeQuery.value || 'Hotel / origen')
                    : 'Aeropuerto';
            }

            if (!destinationDisplay) {
                if (operationType.value === 'AIRPORT') {
                    destinationDisplay = directionSelect.value === 'DESTINATION_TO_AIRPORT'
                        ? 'Aeropuerto'
                        : (placeQuery.value || 'Hotel / destino');
                } else {
                    destinationDisplay = placeQuery.value || 'Destino';
                }
            }

            summaryOperation.textContent = resolveOperationSummary();
            summaryCustomer.textContent = fullName !== '' ? fullName : 'Sin nombre aun';
            summaryRoute.textContent = originDisplay + ' -> ' + destinationDisplay;

            if (summarySchedule && arrivalInput && departureInput) {
                var arrivalValue = (arrivalInput.value || '').trim();
                var departureValue = (departureInput.value || '').trim();
                if (tripType.value === 'ROUND_TRIP') {
                    summarySchedule.textContent = (arrivalValue !== '' ? ('Llegada: ' + arrivalValue) : 'Llegada pendiente') + ' | ' + (departureValue !== '' ? ('Salida: ' + departureValue) : 'Salida pendiente');
                } else if (operationType.value === 'AIRPORT' && directionSelect.value === 'DESTINATION_TO_AIRPORT') {
                    summarySchedule.textContent = departureValue !== '' ? ('Salida: ' + departureValue) : 'Salida pendiente';
                } else {
                    summarySchedule.textContent = arrivalValue !== '' ? ('Horario: ' + arrivalValue) : 'Horario pendiente';
                }
            }

            if (summaryFlight && flightNumberInput) {
                if (operationType.value === 'AIRPORT') {
                    var flightCode = (flightNumberInput.value || '').trim();
                    summaryFlight.textContent = flightCode !== '' ? flightCode : 'Pendiente';
                } else {
                    summaryFlight.textContent = 'No aplica (inter hotel)';
                }
            }

            if (summaryReadyStatus && arrivalInput && departureInput) {
                var readyCustomer = fullName !== '';
                var readyPlace = parseInt(placeIdInput.value || '0', 10) > 0;
                var readySchedule = false;
                if (tripType.value === 'ROUND_TRIP') {
                    readySchedule = (arrivalInput.value || '').trim() !== '' && (departureInput.value || '').trim() !== '';
                } else if (operationType.value === 'AIRPORT' && directionSelect.value === 'DESTINATION_TO_AIRPORT') {
                    readySchedule = (departureInput.value || '').trim() !== '';
                } else {
                    readySchedule = (arrivalInput.value || '').trim() !== '';
                }
                summaryReadyStatus.textContent = (readyCustomer && readyPlace && readySchedule)
                    ? 'Listo para guardar cambios'
                    : 'Faltan datos para guardar';
            }

            summaryPax.textContent = totalPax + ' pax';

            if (summaryVehicle && vehicleSelect) {
                var vehicleText = vehicleSelect.options[vehicleSelect.selectedIndex]
                    ? vehicleSelect.options[vehicleSelect.selectedIndex].text
                    : 'Sin unidad asignada';
                summaryVehicle.textContent = parseInt(vehicleSelect.value || '0', 10) > 0 ? vehicleText : 'Sin unidad asignada';
            }
        }

        setupPlacesAutocomplete({
            queryInput: placeQuery,
            listNode: suggestions,
            onInputReset: function () {
                placeIdInput.value = '';
                zoneIdInput.value = '';
                zoneNameInput.value = '';
                destinationNameInput.value = '';
            },
            onSelect: function (item) {
                placeQuery.value = item.name;
                placeIdInput.value = String(item.id);
                zoneIdInput.value = String(item.zone_id);
                zoneNameInput.value = item.zone_name;
                destinationNameInput.value = item.name;
                fetchRateSuggestion();
            }
        });

        setupPlacesAutocomplete({
            queryInput: originQuery,
            listNode: originSuggestions,
            onInputReset: function () { originNameInput.value = ''; },
            onSelect: function (item) {
                originQuery.value = item.name;
                originNameInput.value = item.name;
            }
        });

        setupAirlinesAutocomplete();

        if (applyRecommendationButton && vehicleSelect) {
            applyRecommendationButton.addEventListener('click', function () {
                var recommendation = getRecommendedVehicle();
                if (!recommendation.vehicle) { return; }
                vehicleSelect.value = String(recommendation.vehicle.id);
                syncSummary();
            });
        }

        if (applyRateSuggestionButton && priceInput) {
            applyRateSuggestionButton.addEventListener('click', function () {
                if (!rateSuggestion || !rateSuggestion.price) { return; }
                priceInput.value = rateSuggestion.price;
                syncSummary();
            });
        }

        var watchedInputs = [operationType, tripType, directionSelect, modeSelect, adultsInput, childrenInput, serviceTypeSelect, currencySelect, vehicleSelect, customerNameInput, customerLastNameInput, placeQuery, originQuery, arrivalInput, departureInput, flightNumberInput];
        watchedInputs.forEach(function (node) {
            if (!node) { return; }
            node.addEventListener('change', function () {
                syncOperationUi();
                renderVehicleRecommendation();
                fetchRateSuggestion();
                syncSummary();
            });
            node.addEventListener('input', function () {
                renderVehicleRecommendation();
                fetchRateSuggestion();
                syncSummary();
            });
        });

        syncOperationUi();
        renderVehicleRecommendation();
        fetchRateSuggestion();
        syncSummary();
    })();
</script>
