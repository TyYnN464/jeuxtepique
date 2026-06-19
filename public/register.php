<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

$controller = new App\Controllers\AuthController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->register();
}

$controller->showRegister();
