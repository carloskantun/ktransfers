<?php
// Vista: Tipos de Servicio
/** @var array $services */
$services = $services ?? [];
$notice = $notice ?? '';
$errorMessage = $error_message ?? '';
$csrfToken = (string) ($csrf_token ?? '');
?>
<div class="page-header">
    <h1>Tipos de Servicio</h1>
    <a href="/admin/catalog/services/create" class="btn btn-primary">Nuevo Tipo</a>
</div>

<?php if ($notice !== ''): ?>
<div class="notice"><?= htmlspecialchars((string) $notice, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if ($errorMessage !== ''): ?>
<div class="error"><?= htmlspecialchars((string) $errorMessage, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

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
            <?php if (empty($services)): ?>
            <tr>
                <td class="admin-empty-row" colspan="7">No hay tipos de servicio registrados.</td>
            </tr>
            <?php endif; ?>
            <?php foreach ($services as $service): ?>
            <tr>
                <td data-label="ID"><?= htmlspecialchars((string)($service['id'] ?? '')) ?></td>
                <td data-label="Codigo"><?= htmlspecialchars($service['code'] ?? '') ?></td>
                <td data-label="Nombre (ES)"><?= htmlspecialchars($service['name_es'] ?? '') ?></td>
                <td data-label="Nombre (EN)"><?= htmlspecialchars($service['name_en'] ?? '') ?></td>
                <td data-label="Orden"><?= htmlspecialchars((string)($service['sort_order'] ?? 0)) ?></td>
                <td data-label="Activa"><?= (int)($service['is_active'] ?? 0) === 1 ? 'Sí' : 'No' ?></td>
                <td data-label="Acciones">
                    <a class="admin-row-action" href="/admin/catalog/services/edit?id=<?= (int) ($service['id'] ?? 0) ?>">Editar</a>
                    <form method="post" action="/admin/catalog/services/delete" style="display:inline; margin-left:8px;" onsubmit="return confirm('¿Eliminar este tipo de servicio? Esta accion no se puede deshacer.')">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="id" value="<?= (int) ($service['id'] ?? 0) ?>">
                        <button type="submit" class="admin-row-action" style="background:none; border:0; padding:0; cursor:pointer;">Eliminar</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
