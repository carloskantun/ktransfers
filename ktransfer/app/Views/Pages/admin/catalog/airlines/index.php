<?php
// Vista: Aerolíneas
/** @var array $airlines */
?>
<div class="page-header">
    <h1>Aerolíneas</h1>
    <a href="/admin/catalog/airlines/create" class="btn btn-primary">Nueva Aerolínea</a>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Código IATA</th>
                <th>Nombre</th>
                <th>Activa</th>
                <th>Creada</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($airlines as $airline): ?>
            <tr>
                <td><?= htmlspecialchars((string)($airline['id'] ?? '')) ?></td>
                <td><strong><?= htmlspecialchars($airline['code'] ?? '') ?></strong></td>
                <td><?= htmlspecialchars($airline['name'] ?? '') ?></td>
                <td><?= (int)($airline['is_active'] ?? 0) === 1 ? '✓' : '✗' ?></td>
                <td><?= htmlspecialchars($airline['created_at'] ?? '') ?></td>
                <td><a href="/admin/catalog/airlines/edit?id=<?= (int) ($airline['id'] ?? 0) ?>">Editar</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
