<?php
declare(strict_types=1);
namespace App\Http\Controllers\Public;

use App\Core\Csrf;
use App\Core\DB;
use App\Core\Request;
use App\Core\Response;

class CheckoutController {
    public function start(Request $request): Response
    {
        if (!Csrf::validate((string) $request->post('_csrf', ''))) {
            return Response::redirect('/');
        }

        $searchContext = $request->sessionGet('search_context');
        if (!is_array($searchContext)) {
            return Response::redirect('/');
        }

        $quoteOptions = $request->sessionGet('quote_options');
        if (!is_array($quoteOptions)) {
            return Response::redirect('/');
        }

        $rateRuleId = (int) $request->post('rate_rule_id', 0);
        $selectedOption = $quoteOptions[$rateRuleId] ?? null;
        if (!is_array($selectedOption)) {
            return Response::redirect('/');
        }

        $bookingCode = $request->sessionGet('booking_code');
        if (!is_string($bookingCode) || $bookingCode === '') {
            $bookingCode = $this->generateBookingCode();
            $request->sessionSet('booking_code', $bookingCode);
        }

        $request->sessionSet('checkout_selection', [
            'rate_rule_id' => (int) $selectedOption['rate_rule_id'],
            'service_type_id' => (int) $selectedOption['service_type_id'],
            'quoted_price' => (float) $selectedOption['quoted_price'],
            'currency_code' => strtoupper((string) $selectedOption['currency_code']),
        ]);

        return Response::redirect('/checkout/details');
    }

    public function details(Request $request): Response
    {
        $bookingCode = (string) $request->sessionGet('booking_code', '');
        if ($bookingCode === '') {
            return Response::redirect('/');
        }

        $db = DB::connection();
        $airlinesStmt = $db->query('SELECT id, code, name FROM airlines WHERE is_active = 1 ORDER BY name ASC');
        $airlines = $airlinesStmt->fetchAll();

        return Response::view('public/checkout_details', [
            'title' => 'Checkout - Details',
            'booking_code' => $bookingCode,
            'csrf_token' => Csrf::token(),
            'airlines' => $airlines,
        ]);
    }

