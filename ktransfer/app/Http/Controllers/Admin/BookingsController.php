<?php
declare(strict_types=1);
namespace App\Http\Controllers\Admin;

use App\Core\ACL;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\DB;
use App\Core\Request;
use App\Core\Response;
use App\Services\BrandingService;
use App\Services\HomeContentService;
use App\Services\RateService;

class BookingsController {
    private const BOOKING_STATUSES = ['PENDING', 'CONFIRMED', 'COMPLETED', 'NO_SHOW', 'CANCELLED'];
    private const PAYMENT_STATUSES = ['UNPAID', 'PARTIAL', 'PAID', 'REFUNDED'];
    private const OPERATION_TYPES = ['AIRPORT', 'INTERHOTEL'];
    private const SERVICE_STATUSES = ['PENDING', 'ASSIGNED', 'IN_PROGRESS', 'DONE', 'NO_SHOW'];
    private const ASSIGNMENT_MODES = ['INTERNAL', 'PROVIDER'];
    private const DEFAULT_AIRPORT_LABEL = 'Aeropuerto de Cancun';

    public function index(Request $request): Response
    {
        if (ACL::currentUserHasRole('operator') && !ACL::currentUserHasRole('admin')) {
            return Response::redirect('/admin/operations/agenda');
        }

        $db = DB::connection();
        $isAgencyScope = $this->isAgencyScope();
        $filters = $this->normalizeIndexFilters($request);
        $perPage = 50;
        $page = max(1, (int) $request->query('page', 1));

        $serviceTypesStmt = $db->query('SELECT id, name_es FROM service_types WHERE is_active = 1 ORDER BY sort_order ASC, name_es ASC');
        $serviceTypes = $serviceTypesStmt->fetchAll();

        $zonesStmt = $db->query('SELECT id, name_es FROM zones WHERE is_active = 1 ORDER BY sort_order ASC, name_es ASC');
        $zones = $zonesStmt->fetchAll();

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
        ";

        [$whereSql, $params] = $this->buildIndexWhere($filters, $isAgencyScope);

        $countStmt = $db->prepare("
            SELECT COUNT(*)
            FROM bookings b
            INNER JOIN service_types st ON st.id = b.service_type_id
            INNER JOIN zones z ON z.id = b.zone_id
            INNER JOIN places p ON p.id = b.place_id
            {$whereSql}
        ");
        $this->bindStatementParams($countStmt, $params);
        $countStmt->execute();
        $totalBookings = (int) $countStmt->fetchColumn();
        $totalPages = max(1, (int) ceil($totalBookings / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
        }
        $offset = ($page - 1) * $perPage;

        $sql .= $whereSql;
        $sql .= "
            ORDER BY COALESCE(b.arrival_datetime, b.departure_datetime, b.created_at) DESC, b.id DESC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $db->prepare($sql);
        $this->bindStatementParams($stmt, $params);
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $bookings = $stmt->fetchAll();

        return Response::view('admin/bookings/index', [
            'title' => 'Reservas',
            'bookings' => $bookings,
            'filters' => $filters,
            'booking_statuses' => self::BOOKING_STATUSES,
            'payment_statuses' => self::PAYMENT_STATUSES,
            'service_types' => $serviceTypes,
            'zones' => $zones,
            'is_agency_scope' => $isAgencyScope,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $totalBookings,
                'total_pages' => $totalPages,
            ],
        ], 'admin');
    }

    public function export(Request $request): Response
    {
        $db = DB::connection();
        $bookings = $this->loadFilteredBookings($db, $this->normalizeIndexFilters($request), $this->isAgencyScope());

        return new Response($this->buildBookingsCsv($bookings), 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="reservas-' . date('Ymd-His') . '.csv"',
        ]);
    }

    public function print(Request $request): Response
    {
        $db = DB::connection();
        $filters = $this->normalizeIndexFilters($request);

        return Response::view('admin/bookings/print_list', [
            'title' => 'Reservas filtradas',
            'bookings' => $this->loadFilteredBookings($db, $filters, $this->isAgencyScope()),
            'filters' => $filters,
            'brand_logo' => $this->resolveBrandLogoPath(),
        ], null);
    }

