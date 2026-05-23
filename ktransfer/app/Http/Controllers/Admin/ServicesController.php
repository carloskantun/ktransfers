<?php
declare(strict_types=1);
namespace App\Http\Controllers\Admin;

use App\Core\Csrf;
use App\Core\DB;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;

class ServicesController {
    public function index(Request $request): Response
    {
        $status = trim((string) $request->query('status', ''));
        $db = DB::connection();
        $stmt = $db->query(
            'SELECT id, code, name_es, name_en, is_active, sort_order FROM service_types ORDER BY sort_order ASC, id ASC'
        );
        $services = $stmt->fetchAll();

        [$notice, $error] = $this->statusMessage($status);

        return Response::view('admin/catalog/services/index', [
            'title' => 'Service Types',
            'services' => $services,
            'csrf_token' => Csrf::token(),
            'notice' => $notice,
            'error_message' => $error,
        ], 'admin');
    }

    public function create(Request $request): Response
    {
        if ($request->method() === 'GET') {
            return Response::view('admin/catalog/services/create', [
                'title' => 'Create Service Type',
                'csrf_token' => Csrf::token(),
                'errors' => [],
                'form' => [
                    'code' => '',
                    'name_es' => '',
                    'name_en' => '',
                    'sort_order' => '0',
                    'is_active' => '1',
                ],
            ], 'admin');
        }

        if (!Csrf::validate((string) $request->post('_csrf', ''))) {
            return Response::redirect('/admin/catalog/services?status=invalid_csrf');
        }

        $form = [
            'code' => strtoupper(trim((string) $request->post('code', ''))),
            'name_es' => trim((string) $request->post('name_es', '')),
            'name_en' => trim((string) $request->post('name_en', '')),
            'sort_order' => trim((string) $request->post('sort_order', '0')),
            'is_active' => $request->post('is_active') !== null ? 1 : 0,
        ];

        $errors = $this->validateServiceForm($form, null);
        if (!empty($errors)) {
            return Response::view('admin/catalog/services/create', [
                'title' => 'Create Service Type',
                'csrf_token' => Csrf::token(),
                'errors' => $errors,
                'form' => $form,
            ], 'admin');
        }

        $db = DB::connection();
        try {
            $insertStmt = $db->prepare(
                'INSERT INTO service_types (code, name_es, name_en, sort_order, is_active, created_at)
                 VALUES (:code, :name_es, :name_en, :sort_order, :is_active, NOW())'
            );
            $insertStmt->execute([
                'code' => $form['code'],
                'name_es' => $form['name_es'],
                'name_en' => $form['name_en'],
                'sort_order' => (int) $form['sort_order'],
                'is_active' => $form['is_active'],
            ]);
        } catch (\PDOException $exception) {
            return Response::view('admin/catalog/services/create', [
                'title' => 'Create Service Type',
                'csrf_token' => Csrf::token(),
                'errors' => ['general' => 'No se pudo crear el tipo de servicio. Verifica que el codigo no este repetido.'],
                'form' => $form,
            ], 'admin');
        }

        return Response::redirect('/admin/catalog/services?status=created');
    }

