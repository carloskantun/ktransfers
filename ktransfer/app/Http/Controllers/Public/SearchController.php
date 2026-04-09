<?php
declare(strict_types=1);
namespace App\Http\Controllers\Public;

use App\Core\Csrf;
use App\Core\DB;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Services\HomeContentService;
use App\Services\RateService;
use RuntimeException;

class SearchController {
    public function index(Request $request): Response
    {
        $currencies = [];
        $zones = [];
        $featuredDestinations = [];
        $zoneStartingRates = [];
        $error = null;
        $setupRequired = false;
        $homeContent = $this->loadHomeContent();

        try {
            $db = DB::connection();

            $currStmt = $db->query('SELECT code, name, symbol FROM currencies WHERE is_active = 1 ORDER BY code ASC');
            $currencies = $currStmt->fetchAll();

            $zoneStmt = $db->query('SELECT id, name_es FROM zones WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');
            $zones = $zoneStmt->fetchAll();

            $featuredDestinations = $this->loadFeaturedDestinations();
            $zoneStartingRates = $this->loadZoneStartingRates('USD');
        } catch (RuntimeException $e) {
            $rawMessage = $e->getMessage();

            if (
                $rawMessage === 'Missing config file.'
                || $rawMessage === 'Invalid config format.'
                || $rawMessage === 'Database config is incomplete.'
            ) {
                $setupRequired = false;
                $error = 'No se pudo cargar la configuración de cotización.';
            } else {
                $error = 'No se pudo conectar a la base de datos. Revisa credenciales y permisos.';
            }
        } catch (\Throwable) {
            $error = 'No se pudo cargar información inicial.';
        }

        return Response::view('public/search', [
            'title' => 'Buscar traslado',
            'csrf_token' => Csrf::token(),
            'currencies' => $currencies,
            'zones' => $zones,
            'featured_destinations' => $featuredDestinations,
            'zone_starting_rates' => $zoneStartingRates,
            'home_content' => $homeContent,
            'errors' => [],
            'form' => [
                'transfer_mode' => 'AIRPORT_TO_DESTINATION',
                'trip_type' => 'ONE_WAY',
                'direction' => 'AIRPORT_TO_DESTINATION',
                'place_query' => '',
                'place_id' => '',
                'arrival_datetime' => '',
                'departure_datetime' => '',
                'adults' => '1',
                'children' => '0',
                'currency_code' => 'USD',
            ],
            'error' => $error,
            'setup_required' => $setupRequired,
        ]);
    }

    public function apiPlaces(Request $request): Response
    {
        $q = trim((string) $request->query('q', ''));
        $zoneIdRaw = trim((string) $request->query('zone_id', ''));
        $zoneId = (ctype_digit($zoneIdRaw) && (int) $zoneIdRaw > 0) ? (int) $zoneIdRaw : null;

        if ($q === '' && $zoneId === null) {
            return Response::json(['items' => []]);
        }

        try {
            $db = DB::connection();
            $sql = 'SELECT p.id, p.name, p.type, p.zone_id, z.name_es AS zone_name
                    FROM places p
                    INNER JOIN zones z ON z.id = p.zone_id
                    WHERE p.is_active = 1
                      AND z.is_active = 1';
            $params = [];

            if ($q !== '') {
                $sql .= ' AND p.name LIKE :q';
                $params['q'] = '%' . $q . '%';
            }

            if ($zoneId !== null) {
                $sql .= ' AND p.zone_id = :zone_id';
                $params['zone_id'] = $zoneId;
            }

            $sql .= ' ORDER BY p.name ASC LIMIT 20';

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $items = $stmt->fetchAll();
        } catch (\Throwable) {
            return Response::json(['items' => [], 'error' => 'places_query_failed'], 500);
        }

        return Response::json(['items' => $items]);
    }

