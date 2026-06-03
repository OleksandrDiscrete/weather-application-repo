<?php
require_once __DIR__ . '/../core/autoload.php';

use WeatherMaster\Core\Router;
use WeatherMaster\Controllers\WeatherController;
use WeatherMaster\Controllers\AdminController;

$router = Router::getInstance();

$router->get('/', WeatherController::class, 'index');
$router->get('/admin', AdminController::class, 'dashboard');
// $router->post('/admin/users/add', AdminController::class, 'addUser');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$router->dispatch($uri, $method);