    public function edit(Request $request): Response
    {
        $id = (int) $request->query('id', 0);
        if ($id <= 0) {
            return Response::redirect('/admin/catalog/services');
        }

        $db = DB::connection();
        $stmt = $db->prepare('SELECT id, code, name_es, name_en, is_active, sort_order FROM service_types WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $service = $stmt->fetch();

        if (!$service) {
            return Response::redirect('/admin/catalog/services');
        }

        if ($request->method() === 'GET') {
            return Response::view('admin/catalog/services/edit', [
                'title' => 'Edit Service Type',
                'csrf_token' => Csrf::token(),
                'errors' => [],
                'form' => [
                    'id' => (string) $service['id'],
                    'code' => (string) $service['code'],
                    'name_es' => (string) $service['name_es'],
                    'name_en' => (string) $service['name_en'],
                    'sort_order' => (string) $service['sort_order'],
                    'is_active' => (string) $service['is_active'],
                ],
            ], 'admin');
        }

        if (!Csrf::validate((string) $request->post('_csrf', ''))) {
            return Response::redirect('/admin/catalog/services');
        }

        $form = [
            'id' => (string) $id,
            'code' => strtoupper(trim((string) $request->post('code', ''))),
            'name_es' => trim((string) $request->post('name_es', '')),
            'name_en' => trim((string) $request->post('name_en', '')),
            'sort_order' => trim((string) $request->post('sort_order', '')),
            'is_active' => $request->post('is_active') !== null ? 1 : 0,
        ];

        $errors = $this->validateServiceForm($form, $id);

        if (!empty($errors)) {
            return Response::view('admin/catalog/services/edit', [
                'title' => 'Edit Service Type',
                'csrf_token' => Csrf::token(),
                'errors' => $errors,
                'form' => $form,
            ], 'admin');
        }

        $updateStmt = $db->prepare(
            'UPDATE service_types
             SET code = :code,
                 name_es = :name_es,
                 name_en = :name_en,
                 sort_order = :sort_order,
                 is_active = :is_active
             WHERE id = :id'
        );
        try {
            $updateStmt->execute([
                'id' => $id,
                'code' => $form['code'],
                'name_es' => $form['name_es'],
                'name_en' => $form['name_en'],
                'sort_order' => (int) $form['sort_order'],
                'is_active' => $form['is_active'],
            ]);
        } catch (\PDOException $exception) {
            return Response::view('admin/catalog/services/edit', [
                'title' => 'Edit Service Type',
                'csrf_token' => Csrf::token(),
                'errors' => ['general' => 'No se pudo actualizar el tipo de servicio. Verifica el codigo ingresado.'],
                'form' => $form,
            ], 'admin');
        }

        return Response::redirect('/admin/catalog/services?status=updated');
    }

    public function delete(Request $request): Response
    {
        if ($request->method() !== 'POST') {
            return Response::redirect('/admin/catalog/services');
        }

        if (!Csrf::validate((string) $request->post('_csrf', ''))) {
            return Response::redirect('/admin/catalog/services?status=invalid_csrf');
        }

        $id = (int) $request->post('id', 0);
        if ($id <= 0) {
            return Response::redirect('/admin/catalog/services?status=invalid_id');
        }

        $db = DB::connection();
        $existsStmt = $db->prepare('SELECT id FROM service_types WHERE id = :id LIMIT 1');
        $existsStmt->execute(['id' => $id]);
        if (!$existsStmt->fetch()) {
            return Response::redirect('/admin/catalog/services?status=not_found');
        }

        $rateRuleUsageStmt = $db->prepare('SELECT id FROM rate_rules WHERE service_type_id = :id LIMIT 1');
        $rateRuleUsageStmt->execute(['id' => $id]);
        if ($rateRuleUsageStmt->fetch()) {
            return Response::redirect('/admin/catalog/services?status=in_use');
        }

        $bookingUsageStmt = $db->prepare('SELECT id FROM bookings WHERE service_type_id = :id LIMIT 1');
        $bookingUsageStmt->execute(['id' => $id]);
        if ($bookingUsageStmt->fetch()) {
            return Response::redirect('/admin/catalog/services?status=in_use');
        }

        $deleteStmt = $db->prepare('DELETE FROM service_types WHERE id = :id');
        $deleteStmt->execute(['id' => $id]);

        return Response::redirect('/admin/catalog/services?status=deleted');
    }

    private function validateServiceForm(array $form, ?int $excludeId): array
    {
        $errors = Validator::required($form, ['code', 'name_es', 'name_en', 'sort_order']);
        if (!ctype_digit((string) $form['sort_order']) || (int) $form['sort_order'] < 0) {
            $errors['sort_order'] = 'sort_order invalido.';
        }

        if ((string) ($form['code'] ?? '') !== '' && !$this->isCodeUnique((string) $form['code'], $excludeId)) {
            $errors['code'] = 'El codigo ya existe.';
        }

        return $errors;
    }

    private function isCodeUnique(string $code, ?int $excludeId): bool
    {
        $db = DB::connection();

        if ($excludeId === null) {
            $stmt = $db->prepare('SELECT id FROM service_types WHERE code = :code LIMIT 1');
            $stmt->execute(['code' => $code]);

            return !$stmt->fetch();
        }

        $stmt = $db->prepare('SELECT id FROM service_types WHERE code = :code AND id <> :id LIMIT 1');
        $stmt->execute([
            'code' => $code,
            'id' => $excludeId,
        ]);

        return !$stmt->fetch();
    }

    private function statusMessage(string $status): array
    {
        $noticeMap = [
            'created' => 'Tipo de servicio creado correctamente.',
            'updated' => 'Tipo de servicio actualizado correctamente.',
            'deleted' => 'Tipo de servicio eliminado correctamente.',
        ];

        if (isset($noticeMap[$status])) {
            return [$noticeMap[$status], ''];
        }

        $errorMap = [
            'invalid_csrf' => 'Sesion expirada. Intenta nuevamente.',
            'invalid_id' => 'Registro invalido.',
            'not_found' => 'El tipo de servicio no existe.',
            'in_use' => 'No se puede eliminar porque el tipo de servicio esta en uso.',
        ];

        return ['', $errorMap[$status] ?? ''];
    }
}