    public function saveDetails(Request $request): Response
    {
        if (!Csrf::validate((string) $request->post('_csrf', ''))) {
            return Response::redirect('/checkout/details');
        }

        $airlineId = (int) $request->post('airline_id', 0);
        $airlineName = '';
        
        // Si seleccionó una aerolínea, obtener su nombre
        if ($airlineId > 0) {
            $db = DB::connection();
            $stmt = $db->prepare('SELECT name FROM airlines WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => $airlineId]);
            $airline = $stmt->fetch();
            $airlineName = $airline['name'] ?? '';
        }

        $request->sessionSet('customer_details', [
            'customer_name' => trim((string) $request->post('customer_name', '')),
            'customer_last_name' => trim((string) $request->post('customer_last_name', '')),
            'customer_email' => trim((string) $request->post('customer_email', '')),
            'customer_phone' => trim((string) $request->post('customer_phone', '')),
            'airline' => $airlineName,
            'flight_number' => trim((string) $request->post('flight_number', '')),
            'pickup_notes' => trim((string) $request->post('pickup_notes', '')),
        ]);

        return Response::redirect('/checkout/payment');
    }

    public function payment(Request $request): Response
    {
        $bookingCode = (string) $request->sessionGet('booking_code', '');
        if ($bookingCode === '') {
            return Response::redirect('/');
        }

        return Response::view('public/checkout_payment', [
            'title' => 'Checkout - Payment',
            'booking_code' => $bookingCode,
            'csrf_token' => Csrf::token(),
        ]);
    }

    public function pay(Request $request): Response
    {
        if (!Csrf::validate((string) $request->post('_csrf', ''))) {
            return Response::redirect('/checkout/payment');
        }

        $bookingCode = (string) $request->sessionGet('booking_code', '');
        $searchContext = $request->sessionGet('search_context');
        $checkoutSelection = $request->sessionGet('checkout_selection');
        $customerDetails = $request->sessionGet('customer_details');

        if (!is_array($searchContext) || !is_array($checkoutSelection) || !is_array($customerDetails)) {
            return Response::redirect('/');
        }

        try {
            $db = DB::connection();
            $db->beginTransaction();

            // Crear el booking
            $stmt = $db->prepare(
                'INSERT INTO bookings (
                    booking_code, trip_type, direction, service_type_id, zone_id, place_id,
                    currency_code, price_total, status, payment_status,
                    arrival_datetime, departure_datetime, airline, flight_number, pickup_notes,
                    customer_name, customer_last_name, customer_email, customer_phone,
                    created_at, updated_at
                ) VALUES (
                    :booking_code, :trip_type, :direction, :service_type_id, :zone_id, :place_id,
                    :currency_code, :price_total, :status, :payment_status,
                    :arrival_datetime, :departure_datetime, :airline, :flight_number, :pickup_notes,
                    :customer_name, :customer_last_name, :customer_email, :customer_phone,
                    NOW(), NOW()
                )'
            );

            $stmt->execute([
                'booking_code' => $bookingCode,
                'trip_type' => $searchContext['trip_type'],
                'direction' => $searchContext['direction'],
                'service_type_id' => $checkoutSelection['service_type_id'],
                'zone_id' => $searchContext['zone_id'],
                'place_id' => $searchContext['place_id'],
                'currency_code' => $checkoutSelection['currency_code'],
                'price_total' => $checkoutSelection['quoted_price'],
                'status' => 'PENDING',
                'payment_status' => 'UNPAID',
                'arrival_datetime' => $searchContext['arrival_datetime'] ?: null,
                'departure_datetime' => $searchContext['departure_datetime'] ?: null,
                'airline' => $customerDetails['airline'] ?? null,
                'flight_number' => $customerDetails['flight_number'] ?? null,
                'pickup_notes' => $customerDetails['pickup_notes'] ?? null,
                'customer_name' => $customerDetails['customer_name'],
                'customer_last_name' => $customerDetails['customer_last_name'] ?? null,
                'customer_email' => $customerDetails['customer_email'],
                'customer_phone' => $customerDetails['customer_phone'] ?? null,
            ]);

            $bookingId = (int) $db->lastInsertId();

            // Guardar pasajeros
            $stmtPax = $db->prepare(
                'INSERT INTO booking_passengers (booking_id, adults, children, total_pax)
                 VALUES (:booking_id, :adults, :children, :total_pax)'
            );
            $stmtPax->execute([
                'booking_id' => $bookingId,
                'adults' => $searchContext['adults'],
                'children' => $searchContext['children'],
                'total_pax' => $searchContext['total_pax'],
            ]);

            $db->commit();

            // Guardar booking_id en sesión
            $request->sessionSet('booking_id', $bookingId);

            return Response::redirect('/checkout/confirmation');
        } catch (\Throwable $e) {
            if (isset($db)) {
                $db->rollBack();
            }
            error_log('Error creating booking: ' . $e->getMessage());
            return Response::redirect('/checkout/payment');
        }
    }

    public function confirmation(Request $request): Response
    {
        $bookingId = (int) $request->sessionGet('booking_id', 0);
        if ($bookingId === 0) {
            return Response::redirect('/');
        }

        try {
            $db = DB::connection();
            $stmt = $db->prepare(
                'SELECT b.*, 
                        st.name_es AS service_type_name,
                        z.name_es AS zone_name,
                        p.name AS place_name,
                        bp.adults, bp.children, bp.total_pax
                 FROM bookings b
                 INNER JOIN service_types st ON st.id = b.service_type_id
                 INNER JOIN zones z ON z.id = b.zone_id
                 INNER JOIN places p ON p.id = b.place_id
                 LEFT JOIN booking_passengers bp ON bp.booking_id = b.id
                 WHERE b.id = :id
                 LIMIT 1'
            );
            $stmt->execute(['id' => $bookingId]);
            $booking = $stmt->fetch();

            if (!$booking) {
                return Response::redirect('/');
            }

            // Limpiar sesión de checkout
            $request->sessionSet('search_context', null);
            $request->sessionSet('checkout_selection', null);
            $request->sessionSet('customer_details', null);
            $request->sessionSet('quote_options', null);
            $request->sessionSet('booking_code', null);

            return Response::view('public/checkout_confirmation', [
                'title' => 'Confirmación de Reserva',
                'booking' => $booking,
            ]);
        } catch (\Throwable $e) {
            error_log('Error loading booking confirmation: ' . $e->getMessage());
            return Response::redirect('/');
        }
    }

    private function generateBookingCode(): string
    {
        $date = date('Ymd');
        $suffix = strtoupper(bin2hex(random_bytes(2)));

        return 'KTR-' . $date . '-' . $suffix;
    }
}
