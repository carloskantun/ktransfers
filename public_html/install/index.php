<?php
declare(strict_types=1);

$projectRoot = dirname(__DIR__, 2);
$appRootCandidates = [
    $projectRoot . '/ktransfer',
    $projectRoot,
];

$installerPath = null;
foreach ($appRootCandidates as $candidate) {
    $candidateInstaller = $candidate . '/install/index.php';
    if (is_file($candidateInstaller)) {
        $installerPath = $candidateInstaller;
        break;
    }
}

if ($installerPath === null) {
    http_response_code(500);
    echo 'Installer not found.';
    exit;
}

require $installerPath;
