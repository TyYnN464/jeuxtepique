<?php if ($rows === []): ?>
    <p class="muted">Aucun score disponible.</p>
<?php else: ?>
    <div class="leaderboard-list">
        <?php foreach ($rows as $index => $row): ?>
            <div class="leaderboard-row">
                <span class="rank"><?= $index + 1 ?></span>
                <img src="<?= e(asset('img/' . $row['avatar'])) ?>" alt="" class="avatar-small">
                <strong><?= e($row['username']) ?></strong>
                <span><?= (int) $row['wins'] ?>V</span>
                <span><?= (int) $row['losses'] ?>D</span>
                <span><?= (int) $row['draws'] ?>N</span>
                <span><?= e($row['win_loss_ratio']) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
