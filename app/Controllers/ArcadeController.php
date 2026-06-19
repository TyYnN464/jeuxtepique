<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Security\Csrf;

final class ArcadeController extends Controller
{
    private const RPS_CHOICES = ['rock', 'paper', 'scissors'];

    public function show(): void
    {
        Auth::requireLogin();

        $game = (string) ($_GET['game'] ?? 'rps');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::requireValid();
            $game = (string) ($_POST['game'] ?? $game);

            if ($game === 'rps') {
                $this->playRps();
                return;
            }
        }

        if ($game === 'memory') {
            $this->view('games/arcade/memory', [
                'title' => 'Memory - JeuxTepique',
            ]);
            return;
        }

        if ($game === 'connect4') {
            $this->view('games/arcade/connect4', [
                'title' => 'Puissance 4 - JeuxTepique',
            ]);
            return;
        }

        $this->view('games/arcade/rps', [
            'title' => 'Pierre Feuille Ciseaux - JeuxTepique',
            'result' => $_SESSION['rps_last_result'] ?? null,
            'stats' => $_SESSION['rps_stats'] ?? ['wins' => 0, 'losses' => 0, 'draws' => 0],
        ]);
    }

    private function playRps(): void
    {
        $choice = (string) ($_POST['choice'] ?? '');

        if (!in_array($choice, self::RPS_CHOICES, true)) {
            $_SESSION['rps_last_result'] = ['message' => 'Choix invalide.', 'player' => '-', 'bot' => '-'];
            \redirect('arcade.php?game=rps');
        }

        $bot = self::RPS_CHOICES[random_int(0, 2)];
        $outcome = $this->rpsOutcome($choice, $bot);

        $_SESSION['rps_stats'] ??= ['wins' => 0, 'losses' => 0, 'draws' => 0];
        $_SESSION['rps_stats'][$outcome]++;

        $messages = [
            'wins' => 'Victoire',
            'losses' => 'Defaite',
            'draws' => 'Egalite',
        ];

        $_SESSION['rps_last_result'] = [
            'message' => $messages[$outcome],
            'player' => $this->rpsLabel($choice),
            'bot' => $this->rpsLabel($bot),
        ];

        \redirect('arcade.php?game=rps');
    }

    private function rpsOutcome(string $player, string $bot): string
    {
        if ($player === $bot) {
            return 'draws';
        }

        $wins = [
            'rock' => 'scissors',
            'paper' => 'rock',
            'scissors' => 'paper',
        ];

        return $wins[$player] === $bot ? 'wins' : 'losses';
    }

    private function rpsLabel(string $choice): string
    {
        return [
            'rock' => 'Pierre',
            'paper' => 'Feuille',
            'scissors' => 'Ciseaux',
        ][$choice] ?? '-';
    }
}
