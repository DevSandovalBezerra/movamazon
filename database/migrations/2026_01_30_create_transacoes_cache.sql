-- ========================================
-- MIGRATION: Tabela de Cache de Transações MP
-- Data: 30/01/2026
-- Objetivo: Persistir histórico completo de transações do Mercado Pago
-- ========================================

-- ✅ Criar tabela de cache
CREATE TABLE IF NOT EXISTS `transacoes_mp_cache` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  
  -- Dados principais da transação
  `payment_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID único do Mercado Pago',
  `external_reference` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Referência da inscrição',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'approved, rejected, pending, etc',
  `status_detail` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Detalhe específico do status',
  
  -- Valores
  `transaction_amount` decimal(10,2) NOT NULL COMMENT 'Valor total da transação',
  `net_amount` decimal(10,2) DEFAULT NULL COMMENT 'Valor líquido (após taxas)',
  `fee_amount` decimal(10,2) DEFAULT NULL COMMENT 'Total de taxas',
  
  -- Método de pagamento
  `payment_method_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'pix, bolbradesco, visa, etc',
  `payment_type_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ticket, credit_card, etc',
  `installments` int(11) DEFAULT 1 COMMENT 'Número de parcelas',
  
  -- Datas importantes
  `date_created` datetime NOT NULL COMMENT 'Data de criação da transação',
  `date_approved` datetime DEFAULT NULL COMMENT 'Data de aprovação',
  `date_last_updated` datetime DEFAULT NULL COMMENT 'Última atualização no MP',
  
  -- Dados do comprador
  `payer_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payer_first_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payer_last_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payer_identification_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'CPF, CNPJ, etc',
  `payer_identification_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  
  -- Dados completos (JSON) para futuras consultas
  `dados_completos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin COMMENT 'JSON completo da transação',
  
  -- Controle de cache
  `ultima_sincronizacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Última vez que foi atualizado',
  `origem` enum('webhook','consulta_manual','sincronizacao_automatica') COLLATE utf8mb4_unicode_ci DEFAULT 'consulta_manual' COMMENT 'De onde veio a sincronização',
  
  -- Chaves
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_payment_id` (`payment_id`),
  KEY `idx_external_reference` (`external_reference`),
  KEY `idx_status` (`status`),
  KEY `idx_date_created` (`date_created`),
  KEY `idx_ultima_sincronizacao` (`ultima_sincronizacao`),
  KEY `idx_payer_email` (`payer_email`),
  KEY `idx_payment_method` (`payment_method_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cache de transações do Mercado Pago para consultas rápidas';

-- ========================================
-- ✅ Criar tabela de log de sincronizações
-- ========================================

CREATE TABLE IF NOT EXISTS `logs_sincronizacao_mp` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tipo` enum('webhook','manual','automatica') COLLATE utf8mb4_unicode_ci NOT NULL,
  `inicio` datetime NOT NULL,
  `fim` datetime DEFAULT NULL,
  `duracao_ms` int(11) DEFAULT NULL COMMENT 'Duração em milissegundos',
  `transacoes_processadas` int(11) DEFAULT 0,
  `transacoes_novas` int(11) DEFAULT 0,
  `transacoes_atualizadas` int(11) DEFAULT 0,
  `erros` int(11) DEFAULT 0,
  `status` enum('em_progresso','concluido','erro') COLLATE utf8mb4_unicode_ci DEFAULT 'em_progresso',
  `mensagem_erro` text COLLATE utf8mb4_unicode_ci,
  `executado_por` int(11) DEFAULT NULL COMMENT 'ID do usuário que executou (se manual)',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tipo` (`tipo`),
  KEY `idx_inicio` (`inicio`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log de sincronizações com Mercado Pago';

-- ========================================
-- ✅ Verificar se tabelas foram criadas
-- ========================================

SELECT 
    'transacoes_mp_cache' as tabela,
    COUNT(*) as total_registros 
FROM transacoes_mp_cache

UNION ALL

SELECT 
    'logs_sincronizacao_mp' as tabela,
    COUNT(*) as total_registros 
FROM logs_sincronizacao_mp;

-- ========================================
-- 📊 Query para estatísticas do cache
-- ========================================

-- USE APÓS POPULAR A TABELA:
/*
SELECT 
    status,
    COUNT(*) as total,
    SUM(transaction_amount) as valor_total,
    MIN(date_created) as primeira_transacao,
    MAX(date_created) as ultima_transacao
FROM transacoes_mp_cache
GROUP BY status
ORDER BY total DESC;
*/
