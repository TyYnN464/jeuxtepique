CREATE DATABASE IF NOT EXISTS jeuxtepique
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE jeuxtepique;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS remember_tokens;
DROP TABLE IF EXISTS moves;
DROP TABLE IF EXISTS invitations;
DROP TABLE IF EXISTS match_players;
DROP TABLE IF EXISTS matches;
DROP TABLE IF EXISTS scores;
DROP TABLE IF EXISTS games;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(24) NOT NULL,
    email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    avatar VARCHAR(80) NOT NULL DEFAULT 'avatar-astronaut.svg',
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    status ENUM('active', 'disabled') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY users_username_unique (username),
    UNIQUE KEY users_email_unique (email),
    KEY users_role_status_index (role, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE games (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(80) NOT NULL,
    name VARCHAR(120) NOT NULL,
    description VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT UNSIGNED NOT NULL DEFAULT 100,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY games_slug_unique (slug),
    KEY games_active_sort_index (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE scores (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    games_played INT UNSIGNED NOT NULL DEFAULT 0,
    wins INT UNSIGNED NOT NULL DEFAULT 0,
    losses INT UNSIGNED NOT NULL DEFAULT 0,
    draws INT UNSIGNED NOT NULL DEFAULT 0,
    points INT UNSIGNED NOT NULL DEFAULT 0,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY scores_user_unique (user_id),
    KEY scores_wins_index (wins),
    KEY scores_games_played_index (games_played),
    CONSTRAINT scores_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE matches (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    game_id INT UNSIGNED NOT NULL,
    status ENUM('waiting', 'active', 'finished') NOT NULL DEFAULT 'waiting',
    mode ENUM('solo', 'multi') NOT NULL DEFAULT 'multi',
    created_by_user_id INT UNSIGNED NOT NULL,
    current_turn_user_id INT UNSIGNED NULL,
    winner_user_id INT UNSIGNED NULL,
    result ENUM('x_win', 'o_win', 'draw') NULL,
    board_state CHAR(9) NOT NULL DEFAULT '---------',
    invitation_token CHAR(32) NULL,
    started_at DATETIME NULL,
    ended_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY matches_invitation_token_unique (invitation_token),
    KEY matches_status_index (status),
    KEY matches_game_status_index (game_id, status),
    KEY matches_created_by_index (created_by_user_id),
    CONSTRAINT matches_board_state_check CHECK (board_state REGEXP '^[XO-]{9}$'),
    CONSTRAINT matches_game_fk FOREIGN KEY (game_id) REFERENCES games (id),
    CONSTRAINT matches_creator_fk FOREIGN KEY (created_by_user_id) REFERENCES users (id),
    CONSTRAINT matches_current_turn_fk FOREIGN KEY (current_turn_user_id) REFERENCES users (id) ON DELETE SET NULL,
    CONSTRAINT matches_winner_fk FOREIGN KEY (winner_user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE match_players (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    match_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NULL,
    symbol ENUM('X', 'O') NOT NULL,
    is_bot TINYINT(1) NOT NULL DEFAULT 0,
    joined_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY match_players_match_user_unique (match_id, user_id),
    UNIQUE KEY match_players_match_symbol_unique (match_id, symbol),
    KEY match_players_user_index (user_id),
    CONSTRAINT match_players_match_fk FOREIGN KEY (match_id) REFERENCES matches (id) ON DELETE CASCADE,
    CONSTRAINT match_players_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE invitations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    match_id INT UNSIGNED NOT NULL,
    token CHAR(32) NOT NULL,
    created_by_user_id INT UNSIGNED NOT NULL,
    invited_user_id INT UNSIGNED NULL,
    status ENUM('pending', 'accepted', 'expired', 'revoked') NOT NULL DEFAULT 'pending',
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    accepted_at DATETIME NULL,
    UNIQUE KEY invitations_token_unique (token),
    KEY invitations_status_index (status),
    CONSTRAINT invitations_match_fk FOREIGN KEY (match_id) REFERENCES matches (id) ON DELETE CASCADE,
    CONSTRAINT invitations_creator_fk FOREIGN KEY (created_by_user_id) REFERENCES users (id),
    CONSTRAINT invitations_invited_fk FOREIGN KEY (invited_user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE moves (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    match_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NULL,
    position TINYINT UNSIGNED NOT NULL,
    symbol ENUM('X', 'O') NOT NULL,
    move_number TINYINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY moves_match_position_unique (match_id, position),
    UNIQUE KEY moves_match_number_unique (match_id, move_number),
    KEY moves_user_index (user_id),
    CONSTRAINT moves_position_check CHECK (position BETWEEN 0 AND 8),
    CONSTRAINT moves_match_fk FOREIGN KEY (match_id) REFERENCES matches (id) ON DELETE CASCADE,
    CONSTRAINT moves_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE remember_tokens (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    selector CHAR(24) NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY remember_tokens_selector_unique (selector),
    KEY remember_tokens_user_index (user_id),
    CONSTRAINT remember_tokens_user_fk FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO games (slug, name, description, is_active, sort_order) VALUES
('tictactoe', 'Morpion', 'Duel 3x3 en solo contre la machine ou en multijoueur prive.', 1, 10),
('rps', 'Pierre Feuille Ciseaux', 'Mini-duel instantane contre la machine.', 1, 20),
('memory', 'Memory spatial', 'Retrouvez toutes les paires dans une grille galactique.', 1, 30),
('connect4', 'Puissance 4', 'Alignez quatre jetons dans une grille verticale.', 0, 40),
('quiz-duel', 'Quiz duel', 'Affrontez un ami sur des questions chronometrees.', 0, 50);
