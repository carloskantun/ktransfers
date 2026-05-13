<?php
declare(strict_types=1);

use App\Services\HomeContentService;

$booking = $booking ?? [];
$documentType = (string) ($document_type ?? 'voucher');
$brandLogo = trim((string) ($brand_logo ?? ''));
$backUrl = trim((string) ($back_url ?? '/admin/bookings'));
$isRoundTrip = (string) ($booking['trip_type'] ?? '') === 'ROUND_TRIP';

$homeContent = (new HomeContentService())->getHomePageContent();
$voucherTheme = is_array($homeContent['voucher_theme'] ?? null) ? $homeContent['voucher_theme'] : [];
$normalizeHex = static function ($value, string $fallback): string {
    $value = strtoupper(trim((string) $value));
    if (preg_match('/^#[0-9A-F]{6}$/', $value) === 1) {
        return $value;
    }

    return $fallback;
};
$voucherPrimary = $normalizeHex($voucherTheme['primary'] ?? '', '#17679A');
$voucherSecondary = $normalizeHex($voucherTheme['secondary'] ?? '', '#0D4F79');
$voucherLine = $normalizeHex($voucherTheme['line'] ?? '', '#1F2937');

if ($brandLogo === '' && is_file(dirname(__DIR__, 6) . '/public_html/assets/expresslogo-300x122.png.webp')) {
    $brandLogo = '/assets/expresslogo-300x122.png.webp';
}
$qrPath = is_file(dirname(__DIR__, 6) . '/public_html/assets/qr_spectial.png') ? '/assets/qr_spectial.png' : '';

$h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$formatDate = static function ($value): string {
    $value = trim((string) $value);
    return $value !== '' ? date('d/m/Y', strtotime($value)) : '';
};
$formatTime = static function ($value): string {
    $value = trim((string) $value);
    return $value !== '' ? date('H:i', strtotime($value)) : '';
};
$money = static function ($amount, $currency): string {
    return '$' . number_format((float) $amount, 2) . ' ' . strtoupper((string) $currency);
};

$customerName = trim((string) ($booking['customer_name'] ?? '') . ' ' . (string) ($booking['customer_last_name'] ?? ''));
$originName = trim((string) ($booking['origin_name'] ?? ''));
$destinationName = trim((string) ($booking['destination_name'] ?? ''));
$placeName = trim((string) ($booking['place_name'] ?? ''));
if ($originName === '') {
    $originName = (string) (($booking['direction'] ?? '') === 'DESTINATION_TO_AIRPORT' ? $placeName : 'Aeropuerto de Cancun');
}
if ($destinationName === '') {
    $destinationName = (string) (($booking['direction'] ?? '') === 'DESTINATION_TO_AIRPORT' ? 'Aeropuerto de Cancun' : $placeName);
}

$flightLabel = trim((string) ($booking['airline'] ?? '') . ' ' . (string) ($booking['flight_number'] ?? ''));
$unitLabel = trim((string) ($booking['vehicle_name'] ?? ''));
if ($unitLabel === '') {
    $unitLabel = trim((string) ($booking['service_type_name'] ?? ''));
}
if ($unitLabel === '') {
    $unitLabel = 'VAN';
}

$outboundDateTime = trim((string) ($booking['arrival_datetime'] ?? '')) !== ''
    ? (string) $booking['arrival_datetime']
    : (string) ($booking['departure_datetime'] ?? '');
$returnDateTime = (string) ($booking['departure_datetime'] ?? '');

$outboundTrip = [
    'title' => 'VIAJE / TRIP',
    'date' => $formatDate($outboundDateTime),
    'time' => $formatTime($outboundDateTime),
    'origin' => $originName,
    'destination' => $destinationName,
    'flight' => $flightLabel,
    'terminal' => (string) ($booking['terminal'] ?? ''),
    'price' => $money($booking['price_total'] ?? 0, $booking['currency_code'] ?? ''),
    'unit' => $unitLabel,
];
$returnTrip = [
    'title' => 'VIAJE RETORNO / RETURN TRIP',
    'date' => $formatDate($returnDateTime),
    'time' => $formatTime($returnDateTime),
    'origin' => $destinationName,
    'destination' => $originName,
    'flight' => $flightLabel,
    'terminal' => (string) ($booking['terminal'] ?? ''),
    'price' => 'Incluido',
    'unit' => $unitLabel,
];

$paymentLabels = \App\Core\StatusCatalog::paymentMap(true);
$paymentStatus = (string) ($booking['payment_status'] ?? 'UNPAID');
$remarks = trim((string) ($booking['pickup_notes'] ?? ''));
$additional = trim((string) ($booking['work_order_notes'] ?? ''));
$bookingCode = (string) ($booking['booking_code'] ?? '');
$documentLabel = $documentType === 'service_order' ? 'ORDEN DE SERVICIO/SERVICE ORDER' : 'ORDEN DE SERVICIO/SERVICE ORDER';

