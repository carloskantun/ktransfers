<?php
// Vista: Contabilidad
/** @var array $payments_received */
/** @var array $provider_balances */
?>
<div class="page-header">
    <h1>Contabilidad</h1>
</div>

<div class="card">
    <h2>Pagos Recibidos</h2>
    <table>
        <thead>
            <tr>
                <th>Moneda</th>
                <th>Total Recibido</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($payments_received as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['currency_code'] ?? '') ?></td>
                <td><?= htmlspecialchars(number_format((float)($row['total'] ?? 0), 2)) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="card" style="margin-top: 20px;">
    <h2>Saldos de Proveedores</h2>
    <table>
        <thead>
            <tr>
                <th>Proveedor ID</th>
                <th>Por Pagar</th>
                <th>Pagado</th>
                <th>Saldo</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($provider_balances as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['provider_id'] ?? '') ?></td>
                <td><?= htmlspecialchars(number_format((float)($row['total_payable'] ?? 0), 2)) ?></td>
                <td><?= htmlspecialchars(number_format((float)($row['total_paid'] ?? 0), 2)) ?></td>
                <td><?= htmlspecialchars(number_format((float)($row['balance'] ?? 0), 2)) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
