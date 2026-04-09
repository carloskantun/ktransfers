<?php
declare(strict_types=1);
namespace App\Http\Middlewares;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

class RequireAuth {
    public static function handle(Request $request): ?Response
    {
        if (!Auth::check()) {
            return Response::redirect('/admin/login');
        }

        return null;
    }
}
