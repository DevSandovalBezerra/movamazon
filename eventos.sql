-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 30, 2026 at 12:48 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `brunor90_movamazon`
--

-- --------------------------------------------------------

--
-- Table structure for table `eventos`
--

CREATE TABLE `eventos` (
  `id` int NOT NULL,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `data_inicio` date DEFAULT NULL,
  `data_fim` date DEFAULT NULL,
  `categoria` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `genero` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `local` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Local geral do evento - cidade/endereço principal (não confundir com programacao_evento.local que é específico de cada item)',
  `cep` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url_mapa` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logradouro` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cidade` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pais` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Brasil',
  `regulamento` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `organizador_id` int NOT NULL,
  `taxa_setup` decimal(10,2) DEFAULT NULL,
  `percentual_repasse` decimal(5,2) DEFAULT NULL,
  `exibir_retirada_kit` tinyint(1) DEFAULT '0',
  `taxa_gratuitas` decimal(10,2) DEFAULT NULL,
  `taxa_pagas` decimal(10,2) DEFAULT NULL,
  `limite_vagas` int DEFAULT NULL,
  `data_fim_inscricoes` date DEFAULT NULL,
  `hora_fim_inscricoes` time DEFAULT NULL,
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `hora_inicio` time DEFAULT NULL COMMENT 'Horário geral de início do evento (não confundir com programacao_evento.hora_inicio que é específico de cada item)',
  `data_realizacao` date DEFAULT NULL,
  `imagem` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` int DEFAULT NULL,
  `delete_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `regulamento_arquivo` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nome/caminho do arquivo de regulamento (PDF/DOC/DOCX)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `eventos`
--

INSERT INTO `eventos` (`id`, `nome`, `descricao`, `data_inicio`, `data_fim`, `categoria`, `genero`, `local`, `cep`, `url_mapa`, `logradouro`, `numero`, `cidade`, `estado`, `pais`, `regulamento`, `status`, `organizador_id`, `taxa_setup`, `percentual_repasse`, `exibir_retirada_kit`, `taxa_gratuitas`, `taxa_pagas`, `limite_vagas`, `data_fim_inscricoes`, `hora_fim_inscricoes`, `data_criacao`, `hora_inicio`, `data_realizacao`, `imagem`, `deleted_at`, `deleted_by`, `delete_reason`, `regulamento_arquivo`) VALUES
(7, 'III Corrida Sauim de Coleira', 'A III Corrida Sauim de Coleira engloba três grandes pilares da nossa sociedade, esporte, por meio da prática de atividade física, meio ambiente, por meio da busca pela preservação ambiental e pela proteção do sauim-de-coleira e a Educação, por meio do Projeto de extensão da UEA o Projeto Primatas, que visa levar para a sociedade manauara conhecimentos de conservação da biodiversidade e sensibilização ambiental. \r\nEsta corrida que já está na terceira versão, é um evento para todos, independentemente da idade ou do nível de condicionamento físico, pois este ano teremos a caminhada em família.', '2026-10-24', NULL, 'corrida-rua', 'Misto', 'Manaus - AM', NULL, NULL, NULL, NULL, 'Manaus', 'AM', 'Brasil', 'sim', 'rascunho', 3, NULL, NULL, 0, NULL, NULL, 2000, NULL, NULL, '2025-12-19 14:13:04', NULL, '2026-10-24', NULL, NULL, NULL, NULL, NULL),
(8, 'III CORRIDA SAUIM DE COLEIRA', 'A III Corrida Sauim de Coleira engloba três grandes pilares da nossa sociedade, esporte, por meio da prática de atividade física, meio ambiente, por meio da busca pela preservação ambiental e pela proteção do sauim-de-coleira e a Educação, por meio do Projeto de extensão da UEA o Projeto Primatas, que visa levar para a sociedade manauara conhecimentos de conservação da biodiversidade e sensibilização ambiental.', '2026-10-24', '2026-10-19', 'corrida_rua', 'misto', 'Manaus - AM', '69050-020', 'https://maps.app.goo.gl/6TBM6FtyMU3iDqZGA?g_st=awb', 'Avenida Darcyr Vargas', '1200', 'Manaus', 'AM', 'Brasil', 'sim', 'ativo', 4, '129.50', '5.00', 0, '2.50', '5.00', 2000, '2026-10-19', '23:59:00', '2025-12-19 14:31:48', '06:00:00', '2026-10-24', 'evento_2.png', NULL, NULL, NULL, 'api/uploads/regulamentos/regulamento_8.pdf');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organizador_id` (`organizador_id`),
  ADD KEY `idx_eventos_data_realizacao` (`data_realizacao`),
  ADD KEY `idx_deleted_at` (`deleted_at`),
  ADD KEY `fk_evento_deleted_by` (`deleted_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `eventos`
--
ALTER TABLE `eventos`
  ADD CONSTRAINT `fk_evento_deleted_by` FOREIGN KEY (`deleted_by`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
