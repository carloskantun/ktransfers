<?php
// Vista: Vehículos
/** @var array $vehicles */
?>
<div class="page-header">
    <h1>Vehículos</h1>
    <a href="/admin/catalog/vehicles/create" class="btn btn-primary">Nuevo Vehículo</a>
</div>

<div class="card">
    <table class="admin-card-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Código</th>
                <th>Nombre</th>
                <th>Max Pasajeros</th>
                <th>Activa</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($vehicles as $vehicle): ?>
            <tr>
                <td data-label="ID"><?= htmlspecialchars((string)($vehicle['id'] ?? '')) ?></td>
                <td data-label="Codigo"><?= htmlspecialchars($vehicle['code'] ?? '') ?></td>
                <td data-label="Nombre"><?= htmlspecialchars($vehicle['name'] ?? '') ?></td>
                <td data-label="Max pasajeros"><?= htmlspecialchars((string)($vehicle['max_pax'] ?? 0)) ?></td>
                <td data-label="Activa"><?= (int)($vehicle['is_active'] ?? 0) === 1 ? 'Sí' : 'No' ?></td>
                <td data-label="Acciones"><a class="admin-row-action" href="/admin/catalog/vehicles/edit?id=<?= (int) ($vehicle['id'] ?? 0) ?>">Editar</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
