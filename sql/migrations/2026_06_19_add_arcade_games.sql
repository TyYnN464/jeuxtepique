USE jeuxtepique;

INSERT INTO games (slug, name, description, is_active, sort_order) VALUES
('rps', 'Pierre Feuille Ciseaux', 'Mini-duel instantane contre la machine.', 1, 20),
('memory', 'Memory spatial', 'Retrouvez toutes les paires dans une grille galactique.', 1, 30)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    is_active = VALUES(is_active),
    sort_order = VALUES(sort_order),
    updated_at = NOW();
