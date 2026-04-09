<?php
declare(strict_types=1);
namespace App\Http\Controllers\Admin;

use App\Core\ACL;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\DB;
use App\Core\Request;
use App\Core\Response;

class BookingsController {
    private const BOOKING_STATUSES = ['PENDING', 'CONFIRMED', 'COMPLETED', 'NO_SHOW', 'CANCELLED'];
    private const PAYMENT_STATUSES = ['UNPAID', 'PARTIAL', 'PAID', 'REFUNDED'];

    public function index(Request $request): Response
    {
        if (ACL::currentUserHasRole('operator') && !ACL::currentUserHasRole('admin')) {
            return Response::redirect('/admin/operations/agenda');
        }

        $db = DB::connection();

        $stmt = $db->query("
            SELECT
                b.id,
                b.booking_code,
                b.customer_name,
                b.customer_last_name,
                b.customer_email,
                b.customer_phone,
                b.trip_type,
                b.direction,
                b.arrival_datetime,
                b.departure_datetime,
                b.airline,
                b.flight_number,
                b.price_total,
                b.currency_code,
                b.status,
                b.payment_status,
                b.created_at,
                st.name_es AS service_name,
                z.name_es AS zone_name,
                p.name AS place_name,
                bp.total_pax
            FROM bookings b
            INNER JOIN service_types st ON st.id = b.service_type_id
            INNER JOIN zones z ON z.id = b.zone_id
            INNER JOIN places p ON p.id = b.place_id
            LEFT JOIN booking_passengers bp ON bp.booking_id = b.id
            ORDER BY COALESCE(b.arrival_datetime, b.departure_datetime, b.created_at) DESC, b.id DESC
            LIMIT 100
        ");

        $bookings = $stmt->fetchAll();

        return Response::view('admin/bookings/index', [
            'title' => 'Reservas',
            'bookings' => $bookings,
        ], 'admin');
    }

    public function create(Request $request): Response
    {
        $db = DB::connection();

        $serviceTypesStmt = $db->query('SELECT id, name_es FROM service_types WHERE is_active = 1 ORDER BY sort_order ASC, name_es ASC');
        $serviceTypes = $serviceTypesStmt->fetchAll();

        $zonesStmt = $db->query('SELECT id, name_es FROM zones WHERE is_active = 1 ORDER BY sort_order ASC, name_es ASC');
        $zones = $zonesStmt->fetchAll();

        $allPlacesStmt = $db->query(
            'SELECT id, zone_id, name, type
             FROM places
             WHERE is_active = 1
             ORDER BY zone_id ASC, name ASC'
        );
        $allPlaces = $allPlacesStmt->fetchAll();

        $zoneIdFromRequest = (int) ($request->method() === 'POST'
            ? $request->post('zone_id', 0)
            : $request->query('zone_id', 0));
        $selectedZoneId = $zoneIdFromRequest > 0
            ? $zoneIdFromRequest
            : (int) ($zones[0]['id'] ?? 0);

        if ($request->method() === 'GET') {
            return Response::view('admin/bookings/create', [
                'title' => 'Nueva Reserva Manual',
                'csrf_token' => Csrf::token(),
                'service_types' => $serviceTypes,
                'zones' => $zones,
                'places' => $allPlaces,
                'errors' => [],
                'form' => [
                    'trip_type' => 'ONE_WAY',
                    'direction' => 'AIRPORT_TO_DESTINATION',
                    'service_type_id' => (string) ($serviceTypes[0]['id'] ?? ''),
                    'zone_id' => (string) $selectedZoneId,
                    'place_id' => '',
                    'currency_code' => 'USD',
                    'price_total' => '0.00',
                    'status' => 'PENDING',
                    'payment_status' => 'UNPAID',
                    'arrival_datetime' => '',
                    'departure_datetime' => '',
                    'airline' => '',
                    'flight_number' => '',
                    'pickup_notes' => '',
                    'customer_name' => '',
                    'customer_last_name' => '',
                    'customer_email' => '',
                    'customer_phone' => '',
                    'comments' => '',
                    'adults' => '1',
                    'children' => '0',
                ],
            ], 'admin');
        }

        if (!Csrf::validate((string) $request->post('csrf_token', ''))) {
            return Response::redirect('/admin/bookings/create');
        }

        $form = [
            'trip_type' => strtoupper(trim((string) $request->post('trip_type', 'ONE_WAY'))),
            'direction' => strtoupper(trim((string) $request->post('direction', 'AIRPORT_TO_DESTINATION'))),
            'service_type_id' => trim((string) $request->post('service_type_id', '')),
            'zone_id' => trim((string) $request->post('zone_id', '')),
            'place_id' => trim((string) $request->post('place_id', '')),
            'currency_code' => strtoupper(trim((string) $request->post('currency_code', 'USD'))),
            'price_total' => trim((string) $request->post('price_total', '0.00')),
            'status' => strtoupper(trim((string) $request->post('status', 'PENDING'))),
            'payment_status' => strtoupper(trim((string) $request->post('payment_status', 'UNPAID'))),
            'arrival_datetime' => trim((string) $request->post('arrival_datetime', '')),
            'departure_datetime' => trim((string) $request->post('departure_datetime', '')),
            'airline' => trim((string) $request->post('airline', '')),
            'flight_number' => trim((string) $request->post('flight_number', '')),
            'pickup_notes' => trim((string) $request->post('pickup_notes', '')),
            'customer_name' => trim((string) $request->post('customer_name', '')),
            'customer_last_name' => trim((string) $request->post('customer_last_name', '')),
            'customer_email' => trim((string) $request->post('customer_email', '')),
            'customer_phone' => trim((string) $request->post('customer_phone', '')),
            'comments' => trim((string) $request->post('comments', '')),
            'adults' => trim((string) $request->post('adults', '1')),
            'children' => trim((string) $request->post('children', '0')),
        ];

        $errors = [];

        if ($form['customer_name'] === '') {
            $errors['customer_name'] = 'Nombre de cliente requerido.';
        }
        if ($form['customer_email'] === '' || !filter_var($form['customer_email'], FILTER_VALIDATE_EMAIL)) {
            $errors['customer_email'] = 'Email valido requerido.';
        }

        if (!ctype_digit($form['service_type_id']) || (int) $form['service_type_id'] <= 0) {
            $errors['service_type_id'] = 'Servicio invalido.';
        }
        if (!ctype_digit($form['zone_id']) || (int) $form['zone_id'] <= 0) {
            $errors['zone_id'] = 'Zona invalida.';
        }
        if (!ctype_digit($form['place_id']) || (int) $form['place_id'] <= 0) {
            $errors['place_id'] = 'Place invalido.';
        }

        if (!in_array($form['trip_type'], ['ONE_WAY', 'ROUND_TRIP'], true)) {
            $errors['trip_type'] = 'Tipo de viaje invalido.';
        }
        if (!in_array($form['direction'], ['AIRPORT_TO_DESTINATION', 'DESTINATION_TO_AIRPORT'], true)) {
            $errors['direction'] = 'Direccion invalida.';
        }
        if (!in_array($form['status'], self::BOOKING_STATUSES, true)) {
            $errors['status'] = 'Estado invalido.';
        }
        if (!in_array($form['payment_status'], self::PAYMENT_STATUSES, true)) {
            $errors['payment_status'] = 'Estado de pago invalido.';
        }
        if (!preg_match('/^[A-Z]{3}$/', $form['currency_code'])) {
            $errors['currency_code'] = 'Moneda invalida.';
        }

        $priceTotal = is_numeric($form['price_total']) ? (float) $form['price_total'] : -1;
        if ($priceTotal < 0) {
            $errors['price_total'] = 'Total invalido.';
        }

        $adults = ctype_digit($form['adults']) ? (int) $form['adults'] : 0;
        $children = ctype_digit($form['children']) ? (int) $form['children'] : -1;
        if ($adults < 1) {
            $errors['adults'] = 'Adults debe ser minimo 1.';
        }
        if ($children < 0) {
            $errors['children'] = 'Children invalido.';
        }

        $zoneId = (int) $form['zone_id'];
        $placeId = (int) $form['place_id'];

        if ($zoneId > 0 && $placeId > 0) {
            $placeCheckStmt = $db->prepare(
                'SELECT id FROM places WHERE id = :place_id AND zone_id = :zone_id LIMIT 1'
            );
            $placeCheckStmt->execute([
                'place_id' => $placeId,
                'zone_id' => $zoneId,
            ]);
            if (!$placeCheckStmt->fetch()) {
                $errors['place_id'] = 'El place no pertenece a la zona seleccionada.';
            }
        }

        if (!empty($errors)) {
            return Response::view('admin/bookings/create', [
                'title' => 'Nueva Reserva Manual',
                'csrf_token' => Csrf::token(),
                'service_types' => $serviceTypes,
                'zones' => $zones,
                'places' => $allPlaces,
                'errors' => $errors,
                'form' => $form,
            ], 'admin');
        }

        $bookingCode = $this->generateBookingCode();

        $insertStmt = $db->prepare(
            'INSERT INTO bookings (
                booking_code,
                trip_type,
                direction,
                service_type_id,
                zone_id,
                place_id,
                currency_code,
                price_total,
                status,
                payment_status,
                arrival_datetime,
                departure_datetime,
                airline,
                flight_number,
                pickup_notes,
                customer_name,
                customer_last_name,
                customer_email,
                customer_phone,
                comments,
                created_at,
                updated_at
            ) VALUES (
                :booking_code,
                :trip_type,
                :direction,
                :service_type_id,
                :zone_id,
                :place_id,
                :currency_code,
                :price_total,
                :status,
                :payment_status,
                :arrival_datetime,
                :departure_datetime,
                :airline,
                :flight_number,
                :pickup_notes,
                :customer_name,
                :customer_last_name,
                :customer_email,
                :customer_phone,
                :comments,
                NOW(),
                NULL
            )'
        );

        $insertStmt->execute([
            'booking_code' => $bookingCode,
            'trip_type' => $form['trip_type'],
            'direction' => $form['direction'],
            'service_type_id' => (int) $form['service_type_id'],
            'zone_id' => $zoneId,
            'place_id' => $placeId,
            'currency_code' => $form['currency_code'],
            'price_total' => number_format($priceTotal, 2, '.', ''),
            'status' => $form['status'],
            'payment_status' => $form['payment_status'],
            'arrival_datetime' => $form['arrival_datetime'] !== '' ? $form['arrival_datetime'] : null,
            'departure_datetime' => $form['departure_datetime'] !== '' ? $form['departure_datetime'] : null,
            'airline' => $form['airline'] !== '' ? $form['airline'] : null,
            'flight_number' => $form['flight_number'] !== '' ? $form['flight_number'] : null,
            'pickup_notes' => $form['pickup_notes'] !== '' ? $form['pickup_notes'] : null,
            'customer_name' => $form['customer_name'],
            'customer_last_name' => $form['customer_last_name'] !== '' ? $form['customer_last_name'] : null,
            'customer_email' => $form['customer_email'],
            'customer_phone' => $form['customer_phone'] !== '' ? $form['customer_phone'] : null,
            'comments' => $form['comments'] !== '' ? $form['comments'] : null,
        ]);

        $bookingId = (int) $db->lastInsertId();

        $passengersStmt = $db->prepare(
            'INSERT INTO booking_passengers (booking_id, adults, children, total_pax)
             VALUES (:booking_id, :adults, :children, :total_pax)'
        );
        $passengersStmt->execute([
            'booking_id' => $bookingId,
            'adults' => $adults,
            'children' => $children,
            'total_pax' => $adults + $children,
        ]);

        return Response::redirect('/admin/bookings');
    }