$renderTrip = static function (array $trip) use ($h): void {
    $rows = [
        'Fecha / Date:' => $trip['date'],
        'Hora / Hour:' => $trip['time'],
        'Origen / Origin:' => $trip['origin'],
        'Destino / Destination:' => $trip['destination'],
        'Vuelo / Flight:' => $trip['flight'],
        'Terminal:' => $trip['terminal'],
        'PRECIO / PRICE:' => $trip['price'],
        'TIPO DE UNIDAD:' => $trip['unit'],
    ];
    ?>
    <table class="trip-table">
        <thead>
            <tr><th colspan="2"><?= $h($trip['title']) ?></th></tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $label => $value): ?>
                <tr>
                    <td class="label"><?= $h($label) ?></td>
                    <td><?= $h($value !== '' ? $value : '-') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
};
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $h('Voucher ' . $bookingCode) ?></title>
    <style>
        :root {
            --blue: <?= $h($voucherPrimary) ?>;
            --blue-dark: <?= $h($voucherSecondary) ?>;
            --line: <?= $h($voucherLine) ?>;
            --muted: #5b6675;
            --sheet: #ffffff;
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            background: #dfe7ef;
            color: #111827;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
        }
        .print-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            width: min(960px, calc(100% - 28px));
            margin: 16px auto 0;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            padding: 8px 14px;
            border: 1px solid #b8c4d1;
            border-radius: 6px;
            background: #fff;
            color: var(--blue-dark);
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }
        .btn-primary {
            border-color: var(--blue);
            background: var(--blue);
            color: #fff;
        }
        .sheet {
            position: relative;
            overflow: hidden;
            width: min(960px, calc(100% - 28px));
            min-height: 720px;
            margin: 16px auto 24px;
            padding: 26px 30px 0;
            border: 1px solid #c7d2de;
            background: var(--sheet);
            box-shadow: 0 18px 44px rgba(15, 23, 42, 0.18);
        }
        .voucher-head {
            display: grid;
            grid-template-columns: 190px 1fr 145px;
            gap: 18px;
            align-items: center;
            min-height: 92px;
        }
        .brand-logo {
            display: block;
            width: 178px;
            max-height: 82px;
            object-fit: contain;
        }
        .brand-fallback {
            color: var(--blue-dark);
            font-size: 18px;
            font-weight: 800;
            line-height: 1.1;
        }
        .title-box {
            text-align: center;
        }
        .title-box h1 {
            margin: 0;
            color: var(--blue-dark);
            font-size: 22px;
            letter-spacing: 0;
        }
        .title-box p {
            margin: 8px 0 0;
            color: var(--muted);
            font-weight: 700;
        }
        .code-box {
            padding: 9px 10px;
            border: 2px solid var(--blue);
            color: var(--blue-dark);
            font-size: 15px;
            font-weight: 800;
            text-align: center;
        }
        .section-title {
            margin-top: 20px;
            padding: 8px 10px;
            background: var(--blue);
            color: #fff;
            font-size: 14px;
            font-weight: 800;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .customer-table td,
        .trip-table td,
        .trip-table th,
        .notes-table td {
            border: 1px solid var(--line);
        }
        .customer-table td,
        .notes-table td {
            height: 36px;
            padding: 8px 10px;
        }
        .customer-table .label,
        .trip-table .label {
            width: 38%;
            color: #111827;
            font-weight: 800;
        }
        .trip-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            margin-top: 18px;
        }
        .trip-grid.one-way {
            grid-template-columns: 1fr;
        }
        .trip-table th {
            padding: 8px 10px;
            background: var(--blue);
            color: #fff;
            font-size: 14px;
            text-align: center;
        }
        .trip-table td {
            height: 35px;
            padding: 8px 10px;
            vertical-align: top;
        }
        .notes-table .label {
            width: 31%;
            background: #eef5fa;
            color: var(--blue-dark);
            font-weight: 800;
        }
        .notes-table .tall {
            min-height: 52px;
        }
        .footer {
            position: relative;
            margin: 52px -30px 0;
            min-height: 126px;
            padding: 34px 32px 16px;
            background: linear-gradient(180deg, var(--blue) 0%, var(--blue-dark) 100%);
            color: #fff;
            break-inside: avoid;
        }
        .footer::before,
        .footer::after {
            content: "";
            position: absolute;
            right: -7%;
            left: -7%;
            height: 112px;
            border-radius: 50% 50% 0 0;
            z-index: 0;
        }
        .footer::before {
            top: -36px;
            background: var(--blue);
        }
        .footer::after {
            bottom: -62px;
            background: var(--blue-dark);
            opacity: 0.78;
        }
        .footer-inner {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 98px 1fr;
            gap: 20px;
            align-items: end;
        }
        .qr-box {
            display: grid;
            place-items: center;
            width: 86px;
            height: 86px;
            padding: 6px;
            border: 5px solid #fff;
            background: #fff;
            color: var(--blue-dark);
            font-size: 13px;
            font-weight: 800;
        }
        .qr-box img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .footer-copy {
            display: grid;
            gap: 5px;
            font-size: 13px;
            font-weight: 700;
            line-height: 1.35;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.28);
        }
        .thanks {
            color: #fff;
            font-size: 16px;
            font-weight: 900;
        }
        @media (max-width: 760px) {
            .print-actions {
                justify-content: stretch;
            }
            .print-actions .btn {
                flex: 1;
            }
            .sheet {
                padding: 18px 16px 0;
            }
            .voucher-head,
            .trip-grid,
            .footer-inner {
                grid-template-columns: 1fr;
            }
            .footer {
                margin: 46px -16px 0;
                padding: 34px 18px 18px;
            }
            .title-box {
                text-align: left;
            }
            .code-box {
                text-align: left;
            }
        }
        @media print {
            @page {
                size: letter;
                margin: 10mm;
            }
            body {
                background: #fff;
            }
            .print-actions {
                display: none;
            }
            .sheet {
                width: 100%;
                min-height: 0;
                margin: 0;
                padding: 0;
                border: 0;
                overflow: visible;
                box-shadow: none;
            }
            .voucher-head {
                grid-template-columns: 180px 1fr 138px;
            }
            .trip-grid {
                break-inside: avoid;
            }
            .footer {
                position: relative;
                margin: 9mm 0 0;
                min-height: 32mm;
                padding: 9mm 8mm 4mm;
            }
            .footer::before {
                top: -8mm;
                height: 24mm;
            }
            .footer::after {
                bottom: -14mm;
                height: 24mm;
            }
        }
    </style>
