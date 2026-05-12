<?php
declare(strict_types=1);

$agendaItems = $agenda_items ?? [];
$startDate = (string) ($start_date ?? '');
$endDate = (string) ($end_date ?? '');
$brandLogo = trim((string) ($brand_logo ?? ''));
$exportQuery = (string) ($export_query ?? '');
$defaultAirportLabel = \App\Http\Controllers\Admin\OperationsAgendaController::defaultAirportLabel();

$resolveOrigin = static function (array $item) use ($defaultAirportLabel): string {
    $customOrigin = trim((string) ($item['origin_name'] ?? ''));
    if ($customOrigin !== '') {
        return $customOrigin;
    }

    if ((string) ($item['service_leg'] ?? 'ARRIVAL') === 'DEPARTURE') {
        $placeName = trim((string) ($item['place_name'] ?? ''));
        return $placeName !== '' ? $placeName : $defaultAirportLabel;
    }

    return $defaultAirportLabel;
};

$resolveDestination = static function (array $item) use ($defaultAirportLabel): string {
    $customDestination = trim((string) ($item['destination_name'] ?? ''));
    if ($customDestination !== '') {
        return $customDestination;
    }

    if ((string) ($item['service_leg'] ?? 'ARRIVAL') === 'DEPARTURE') {
        return $defaultAirportLabel;
    }

    $placeName = trim((string) ($item['place_name'] ?? ''));
    return $placeName !== '' ? $placeName : $defaultAirportLabel;
};

$resolveOperationLabel = static function (array $item): string {
    if ((string) ($item['operation_type'] ?? 'AIRPORT') === 'INTERHOTEL') {
        return 'INTER HOTEL';
    }

    return ((string) ($item['service_leg'] ?? 'ARRIVAL') === 'DEPARTURE') ? 'SALIDA' : 'LLEGADA';
};
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Orden de servicio</title>
    <style>
        * {
            box-sizing: border-box;
        }
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
            display: inline-flex;
            align-items: center;
            justify-content: center;
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
        .btn-primary {
            border-color: #0f3b75;
            background: #0f3b75;
            color: #fff;
        }
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
        .range-box {
            text-align: right;
        }
        .range-box h1 {
            margin: 0;
            color: #0f3b75;
            font-size: 1.35rem;
        }
        .range-box p {
            margin: 5px 0 0;
            color: #475569;
            font-weight: 700;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 11px;
        }
        th,
        td {
            border: 1px solid #111827;
            padding: 5px 6px;
            text-align: left;
            vertical-align: top;
            overflow-wrap: anywhere;
        }
        th {
            background: #e7edf5;
            color: #111827;
            font-weight: 800;
        }
        .w-date { width: 72px; }
        .w-hour { width: 54px; }
        .w-short { width: 76px; }
        .w-code { width: 108px; }
        .w-pax { width: 42px; }
        .empty {
            padding: 22px;
            border: 1px solid #cbd5e1;
            color: #475569;
            text-align: center;
        }
        @media print {
            @page {
                size: landscape;
                margin: 8mm;
            }
            body {
                background: #fff;
            }
            .actions {
                display: none;
            }
            .sheet {
                margin: 0;
                padding: 0;
                max-width: none;
                border: 0;
            }
            table {
                font-size: 9px;
            }
            th,
            td {
                padding: 3px 4px;
            }
        }
    </style>
</head>
<body>
    <div class="actions">
        <a class="btn" href="/admin/operations/agenda">Volver</a>
        <a class="btn" href="/admin/operations/agenda/export?<?= htmlspecialchars($exportQuery, ENT_QUOTES, 'UTF-8') ?>">Descargar CSV</a>
        <button class="btn btn-primary" type="button" onclick="window.print();">Imprimir / guardar PDF</button>
    </div>

    <main class="sheet">
        <header class="sheet-head">
            <div>
                <?php if ($brandLogo !== ''): ?>
                    <img class="brand-logo" src="<?= htmlspecialchars($brandLogo, ENT_QUOTES, 'UTF-8') ?>" alt="Logo">
                <?php else: ?>
                    <div class="brand-fallback">Express Transfer Cancun</div>
                <?php endif; ?>
            </div>
            <div class="range-box">
                <h1>Orden de servicio</h1>
                <p><?= htmlspecialchars($startDate . ' a ' . $endDate, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </header>

        <?php if (empty($agendaItems)): ?>
            <div class="empty">No hay servicios para los filtros actuales.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th class="w-date">Fecha</th>
                        <th class="w-hour">Hora</th>
                        <th class="w-short">Unidad</th>
                        <th class="w-short">Operador</th>
                        <th class="w-short">Agencia</th>
                        <th class="w-short">Proveedor</th>
                        <th class="w-short">Servicio</th>
                        <th class="w-code">No. Servicio</th>
                        <th class="w-short">Terminal</th>
                        <th class="w-short">Vuelo</th>
                        <th>Cliente</th>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th class="w-pax">Pax</th>
                        <th class="w-short">Balance</th>
                        <th>Nota</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($agendaItems as $item): ?>
                        <?php $serviceDatetime = (string) ($item['service_datetime'] ?? 'now'); ?>
                        <tr>
                            <td><?= htmlspecialchars(date('d/m/Y', strtotime($serviceDatetime)), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars(date('H:i', strtotime($serviceDatetime)), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($item['vehicle_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($item['operator_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($item['agency_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($item['provider_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($resolveOperationLabel($item), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($item['booking_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($item['terminal'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars(trim((string) ($item['airline'] ?? '') . ' ' . (string) ($item['flight_number'] ?? '')), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars(trim((string) ($item['customer_name'] ?? '') . ' ' . (string) ($item['customer_last_name'] ?? '')), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($resolveOrigin($item), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($resolveDestination($item), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($item['total_pax'] ?? '0'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars(number_format((float) ($item['balance_due'] ?? 0), 2) . ' ' . (string) ($item['currency_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($item['work_order_notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</body>
</html>