    public function create(Request $request): Response
    {
        $db = DB::connection();
        $isAgencyScope = $this->isAgencyScope();
        $currentUser = Auth::user();
        $currentUserId = Auth::id();
        $currentAgencyProviderId = ctype_digit((string) ($currentUser['provider_id'] ?? ''))
            ? (int) $currentUser['provider_id']
            : ((int) ($currentUser['provider_id'] ?? 0) > 0 ? (int) $currentUser['provider_id'] : null);
        $currentAgencyProviderName = trim((string) ($currentUser['provider_name'] ?? ''));

        $serviceTypesStmt = $db->query('SELECT id, code, name_es FROM service_types WHERE is_active = 1 ORDER BY sort_order ASC, name_es ASC');
        $serviceTypes = $serviceTypesStmt->fetchAll();
        $serviceTypesById = [];
        foreach ($serviceTypes as $serviceType) {
            $serviceTypesById[(int) ($serviceType['id'] ?? 0)] = $serviceType;
        }

        $zonesStmt = $db->query('SELECT id, name_es FROM zones WHERE is_active = 1 ORDER BY sort_order ASC, name_es ASC');
        $zones = $zonesStmt->fetchAll();

        $allPlaces = $this->loadPlacesCatalog($db);

        $operators = $this->loadAssignableOperators($db);
        $providers = $this->loadProviders($db);
        $vehicles = $this->loadVehicles($db);
        $currencies = $this->loadActiveCurrencies($db);
        $defaultCurrencyCode = strtoupper((string) ($currencies[0]['code'] ?? 'USD'));
        $allowedCurrencyCodes = array_map(
            static fn (array $currency): string => strtoupper((string) ($currency['code'] ?? '')),
            $currencies
        );

        if ($request->method() === 'GET') {
            $defaultForm = [
                'trip_type' => 'ONE_WAY',
                'operation_type' => 'AIRPORT',
                'direction' => 'AIRPORT_TO_DESTINATION',
                'service_type_id' => (string) ($serviceTypes[0]['id'] ?? ''),
                'zone_id' => '',
                'zone_name' => '',
                'place_id' => '',
                'place_query' => '',
                'place_mode' => 'EXISTING',
                'new_place_type' => 'HOTEL',
                'new_place_name' => '',
                'new_place_address' => '',
                'new_place_city' => '',
                'new_place_zone_id' => '',
                'origin_query' => '',
                'agency_name' => $isAgencyScope ? $currentAgencyProviderName : '',
                'agency_provider_id' => $isAgencyScope && $currentAgencyProviderId !== null ? (string) $currentAgencyProviderId : '',
                'currency_code' => $defaultCurrencyCode,
                'price_total' => '0.00',
                'agency_collection_mode' => 'COMPANY_COLLECTS',
                'agency_report_total' => '0.00',
                'agency_collected_total' => '0.00',
                'status' => 'PENDING',
                'payment_status' => 'UNPAID',
                'arrival_datetime' => '',
                'departure_datetime' => '',
                'airline' => '',
                'flight_number' => '',
                'terminal' => '',
                'origin_name' => '',
                'destination_name' => '',
                'pickup_notes' => '',
                'work_order_notes' => '',
                'work_date' => '',
                'customer_name' => '',
                'customer_last_name' => '',
                'customer_email' => '',
                'customer_phone' => '',
                'comments' => '',
                'adults' => '1',
                'children' => '0',
                'mode' => 'INTERNAL',
                'operator_user_id' => '',
                'provider_id' => '',
                'vehicle_id' => '',
                'service_status' => 'PENDING',
            ];

            return Response::view('admin/bookings/create', [
                'title' => 'Nueva Reserva Manual',
                'csrf_token' => Csrf::token(),
                'service_types' => $serviceTypes,
                'zones' => $zones,
                'places' => $allPlaces,
                'operators' => $operators,
                'providers' => $providers,
                'vehicles' => $vehicles,
                'currencies' => $currencies,
                'is_agency_scope' => $isAgencyScope,
                'errors' => [],
                'form' => $defaultForm,
                'vehicle_recommendation' => $this->buildVehicleRecommendation(
                    1,
                    isset($serviceTypes[0]) && is_array($serviceTypes[0]) ? $serviceTypes[0] : null,
                    $vehicles
                ),
            ], 'admin');
        }

        if (!Csrf::validate((string) $request->post('csrf_token', ''))) {
            return Response::redirect('/admin/bookings/create');
        }

        $form = [
            'trip_type' => strtoupper(trim((string) $request->post('trip_type', 'ONE_WAY'))),
            'operation_type' => strtoupper(trim((string) $request->post('operation_type', 'AIRPORT'))),
            'direction' => strtoupper(trim((string) $request->post('direction', 'AIRPORT_TO_DESTINATION'))),
            'service_type_id' => trim((string) $request->post('service_type_id', '')),
            'zone_id' => trim((string) $request->post('zone_id', '')),
            'zone_name' => trim((string) $request->post('zone_name', '')),
            'place_id' => trim((string) $request->post('place_id', '')),
            'place_query' => trim((string) $request->post('place_query', '')),
            'place_mode' => strtoupper(trim((string) $request->post('place_mode', 'EXISTING'))),
            'new_place_type' => strtoupper(trim((string) $request->post('new_place_type', 'HOTEL'))),
            'new_place_name' => trim((string) $request->post('new_place_name', '')),
            'new_place_address' => trim((string) $request->post('new_place_address', '')),
            'new_place_city' => trim((string) $request->post('new_place_city', '')),
            'new_place_zone_id' => trim((string) $request->post('new_place_zone_id', '')),
            'origin_query' => trim((string) $request->post('origin_query', '')),
            'agency_name' => trim((string) $request->post('agency_name', '')),
            'agency_provider_id' => trim((string) $request->post('agency_provider_id', '')),
            'currency_code' => strtoupper(trim((string) $request->post('currency_code', $defaultCurrencyCode))),
            'price_total' => trim((string) $request->post('price_total', '0.00')),
            'agency_collection_mode' => strtoupper(trim((string) $request->post('agency_collection_mode', 'COMPANY_COLLECTS'))),
            'agency_report_total' => trim((string) $request->post('agency_report_total', '0.00')),
            'agency_collected_total' => trim((string) $request->post('agency_collected_total', '0.00')),
            'status' => strtoupper(trim((string) $request->post('status', 'PENDING'))),
            'payment_status' => strtoupper(trim((string) $request->post('payment_status', 'UNPAID'))),
            'arrival_datetime' => trim((string) $request->post('arrival_datetime', '')),
            'departure_datetime' => trim((string) $request->post('departure_datetime', '')),
            'airline' => trim((string) $request->post('airline', '')),
            'flight_number' => trim((string) $request->post('flight_number', '')),
            'terminal' => trim((string) $request->post('terminal', '')),
            'origin_name' => trim((string) $request->post('origin_name', '')),
            'destination_name' => trim((string) $request->post('destination_name', '')),
            'pickup_notes' => trim((string) $request->post('pickup_notes', '')),
            'work_order_notes' => trim((string) $request->post('work_order_notes', '')),
            'customer_name' => trim((string) $request->post('customer_name', '')),
            'customer_last_name' => trim((string) $request->post('customer_last_name', '')),
            'customer_email' => trim((string) $request->post('customer_email', '')),
            'customer_phone' => trim((string) $request->post('customer_phone', '')),
            'comments' => trim((string) $request->post('comments', '')),
            'adults' => trim((string) $request->post('adults', '1')),
            'children' => trim((string) $request->post('children', '0')),
            'work_date' => trim((string) $request->post('work_date', '')),
            'mode' => strtoupper(trim((string) $request->post('mode', 'INTERNAL'))),
            'operator_user_id' => trim((string) $request->post('operator_user_id', '')),
            'provider_id' => trim((string) $request->post('provider_id', '')),
            'vehicle_id' => trim((string) $request->post('vehicle_id', '')),
            'service_status' => strtoupper(trim((string) $request->post('service_status', 'PENDING'))),
        ];

        $agencyProviderId = ctype_digit($form['agency_provider_id']) && (int) $form['agency_provider_id'] > 0
            ? (int) $form['agency_provider_id']
            : null;
        $errors = [];

        if ($isAgencyScope) {
            $agencyProviderId = $currentAgencyProviderId;
            $form['agency_provider_id'] = $agencyProviderId !== null ? (string) $agencyProviderId : '';
            $form['agency_name'] = $currentAgencyProviderName;

            if ($agencyProviderId === null || $currentAgencyProviderName === '') {
                $errors['agency_provider_id'] = 'Tu usuario de agencia no tiene una agencia vinculada activa. Contacta a administracion.';
            }
            $form['price_total'] = '0.00';
            $form['status'] = 'PENDING';
            $form['payment_status'] = 'UNPAID';
            $form['mode'] = 'INTERNAL';
            $form['operator_user_id'] = '';
            $form['provider_id'] = '';
            $form['vehicle_id'] = '';
            $form['service_status'] = 'PENDING';
            $form['work_date'] = '';
            $form['work_order_notes'] = '';
            $form['comments'] = '';
        } elseif ($agencyProviderId !== null && !$this->entityExists($providers, $agencyProviderId)) {
            $errors['agency_provider_id'] = 'Agencia invalida.';
        }

        if (!$isAgencyScope && $form['agency_name'] === '' && $agencyProviderId !== null) {
            foreach ($providers as $provider) {
                if ((int) ($provider['id'] ?? 0) === $agencyProviderId) {
                    $form['agency_name'] = trim((string) ($provider['name'] ?? ''));
                    break;
                }
            }
        }
        $agencyReportTotal = 0.0;
        $agencyCollectedTotal = 0.0;

        if ($form['customer_name'] === '') {
            $errors['customer_name'] = 'Nombre de cliente requerido.';
        }
        if ($form['customer_email'] === '' || !filter_var($form['customer_email'], FILTER_VALIDATE_EMAIL)) {
            $errors['customer_email'] = 'Email invalido.';
        }

        if (!ctype_digit($form['service_type_id']) || (int) $form['service_type_id'] <= 0) {
            $errors['service_type_id'] = 'Servicio invalido.';
        }

        $serviceTypeId = ctype_digit($form['service_type_id']) ? (int) $form['service_type_id'] : 0;
        $selectedServiceType = $serviceTypesById[$serviceTypeId] ?? null;
        if ($selectedServiceType === null) {
            $errors['service_type_id'] = 'Servicio invalido.';
        }

        if (!in_array($form['place_mode'], ['EXISTING', 'NEW'], true)) {
            $errors['place_mode'] = 'Modo de lugar invalido.';
            $form['place_mode'] = 'EXISTING';
        }

        if ($form['place_mode'] === 'EXISTING' && (!ctype_digit($form['place_id']) || (int) $form['place_id'] <= 0)) {
            $errors['place_id'] = 'Place invalido.';
        }

        if (!in_array($form['trip_type'], ['ONE_WAY', 'ROUND_TRIP'], true)) {
            $errors['trip_type'] = 'Tipo de viaje invalido.';
        }
        if (!in_array($form['operation_type'], self::OPERATION_TYPES, true)) {
            $errors['operation_type'] = 'Tipo de operacion invalido.';
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
        if (!in_array($form['mode'], self::ASSIGNMENT_MODES, true)) {
            $errors['mode'] = 'Modo de asignacion invalido.';
        }
        if (!in_array($form['service_status'], self::SERVICE_STATUSES, true)) {
            $errors['service_status'] = 'Estado operativo invalido.';
        }
        if (!in_array($form['agency_collection_mode'], ['COMPANY_COLLECTS', 'AGENCY_COLLECTED'], true)) {
            $errors['agency_collection_mode'] = 'Modo de cobro invalido.';
        }
        if (empty($allowedCurrencyCodes)) {
            $errors['currency_code'] = 'No hay monedas activas configuradas.';
        }
        if (
            !preg_match('/^[A-Z]{3}$/', $form['currency_code'])
            || !in_array($form['currency_code'], $allowedCurrencyCodes, true)
        ) {
            $errors['currency_code'] = 'Moneda invalida o no activa.';
        }

        if ($isAgencyScope) {
            if ($form['agency_collection_mode'] === 'COMPANY_COLLECTS') {
                $agencyCollectedTotal = is_numeric($form['agency_collected_total']) ? (float) $form['agency_collected_total'] : -1;
                if ($agencyCollectedTotal < 0) {
                    $errors['agency_collected_total'] = 'Cobro al cliente invalido.';
                }
            } else {
                // Si la agencia ya cobro, su obligacion es liquidar tarifa de reporte.
                // Se sincroniza despues de resolver tarifa para evitar diferencias por moneda.
                $agencyCollectedTotal = 0.0;
            }
        }

        $priceTotal = 0.0;
        if (!$isAgencyScope) {
            $priceTotal = is_numeric($form['price_total']) ? (float) $form['price_total'] : -1;
            if ($priceTotal < 0) {
                $errors['price_total'] = 'Total invalido.';
            }
        }

        $adults = ctype_digit($form['adults']) ? (int) $form['adults'] : 0;
        $children = ctype_digit($form['children']) ? (int) $form['children'] : -1;
        if ($adults < 1) {
            $errors['adults'] = 'Adults debe ser minimo 1.';
        }
        if ($children < 0) {
            $errors['children'] = 'Children invalido.';
        }

        $operatorUserId = ctype_digit($form['operator_user_id']) && (int) $form['operator_user_id'] > 0
            ? (int) $form['operator_user_id']
            : null;
        $providerId = ctype_digit($form['provider_id']) && (int) $form['provider_id'] > 0
            ? (int) $form['provider_id']
            : null;
        $vehicleId = ctype_digit($form['vehicle_id']) && (int) $form['vehicle_id'] > 0
            ? (int) $form['vehicle_id']
            : null;
        $workDate = $this->normalizeDate($form['work_date']);

        if ($form['work_date'] !== '' && $workDate === null) {
            $errors['work_date'] = 'Fecha operativa invalida.';
        }

        if ($operatorUserId !== null && !$this->entityExists($operators, $operatorUserId)) {
            $errors['operator_user_id'] = 'Operador invalido.';
        }
        if ($providerId !== null && !$this->entityExists($providers, $providerId)) {
            $errors['provider_id'] = 'Proveedor invalido.';
        }
        if ($vehicleId !== null && !$this->entityExists($vehicles, $vehicleId)) {
            $errors['vehicle_id'] = 'Unidad invalida.';
        }

        if ($form['mode'] === 'PROVIDER') {
            if ($providerId === null) {
                $errors['provider_id'] = 'Selecciona un proveedor cuando el modo es proveedor.';
            }
            $operatorUserId = null;
            $form['operator_user_id'] = '';
        } else {
            $providerId = null;
            $form['provider_id'] = '';
        }

        $zoneId = ctype_digit($form['zone_id']) ? (int) $form['zone_id'] : 0;
        $placeId = (int) $form['place_id'];

        $placeName = null;

        if ($form['place_mode'] === 'NEW') {
            if (!in_array($form['new_place_type'], ['HOTEL', 'AIRBNB', 'POINT'], true)) {
                $errors['new_place_type'] = 'Tipo de lugar invalido.';
            }

            $newPlaceZoneId = ctype_digit($form['new_place_zone_id']) ? (int) $form['new_place_zone_id'] : 0;
            if ($newPlaceZoneId <= 0 || !$this->entityExists($zones, $newPlaceZoneId)) {
                $errors['new_place_zone_id'] = 'Zona del nuevo lugar invalida.';
            } else {
                $zoneId = $newPlaceZoneId;
                $form['zone_id'] = (string) $zoneId;
                foreach ($zones as $zone) {
                    if ((int) ($zone['id'] ?? 0) === $zoneId) {
                        $form['zone_name'] = (string) ($zone['name_es'] ?? '');
                        break;
                    }
                }
            }

            if ($form['new_place_type'] === 'HOTEL' && $form['new_place_name'] === '') {
                $errors['new_place_name'] = 'Nombre de hotel requerido.';
            }
            if (in_array($form['new_place_type'], ['AIRBNB', 'POINT'], true) && $form['new_place_address'] === '') {
                $errors['new_place_address'] = 'Direccion requerida para Airbnb y punto.';
            }
            if ($form['new_place_name'] === '' && $form['new_place_address'] !== '') {
                $form['new_place_name'] = $form['new_place_address'];
            }
            if ($form['new_place_name'] === '') {
                $errors['new_place_name'] = 'Nombre o referencia del nuevo lugar requerido.';
            }

            $placeName = $form['new_place_name'];
            $form['destination_name'] = $placeName;
            $form['place_query'] = $placeName;
        } elseif ($placeId > 0) {
            $placeCheckStmt = $db->prepare(
                'SELECT p.id, p.zone_id, p.name, z.name_es AS zone_name
                 FROM places p
                 INNER JOIN zones z ON z.id = p.zone_id
                 WHERE p.id = :place_id
                 LIMIT 1'
            );
            $placeCheckStmt->execute([
                'place_id' => $placeId,
            ]);
            $placeRow = $placeCheckStmt->fetch();
            if (!$placeRow) {
                $errors['place_id'] = 'El hotel seleccionado no existe.';
            } else {
                $placeName = (string) ($placeRow['name'] ?? '');
                $placeZoneId = (int) ($placeRow['zone_id'] ?? 0);
                $placeZoneName = (string) ($placeRow['zone_name'] ?? '');
                if ($zoneId <= 0) {
                    $zoneId = $placeZoneId;
                    $form['zone_id'] = (string) $placeZoneId;
                    $form['zone_name'] = $placeZoneName;
                } elseif ($placeZoneId !== $zoneId) {
                    $errors['place_id'] = 'El hotel seleccionado no corresponde con la zona actual.';
                } else {
                    $form['zone_name'] = $placeZoneName;
                }
            }
        }

        if ($zoneId <= 0) {
            $errors['zone_id'] = 'Zona invalida.';
        }

        $arrivalDateTime = $this->normalizeDateTimeInput($form['arrival_datetime']);
        $departureDateTime = $this->normalizeDateTimeInput($form['departure_datetime']);

        if ($form['arrival_datetime'] !== '' && $arrivalDateTime === null) {
            $errors['arrival_datetime'] = 'Horario de llegada invalido.';
        }
        if ($form['departure_datetime'] !== '' && $departureDateTime === null) {
            $errors['departure_datetime'] = 'Horario de salida invalido.';
        }

        if ($form['operation_type'] === 'INTERHOTEL') {
            if (trim($form['origin_name']) === '') {
                $errors['origin_name'] = 'Origen requerido para inter hotel.';
            }
            if (trim($form['destination_name']) === '') {
                $errors['destination_name'] = 'Destino requerido para inter hotel.';
            }
            $form['direction'] = 'AIRPORT_TO_DESTINATION';
        } elseif ($form['trip_type'] === 'ROUND_TRIP') {
            $form['direction'] = 'AIRPORT_TO_DESTINATION';
        }

        // Validacion operativa de fecha/hora: siempre requerida segun tipo de servicio.
        if ($form['trip_type'] === 'ROUND_TRIP') {
            if ($form['arrival_datetime'] === '') {
                $errors['arrival_datetime'] = 'Horario de llegada requerido para round trip.';
            } elseif (!$this->isValidDateTimeInput($form['arrival_datetime'])) {
                $errors['arrival_datetime'] = 'Horario de llegada invalido.';
            }

            if ($form['departure_datetime'] === '') {
                $errors['departure_datetime'] = 'Horario de salida requerido para round trip.';
            } elseif (!$this->isValidDateTimeInput($form['departure_datetime'])) {
                $errors['departure_datetime'] = 'Horario de salida invalido.';
            }
        } elseif ($form['operation_type'] === 'AIRPORT' && $form['direction'] === 'DESTINATION_TO_AIRPORT') {
            if ($form['departure_datetime'] === '') {
                $errors['departure_datetime'] = 'Horario de salida requerido.';
            } elseif (!$this->isValidDateTimeInput($form['departure_datetime'])) {
                $errors['departure_datetime'] = 'Horario de salida invalido.';
            }
        } else {
            if ($form['arrival_datetime'] === '') {
                $errors['arrival_datetime'] = 'Horario requerido.';
            } elseif (!$this->isValidDateTimeInput($form['arrival_datetime'])) {
                $errors['arrival_datetime'] = 'Horario invalido.';
            }
        }

        if ($form['operation_type'] === 'AIRPORT') {
            if ($form['trip_type'] === 'ROUND_TRIP') {
                if ($arrivalDateTime === null) {
                    $errors['arrival_datetime'] = 'Horario de llegada requerido.';
                }
                if ($departureDateTime === null) {
                    $errors['departure_datetime'] = 'Horario de salida requerido.';
                }
            } elseif ($form['direction'] === 'DESTINATION_TO_AIRPORT') {
                if ($departureDateTime === null) {
                    $errors['departure_datetime'] = 'Horario de salida requerido.';
                }
            } else {
                if ($arrivalDateTime === null) {
                    $errors['arrival_datetime'] = 'Horario de llegada requerido.';
                }
            }

            if ($form['flight_number'] === '') {
                $errors['flight_number'] = 'Numero de vuelo requerido.';
            }
            if ($form['terminal'] === '') {
                $errors['terminal'] = 'Terminal requerida.';
            }
        }

        $totalPax = $adults + $children;

        if ($form['place_mode'] === 'NEW' && empty($errors)) {
            $newPlace = $this->findOrCreatePlace(
                $db,
                $zoneId,
                $form['new_place_type'],
                $form['new_place_name'],
                $form['new_place_address'],
                $form['new_place_city']
            );

            if ($newPlace === null) {
                $errors['new_place_name'] = 'No se pudo crear el nuevo lugar.';
            } else {
                $placeId = (int) ($newPlace['id'] ?? 0);
                $placeName = (string) ($newPlace['name'] ?? $form['new_place_name']);
                $zoneId = (int) ($newPlace['zone_id'] ?? $zoneId);
                $form['place_id'] = (string) $placeId;
                $form['zone_id'] = (string) $zoneId;
                $form['zone_name'] = (string) ($newPlace['zone_name'] ?? $form['zone_name']);
                $form['place_query'] = $placeName;
                $form['destination_name'] = $placeName;
            }
        }

        if ($isAgencyScope && empty($errors)) {
            $systemPrice = $this->resolveSystemPrice(
                $placeId,
                $adults,
                $children,
                $form['currency_code'],
                $form['trip_type'],
                $serviceTypeId
            );

            if ($systemPrice === null) {
                $errors['price_total'] = 'No hay tarifa activa para esta combinacion. Contacta a administracion.';
            } else {
                $form['price_total'] = number_format($systemPrice, 2, '.', '');
                $agencyReportTotal = $form['agency_report_total'] !== '' && is_numeric($form['agency_report_total'])
                    ? (float) $form['agency_report_total']
                    : $systemPrice;

                if ($agencyReportTotal < $systemPrice) {
                    $errors['agency_report_total'] = 'La tarifa de reporte no puede ser menor a la tarifa base del sistema.';
                } else {
                    $priceTotal = $agencyReportTotal;
                    $form['agency_report_total'] = number_format($agencyReportTotal, 2, '.', '');
                    if ($form['agency_collection_mode'] === 'AGENCY_COLLECTED') {
                        $agencyCollectedTotal = $agencyReportTotal;
                        $form['agency_collected_total'] = number_format($agencyCollectedTotal, 2, '.', '');
                    }
                    $form['payment_status'] = 'UNPAID';
                }
            }
        }

        $vehicleRecommendation = $this->buildVehicleRecommendation(
            $totalPax,
            $selectedServiceType,
            $vehicles
        );

        $hasAssignmentSetup = $operatorUserId !== null
            || $providerId !== null
            || $vehicleId !== null
            || $form['mode'] === 'PROVIDER';
        $hasOperationalSetup = $hasAssignmentSetup
            || $form['service_status'] !== 'PENDING'
            || $form['work_order_notes'] !== ''
            || $workDate !== null;

        if ($hasAssignmentSetup && $form['service_status'] === 'PENDING') {
            $form['service_status'] = 'ASSIGNED';
        }

        if (!empty($errors)) {
            return Response::view('admin/bookings/create', [
                'title' => 'Nueva Reserva Manual',
                'csrf_token' => Csrf::token(),
                'service_types' => $serviceTypes,
                'zones' => $zones,
                'places' => $allPlaces,
                'operators' => $operators,
                'providers' => $providers,
                'vehicles' => $vehicles,
                'currencies' => $currencies,
                'is_agency_scope' => $isAgencyScope,
                'errors' => $errors,
                'form' => $form,
                'vehicle_recommendation' => $vehicleRecommendation,
            ], 'admin');
        }

        $bookingCode = $this->generateBookingCode();
        $routeLabels = $this->resolveRouteLabels(
            $form['operation_type'],
            $form['direction'],
            $placeName,
            $form['origin_name'],
            $form['destination_name']
        );

        try {
            $db->beginTransaction();

            $insertStmt = $db->prepare(
                'INSERT INTO bookings (
                    booking_code,
                    trip_type,
                    operation_type,
                    direction,
                    service_type_id,
                    zone_id,
                    place_id,
                    origin_name,
                    destination_name,
                    currency_code,
                    price_total,
                    agency_collected_total,
                    status,
                    payment_status,
                    arrival_datetime,
                    departure_datetime,
                    airline,
                    flight_number,
                    terminal,
                    pickup_notes,
                    customer_name,
                    customer_last_name,
                    customer_email,
                    customer_phone,
                    agency_name,
                    agency_provider_id,
                    comments,
                    created_by_user_id,
                    created_at,
                    updated_at
                ) VALUES (
                    :booking_code,
                    :trip_type,
                    :operation_type,
                    :direction,
                    :service_type_id,
                    :zone_id,
                    :place_id,
                    :origin_name,
                    :destination_name,
                    :currency_code,
                    :price_total,
                    :agency_collected_total,
                    :status,
                    :payment_status,
                    :arrival_datetime,
                    :departure_datetime,
                    :airline,
                    :flight_number,
                    :terminal,
                    :pickup_notes,
                    :customer_name,
                    :customer_last_name,
                    :customer_email,
                    :customer_phone,
                    :agency_name,
                    :agency_provider_id,
                    :comments,
                    :created_by_user_id,
                    NOW(),
                    NULL
                )'
            );

            $insertStmt->execute([
                'booking_code' => $bookingCode,
                'trip_type' => $form['trip_type'],
                'operation_type' => $form['operation_type'],
                'direction' => $form['direction'],
                'service_type_id' => (int) $form['service_type_id'],
                'zone_id' => $zoneId,
                'place_id' => $placeId,
                'origin_name' => $routeLabels['origin_name'],
                'destination_name' => $routeLabels['destination_name'],
                'currency_code' => $form['currency_code'],
                'price_total' => number_format($priceTotal, 2, '.', ''),
                'agency_collected_total' => number_format($agencyCollectedTotal, 2, '.', ''),
                'status' => $form['status'],
                'payment_status' => $form['payment_status'],
                'arrival_datetime' => $arrivalDateTime,
                'departure_datetime' => $departureDateTime,
                'airline' => $form['airline'] !== '' ? $form['airline'] : null,
                'flight_number' => $form['flight_number'] !== '' ? $form['flight_number'] : null,
                'terminal' => $form['terminal'] !== '' ? $form['terminal'] : null,
                'pickup_notes' => $form['pickup_notes'] !== '' ? $form['pickup_notes'] : null,
                'customer_name' => $form['customer_name'],
                'customer_last_name' => $form['customer_last_name'] !== '' ? $form['customer_last_name'] : null,
                'customer_email' => $form['customer_email'],
                'customer_phone' => $form['customer_phone'] !== '' ? $form['customer_phone'] : null,
                'agency_name' => $form['agency_name'] !== '' ? $form['agency_name'] : null,
                'agency_provider_id' => $agencyProviderId,
                'comments' => $form['comments'] !== '' ? $form['comments'] : null,
                'created_by_user_id' => $currentUserId,
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
                'total_pax' => $totalPax,
            ]);

            $resolvedWorkDate = $workDate ?? $this->deriveWorkDate($form['arrival_datetime'], $form['departure_datetime']);
            $shouldCreateAssignment = $operatorUserId !== null
                || $providerId !== null
                || $vehicleId !== null
                || $form['mode'] === 'PROVIDER'
                || $form['service_status'] !== 'PENDING';

            if ($shouldCreateAssignment) {
                $assignmentStmt = $db->prepare(
                    'INSERT INTO assignments (
                        booking_id,
                        mode,
                        provider_id,
                        vehicle_id,
                        operator_user_id,
                        service_status,
                        assigned_at,
                        done_at
                    ) VALUES (
                        :booking_id,
                        :mode,
                        :provider_id,
                        :vehicle_id,
                        :operator_user_id,
                        :service_status,
                        :assigned_at,
                        :done_at
                    )'
                );
                $assignmentStmt->execute([
                    'booking_id' => $bookingId,
                    'mode' => $form['mode'],
                    'provider_id' => $providerId,
                    'vehicle_id' => $vehicleId,
                    'operator_user_id' => $operatorUserId,
                    'service_status' => $form['service_status'],
                    'assigned_at' => ($operatorUserId !== null || $providerId !== null || $vehicleId !== null || $form['service_status'] !== 'PENDING')
                        ? date('Y-m-d H:i:s')
                        : null,
                    'done_at' => in_array($form['service_status'], ['DONE', 'NO_SHOW'], true)
                        ? date('Y-m-d H:i:s')
                        : null,
                ]);
            }

            if ($form['work_order_notes'] !== '' || $shouldCreateAssignment || $workDate !== null) {
                $workOrderStmt = $db->prepare(
                    'INSERT INTO work_orders (work_date, booking_id, notes, created_at)
                     VALUES (:work_date, :booking_id, :notes, NOW())'
                );
                $workOrderStmt->execute([
                    'work_date' => $resolvedWorkDate,
                    'booking_id' => $bookingId,
                    'notes' => $form['work_order_notes'] !== '' ? $form['work_order_notes'] : null,
                ]);
            }

            $db->commit();
        } catch (\Throwable) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            $errors['general'] = 'No se pudo crear la reserva manual. Revisa los datos operativos e intenta de nuevo.';

