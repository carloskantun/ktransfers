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
    <a href="/admin/catalog/places/create" class="btn btn-primary">Nuevo Lugar</a>
</div>

<div class="card">
    <form method="get" action="/admin/catalog/places" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;margin-bottom:12px;">
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
        <a href="/admin/catalog/places">Limpiar</a>
    </form>

    <table>
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
                <td colspan="6">No hay lugares registrados.</td>
            </tr>
            <?php endif; ?>
            <?php foreach ($places as $place): ?>
            <tr>
                <td><?= htmlspecialchars((string)($place['id'] ?? '')) ?></td>
                <td><?= htmlspecialchars($place['name'] ?? '') ?></td>
                <td><?= htmlspecialchars($place['zone_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($place['type'] ?? '') ?></td>
                <td><?= (int)($place['is_active'] ?? 0) === 1 ? 'Sí' : 'No' ?></td>
                <td><a href="/admin/catalog/places/edit?id=<?= (int) ($place['id'] ?? 0) ?>">Editar</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($totalPages > 1): ?>
    <div style="display:flex;gap:12px;align-items:center;justify-content:flex-end;margin-top:12px;">
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
