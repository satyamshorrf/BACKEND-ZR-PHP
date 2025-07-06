class Request {
    public static function json() {
        return json_decode(file_get_contents('php://input'), true);
    }
}

<?php
// core/Request.php

class Request {
    public $method;
    public $path;
    public $body;

    public function __construct() {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $this->body = json_decode(file_get_contents('php://input'), true);
    }

    public function input($key, $default = null) {
        return $this->body[$key] ?? $default;
    }
}
