<?php
declare(strict_types=1);

$projectRoot = dirname(__DIR__);
$appRootCandidates = [
	$projectRoot . '/ktransfer',
	$projectRoot,
];

$appRoot = null;
foreach ($appRootCandidates as $candidate) {
	if (is_file($candidate . '/app/Core/App.php')) {
		$appRoot = $candidate;
		break;
	}
}

if ($appRoot === null) {
	http_response_code(500);
	echo 'Application root not found.';
	exit;
}

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$requestPath = is_string($requestPath) ? rtrim($requestPath, '/') : '';

if ($requestPath === '/install' || $requestPath === '/install/index.php') {
	$installerFile = $appRoot . '/install/index.php';

	if (!is_file($installerFile)) {
		http_response_code(500);
		echo 'Installer not found.';
		exit;
	}

	require $installerFile;
	exit;
}

spl_autoload_register(static function (string $class) use ($appRoot): void {
	$prefix = 'App\\';
	if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
		return;
	}

	$relativeClass = substr($class, strlen($prefix));
	$file = $appRoot . '/app/' . str_replace('\\', '/', $relativeClass) . '.php';

	if (is_file($file)) {
		require_once $file;
	}
});

$app = new App\Core\App();
$app->run();
