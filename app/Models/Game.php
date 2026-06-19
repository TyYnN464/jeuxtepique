<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Game
{
    public static function allActive(): array
    {
        $statement = Database::pdo()->query(
            'SELECT * FROM games WHERE is_active = 1 ORDER BY sort_order ASC, name ASC'
        );

        return $statement->fetchAll();
    }

    public static function findBySlug(string $slug): ?array
    {
        $statement = Database::pdo()->prepare('SELECT * FROM games WHERE slug = :slug LIMIT 1');
        $statement->execute(['slug' => $slug]);

        $game = $statement->fetch();
        return $game === false ? null : $game;
    }
}
