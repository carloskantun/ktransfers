<?php
declare(strict_types=1);

// Runner de migraciones SQL
// Ejecuta migraciones en orden desde database/migrations/

require_once __DIR__ . '/../app/Core/DB.php';

try {
    $configFile = __DIR__ . '/../config/config.php';
    if (!is_file($configFile)) {
        die("Error: config.php no existe. Ejecuta el instalador primero.\n");
    }

    $config = require $configFile;
    if (!is_array($config)) {
        die("Error: config.php inválido.\n");
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $config['db_host'] ?? 'localhost',
        $config['db_port'] ?? 3306,
        $config['db_name'] ?? '',
        $config['db_charset'] ?? 'utf8mb4'
    );

    $pdo = new PDO($dsn, $config['db_user'] ?? '', $config['db_pass'] ?? '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    echo "Conectado a la base de datos.\n";

    // Crear tabla migrations si no existe
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            filename VARCHAR(190) NOT NULL UNIQUE,
            executed_at DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // Obtener migraciones ya ejecutadas
    $stmt = $pdo->query('SELECT filename FROM migrations');
    $executed = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'filename');

    // Si la DB fue creada desde schema.sql, el baseline 001..007 ya existe físicamente
    // aunque la tabla migrations esté vacía. Marcamos baseline para evitar errores por
    // objetos/índices duplicados al ejecutar migrate.php.
    if (empty($executed)) {
        $baselineMaxFilename = '007_airlines.sql';
        $migrationsDir = __DIR__ . '/../database/migrations';
        $baselineFiles = glob($migrationsDir . '/*.sql');
        if ($baselineFiles !== false) {
            sort($baselineFiles);
            foreach ($baselineFiles as $baselineFile) {
                $baselineName = basename($baselineFile);
                if (strcmp($baselineName, $baselineMaxFilename) <= 0) {
                    $pdo->prepare('INSERT IGNORE INTO migrations (filename, executed_at) VALUES (:filename, NOW())')
                        ->execute(['filename' => $baselineName]);
                }
            }
        }

        $stmt = $pdo->query('SELECT filename FROM migrations');
        $executed = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'filename');
    }

    // Leer archivos de migraciones
    $migrationsDir = __DIR__ . '/../database/migrations';
    $files = glob($migrationsDir . '/*.sql');
    sort($files);

    $newMigrations = 0;

    foreach ($files as $file) {
        $filename = basename($file);

        if (in_array($filename, $executed, true)) {
            echo "⏭️  Omitiendo: {$filename} (ya ejecutada)\n";
            continue;
        }

        echo "⚡ Ejecutando: {$filename}\n";
        $sql = file_get_contents($file);
        if ($sql === false) {
            echo "❌ Error leyendo: {$filename}\n";
            continue;
        }

        $pdo->exec($sql);
        $pdo->prepare('INSERT INTO migrations (filename, executed_at) VALUES (:filename, NOW())')
            ->execute(['filename' => $filename]);

        echo "✅ Completada: {$filename}\n";
        $newMigrations++;
    }

    echo "\n✨ Proceso finalizado. {$newMigrations} migración(es) nueva(s) ejecutada(s).\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