    public function search(Request $request): Response
    {
        if (!Csrf::validate((string) $request->post('_csrf', ''))) {
            return Response::view('public/search', [
                'title' => 'Buscar traslado',
                'csrf_token' => Csrf::token(),
                'currencies' => $this->loadCurrencies(),
                'zones' => $this->loadZones(),
                'featured_destinations' => $this->loadFeaturedDestinations(),
                'zone_starting_rates' => $this->loadZoneStartingRates('USD'),
                'home_content' => $this->loadHomeContent(),
                'errors' => ['_csrf' => 'Token CSRF inválido.'],
                'form' => $this->formDataFromRequest($request),
                'error' => null,
            ]);
        }

        $form = $this->formDataFromRequest($request);
        $errors = $this->validateForm($form);

        if (!empty($errors)) {
            return Response::view('public/search', [
                'title' => 'Buscar traslado',
                'csrf_token' => Csrf::token(),
                'currencies' => $this->loadCurrencies(),
                'zones' => $this->loadZones(),
                'featured_destinations' => $this->loadFeaturedDestinations(),
                'zone_starting_rates' => $this->loadZoneStartingRates('USD'),
                'home_content' => $this->loadHomeContent(),
                'errors' => $errors,
                'form' => $form,
                'error' => null,
            ]);
        }

        try {
            $service = new RateService();
            $quote = $service->quote(
                (int) $form['place_id'],
                (int) $form['adults'],
                (int) $form['children'],
                (string) $form['currency_code'],
                (string) $form['trip_type']
            );
        } catch (\Throwable) {
            return Response::view('public/search', [
                'title' => 'Buscar traslado',
                'csrf_token' => Csrf::token(),
                'currencies' => $this->loadCurrencies(),
                'zones' => $this->loadZones(),
                'featured_destinations' => $this->loadFeaturedDestinations(),
                'zone_starting_rates' => $this->loadZoneStartingRates('USD'),
                'home_content' => $this->loadHomeContent(),
                'errors' => ['general' => 'No se pudo cotizar en este momento.'],
                'form' => $form,
                'error' => null,
            ]);
        }

        $searchContext = [
            'trip_type' => $form['trip_type'],
            'direction' => $form['direction'],
            'place_id' => (int) $form['place_id'],
            'arrival_datetime' => $form['arrival_datetime'],
            'departure_datetime' => $form['departure_datetime'],
            'adults' => (int) $form['adults'],
            'children' => (int) $form['children'],
            'total_pax' => (int) $form['adults'] + (int) $form['children'],
            'currency_code' => $form['currency_code'],
            'zone_id' => $quote['zone_id'],
        ];

        $request->sessionSet('search_context', $searchContext);
        $request->sessionSet('quote_options', $this->indexQuoteOptions($quote['options'] ?? []));

        return Response::view('public/results', [
            'title' => 'Resultados de cotización',
            'csrf_token' => Csrf::token(),
            'search_context' => $searchContext,
            'options' => $quote['options'],
            'pax_label' => $quote['pax_label'],
        ]);
    }

    private function loadCurrencies(): array
    {
        try {
            $db = DB::connection();
            $stmt = $db->query('SELECT code, name, symbol FROM currencies WHERE is_active = 1 ORDER BY code ASC');
            return $stmt->fetchAll();
        } catch (\Throwable) {
            return [];
        }
    }

    private function loadZones(): array
    {
        try {
            $db = DB::connection();
            $stmt = $db->query('SELECT id, name_es FROM zones WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');
            return $stmt->fetchAll();
        } catch (\Throwable) {
            return [];
        }
    }

    private function loadHomeContent(): array
    {
        return (new HomeContentService())->getHomePageContent();
    }

    private function formDataFromRequest(Request $request): array
    {
        $transferMode = strtoupper(trim((string) $request->post('transfer_mode', 'AIRPORT_TO_DESTINATION')));
        $tripType = strtoupper((string) $request->post('trip_type', 'ONE_WAY'));
        $direction = strtoupper((string) $request->post('direction', 'AIRPORT_TO_DESTINATION'));

        if ($transferMode === 'ROUND_TRIP') {
            $tripType = 'ROUND_TRIP';
            $direction = 'AIRPORT_TO_DESTINATION';
        } elseif (in_array($transferMode, ['AIRPORT_TO_DESTINATION', 'DESTINATION_TO_AIRPORT'], true)) {
            $tripType = 'ONE_WAY';
            $direction = $transferMode;
        }

        return [
            'transfer_mode' => $transferMode,
            'trip_type' => $tripType,
            'direction' => $direction,
            'place_query' => trim((string) $request->post('place_query', '')),
            'place_id' => trim((string) $request->post('place_id', '')),
            'arrival_datetime' => trim((string) $request->post('arrival_datetime', '')),
            'departure_datetime' => trim((string) $request->post('departure_datetime', '')),
            'adults' => trim((string) $request->post('adults', '1')),
            'children' => trim((string) $request->post('children', '0')),
            'currency_code' => strtoupper(trim((string) $request->post('currency_code', 'USD'))),
        ];
    }

