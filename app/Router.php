<?php

class Router {
    private $routes = [];
    private $middleware = [];
    
    public function addRoute($method, $path, $handler, $middleware = []) {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }
    
    public function get($path, $handler, $middleware = []) {
        $this->addRoute('GET', $path, $handler, $middleware);
    }
    
    public function post($path, $handler, $middleware = []) {
        $this->addRoute('POST', $path, $handler, $middleware);
    }
    
    public function put($path, $handler, $middleware = []) {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }
    
    public function delete($path, $handler, $middleware = []) {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }
    
    public function addMiddleware($name, $callable) {
        $this->middleware[$name] = $callable;
    }
    
    public function dispatch($method, $path) {
        $method = strtoupper($method);
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchPath($route['path'], $path)) {
                // Extract parameters
                $params = $this->extractParams($route['path'], $path);
                
                // Run middleware
                foreach ($route['middleware'] as $middlewareName) {
                    if (isset($this->middleware[$middlewareName])) {
                        $result = call_user_func($this->middleware[$middlewareName]);
                        if ($result === false) {
                            return; // Middleware blocked the request
                        }
                    }
                }
                
                // Call handler
                if (is_array($route['handler'])) {
                    list($class, $method) = $route['handler'];
                    $controller = new $class();
                    return call_user_func_array([$controller, $method], $params);
                } else {
                    return call_user_func_array($route['handler'], $params);
                }
            }
        }
        
        // 404 Not Found
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint not found',
            'error' => 'The requested endpoint does not exist'
        ], JSON_UNESCAPED_UNICODE);
    }
    
    private function matchPath($routePath, $requestPath) {
        // Convert route path to regex pattern
        $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';
        
        return preg_match($pattern, $requestPath);
    }
    
    private function extractParams($routePath, $requestPath) {
        $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';
        
        preg_match($pattern, $requestPath, $matches);
        array_shift($matches); // Remove full match
        
        return $matches;
    }
}
