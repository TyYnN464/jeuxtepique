<?php require BASE_PATH . '/app/Views/partials/header.php'; ?>

<section class="hero">
    <div class="hero-copy">
        <p class="eyebrow">Mini-jeux en ligne</p>
        <h1>JeuxTepique</h1>
        <p class="hero-text">Morpion, duels prives, avatars galactiques et classements dans une interface sombre, fluide et precise.</p>
        <div class="hero-actions">
            <?php if (current_user()): ?>
                <a class="button button-primary" href="<?= e(url('dashboard.php')) ?>">Ouvrir le dashboard</a>
            <?php else: ?>
                <a class="button button-primary" href="<?= e(url('register.php')) ?>">Creer un compte</a>
                <a class="button button-secondary" href="<?= e(url('login.php')) ?>">Connexion</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="hero-visual glass-panel">
        <div class="mini-board" aria-hidden="true">
            <span>X</span><span>O</span><span>X</span>
            <span>O</span><span>X</span><span></span>
            <span></span><span>O</span><span>X</span>
        </div>
        <div class="hero-stats">
            <div>
                <strong>Solo IA</strong>
                <span>morpion tactique</span>
            </div>
            <div>
                <strong>Invitation</strong>
                <span>lien prive</span>
            </div>
            <div>
                <strong>Classement</strong>
                <span>victoires et ratio</span>
            </div>
        </div>
    </div>
</section>

<section class="section-grid">
    <article class="feature-card">
        <img src="<?= e(asset('img/avatar-rocket.svg')) ?>" alt="" class="feature-icon">
        <h2>Parties rapides</h2>
        <p>Lancez un morpion contre la machine ou creez une partie privee en quelques secondes.</p>
    </article>
    <article class="feature-card">
        <img src="<?= e(asset('img/avatar-planet.svg')) ?>" alt="" class="feature-icon">
        <h2>Profil joueur</h2>
        <p>Pseudo unique, avatar, historique et statistiques persistantes par compte.</p>
    </article>
    <article class="feature-card">
        <img src="<?= e(asset('img/avatar-star.svg')) ?>" alt="" class="feature-icon">
        <h2>Classements</h2>
        <p>Comparez les joueurs par victoires, ratio victoire/defaite et volume de parties.</p>
    </article>
</section>

<?php if ($topPlayers !== []): ?>
    <section class="glass-panel leaderboard-preview">
        <div class="section-heading">
            <p class="eyebrow">Top joueurs</p>
            <h2>En tete de la galaxie</h2>
        </div>
        <div class="compact-list">
            <?php foreach ($topPlayers as $player): ?>
                <div class="compact-row">
                    <img src="<?= e(asset('img/' . $player['avatar'])) ?>" alt="" class="avatar-small">
                    <span><?= e($player['username']) ?></span>
                    <strong><?= (int) $player['wins'] ?> victoires</strong>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