    private function validateForm(array $form): array
    {
        $errors = Validator::required($form, [
            'trip_type',
            'direction',
            'place_id',
            'adults',
            'children',
            'currency_code',
        ]);

        if (!Validator::in((string) $form['trip_type'], ['ONE_WAY', 'ROUND_TRIP'])) {
            $errors['trip_type'] = 'Tipo de viaje inválido.';
        }

        if (!Validator::in((string) $form['direction'], ['AIRPORT_TO_DESTINATION', 'DESTINATION_TO_AIRPORT'])) {
            $errors['direction'] = 'Dirección inválida.';
        }

        if (!ctype_digit((string) $form['place_id']) || (int) $form['place_id'] <= 0) {
            $errors['place_id'] = 'Selecciona un place válido.';
        } else {
            $placeInfo = $this->loadPlaceById((int) $form['place_id']);
            if ($placeInfo === null) {
                $errors['place_id'] = 'El hotel/destino seleccionado no está disponible.';
            }
        }

        if (!ctype_digit((string) $form['adults']) || (int) $form['adults'] < 1) {
            $errors['adults'] = 'Adults debe ser mayor o igual a 1.';
        }

        if (!ctype_digit((string) $form['children']) || (int) $form['children'] < 0) {
            $errors['children'] = 'Children inválido.';
        }

        if (!preg_match('/^[A-Z]{3}$/', (string) $form['currency_code'])) {
            $errors['currency_code'] = 'Moneda inválida.';
        }

        if ((string) $form['arrival_datetime'] === '') {
            $errors['arrival_datetime'] = 'Fecha/hora de llegada requerida.';
        }

        if ((string) $form['trip_type'] === 'ROUND_TRIP' && (string) $form['departure_datetime'] === '') {
            $errors['departure_datetime'] = 'Fecha/hora de salida requerida para round trip.';
        }

        return $errors;
    }

    public function apiAirlines(Request $request): Response
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '' || mb_strlen($q) < 1) {
            return Response::json(['items' => []]);
        }

        try {
            $db = DB::connection();
            $searchTerm = '%' . $q . '%';
            $stmt = $db->prepare(
                'SELECT id, code, name
                 FROM airlines
                 WHERE is_active = 1
                   AND (name LIKE :q1 OR code LIKE :q2)
                 ORDER BY name ASC
                 LIMIT 20'
            );
            $stmt->execute(['q1' => $searchTerm, 'q2' => $searchTerm]);
            $items = $stmt->fetchAll();
            return Response::json(['items' => is_array($items) ? $items : []]);
        } catch (\PDOException $e) {
            error_log('PDO Error in apiAirlines: ' . $e->getMessage());
            return Response::json(['items' => [], 'error' => 'database_error'], 500);
        } catch (\Throwable $e) {
            error_log('General Error in apiAirlines: ' . $e->getMessage());
            return Response::json(['items' => [], 'error' => 'query_failed'], 500);
        }
    }

    private function loadPlaceById(int $placeId): ?array
    {
        try {
            $db = DB::connection();
            $stmt = $db->prepare(
                'SELECT p.id, p.zone_id, p.name
                 FROM places p
                 INNER JOIN zones z ON z.id = p.zone_id
                 WHERE p.id = :id
                   AND p.is_active = 1
                   AND z.is_active = 1
                 LIMIT 1'
            );
            $stmt->execute(['id' => $placeId]);
            $row = $stmt->fetch();

            return is_array($row) ? $row : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function loadFeaturedDestinations(): array
    {
        try {
            $db = DB::connection();
            $stmt = $db->query(
                'SELECT p.id, p.name, p.type, z.id AS zone_id, z.name_es AS zone_name
                 FROM places p
                 INNER JOIN zones z ON z.id = p.zone_id
                 WHERE p.is_active = 1
                   AND z.is_active = 1
                 ORDER BY z.sort_order ASC, p.name ASC
                 LIMIT 12'
            );

            return $stmt->fetchAll();
        } catch (\Throwable) {
            return [];
        }
    }

    private function loadZoneStartingRates(string $currencyCode): array
    {
        try {
            $db = DB::connection();
            $stmt = $db->prepare(
                'SELECT rr.zone_id, MIN(rr.one_way_price) AS starting_price
                 FROM rate_rules rr
                 INNER JOIN zones z ON z.id = rr.zone_id
                 WHERE rr.is_active = 1
                   AND z.is_active = 1
                   AND rr.currency_code = :currency_code
                 GROUP BY rr.zone_id'
            );
            $stmt->execute(['currency_code' => strtoupper($currencyCode)]);
            $rows = $stmt->fetchAll();

            $out = [];
            foreach ($rows as $row) {
                $out[(int) $row['zone_id']] = (float) $row['starting_price'];
            }

            return $out;
        } catch (\Throwable) {
            return [];
        }
    }

    private function indexQuoteOptions(array $options): array
    {
        $indexed = [];

        foreach ($options as $option) {
            if (!is_array($option)) {
                continue;
            }

            $rateRuleId = (int) ($option['rate_rule_id'] ?? 0);
            if ($rateRuleId <= 0) {
                continue;
            }

            $indexed[$rateRuleId] = [
                'rate_rule_id' => $rateRuleId,
                'service_type_id' => (int) ($option['service_type_id'] ?? 0),
                'service_type_name' => (string) ($option['service_type_name'] ?? ''),
                'pax_range_id' => (int) ($option['pax_range_id'] ?? 0),
                'pax_label' => (string) ($option['pax_label'] ?? ''),
                'currency_code' => strtoupper((string) ($option['currency_code'] ?? 'USD')),
                'quoted_price' => (float) ($option['quoted_price'] ?? 0),
            ];
        }

        return $indexed;
    }
}
