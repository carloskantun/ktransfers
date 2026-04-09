<?php
declare(strict_types=1);
namespace App\Http\Controllers\Admin;

use App\Core\Csrf;
use App\Core\DB;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;

class CurrenciesController {
    public function index(Request $request): Response
    {
        $db = DB::connection();
        $stmt = $db->query('SELECT code, name, symbol, is_active FROM currencies ORDER BY code ASC');
        $currencies = $stmt->fetchAll();

        return Response::view('admin/catalog/currencies/index', [
            'title' => 'Currencies',
            'currencies' => $currencies,
            'csrf_token' => Csrf::token(),
        ], 'admin');
    }

    public function create(Request $request): Response
    {
        if ($request->method() === 'GET') {
            return Response::view('admin/catalog/currencies/create', [
                'title' => 'Create Currency',
                'csrf_token' => Csrf::token(),
                'errors' => [],
                'form' => ['code' => '', 'name' => '', 'symbol' => ''],
            ], 'admin');
        }

        if (!Csrf::validate((string) $request->post('_csrf', ''))) {
            return Response::redirect('/admin/catalog/currencies');
        }

        $form = [
            'code' => strtoupper(trim((string) $request->post('code', ''))),
            'name' => trim((string) $request->post('name', '')),
            'symbol' => trim((string) $request->post('symbol', '')),
        ];

        $errors = Validator::required($form, ['code', 'name']);
        if (!preg_match('/^[A-Z]{3}$/', $form['code'])) {
            $errors['code'] = 'El código debe tener 3 letras (ej. USD).';
        }

        $db = DB::connection();
        $existsStmt = $db->prepare('SELECT code FROM currencies WHERE code = :code LIMIT 1');
        $existsStmt->execute(['code' => $form['code']]);
        if ($existsStmt->fetch()) {
            $errors['code'] = 'Ese código de moneda ya existe.';
        }

        if (!empty($errors)) {
            return Response::view('admin/catalog/currencies/create', [
                'title' => 'Create Currency',
                'csrf_token' => Csrf::token(),
                'errors' => $errors,
                'form' => $form,
            ], 'admin');
        }

        $stmt = $db->prepare(
            'INSERT INTO currencies (code, name, symbol, is_active) VALUES (:code, :name, :symbol, 1)'
        );
        $stmt->execute([
            'code' => $form['code'],
            'name' => $form['name'],
            'symbol' => $form['symbol'] === '' ? null : $form['symbol'],
        ]);

        return Response::redirect('/admin/catalog/currencies');
    }

    public function edit(Request $request): Response
    {
        $code = strtoupper(trim((string) $request->query('code', '')));
        if (!preg_match('/^[A-Z]{3}$/', $code)) {
            return Response::redirect('/admin/catalog/currencies');
        }

        $db = DB::connection();
        $stmt = $db->prepare('SELECT code, name, symbol, is_active FROM currencies WHERE code = :code LIMIT 1');
        $stmt->execute(['code' => $code]);
        $currency = $stmt->fetch();

        if (!$currency) {
            return Response::redirect('/admin/catalog/currencies');
        }

        if ($request->method() === 'GET') {
            return Response::view('admin/catalog/currencies/edit', [
                'title' => 'Edit Currency',
                'csrf_token' => Csrf::token(),
                'errors' => [],
                'form' => [
                    'code' => (string) $currency['code'],
                    'name' => (string) $currency['name'],
                    'symbol' => (string) ($currency['symbol'] ?? ''),
                    'is_active' => (string) $currency['is_active'],
                ],
            ], 'admin');
        }

        if (!Csrf::validate((string) $request->post('_csrf', ''))) {
            return Response::redirect('/admin/catalog/currencies');
        }

        $form = [
            'code' => $code,
            'name' => trim((string) $request->post('name', '')),
            'symbol' => trim((string) $request->post('symbol', '')),
            'is_active' => $request->post('is_active') !== null ? 1 : 0,
        ];

        $errors = Validator::required($form, ['name']);
        if (!empty($errors)) {
            return Response::view('admin/catalog/currencies/edit', [
                'title' => 'Edit Currency',
                'csrf_token' => Csrf::token(),
                'errors' => $errors,
                'form' => $form,
            ], 'admin');
        }

        $updateStmt = $db->prepare(
            'UPDATE currencies SET name = :name, symbol = :symbol, is_active = :is_active WHERE code = :code'
        );
        $updateStmt->execute([
            'code' => $code,
            'name' => $form['name'],
            'symbol' => $form['symbol'] === '' ? null : $form['symbol'],
            'is_active' => $form['is_active'],
        ]);

        return Response::redirect('/admin/catalog/currencies');
    }
}
