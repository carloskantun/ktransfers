<?php
// Vista: Crear Vehículo
?>
<div class="page-header">
    <h1>Nuevo Vehículo</h1>
</div>

<div class="card admin-form-card">
    <form method="post" action="/admin/catalog/vehicles/create">
        <input type="hidden" name="_csrf" value="<?= \App\Core\Csrf::token() ?>">

        <div class="form-group">
            <label>Código</label>
            <input type="text" name="code" required>
        </div>

        <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="name" required>
        </div>

        <div class="form-group">
            <label>Máximo de Pasajeros</label>
            <input type="number" name="max_pax" min="1" required>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Crear Vehículo</button>
            <a href="/admin/catalog/vehicles" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
