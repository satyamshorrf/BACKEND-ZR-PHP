<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Database.php';

class AuthController {
    public function signup($req, $res) {
        $name = $req['name'] ?? null;
        $email = $req['email'] ?? null;
        $password = $req['password'] ?? null;

        if (!$name || !$email || !$password) {
            return Response::json($res, ['message' => 'Missing fields'], 400);
        }

        $userModel = new User();
        if ($userModel->findByEmail($email)) {
            return Response::json($res, ['message' => 'User already exists'], 400);
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $userId = $userModel->create($name, $email, $hashedPassword);

        [$accessToken, $refreshToken] = Auth::generateTokens($userId);
        Auth::storeRefreshToken($userId, $refreshToken);
        Auth::setCookies($res, $accessToken, $refreshToken);

        $user = $userModel->findById($userId);
        return Response::json($res, $user, 201);
    }

    public function login($req, $res) {
        $email = $req['email'] ?? null;
        $password = $req['password'] ?? null;

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            [$accessToken, $refreshToken] = Auth::generateTokens($user['id']);
            Auth::storeRefreshToken($user['id'], $refreshToken);
            Auth::setCookies($res, $accessToken, $refreshToken);

            unset($user['password']);
            return Response::json($res, $user);
        }

        return Response::json($res, ['message' => 'Invalid email or password'], 400);
    }

    public function logout($req, $res) {
        if (!empty($_COOKIE['refreshToken'])) {
            $decoded = Auth::verifyToken($_COOKIE['refreshToken'], getenv('REFRESH_TOKEN_SECRET'));
            if ($decoded) {
                Auth::deleteRefreshToken($decoded->userId);
            }
        }

        setcookie('accessToken', '', time() - 3600, '/');
        setcookie('refreshToken', '', time() - 3600, '/');
        return Response::json($res, ['message' => 'Logged out successfully']);
    }

    public function refreshToken($req, $res) {
        $refreshToken = $_COOKIE['refreshToken'] ?? null;

        if (!$refreshToken) {
            return Response::json($res, ['message' => 'No refresh token provided'], 401);
        }

        $decoded = Auth::verifyToken($refreshToken, getenv('REFRESH_TOKEN_SECRET'));
        if (!$decoded) {
            return Response::json($res, ['message' => 'Invalid refresh token'], 401);
        }

        $stored = Auth::getStoredRefreshToken($decoded->userId);
        if ($stored !== $refreshToken) {
            return Response::json($res, ['message' => 'Refresh token mismatch'], 401);
        }

        $accessToken = Auth::generateAccessToken($decoded->userId);
        Auth::setAccessTokenCookie($res, $accessToken);

        return Response::json($res, ['message' => 'Token refreshed successfully']);
    }

    public function getProfile($req, $res) {
        return Response::json($res, $req['user']);
    }
}
