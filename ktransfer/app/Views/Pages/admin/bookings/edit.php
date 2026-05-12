<?php
declare(strict_types=1);

$booking              = $booking ?? [];
$bookingStatuses      = $booking_statuses ?? [];
$isAdminOrSuperAdmin = \App\Core\ACL::currentUserHasRole('admin') || \App\Core\ACL::currentUserHasRole('superadmin');
$paymentStatuses = $payment_statuses ?? [];
$serviceTypes = $service_types ?? [];
$places = $places ?? [];
$currencies = $currencies ?? [];
$providers = $providers ?? [];
$isAgencyScope = (bool) ($is_agency_scope ?? false);
$bookingEditLogs = isset($booking_edit_logs) && is_array($booking_edit_logs) ? $booking_edit_logs : [];
$bookingDeleteRequests = isset($booking_delete_requests) && is_array($booking_delete_requests) ? $booking_delete_requests : [];
$canDeleteApprove = (bool) ($can_delete_approve ?? false);
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

$tripLabels = [
    'ONE_WAY' => 'Solo ida',
    'ROUND_TRIP' => 'Round trip',
];

$operationTypeLabels = [
    'AIRPORT' => 'Aeropuerto',
    'INTERHOTEL' => 'Inter Hotel',
];

$directionLabels = [
    'AIRPORT_TO_DESTINATION' => 'Llegada',
    'DESTINATION_TO_AIRPORT' => 'Salida',
];

$agendaDate = '';
if (($booking['arrival_datetime'] ?? '') !== '') {
    $agendaDate = date('Y-m-d', strtotime((string) $booking['arrival_datetime']));
} elseif (($booking['departure_datetime'] ?? '') !== '') {
    $agendaDate = date('Y-m-d', strtotime((string) $booking['departure_datetime']));
}

$agencyCollectedTotal = (float) ($booking['agency_collected_total'] ?? 0);
$reportTotal = (float) ($booking['price_total'] ?? 0);
$agencyGain = $agencyCollectedTotal - $reportTotal;
$arrivalInput = (string) ($booking['arrival_datetime'] ?? '') !== ''
    ? date('Y-m-d\TH:i', strtotime((string) $booking['arrival_datetime']))
    : '';
$departureInput = (string) ($booking['departure_datetime'] ?? '') !== ''
    ? date('Y-m-d\TH:i', strtotime((string) $booking['departure_datetime']))
    : '';
$agencyChargeStatus = $agencyCollectedTotal > 0
    ? 'Ya cobro al cliente; pendiente de pago a KTransfers al completar servicio.'
    : 'Pendiente de cobro al cliente / podemos cobrar al dar servicio.';
