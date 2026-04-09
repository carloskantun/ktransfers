<?php
// Vista: Rate Rules
/** @var array $rate_groups */
/** @var array $currencies */
$rateGroups = $rate_groups ?? [];
$currencies = $currencies ?? [];
?>
<div class="page-header">
    <h1>Rate Rules</h1>
</div>

<div class="card">
    <p style="margin-bottom:12px; color:#475569; font-size:0.92rem;">
        Vista agrupada por <strong>Zona + Servicio + Rango de PAX</strong>. Edita todas las monedas activas en una sola pantalla.
    </p>
    <table>
        <thead>
            <tr>
                <th>Zona</th>
                <th>Servicio</th>
                <th>Grupo PAX</th>
                <?php foreach ($currencies as $currency): ?>
                <th><?= htmlspecialchars((string) ($currency['code'] ?? ''), ENT_QUOTES, 'UTF-8') ?> (OW / RT)</th>
                <?php endforeach; ?>
                <th>Activa</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($rateGroups)): ?>
            <tr>
                <td colspan="<?= 5 + count($currencies) ?>" style="text-align:center; color:#64748b;">No hay rate rules disponibles.</td>
            </tr>
            <?php else: ?>
                <?php foreach ($rateGroups as $group): ?>
                <?php
                    $formatPair = static function (?array $rate): string {
                        if ($rate === null) {
                            return '—';
                        }

                        $ow = number_format((float) ($rate['one_way_price'] ?? 0), 2);
                        $rt = number_format((float) ($rate['round_trip_price'] ?? 0), 2);
                        return $ow . ' / ' . $rt;
                    };
                ?>
                <tr>
                    <td><?= htmlspecialchars((string) ($group['zone_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) ($group['service_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) ($group['pax_label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                    <?php foreach ($currencies as $currency): ?>
                    <?php
                        $currencyCode = strtoupper((string) ($currency['code'] ?? ''));
                        $currencyRate = $group['currencies'][$currencyCode] ?? null;
                    ?>
                    <td><?= htmlspecialchars($formatPair($currencyRate), ENT_QUOTES, 'UTF-8') ?></td>
                    <?php endforeach; ?>
                    <td><?= !empty($group['all_active']) ? 'Sí' : (!empty($group['has_any_rate']) ? 'Parcial' : 'No') ?></td>
                    <td>
                        <a href="/admin/pricing/rate-rules/edit-group?zone_id=<?= (int) ($group['zone_id'] ?? 0) ?>&service_type_id=<?= (int) ($group['service_type_id'] ?? 0) ?>&pax_range_id=<?= (int) ($group['pax_range_id'] ?? 0) ?>">
                            Configurar grupo
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
