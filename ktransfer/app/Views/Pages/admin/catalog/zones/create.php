<?php
declare(strict_types=1);

$form = $form ?? [];
$errors = $errors ?? [];
?>
<h2>Create Zone</h2>
<p><a href="/admin/catalog/zones">← Back</a></p>

<form method="post" action="/admin/catalog/zones/create">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrf_token, ENT_QUOTES, 'UTF-8') ?>">
    
    <label>Code</label>
    <input type="text" name="code" value="<?= htmlspecialchars((string) ($form['code'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
    <?php if (!empty($errors['code'])): ?>
        <p style="color: red;"><?= htmlspecialchars((string) $errors['code'], ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    
    <label>Name (ES)</label>
    <input type="text" name="name_es" value="<?= htmlspecialchars((string) ($form['name_es'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
    
    <label>Name (EN)</label>
    <input type="text" name="name_en" value="<?= htmlspecialchars((string) ($form['name_en'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
    
    <label>Sort Order</label>
    <input type="number" name="sort_order" value="<?= htmlspecialchars((string) ($form['sort_order'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>">
    
    <button type="submit" class="btn">Create</button>
</form>
