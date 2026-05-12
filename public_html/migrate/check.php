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

function index_exists(PDO $pdo, string $table, string $index): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) AS total
         FROM information_schema.statistics
         WHERE table_schema = DATABASE()
           AND table_name = :table_name
           AND index_name = :index_name'
    );
    $stmt->execute([
        'table_name' => $table,
        'index_name' => $index,
    ]);
    $row = $stmt->fetch();

    return is_array($row) && (int) ($row['total'] ?? 0) > 0;
}

function foreign_key_exists(PDO $pdo, string $table, string $constraint): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) AS total
         FROM information_schema.table_constraints
         WHERE constraint_schema = DATABASE()
           AND table_name = :table_name
           AND constraint_name = :constraint_name
           AND constraint_type = "FOREIGN KEY"'
    );
    $stmt->execute([
        'table_name' => $table,
        'constraint_name' => $constraint,
    ]);
    $row = $stmt->fetch();

    return is_array($row) && (int) ($row['total'] ?? 0) > 0;
}

function migration_registered(PDO $pdo, string $filename): bool
{
    if (!table_exists($pdo, 'migrations')) {
        return false;
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM migrations WHERE filename = :filename');
    $stmt->execute(['filename' => $filename]);
    $row = $stmt->fetch();

    return is_array($row) && (int) ($row['total'] ?? 0) > 0;
}

function row_count(PDO $pdo, string $sql, array $params = []): int
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();

    return is_array($row) ? (int) ($row['total'] ?? 0) : 0;
}

function check_item(string $label, bool $ok, string $detail = ''): array
{
    return [
        'label' => $label,
        'ok' => $ok,
        'detail' => $detail,
    ];
}

$error = '';
$checks = [];
$databaseName = '';
$isAuthenticated = (bool) ($_SESSION['migration_admin'] ?? false);

try {
    if ($isAuthenticated) {
        $config = load_config($configFile);
        $pdo = pdo_from_config($config);
        $databaseName = (string) $pdo->query('SELECT DATABASE()')->fetchColumn();

        $checks[] = check_item('Conexion a base de datos', true, 'Base actual: ' . $databaseName);
        $checks[] = check_item('Archivo 018 existe en servidor', is_file($migrationsDir . '/018_agency_role_booking_ownership.sql'));
        $checks[] = check_item('Tabla migrations existe', table_exists($pdo, 'migrations'));
        $checks[] = check_item('018 registrada como ejecutada', migration_registered($pdo, '018_agency_role_booking_ownership.sql'));
        $checks[] = check_item('Columna bookings.created_by_user_id', column_exists($pdo, 'bookings', 'created_by_user_id'));
        $checks[] = check_item('Indice idx_bookings_created_by_user', index_exists($pdo, 'bookings', 'idx_bookings_created_by_user'));
        $checks[] = check_item('Foreign key fk_bookings_created_by_user', foreign_key_exists($pdo, 'bookings', 'fk_bookings_created_by_user'));
        $checks[] = check_item('Permiso bookings.create', row_count($pdo, 'SELECT COUNT(*) AS total FROM permissions WHERE code = :code', ['code' => 'bookings.create']) > 0);
        $checks[] = check_item('Rol agency', row_count($pdo, 'SELECT COUNT(*) AS total FROM roles WHERE code = :code', ['code' => 'agency']) > 0);
        $checks[] = check_item('Rol operator', row_count($pdo, 'SELECT COUNT(*) AS total FROM roles WHERE code = :code', ['code' => 'operator']) > 0);
        $checks[] = check_item(
            'Permisos base asignados a agency',
            row_count(
                $pdo,
                'SELECT COUNT(*) AS total
                 FROM role_permissions rp
                 INNER JOIN roles r ON r.id = rp.role_id
                 INNER JOIN permissions p ON p.id = rp.permission_id
                 WHERE r.code = "agency"
                   AND p.code IN ("dashboard.view", "bookings.view", "bookings.create")'
            ) >= 3,
            'Debe tener dashboard.view, bookings.view y bookings.create.'
        );
        $checks[] = check_item(
            'Permiso bookings.create para admin/sales',
            row_count(
                $pdo,
                'SELECT COUNT(DISTINCT r.code) AS total
                 FROM role_permissions rp
                 INNER JOIN roles r ON r.id = rp.role_id
                 INNER JOIN permissions p ON p.id = rp.permission_id
                 WHERE r.code IN ("admin", "sales")
                   AND p.code = "bookings.create"'
            ) >= 2,
            'Admin y ventas deben conservar la creacion manual.'
        );
        $checks[] = check_item('Tabla assignments existe', table_exists($pdo, 'assignments'));
        $checks[] = check_item('Campo assignments.operator_id', column_exists($pdo, 'assignments', 'operator_id'));
        $checks[] = check_item('Campo assignments.provider_id', column_exists($pdo, 'assignments', 'provider_id'));
        $checks[] = check_item('Campo assignments.vehicle_id', column_exists($pdo, 'assignments', 'vehicle_id'));
    }
} catch (Throwable $e) {
    $error = $e->getMessage();
}

