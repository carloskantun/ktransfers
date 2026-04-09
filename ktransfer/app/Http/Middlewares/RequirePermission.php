<?php
declare(strict_types=1);
namespace App\Http\Middlewares;

use App\Core\ACL;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

class RequirePermission {
    public static function handle(Request $request, string $permissionCode): ?Response
    {
        if (!Auth::check()) {
            return Response::redirect('/admin/login');
        }

        if (!ACL::currentUserCan($permissionCode)) {
            return new Response('Forbidden: No tienes permiso para acceder.', 403);
        }

        return null;
    }
}
