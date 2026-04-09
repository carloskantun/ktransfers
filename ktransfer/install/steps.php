<?php
declare(strict_types=1);

// Pasos del instalador

$dbHost = trim($_POST['db_host'] ?? 'localhost');
$dbName = trim($_POST['db_name'] ?? '');
$dbUser = trim($_POST['db_user'] ?? '');
$dbPass = $_POST['db_pass'] ?? '';
$baseUrl = trim($_POST['base_url'] ?? '');
$adminName = trim($_POST['admin_name'] ?? '');
$adminEmail = trim($_POST['admin_email'] ?? '');
$adminPassword = $_POST['admin_password'] ?? '';

if ($dbName === '' || $dbUser === '' || $adminName === '' || $adminEmail === '' || $adminPassword === '') {
    die('Error: Todos los campos son obligatorios.');
}

try {
    // Conectar a DB
    $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Ejecutar schema.sql
    $schemaFile = __DIR__ . '/../database/schema.sql';
    if (!is_file($schemaFile)) {
        die('Error: schema.sql no encontrado.');
    }

    $sql = file_get_contents($schemaFile);
    if ($sql === false) {
        die('Error: No se pudo leer schema.sql.');
    }

    $pdo->exec($sql);

    // Ejecutar migraciones posteriores al baseline consolidado en schema.sql
    // (schema.sql ya incluye 001..007)
    $baselineFilename = '007_airlines.sql';

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            filename VARCHAR(190) NOT NULL UNIQUE,
            executed_at DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $migrationsDir = __DIR__ . '/../database/migrations';
    $migrationFiles = glob($migrationsDir . '/*.sql');
    if ($migrationFiles !== false) {
        sort($migrationFiles);

        $executedStmt = $pdo->query('SELECT filename FROM migrations');
        $executed = array_column($executedStmt->fetchAll(PDO::FETCH_ASSOC), 'filename');

        foreach ($migrationFiles as $migrationFile) {
            $filename = basename($migrationFile);

            if (strcmp($filename, $baselineFilename) <= 0) {
                continue;
            }

            if (in_array($filename, $executed, true)) {
                continue;
            }

            $migrationSql = file_get_contents($migrationFile);
            if ($migrationSql === false) {
                throw new RuntimeException('No se pudo leer migración: ' . $filename);
            }

            $pdo->exec($migrationSql);
            $pdo->prepare('INSERT INTO migrations (filename, executed_at) VALUES (:filename, NOW())')
                ->execute(['filename' => $filename]);
        }
    }

    // Crear usuario admin
    $passwordHash = password_hash($adminPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare(
        'INSERT INTO users (name, email, password_hash, is_active, created_at) VALUES (:name, :email, :password_hash, 1, NOW())'
    );
    $stmt->execute([
        'name' => $adminName,
        'email' => $adminEmail,
        'password_hash' => $passwordHash,
    ]);

    $userId = (int) $pdo->lastInsertId();

    // Crear rol ADMIN si no existe
    $pdo->exec(
        "INSERT IGNORE INTO roles (code, name, created_at) VALUES ('ADMIN', 'Administrador', NOW())"
    );
    $roleStmt = $pdo->query("SELECT id FROM roles WHERE code = 'ADMIN' LIMIT 1");
    $roleId = (int) ($roleStmt->fetch()['id'] ?? 0);

    if ($roleId > 0) {
        $pdo->prepare('INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)')
            ->execute(['user_id' => $userId, 'role_id' => $roleId]);
    }

    // Crear config.php
    $configContent = "<?php\nreturn [\n";
    $configContent .= "    'db_host' => '" . addslashes($dbHost) . "',\n";
    $configContent .= "    'db_name' => '" . addslashes($dbName) . "',\n";
    $configContent .= "    'db_user' => '" . addslashes($dbUser) . "',\n";
    $configContent .= "    'db_pass' => '" . addslashes($dbPass) . "',\n";
    $configContent .= "    'db_port' => 3306,\n";
    $configContent .= "    'db_charset' => 'utf8mb4',\n";
    $configContent .= "    'base_url' => '" . addslashes($baseUrl) . "',\n";
    $configContent .= "];\n";

    $configFile = __DIR__ . '/../config/config.php';
    if (file_put_contents($configFile, $configContent) === false) {
        die('Error: No se pudo crear config.php.');
    }

    // Crear lock.php
    $lockContent = "<?php\n// Instalación completada el " . date('Y-m-d H:i:s') . "\n";
    file_put_contents(__DIR__ . '/lock.php', $lockContent);

    echo "<!doctype html><html lang='es'><head><meta charset='utf-8'><meta name='viewport' content='width=device-width, initial-scale=1'><title>Instalación Exitosa</title>";
    echo "<style>*{box-sizing:border-box}body{margin:0;font-family:system-ui,sans-serif;background:#f1f5f9;color:#0f172a;padding:40px 20px}.card{max-width:720px;margin:0 auto;background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:32px;box-shadow:0 10px 25px rgba(2,6,23,.08)}h1{margin:0 0 12px}.ok{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;padding:12px;border-radius:8px;margin:12px 0 18px}a.btn{display:inline-block;padding:10px 14px;border-radius:8px;background:#2563eb;color:#fff;text-decoration:none;font-weight:600}</style></head><body><main class='card'>";
    echo "<h1>✅ Instalación completada</h1>";
    echo "<div class='ok'>Usuario admin creado: <strong>" . htmlspecialchars($adminEmail, ENT_QUOTES, 'UTF-8') . "</strong></div>";
    echo "<p><a class='btn' href='/admin/login'>Ir al login admin</a></p>";
    echo "</main></body></html>";
} catch (Exception $e) {
    die('Error durante instalación: ' . $e->getMessage());
}
