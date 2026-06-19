<?php require BASE_PATH . '/app/Views/partials/header.php'; ?>

<section class="auth-layout">
    <div>
        <p class="eyebrow">Connexion</p>
        <h1>Retour dans l'arene</h1>
        <p class="muted">Connectez-vous pour reprendre vos parties, rejoindre une invitation ou suivre votre classement.</p>
    </div>

    <form class="glass-panel form-panel" method="post" action="<?= e(url('login.php')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="redirect" value="<?= e($redirect ?? 'dashboard.php') ?>">

        <label for="email">Email</label>
        <input id="email" name="email" type="email" autocomplete="email" required maxlength="190">

        <label for="password">Mot de passe</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>

        <button class="button button-primary" type="submit">Se connecter</button>
        <p class="form-note">Pas encore de compte ? <a href="<?= e(url('register.php?redirect=' . rawurlencode($redirect ?? 'dashboard.php'))) ?>">Inscription</a></p>
    </form>
</section>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
