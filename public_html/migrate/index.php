<?php
declare(strict_types=1);

$projectRoot = dirname(__DIR__, 2);
$appRoot = $projectRoot . '/ktransfer';
$configFile = $appRoot . '/config/config.php';
$migrationsDir = $appRoot . '/database/migrations';

session_start();

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function load_config(string $configFile): array
{
    if (!is_file($configFile)) {
        throw new RuntimeException('No existe config.php. Ejecuta primero el instalador.');
    }

    $config = require $configFile;
    if (!is_array($config)) {
        throw new RuntimeException('config.php no es valido.');
    }

    return $config;
}

function pdo_from_config(array $config): PDO
{
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $config['db_host'] ?? 'localhost',
        (int) ($config['db_port'] ?? 3306),
        $config['db_name'] ?? '',
        $config['db_charset'] ?? 'utf8mb4'
    );

    return new PDO($dsn, (string) ($config['db_user'] ?? ''), (string) ($config['db_pass'] ?? ''), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

function ensure_migrations_table(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            filename VARCHAR(190) NOT NULL UNIQUE,
            executed_at DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}

function executed_migrations(PDO $pdo): array
{
    ensure_migrations_table($pdo);
    $stmt = $pdo->query('SELECT filename FROM migrations');
    return array_column($stmt->fetchAll(), 'filename');
}

function migration_files(string $migrationsDir): array
{
    $files = glob($migrationsDir . '/*.sql');
    if ($files === false) {
        return [];
    }

    sort($files);
    return $files;
}

function table_exists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) AS total
         FROM information_schema.tables
         WHERE table_schema = DATABASE()
           AND table_name = :table_name'
    );
    $stmt->execute(['table_name' => $table]);
    $row = $stmt->fetch();

    return is_array($row) && (int) ($row['total'] ?? 0) > 0;
}

function column_exists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) AS total
         FROM information_schema.columns
         WHERE table_schema = DATABASE()
           AND table_name = :table_name
           AND column_name = :column_name'
    );
    $stmt->execute([
        'table_name' => $table,
        'column_name' => $column,
    ]);
    $row = $stmt->fetch();

    return is_array($row) && (int) ($row['total'] ?? 0) > 0;
}

