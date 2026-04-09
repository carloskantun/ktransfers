<?php
/** @var array $currencies */
$currencies = $currencies ?? [];
?>
<div class="page-header">
    <h1>Monedas</h1>
    <a href="/admin/catalog/currencies/create" class="btn btn-primary">Nueva Moneda</a>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Símbolo</th>
                <th>Activa</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($currencies)): ?>
            <tr>
                <td colspan="5">No hay monedas registradas.</td>
            </tr>
            <?php endif; ?>
            <?php foreach ($currencies as $currency): ?>
            <tr>
                <td><?= htmlspecialchars((string) ($currency['code'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($currency['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($currency['symbol'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= (int) ($currency['is_active'] ?? 0) === 1 ? 'Sí' : 'No' ?></td>
                <td>
                    <a href="/admin/catalog/currencies/edit?code=<?= htmlspecialchars((string) ($currency['code'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">Editar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
