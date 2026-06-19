<?php

declare(strict_types=1);

namespace App\Security;

final class Validator
{
    public static function username(string $value): ?string
    {
        $value = trim($value);

        if (strlen($value) < 3 || strlen($value) > 24) {
            return 'Le pseudo doit contenir entre 3 et 24 caracteres.';
        }

        if (preg_match('/^[a-zA-Z0-9_\-]+$/', $value) !== 1) {
            return 'Le pseudo accepte seulement lettres, chiffres, tirets et underscores.';
        }

        return null;
    }

    public static function email(string $value): ?string
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false || strlen($value) > 190) {
            return 'Adresse email invalide.';
        }

        return null;
    }

    public static function password(string $value): ?string
    {
        if (strlen($value) < 10) {
            return 'Le mot de passe doit contenir au moins 10 caracteres.';
        }

        if (!preg_match('/[A-Z]/', $value) || !preg_match('/[a-z]/', $value) || !preg_match('/[0-9]/', $value)) {
            return 'Le mot de passe doit contenir majuscule, minuscule et chiffre.';
        }

        return null;
    }

    public static function avatar(string $value): bool
    {
        return array_key_exists($value, (array) \app_config('avatars', []));
    }
}
