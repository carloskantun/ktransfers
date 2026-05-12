<?php
declare(strict_types=1);

$form = $form ?? [];
$errors = $errors ?? [];
?>
<div class="page-header">
    <div>
        <h1>Editar zona</h1>
        <p class="admin-page-note">Ajusta nombres, orden y disponibilidad de la zona.</p>
    </div>
</div>

<div class="card admin-form-card">
    <form method="post" action="/admin/catalog/zones/edit?id=<?= (int) ($form['id'] ?? 0) ?>">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrf_token, ENT_QUOTES, 'UTF-8') ?>">

        <div class="admin-form-grid">
            <div class="form-group">
                <label>Codigo</label>
                <input type="text" name="code" value="<?= htmlspecialchars((string) ($form['code'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                <?php if (!empty($errors['code'])): ?>
                    <span class="field-note" style="color: var(--danger);"><?= htmlspecialchars((string) $errors['code'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Orden</label>
                <input type="number" name="sort_order" value="<?= htmlspecialchars((string) ($form['sort_order'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="form-group">
                <label>Nombre (ES)</label>
                <input type="text" name="name_es" value="<?= htmlspecialchars((string) ($form['name_es'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="form-group">
                <label>Nombre (EN)</label>
                <input type="text" name="name_en" value="<?= htmlspecialchars((string) ($form['name_en'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="form-group admin-form-full">
                <label class="admin-check">
                    <input type="checkbox" name="is_active" value="1" <?= (int) ($form['is_active'] ?? 0) === 1 ? 'checked' : '' ?>>
                    Zona activa
                </label>
            </div>
        </div>

        <div class="form-actions" style="margin-top:14px;">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a href="/admin/catalog/zones" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
