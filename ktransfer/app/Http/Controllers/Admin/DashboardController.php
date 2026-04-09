<?php
declare(strict_types=1);
namespace App\Http\Controllers\Admin;

use App\Core\DB;
use App\Core\Request;
use App\Core\Response;

class DashboardController {
    public function index(Request $request): Response
    {
        $db = DB::connection();

        $stmt = $db->query("
            SELECT
                b.id,
                b.booking_code,
                b.customer_name,
                b.price_total,
                b.currency_code,
                b.status,
                b.payment_status,
                b.created_at
            FROM bookings b
            ORDER BY b.created_at DESC
            LIMIT 20
        ");

        $bookings = $stmt->fetchAll();

        return Response::view('admin/dashboard/index', [
            'title' => 'Dashboard',
            'bookings' => $bookings,
        ], 'admin');
    }
}
