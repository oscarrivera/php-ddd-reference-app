<?php

declare(strict_types=1);

if (PHP_SAPI === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $file = __DIR__ . $path;
    if ($path !== '/' && is_file($file)) {
        return false;
    }
}

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';
if (!is_file($autoload)) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo '{"error":"Run composer install first.","code":"NOT_INSTALLED"}';
    exit(1);
}

require $autoload;

use Warehouse\Shared\Infrastructure\AppFactory;
use Warehouse\Shared\Infrastructure\Http\Kernel;

$app = AppFactory::sqlite($root . '/var/app.sqlite');
(new Kernel($app))->run();
