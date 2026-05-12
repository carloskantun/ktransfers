<?php
declare(strict_types=1);
namespace App\Http\Controllers\Admin;

use App\Core\DB;
use App\Core\Request;
use App\Core\Response;

class KpisController {
    public function index(Request $request): Response
    {
        $db = DB::connection();
        $filters = $this->normalizeFilters($request);
        $metrics = $this->loadMetrics($db, $filters);

        return Response::view('admin/kpis/index', array_merge([
            'title' => 'KPIs',
            'filters' => $filters,
            'zones' => $this->loadZones($db),
            'currencies' => $this->loadCurrencies($db),
        ], $metrics), 'admin');
    }

    public function export(Request $request): Response
    {
        $db = DB::connection();
        $filters = $this->normalizeFilters($request);
        $metrics = $this->loadMetrics($db, $filters);

        return new Response($this->buildCsv($metrics), 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="kpis-' . date('Ymd-His') . '.csv"',
        ]);
    }

    private function loadMetrics(\PDO $db, array $filters): array
    {
        $bookingWhere = [];
        $bookingParams = [];

        if ($filters['date_from'] !== '') {
            $bookingWhere[] = 'COALESCE(b.arrival_datetime, b.departure_datetime, b.created_at) >= :date_from';
            $bookingParams['date_from'] = $filters['date_from'] . ' 00:00:00';
        }
        if ($filters['date_to'] !== '') {
            $bookingWhere[] = 'COALESCE(b.arrival_datetime, b.departure_datetime, b.created_at) <= :date_to';
            $bookingParams['date_to'] = $filters['date_to'] . ' 23:59:59';
        }
        if ($filters['zone_id'] > 0) {
            $bookingWhere[] = 'b.zone_id = :zone_id';
            $bookingParams['zone_id'] = $filters['zone_id'];
        }
        if ($filters['currency_code'] !== '') {
            $bookingWhere[] = 'b.currency_code = :currency_code';
            $bookingParams['currency_code'] = $filters['currency_code'];
        }

        $bookingWhereSql = !empty($bookingWhere) ? 'WHERE ' . implode(' AND ', $bookingWhere) : '';

        $totalStmt = $db->prepare("SELECT COUNT(*) AS total FROM bookings b {$bookingWhereSql}");
        $this->bindParams($totalStmt, $bookingParams);
        $totalStmt->execute();
        $totalBookings = (int) ($totalStmt->fetch()['total'] ?? 0);

        $revenueWhere = ["bp.status = 'PAID'"];
        $revenueParams = [];
        if ($filters['date_from'] !== '') {
            $revenueWhere[] = 'COALESCE(bp.paid_at, bp.created_at) >= :paid_date_from';
            $revenueParams['paid_date_from'] = $filters['date_from'] . ' 00:00:00';
        }
        if ($filters['date_to'] !== '') {
            $revenueWhere[] = 'COALESCE(bp.paid_at, bp.created_at) <= :paid_date_to';
            $revenueParams['paid_date_to'] = $filters['date_to'] . ' 23:59:59';
        }
        if ($filters['currency_code'] !== '') {
            $revenueWhere[] = 'bp.currency_code = :paid_currency_code';
            $revenueParams['paid_currency_code'] = $filters['currency_code'];
        }
        if ($filters['zone_id'] > 0) {
            $revenueWhere[] = 'b.zone_id = :paid_zone_id';
            $revenueParams['paid_zone_id'] = $filters['zone_id'];
        }

        $revenueStmt = $db->prepare("
            SELECT
                bp.currency_code,
                SUM(bp.amount) AS total_revenue
            FROM booking_payments bp
            INNER JOIN bookings b ON b.id = bp.booking_id
            WHERE " . implode(' AND ', $revenueWhere) . "
            GROUP BY bp.currency_code
            ORDER BY bp.currency_code ASC
        ");
        $this->bindParams($revenueStmt, $revenueParams);
        $revenueStmt->execute();
        $revenue = $revenueStmt->fetchAll();

        $noshowWhereSql = !empty($bookingWhere) ? $bookingWhereSql . " AND b.status = 'NO_SHOW'" : "WHERE b.status = 'NO_SHOW'";
        $noshowStmt = $db->prepare("SELECT COUNT(*) AS total FROM bookings b {$noshowWhereSql}");
        $this->bindParams($noshowStmt, $bookingParams);
        $noshowStmt->execute();
        $noShows = (int) ($noshowStmt->fetch()['total'] ?? 0);

        $topZonesStmt = $db->prepare("
            SELECT z.name_es AS zone_name, COUNT(b.id) AS total
            FROM bookings b
            INNER JOIN zones z ON z.id = b.zone_id
            {$bookingWhereSql}
            GROUP BY z.id, z.name_es
            ORDER BY total DESC
            LIMIT 5
        ");
        $this->bindParams($topZonesStmt, $bookingParams);
        $topZonesStmt->execute();
        $topZones = $topZonesStmt->fetchAll();

        $paidWhereSql = !empty($bookingWhere) ? $bookingWhereSql . " AND b.payment_status = 'PAID'" : "WHERE b.payment_status = 'PAID'";
        $unpaidWhereSql = !empty($bookingWhere) ? $bookingWhereSql . " AND b.payment_status IN ('UNPAID', 'PARTIAL')" : "WHERE b.payment_status IN ('UNPAID', 'PARTIAL')";
        $paidStmt = $db->prepare("SELECT COUNT(*) AS total FROM bookings b {$paidWhereSql}");
        $unpaidStmt = $db->prepare("SELECT COUNT(*) AS total FROM bookings b {$unpaidWhereSql}");
        $this->bindParams($paidStmt, $bookingParams);
        $this->bindParams($unpaidStmt, $bookingParams);
        $paidStmt->execute();
        $unpaidStmt->execute();
        $paidCount = (int) ($paidStmt->fetch()['total'] ?? 0);
        $unpaidCount = (int) ($unpaidStmt->fetch()['total'] ?? 0);

        $agencyWhere = ['b.agency_collected_total > 0'];
        $agencyParams = [];
        if ($filters['date_from'] !== '') {
            $agencyWhere[] = 'b.created_at >= :agency_date_from';
            $agencyParams['agency_date_from'] = $filters['date_from'] . ' 00:00:00';
        }
        if ($filters['date_to'] !== '') {
            $agencyWhere[] = 'b.created_at <= :agency_date_to';
            $agencyParams['agency_date_to'] = $filters['date_to'] . ' 23:59:59';
        }
        if ($filters['zone_id'] > 0) {
            $agencyWhere[] = 'b.zone_id = :agency_zone_id';
            $agencyParams['agency_zone_id'] = $filters['zone_id'];
        }
        if ($filters['currency_code'] !== '') {
            $agencyWhere[] = 'b.currency_code = :agency_currency_code';
            $agencyParams['agency_currency_code'] = $filters['currency_code'];
        }

        $agencyCollectedStmt = $db->prepare(
            'SELECT COUNT(*) AS total
             FROM bookings b
             WHERE ' . implode(' AND ', $agencyWhere)
        );
        $this->bindParams($agencyCollectedStmt, $agencyParams);
        $agencyCollectedStmt->execute();
        $agencyCollectedBookings = (int) ($agencyCollectedStmt->fetch()['total'] ?? 0);

        $agencyBalanceStmt = $db->prepare(
            'SELECT
                b.currency_code,
                SUM(b.agency_collected_total - b.price_total) AS estimated_gain
             FROM bookings b
             WHERE ' . implode(' AND ', $agencyWhere) . '
             GROUP BY b.currency_code
             ORDER BY b.currency_code ASC'
        );
        $this->bindParams($agencyBalanceStmt, $agencyParams);
        $agencyBalanceStmt->execute();
        $agencyEstimatedGainByCurrency = $agencyBalanceStmt->fetchAll();

        return [
            'total_bookings' => $totalBookings,
            'revenue_by_currency' => $revenue,
            'no_shows' => $noShows,
            'top_zones' => $topZones,
            'paid_bookings' => $paidCount,
            'unpaid_bookings' => $unpaidCount,
            'agency_collected_bookings' => $agencyCollectedBookings,
            'agency_estimated_gain_by_currency' => $agencyEstimatedGainByCurrency,
        ];
    }

    private function loadZones(\PDO $db): array
    {
        return $db->query('SELECT id, name_es FROM zones ORDER BY name_es ASC')->fetchAll();
    }

    private function loadCurrencies(\PDO $db): array
    {
        return $db->query('SELECT code, name FROM currencies ORDER BY code ASC')->fetchAll();
    }

    private function bindParams(\PDOStatement $stmt, array $params): void
    {
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
    }

    /**
     * @return array{date_from:string,date_to:string,currency_code:string,zone_id:int}
     */
    private function normalizeFilters(Request $request): array
    {
        $dateFrom = $this->normalizeDate((string) $request->query('date_from', '')) ?? '';
        $dateTo = $this->normalizeDate((string) $request->query('date_to', '')) ?? '';
        if ($dateFrom !== '' && $dateTo !== '' && $dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        $currencyCode = strtoupper(trim((string) $request->query('currency_code', '')));
        if ($currencyCode !== '' && !preg_match('/^[A-Z]{3}$/', $currencyCode)) {
            $currencyCode = '';
        }

        return [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'currency_code' => $currencyCode,
            'zone_id' => max(0, (int) $request->query('zone_id', 0)),
        ];
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

    private function buildCsv(array $metrics): string
    {
        $handle = fopen('php://temp', 'r+');
        if ($handle === false) {
            return '';
        }

        fputcsv($handle, ['Metrica', 'Valor']);
        fputcsv($handle, ['Total reservas', (string) ($metrics['total_bookings'] ?? 0)]);
        fputcsv($handle, ['No shows', (string) ($metrics['no_shows'] ?? 0)]);
        fputcsv($handle, ['Reservas pagadas', (string) ($metrics['paid_bookings'] ?? 0)]);
        fputcsv($handle, ['Reservas sin pagar', (string) ($metrics['unpaid_bookings'] ?? 0)]);
        fputcsv($handle, []);
        fputcsv($handle, ['Ingresos por moneda']);
        fputcsv($handle, ['Moneda', 'Total']);
        foreach (($metrics['revenue_by_currency'] ?? []) as $row) {
            fputcsv($handle, [(string) ($row['currency_code'] ?? ''), number_format((float) ($row['total_revenue'] ?? 0), 2, '.', '')]);
        }
        fputcsv($handle, []);
        fputcsv($handle, ['Top zonas']);
        fputcsv($handle, ['Zona', 'Total reservas']);
        foreach (($metrics['top_zones'] ?? []) as $row) {
            fputcsv($handle, [(string) ($row['zone_name'] ?? ''), (string) ($row['total'] ?? 0)]);
        }

        fputcsv($handle, []);
        fputcsv($handle, ['Cobro capturado por agencias']);
        fputcsv($handle, ['Reservas con cobro de agencia', (string) ($metrics['agency_collected_bookings'] ?? 0)]);
        fputcsv($handle, ['Moneda', 'Ganancia estimada (cobro cliente - tarifa reporte)']);
        foreach (($metrics['agency_estimated_gain_by_currency'] ?? []) as $row) {
            fputcsv($handle, [
                (string) ($row['currency_code'] ?? ''),
                number_format((float) ($row['estimated_gain'] ?? 0), 2, '.', ''),
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return "\xEF\xBB\xBF" . (is_string($csv) ? $csv : '');
    }
}
