<?php

declare(strict_types=1);

use App\Models\User;
use App\Security\Validator;

if (PHP_SAPI !== 'cli') {
    exit("Ce script doit etre execute en ligne de commande.\n");
}

require_once __DIR__ . '/../app/bootstrap.php';

function prompt(string $label): string
{
    if (function_exists('readline')) {
        $value = readline($label);
    } else {
        echo $label;
        $value = fgets(STDIN);
    }

    return trim((string) $value);
}

$username = prompt('Pseudo admin: ');
$email = prompt('Email admin: ');
$password = prompt('Mot de passe admin: ');

$errors = array_filter([
    Validator::username($username),
    Validator::email($email),
    Validator::password($password),
]);

if (User::usernameExists($username)) {
    $errors[] = 'Ce pseudo existe deja.';
}

if (User::emailExists($email)) {
    $errors[] = 'Cet email existe deja.';
}

if ($errors !== []) {
    fwrite(STDERR, implode("\n", $errors) . "\n");
    exit(1);
}

$id = User::create($username, $email, $password, 'avatar-star.svg', 'admin');

echo "Compte admin cree avec l ID {$id}.\n";
