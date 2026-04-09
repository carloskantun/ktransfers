<?php
// Vista: Tipos de Servicio
/** @var array $services */
?>
<div class="page-header">
    <h1>Tipos de Servicio</h1>
</div>

<div class="card">
    <table>
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
                <td><?= htmlspecialchars((string)($service['id'] ?? '')) ?></td>
                <td><?= htmlspecialchars($service['code'] ?? '') ?></td>
                <td><?= htmlspecialchars($service['name_es'] ?? '') ?></td>
                <td><?= htmlspecialchars($service['name_en'] ?? '') ?></td>
                <td><?= htmlspecialchars((string)($service['sort_order'] ?? 0)) ?></td>
                <td><?= (int)($service['is_active'] ?? 0) === 1 ? 'Sí' : 'No' ?></td>
                <td><a href="/admin/catalog/services/edit?id=<?= (int) ($service['id'] ?? 0) ?>">Editar</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
