<?php

declare(strict_types=1);

namespace App\Security;

final class Csrf
{
    public static function token(): string
    {
        $key = (string) \app_config('security.csrf_key', '_csrf_token');

        if (empty($_SESSION[$key])) {
            $_SESSION[$key] = bin2hex(random_bytes(32));
        }

        return $_SESSION[$key];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . \e(self::token()) . '">';
    }

    public static function isValid(?string $token): bool
    {
        $key = (string) \app_config('security.csrf_key', '_csrf_token');

        return is_string($token)
            && isset($_SESSION[$key])
            && hash_equals((string) $_SESSION[$key], $token);
    }

    public static function requireValid(): void
    {
        if (!self::isValid($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('Token CSRF invalide. Rechargez la page puis recommencez.');
        }
    }
}
