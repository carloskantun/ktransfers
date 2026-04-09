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

        // Pagos recibidos
        $paymentsStmt = $db->query("
            SELECT
                currency_code,
                SUM(amount) AS total_received
            FROM booking_payments
            WHERE status = 'PAID'
            GROUP BY currency_code
        ");
        $paymentsReceived = $paymentsStmt->fetchAll();

        // Pendiente por pagar a proveedores
        $payablesStmt = $db->query("
            SELECT
                p.name AS provider_name,
                pt.currency_code,
                SUM(CASE WHEN pt.type = 'PAYABLE' THEN pt.amount ELSE 0 END) -
                SUM(CASE WHEN pt.type = 'PAYMENT' THEN pt.amount ELSE 0 END) AS balance
            FROM provider_transactions pt
            INNER JOIN providers p ON p.id = pt.provider_id
            GROUP BY p.id, p.name, pt.currency_code
            HAVING balance != 0
            ORDER BY p.name ASC
        ");
        $providerBalances = $payablesStmt->fetchAll();

        return Response::view('admin/accounting/index', [
            'title' => 'Accounting',
            'payments_received' => $paymentsReceived,
            'provider_balances' => $providerBalances,
        ], 'admin');
    }
}