$failed = array_filter($checks, static fn (array $check): bool => !$check['ok']);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Comprobacion de base de datos</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, sans-serif; background: #eef4fb; color: #0f172a; padding: 28px 14px; }
        .wrap { max-width: 900px; margin: 0 auto; }
        .card { background: #fff; border: 1px solid #dbe3ef; border-radius: 16px; padding: 22px; box-shadow: 0 14px 32px rgba(15,23,42,.08); }
        h1 { margin: 0 0 8px; font-size: 1.6rem; }
        p { color: #64748b; line-height: 1.5; }
        a.button { display: inline-flex; align-items: center; justify-content: center; min-height: 42px; padding: 10px 14px; border-radius: 10px; background: #e2e8f0; color: #1f2937; font-weight: 800; text-decoration: none; }
        .top-actions { display: flex; justify-content: flex-end; margin-bottom: 12px; }
        .error { margin: 14px 0; padding: 12px; border-radius: 10px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
        .notice { margin: 14px 0; padding: 12px; border-radius: 10px; background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
        .warn { margin: 14px 0; padding: 12px; border-radius: 10px; background: #fff7ed; border: 1px solid #fed7aa; color: #9a3412; }
        .checks { display: grid; gap: 10px; margin-top: 16px; }
        .check { display: grid; grid-template-columns: 42px 1fr; gap: 12px; align-items: start; padding: 12px; border: 1px solid #e7edf5; border-radius: 12px; background: #f8fafc; }
        .badge { width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center; border-radius: 999px; font-weight: 900; }
        .badge.ok { background: #dcfce7; color: #166534; }
        .badge.fail { background: #fee2e2; color: #991b1b; }
        .label { font-weight: 800; }
        .detail { margin-top: 3px; color: #64748b; font-size: .94rem; }
        @media (max-width: 640px) {
            .card { padding: 16px; }
            .top-actions { display: block; }
            a.button { width: 100%; }
        }
    </style>
</head>
<body>
    <main class="wrap">
        <section class="card">
            <div class="top-actions">
                <a class="button" href="index.php">Volver a migraciones</a>
            </div>

            <h1>Comprobacion de base de datos</h1>
            <p>Este diagnostico solo lee la base de datos. No crea tablas, no ejecuta migraciones y no modifica informacion.</p>

            <?php if (!$isAuthenticated): ?>
                <div class="warn">Primero entra como admin en el runner de migraciones.</div>
            <?php elseif ($error !== ''): ?>
                <div class="error"><?= h($error) ?></div>
            <?php else: ?>
                <?php if (empty($failed)): ?>
                    <div class="notice">Todo lo esperado para roles, agencias y operacion esta presente.</div>
                <?php else: ?>
                    <div class="warn">Hay <?= count($failed) ?> comprobacion(es) que necesitan revision.</div>
                <?php endif; ?>

                <div class="checks">
                    <?php foreach ($checks as $check): ?>
                        <div class="check">
                            <span class="badge <?= $check['ok'] ? 'ok' : 'fail' ?>"><?= $check['ok'] ? 'OK' : '!' ?></span>
                            <div>
                                <div class="label"><?= h((string) $check['label']) ?></div>
                                <?php if ((string) $check['detail'] !== ''): ?>
                                    <div class="detail"><?= h((string) $check['detail']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
