-- ============================================================
-- The Halley Project — Forum Database Schema
-- Execute: mysql -u root -p < schema.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS halley_forum
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE halley_forum;

-- ── Usuários ─────────────────────────────────────────────────
CREATE TABLE users (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(30)  NOT NULL UNIQUE,
    email       VARCHAR(255) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,           -- password_hash()
    role        ENUM('user','editor','admin') DEFAULT 'user',
    bio         VARCHAR(500) DEFAULT NULL,
    avatar_url  VARCHAR(255) DEFAULT NULL,
    language    ENUM('en','pt-BR') DEFAULT 'en',
    is_active   TINYINT(1)  DEFAULT 1,
    created_at  DATETIME    DEFAULT CURRENT_TIMESTAMP,
    last_login  DATETIME    DEFAULT NULL,

    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB;

-- ── Categorias ───────────────────────────────────────────────
CREATE TABLE categories (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name_en     VARCHAR(100) NOT NULL,
    name_pt     VARCHAR(100) NOT NULL,
    slug        VARCHAR(100) NOT NULL UNIQUE,
    description_en VARCHAR(255) DEFAULT NULL,
    description_pt VARCHAR(255) DEFAULT NULL,
    sort_order  INT DEFAULT 0,
    icon        VARCHAR(10) DEFAULT '📄'
) ENGINE=InnoDB;

-- Categorias iniciais
INSERT INTO categories (name_en, name_pt, slug, description_en, description_pt, sort_order, icon) VALUES
('Halley''s Comet',    'Cometa Halley',        'halley',       'Articles focused on Halley''s Comet',              'Artigos focados no Cometa Halley',                 1, '☄️'),
('Observation',         'Observação',           'observation',  'Observation reports and techniques',               'Relatórios e técnicas de observação',              2, '🔭'),
('History',             'História',             'history',      'Historical records and cultural impact',           'Registros históricos e impacto cultural',          3, '📜'),
('Astrophysics',        'Astrofísica',          'astrophysics', 'Scientific analysis and research',                 'Análise científica e pesquisa',                    4, '⚛️'),
('General Astronomy',   'Astronomia Geral',     'astronomy',    'Broader astronomical topics',                      'Temas astronômicos em geral',                      5, '🌌'),
('Community',           'Comunidade',           'community',    'Discussions, introductions, and meta topics',      'Discussões, apresentações e meta tópicos',         6, '👥');

-- ── Artigos ──────────────────────────────────────────────────
CREATE TABLE articles (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id      INT UNSIGNED NOT NULL,
    category_id  INT UNSIGNED NOT NULL,
    title        VARCHAR(200) NOT NULL,
    slug         VARCHAR(220) NOT NULL UNIQUE,
    excerpt      VARCHAR(500) DEFAULT NULL,
    body         TEXT NOT NULL,                    -- Markdown ou HTML
    language     ENUM('en','pt-BR') DEFAULT 'en',
    status       ENUM('draft','review','published','rejected') DEFAULT 'draft',
    views        INT UNSIGNED DEFAULT 0,
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    published_at DATETIME DEFAULT NULL,

    FOREIGN KEY (user_id)     REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id),

    INDEX idx_status    (status),
    INDEX idx_slug      (slug),
    INDEX idx_published (published_at DESC),
    INDEX idx_category  (category_id, status, published_at DESC)
) ENGINE=InnoDB;

-- ── Comentários ──────────────────────────────────────────────
CREATE TABLE comments (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    article_id  INT UNSIGNED NOT NULL,
    user_id     INT UNSIGNED NOT NULL,
    parent_id   INT UNSIGNED DEFAULT NULL,        -- Respostas aninhadas
    body        TEXT NOT NULL,
    is_hidden   TINYINT(1) DEFAULT 0,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id)  REFERENCES comments(id) ON DELETE SET NULL,

    INDEX idx_article (article_id, created_at)
) ENGINE=InnoDB;

-- ── Conta de administrador padrão ────────────────────────────
-- Senha temporária: HalleyAdmin2061! (trocar no primeiro login)
-- Hash gerado com: password_hash('HalleyAdmin2061!', PASSWORD_BCRYPT)
INSERT INTO users (username, email, password, role, language) VALUES
('admin', 'admin@thehalleyproject.org',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 'admin', 'pt-BR');
