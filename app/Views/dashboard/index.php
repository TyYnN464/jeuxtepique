<?php require BASE_PATH . '/app/Views/partials/header.php'; ?>

<section class="dashboard-hero">
    <div>
        <p class="eyebrow">Dashboard joueur</p>
        <h1>Bonjour, <?= e($user['username'] ?? 'joueur') ?></h1>
    </div>
    <div class="player-card glass-panel">
        <img src="<?= e(asset('img/' . ($user['avatar'] ?? 'avatar-astronaut.svg'))) ?>" alt="" class="avatar-large">
        <div>
            <strong><?= e($user['username'] ?? '') ?></strong>
            <span><?= (int) ($user['games_played'] ?? 0) ?> parties jouees</span>
        </div>
    </div>
</section>

<section class="stats-grid">
    <div class="stat-card"><span>Victoires</span><strong><?= (int) ($user['wins'] ?? 0) ?></strong></div>
    <div class="stat-card"><span>Defaites</span><strong><?= (int) ($user['losses'] ?? 0) ?></strong></div>
    <div class="stat-card"><span>Egalites</span><strong><?= (int) ($user['draws'] ?? 0) ?></strong></div>
    <div class="stat-card"><span>Points</span><strong><?= (int) ($user['points'] ?? 0) ?></strong></div>
</section>

<section class="dashboard-grid">
    <div class="glass-panel">
        <div class="section-heading">
            <p class="eyebrow">Jeux</p>
            <h2>Disponibles</h2>
        </div>
        <div class="game-list">
            <?php foreach ($games as $game): ?>
                <article class="game-item">
                    <div>
                        <span class="game-badge"><?= e($game['slug'] === 'tictactoe' ? 'Multijoueur' : 'Solo') ?></span>
                        <h3><?= e($game['name']) ?></h3>
                        <p><?= e($game['description']) ?></p>
                    </div>
                    <div class="game-actions">
                        <?php if ($game['slug'] === 'tictactoe'): ?>
                            <form method="post" action="<?= e(url('create_match.php')) ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="mode" value="solo">
                                <button class="button button-secondary" type="submit">Jouer contre la machine</button>
                            </form>
                            <form method="post" action="<?= e(url('create_match.php')) ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="mode" value="multi">
                                <button class="button button-primary" type="submit">Inviter un ami</button>
                            </form>
                        <?php else: ?>
                            <a class="button button-primary" href="<?= e(url('arcade.php?game=' . rawurlencode($game['slug']))) ?>">Jouer</a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>

    <aside class="glass-panel">
        <div class="section-heading">
            <p class="eyebrow">Classement</p>
            <h2>Global</h2>
        </div>
        <div class="compact-list">
            <?php foreach ($leaders as $leader): ?>
                <div class="compact-row">
                    <img src="<?= e(asset('img/' . $leader['avatar'])) ?>" alt="" class="avatar-small">
                    <span><?= e($leader['username']) ?></span>
                    <strong><?= (int) $leader['wins'] ?> V</strong>
                </div>
            <?php endforeach; ?>
        </div>
        <a class="text-link" href="<?= e(url('leaderboard.php')) ?>">Voir tous les classements</a>
    </aside>
</section>

<section class="glass-panel">
    <div class="section-heading">
        <p class="eyebrow">Historique</p>
        <h2>Dernieres parties</h2>
    </div>
    <?php if ($matches === []): ?>
        <p class="muted">Aucune partie pour le moment.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Jeu</th>
                        <th>Mode</th>
                        <th>Etat</th>
                        <th>Resultat</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($matches as $match): ?>
                        <tr>
                            <td><?= e($match['game_name']) ?></td>
                            <td><?= e($match['mode']) ?></td>
                            <td><span class="status status-<?= e($match['status']) ?>"><?= e($match['status']) ?></span></td>
                            <td><?= e($match['result'] ?? 'en cours') ?></td>
                            <td><a class="text-link" href="<?= e(url('match.php?id=' . $match['id'])) ?>">Ouvrir</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