function permission_exists(PDO $pdo, string $permissionCode): bool
{
    if (!table_exists($pdo, 'permissions')) {
        return false;
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM permissions WHERE code = :code');
    $stmt->execute(['code' => $permissionCode]);
    $row = $stmt->fetch();

    return is_array($row) && (int) ($row['total'] ?? 0) > 0;
}

function role_exists(PDO $pdo, string $roleCode): bool
{
    if (!table_exists($pdo, 'roles')) {
        return false;
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM roles WHERE code = :code');
    $stmt->execute(['code' => $roleCode]);
    $row = $stmt->fetch();

    return is_array($row) && (int) ($row['total'] ?? 0) > 0;
}

function mark_migration_executed(PDO $pdo, string $filename): void
{
    $stmt = $pdo->prepare('INSERT IGNORE INTO migrations (filename, executed_at) VALUES (:filename, NOW())');
    $stmt->execute(['filename' => $filename]);
}

function repair_migration_history(PDO $pdo): array
{
    ensure_migrations_table($pdo);

    $repairChecks = [
        '001_init.sql' => static fn (PDO $pdo): bool => table_exists($pdo, 'users') && table_exists($pdo, 'audit_log'),
        '002_rbac.sql' => static fn (PDO $pdo): bool => table_exists($pdo, 'roles') && table_exists($pdo, 'permissions') && table_exists($pdo, 'role_permissions'),
        '003_catalog.sql' => static fn (PDO $pdo): bool => table_exists($pdo, 'service_types') && table_exists($pdo, 'zones') && table_exists($pdo, 'places'),
        '004_pricing.sql' => static fn (PDO $pdo): bool => table_exists($pdo, 'pax_ranges') && table_exists($pdo, 'rate_rules'),
        '005_bookings.sql' => static fn (PDO $pdo): bool => table_exists($pdo, 'bookings') && table_exists($pdo, 'booking_passengers') && table_exists($pdo, 'booking_payments'),
        '006_operations_accounting.sql' => static fn (PDO $pdo): bool => table_exists($pdo, 'assignments') && table_exists($pdo, 'work_orders') && table_exists($pdo, 'providers'),
        '007_airlines.sql' => static fn (PDO $pdo): bool => table_exists($pdo, 'airlines'),
        '015_roles_content_operations.sql' => static fn (PDO $pdo): bool => table_exists($pdo, 'site_content') && permission_exists($pdo, 'content.manage') && role_exists($pdo, 'operator'),
        '016_booking_operation_sheet_fields.sql' => static fn (PDO $pdo): bool => column_exists($pdo, 'bookings', 'agency_name') && column_exists($pdo, 'bookings', 'terminal') && column_exists($pdo, 'bookings', 'origin_name') && column_exists($pdo, 'bookings', 'destination_name'),
        '017_booking_operation_type.sql' => static fn (PDO $pdo): bool => column_exists($pdo, 'bookings', 'operation_type'),
        '018_agency_role_booking_ownership.sql' => static fn (PDO $pdo): bool => column_exists($pdo, 'bookings', 'created_by_user_id') && permission_exists($pdo, 'bookings.create') && role_exists($pdo, 'agency'),
    ];

    $executed = executed_migrations($pdo);
    $repaired = [];

    foreach ($repairChecks as $filename => $isApplied) {
        if (in_array($filename, $executed, true)) {
            continue;
        }

        if ($isApplied($pdo)) {
            mark_migration_executed($pdo, $filename);
            $repaired[] = $filename;
        }
    }

    return $repaired;
}

function is_admin_login(PDO $pdo, string $email, string $password): bool
{
    $stmt = $pdo->prepare(
        'SELECT id, password_hash
         FROM users
         WHERE email = :email
           AND is_active = 1
         LIMIT 1'
    );
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!is_array($user) || !password_verify($password, (string) ($user['password_hash'] ?? ''))) {
        return false;
    }

    $roleStmt = $pdo->prepare(
        'SELECT COUNT(*) AS total
         FROM user_roles ur
         INNER JOIN roles r ON r.id = ur.role_id
         WHERE ur.user_id = :user_id
           AND r.code = "admin"'
    );
    $roleStmt->execute(['user_id' => (int) $user['id']]);
    $roleRow = $roleStmt->fetch();

    return is_array($roleRow) && (int) ($roleRow['total'] ?? 0) > 0;
}

function run_pending_migrations(PDO $pdo, string $migrationsDir): array
{
    $repaired = repair_migration_history($pdo);

    $executed = executed_migrations($pdo);
    $log = [];

    foreach ($repaired as $filename) {
        $log[] = ['type' => 'skip', 'message' => $filename . ' ya existia en la base y fue marcado como ejecutado.'];
    }

    foreach (migration_files($migrationsDir) as $file) {
        $filename = basename($file);
        if (in_array($filename, $executed, true)) {
            $log[] = ['type' => 'skip', 'message' => $filename . ' ya estaba ejecutada.'];
            continue;
        }

        $sql = file_get_contents($file);
        if ($sql === false) {
            throw new RuntimeException('No se pudo leer ' . $filename);
        }

        try {
            $pdo->exec($sql);
            $stmt = $pdo->prepare('INSERT INTO migrations (filename, executed_at) VALUES (:filename, NOW())');
            $stmt->execute(['filename' => $filename]);
            $log[] = ['type' => 'ok', 'message' => $filename . ' ejecutada correctamente.'];
        } catch (Throwable $e) {
            throw new RuntimeException('Error en ' . $filename . ': ' . $e->getMessage(), 0, $e);
        }
    }

    return $log;
}

$error = '';
$log = [];
$pending = [];
$isAuthenticated = (bool) ($_SESSION['migration_admin'] ?? false);

try {
    $config = load_config($configFile);
    $pdo = pdo_from_config($config);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
        unset($_SESSION['migration_admin']);
        $isAuthenticated = false;
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['password'])) {
        $email = trim((string) $_POST['email']);
        $password = (string) $_POST['password'];
        if (is_admin_login($pdo, $email, $password)) {
            $_SESSION['migration_admin'] = true;
            $isAuthenticated = true;
        } else {
            $error = 'Credenciales invalidas o usuario sin rol admin.';
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAuthenticated && isset($_POST['run_migrations'])) {
        $log = run_pending_migrations($pdo, $migrationsDir);
    }

    if ($isAuthenticated) {
        $log = array_merge($log, array_map(
            static fn (string $filename): array => [
                'type' => 'skip',
                'message' => $filename . ' ya existia en la base y fue marcado como ejecutado.',
            ],
            repair_migration_history($pdo)
        ));

        $executed = executed_migrations($pdo);
        foreach (migration_files($migrationsDir) as $file) {
            $filename = basename($file);
            if (!in_array($filename, $executed, true)) {
                $pending[] = $filename;
            }
        }
    }
} catch (Throwable $e) {
    $error = $e->getMessage();
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Migraciones KTransfers</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, sans-serif; background: #eef4fb; color: #0f172a; padding: 28px 14px; }
        .wrap { max-width: 820px; margin: 0 auto; }
        .card { background: #fff; border: 1px solid #dbe3ef; border-radius: 16px; padding: 22px; box-shadow: 0 14px 32px rgba(15,23,42,.08); }
        h1 { margin: 0 0 8px; font-size: 1.6rem; }
        p { color: #64748b; line-height: 1.5; }
        label { display: block; margin: 14px 0 6px; font-weight: 700; }
        input { width: 100%; border: 1px solid #cbd5e1; border-radius: 10px; padding: 11px 12px; font-size: 1rem; }
        button { width: 100%; margin-top: 16px; border: 0; border-radius: 10px; padding: 12px 14px; background: #2463eb; color: #fff; font-weight: 800; cursor: pointer; }
        button.secondary { background: #e2e8f0; color: #1f2937; }
        a.button-link { display: inline-flex; align-items: center; justify-content: center; min-height: 42px; padding: 10px 14px; border-radius: 10px; background: #e2e8f0; color: #1f2937; font-weight: 800; text-decoration: none; }
        .error { margin: 14px 0; padding: 12px; border-radius: 10px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
        .notice { margin: 14px 0; padding: 12px; border-radius: 10px; background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
        .list { display: grid; gap: 8px; margin: 14px 0; padding: 0; list-style: none; }
        .list li { padding: 10px 12px; border: 1px solid #e7edf5; border-radius: 10px; background: #f8fafc; }
        .ok { color: #166534; }
        .skip { color: #64748b; }
        .top-actions { display: flex; gap: 10px; justify-content: flex-end; }
        .top-actions form { min-width: 160px; }
        @media (max-width: 640px) {
            .card { padding: 16px; }
            .top-actions { display: block; }
            a.button-link { width: 100%; }
        }
    </style>
</head>
<body>
    <main class="wrap">
        <section class="card">
            <div class="top-actions">
                <?php if ($isAuthenticated): ?>
                    <a class="button-link" href="check.php">Comprobar base</a>
                    <form method="post">
                        <button class="secondary" type="submit" name="logout" value="1">Cerrar sesion</button>
                    </form>
                <?php endif; ?>
            </div>

            <h1>Migraciones KTransfers</h1>
            <p>Ejecuta solo las migraciones pendientes y registra cada archivo en la tabla <strong>migrations</strong>.</p>

            <?php if ($error !== ''): ?>
                <div class="error"><?= h($error) ?></div>
            <?php endif; ?>

            <?php if (!$isAuthenticated): ?>
                <form method="post">
                    <label>Email admin</label>
                    <input type="email" name="email" required>

                    <label>Contrasena</label>
                    <input type="password" name="password" required>

                    <button type="submit">Entrar</button>
                </form>
            <?php else: ?>
                <?php if (empty($pending)): ?>
                    <div class="notice">No hay migraciones pendientes.</div>
                <?php else: ?>
                    <h2>Pendientes</h2>
                    <ul class="list">
                        <?php foreach ($pending as $filename): ?>
                            <li><?= h($filename) ?></li>
                        <?php endforeach; ?>
                    </ul>

                    <form method="post">
                        <button type="submit" name="run_migrations" value="1">Ejecutar migraciones pendientes</button>
                    </form>
                <?php endif; ?>

                <?php if (!empty($log)): ?>
                    <h2>Resultado</h2>
                    <ul class="list">
                        <?php foreach ($log as $row): ?>
                            <li class="<?= h((string) $row['type']) ?>"><?= h((string) $row['message']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
