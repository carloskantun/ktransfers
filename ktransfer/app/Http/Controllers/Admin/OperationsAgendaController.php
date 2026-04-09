<?php
declare(strict_types=1);
namespace App\Http\Controllers\Admin;

use App\Core\ACL;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\DB;
use App\Core\Request;
use App\Core\Response;
use DateTimeImmutable;
use Throwable;

class OperationsAgendaController {
    private const STATUS_OPTIONS = ['PENDING', 'ASSIGNED', 'IN_PROGRESS', 'DONE', 'NO_SHOW'];
    private const MODE_OPTIONS = ['INTERNAL', 'PROVIDER'];
    private const RANGE_PRESETS = ['TODAY', 'THIS_WEEK', 'LAST_WEEK', 'NEXT_7_DAYS', 'CUSTOM'];
    private const OPERATOR_BOOKING_STATUSES = ['COMPLETED', 'NO_SHOW', 'CANCELLED'];

    public function index(Request $request): Response
    {
        $currentUserId = Auth::id();
        $isOperatorScope = $this->isOperatorScope();
        $range = $this->resolveDateRange(
            (string) $request->query('preset', ''),
            (string) $request->query('start_date', ''),
            (string) $request->query('end_date', ''),
            (string) $request->query('date', '')
        );
        $selectedOperatorId = $isOperatorScope
            ? $currentUserId
            : $this->normalizePositiveInt((string) $request->query('operator_user_id', ''));
        $selectedStatus = $this->normalizeStatus((string) $request->query('service_status', ''));
        $saved = $request->query('saved', '') === '1';

        $db = DB::connection();
        $agendaItems = $this->loadAgendaItems($db, $range['start_date'], $range['end_date'], $selectedOperatorId, $selectedStatus);

        return Response::view('admin/operations/agenda', [
            'title' => 'Orden del dia',
            'csrf_token' => Csrf::token(),
            'saved' => $saved,
            'range_preset' => $range['preset'],
            'start_date' => $range['start_date'],
            'end_date' => $range['end_date'],
            'is_operator_scope' => $isOperatorScope,
            'status_options' => self::STATUS_OPTIONS,
            'mode_options' => self::MODE_OPTIONS,
            'operator_booking_statuses' => self::OPERATOR_BOOKING_STATUSES,
            'selected_operator_id' => $selectedOperatorId,
            'selected_status' => $selectedStatus,
            'operators' => $this->loadOperators($db),
            'providers' => $this->loadProviders($db),
            'vehicles' => $this->loadVehicles($db),
            'agenda_items' => $agendaItems,
            'agenda_stats' => $this->buildAgendaStats($agendaItems),
        ], 'admin');
    }

