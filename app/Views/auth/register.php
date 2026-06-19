<?php require BASE_PATH . '/app/Views/partials/header.php'; ?>

<section class="auth-layout">
    <div>
        <p class="eyebrow">Inscription</p>
        <h1>Creer un profil joueur</h1>
        <p class="muted">Un pseudo unique, un avatar et des statistiques qui vous suivent sur chaque partie.</p>
    </div>

    <form class="glass-panel form-panel" method="post" action="<?= e(url('register.php')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="redirect" value="<?= e($redirect ?? 'dashboard.php') ?>">

        <label for="username">Pseudo</label>
        <input id="username" name="username" type="text" required minlength="3" maxlength="24" pattern="[a-zA-Z0-9_\-]+">

        <label for="email">Email</label>
        <input id="email" name="email" type="email" required maxlength="190" autocomplete="email">

        <label for="password">Mot de passe</label>
        <input id="password" name="password" type="password" required minlength="10" autocomplete="new-password">

        <label for="password_confirm">Confirmation</label>
        <input id="password_confirm" name="password_confirm" type="password" required minlength="10" autocomplete="new-password">

        <fieldset class="avatar-picker">
            <legend>Avatar</legend>
            <?php foreach ($avatars as $file => $label): ?>
                <label>
                    <input type="radio" name="avatar" value="<?= e($file) ?>" <?= $file === 'avatar-astronaut.svg' ? 'checked' : '' ?>>
                    <img src="<?= e(asset('img/' . $file)) ?>" alt="<?= e($label) ?>">
                    <span><?= e($label) ?></span>
                </label>
            <?php endforeach; ?>
        </fieldset>

        <button class="button button-primary" type="submit">Creer le compte</button>
        <p class="form-note">Deja inscrit ? <a href="<?= e(url('login.php?redirect=' . rawurlencode($redirect ?? 'dashboard.php'))) ?>">Connexion</a></p>
    </form>
</section>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
