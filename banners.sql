-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 05/02/2026 às 11:02
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
-- Estrutura para tabela `banners`
--

CREATE TABLE `banners` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `imagem` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `texto_botao` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ordem` int(11) DEFAULT '0',
  `ativo` tinyint(1) DEFAULT '1',
  `data_inicio` datetime DEFAULT NULL,
  `data_fim` datetime DEFAULT NULL,
  `target_blank` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `banners`
--

INSERT INTO `banners` (`id`, `titulo`, `descricao`, `imagem`, `link`, `texto_botao`, `ordem`, `ativo`, `data_inicio`, `data_fim`, `target_blank`, `created_at`, `updated_at`) VALUES
(4, 'Encontre sua próxima Corrida', 'A plataforma completa e única que une você a todos os eventos de corridas no Amazonas', '/frontend/assets/img/banners/banner_1770299520_6984a08079668.jpg', NULL, NULL, 1, 1, '2026-02-05 09:53:00', '2026-07-05 09:53:00', 0, '2026-02-05 13:53:33', '2026-02-05 13:53:33'),
(5, 'Este ano eu vou', 'Viva a aventura de correr, encontre um caminho para a saúde', '/frontend/assets/img/banners/banner_1770299629_6984a0ed3373a.png', NULL, NULL, 2, 1, '2026-02-05 09:55:00', '2026-07-05 09:55:00', 0, '2026-02-05 13:55:36', '2026-02-05 13:55:36'),
(6, 'Correr è viver', 'Junte os amigos e venha participar deste movimento', '/frontend/assets/img/banners/banner_1770299800_6984a1981421d.jpg', NULL, NULL, 3, 1, '2026-02-05 09:57:00', '2026-07-05 09:57:00', 0, '2026-02-05 13:57:45', '2026-02-05 13:57:45');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ativo` (`ativo`),
  ADD KEY `idx_ordem` (`ordem`),
  ADD KEY `idx_datas` (`data_inicio`,`data_fim`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `banners`
--
ALTER TABLE `banners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
