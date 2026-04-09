<?php
declare(strict_types=1);
namespace App\Http\Controllers\Admin;

use App\Core\Csrf;
use App\Core\DB;
use App\Core\Request;
use App\Core\Response;

class RatesController {
    public function index(Request $request): Response
    {
        $db = DB::connection();

        $currencyStmt = $db->query('SELECT code, name, symbol FROM currencies WHERE is_active = 1 ORDER BY code ASC');
        $currencies = $currencyStmt->fetchAll();

        $zonesStmt = $db->query('SELECT id, name_es, sort_order FROM zones WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');
        $zones = $zonesStmt->fetchAll();

        $servicesStmt = $db->query('SELECT id, name_es, sort_order FROM service_types WHERE is_active = 1 ORDER BY sort_order ASC, id ASC');
        $services = $servicesStmt->fetchAll();

        $paxStmt = $db->query('SELECT id, label, min_pax, sort_order FROM pax_ranges ORDER BY min_pax ASC, sort_order ASC, id ASC');
        $paxRanges = $paxStmt->fetchAll();

        $existingStmt = $db->query(
            'SELECT
                id,
                zone_id,
                service_type_id,
                pax_range_id,
                currency_code,
                one_way_price,
                round_trip_price,
                is_active
             FROM rate_rules'
        );
        $existingRates = $existingStmt->fetchAll();

        $existingMap = [];
        foreach ($existingRates as $rate) {
            $key = (int) $rate['zone_id'] . '|' . (int) $rate['service_type_id'] . '|' . (int) $rate['pax_range_id'];
            $currencyCode = strtoupper((string) ($rate['currency_code'] ?? ''));
            $existingMap[$key][$currencyCode] = [
                'id' => (int) ($rate['id'] ?? 0),
                'one_way_price' => (float) ($rate['one_way_price'] ?? 0),
                'round_trip_price' => (float) ($rate['round_trip_price'] ?? 0),
                'is_active' => (int) ($rate['is_active'] ?? 0),
            ];
        }

        $rateGroups = [];
        foreach ($zones as $zone) {
            foreach ($services as $service) {
                foreach ($paxRanges as $paxRange) {
                    $zoneId = (int) ($zone['id'] ?? 0);
                    $serviceTypeId = (int) ($service['id'] ?? 0);
                    $paxRangeId = (int) ($paxRange['id'] ?? 0);
                    $key = $zoneId . '|' . $serviceTypeId . '|' . $paxRangeId;

                    $groupCurrencies = [];
                    $allActive = true;
                    $hasAnyRate = false;

                    foreach ($currencies as $currency) {
                        $code = strtoupper((string) ($currency['code'] ?? ''));
                        $entry = $existingMap[$key][$code] ?? null;
                        $groupCurrencies[$code] = $entry;

                        if ($entry !== null) {
                            $hasAnyRate = true;
                            if ((int) ($entry['is_active'] ?? 0) !== 1) {
                                $allActive = false;
                            }
                        } else {
                            $allActive = false;
                        }
                    }

                    $rateGroups[] = [
                        'zone_id' => $zoneId,
                        'service_type_id' => $serviceTypeId,
                        'pax_range_id' => $paxRangeId,
                        'zone_name' => (string) ($zone['name_es'] ?? ''),
                        'service_name' => (string) ($service['name_es'] ?? ''),
                        'pax_label' => (string) ($paxRange['label'] ?? ''),
                        'currencies' => $groupCurrencies,
                        'all_active' => $allActive,
                        'has_any_rate' => $hasAnyRate,
                    ];
                }
            }
        }

        return Response::view('admin/pricing/rate_rules/index', [
            'title' => 'Rate Rules',
            'rate_groups' => $rateGroups,
            'currencies' => $currencies,
            'csrf_token' => Csrf::token(),
        ], 'admin');
    }

