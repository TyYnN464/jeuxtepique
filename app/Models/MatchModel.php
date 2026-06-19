<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use Games\tictactoe\TicTacToe;
use InvalidArgumentException;
use PDO;
use RuntimeException;

final class MatchModel
{
    public static function createSolo(int $userId): int
    {
        $pdo = Database::pdo();
        $game = Game::findBySlug('tictactoe');

        if ($game === null) {
            throw new RuntimeException('Jeu morpion introuvable.');
        }

        $pdo->beginTransaction();

        $statement = $pdo->prepare(
            'INSERT INTO matches (game_id, status, mode, created_by_user_id, current_turn_user_id, board_state, started_at)
             VALUES (:game_id, "active", "solo", :created_by, :current_turn, :board_state, NOW())'
        );
        $statement->execute([
            'game_id' => $game['id'],
            'created_by' => $userId,
            'current_turn' => $userId,
            'board_state' => TicTacToe::INITIAL_BOARD,
        ]);

        $matchId = (int) $pdo->lastInsertId();
        self::insertPlayer($pdo, $matchId, $userId, 'X', false);
        self::insertPlayer($pdo, $matchId, null, 'O', true);

        $pdo->commit();

        return $matchId;
    }

    public static function createPrivate(int $userId): array
    {
        $pdo = Database::pdo();
        $game = Game::findBySlug('tictactoe');

        if ($game === null) {
            throw new RuntimeException('Jeu morpion introuvable.');
        }

        $token = bin2hex(random_bytes(16));
        $pdo->beginTransaction();

        $statement = $pdo->prepare(
            'INSERT INTO matches (game_id, status, mode, created_by_user_id, current_turn_user_id, board_state, invitation_token)
             VALUES (:game_id, "waiting", "multi", :created_by, :current_turn, :board_state, :token)'
        );
        $statement->execute([
            'game_id' => $game['id'],
            'created_by' => $userId,
            'current_turn' => $userId,
            'board_state' => TicTacToe::INITIAL_BOARD,
            'token' => $token,
        ]);

        $matchId = (int) $pdo->lastInsertId();
        self::insertPlayer($pdo, $matchId, $userId, 'X', false);

        $invitation = $pdo->prepare(
            'INSERT INTO invitations (match_id, token, created_by_user_id, status, expires_at)
             VALUES (:match_id, :token, :created_by, "pending", DATE_ADD(NOW(), INTERVAL 7 DAY))'
        );
        $invitation->execute([
            'match_id' => $matchId,
            'token' => $token,
            'created_by' => $userId,
        ]);

        $pdo->commit();

        return ['match_id' => $matchId, 'token' => $token];
    }

    public static function joinByToken(string $token, int $userId): int
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();

        $statement = $pdo->prepare(
            'SELECT i.*, m.status AS match_status, m.created_by_user_id
             FROM invitations i
             JOIN matches m ON m.id = i.match_id
             WHERE i.token = :token
             LIMIT 1
             FOR UPDATE'
        );
        $statement->execute(['token' => $token]);
        $invitation = $statement->fetch();

        if ($invitation === false) {
            $pdo->rollBack();
            throw new RuntimeException('Invitation introuvable.');
        }

        $matchId = (int) $invitation['match_id'];

        if (self::isPlayer($matchId, $userId)) {
            $pdo->commit();
            return $matchId;
        }

        if ($invitation['status'] !== 'pending' || $invitation['match_status'] !== 'waiting') {
            $pdo->rollBack();
            throw new RuntimeException('Cette invitation n est plus disponible.');
        }

        if (strtotime((string) $invitation['expires_at']) < time()) {
            $expire = $pdo->prepare('UPDATE invitations SET status = "expired" WHERE id = :id');
            $expire->execute(['id' => $invitation['id']]);
            $pdo->commit();
            throw new RuntimeException('Cette invitation a expire.');
        }

        if ((int) $invitation['created_by_user_id'] === $userId) {
            $pdo->commit();
            return $matchId;
        }

