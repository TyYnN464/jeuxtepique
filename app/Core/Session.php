<?php

declare(strict_types=1);

namespace App\Core;

final class Session
{
    public static function start(array $config): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_name($config['session_name'] ?? 'JEUXTEPIQUESESSID');

        $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();

        if (!isset($_SESSION['_created_at'])) {
            $_SESSION['_created_at'] = time();
        }

        if (!isset($_SESSION['_last_regeneration']) || (time() - (int) $_SESSION['_last_regeneration']) > 900) {
            session_regenerate_id(true);
            $_SESSION['_last_regeneration'] = time();
        }
    }

    public static function flash(string $key, ?string $message = null): ?string
    {
        if ($message !== null) {
            $_SESSION['_flash'][$key] = $message;
            return null;
        }

        if (!isset($_SESSION['_flash'][$key])) {
            return null;
        }

        $value = $_SESSION['_flash'][$key];
        unset($_SESSION['_flash'][$key]);

        return $value;
    }
}
