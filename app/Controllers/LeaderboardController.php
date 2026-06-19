<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Score;

final class LeaderboardController extends Controller
{
    public function index(): void
    {
        $this->view('leaderboard/index', [
            'title' => 'Classements - JeuxTepique',
            'byWins' => Score::topByWins(25),
            'byRatio' => Score::topByRatio(25),
            'byGames' => Score::topByGames(25),
        ]);
    }
}
