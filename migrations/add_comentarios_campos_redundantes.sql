-- Adicionar comentários para diferenciar campos redundantes
-- As colunas já existem (verificado em programacao_evento.sql)
-- Este script apenas adiciona comentários explicativos

-- Tabela eventos
ALTER TABLE `eventos` 
MODIFY COLUMN `hora_inicio` TIME NULL COMMENT 'Horário geral de início do evento (não confundir com programacao_evento.hora_inicio que é específico de cada item)',
MODIFY COLUMN `local` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Local geral do evento - cidade/endereço principal (não confundir com programacao_evento.local que é específico de cada item)';

-- Tabela programacao_evento
ALTER TABLE `programacao_evento`
MODIFY COLUMN `hora_inicio` TIME NULL COMMENT 'Horário específico de largada/atividade desta programação (diferente de eventos.hora_inicio que é o horário geral)',
MODIFY COLUMN `hora_fim` TIME NULL COMMENT 'Horário de término de atividade (específico desta programação)',
MODIFY COLUMN `local` VARCHAR(255) NULL COMMENT 'Local específico deste item de programação - percurso ou atividade (diferente de eventos.local que é o local geral)',
MODIFY COLUMN `latitude` DECIMAL(10, 8) NULL COMMENT 'Latitude do local específico deste item de programação',
MODIFY COLUMN `longitude` DECIMAL(11, 8) NULL COMMENT 'Longitude do local específico deste item de programação';

