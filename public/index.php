<?php

// AIH Healthcare API - Secure Entry Point
define('API_START_TIME', microtime(true));

// Security headers first
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// CORS Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Load autoloader and environment
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, '"\'');
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// Load required classes
require_once __DIR__ . '/../app/Database.php';
require_once __DIR__ . '/../app/Router.php';
require_once __DIR__ . '/../app/Middleware/SecurityMiddleware.php';

// Load Controllers and Services
require_once __DIR__ . '/../app/Http/Controllers/HealthController.php';
require_once __DIR__ . '/../app/Http/Controllers/DoctorController.php';
require_once __DIR__ . '/../app/Http/Controllers/SpecialtyController.php';
require_once __DIR__ . '/../app/Http/Controllers/PostController.php';
require_once __DIR__ . '/../app/Http/Controllers/SingleServiceController.php';
require_once __DIR__ . '/../app/Http/Controllers/PackageController.php';

// Initialize Database
$db = Database::getInstance();

// Create Router
$router = new Router();

// Add security middleware
$router->addMiddleware('security', [SecurityMiddleware::class, 'securityHeaders']);
$router->addMiddleware('rate_limit', [SecurityMiddleware::class, 'rateLimiting']);
$router->addMiddleware('input_validation', [SecurityMiddleware::class, 'inputValidation']);

// Health and Info endpoints
$router->get('api/v1/health', function() {
    $controller = new HealthController();
    $controller->status();
}, ['security','rate_limit']);

// Doctors endpoints
$router->get('api/v1/doctors', function() {
    $page = (int)($_GET['page'] ?? 1);
    $page_size = (int)($_GET['page_size'] ?? 10);
    $offset = ($page - 1) * $page_size;
    $lang_code = $_GET['lang'] ?? 'vi';
    $filter = $_GET['filter'] ?? null;
    $controller = new DoctorController();
    $controller->Get($page, $page_size, $lang_code, $filter);
}, ['security', 'rate_limit', 'input_validation']);

$router->get('api/v1/doctors/{id}', function($id) {
    $controller = new DoctorController();
    $controller->GetDoctorById($id);
}, ['security', 'rate_limit', 'input_validation']);

// Specialty endpoints
$router->get('api/v1/specialties', function() {
    $lang_code = $_GET['lang'] ?? 'vi';
    $filter = $_GET['filter'] ?? null;
    $controller = new SpecialtyController();
    $controller->Get($lang_code, $filter);
}, ['security', 'rate_limit', 'input_validation']);

$router->get('api/v1/specialties/{id}', function($id) {
    $controller = new SpecialtyController();
    $controller->GetById($id);
}, ['security', 'rate_limit', 'input_validation']);

// Post endpoints
$router->get('api/v1/posts', function() {
    $page = (int)($_GET['page'] ?? 1);
    $page_size = (int)($_GET['page_size'] ?? 10);
    $lang_code = $_GET['lang'] ?? 'vi';
    $filter = $_GET['filter'] ?? null;
    $controller = new PostController();
    $controller->Get($page, $page_size, $lang_code, $filter);
}, ['security', 'rate_limit', 'input_validation']);

$router->get('api/v1/posts/{id}', function($id) {
    $controller = new PostController();
    $controller->GetById($id);
}, ['security', 'rate_limit', 'input_validation']);

$router->get('api/v1/posts/category/{category_id}', function($category_id) {
    $page = (int)($_GET['page'] ?? 1);
    $page_size = (int)($_GET['page_size'] ?? 10);
    $lang_code = $_GET['lang'] ?? 'vi';
    $controller = new PostController();
    $controller->GetListByCategory($category_id, $page, $page_size, $lang_code);
}, ['security', 'rate_limit', 'input_validation']);

// Single Service endpoints
$router->get('api/v1/singleservices', function() {
    $page = (int)($_GET['page'] ?? 1);
    $page_size = (int)($_GET['page_size'] ?? 10);
    $lang_code = $_GET['lang'] ?? 'vi';
    $filter = $_GET['filter'] ?? null;
    $controller = new SingleServiceController();
    $controller->Get($page, $page_size, $lang_code, $filter);
}, ['security', 'rate_limit', 'input_validation']);
 
$router->get('api/v1/singleservices/{id}', function($id) {
    $controller = new SingleServiceController();
    $controller->GetById($id);
}, ['security', 'rate_limit', 'input_validation']);

// Package endpoints
$router->get('api/v1/packages/specialty/{category_id}', function($category_id) {
    $lang_code = $_GET['lang'] ?? 'vi';
    $controller = new PackageController();
    $controller->GetPackageBySpecialty($category_id, $lang_code);
}, ['security', 'rate_limit', 'input_validation']);

$router->get('api/v1/packages/services/{package_id}', function($package_id) {
    $lang_code = $_GET['lang'] ?? 'vi';
    $controller = new PackageController();
    $controller->GetListPackageServicesByPackageId($package_id, $lang_code);
}, ['security', 'rate_limit', 'input_validation']);

$router->get('api/v1/packages/services/items/{package_service_id}', function($package_service_id) {
    $controller = new PackageController();
    $controller->GetListServiceItemsByPackageServiceId($package_service_id);
}, ['security', 'rate_limit', 'input_validation']);


// Dispatch the request
$method = $_SERVER['REQUEST_METHOD'];
$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

try {
    $router->dispatch($method, $path);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error',
        'error' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Something went wrong'
    ], JSON_UNESCAPED_UNICODE);
}
