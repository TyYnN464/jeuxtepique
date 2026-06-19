<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\MatchModel;
use App\Security\Csrf;
use Games\tictactoe\TicTacToe;
use RuntimeException;

final class GameController extends Controller
{
    public function create(): void
    {
        Auth::requireLogin();
        Csrf::requireValid();

        $mode = (string) ($_POST['mode'] ?? '');

        try {
            if ($mode === 'solo') {
                $matchId = MatchModel::createSolo((int) Auth::id());
                Session::flash('success', 'Partie contre la machine creee.');
                $this->redirect('match.php?id=' . $matchId);
            }

            if ($mode === 'multi') {
                $data = MatchModel::createPrivate((int) Auth::id());
                Session::flash('success', 'Partie privee creee. Envoyez le lien a votre ami.');
                $this->redirect('match.php?id=' . $data['match_id']);
            }
        } catch (RuntimeException $exception) {
            Session::flash('error', $exception->getMessage());
        }

        $this->redirect('dashboard.php');
    }

    public function show(): void
    {
        Auth::requireLogin();

        $matchId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$matchId) {
            Session::flash('error', 'Partie invalide.');
            $this->redirect('dashboard.php');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->play((int) $matchId);
        }

        $match = MatchModel::find((int) $matchId);
        if ($match === null) {
            Session::flash('error', 'Partie introuvable.');
            $this->redirect('dashboard.php');
        }

        if (!MatchModel::isPlayer((int) $matchId, (int) Auth::id())) {
            Session::flash('error', 'Vous ne participez pas a cette partie.');
            $this->redirect('dashboard.php');
        }

        $players = MatchModel::players((int) $matchId);
        $moves = MatchModel::moves((int) $matchId);
        $userSymbol = null;

        foreach ($players as $player) {
            if ((int) ($player['user_id'] ?? 0) === (int) Auth::id()) {
                $userSymbol = $player['symbol'];
                break;
            }
        }

        $this->view('games/tictactoe/match', [
            'title' => 'Morpion - JeuxTepique',
            'match' => $match,
            'players' => $players,
            'moves' => $moves,
            'board' => TicTacToe::boardArray((string) $match['board_state']),
            'userSymbol' => $userSymbol,
            'inviteUrl' => $match['invitation_token'] ? \full_url('join.php?token=' . $match['invitation_token']) : null,
        ]);
    }

    private function play(int $matchId): void
    {
        Csrf::requireValid();

        $position = filter_input(INPUT_POST, 'position', FILTER_VALIDATE_INT);

        try {
            if ($position === false || $position === null) {
                throw new RuntimeException('Coup invalide.');
            }

            MatchModel::recordMove($matchId, (int) Auth::id(), (int) $position);
            Session::flash('success', 'Coup joue.');
        } catch (\Throwable $exception) {
            Session::flash('error', $exception->getMessage());
        }

        $this->redirect('match.php?id=' . $matchId);
    }
}
