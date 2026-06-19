<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Session;
use App\Security\Csrf;

define('BASE_PATH', dirname(__DIR__));

$config = require BASE_PATH . '/config/config.php';
$GLOBALS['app_config'] = $config;

date_default_timezone_set($config['app']['timezone'] ?? 'Europe/Paris');

if (($config['app']['debug'] ?? false) === true) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'App\\' => BASE_PATH . '/app/',
        'Games\\' => BASE_PATH . '/games/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
            continue;
        }

        $relativeClass = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (is_file($file)) {
            require $file;
        }
    }
});

Session::start($config['app']);

if (!function_exists('app_config')) {
    function app_config(?string $key = null, mixed $default = null): mixed
    {
        $config = $GLOBALS['app_config'] ?? [];

        if ($key === null) {
            return $config;
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($config) || !array_key_exists($segment, $config)) {
                return $default;
            }

            $config = $config[$segment];
        }

        return $config;
    }
}

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        if (preg_match('#^https?://#i', $path) === 1) {
            return $path;
        }

        $basePath = trim((string) app_config('app.base_path', ''), '/');

        if ($path === '') {
            return $basePath === '' ? '/' : '/' . $basePath;
        }

        return ($basePath === '' ? '' : '/' . $basePath) . '/' . ltrim($path, '/');
    }
}

if (!function_exists('full_url')) {
    function full_url(string $path = ''): string
    {
        if (preg_match('#^https?://#i', $path) === 1) {
            return $path;
        }

        return rtrim((string) app_config('app.base_url'), '/') . url($path);
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }
}

if (!function_exists('current_user')) {
    function current_user(): ?array
    {
        return Auth::user();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return Csrf::field();
    }
}
