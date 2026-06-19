<?php require BASE_PATH . '/app/Views/partials/header.php'; ?>

<section class="profile-grid">
    <div class="glass-panel profile-summary">
        <img src="<?= e(asset('img/' . $user['avatar'])) ?>" alt="" class="avatar-xl">
        <h1><?= e($user['username']) ?></h1>
        <p class="muted">Compte cree le <?= e(date('d/m/Y', strtotime((string) $user['created_at']))) ?></p>
        <div class="stats-grid compact-stats">
            <div class="stat-card"><span>Parties</span><strong><?= (int) $user['games_played'] ?></strong></div>
            <div class="stat-card"><span>Victoires</span><strong><?= (int) $user['wins'] ?></strong></div>
            <div class="stat-card"><span>Defaites</span><strong><?= (int) $user['losses'] ?></strong></div>
            <div class="stat-card"><span>Egalites</span><strong><?= (int) $user['draws'] ?></strong></div>
        </div>
    </div>

    <form class="glass-panel form-panel" method="post" action="<?= e(url('profile.php')) ?>">
        <?= csrf_field() ?>
        <div class="section-heading">
            <p class="eyebrow">Profil</p>
            <h2>Personnalisation</h2>
        </div>

        <label for="username">Pseudo</label>
        <input id="username" name="username" type="text" value="<?= e($user['username']) ?>" required minlength="3" maxlength="24" pattern="[a-zA-Z0-9_\-]+">

        <fieldset class="avatar-picker">
            <legend>Avatar</legend>
            <?php foreach ($avatars as $file => $label): ?>
                <label>
                    <input type="radio" name="avatar" value="<?= e($file) ?>" <?= $file === $user['avatar'] ? 'checked' : '' ?>>
                    <img src="<?= e(asset('img/' . $file)) ?>" alt="<?= e($label) ?>">
                    <span><?= e($label) ?></span>
                </label>
            <?php endforeach; ?>
        </fieldset>

        <button class="button button-primary" type="submit">Enregistrer</button>
    </form>
</section>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
