<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\User;
use App\Security\Csrf;
use App\Security\Validator;

final class UserController extends Controller
{
    public function profile(): void
    {
        Auth::requireLogin();

        $this->view('profile/show', [
            'title' => 'Profil - JeuxTepique',
            'user' => User::findById((int) Auth::id()),
            'avatars' => \app_config('avatars', []),
        ]);
    }

    public function update(): void
    {
        Auth::requireLogin();
        Csrf::requireValid();

        $userId = (int) Auth::id();
        $username = trim((string) ($_POST['username'] ?? ''));
        $avatar = (string) ($_POST['avatar'] ?? 'avatar-astronaut.svg');

        $errors = array_filter([Validator::username($username)]);

        if (!Validator::avatar($avatar)) {
            $errors[] = 'Avatar invalide.';
        }

        if (User::usernameExists($username, $userId)) {
            $errors[] = 'Ce pseudo est deja utilise.';
        }

        if ($errors !== []) {
            Session::flash('error', implode(' ', $errors));
            $this->redirect('profile.php');
        }

        User::updateProfile($userId, $username, $avatar);
        Session::flash('success', 'Profil mis a jour.');
        $this->redirect('profile.php');
    }
}
