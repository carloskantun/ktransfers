<?php
declare(strict_types=1);
namespace App\Core;

class Router {
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function get(string $path, string $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, string $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function dispatch(Request $request): Response
    {
        $method = strtoupper($request->method());
        $path = $this->normalizePath($request->path());

        $handler = $this->routes[$method][$path] ?? null;
        if ($handler === null) {
            return new Response('Not Found', 404);
        }

        return $this->invokeHandler($handler, $request);
    }

    private function add(string $method, string $path, string $handler): void
    {
        $this->routes[$method][$this->normalizePath($path)] = $handler;
    }

    private function normalizePath(string $path): string
    {
        $normalized = '/' . trim($path, '/');
        return $normalized === '//' ? '/' : $normalized;
    }

    private function invokeHandler(string $handler, Request $request): Response
    {
        [$class, $method] = explode('@', $handler, 2);

        if (!class_exists($class)) {
            return new Response('Controller Not Found', 500);
        }

        $instance = new $class();

        if (!method_exists($instance, $method)) {
            return new Response('Action Not Found', 500);
        }

        $result = $instance->{$method}($request);

        if ($result instanceof Response) {
            return $result;
        }

        return new Response((string) $result, 200);
    }
}
