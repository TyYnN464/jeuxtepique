<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\User;
use App\Security\Csrf;
use App\Security\Validator;

final class AuthController extends Controller
{
    public function showRegister(): void
    {
        $this->view('auth/register', [
            'title' => 'Inscription - JeuxTepique',
            'avatars' => \app_config('avatars', []),
            'redirect' => $_GET['redirect'] ?? 'dashboard.php',
        ]);
    }

    public function register(): void
    {
        Csrf::requireValid();

        $username = trim((string) ($_POST['username'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');
        $avatar = (string) ($_POST['avatar'] ?? 'avatar-astronaut.svg');

        $errors = array_filter([
            Validator::username($username),
            Validator::email($email),
            Validator::password($password),
        ]);

        if ($password !== $passwordConfirm) {
            $errors[] = 'Les mots de passe ne correspondent pas.';
        }

        if (!Validator::avatar($avatar)) {
            $errors[] = 'Avatar invalide.';
        }

        if (User::usernameExists($username)) {
            $errors[] = 'Ce pseudo est deja utilise.';
        }

        if (User::emailExists($email)) {
            $errors[] = 'Cette adresse email est deja utilisee.';
        }

        if ($errors !== []) {
            Session::flash('error', implode(' ', $errors));
            $this->redirect('register.php?redirect=' . rawurlencode($this->safeRedirect($_POST['redirect'] ?? 'dashboard.php')));
        }

        $userId = User::create($username, $email, $password, $avatar);
        $user = User::findById($userId);

        if ($user !== null) {
            Auth::login($user);
        }

        Session::flash('success', 'Compte cree. Bienvenue sur JeuxTepique.');
        $this->redirectAfterAuth((string) ($_POST['redirect'] ?? 'dashboard.php'));
    }

    public function showLogin(): void
    {
        $this->view('auth/login', [
            'title' => 'Connexion - JeuxTepique',
            'redirect' => $_GET['redirect'] ?? 'dashboard.php',
        ]);
    }

    public function login(): void
    {
        Csrf::requireValid();

        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $user = User::findByEmail($email);

        if ($user === null || !password_verify($password, (string) $user['password_hash'])) {
            Session::flash('error', 'Identifiants invalides.');
            $this->redirect('login.php?redirect=' . rawurlencode($this->safeRedirect($_POST['redirect'] ?? 'dashboard.php')));
        }

        if ($user['status'] !== 'active') {
            Session::flash('error', 'Ce compte est desactive.');
            $this->redirect('login.php');
        }

        Auth::login($user);
        Session::flash('success', 'Connexion reussie.');
        $this->redirectAfterAuth((string) ($_POST['redirect'] ?? 'dashboard.php'));
    }

    public function logout(): void
    {
        Auth::logout();
        \redirect('index.php');
    }

    private function redirectAfterAuth(string $fallback): void
    {
        if (!empty($_SESSION['pending_invite_token'])) {
            $token = (string) $_SESSION['pending_invite_token'];
            unset($_SESSION['pending_invite_token']);
            $this->redirect('join.php?token=' . rawurlencode($token));
        }

        $this->redirect($this->safeRedirect($fallback));
    }

    private function safeRedirect(mixed $value): string
    {
        $target = trim((string) $value);

        if ($target === '' || str_contains($target, "\n") || str_contains($target, "\r")) {
            return 'dashboard.php';
        }

        if (preg_match('#^https?://#i', $target) === 1 || str_starts_with($target, '//')) {
            return 'dashboard.php';
        }

        return $target;
    }
}
