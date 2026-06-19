<?php require BASE_PATH . '/app/Views/partials/header.php'; ?>

<section class="section-heading page-heading">
    <p class="eyebrow">Administration</p>
    <h1>Console JeuxTepique</h1>
</section>

<section class="stats-grid">
    <div class="stat-card"><span>Utilisateurs</span><strong><?= (int) $stats['users'] ?></strong></div>
    <div class="stat-card"><span>Actifs</span><strong><?= (int) $stats['active_users'] ?></strong></div>
    <div class="stat-card"><span>Parties</span><strong><?= (int) $stats['matches'] ?></strong></div>
    <div class="stat-card"><span>Coups</span><strong><?= (int) $stats['moves'] ?></strong></div>
</section>

<section class="admin-grid">
    <div class="glass-panel">
        <div class="section-heading">
            <p class="eyebrow">Comptes</p>
            <h2>Utilisateurs</h2>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Pseudo</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Statut</th>
                        <th>Stats</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $row): ?>
                        <tr>
                            <td>
                                <span class="table-user">
                                    <img src="<?= e(asset('img/' . $row['avatar'])) ?>" alt="" class="avatar-small">
                                    <?= e($row['username']) ?>
                                </span>
                            </td>
                            <td><?= e($row['email']) ?></td>
                            <td><?= e($row['role']) ?></td>
                            <td><?= e($row['status']) ?></td>
                            <td><?= (int) $row['wins'] ?>V / <?= (int) $row['losses'] ?>D / <?= (int) $row['draws'] ?>N</td>
                            <td>
                                <?php if ($row['status'] === 'active' && (int) $row['id'] !== (int) current_user()['id']): ?>
                                    <form method="post" action="<?= e(url('admin.php')) ?>" data-confirm="Desactiver cet utilisateur ?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="disable_user">
                                        <input type="hidden" name="user_id" value="<?= (int) $row['id'] ?>">
                                        <button class="danger-link" type="submit">Desactiver</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="glass-panel">
        <div class="section-heading">
            <p class="eyebrow">Parties</p>
            <h2>Historique</h2>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Jeu</th>
                        <th>Createur</th>
                        <th>Etat</th>
                        <th>Resultat</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($matches as $match): ?>
                        <tr>
                            <td>#<?= (int) $match['id'] ?></td>
                            <td><?= e($match['game_name']) ?></td>
                            <td><?= e($match['creator_username']) ?></td>
                            <td><span class="status status-<?= e($match['status']) ?>"><?= e($match['status']) ?></span></td>
                            <td><?= e($match['result'] ?? 'en cours') ?></td>
                            <td>
                                <form method="post" action="<?= e(url('admin.php')) ?>" data-confirm="Supprimer cette partie ?">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete_match">
                                    <input type="hidden" name="match_id" value="<?= (int) $match['id'] ?>">
                                    <button class="danger-link" type="submit">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php require BASE_PATH . '/app/Views/partials/footer.php'; ?>