        $count = $pdo->prepare('SELECT COUNT(*) FROM match_players WHERE match_id = :match_id AND is_bot = 0');
        $count->execute(['match_id' => $matchId]);
        if ((int) $count->fetchColumn() >= 2) {
            $pdo->rollBack();
            throw new RuntimeException('Cette partie est deja complete.');
        }

        self::insertPlayer($pdo, $matchId, $userId, 'O', false);

        $match = $pdo->prepare('UPDATE matches SET status = "active", started_at = NOW(), updated_at = NOW() WHERE id = :id');
        $match->execute(['id' => $matchId]);

        $accept = $pdo->prepare(
            'UPDATE invitations SET status = "accepted", invited_user_id = :user_id, accepted_at = NOW() WHERE id = :id'
        );
        $accept->execute(['user_id' => $userId, 'id' => $invitation['id']]);

        $pdo->commit();

        return $matchId;
    }

    public static function find(int $id): ?array
    {
        $statement = Database::pdo()->prepare(
            'SELECT m.*, g.name AS game_name, g.slug AS game_slug,
                    creator.username AS creator_username,
                    winner.username AS winner_username
             FROM matches m
             JOIN games g ON g.id = m.game_id
             JOIN users creator ON creator.id = m.created_by_user_id
             LEFT JOIN users winner ON winner.id = m.winner_user_id
             WHERE m.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);

        $match = $statement->fetch();
        return $match === false ? null : $match;
    }

    public static function players(int $matchId): array
    {
        $statement = Database::pdo()->prepare(
            'SELECT mp.*, u.username, u.avatar
             FROM match_players mp
             LEFT JOIN users u ON u.id = mp.user_id
             WHERE mp.match_id = :match_id
             ORDER BY mp.symbol ASC'
        );
        $statement->execute(['match_id' => $matchId]);

        return $statement->fetchAll();
    }

    public static function moves(int $matchId): array
    {
        $statement = Database::pdo()->prepare(
            'SELECT mv.*, u.username
             FROM moves mv
             LEFT JOIN users u ON u.id = mv.user_id
             WHERE mv.match_id = :match_id
             ORDER BY mv.move_number ASC'
        );
        $statement->execute(['match_id' => $matchId]);

        return $statement->fetchAll();
    }

    public static function recordMove(int $matchId, int $userId, int $position): void
    {
        if ($position < 0 || $position > 8) {
            throw new InvalidArgumentException('Position invalide.');
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();

        $statement = $pdo->prepare('SELECT * FROM matches WHERE id = :id LIMIT 1 FOR UPDATE');
        $statement->execute(['id' => $matchId]);
        $match = $statement->fetch();

        if ($match === false) {
            $pdo->rollBack();
            throw new RuntimeException('Partie introuvable.');
        }

        if ($match['status'] !== 'active') {
            $pdo->rollBack();
            throw new RuntimeException('Cette partie ne peut pas recevoir de coup.');
        }

        if ((int) $match['current_turn_user_id'] !== $userId) {
            $pdo->rollBack();
            throw new RuntimeException('Ce n est pas votre tour.');
        }

        $player = self::playerFor($pdo, $matchId, $userId);
        if ($player === null) {
            $pdo->rollBack();
            throw new RuntimeException('Vous ne participez pas a cette partie.');
        }

        $board = TicTacToe::normalizeBoard((string) $match['board_state']);

        if ($board[$position] !== TicTacToe::EMPTY) {
            $pdo->rollBack();
            throw new RuntimeException('Cette case est deja prise.');
        }

        $symbol = (string) $player['symbol'];
        $board = TicTacToe::applyMove($board, $position, $symbol);
        self::insertMove($pdo, $matchId, $userId, $position, $symbol);

        if (self::persistEndIfNeeded($pdo, $match, $board)) {
            $pdo->commit();
            return;
        }

        if ($match['mode'] === 'solo') {
            $botPosition = TicTacToe::botMove($board, 'O');

            if ($botPosition !== null) {
                $board = TicTacToe::applyMove($board, $botPosition, 'O');
                self::insertMove($pdo, $matchId, null, $botPosition, 'O');
            }

            if (!self::persistEndIfNeeded($pdo, $match, $board)) {
                $update = $pdo->prepare(
                    'UPDATE matches SET board_state = :board, current_turn_user_id = :turn, updated_at = NOW() WHERE id = :id'
                );
                $update->execute(['board' => $board, 'turn' => $userId, 'id' => $matchId]);
            }

            $pdo->commit();
            return;
        }

        $nextUserId = self::nextHumanTurn($pdo, $matchId, $userId);
        $update = $pdo->prepare(
            'UPDATE matches SET board_state = :board, current_turn_user_id = :turn, updated_at = NOW() WHERE id = :id'
        );
        $update->execute(['board' => $board, 'turn' => $nextUserId, 'id' => $matchId]);

        $pdo->commit();
    }

    public static function listForUser(int $userId, int $limit = 8): array
    {
        $statement = Database::pdo()->prepare(
            'SELECT m.*, g.name AS game_name, g.slug AS game_slug,
                    winner.username AS winner_username
             FROM matches m
             JOIN games g ON g.id = m.game_id
             LEFT JOIN users winner ON winner.id = m.winner_user_id
             WHERE EXISTS (
                SELECT 1 FROM match_players mp WHERE mp.match_id = m.id AND mp.user_id = :user_id
             )
             ORDER BY m.updated_at DESC, m.created_at DESC
             LIMIT :limit'
        );
        $statement->bindValue('user_id', $userId, PDO::PARAM_INT);
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public static function allForAdmin(int $limit = 100): array
    {
        $statement = Database::pdo()->prepare(
            'SELECT m.*, g.name AS game_name, creator.username AS creator_username, winner.username AS winner_username
             FROM matches m
             JOIN games g ON g.id = m.game_id
             JOIN users creator ON creator.id = m.created_by_user_id
             LEFT JOIN users winner ON winner.id = m.winner_user_id
             ORDER BY m.created_at DESC
             LIMIT :limit'
        );
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public static function delete(int $matchId): void
    {
        $statement = Database::pdo()->prepare('DELETE FROM matches WHERE id = :id');
        $statement->execute(['id' => $matchId]);
    }

    public static function isPlayer(int $matchId, int $userId): bool
    {
        $statement = Database::pdo()->prepare(
            'SELECT id FROM match_players WHERE match_id = :match_id AND user_id = :user_id LIMIT 1'
        );
        $statement->execute(['match_id' => $matchId, 'user_id' => $userId]);

        return $statement->fetchColumn() !== false;
    }

    public static function stats(): array
    {
        $pdo = Database::pdo();

        return [
            'matches' => (int) $pdo->query('SELECT COUNT(*) FROM matches')->fetchColumn(),
            'active_matches' => (int) $pdo->query('SELECT COUNT(*) FROM matches WHERE status = "active"')->fetchColumn(),
            'finished_matches' => (int) $pdo->query('SELECT COUNT(*) FROM matches WHERE status = "finished"')->fetchColumn(),
            'moves' => (int) $pdo->query('SELECT COUNT(*) FROM moves')->fetchColumn(),
        ];
    }

    private static function insertPlayer(PDO $pdo, int $matchId, ?int $userId, string $symbol, bool $isBot): void
    {
        $statement = $pdo->prepare(
            'INSERT INTO match_players (match_id, user_id, symbol, is_bot)
             VALUES (:match_id, :user_id, :symbol, :is_bot)'
        );
        $statement->bindValue('match_id', $matchId, PDO::PARAM_INT);
        $statement->bindValue('user_id', $userId, $userId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $statement->bindValue('symbol', $symbol);
        $statement->bindValue('is_bot', $isBot ? 1 : 0, PDO::PARAM_INT);
        $statement->execute();
    }

    private static function playerFor(PDO $pdo, int $matchId, int $userId): ?array
    {
        $statement = $pdo->prepare(
            'SELECT * FROM match_players WHERE match_id = :match_id AND user_id = :user_id AND is_bot = 0 LIMIT 1'
        );
        $statement->execute(['match_id' => $matchId, 'user_id' => $userId]);

        $player = $statement->fetch();
        return $player === false ? null : $player;
    }

    private static function insertMove(PDO $pdo, int $matchId, ?int $userId, int $position, string $symbol): void
    {
        $moveNumber = self::nextMoveNumber($pdo, $matchId);
        $statement = $pdo->prepare(
            'INSERT INTO moves (match_id, user_id, position, symbol, move_number)
             VALUES (:match_id, :user_id, :position, :symbol, :move_number)'
        );
        $statement->bindValue('match_id', $matchId, PDO::PARAM_INT);
        $statement->bindValue('user_id', $userId, $userId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $statement->bindValue('position', $position, PDO::PARAM_INT);
        $statement->bindValue('symbol', $symbol);
        $statement->bindValue('move_number', $moveNumber, PDO::PARAM_INT);
        $statement->execute();
    }

    private static function nextMoveNumber(PDO $pdo, int $matchId): int
    {
        $statement = $pdo->prepare('SELECT COALESCE(MAX(move_number), 0) + 1 FROM moves WHERE match_id = :match_id');
        $statement->execute(['match_id' => $matchId]);

        return (int) $statement->fetchColumn();
    }

    private static function nextHumanTurn(PDO $pdo, int $matchId, int $currentUserId): ?int
    {
        $statement = $pdo->prepare(
            'SELECT user_id FROM match_players
             WHERE match_id = :match_id AND is_bot = 0 AND user_id <> :current_user_id
             LIMIT 1'
        );
        $statement->execute(['match_id' => $matchId, 'current_user_id' => $currentUserId]);
        $next = $statement->fetchColumn();

        return $next === false ? null : (int) $next;
    }

    private static function persistEndIfNeeded(PDO $pdo, array $match, string $board): bool
    {
        $winnerSymbol = TicTacToe::winner($board);
        $isDraw = TicTacToe::isDraw($board);

        if ($winnerSymbol === null && !$isDraw) {
            return false;
        }

        $players = self::players((int) $match['id']);
        $winnerUserId = null;

        if ($winnerSymbol !== null) {
            foreach ($players as $player) {
                if ($player['symbol'] === $winnerSymbol && $player['user_id'] !== null) {
                    $winnerUserId = (int) $player['user_id'];
                    break;
                }
            }
        }

        $result = $isDraw ? 'draw' : strtolower($winnerSymbol) . '_win';

        $update = $pdo->prepare(
            'UPDATE matches
             SET status = "finished", board_state = :board, winner_user_id = :winner_user_id,
                 result = :result, current_turn_user_id = NULL, ended_at = NOW(), updated_at = NOW()
             WHERE id = :id'
        );
        $update->bindValue('board', $board);
        $update->bindValue('winner_user_id', $winnerUserId, $winnerUserId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $update->bindValue('result', $result);
        $update->bindValue('id', (int) $match['id'], PDO::PARAM_INT);
        $update->execute();

        self::updateScores($pdo, $players, $winnerUserId, $isDraw);

        return true;
    }

    private static function updateScores(PDO $pdo, array $players, ?int $winnerUserId, bool $isDraw): void
    {
        $statement = $pdo->prepare(
            'INSERT INTO scores (user_id, games_played, wins, losses, draws, points)
             VALUES (:user_id, 1, :wins, :losses, :draws, :points)
             ON DUPLICATE KEY UPDATE
                games_played = games_played + 1,
                wins = wins + VALUES(wins),
                losses = losses + VALUES(losses),
                draws = draws + VALUES(draws),
                points = points + VALUES(points),
                updated_at = NOW()'
        );

        foreach ($players as $player) {
            if ((int) $player['is_bot'] === 1 || $player['user_id'] === null) {
                continue;
            }

            $userId = (int) $player['user_id'];
            $wins = (!$isDraw && $winnerUserId !== null && $winnerUserId === $userId) ? 1 : 0;
            $draws = $isDraw ? 1 : 0;
            $losses = (!$isDraw && $wins === 0) ? 1 : 0;
            $points = ($wins * 3) + $draws;

            $statement->execute([
                'user_id' => $userId,
                'wins' => $wins,
                'losses' => $losses,
                'draws' => $draws,
                'points' => $points,
            ]);
        }
    }
}
