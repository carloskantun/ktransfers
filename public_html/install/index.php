<?php
declare(strict_types=1);

$installerPath = dirname(__DIR__, 2) . '/ktransfer/install/index.php';

if (!is_file($installerPath)) {
    http_response_code(500);
    echo 'Installer not found.';
    exit;
}

require $installerPath;
