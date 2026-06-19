<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Game;
use App\Models\MatchModel;
use App\Models\Score;
use App\Models\User;

final class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();

        $userId = (int) Auth::id();

        $this->view('dashboard/index', [
            'title' => 'Tableau de bord - JeuxTepique',
            'user' => User::findById($userId),
            'games' => Game::allActive(),
            'matches' => MatchModel::listForUser($userId, 8),
            'leaders' => Score::topByWins(5),
        ]);
    }
}
