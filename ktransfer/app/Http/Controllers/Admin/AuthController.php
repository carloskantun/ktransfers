<?php
declare(strict_types=1);
namespace App\Http\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;

class AuthController {
    public function showLogin(Request $request): Response
    {
        if (Auth::check()) {
            return Response::redirect('/admin');
        }

        return Response::view('admin/auth/login', [
            'title' => 'Login',
            'csrf_token' => Csrf::token(),
            'error' => null,
        ], 'admin_login');
    }

    public function login(Request $request): Response
    {
        if (!Csrf::validate((string) $request->post('_csrf', ''))) {
            return Response::view('admin/auth/login', [
                'title' => 'Login',
                'csrf_token' => Csrf::token(),
                'error' => 'Token CSRF inválido.',
            ], 'admin_login');
        }

        $email = trim((string) $request->post('email', ''));
        $password = (string) $request->post('password', '');

        if (!Auth::attempt($email, $password)) {
            return Response::view('admin/auth/login', [
                'title' => 'Login',
                'csrf_token' => Csrf::token(),
                'error' => 'Credenciales incorrectas.',
            ], 'admin_login');
        }

        return Response::redirect('/admin');
    }

    public function logout(Request $request): Response
    {
        if (!Csrf::validate((string) $request->post('_csrf', ''))) {
            return Response::redirect('/admin');
        }

        Auth::logout();
        return Response::redirect('/admin/login');
    }
}
