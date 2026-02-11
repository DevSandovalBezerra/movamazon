-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 07/11/2025 às 00:17
-- Versão do servidor: 8.2.0
-- Versão do PHP: 8.2.13

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
-- Estrutura para tabela `kits_eventos`
--

DROP TABLE IF EXISTS `kits_eventos`;
CREATE TABLE IF NOT EXISTS `kits_eventos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `evento_id` int NOT NULL,
  `modalidade_evento_id` int NOT NULL,
  `kit_template_id` int DEFAULT NULL,
  `valor` decimal(10,2) NOT NULL,
  `foto_kit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disponivel_venda` tinyint(1) DEFAULT '1',
  `preco_calculado` decimal(10,2) DEFAULT '0.00',
  `ativo` tinyint(1) DEFAULT '1',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `evento_id` (`evento_id`),
  KEY `idx_kits_evento` (`evento_id`),
  KEY `idx_kits_modalidade` (`modalidade_evento_id`),
  KEY `idx_kits_ativo` (`ativo`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `kits_eventos`
--

INSERT INTO `kits_eventos` (`id`, `nome`, `descricao`, `evento_id`, `modalidade_evento_id`, `kit_template_id`, `valor`, `foto_kit`, `disponivel_venda`, `preco_calculado`, `ativo`, `data_criacao`, `updated_at`) VALUES
(3, 'Kit Promocional - CORRIDA 5KM', 'Kit Promocional aplicado em CORRIDA 5KM', 3, 17, 2, 80.00, NULL, 1, 80.00, 1, '2025-07-25 08:27:40', '2025-09-03 17:26:53'),
(5, 'Kit Atleta - CORRIDA 10KM', 'Kit Atleta aplicado em CORRIDA 10KM', 2, 1, 1, 149.50, 'kit_template_Kit Atleta.png', 1, 149.50, 0, '2025-07-26 20:46:00', '2025-10-08 03:28:11'),
(6, 'Kit Atleta - CORRIDA 5KM', 'Kit Atleta aplicado em CORRIDA 5KM', 2, 3, 1, 149.50, 'kit_template_Kit Atleta.png', 1, 149.50, 0, '2025-07-26 20:46:00', '2025-09-13 20:30:13'),
(7, 'Kit Atleta - CORRIDA 10KM', 'Kit Atleta aplicado em CORRIDA 10KM', 3, 9, 1, 149.50, 'kit_template_Kit Atleta.png', 1, 149.50, 1, '2025-07-26 20:46:00', '2025-09-03 17:29:15'),
(8, 'Kit Atleta - CORRIDA 5KM', 'Kit Atleta aplicado em CORRIDA 5KM', 2, 11, 1, 149.50, 'kit_template_Kit Atleta.png', 1, 149.50, 0, '2025-07-26 20:46:00', '2025-11-04 19:35:52'),
(9, 'Kit Promocional - CORRIDA 5KM', 'Kit Promocional aplicado em CORRIDA 5KM | KIT COMPLETO - FAMÍLIA 1', 0, 0, 0, 80.00, 'kit_template_Kit Promocional.png', 1, 80.00, 0, '2025-09-03 16:41:42', '2025-09-04 21:58:51'),
(10, 'KIT Famila - CORRIDA 5KM ', 'KIT Famila aplicado em CORRIDA 5KM ', 2, 5, 3, 32.50, 'kit_template_KIT Famila.png', 1, 32.50, 0, '2025-09-03 17:48:53', '2025-10-08 03:39:13');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
