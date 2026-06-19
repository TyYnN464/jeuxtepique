<?php require BASE_PATH . '/app/Views/partials/header.php'; ?>

<section class="match-header">
    <div>
        <p class="eyebrow">Mini-jeu solo</p>
        <h1>Puissance 4</h1>
        <p class="muted">Alignez quatre jetons avant la machine. Les jetons tombent dans la colonne choisie.</p>
    </div>
    <a class="button button-secondary" href="<?= e(url('dashboard.php')) ?>">Retour dashboard</a>
</section>

<section class="arcade-grid">
    <div class="glass-panel">
        <div class="connect4-toolbar">
            <div>
                <span class="muted">Tour</span>
                <strong id="connect4-turn">Joueur</strong>
            </div>
            <div>
                <span class="muted">Coups</span>
                <strong id="connect4-moves">0</strong>
            </div>
            <button class="button button-secondary" type="button" id="connect4-reset">Recommencer</button>
        </div>

        <div class="connect4-columns" id="connect4-columns" aria-label="Colonnes Puissance 4"></div>
        <div class="connect4-board" id="connect4-board" aria-label="Plateau Puissance 4"></div>
        <div class="result-banner arcade-result" id="connect4-result" hidden>Partie terminee</div>
    </div>

    <aside class="glass-panel">
        <div class="section-heading">
            <p class="eyebrow">Regle</p>
            <h2>Aligner 4</h2>
        </div>
        <p class="muted">Le joueur violet commence. La machine joue en bleu juste apres votre coup. Le premier a aligner quatre jetons horizontalement, verticalement ou en diagonale gagne.</p>
    </aside>
</section>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
