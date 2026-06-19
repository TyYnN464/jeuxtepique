<?php require BASE_PATH . '/app/Views/partials/header.php'; ?>

<section class="section-heading page-heading">
    <p class="eyebrow">Classements</p>
    <h1>Performance globale</h1>
</section>

<section class="leaderboard-grid">
    <div class="glass-panel">
        <h2>Par victoires</h2>
        <?php $rows = $byWins; require BASE_PATH . '/app/Views/leaderboard/table.php'; ?>
    </div>

    <div class="glass-panel">
        <h2>Par ratio</h2>
        <?php $rows = $byRatio; require BASE_PATH . '/app/Views/leaderboard/table.php'; ?>
    </div>

    <div class="glass-panel">
        <h2>Par parties jouees</h2>
        <?php $rows = $byGames; require BASE_PATH . '/app/Views/leaderboard/table.php'; ?>
    </div>
</section>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
