$router->post('/api/events', [EventController::class, 'createEvent']);
$router->get('/api/events', [EventController::class, 'getEvents']);
$router->put('/api/events/:eventId', [EventController::class, 'updateEvent']);
$router->delete('/api/events/:eventId', [EventController::class, 'deleteEvent']);

class Router {
    private $routes = [];

    public function add($method, $uri, $handler) {
        $this->routes[strtoupper($method)][$uri] = $handler;
    }

    public function dispatch($method, $uri) {
        $method = strtoupper($method);
        if (isset($this->routes[$method][$uri])) {
            [$controller, $function] = $this->routes[$method][$uri];
            $ctrl = new $controller();
            $ctrl->$function($_REQUEST, $_SERVER);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Route not found']);
        }
    }
}

<?php
// core/Router.php

class Router {
    private $routes = [];

    public function add($method, $path, $handler) {
        $this->routes[] = compact('method', 'path', 'handler');
    }

    public function dispatch(Request $request) {
        foreach ($this->routes as $route) {
            if ($request->method === $route['method'] && $request->path === $route['path']) {
                call_user_func($route['handler'], $request);
                return;
            }
        }

        http_response_code(404);
        echo json_encode(['message' => 'Not Found']);
    }
}
