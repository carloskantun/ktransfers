<?php
declare(strict_types=1);

/** @var array $providers */
$providers = $providers ?? [];
$filters = isset($filters) && is_array($filters) ? $filters : [];
$search = (string) ($filters['q'] ?? '');
$active = (string) ($filters['active'] ?? '');
?>
<div class="page-header">
    <div>
        <h1>Proveedores</h1>
        <p class="admin-page-note">Empresas externas a las que se puede asignar un servicio en la orden del dia.</p>
    </div>
    <a href="/admin/catalog/providers/create" class="btn btn-primary">Nuevo proveedor</a>
</div>

<div class="card">
    <form method="get" action="/admin/catalog/providers" class="admin-filter-bar">
        <div>
            <label for="q">Buscar</label>
            <input id="q" name="q" type="text" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Nombre, contacto, email o telefono">
        </div>
        <div>
            <label for="active">Estado</label>
            <select id="active" name="active">
                <option value="">Todos</option>
                <option value="1" <?= $active === '1' ? 'selected' : '' ?>>Activos</option>
                <option value="0" <?= $active === '0' ? 'selected' : '' ?>>Inactivos</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Filtrar</button>
        <a href="/admin/catalog/providers" class="admin-row-action">Limpiar</a>
    </form>

    <table class="admin-card-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Proveedor</th>
                <th>Contacto</th>
                <th>Servicios</th>
                <th>Activo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($providers as $provider): ?>
                <tr>
                    <td data-label="ID"><?= htmlspecialchars((string) ($provider['id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td data-label="Proveedor">
                        <strong><?= htmlspecialchars((string) ($provider['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                    </td>
                    <td data-label="Contacto">
                        <strong><?= htmlspecialchars((string) ($provider['contact_name'] ?? 'Sin contacto'), ENT_QUOTES, 'UTF-8') ?></strong><br>
                        <?= htmlspecialchars((string) ($provider['email'] ?? 'Sin email'), ENT_QUOTES, 'UTF-8') ?><br>
                        <span class="admin-page-note"><?= htmlspecialchars((string) ($provider['phone'] ?? 'Sin telefono'), ENT_QUOTES, 'UTF-8') ?></span>
                    </td>
                    <td data-label="Servicios"><?= (int) ($provider['assigned_services'] ?? 0) ?></td>
                    <td data-label="Activo"><?= (int) ($provider['is_active'] ?? 0) === 1 ? 'Si' : 'No' ?></td>
                    <td data-label="Acciones">
                        <a class="admin-row-action" href="/admin/catalog/providers/edit?id=<?= (int) ($provider['id'] ?? 0) ?>">Editar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($providers)): ?>
                <tr>
                    <td colspan="6">Aun no hay proveedores registrados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
