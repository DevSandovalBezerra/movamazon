-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 12/11/2025 às 15:06
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
-- Banco de dados: `u697465806_movhealth`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `progresso_treino`
--

DROP TABLE IF EXISTS `progresso_treino`;
CREATE TABLE IF NOT EXISTS `progresso_treino` (
  `id` int NOT NULL AUTO_INCREMENT,
  `treino_id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `data_realizado` date NOT NULL,
  `percepcao_esforco` int DEFAULT NULL COMMENT 'PSE de 0 a 10 (recomendado 3-5 para diabetes)',
  `duracao_minutos` int DEFAULT NULL,
  `glicemia_pre_treino` int DEFAULT NULL COMMENT 'Glicemia antes do treino (mg/dL)',
  `glicemia_pos_treino` int DEFAULT NULL COMMENT 'Glicemia após o treino (mg/dL)',
  `sinais_alerta_observados` text COLLATE utf8mb4_unicode_ci COMMENT 'Tontura, sudorese fria, tremores, fraqueza, visão turva, confusão mental',
  `mal_estar_observado` enum('sim','nao') COLLATE utf8mb4_unicode_ci DEFAULT 'nao' COMMENT 'Houve mal-estar durante ou após o treino',
  `observacoes` text COLLATE utf8mb4_unicode_ci COMMENT 'Alterações glicêmicas, desconfortos anormais e outras observações',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `idx_progresso_treino` (`treino_id`,`data_realizado`),
  KEY `idx_glicemia_monitoring` (`glicemia_pre_treino`,`glicemia_pos_treino`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `progresso_treino`
--

INSERT INTO `progresso_treino` (`id`, `treino_id`, `usuario_id`, `data_realizado`, `percepcao_esforco`, `duracao_minutos`, `glicemia_pre_treino`, `glicemia_pos_treino`, `sinais_alerta_observados`, `mal_estar_observado`, `observacoes`, `created_at`) VALUES
(1, 145, 12, '2025-07-01', 4, 55, 110, 125, '', 'nao', 'Treino normal', '2025-07-09 03:07:01'),
(2, 146, 12, '2025-07-03', 5, 60, 120, 130, '', 'nao', 'Treino intenso', '2025-07-09 03:07:01'),
(3, 147, 12, '2025-07-05', 3, 50, 105, 115, '', 'nao', 'Treino leve', '2025-07-09 03:07:01'),
(4, 148, 12, '2025-07-07', 4, 58, 115, 128, '', 'nao', 'Treino regular', '2025-07-09 03:07:01'),
(5, 149, 12, '2025-07-09', 5, 62, 118, 132, '', 'nao', 'Treino forte', '2025-07-09 03:07:01'),
(6, 185, 30, '2025-07-12', 3, 70, NULL, NULL, NULL, 'nao', NULL, '2025-07-12 15:36:57'),
(7, 188, 30, '2025-07-13', 5, 95, NULL, NULL, NULL, 'nao', NULL, '2025-07-14 17:17:44'),
(8, 190, 31, '2025-07-21', 8, 60, NULL, NULL, NULL, 'nao', NULL, '2025-07-22 10:19:08'),
(9, 190, 31, '2025-07-22', 5, 50, NULL, NULL, NULL, 'nao', NULL, '2025-07-23 12:27:25'),
(10, 192, 31, '2025-07-24', 5, 58, NULL, NULL, 'tontura, visao_turva', 'nao', NULL, '2025-07-24 10:03:00'),
(11, 193, 31, '2025-07-24', 5, 50, NULL, NULL, 'visao_turva', 'nao', NULL, '2025-07-24 12:10:39'),
(12, 157, 22, '2025-07-25', 6, 80, 110, 90, NULL, 'nao', 'Treinou o corpo todo.', '2025-07-25 22:33:33'),
(13, 159, 10, '2025-07-31', 3, 45, 115, 96, '[]', 'nao', NULL, '2025-07-31 18:45:13'),
(14, 160, 10, '2025-07-28', 1, 30, 100, 95, '[]', 'nao', NULL, '2025-07-31 18:46:06'),
(15, 319, 35, '2025-07-30', 2, 60, 110, 90, '[]', 'nao', NULL, '2025-07-31 20:25:56'),
(16, 309, 45, '2025-07-30', 3, 110, 110, 85, '[]', 'nao', NULL, '2025-07-31 20:26:41'),
(17, 309, 45, '2025-07-31', 2, 70, NULL, NULL, '[]', 'nao', NULL, '2025-07-31 22:25:28'),
(18, 314, 37, '2025-07-31', 5, 75, NULL, NULL, '[]', 'nao', NULL, '2025-07-31 22:27:30'),
(19, 310, 45, '2025-08-01', 3, 50, NULL, NULL, '[]', 'nao', NULL, '2025-08-01 19:54:25'),
(20, 346, 48, '2025-08-04', 2, 90, NULL, NULL, '[]', 'nao', NULL, '2025-08-04 18:08:08'),
(21, 347, 48, '2025-08-05', 4, 60, NULL, NULL, '[\"tontura\",\"tremores\"]', 'nao', NULL, '2025-08-05 12:31:54'),
(22, 345, 48, '2025-08-06', 1, 70, NULL, NULL, '[]', 'nao', NULL, '2025-08-06 13:40:34'),
(23, 338, 47, '2025-08-06', 5, 50, NULL, NULL, '[]', 'nao', 'Reduzi as repetições de todos os exercícios com exceção da flexão ', '2025-08-07 01:44:13'),
(24, 348, 48, '2025-08-07', 1, 80, NULL, NULL, '[]', 'nao', 'Algumas máquinas não, substituir alguns exercios ', '2025-08-07 13:19:48'),
(25, 346, 48, '2025-08-11', 2, 90, NULL, NULL, '[]', 'nao', 'Aquecimento foi 35 min de esteira, 2,5km', '2025-08-12 02:00:31'),
(26, 337, 47, '2025-08-12', 3, 30, NULL, NULL, '[]', 'nao', NULL, '2025-08-13 02:56:39'),
(27, 338, 47, '2025-08-13', 6, 34, NULL, NULL, '[]', 'nao', 'Reduzi os tiros de 200m para 5', '2025-08-14 01:17:13'),
(28, 339, 47, '2025-08-14', 5, 44, NULL, NULL, '[]', 'nao', NULL, '2025-08-15 01:15:42'),
(29, 340, 47, '2025-08-15', 5, 50, NULL, NULL, '[]', 'nao', NULL, '2025-08-16 01:41:15'),
(30, 341, 47, '2025-08-16', 6, 40, NULL, NULL, '[]', 'nao', 'Reduzi as repetições de flexões pra 10 e aumentei uma série ', '2025-08-16 13:12:41'),
(31, 457, 30, '2025-08-18', 2, 50, NULL, NULL, '[]', 'nao', NULL, '2025-08-18 09:22:27'),
(32, 346, 48, '2025-08-18', 1, 90, NULL, NULL, '[]', 'nao', 'Só realizei treino de perna, acrescentei 3 séries de adutora e e séries de abdutora. Pq trabalhei muito pesado com a força dos braças hj no trabalho e forcei o pulso, para não machucar preferi só treinar perna ', '2025-08-19 01:28:29');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
