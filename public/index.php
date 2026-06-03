<?php
require_once __DIR__ . '/../core/autoload.php';

use WeatherMaster\Controllers\AccountController;
use WeatherMaster\Controllers\AdminController;
use WeatherMaster\Controllers\ApiController;
use WeatherMaster\Controllers\ChatController;
use WeatherMaster\Controllers\WeatherController;
use WeatherMaster\Core\Router;

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
$router->get('/guestbook', WeatherController::class, 'getGuestbook');
$router->post('/guestbook', WeatherController::class, 'postGuestbook');
$router->get('/strategy', WeatherController::class, 'getStrategyDemo');
$router->get('/adapter', WeatherController::class, 'getAdapter');

$router->get('/auth/login', AccountController::class, 'getLogin');
$router->post('/auth/login', AccountController::class, 'processLogin');
$router->get('/auth/logout', AccountController::class, 'logout');

$router->get('/admin', AdminController::class, 'getIndex');
$router->post('/admin/city', AdminController::class, 'saveCity');
$router->post('/admin/alert', AdminController::class, 'saveAlert');
$router->get('/admin/export', AdminController::class, 'exportCsv');

$router->get('/chat', ChatController::class, 'getIndex');

$router->get('/api/alerts', ApiController::class, 'getAlert');

$router->dispatch();