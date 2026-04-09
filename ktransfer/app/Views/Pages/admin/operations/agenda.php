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
?>
<style>
    .agenda-grid {
        display: grid;
        gap: 18px;
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
</style>

<div class="page-header">
    <div>
        <h1>Orden del dia</h1>
        <p style="margin: 6px 0 0; color: var(--muted);">
            <?= $isOperatorScope
                ? 'Vista personal de servicios asignados para consultar historico, proximas salidas y actualizar solo el resultado final.'
                : 'Vista operativa por rango para revisar llegadas, salidas y carga por operador o proveedor.' ?>
        </p>
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
    <div class="card">
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
                            <?= htmlspecialchars((string) $statusOption, ENT_QUOTES, 'UTF-8') ?>
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
        <div class="card">
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
    <?php endif; ?>

    <?php if (empty($agendaItems)): ?>
        <div class="card">
            <p>No hay servicios para el rango y filtros actuales.</p>
        </div>
    <?php else: ?>
        <div class="card">
            <table class="agenda-table">
                <thead>
                    <tr>
                        <th>Fecha / hora</th>
                        <th>Reserva</th>
                        <th>Ruta</th>
                        <th>Servicio</th>
                        <th>Asignacion</th>
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
                        ?>
                        <tr>
                            <td>
                                <div class="agenda-booking">
                                    <strong><?= htmlspecialchars((string) date('d/m/Y', strtotime((string) ($item['service_datetime'] ?? 'now'))), ENT_QUOTES, 'UTF-8') ?></strong>
                                    <span><?= htmlspecialchars((string) date('H:i', strtotime((string) ($item['service_datetime'] ?? 'now'))), ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="agenda-subtle"><?= htmlspecialchars((string) ($item['service_leg'] ?? 'ARRIVAL'), ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="agenda-booking">
                                    <strong><?= htmlspecialchars((string) ($item['booking_code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                                    <span><?= htmlspecialchars(trim((string) ($item['customer_name'] ?? '') . ' ' . (string) ($item['customer_last_name'] ?? '')), ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="agenda-subtle"><?= htmlspecialchars((string) ($item['customer_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="agenda-subtle"><?= htmlspecialchars((string) ($item['customer_phone'] ?? 'Sin telefono'), ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="agenda-booking">
                                    <strong><?= htmlspecialchars((string) ($item['zone_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                                    <span><?= htmlspecialchars((string) ($item['place_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php if (($item['airline'] ?? '') !== '' || ($item['flight_number'] ?? '') !== ''): ?>
                                        <span class="agenda-subtle"><?= htmlspecialchars(trim((string) ($item['airline'] ?? '') . ' ' . (string) ($item['flight_number'] ?? '')), ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php endif; ?>
                                    <?php if (($item['pickup_notes'] ?? '') !== ''): ?>
                                        <span class="agenda-subtle"><?= htmlspecialchars((string) ($item['pickup_notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="agenda-booking">
                                    <strong><?= htmlspecialchars((string) ($item['service_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                                    <span class="agenda-subtle"><?= htmlspecialchars((string) (($item['total_pax'] ?? '0') . ' pax'), ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="agenda-subtle">Reserva: <?= htmlspecialchars((string) ($item['booking_status'] ?? ''), ENT_QUOTES, 'UTF-8') ?> / Pago: <?= htmlspecialchars((string) ($item['payment_status'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php if (($item['provider_name'] ?? '') !== ''): ?>
                                        <span class="agenda-subtle">Proveedor: <?= htmlspecialchars((string) ($item['provider_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php endif; ?>
                                    <?php if (($item['vehicle_name'] ?? '') !== ''): ?>
                                        <span class="agenda-subtle">Vehiculo: <?= htmlspecialchars((string) ($item['vehicle_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td style="min-width: 360px;">
                                <form method="post" action="/admin/operations/agenda" class="agenda-form-grid">
                                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) ($csrf_token ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="booking_id" value="<?= $bookingId ?>">
                                    <input type="hidden" name="work_date" value="<?= htmlspecialchars($workDate, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="preset" value="<?= htmlspecialchars($rangePreset, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="start_date" value="<?= htmlspecialchars($startDate, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="end_date" value="<?= htmlspecialchars($endDate, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="filter_operator_user_id" value="<?= htmlspecialchars((string) ($selectedOperatorId ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="filter_service_status" value="<?= htmlspecialchars((string) ($selectedStatus ?? ''), ENT_QUOTES, 'UTF-8') ?>">

                                    <?php if ($isOperatorScope): ?>
                                        <div class="form-group">
                                            <label>Asignado actualmente</label>
                                            <div class="agenda-subtle">
                                                Operador: <?= htmlspecialchars((string) ($item['operator_name'] ?? 'Sin asignar'), ENT_QUOTES, 'UTF-8') ?><br>
                                                Modo: <?= htmlspecialchars($mode, ENT_QUOTES, 'UTF-8') ?><br>
                                                Proveedor: <?= htmlspecialchars((string) ($item['provider_name'] ?? 'Sin proveedor'), ENT_QUOTES, 'UTF-8') ?><br>
                                                Vehiculo: <?= htmlspecialchars((string) ($item['vehicle_name'] ?? 'Sin vehiculo'), ENT_QUOTES, 'UTF-8') ?><br>
                                                Estado operativo: <?= htmlspecialchars((string) ($item['service_status'] ?? 'PENDING'), ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Resultado final</label>
                                            <select name="operator_booking_status">
                                                <option value="" <?= $hasOperatorFinalStatus ? '' : 'selected' ?>>Seleccionar resultado</option>
                                                <?php foreach ($operatorBookingStatuses as $bookingStatusOption): ?>
                                                    <option value="<?= htmlspecialchars((string) $bookingStatusOption, ENT_QUOTES, 'UTF-8') ?>" <?= ($currentBookingStatus === $bookingStatusOption) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars((string) $bookingStatusOption, ENT_QUOTES, 'UTF-8') ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="form-actions">
                                            <button type="submit" class="btn">Actualizar estado</button>
                                        </div>
                                    <?php else: ?>
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
                                            <label>Vehiculo</label>
                                            <select name="vehicle_id">
                                                <option value="">Sin vehiculo</option>
                                                <?php foreach ($vehicles as $vehicle): ?>
                                                    <?php $vehicleId = (int) ($vehicle['id'] ?? 0); ?>
                                                    <option value="<?= $vehicleId ?>" <?= ((int) ($item['vehicle_id'] ?? 0) === $vehicleId) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars((string) ($vehicle['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Estado operativo</label>
                                            <select name="service_status">
                                                <?php foreach ($statusOptions as $statusOption): ?>
                                                    <option value="<?= htmlspecialchars((string) $statusOption, ENT_QUOTES, 'UTF-8') ?>" <?= ((string) ($item['service_status'] ?? 'PENDING') === $statusOption) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars((string) $statusOption, ENT_QUOTES, 'UTF-8') ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Nota operativa</label>
                                            <textarea name="work_order_notes" rows="3"><?= htmlspecialchars((string) ($item['work_order_notes'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                                        </div>

                                        <div class="form-actions">
                                            <button type="submit" class="btn">Guardar asignacion</button>
                                        </div>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
