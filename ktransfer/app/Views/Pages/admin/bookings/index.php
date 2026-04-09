<?php
declare(strict_types=1);

$bookings = $bookings ?? [];

$tripLabels = [
    'ONE_WAY' => 'Solo ida',
    'ROUND_TRIP' => 'Round trip',
];

$directionLabels = [
    'AIRPORT_TO_DESTINATION' => 'Aeropuerto a destino',
    'DESTINATION_TO_AIRPORT' => 'Destino a aeropuerto',
];
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
</style>

<div class="page-header">
    <div>
        <h1>Reservas</h1>
        <p style="margin: 6px 0 0; color: var(--muted);">Listado administrativo con fecha, cliente, ruta, servicio y estado operativo básico.</p>
    </div>
    <a href="/admin/bookings/create" class="btn btn-primary">Nueva reserva manual</a>
</div>

<p><a href="/admin" class="btn btn-secondary">Volver al dashboard</a></p>

<?php if (empty($bookings)): ?>
    <p>No se encontraron reservas.</p>
<?php else: ?>
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
                    <td>
                        <div class="booking-meta">
                            <strong><?= htmlspecialchars((string) ($booking['booking_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                            <span class="booking-subtle"><?= htmlspecialchars($tripLabels[(string) ($booking['trip_type'] ?? '')] ?? (string) ($booking['trip_type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="booking-subtle"><?= htmlspecialchars($directionLabels[(string) ($booking['direction'] ?? '')] ?? (string) ($booking['direction'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                    </td>
                    <td>
                        <div class="booking-meta">
                            <strong>
                                <?= htmlspecialchars(trim((string) ($booking['customer_name'] ?? '') . ' ' . (string) ($booking['customer_last_name'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                            </strong>
                            <span class="booking-subtle"><?= htmlspecialchars((string) ($booking['customer_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="booking-subtle"><?= htmlspecialchars((string) ($booking['customer_phone'] ?? 'Sin telefono'), ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                    </td>
                    <td>
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
                    <td>
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
                    <td>
                        <strong><?= htmlspecialchars(number_format((float) ($booking['price_total'] ?? 0), 2) . ' ' . (string) ($booking['currency_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                    </td>
                    <td>
                        <div class="status-stack">
                            <span class="status-chip"><?= htmlspecialchars((string) ($booking['status'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="status-chip" style="background: #ecfdf5; color: #047857;"><?= htmlspecialchars((string) ($booking['payment_status'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                    </td>
                    <td>
                        <a href="/admin/bookings/edit?id=<?= (int) ($booking['id'] ?? 0) ?>" class="btn btn-secondary">Ver / editar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
