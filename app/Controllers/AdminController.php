<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\MatchModel;
use App\Models\User;
use App\Security\Csrf;

final class AdminController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleAction();
        }

        $this->view('admin/index', [
            'title' => 'Administration - JeuxTepique',
            'users' => User::allWithScores(100),
            'matches' => MatchModel::allForAdmin(100),
            'stats' => array_merge(MatchModel::stats(), [
                'users' => User::countAll(),
                'active_users' => User::countActive(),
            ]),
        ]);
    }

    private function handleAction(): void
    {
        Csrf::requireValid();

        $action = (string) ($_POST['action'] ?? '');

        if ($action === 'disable_user') {
            $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

            if ($userId && (int) $userId !== (int) Auth::id()) {
                User::deactivate((int) $userId);
                Session::flash('success', 'Utilisateur desactive.');
            } else {
                Session::flash('error', 'Action impossible sur ce compte.');
            }

            $this->redirect('admin.php');
        }

        if ($action === 'delete_match') {
            $matchId = filter_input(INPUT_POST, 'match_id', FILTER_VALIDATE_INT);

            if ($matchId) {
                MatchModel::delete((int) $matchId);
                Session::flash('success', 'Partie supprimee.');
            }

            $this->redirect('admin.php');
        }

        Session::flash('error', 'Action inconnue.');
        $this->redirect('admin.php');
    }
}
