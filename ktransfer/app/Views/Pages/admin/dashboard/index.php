<?php
declare(strict_types=1);

$bookings = $bookings ?? [];
?>

<div class="page-header">
    <div>
        <h1>Dashboard</h1>
        <p style="margin: 6px 0 0; color: var(--muted);">Vista rápida del panel con las reservas más recientes para supervisión operativa.</p>
    </div>
</div>

<?php if (empty($bookings)): ?>
    <div class="card">
        <p>No hay reservas aún.</p>
    </div>
<?php else: ?>
    <div class="card">
        <p style="margin-bottom: 14px;"><strong>Ultimas 20 reservas</strong></p>
        <table>
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
                        <td><?= htmlspecialchars((string) $booking['booking_code'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $booking['customer_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars(number_format((float) $booking['price_total'], 2) . ' ' . (string) $booking['currency_code'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $booking['status'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $booking['payment_status'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) $booking['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
