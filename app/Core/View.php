<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class View
{
    public static function render(string $view, array $data = []): void
    {
        $viewPath = BASE_PATH . '/app/Views/' . $view . '.php';

        if (!is_file($viewPath)) {
            throw new RuntimeException('Vue introuvable: ' . $view);
        }

        extract($data, EXTR_SKIP);
        require $viewPath;
    }
}
