<?php
/** @var array $ranges */
$ranges = $ranges ?? [];
?>
<div class="page-header">
    <h1>Rangos de Pasajeros (PAX)</h1>
    <a href="/admin/pricing/pax-ranges/create" class="btn btn-primary">Nuevo Rango</a>
</div>

<div class="card">
    <table class="admin-card-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Etiqueta</th>
                <th>Mínimo</th>
                <th>Máximo</th>
                <th>Orden</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($ranges)): ?>
            <tr>
                <td class="admin-empty-row" colspan="6">No hay rangos registrados.</td>
            </tr>
            <?php endif; ?>
            <?php foreach ($ranges as $range): ?>
            <tr>
                <td data-label="ID"><?= htmlspecialchars((string) ($range['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td data-label="Etiqueta"><?= htmlspecialchars((string) ($range['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td data-label="Minimo"><?= htmlspecialchars((string) ($range['min_pax'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td data-label="Maximo"><?= htmlspecialchars((string) ($range['max_pax'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td data-label="Orden"><?= htmlspecialchars((string) ($range['sort_order'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td data-label="Acciones"><a class="admin-row-action" href="/admin/pricing/pax-ranges/edit?id=<?= (int) ($range['id'] ?? 0) ?>">Editar</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
