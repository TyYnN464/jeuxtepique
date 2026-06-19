<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

final class Auth
{
    private static ?array $cachedUser = null;

    public static function user(): ?array
    {
        if (empty($_SESSION['user_id'])) {
            return null;
        }

        if (self::$cachedUser !== null && (int) self::$cachedUser['id'] === (int) $_SESSION['user_id']) {
            return self::$cachedUser;
        }

        $user = User::findById((int) $_SESSION['user_id']);

        if ($user === null || $user['status'] !== 'active') {
            self::logout();
            return null;
        }

        self::$cachedUser = $user;
        return $user;
    }

    public static function id(): ?int
    {
        $user = self::user();
        return $user === null ? null : (int) $user['id'];
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['_last_regeneration'] = time();
        self::$cachedUser = $user;
    }

    public static function logout(): void
    {
        self::$cachedUser = null;
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            Session::flash('error', 'Connectez-vous pour continuer.');
            \redirect('login.php?redirect=' . rawurlencode($_SERVER['REQUEST_URI'] ?? '/dashboard.php'));
        }
    }

    public static function requireAdmin(): void
    {
        self::requireLogin();

        $user = self::user();
        if ($user === null || $user['role'] !== 'admin') {
            http_response_code(403);
            exit('Acces refuse.');
        }
    }
}
