<?php
declare(strict_types=1);

$zones = $zones ?? [];
?>
<h2>Zones</h2>
<p><a href="/admin/catalog/zones/create" class="btn">+ Create Zone</a></p>

<?php if (empty($zones)): ?>
    <p>No zones found.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Name (ES)</th>
                <th>Name (EN)</th>
                <th>Active</th>
                <th>Sort Order</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($zones as $zone): ?>
                <tr>
                    <td><?= htmlspecialchars((string) $zone['code'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) $zone['name_es'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars((string) $zone['name_en'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= ((int) $zone['is_active']) === 1 ? 'Yes' : 'No' ?></td>
                    <td><?= (int) $zone['sort_order'] ?></td>
                    <td><a href="/admin/catalog/zones/edit?id=<?= (int) $zone['id'] ?>">Edit</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
