<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;


// Handle CORS before Laravel boots (cPanel/shared hosting fix)
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = ['https://ecr-ts.com', 'https://www.ecr-ts.com'];

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Max-Age: 86400");

    // Reflect back whatever headers the browser/proxy requests
    $requestedHeaders = $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'] ?? 'Content-Type, Authorization, X-Requested-With, X-XSRF-TOKEN, Accept';
    header("Access-Control-Allow-Headers: $requestedHeaders");
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
