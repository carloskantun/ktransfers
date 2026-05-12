<?php
// Vista: Aerolíneas
/** @var array $airlines */
$airlines = $airlines ?? [];
$filters = is_array($filters ?? null) ? $filters : [];
$pagination = is_array($pagination ?? null) ? $pagination : [];
$search = (string) ($filters['q'] ?? '');
$active = (string) ($filters['active'] ?? '');
$currentPage = (int) ($pagination['page'] ?? 1);
$totalPages = (int) ($pagination['total_pages'] ?? 1);
$total = (int) ($pagination['total'] ?? count($airlines));
$baseQuery = [];
if ($search !== '') {
    $baseQuery['q'] = $search;
}
if ($active !== '') {
    $baseQuery['active'] = $active;
}
?>
<div class="page-header">
    <h1>Aerolíneas</h1>
    <div class="form-actions">
        <a href="/admin/catalog/airlines/export?<?= htmlspecialchars(http_build_query($baseQuery), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-secondary">Descargar CSV</a>
        <a href="/admin/catalog/airlines/create" class="btn btn-primary">Nueva Aerolínea</a>
    </div>
</div>

<div class="card">
    <form method="get" action="/admin/catalog/airlines" class="admin-filter-bar">
        <div>
            <label for="q">Buscar</label>
            <input id="q" name="q" type="text" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Codigo o nombre">
        </div>
        <div>
            <label for="active">Estado</label>
            <select id="active" name="active">
                <option value="">Todas</option>
                <option value="1" <?= $active === '1' ? 'selected' : '' ?>>Activas</option>
                <option value="0" <?= $active === '0' ? 'selected' : '' ?>>Inactivas</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Filtrar</button>
        <a href="/admin/catalog/airlines" class="admin-row-action">Limpiar</a>
    </form>

    <table class="admin-card-table">
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
                <td data-label="ID"><?= htmlspecialchars((string)($airline['id'] ?? '')) ?></td>
                <td data-label="Codigo IATA"><strong><?= htmlspecialchars($airline['code'] ?? '') ?></strong></td>
                <td data-label="Nombre"><?= htmlspecialchars($airline['name'] ?? '') ?></td>
                <td data-label="Activa"><?= (int)($airline['is_active'] ?? 0) === 1 ? 'Si' : 'No' ?></td>
                <td data-label="Creada"><?= htmlspecialchars($airline['created_at'] ?? '') ?></td>
                <td data-label="Acciones"><a class="admin-row-action" href="/admin/catalog/airlines/edit?id=<?= (int) ($airline['id'] ?? 0) ?>">Editar</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if ($totalPages > 1): ?>
        <div class="admin-pagination">
            <?php if ($currentPage > 1): ?>
                <a href="/admin/catalog/airlines?<?= htmlspecialchars(http_build_query(array_merge($baseQuery, ['page' => $currentPage - 1])), ENT_QUOTES, 'UTF-8') ?>">Anterior</a>
            <?php endif; ?>
            <span>Pagina <?= $currentPage ?> de <?= $totalPages ?> (<?= $total ?> registros)</span>
            <?php if ($currentPage < $totalPages): ?>
                <a href="/admin/catalog/airlines?<?= htmlspecialchars(http_build_query(array_merge($baseQuery, ['page' => $currentPage + 1])), ENT_QUOTES, 'UTF-8') ?>">Siguiente</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
