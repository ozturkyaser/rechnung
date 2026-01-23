<?php
namespace Libs;

/**
 * Router Klasse
 * Verwaltet Routen und dispatched Requests
 */
class Router {
    private $routes = [];
    private $namedRoutes = [];
    private $currentRoute = null;
    private $middlewares = [];

    /**
     * Füge GET Route hinzu
     */
    public function get($path, $handler, $name = null) {
        return $this->addRoute('GET', $path, $handler, $name);
    }

    /**
     * Füge POST Route hinzu
     */
    public function post($path, $handler, $name = null) {
        return $this->addRoute('POST', $path, $handler, $name);
    }

    /**
     * Füge PUT Route hinzu
     */
    public function put($path, $handler, $name = null) {
        return $this->addRoute('PUT', $path, $handler, $name);
    }

    /**
     * Füge DELETE Route hinzu
     */
    public function delete($path, $handler, $name = null) {
        return $this->addRoute('DELETE', $path, $handler, $name);
    }

    /**
     * Füge Route hinzu
     */
    private function addRoute($method, $path, $handler, $name = null) {
        $route = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'pattern' => $this->convertToPattern($path),
            'middlewares' => []
        ];

        $this->routes[] = $route;
        $index = count($this->routes) - 1;

        if ($name) {
            $this->namedRoutes[$name] = $path;
        }

        return $this;
    }

    /**
     * Konvertiere Path zu Regex Pattern
     */
    private function convertToPattern($path) {
        // Ersetze {param} mit regex
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    /**
     * Füge Middleware hinzu
     */
    public function middleware($middleware) {
        if (count($this->routes) > 0) {
            $index = count($this->routes) - 1;
            $this->routes[$index]['middlewares'][] = $middleware;
        }
        return $this;
    }

    /**
     * Registriere globale Middleware
     */
    public function registerMiddleware($name, $callback) {
        $this->middlewares[$name] = $callback;
    }

    /**
     * Dispatch Request
     */
    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Entferne trailing slash (außer für root)
        if ($uri !== '/' && substr($uri, -1) === '/') {
            $uri = rtrim($uri, '/');
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $uri, $matches)) {
                array_shift($matches); // Entferne full match

                $this->currentRoute = $route;

                // Führe Middlewares aus
                foreach ($route['middlewares'] as $middleware) {
                    if (isset($this->middlewares[$middleware])) {
                        call_user_func($this->middlewares[$middleware]);
                    }
                }

                // Führe Handler aus
                return $this->executeHandler($route['handler'], $matches);
            }
        }

        // 404 Not Found
        $this->handle404();
    }

    /**
     * Führe Handler aus
     */
    private function executeHandler($handler, $params = []) {
        if (is_callable($handler)) {
            // Closure
            return call_user_func_array($handler, $params);
        }

        if (is_string($handler) && strpos($handler, '@') !== false) {
            // Controller@method
            list($controller, $method) = explode('@', $handler);

            $controllerClass = "App\\Controllers\\{$controller}";

            if (!class_exists($controllerClass)) {
                throw new \Exception("Controller not found: {$controllerClass}");
            }

            $controllerInstance = new $controllerClass();

            if (!method_exists($controllerInstance, $method)) {
                throw new \Exception("Method not found: {$method}");
            }

            return call_user_func_array([$controllerInstance, $method], $params);
        }

        throw new \Exception("Invalid handler");
    }

    /**
     * 404 Handler
     */
    private function handle404() {
        http_response_code(404);

        if (isAjax()) {
            jsonResponse(['error' => 'Not Found'], 404);
        }

        if (file_exists(APP_PATH . '/views/errors/404.php')) {
            require APP_PATH . '/views/errors/404.php';
        } else {
            echo '<h1>404 - Page Not Found</h1>';
        }
        exit;
    }

    /**
     * Hole URL für Named Route
     */
    public function route($name, $params = []) {
        if (!isset($this->namedRoutes[$name])) {
            throw new \Exception("Route not found: {$name}");
        }

        $path = $this->namedRoutes[$name];

        foreach ($params as $key => $value) {
            $path = str_replace('{' . $key . '}', $value, $path);
        }

        return url($path);
    }
}