</head>
<body>
    <div class="print-actions">
        <a class="btn" href="<?= $h($backUrl) ?>">Volver</a>
        <button class="btn btn-primary" type="button" onclick="window.print();">Imprimir / guardar PDF</button>
    </div>

    <main class="sheet">
        <header class="voucher-head">
            <div>
                <?php if ($brandLogo !== ''): ?>
                    <img class="brand-logo" src="<?= $h($brandLogo) ?>" alt="Logo">
                <?php else: ?>
                    <div class="brand-fallback">Express Transfer Cancun</div>
                <?php endif; ?>
            </div>
            <div class="title-box">
                <h1><?= $h($documentLabel) ?></h1>
                <p><?= $h($isRoundTrip ? 'Round trip' : 'One way') ?></p>
            </div>
            <div class="code-box"><?= $h($bookingCode) ?></div>
        </header>

        <div class="section-title">CLIENTE / CUSTOMER</div>
        <table class="customer-table">
            <tbody>
                <tr>
                    <td class="label">NOMBRE / NAME:</td>
                    <td><?= $h($customerName !== '' ? $customerName : '-') ?></td>
                </tr>
                <tr>
                    <td class="label">TELÉFONO / PHONE:</td>
                    <td><?= $h((string) ($booking['customer_phone'] ?? '-')) ?></td>
                </tr>
                <tr>
                    <td class="label">NÚMERO DE PASAJEROS / PAX:</td>
                    <td><?= $h((string) ($booking['total_pax'] ?? '0')) ?></td>
                </tr>
            </tbody>
        </table>

        <section class="trip-grid<?= $isRoundTrip ? '' : ' one-way' ?>">
            <?php $renderTrip($outboundTrip); ?>
            <?php if ($isRoundTrip): ?>
                <?php $renderTrip($returnTrip); ?>
            <?php endif; ?>
        </section>

        <div class="section-title">INFORMACIÓN ADICIONAL / ADDITIONAL INFORMATION</div>
        <table class="notes-table">
            <tbody>
                <tr>
                    <td class="label">OBSERVACIONES / REMARKS</td>
                    <td class="tall"><?= $h($remarks !== '' ? $remarks : '-') ?></td>
                </tr>
                <tr>
                    <td class="label">ADICIONALES / ADDITIONAL SERVICES</td>
                    <td class="tall"><?= $h($additional !== '' ? $additional : '-') ?></td>
                </tr>
                <tr>
                    <td class="label">ESTADO DE PAGO / PAYMENT STATUS</td>
                    <td><?= $h($paymentLabels[$paymentStatus] ?? $paymentStatus) ?></td>
                </tr>
            </tbody>
        </table>

        <footer class="footer">
            <div class="footer-inner">
                <div class="qr-box">
                    <?php if ($qrPath !== ''): ?>
                        <img src="<?= $h($qrPath) ?>" alt="QR">
                    <?php else: ?>
                        QR
                    <?php endif; ?>
                </div>
                <div class="footer-copy">
                    <div class="thanks">GRACIAS POR SU PREFERENCIA / THANK YOU FOR YOUR PREFERENCE</div>
                    <div>Reservation phone: +52 998 756 4000 &nbsp; | &nbsp; +52 998 222 3778</div>
                    <div>info@expresstransfercancun.com &nbsp; | &nbsp; expresstransfercancun.com</div>
                </div>
            </div>
        </footer>
    </main>
</body>
</html>
