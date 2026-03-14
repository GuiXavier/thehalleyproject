-- ============================================================
-- The Halley Project — Migration: Soft Delete + Mod Log
-- Execute: mysql -u root -pResidentevil4 halley_forum < migration_trash.sql
-- ============================================================

USE halley_forum;

-- Adicionar soft-delete nos artigos
ALTER TABLE articles ADD COLUMN deleted_at DATETIME DEFAULT NULL;
ALTER TABLE articles ADD COLUMN deleted_by INT UNSIGNED DEFAULT NULL;
ALTER TABLE articles ADD INDEX idx_deleted (deleted_at);

-- Adicionar soft-delete nos comentários
ALTER TABLE comments ADD COLUMN deleted_at DATETIME DEFAULT NULL;
ALTER TABLE comments ADD COLUMN deleted_by INT UNSIGNED DEFAULT NULL;
ALTER TABLE comments ADD INDEX idx_com_deleted (deleted_at);

-- Log de moderação (histórico permanente)
CREATE TABLE IF NOT EXISTS mod_log (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id    INT UNSIGNED NOT NULL,
    action      VARCHAR(50)  NOT NULL,
    target_type ENUM('user','article','comment') NOT NULL,
    target_id   INT UNSIGNED NOT NULL,
    details     VARCHAR(500) DEFAULT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_created (created_at DESC),
    INDEX idx_target  (target_type, target_id)
) ENGINE=InnoDB;
