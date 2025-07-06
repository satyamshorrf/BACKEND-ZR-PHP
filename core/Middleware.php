<?php
// core/Middleware.php

class Middleware {
    public static function auth($callback) {
        session_start();
        if (!isset($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized']);
            exit;
        }
        $callback();
    }
}