    public function editGroup(Request $request): Response
    {
        $zoneId = (int) $request->query('zone_id', 0);
        $serviceTypeId = (int) $request->query('service_type_id', 0);
        $paxRangeId = (int) $request->query('pax_range_id', 0);

        if ($zoneId <= 0 || $serviceTypeId <= 0 || $paxRangeId <= 0) {
            return Response::redirect('/admin/pricing/rate-rules');
        }

        $db = DB::connection();

        $groupStmt = $db->prepare(
            'SELECT
                z.id AS zone_id,
                z.name_es AS zone_name,
                st.id AS service_type_id,
                st.name_es AS service_name,
                pr.id AS pax_range_id,
                pr.label AS pax_label
             FROM zones z
             INNER JOIN service_types st ON st.id = :service_type_id
             INNER JOIN pax_ranges pr ON pr.id = :pax_range_id
             WHERE z.id = :zone_id
             LIMIT 1'
        );
        $groupStmt->execute([
            'zone_id' => $zoneId,
            'service_type_id' => $serviceTypeId,
            'pax_range_id' => $paxRangeId,
        ]);
        $group = $groupStmt->fetch();

        if (!$group) {
            return Response::redirect('/admin/pricing/rate-rules');
        }

        $currencyStmt = $db->query('SELECT code, name, symbol FROM currencies WHERE is_active = 1 ORDER BY code ASC');
        $currencies = $currencyStmt->fetchAll();

        if (empty($currencies)) {
            return Response::redirect('/admin/pricing/rate-rules');
        }

        $rulesStmt = $db->prepare(
            'SELECT id, currency_code, one_way_price, round_trip_price, is_active
             FROM rate_rules
             WHERE zone_id = :zone_id
               AND service_type_id = :service_type_id
               AND pax_range_id = :pax_range_id
             ORDER BY currency_code ASC, id ASC'
        );
        $rulesStmt->execute([
            'zone_id' => $zoneId,
            'service_type_id' => $serviceTypeId,
            'pax_range_id' => $paxRangeId,
        ]);
        $existingRules = $rulesStmt->fetchAll();

        $existingByCurrency = [];
        foreach ($existingRules as $rule) {
            $existingByCurrency[strtoupper((string) ($rule['currency_code'] ?? ''))] = $rule;
        }

        $currencyRows = [];
        foreach ($currencies as $currency) {
            $code = strtoupper((string) ($currency['code'] ?? ''));
            $existing = $existingByCurrency[$code] ?? null;

            $currencyRows[] = [
                'currency_code' => $code,
                'currency_name' => (string) ($currency['name'] ?? ''),
                'rule_id' => $existing ? (int) ($existing['id'] ?? 0) : 0,
                'one_way_price' => $existing ? (string) ($existing['one_way_price'] ?? '0.00') : '0.00',
                'round_trip_price' => $existing ? (string) ($existing['round_trip_price'] ?? '0.00') : '0.00',
                'is_active' => $existing ? (int) ($existing['is_active'] ?? 0) : 0,
            ];
        }

        if ($request->method() === 'GET') {
            $form = [
                'one_way_price' => [],
                'round_trip_price' => [],
                'is_active' => [],
            ];

            foreach ($currencyRows as $row) {
                $code = (string) $row['currency_code'];
                $form['one_way_price'][$code] = (string) $row['one_way_price'];
                $form['round_trip_price'][$code] = (string) $row['round_trip_price'];
                if ((int) $row['is_active'] === 1) {
                    $form['is_active'][$code] = '1';
                }
            }

            return Response::view('admin/pricing/rate_rules/edit_group', [
                'title' => 'Editar grupo de tarifas',
                'csrf_token' => Csrf::token(),
                'errors' => [],
                'group' => [
                    'zone_id' => (int) ($group['zone_id'] ?? 0),
                    'service_type_id' => (int) ($group['service_type_id'] ?? 0),
                    'pax_range_id' => (int) ($group['pax_range_id'] ?? 0),
                    'zone_name' => (string) ($group['zone_name'] ?? ''),
                    'service_name' => (string) ($group['service_name'] ?? ''),
                    'pax_label' => (string) ($group['pax_label'] ?? ''),
                ],
                'currency_rows' => $currencyRows,
                'form' => $form,
            ], 'admin');
        }

        if (!Csrf::validate((string) $request->post('_csrf', ''))) {
            return Response::redirect('/admin/pricing/rate-rules');
        }

        $oneWayPrices = $request->post('one_way_price', []);
        $roundTripPrices = $request->post('round_trip_price', []);
        $activeFlags = $request->post('is_active', []);

        if (!is_array($oneWayPrices)) {
            $oneWayPrices = [];
        }
        if (!is_array($roundTripPrices)) {
            $roundTripPrices = [];
        }
        if (!is_array($activeFlags)) {
            $activeFlags = [];
        }

        $errors = [];
        $changes = [];
        $viewRows = [];

        foreach ($currencyRows as $row) {
            $code = (string) $row['currency_code'];
            $ruleId = (int) $row['rule_id'];

            $oneWayRaw = trim((string) ($oneWayPrices[$code] ?? ''));
            $roundTripRaw = trim((string) ($roundTripPrices[$code] ?? ''));
            $isActive = isset($activeFlags[$code]) ? 1 : 0;

            if ($oneWayRaw === '' || !is_numeric($oneWayRaw) || (float) $oneWayRaw < 0) {
                $errors['one_way_' . $code] = 'One way inválido para ' . $code . '.';
            }
            if ($roundTripRaw === '' || !is_numeric($roundTripRaw) || (float) $roundTripRaw < 0) {
                $errors['round_trip_' . $code] = 'Round trip inválido para ' . $code . '.';
            }

            $oneWay = (float) ($oneWayRaw === '' ? '0' : $oneWayRaw);
            $roundTrip = (float) ($roundTripRaw === '' ? '0' : $roundTripRaw);

            $changes[] = [
                'rule_id' => $ruleId,
                'currency_code' => $code,
                'one_way_price' => $oneWay,
                'round_trip_price' => $roundTrip,
                'is_active' => $isActive,
            ];

            $viewRows[] = [
                'currency_code' => $code,
                'currency_name' => (string) $row['currency_name'],
                'rule_id' => $ruleId,
                'one_way_price' => (string) $oneWayRaw,
                'round_trip_price' => (string) $roundTripRaw,
                'is_active' => $isActive,
            ];
        }

        $form = [
            'one_way_price' => $oneWayPrices,
            'round_trip_price' => $roundTripPrices,
            'is_active' => $activeFlags,
        ];

        if (!empty($errors)) {
            return Response::view('admin/pricing/rate_rules/edit_group', [
                'title' => 'Editar grupo de tarifas',
                'csrf_token' => Csrf::token(),
                'errors' => $errors,
                'group' => [
                    'zone_id' => (int) ($group['zone_id'] ?? 0),
                    'service_type_id' => (int) ($group['service_type_id'] ?? 0),
                    'pax_range_id' => (int) ($group['pax_range_id'] ?? 0),
                    'zone_name' => (string) ($group['zone_name'] ?? ''),
                    'service_name' => (string) ($group['service_name'] ?? ''),
                    'pax_label' => (string) ($group['pax_label'] ?? ''),
                ],
                'currency_rows' => $viewRows,
                'form' => $form,
            ], 'admin');
        }

        $updateStmt = $db->prepare(
            'UPDATE rate_rules
             SET one_way_price = :one_way_price,
                 round_trip_price = :round_trip_price,
                 is_active = :is_active
             WHERE id = :id'
        );

        $insertStmt = $db->prepare(
            'INSERT INTO rate_rules (
                zone_id,
                service_type_id,
                pax_range_id,
                vehicle_id,
                currency_code,
                one_way_price,
                round_trip_price,
                is_active,
                created_at
             ) VALUES (
                :zone_id,
                :service_type_id,
                :pax_range_id,
                NULL,
                :currency_code,
                :one_way_price,
                :round_trip_price,
                :is_active,
                NOW()
             )'
        );

