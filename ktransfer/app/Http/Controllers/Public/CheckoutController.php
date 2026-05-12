<?php
declare(strict_types=1);
namespace App\Http\Controllers\Public;

use App\Core\Csrf;
use App\Core\DB;
use App\Core\Request;
use App\Core\Response;
use App\Services\BrandingService;
use App\Services\HomeContentService;

class CheckoutController {
    private const DEFAULT_AIRPORT_LABEL = 'Aeropuerto de Cancun';
    private const MANUAL_PAYMENT_METHODS = ['MANUAL', 'BANK', 'CASH', 'CARD', 'PAYPAL'];
    private const MERCADO_PAGO_API_BASE = 'https://api.mercadopago.com';

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
            'terminal' => trim((string) $request->post('terminal', '')),
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
            'mercado_pago_enabled' => $this->mercadoPagoIsConfigured(),
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
        $paymentMethod = $this->normalizeManualPaymentMethod((string) $request->post('payment_method', 'MANUAL'));

        if (!is_array($searchContext) || !is_array($checkoutSelection) || !is_array($customerDetails)) {
            return Response::redirect('/');
        }

        try {
            $booking = $this->createPendingCheckoutBooking($request, $paymentMethod, 'Checkout manual pendiente');

            // Guardar booking_id en sesión
            $request->sessionSet('booking_id', (int) $booking['booking_id']);

            return Response::redirect('/checkout/confirmation');
        } catch (\Throwable $e) {
            error_log('Error creating booking: ' . $e->getMessage());
            return Response::redirect('/checkout/payment');
        }
    }

    public function startMercadoPago(Request $request): Response
    {
        if (!Csrf::validate((string) $request->post('_csrf', ''))) {
            return Response::redirect('/checkout/payment');
        }

        $settings = $this->mercadoPagoSettings();
        if (empty($settings['enabled']) || trim((string) ($settings['access_token'] ?? '')) === '') {
            return Response::redirect('/checkout/payment');
        }

        try {
            $booking = $this->createPendingCheckoutBooking($request, 'MERCADO_PAGO', 'Mercado Pago Checkout Pro pendiente');
            $request->sessionSet('booking_id', (int) $booking['booking_id']);

            $preference = $this->createMercadoPagoPreference($booking, (string) $settings['access_token']);
            $preferenceId = (string) ($preference['id'] ?? '');
            if ($preferenceId !== '') {
                $this->updatePaymentReference((int) $booking['payment_id'], 'mp_pref:' . $preferenceId);
            }

            $redirectUrl = $this->mercadoPagoRedirectUrl($preference, (string) $settings['access_token']);
            if ($redirectUrl === '') {
                throw new \RuntimeException('Mercado Pago preference did not return redirect URL.');
            }

            return Response::redirect($redirectUrl);
        } catch (\Throwable $e) {
            error_log('Mercado Pago start error: ' . $e->getMessage());
            return Response::redirect('/checkout/payment');
        }
    }

    public function mercadoPagoReturn(Request $request): Response
    {
        $paymentId = trim((string) ($request->query('payment_id', '') ?: $request->query('collection_id', '')));
        if ($paymentId !== '') {
            $this->syncMercadoPagoPayment($paymentId);
        }

        $bookingId = (int) $request->query('booking_id', 0);
        if ($bookingId > 0) {
            $request->sessionSet('booking_id', $bookingId);
        }

        return Response::redirect('/checkout/confirmation');
    }

    public function mercadoPagoWebhook(Request $request): Response
    {
        $payload = json_decode((string) file_get_contents('php://input'), true);
        $topic = (string) ($request->query('topic', '') ?: $request->query('type', '') ?: ($payload['type'] ?? ''));
        $paymentId = (string) (
            $request->query('data.id', '')
            ?: $request->query('data_id', '')
            ?: $request->query('id', '')
            ?: ($payload['data']['id'] ?? '')
            ?: ($payload['id'] ?? '')
        );

        if ($topic === '' || str_contains(strtolower($topic), 'payment')) {
            if (trim($paymentId) !== '') {
                $this->syncMercadoPagoPayment(trim($paymentId));
            }
        }

        return Response::json(['ok' => true]);
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

    public function voucher(Request $request): Response
    {
        $bookingId = (int) $request->sessionGet('booking_id', 0);
        if ($bookingId === 0) {
            return Response::redirect('/');
        }

        try {
            $booking = $this->loadBookingDetails(DB::connection(), $bookingId);
            if ($booking === null) {
                return Response::redirect('/');
            }

            return Response::view('admin/bookings/printable', [
                'title' => 'Voucher ' . (string) ($booking['booking_code'] ?? ''),
                'booking' => $booking,
                'document_type' => 'voucher',
                'brand_logo' => $this->resolveBrandLogoPath(),
                'back_url' => '/checkout/confirmation',
            ], null);
        } catch (\Throwable $e) {
            error_log('Error loading booking voucher: ' . $e->getMessage());
            return Response::redirect('/');
        }
    }

    private function generateBookingCode(): string
    {
        return (new BrandingService())->generateBookingCode();
    }

    private function createPendingCheckoutBooking(Request $request, string $paymentMethod, string $paymentReference): array
    {
        $bookingCode = (string) $request->sessionGet('booking_code', '');
        $searchContext = $request->sessionGet('search_context');
        $checkoutSelection = $request->sessionGet('checkout_selection');
        $customerDetails = $request->sessionGet('customer_details');

        if ($bookingCode === '' || !is_array($searchContext) || !is_array($checkoutSelection) || !is_array($customerDetails)) {
            throw new \RuntimeException('Checkout session is incomplete.');
        }

        $db = DB::connection();
        $db->beginTransaction();

        try {
            $existingStmt = $db->prepare('SELECT id, booking_code, price_total, currency_code, customer_email FROM bookings WHERE booking_code = :booking_code LIMIT 1');
            $existingStmt->execute(['booking_code' => $bookingCode]);
            $existingBooking = $existingStmt->fetch();

            if (is_array($existingBooking)) {
                $bookingId = (int) $existingBooking['id'];
                $paymentId = $this->ensurePendingPayment($db, $bookingId, $paymentMethod, (float) $existingBooking['price_total'], (string) $existingBooking['currency_code'], $paymentReference);
                $db->commit();

                return [
                    'booking_id' => $bookingId,
                    'payment_id' => $paymentId,
                    'booking_code' => (string) $existingBooking['booking_code'],
                    'price_total' => (float) $existingBooking['price_total'],
                    'currency_code' => (string) $existingBooking['currency_code'],
                    'customer_email' => (string) ($existingBooking['customer_email'] ?? ''),
                    'customer_name' => (string) ($customerDetails['customer_name'] ?? ''),
                    'customer_last_name' => (string) ($customerDetails['customer_last_name'] ?? ''),
                ];
            }

            $routeLabels = $this->resolveRouteLabels(
                (string) ($searchContext['direction'] ?? 'AIRPORT_TO_DESTINATION'),
                (string) ($searchContext['place_name'] ?? '')
            );

            $stmt = $db->prepare(
                'INSERT INTO bookings (
                    booking_code, trip_type, direction, service_type_id, zone_id, place_id, origin_name, destination_name,
                    currency_code, price_total, status, payment_status,
                    arrival_datetime, departure_datetime, airline, flight_number, terminal, pickup_notes,
                    customer_name, customer_last_name, customer_email, customer_phone, agency_name,
                    created_at, updated_at
                ) VALUES (
                    :booking_code, :trip_type, :direction, :service_type_id, :zone_id, :place_id, :origin_name, :destination_name,
                    :currency_code, :price_total, :status, :payment_status,
                    :arrival_datetime, :departure_datetime, :airline, :flight_number, :terminal, :pickup_notes,
                    :customer_name, :customer_last_name, :customer_email, :customer_phone, :agency_name,
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
                'origin_name' => $routeLabels['origin_name'],
                'destination_name' => $routeLabels['destination_name'],
                'currency_code' => $checkoutSelection['currency_code'],
                'price_total' => $checkoutSelection['quoted_price'],
                'status' => 'PENDING',
                'payment_status' => 'UNPAID',
                'arrival_datetime' => $searchContext['arrival_datetime'] ?: null,
                'departure_datetime' => $searchContext['departure_datetime'] ?: null,
                'airline' => $customerDetails['airline'] ?? null,
                'flight_number' => $customerDetails['flight_number'] ?? null,
                'terminal' => ($customerDetails['terminal'] ?? '') !== '' ? $customerDetails['terminal'] : null,
                'pickup_notes' => $customerDetails['pickup_notes'] ?? null,
                'customer_name' => $customerDetails['customer_name'],
                'customer_last_name' => $customerDetails['customer_last_name'] ?? null,
                'customer_email' => $customerDetails['customer_email'],
                'customer_phone' => $customerDetails['customer_phone'] ?? null,
                'agency_name' => 'WEB DIRECTO',
            ]);

            $bookingId = (int) $db->lastInsertId();

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

            $paymentId = $this->ensurePendingPayment(
                $db,
                $bookingId,
                $paymentMethod,
                (float) $checkoutSelection['quoted_price'],
                (string) $checkoutSelection['currency_code'],
                $paymentReference
            );

            $historyStmt = $db->prepare(
                'INSERT INTO booking_status_history (booking_id, old_status, new_status, changed_by, note, created_at)
                 VALUES (:booking_id, NULL, :new_status, NULL, :note, NOW())'
            );
            $historyStmt->execute([
                'booking_id' => $bookingId,
                'new_status' => 'PENDING',
                'note' => 'Reserva creada desde checkout publico. Pago pendiente por metodo: ' . $paymentMethod,
            ]);

            $db->commit();

            return [
                'booking_id' => $bookingId,
                'payment_id' => $paymentId,
                'booking_code' => $bookingCode,
                'price_total' => (float) $checkoutSelection['quoted_price'],
                'currency_code' => (string) $checkoutSelection['currency_code'],
                'customer_email' => (string) ($customerDetails['customer_email'] ?? ''),
                'customer_name' => (string) ($customerDetails['customer_name'] ?? ''),
                'customer_last_name' => (string) ($customerDetails['customer_last_name'] ?? ''),
            ];
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    private function ensurePendingPayment(\PDO $db, int $bookingId, string $method, float $amount, string $currencyCode, string $reference): int
    {
        $stmt = $db->prepare(
            'SELECT id
             FROM booking_payments
             WHERE booking_id = :booking_id
               AND method = :method
               AND status = "PENDING"
             ORDER BY id DESC
             LIMIT 1'
        );
        $stmt->execute([
            'booking_id' => $bookingId,
            'method' => $method,
        ]);
        $row = $stmt->fetch();
        if (is_array($row)) {
            return (int) $row['id'];
        }

        $paymentStmt = $db->prepare(
            'INSERT INTO booking_payments (
                booking_id, method, status, amount, currency_code, reference, paid_at, created_at
            ) VALUES (
                :booking_id, :method, :status, :amount, :currency_code, :reference, NULL, NOW()
            )'
        );
        $paymentStmt->execute([
            'booking_id' => $bookingId,
            'method' => $method,
            'status' => 'PENDING',
            'amount' => $amount,
            'currency_code' => strtoupper($currencyCode),
            'reference' => $reference,
        ]);

        return (int) $db->lastInsertId();
    }

    private function mercadoPagoSettings(): array
    {
        $homeContent = (new HomeContentService())->getHomePageContent();
        $settings = $homeContent['payment_settings']['mercado_pago'] ?? [];
        return is_array($settings) ? $settings : [];
    }

    private function mercadoPagoIsConfigured(): bool
    {
        $settings = $this->mercadoPagoSettings();
        return !empty($settings['enabled']) && trim((string) ($settings['access_token'] ?? '')) !== '';
    }

    private function createMercadoPagoPreference(array $booking, string $accessToken): array
    {
        $baseUrl = $this->baseUrl();
        $bookingId = (int) $booking['booking_id'];
        $bookingCode = (string) $booking['booking_code'];
        $currencyCode = strtoupper((string) $booking['currency_code']);

        $payload = [
            'items' => [
                [
                    'id' => $bookingCode,
                    'title' => 'Private transfer ' . $bookingCode,
                    'quantity' => 1,
                    'currency_id' => $currencyCode,
                    'unit_price' => round((float) $booking['price_total'], 2),
                ],
            ],
            'payer' => [
                'email' => (string) ($booking['customer_email'] ?? ''),
                'name' => (string) ($booking['customer_name'] ?? ''),
                'surname' => (string) ($booking['customer_last_name'] ?? ''),
            ],
            'external_reference' => $bookingCode,
            'notification_url' => $baseUrl . '/webhooks/mercado-pago',
            'back_urls' => [
                'success' => $baseUrl . '/checkout/mercado-pago/return?booking_id=' . $bookingId,
                'failure' => $baseUrl . '/checkout/mercado-pago/return?booking_id=' . $bookingId,
                'pending' => $baseUrl . '/checkout/mercado-pago/return?booking_id=' . $bookingId,
            ],
            'auto_return' => 'approved',
            'metadata' => [
                'booking_id' => $bookingId,
                'booking_code' => $bookingCode,
            ],
        ];

        return $this->mercadoPagoRequest('POST', '/checkout/preferences', $accessToken, $payload);
    }

    private function mercadoPagoRedirectUrl(array $preference, string $accessToken): string
    {
        $isSandbox = str_starts_with(strtoupper(trim($accessToken)), 'TEST-');
        if ($isSandbox && !empty($preference['sandbox_init_point'])) {
            return (string) $preference['sandbox_init_point'];
        }

        return (string) ($preference['init_point'] ?? $preference['sandbox_init_point'] ?? '');
    }

    private function syncMercadoPagoPayment(string $paymentId): void
    {
        $settings = $this->mercadoPagoSettings();
        $accessToken = trim((string) ($settings['access_token'] ?? ''));
        if ($accessToken === '') {
            return;
        }

        try {
            $payment = $this->mercadoPagoRequest('GET', '/v1/payments/' . rawurlencode($paymentId), $accessToken);
            $bookingCode = trim((string) ($payment['external_reference'] ?? ''));
            if ($bookingCode === '') {
                $metadata = is_array($payment['metadata'] ?? null) ? $payment['metadata'] : [];
                $bookingCode = trim((string) ($metadata['booking_code'] ?? ''));
            }
            if ($bookingCode === '') {
                return;
            }

            $this->applyMercadoPagoPaymentStatus($bookingCode, $payment);
        } catch (\Throwable $e) {
            error_log('Mercado Pago sync error: ' . $e->getMessage());
        }
    }

    private function applyMercadoPagoPaymentStatus(string $bookingCode, array $payment): void
    {
        $db = DB::connection();
        $status = strtolower((string) ($payment['status'] ?? ''));
        $paymentId = (string) ($payment['id'] ?? '');
        $paidAt = (string) ($payment['date_approved'] ?? '');
        $reference = $paymentId !== '' ? 'mp_payment:' . $paymentId : 'mp_payment';

        $bookingStmt = $db->prepare('SELECT id, status FROM bookings WHERE booking_code = :booking_code LIMIT 1');
        $bookingStmt->execute(['booking_code' => $bookingCode]);
        $booking = $bookingStmt->fetch();
        if (!is_array($booking)) {
            return;
        }

        $bookingId = (int) $booking['id'];
        $oldStatus = (string) ($booking['status'] ?? 'PENDING');

        if ($status === 'approved') {
            $db->beginTransaction();
            try {
                $paymentStmt = $db->prepare(
                    'UPDATE booking_payments
                     SET status = "PAID",
                         reference = :reference,
                         paid_at = COALESCE(:paid_at, NOW())
                     WHERE booking_id = :booking_id
                       AND method = "MERCADO_PAGO"
                     ORDER BY id DESC
                     LIMIT 1'
                );
                $paymentStmt->execute([
                    'reference' => $reference,
                    'paid_at' => $paidAt !== '' ? date('Y-m-d H:i:s', strtotime($paidAt)) : null,
                    'booking_id' => $bookingId,
                ]);

                $updateBookingStmt = $db->prepare(
                    'UPDATE bookings
                     SET status = "CONFIRMED",
                         payment_status = "PAID",
                         updated_at = NOW()
                     WHERE id = :booking_id'
                );
                $updateBookingStmt->execute(['booking_id' => $bookingId]);

                if ($oldStatus !== 'CONFIRMED') {
                    $historyStmt = $db->prepare(
                        'INSERT INTO booking_status_history (booking_id, old_status, new_status, changed_by, note, created_at)
                         VALUES (:booking_id, :old_status, "CONFIRMED", NULL, :note, NOW())'
                    );
                    $historyStmt->execute([
                        'booking_id' => $bookingId,
                        'old_status' => $oldStatus,
                        'note' => 'Pago aprobado por Mercado Pago.',
                    ]);
                }

                $db->commit();
            } catch (\Throwable $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                throw $e;
            }

            return;
        }

        if (in_array($status, ['rejected', 'cancelled', 'refunded', 'charged_back'], true)) {
            $paymentStmt = $db->prepare(
                'UPDATE booking_payments
                 SET status = :status,
                     reference = :reference
                 WHERE booking_id = :booking_id
                   AND method = "MERCADO_PAGO"
                 ORDER BY id DESC
                 LIMIT 1'
            );
            $paymentStmt->execute([
                'status' => $status === 'refunded' ? 'REFUNDED' : 'FAILED',
                'reference' => $reference,
                'booking_id' => $bookingId,
            ]);
        }
    }

    private function updatePaymentReference(int $paymentId, string $reference): void
    {
        $stmt = DB::connection()->prepare('UPDATE booking_payments SET reference = :reference WHERE id = :id');
        $stmt->execute([
            'id' => $paymentId,
            'reference' => $reference,
        ]);
    }

    private function mercadoPagoRequest(string $method, string $path, string $accessToken, ?array $payload = null): array
    {
        if (!function_exists('curl_init')) {
            throw new \RuntimeException('La extension cURL de PHP es requerida para Mercado Pago.');
        }

        $ch = curl_init(self::MERCADO_PAGO_API_BASE . $path);
        if ($ch === false) {
            throw new \RuntimeException('No se pudo iniciar cURL.');
        }

        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
        ];

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);

        if ($payload !== null) {
            $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($json === false) {
                throw new \RuntimeException('No se pudo codificar payload de Mercado Pago.');
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        }

        $responseBody = curl_exec($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if (!is_string($responseBody) || $responseBody === '') {
            throw new \RuntimeException('Mercado Pago no respondio. ' . $error);
        }

        $decoded = json_decode($responseBody, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Respuesta invalida de Mercado Pago.');
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            $message = (string) ($decoded['message'] ?? $decoded['error'] ?? 'Error de Mercado Pago');
            throw new \RuntimeException($message);
        }

        return $decoded;
    }

    private function baseUrl(): string
    {
        $configFile = dirname(__DIR__, 5) . '/config/config.php';
        if (is_file($configFile)) {
            $config = require $configFile;
            if (is_array($config)) {
                $baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');
                if ($baseUrl !== '') {
                    return $baseUrl;
                }
            }
        }

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
        return $scheme . '://' . $host;
    }

    private function normalizeManualPaymentMethod(string $paymentMethod): string
    {
        $paymentMethod = strtoupper(trim($paymentMethod));
        return in_array($paymentMethod, self::MANUAL_PAYMENT_METHODS, true) ? $paymentMethod : 'MANUAL';
    }

    private function resolveRouteLabels(string $direction, string $placeName): array
    {
        $placeName = trim($placeName);

        if ($direction === 'DESTINATION_TO_AIRPORT') {
            return [
                'origin_name' => $placeName !== '' ? $placeName : null,
                'destination_name' => self::DEFAULT_AIRPORT_LABEL,
            ];
        }

        return [
            'origin_name' => self::DEFAULT_AIRPORT_LABEL,
            'destination_name' => $placeName !== '' ? $placeName : null,
        ];
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
                pr.name AS provider_name,
                v.name AS vehicle_name,
                wo.work_date,
                wo.notes AS work_order_notes
             FROM bookings b
             INNER JOIN service_types st ON st.id = b.service_type_id
             INNER JOIN zones z ON z.id = b.zone_id
             INNER JOIN places p ON p.id = b.place_id
             LEFT JOIN booking_passengers bp ON bp.booking_id = b.id
             LEFT JOIN assignments a ON a.booking_id = b.id
             LEFT JOIN users u ON u.id = a.operator_user_id
             LEFT JOIN providers pr ON pr.id = a.provider_id
             LEFT JOIN vehicles v ON v.id = a.vehicle_id
             LEFT JOIN work_orders wo ON wo.booking_id = b.id
             WHERE b.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $booking = $stmt->fetch();

        return is_array($booking) ? $booking : null;
    }

    private function resolveBrandLogoPath(): ?string
    {
        $homeContent = (new HomeContentService())->getHomePageContent();
        $customLogo = trim((string) (($homeContent['brand_logo_light'] ?? '') !== '' ? $homeContent['brand_logo_light'] : ($homeContent['brand_logo'] ?? '')));
        if ($customLogo === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $customLogo) === 1) {
            return $customLogo;
        }

        $projectRoot = dirname(__DIR__, 5);
        $publicRoot = $projectRoot . '/public_html';
        $candidate = str_starts_with($customLogo, '/') ? $customLogo : '/' . ltrim($customLogo, '/');
        $relativePath = ltrim($candidate, '/');

        if ($relativePath === '' || str_contains($relativePath, '..') || !is_file($publicRoot . '/' . $relativePath)) {
            return null;
        }

        return $candidate;
    }
}
