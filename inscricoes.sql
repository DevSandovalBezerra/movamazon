-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 04/02/2026 às 18:44
-- Versão do servidor: 5.7.23-23
-- Versão do PHP: 8.1.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `brunor90_movamazon`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `inscricoes`
--

CREATE TABLE `inscricoes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `modalidade_evento_id` int(11) NOT NULL,
  `lote_inscricao_id` int(11) DEFAULT NULL,
  `tipo_publico` enum('comunidade_academica','publico_geral') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kit_modalidade_id` int(11) DEFAULT NULL,
  `kit_id` int(11) DEFAULT NULL,
  `tamanho_camiseta` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tamanho_id` int(11) DEFAULT NULL,
  `produtos_extras_ids` text COLLATE utf8mb4_unicode_ci,
  `numero_inscricao` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `protocolo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grupo_assessoria` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nome_equipe` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ordem_equipe` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `posicao_legenda` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `escolha_tamanho` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fisicamente_apto` tinyint(1) DEFAULT NULL,
  `apelido_peito` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contato_emergencia_nome` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contato_emergencia_telefone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `equipe_extra` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_comprovante_universidade` text COLLATE utf8mb4_unicode_ci,
  `data_inscricao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pendente','confirmada','cancelada') COLLATE utf8mb4_unicode_ci DEFAULT 'pendente',
  `status_pagamento` enum('pendente','pago','cancelado','rejeitado','processando') COLLATE utf8mb4_unicode_ci DEFAULT 'pendente',
  `valor_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `valor_desconto` decimal(10,2) DEFAULT '0.00',
  `cupom_aplicado` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_pagamento` datetime DEFAULT NULL,
  `data_expiracao_pagamento` datetime DEFAULT NULL COMMENT 'Data de expiração do boleto ou outro método de pagamento',
  `forma_pagamento` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parcelas` int(11) DEFAULT '1',
  `seguro_contratado` tinyint(1) DEFAULT '0',
  `external_reference` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preference_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `colocacao` int(11) DEFAULT NULL,
  `aceite_termos` tinyint(1) DEFAULT '0',
  `data_aceite_termos` timestamp NULL DEFAULT NULL,
  `versao_termos` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `inscricoes`
--