        foreach ($changes as $change) {
            if ((int) $change['rule_id'] > 0) {
                $updateStmt->execute([
                    'id' => (int) $change['rule_id'],
                    'one_way_price' => (float) $change['one_way_price'],
                    'round_trip_price' => (float) $change['round_trip_price'],
                    'is_active' => (int) $change['is_active'],
                ]);
                continue;
            }

            if (
                (int) $change['is_active'] === 1
                || (float) $change['one_way_price'] > 0
                || (float) $change['round_trip_price'] > 0
            ) {
                $insertStmt->execute([
                    'zone_id' => $zoneId,
                    'service_type_id' => $serviceTypeId,
                    'pax_range_id' => $paxRangeId,
                    'currency_code' => (string) $change['currency_code'],
                    'one_way_price' => (float) $change['one_way_price'],
                    'round_trip_price' => (float) $change['round_trip_price'],
                    'is_active' => (int) $change['is_active'],
                ]);
            }
        }

        return Response::redirect('/admin/pricing/rate-rules');
    }

    public function edit(Request $request): Response
    {
        $id = (int) $request->query('id', 0);
        if ($id <= 0) {
            return Response::redirect('/admin/pricing/rate-rules');
        }

        $db = DB::connection();
        $stmt = $db->prepare(
            'SELECT
                rr.id,
                rr.one_way_price,
                rr.round_trip_price,
                rr.is_active,
                rr.currency_code,
                z.name_es AS zone_name,
                st.name_es AS service_name,
                pr.label AS pax_label
             FROM rate_rules rr
             INNER JOIN zones z ON z.id = rr.zone_id
             INNER JOIN service_types st ON st.id = rr.service_type_id
             INNER JOIN pax_ranges pr ON pr.id = rr.pax_range_id
             WHERE rr.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $rate = $stmt->fetch();

        if (!$rate) {
            return Response::redirect('/admin/pricing/rate-rules');
        }

        if ($request->method() === 'GET') {
            return Response::view('admin/pricing/rate_rules/edit', [
                'title' => 'Edit Rate Rule',
                'csrf_token' => Csrf::token(),
                'errors' => [],
                'form' => [
                    'id' => (string) $rate['id'],
                    'one_way_price' => (string) $rate['one_way_price'],
                    'round_trip_price' => (string) $rate['round_trip_price'],
                    'is_active' => (string) $rate['is_active'],
                ],
                'rate' => $rate,
            ], 'admin');
        }

        if (!Csrf::validate((string) $request->post('_csrf', ''))) {
            return Response::redirect('/admin/pricing/rate-rules');
        }

        $form = [
            'id' => (string) $id,
            'one_way_price' => trim((string) $request->post('one_way_price', '0')),
            'round_trip_price' => trim((string) $request->post('round_trip_price', '0')),
            'is_active' => $request->post('is_active') !== null ? 1 : 0,
        ];

        $errors = [];
        if (!is_numeric($form['one_way_price']) || (float) $form['one_way_price'] < 0) {
            $errors['one_way_price'] = 'One way inválido.';
        }
        if (!is_numeric($form['round_trip_price']) || (float) $form['round_trip_price'] < 0) {
            $errors['round_trip_price'] = 'Round trip inválido.';
        }

        if (!empty($errors)) {
            $rate['one_way_price'] = $form['one_way_price'];
            $rate['round_trip_price'] = $form['round_trip_price'];
            $rate['is_active'] = $form['is_active'];

            return Response::view('admin/pricing/rate_rules/edit', [
                'title' => 'Edit Rate Rule',
                'csrf_token' => Csrf::token(),
                'errors' => $errors,
                'form' => $form,
                'rate' => $rate,
            ], 'admin');
        }

        $updateStmt = $db->prepare(
            'UPDATE rate_rules
             SET one_way_price = :one_way_price,
                 round_trip_price = :round_trip_price,
                 is_active = :is_active
             WHERE id = :id'
        );
        $updateStmt->execute([
            'id' => $id,
            'one_way_price' => (float) $form['one_way_price'],
            'round_trip_price' => (float) $form['round_trip_price'],
            'is_active' => $form['is_active'],
        ]);

        return Response::redirect('/admin/pricing/rate-rules');
    }
}
