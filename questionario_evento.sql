-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 23/12/2025 às 14:59
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
-- Estrutura para tabela `questionario_evento`
--

CREATE TABLE `questionario_evento` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `modalidade_id` int(11) NOT NULL,
  `tipo` enum('pergunta','campo') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_resposta` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `classificacao` enum('evento','atleta') COLLATE utf8mb4_unicode_ci DEFAULT 'evento',
  `mascara` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `texto` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `obrigatorio` tinyint(1) DEFAULT '0',
  `ordem` int(11) DEFAULT '0',
  `ativo` tinyint(1) DEFAULT '1',
  `status_site` enum('publicada','rascunho') COLLATE utf8mb4_unicode_ci DEFAULT 'publicada',
  `status_grupo` enum('publicada','rascunho') COLLATE utf8mb4_unicode_ci DEFAULT 'publicada',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `questionario_evento`
--

INSERT INTO `questionario_evento` (`id`, `evento_id`, `modalidade_id`, `tipo`, `tipo_resposta`, `classificacao`, `mascara`, `texto`, `obrigatorio`, `ordem`, `ativo`, `status_site`, `status_grupo`, `data_criacao`) VALUES
(16, 8, 0, 'campo', 'texto_aberto', 'evento', NULL, 'Você sabe o que pode acontecer se o Sauim-de-coleira for extinto?', 0, 0, 1, 'publicada', 'publicada', '2025-12-20 17:26:29'),
(17, 8, 0, 'campo', 'texto_aberto', 'evento', NULL, 'Em caso de incidentes a organização entra em contato com? (*Informe nome e DDD fone)', 0, 0, 1, 'publicada', 'publicada', '2025-12-20 17:27:36'),
(18, 8, 0, 'campo', 'texto_aberto', 'evento', NULL, 'Para você que espécie é considerada o símbolo da cidade de Manaus?', 0, 0, 1, 'publicada', 'publicada', '2025-12-21 02:04:19'),
(19, 8, 0, 'campo', 'texto_aberto', 'evento', NULL, 'Qual ação em prol do meio ambiente que você pratica no seu dia-a-dia (em casa, no trabalho, bairro ou escola)?', 0, 0, 1, 'publicada', 'publicada', '2025-12-21 02:05:07'),
(20, 8, 0, 'campo', 'texto_aberto', 'atleta', NULL, 'Anexe aqui o documento que comprove seu vinculo com as Universidades Públicas e privadas do Amazonas, incluindo o Instituto Federal do Amazonas..', 0, 0, 1, 'publicada', 'publicada', '2025-12-21 02:05:46'),
(21, 8, 0, 'campo', 'texto_aberto', 'evento', NULL, 'Você sabe as consequências para todos nós quando um animal é extinto?', 0, 6, 1, 'publicada', 'publicada', '2025-12-21 02:06:16'),
(22, 8, 0, 'campo', 'texto_aberto', 'evento', NULL, 'Você já participou de alguma ação de Educação Ambiental para conservação de alguma espécie em Manaus?', 0, 7, 1, 'publicada', 'publicada', '2025-12-21 02:06:41'),
(23, 8, 0, 'campo', 'texto_aberto', 'evento', NULL, '	  Declara que este participante está apto fisicamente e leu o REGULAMENTO estando de acordo para participar do evento?', 0, 0, 1, 'publicada', 'publicada', '2025-12-21 02:07:28'),
(24, 8, 0, 'campo', 'texto_aberto', 'atleta', NULL, 'Ciente que a criança recebe apenas camiseta e medalha, e não o kit completo?', 0, 9, 1, 'publicada', 'publicada', '2025-12-21 02:07:49'),
(25, 8, 0, 'campo', 'texto_aberto', 'atleta', NULL, '  Informe primeiro nome ou apelido para número de peito', 0, 0, 1, 'publicada', 'publicada', '2025-12-21 02:08:11'),
(26, 8, 0, 'campo', 'texto_aberto', 'evento', NULL, 'Você conhece qual é o animal que é o Símbolo de Manaus?', 0, 11, 1, 'publicada', 'publicada', '2025-12-21 02:09:14'),
(27, 8, 0, 'campo', 'texto_aberto', 'evento', NULL, 'Você já participou das edições anteriores da corrida sauim-de-coleira?', 0, 12, 1, 'publicada', 'publicada', '2025-12-21 02:09:40'),
(28, 8, 0, 'campo', 'texto_aberto', 'evento', NULL, '  O que você pode fazer para contribuir com a preservação do Sauim-de-Coleira?', 0, 13, 1, 'publicada', 'publicada', '2025-12-21 02:10:11'),
(29, 8, 0, 'campo', 'texto_aberto', 'evento', NULL, '  Você acha importante conservar os animais ameaçados de extinção?', 0, 14, 1, 'publicada', 'publicada', '2025-12-21 02:10:34');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `questionario_evento`
--
ALTER TABLE `questionario_evento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evento_id` (`evento_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `questionario_evento`
--
ALTER TABLE `questionario_evento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
