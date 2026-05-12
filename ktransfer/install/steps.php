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
$superAdminName = trim($_POST['superadmin_name'] ?? '');
$superAdminEmail = trim($_POST['superadmin_email'] ?? '');
$superAdminPassword = $_POST['superadmin_password'] ?? '';

if (
    $dbName === '' ||
    $dbUser === '' ||
    $adminName === '' ||
    $adminEmail === '' ||
    $adminPassword === '' ||
    $superAdminName === '' ||
    $superAdminEmail === '' ||
    $superAdminPassword === ''
) {
    die('Error: Todos los campos son obligatorios.');
}

$derivePrefixFromBaseUrl = static function (string $inputUrl): string {
    $host = parse_url($inputUrl, PHP_URL_HOST);
    $host = is_string($host) ? strtolower(trim($host)) : '';
    $host = preg_replace('/^www\./', '', $host) ?? $host;

    if ($host === '') {
        return 'KTR';
    }

    $tokens = preg_split('/[^a-z0-9]+/', $host) ?: [];
    $letters = '';
    foreach ($tokens as $token) {
        if ($token === '' || ctype_digit($token)) {
            continue;
        }
        $letters .= strtoupper($token[0]);
        if (strlen($letters) >= 3) {
            break;
        }
    }

    if (strlen($letters) < 3) {
        $flattened = preg_replace('/[^a-z]/', '', $host) ?? '';
        if (strlen($flattened) >= 3) {
            $letters = strtoupper(substr($flattened, 0, 3));
        }
    }

    if (preg_match('/^[A-Z]{3}$/', $letters) !== 1) {
        return 'KTR';
    }

    return $letters;
};

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

    $roleStmt = $pdo->query("SELECT id, code FROM roles WHERE code IN ('admin', 'superadmin')");
    $roleRows = $roleStmt->fetchAll(PDO::FETCH_ASSOC);
    $roleIds = [];
    foreach ($roleRows as $row) {
        $code = (string) ($row['code'] ?? '');
        $roleIds[$code] = (int) ($row['id'] ?? 0);
    }

    if (($roleIds['admin'] ?? 0) <= 0) {
        throw new RuntimeException('No existe el rol admin en la base de datos.');
    }
    if (($roleIds['superadmin'] ?? 0) <= 0) {
        throw new RuntimeException('No existe el rol superadmin en la base de datos. Ejecuta migraciones pendientes.');
    }

    $insertUserStmt = $pdo->prepare(
        'INSERT INTO users (name, email, password_hash, is_active, created_at) VALUES (:name, :email, :password_hash, 1, NOW())'
    );
    $insertUserRoleStmt = $pdo->prepare('INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)');

    $insertUserStmt->execute([
        'name' => $adminName,
        'email' => $adminEmail,
        'password_hash' => password_hash($adminPassword, PASSWORD_DEFAULT),
    ]);
    $adminUserId = (int) $pdo->lastInsertId();
    $insertUserRoleStmt->execute(['user_id' => $adminUserId, 'role_id' => (int) $roleIds['admin']]);

    $insertUserStmt->execute([
        'name' => $superAdminName,
        'email' => $superAdminEmail,
        'password_hash' => password_hash($superAdminPassword, PASSWORD_DEFAULT),
    ]);
    $superAdminUserId = (int) $pdo->lastInsertId();
    $insertUserRoleStmt->execute(['user_id' => $superAdminUserId, 'role_id' => (int) $roleIds['superadmin']]);

    $prefix = $derivePrefixFromBaseUrl($baseUrl);
    $homeContentStmt = $pdo->prepare('SELECT content_json FROM site_content WHERE content_key = :content_key LIMIT 1');
    $homeContentStmt->execute(['content_key' => 'home_page']);
    $homeContentRow = $homeContentStmt->fetch(PDO::FETCH_ASSOC);
    $homeContent = [];
    if (is_array($homeContentRow) && isset($homeContentRow['content_json'])) {
        $decoded = json_decode((string) $homeContentRow['content_json'], true);
        if (is_array($decoded)) {
            $homeContent = $decoded;
        }
    }
    $homeContent['booking_code_prefix'] = $prefix;

    $upsertHomeContentStmt = $pdo->prepare(
        'INSERT INTO site_content (content_key, content_json, updated_by, updated_at)
         VALUES (:content_key, :content_json, :updated_by, NOW())
         ON DUPLICATE KEY UPDATE
            content_json = VALUES(content_json),
            updated_by = VALUES(updated_by),
            updated_at = NOW()'
    );
    $upsertHomeContentStmt->execute([
        'content_key' => 'home_page',
        'content_json' => json_encode($homeContent, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}',
        'updated_by' => $superAdminUserId,
    ]);

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
    echo "<div class='ok'>Usuario admin creado: <strong>" . htmlspecialchars($adminEmail, ENT_QUOTES, 'UTF-8') . "</strong><br>";
    echo "Usuario superadmin creado: <strong>" . htmlspecialchars($superAdminEmail, ENT_QUOTES, 'UTF-8') . "</strong><br>";
    echo "Prefijo de reserva sugerido: <strong>" . htmlspecialchars($prefix, ENT_QUOTES, 'UTF-8') . "</strong></div>";
    echo "<p><a class='btn' href='/admin/login'>Ir al login admin</a></p>";
    echo "</main></body></html>";
} catch (Exception $e) {
    die('Error durante instalación: ' . $e->getMessage());
}
