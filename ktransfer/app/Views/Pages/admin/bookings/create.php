<?php
/** @var array $service_types */
/** @var array $zones */
/** @var array $places */
/** @var array $operators */
/** @var array $providers */
/** @var array $vehicles */
/** @var array $errors */
/** @var array $form */
/** @var array|null $vehicle_recommendation */

$serviceTypes = $service_types ?? [];
$operators = $operators ?? [];
$providers = $providers ?? [];
$vehicles = $vehicles ?? [];
$currencies = $currencies ?? [];
$places = $places ?? [];
$form = $form ?? [];
$errors = $errors ?? [];
$vehicleRecommendation = $vehicle_recommendation ?? null;
$isAgencyScope = (bool) ($is_agency_scope ?? false);
$isNewPlaceMode = strtoupper((string) ($form['place_mode'] ?? 'EXISTING')) === 'NEW';

$totalPax = max(
    0,
    (int) ($form['adults'] ?? 0) + (int) ($form['children'] ?? 0)
);
$selectedVehicleId = (int) ($form['vehicle_id'] ?? 0);
$selectedVehicle = null;
foreach ($vehicles as $vehicle) {
    if ((int) ($vehicle['id'] ?? 0) === $selectedVehicleId) {
        $selectedVehicle = $vehicle;
        break;
    }
}

$serviceTypesForJs = [];
foreach ($serviceTypes as $serviceType) {
    $serviceTypesForJs[] = [
        'id' => (int) ($serviceType['id'] ?? 0),
        'code' => (string) ($serviceType['code'] ?? ''),
        'name_es' => (string) ($serviceType['name_es'] ?? ''),
    ];
}

$vehiclesForJs = [];
foreach ($vehicles as $vehicle) {
    $vehiclesForJs[] = [
        'id' => (int) ($vehicle['id'] ?? 0),
        'code' => (string) ($vehicle['code'] ?? ''),
        'name' => (string) ($vehicle['name'] ?? ''),
        'max_pax' => (int) ($vehicle['max_pax'] ?? 0),
    ];
}

$placesForJs = [];
foreach ($places as $place) {
    $placesForJs[] = [
        'id' => (int) ($place['id'] ?? 0),
        'zone_id' => (int) ($place['zone_id'] ?? 0),
        'name' => (string) ($place['name'] ?? ''),
        'type' => (string) ($place['type'] ?? ''),
        'address' => (string) ($place['address'] ?? ''),
        'zone_name' => (string) ($place['zone_name'] ?? ''),
    ];
}

