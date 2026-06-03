<?php
namespace WeatherMaster\Core;

class Router
{
    private static ?Router $instance = null;

    private array $routes = [
        'GET' => [],
        'POST' => []
    ];

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * Get the Singleton instance of the Router.
     */
    public static function getInstance(): Router
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register a GET route.
     */
    public function get(string $path, string $controller, string $action): void
    {
        $this->routes['GET'][$path] = [
            'controller' => $controller,
            'action' => $action
        ];
    }

    /**
     * Register a POST route.
     */
    public function post(string $path, string $controller, string $action): void
    {
        $this->routes['POST'][$path] = [
            'controller' => $controller,
            'action' => $action
        ];
    }

    /**
     * Match the incoming URL and execute the corresponding controller.
     */
    public function dispatch(string $uri, string $method): void
    {
        $path = parse_url($uri, PHP_URL_PATH);
        $path = str_replace('/weather-application-repo/public', '', $path);

        // Default to '/' if the path becomes empty after replacement
        if ($path === '') {
            $path = '/';
        }

        // Check if the route exists for this HTTP method
        if (isset($this->routes[$method][$path])) {
            $route = $this->routes[$method][$path];
            $controllerName = $route['controller'];
            $actionName = $route['action'];

            // Instantiate the Controller and call the specific Action method
            if (class_exists($controllerName)) {
                $controllerInstance = new $controllerName();
                if (method_exists($controllerInstance, $actionName)) {
                    $controllerInstance->$actionName();
                    return;
                }
            }
        }
        $this->sendNotFound();
    }
    private function sendNotFound(): void
    {
        http_response_code(404);
        echo "<h1>404 - Сторінку не знайдено</h1>";
        echo "<p>The requested URL was not found on this server.</p>";
        exit();
    }
}