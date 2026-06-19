<?php

$user = current_user();
$isMyTurn = $match['status'] === 'active' && (int) ($match['current_turn_user_id'] ?? 0) === (int) ($user['id'] ?? 0);
$isWaiting = $match['status'] === 'waiting';
$isFinished = $match['status'] === 'finished';
?>
<?php require BASE_PATH . '/app/Views/partials/header.php'; ?>

<section class="match-header">
    <div>
        <p class="eyebrow">Morpion</p>
        <h1>Partie #<?= (int) $match['id'] ?></h1>
        <p class="muted">Mode <?= e($match['mode']) ?> - <span class="status status-<?= e($match['status']) ?>"><?= e($match['status']) ?></span></p>
    </div>
    <a class="button button-secondary" href="<?= e(url('dashboard.php')) ?>">Retour dashboard</a>
</section>

<?php if ($isWaiting && $inviteUrl !== null): ?>
    <section class="glass-panel invite-panel">
        <div>
            <p class="eyebrow">Invitation privee</p>
            <h2>Envoyer le lien a votre ami</h2>
        </div>
        <div class="copy-field">
            <input id="invite-url" type="text" value="<?= e($inviteUrl) ?>" readonly>
            <button class="button button-primary" type="button" data-copy="#invite-url">Copier</button>
        </div>
    </section>
<?php endif; ?>

<section class="match-grid">
    <div class="glass-panel board-panel">
        <?php if ($isFinished): ?>
            <div class="result-banner">
                <?php if ($match['result'] === 'draw'): ?>
                    Egalite
                <?php elseif ($match['winner_user_id'] !== null): ?>
                    Victoire de <?= e($match['winner_username']) ?>
                <?php else: ?>
                    Victoire de la machine
                <?php endif; ?>
            </div>
        <?php elseif ($isWaiting): ?>
            <div class="result-banner">En attente d'un deuxieme joueur</div>
        <?php else: ?>
            <div class="result-banner"><?= $isMyTurn ? 'A votre tour' : 'Tour adverse' ?></div>
        <?php endif; ?>

        <div class="tic-board" role="grid" aria-label="Plateau de morpion">
            <?php foreach ($board as $index => $cell): ?>
                <?php if ($cell === '-' && $isMyTurn): ?>
                    <form method="post" action="<?= e(url('match.php?id=' . $match['id'])) ?>" class="tic-cell-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="position" value="<?= (int) $index ?>">
                        <button class="tic-cell" type="submit" aria-label="Jouer case <?= (int) $index + 1 ?>"></button>
                    </form>
                <?php else: ?>
                    <div class="tic-cell tic-cell-static <?= $cell !== '-' ? 'is-filled' : '' ?>" role="gridcell">
                        <?= $cell === '-' ? '' : e($cell) ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <aside class="glass-panel">
        <div class="section-heading">
            <p class="eyebrow">Joueurs</p>
            <h2>Symboles</h2>
        </div>
        <div class="players-list">
            <?php foreach ($players as $player): ?>
                <div class="player-line">
                    <img src="<?= e(asset('img/' . ((int) $player['is_bot'] === 1 ? 'avatar-gamepad.svg' : $player['avatar']))) ?>" alt="" class="avatar-small">
                    <div>
                        <strong><?= (int) $player['is_bot'] === 1 ? 'Machine' : e($player['username']) ?></strong>
                        <span><?= e($player['symbol']) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="section-heading moves-heading">
            <p class="eyebrow">Coups</p>
            <h2>Historique</h2>
        </div>
        <?php if ($moves === []): ?>
            <p class="muted">Aucun coup joue.</p>
        <?php else: ?>
            <ol class="moves-list">
                <?php foreach ($moves as $move): ?>
                    <li>
                        <span><?= e($move['symbol']) ?> en case <?= (int) $move['position'] + 1 ?></span>
                        <strong><?= $move['username'] ? e($move['username']) : 'Machine' ?></strong>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </aside>
</section>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
