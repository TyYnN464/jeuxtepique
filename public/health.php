<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['status' => 'ok', 'app' => app_config('app.name')], JSON_THROW_ON_ERROR);
