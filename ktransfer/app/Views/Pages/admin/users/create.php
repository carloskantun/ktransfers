<?php
$errors = $errors ?? [];
$form = $form ?? [];
$roles = $roles ?? [];
$providers = $providers ?? [];
$roleDescriptions = [
    'superadmin' => 'Superadmin: acceso total y control de configuracion global.',
    'agency' => 'Agencia/agente externo: crea reservas propias y no puede editar precios ni logistica.',
    'operator' => 'Operador/chofer: ve unicamente la operacion del dia asignada.',
    'sales' => 'Ventas/reservaciones: crea y administra reservas autorizadas.',
    'accounting' => 'Contabilidad: revisa saldos, KPIs y reportes financieros.',
    'admin' => 'Administrador: acceso completo al panel.',
];

$roleCodeById = [];
foreach ($roles as $role) {
    $roleCodeById[(string) ($role['id'] ?? '')] = (string) ($role['code'] ?? '');
}

$selectedRoleCode = $roleCodeById[(string) ($form['role_id'] ?? '')] ?? '';
$isAgencySelected = $selectedRoleCode === 'agency';
?>
<div class="page-header">
    <div>
        <h1>Nuevo usuario</h1>
        <p class="admin-page-note">Crea accesos para operadores, agencias/agentes externos y personal interno.</p>
    </div>
</div>

<div class="card admin-form-card">
    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="/admin/users/create">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) ($csrf_token ?? ''), ENT_QUOTES, 'UTF-8') ?>">

        <div class="form-group">
            <label>Nombre Completo</label>
            <input type="text" name="name" value="<?= htmlspecialchars((string) ($form['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars((string) ($form['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="form-group">
            <label>Contraseña</label>
            <input type="password" name="password" required>
        </div>

        <div class="form-group">
            <label>Rol principal</label>
            <select name="role_id" id="role_id" required>
                <option value="">Selecciona un rol</option>
                <?php foreach ($roles as $role): ?>
                    <?php $roleId = (string) ($role['id'] ?? ''); ?>
                    <option
                        value="<?= htmlspecialchars($roleId, ENT_QUOTES, 'UTF-8') ?>"
                        data-role-code="<?= htmlspecialchars((string) ($role['code'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                        <?= (($form['role_id'] ?? '') === $roleId) ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars((string) ($role['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <span class="field-note">Para agencias externas usa el rol “Agencia / agente externo”; para choferes usa “Operador / chofer”.</span>
        </div>

        <div id="agency_link_block" class="card" style="margin-bottom: 12px; background: #f8fafc; <?= $isAgencySelected ? '' : 'display:none;' ?>">
            <strong>Vinculo de agencia</strong>
            <p class="admin-page-note">El usuario con rol agencia siempre debe quedar ligado a una agencia/proveedor.</p>

            <div class="form-group">
                <label>Como vincular agencia</label>
                <select name="provider_mode" id="provider_mode">
                    <option value="existing" <?= (($form['provider_mode'] ?? 'existing') === 'existing') ? 'selected' : '' ?>>Seleccionar agencia existente</option>
                    <option value="new" <?= (($form['provider_mode'] ?? '') === 'new') ? 'selected' : '' ?>>Crear agencia nueva</option>
                </select>
            </div>

            <div id="provider_existing_group" style="<?= (($form['provider_mode'] ?? 'existing') === 'existing') ? '' : 'display:none;' ?>">
                <div class="form-group">
                    <label>Agencia existente</label>
                    <select name="provider_id" id="provider_id">
                        <option value="">Selecciona una agencia</option>
                        <?php foreach ($providers as $provider): ?>
                            <?php $providerId = (string) ($provider['id'] ?? ''); ?>
                            <option value="<?= htmlspecialchars($providerId, ENT_QUOTES, 'UTF-8') ?>" <?= (($form['provider_id'] ?? '') === $providerId) ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) ($provider['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div id="provider_new_group" style="<?= (($form['provider_mode'] ?? '') === 'new') ? '' : 'display:none;' ?>">
                <div class="form-group">
                    <label>Nombre de agencia</label>
                    <input type="text" name="provider_new_name" value="<?= htmlspecialchars((string) ($form['provider_new_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label>Contacto principal</label>
                    <input type="text" name="provider_new_contact_name" value="<?= htmlspecialchars((string) ($form['provider_new_contact_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label>Email de contacto</label>
                    <input type="email" name="provider_new_email" value="<?= htmlspecialchars((string) ($form['provider_new_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="form-group">
                    <label>Telefono de contacto</label>
                    <input type="text" name="provider_new_phone" value="<?= htmlspecialchars((string) ($form['provider_new_phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </div>
        </div>

        <div class="card" style="margin-bottom: 12px; background: #f8fafc;">
            <strong>Guia rapida de roles</strong>
            <?php foreach ($roles as $role): ?>
                <?php
                $code = (string) ($role['code'] ?? '');
                $description = $roleDescriptions[$code] ?? 'Rol personalizado.';
                ?>
                <p class="admin-page-note">
                    <strong><?= htmlspecialchars((string) ($role['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>:</strong>
                    <?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endforeach; ?>
        </div>

        <div class="form-group">
            <label class="admin-check">
                <input type="checkbox" name="is_active" value="1" <?= (($form['is_active'] ?? '1') === '1') ? 'checked' : '' ?>>
                Usuario Activo
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Crear Usuario</button>
            <a href="/admin/users" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<script>
(() => {
    const roleSelect = document.getElementById('role_id');
    const providerModeSelect = document.getElementById('provider_mode');
    const agencyBlock = document.getElementById('agency_link_block');
    const existingGroup = document.getElementById('provider_existing_group');
    const newGroup = document.getElementById('provider_new_group');

    const syncRole = () => {
        if (!roleSelect || !agencyBlock) return;
        const selected = roleSelect.options[roleSelect.selectedIndex];
        const roleCode = selected ? (selected.getAttribute('data-role-code') || '') : '';
        const isAgency = roleCode === 'agency';
        agencyBlock.style.display = isAgency ? '' : 'none';
    };

    const syncProviderMode = () => {
        if (!providerModeSelect || !existingGroup || !newGroup) return;
        const mode = providerModeSelect.value;
        existingGroup.style.display = mode === 'existing' ? '' : 'none';
        newGroup.style.display = mode === 'new' ? '' : 'none';
    };

    if (roleSelect) {
        roleSelect.addEventListener('change', syncRole);
        syncRole();
    }

    if (providerModeSelect) {
        providerModeSelect.addEventListener('change', syncProviderMode);
        syncProviderMode();
    }
})();
</script>
