<?php
declare(strict_types=1);

$bookings = $bookings ?? [];
$isAgencyScope = (bool) ($is_agency_scope ?? false);
$pendingDeleteRequests = isset($pending_delete_requests) && is_array($pending_delete_requests) ? $pending_delete_requests : [];
$bookingStatusLabels = \App\Core\StatusCatalog::bookingMap(true);
$paymentStatusLabels = \App\Core\StatusCatalog::paymentMap(true);
?>

<div class="page-header">
    <div>
        <h1>Dashboard</h1>
        <p style="margin: 6px 0 0; color: var(--muted);">
            <?= $isAgencyScope
                ? 'Vista rapida de tus reservas mas recientes.'
                : 'Vista rápida del panel con las reservas más recientes para supervisión operativa.' ?>
        </p>
    </div>
</div>

<?php if (empty($bookings)): ?>
    <div class="card">
        <p>No hay reservas aún.</p>
    </div>
<?php else: ?>
    <div class="card">
        <p style="margin-bottom: 14px;"><strong><?= $isAgencyScope ? 'Tus ultimas reservas' : 'Ultimas 20 reservas' ?></strong></p>
        <table class="admin-card-table">
            <thead>
                <tr>
                    <th>Codigo</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Pago</th>
                    <th>Creada</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td data-label="Codigo"><?= htmlspecialchars((string) $booking['booking_code'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td data-label="Cliente"><?= htmlspecialchars((string) $booking['customer_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td data-label="Total"><?= htmlspecialchars(number_format((float) $booking['price_total'], 2) . ' ' . (string) $booking['currency_code'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td data-label="Estado"><?= htmlspecialchars((string) ($bookingStatusLabels[(string) ($booking['status'] ?? '')] ?? ($booking['status'] ?? '')), ENT_QUOTES, 'UTF-8') ?></td>
                        <td data-label="Pago"><?= htmlspecialchars((string) ($paymentStatusLabels[(string) ($booking['payment_status'] ?? '')] ?? ($booking['payment_status'] ?? '')), ENT_QUOTES, 'UTF-8') ?></td>
                        <td data-label="Creada"><?= htmlspecialchars((string) $booking['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php if (!empty($pendingDeleteRequests)): ?>
<div class="card" style="margin-top: 18px; border-left: 4px solid var(--danger);">
    <p style="margin-bottom: 14px;"><strong style="color: var(--danger);">&#9888; Solicitudes de borrado pendientes (<?= count($pendingDeleteRequests) ?>)</strong></p>
    <table class="admin-card-table">
        <thead>
            <tr>
                <th>Código reserva</th>
                <th>Solicitó</th>
                <th>Motivo</th>
                <th>Fecha solicitud</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pendingDeleteRequests as $dr): ?>
                <tr>
                    <td data-label="Código"><a href="/admin/bookings/edit?id=<?= (int) ($dr['booking_id'] ?? 0) ?>"><?= htmlspecialchars((string) ($dr['booking_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a></td>
                    <td data-label="Solicitó"><?= htmlspecialchars((string) ($dr['requested_by_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td data-label="Motivo"><?= htmlspecialchars((string) ($dr['reason'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td data-label="Fecha"><?= htmlspecialchars((string) ($dr['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td data-label="Acción"><a href="/admin/bookings/edit?id=<?= (int) ($dr['booking_id'] ?? 0) ?>" class="btn btn-secondary" style="font-size: 12px; padding: 4px 10px;">Revisar</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
