<?php
declare(strict_types=1);
/** @var array $booking */

$tripTypeLabels = [
    'ROUND_TRIP' => 'Viaje redondo',
    'ONE_WAY' => 'Solo ida',
];
$directionLabels = [
    'AIRPORT_TO_DESTINATION' => 'Aeropuerto a hotel',
    'DESTINATION_TO_AIRPORT' => 'Hotel a aeropuerto',
];
$statusLabels = \App\Core\StatusCatalog::bookingMap(true);
?>
<div class="flow-shell flow-stack">
    <section class="flow-hero">
        <span class="flow-kicker">Reserva enviada</span>
        <h1>Todo listo.</h1>
        <p>Tu solicitud ya fue registrada. A continuacion puedes revisar el codigo y los datos principales del traslado en una vista clara y facil de leer.</p>
    </section>

    <section class="flow-card flow-stack">
        <div class="summary-grid">
            <div class="summary-item">
                <span class="stat-label">Codigo de reserva</span>
                <strong><?= htmlspecialchars((string) ($booking['booking_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="summary-item">
                <span class="stat-label">Estado</span>
                <strong><?= htmlspecialchars((string) ($statusLabels[$booking['status']] ?? ($booking['status'] ?? '')), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="summary-item">
                <span class="stat-label">Total</span>
                <strong><?= htmlspecialchars(number_format((float) ($booking['price_total'] ?? 0), 2), ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars((string) ($booking['currency_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
        </div>

        <div class="message-box success">
            Hemos enviado la confirmacion a <strong><?= htmlspecialchars((string) ($booking['customer_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>. Guarda el codigo de reserva para cualquier seguimiento.
        </div>
    </section>

    <section class="flow-card flow-stack">
        <div>
            <span class="card-label">Detalles del traslado</span>
            <h2>Resumen del servicio</h2>
        </div>

        <div class="info-grid">
            <div class="summary-item">
                <span class="stat-label">Tipo de viaje</span>
                <strong><?= htmlspecialchars((string) ($tripTypeLabels[$booking['trip_type']] ?? ($booking['trip_type'] ?? '')), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="summary-item">
                <span class="stat-label">Direccion</span>
                <strong><?= htmlspecialchars((string) ($directionLabels[$booking['direction']] ?? ($booking['direction'] ?? '')), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="summary-item">
                <span class="stat-label">Servicio</span>
                <strong><?= htmlspecialchars((string) ($booking['service_type_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="summary-item">
                <span class="stat-label">Pasajeros</span>
                <strong><?= (int) ($booking['total_pax'] ?? 0) ?></strong>
            </div>
            <div class="summary-item">
                <span class="stat-label">Destino</span>
                <strong><?= htmlspecialchars((string) ($booking['place_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="summary-item">
                <span class="stat-label">Zona</span>
                <strong><?= htmlspecialchars((string) ($booking['zone_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <?php if (!empty($booking['arrival_datetime'])): ?>
                <div class="summary-item">
                    <span class="stat-label">Llegada</span>
                    <strong><?= htmlspecialchars(date('d/m/Y H:i', strtotime((string) $booking['arrival_datetime'])), ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
            <?php endif; ?>
            <?php if (!empty($booking['departure_datetime'])): ?>
                <div class="summary-item">
                    <span class="stat-label">Salida</span>
                    <strong><?= htmlspecialchars(date('d/m/Y H:i', strtotime((string) $booking['departure_datetime'])), ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php if (!empty($booking['airline']) || !empty($booking['flight_number']) || !empty($booking['pickup_notes'])): ?>
        <section class="flow-card flow-stack">
            <div>
                <span class="card-label">Informacion del vuelo</span>
                <h2>Coordinacion de llegada</h2>
            </div>

            <div class="info-grid">
                <?php if (!empty($booking['airline'])): ?>
                    <div class="summary-item">
                        <span class="stat-label">Aerolinea</span>
                        <strong><?= htmlspecialchars((string) $booking['airline'], ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                <?php endif; ?>
                <?php if (!empty($booking['flight_number'])): ?>
                    <div class="summary-item">
                        <span class="stat-label">Numero de vuelo</span>
                        <strong><?= htmlspecialchars((string) $booking['flight_number'], ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                <?php endif; ?>
                <?php if (!empty($booking['pickup_notes'])): ?>
                    <div class="summary-item" style="grid-column: 1 / -1;">
                        <span class="stat-label">Notas</span>
                        <strong><?= htmlspecialchars((string) $booking['pickup_notes'], ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="flow-card flow-stack">
        <div>
            <span class="card-label">Datos del cliente</span>
            <h2>Contacto registrado</h2>
        </div>

        <div class="info-grid">
            <div class="summary-item">
                <span class="stat-label">Nombre completo</span>
                <strong><?= htmlspecialchars(trim((string) (($booking['customer_name'] ?? '') . ' ' . ($booking['customer_last_name'] ?? ''))), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="summary-item">
                <span class="stat-label">Email</span>
                <strong><?= htmlspecialchars((string) ($booking['customer_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <?php if (!empty($booking['customer_phone'])): ?>
                <div class="summary-item">
                    <span class="stat-label">Telefono</span>
                    <strong><?= htmlspecialchars((string) $booking['customer_phone'], ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="flow-card flow-stack">
        <div class="action-row">
            <a class="action-link" href="/checkout/voucher" target="_blank" rel="noopener">Ver voucher / ticket</a>
            <button type="button" onclick="window.print();">Imprimir resumen</button>
            <a class="action-link" href="/">Hacer nueva busqueda</a>
        </div>
    </section>
</div>
