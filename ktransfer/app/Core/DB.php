<?php
declare(strict_types=1);
namespace App\Core;

use PDO;
use RuntimeException;

class DB {
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $configFile = dirname(__DIR__, 2) . '/config/config.php';
        if (!is_file($configFile)) {
            throw new RuntimeException('Missing config file.');
        }

        $config = require $configFile;
        if (!is_array($config)) {
            throw new RuntimeException('Invalid config format.');
        }

        $host = (string)($config['db_host'] ?? '');
        $name = (string)($config['db_name'] ?? '');
        $user = (string)($config['db_user'] ?? '');
        $pass = (string)($config['db_pass'] ?? '');
        $charset = (string)($config['db_charset'] ?? 'utf8mb4');
        $port = (int)($config['db_port'] ?? 3306);

        if ($host === '' || $name === '' || $user === '') {
            throw new RuntimeException('Database config is incomplete.');
        }

        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $name, $charset);

        self::$pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return self::$pdo;
    }
}
