<?php
declare(strict_types=1);

$agendaItems = $agenda_items ?? [];
$operators = $operators ?? [];
$providers = $providers ?? [];
$vehicles = $vehicles ?? [];
$startDate = (string) ($start_date ?? gmdate('Y-m-d'));
$endDate = (string) ($end_date ?? gmdate('Y-m-d'));
$rangePreset = (string) ($range_preset ?? 'THIS_WEEK');
$selectedOperatorId = $selected_operator_id ?? null;
$selectedStatus = $selected_status ?? null;
$isOperatorScope = (bool) ($is_operator_scope ?? false);
$statusOptions = $status_options ?? [];
$modeOptions = $mode_options ?? [];
$operatorBookingStatuses = $operator_booking_statuses ?? [];
$saved = (bool) ($saved ?? false);
$agendaStats = $agenda_stats ?? [];
$csrfToken = (string) ($csrf_token ?? '');
$agendaActionQuery = http_build_query([
    'preset' => $rangePreset,
    'start_date' => $startDate,
    'end_date' => $endDate,
    'operator_user_id' => $selectedOperatorId,
    'service_status' => $selectedStatus,
]);
$defaultAirportLabel = \App\Http\Controllers\Admin\OperationsAgendaController::defaultAirportLabel();
$serviceTypeLabels = [
    'ARRIVAL' => 'LLEGADA',
    'DEPARTURE' => 'SALIDA',
];
$serviceStatusLabels = \App\Core\StatusCatalog::serviceMap(true);
$resolveOperationLabel = static function (array $item) use ($serviceTypeLabels): string {
    if ((string) ($item['operation_type'] ?? 'AIRPORT') === 'INTERHOTEL') {
        return 'INTER HOTEL';
    }

    return $serviceTypeLabels[(string) ($item['service_leg'] ?? 'ARRIVAL')] ?? (string) ($item['service_leg'] ?? 'ARRIVAL');
};
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

