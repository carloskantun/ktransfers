<?php
declare(strict_types=1);

// Instalador web de KTransfers
// Crea config.php, ejecuta schema.sql y crea usuario admin inicial

$lockFile = __DIR__ . '/lock.php';
$configFile = __DIR__ . '/../config/config.php';

$hasCompleteConfig = false;
if (is_file($configFile)) {
    $config = require $configFile;
    if (is_array($config)) {
        $dbHost = trim((string) ($config['db_host'] ?? ''));
        $dbName = trim((string) ($config['db_name'] ?? ''));
        $dbUser = trim((string) ($config['db_user'] ?? ''));
        $hasCompleteConfig = ($dbHost !== '' && $dbName !== '' && $dbUser !== '');
    }
}

if (is_file($lockFile) && $hasCompleteConfig) {
    die('Ya se ejecutó el instalador. Elimina ktransfer/install/lock.php para reinstalar.');
}

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require __DIR__ . '/steps.php';
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Instalador KTransfers</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; background: #f1f5f9; padding: 40px 20px; color: #0f172a; }
        .container { max-width: 720px; margin: 0 auto; background: #fff; padding: 32px; border-radius: 10px; box-shadow: 0 10px 25px rgba(2,6,23,0.08); border: 1px solid #e2e8f0; }
        h1 { margin-bottom: 8px; }
        .subtitle { color: #475569; margin-bottom: 24px; }
        h3 { margin: 22px 0 10px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; }
        input { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #cbd5e1; border-radius: 6px; }
        hr { margin: 8px 0 0; border: 0; border-top: 1px solid #e2e8f0; }
        button { width: 100%; padding: 12px; background: #2563eb; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: 600; margin-top: 10px; }
        button:hover { background: #1d4ed8; }
        .error { background: #fef2f2; color: #991b1b; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Instalador KTransfers</h1>
        <p class="subtitle">Configura tu base de datos y crea el usuario administrador inicial.</p>

        <form method="post">
            <h3>Configuración de Base de Datos</h3>
            <label>DB Host</label>
            <input type="text" name="db_host" value="localhost" required>

            <label>DB Name</label>
            <input type="text" name="db_name" required>

            <label>DB User</label>
            <input type="text" name="db_user" required>

            <label>DB Password</label>
            <input type="password" name="db_pass">

            <label>Base URL (ej: https://tudominio.com)</label>
            <input type="url" name="base_url" value="http://localhost" required>

            <hr>

            <h3>Usuario Administrador</h3>
            <label>Nombre</label>
            <input type="text" name="admin_name" required>

            <label>Email</label>
            <input type="email" name="admin_email" required>

            <label>Contraseña</label>
            <input type="password" name="admin_password" required>

            <button type="submit">Instalar</button>
        </form>
    </div>
</body>
</html>
