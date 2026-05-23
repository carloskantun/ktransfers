<?php
/** @var array $ranges */
$ranges = $ranges ?? [];
$notice = $notice ?? '';
$errorMessage = $error_message ?? '';
$csrfToken = (string) ($csrf_token ?? '');
?>
<div class="page-header">
    <h1>Rangos de Pasajeros (PAX)</h1>
    <a href="/admin/pricing/pax-ranges/create" class="btn btn-primary">Nuevo Rango</a>
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
                <td data-label="Acciones">
                    <a class="admin-row-action" href="/admin/pricing/pax-ranges/edit?id=<?= (int) ($range['id'] ?? 0) ?>">Editar</a>
                    <form method="post" action="/admin/pricing/pax-ranges/delete" style="display:inline; margin-left:8px;" onsubmit="return confirm('¿Eliminar este rango de pasajeros? Esta accion no se puede deshacer.')">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="id" value="<?= (int) ($range['id'] ?? 0) ?>">
                        <button type="submit" class="admin-row-action" style="background:none; border:0; padding:0; cursor:pointer;">Eliminar</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