$bookingStatusLabels = \App\Core\StatusCatalog::bookingMap(true);
$paymentStatusLabels = \App\Core\StatusCatalog::paymentMap(true);
$serviceStatusLabels = \App\Core\StatusCatalog::serviceMap(true);
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
    .places-list-create {
        display: grid;
        gap: 4px;
        width: 100%;
        border: 0;
        background: #eef5ff;
        color: #173b75;
        padding: 12px 14px;
        text-align: left;
        cursor: pointer;
        font-weight: 800;
    }
    .places-list-create span {
        color: #506885;
        font-size: 0.84rem;
        font-weight: 600;
        line-height: 1.35;
    }
    .manual-inline-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 10px;
        padding: 0;
        border: 0;
        background: transparent;
        color: var(--primary);
        font-weight: 700;
        cursor: pointer;
    }
    .manual-inline-link.is-cancel {
        color: #64748b;
    }
    .new-place-fields {
        display: <?= $isNewPlaceMode ? 'block' : 'none' ?>;
    }
    @media (max-width: 1180px) {
        .manual-booking-shell {
            grid-template-columns: 1fr;
        }
        .manual-sidebar {
            position: static;
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
        <h1><?= $isAgencyScope ? 'Nueva reserva' : 'Nueva Reserva Manual' ?></h1>
        <p style="margin: 6px 0 0; color: var(--muted);">
            <?= $isAgencyScope
                ? 'Captura la solicitud de tu cliente. El precio sale de tarifas activas y administracion confirma la operacion.'
                : 'Carga comercial y operativa en una sola captura. El sistema sugiere una unidad por capacidad, pero administracion confirma la asignacion final.' ?>
        </p>
    </div>
    <a href="/admin/bookings" class="btn btn-secondary">Volver a Reservas</a>
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
    <form method="post" action="/admin/bookings/create" class="manual-booking-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) \App\Core\Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">

        <section class="manual-section">
            <div class="manual-section-head">
                <div>
                    <h2>Cliente y canal</h2>
                    <p>Datos basicos del pasajero y del canal comercial para identificar el servicio rapido en operacion.</p>
                </div>
                <div class="manual-inline-pills">
                    <span class="manual-chip">Booking manual</span>
                    <span class="manual-chip">Panel admin</span>
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
                    <span class="manual-help"><?= $isAgencyScope ? 'Tu agencia esta vinculada al usuario y no puede editarse desde este formulario.' : 'Escribe la agencia o concierge. El campo sugiere nombres del catalogo de proveedores, pero no obliga a seleccionarlos.' ?></span>
                </div>
                <div class="form-group manual-span-6">
                    <label for="customer_email">Email</label>
                    <input id="customer_email" type="email" name="customer_email" value="<?= htmlspecialchars((string) ($form['customer_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group manual-span-6">
                    <label for="customer_phone">Telefono</label>
                    <input id="customer_phone" name="customer_phone" value="<?= htmlspecialchars((string) ($form['customer_phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </div>
        </section>

        <section class="manual-section">
            <div class="manual-section-head">
                <div>
                    <h2>Servicio y ruta</h2>
                    <p>Describe la operacion como la entienden en despacho: llegada, salida, inter hotel o round trip con control interno.</p>
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

                <input type="hidden" id="place_mode" name="place_mode" value="<?= $isNewPlaceMode ? 'NEW' : 'EXISTING' ?>">

                <div class="form-group manual-span-6" id="destination_query_group" style="<?= $isNewPlaceMode ? 'display:none;' : '' ?>">
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
                    <button type="button" class="manual-inline-link" id="show_new_place_action">No esta en catalogo? Crear nuevo lugar</button>
                </div>

                <div class="form-group manual-span-12 new-place-fields">
                    <button type="button" class="manual-inline-link is-cancel" id="cancel_new_place_action">Volver a buscar en catalogo</button>
                </div>

                <div class="form-group manual-span-3 new-place-fields">
                    <label for="new_place_type">Tipo nuevo</label>
                    <select id="new_place_type" name="new_place_type">
                        <option value="HOTEL" <?= ($form['new_place_type'] ?? 'HOTEL') === 'HOTEL' ? 'selected' : '' ?>>Hotel</option>
                        <option value="AIRBNB" <?= ($form['new_place_type'] ?? '') === 'AIRBNB' ? 'selected' : '' ?>>Airbnb</option>
                        <option value="POINT" <?= ($form['new_place_type'] ?? '') === 'POINT' ? 'selected' : '' ?>>Punto</option>
                    </select>
                </div>

                <div class="form-group manual-span-3 new-place-fields">
                    <label for="new_place_zone_id">Zona</label>
                    <select id="new_place_zone_id" name="new_place_zone_id">
                        <option value="">Seleccionar zona</option>
                        <?php foreach ($zones as $zone): ?>
                            <?php $zoneId = (int) ($zone['id'] ?? 0); ?>
                            <option value="<?= $zoneId ?>" <?= (int) ($form['new_place_zone_id'] ?? $form['zone_id'] ?? 0) === $zoneId ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) ($zone['name_es'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group manual-span-6 new-place-fields">
                    <label for="new_place_name">Nombre o referencia</label>
                    <input
                        id="new_place_name"
                        name="new_place_name"
                        value="<?= htmlspecialchars((string) ($form['new_place_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                        placeholder="Nombre de hotel, residencia o referencia"
                    >
                </div>

                <div class="form-group manual-span-8 new-place-fields">
                    <label for="new_place_address">Dirección</label>
                    <input
                        id="new_place_address"
                        name="new_place_address"
                        value="<?= htmlspecialchars((string) ($form['new_place_address'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                        placeholder="Direccion, ubicacion catastral o referencia operativa"
                    >
                    <span class="manual-help" id="new_place_address_help">Opcional para hoteles; requerida para Airbnb y puntos.</span>
                </div>

                <div class="form-group manual-span-4 new-place-fields">
                    <label for="new_place_city">Ciudad (opcional)</label>
                    <input
                        id="new_place_city"
                        name="new_place_city"
                        value="<?= htmlspecialchars((string) ($form['new_place_city'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                    >
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

        <section class="manual-section">
            <div class="manual-section-head">
                <div>
                    <h2>Horarios y datos de vuelo</h2>
                    <p>El sistema guarda lo que venga del search o de la toma manual, y operacion luego lo usa para la hoja diaria.</p>
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
                    <span class="manual-help">Busca y selecciona una aerolinea del catalogo preguardado.</span>
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

        <section class="manual-section">
            <div class="manual-section-head">
                <div>
                    <h2>Pasajeros y cobro</h2>
                    <p><?= $isAgencyScope
                        ? 'Define tarifa de reporte y cobro al cliente. La tarifa de reporte nunca puede ser menor que la tarifa base calculada por el sistema.'
                        : 'El search suele asegurar pax y tarifa. Aqui puedes ajustarlo manualmente y dejar el estatus comercial correcto.' ?></p>
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
                    <select id="currency_code" name="currency_code" required>
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
                </div>
                <?php if ($isAgencyScope): ?>
                    <input id="price_total" type="hidden" name="price_total" value="<?= htmlspecialchars((string) ($form['price_total'] ?? '0.00'), ENT_QUOTES, 'UTF-8') ?>">
                <?php else: ?>
                    <div class="form-group manual-span-3">
                        <label for="price_total">Total</label>
                        <input id="price_total" type="number" min="0" step="0.01" name="price_total" required value="<?= htmlspecialchars((string) ($form['price_total'] ?? '0.00'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                <?php endif; ?>
                <?php if ($isAgencyScope): ?>
                    <div class="form-group manual-span-3">
                        <label for="agency_collection_mode">Cobro al cliente</label>
                        <select id="agency_collection_mode" name="agency_collection_mode">
                            <option value="COMPANY_COLLECTS" <?= strtoupper((string) ($form['agency_collection_mode'] ?? 'COMPANY_COLLECTS')) === 'COMPANY_COLLECTS' ? 'selected' : '' ?>>Empresa cobra al abordar (capturar cobro final)</option>
                            <option value="AGENCY_COLLECTED" <?= strtoupper((string) ($form['agency_collection_mode'] ?? '')) === 'AGENCY_COLLECTED' ? 'selected' : '' ?>>Agencia ya cobro (solo liquida tarifa de reporte)</option>
                        </select>
                        <span class="manual-help">Si empresa cobra: captura cobro final al cliente. Si agencia ya cobro: solo debe liquidar la tarifa de reporte al cierre.</span>
                    </div>
                    <div class="form-group manual-span-3">
                        <label for="agency_report_total">Tarifa de reporte (agencia)</label>
                        <input id="agency_report_total" type="number" min="0" step="0.01" name="agency_report_total" value="<?= htmlspecialchars((string) ($form['agency_report_total'] ?? $form['price_total'] ?? '0.00'), ENT_QUOTES, 'UTF-8') ?>">
                        <span class="manual-help">Ya se autocompleta con la tarifa base activa y puedes ajustarla si necesitas reportar un valor mayor.</span>
                    </div>
                    <div class="form-group manual-span-3">
                        <label for="agency_collected_total" id="agency_collected_total_label">Cobro final al cliente</label>
                        <input id="agency_collected_total" type="number" min="0" step="0.01" name="agency_collected_total" value="<?= htmlspecialchars((string) ($form['agency_collected_total'] ?? '0.00'), ENT_QUOTES, 'UTF-8') ?>">
                        <span class="manual-help" id="agency_collected_total_help">Monto final al cliente para calcular comision/diferencia vs tarifa de reporte.</span>
                    </div>
                <?php endif; ?>
                <div class="manual-span-12">
                    <div class="manual-rate-card" id="rate_suggestion_card">
                        <strong id="rate_suggestion_title">Tarifa base pendiente</strong>
                        <span id="rate_suggestion_text">Selecciona hotel, pasajeros, moneda y servicio para consultar la tarifa base del sistema.</span>
                        <div class="manual-rate-actions">
                            <?php if (!$isAgencyScope): ?>
                                <button type="button" class="btn btn-secondary" id="apply_rate_suggestion" disabled>Usar tarifa base</button>
                            <?php else: ?>
                                <button type="button" class="btn btn-secondary" id="apply_rate_suggestion" disabled style="display:none;">Usar tarifa base</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="form-group manual-span-6 manual-admin-only">
                    <label for="status">Estado reserva</label>
                    <select id="status" name="status" required>
                        <?php foreach (['PENDING', 'CONFIRMED', 'COMPLETED', 'NO_SHOW', 'CANCELLED'] as $status): ?>
                            <option value="<?= $status ?>" <?= ($form['status'] ?? 'PENDING') === $status ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) ($bookingStatusLabels[$status] ?? $status), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group manual-span-6 manual-admin-only">
                    <label for="payment_status">Estado pago</label>
                    <select id="payment_status" name="payment_status" required>
                        <?php foreach (['UNPAID', 'PARTIAL', 'PAID', 'REFUNDED'] as $paymentStatus): ?>
                            <option value="<?= $paymentStatus ?>" <?= ($form['payment_status'] ?? 'UNPAID') === $paymentStatus ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) ($paymentStatusLabels[$paymentStatus] ?? $paymentStatus), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </section>

        <?php if ($isAgencyScope): ?>
            <section class="manual-section">
                <div class="manual-section-head">
                    <div>
                        <h2>Resumen antes de crear</h2>
                        <p>Confirma estos montos antes de guardar la reserva.</p>
                    </div>
                </div>
                <div class="manual-form-grid">
                    <div class="form-group manual-span-3">
                        <label>Tarifa de reporte</label>
                        <strong class="manual-summary-value" id="inline_summary_report_total">-</strong>
                    </div>
                    <div class="form-group manual-span-3">
                        <label id="inline_summary_receipt_label">Cobro al cliente</label>
                        <strong class="manual-summary-value" id="inline_summary_receipt_total">-</strong>
                    </div>
                    <div class="form-group manual-span-3">
                        <label>Ganancia estimada</label>
                        <strong class="manual-summary-value" id="inline_summary_profit_total">-</strong>
                    </div>
                    <div class="form-group manual-span-3">
                        <label>Estatus cobro agencia</label>
                        <strong class="manual-summary-value" id="inline_summary_agency_charge_status">-</strong>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <section class="manual-section manual-admin-only">
            <div class="manual-section-head">
                <div>
                    <h2>Logistica inicial</h2>
                    <p>Opcional, pero util cuando la reserva manual ya nace casi lista para despacho. Se mantiene la jerga operativa: unidad, operador y proveedor.</p>
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
                            <?php $operatorId = (int) ($operator['id'] ?? 0); ?>
                            <option value="<?= $operatorId ?>" <?= (int) ($form['operator_user_id'] ?? 0) === $operatorId ? 'selected' : '' ?>>
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
                            <?php $providerId = (int) ($provider['id'] ?? 0); ?>
                            <option value="<?= $providerId ?>" <?= (int) ($form['provider_id'] ?? 0) === $providerId ? 'selected' : '' ?>>
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
                            <?php $vehicleId = (int) ($vehicle['id'] ?? 0); ?>
                            <option
                                value="<?= $vehicleId ?>"
                                data-max-pax="<?= (int) ($vehicle['max_pax'] ?? 0) ?>"
                                <?= $selectedVehicleId === $vehicleId ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars((string) ($vehicle['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="manual-help">La unidad sugerida se calcula por capacidad; puedes cambiarla manualmente.</span>
                </div>
                <div class="form-group manual-span-4">
                    <label for="service_status">Estado operativo</label>
                    <select id="service_status" name="service_status">
                        <?php foreach (['PENDING', 'ASSIGNED', 'IN_PROGRESS', 'DONE', 'NO_SHOW'] as $serviceStatus): ?>
                            <option value="<?= $serviceStatus ?>" <?= ($form['service_status'] ?? 'PENDING') === $serviceStatus ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) ($serviceStatusLabels[$serviceStatus] ?? $serviceStatus), ENT_QUOTES, 'UTF-8') ?>
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
                    ? 'Antes de guardar revisa: tarifa base, tarifa de reporte, cobro al cliente y ganancia estimada de la agencia. El estatus de cobro se calcula automaticamente.'
                    : 'La reserva se guarda con su base comercial y, si ya capturas logistica, tambien puede nacer con hoja operativa y asignacion inicial.' ?>
            </p>
            <div class="form-actions">
                <button type="submit" class="btn">Crear reserva</button>
                <a href="/admin/bookings" class="btn btn-secondary">Cancelar</a>
            </div>
        </div>
    </form>

    <aside class="manual-sidebar">
        <div class="manual-summary-card">
            <h2>Resumen rapido</h2>
            <div class="manual-summary-list">
                <div class="manual-summary-row">
                    <span class="manual-summary-label">Control operativo</span>
                    <strong class="manual-summary-value" id="summary_operation">
                        <?= htmlspecialchars((string) (($form['operation_type'] ?? 'AIRPORT') === 'INTERHOTEL' ? 'Inter Hotel' : (($form['trip_type'] ?? 'ONE_WAY') === 'ROUND_TRIP' ? 'Round trip' : (($form['direction'] ?? 'AIRPORT_TO_DESTINATION') === 'DESTINATION_TO_AIRPORT' ? 'Salida' : 'Llegada'))), ENT_QUOTES, 'UTF-8') ?>
                    </strong>
                </div>
                <div class="manual-summary-row">
                    <span class="manual-summary-label">Cliente</span>
                    <strong class="manual-summary-value" id="summary_customer">
                        <?= htmlspecialchars(trim((string) ($form['customer_name'] ?? '') . ' ' . (string) ($form['customer_last_name'] ?? '')) !== '' ? trim((string) ($form['customer_name'] ?? '') . ' ' . (string) ($form['customer_last_name'] ?? '')) : 'Sin nombre aun', ENT_QUOTES, 'UTF-8') ?>
                    </strong>
                </div>
                <div class="manual-summary-row">
                    <span class="manual-summary-label">Ruta</span>
                    <strong class="manual-summary-value" id="summary_route">
                        <?= htmlspecialchars(trim((string) ($form['origin_name'] ?? '')) !== '' || trim((string) ($form['destination_name'] ?? '')) !== '' ? trim((string) ($form['origin_name'] ?? 'Aeropuerto / origen')) . ' -> ' . trim((string) ($form['destination_name'] ?? 'Destino')) : 'Pendiente de seleccionar hotel / origen', ENT_QUOTES, 'UTF-8') ?>
                    </strong>
                </div>
                <div class="manual-summary-row">
                    <span class="manual-summary-label">Horario</span>
                    <strong class="manual-summary-value" id="summary_schedule">Pendiente</strong>
                </div>
                <div class="manual-summary-row">
                    <span class="manual-summary-label">Vuelo</span>
                    <strong class="manual-summary-value" id="summary_flight">Pendiente / no aplica</strong>
                </div>
                <div class="manual-summary-row">
                    <span class="manual-summary-label">Checklist rapido</span>
                    <strong class="manual-summary-value" id="summary_ready_status">Faltan datos para crear reserva</strong>
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
                <?php if ($isAgencyScope): ?>
                    <div class="manual-summary-row">
                        <span class="manual-summary-label">Tarifa de reporte</span>
                        <strong class="manual-summary-value" id="summary_report_total"><?= htmlspecialchars((string) ($form['agency_report_total'] ?? $form['price_total'] ?? '0.00') . ' ' . strtoupper((string) ($form['currency_code'] ?? 'USD')), ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                    <div class="manual-summary-row">
                        <span class="manual-summary-label" id="summary_receipt_label">Cobro al cliente (recibo)</span>
                        <strong class="manual-summary-value" id="summary_receipt_total"><?= htmlspecialchars((string) ($form['agency_collected_total'] ?? '0.00') . ' ' . strtoupper((string) ($form['currency_code'] ?? 'USD')), ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                    <div class="manual-summary-row">
                        <span class="manual-summary-label">Ganancia estimada</span>
                        <strong class="manual-summary-value" id="summary_profit_total">-</strong>
                    </div>
                    <div class="manual-summary-row">
                        <span class="manual-summary-label">Estatus cobro agencia</span>
                        <strong class="manual-summary-value" id="summary_agency_charge_status">Pendiente de cobro al cliente</strong>
                    </div>
                <?php endif; ?>
            </div>
        </div>

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
    </aside>
</div>

<script>
    (function () {
        var serviceTypes = <?= json_encode($serviceTypesForJs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        var vehicles = <?= json_encode($vehiclesForJs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        var placesCatalog = <?= json_encode($placesForJs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        var operationType = document.getElementById('operation_type');
        var tripType = document.getElementById('trip_type');
        var directionGroup = document.getElementById('direction_group');
        var directionSelect = document.getElementById('direction');
        var placeMode = document.getElementById('place_mode');
        var destinationQueryGroup = document.getElementById('destination_query_group');
        var placeQuery = document.getElementById('admin_place_query');
        var placeIdInput = document.getElementById('place_id');
        var zoneIdInput = document.getElementById('zone_id');
        var zoneNameInput = document.getElementById('zone_name');
        var newPlaceFields = Array.prototype.slice.call(document.querySelectorAll('.new-place-fields'));
        var newPlaceType = document.getElementById('new_place_type');
        var newPlaceName = document.getElementById('new_place_name');
        var newPlaceAddress = document.getElementById('new_place_address');
        var newPlaceAddressHelp = document.getElementById('new_place_address_help');
        var newPlaceCity = document.getElementById('new_place_city');
        var newPlaceZone = document.getElementById('new_place_zone_id');
        var suggestions = document.getElementById('admin_places_suggestions');
        var destinationNameInput = document.getElementById('destination_name');
        var destinationQueryLabel = document.getElementById('destination_query_label');
        var destinationQueryHelp = document.getElementById('destination_query_help');
        var showNewPlaceAction = document.getElementById('show_new_place_action');
        var cancelNewPlaceAction = document.getElementById('cancel_new_place_action');
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
        var agencyCollectionModeInput = document.getElementById('agency_collection_mode');
        var agencyReportInput = document.getElementById('agency_report_total');
        var agencyCollectedInput = document.getElementById('agency_collected_total');
        var agencyCollectedLabel = document.getElementById('agency_collected_total_label');
        var agencyCollectedHelp = document.getElementById('agency_collected_total_help');
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
        var summaryReportTotal = document.getElementById('summary_report_total');
        var summaryReceiptLabel = document.getElementById('summary_receipt_label');
        var summaryReceiptTotal = document.getElementById('summary_receipt_total');
        var summaryProfitTotal = document.getElementById('summary_profit_total');
        var summaryAgencyChargeStatus = document.getElementById('summary_agency_charge_status');
        var inlineSummaryReportTotal = document.getElementById('inline_summary_report_total');
        var inlineSummaryReceiptLabel = document.getElementById('inline_summary_receipt_label');
        var inlineSummaryReceiptTotal = document.getElementById('inline_summary_receipt_total');
        var inlineSummaryProfitTotal = document.getElementById('inline_summary_profit_total');
        var inlineSummaryAgencyChargeStatus = document.getElementById('inline_summary_agency_charge_status');
        var customerNameInput = document.getElementById('customer_name');
        var customerLastNameInput = document.getElementById('customer_last_name');
        var arrivalInput = document.getElementById('arrival_datetime');
        var departureInput = document.getElementById('departure_datetime');
        var flightNumberInput = document.getElementById('flight_number');
        var bookingForm = document.querySelector('.manual-booking-form');

        if (!operationType || !tripType || !directionGroup || !directionSelect || !placeMode || !destinationQueryGroup || !placeQuery || !placeIdInput || !zoneIdInput || !zoneNameInput || !newPlaceType || !newPlaceName || !newPlaceAddress || !newPlaceZone || !suggestions || !destinationNameInput || !originGroup || !originQuery || !originNameInput || !originSuggestions || !airlineInput || !airlineSuggestions || !arrivalGroup || !arrivalLabel || !departureGroup || !departureLabel || !vehicleSelect || !adultsInput || !childrenInput || !serviceTypeSelect || !currencySelect || !priceInput || !rateSuggestionTitle || !rateSuggestionText || !applyRateSuggestionButton || !recommendationLabel || !recommendationMeta || !recommendationNotes) {
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

        function renderCreatePlaceAction(listNode, queryText) {
            listNode.innerHTML = '';

            var emptyLi = document.createElement('li');
            emptyLi.className = 'places-list-empty';
            emptyLi.textContent = 'No encontramos lugares con ese nombre.';
            listNode.appendChild(emptyLi);

            var createLi = document.createElement('li');
            var createButton = document.createElement('button');
            createButton.type = 'button';
            createButton.className = 'places-list-create';
            createButton.appendChild(document.createTextNode('Crear nuevo lugar'));
            var createMeta = document.createElement('span');
            createMeta.textContent = queryText;
            createButton.appendChild(createMeta);
            createButton.addEventListener('click', function (event) {
                event.preventDefault();
                placeMode.value = 'NEW';
                newPlaceType.value = operationType.value === 'INTERHOTEL' ? 'POINT' : 'AIRBNB';
                newPlaceName.value = '';
                newPlaceAddress.value = queryText;
                syncPlaceModeUi();
                closeList(listNode);
                newPlaceZone.focus();
                fetchRateSuggestion();
                syncSummary();
            });
            createLi.appendChild(createButton);
            listNode.appendChild(createLi);
            listNode.style.display = 'block';
        }

        function setupPlacesAutocomplete(config) {
            var queryInput = config.queryInput;
            var listNode = config.listNode;
            var onSelect = config.onSelect;
            var debounceTimer;

            function localSearchItems(q) {
                var normalized = q.toLowerCase();
                return (Array.isArray(config.localItems) ? config.localItems : []).filter(function (item) {
                    var name = String(item.name || '').toLowerCase();
                    var address = String(item.address || '').toLowerCase();
                    var zoneName = String(item.zone_name || '').toLowerCase();
                    return name.indexOf(normalized) !== -1 || address.indexOf(normalized) !== -1 || zoneName.indexOf(normalized) !== -1;
                }).slice(0, 20);
            }

            function renderItems(items) {
                listNode.innerHTML = '';

                if (items.length === 0) {
                    if (config.onCreateNew) {
                        renderCreatePlaceAction(listNode, queryInput.value.trim());
                        return;
                    }
                    renderMessage(listNode, 'No encontramos lugares con ese nombre.');
                    return;
                }

                items.forEach(function (item) {
                    var li = document.createElement('li');
                    var button = document.createElement('button');
                    var title = document.createElement('strong');
                    var subtitle = document.createElement('span');
                    var detail = item.zone_name || '';

                    if (item.address) {
                        detail += detail !== '' ? ' - ' + item.address : item.address;
                    }

                    button.type = 'button';
                    button.className = 'places-list-button';
                    title.textContent = item.name;
                    subtitle.textContent = detail;
                    button.appendChild(title);
                    button.appendChild(subtitle);
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
            }

            async function fetchPlaces() {
                var q = queryInput.value.trim();

                if (q.length < 1) {
                    closeList(listNode);
                    return;
                }

                 if (Array.isArray(config.localItems) && config.localItems.length > 0) {
                    renderItems(localSearchItems(q));
                    return;
                }

                try {
                    var response = await fetch('/api/places?q=' + encodeURIComponent(q));
                    if (!response.ok) {
                        throw new Error('places_query_failed');
                    }
                    var data = await response.json();
                    var items = Array.isArray(data.items) ? data.items : [];
                    renderItems(items);
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
            return serviceTypes.find(function (item) {
                return item.id === selectedId;
            }) || null;
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

            return {
                totalPax: totalPax,
                serviceType: selectedService,
                vehicle: recommendedVehicle,
                notes: notes
            };
        }

        function renderVehicleRecommendation() {
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
            rateSuggestionTitle.textContent = 'Tarifa base pendiente';
            rateSuggestionText.textContent = message;
            if (applyRateSuggestionButton) {
                applyRateSuggestionButton.disabled = true;
            }
        }

        function fetchRateSuggestion() {
            clearTimeout(rateTimer);
            rateTimer = setTimeout(async function () {
                var placeId = parseInt(placeIdInput.value || '0', 10);
                var quoteZoneId = placeMode.value === 'NEW'
                    ? parseInt(newPlaceZone.value || '0', 10)
                    : parseInt(zoneIdInput.value || '0', 10);
                var adults = parseInt(adultsInput.value || '0', 10);
                var children = parseInt(childrenInput.value || '0', 10);
                var serviceTypeId = parseInt(serviceTypeSelect.value || '0', 10);
                var currencyCode = currencySelect.value || 'USD';
                var trip = tripType.value || 'ONE_WAY';
                var requestId = ++rateRequestId;

                if ((!placeId && !quoteZoneId) || adults < 1 || children < 0 || !serviceTypeId) {
                    setRateSuggestionPending('Selecciona hotel o zona, pasajeros y servicio para consultar la tarifa base del sistema.');
                    return;
                }

                rateSuggestionTitle.textContent = 'Consultando tarifa base...';
                rateSuggestionText.textContent = 'Buscando tarifa activa por zona, pax, moneda y servicio.';
                if (applyRateSuggestionButton) {
                    applyRateSuggestionButton.disabled = true;
                }

                try {
                    var params = new URLSearchParams({
                        place_id: String(placeId),
                        zone_id: String(quoteZoneId),
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

                    if (requestId !== rateRequestId) {
                        return;
                    }

                    if (!response.ok || !data.ok) {
                        rateSuggestion = null;
                        rateSuggestionTitle.textContent = 'Sin tarifa base activa';
                        rateSuggestionText.textContent = data.message || 'No hay tarifa activa para esta combinacion.';
                        if (applyRateSuggestionButton) {
                            applyRateSuggestionButton.disabled = true;
                        }
                        if (priceInput) {
                            priceInput.value = '0.00';
                        }
                        syncAgencyRules();
                        syncSummary();
                        return;
                    }

                    rateSuggestion = data;
                    rateSuggestionTitle.textContent = 'Tarifa base: ' + data.price + ' ' + data.currency_code;
                    rateSuggestionText.textContent = data.service_type_name + ' - ' + data.pax_label + '. En agencia, esta tarifa es la referencia minima para la tarifa de reporte.';
                    if (applyRateSuggestionButton) {
                        applyRateSuggestionButton.disabled = false;
                    }

                    if (priceInput) {
                        priceInput.value = data.price;
                    }

                    if (agencyReportInput) {
                        agencyReportInput.value = data.price;
                    }
                    syncAgencyRules();
                    syncSummary();
                } catch (error) {
                    if (requestId !== rateRequestId) {
                        return;
                    }

                    rateSuggestion = null;
                    rateSuggestionTitle.textContent = 'No se pudo consultar tarifa';
                    rateSuggestionText.textContent = 'Revisa la conexion o captura el total manualmente.';
                    if (applyRateSuggestionButton) {
                        applyRateSuggestionButton.disabled = true;
                    }
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

            if (providerGroup && operatorGroup) {
                providerGroup.style.display = providerMode ? '' : 'none';
                operatorGroup.style.display = providerMode ? 'none' : '';
            }
            if (providerMode && operatorSelect) {
                operatorSelect.value = '';
            } else if (!providerMode && providerSelect) {
                providerSelect.value = '';
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
                if (!destinationNameInput.value) {
                    destinationNameInput.value = '';
                }
            } else {
                destinationQueryLabel.textContent = 'Destino';
                destinationQueryHelp.textContent = 'Selecciona el hotel o punto de destino.';
                arrivalGroup.style.display = '';
                departureGroup.style.display = isRoundTrip ? '' : 'none';
                arrivalLabel.textContent = isRoundTrip ? 'Ida' : 'Servicio';
                departureLabel.textContent = 'Regreso';
            }
        }

        function syncPlaceModeUi() {
            var isNewPlace = placeMode.value === 'NEW';
            newPlaceFields.forEach(function (field) {
                field.style.display = isNewPlace ? 'block' : 'none';
            });
            destinationQueryGroup.style.display = isNewPlace ? 'none' : 'block';

            placeQuery.required = !isNewPlace;
            newPlaceZone.required = isNewPlace;
            newPlaceName.required = isNewPlace && newPlaceType.value === 'HOTEL';
            newPlaceAddress.required = isNewPlace && (newPlaceType.value === 'AIRBNB' || newPlaceType.value === 'POINT');

            if (newPlaceAddressHelp) {
                newPlaceAddressHelp.textContent = newPlaceAddress.required
                    ? 'Requerida para que operacion pueda ubicar este servicio.'
                    : 'Opcional para hoteles conocidos; util si quieres dejar referencia operativa.';
            }

            if (isNewPlace) {
                var typedDestination = (placeQuery.value || '').trim();
                if ((newPlaceName.value || '').trim() === '' && (newPlaceAddress.value || '').trim() === '' && typedDestination !== '') {
                    newPlaceType.value = operationType.value === 'INTERHOTEL' ? 'POINT' : 'AIRBNB';
                    newPlaceAddress.value = typedDestination;
                }
                closeList(suggestions);
                placeIdInput.value = '';
                zoneIdInput.value = newPlaceZone.value || '';
                var zoneText = newPlaceZone.options[newPlaceZone.selectedIndex]
                    ? newPlaceZone.options[newPlaceZone.selectedIndex].text
                    : '';
                zoneNameInput.value = newPlaceZone.value ? zoneText.trim() : '';
                var newDisplay = (newPlaceName.value || '').trim() || (newPlaceAddress.value || '').trim();
                destinationNameInput.value = newDisplay;
                placeQuery.value = newDisplay;
            }

            if (showNewPlaceAction) {
                showNewPlaceAction.style.display = isNewPlace ? 'none' : 'inline-flex';
            }
            if (cancelNewPlaceAction) {
                cancelNewPlaceAction.style.display = isNewPlace ? 'inline-flex' : 'none';
            }
        }

        function parseAmount(value) {
            var amount = parseFloat(value || '0');
            return isNaN(amount) ? 0 : amount;
        }

        function formatAmount(amount) {
            return amount.toFixed(2) + ' ' + (currencySelect.value || 'USD');
        }

        function syncAgencyRules() {
            if (!agencyReportInput || !priceInput) {
                return;
            }

            if (agencyCollectionModeInput && agencyCollectedInput) {
                var companyCollects = agencyCollectionModeInput.value === 'COMPANY_COLLECTS';
                agencyCollectedInput.disabled = !companyCollects;
                if (!companyCollects) {
                    var reportSync = parseAmount(agencyReportInput.value || priceInput.value);
                    agencyCollectedInput.value = reportSync.toFixed(2);
                }

                if (agencyCollectedLabel) {
                    agencyCollectedLabel.textContent = companyCollects ? 'Cobro final al cliente' : 'Liquidacion a empresa';
                }
                if (agencyCollectedHelp) {
                    agencyCollectedHelp.textContent = companyCollects
                        ? 'Monto final al cliente para calcular comision/diferencia vs tarifa de reporte.'
                        : 'Se sincroniza automaticamente con la tarifa de reporte porque la agencia ya cobro y solo liquida el servicio.';
                }
                if (summaryReceiptLabel) {
                    summaryReceiptLabel.textContent = companyCollects ? 'Cobro al cliente (recibo)' : 'Liquidacion a empresa';
                }
                if (inlineSummaryReceiptLabel) {
                    inlineSummaryReceiptLabel.textContent = companyCollects ? 'Cobro al cliente' : 'Liquidacion a empresa';
                }
            }

            var baseAmount = parseAmount(priceInput.value);
            var reportAmount = parseAmount(agencyReportInput.value);

            if (baseAmount <= 0) {
                agencyReportInput.min = '0.00';
                agencyReportInput.setCustomValidity('No hay tarifa base activa para esta moneda/servicio/zona.');
                return;
            }

            agencyReportInput.min = baseAmount.toFixed(2);
            if (reportAmount < baseAmount) {
                agencyReportInput.setCustomValidity('La tarifa de reporte no puede ser menor a la tarifa base del sistema.');
            } else {
                agencyReportInput.setCustomValidity('');
            }
        }

        function resolveOperationSummary() {
            if (operationType.value === 'INTERHOTEL') {
                return tripType.value === 'ROUND_TRIP' ? 'Inter Hotel RT' : 'Inter Hotel';
            }

            if (tripType.value === 'ROUND_TRIP') {
                return 'Round trip';
            }

            return directionSelect.value === 'DESTINATION_TO_AIRPORT' ? 'Salida' : 'Llegada';
        }

        function syncSummary() {
            var customerName = (customerNameInput.value || '').trim();
            var customerLastName = (customerLastNameInput.value || '').trim();
            var fullName = (customerName + ' ' + customerLastName).trim();
            var originDisplay = (originNameInput.value || '').trim();
            var destinationDisplay = (destinationNameInput.value || '').trim();
            var selectedVehicleText = vehicleSelect.options[vehicleSelect.selectedIndex]
                ? vehicleSelect.options[vehicleSelect.selectedIndex].text
                : 'Sin unidad asignada';
            var totalPax = Math.max(0, parseInt(adultsInput.value || '0', 10) + parseInt(childrenInput.value || '0', 10));

            if (operationType.value === 'AIRPORT' && !originDisplay) {
                originDisplay = directionSelect.value === 'DESTINATION_TO_AIRPORT'
                    ? (placeQuery.value || 'Hotel / origen')
                    : 'Aeropuerto';
            }

            if (placeMode.value === 'NEW') {
                destinationDisplay = (newPlaceName.value || '').trim() || (newPlaceAddress.value || '').trim() || 'Nuevo lugar';
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

            if (summaryOperation) {
                summaryOperation.textContent = resolveOperationSummary();
            }
            if (summaryCustomer) {
                summaryCustomer.textContent = fullName !== '' ? fullName : 'Sin nombre aun';
            }
            if (summaryRoute) {
                summaryRoute.textContent = originDisplay + ' -> ' + destinationDisplay;
            }

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
                var readyPlace = false;
                if (placeMode.value === 'NEW') {
                    var newZoneReady = parseInt(newPlaceZone.value || '0', 10) > 0;
                    if (newPlaceType.value === 'HOTEL') {
                        readyPlace = newZoneReady && (newPlaceName.value || '').trim() !== '';
                    } else {
                        readyPlace = newZoneReady && (newPlaceAddress.value || '').trim() !== '';
                    }
                } else {
                    readyPlace = parseInt(placeIdInput.value || '0', 10) > 0;
                }
                var readySchedule = false;
                if (tripType.value === 'ROUND_TRIP') {
                    readySchedule = (arrivalInput.value || '').trim() !== '' && (departureInput.value || '').trim() !== '';
                } else if (operationType.value === 'AIRPORT' && directionSelect.value === 'DESTINATION_TO_AIRPORT') {
                    readySchedule = (departureInput.value || '').trim() !== '';
                } else {
                    readySchedule = (arrivalInput.value || '').trim() !== '';
                }
                summaryReadyStatus.textContent = (readyCustomer && readyPlace && readySchedule)
                    ? 'Listo para crear reserva'
                    : 'Faltan datos para crear reserva';
            }

            if (summaryPax) {
                summaryPax.textContent = totalPax + ' pax';
            }
            if (summaryVehicle) {
                summaryVehicle.textContent = selectedVehicleIdValue() ? selectedVehicleText : 'Sin unidad asignada';
            }

            if (summaryReportTotal && summaryReceiptTotal && summaryProfitTotal && summaryAgencyChargeStatus) {
                var reportAmount = agencyReportInput ? parseAmount(agencyReportInput.value) : parseAmount(priceInput.value);
                var receiptAmount = agencyCollectedInput ? parseAmount(agencyCollectedInput.value) : 0;
                var companyCollects = agencyCollectionModeInput && agencyCollectionModeInput.value === 'COMPANY_COLLECTS';
                var estimatedProfit = receiptAmount - reportAmount;

                summaryReportTotal.textContent = formatAmount(reportAmount);
                summaryReceiptTotal.textContent = formatAmount(receiptAmount);
                summaryProfitTotal.textContent = formatAmount(estimatedProfit);
                summaryAgencyChargeStatus.textContent = companyCollects
                    ? 'Empresa cobra al abordar; registra cobro final al cliente y diferencia/comision.'
                    : 'Agencia ya cobro; al finalizar solo liquida tarifa de reporte.';

                if (inlineSummaryReportTotal) {
                    inlineSummaryReportTotal.textContent = summaryReportTotal.textContent;
                }
                if (inlineSummaryReceiptTotal) {
                    inlineSummaryReceiptTotal.textContent = summaryReceiptTotal.textContent;
                }
                if (inlineSummaryProfitTotal) {
                    inlineSummaryProfitTotal.textContent = summaryProfitTotal.textContent;
                }
                if (inlineSummaryAgencyChargeStatus) {
                    inlineSummaryAgencyChargeStatus.textContent = summaryAgencyChargeStatus.textContent;
                }
            }
        }

        function selectedVehicleIdValue() {
            return parseInt(vehicleSelect.value || '0', 10) > 0;
        }

        setupPlacesAutocomplete({
            queryInput: placeQuery,
            listNode: suggestions,
            localItems: placesCatalog,
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
            },
            onCreateNew: true
        });

        setupPlacesAutocomplete({
            queryInput: originQuery,
            listNode: originSuggestions,
            localItems: placesCatalog,
            onInputReset: function () {
                originNameInput.value = '';
            },
            onSelect: function (item) {
                originQuery.value = item.name;
                originNameInput.value = item.name;
            }
        });

        if (showNewPlaceAction) {
            showNewPlaceAction.addEventListener('click', function () {
                placeMode.value = 'NEW';
                syncPlaceModeUi();
                if (newPlaceZone) {
                    newPlaceZone.focus();
                }
                syncSummary();
            });
        }

        if (cancelNewPlaceAction) {
            cancelNewPlaceAction.addEventListener('click', function () {
                placeMode.value = 'EXISTING';
                syncPlaceModeUi();
                placeIdInput.value = '';
                zoneIdInput.value = '';
                zoneNameInput.value = '';
                destinationNameInput.value = '';
                if (placeQuery) {
                    placeQuery.focus();
                }
                syncSummary();
            });
        }

        setupAirlinesAutocomplete();

        if (applyRecommendationButton) {
            applyRecommendationButton.addEventListener('click', function () {
                var recommendation = getRecommendedVehicle();
                if (!recommendation.vehicle) {
                    return;
                }

                vehicleSelect.value = String(recommendation.vehicle.id);
                syncSummary();
            });
        }

        applyRateSuggestionButton.addEventListener('click', function () {
            if (!rateSuggestion || !rateSuggestion.price) {
                return;
            }

            priceInput.value = rateSuggestion.price;
            if (agencyReportInput) {
                agencyReportInput.value = rateSuggestion.price;
            }
            syncAgencyRules();
            syncSummary();
        });

        [operationType, tripType, directionSelect, modeSelect, adultsInput, childrenInput, serviceTypeSelect, currencySelect, vehicleSelect, customerNameInput, customerLastNameInput, placeMode, placeQuery, newPlaceType, newPlaceName, newPlaceAddress, newPlaceCity, newPlaceZone, originQuery, agencyCollectionModeInput, agencyReportInput, agencyCollectedInput, arrivalInput, departureInput, flightNumberInput].forEach(function (node) {
            if (!node) {
                return;
            }

            node.addEventListener('change', function () {
                syncOperationUi();
                syncPlaceModeUi();
                renderVehicleRecommendation();
                fetchRateSuggestion();
                syncAgencyRules();
                syncSummary();
            });

            node.addEventListener('input', function () {
                syncPlaceModeUi();
                renderVehicleRecommendation();
                fetchRateSuggestion();
                syncAgencyRules();
                syncSummary();
            });
        });

        if (bookingForm) {
            bookingForm.addEventListener('submit', function (event) {
                syncAgencyRules();
                if (agencyReportInput && !agencyReportInput.checkValidity()) {
                    agencyReportInput.reportValidity();
                    event.preventDefault();
                    return;
                }
            });
        }

        syncOperationUi();
        syncPlaceModeUi();
        renderVehicleRecommendation();
        fetchRateSuggestion();
        syncAgencyRules();
        syncSummary();
    })();
</script>
