-- ========================================
-- MIGRATION: Modulo Financeiro (MVP)
-- Data: 2026-02-23
-- Objetivo:
-- 1) Criar tabelas financeiras com PK/AUTO_INCREMENT
-- 2) Criar indices para consultas de extrato/saldo/webhook
-- 3) Aplicar chaves estrangeiras principais
-- ========================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- ========================================
-- 1) Tabela: financeiro_beneficiarios
-- ========================================
CREATE TABLE IF NOT EXISTS `financeiro_beneficiarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `organizador_id` int(11) NOT NULL,
  `tipo` enum('PF','PJ') COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `documento` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('ativo','inativo','pendente_validacao') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente_validacao',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fin_beneficiarios_organizador` (`organizador_id`),
  KEY `idx_fin_beneficiarios_status` (`status`),
  CONSTRAINT `fk_fin_beneficiarios_organizador`
    FOREIGN KEY (`organizador_id`) REFERENCES `organizadores` (`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 2) Tabela: financeiro_contas_bancarias
-- ========================================
CREATE TABLE IF NOT EXISTS `financeiro_contas_bancarias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `beneficiario_id` int(11) NOT NULL,
  `banco_codigo` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `banco_nome` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `agencia` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `conta` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `conta_dv` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_conta` enum('corrente','poupanca','pagamento') COLLATE utf8mb4_unicode_ci NOT NULL,
  `titular_nome` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `titular_documento` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('ativa','inativa','pendente_validacao') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente_validacao',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fin_contas_beneficiario` (`beneficiario_id`),
  KEY `idx_fin_contas_status` (`status`),
  CONSTRAINT `fk_fin_contas_beneficiario`
    FOREIGN KEY (`beneficiario_id`) REFERENCES `financeiro_beneficiarios` (`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 3) Tabela: financeiro_ledger
-- ========================================
CREATE TABLE IF NOT EXISTS `financeiro_ledger` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `evento_id` int(11) NOT NULL,
  `origem_tipo` enum('inscricao','pagamento','taxa','repasse','estorno','chargeback','ajuste_manual','produto','outro') COLLATE utf8mb4_unicode_ci NOT NULL,
  `origem_id` bigint(20) DEFAULT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direcao` enum('credito','debito') COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pendente','disponivel','liquidado','revertido','bloqueado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente',
  `ocorrido_em` datetime NOT NULL,
  `disponivel_em` datetime DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fin_ledger_evento_status_ocorrido` (`evento_id`,`status`,`ocorrido_em`),
  KEY `idx_fin_ledger_evento_ocorrido` (`evento_id`,`ocorrido_em`),
  KEY `idx_fin_ledger_evento_disponivel` (`evento_id`,`disponivel_em`),
  KEY `idx_fin_ledger_origem` (`origem_tipo`,`origem_id`),
  KEY `idx_fin_ledger_status_direcao` (`status`,`direcao`),
  CONSTRAINT `fk_fin_ledger_evento`
    FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_fin_ledger_created_by`
    FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 4) Tabela: financeiro_repasses
-- ========================================
CREATE TABLE IF NOT EXISTS `financeiro_repasses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `evento_id` int(11) NOT NULL,
  `beneficiario_id` int(11) NOT NULL,
  `conta_bancaria_id` int(11) NOT NULL,
  `valor_solicitado` decimal(10,2) NOT NULL,
  `valor_taxa_repasse` decimal(10,2) NOT NULL DEFAULT 0.00,
  `valor_liquido` decimal(10,2) NOT NULL,
  `status` enum('criado','agendado','processando','pago','falhou','cancelado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'criado',
  `agendado_para` date DEFAULT NULL,
  `solicitado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `processado_em` datetime DEFAULT NULL,
  `comprovante_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gateway_transfer_id` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `motivo_falha` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_fin_repasses_evento_status` (`evento_id`,`status`),
  KEY `idx_fin_repasses_agendado` (`agendado_para`,`status`),
  KEY `idx_fin_repasses_beneficiario` (`beneficiario_id`),
  KEY `idx_fin_repasses_conta` (`conta_bancaria_id`),
  CONSTRAINT `fk_fin_repasses_evento`
    FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_fin_repasses_beneficiario`
    FOREIGN KEY (`beneficiario_id`) REFERENCES `financeiro_beneficiarios` (`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_fin_repasses_conta`
    FOREIGN KEY (`conta_bancaria_id`) REFERENCES `financeiro_contas_bancarias` (`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 5) Tabela: financeiro_estornos
-- ========================================
CREATE TABLE IF NOT EXISTS `financeiro_estornos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `evento_id` int(11) NOT NULL,
  `inscricao_id` int(11) DEFAULT NULL,
  `pagamento_ml_id` int(11) DEFAULT NULL,
  `valor` decimal(10,2) NOT NULL,
  `motivo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('solicitado','em_processamento','concluido','negado','falhou') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'solicitado',
  `solicitado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `concluido_em` datetime DEFAULT NULL,
  `gateway_refund_id` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `raw_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_fin_estornos_evento_status` (`evento_id`,`status`),
  KEY `idx_fin_estornos_inscricao` (`inscricao_id`),
  KEY `idx_fin_estornos_pagamento_ml` (`pagamento_ml_id`),
  CONSTRAINT `fk_fin_estornos_evento`
    FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_fin_estornos_inscricao`
    FOREIGN KEY (`inscricao_id`) REFERENCES `inscricoes` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_fin_estornos_pagamento_ml`
    FOREIGN KEY (`pagamento_ml_id`) REFERENCES `pagamentos_ml` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 6) Tabela: financeiro_chargebacks
-- ========================================
CREATE TABLE IF NOT EXISTS `financeiro_chargebacks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `evento_id` int(11) NOT NULL,
  `inscricao_id` int(11) DEFAULT NULL,
  `pagamento_ml_id` int(11) DEFAULT NULL,
  `valor` decimal(10,2) NOT NULL,
  `status` enum('aberto','em_disputa','ganho','perdido','cancelado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aberto',
  `aberto_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `encerrado_em` datetime DEFAULT NULL,
  `motivo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prazo_resposta` date DEFAULT NULL,
  `evidencias_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `raw_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_fin_chargebacks_evento_status` (`evento_id`,`status`),
  KEY `idx_fin_chargebacks_inscricao` (`inscricao_id`),
  KEY `idx_fin_chargebacks_pagamento_ml` (`pagamento_ml_id`),
  CONSTRAINT `fk_fin_chargebacks_evento`
    FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_fin_chargebacks_inscricao`
    FOREIGN KEY (`inscricao_id`) REFERENCES `inscricoes` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_fin_chargebacks_pagamento_ml`
    FOREIGN KEY (`pagamento_ml_id`) REFERENCES `pagamentos_ml` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 7) Tabela: financeiro_fechamentos
-- ========================================
CREATE TABLE IF NOT EXISTS `financeiro_fechamentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `evento_id` int(11) NOT NULL,
  `fechado_por` int(11) DEFAULT NULL,
  `fechado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `receita_bruta` decimal(10,2) NOT NULL DEFAULT 0.00,
  `taxas` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estornos` decimal(10,2) NOT NULL DEFAULT 0.00,
  `chargebacks` decimal(10,2) NOT NULL DEFAULT 0.00,
  `repasses` decimal(10,2) NOT NULL DEFAULT 0.00,
  `saldo_final` decimal(10,2) NOT NULL DEFAULT 0.00,
  `snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_fin_fechamentos_evento_data` (`evento_id`,`fechado_em`),
  CONSTRAINT `fk_fin_fechamentos_evento`
    FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_fin_fechamentos_user`
    FOREIGN KEY (`fechado_por`) REFERENCES `usuarios` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- 8) Tabela: financeiro_webhook_eventos
-- ========================================
CREATE TABLE IF NOT EXISTS `financeiro_webhook_eventos` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `gateway` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_id` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_id` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_fin_webhook_gateway_event` (`gateway`,`event_id`),
  KEY `idx_fin_webhook_payment` (`payment_id`),
  KEY `idx_fin_webhook_status` (`status`),
  KEY `idx_fin_webhook_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- Fim
-- ========================================
