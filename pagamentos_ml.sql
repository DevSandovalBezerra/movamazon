-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 03/02/2026 às 14:10
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
-- Estrutura para tabela `pagamentos_ml`
--

CREATE TABLE `pagamentos_ml` (
  `id` int(11) NOT NULL,
  `inscricao_id` int(11) NOT NULL,
  `preference_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `init_point` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pendente','pago','cancelado','rejeitado','processando') COLLATE utf8mb4_unicode_ci DEFAULT 'pendente',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `dados_pagamento` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `valor_pago` decimal(10,2) DEFAULT NULL,
  `metodo_pagamento` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parcelas` int(11) DEFAULT '1',
  `taxa_ml` decimal(10,2) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `pagamentos_ml`
--
ALTER TABLE `pagamentos_ml`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_inscricao_id` (`inscricao_id`),
  ADD KEY `idx_preference_id` (`preference_id`),
  ADD KEY `idx_payment_id` (`payment_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_data_criacao` (`data_criacao`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `pagamentos_ml`
--
ALTER TABLE `pagamentos_ml`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
