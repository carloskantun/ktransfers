<?php
declare(strict_types=1);
namespace App\Http\Controllers\Admin;

use App\Core\DB;
use App\Core\Request;
use App\Core\Response;

class AccountingController {
    public function index(Request $request): Response
    {
        $db = DB::connection();
        $filters = $this->normalizeFilters($request);
        $providers = $this->loadProviders($db);
        $currencies = $this->loadCurrencies($db);

        $paymentsReceived = $this->loadPaymentsReceived($db, $filters);
        $providerBalances = $this->loadProviderBalances($db, $filters);
        $agencySettlements = $this->loadAgencySettlements($db, $filters);
        $controlSummary = $this->loadControlSummary($db, $filters);

        return Response::view('admin/accounting/index', [
            'title' => 'Contabilidad',
            'payments_received' => $paymentsReceived,
            'provider_balances' => $providerBalances,
            'agency_settlements' => $agencySettlements,
            'control_summary' => $controlSummary,
            'filters' => $filters,
            'providers' => $providers,
            'currencies' => $currencies,
        ], 'admin');
    }

    public function export(Request $request): Response
    {
        $db = DB::connection();
        $filters = $this->normalizeFilters($request);
        $csv = $this->buildCsv(
            $this->loadPaymentsReceived($db, $filters),
            $this->loadProviderBalances($db, $filters),
            $this->loadAgencySettlements($db, $filters)
        );

        return new Response($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="contabilidad-' . date('Ymd-His') . '.csv"',
        ]);
    }

    private function loadPaymentsReceived(\PDO $db, array $filters): array
    {
        $where = ["status = 'PAID'"];
        $params = [];

        if ($filters['date_from'] !== '') {
            $where[] = 'COALESCE(paid_at, created_at) >= :payments_date_from';
            $params['payments_date_from'] = $filters['date_from'] . ' 00:00:00';
        }
        if ($filters['date_to'] !== '') {
            $where[] = 'COALESCE(paid_at, created_at) <= :payments_date_to';
            $params['payments_date_to'] = $filters['date_to'] . ' 23:59:59';
        }
        if ($filters['currency_code'] !== '') {
            $where[] = 'currency_code = :payments_currency_code';
            $params['payments_currency_code'] = $filters['currency_code'];
        }

        $stmt = $db->prepare("
            SELECT
                currency_code,
                SUM(amount) AS total_received
            FROM booking_payments
            WHERE " . implode(' AND ', $where) . "
            GROUP BY currency_code
            ORDER BY currency_code ASC
        ");
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    private function loadProviderBalances(\PDO $db, array $filters): array
    {
        $where = [];
        $params = [];

        if ($filters['date_from'] !== '') {
            $where[] = 'pt.created_at >= :provider_date_from';
            $params['provider_date_from'] = $filters['date_from'] . ' 00:00:00';
        }
        if ($filters['date_to'] !== '') {
            $where[] = 'pt.created_at <= :provider_date_to';
            $params['provider_date_to'] = $filters['date_to'] . ' 23:59:59';
        }
        if ($filters['currency_code'] !== '') {
            $where[] = 'pt.currency_code = :provider_currency_code';
            $params['provider_currency_code'] = $filters['currency_code'];
        }
        if ($filters['provider_id'] > 0) {
            $where[] = 'pt.provider_id = :provider_id';
            $params['provider_id'] = $filters['provider_id'];
        }

        $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $db->prepare("
            SELECT
                p.id AS provider_id,
                p.name AS provider_name,
                pt.currency_code,
                SUM(CASE WHEN pt.type = 'PAYABLE' THEN pt.amount ELSE 0 END) AS total_payable,
                SUM(CASE WHEN pt.type = 'PAYMENT' THEN pt.amount ELSE 0 END) AS total_paid,
                SUM(CASE WHEN pt.type = 'PAYABLE' THEN pt.amount ELSE 0 END)
                    - SUM(CASE WHEN pt.type = 'PAYMENT' THEN pt.amount ELSE 0 END) AS balance
            FROM provider_transactions pt
            INNER JOIN providers p ON p.id = pt.provider_id
            {$whereSql}
            GROUP BY p.id, p.name, pt.currency_code
            HAVING balance != 0
            ORDER BY p.name ASC, pt.currency_code ASC
        ");
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }

    private function loadAgencySettlements(\PDO $db, array $filters): array
    {
        $where = ['b.agency_collected_total > 0'];
        $params = [];

        if ($filters['date_from'] !== '') {
            $where[] = 'b.created_at >= :agency_date_from';
            $params['agency_date_from'] = $filters['date_from'] . ' 00:00:00';
        }
        if ($filters['date_to'] !== '') {
            $where[] = 'b.created_at <= :agency_date_to';
            $params['agency_date_to'] = $filters['date_to'] . ' 23:59:59';
        }
        if ($filters['currency_code'] !== '') {
            $where[] = 'b.currency_code = :agency_currency_code';
            $params['agency_currency_code'] = $filters['currency_code'];
        }

        $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $db->prepare(" 
            SELECT
                COALESCE(NULLIF(TRIM(b.agency_name), ''), 'Agencia sin nombre') AS agency_name,
                b.currency_code,
                COUNT(*) AS total_bookings,
                SUM(b.price_total) AS total_report,
                SUM(b.agency_collected_total) AS total_receipt,
                SUM(b.agency_collected_total - b.price_total) AS estimated_gain
            FROM bookings b
            {$whereSql}
            GROUP BY COALESCE(NULLIF(TRIM(b.agency_name), ''), 'Agencia sin nombre'), b.currency_code
            ORDER BY agency_name ASC, b.currency_code ASC
        ");
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, \PDO::PARAM_STR);
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }

    private function loadProviders(\PDO $db): array
    {
        $stmt = $db->query('SELECT id, name FROM providers ORDER BY name ASC');
        return $stmt->fetchAll();
    }

    private function loadControlSummary(\PDO $db, array $filters): array
    {
        $bookingsWhere = [];
        $bookingsParams = [];
        if ($filters['date_from'] !== '') {
            $bookingsWhere[] = 'COALESCE(b.arrival_datetime, b.departure_datetime, b.created_at) >= :bookings_date_from';
            $bookingsParams['bookings_date_from'] = $filters['date_from'] . ' 00:00:00';
        }
        if ($filters['date_to'] !== '') {
            $bookingsWhere[] = 'COALESCE(b.arrival_datetime, b.departure_datetime, b.created_at) <= :bookings_date_to';
            $bookingsParams['bookings_date_to'] = $filters['date_to'] . ' 23:59:59';
        }
        if ($filters['currency_code'] !== '') {
            $bookingsWhere[] = 'b.currency_code = :bookings_currency_code';
            $bookingsParams['bookings_currency_code'] = $filters['currency_code'];
        }
        $bookingsWhere[] = "b.status <> 'CANCELLED'";

        $bookingsSql = '
            SELECT COUNT(*) AS total_services
            FROM bookings b
            WHERE ' . implode(' AND ', $bookingsWhere);
        $bookingsStmt = $db->prepare($bookingsSql);
        foreach ($bookingsParams as $key => $value) {
            $bookingsStmt->bindValue(':' . $key, $value, \PDO::PARAM_STR);
        }
        $bookingsStmt->execute();
        $totalServices = (int) $bookingsStmt->fetchColumn();

        $transactionWhere = [];
        $transactionParams = [];
        if ($filters['date_from'] !== '') {
            $transactionWhere[] = 'pt.created_at >= :tx_date_from';
            $transactionParams['tx_date_from'] = $filters['date_from'] . ' 00:00:00';
        }
        if ($filters['date_to'] !== '') {
            $transactionWhere[] = 'pt.created_at <= :tx_date_to';
            $transactionParams['tx_date_to'] = $filters['date_to'] . ' 23:59:59';
        }
        if ($filters['currency_code'] !== '') {
            $transactionWhere[] = 'pt.currency_code = :tx_currency_code';
            $transactionParams['tx_currency_code'] = $filters['currency_code'];
        }
        if ($filters['provider_id'] > 0) {
            $transactionWhere[] = 'pt.provider_id = :tx_provider_id';
            $transactionParams['tx_provider_id'] = $filters['provider_id'];
        }

        $transactionWhereSql = !empty($transactionWhere) ? 'WHERE ' . implode(' AND ', $transactionWhere) : '';
        $summaryStmt = $db->prepare(
            "SELECT
                pt.currency_code,
                SUM(CASE WHEN pt.type = 'PAYABLE' THEN pt.amount ELSE 0 END) AS total_payable,
                SUM(CASE WHEN pt.type = 'PAYMENT' THEN pt.amount ELSE 0 END) AS total_paid,
                SUM(CASE WHEN pt.type = 'RECEIVABLE' THEN pt.amount ELSE 0 END) AS total_receivable,
                SUM(CASE WHEN pt.type = 'CHARGE' THEN pt.amount ELSE 0 END) AS total_charged
             FROM provider_transactions pt
             {$transactionWhereSql}
             GROUP BY pt.currency_code
             ORDER BY pt.currency_code ASC"
        );
        foreach ($transactionParams as $key => $value) {
            $summaryStmt->bindValue(':' . $key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
        $summaryStmt->execute();

        $byCurrency = [];
        foreach ($summaryStmt->fetchAll() as $row) {
            $currencyCode = (string) ($row['currency_code'] ?? '');
            if ($currencyCode === '') {
                continue;
            }

            $porPagar = (float) ($row['total_payable'] ?? 0) - (float) ($row['total_paid'] ?? 0);
            $porCobrar = (float) ($row['total_receivable'] ?? 0) - (float) ($row['total_charged'] ?? 0);
            $byCurrency[] = [
                'currency_code' => $currencyCode,
                'to_pay' => $porPagar,
                'to_collect' => $porCobrar,
            ];
        }

        return [
            'total_services' => $totalServices,
            'by_currency' => $byCurrency,
        ];
    }

    private function loadCurrencies(\PDO $db): array
    {
        $stmt = $db->query('SELECT code, name FROM currencies ORDER BY code ASC');
        return $stmt->fetchAll();
    }

    /**
     * @return array{date_from:string,date_to:string,currency_code:string,provider_id:int}
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
            'provider_id' => max(0, (int) $request->query('provider_id', 0)),
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

    private function buildCsv(array $paymentsReceived, array $providerBalances, array $agencySettlements): string
    {
        $handle = fopen('php://temp', 'r+');
        if ($handle === false) {
            return '';
        }

        fputcsv($handle, ['Pagos recibidos']);
        fputcsv($handle, ['Moneda', 'Total recibido']);
        foreach ($paymentsReceived as $row) {
            fputcsv($handle, [
                (string) ($row['currency_code'] ?? ''),
                number_format((float) ($row['total_received'] ?? 0), 2, '.', ''),
            ]);
        }

        fputcsv($handle, []);
        fputcsv($handle, ['Saldos de proveedores']);
        fputcsv($handle, ['Proveedor', 'Moneda', 'Por pagar', 'Pagado', 'Saldo']);
        foreach ($providerBalances as $row) {
            fputcsv($handle, [
                (string) ($row['provider_name'] ?? ''),
                (string) ($row['currency_code'] ?? ''),
                number_format((float) ($row['total_payable'] ?? 0), 2, '.', ''),
                number_format((float) ($row['total_paid'] ?? 0), 2, '.', ''),
                number_format((float) ($row['balance'] ?? 0), 2, '.', ''),
            ]);
        }

        fputcsv($handle, []);
        fputcsv($handle, ['Resumen comercial de agencias']);
        fputcsv($handle, ['Agencia', 'Moneda', 'Reservas', 'Tarifa reporte', 'Cobro cliente', 'Ganancia estimada']);
        foreach ($agencySettlements as $row) {
            fputcsv($handle, [
                (string) ($row['agency_name'] ?? ''),
                (string) ($row['currency_code'] ?? ''),
                (string) ($row['total_bookings'] ?? '0'),
                number_format((float) ($row['total_report'] ?? 0), 2, '.', ''),
                number_format((float) ($row['total_receipt'] ?? 0), 2, '.', ''),
                number_format((float) ($row['estimated_gain'] ?? 0), 2, '.', ''),
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return "\xEF\xBB\xBF" . (is_string($csv) ? $csv : '');
    }
}
