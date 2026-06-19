<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Score
{
    public static function topByWins(int $limit = 10): array
    {
        $statement = Database::pdo()->prepare(
            'SELECT u.id, u.username, u.avatar, s.games_played, s.wins, s.losses, s.draws, s.points,
                    CASE WHEN s.losses = 0 THEN s.wins ELSE ROUND(s.wins / s.losses, 2) END AS win_loss_ratio
             FROM scores s
             JOIN users u ON u.id = s.user_id
             WHERE u.status = "active"
             ORDER BY s.wins DESC, s.points DESC, s.games_played DESC
             LIMIT :limit'
        );
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public static function topByRatio(int $limit = 10): array
    {
        $statement = Database::pdo()->prepare(
            'SELECT u.id, u.username, u.avatar, s.games_played, s.wins, s.losses, s.draws, s.points,
                    CASE WHEN s.losses = 0 THEN s.wins ELSE ROUND(s.wins / s.losses, 2) END AS win_loss_ratio
             FROM scores s
             JOIN users u ON u.id = s.user_id
             WHERE u.status = "active" AND s.games_played > 0
             ORDER BY win_loss_ratio DESC, s.wins DESC, s.games_played DESC
             LIMIT :limit'
        );
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public static function topByGames(int $limit = 10): array
    {
        $statement = Database::pdo()->prepare(
            'SELECT u.id, u.username, u.avatar, s.games_played, s.wins, s.losses, s.draws, s.points,
                    CASE WHEN s.losses = 0 THEN s.wins ELSE ROUND(s.wins / s.losses, 2) END AS win_loss_ratio
             FROM scores s
             JOIN users u ON u.id = s.user_id
             WHERE u.status = "active"
             ORDER BY s.games_played DESC, s.wins DESC, s.points DESC
             LIMIT :limit'
        );
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }
}
