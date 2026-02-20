-- ==========================================================
-- Migracao: Tabela de convites da assessoria
-- Data: 2026-02-20
-- Descricao: Sistema de convites para vincular atletas
-- IMPORTANTE: Executar manualmente no banco de dados
-- ==========================================================

CREATE TABLE IF NOT EXISTS `assessoria_convites` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `assessoria_id` INT(11) NOT NULL,
  `atleta_usuario_id` INT(11) NULL,
  `email_convidado` VARCHAR(150) NULL,
  `token` VARCHAR(64) NOT NULL,
  `status` ENUM('pendente','aceito','recusado','expirado','cancelado') NOT NULL DEFAULT 'pendente',
  `enviado_por_usuario_id` INT(11) NOT NULL,
  `mensagem` TEXT NULL,
  `criado_em` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `respondido_em` TIMESTAMP NULL DEFAULT NULL,
  `expira_em` TIMESTAMP NULL DEFAULT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_convites_token` (`token`),
  KEY `idx_convites_assessoria` (`assessoria_id`),
  KEY `idx_convites_atleta` (`atleta_usuario_id`),
  KEY `idx_convites_status` (`status`),
  KEY `idx_convites_enviado_por` (`enviado_por_usuario_id`),

  CONSTRAINT `fk_convites_assessoria`
    FOREIGN KEY (`assessoria_id`) REFERENCES `assessorias`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,

  CONSTRAINT `fk_convites_atleta`
    FOREIGN KEY (`atleta_usuario_id`) REFERENCES `usuarios`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,

  CONSTRAINT `fk_convites_enviado_por`
    FOREIGN KEY (`enviado_por_usuario_id`) REFERENCES `usuarios`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
