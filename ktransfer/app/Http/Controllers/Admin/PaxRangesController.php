<?php
declare(strict_types=1);
namespace App\Http\Controllers\Admin;

use App\Core\Csrf;
use App\Core\DB;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;

class PaxRangesController {
    public function index(Request $request): Response
    {
        $db = DB::connection();
        $stmt = $db->query(
            'SELECT id, label, min_pax, max_pax, sort_order FROM pax_ranges ORDER BY min_pax ASC, sort_order ASC, id ASC'
        );
        $ranges = $stmt->fetchAll();

        return Response::view('admin/pricing/pax_ranges/index', [
            'title' => 'PAX Ranges',
            'ranges' => $ranges,
            'csrf_token' => Csrf::token(),
        ], 'admin');
    }

    public function create(Request $request): Response
    {
        if ($request->method() === 'GET') {
            return Response::view('admin/pricing/pax_ranges/create', [
                'title' => 'Create PAX Range',
                'csrf_token' => Csrf::token(),
                'errors' => [],
                'form' => ['label' => '', 'min_pax' => '', 'max_pax' => '', 'sort_order' => '0'],
            ], 'admin');
        }

        if (!Csrf::validate((string) $request->post('_csrf', ''))) {
            return Response::redirect('/admin/pricing/pax-ranges');
        }

        $form = [
            'label' => trim((string) $request->post('label', '')),
            'min_pax' => trim((string) $request->post('min_pax', '')),
            'max_pax' => trim((string) $request->post('max_pax', '')),
            'sort_order' => trim((string) $request->post('sort_order', '0')),
        ];

        $errors = $this->validateRangeForm($form);
        if (empty($errors) && $this->hasOverlap((int) $form['min_pax'], (int) $form['max_pax'], null)) {
            $errors['range'] = 'Este rango se cruza con otro ya existente.';
        }

        if (!empty($errors)) {
            return Response::view('admin/pricing/pax_ranges/create', [
                'title' => 'Create PAX Range',
                'csrf_token' => Csrf::token(),
                'errors' => $errors,
                'form' => $form,
            ], 'admin');
        }

        $db = DB::connection();
        $stmt = $db->prepare(
            'INSERT INTO pax_ranges (label, min_pax, max_pax, sort_order) VALUES (:label, :min_pax, :max_pax, :sort_order)'
        );
        $stmt->execute([
            'label' => $form['label'],
            'min_pax' => (int) $form['min_pax'],
            'max_pax' => (int) $form['max_pax'],
            'sort_order' => (int) $form['sort_order'],
        ]);

        return Response::redirect('/admin/pricing/pax-ranges');
    }

    public function edit(Request $request): Response
    {
        $id = (int) $request->query('id', 0);
        if ($id <= 0) {
            return Response::redirect('/admin/pricing/pax-ranges');
        }

        $db = DB::connection();
        $stmt = $db->prepare('SELECT id, label, min_pax, max_pax, sort_order FROM pax_ranges WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $range = $stmt->fetch();

        if (!$range) {
            return Response::redirect('/admin/pricing/pax-ranges');
        }

        if ($request->method() === 'GET') {
            return Response::view('admin/pricing/pax_ranges/edit', [
                'title' => 'Edit PAX Range',
                'csrf_token' => Csrf::token(),
                'errors' => [],
                'form' => [
                    'id' => (string) $range['id'],
                    'label' => (string) $range['label'],
                    'min_pax' => (string) $range['min_pax'],
                    'max_pax' => (string) $range['max_pax'],
                    'sort_order' => (string) $range['sort_order'],
                ],
            ], 'admin');
        }

        if (!Csrf::validate((string) $request->post('_csrf', ''))) {
            return Response::redirect('/admin/pricing/pax-ranges');
        }

        $form = [
            'id' => (string) $id,
            'label' => trim((string) $request->post('label', '')),
            'min_pax' => trim((string) $request->post('min_pax', '')),
            'max_pax' => trim((string) $request->post('max_pax', '')),
            'sort_order' => trim((string) $request->post('sort_order', '0')),
        ];

        $errors = $this->validateRangeForm($form);
        if (empty($errors) && $this->hasOverlap((int) $form['min_pax'], (int) $form['max_pax'], $id)) {
            $errors['range'] = 'Este rango se cruza con otro ya existente.';
        }

        if (!empty($errors)) {
            return Response::view('admin/pricing/pax_ranges/edit', [
                'title' => 'Edit PAX Range',
                'csrf_token' => Csrf::token(),
                'errors' => $errors,
                'form' => $form,
            ], 'admin');
        }

        $updateStmt = $db->prepare(
            'UPDATE pax_ranges
             SET label = :label,
                 min_pax = :min_pax,
                 max_pax = :max_pax,
                 sort_order = :sort_order
             WHERE id = :id'
        );
        $updateStmt->execute([
            'id' => $id,
            'label' => $form['label'],
            'min_pax' => (int) $form['min_pax'],
            'max_pax' => (int) $form['max_pax'],
            'sort_order' => (int) $form['sort_order'],
        ]);

        return Response::redirect('/admin/pricing/pax-ranges');
    }

    private function validateRangeForm(array $form): array
    {
        $errors = Validator::required($form, ['label', 'min_pax', 'max_pax', 'sort_order']);

        if (!ctype_digit((string) ($form['min_pax'] ?? '')) || (int) $form['min_pax'] < 1) {
            $errors['min_pax'] = 'min_pax debe ser un entero mayor o igual a 1.';
        }

        if (!ctype_digit((string) ($form['max_pax'] ?? '')) || (int) $form['max_pax'] < 1) {
            $errors['max_pax'] = 'max_pax debe ser un entero mayor o igual a 1.';
        }

        if (
            empty($errors['min_pax'])
            && empty($errors['max_pax'])
            && (int) $form['max_pax'] < (int) $form['min_pax']
        ) {
            $errors['max_pax'] = 'max_pax no puede ser menor que min_pax.';
        }

        if (!preg_match('/^-?\d+$/', (string) ($form['sort_order'] ?? ''))) {
            $errors['sort_order'] = 'sort_order inválido.';
        }

        return $errors;
    }

    private function hasOverlap(int $minPax, int $maxPax, ?int $excludeId): bool
    {
        $db = DB::connection();

        if ($excludeId === null) {
            $stmt = $db->prepare(
                'SELECT id
                 FROM pax_ranges
                 WHERE NOT (max_pax < :min_pax OR min_pax > :max_pax)
                 LIMIT 1'
            );
            $stmt->execute([
                'min_pax' => $minPax,
                'max_pax' => $maxPax,
            ]);

            return (bool) $stmt->fetch();
        }

        $stmt = $db->prepare(
            'SELECT id
             FROM pax_ranges
             WHERE id <> :exclude_id
               AND NOT (max_pax < :min_pax OR min_pax > :max_pax)
             LIMIT 1'
        );
        $stmt->execute([
            'exclude_id' => $excludeId,
            'min_pax' => $minPax,
            'max_pax' => $maxPax,
        ]);

        return (bool) $stmt->fetch();
    }
}
