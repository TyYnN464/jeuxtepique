<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Score;

final class HomeController extends Controller
{
    public function index(): void
    {
        $this->view('home', [
            'title' => 'JeuxTepique - Mini-jeux en ligne',
            'topPlayers' => Score::topByWins(5),
        ]);
    }
}
