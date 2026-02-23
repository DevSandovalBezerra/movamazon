# 04 – Migrações SQL (MySQL 5.7)

Este arquivo contém a migração **UP** e **DOWN** (rollback) do módulo /assessoria.

## Migração UP

```sql
START TRANSACTION;

SET @db := DATABASE();

CREATE TABLE IF NOT EXISTS `assessorias` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `tipo` ENUM('PF','PJ') NOT NULL,
  `nome_fantasia` VARCHAR(200) NOT NULL,
  `razao_social` VARCHAR(200) NULL,
  `cpf_cnpj` VARCHAR(20) NOT NULL,
  `responsavel_usuario_id` INT(11) NOT NULL,

  `email_contato` VARCHAR(150) NULL,
  `telefone_contato` VARCHAR(30) NULL,
  `site` VARCHAR(200) NULL,
  `instagram` VARCHAR(200) NULL,
  `logo` VARCHAR(255) NULL,

  `endereco` VARCHAR(255) NULL,
  `cidade` VARCHAR(100) NULL,
  `uf` VARCHAR(2) NULL,
  `cep` VARCHAR(12) NULL,

  `status` ENUM('ativo','pendente','suspenso') NOT NULL DEFAULT 'pendente',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_assessorias_cpf_cnpj` (`cpf_cnpj`),
  KEY `idx_assessorias_responsavel` (`responsavel_usuario_id`),
  CONSTRAINT `fk_assessorias_responsavel_usuario`
    FOREIGN KEY (`responsavel_usuario_id`) REFERENCES `usuarios`(`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `assessoria_equipe` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `assessoria_id` INT(11) NOT NULL,
  `usuario_id` INT(11) NOT NULL,
  `funcao` ENUM('admin','assessor','suporte') NOT NULL DEFAULT 'assessor',
  `status` ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_assessoria_equipe` (`assessoria_id`,`usuario_id`),
  KEY `idx_assessoria_equipe_assessoria` (`assessoria_id`),
  KEY `idx_assessoria_equipe_usuario` (`usuario_id`),

  CONSTRAINT `fk_assessoria_equipe_assessoria`
    FOREIGN KEY (`assessoria_id`) REFERENCES `assessorias`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,

  CONSTRAINT `fk_assessoria_equipe_usuario`
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `assessoria_atletas` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `assessoria_id` INT(11) NOT NULL,
  `atleta_usuario_id` INT(11) NOT NULL,
  `assessor_usuario_id` INT(11) NULL,

  `status` ENUM('ativo','pausado','encerrado') NOT NULL DEFAULT 'ativo',
  `data_inicio` DATE NULL,
  `data_fim` DATE NULL,
  `origem` ENUM('inscricao_evento','convite','manual') NOT NULL DEFAULT 'manual',
  `observacoes` TEXT NULL,

  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_assessoria_atletas` (`assessoria_id`,`atleta_usuario_id`),
  KEY `idx_assessoria_atletas_assessoria` (`assessoria_id`),
  KEY `idx_assessoria_atletas_atleta` (`atleta_usuario_id`),
  KEY `idx_assessoria_atletas_assessor` (`assessor_usuario_id`),

  CONSTRAINT `fk_assessoria_atletas_assessoria`
    FOREIGN KEY (`assessoria_id`) REFERENCES `assessorias`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,

  CONSTRAINT `fk_assessoria_atletas_atleta`
    FOREIGN KEY (`atleta_usuario_id`) REFERENCES `usuarios`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,

  CONSTRAINT `fk_assessoria_atletas_assessor`
    FOREIGN KEY (`assessor_usuario_id`) REFERENCES `usuarios`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `assessoria_programas` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `assessoria_id` INT(11) NOT NULL,

  `titulo` VARCHAR(200) NOT NULL,
  `tipo` ENUM('evento','continuo') NOT NULL,
  `evento_id` INT(11) NULL,

  `data_inicio` DATE NULL,
  `data_fim` DATE NULL,
  `objetivo` TEXT NULL,
  `metodologia` TEXT NULL,

  `status` ENUM('rascunho','ativo','encerrado') NOT NULL DEFAULT 'rascunho',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  KEY `idx_programas_assessoria` (`assessoria_id`),
  KEY `idx_programas_evento` (`evento_id`),

  CONSTRAINT `fk_programas_assessoria`
    FOREIGN KEY (`assessoria_id`) REFERENCES `assessorias`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,

  CONSTRAINT `fk_programas_evento`
    FOREIGN KEY (`evento_id`) REFERENCES `eventos`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `assessoria_programa_atletas` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `programa_id` INT(11) NOT NULL,
  `atleta_usuario_id` INT(11) NOT NULL,
  `status` ENUM('ativo','encerrado') NOT NULL DEFAULT 'ativo',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_programa_atletas` (`programa_id`,`atleta_usuario_id`),
  KEY `idx_programa_atletas_programa` (`programa_id`),
  KEY `idx_programa_atletas_atleta` (`atleta_usuario_id`),

  CONSTRAINT `fk_programa_atletas_programa`
    FOREIGN KEY (`programa_id`) REFERENCES `assessoria_programas`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,

  CONSTRAINT `fk_programa_atletas_atleta`
    FOREIGN KEY (`atleta_usuario_id`) REFERENCES `usuarios`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================
-- ALTER: adiciona colunas se não existirem (MySQL 5.7)
-- =========================
-- planos_treino_gerados
SET @t := 'planos_treino_gerados';

SET @c := 'assessoria_id';
SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME=@t AND COLUMN_NAME=@c)=0,
  CONCAT('ALTER TABLE `',@t,'` ADD COLUMN `assessoria_id` INT(11) NULL, ADD KEY `idx_planos_assessoria` (`assessoria_id`);'),
  'SELECT 1;'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := 'programa_id';
SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME=@t AND COLUMN_NAME=@c)=0,
  CONCAT('ALTER TABLE `',@t,'` ADD COLUMN `programa_id` INT(11) NULL, ADD KEY `idx_planos_programa` (`programa_id`);'),
  'SELECT 1;'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := 'status';
SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME=@t AND COLUMN_NAME=@c)=0,
  CONCAT("ALTER TABLE `",@t,"` ADD COLUMN `status` ENUM('rascunho','publicado','revisao','arquivado') NOT NULL DEFAULT 'rascunho';"),
  'SELECT 1;'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := 'versao';
SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME=@t AND COLUMN_NAME=@c)=0,
  CONCAT('ALTER TABLE `',@t,'` ADD COLUMN `versao` INT(11) NOT NULL DEFAULT 1;'),
  'SELECT 1;'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := 'publicado_em';
SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME=@t AND COLUMN_NAME=@c)=0,
  CONCAT('ALTER TABLE `',@t,'` ADD COLUMN `publicado_em` TIMESTAMP NULL DEFAULT NULL;'),
  'SELECT 1;'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- FKs planos_treino_gerados
SET @fk := 'fk_planos_assessoria';
SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=@db AND TABLE_NAME='planos_treino_gerados' AND CONSTRAINT_NAME=@fk)=0,
  'ALTER TABLE `planos_treino_gerados`
     ADD CONSTRAINT `fk_planos_assessoria` FOREIGN KEY (`assessoria_id`) REFERENCES `assessorias`(`id`)
     ON DELETE SET NULL ON UPDATE CASCADE;',
  'SELECT 1;'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk := 'fk_planos_programa';
SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=@db AND TABLE_NAME='planos_treino_gerados' AND CONSTRAINT_NAME=@fk)=0,
  'ALTER TABLE `planos_treino_gerados`
     ADD CONSTRAINT `fk_planos_programa` FOREIGN KEY (`programa_id`) REFERENCES `assessoria_programas`(`id`)
     ON DELETE SET NULL ON UPDATE CASCADE;',
  'SELECT 1;'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- treinos
SET @t := 'treinos';

