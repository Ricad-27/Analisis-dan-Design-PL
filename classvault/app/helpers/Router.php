<?php
/**
 * ClassVault - Fast & Simple HTTP Router
 * Mendukung GET & POST, parameter URL dinamis (e.g. {id}), dan penanganan 404.
 */

class Router {
    private static array $routes = [];

    public static function get(string $path, array|callable $handler): void {
        self::addRoute('GET', $path, $handler);
    }

    public static function post(string $path, array|callable $handler): void {
        self::addRoute('POST', $path, $handler);
    }

    private static function addRoute(string $method, string $path, array|callable $handler): void {
        self::$routes[] = [
            'method'  => strtoupper($method),
            'path'    => '/' . trim($path, '/'),
            'handler' => $handler,
        ];
    }

    /**
     * Jalankan request dispatching
     */
    public static function dispatch(): void {
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';

        // Buang query string (?foo=bar)
        if (($pos = strpos($requestUri, '?')) !== false) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        // Hapus base path jika aplikasi berada di subdirektori (misal: /classvault/public)
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseDir = dirname($scriptName);
        $baseDir = str_replace('\\', '/', $baseDir);
        $baseDir = rtrim($baseDir, '/');

        if ($baseDir !== '' && str_starts_with($requestUri, $baseDir)) {
            $requestUri = substr($requestUri, strlen($baseDir));
        }

        $requestUri = '/' . trim($requestUri, '/');

        foreach (self::$routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            // Ubah pattern {param} menjadi regex
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $requestUri, $matches)) {
                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = urldecode($value);
                    }
                }

                self::executeHandler($route['handler'], $params);
                return;
            }
        }

        // Halaman 404 Tidak Ditemukan
        http_response_code(404);
        View::render('errors/404', [
            'page_title' => '404 - Halaman Tidak Ditemukan'
        ]);
    }

    private static function executeHandler(array|callable $handler, array $params): void {
        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
            return;
        }

        if (is_array($handler) && count($handler) === 2) {
            [$controllerName, $action] = $handler;
            
            $controllerFile = APP_PATH . '/controllers/' . $controllerName . '.php';
            if (file_exists($controllerFile)) {
                require_once $controllerFile;
            }

            if (class_exists($controllerName)) {
                $controller = new $controllerName();
                if (method_exists($controller, $action)) {
                    call_user_func_array([$controller, $action], $params);
                    return;
                }
            }

            http_response_code(500);
            echo "Kesalahan Kontroler: Metode '{$controllerName}::{$action}' tidak ditemukan.";
            return;
        }

        http_response_code(500);
        echo "Format handler rute tidak valid.";
    }
}
