<?php
class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, callable $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        // HEAD-запросы обрабатываем как GET
        if ($method === 'HEAD') {
            $method = 'GET';
        }

        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        // Ищем точное совпадение
        if (isset($this->routes[$method][$uri])) {
            $this->routes[$method][$uri]();
            return;
        }

        // Ищем с параметрами
        foreach (($this->routes[$method] ?? []) as $pattern => $handler) {
            $regex = preg_replace('/\{(\w+)\}/', '(\w+)', $pattern);
            $regex = '#^' . $regex . '$#';
            if (preg_match($regex, $uri, $matches)) {
                array_shift($matches);
                $handler(...$matches);
                return;
            }
        }

        http_response_code(404);
        require __DIR__ . '/../templates/error.php';
    }
}