INSERT INTO `inscricoes` (`id`, `usuario_id`, `evento_id`, `modalidade_evento_id`, `lote_inscricao_id`, `tipo_publico`, `kit_modalidade_id`, `kit_id`, `tamanho_camiseta`, `tamanho_id`, `produtos_extras_ids`, `numero_inscricao`, `protocolo`, `grupo_assessoria`, `nome_equipe`, `ordem_equipe`, `posicao_legenda`, `escolha_tamanho`, `fisicamente_apto`, `apelido_peito`, `contato_emergencia_nome`, `contato_emergencia_telefone`, `equipe_extra`, `doc_comprovante_universidade`, `data_inscricao`, `status`, `status_pagamento`, `valor_total`, `valor_desconto`, `cupom_aplicado`, `data_pagamento`, `data_expiracao_pagamento`, `forma_pagamento`, `parcelas`, `seguro_contratado`, `external_reference`, `preference_id`, `colocacao`, `aceite_termos`, `data_aceite_termos`, `versao_termos`) VALUES
(6, 19, 8, 24, NULL, NULL, NULL, NULL, 'M', NULL, '[]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-23 21:16:05', 'cancelada', 'cancelado', 129.00, 0.00, NULL, NULL, NULL, NULL, 1, 0, 'MOVAMAZON_1766524480_19', '260742905-92184687-d7d0-4f31-93a5-019e1fd373e4', NULL, 0, NULL, NULL),
(7, 21, 8, 25, NULL, NULL, NULL, NULL, 'M', NULL, '[]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-31 01:48:58', 'pendente', 'processando', 20.00, 0.00, NULL, NULL, '2025-12-29 22:59:59', 'pix', 1, 0, '144201992786', '260742905-74f8ed9b-667e-46a0-a916-d2adc6e83639', NULL, 0, NULL, NULL),
(9, 17, 8, 25, NULL, NULL, NULL, NULL, 'P', NULL, '[]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-03 18:07:54', 'pendente', 'processando', 20.00, 0.00, '989D-ZXJS', NULL, NULL, 'pix', 1, 0, '143979394703', '260742905-7a1ef12d-e139-45e8-ab1c-875c3f12d0d0', NULL, 0, NULL, NULL),
(10, 20, 8, 22, NULL, NULL, NULL, NULL, 'M', NULL, '[]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-25 21:35:36', 'cancelada', 'cancelado', 129.00, 0.00, NULL, NULL, NULL, NULL, 1, 0, 'MOVAMAZON_1766698536_20', '260742905-6cd34709-b2ed-4a24-8e05-bf749b5b6bcc', NULL, 0, NULL, NULL),
(11, 18, 8, 25, NULL, NULL, NULL, NULL, 'M', NULL, '[]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-30 23:44:53', 'cancelada', 'cancelado', 20.00, 0.00, NULL, NULL, '2026-01-29 22:59:59', 'boleto', 1, 0, '142946577853', '260742905-2d66e472-fe70-48a3-8a66-4e3765f1b56d', NULL, 0, NULL, NULL),
(12, 23, 8, 23, NULL, NULL, NULL, NULL, 'M', NULL, '[]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-31 11:02:50', 'cancelada', 'cancelado', 109.00, 0.00, NULL, NULL, '2026-01-05 22:59:59', 'boleto', 1, 0, '140145304058', '260742905-8dcf7da1-f849-4967-8db1-d384e891e315', NULL, 0, NULL, NULL),
(13, 22, 8, 25, NULL, NULL, NULL, NULL, 'GG', NULL, '[]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-04 21:33:43', 'pendente', 'processando', 20.00, 0.00, NULL, NULL, NULL, 'pix', 1, 0, '144829933062', '260742905-6fb5b199-471e-4100-8fc8-e283ad33b3f6', NULL, 0, NULL, NULL),
(14, 24, 8, 22, NULL, NULL, NULL, NULL, 'M', NULL, '[]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-26 22:02:02', 'cancelada', 'cancelado', 129.00, 0.00, NULL, NULL, '2026-01-29 22:59:59', 'boleto', 1, 0, '142946715951', '260742905-d1aecc16-bc95-42b4-80d4-6bb31b819e9e', NULL, 0, NULL, NULL),
(15, 25, 8, 22, NULL, NULL, NULL, NULL, 'M', NULL, '[]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-30 19:11:21', 'cancelada', 'cancelado', 129.00, 0.00, NULL, NULL, NULL, NULL, 1, 0, 'MOVAMAZON_1769800269_25', '260742905-249c39b4-d314-4784-b547-379ed1c9ea7e', NULL, 0, NULL, NULL),
(16, 26, 8, 24, NULL, NULL, NULL, NULL, 'P', NULL, '[{\"id\":5,\"nome\":\"camiseta da corrida\",\"valor\":30}]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-30 19:47:17', 'cancelada', 'cancelado', 139.00, 0.00, NULL, NULL, '2026-02-02 22:59:59', 'pix', 1, 0, '143470736925', '260742905-30959ebf-4bd6-43a4-a823-ca0ca268f0ad', NULL, 0, NULL, NULL);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `inscricoes`
--
ALTER TABLE `inscricoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_usuario_evento` (`usuario_id`,`evento_id`),
  ADD KEY `evento_id` (`evento_id`),
  ADD KEY `modalidade_evento_id` (`modalidade_evento_id`),
  ADD KEY `idx_status_pagamento` (`status_pagamento`),
  ADD KEY `idx_data_pagamento` (`data_pagamento`),
  ADD KEY `idx_external_reference` (`external_reference`),
  ADD KEY `idx_preference_id` (`preference_id`),
  ADD KEY `idx_data_expiracao_pagamento` (`data_expiracao_pagamento`),
  ADD KEY `idx_status_data_inscricao` (`status_pagamento`,`data_inscricao`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `inscricoes`
--
ALTER TABLE `inscricoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