            return Response::view('admin/bookings/create', [
                'title' => 'Nueva Reserva Manual',
                'csrf_token' => Csrf::token(),
                'service_types' => $serviceTypes,
                'zones' => $zones,
                'places' => $allPlaces,
                'operators' => $operators,
                'providers' => $providers,
                'vehicles' => $vehicles,
                'currencies' => $currencies,
                'is_agency_scope' => $isAgencyScope,
                'errors' => $errors,
                'form' => $form,
                'vehicle_recommendation' => $vehicleRecommendation,
            ], 'admin');
        }

        if ($isAgencyScope) {
            return Response::redirect('/admin/bookings');
        }

        return Response::redirect('/admin/bookings/edit?id=' . $bookingId);
    }

    public function quote(Request $request): Response
    {
        $placeId = (int) $request->query('place_id', 0);
        $zoneId = (int) $request->query('zone_id', 0);
        $adults = (int) $request->query('adults', 1);
        $children = (int) $request->query('children', 0);
        $currencyCode = strtoupper(trim((string) $request->query('currency_code', 'USD')));
        $tripType = strtoupper(trim((string) $request->query('trip_type', 'ONE_WAY')));
        $serviceTypeId = (int) $request->query('service_type_id', 0);

        if (
            ($placeId <= 0 && $zoneId <= 0)
            || $adults < 1
            || $children < 0
            || !preg_match('/^[A-Z]{3}$/', $currencyCode)
            || !in_array($tripType, ['ONE_WAY', 'ROUND_TRIP'], true)
            || $serviceTypeId <= 0
        ) {
            return Response::json([
                'ok' => false,
                'message' => 'Datos insuficientes para cotizar.',
            ], 422);
        }

        if (!$this->isCurrencyActive(DB::connection(), $currencyCode)) {
            return Response::json([
                'ok' => false,
                'message' => 'La moneda seleccionada no esta activa.',
            ], 422);
        }

        try {
            $rateService = new RateService();
            $quote = $placeId > 0
                ? $rateService->quote($placeId, $adults, $children, $currencyCode, $tripType)
                : $rateService->quoteForZone($zoneId, $adults, $children, $currencyCode, $tripType);
        } catch (\Throwable) {
            return Response::json([
                'ok' => false,
                'message' => 'No se pudo consultar la tarifa.',
            ], 500);
        }

        foreach (($quote['options'] ?? []) as $option) {
            if ((int) ($option['service_type_id'] ?? 0) !== $serviceTypeId) {
                continue;
            }

            return Response::json([
                'ok' => true,
                'price' => number_format((float) ($option['quoted_price'] ?? 0), 2, '.', ''),
                'currency_code' => (string) ($option['currency_code'] ?? $currencyCode),
                'service_type_name' => (string) ($option['service_type_name'] ?? ''),
                'pax_label' => (string) ($option['pax_label'] ?? $quote['pax_label'] ?? ''),
                'zone_id' => (int) ($quote['zone_id'] ?? 0),
                'rate_rule_id' => (int) ($option['rate_rule_id'] ?? 0),
            ]);
        }

        return Response::json([
            'ok' => false,
            'message' => 'No hay tarifa activa para esta zona, pax, moneda y servicio.',
            'pax_label' => (string) ($quote['pax_label'] ?? ''),
            'zone_id' => (int) ($quote['zone_id'] ?? 0),
        ], 404);
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

        if (!$this->currentUserCanViewBooking($booking)) {
            return Response::redirect('/admin/bookings');
        }

        $serviceTypesStmt = $db->query('SELECT id, code, name_es FROM service_types WHERE is_active = 1 ORDER BY sort_order ASC, name_es ASC');
        $serviceTypes = $serviceTypesStmt->fetchAll();

        $serviceTypesForJs = [];
        foreach ($serviceTypes as $serviceType) {
            $serviceTypesForJs[] = [
                'id' => (int) ($serviceType['id'] ?? 0),
                'code' => (string) ($serviceType['code'] ?? ''),
                'name_es' => (string) ($serviceType['name_es'] ?? ''),
            ];
        }

        $currencies = $this->loadActiveCurrencies($db);
        $providers = $this->loadProviders($db);
        $operators = $this->loadAssignableOperators($db);
        $vehicles = $this->loadVehicles($db);

        $vehiclesForJs = [];
        foreach ($vehicles as $vehicle) {
            $vehiclesForJs[] = [
                'id' => (int) ($vehicle['id'] ?? 0),
                'code' => (string) ($vehicle['code'] ?? ''),
                'name' => (string) ($vehicle['name'] ?? ''),
                'max_pax' => (int) ($vehicle['max_pax'] ?? 0),
            ];
        }

        $arrivalInput = (string) ($booking['arrival_datetime'] ?? '') !== ''
            ? date('Y-m-d\TH:i', strtotime((string) $booking['arrival_datetime']))
            : '';
        $departureInput = (string) ($booking['departure_datetime'] ?? '') !== ''
            ? date('Y-m-d\TH:i', strtotime((string) $booking['departure_datetime']))
            : '';

        $totalPax = max(1, (int) ($booking['adults'] ?? 1) + max(0, (int) ($booking['children'] ?? 0)));
        $serviceTypeForRec = null;
        foreach ($serviceTypes as $st) {
            if ((int) ($st['id'] ?? 0) === (int) ($booking['service_type_id'] ?? 0)) {
                $serviceTypeForRec = $st;
                break;
            }
        }

        $form = [
            'trip_type' => (string) ($booking['trip_type'] ?? 'ONE_WAY'),
            'operation_type' => (string) ($booking['operation_type'] ?? 'AIRPORT'),
            'direction' => (string) ($booking['direction'] ?? 'AIRPORT_TO_DESTINATION'),
            'service_type_id' => (string) ($booking['service_type_id'] ?? ''),
            'zone_id' => (string) ($booking['zone_id'] ?? ''),
            'zone_name' => (string) ($booking['zone_name'] ?? ''),
            'place_id' => (string) ($booking['place_id'] ?? ''),
            'place_query' => (string) ($booking['place_name'] ?? ''),
            'origin_query' => (string) ($booking['origin_name'] ?? ''),
            'origin_name' => (string) ($booking['origin_name'] ?? ''),
            'destination_name' => (string) ($booking['destination_name'] ?? ''),
            'agency_name' => (string) ($booking['agency_name'] ?? ''),
            'agency_provider_id' => (string) ($booking['agency_provider_id'] ?? ''),
            'currency_code' => strtoupper((string) ($booking['currency_code'] ?? 'USD')),
            'price_total' => number_format((float) ($booking['price_total'] ?? 0), 2, '.', ''),
            'agency_collection_mode' => 'COMPANY_COLLECTS',
            'agency_report_total' => number_format((float) ($booking['price_total'] ?? 0), 2, '.', ''),
            'agency_collected_total' => number_format((float) ($booking['agency_collected_total'] ?? 0), 2, '.', ''),
            'status' => (string) ($booking['status'] ?? 'PENDING'),
            'payment_status' => (string) ($booking['payment_status'] ?? 'UNPAID'),
            'arrival_datetime' => $arrivalInput,
            'departure_datetime' => $departureInput,
            'airline' => (string) ($booking['airline'] ?? ''),
            'flight_number' => (string) ($booking['flight_number'] ?? ''),
            'terminal' => (string) ($booking['terminal'] ?? ''),
            'pickup_notes' => (string) ($booking['pickup_notes'] ?? ''),
            'work_order_notes' => (string) ($booking['work_order_notes'] ?? ''),
            'customer_name' => (string) ($booking['customer_name'] ?? ''),
            'customer_last_name' => (string) ($booking['customer_last_name'] ?? ''),
            'customer_email' => (string) ($booking['customer_email'] ?? ''),
            'customer_phone' => (string) ($booking['customer_phone'] ?? ''),
            'comments' => (string) ($booking['comments'] ?? ''),
            'adults' => (string) max(1, (int) ($booking['adults'] ?? 1)),
            'children' => (string) max(0, (int) ($booking['children'] ?? 0)),
            'mode' => strtoupper((string) ($booking['assignment_mode'] ?? 'INTERNAL')),
            'operator_user_id' => (string) ($booking['assignment_operator_user_id'] ?? ''),
            'provider_id' => (string) ($booking['assignment_provider_id'] ?? ''),
            'vehicle_id' => (string) ($booking['assignment_vehicle_id'] ?? ''),
            'service_status' => strtoupper((string) ($booking['assignment_status'] ?? 'PENDING')),
            'work_date' => (string) ($booking['work_date'] ?? ''),
        ];

        $selectedVehicleId = (int) ($form['vehicle_id'] ?? 0);
        $selectedVehicle = null;
        foreach ($vehicles as $vehicle) {
            if ((int) ($vehicle['id'] ?? 0) === $selectedVehicleId) {
                $selectedVehicle = $vehicle;
                break;
            }
        }

        return Response::view('admin/bookings/edit_new', [
            'title' => 'Editar Reserva ' . (string) ($booking['booking_code'] ?? ''),
            'booking' => $booking,
            'booking_statuses' => self::BOOKING_STATUSES,
            'payment_statuses' => self::PAYMENT_STATUSES,
            'service_types' => $serviceTypes,
            'service_types_for_js' => $serviceTypesForJs,
            'currencies' => $currencies,
            'providers' => $providers,
            'operators' => $operators,
            'vehicles' => $vehicles,
            'vehicles_for_js' => $vehiclesForJs,
            'is_agency_scope' => $this->isAgencyScope(),
            'booking_edit_logs' => $this->loadRecentEditLogs($db, $id, 3),
            'booking_delete_requests' => $this->loadBookingDeleteRequests($db, $id, 10),
            'can_delete_approve' => $this->isDeleteApprover(),
            'form' => $form,
            'vehicle_recommendation' => $this->buildVehicleRecommendation($totalPax, $serviceTypeForRec, $vehicles),
            'selected_vehicle' => $selectedVehicle,
        ], 'admin');
    }

    public function serviceOrder(Request $request): Response
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

        return Response::view('admin/bookings/printable', [
            'title' => 'Orden de servicio ' . (string) ($booking['booking_code'] ?? ''),
            'booking' => $booking,
            'document_type' => 'service_order',
            'brand_logo' => $this->resolveBrandLogoPath(),
        ], null);
    }

    public function voucher(Request $request): Response
    {
        $id = (int) $request->query('id', 0);
        if ($id <= 0) {
            return Response::redirect('/admin/bookings');
        }

        $db = DB::connection();
        $booking = $this->loadBookingDetails($db, $id);
        if ($booking === null || !$this->currentUserCanViewBooking($booking)) {
            return Response::redirect('/admin/bookings');
        }

        return Response::view('admin/bookings/printable', [
            'title' => 'Voucher ' . (string) ($booking['booking_code'] ?? ''),
            'booking' => $booking,
            'document_type' => 'voucher',
            'brand_logo' => $this->resolveBrandLogoPath(),
        ], null);
    }

    public function update(Request $request): Response
    {
        if (!Csrf::validate((string) $request->post('csrf_token', ''))) {
            return Response::redirect('/admin/bookings');
        }

        $id = (int) $request->post('id', 0);
        $tripType = strtoupper(trim((string) $request->post('trip_type', 'ONE_WAY')));
        $operationType = strtoupper(trim((string) $request->post('operation_type', 'AIRPORT')));
        $direction = strtoupper(trim((string) $request->post('direction', 'AIRPORT_TO_DESTINATION')));
        $serviceTypeId = (int) $request->post('service_type_id', 0);
        $placeId = (int) $request->post('place_id', 0);
        $currencyCode = strtoupper(trim((string) $request->post('currency_code', 'USD')));
        $priceTotalInput = trim((string) $request->post('price_total', '0.00'));
        $status = strtoupper(trim((string) $request->post('status', '')));
        $paymentStatus = strtoupper(trim((string) $request->post('payment_status', '')));
        $arrivalDatetimeInput = trim((string) $request->post('arrival_datetime', ''));
        $departureDatetimeInput = trim((string) $request->post('departure_datetime', ''));
        $airline = trim((string) $request->post('airline', ''));
        $flightNumber = trim((string) $request->post('flight_number', ''));
        $agencyName = trim((string) $request->post('agency_name', ''));
        $terminal = trim((string) $request->post('terminal', ''));
        $originName = trim((string) $request->post('origin_name', ''));
        $destinationName = trim((string) $request->post('destination_name', ''));
        $pickupNotes = trim((string) $request->post('pickup_notes', ''));
        $workOrderNotes = trim((string) $request->post('work_order_notes', ''));
        $customerName = trim((string) $request->post('customer_name', ''));
        $customerLastName = trim((string) $request->post('customer_last_name', ''));
        $customerEmail = trim((string) $request->post('customer_email', ''));
        $customerPhone = trim((string) $request->post('customer_phone', ''));
        $comments = trim((string) $request->post('comments', ''));
        $adults = max(0, (int) $request->post('adults', 1));
        $children = max(0, (int) $request->post('children', 0));
        $isAgencyScope = $this->isAgencyScope();
        $currentUser = Auth::user();
        $agencyProviderId = null;
        $modeInput = strtoupper(trim((string) $request->post('mode', 'INTERNAL')));
        $operatorUserIdInput = trim((string) $request->post('operator_user_id', ''));
        $providerIdInput = trim((string) $request->post('provider_id', ''));
        $vehicleIdInput = trim((string) $request->post('vehicle_id', ''));
        $serviceStatusInput = strtoupper(trim((string) $request->post('service_status', 'PENDING')));
        $workDateInput = trim((string) $request->post('work_date', ''));

        if ($isAgencyScope) {
            $agencyName = trim((string) ($currentUser['provider_name'] ?? ''));
            $agencyProviderId = ctype_digit((string) ($currentUser['provider_id'] ?? ''))
                ? (int) $currentUser['provider_id']
                : ((int) ($currentUser['provider_id'] ?? 0) > 0 ? (int) $currentUser['provider_id'] : null);
            if ($agencyProviderId === null || $agencyName === '') {
                return Response::redirect('/admin/bookings/edit?id=' . $id);
            }
        } elseif ($agencyName === '') {
            $agencyProviderId = null;
        }

        if (
            $id <= 0
            || !in_array($tripType, ['ONE_WAY', 'ROUND_TRIP'], true)
            || !in_array($operationType, self::OPERATION_TYPES, true)
            || !in_array($direction, ['AIRPORT_TO_DESTINATION', 'DESTINATION_TO_AIRPORT'], true)
            || (!$isAgencyScope && !in_array($status, self::BOOKING_STATUSES, true))
            || (!$isAgencyScope && !in_array($paymentStatus, self::PAYMENT_STATUSES, true))
        ) {
            return Response::redirect('/admin/bookings');
        }

        $db = DB::connection();
        $current = $this->loadBookingDetails($db, $id);
        if (!is_array($current)) {
            return Response::redirect('/admin/bookings');
        }

        if (!$this->currentUserCanViewBooking($current)) {
            return Response::redirect('/admin/bookings');
        }

        if ($isAgencyScope) {
            $status = (string) ($current['status'] ?? 'PENDING');
            $paymentStatus = (string) ($current['payment_status'] ?? 'UNPAID');
            $currencyCode = strtoupper((string) ($current['currency_code'] ?? $currencyCode));
            $comments = (string) ($current['comments'] ?? '');
            $workOrderNotes = (string) ($current['work_order_notes'] ?? '');
            $workDateInput = (string) ($current['work_date'] ?? '');
        }

        $serviceTypeStmt = $db->prepare('SELECT id FROM service_types WHERE id = :id AND is_active = 1 LIMIT 1');
        $serviceTypeStmt->execute(['id' => $serviceTypeId]);
        if (!is_array($serviceTypeStmt->fetch())) {
            return Response::redirect('/admin/bookings/edit?id=' . $id);
        }

        $placeStmt = $db->prepare(
            'SELECT p.id, p.zone_id, p.name, z.name_es AS zone_name
             FROM places p
             INNER JOIN zones z ON z.id = p.zone_id
             WHERE p.id = :id AND p.is_active = 1
             LIMIT 1'
        );
        $placeStmt->execute(['id' => $placeId]);
        $place = $placeStmt->fetch();
        if (!is_array($place)) {
            return Response::redirect('/admin/bookings/edit?id=' . $id);
        }

        $currencyStmt = $db->prepare('SELECT code FROM currencies WHERE code = :code LIMIT 1');
        $currencyStmt->execute(['code' => $currencyCode]);
        if (!is_array($currencyStmt->fetch())) {
            return Response::redirect('/admin/bookings/edit?id=' . $id);
        }

        if ($customerName === '' || $customerEmail === '' || !filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
            return Response::redirect('/admin/bookings/edit?id=' . $id);
        }

        if ($adults < 1) {
            $adults = 1;
        }

        $arrivalDatetime = $this->normalizeDateTimeInput($arrivalDatetimeInput);
        $departureDatetime = $this->normalizeDateTimeInput($departureDatetimeInput);
        if (($arrivalDatetimeInput !== '' && $arrivalDatetime === null) || ($departureDatetimeInput !== '' && $departureDatetime === null)) {
            return Response::redirect('/admin/bookings/edit?id=' . $id);
        }

        $priceTotal = $isAgencyScope
            ? (float) ($current['price_total'] ?? 0)
            : (is_numeric($priceTotalInput) ? (float) $priceTotalInput : -1);
        if ($priceTotal < 0) {
            return Response::redirect('/admin/bookings/edit?id=' . $id);
        }

        if (!$isAgencyScope) {
            $providers = $this->loadProviders($db);
            $agencyProviderId = null;
            foreach ($providers as $provider) {
                $providerName = trim((string) ($provider['name'] ?? ''));
                if ($providerName !== '' && mb_strtolower($providerName) === mb_strtolower($agencyName)) {
                    $agencyProviderId = (int) ($provider['id'] ?? 0) > 0 ? (int) $provider['id'] : null;
                    $agencyName = $providerName;
                    break;
                }
            }
        }

        $currentWorkOrderNotes = trim((string) ($current['work_order_notes'] ?? ''));

        $routeLabels = $this->resolveRouteLabels(
            $operationType,
            $direction,
            (string) ($place['name'] ?? ''),
            $originName,
            $destinationName
        );

        $oldSnapshot = [
            'trip_type' => (string) ($current['trip_type'] ?? ''),
            'operation_type' => (string) ($current['operation_type'] ?? ''),
            'direction' => (string) ($current['direction'] ?? ''),
            'service_type_id' => (string) ($current['service_type_id'] ?? ''),
            'place_id' => (string) ($current['place_id'] ?? ''),
            'currency_code' => (string) ($current['currency_code'] ?? ''),
            'price_total' => (string) ($current['price_total'] ?? ''),
            'status' => (string) ($current['status'] ?? ''),
            'payment_status' => (string) ($current['payment_status'] ?? ''),
            'arrival_datetime' => (string) ($current['arrival_datetime'] ?? ''),
            'departure_datetime' => (string) ($current['departure_datetime'] ?? ''),
            'airline' => (string) ($current['airline'] ?? ''),
            'flight_number' => (string) ($current['flight_number'] ?? ''),
            'agency_name' => (string) ($current['agency_name'] ?? ''),
            'customer_name' => (string) ($current['customer_name'] ?? ''),
            'customer_last_name' => (string) ($current['customer_last_name'] ?? ''),
            'customer_email' => (string) ($current['customer_email'] ?? ''),
            'customer_phone' => (string) ($current['customer_phone'] ?? ''),
            'terminal' => (string) ($current['terminal'] ?? ''),
            'origin_name' => (string) ($current['origin_name'] ?? ''),
            'destination_name' => (string) ($current['destination_name'] ?? ''),
            'pickup_notes' => (string) ($current['pickup_notes'] ?? ''),
            'comments' => (string) ($current['comments'] ?? ''),
            'adults' => (string) ($current['adults'] ?? ''),
            'children' => (string) ($current['children'] ?? ''),
            'work_order_notes' => $currentWorkOrderNotes,
        ];

        $newSnapshot = [
            'trip_type' => $tripType,
            'operation_type' => $operationType,
            'direction' => $direction,
            'service_type_id' => (string) $serviceTypeId,
            'place_id' => (string) $placeId,
            'currency_code' => $currencyCode,
            'price_total' => number_format($priceTotal, 2, '.', ''),
            'status' => $status,
            'payment_status' => $paymentStatus,
            'arrival_datetime' => $arrivalDatetime ?? '',
            'departure_datetime' => $departureDatetime ?? '',
            'airline' => $airline,
            'flight_number' => $flightNumber,
            'agency_name' => $agencyName,
            'customer_name' => $customerName,
            'customer_last_name' => $customerLastName,
            'customer_email' => $customerEmail,
            'customer_phone' => $customerPhone,
            'terminal' => $terminal,
            'origin_name' => (string) ($routeLabels['origin_name'] ?? ''),
            'destination_name' => (string) ($routeLabels['destination_name'] ?? ''),
            'pickup_notes' => $pickupNotes,
            'comments' => $comments,
            'adults' => (string) $adults,
            'children' => (string) $children,
            'work_order_notes' => $workOrderNotes,
        ];

        $changedFields = $this->buildChangedFieldList($oldSnapshot, $newSnapshot);
        $hasMeaningfulChanges = !empty($changedFields);

        if (!$isAgencyScope) {
            $agencyProviderId = $agencyName !== ''
                ? ((int) ($current['agency_provider_id'] ?? 0) > 0 ? (int) $current['agency_provider_id'] : null)
                : null;
        }

        // --- Bloque principal: actualización crítica de la reserva ---
        $db->beginTransaction();
        try {
            $stmt = $db->prepare(
                'UPDATE bookings
                 SET trip_type = :trip_type,
                     operation_type = :operation_type,
                     direction = :direction,
                     service_type_id = :service_type_id,
                     zone_id = :zone_id,
                     place_id = :place_id,
                     origin_name = :origin_name,
                     destination_name = :destination_name,
                     currency_code = :currency_code,
                     price_total = :price_total,
                     status = :status,
                     payment_status = :payment_status,
                     arrival_datetime = :arrival_datetime,
                     departure_datetime = :departure_datetime,
                     airline = :airline,
                     flight_number = :flight_number,
                     agency_name = :agency_name,
                     agency_provider_id = :agency_provider_id,
                     customer_name = :customer_name,
                     customer_last_name = :customer_last_name,
                     customer_email = :customer_email,
                     customer_phone = :customer_phone,
                     terminal = :terminal,
                     pickup_notes = :pickup_notes,
                     comments = :comments,
                     updated_at = NOW()
                 WHERE id = :id'
            );
            $stmt->execute([
                'trip_type' => $tripType,
                'operation_type' => $operationType,
                'direction' => $direction,
                'service_type_id' => $serviceTypeId,
                'zone_id' => (int) ($place['zone_id'] ?? 0),
                'place_id' => $placeId,
                'origin_name' => ($routeLabels['origin_name'] ?? '') !== '' ? $routeLabels['origin_name'] : null,
                'destination_name' => ($routeLabels['destination_name'] ?? '') !== '' ? $routeLabels['destination_name'] : null,
                'currency_code' => $currencyCode,
                'price_total' => number_format($priceTotal, 2, '.', ''),
                'status' => $status,
                'payment_status' => $paymentStatus,
                'arrival_datetime' => $arrivalDatetime,
                'departure_datetime' => $departureDatetime,
                'airline' => $airline !== '' ? $airline : null,
                'flight_number' => $flightNumber !== '' ? $flightNumber : null,
                'agency_name' => $agencyName !== '' ? $agencyName : null,
                'agency_provider_id' => $agencyProviderId,
                'customer_name' => $customerName,
                'customer_last_name' => $customerLastName !== '' ? $customerLastName : null,
                'customer_email' => $customerEmail,
                'customer_phone' => $customerPhone !== '' ? $customerPhone : null,
                'terminal' => $terminal !== '' ? $terminal : null,
                'pickup_notes' => $pickupNotes !== '' ? $pickupNotes : null,
                'comments' => $comments !== '' ? $comments : null,
                'id' => $id,
            ]);

            $passengersStmt = $db->prepare(
                'INSERT INTO booking_passengers (booking_id, adults, children, total_pax)
                 VALUES (:booking_id, :adults, :children, :total_pax)
                 ON DUPLICATE KEY UPDATE
                    adults = VALUES(adults),
                    children = VALUES(children),
                    total_pax = VALUES(total_pax)'
            );
            $passengersStmt->execute([
                'booking_id' => $id,
                'adults' => $adults,
                'children' => $children,
                'total_pax' => $adults + $children,
            ]);

            $workOrderStmt = $db->prepare('SELECT id FROM work_orders WHERE booking_id = :booking_id LIMIT 1');
            $workOrderStmt->execute(['booking_id' => $id]);
            $workOrder = $workOrderStmt->fetch();
            $explicitWorkDate = $this->normalizeDate($workDateInput);
            $workDate = $explicitWorkDate ?? $this->deriveWorkDate(
                $arrivalDatetime ?? (string) ($current['arrival_datetime'] ?? ''),
                $departureDatetime ?? (string) ($current['departure_datetime'] ?? '')
            );

            if (is_array($workOrder)) {
                $updateWorkOrderStmt = $db->prepare(
                    'UPDATE work_orders
                     SET work_date = :work_date,
                         notes = :notes
                     WHERE booking_id = :booking_id'
                );
                $updateWorkOrderStmt->execute([
                    'work_date' => $workDate,
                    'notes' => $workOrderNotes !== '' ? $workOrderNotes : null,
                    'booking_id' => $id,
                ]);
            } elseif ($workOrderNotes !== '') {
                $insertWorkOrderStmt = $db->prepare(
                    'INSERT INTO work_orders (work_date, booking_id, notes, created_at)
                     VALUES (:work_date, :booking_id, :notes, NOW())'
                );
                $insertWorkOrderStmt->execute([
                    'work_date' => $workDate,
                    'booking_id' => $id,
                    'notes' => $workOrderNotes,
                ]);
            }

            if ((string) ($current['status'] ?? '') !== $status) {
                try {
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
                } catch (\Throwable) {
                    // La tabla booking_status_history puede no existir aún; no bloquear el guardado.
                }
            }

            if (!$isAgencyScope) {
                $validModes = ['INTERNAL', 'PROVIDER'];
                $validServiceStatuses = ['PENDING', 'ASSIGNED', 'IN_PROGRESS', 'DONE', 'NO_SHOW'];
                $modeForAssignment = in_array($modeInput, $validModes, true) ? $modeInput : 'INTERNAL';
                $serviceStatusForAssignment = in_array($serviceStatusInput, $validServiceStatuses, true) ? $serviceStatusInput : 'PENDING';
                $operatorForAssignment = $modeForAssignment === 'INTERNAL' && ctype_digit($operatorUserIdInput) && (int) $operatorUserIdInput > 0
                    ? (int) $operatorUserIdInput : null;
                $providerForAssignment = $modeForAssignment === 'PROVIDER' && ctype_digit($providerIdInput) && (int) $providerIdInput > 0
                    ? (int) $providerIdInput : null;
                $vehicleForAssignment = ctype_digit($vehicleIdInput) && (int) $vehicleIdInput > 0
                    ? (int) $vehicleIdInput : null;

                $existingAsgStmt = $db->prepare('SELECT id, assigned_at, done_at FROM assignments WHERE booking_id = :booking_id LIMIT 1');
                $existingAsgStmt->execute(['booking_id' => $id]);
                $existingAsg = $existingAsgStmt->fetch();

                if (is_array($existingAsg)) {
                    $newAssignedAt = $existingAsg['assigned_at'];
                    if ($newAssignedAt === null && ($operatorForAssignment !== null || $providerForAssignment !== null || $vehicleForAssignment !== null)) {
                        $newAssignedAt = date('Y-m-d H:i:s');
                    }
                    $newDoneAt = $existingAsg['done_at'];
                    if ($newDoneAt === null && in_array($serviceStatusForAssignment, ['DONE', 'NO_SHOW'], true)) {
                        $newDoneAt = date('Y-m-d H:i:s');
                    }
                    $updateAsgStmt = $db->prepare(
                        'UPDATE assignments
                         SET mode = :mode, provider_id = :provider_id, vehicle_id = :vehicle_id,
                             operator_user_id = :operator_user_id, service_status = :service_status,
                             assigned_at = :assigned_at, done_at = :done_at
                         WHERE booking_id = :booking_id'
                    );
                    $updateAsgStmt->execute([
                        'mode' => $modeForAssignment,
                        'provider_id' => $providerForAssignment,
                        'vehicle_id' => $vehicleForAssignment,
                        'operator_user_id' => $operatorForAssignment,
                        'service_status' => $serviceStatusForAssignment,
                        'assigned_at' => $newAssignedAt,
                        'done_at' => $newDoneAt,
                        'booking_id' => $id,
                    ]);
                } elseif ($operatorForAssignment !== null || $providerForAssignment !== null || $vehicleForAssignment !== null || $serviceStatusForAssignment !== 'PENDING') {
                    $insertAsgStmt = $db->prepare(
                        'INSERT INTO assignments (booking_id, mode, provider_id, vehicle_id, operator_user_id, service_status, assigned_at, done_at)
                         VALUES (:booking_id, :mode, :provider_id, :vehicle_id, :operator_user_id, :service_status, :assigned_at, :done_at)'
                    );
                    $insertAsgStmt->execute([
                        'booking_id' => $id,
                        'mode' => $modeForAssignment,
                        'provider_id' => $providerForAssignment,
                        'vehicle_id' => $vehicleForAssignment,
                        'operator_user_id' => $operatorForAssignment,
                        'service_status' => $serviceStatusForAssignment,
                        'assigned_at' => ($operatorForAssignment !== null || $providerForAssignment !== null || $vehicleForAssignment !== null) ? date('Y-m-d H:i:s') : null,
                        'done_at' => in_array($serviceStatusForAssignment, ['DONE', 'NO_SHOW'], true) ? date('Y-m-d H:i:s') : null,
                    ]);
                }
            }

            $db->commit();
        } catch (\Throwable) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return Response::redirect('/admin/bookings/edit?id=' . $id);
        }

        // --- Bloque secundario: auditoría de cambios (no crítico, no afecta el guardado) ---
        if ($hasMeaningfulChanges) {
            try {
                $logStmt = $db->prepare(
                    'INSERT INTO booking_edit_logs (
                        booking_id,
                        changed_by_user_id,
                        actor_role_code,
                        old_snapshot_json,
                        new_snapshot_json,
                        changed_fields_json,
                        created_at
                    ) VALUES (
                        :booking_id,
                        :changed_by_user_id,
                        :actor_role_code,
                        :old_snapshot_json,
                        :new_snapshot_json,
                        :changed_fields_json,
                        NOW()
                    )'
                );
                $logStmt->execute([
                    'booking_id' => $id,
                    'changed_by_user_id' => Auth::id(),
                    'actor_role_code' => $this->detectCurrentRoleCode(),
                    'old_snapshot_json' => (string) json_encode($oldSnapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'new_snapshot_json' => (string) json_encode($newSnapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'changed_fields_json' => (string) json_encode($changedFields, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);

                $this->pruneBookingEditLogs($db, $id, 3);

                if ($isAgencyScope) {
                    $this->createAdminSuperadminNotifications(
                        $db,
                        'booking.agency_edit',
                        $id,
                        [
                            'booking_code' => (string) ($current['booking_code'] ?? ''),
                            'changed_fields' => $changedFields,
                            'edited_by_user_id' => Auth::id(),
                        ]
                    );
                }
            } catch (\Throwable) {
                // La tabla booking_edit_logs puede no existir aún; el guardado ya se completó.
            }
        }

        return Response::redirect('/admin/bookings/edit?id=' . $id);
    }

    public function requestDelete(Request $request): Response
    {
        if (!Csrf::validate((string) $request->post('csrf_token', ''))) {
            return Response::redirect('/admin/bookings');
        }

        $id = (int) $request->post('id', 0);
        if ($id <= 0) {
            return Response::redirect('/admin/bookings');
        }

        $db = DB::connection();
        $bookingStmt = $db->prepare('SELECT id, booking_code, agency_provider_id FROM bookings WHERE id = :id LIMIT 1');
        $bookingStmt->execute(['id' => $id]);
        $booking = $bookingStmt->fetch();

        if (!is_array($booking) || !$this->currentUserCanViewBooking($booking)) {
            return Response::redirect('/admin/bookings');
        }

        try {
            $pendingStmt = $db->prepare(
                'SELECT id
                 FROM booking_delete_requests
                 WHERE booking_id = :booking_id
                   AND status = :status
                 LIMIT 1'
            );
            $pendingStmt->execute([
                'booking_id' => $id,
                'status' => 'PENDING',
            ]);
            if (is_array($pendingStmt->fetch())) {
                return Response::redirect('/admin/bookings/edit?id=' . $id);
            }
        } catch (\Throwable) {
            // Tabla booking_delete_requests no existe aún; continuar.
        }

        $reason = trim((string) $request->post('delete_reason', ''));
        if ($reason === '') {
            $reason = 'Solicitud de borrado generada desde panel de reserva.';
        }

        $insertStmt = $db->prepare(
            'INSERT INTO booking_delete_requests (
                booking_id,
                booking_code,
                requested_by_user_id,
                reason,
                status,
                created_at
            ) VALUES (
                :booking_id,
                :booking_code,
                :requested_by_user_id,
                :reason,
                :status,
                NOW()
            )'
        );
        $insertStmt->execute([
            'booking_id' => $id,
            'booking_code' => (string) ($booking['booking_code'] ?? ''),
            'requested_by_user_id' => Auth::id(),
            'reason' => $reason,
            'status' => 'PENDING',
        ]);

        $this->createAdminSuperadminNotifications(
            $db,
            'booking.delete_request',
            $id,
            [
                'booking_code' => (string) ($booking['booking_code'] ?? ''),
                'requested_by_user_id' => Auth::id(),
                'reason' => $reason,
            ]
        );

        return Response::redirect('/admin/bookings/edit?id=' . $id);
    }

    public function reviewDeleteRequest(Request $request): Response
    {
        if (!Csrf::validate((string) $request->post('csrf_token', ''))) {
            return Response::redirect('/admin/bookings');
        }

        if (!$this->isDeleteApprover()) {
            return Response::redirect('/admin/bookings');
        }

        $requestId = (int) $request->post('request_id', 0);
        $action = strtoupper(trim((string) $request->post('review_action', '')));
        $reviewNote = trim((string) $request->post('review_note', ''));

        if ($requestId <= 0 || !in_array($action, ['APPROVE', 'REJECT'], true)) {
            return Response::redirect('/admin/bookings');
        }

        $db = DB::connection();
        try {
            $db->beginTransaction();

            $findStmt = $db->prepare(
                'SELECT id, booking_id, booking_code, status
                 FROM booking_delete_requests
                 WHERE id = :id
                 LIMIT 1
                 FOR UPDATE'
            );
            $findStmt->execute(['id' => $requestId]);
            $deleteRequest = $findStmt->fetch();

            if (!is_array($deleteRequest) || (string) ($deleteRequest['status'] ?? '') !== 'PENDING') {
                $db->rollBack();
                return Response::redirect('/admin/bookings');
            }

            $nextStatus = $action === 'APPROVE' ? 'APPROVED' : 'REJECTED';

            $updateStmt = $db->prepare(
                'UPDATE booking_delete_requests
                 SET status = :status,
                     reviewed_by_user_id = :reviewed_by_user_id,
                     review_note = :review_note,
                     reviewed_at = NOW()
                 WHERE id = :id'
            );
            $updateStmt->execute([
                'status' => $nextStatus,
                'reviewed_by_user_id' => Auth::id(),
                'review_note' => $reviewNote !== '' ? $reviewNote : null,
                'id' => $requestId,
            ]);

            $bookingId = (int) ($deleteRequest['booking_id'] ?? 0);
            if ($nextStatus === 'APPROVED' && $bookingId > 0) {
                $deleteBookingStmt = $db->prepare('DELETE FROM bookings WHERE id = :id');
                $deleteBookingStmt->execute(['id' => $bookingId]);
            }

            $db->commit();
        } catch (\Throwable) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
        }

        $redirectId = max(0, (int) $request->post('booking_id', 0));
        if ($redirectId > 0) {
            return Response::redirect('/admin/bookings/edit?id=' . $redirectId);
        }

        return Response::redirect('/admin/bookings');
    }

    public function delete(Request $request): Response
    {
        if (!ACL::currentUserHasRole('admin') && !ACL::currentUserHasRole('superadmin')) {
            return Response::redirect('/admin/bookings');
        }

        if (!Csrf::validate((string) $request->post('csrf_token', ''))) {
            return Response::redirect('/admin/bookings');
        }

        $id = (int) $request->post('id', 0);
        if ($id <= 0) {
            return Response::redirect('/admin/bookings');
        }

        $db = DB::connection();
        $checkStmt = $db->prepare('SELECT id, booking_code FROM bookings WHERE id = :id LIMIT 1');
        $checkStmt->execute(['id' => $id]);
        $booking = $checkStmt->fetch();

        if (!is_array($booking)) {
            return Response::redirect('/admin/bookings');
        }

        $db->beginTransaction();
        try {
            $db->prepare('DELETE FROM bookings WHERE id = :id')->execute(['id' => $id]);
            $db->commit();
        } catch (\Throwable) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return Response::redirect('/admin/bookings');
        }

        return Response::redirect('/admin/bookings');
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
                a.mode AS assignment_mode,
                a.operator_user_id AS assignment_operator_user_id,
                a.vehicle_id AS assignment_vehicle_id,
                a.provider_id AS assignment_provider_id,
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

    private function generateBookingCode(): string
    {
        return (new BrandingService())->generateBookingCode();
    }

    private function resolveRouteLabels(
        string $operationType,
        string $direction,
        ?string $placeName,
        string $originName,
        string $destinationName
    ): array
    {
        $placeName = trim((string) $placeName);
        $originName = trim($originName);
        $destinationName = trim($destinationName);

        if ($operationType === 'INTERHOTEL') {
            return [
                'origin_name' => $originName !== '' ? $originName : null,
                'destination_name' => $destinationName !== '' ? $destinationName : ($placeName !== '' ? $placeName : null),
            ];
        }

        if ($originName !== '' || $destinationName !== '') {
            return [
                'origin_name' => $originName !== '' ? $originName : null,
                'destination_name' => $destinationName !== '' ? $destinationName : null,
            ];
        }

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

    private function deriveWorkDate(string $arrivalDatetime, string $departureDatetime): string
    {
        $arrivalDatetime = trim($arrivalDatetime);
        if ($arrivalDatetime !== '') {
            return date('Y-m-d', strtotime($arrivalDatetime));
        }

        $departureDatetime = trim($departureDatetime);
        if ($departureDatetime !== '') {
            return date('Y-m-d', strtotime($departureDatetime));
        }

        return date('Y-m-d');
    }

    private function normalizeDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $value);
        return $date instanceof \DateTimeImmutable ? $date->format('Y-m-d') : null;
    }

    private function normalizeDateTimeInput(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $formats = ['Y-m-d\TH:i', 'Y-m-d\TH:i:s', 'Y-m-d H:i:s', 'Y-m-d H:i'];
        foreach ($formats as $format) {
            $dateTime = \DateTimeImmutable::createFromFormat($format, $value);
            if ($dateTime instanceof \DateTimeImmutable) {
                return $dateTime->format('Y-m-d H:i:s');
            }
        }

        return null;
    }

    /**
     * @return array{q:string,status:string,payment_status:string,service_type_id:int,zone_id:int,date_from:string,date_to:string}
     */
    private function normalizeIndexFilters(Request $request): array
    {
        $status = strtoupper(trim((string) $request->query('status', '')));
        if (!in_array($status, self::BOOKING_STATUSES, true)) {
            $status = '';
        }

        $paymentStatus = strtoupper(trim((string) $request->query('payment_status', '')));
        if (!in_array($paymentStatus, self::PAYMENT_STATUSES, true)) {
            $paymentStatus = '';
        }

        $dateFrom = $this->normalizeDate((string) $request->query('date_from', '')) ?? '';
        $dateTo = $this->normalizeDate((string) $request->query('date_to', '')) ?? '';
        if ($dateFrom !== '' && $dateTo !== '' && $dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        return [
            'q' => trim((string) $request->query('q', '')),
            'status' => $status,
            'payment_status' => $paymentStatus,
            'service_type_id' => max(0, (int) $request->query('service_type_id', 0)),
            'zone_id' => max(0, (int) $request->query('zone_id', 0)),
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];
    }

    private function buildIndexWhere(array $filters, bool $isAgencyScope): array
    {
        $where = [];
        $params = [];

        if ($isAgencyScope) {
            $currentUser = Auth::user();
            $currentUserId = Auth::id();
            $agencyProviderId = (int) ($currentUser['provider_id'] ?? 0);

            if ($agencyProviderId > 0) {
                // Reservas de la agencia: por provider_id vinculado O reservas propias sin provider asignado aún
                $where[] = '(b.agency_provider_id = :agency_provider_id OR (b.agency_provider_id IS NULL AND b.created_by_user_id = :fallback_user_id))';
                $params['agency_provider_id'] = $agencyProviderId;
                $params['fallback_user_id'] = $currentUserId;
            } elseif ($currentUserId !== null) {
                // Usuario agency sin provider vinculado aún: mostrar sus propias reservas
                $where[] = 'b.created_by_user_id = :created_by_user_id';
                $params['created_by_user_id'] = $currentUserId;
            } else {
                $where[] = '1 = 0';
            }
        }

        if ($filters['q'] !== '') {
            $searchableColumns = [
                'b.booking_code',
                'b.customer_name',
                'b.customer_last_name',
                'b.customer_email',
                'b.customer_phone',
                'b.flight_number',
                'b.airline',
                'p.name',
                'z.name_es',
            ];
            $searchWhere = [];
            foreach ($searchableColumns as $index => $column) {
                $paramKey = 'search_' . $index;
                $searchWhere[] = $column . ' LIKE :' . $paramKey;
                $params[$paramKey] = '%' . $filters['q'] . '%';
            }
            $where[] = '(' . implode(' OR ', $searchWhere) . ')';
        }

        if ($filters['status'] !== '') {
            $where[] = 'b.status = :status';
            $params['status'] = $filters['status'];
        }

        if ($filters['payment_status'] !== '') {
            $where[] = 'b.payment_status = :payment_status';
            $params['payment_status'] = $filters['payment_status'];
        }

        if ($filters['service_type_id'] > 0) {
            $where[] = 'b.service_type_id = :service_type_id';
            $params['service_type_id'] = $filters['service_type_id'];
        }

        if ($filters['zone_id'] > 0) {
            $where[] = 'b.zone_id = :zone_id';
            $params['zone_id'] = $filters['zone_id'];
        }

        if ($filters['date_from'] !== '') {
            $where[] = 'COALESCE(b.arrival_datetime, b.departure_datetime, b.created_at) >= :date_from';
            $params['date_from'] = $filters['date_from'] . ' 00:00:00';
        }

        if ($filters['date_to'] !== '') {
            $where[] = 'COALESCE(b.arrival_datetime, b.departure_datetime, b.created_at) <= :date_to';
            $params['date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        return [!empty($where) ? ' WHERE ' . implode(' AND ', $where) : '', $params];
    }

    private function loadFilteredBookings(\PDO $db, array $filters, bool $isAgencyScope): array
    {
        [$whereSql, $params] = $this->buildIndexWhere($filters, $isAgencyScope);
        $stmt = $db->prepare("
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
            {$whereSql}
            ORDER BY COALESCE(b.arrival_datetime, b.departure_datetime, b.created_at) DESC, b.id DESC
        ");
        $this->bindStatementParams($stmt, $params);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    private function bindStatementParams(\PDOStatement $stmt, array $params): void
    {
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
    }

    private function buildBookingsCsv(array $bookings): string
    {
        $handle = fopen('php://temp', 'r+');
        if ($handle === false) {
            return '';
        }

        fputcsv($handle, ['Codigo', 'Cliente', 'Email', 'Telefono', 'Servicio', 'Zona', 'Lugar', 'Pax', 'Llegada', 'Salida', 'Vuelo', 'Total', 'Moneda', 'Estado', 'Pago', 'Creada']);
        foreach ($bookings as $booking) {
            fputcsv($handle, [
                (string) ($booking['booking_code'] ?? ''),
                trim((string) ($booking['customer_name'] ?? '') . ' ' . (string) ($booking['customer_last_name'] ?? '')),
                (string) ($booking['customer_email'] ?? ''),
                (string) ($booking['customer_phone'] ?? ''),
                (string) ($booking['service_name'] ?? ''),
                (string) ($booking['zone_name'] ?? ''),
                (string) ($booking['place_name'] ?? ''),
                (string) ($booking['total_pax'] ?? '0'),
                (string) ($booking['arrival_datetime'] ?? ''),
                (string) ($booking['departure_datetime'] ?? ''),
                trim((string) ($booking['airline'] ?? '') . ' ' . (string) ($booking['flight_number'] ?? '')),
                number_format((float) ($booking['price_total'] ?? 0), 2, '.', ''),
                (string) ($booking['currency_code'] ?? ''),
                (string) ($booking['status'] ?? ''),
                (string) ($booking['payment_status'] ?? ''),
                (string) ($booking['created_at'] ?? ''),
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return "\xEF\xBB\xBF" . (is_string($csv) ? $csv : '');
    }

    private function loadAssignableOperators(\PDO $db): array
    {
        $stmt = $db->query(
            'SELECT DISTINCT
                u.id,
                u.name,
                u.email
             FROM users u
             INNER JOIN user_roles ur ON ur.user_id = u.id
             INNER JOIN roles r ON r.id = ur.role_id
             WHERE u.is_active = 1
               AND r.code IN (\'admin\', \'operator\')
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
        $stmt = $db->query('SELECT id, code, name, max_pax FROM vehicles WHERE is_active = 1 ORDER BY max_pax ASC, name ASC');
        return $stmt->fetchAll();
    }

    private function loadActiveCurrencies(\PDO $db): array
    {
                $stmt = $db->query(
                        'SELECT c.code, c.name
                         FROM currencies c
                         WHERE c.is_active = 1
                             AND EXISTS (
                                     SELECT 1
                                     FROM rate_rules rr
                                     WHERE rr.currency_code = c.code
                                         AND rr.is_active = 1
                             )
                         ORDER BY c.code ASC'
                );
        return $stmt->fetchAll();
    }

    private function isCurrencyActive(\PDO $db, string $currencyCode): bool
    {
        $stmt = $db->prepare('SELECT COUNT(*) FROM currencies WHERE code = :code AND is_active = 1');
        $stmt->execute(['code' => strtoupper($currencyCode)]);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function entityExists(array $rows, int $id): bool
    {
        foreach ($rows as $row) {
            if ((int) ($row['id'] ?? 0) === $id) {
                return true;
            }
        }

        return false;
    }

    private function loadPlacesCatalog(\PDO $db): array
    {
        try {
            $stmt = $db->query(
                'SELECT p.id, p.zone_id, p.name, p.type, p.address, z.name_es AS zone_name
                 FROM places p
                 INNER JOIN zones z ON z.id = p.zone_id
                 WHERE p.is_active = 1 AND z.is_active = 1
                 ORDER BY z.name_es ASC, p.name ASC'
            );
            $rows = $stmt->fetchAll();

            return is_array($rows) ? $rows : [];
        } catch (\Throwable) {
            $stmt = $db->query(
                'SELECT p.id, p.zone_id, p.name, p.type, "" AS address, z.name_es AS zone_name
                 FROM places p
                 INNER JOIN zones z ON z.id = p.zone_id
                 WHERE p.is_active = 1 AND z.is_active = 1
                 ORDER BY z.name_es ASC, p.name ASC'
            );
            $rows = $stmt->fetchAll();

            return is_array($rows) ? $rows : [];
        }
    }

    private function findOrCreatePlace(
        \PDO $db,
        int $zoneId,
        string $type,
        string $name,
        string $address,
        string $city
    ): ?array {
        $name = trim($name);
        $address = trim($address);
        $city = trim($city);

        if ($zoneId <= 0 || $name === '' || !in_array($type, ['HOTEL', 'AIRBNB', 'POINT'], true)) {
            return null;
        }

        try {
            $findStmt = $db->prepare(
                'SELECT p.id, p.zone_id, p.name, p.address, z.name_es AS zone_name
                 FROM places p
                 INNER JOIN zones z ON z.id = p.zone_id
                 WHERE p.zone_id = :zone_id
                   AND (
                        LOWER(TRIM(p.name)) = LOWER(TRIM(:name))
                        OR (:address <> "" AND LOWER(TRIM(COALESCE(p.address, ""))) = LOWER(TRIM(:address_lookup)))
                   )
                 LIMIT 1'
            );
            $findStmt->execute([
                'zone_id' => $zoneId,
                'name' => $name,
                'address' => $address,
                'address_lookup' => $address,
            ]);
            $existing = $findStmt->fetch();

            if ($existing) {
                if ($address !== '' && trim((string) ($existing['address'] ?? '')) === '') {
                    $updateStmt = $db->prepare('UPDATE places SET address = :address WHERE id = :id');
                    $updateStmt->execute([
                        'id' => (int) ($existing['id'] ?? 0),
                        'address' => $address,
                    ]);
                    $existing['address'] = $address;
                }

                return $existing;
            }

            $insertStmt = $db->prepare(
                'INSERT INTO places (zone_id, type, name, address, city, is_active, created_at)
                 VALUES (:zone_id, :type, :name, :address, :city, 1, NOW())'
            );
            $insertStmt->execute([
                'zone_id' => $zoneId,
                'type' => $type,
                'name' => $name,
                'address' => $address !== '' ? $address : null,
                'city' => $city !== '' ? $city : null,
            ]);

            $placeId = (int) $db->lastInsertId();
            $loadStmt = $db->prepare(
                'SELECT p.id, p.zone_id, p.name, p.address, z.name_es AS zone_name
                 FROM places p
                 INNER JOIN zones z ON z.id = p.zone_id
                 WHERE p.id = :id
                 LIMIT 1'
            );
            $loadStmt->execute(['id' => $placeId]);
            $place = $loadStmt->fetch();

            return $place ?: null;
        } catch (\Throwable) {
            $findStmt = $db->prepare(
                'SELECT p.id, p.zone_id, p.name, "" AS address, z.name_es AS zone_name
                 FROM places p
                 INNER JOIN zones z ON z.id = p.zone_id
                 WHERE p.zone_id = :zone_id
                   AND LOWER(TRIM(p.name)) = LOWER(TRIM(:name))
                 LIMIT 1'
            );
            $findStmt->execute([
                'zone_id' => $zoneId,
                'name' => $name,
            ]);
            $existing = $findStmt->fetch();

            if ($existing) {
                return $existing;
            }

            $insertStmt = $db->prepare(
                'INSERT INTO places (zone_id, type, name, city, is_active, created_at)
                 VALUES (:zone_id, :type, :name, :city, 1, NOW())'
            );
            $insertStmt->execute([
                'zone_id' => $zoneId,
                'type' => $type,
                'name' => $name,
                'city' => $city !== '' ? $city : null,
            ]);

            $placeId = (int) $db->lastInsertId();
            $loadStmt = $db->prepare(
                'SELECT p.id, p.zone_id, p.name, "" AS address, z.name_es AS zone_name
                 FROM places p
                 INNER JOIN zones z ON z.id = p.zone_id
                 WHERE p.id = :id
                 LIMIT 1'
            );
            $loadStmt->execute(['id' => $placeId]);
            $place = $loadStmt->fetch();

            return $place ?: null;
        }
    }

    private function resolveSystemPrice(
        int $placeId,
        int $adults,
        int $children,
        string $currencyCode,
        string $tripType,
        int $serviceTypeId
    ): ?float {
        try {
            $quote = (new RateService())->quote($placeId, $adults, $children, $currencyCode, $tripType);
        } catch (\Throwable) {
            return null;
        }

        foreach (($quote['options'] ?? []) as $option) {
            if ((int) ($option['service_type_id'] ?? 0) === $serviceTypeId) {
                return (float) ($option['quoted_price'] ?? 0);
            }
        }

        return null;
    }

    private function isValidDateTimeInput(string $value): bool
    {
        $value = trim($value);
        if ($value === '') {
            return false;
        }

        return strtotime($value) !== false;
    }

    private function buildChangedFieldList(array $oldSnapshot, array $newSnapshot): array
    {
        $changed = [];

        foreach ($newSnapshot as $field => $newValue) {
            $oldValue = $oldSnapshot[$field] ?? '';
            if (trim((string) $oldValue) !== trim((string) $newValue)) {
                $changed[] = $field;
            }
        }

        return $changed;
    }

    private function detectCurrentRoleCode(): string
    {
        $rolePriority = ['superadmin', 'admin', 'sales', 'agency', 'operator', 'accounting'];
        foreach ($rolePriority as $roleCode) {
            if (ACL::currentUserHasRole($roleCode)) {
                return $roleCode;
            }
        }

        return 'unknown';
    }

    private function pruneBookingEditLogs(\PDO $db, int $bookingId, int $keep): void
    {
        if ($keep < 1) {
            return;
        }

        $safeKeep = max(1, (int) $keep);

        $pruneStmt = $db->prepare(
            'DELETE FROM booking_edit_logs
             WHERE booking_id = :booking_id
               AND id NOT IN (
                   SELECT id FROM (
                       SELECT id
                       FROM booking_edit_logs
                       WHERE booking_id = :booking_id_inner
                       ORDER BY created_at DESC, id DESC
                       LIMIT ' . $safeKeep . '
                   ) AS latest
               )'
        );
        $pruneStmt->bindValue(':booking_id', $bookingId, \PDO::PARAM_INT);
        $pruneStmt->bindValue(':booking_id_inner', $bookingId, \PDO::PARAM_INT);
        $pruneStmt->execute();
    }

    private function createAdminSuperadminNotifications(\PDO $db, string $type, int $bookingId, array $payload): void
    {
        $usersStmt = $db->query(
            'SELECT DISTINCT u.id
             FROM users u
             INNER JOIN user_roles ur ON ur.user_id = u.id
             INNER JOIN roles r ON r.id = ur.role_id
             WHERE u.is_active = 1
               AND r.code IN ("admin", "superadmin")'
        );
        $users = $usersStmt->fetchAll();
        if (empty($users)) {
            return;
        }

        $insertStmt = $db->prepare(
            'INSERT INTO admin_notifications (
                user_id,
                type,
                booking_id,
                payload_json,
                is_read,
                created_at
            ) VALUES (
                :user_id,
                :type,
                :booking_id,
                :payload_json,
                0,
                NOW()
            )'
        );

        $payloadJson = (string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        foreach ($users as $user) {
            $userId = (int) ($user['id'] ?? 0);
            if ($userId <= 0) {
                continue;
            }

            $insertStmt->execute([
                'user_id' => $userId,
                'type' => $type,
                'booking_id' => $bookingId > 0 ? $bookingId : null,
                'payload_json' => $payloadJson,
            ]);
        }
    }

    private function loadRecentEditLogs(\PDO $db, int $bookingId, int $limit): array
    {
        try {
        $stmt = $db->prepare(
            'SELECT
                l.id,
                l.actor_role_code,
                l.changed_fields_json,
                l.old_snapshot_json,
                l.new_snapshot_json,
                l.created_at,
                u.name AS changed_by_name
             FROM booking_edit_logs l
             LEFT JOIN users u ON u.id = l.changed_by_user_id
             WHERE l.booking_id = :booking_id
             ORDER BY l.created_at DESC, l.id DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':booking_id', $bookingId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, $limit), \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $row['changed_fields'] = json_decode((string) ($row['changed_fields_json'] ?? '[]'), true);
            $row['old_snapshot'] = json_decode((string) ($row['old_snapshot_json'] ?? '{}'), true);
            $row['new_snapshot'] = json_decode((string) ($row['new_snapshot_json'] ?? '{}'), true);
            if (!is_array($row['changed_fields'])) {
                $row['changed_fields'] = [];
            }
            if (!is_array($row['old_snapshot'])) {
                $row['old_snapshot'] = [];
            }
            if (!is_array($row['new_snapshot'])) {
                $row['new_snapshot'] = [];
            }
        }
        unset($row);

        return $rows;
        } catch (\Throwable) {
            return [];
        }
    }

    private function loadBookingDeleteRequests(\PDO $db, int $bookingId, int $limit): array
    {
        try {
        $stmt = $db->prepare(
            'SELECT
                r.id,
                r.booking_id,
                r.booking_code,
                r.reason,
                r.status,
                r.review_note,
                r.created_at,
                r.reviewed_at,
                requester.name AS requested_by_name,
                reviewer.name AS reviewed_by_name
             FROM booking_delete_requests r
             LEFT JOIN users requester ON requester.id = r.requested_by_user_id
             LEFT JOIN users reviewer ON reviewer.id = r.reviewed_by_user_id
             WHERE r.booking_id = :booking_id
             ORDER BY r.created_at DESC, r.id DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':booking_id', $bookingId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, $limit), \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
        } catch (\Throwable) {
            return [];
        }
    }

    private function isDeleteApprover(): bool
    {
        return ACL::currentUserHasRole('admin') || ACL::currentUserHasRole('superadmin');
    }

    private function isAgencyScope(): bool
    {
        return ACL::currentUserHasRole('agency') && !ACL::currentUserHasRole('admin') && !ACL::currentUserHasRole('superadmin');
    }

    private function currentUserCanViewBooking(array $booking): bool
    {
        if (!$this->isAgencyScope()) {
            return true;
        }

        $currentUser = Auth::user();
        $currentUserId = Auth::id();
        $agencyProviderId = (int) ($currentUser['provider_id'] ?? 0);
        $bookingAgencyProviderId = (int) ($booking['agency_provider_id'] ?? 0);
        $bookingCreatedBy = (int) ($booking['created_by_user_id'] ?? 0);

        if ($agencyProviderId > 0) {
            // Puede ver si la reserva es de su agencia (por provider) o fue creada por él mismo antes del vínculo
            return $bookingAgencyProviderId === $agencyProviderId
                || ($bookingAgencyProviderId === 0 && $bookingCreatedBy === $currentUserId);
        }

        // Sin provider vinculado: solo puede ver sus propias reservas
        return $currentUserId !== null && $bookingCreatedBy === $currentUserId;
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

    private function buildVehicleRecommendation(int $totalPax, ?array $serviceType, array $vehicles): ?array
    {
        if ($totalPax < 1 || empty($vehicles)) {
            return null;
        }

        $recommendedVehicle = null;
        foreach ($vehicles as $vehicle) {
            if ((int) ($vehicle['max_pax'] ?? 0) >= $totalPax) {
                $recommendedVehicle = $vehicle;
                break;
            }
        }

        if ($recommendedVehicle === null) {
            $recommendedVehicle = end($vehicles);
            if (!is_array($recommendedVehicle)) {
                return null;
            }
        }

        $serviceCode = strtoupper(trim((string) ($serviceType['code'] ?? '')));
        $serviceName = trim((string) ($serviceType['name_es'] ?? ''));
        $notes = [];

        $notes[] = 'Recomendacion basada en capacidad para ' . $totalPax . ' pax.';

        if (in_array($serviceCode, ['VIP', 'LUXURY'], true)) {
            $notes[] = 'Validar que la unidad cumpla el nivel premium del servicio ' . ($serviceName !== '' ? $serviceName : $serviceCode) . '.';
        }

        if ((int) ($recommendedVehicle['max_pax'] ?? 0) < $totalPax) {
            $notes[] = 'La capacidad activa mas alta no cubre todos los pasajeros. Se requiere revision manual.';
        }

        return [
            'vehicle_id' => (int) ($recommendedVehicle['id'] ?? 0),
            'label' => (string) ($recommendedVehicle['name'] ?? 'Sin unidad sugerida'),
            'max_pax' => (int) ($recommendedVehicle['max_pax'] ?? 0),
            'notes' => $notes,
        ];
    }
}
