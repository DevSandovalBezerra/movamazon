-- Migration: tabelas e seeds para gerenciamento de banners/carrossel

CREATE TABLE IF NOT EXISTS `banners` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `titulo` VARCHAR(255) NOT NULL,
  `descricao` TEXT,
  `imagem` VARCHAR(255) NOT NULL,
  `link` VARCHAR(500) DEFAULT NULL,
  `texto_botao` VARCHAR(100) DEFAULT NULL,
  `ordem` INT DEFAULT 0,
  `ativo` TINYINT(1) DEFAULT 1,
  `data_inicio` DATETIME DEFAULT NULL,
  `data_fim` DATETIME DEFAULT NULL,
  `target_blank` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ativo` (`ativo`),
  KEY `idx_ordem` (`ordem`),
  KEY `idx_datas` (`data_inicio`,`data_fim`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `banners` (`titulo`, `descricao`, `imagem`, `link`, `texto_botao`, `ordem`, `ativo`)
VALUES
('Partiu MovAmazonas', 'Experiências completas de corrida e trilhas na Amazônia', 'frontend/assets/img/banners/banner_default_1.jpg', '/frontend/paginas/public/index.php#eventos', 'Conheça os Eventos', 1, 1),
('Inscrições abertas', 'Garanta sua vaga nas próximas corridas e treinos oficiais', 'frontend/assets/img/banners/banner_default_2.jpg', '/frontend/paginas/public/eventos.php', 'Ver Calendário', 2, 1)
ON DUPLICATE KEY UPDATE titulo = VALUES(titulo);

