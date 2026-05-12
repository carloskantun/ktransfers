<?php
// Vista: Tipos de Servicio
/** @var array $services */
?>
<div class="page-header">
    <h1>Tipos de Servicio</h1>
</div>

<div class="card">
    <table class="admin-card-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Código</th>
                <th>Nombre (ES)</th>
                <th>Nombre (EN)</th>
                <th>Orden</th>
                <th>Activa</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($services as $service): ?>
            <tr>
                <td data-label="ID"><?= htmlspecialchars((string)($service['id'] ?? '')) ?></td>
                <td data-label="Codigo"><?= htmlspecialchars($service['code'] ?? '') ?></td>
                <td data-label="Nombre (ES)"><?= htmlspecialchars($service['name_es'] ?? '') ?></td>
                <td data-label="Nombre (EN)"><?= htmlspecialchars($service['name_en'] ?? '') ?></td>
                <td data-label="Orden"><?= htmlspecialchars((string)($service['sort_order'] ?? 0)) ?></td>
                <td data-label="Activa"><?= (int)($service['is_active'] ?? 0) === 1 ? 'Sí' : 'No' ?></td>
                <td data-label="Acciones"><a class="admin-row-action" href="/admin/catalog/services/edit?id=<?= (int) ($service['id'] ?? 0) ?>">Editar</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
