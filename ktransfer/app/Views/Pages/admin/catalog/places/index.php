<?php
// Vista: Lugares (Hoteles/Airbnb)
/** @var array $places */
/** @var array $zones */
/** @var array<string,mixed> $filters */
/** @var array<string,int> $pagination */

$currentPage = (int) ($pagination['page'] ?? 1);
$totalPages = (int) ($pagination['total_pages'] ?? 1);
$totalPlaces = (int) ($pagination['total'] ?? 0);
$search = trim((string) ($filters['q'] ?? ''));
$selectedZoneId = (int) ($filters['zone_id'] ?? 0);

$baseQuery = [];
if ($search !== '') {
    $baseQuery['q'] = $search;
}
if ($selectedZoneId > 0) {
    $baseQuery['zone_id'] = $selectedZoneId;
}
?>
<div class="page-header">
    <h1>Lugares (Hoteles/Airbnb)</h1>
    <div class="form-actions">
        <a href="/admin/catalog/places/export?<?= htmlspecialchars(http_build_query($baseQuery), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-secondary">Descargar CSV</a>
        <a href="/admin/catalog/places/create" class="btn btn-primary">Nuevo Lugar</a>
    </div>
</div>

<div class="card">
    <form method="get" action="/admin/catalog/places" class="admin-filter-bar">
        <div>
            <label for="q">Buscar hotel/lugar</label>
            <input id="q" name="q" type="text" value="<?= htmlspecialchars($search) ?>" placeholder="Nombre" />
        </div>
        <div>
            <label for="zone_id">Zona</label>
            <select id="zone_id" name="zone_id">
                <option value="0">Todas</option>
                <?php foreach ($zones as $zone): ?>
                    <?php $zoneId = (int) ($zone['id'] ?? 0); ?>
                    <option value="<?= $zoneId ?>" <?= $selectedZoneId === $zoneId ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string) ($zone['name_es'] ?? '')) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Filtrar</button>
        <a href="/admin/catalog/places" class="admin-row-action">Limpiar</a>
    </form>

    <table class="admin-card-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Zona</th>
                <th>Tipo</th>
                <th>Activa</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($places)): ?>
            <tr>
                <td class="admin-empty-row" colspan="6">No hay lugares registrados.</td>
            </tr>
            <?php endif; ?>
            <?php foreach ($places as $place): ?>
            <tr>
                <td data-label="ID"><?= htmlspecialchars((string)($place['id'] ?? '')) ?></td>
                <td data-label="Nombre"><?= htmlspecialchars($place['name'] ?? '') ?></td>
                <td data-label="Zona"><?= htmlspecialchars($place['zone_name'] ?? '') ?></td>
                <td data-label="Tipo"><?= htmlspecialchars($place['type'] ?? '') ?></td>
                <td data-label="Activa"><?= (int)($place['is_active'] ?? 0) === 1 ? 'Sí' : 'No' ?></td>
                <td data-label="Acciones"><a class="admin-row-action" href="/admin/catalog/places/edit?id=<?= (int) ($place['id'] ?? 0) ?>">Editar</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($totalPages > 1): ?>
    <div class="admin-pagination">
        <?php if ($currentPage > 1): ?>
            <?php $prevQuery = http_build_query(array_merge($baseQuery, ['page' => $currentPage - 1])); ?>
            <a href="/admin/catalog/places?<?= htmlspecialchars($prevQuery) ?>">← Anterior</a>
        <?php endif; ?>
        <span>Página <?= $currentPage ?> de <?= $totalPages ?> (<?= $totalPlaces ?> registros)</span>
        <?php if ($currentPage < $totalPages): ?>
            <?php $nextQuery = http_build_query(array_merge($baseQuery, ['page' => $currentPage + 1])); ?>
            <a href="/admin/catalog/places?<?= htmlspecialchars($nextQuery) ?>">Siguiente →</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
