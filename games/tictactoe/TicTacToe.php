<?php

declare(strict_types=1);

namespace Games\tictactoe;

final class TicTacToe
{
    public const EMPTY = '-';
    public const INITIAL_BOARD = '---------';

    private const LINES = [
        [0, 1, 2],
        [3, 4, 5],
        [6, 7, 8],
        [0, 3, 6],
        [1, 4, 7],
        [2, 5, 8],
        [0, 4, 8],
        [2, 4, 6],
    ];

    public static function normalizeBoard(?string $board): string
    {
        if (!is_string($board) || strlen($board) !== 9 || preg_match('/^[XO\-]{9}$/', $board) !== 1) {
            return self::INITIAL_BOARD;
        }

        return $board;
    }

    public static function boardArray(string $board): array
    {
        return str_split(self::normalizeBoard($board));
    }

    public static function applyMove(string $board, int $position, string $symbol): string
    {
        $board = self::normalizeBoard($board);

        if ($position < 0 || $position > 8) {
            throw new \InvalidArgumentException('Position invalide.');
        }

        if (!in_array($symbol, ['X', 'O'], true)) {
            throw new \InvalidArgumentException('Symbole invalide.');
        }

        if ($board[$position] !== self::EMPTY) {
            throw new \InvalidArgumentException('Cette case est deja prise.');
        }

        $board[$position] = $symbol;

        return $board;
    }

    public static function winner(string $board): ?string
    {
        $cells = self::boardArray($board);

        foreach (self::LINES as $line) {
            [$a, $b, $c] = $line;
            if ($cells[$a] !== self::EMPTY && $cells[$a] === $cells[$b] && $cells[$b] === $cells[$c]) {
                return $cells[$a];
            }
        }

        return null;
    }

    public static function isDraw(string $board): bool
    {
        return self::winner($board) === null && !str_contains(self::normalizeBoard($board), self::EMPTY);
    }

    public static function availableMoves(string $board): array
    {
        $moves = [];
        foreach (self::boardArray($board) as $index => $cell) {
            if ($cell === self::EMPTY) {
                $moves[] = $index;
            }
        }

        return $moves;
    }

    public static function botMove(string $board, string $botSymbol = 'O'): ?int
    {
        $board = self::normalizeBoard($board);
        $humanSymbol = $botSymbol === 'O' ? 'X' : 'O';

        $winningMove = self::findTacticalMove($board, $botSymbol);
        if ($winningMove !== null) {
            return $winningMove;
        }

        $blockingMove = self::findTacticalMove($board, $humanSymbol);
        if ($blockingMove !== null) {
            return $blockingMove;
        }

        if ($board[4] === self::EMPTY) {
            return 4;
        }

        foreach ([0, 2, 6, 8, 1, 3, 5, 7] as $preferred) {
            if ($board[$preferred] === self::EMPTY) {
                return $preferred;
            }
        }

        return null;
    }

    private static function findTacticalMove(string $board, string $symbol): ?int
    {
        foreach (self::availableMoves($board) as $move) {
            $trial = self::applyMove($board, $move, $symbol);
            if (self::winner($trial) === $symbol) {
                return $move;
            }
        }

        return null;
    }
}
