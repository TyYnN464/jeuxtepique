<?php

declare(strict_types=1);

$env = static function (string $key, mixed $default = null): mixed {
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

    if ($value === false || $value === null || $value === '') {
        return $default;
    }

    return $value;
};

return [
    'app' => [
        'name' => 'JeuxTepique',
        'env' => $env('APP_ENV', 'production'),
        'debug' => $env('APP_DEBUG', 'false') === 'true',
        'base_url' => $env('APP_URL', 'http://jeuxtepique.local'),
        'base_path' => $env('APP_BASE_PATH', ''),
        'timezone' => 'Europe/Paris',
        'session_name' => 'JEUXTEPIQUESESSID',
    ],
    'db' => [
        'host' => $env('DB_HOST', '127.0.0.1'),
        'port' => (int) $env('DB_PORT', 3306),
        'database' => $env('DB_DATABASE', 'jeuxtepique'),
        'username' => $env('DB_USERNAME', 'jeuxtepique_user'),
        'password' => $env('DB_PASSWORD', 'ChangeMe_StrongPassword_2026!'),
        'charset' => 'utf8mb4',
    ],
    'security' => [
        'csrf_key' => '_csrf_token',
        'password_algo' => PASSWORD_DEFAULT,
    ],
    'avatars' => [
        'avatar-astronaut.svg' => 'Astronaute',
        'avatar-planet.svg' => 'Planete',
        'avatar-rocket.svg' => 'Fusee',
        'avatar-star.svg' => 'Etoile',
        'avatar-gamepad.svg' => 'Manette',
    ],
];
