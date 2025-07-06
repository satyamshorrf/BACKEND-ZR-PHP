<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth {
    public static function generateTokens($userId) {
        $accessToken = self::generateAccessToken($userId);
        $refreshToken = JWT::encode(
            ['userId' => $userId, 'exp' => time() + 604800], // 7 days
            getenv('REFRESH_TOKEN_SECRET'),
            'HS256'
        );
        return [$accessToken, $refreshToken];
    }

    public static function generateAccessToken($userId) {
        return JWT::encode(
            ['userId' => $userId, 'exp' => time() + 900], // 15 minutes
            getenv('ACCESS_TOKEN_SECRET'),
            'HS256'
        );
    }

    public static function verifyToken($token, $secret) {
        try {
            return JWT::decode($token, new Key($secret, 'HS256'));
        } catch (Exception $e) {
            return false;
        }
    }

    public static function storeRefreshToken($userId, $token) {
        file_put_contents(__DIR__ . "/../.tokens/refresh_$userId.txt", $token);
    }

    public static function getStoredRefreshToken($userId) {
        $path = __DIR__ . "/../.tokens/refresh_$userId.txt";
        return file_exists($path) ? file_get_contents($path) : null;
    }

    public static function deleteRefreshToken($userId) {
        $path = __DIR__ . "/../.tokens/refresh_$userId.txt";
        if (file_exists($path)) unlink($path);
    }

    public static function setCookies($accessToken, $refreshToken) {
        setcookie("accessToken", $accessToken, time() + 900, "/", "", false, true);
        setcookie("refreshToken", $refreshToken, time() + 604800, "/", "", false, true);
    }

    public static function setAccessTokenCookie($accessToken) {
        setcookie("accessToken", $accessToken, time() + 900, "/", "", false, true);
    }

    // Session-based methods
    public static function user() {
        session_start();
        return $_SESSION['user'] ?? null;
    }

    public static function check() {
        if (!self::user()) {
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized']);
            exit;
        }
    }
}
