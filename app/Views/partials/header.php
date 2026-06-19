<?php

$authUser = current_user();
$pageTitle = $title ?? app_config('app.name');
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="JeuxTepique, plateforme de mini-jeux en ligne avec morpion, invitations et classements.">
    <title><?= e($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <script defer src="<?= e(asset('js/app.js')) ?>"></script>
</head>
<body>
    <div class="starfield" aria-hidden="true"></div>
    <header class="site-header">
        <a class="brand" href="<?= e(url('index.php')) ?>" aria-label="Accueil JeuxTepique">
            <span class="brand-mark">J</span>
            <span>JeuxTepique</span>
        </a>
        <nav class="nav-links" aria-label="Navigation principale">
            <a href="<?= e(url('leaderboard.php')) ?>">Classements</a>
            <?php if ($authUser !== null): ?>
                <a href="<?= e(url('dashboard.php')) ?>">Dashboard</a>
                <a href="<?= e(url('profile.php')) ?>">Profil</a>
                <?php if ($authUser['role'] === 'admin'): ?>
                    <a href="<?= e(url('admin.php')) ?>">Admin</a>
                <?php endif; ?>
                <a class="nav-pill" href="<?= e(url('logout.php')) ?>">Deconnexion</a>
            <?php else: ?>
                <a href="<?= e(url('login.php')) ?>">Connexion</a>
                <a class="nav-pill" href="<?= e(url('register.php')) ?>">Inscription</a>
            <?php endif; ?>
        </nav>
    </header>
    <main class="page-shell">
        <?php require BASE_PATH . '/app/Views/partials/flash.php'; ?>
