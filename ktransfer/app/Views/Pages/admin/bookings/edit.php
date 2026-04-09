<?php
declare(strict_types=1);

$booking = $booking ?? [];
$bookingStatuses = $booking_statuses ?? [];
$paymentStatuses = $payment_statuses ?? [];

$tripLabels = [
    'ONE_WAY' => 'Solo ida',
    'ROUND_TRIP' => 'Round trip',
];

$directionLabels = [
    'AIRPORT_TO_DESTINATION' => 'Aeropuerto a destino',
    'DESTINATION_TO_AIRPORT' => 'Destino a aeropuerto',
];

$agendaDate = '';
if (($booking['arrival_datetime'] ?? '') !== '') {
    $agendaDate = date('Y-m-d', strtotime((string) $booking['arrival_datetime']));
} elseif (($booking['departure_datetime'] ?? '') !== '') {
    $agendaDate = date('Y-m-d', strtotime((string) $booking['departure_datetime']));
}
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
        </div>
    </section>

    <section class="booking-detail-card">
        <h2>Servicio</h2>
        <div class="booking-detail-list">
            <div class="booking-detail-row">
                <span>Tipo de viaje</span>
                <strong><?= htmlspecialchars($tripLabels[(string) ($booking['trip_type'] ?? '')] ?? (string) ($booking['trip_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="booking-detail-row">
                <span>Direccion</span>
                <strong><?= htmlspecialchars($directionLabels[(string) ($booking['direction'] ?? '')] ?? (string) ($booking['direction'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="booking-detail-row">
                <span>Servicio reservado</span>
                <strong><?= htmlspecialchars((string) ($booking['service_type_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="booking-detail-row">
                <span>Pasajeros</span>
                <strong><?= htmlspecialchars((string) (($booking['adults'] ?? '0') . ' adultos, ' . ($booking['children'] ?? '0') . ' menores, total ' . ($booking['total_pax'] ?? '0')), ENT_QUOTES, 'UTF-8') ?></strong>
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
                <span>Operador</span>
                <strong><?= htmlspecialchars((string) ($booking['operator_name'] ?? 'Sin asignar'), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="booking-detail-row">
                <span>Estado operativo</span>
                <strong><?= htmlspecialchars((string) ($booking['assignment_status'] ?? 'PENDING'), ENT_QUOTES, 'UTF-8') ?></strong>
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
                <h2>Estado comercial</h2>
                <div class="form-group">
                    <label>Estado de reserva</label>
                    <select name="status" required>
                        <?php foreach ($bookingStatuses as $status): ?>
                            <option value="<?= htmlspecialchars((string) $status, ENT_QUOTES, 'UTF-8') ?>" <?= ((string) ($booking['status'] ?? '') === $status) ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) $status, ENT_QUOTES, 'UTF-8') ?>
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
                                <?= htmlspecialchars((string) $status, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </section>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <span class="pill"><?= htmlspecialchars(number_format((float) ($booking['price_total'] ?? 0), 2) . ' ' . (string) ($booking['currency_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    </form>
</div>
