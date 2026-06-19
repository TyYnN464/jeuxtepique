<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class User
{
    public static function create(string $username, string $email, string $password, string $avatar = 'avatar-astronaut.svg', string $role = 'user'): int
    {
        $pdo = Database::pdo();
        $hash = password_hash($password, (int) \app_config('security.password_algo', PASSWORD_DEFAULT));

        $pdo->beginTransaction();

        $statement = $pdo->prepare(
            'INSERT INTO users (username, email, password_hash, avatar, role, status)
             VALUES (:username, :email, :password_hash, :avatar, :role, "active")'
        );
        $statement->execute([
            'username' => $username,
            'email' => mb_strtolower($email),
            'password_hash' => $hash,
            'avatar' => $avatar,
            'role' => $role,
        ]);

        $userId = (int) $pdo->lastInsertId();

        $score = $pdo->prepare('INSERT INTO scores (user_id) VALUES (:user_id)');
        $score->execute(['user_id' => $userId]);

        $pdo->commit();

        return $userId;
    }

    public static function findById(int $id): ?array
    {
        $statement = Database::pdo()->prepare(
            'SELECT u.*, COALESCE(s.games_played, 0) AS games_played, COALESCE(s.wins, 0) AS wins,
                    COALESCE(s.losses, 0) AS losses, COALESCE(s.draws, 0) AS draws, COALESCE(s.points, 0) AS points
             FROM users u
             LEFT JOIN scores s ON s.user_id = u.id
             WHERE u.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);

        $user = $statement->fetch();
        return $user === false ? null : $user;
    }

    public static function findByEmail(string $email): ?array
    {
        $statement = Database::pdo()->prepare(
            'SELECT u.*, COALESCE(s.games_played, 0) AS games_played, COALESCE(s.wins, 0) AS wins,
                    COALESCE(s.losses, 0) AS losses, COALESCE(s.draws, 0) AS draws, COALESCE(s.points, 0) AS points
             FROM users u
             LEFT JOIN scores s ON s.user_id = u.id
             WHERE LOWER(u.email) = LOWER(:email)
             LIMIT 1'
        );
        $statement->execute(['email' => $email]);

        $user = $statement->fetch();
        return $user === false ? null : $user;
    }

    public static function findByUsername(string $username): ?array
    {
        $statement = Database::pdo()->prepare(
            'SELECT * FROM users WHERE LOWER(username) = LOWER(:username) LIMIT 1'
        );
        $statement->execute(['username' => $username]);

        $user = $statement->fetch();
        return $user === false ? null : $user;
    }

    public static function usernameExists(string $username, ?int $exceptUserId = null): bool
    {
        $sql = 'SELECT id FROM users WHERE LOWER(username) = LOWER(:username)';
        $params = ['username' => $username];

        if ($exceptUserId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $exceptUserId;
        }

        $sql .= ' LIMIT 1';
        $statement = Database::pdo()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchColumn() !== false;
    }

    public static function emailExists(string $email): bool
    {
        $statement = Database::pdo()->prepare('SELECT id FROM users WHERE LOWER(email) = LOWER(:email) LIMIT 1');
        $statement->execute(['email' => $email]);

        return $statement->fetchColumn() !== false;
    }

    public static function updateProfile(int $id, string $username, string $avatar): void
    {
        $statement = Database::pdo()->prepare(
            'UPDATE users SET username = :username, avatar = :avatar, updated_at = NOW() WHERE id = :id'
        );
        $statement->execute([
            'id' => $id,
            'username' => $username,
            'avatar' => $avatar,
        ]);
    }

    public static function deactivate(int $id): void
    {
        $statement = Database::pdo()->prepare('UPDATE users SET status = "disabled", updated_at = NOW() WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    public static function allWithScores(int $limit = 100): array
    {
        $statement = Database::pdo()->prepare(
            'SELECT u.id, u.username, u.email, u.avatar, u.role, u.status, u.created_at,
                    COALESCE(s.games_played, 0) AS games_played, COALESCE(s.wins, 0) AS wins,
                    COALESCE(s.losses, 0) AS losses, COALESCE(s.draws, 0) AS draws, COALESCE(s.points, 0) AS points
             FROM users u
             LEFT JOIN scores s ON s.user_id = u.id
             ORDER BY u.created_at DESC
             LIMIT :limit'
        );
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public static function countAll(): int
    {
        return (int) Database::pdo()->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }

    public static function countActive(): int
    {
        return (int) Database::pdo()->query('SELECT COUNT(*) FROM users WHERE status = "active"')->fetchColumn();
    }
}