    public function edit(Request $request): Response
    {
        $id = (int) $request->query('id', 0);
        if ($id <= 0) {
            return Response::redirect('/admin/bookings');
        }

        $db = DB::connection();
        $booking = $this->loadBookingDetails($db, $id);

        if ($booking === null) {
            return Response::redirect('/admin/bookings');
        }

        return Response::view('admin/bookings/edit', [
            'title' => 'Editar Booking',
            'booking' => $booking,
            'booking_statuses' => self::BOOKING_STATUSES,
            'payment_statuses' => self::PAYMENT_STATUSES,
        ], 'admin');
    }

    public function update(Request $request): Response
    {
        if (!Csrf::validate((string) $request->post('csrf_token', ''))) {
            return Response::redirect('/admin/bookings');
        }

        $id = (int) $request->post('id', 0);
        $status = strtoupper(trim((string) $request->post('status', '')));
        $paymentStatus = strtoupper(trim((string) $request->post('payment_status', '')));

        if ($id <= 0 || !in_array($status, self::BOOKING_STATUSES, true) || !in_array($paymentStatus, self::PAYMENT_STATUSES, true)) {
            return Response::redirect('/admin/bookings');
        }

        $db = DB::connection();
        $currentStmt = $db->prepare('SELECT status, payment_status FROM bookings WHERE id = :id LIMIT 1');
        $currentStmt->execute(['id' => $id]);
        $current = $currentStmt->fetch();

        if (!is_array($current)) {
            return Response::redirect('/admin/bookings');
        }

        $db->beginTransaction();

        try {
            $stmt = $db->prepare(
                'UPDATE bookings
                 SET status = :status,
                     payment_status = :payment_status,
                     updated_at = NOW()
                 WHERE id = :id'
            );
            $stmt->execute([
                'status' => $status,
                'payment_status' => $paymentStatus,
                'id' => $id,
            ]);

            if ((string) ($current['status'] ?? '') !== $status) {
                $historyStmt = $db->prepare(
                    'INSERT INTO booking_status_history (booking_id, old_status, new_status, changed_by, note, created_at)
                     VALUES (:booking_id, :old_status, :new_status, :changed_by, :note, NOW())'
                );
                $historyStmt->execute([
                    'booking_id' => $id,
                    'old_status' => (string) ($current['status'] ?? ''),
                    'new_status' => $status,
                    'changed_by' => Auth::id(),
                    'note' => 'Status updated from admin bookings.',
                ]);
            }

            $db->commit();
        } catch (\Throwable) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
        }

