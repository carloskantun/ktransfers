<?php
declare(strict_types=1);

$error = $error ?? null;
?>
<h1>Admin Login</h1>

<?php if (is_string($error) && $error !== ''): ?>
    <div class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<form method="post" action="/admin/login">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string) $csrf_token, ENT_QUOTES, 'UTF-8') ?>">
    
    <label>Email</label>
    <input type="email" name="email" required>
    
    <label>Password</label>
    <input type="password" name="password" required>
    
    <button type="submit">Ingresar</button>
</form>
