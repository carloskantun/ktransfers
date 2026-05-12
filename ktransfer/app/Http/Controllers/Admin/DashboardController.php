<?php
declare(strict_types=1);
namespace App\Http\Controllers\Admin;

use App\Core\ACL;
use App\Core\Auth;
use App\Core\DB;
use App\Core\Request;
use App\Core\Response;

class DashboardController {
    public function index(Request $request): Response
    {
        if (ACL::currentUserHasRole('operator') && !ACL::currentUserHasRole('admin')) {
            return Response::redirect('/admin/operations/agenda');
        }

        $db = DB::connection();
        $isAgencyScope = ACL::currentUserHasRole('agency') && !ACL::currentUserCan('bookings.manage');

        $sql = "
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
        ";

        $params = [];
        if ($isAgencyScope) {
            $sql .= ' WHERE b.created_by_user_id = :created_by_user_id';
            $params['created_by_user_id'] = Auth::id();
        }

        $sql .= "
            ORDER BY b.created_at DESC
            LIMIT 20
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $bookings = $stmt->fetchAll();

        $pendingDeleteRequests = [];
        if (ACL::currentUserCan('bookings.delete.approve')) {
            $delStmt = $db->prepare("
                SELECT
                    dr.id,
                    dr.booking_id,
                    dr.booking_code,
                    dr.reason,
                    dr.created_at,
                    u.name AS requested_by_name
                FROM booking_delete_requests dr
                LEFT JOIN users u ON u.id = dr.requested_by_user_id
                WHERE dr.status = 'PENDING'
                ORDER BY dr.created_at ASC
                LIMIT 50
            ");
            $delStmt->execute();
            $pendingDeleteRequests = $delStmt->fetchAll();
        }

        return Response::view('admin/dashboard/index', [
            'title' => 'Dashboard',
            'bookings' => $bookings,
            'is_agency_scope' => $isAgencyScope,
            'pending_delete_requests' => $pendingDeleteRequests,
        ], 'admin');
    }
}