?>
<style>
    .booking-detail-grid {
        display: grid;
        gap: 16px;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        margin-bottom: 18px;
    }
    .booking-detail-card {
        padding: 16px;
        border: 1px solid var(--border);
        border-radius: 14px;
        background: linear-gradient(180deg, #fff, #f8fbff);
    }
    .booking-detail-card h2 {
        margin: 0 0 14px;
        font-size: 1rem;
    }
    .booking-detail-list {
        display: grid;
        gap: 10px;
    }
    .booking-detail-row {
        display: grid;
        gap: 3px;
    }
    .booking-detail-row span {
        color: var(--muted);
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }
    @media (max-width: 900px) {
        .booking-detail-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="page-header">
    <div>
        <h1>Reserva <?= htmlspecialchars((string) ($booking['booking_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
        <p style="margin: 6px 0 0; color: var(--muted);">Se agregaron datos operativos para saber qué servicio es, cuándo ocurre y quién lo reservó.</p>
    </div>
    <div class="form-actions">
        <?php if ($agendaDate !== ''): ?>
            <a href="/admin/operations/agenda?date=<?= htmlspecialchars($agendaDate, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-secondary">Ver en agenda</a>
        <?php endif; ?>
        <a href="/admin/bookings/service-order?id=<?= (int) ($booking['id'] ?? 0) ?>" class="btn btn-secondary" target="_blank" rel="noreferrer">Orden de servicio</a>
        <a href="/admin/bookings/voucher?id=<?= (int) ($booking['id'] ?? 0) ?>" class="btn btn-secondary" target="_blank" rel="noreferrer">Voucher / ticket</a>
        <a href="/admin/bookings" class="btn btn-secondary">Volver</a>
    </div>
</div>

<div class="booking-detail-grid">
    <section class="booking-detail-card">
        <h2>Cliente</h2>
        <div class="booking-detail-list">
            <div class="booking-detail-row">
                <span>Nombre</span>
                <strong><?= htmlspecialchars(trim((string) ($booking['customer_name'] ?? '') . ' ' . (string) ($booking['customer_last_name'] ?? '')), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="booking-detail-row">
                <span>Email</span>
                <strong><?= htmlspecialchars((string) ($booking['customer_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="booking-detail-row">
                <span>Telefono</span>
                <strong><?= htmlspecialchars((string) ($booking['customer_phone'] ?? 'Sin telefono'), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="booking-detail-row">
                <span>Agencia</span>
                <strong><?= htmlspecialchars((string) ($booking['agency_name'] ?? 'Sin agencia'), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
        </div>
    </section>

    <section class="booking-detail-card">
        <h2>Servicio</h2>
        <div class="booking-detail-list">
            <div class="booking-detail-row">
                <span>Tipo de operacion</span>
                <strong><?= htmlspecialchars($operationTypeLabels[(string) ($booking['operation_type'] ?? 'AIRPORT')] ?? (string) ($booking['operation_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="booking-detail-row">
                <span>Tipo de viaje</span>
                <strong><?= htmlspecialchars($tripLabels[(string) ($booking['trip_type'] ?? '')] ?? (string) ($booking['trip_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <?php if ((string) ($booking['operation_type'] ?? 'AIRPORT') === 'AIRPORT'): ?>
                <div class="booking-detail-row">
                    <span>Direccion</span>
                    <strong><?= htmlspecialchars($directionLabels[(string) ($booking['direction'] ?? '')] ?? (string) ($booking['direction'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
            <?php endif; ?>
            <div class="booking-detail-row">
                <span>Servicio reservado</span>
                <strong><?= htmlspecialchars((string) ($booking['service_type_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="booking-detail-row">
                <span>Pasajeros</span>
                <strong><?= htmlspecialchars((string) (($booking['adults'] ?? '0') . ' adultos, ' . ($booking['children'] ?? '0') . ' menores, total ' . ($booking['total_pax'] ?? '0')), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="booking-detail-row">
                <span>Tarifa de reporte (agencia)</span>
                <strong><?= htmlspecialchars(number_format($reportTotal, 2) . ' ' . (string) ($booking['currency_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="booking-detail-row">
                <span>Cobro al cliente (recibo)</span>
                <strong><?= htmlspecialchars(number_format($agencyCollectedTotal, 2) . ' ' . (string) ($booking['currency_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="booking-detail-row">
                <span>Ganancia estimada agencia</span>
                <strong><?= htmlspecialchars(number_format($agencyGain, 2) . ' ' . (string) ($booking['currency_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="booking-detail-row">
                <span>Estatus cobro agencia</span>
                <strong><?= htmlspecialchars($agencyChargeStatus, ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
        </div>
    </section>

    <section class="booking-detail-card">
        <h2>Ruta y horarios</h2>
        <div class="booking-detail-list">
            <div class="booking-detail-row">
                <span>Zona</span>
                <strong><?= htmlspecialchars((string) ($booking['zone_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="booking-detail-row">
                <span>Destino</span>
                <strong><?= htmlspecialchars((string) ($booking['place_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="booking-detail-row">
                <span>Origen operativo</span>
                <strong><?= htmlspecialchars((string) ($booking['origin_name'] ?? 'Sin definir'), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="booking-detail-row">
                <span>Destino operativo</span>
                <strong><?= htmlspecialchars((string) ($booking['destination_name'] ?? 'Sin definir'), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <?php if (($booking['arrival_datetime'] ?? '') !== ''): ?>
                <div class="booking-detail-row">
                    <span>Llegada</span>
                    <strong><?= htmlspecialchars((string) date('d/m/Y H:i', strtotime((string) $booking['arrival_datetime'])), ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
            <?php endif; ?>
            <?php if (($booking['departure_datetime'] ?? '') !== ''): ?>
                <div class="booking-detail-row">
                    <span>Salida</span>
                    <strong><?= htmlspecialchars((string) date('d/m/Y H:i', strtotime((string) $booking['departure_datetime'])), ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="booking-detail-card">
        <h2>Vuelo y operacion</h2>
        <div class="booking-detail-list">
            <div class="booking-detail-row">
                <span>Aerolinea</span>
                <strong><?= htmlspecialchars((string) ($booking['airline'] ?? 'Sin definir'), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="booking-detail-row">
                <span>Numero de vuelo</span>
                <strong><?= htmlspecialchars((string) ($booking['flight_number'] ?? 'Sin definir'), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="booking-detail-row">
                <span>Terminal</span>
                <strong><?= htmlspecialchars((string) ($booking['terminal'] ?? 'Sin definir'), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="booking-detail-row">
                <span>Operador</span>
                <strong><?= htmlspecialchars((string) ($booking['operator_name'] ?? 'Sin asignar'), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="booking-detail-row">
                <span>Proveedor</span>
                <strong><?= htmlspecialchars((string) ($booking['provider_name'] ?? 'Sin proveedor'), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="booking-detail-row">
                <span>Unidad</span>
                <strong><?= htmlspecialchars((string) ($booking['vehicle_name'] ?? 'Sin unidad'), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="booking-detail-row">
                <span>Estado operativo</span>
                <strong><?= htmlspecialchars(\App\Core\StatusCatalog::serviceLabel((string) ($booking['assignment_status'] ?? 'PENDING'), true), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="booking-detail-row">
                <span>Notas de pickup</span>
                <strong><?= htmlspecialchars((string) ($booking['pickup_notes'] ?? 'Sin notas'), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="booking-detail-row">
                <span>Work order</span>
                <strong><?= htmlspecialchars((string) ($booking['work_order_notes'] ?? 'Sin nota operativa'), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
        </div>
    </section>
</div>

<div class="card">
    <form method="post" action="/admin/bookings/update">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) \App\Core\Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="id" value="<?= htmlspecialchars((string) ($booking['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">

        <div class="booking-detail-grid" style="margin-bottom: 0;">
            <section class="booking-detail-card">
                <h2>Cliente y contacto</h2>
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="customer_name" required value="<?= htmlspecialchars((string) ($booking['customer_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label>Apellido</label>
                    <input type="text" name="customer_last_name" value="<?= htmlspecialchars((string) ($booking['customer_last_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="customer_email" required value="<?= htmlspecialchars((string) ($booking['customer_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" name="customer_phone" value="<?= htmlspecialchars((string) ($booking['customer_phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label>Agencia</label>
                    <input type="text" name="agency_name" list="agency_name_suggestions" value="<?= htmlspecialchars((string) ($booking['agency_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" <?= $isAgencyScope ? 'readonly' : '' ?>>
                    <?php if (!$isAgencyScope): ?>
                        <datalist id="agency_name_suggestions">
                            <?php foreach ($providers as $provider): ?>
                                <?php $providerName = trim((string) ($provider['name'] ?? '')); ?>
                                <?php if ($providerName === '') { continue; } ?>
                                <option value="<?= htmlspecialchars($providerName, ENT_QUOTES, 'UTF-8') ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                    <?php endif; ?>
                </div>
            </section>

            <section class="booking-detail-card">
                <h2>Servicio y tarifa</h2>
                <div class="form-group">
                    <label>Tipo de viaje</label>
                    <select name="trip_type" required>
                        <option value="ONE_WAY" <?= ((string) ($booking['trip_type'] ?? '') === 'ONE_WAY') ? 'selected' : '' ?>>Solo ida</option>
                        <option value="ROUND_TRIP" <?= ((string) ($booking['trip_type'] ?? '') === 'ROUND_TRIP') ? 'selected' : '' ?>>Round trip</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tipo de operación</label>
                    <select name="operation_type" required>
                        <option value="AIRPORT" <?= ((string) ($booking['operation_type'] ?? 'AIRPORT') === 'AIRPORT') ? 'selected' : '' ?>>Aeropuerto</option>
                        <option value="INTERHOTEL" <?= ((string) ($booking['operation_type'] ?? '') === 'INTERHOTEL') ? 'selected' : '' ?>>Inter Hotel</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Control operativo</label>
                    <select name="direction" required>
                        <option value="AIRPORT_TO_DESTINATION" <?= ((string) ($booking['direction'] ?? '') === 'AIRPORT_TO_DESTINATION') ? 'selected' : '' ?>>Llegada</option>
                        <option value="DESTINATION_TO_AIRPORT" <?= ((string) ($booking['direction'] ?? '') === 'DESTINATION_TO_AIRPORT') ? 'selected' : '' ?>>Salida</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Servicio comercial</label>
                    <select name="service_type_id" required>
                        <?php foreach ($serviceTypes as $serviceType): ?>
                            <?php $serviceTypeId = (int) ($serviceType['id'] ?? 0); ?>
                            <option value="<?= $serviceTypeId ?>" <?= ((int) ($booking['service_type_id'] ?? 0) === $serviceTypeId) ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) ($serviceType['name_es'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Hotel / destino</label>
                    <select name="place_id" required>
                        <?php foreach ($places as $place): ?>
                            <?php $placeId = (int) ($place['id'] ?? 0); ?>
                            <option value="<?= $placeId ?>" <?= ((int) ($booking['place_id'] ?? 0) === $placeId) ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) ($place['zone_name'] ?? '') . ' / ' . (string) ($place['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Moneda</label>
                    <select name="currency_code" required>
                        <?php foreach ($currencies as $currency): ?>
                            <?php $currencyCode = strtoupper((string) ($currency['code'] ?? '')); ?>
                            <option value="<?= htmlspecialchars($currencyCode, ENT_QUOTES, 'UTF-8') ?>" <?= strtoupper((string) ($booking['currency_code'] ?? '')) === $currencyCode ? 'selected' : '' ?>><?= htmlspecialchars($currencyCode, ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($isAgencyScope): ?>
                    <input type="hidden" name="price_total" value="<?= htmlspecialchars((string) ($booking['price_total'] ?? '0.00'), ENT_QUOTES, 'UTF-8') ?>">
                <?php else: ?>
                    <div class="form-group">
                        <label>Total</label>
                        <input type="number" min="0" step="0.01" name="price_total" value="<?= htmlspecialchars((string) ($booking['price_total'] ?? '0.00'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                <?php endif; ?>
                <div class="form-group">
                    <label>Adults</label>
                    <input type="number" min="1" name="adults" value="<?= htmlspecialchars((string) ($booking['adults'] ?? '1'), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label>Children</label>
                    <input type="number" min="0" name="children" value="<?= htmlspecialchars((string) ($booking['children'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </section>

            <section class="booking-detail-card">
                <h2>Fechas y vuelo</h2>
                <div class="form-group">
                    <label>Llegada</label>
                    <input type="datetime-local" name="arrival_datetime" value="<?= htmlspecialchars($arrivalInput, ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label>Salida</label>
                    <input type="datetime-local" name="departure_datetime" value="<?= htmlspecialchars($departureInput, ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label>Aerolínea</label>
                    <input type="text" name="airline" value="<?= htmlspecialchars((string) ($booking['airline'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label>Vuelo</label>
                    <input type="text" name="flight_number" value="<?= htmlspecialchars((string) ($booking['flight_number'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label>Terminal</label>
                    <input type="text" name="terminal" value="<?= htmlspecialchars((string) ($booking['terminal'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label>Notas de pickup</label>
                    <input type="text" name="pickup_notes" value="<?= htmlspecialchars((string) ($booking['pickup_notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </section>

            <section class="booking-detail-card">
                <h2>Estado comercial</h2>
                <div class="form-group">
                    <label>Estado de reserva</label>
                    <select name="status" required>
                        <?php foreach ($bookingStatuses as $status): ?>
                            <option value="<?= htmlspecialchars((string) $status, ENT_QUOTES, 'UTF-8') ?>" <?= ((string) ($booking['status'] ?? '') === $status) ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) ($bookingStatusLabels[(string) $status] ?? $status), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </section>

            <section class="booking-detail-card">
                <h2>Pago</h2>
                <div class="form-group">
                    <label>Estado de pago</label>
                    <select name="payment_status" required>
                        <?php foreach ($paymentStatuses as $status): ?>
                            <option value="<?= htmlspecialchars((string) $status, ENT_QUOTES, 'UTF-8') ?>" <?= ((string) ($booking['payment_status'] ?? '') === $status) ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) ($paymentStatusLabels[(string) $status] ?? $status), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </section>

            <section class="booking-detail-card">
                <h2>Hoja operativa</h2>
                <div class="form-group">
                    <label>Origen</label>
                    <input type="text" name="origin_name" value="<?= htmlspecialchars((string) ($booking['origin_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label>Destino</label>
                    <input type="text" name="destination_name" value="<?= htmlspecialchars((string) ($booking['destination_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label>Nota operativa</label>
                    <textarea name="work_order_notes" rows="4"><?= htmlspecialchars((string) ($booking['work_order_notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <div class="form-group">
                    <label>Comentarios</label>
                    <textarea name="comments" rows="4"><?= htmlspecialchars((string) ($booking['comments'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
            </section>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <span class="pill"><?= htmlspecialchars(number_format((float) ($booking['price_total'] ?? 0), 2) . ' ' . (string) ($booking['currency_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    </form>
</div>

<?php if ($isAdminOrSuperAdmin): ?>
<div class="card" style="margin-top: 14px; border-left: 4px solid var(--danger);">
    <p style="margin-bottom: 10px;"><strong style="color: var(--danger);">Zona de peligro — Borrar reserva</strong></p>
    <p class="admin-page-note" style="margin-bottom: 12px;">Esta acción es irreversible. La reserva se eliminará de forma permanente.</p>
    <form method="post" action="/admin/bookings/delete" onsubmit="return confirm('¿Confirmas el borrado permanente de esta reserva? Esta acción no se puede deshacer.')">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) \App\Core\Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="id" value="<?= (int) ($booking['id'] ?? 0) ?>">
        <button type="submit" class="btn" style="background: var(--danger); color: #fff; border: none;">Borrar reserva definitivamente</button>
    </form>
</div>
<?php endif; ?>

<div class="booking-detail-grid" style="margin-top: 18px;">
    <section class="booking-detail-card">
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
            <h3 style="margin-bottom: 8px;">Historial de solicitudes</h3>
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
                            <input type="hidden" name="booking_id" value="<?= (int) ($booking['id'] ?? 0) ?>">
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

    <section class="booking-detail-card">
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
