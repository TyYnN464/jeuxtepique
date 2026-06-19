<?php require BASE_PATH . '/app/Views/partials/header.php'; ?>

<section class="match-header">
    <div>
        <p class="eyebrow">Mini-jeu solo</p>
        <h1>Pierre Feuille Ciseaux</h1>
        <p class="muted">Choisissez un signe, la machine repond instantanement.</p>
    </div>
    <a class="button button-secondary" href="<?= e(url('dashboard.php')) ?>">Retour dashboard</a>
</section>

<section class="arcade-grid">
    <div class="glass-panel">
        <div class="rps-actions">
            <?php foreach (['rock' => 'Pierre', 'paper' => 'Feuille', 'scissors' => 'Ciseaux'] as $choice => $label): ?>
                <form method="post" action="<?= e(url('arcade.php?game=rps')) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="game" value="rps">
                    <input type="hidden" name="choice" value="<?= e($choice) ?>">
                    <button class="rps-button" type="submit"><?= e($label) ?></button>
                </form>
            <?php endforeach; ?>
        </div>

        <?php if ($result !== null): ?>
            <div class="result-banner arcade-result">
                <?= e($result['message']) ?> - Vous: <?= e($result['player']) ?> / Machine: <?= e($result['bot']) ?>
            </div>
        <?php endif; ?>
    </div>

    <aside class="glass-panel">
        <div class="section-heading">
            <p class="eyebrow">Session</p>
            <h2>Score rapide</h2>
        </div>
        <div class="stats-grid compact-stats">
            <div class="stat-card"><span>Victoires</span><strong><?= (int) $stats['wins'] ?></strong></div>
            <div class="stat-card"><span>Defaites</span><strong><?= (int) $stats['losses'] ?></strong></div>
            <div class="stat-card"><span>Egalites</span><strong><?= (int) $stats['draws'] ?></strong></div>
        </div>
    </aside>
</section>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
