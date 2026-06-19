<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Invitation
{
    public static function findByToken(string $token): ?array
    {
        $statement = Database::pdo()->prepare(
            'SELECT i.*, m.status AS match_status, m.mode, m.created_by_user_id, u.username AS creator_username
             FROM invitations i
             JOIN matches m ON m.id = i.match_id
             JOIN users u ON u.id = i.created_by_user_id
             WHERE i.token = :token
             LIMIT 1'
        );
        $statement->execute(['token' => $token]);

        $invitation = $statement->fetch();
        return $invitation === false ? null : $invitation;
    }
}
