<?php
declare(strict_types=1);

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$requestPath = is_string($requestPath) ? rtrim($requestPath, '/') : '';

if ($requestPath === '/install' || $requestPath === '/install/index.php') {
	$installerFile = dirname(__DIR__) . '/ktransfer/install/index.php';

	if (!is_file($installerFile)) {
		http_response_code(500);
		echo 'Installer not found.';
		exit;
	}

	require $installerFile;
	exit;
}

spl_autoload_register(static function (string $class): void {
	$prefix = 'App\\';
	if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
		return;
	}

	$relativeClass = substr($class, strlen($prefix));
	$file = dirname(__DIR__) . '/ktransfer/app/' . str_replace('\\', '/', $relativeClass) . '.php';

	if (is_file($file)) {
		require_once $file;
	}
});

$app = new App\Core\App();
$app->run();
