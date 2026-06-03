<?php
require_once __DIR__ . '/../core/autoload.php';

use WeatherMaster\Core\Router;
use WeatherMaster\Controllers\WeatherController;
use WeatherMaster\Controllers\AccountController;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$router = Router::getInstance();

$router->get('/', WeatherController::class, 'getIndex');
$router->get('/weekly', WeatherController::class, 'getWeeklyForecast');
$router->post('/', WeatherController::class, 'saveCity');

$router->get('/auth/login', AccountController::class, 'getLogin');
$router->post('/auth/login', AccountController::class, 'processLogin');
$router->get('/auth/logout', AccountController::class, 'logout');

// $router->get('/admin', AdminController::class, 'dashboard');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$router->dispatch($uri, $method);