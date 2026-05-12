<?php
declare(strict_types=1);

$zones = $zones ?? [];
?>
<div class="page-header">
    <h1>Zonas</h1>
    <a href="/admin/catalog/zones/create" class="btn btn-primary">Nueva zona</a>
</div>

<?php if (empty($zones)): ?>
    <div class="card">
        <p>No hay zonas registradas.</p>
    </div>
<?php else: ?>
    <div class="card">
        <table class="admin-card-table">
            <thead>
                <tr>
                    <th>Codigo</th>
                    <th>Nombre (ES)</th>
                    <th>Nombre (EN)</th>
                    <th>Activa</th>
                    <th>Orden</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($zones as $zone): ?>
                    <tr>
                        <td data-label="Codigo"><?= htmlspecialchars((string) $zone['code'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td data-label="Nombre (ES)"><?= htmlspecialchars((string) $zone['name_es'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td data-label="Nombre (EN)"><?= htmlspecialchars((string) $zone['name_en'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td data-label="Activa"><?= ((int) $zone['is_active']) === 1 ? 'Si' : 'No' ?></td>
                        <td data-label="Orden"><?= (int) $zone['sort_order'] ?></td>
                        <td data-label="Acciones"><a class="admin-row-action" href="/admin/catalog/zones/edit?id=<?= (int) $zone['id'] ?>">Editar</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
