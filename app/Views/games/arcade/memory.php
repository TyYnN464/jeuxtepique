<?php require BASE_PATH . '/app/Views/partials/header.php'; ?>

<section class="match-header">
    <div>
        <p class="eyebrow">Mini-jeu solo</p>
        <h1>Memory spatial</h1>
        <p class="muted">Retrouvez toutes les paires avec le moins de coups possible.</p>
    </div>
    <a class="button button-secondary" href="<?= e(url('dashboard.php')) ?>">Retour dashboard</a>
</section>

<section class="arcade-grid">
    <div class="glass-panel">
        <div class="memory-toolbar">
            <div><span class="muted">Coups</span><strong id="memory-moves">0</strong></div>
            <div><span class="muted">Paires</span><strong id="memory-pairs">0/6</strong></div>
            <button class="button button-secondary" type="button" id="memory-reset">Recommencer</button>
        </div>
        <div class="memory-board" id="memory-board" aria-label="Plateau memory"></div>
        <div class="result-banner arcade-result" id="memory-result" hidden>Partie terminee</div>
    </div>

    <aside class="glass-panel">
        <div class="section-heading">
            <p class="eyebrow">Objectif</p>
            <h2>Concentration</h2>
        </div>
        <p class="muted">Le jeu tourne dans le navigateur pour une demo rapide. Les prochains jeux peuvent reprendre la meme structure que le morpion pour persister les scores en base.</p>
    </aside>
</section>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
