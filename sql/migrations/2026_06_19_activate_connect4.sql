USE jeuxtepique;

INSERT INTO games (slug, name, description, is_active, sort_order) VALUES
('connect4', 'Puissance 4', 'Alignez quatre jetons dans une grille verticale.', 1, 40)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    is_active = VALUES(is_active),
    sort_order = VALUES(sort_order),
    updated_at = NOW();
