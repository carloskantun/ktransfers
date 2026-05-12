<?php
// Vista: Crear Lugar
/** @var array $zones */
?>
<div class="page-header">
    <h1>Nuevo Lugar</h1>
</div>

<div class="card admin-form-card">
    <form method="post" action="/admin/catalog/places/create">
        <input type="hidden" name="_csrf" value="<?= \App\Core\Csrf::token() ?>">

        <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="name" required>
        </div>

        <div class="form-group">
            <label>Zona</label>
            <select name="zone_id" required>
                <option value="">-- Seleccionar Zona --</option>
                <?php foreach ($zones as $zone): ?>
                <option value="<?= htmlspecialchars((string)($zone['id'] ?? '')) ?>">
                    <?= htmlspecialchars($zone['name_es'] ?? '') ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Tipo de Lugar</label>
            <select name="type" required>
                <option value="">-- Seleccionar --</option>
                <option value="HOTEL">Hotel</option>
                <option value="AIRBNB">Airbnb</option>
                <option value="POINT">Punto</option>
            </select>
        </div>

        <div class="form-group">
            <label>Ciudad (opcional)</label>
            <input type="text" name="city">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Crear Lugar</button>
            <a href="/admin/catalog/places" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