SET @c := 'assessoria_id';
SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME=@t AND COLUMN_NAME=@c)=0,
  CONCAT('ALTER TABLE `',@t,'` ADD COLUMN `assessoria_id` INT(11) NULL, ADD KEY `idx_treinos_assessoria` (`assessoria_id`);'),
  'SELECT 1;'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := 'programa_id';
SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME=@t AND COLUMN_NAME=@c)=0,
  CONCAT('ALTER TABLE `',@t,'` ADD COLUMN `programa_id` INT(11) NULL, ADD KEY `idx_treinos_programa` (`programa_id`);'),
  'SELECT 1;'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := 'criado_por_usuario_id';
SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME=@t AND COLUMN_NAME=@c)=0,
  CONCAT('ALTER TABLE `',@t,'` ADD COLUMN `criado_por_usuario_id` INT(11) NULL, ADD KEY `idx_treinos_criado_por` (`criado_por_usuario_id`);'),
  'SELECT 1;'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := 'status';
SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME=@t AND COLUMN_NAME=@c)=0,
  CONCAT("ALTER TABLE `",@t,"` ADD COLUMN `status` ENUM('rascunho','ativo','pausado') NOT NULL DEFAULT 'rascunho';"),
  'SELECT 1;'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := 'publicado_em';
SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME=@t AND COLUMN_NAME=@c)=0,
  CONCAT('ALTER TABLE `',@t,'` ADD COLUMN `publicado_em` TIMESTAMP NULL DEFAULT NULL;'),
  'SELECT 1;'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- FKs treinos
SET @fk := 'fk_treinos_assessoria';
SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=@db AND TABLE_NAME='treinos' AND CONSTRAINT_NAME=@fk)=0,
  'ALTER TABLE `treinos`
     ADD CONSTRAINT `fk_treinos_assessoria` FOREIGN KEY (`assessoria_id`) REFERENCES `assessorias`(`id`)
     ON DELETE SET NULL ON UPDATE CASCADE;',
  'SELECT 1;'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk := 'fk_treinos_programa';
SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=@db AND TABLE_NAME='treinos' AND CONSTRAINT_NAME=@fk)=0,
  'ALTER TABLE `treinos`
     ADD CONSTRAINT `fk_treinos_programa` FOREIGN KEY (`programa_id`) REFERENCES `assessoria_programas`(`id`)
     ON DELETE SET NULL ON UPDATE CASCADE;',
  'SELECT 1;'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk := 'fk_treinos_criado_por';
SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=@db AND TABLE_NAME='treinos' AND CONSTRAINT_NAME=@fk)=0,
  'ALTER TABLE `treinos`
     ADD CONSTRAINT `fk_treinos_criado_por` FOREIGN KEY (`criado_por_usuario_id`) REFERENCES `usuarios`(`id`)
     ON DELETE SET NULL ON UPDATE CASCADE;',
  'SELECT 1;'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- progresso_treino
SET @t := 'progresso_treino';

SET @c := 'fonte';
SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME=@t AND COLUMN_NAME=@c)=0,
  CONCAT("ALTER TABLE `",@t,"` ADD COLUMN `fonte` ENUM('atleta','assessor') NOT NULL DEFAULT 'atleta';"),
  'SELECT 1;'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := 'registrado_por_usuario_id';
SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME=@t AND COLUMN_NAME=@c)=0,
  CONCAT('ALTER TABLE `',@t,'` ADD COLUMN `registrado_por_usuario_id` INT(11) NULL, ADD KEY `idx_progresso_registrado_por` (`registrado_por_usuario_id`);'),
  'SELECT 1;'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := 'feedback_atleta';
SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME=@t AND COLUMN_NAME=@c)=0,
  CONCAT('ALTER TABLE `',@t,'` ADD COLUMN `feedback_atleta` TEXT NULL;'),
  'SELECT 1;'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := 'feedback_assessor';
SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME=@t AND COLUMN_NAME=@c)=0,
  CONCAT('ALTER TABLE `',@t,'` ADD COLUMN `feedback_assessor` TEXT NULL;'),
  'SELECT 1;'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk := 'fk_progresso_registrado_por';
SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=@db AND TABLE_NAME='progresso_treino' AND CONSTRAINT_NAME=@fk)=0,
  'ALTER TABLE `progresso_treino`
     ADD CONSTRAINT `fk_progresso_registrado_por` FOREIGN KEY (`registrado_por_usuario_id`) REFERENCES `usuarios`(`id`)
     ON DELETE SET NULL ON UPDATE CASCADE;',
  'SELECT 1;'
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- RBAC: inserir papeis caso não existam
INSERT INTO `papeis` (`id`, `nome`)
SELECT (SELECT IFNULL(MAX(id),0)+1 FROM `papeis`), 'assessoria_admin'
WHERE NOT EXISTS (SELECT 1 FROM `papeis` WHERE `nome`='assessoria_admin');

INSERT INTO `papeis` (`id`, `nome`)
SELECT (SELECT IFNULL(MAX(id),0)+1 FROM `papeis`), 'assessor'
WHERE NOT EXISTS (SELECT 1 FROM `papeis` WHERE `nome`='assessor');

COMMIT;
```

## Migração DOWN (rollback)
```sql
START TRANSACTION;

ALTER TABLE `progresso_treino` DROP FOREIGN KEY `fk_progresso_registrado_por`;

ALTER TABLE `treinos` DROP FOREIGN KEY `fk_treinos_criado_por`;
ALTER TABLE `treinos` DROP FOREIGN KEY `fk_treinos_programa`;
ALTER TABLE `treinos` DROP FOREIGN KEY `fk_treinos_assessoria`;

ALTER TABLE `planos_treino_gerados` DROP FOREIGN KEY `fk_planos_programa`;
ALTER TABLE `planos_treino_gerados` DROP FOREIGN KEY `fk_planos_assessoria`;

ALTER TABLE `progresso_treino`
  DROP COLUMN `feedback_assessor`,
  DROP COLUMN `feedback_atleta`,
  DROP COLUMN `registrado_por_usuario_id`,
  DROP COLUMN `fonte`;

ALTER TABLE `treinos`
  DROP COLUMN `publicado_em`,
  DROP COLUMN `status`,
  DROP COLUMN `criado_por_usuario_id`,
  DROP COLUMN `programa_id`,
  DROP COLUMN `assessoria_id`;

ALTER TABLE `planos_treino_gerados`
  DROP COLUMN `publicado_em`,
  DROP COLUMN `versao`,
  DROP COLUMN `status`,
  DROP COLUMN `programa_id`,
  DROP COLUMN `assessoria_id`;

DROP TABLE IF EXISTS `assessoria_programa_atletas`;
DROP TABLE IF EXISTS `assessoria_programas`;
DROP TABLE IF EXISTS `assessoria_atletas`;
DROP TABLE IF EXISTS `assessoria_equipe`;
DROP TABLE IF EXISTS `assessorias`;

COMMIT;
```

## Queries úteis: importar atletas por evento
### Listar inscritos de um evento
```sql
SELECT
  i.id AS inscricao_id,
  u.id AS usuario_id,
  u.nome_completo,
  u.email,
  i.evento_id,
  i.modalidade_evento_id,
  i.status_pagamento,
  i.data_inscricao,
  i.nome_equipe,
  i.grupo_assessoria
FROM inscricoes i
JOIN usuarios u ON u.id = i.usuario_id
WHERE i.evento_id = :evento_id;
```

### Vincular em lote na assessoria
```sql
INSERT INTO assessoria_atletas (assessoria_id, atleta_usuario_id, assessor_usuario_id, origem, data_inicio, status)
SELECT
  :assessoria_id,
  i.usuario_id,
  NULL,
  'inscricao_evento',
  CURDATE(),
  'ativo'
FROM inscricoes i
WHERE i.evento_id = :evento_id
  AND NOT EXISTS (
    SELECT 1
    FROM assessoria_atletas aa
    WHERE aa.assessoria_id = :assessoria_id
      AND aa.atleta_usuario_id = i.usuario_id
  );
```
