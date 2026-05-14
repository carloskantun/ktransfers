<?php
declare(strict_types=1);

$bookings = isset($bookings) && is_array($bookings) ? $bookings : [];
$filters = isset($filters) && is_array($filters) ? $filters : [];
$brandLogo = trim((string) ($brand_logo ?? ''));
$brandName = trim((string) ($brand_name ?? 'Express Transfers'));
$brandName = $brandName !== '' ? $brandName : 'Express Transfers';
$dateFrom = (string) ($filters['date_from'] ?? '');
$dateTo = (string) ($filters['date_to'] ?? '');
$rangeLabel = ($dateFrom !== '' || $dateTo !== '') ? trim($dateFrom . ' a ' . $dateTo) : 'Todas las fechas filtradas';
$bookingStatusLabels = \App\Core\StatusCatalog::bookingMap(true);
$paymentStatusLabels = \App\Core\StatusCatalog::paymentMap(true);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reservas filtradas</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #eef2f7;
            color: #111827;
            font-family: Arial, Helvetica, sans-serif;
        }
        .actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            padding: 16px 18px 0;
        }
        .btn {
            min-height: 38px;
            padding: 9px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #fff;
            color: #0f3b75;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }
        .btn-primary { border-color: #0f3b75; background: #0f3b75; color: #fff; }
        .sheet {
            margin: 16px auto;
            padding: 18px;
            max-width: 1500px;
            background: #fff;
            border: 1px solid #cbd5e1;
        }
        .sheet-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 14px;
        }
        .brand-logo {
            display: block;
            width: 190px;
            max-height: 90px;
            object-fit: contain;
        }
        .brand-fallback {
            color: #0f3b75;
            font-size: 1.25rem;
            font-weight: 800;
        }
        .range-box { text-align: right; }
        .range-box h1 { margin: 0; color: #0f3b75; font-size: 1.35rem; }
        .range-box p { margin: 5px 0 0; color: #475569; font-weight: 700; }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 11px;
        }
        th, td {
            border: 1px solid #111827;
            padding: 5px 6px;
            text-align: left;
            vertical-align: top;
            overflow-wrap: anywhere;
        }
        th { background: #e7edf5; font-weight: 800; }
        @media print {
            @page { size: landscape; margin: 8mm; }
            body { background: #fff; }
            .actions { display: none; }
            .sheet { margin: 0; padding: 0; max-width: none; border: 0; }
            table { font-size: 9px; }
            th, td { padding: 3px 4px; }
        }
    </style>
</head>
<body>
    <div class="actions">
        <a class="btn" href="/admin/bookings">Volver</a>
        <button class="btn btn-primary" type="button" onclick="window.print();">Imprimir / guardar PDF</button>
    </div>

    <main class="sheet">
        <header class="sheet-head">
            <div>
                <?php if ($brandLogo !== ''): ?>
                    <img class="brand-logo" src="<?= htmlspecialchars($brandLogo, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($brandName, ENT_QUOTES, 'UTF-8') ?>">
                <?php else: ?>
                    <div class="brand-fallback"><?= htmlspecialchars($brandName, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </div>
            <div class="range-box">
                <h1>Reservas filtradas</h1>
                <p><?= htmlspecialchars($rangeLabel, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </header>

        <table>
            <thead>
                <tr>
                    <th>Codigo</th>
                    <th>Cliente</th>
                    <th>Contacto</th>
                    <th>Servicio</th>
                    <th>Ruta</th>
                    <th>Pax</th>
                    <th>Llegada</th>
                    <th>Salida</th>
                    <th>Vuelo</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Pago</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) ($booking['booking_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars(trim((string) ($booking['customer_name'] ?? '') . ' ' . (string) ($booking['customer_last_name'] ?? '')), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($booking['customer_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?><br><?= htmlspecialchars((string) ($booking['customer_phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($booking['service_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($booking['zone_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?><br><?= htmlspecialchars((string) ($booking['place_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($booking['total_pax'] ?? '0'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($booking['arrival_datetime'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($booking['departure_datetime'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars(trim((string) ($booking['airline'] ?? '') . ' ' . (string) ($booking['flight_number'] ?? '')), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars(number_format((float) ($booking['price_total'] ?? 0), 2) . ' ' . (string) ($booking['currency_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($bookingStatusLabels[(string) ($booking['status'] ?? '')] ?? ($booking['status'] ?? '')), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($paymentStatusLabels[(string) ($booking['payment_status'] ?? '')] ?? ($booking['payment_status'] ?? '')), ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($bookings)): ?>
                    <tr>
                        <td colspan="12">No hay reservas para los filtros actuales.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