$renderAgendaEditor = static function (
    array $item,
    int $bookingId,
    string $workDate,
    string $mode,
    string $currentBookingStatus,
    string $originDisplay,
    string $destinationDisplay,
    string $context
) use (
    $csrfToken,
    $rangePreset,
    $startDate,
    $endDate,
    $selectedOperatorId,
    $selectedStatus,
    $isOperatorScope,
    $modeOptions,
    $operators,
    $providers,
    $vehicles,
    $statusOptions
): void {
    $editorId = 'agenda-edit-' . $context . '-' . $bookingId;
    $isFinalOperatorStatus = in_array($currentBookingStatus, ['COMPLETED', 'NO_SHOW', 'CANCELLED'], true);
    ?>
    <?php if ($isOperatorScope): ?>
        <form method="post" action="/admin/operations/agenda" class="agenda-status-actions">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="booking_id" value="<?= $bookingId ?>">
            <input type="hidden" name="work_date" value="<?= htmlspecialchars($workDate, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="preset" value="<?= htmlspecialchars($rangePreset, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="start_date" value="<?= htmlspecialchars($startDate, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="end_date" value="<?= htmlspecialchars($endDate, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="filter_operator_user_id" value="<?= htmlspecialchars((string) ($selectedOperatorId ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="filter_service_status" value="<?= htmlspecialchars((string) ($selectedStatus ?? ''), ENT_QUOTES, 'UTF-8') ?>">

            <span class="agenda-subtle">
                Estado actual: <?= htmlspecialchars((string) ($serviceStatusLabels[(string) ($item['service_status'] ?? 'PENDING')] ?? ($item['service_status'] ?? 'PENDING')), ENT_QUOTES, 'UTF-8') ?>
                <?= $isFinalOperatorStatus ? ' - resultado cerrado' : '' ?>
            </span>
            <button class="btn btn-primary" type="submit" name="operator_booking_status" value="COMPLETED" <?= $isFinalOperatorStatus ? 'disabled' : '' ?>>Servicio hecho</button>
            <button class="btn" type="submit" name="operator_booking_status" value="NO_SHOW" <?= $isFinalOperatorStatus ? 'disabled' : '' ?>>No show</button>
        </form>
    <?php else: ?>
        <div class="agenda-edit-summary">
            <span class="agenda-subtle">Estado: <?= htmlspecialchars((string) ($serviceStatusLabels[(string) ($item['service_status'] ?? 'PENDING')] ?? ($item['service_status'] ?? 'PENDING')), ENT_QUOTES, 'UTF-8') ?></span>
            <span class="agenda-subtle">Modo: <?= htmlspecialchars($mode, ENT_QUOTES, 'UTF-8') ?></span>
            <input class="agenda-edit-toggle" type="checkbox" id="<?= htmlspecialchars($editorId, ENT_QUOTES, 'UTF-8') ?>">
            <label class="btn btn-secondary" for="<?= htmlspecialchars($editorId, ENT_QUOTES, 'UTF-8') ?>">Editar</label>
            <label class="agenda-edit-backdrop" for="<?= htmlspecialchars($editorId, ENT_QUOTES, 'UTF-8') ?>" aria-label="Cerrar editor"></label>
            <div class="agenda-edit-modal" role="dialog" aria-modal="true" aria-labelledby="<?= htmlspecialchars($editorId, ENT_QUOTES, 'UTF-8') ?>-title">
                <div class="agenda-edit-head">
                    <div>
                        <h3 id="<?= htmlspecialchars($editorId, ENT_QUOTES, 'UTF-8') ?>-title">Editar operacion</h3>
                        <p class="agenda-subtle" style="margin:4px 0 0;">
                            <?= htmlspecialchars((string) ($item['booking_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    </div>
                    <label class="agenda-edit-close" for="<?= htmlspecialchars($editorId, ENT_QUOTES, 'UTF-8') ?>">Cerrar</label>
                </div>

                <form method="post" action="/admin/operations/agenda" class="agenda-form-grid">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="booking_id" value="<?= $bookingId ?>">
                    <input type="hidden" name="work_date" value="<?= htmlspecialchars($workDate, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="preset" value="<?= htmlspecialchars($rangePreset, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="start_date" value="<?= htmlspecialchars($startDate, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="end_date" value="<?= htmlspecialchars($endDate, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="filter_operator_user_id" value="<?= htmlspecialchars((string) ($selectedOperatorId ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="filter_service_status" value="<?= htmlspecialchars((string) ($selectedStatus ?? ''), ENT_QUOTES, 'UTF-8') ?>">

                    <div class="form-group">
                        <label>Modo de asignacion</label>
                        <select name="mode">
                            <?php foreach ($modeOptions as $modeOption): ?>
                                <option value="<?= htmlspecialchars((string) $modeOption, ENT_QUOTES, 'UTF-8') ?>" <?= $mode === $modeOption ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string) $modeOption, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Estado operativo</label>
                        <select name="service_status">
                            <?php foreach ($statusOptions as $statusOption): ?>
                                <option value="<?= htmlspecialchars((string) $statusOption, ENT_QUOTES, 'UTF-8') ?>" <?= ((string) ($item['service_status'] ?? 'PENDING') === $statusOption) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string) ($serviceStatusLabels[(string) $statusOption] ?? $statusOption), ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Operador</label>
                        <select name="operator_user_id">
                            <option value="">Sin asignar</option>
                            <?php foreach ($operators as $operator): ?>
                                <?php $operatorId = (int) ($operator['id'] ?? 0); ?>
                                <option value="<?= $operatorId ?>" <?= ((int) ($item['operator_user_id'] ?? 0) === $operatorId) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string) ($operator['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Proveedor</label>
                        <select name="provider_id">
                            <option value="">Sin proveedor</option>
                            <?php foreach ($providers as $provider): ?>
                                <?php $providerId = (int) ($provider['id'] ?? 0); ?>
                                <option value="<?= $providerId ?>" <?= ((int) ($item['provider_id'] ?? 0) === $providerId) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string) ($provider['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Unidad</label>
                        <select name="vehicle_id">
                            <option value="">Sin unidad</option>
                            <?php foreach ($vehicles as $vehicle): ?>
                                <?php $vehicleId = (int) ($vehicle['id'] ?? 0); ?>
                                <option value="<?= $vehicleId ?>" <?= ((int) ($item['vehicle_id'] ?? 0) === $vehicleId) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string) ($vehicle['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Agencia</label>
                        <input type="text" name="agency_name"
                            value="<?= htmlspecialchars((string) ($item['agency_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                            <?= (int) ($item['agency_provider_id'] ?? 0) > 0 ? 'readonly' : '' ?>>
                        <?php if ((int) ($item['agency_provider_id'] ?? 0) > 0): ?>
                            <span class="admin-page-note">Agencia vinculada al usuario que creó la reserva, no puede editarse aquí.</span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Terminal</label>
                        <input type="text" name="terminal" value="<?= htmlspecialchars((string) ($item['terminal'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="form-group">
                        <label>Origen</label>
                        <input type="text" name="origin_name" value="<?= htmlspecialchars(trim((string) ($item['origin_name'] ?? '')) !== '' ? (string) $item['origin_name'] : $originDisplay, ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="form-group">
                        <label>Destino</label>
                        <input type="text" name="destination_name" value="<?= htmlspecialchars(trim((string) ($item['destination_name'] ?? '')) !== '' ? (string) $item['destination_name'] : $destinationDisplay, ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="form-group">
                        <label>Nota operativa</label>
                        <textarea name="work_order_notes" rows="3"><?= htmlspecialchars((string) ($item['work_order_notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
    <?php
};
?>
<style>
    .agenda-toolbar {
        display: flex;
        gap: 12px;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
    }
    .agenda-grid {
        display: grid;
        gap: 18px;
    }
    .agenda-filter-toggle,
    .agenda-filter-label,
    .agenda-operator-toggle,
    .agenda-operator-label {
        display: none;
    }
    .agenda-filters {
        display: grid;
        gap: 14px;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        align-items: end;
    }
    .agenda-stats {
        display: grid;
        gap: 14px;
        grid-template-columns: repeat(5, minmax(0, 1fr));
    }
    .agenda-stat {
        padding: 16px;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: linear-gradient(180deg, #fff, #f8fbff);
    }
    .agenda-stat span {
        display: block;
        color: var(--muted);
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }
    .agenda-stat strong {
        display: block;
        margin-top: 8px;
        font-size: 1.6rem;
    }
    .operator-summary {
        display: grid;
        gap: 12px;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .operator-card {
        padding: 16px;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: #fff;
    }
    .operator-card h3 {
        margin: 0 0 10px;
        font-size: 1rem;
    }
    .operator-card p {
        margin: 0;
        color: var(--muted);
        line-height: 1.5;
    }
    .agenda-booking {
        display: grid;
        gap: 4px;
    }
    .agenda-subtle {
        color: var(--muted);
        font-size: 0.84rem;
        line-height: 1.45;
    }
    .agenda-form-grid {
        display: grid;
        gap: 10px;
    }
    .agenda-table td {
        vertical-align: top;
    }
    .agenda-table-wrapper {
        overflow-x: auto;
    }
    .agenda-edit-cell {
        min-width: 190px;
    }
    .agenda-status-actions {
        display: grid;
        gap: 8px;
    }
    .agenda-status-actions .btn {
        width: 100%;
    }
    .agenda-edit-summary {
        display: grid;
        gap: 8px;
    }
    .agenda-edit-toggle {
        position: fixed;
        opacity: 0;
        pointer-events: none;
    }
    .agenda-edit-backdrop,
    .agenda-edit-modal {
        display: none;
    }
    .agenda-edit-backdrop {
        position: fixed;
        inset: 0;
        z-index: 80;
        background: rgba(15, 23, 42, 0.48);
    }
    .agenda-edit-modal {
        position: fixed;
        top: 50%;
        left: 50%;
        z-index: 90;
        width: min(680px, calc(100vw - 28px));
        max-height: min(86vh, 820px);
        overflow: auto;
        transform: translate(-50%, -50%);
        padding: 18px;
        border: 1px solid var(--border);
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 26px 80px rgba(15, 23, 42, 0.28);
    }
    .agenda-edit-toggle:checked ~ .agenda-edit-backdrop,
    .agenda-edit-toggle:checked ~ .agenda-edit-modal {
        display: block;
    }
    .agenda-edit-head {
        display: flex;
        gap: 14px;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 14px;
    }
    .agenda-edit-head h3 {
        margin: 0;
        font-size: 1.1rem;
    }
    .agenda-edit-close {
        border: 0;
        border-radius: 999px;
        padding: 7px 10px;
        background: #e2e8f0;
        color: #1f2937;
        font-weight: 800;
        cursor: pointer;
    }
    .agenda-edit-modal .agenda-form-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .agenda-edit-modal .form-group:last-of-type,
    .agenda-edit-modal .form-actions {
        grid-column: 1 / -1;
    }
    .agenda-mobile-list {
        display: none;
    }
    .agenda-drawer-toggle {
        position: fixed;
        opacity: 0;
        pointer-events: none;
    }
    .agenda-mobile-card,
    .agenda-drawer-panel,
    .agenda-drawer-backdrop {
        display: none;
    }
    .agenda-print-sheet {
        display: none;
    }
    .agenda-print-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }
    .agenda-print-table th,
    .agenda-print-table td {
        border: 1px solid #1d1d1d;
        padding: 6px;
        text-align: left;
        vertical-align: top;
    }
    .agenda-print-table th {
        background: #f4f6f8;
    }
    @media (max-width: 1100px) {
        .agenda-filters,
        .agenda-stats,
        .operator-summary {
            grid-template-columns: 1fr 1fr;
        }
    }
    @media (max-width: 900px) {
        .agenda-filters,
        .agenda-stats,
        .operator-summary {
            grid-template-columns: 1fr;
        }
    }
    @media (max-width: 760px) {
        .agenda-page-header {
            margin-bottom: 8px;
        }
        .agenda-page-header p {
            display: none;
        }
        .agenda-toolbar {
            display: none;
        }
        .agenda-grid {
            gap: 10px;
        }
        .agenda-filter-card {
            order: 1;
            padding: 0;
            overflow: hidden;
        }
        .agenda-services-card {
            order: 2;
            padding: 0;
            border: 0;
            background: transparent;
        }
        .agenda-stats {
            order: 3;
        }
        .agenda-operator-card {
            order: 4;
        }
        .agenda-filter-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 12px 14px;
            color: #0f172a;
            font-weight: 800;
            cursor: pointer;
        }
        .agenda-filter-label::after {
            content: "Abrir";
            color: var(--primary);
            font-size: 0.82rem;
        }
        .agenda-filter-toggle:checked + .agenda-filter-label::after {
            content: "Cerrar";
        }
        .agenda-filter-summary {
            display: block;
            color: var(--muted);
            font-size: 0.82rem;
            font-weight: 600;
        }
        .agenda-filter-card .agenda-filters {
            display: none;
            padding: 0 14px 14px;
            border-top: 1px solid #eef3f8;
        }
        .agenda-filter-toggle:checked ~ .agenda-filters {
            display: grid;
        }
        .agenda-filters {
            gap: 8px;
        }
        .agenda-filters .form-group {
            margin-bottom: 0;
        }
        .agenda-stat,
        .operator-card {
            border-radius: 10px;
            padding: 9px 10px;
        }
        .agenda-stats {
            grid-template-columns: repeat(5, minmax(74px, 1fr));
            gap: 8px;
            overflow-x: auto;
            padding-bottom: 2px;
        }
        .agenda-stat span {
            font-size: 0.66rem;
            letter-spacing: 0.04em;
            white-space: nowrap;
        }
        .agenda-stat strong {
            margin-top: 3px;
            font-size: 1rem;
        }
        .agenda-operator-card {
            padding: 0;
            overflow: hidden;
        }
        .agenda-operator-toggle,
        .agenda-operator-label {
            display: block;
        }
        .agenda-operator-toggle {
            position: fixed;
            opacity: 0;
            pointer-events: none;
        }
        .agenda-operator-label {
            padding: 12px 14px;
            font-weight: 800;
            cursor: pointer;
        }
        .agenda-operator-label::after {
            content: "Ver";
            float: right;
            color: var(--primary);
            font-size: 0.82rem;
        }
        .agenda-operator-toggle:checked + .agenda-operator-label::after {
            content: "Ocultar";
        }
        .agenda-operator-body {
            display: none;
            padding: 0 14px 14px;
            border-top: 1px solid #eef3f8;
        }
        .agenda-operator-toggle:checked ~ .agenda-operator-body {
            display: block;
        }
        .agenda-operator-body .page-header {
            display: none;
        }
        .agenda-table-wrapper {
            display: none;
        }
        .agenda-mobile-list {
            display: grid;
            gap: 10px;
        }
        .agenda-mobile-card {
            display: grid;
            gap: 8px;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 12px;
            background: #fff;
            cursor: pointer;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
        }
        .agenda-card-top {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            justify-content: space-between;
        }
        .agenda-time-badge {
            min-width: 62px;
            padding: 7px;
            border-radius: 12px;
            background: #0f1f3a;
            color: #fff;
            text-align: center;
        }
        .agenda-time-badge strong,
        .agenda-time-badge span {
            display: block;
        }
        .agenda-time-badge span {
            margin-top: 2px;
            color: #bfd4f5;
            font-size: 0.76rem;
        }
        .agenda-route-card {
            padding: 9px;
            border-radius: 12px;
            background: #f8fafc;
            color: #334155;
            font-size: 0.9rem;
            line-height: 1.45;
        }
        .agenda-card-kpis {
            gap: 8px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        .agenda-card-kpi {
            border: 1px solid #e7edf5;
            border-radius: 11px;
            padding: 7px;
            min-width: 0;
        }
        .agenda-card-kpi span {
            display: block;
            color: var(--muted);
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .agenda-card-kpi strong {
            display: block;
            margin-top: 3px;
            font-size: 0.88rem;
            overflow-wrap: anywhere;
        }
        .agenda-drawer-backdrop {
            position: fixed;
            inset: 0;
            z-index: 50;
            background: rgba(15, 23, 42, 0.44);
        }
        .agenda-drawer-panel {
            position: fixed;
            left: 10px;
            right: 10px;
            bottom: 10px;
            z-index: 60;
            max-height: 86vh;
            overflow: auto;
            padding: 16px;
            border: 1px solid var(--border);
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.28);
        }
        .agenda-drawer-toggle:checked ~ .agenda-drawer-backdrop,
        .agenda-drawer-toggle:checked ~ .agenda-drawer-panel {
            display: block;
        }
        .agenda-drawer-head {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 14px;
        }
        .agenda-drawer-close {
            border: 0;
            border-radius: 999px;
            padding: 7px 10px;
            background: #e2e8f0;
            color: #1f2937;
            font-weight: 800;
            cursor: pointer;
        }
        .agenda-detail-grid {
            display: grid;
            gap: 10px;
            margin-bottom: 14px;
        }
        .agenda-detail-row {
            padding: 10px;
            border: 1px solid #e7edf5;
            border-radius: 12px;
            background: #fbfdff;
        }
        .agenda-detail-row span {
            display: block;
            margin-bottom: 4px;
            color: var(--muted);
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .agenda-detail-row strong,
        .agenda-detail-row p {
            margin: 0;
            overflow-wrap: anywhere;
        }
        .agenda-form-grid {
            gap: 8px;
        }
        .agenda-edit-modal .agenda-form-grid {
            grid-template-columns: 1fr;
        }
    }
    @media print {
        body {
            background: #fff;
        }
        .page-header,
        .sidebar,
        .topbar,
        .agenda-toolbar,
        .agenda-filters,
        .agenda-stats,
        .operator-summary,
        .notice,
        .agenda-form-grid,
        .form-actions,
        .card > :not(.agenda-print-sheet) {
            display: none !important;
        }
        .card {
            border: 0;
            box-shadow: none;
            padding: 0;
            background: transparent;
        }
        .main,
        .content {
            padding: 0 !important;
        }
        .agenda-print-sheet {
            display: block !important;
        }
        .agenda-table-wrapper {
            display: none !important;
        }
    }
</style>

<div class="page-header agenda-page-header">
    <div>
        <h1>Orden del dia</h1>
        <p style="margin: 6px 0 0; color: var(--muted);">
            <?= $isOperatorScope
                ? 'Vista personal de servicios asignados para consultar historico, proximas salidas y actualizar solo el resultado final.'
                : 'Vista operativa por rango para revisar llegadas, salidas y carga por operador o proveedor.' ?>
        </p>
    </div>
</div>

<div class="agenda-toolbar">
    <span class="agenda-subtle">Unidad se toma del catalogo de vehiculos asignado en operacion.</span>
    <div class="form-actions">
        <a class="btn" href="/admin/operations/agenda/export?<?= htmlspecialchars($agendaActionQuery, ENT_QUOTES, 'UTF-8') ?>">Descargar CSV</a>
        <a class="btn" href="/admin/operations/agenda/print?<?= htmlspecialchars($agendaActionQuery, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noreferrer">PDF filtrado / imprimir</a>
    </div>
</div>

<?php if ($saved): ?>
    <div class="notice">
        <?= $isOperatorScope
            ? 'El estado final del servicio se actualizo correctamente.'
            : 'La asignacion o nota operativa se guardo correctamente.' ?>
    </div>
<?php endif; ?>

<div class="agenda-grid">
    <div class="card agenda-filter-card">
        <input class="agenda-filter-toggle" type="checkbox" id="agenda-filter-toggle">
        <label class="agenda-filter-label" for="agenda-filter-toggle">
            <span>
                Ajustar filtros
                <span class="agenda-filter-summary">
                    <?= htmlspecialchars($startDate . ' a ' . $endDate, ENT_QUOTES, 'UTF-8') ?>
                    <?= $selectedStatus !== null && $selectedStatus !== '' ? ' - ' . htmlspecialchars((string) $selectedStatus, ENT_QUOTES, 'UTF-8') : '' ?>
                </span>
            </span>
        </label>
        <form method="get" action="/admin/operations/agenda" class="agenda-filters">
            <div class="form-group">
                <label for="preset">Rango rapido</label>
                <select id="preset" name="preset">
                    <option value="TODAY" <?= $rangePreset === 'TODAY' ? 'selected' : '' ?>>Hoy</option>
                    <option value="THIS_WEEK" <?= $rangePreset === 'THIS_WEEK' ? 'selected' : '' ?>>Esta semana</option>
                    <option value="LAST_WEEK" <?= $rangePreset === 'LAST_WEEK' ? 'selected' : '' ?>>Semana pasada</option>
                    <option value="NEXT_7_DAYS" <?= $rangePreset === 'NEXT_7_DAYS' ? 'selected' : '' ?>>Proximos 7 dias</option>
                    <option value="CUSTOM" <?= $rangePreset === 'CUSTOM' ? 'selected' : '' ?>>Personalizado</option>
                </select>
            </div>

            <div class="form-group">
                <label for="start_date">Desde</label>
                <input id="start_date" type="date" name="start_date" value="<?= htmlspecialchars($startDate, ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="form-group">
                <label for="end_date">Hasta</label>
                <input id="end_date" type="date" name="end_date" value="<?= htmlspecialchars($endDate, ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <?php if (!$isOperatorScope): ?>
                <div class="form-group">
                    <label for="operator_user_id">Operador</label>
                    <select id="operator_user_id" name="operator_user_id">
                        <option value="">Todos</option>
                        <?php foreach ($operators as $operator): ?>
                            <?php $operatorId = (int) ($operator['id'] ?? 0); ?>
                            <option value="<?= $operatorId ?>" <?= ($selectedOperatorId === $operatorId) ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) ($operator['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="service_status">Estado operativo</label>
                <select id="service_status" name="service_status">
                    <option value="">Todos</option>
                    <?php foreach ($statusOptions as $statusOption): ?>
                        <option value="<?= htmlspecialchars((string) $statusOption, ENT_QUOTES, 'UTF-8') ?>" <?= ($selectedStatus === $statusOption) ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string) ($serviceStatusLabels[(string) $statusOption] ?? $statusOption), ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn">Aplicar filtros</button>
            </div>
        </form>
    </div>

    <div class="agenda-stats">
        <div class="agenda-stat">
            <span>Servicios</span>
            <strong><?= (int) ($agendaStats['total_services'] ?? 0) ?></strong>
        </div>
        <div class="agenda-stat">
            <span>Llegadas</span>
            <strong><?= (int) ($agendaStats['arrivals'] ?? 0) ?></strong>
        </div>
        <div class="agenda-stat">
            <span>Salidas</span>
            <strong><?= (int) ($agendaStats['departures'] ?? 0) ?></strong>
        </div>
        <div class="agenda-stat">
            <span>Asignados</span>
            <strong><?= (int) ($agendaStats['assigned'] ?? 0) ?></strong>
        </div>
        <div class="agenda-stat">
            <span>Con proveedor</span>
            <strong><?= (int) ($agendaStats['provider_services'] ?? 0) ?></strong>
        </div>
    </div>

    <?php if (!$isOperatorScope && !empty($agendaStats['by_operator'])): ?>
        <div class="card agenda-operator-card">
            <input class="agenda-operator-toggle" type="checkbox" id="agenda-operator-toggle">
            <label class="agenda-operator-label" for="agenda-operator-toggle">Resumen por operador</label>
            <div class="agenda-operator-body">
                <div class="page-header" style="margin-bottom: 12px;">
                    <div>
                        <h2 style="margin:0; font-size: 1.2rem;">Resumen por operador</h2>
                        <p style="margin: 6px 0 0; color: var(--muted);">Ideal para revisar semanas pasadas o el total de salidas/llegadas de cada operador.</p>
                    </div>
                </div>
                <div class="operator-summary">
                    <?php foreach ($agendaStats['by_operator'] as $operatorStat): ?>
                        <article class="operator-card">
                            <h3><?= htmlspecialchars((string) ($operatorStat['operator_name'] ?? 'Sin asignar'), ENT_QUOTES, 'UTF-8') ?></h3>
                            <p>Total: <?= (int) ($operatorStat['total'] ?? 0) ?></p>
                            <p>Llegadas: <?= (int) ($operatorStat['arrivals'] ?? 0) ?></p>
                            <p>Salidas: <?= (int) ($operatorStat['departures'] ?? 0) ?></p>
                            <p>Completados: <?= (int) ($operatorStat['done'] ?? 0) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (empty($agendaItems)): ?>
        <div class="card agenda-services-card">
            <p>No hay servicios para el rango y filtros actuales.</p>
        </div>
    <?php else: ?>
        <div class="card agenda-services-card">
            <div class="agenda-mobile-list" aria-label="Servicios en tarjetas">
                <?php foreach ($agendaItems as $item): ?>
                    <?php
                    $mode = (string) ($item['mode'] ?? 'INTERNAL');
                    $bookingId = (int) ($item['id'] ?? 0);
                    $workDate = (string) ($item['work_date'] ?? date('Y-m-d', strtotime((string) ($item['service_datetime'] ?? 'now'))));
                    $currentBookingStatus = (string) ($item['booking_status'] ?? 'CONFIRMED');
                    $hasOperatorFinalStatus = in_array($currentBookingStatus, $operatorBookingStatuses, true);
                    $serviceTypeLabel = $resolveOperationLabel($item);
                    $originDisplay = $resolveOrigin($item);
                    $destinationDisplay = $resolveDestination($item);
                    $serviceDate = (string) date('d/m/Y', strtotime((string) ($item['service_datetime'] ?? 'now')));
                    $serviceHour = (string) date('H:i', strtotime((string) ($item['service_datetime'] ?? 'now')));
                    $customerName = trim((string) ($item['customer_name'] ?? '') . ' ' . (string) ($item['customer_last_name'] ?? ''));
                    $flightLabel = trim((string) ($item['airline'] ?? '') . ' ' . (string) ($item['flight_number'] ?? ''));
                    $drawerId = 'agenda-drawer-' . $bookingId . '-' . substr(md5($workDate . (string) ($item['service_datetime'] ?? '') . $serviceTypeLabel), 0, 8);
                    ?>
                    <article class="agenda-mobile-item">
                        <input class="agenda-drawer-toggle" type="checkbox" id="<?= htmlspecialchars($drawerId, ENT_QUOTES, 'UTF-8') ?>">
                        <label class="agenda-mobile-card" for="<?= htmlspecialchars($drawerId, ENT_QUOTES, 'UTF-8') ?>">
                            <span class="agenda-card-top">
                                <span class="agenda-time-badge">
                                    <strong><?= htmlspecialchars($serviceHour, ENT_QUOTES, 'UTF-8') ?></strong>
                                    <span><?= htmlspecialchars($serviceDate, ENT_QUOTES, 'UTF-8') ?></span>
                                </span>
                                <span class="agenda-booking" style="flex:1; min-width:0;">
                                    <strong><?= htmlspecialchars($customerName !== '' ? $customerName : 'Sin cliente', ENT_QUOTES, 'UTF-8') ?></strong>
                                    <span class="agenda-subtle"><?= htmlspecialchars((string) ($item['booking_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($serviceTypeLabel, ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="agenda-subtle"><?= htmlspecialchars((string) ($serviceStatusLabels[(string) ($item['service_status'] ?? 'PENDING')] ?? ($item['service_status'] ?? 'PENDING')), ENT_QUOTES, 'UTF-8') ?></span>
                                </span>
                            </span>
                            <span class="agenda-route-card">
                                <?= htmlspecialchars($originDisplay, ENT_QUOTES, 'UTF-8') ?><br>
                                <?= htmlspecialchars($destinationDisplay, ENT_QUOTES, 'UTF-8') ?>
                            </span>
                            <span class="agenda-card-kpis">
                                <span class="agenda-card-kpi">
                                    <span>Pax</span>
                                    <strong><?= htmlspecialchars((string) ($item['total_pax'] ?? '0'), ENT_QUOTES, 'UTF-8') ?></strong>
                                </span>
                                <span class="agenda-card-kpi">
                                    <span>Unidad</span>
                                    <strong><?= htmlspecialchars((string) ($item['vehicle_name'] ?? 'Sin unidad'), ENT_QUOTES, 'UTF-8') ?></strong>
                                </span>
                                <span class="agenda-card-kpi">
                                    <span>Operador</span>
                                    <strong><?= htmlspecialchars((string) ($item['operator_name'] ?? 'Sin asignar'), ENT_QUOTES, 'UTF-8') ?></strong>
                                </span>
                            </span>
                        </label>
                        <label class="agenda-drawer-backdrop" for="<?= htmlspecialchars($drawerId, ENT_QUOTES, 'UTF-8') ?>" aria-label="Cerrar detalle"></label>
                        <aside class="agenda-drawer-panel" role="dialog" aria-modal="true" aria-labelledby="<?= htmlspecialchars($drawerId, ENT_QUOTES, 'UTF-8') ?>-title">
                            <div class="agenda-drawer-head">
                                <div>
                                    <h2 id="<?= htmlspecialchars($drawerId, ENT_QUOTES, 'UTF-8') ?>-title" style="margin:0; font-size:1.15rem;">
                                        <?= htmlspecialchars($serviceHour . ' - ' . $serviceTypeLabel, ENT_QUOTES, 'UTF-8') ?>
                                    </h2>
                                    <p class="agenda-subtle" style="margin:4px 0 0;">
                                        <?= htmlspecialchars((string) ($item['booking_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($customerName !== '' ? $customerName : 'Sin cliente', ENT_QUOTES, 'UTF-8') ?>
                                    </p>
                                </div>
                                <label class="agenda-drawer-close" for="<?= htmlspecialchars($drawerId, ENT_QUOTES, 'UTF-8') ?>">Cerrar</label>
                            </div>

                            <div class="agenda-detail-grid">
                                <div class="agenda-detail-row">
                                    <span>Ruta</span>
                                    <strong><?= htmlspecialchars($originDisplay, ENT_QUOTES, 'UTF-8') ?></strong>
                                    <p class="agenda-subtle"><?= htmlspecialchars($destinationDisplay, ENT_QUOTES, 'UTF-8') ?></p>
                                </div>
                                <div class="agenda-detail-row">
                                    <span>Vuelo</span>
                                    <strong><?= htmlspecialchars($flightLabel !== '' ? $flightLabel : 'Sin vuelo', ENT_QUOTES, 'UTF-8') ?></strong>
                                    <p class="agenda-subtle">Terminal: <?= htmlspecialchars((string) ($item['terminal'] ?? 'Sin terminal'), ENT_QUOTES, 'UTF-8') ?></p>
                                </div>
                                <div class="agenda-detail-row">
                                    <span>Logistica</span>
                                    <strong>Unidad: <?= htmlspecialchars((string) ($item['vehicle_name'] ?? 'Sin unidad'), ENT_QUOTES, 'UTF-8') ?></strong>
                                    <p class="agenda-subtle">Operador: <?= htmlspecialchars((string) ($item['operator_name'] ?? 'Sin asignar'), ENT_QUOTES, 'UTF-8') ?></p>
                                    <p class="agenda-subtle">Proveedor: <?= htmlspecialchars((string) ($item['provider_name'] ?? 'Sin proveedor'), ENT_QUOTES, 'UTF-8') ?></p>
                                </div>
                                <div class="agenda-detail-row">
                                    <span>Cobro</span>
                                    <strong><?= htmlspecialchars(number_format((float) ($item['balance_due'] ?? 0), 2) . ' ' . (string) ($item['currency_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                                    <p class="agenda-subtle">Pax: <?= htmlspecialchars((string) (($item['total_pax'] ?? '0') . ' pax'), ENT_QUOTES, 'UTF-8') ?></p>
                                </div>
                            </div>

                            <?php $renderAgendaEditor($item, $bookingId, $workDate, $mode, $currentBookingStatus, $originDisplay, $destinationDisplay, 'mobile'); ?>
                        </aside>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="agenda-table-wrapper">
                <table class="agenda-table">
                    <thead>
                        <tr>
                            <th>Fecha / hora</th>
                            <th>Reserva / cliente</th>
                            <th>Ruta</th>
                            <th>Operacion</th>
                            <th>Edicion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($agendaItems as $item): ?>
                            <?php
                            $mode = (string) ($item['mode'] ?? 'INTERNAL');
                            $bookingId = (int) ($item['id'] ?? 0);
                            $workDate = (string) ($item['work_date'] ?? date('Y-m-d', strtotime((string) ($item['service_datetime'] ?? 'now'))));
                            $currentBookingStatus = (string) ($item['booking_status'] ?? 'CONFIRMED');
                            $hasOperatorFinalStatus = in_array($currentBookingStatus, $operatorBookingStatuses, true);
                            $serviceTypeLabel = $resolveOperationLabel($item);
                            $originDisplay = $resolveOrigin($item);
                            $destinationDisplay = $resolveDestination($item);
                            ?>
                            <tr>
                                <td data-label="Fecha / hora">
                                    <div class="agenda-booking">
                                        <strong><?= htmlspecialchars((string) date('d/m/Y', strtotime((string) ($item['service_datetime'] ?? 'now'))), ENT_QUOTES, 'UTF-8') ?></strong>
                                        <span><?= htmlspecialchars((string) date('H:i', strtotime((string) ($item['service_datetime'] ?? 'now'))), ENT_QUOTES, 'UTF-8') ?></span>
                                        <span class="agenda-subtle"><?= htmlspecialchars($serviceTypeLabel, ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                </td>
                                <td data-label="Reserva / cliente">
                                    <div class="agenda-booking">
                                        <strong><?= htmlspecialchars((string) ($item['booking_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                                        <span><?= htmlspecialchars(trim((string) ($item['customer_name'] ?? '') . ' ' . (string) ($item['customer_last_name'] ?? '')), ENT_QUOTES, 'UTF-8') ?></span>
                                        <span class="agenda-subtle">Agencia: <?= htmlspecialchars((string) ($item['agency_name'] ?? 'Sin agencia'), ENT_QUOTES, 'UTF-8') ?></span>
                                        <span class="agenda-subtle"><?= htmlspecialchars((string) ($item['customer_phone'] ?? 'Sin telefono'), ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                </td>
                                <td data-label="Ruta">
                                    <div class="agenda-booking">
                                        <strong><?= htmlspecialchars($originDisplay, ENT_QUOTES, 'UTF-8') ?></strong>
                                        <span><?= htmlspecialchars($destinationDisplay, ENT_QUOTES, 'UTF-8') ?></span>
                                        <span class="agenda-subtle">Terminal: <?= htmlspecialchars((string) ($item['terminal'] ?? 'Sin terminal'), ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php if (($item['airline'] ?? '') !== '' || ($item['flight_number'] ?? '') !== ''): ?>
                                            <span class="agenda-subtle">Vuelo: <?= htmlspecialchars(trim((string) ($item['airline'] ?? '') . ' ' . (string) ($item['flight_number'] ?? '')), ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td data-label="Operacion">
                                    <div class="agenda-booking">
                                        <strong><?= htmlspecialchars((string) ($item['service_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                                        <span class="agenda-subtle">Unidad: <?= htmlspecialchars((string) ($item['vehicle_name'] ?? 'Sin unidad'), ENT_QUOTES, 'UTF-8') ?></span>
                                        <span class="agenda-subtle">Operador: <?= htmlspecialchars((string) ($item['operator_name'] ?? 'Sin asignar'), ENT_QUOTES, 'UTF-8') ?></span>
                                        <span class="agenda-subtle">Proveedor: <?= htmlspecialchars((string) ($item['provider_name'] ?? 'Sin proveedor'), ENT_QUOTES, 'UTF-8') ?></span>
                                        <span class="agenda-subtle">Pax: <?= htmlspecialchars((string) (($item['total_pax'] ?? '0') . ' pax'), ENT_QUOTES, 'UTF-8') ?></span>
                                        <span class="agenda-subtle">Balance: <?= htmlspecialchars(number_format((float) ($item['balance_due'] ?? 0), 2) . ' ' . (string) ($item['currency_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                </td>
                                <td class="agenda-edit-cell" data-label="Edicion">
                                    <?php $renderAgendaEditor($item, $bookingId, $workDate, $mode, $currentBookingStatus, $originDisplay, $destinationDisplay, 'desktop'); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="agenda-print-sheet">
                <table class="agenda-print-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Unidad</th>
                            <th>Operador</th>
                            <th>Hora</th>
                            <th>Agencia</th>
                            <th>Proveedor</th>
                            <th>Tipo de servicio</th>
                            <th>No. Servicio</th>
                            <th>Terminal</th>
                            <th>Vuelo</th>
                            <th>Cliente</th>
                            <th>Origen</th>
                            <th>Destino</th>
                            <th>Pax</th>
                            <th>Balance</th>
                            <th>Nota</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($agendaItems as $item): ?>
                            <?php
                            $serviceTypeLabel = $resolveOperationLabel($item);
                            $originDisplay = $resolveOrigin($item);
                            $destinationDisplay = $resolveDestination($item);
                            ?>
                            <tr>
                                <td><?= htmlspecialchars((string) date('d/m/Y', strtotime((string) ($item['service_datetime'] ?? 'now'))), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($item['vehicle_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($item['operator_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) date('H:i', strtotime((string) ($item['service_datetime'] ?? 'now'))), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($item['agency_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($item['provider_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($serviceTypeLabel, ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($item['booking_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($item['terminal'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars(trim((string) ($item['airline'] ?? '') . ' ' . (string) ($item['flight_number'] ?? '')), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars(trim((string) ($item['customer_name'] ?? '') . ' ' . (string) ($item['customer_last_name'] ?? '')), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($originDisplay, ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($destinationDisplay, ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($item['total_pax'] ?? '0'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars(number_format((float) ($item['balance_due'] ?? 0), 2) . ' ' . (string) ($item['currency_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($item['work_order_notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