        return Response::redirect('/admin/bookings/edit?id=' . $id);
    }

    private function loadBookingDetails(\PDO $db, int $id): ?array
    {
        $stmt = $db->prepare(
            'SELECT
                b.*,
                st.name_es AS service_type_name,
                z.name_es AS zone_name,
                p.name AS place_name,
                bp.adults,
                bp.children,
                bp.total_pax,
                a.service_status AS assignment_status,
                u.name AS operator_name,
                wo.work_date,
                wo.notes AS work_order_notes
             FROM bookings b
             INNER JOIN service_types st ON st.id = b.service_type_id
             INNER JOIN zones z ON z.id = b.zone_id
             INNER JOIN places p ON p.id = b.place_id
             LEFT JOIN booking_passengers bp ON bp.booking_id = b.id
             LEFT JOIN assignments a ON a.booking_id = b.id
             LEFT JOIN users u ON u.id = a.operator_user_id
             LEFT JOIN work_orders wo ON wo.booking_id = b.id
             WHERE b.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $booking = $stmt->fetch();

        return is_array($booking) ? $booking : null;
    }

    private function generateBookingCode(): string
    {
        $date = date('Ymd');
        $suffix = strtoupper(bin2hex(random_bytes(2)));

        return 'KTR-' . $date . '-' . $suffix;
    }
}
