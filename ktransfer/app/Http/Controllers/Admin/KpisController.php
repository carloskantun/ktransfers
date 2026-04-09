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

        // Total bookings
        $totalStmt = $db->query('SELECT COUNT(*) AS total FROM bookings');
        $totalBookings = (int) ($totalStmt->fetch()['total'] ?? 0);

        // Revenue por moneda
        $revenueStmt = $db->query("
            SELECT
                bp.currency_code,
                SUM(bp.amount) AS total_revenue
            FROM booking_payments bp
            WHERE bp.status = 'PAID'
            GROUP BY bp.currency_code
        ");
        $revenue = $revenueStmt->fetchAll();

        // No-shows
        $noshowStmt = $db->query("SELECT COUNT(*) AS total FROM bookings WHERE status = 'NO_SHOW'");
        $noShows = (int) ($noshowStmt->fetch()['total'] ?? 0);

        // Top zones
        $topZonesStmt = $db->query("
            SELECT z.name_es AS zone_name, COUNT(b.id) AS total
            FROM bookings b
            INNER JOIN zones z ON z.id = b.zone_id
            GROUP BY z.id, z.name_es
            ORDER BY total DESC
            LIMIT 5
        ");
        $topZones = $topZonesStmt->fetchAll();

        // Paid vs unpaid
        $paidStmt = $db->query("SELECT COUNT(*) AS total FROM bookings WHERE payment_status = 'PAID'");
        $unpaidStmt = $db->query("SELECT COUNT(*) AS total FROM bookings WHERE payment_status IN ('UNPAID', 'PARTIAL')");
        $paidCount = (int) ($paidStmt->fetch()['total'] ?? 0);
        $unpaidCount = (int) ($unpaidStmt->fetch()['total'] ?? 0);

        return Response::view('admin/kpis/index', [
            'title' => 'KPIs',
            'total_bookings' => $totalBookings,
            'revenue_by_currency' => $revenue,
            'no_shows' => $noShows,
            'top_zones' => $topZones,
            'paid_bookings' => $paidCount,
            'unpaid_bookings' => $unpaidCount,
        ], 'admin');
    }
}