    public function update(Request $request): Response
    {
        $currentUserId = Auth::id();
        $isOperatorScope = $this->isOperatorScope();
        $range = $this->resolveDateRange(
            (string) $request->post('preset', ''),
            (string) $request->post('start_date', ''),
            (string) $request->post('end_date', ''),
            (string) $request->post('work_date', '')
        );
        $selectedOperatorId = $isOperatorScope
            ? $currentUserId
            : $this->normalizePositiveInt((string) $request->post('filter_operator_user_id', ''));
        $selectedStatus = $this->normalizeStatus((string) $request->post('filter_service_status', ''));

        $redirectUrl = '/admin/operations/agenda?'
            . http_build_query([
                'preset' => $range['preset'],
                'start_date' => $range['start_date'],
                'end_date' => $range['end_date'],
                'operator_user_id' => $selectedOperatorId,
                'service_status' => $selectedStatus,
                'saved' => 1,
            ]);

        if (!Csrf::validate((string) $request->post('_csrf', ''))) {
            return Response::redirect($redirectUrl);
        }

        $bookingId = $this->normalizePositiveInt((string) $request->post('booking_id', ''));
        $operatorUserId = $this->normalizePositiveInt((string) $request->post('operator_user_id', ''));
        $providerId = $this->normalizePositiveInt((string) $request->post('provider_id', ''));
        $vehicleId = $this->normalizePositiveInt((string) $request->post('vehicle_id', ''));
        $serviceStatus = $this->normalizeStatus((string) $request->post('service_status', '')) ?? 'PENDING';
        $mode = $this->normalizeMode((string) $request->post('mode', '')) ?? 'INTERNAL';
        $notes = trim((string) $request->post('work_order_notes', ''));
        $workDate = $this->normalizeDate((string) $request->post('work_date', '')) ?? $range['start_date'];

        if ($bookingId === null) {
            return Response::redirect($redirectUrl);
        }

        if ($mode !== 'PROVIDER') {
            $providerId = null;
        }

        $db = DB::connection();

        if ($isOperatorScope) {
            $bookingStatus = strtoupper(trim((string) $request->post('operator_booking_status', '')));
            if (!in_array($bookingStatus, self::OPERATOR_BOOKING_STATUSES, true) || $currentUserId === null) {
                return Response::redirect($redirectUrl);
            }

            $this->updateOperatorOwnedAssignmentStatus($db, $bookingId, $currentUserId, $bookingStatus);
            return Response::redirect($redirectUrl);
        }

        try {
            $db->beginTransaction();

            $assignmentStmt = $db->prepare('SELECT id, assigned_at, done_at FROM assignments WHERE booking_id = :booking_id LIMIT 1');
            $assignmentStmt->execute(['booking_id' => $bookingId]);
            $assignment = $assignmentStmt->fetch();

            $doneAt = $serviceStatus === 'DONE' ? (new DateTimeImmutable('now'))->format('Y-m-d H:i:s') : null;

            if (is_array($assignment)) {
                $updateAssignmentStmt = $db->prepare(
                    'UPDATE assignments
                     SET mode = :mode,
                         provider_id = :provider_id,
                         vehicle_id = :vehicle_id,
                         operator_user_id = :operator_user_id,
                         service_status = :service_status,
                         assigned_at = CASE
                             WHEN (:operator_user_id IS NOT NULL OR :provider_id IS NOT NULL) AND assigned_at IS NULL THEN NOW()
                             ELSE assigned_at
                         END,
                         done_at = :done_at
                     WHERE booking_id = :booking_id'
                );
                $updateAssignmentStmt->execute([
                    'mode' => $mode,
                    'provider_id' => $providerId,
                    'vehicle_id' => $vehicleId,
                    'operator_user_id' => $operatorUserId,
                    'service_status' => $serviceStatus,
                    'done_at' => $doneAt,
                    'booking_id' => $bookingId,
                ]);
            } else {
                $insertAssignmentStmt = $db->prepare(
                    'INSERT INTO assignments (booking_id, mode, provider_id, vehicle_id, operator_user_id, service_status, assigned_at, done_at)
                     VALUES (:booking_id, :mode, :provider_id, :vehicle_id, :operator_user_id, :service_status, :assigned_at, :done_at)'
                );
                $insertAssignmentStmt->execute([
                    'booking_id' => $bookingId,
                    'mode' => $mode,
                    'provider_id' => $providerId,
                    'vehicle_id' => $vehicleId,
                    'operator_user_id' => $operatorUserId,
                    'service_status' => $serviceStatus,
                    'assigned_at' => ($operatorUserId !== null || $providerId !== null) ? (new DateTimeImmutable('now'))->format('Y-m-d H:i:s') : null,
                    'done_at' => $doneAt,
                ]);
            }

            $workOrderStmt = $db->prepare('SELECT id FROM work_orders WHERE booking_id = :booking_id LIMIT 1');
            $workOrderStmt->execute(['booking_id' => $bookingId]);
            $workOrder = $workOrderStmt->fetch();

            if (is_array($workOrder)) {
                $updateWorkOrderStmt = $db->prepare(
                    'UPDATE work_orders
                     SET work_date = :work_date,
                         notes = :notes
                     WHERE booking_id = :booking_id'
                );
                $updateWorkOrderStmt->execute([
                    'work_date' => $workDate,
                    'notes' => $notes !== '' ? $notes : null,
                    'booking_id' => $bookingId,
                ]);
            } else {
                $insertWorkOrderStmt = $db->prepare(
                    'INSERT INTO work_orders (work_date, booking_id, notes, created_at)
                     VALUES (:work_date, :booking_id, :notes, NOW())'
                );
                $insertWorkOrderStmt->execute([
                    'work_date' => $workDate,
                    'booking_id' => $bookingId,
                    'notes' => $notes !== '' ? $notes : null,
                ]);
            }

            $db->commit();
        } catch (Throwable) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
        }

