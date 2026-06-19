<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\Invitation;
use App\Models\MatchModel;

final class InvitationController extends Controller
{
    public function join(): void
    {
        $token = (string) ($_GET['token'] ?? '');

        if (preg_match('/^[a-f0-9]{32}$/', $token) !== 1) {
            Session::flash('error', 'Lien d invitation invalide.');
            $this->redirect('index.php');
        }

        $invitation = Invitation::findByToken($token);
        if ($invitation === null) {
            Session::flash('error', 'Invitation introuvable.');
            $this->redirect('index.php');
        }

        if (!Auth::check()) {
            $_SESSION['pending_invite_token'] = $token;
            Session::flash('success', 'Connectez-vous ou creez un compte pour rejoindre la partie.');
            $this->redirect('login.php?redirect=' . rawurlencode('join.php?token=' . $token));
        }

        try {
            $matchId = MatchModel::joinByToken($token, (int) Auth::id());
            Session::flash('success', 'Vous avez rejoint la partie.');
            $this->redirect('match.php?id=' . $matchId);
        } catch (\Throwable $exception) {
            Session::flash('error', $exception->getMessage());
            $this->redirect('dashboard.php');
        }
    }
}
