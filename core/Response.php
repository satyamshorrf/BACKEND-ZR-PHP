<?php
// core/Response.php

class Response {
    public static function json($data, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public static function error($message, $code = 400) {
        self::json(['message' => $message], $code);
    }
}