        return Response::redirect($redirectUrl);
    }

    private function loadOperators(\PDO $db): array
    {
        $stmt = $db->query(
            'SELECT
                u.id,
                u.name,
                u.email,
                COALESCE(GROUP_CONCAT(DISTINCT r.name ORDER BY r.name SEPARATOR ", "), "") AS role_names
             FROM users u
             LEFT JOIN user_roles ur ON ur.user_id = u.id
             LEFT JOIN roles r ON r.id = ur.role_id
             WHERE u.is_active = 1
             GROUP BY u.id, u.name, u.email
             ORDER BY u.name ASC'
        );

        return $stmt->fetchAll();
    }

    private function loadProviders(\PDO $db): array
    {
        $stmt = $db->query('SELECT id, name FROM providers WHERE is_active = 1 ORDER BY name ASC');
        return $stmt->fetchAll();
    }

    private function loadVehicles(\PDO $db): array
    {
        $stmt = $db->query('SELECT id, name FROM vehicles WHERE is_active = 1 ORDER BY name ASC');
        return $stmt->fetchAll();
    }

    private function loadAgendaItems(\PDO $db, string $startDate, string $endDate, ?int $operatorUserId, ?string $serviceStatus): array
    {
        $timeExpression = "
            CASE
                WHEN wo.work_date IS NOT NULL AND b.direction = 'DESTINATION_TO_AIRPORT' AND b.departure_datetime IS NOT NULL THEN b.departure_datetime
                WHEN wo.work_date IS NOT NULL AND b.arrival_datetime IS NOT NULL THEN b.arrival_datetime
                WHEN b.arrival_datetime IS NOT NULL THEN b.arrival_datetime
                WHEN b.departure_datetime IS NOT NULL THEN b.departure_datetime
                ELSE b.created_at
            END
        ";

        $legExpression = "
            CASE
                WHEN b.arrival_datetime IS NOT NULL AND DATE(b.arrival_datetime) BETWEEN :start_date_arrival_leg AND :end_date_arrival_leg THEN 'ARRIVAL'
                WHEN b.departure_datetime IS NOT NULL AND DATE(b.departure_datetime) BETWEEN :start_date_departure_leg AND :end_date_departure_leg THEN 'DEPARTURE'
                WHEN b.direction = 'DESTINATION_TO_AIRPORT' THEN 'DEPARTURE'
                ELSE 'ARRIVAL'
            END
        ";

        $sql = "
            SELECT
                b.id,
                b.booking_code,
                b.customer_name,
                b.customer_last_name,
                b.customer_email,
                b.customer_phone,
                b.trip_type,
                b.direction,
                b.status AS booking_status,
                b.payment_status,
                b.airline,
                b.flight_number,
                b.pickup_notes,
                z.name_es AS zone_name,
                st.name_es AS service_name,
                p.name AS place_name,
                bp.total_pax,
                {$timeExpression} AS service_datetime,
                {$legExpression} AS service_leg,
                a.mode,
                a.provider_id,
                a.vehicle_id,
                a.operator_user_id,
                COALESCE(a.service_status, 'PENDING') AS service_status,
                u.name AS operator_name,
                v.name AS vehicle_name,
                pr.name AS provider_name,
                wo.work_date,
                wo.notes AS work_order_notes
            FROM bookings b
            LEFT JOIN booking_passengers bp ON bp.booking_id = b.id
            LEFT JOIN service_types st ON st.id = b.service_type_id
            LEFT JOIN zones z ON z.id = b.zone_id
            LEFT JOIN places p ON p.id = b.place_id
            LEFT JOIN assignments a ON a.booking_id = b.id
            LEFT JOIN users u ON u.id = a.operator_user_id
            LEFT JOIN vehicles v ON v.id = a.vehicle_id
            LEFT JOIN providers pr ON pr.id = a.provider_id
            LEFT JOIN work_orders wo ON wo.booking_id = b.id
            WHERE (
                wo.work_date BETWEEN :start_date_work_filter AND :end_date_work_filter
                OR (wo.work_date IS NULL AND b.arrival_datetime IS NOT NULL AND DATE(b.arrival_datetime) BETWEEN :start_date_arrival_filter AND :end_date_arrival_filter)
                OR (wo.work_date IS NULL AND b.departure_datetime IS NOT NULL AND DATE(b.departure_datetime) BETWEEN :start_date_departure_filter AND :end_date_departure_filter)
                OR (
                    wo.work_date IS NULL
                    AND b.arrival_datetime IS NULL
                    AND b.departure_datetime IS NULL
                    AND DATE(b.created_at) BETWEEN :start_date_created_filter AND :end_date_created_filter
                )
            )
        ";

        $params = [
            'start_date_arrival_leg' => $startDate,
            'end_date_arrival_leg' => $endDate,
            'start_date_departure_leg' => $startDate,
            'end_date_departure_leg' => $endDate,
            'start_date_work_filter' => $startDate,
            'end_date_work_filter' => $endDate,
            'start_date_arrival_filter' => $startDate,
            'end_date_arrival_filter' => $endDate,
            'start_date_departure_filter' => $startDate,
            'end_date_departure_filter' => $endDate,
            'start_date_created_filter' => $startDate,
            'end_date_created_filter' => $endDate,
        ];

        if ($operatorUserId !== null) {
            $sql .= ' AND a.operator_user_id = :operator_user_id';
            $params['operator_user_id'] = $operatorUserId;
        }

        if ($serviceStatus !== null) {
            $sql .= ' AND COALESCE(a.service_status, \'PENDING\') = :service_status';
            $params['service_status'] = $serviceStatus;
        }

        $sql .= ' ORDER BY DATE(service_datetime) ASC, service_datetime ASC, b.created_at ASC, b.id ASC';

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    private function updateOperatorOwnedAssignmentStatus(\PDO $db, int $bookingId, int $currentUserId, string $bookingStatus): void
    {
        $stmt = $db->prepare(
            'SELECT
                b.id,
                b.status,
                a.id AS assignment_id,
                a.service_status
             FROM bookings b
             INNER JOIN assignments a ON a.booking_id = b.id
             WHERE b.id = :booking_id
               AND a.operator_user_id = :operator_user_id
             LIMIT 1'
        );
        $stmt->execute([
            'booking_id' => $bookingId,
            'operator_user_id' => $currentUserId,
        ]);
        $row = $stmt->fetch();

        if (!is_array($row)) {
            return;
        }

        $serviceStatus = $this->mapOperatorBookingStatusToServiceStatus($bookingStatus, (string) ($row['service_status'] ?? 'PENDING'));
        $doneAt = in_array($bookingStatus, ['COMPLETED', 'NO_SHOW'], true)
            ? (new DateTimeImmutable('now'))->format('Y-m-d H:i:s')
            : null;

        try {
            $db->beginTransaction();

            $updateBookingStmt = $db->prepare(
                'UPDATE bookings
                 SET status = :status,
                     updated_at = NOW()
                 WHERE id = :booking_id'
            );
            $updateBookingStmt->execute([
                'status' => $bookingStatus,
                'booking_id' => $bookingId,
            ]);

            $updateAssignmentStmt = $db->prepare(
                'UPDATE assignments
                 SET service_status = :service_status,
                     done_at = :done_at
                 WHERE booking_id = :booking_id
                   AND operator_user_id = :operator_user_id'
            );
            $updateAssignmentStmt->execute([
                'service_status' => $serviceStatus,
                'done_at' => $doneAt,
                'booking_id' => $bookingId,
                'operator_user_id' => $currentUserId,
            ]);

            if ((string) ($row['status'] ?? '') !== $bookingStatus) {
                $historyStmt = $db->prepare(
                    'INSERT INTO booking_status_history (booking_id, old_status, new_status, changed_by, note, created_at)
                     VALUES (:booking_id, :old_status, :new_status, :changed_by, :note, NOW())'
                );
                $historyStmt->execute([
                    'booking_id' => $bookingId,
                    'old_status' => (string) ($row['status'] ?? ''),
                    'new_status' => $bookingStatus,
                    'changed_by' => $currentUserId,
                    'note' => 'Status updated from operator agenda.',
                ]);
            }

            $db->commit();
        } catch (Throwable) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
        }
    }

    private function buildAgendaStats(array $agendaItems): array
    {
        $stats = [
            'total_services' => count($agendaItems),
            'arrivals' => 0,
            'departures' => 0,
            'assigned' => 0,
            'provider_services' => 0,
            'done' => 0,
            'by_operator' => [],
        ];

        foreach ($agendaItems as $item) {
            $leg = (string) ($item['service_leg'] ?? 'ARRIVAL');
            $status = (string) ($item['service_status'] ?? 'PENDING');
            $mode = (string) ($item['mode'] ?? 'INTERNAL');
            $operatorKey = (string) ($item['operator_user_id'] ?? '0');
            $operatorName = trim((string) ($item['operator_name'] ?? '')) !== '' ? (string) $item['operator_name'] : 'Sin asignar';

            if ($leg === 'DEPARTURE') {
                $stats['departures']++;
            } else {
                $stats['arrivals']++;
            }

            if ($status !== 'PENDING') {
                $stats['assigned']++;
            }

            if ($mode === 'PROVIDER') {
                $stats['provider_services']++;
            }

            if ($status === 'DONE') {
                $stats['done']++;
            }

            if (!isset($stats['by_operator'][$operatorKey])) {
                $stats['by_operator'][$operatorKey] = [
                    'operator_name' => $operatorName,
                    'total' => 0,
                    'arrivals' => 0,
                    'departures' => 0,
                    'done' => 0,
                ];
            }

            $stats['by_operator'][$operatorKey]['total']++;
            if ($leg === 'DEPARTURE') {
                $stats['by_operator'][$operatorKey]['departures']++;
            } else {
                $stats['by_operator'][$operatorKey]['arrivals']++;
            }
            if ($status === 'DONE') {
                $stats['by_operator'][$operatorKey]['done']++;
            }
        }

        uasort($stats['by_operator'], static fn (array $a, array $b): int => $b['total'] <=> $a['total']);
        $stats['by_operator'] = array_values($stats['by_operator']);

        return $stats;
    }

    private function mapOperatorBookingStatusToServiceStatus(string $bookingStatus, string $currentServiceStatus): string
    {
        return match ($bookingStatus) {
            'COMPLETED' => 'DONE',
            'NO_SHOW' => 'NO_SHOW',
            'CANCELLED' => in_array($currentServiceStatus, self::STATUS_OPTIONS, true) ? $currentServiceStatus : 'PENDING',
            default => 'PENDING',
        };
    }

    private function resolveDateRange(string $presetRaw, string $startRaw, string $endRaw, string $legacyDateRaw): array
    {
        $today = new DateTimeImmutable('today');
        $preset = strtoupper(trim($presetRaw));

        if ($preset === '' && trim($legacyDateRaw) !== '') {
            $date = $this->normalizeDate($legacyDateRaw) ?? $today->format('Y-m-d');
            return [
                'preset' => 'CUSTOM',
                'start_date' => $date,
                'end_date' => $date,
            ];
        }

        if (!in_array($preset, self::RANGE_PRESETS, true)) {
            $preset = 'THIS_WEEK';
        }

        if ($preset === 'CUSTOM') {
            $startDate = $this->normalizeDate($startRaw) ?? $today->format('Y-m-d');
            $endDate = $this->normalizeDate($endRaw) ?? $startDate;

            if ($startDate > $endDate) {
                [$startDate, $endDate] = [$endDate, $startDate];
            }

            return [
                'preset' => 'CUSTOM',
                'start_date' => $startDate,
                'end_date' => $endDate,
            ];
        }

        return match ($preset) {
            'TODAY' => [
                'preset' => 'TODAY',
                'start_date' => $today->format('Y-m-d'),
                'end_date' => $today->format('Y-m-d'),
            ],
            'LAST_WEEK' => [
                'preset' => 'LAST_WEEK',
                'start_date' => $today->modify('monday last week')->format('Y-m-d'),
                'end_date' => $today->modify('sunday last week')->format('Y-m-d'),
            ],
            'NEXT_7_DAYS' => [
                'preset' => 'NEXT_7_DAYS',
                'start_date' => $today->format('Y-m-d'),
                'end_date' => $today->modify('+6 days')->format('Y-m-d'),
            ],
            default => [
                'preset' => 'THIS_WEEK',
                'start_date' => $today->modify('monday this week')->format('Y-m-d'),
                'end_date' => $today->modify('sunday this week')->format('Y-m-d'),
            ],
        };
    }

    private function normalizeDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d', $value);
        return $date instanceof DateTimeImmutable ? $date->format('Y-m-d') : null;
    }

    private function normalizePositiveInt(string $value): ?int
    {
        $value = trim($value);

        if ($value === '' || !ctype_digit($value) || (int) $value <= 0) {
            return null;
        }

        return (int) $value;
    }

    private function normalizeStatus(string $value): ?string
    {
        $value = strtoupper(trim($value));

        if ($value === '' || !in_array($value, self::STATUS_OPTIONS, true)) {
            return null;
        }

        return $value;
    }

    private function normalizeMode(string $value): ?string
    {
        $value = strtoupper(trim($value));

        if ($value === '' || !in_array($value, self::MODE_OPTIONS, true)) {
            return null;
        }

        return $value;
    }

    private function isOperatorScope(): bool
    {
        return ACL::currentUserHasRole('operator') && !ACL::currentUserHasRole('admin');
    }
}
