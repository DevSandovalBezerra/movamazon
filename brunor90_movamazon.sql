-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 23/02/2026 às 19:45
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

DELIMITER $$
--
-- Funções
--
CREATE DEFINER=`brunor90`@`localhost` FUNCTION `get_lote_ativo` (`modalidade_id` INT, `tipo_publico` VARCHAR(20)) RETURNS INT(11) DETERMINISTIC READS SQL DATA BEGIN
    DECLARE lote_id INT;
    
    SELECT id INTO lote_id
    FROM lotes_inscricao 
    WHERE modalidade_evento_id = modalidade_id 
      AND tipo_publico = tipo_publico
      AND data_inicio <= CURDATE() 
      AND data_fim >= CURDATE()
      AND ativo = 1
    ORDER BY numero_lote DESC
    LIMIT 1;
    
    RETURN lote_id;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `aceites_termos`
--

CREATE TABLE `aceites_termos` (
  `id` int(11) NOT NULL,
  `inscricao_id` int(11) NOT NULL,
  `termos_id` int(11) NOT NULL,
  `aceito` tinyint(1) DEFAULT '0',
  `data_aceite` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_usuario` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `anamneses`
--

CREATE TABLE `anamneses` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `inscricao_id` int(11) DEFAULT NULL COMMENT 'Vinculação com inscrição na corrida',
  `profissional_id` int(11) DEFAULT NULL,
  `data_anamnese` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `peso` decimal(5,2) NOT NULL,
  `altura` int(11) NOT NULL,
  `imc` decimal(4,2) DEFAULT NULL,
  `fc_maxima` smallint(6) DEFAULT NULL COMMENT 'Frequência Cardíaca Máxima (bpm)',
  `vo2_max` decimal(5,2) DEFAULT NULL COMMENT 'VO2 Máximo (ml/kg/min)',
  `zona_alvo_treino` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Zona Alvo de Treinamento (descritivo ou faixa)',
  `doencas_preexistentes` text COLLATE utf8mb4_unicode_ci,
  `uso_medicamentos` text COLLATE utf8mb4_unicode_ci,
  `nivel_atividade` enum('ativo','inativo') COLLATE utf8mb4_unicode_ci NOT NULL,
  `objetivo_principal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foco_primario` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_glicemia` int(11) DEFAULT NULL COMMENT 'Glicemia máxima (mg/dL) - opcional para anamnese simplificada',
  `limitacoes_fisicas` text COLLATE utf8mb4_unicode_ci,
  `preferencias_atividades` text COLLATE utf8mb4_unicode_ci,
  `disponibilidade_horarios` text COLLATE utf8mb4_unicode_ci,
  `historico_corridas` text COLLATE utf8mb4_unicode_ci COMMENT 'Histórico de corridas do participante',
  `assinatura_aluno` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assinatura_responsavel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pendente','em_analise','aprovada','arquivada') COLLATE utf8mb4_unicode_ci DEFAULT 'pendente',
  `aceite_termos_anamnese` tinyint(1) DEFAULT '0' COMMENT '1 = aceitou termos de anamnese',
  `data_aceite_termos_anamnese` timestamp NULL DEFAULT NULL COMMENT 'Data/hora do aceite',
  `termos_id_anamnese` int(11) DEFAULT NULL COMMENT 'ID do termo aceito'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `anamneses`
--

INSERT INTO `anamneses` (`id`, `usuario_id`, `inscricao_id`, `profissional_id`, `data_anamnese`, `peso`, `altura`, `imc`, `fc_maxima`, `vo2_max`, `zona_alvo_treino`, `doencas_preexistentes`, `uso_medicamentos`, `nivel_atividade`, `objetivo_principal`, `foco_primario`, `max_glicemia`, `limitacoes_fisicas`, `preferencias_atividades`, `disponibilidade_horarios`, `historico_corridas`, `assinatura_aluno`, `assinatura_responsavel`, `status`, `aceite_termos_anamnese`, `data_aceite_termos_anamnese`, `termos_id_anamnese`) VALUES
(3, 18, NULL, NULL, '2025-12-19 17:14:02', 82.00, 163, 30.86, NULL, NULL, NULL, 'Pré Diabetes', NULL, 'ativo', 'saude', NULL, NULL, NULL, 'Corrida, caminhada, natação', 'Manhã ( 06 as 09h)', 'Já participei de corridas de 10km', NULL, NULL, 'pendente', 0, NULL, NULL),
(4, 19, NULL, NULL, '2025-12-19 22:39:41', 63.00, 165, 23.14, NULL, NULL, NULL, NULL, NULL, 'ativo', 'saude', NULL, NULL, NULL, 'Corrida', 'A definir pelo participante', NULL, NULL, NULL, 'pendente', 0, NULL, NULL),
(5, 17, NULL, NULL, '2026-02-10 01:06:04', 65.00, 165, 23.88, NULL, NULL, NULL, 'Não tenho.', 'Nenhum.', 'ativo', 'preparacao_corrida', NULL, NULL, 'Dor no joelho esquerdo', 'Musculação e corrida', 'Segunda, terça, quarta, quinta, sexta e sábado.', 'Já participei de duas maratonas e uma corrida de 50 km', NULL, NULL, 'pendente', 0, NULL, NULL),
(6, 28, 999, NULL, '2026-02-06 11:53:09', 96.00, 173, 32.08, NULL, NULL, NULL, NULL, NULL, 'ativo', 'me mantes ativo', NULL, NULL, 'não', 'Corridas de 5k, 10k, 21k, 84k, 80k,48k.', 'A definir pelo participante', NULL, NULL, NULL, 'pendente', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `assessorias`
--

CREATE TABLE `assessorias` (
  `id` int(11) NOT NULL,
  `tipo` enum('PF','PJ') COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome_fantasia` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `razao_social` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cpf_cnpj` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `responsavel_usuario_id` int(11) NOT NULL,
  `email_contato` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone_contato` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `site` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `endereco` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cidade` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uf` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cep` varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('ativo','pendente','suspenso') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `assessoria_atletas`
--

CREATE TABLE `assessoria_atletas` (
  `id` int(11) NOT NULL,
  `assessoria_id` int(11) NOT NULL,
  `atleta_usuario_id` int(11) NOT NULL,
  `assessor_usuario_id` int(11) DEFAULT NULL,
  `status` enum('ativo','pausado','encerrado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ativo',
  `data_inicio` date DEFAULT NULL,
  `data_fim` date DEFAULT NULL,
  `origem` enum('inscricao_evento','convite','manual') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'convite',
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `assessoria_convites`
--

CREATE TABLE `assessoria_convites` (
  `id` int(11) NOT NULL,
  `assessoria_id` int(11) NOT NULL,
  `atleta_usuario_id` int(11) DEFAULT NULL,
  `email_convidado` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pendente','aceito','recusado','expirado','cancelado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente',
  `enviado_por_usuario_id` int(11) NOT NULL,
  `mensagem` text COLLATE utf8mb4_unicode_ci,
  `criado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `respondido_em` timestamp NULL DEFAULT NULL,
  `expira_em` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `assessoria_equipe`
--

CREATE TABLE `assessoria_equipe` (
  `id` int(11) NOT NULL,
  `assessoria_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `funcao` enum('admin','assessor','suporte') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'assessor',
  `status` enum('ativo','inativo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ativo',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `assessoria_programas`
--

CREATE TABLE `assessoria_programas` (
  `id` int(11) NOT NULL,
  `assessoria_id` int(11) NOT NULL,
  `titulo` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('evento','continuo') COLLATE utf8mb4_unicode_ci NOT NULL,
  `evento_id` int(11) DEFAULT NULL,
  `data_inicio` date DEFAULT NULL,
  `data_fim` date DEFAULT NULL,
  `objetivo` text COLLATE utf8mb4_unicode_ci,
  `metodologia` text COLLATE utf8mb4_unicode_ci,
  `status` enum('rascunho','ativo','encerrado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'rascunho',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `assessoria_programa_atletas`
--

CREATE TABLE `assessoria_programa_atletas` (
  `id` int(11) NOT NULL,
  `programa_id` int(11) NOT NULL,
  `atleta_usuario_id` int(11) NOT NULL,
  `status` enum('ativo','encerrado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ativo',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- --------------------------------------------------------

--
-- Estrutura para tabela `camisas`
--

CREATE TABLE `camisas` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `produto_id` int(11) DEFAULT NULL,
  `tamanho` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantidade_inicial` int(11) NOT NULL DEFAULT '0',
  `quantidade_vendida` int(11) NOT NULL DEFAULT '0',
  `quantidade_disponivel` int(11) NOT NULL DEFAULT '0',
  `quantidade_reservada` int(11) NOT NULL DEFAULT '0',
  `ativo` tinyint(1) DEFAULT '1',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `camisas`
--

INSERT INTO `camisas` (`id`, `evento_id`, `produto_id`, `tamanho`, `quantidade_inicial`, `quantidade_vendida`, `quantidade_disponivel`, `quantidade_reservada`, `ativo`, `data_criacao`) VALUES
(7, 8, NULL, 'PP', 200, 0, 200, 0, 1, '2025-12-20 12:22:30'),
(8, 8, NULL, 'P', 400, 0, 400, 0, 1, '2025-12-20 12:22:44'),
(9, 8, NULL, 'M', 500, 0, 500, 0, 1, '2025-12-20 12:22:55'),
(10, 8, NULL, 'G', 300, 1, 299, 0, 1, '2025-12-20 12:23:09'),
(11, 8, NULL, 'GG', 200, 1, 199, 0, 1, '2025-12-20 12:23:24'),
(12, 8, NULL, 'XG', 100, 0, 100, 0, 1, '2025-12-20 12:23:36'),
(13, 8, NULL, 'XXG', 40, 0, 40, 0, 1, '2025-12-20 12:23:49'),
(14, 10, NULL, 'PP', 50, 0, 50, 0, 1, '2026-02-17 04:18:19'),
(15, 10, NULL, 'P', 50, 0, 50, 0, 1, '2026-02-17 04:18:34'),
(16, 10, NULL, 'M', 50, 0, 50, 0, 1, '2026-02-17 04:18:45'),
(17, 10, NULL, 'G', 100, 0, 100, 0, 1, '2026-02-17 04:18:59'),
(18, 10, NULL, 'GG', 100, 0, 100, 0, 1, '2026-02-17 04:19:11'),
(19, 10, NULL, 'XG', 100, 0, 100, 0, 1, '2026-02-17 04:19:23'),
(20, 10, NULL, 'XXG', 50, 0, 50, 0, 1, '2026-02-17 04:19:34');

-- --------------------------------------------------------

--
-- Estrutura para tabela `cashback_atletas`
--

CREATE TABLE `cashback_atletas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `inscricao_id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `valor_inscricao` decimal(10,2) NOT NULL COMMENT 'Valor base da inscrição (sem extras)',
  `valor_cashback` decimal(10,2) NOT NULL COMMENT 'Valor do cashback (1% do valor_inscricao)',
  `percentual` decimal(5,2) DEFAULT '1.00' COMMENT 'Percentual aplicado (padrão 1%)',
  `status` enum('pendente','disponivel','utilizado','expirado') COLLATE utf8mb4_unicode_ci DEFAULT 'disponivel',
  `data_credito` datetime DEFAULT CURRENT_TIMESTAMP,
  `data_utilizacao` datetime DEFAULT NULL COMMENT 'Data em que o cashback foi utilizado',
  `inscricao_uso_id` int(11) DEFAULT NULL COMMENT 'ID da inscrição onde o cashback foi aplicado',
  `observacao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de cashback de 1% sobre inscrições pagas';

--
-- Despejando dados para a tabela `cashback_atletas`
--

INSERT INTO `cashback_atletas` (`id`, `usuario_id`, `inscricao_id`, `evento_id`, `valor_inscricao`, `valor_cashback`, `percentual`, `status`, `data_credito`, `data_utilizacao`, `inscricao_uso_id`, `observacao`, `created_at`, `updated_at`) VALUES
(1, 22, 13, 8, 20.00, 0.20, 1.00, 'disponivel', '2026-02-06 10:26:58', NULL, NULL, 'Cashback automático - III CORRIDA SAUIM DE COLEIRA', '2026-02-06 13:26:58', '2026-02-06 13:26:58'),
(2, 17, 9, 8, 20.00, 0.20, 1.00, 'disponivel', '2026-02-06 13:09:05', NULL, NULL, 'Cashback automático - III CORRIDA SAUIM DE COLEIRA', '2026-02-06 16:09:05', '2026-02-06 16:09:05'),
(3, 27, 18, 8, 20.00, 0.20, 1.00, 'disponivel', '2026-02-07 21:41:17', NULL, NULL, 'Cashback automático - III CORRIDA SAUIM DE COLEIRA', '2026-02-08 00:41:17', '2026-02-08 00:41:17'),
(4, 27, 19, 8, 20.00, 0.20, 1.00, 'disponivel', '2026-02-07 23:54:32', NULL, NULL, 'Cashback automático - III CORRIDA SAUIM DE COLEIRA', '2026-02-08 02:54:32', '2026-02-08 02:54:32'),
(5, 28, 17, 8, 20.00, 0.20, 1.00, 'disponivel', '2026-02-17 14:26:15', NULL, NULL, 'Cashback automático - III CORRIDA SAUIM DE COLEIRA', '2026-02-17 17:26:15', '2026-02-17 17:26:15'),
(6, 18, 11, 8, 20.00, 0.20, 1.00, 'disponivel', '2026-02-17 22:18:43', NULL, NULL, 'Cashback automático - III CORRIDA SAUIM DE COLEIRA', '2026-02-18 01:18:43', '2026-02-18 01:18:43');

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `tipo_publico` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Publico Geral',
  `idade_min` int(11) DEFAULT '0',
  `idade_max` int(11) DEFAULT '100',
  `desconto_idoso` tinyint(1) DEFAULT '0',
  `exibir_inscricao_geral` tinyint(1) DEFAULT '1',
  `exibir_inscricao_grupos` tinyint(1) DEFAULT '1',
  `titulo_link_oculto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `categorias`
--

INSERT INTO `categorias` (`id`, `evento_id`, `nome`, `descricao`, `tipo_publico`, `idade_min`, `idade_max`, `desconto_idoso`, `exibir_inscricao_geral`, `exibir_inscricao_grupos`, `titulo_link_oculto`, `ativo`, `data_criacao`, `updated_at`) VALUES
(13, 8, 'Kit completo 10 km', '10 km Kit completo', 'Comunidade Acadêmica', 18, 100, 1, 1, 1, NULL, 1, '2025-12-20 05:25:26', '2025-12-20 05:25:26'),
(14, 8, 'Kit completo 5 Km', 'Kit completo 5 km', 'Comunidade acadêmica', 80, 100, 1, 1, 1, NULL, 1, '2025-12-20 05:26:18', '2025-12-20 05:26:18'),
(16, 8, 'Kit completo 10 km - Público em geral', 'Kit completo 10 km - Público em geral', 'Público em geral', 18, 100, 1, 1, 1, NULL, 1, '2025-12-20 05:27:38', '2025-12-20 05:27:38'),
(17, 8, 'Kit completo 5 km - Público em geral', 'Kit completo 5 km - Público em geral.', 'Público em geral', 18, 100, 1, 1, 1, NULL, 1, '2025-12-20 05:28:29', '2025-12-20 05:28:29'),
(18, 8, 'Publico Geral', 'categoria de publico geral', 'pessoa em geral', 0, 80, 1, 1, 1, NULL, 1, '2025-12-20 11:32:01', '2025-12-20 11:32:01'),
(19, 8, 'Comunidade Acadêmica', 'Pessoa do meio acadêmico', 'Docentes em geral', 0, 80, 1, 1, 1, NULL, 1, '2025-12-20 11:33:40', '2025-12-20 11:33:40'),
(20, 10, 'Prova de 40 km', '', 'Corrida Solo', 18, 90, 1, 1, 1, NULL, 1, '2026-02-17 03:55:05', '2026-02-17 03:55:05'),
(21, 10, 'Prova de 80 km', '', 'Corrida Solo de 80 Km', 18, 90, 1, 1, 1, NULL, 1, '2026-02-17 03:56:03', '2026-02-17 03:56:03');

-- --------------------------------------------------------

--
-- Estrutura para tabela `config`
--

CREATE TABLE `config` (
  `id` int(11) NOT NULL,
  `chave` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` text COLLATE utf8mb4_unicode_ci,
  `tipo` enum('string','number','boolean','json','encrypted') COLLATE utf8mb4_unicode_ci DEFAULT 'string',
  `categoria` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `editavel` tinyint(1) DEFAULT '1',
  `visivel` tinyint(1) DEFAULT '1',
  `validacao` text COLLATE utf8mb4_unicode_ci COMMENT 'Regras em JSON',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL COMMENT 'ID do admin/organizador que atualizou (referência flexível)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `config`
--

INSERT INTO `config` (`id`, `chave`, `valor`, `tipo`, `categoria`, `descricao`, `editavel`, `visivel`, `validacao`, `created_at`, `updated_at`, `updated_by`) VALUES
(1, 'sistema.nome', 'MovAmazonas', 'string', 'sistema', 'Nome exibido em títulos e e-mails', 1, 1, NULL, '2025-11-25 15:44:36', '2025-11-25 15:44:36', NULL),
(2, 'sistema.url', '', 'string', 'sistema', 'URL base do sistema (ex.: https://movamazonas.com)', 1, 1, NULL, '2025-11-25 15:44:36', '2025-11-25 15:44:36', NULL),
(3, 'sistema.timezone', 'America/Manaus', 'string', 'sistema', 'Timezone padrão do sistema', 1, 1, NULL, '2025-11-25 15:44:36', '2025-11-25 15:44:36', NULL),
(4, 'sistema.idioma', 'pt-BR', 'string', 'sistema', 'Idioma padrão das interfaces', 1, 1, NULL, '2025-11-25 15:44:36', '2025-11-25 15:44:36', NULL),
(5, 'sistema.manutencao', 'false', 'boolean', 'sistema', 'Habilita modo manutenção', 1, 1, NULL, '2025-11-25 15:44:36', '2025-11-25 15:44:36', NULL),
(6, 'sistema.manutencao_mensagem', '', 'string', 'sistema', 'Mensagem exibida no modo manutenção', 1, 1, NULL, '2025-11-25 15:44:36', '2025-11-25 15:44:36', NULL),
(7, 'sistema.logs_retention_days', '90', 'number', 'sistema', 'Dias para retenção de logs', 1, 1, NULL, '2025-11-25 15:44:36', '2025-11-25 15:44:36', NULL),
(8, 'sistema.max_upload_size', '5242880', 'number', 'sistema', 'Tamanho máximo de upload (bytes)', 1, 1, NULL, '2025-11-25 15:44:36', '2025-11-25 15:44:36', NULL),
(9, 'ai.provedor_ativo', 'openai', 'string', 'ai', 'Provedor de IA atualmente em uso (openai, anthropic, google)', 1, 1, NULL, '2025-11-25 17:55:16', '2025-11-25 17:55:16', NULL),
(10, 'ai.openai.api_key', '', 'encrypted', 'ai', 'Chave API OpenAI (sk-...)', 1, 1, NULL, '2025-11-25 17:55:16', '2025-11-25 17:55:16', NULL),
(11, 'ai.openai.model', 'gpt-4o', 'string', 'ai', 'Modelo OpenAI (gpt-4o, gpt-4-turbo, gpt-3.5-turbo)', 1, 1, NULL, '2025-11-25 17:55:16', '2025-11-25 17:55:16', NULL),
(12, 'ai.openai.temperature', '0.5', 'number', 'ai', 'Temperature OpenAI (0-2)', 1, 1, NULL, '2025-11-25 17:55:16', '2025-11-25 17:55:16', NULL),
(13, 'ai.openai.max_tokens', '8000', 'number', 'ai', 'Máximo de tokens por requisição', 1, 1, NULL, '2025-11-25 17:55:16', '2025-11-25 17:55:16', NULL),
(14, 'ai.anthropic.api_key', '', 'encrypted', 'ai', 'Chave API Anthropic (Claude)', 1, 1, NULL, '2025-11-25 17:55:16', '2025-11-25 17:55:16', NULL),
(15, 'ai.anthropic.model', 'claude-3-5-sonnet-20241022', 'string', 'ai', 'Modelo Anthropic', 1, 1, NULL, '2025-11-25 17:55:16', '2025-11-25 17:55:16', NULL),
(16, 'ai.google.api_key', '', 'encrypted', 'ai', 'Chave API Google Gemini', 1, 1, NULL, '2025-11-25 17:55:16', '2025-11-25 17:55:16', NULL),
(17, 'ai.google.model', 'gemini-pro', 'string', 'ai', 'Modelo Google Gemini', 1, 1, NULL, '2025-11-25 17:55:16', '2025-11-25 17:55:16', NULL),
(18, 'ai.timeout', '120', 'number', 'ai', 'Timeout em segundos para requisições de IA', 1, 1, NULL, '2025-11-25 17:55:16', '2025-11-25 17:55:16', NULL),
(19, 'ai.prompt_treino_base', 'Você é um Profissional de Educação Física especialista em preparação para corridas de rua, com conhecimento profundo em periodização de treinamento, fisiologia do exercício e prevenção de lesões.', 'string', 'ai', 'Prompt base para geração de treinos', 1, 1, NULL, '2025-11-25 17:55:16', '2025-11-25 17:55:16', NULL),
(20, 'treino.exigir_inscricao', 'false', 'boolean', 'treino', 'Exigir inscrição confirmada para gerar treino (desativar apenas temporariamente para testes/desenvolvimento)', 1, 1, NULL, '2026-01-31 01:47:27', '2026-01-31 02:14:26', 2);

-- --------------------------------------------------------

--
-- Estrutura para tabela `config_historico`
--

CREATE TABLE `config_historico` (
  `id` bigint(20) NOT NULL,
  `config_id` int(11) NOT NULL,
  `chave` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor_antigo` text COLLATE utf8mb4_unicode_ci,
  `valor_novo` text COLLATE utf8mb4_unicode_ci,
  `alterado_por` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `config_historico`
--

INSERT INTO `config_historico` (`id`, `config_id`, `chave`, `valor_antigo`, `valor_novo`, `alterado_por`, `created_at`) VALUES
(7, 20, 'treino.exigir_inscricao', 'true', 'false', 2, '2026-01-31 02:14:26');

-- --------------------------------------------------------

--
-- Estrutura para tabela `cupons_remessa`
--

CREATE TABLE `cupons_remessa` (
  `id` int(11) NOT NULL,
  `titulo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_remessa` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor_desconto` decimal(10,2) NOT NULL,
  `tipo_valor` enum('percentual','valor_real','preco_fixo') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_desconto` enum('web','mobile','ambos') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ambos',
  `max_uso` int(11) NOT NULL DEFAULT '1',
  `usos_atuais` int(11) DEFAULT '0',
  `habilita_desconto_itens` tinyint(1) DEFAULT '0',
  `data_criacao` datetime DEFAULT CURRENT_TIMESTAMP,
  `data_inicio` date NOT NULL,
  `data_validade` date NOT NULL,
  `status` enum('ativo','cancelado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ativo',
  `evento_id` int(11) DEFAULT NULL,
  `aplicavel_modalidades` text COLLATE utf8mb4_unicode_ci,
  `aplicavel_categorias` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `cupons_remessa`
--

INSERT INTO `cupons_remessa` (`id`, `titulo`, `codigo_remessa`, `valor_desconto`, `tipo_valor`, `tipo_desconto`, `max_uso`, `usos_atuais`, `habilita_desconto_itens`, `data_criacao`, `data_inicio`, `data_validade`, `status`, `evento_id`, `aplicavel_modalidades`, `aplicavel_categorias`) VALUES
(4, 'Reitoria da UEA', '989D-ZXJS', 100.00, 'percentual', 'ambos', 20, 0, 0, '2025-12-20 14:23:09', '2025-12-20', '2026-10-19', 'ativo', 8, NULL, NULL),
(9, 'Cupom teste', 'ICOA-PKCV', 50.00, 'percentual', 'ambos', 20, 0, 0, '2026-01-29 12:11:21', '2026-01-25', '2026-01-31', 'ativo', 8, NULL, NULL),
(10, 'Cupom teste', '1T23-L7BK', 50.00, 'percentual', 'ambos', 20, 0, 0, '2026-02-17 01:02:55', '2026-02-17', '2026-09-10', 'ativo', 10, NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `enderecos_entrega`
--

CREATE TABLE `enderecos_entrega` (
  `id` int(11) NOT NULL,
  `inscricao_id` int(11) NOT NULL,
  `cep` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `endereco` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `complemento` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bairro` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cidade` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uf` char(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pais` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `estoque_kits`
--

CREATE TABLE `estoque_kits` (
  `id` int(11) NOT NULL,
  `kit_id` int(11) NOT NULL,
  `quantidade_inicial` int(11) NOT NULL,
  `quantidade_vendida` int(11) DEFAULT '0',
  `quantidade_disponivel` int(11) NOT NULL,
  `data_atualizacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `estoque_produtos_extras`
--

CREATE TABLE `estoque_produtos_extras` (
  `id` int(11) NOT NULL,
  `produto_extra_id` int(11) NOT NULL,
  `quantidade_inicial` int(11) NOT NULL,
  `quantidade_vendida` int(11) DEFAULT '0',
  `quantidade_disponivel` int(11) NOT NULL,
  `data_atualizacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `eventos`
--

CREATE TABLE `eventos` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `data_inicio` date DEFAULT NULL,
  `data_fim` date DEFAULT NULL,
  `categoria` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `genero` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `local` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Local geral do evento - cidade/endereço principal (não confundir com programacao_evento.local que é específico de cada item)',
  `cep` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url_mapa` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logradouro` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cidade` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pais` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Brasil',
  `regulamento` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `organizador_id` int(11) NOT NULL,
  `taxa_setup` decimal(10,2) DEFAULT NULL,
  `percentual_repasse` decimal(5,2) DEFAULT NULL,
  `exibir_retirada_kit` tinyint(1) DEFAULT '0',
  `taxa_gratuitas` decimal(10,2) DEFAULT NULL,
  `taxa_pagas` decimal(10,2) DEFAULT NULL,
  `limite_vagas` int(11) DEFAULT NULL,
  `data_fim_inscricoes` date DEFAULT NULL,
  `hora_fim_inscricoes` time DEFAULT NULL,
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `hora_inicio` time DEFAULT NULL COMMENT 'Horário geral de início do evento (não confundir com programacao_evento.hora_inicio que é específico de cada item)',
  `data_realizacao` date DEFAULT NULL,
  `imagem` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `delete_reason` text COLLATE utf8mb4_unicode_ci,
  `regulamento_arquivo` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nome/caminho do arquivo de regulamento (PDF/DOC/DOCX)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `eventos`
--

INSERT INTO `eventos` (`id`, `nome`, `descricao`, `data_inicio`, `data_fim`, `categoria`, `genero`, `local`, `cep`, `url_mapa`, `logradouro`, `numero`, `cidade`, `estado`, `pais`, `regulamento`, `status`, `organizador_id`, `taxa_setup`, `percentual_repasse`, `exibir_retirada_kit`, `taxa_gratuitas`, `taxa_pagas`, `limite_vagas`, `data_fim_inscricoes`, `hora_fim_inscricoes`, `data_criacao`, `hora_inicio`, `data_realizacao`, `imagem`, `deleted_at`, `deleted_by`, `delete_reason`, `regulamento_arquivo`) VALUES
(8, 'III CORRIDA SAUIM DE COLEIRA', 'A III Corrida Sauim de Coleira engloba três grandes pilares da nossa sociedade, esporte, por meio da prática de atividade física, meio ambiente, por meio da busca pela preservação ambiental e pela proteção do sauim-de-coleira e a Educação, por meio do Projeto de extensão da UEA o Projeto Primatas, que visa levar para a sociedade manauara conhecimentos de conservação da biodiversidade e sensibilização ambiental.', '2026-10-24', '2026-10-19', 'corrida_rua', 'misto', 'Manaus - AM', '69050-020', 'https://maps.app.goo.gl/6TBM6FtyMU3iDqZGA?g_st=awb', 'Avenida Darcyr Vargas', '1200', 'Manaus', '', 'Brasil', 'sim', 'ativo', 4, 129.50, 5.00, 0, 2.50, 5.00, 2000, '2026-10-19', '23:59:00', '2025-12-19 14:31:48', '06:00:00', '2026-10-24', 'evento_8.png', NULL, NULL, NULL, 'api/uploads/regulamentos/regulamento_8.pdf'),
(10, 'Rota das Baleias', 'Evento único em Santa Catarina', '2026-02-17', '2026-09-07', 'outros', 'misto', 'Imbituba -SC', '88780-973', '', 'Rua Geral', '123', 'Inbituba', 'SC', 'Brasil', 'sim', 'rascunho', 5, 149.00, 7.00, 1, 2.00, NULL, 500, '2026-09-10', '23:59:00', '2026-02-17 03:38:54', '23:59:00', '2026-09-12', 'evento_10.jpg', NULL, NULL, NULL, 'api/uploads/regulamentos/regulamento_10.pdf');

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `eventos_ativos`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `eventos_ativos` (
`id` int(11)
,`nome` varchar(255)
,`descricao` text
,`data_inicio` date
,`data_fim` date
,`categoria` varchar(255)
,`genero` varchar(100)
,`local` varchar(255)
,`cep` varchar(20)
,`url_mapa` varchar(500)
,`logradouro` varchar(255)
,`numero` varchar(20)
,`cidade` varchar(100)
,`estado` varchar(50)
,`pais` varchar(50)
,`regulamento` text
,`status` varchar(50)
,`organizador_id` int(11)
,`taxa_setup` decimal(10,2)
,`percentual_repasse` decimal(5,2)
,`exibir_retirada_kit` tinyint(1)
,`taxa_gratuitas` decimal(10,2)
,`taxa_pagas` decimal(10,2)
,`limite_vagas` int(11)
,`data_fim_inscricoes` date
,`hora_fim_inscricoes` time
,`data_criacao` timestamp
,`hora_inicio` time
,`data_realizacao` date
,`imagem` varchar(500)
,`deleted_at` timestamp
,`deleted_by` int(11)
,`delete_reason` text
);

-- --------------------------------------------------------

--
-- Estrutura para tabela `financeiro_beneficiarios`
--

CREATE TABLE `financeiro_beneficiarios` (
  `id` int(11) NOT NULL,
  `organizador_id` int(11) NOT NULL,
  `tipo` enum('PF','PJ') COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `documento` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('ativo','inativo','pendente_validacao') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente_validacao',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `financeiro_chargebacks`
--

CREATE TABLE `financeiro_chargebacks` (
  `id` int(11) NOT NULL,
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
  `raw_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `financeiro_contas_bancarias`
--

CREATE TABLE `financeiro_contas_bancarias` (
  `id` int(11) NOT NULL,
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
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `financeiro_estornos`
--

CREATE TABLE `financeiro_estornos` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `inscricao_id` int(11) DEFAULT NULL,
  `pagamento_ml_id` int(11) DEFAULT NULL,
  `valor` decimal(10,2) NOT NULL,
  `motivo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('solicitado','em_processamento','concluido','negado','falhou') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'solicitado',
  `solicitado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `concluido_em` datetime DEFAULT NULL,
  `gateway_refund_id` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `raw_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `financeiro_fechamentos`
--

CREATE TABLE `financeiro_fechamentos` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `fechado_por` int(11) DEFAULT NULL,
  `fechado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `receita_bruta` decimal(10,2) NOT NULL DEFAULT '0.00',
  `taxas` decimal(10,2) NOT NULL DEFAULT '0.00',
  `estornos` decimal(10,2) NOT NULL DEFAULT '0.00',
  `chargebacks` decimal(10,2) NOT NULL DEFAULT '0.00',
  `repasses` decimal(10,2) NOT NULL DEFAULT '0.00',
  `saldo_final` decimal(10,2) NOT NULL DEFAULT '0.00',
  `snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `financeiro_ledger`
--

CREATE TABLE `financeiro_ledger` (
  `id` bigint(20) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `origem_tipo` enum('inscricao','pagamento','taxa','repasse','estorno','chargeback','ajuste_manual','produto','outro') COLLATE utf8mb4_unicode_ci NOT NULL,
  `origem_id` bigint(20) DEFAULT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direcao` enum('credito','debito') COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` enum('pendente','disponivel','liquidado','revertido','bloqueado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente',
  `ocorrido_em` datetime NOT NULL,
  `disponivel_em` datetime DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `financeiro_repasses`
--

CREATE TABLE `financeiro_repasses` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `beneficiario_id` int(11) NOT NULL,
  `conta_bancaria_id` int(11) NOT NULL,
  `valor_solicitado` decimal(10,2) NOT NULL,
  `valor_taxa_repasse` decimal(10,2) NOT NULL DEFAULT '0.00',
  `valor_liquido` decimal(10,2) NOT NULL,
  `status` enum('criado','agendado','processando','pago','falhou','cancelado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'criado',
  `agendado_para` date DEFAULT NULL,
  `solicitado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `processado_em` datetime DEFAULT NULL,
  `comprovante_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gateway_transfer_id` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `motivo_falha` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `financeiro_webhook_eventos`
--

CREATE TABLE `financeiro_webhook_eventos` (
  `id` bigint(20) NOT NULL,
  `gateway` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_id` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_id` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `formas_pagamento_evento`
--

CREATE TABLE `formas_pagamento_evento` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `tipo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `detalhes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parcelamento` text COLLATE utf8mb4_unicode_ci,
  `observacoes` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `formas_pagamento_evento`
--

INSERT INTO `formas_pagamento_evento` (`id`, `evento_id`, `tipo`, `detalhes`, `parcelamento`, `observacoes`) VALUES
(4, 2, 'Boleto bancário', 'Boleto bancário: disponível até 13/10/2025', 'a vista e até 2x com juros', 'A partir de 10 de outubro não será mais permitido fazer a inscrição de forma parcelada'),
(5, 2, 'Cartão de crédito', 'Cartão de crédito: disponível até 20/10/2025 / parcelamento em até 2X', 'a vista e até 2x com juros', 'A partir de 10 de outubro não será mais permitido fazer a inscrição de forma parcelada'),
(6, 2, 'Pagamento instantâneo', 'Pagamento instantâneo: disponível até 20/10/2025', 'a vista e até 2x com juros', 'A partir de 10 de outubro não será mais permitido fazer a inscrição de forma parcelada');

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
(9, 17, 8, 25, NULL, NULL, NULL, NULL, 'P', NULL, '[]', 'MOV20260206-0009', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 01:17:00', 'cancelada', 'cancelado', 20.00, 0.00, NULL, '2026-02-06 13:47:10', '2026-02-20 22:59:59', 'boleto', 1, 0, '143979394703', '260742905-f7b563c9-14e1-4ac7-8410-558498ad2708', NULL, 0, NULL, NULL),
(11, 18, 8, 25, NULL, NULL, NULL, NULL, 'M', NULL, '[]', 'MOV20260217-0011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 01:17:44', 'pendente', 'cancelado', 20.00, 0.00, NULL, NULL, NULL, 'pix', 1, 0, '142946577853', '260742905-2d66e472-fe70-48a3-8a66-4e3765f1b56d', NULL, 0, NULL, NULL),
(13, 22, 8, 25, NULL, NULL, NULL, NULL, 'GG', NULL, '[]', 'MOV20260206-0013', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-04 21:33:43', 'confirmada', 'pago', 20.00, 0.00, NULL, '2026-02-06 13:47:02', NULL, 'pix', 1, 0, '144829933062', '260742905-6fb5b199-471e-4100-8fc8-e283ad33b3f6', NULL, 0, NULL, NULL),
(17, 28, 8, 25, NULL, NULL, NULL, NULL, 'G', NULL, '[]', 'MOV20260217-0017', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 01:46:41', 'cancelada', 'cancelado', 20.00, 0.00, NULL, NULL, NULL, NULL, 1, 0, '144511541833', '260742905-34db0a04-cd69-446d-89ad-f537df4e3850', NULL, 0, NULL, NULL),
(18, 29, 8, 25, NULL, NULL, NULL, NULL, 'GG', NULL, '[]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-21 18:45:52', 'pendente', 'processando', 20.00, 0.00, NULL, NULL, NULL, 'pix', 1, 0, 'MOVAMAZON_1771698595_29', NULL, NULL, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `inscricoes_cupons`
--

CREATE TABLE `inscricoes_cupons` (
  `id` int(11) NOT NULL,
  `inscricao_id` int(11) NOT NULL,
  `cupom_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `inscricoes_produtos_extras`
--

CREATE TABLE `inscricoes_produtos_extras` (
  `id` int(11) NOT NULL,
  `inscricao_id` int(11) NOT NULL,
  `produto_extra_evento_id` int(11) NOT NULL,
  `quantidade` int(11) DEFAULT '1',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT 'pendente',
  `data_compra` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `inscricoes_produtos_extras`
--

INSERT INTO `inscricoes_produtos_extras` (`id`, `inscricao_id`, `produto_extra_evento_id`, `quantidade`, `status`, `data_compra`) VALUES
(13, 14, 5, 1, 'pendente', '2026-01-26 19:01:44'),
(19, 16, 5, 1, 'pendente', '2026-01-30 16:47:17'),
(21, 13, 5, 1, 'pendente', '2026-01-30 17:18:06'),
(23, 17, 5, 1, 'pendente', '2026-02-17 22:35:15');

-- --------------------------------------------------------

--
-- Estrutura para tabela `kits_eventos`
--

CREATE TABLE `kits_eventos` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `evento_id` int(11) NOT NULL,
  `modalidade_evento_id` int(11) NOT NULL,
  `kit_template_id` int(11) DEFAULT NULL,
  `valor` decimal(10,2) NOT NULL,
  `foto_kit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disponivel_venda` tinyint(1) DEFAULT '1',
  `preco_calculado` decimal(10,2) DEFAULT '0.00',
  `ativo` tinyint(1) DEFAULT '1',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `kits_eventos`
--

INSERT INTO `kits_eventos` (`id`, `nome`, `descricao`, `evento_id`, `modalidade_evento_id`, `kit_template_id`, `valor`, `foto_kit`, `disponivel_venda`, `preco_calculado`, `ativo`, `data_criacao`, `updated_at`) VALUES
(25, 'Kit para a prova de 10 Km - Comunidade Acadêmica - Corrida 10 Km', 'Kit para a prova de 5 e 10 Km - Comunidade Acadêmica aplicado em Corrida 10 Km', 8, 23, 12, 109.00, 'kit_template_evento_8_kit_25.png', 1, 109.00, 1, '2026-01-24 17:28:38', '2026-01-29 18:23:55'),
(26, 'Kit para a prova de 10 Km - Comunidade Acadêmica - Corrida 5 Km', 'Kit para a prova de 5 Km - Comunidade Acadêmica aplicado em Corrida 5 Km', 8, 24, 12, 109.00, 'kit_template_evento_8_kit_26.png', 1, 109.00, 1, '2026-01-24 17:28:38', '2026-01-29 18:24:17'),
(27, 'Kit do atleta - Público em geral para a prova de 10 Km e 5 km - Corrida 10 Km', 'Kit do atleta - Público em geral para a prova de 10 Km', 8, 21, 5, 129.00, 'kit_template_evento_8_kit_27.png', 1, 129.00, 1, '2026-01-24 17:55:18', '2026-01-29 18:23:31'),
(28, 'Kit do atleta - Público em geral para a prova de 10 Km e 5 km - Corrida 5 Km', 'Kit do atleta - Público em geral para a prova de 5 km', 8, 22, 5, 129.00, 'kit_template_evento_8_kit_28.png', 1, 129.00, 1, '2026-01-24 17:55:18', '2026-01-29 18:23:44'),
(29, 'Kit Teste - Corrida de 10 km para teste', 'Kit Teste aplicado em Corrida de 10 km para teste', 8, 25, 13, 50.00, 'kit_template_evento_8_kit_29.png', 1, 50.00, 1, '2026-01-29 18:24:47', '2026-01-29 18:24:47');

-- --------------------------------------------------------

--
-- Estrutura para tabela `kit_modalidade_evento`
--

CREATE TABLE `kit_modalidade_evento` (
  `kit_id` int(11) NOT NULL,
  `modalidade_evento_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `kit_modalidade_evento`
--

INSERT INTO `kit_modalidade_evento` (`kit_id`, `modalidade_evento_id`) VALUES
(27, 21),
(28, 22),
(25, 23),
(26, 24),
(29, 25);

-- --------------------------------------------------------

--
-- Estrutura para tabela `kit_produtos`
--

CREATE TABLE `kit_produtos` (
  `id` int(11) NOT NULL,
  `kit_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL DEFAULT '1',
  `ordem` int(11) NOT NULL DEFAULT '0',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `kit_produtos`
--

INSERT INTO `kit_produtos` (`id`, `kit_id`, `produto_id`, `quantidade`, `ordem`, `ativo`, `updated_at`) VALUES
(60, 27, 37, 1, 1, 1, '2026-02-17 22:54:58'),
(61, 28, 37, 1, 1, 1, '2026-02-17 22:54:58'),
(62, 25, 43, 1, 1, 1, '2026-02-17 22:55:33'),
(63, 26, 43, 1, 1, 1, '2026-02-17 22:55:33'),
(64, 29, 30, 1, 1, 1, '2026-02-17 22:55:54');

-- --------------------------------------------------------

--
-- Estrutura para tabela `kit_templates`
--

CREATE TABLE `kit_templates` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `preco_base` decimal(10,2) DEFAULT '0.00',
  `foto_kit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disponivel_venda` tinyint(1) DEFAULT '1',
  `ativo` tinyint(1) DEFAULT '1',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `kit_templates`
--

INSERT INTO `kit_templates` (`id`, `nome`, `descricao`, `preco_base`, `foto_kit`, `disponivel_venda`, `ativo`, `data_criacao`, `updated_at`) VALUES
(5, 'Kit do atleta - Público em geral para a prova de 10 Km e 5 km', 'Kit completo para a prova de 10 Km e 5 km - Público em Geral', 129.00, 'kit_template_kit_5.png', 1, 1, '2025-12-20 12:13:47', '2026-02-17 22:54:58'),
(12, 'Kit para a prova de 5 e 10 Km - Comunidade Acadêmica', 'Kit completo para a prova de 5 e 10 Km - Comunidade acadêmica.', 109.00, 'kit_template_kit_12.png', 1, 1, '2025-12-29 22:51:59', '2026-02-17 22:55:33'),
(13, 'Kit Teste', '', 50.00, 'kit_template_kit_13.png', 1, 1, '2026-01-29 17:26:23', '2026-02-17 22:55:54'),
(14, 'Kit para a prova de 40 km', 'Kit completo para a prova de 40 km', 200.00, 'kit_template_kit_14.jpeg', 1, 1, '2026-02-17 04:11:38', '2026-02-17 04:11:38'),
(15, 'Kit para a prova de 80 km', 'Kit completo para a prova de 80 km', 200.00, 'kit_template_kit_15.jpeg', 1, 1, '2026-02-17 04:12:17', '2026-02-17 04:12:17');

-- --------------------------------------------------------

--
-- Estrutura para tabela `kit_template_produtos`
--

CREATE TABLE `kit_template_produtos` (
  `id` int(11) NOT NULL,
  `kit_template_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` int(11) DEFAULT '1',
  `ordem` int(11) DEFAULT '0',
  `ativo` tinyint(1) DEFAULT '1',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `kit_template_produtos`
--

INSERT INTO `kit_template_produtos` (`id`, `kit_template_id`, `produto_id`, `quantidade`, `ordem`, `ativo`, `data_criacao`) VALUES
(92, 5, 37, 1, 1, 1, '2026-02-17 22:54:58'),
(93, 12, 43, 1, 1, 1, '2026-02-17 22:55:33'),
(94, 13, 30, 1, 1, 1, '2026-02-17 22:55:54');

-- --------------------------------------------------------

--
-- Estrutura para tabela `leads_organizadores`
--

CREATE TABLE `leads_organizadores` (
  `id` int(11) NOT NULL,
  `nome_completo` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `empresa` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `regiao` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `modalidade_esportiva` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantidade_eventos` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome_evento` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `regulamento` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `indicacao` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('novo','contatado','convertido','descartado') COLLATE utf8mb4_unicode_ci DEFAULT 'novo',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `data_contato` timestamp NULL DEFAULT NULL,
  `observacoes` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `leads_organizadores`
--

INSERT INTO `leads_organizadores` (`id`, `nome_completo`, `email`, `telefone`, `empresa`, `regiao`, `modalidade_esportiva`, `quantidade_eventos`, `nome_evento`, `regulamento`, `indicacao`, `status`, `data_criacao`, `data_contato`, `observacoes`) VALUES
(4, 'Eudimaci Barboza de Lira', 'eudimaci.pecim@gmail.com.br', '+55 +55 92982027654', 'EBL Eventos Esportivos', 'AM', 'corrida-rua', '1', 'III Corrida Sauim de Coleira', 'sim', 'Amigos', 'novo', '2025-12-19 13:44:06', NULL, NULL),
(5, 'EUDIMACI BARBOZA DE LIRA', 'eudimaci08@yahoo.com.br', '+55 +55 92982027654', 'EBL EVENTOS ESPORTIVOS', 'AM', 'corrida-rua', '1', 'III CORRIDA SAUIM DE COLEIRA', 'sim', 'Amigos', 'novo', '2025-12-19 14:21:32', NULL, NULL),
(6, 'EUDIMACI BARBOZA DE LIRA', 'moveromundobrasil@gmail.com', '+55 +55 92981630385', 'Projeto Primatas.', 'AM', 'corrida-rua', '1', 'Rota das Baleias', 'sim', NULL, 'novo', '2026-02-02 19:52:02', NULL, NULL),
(7, 'EUDIMACI BARBOZA DE LIRA', 'eudimaci08@yahoo.com.br', '+55 +55 92982027654', 'EBL Eventos Esportivos', 'AM', 'corrida-rua', '2-4', 'III Corrida Sauim de Coleira', 'sim', 'Internet', 'novo', '2026-02-07 14:19:21', NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `logs_admin`
--

CREATE TABLE `logs_admin` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `acao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_acao` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `logs_inscricoes_pagamentos`
--

CREATE TABLE `logs_inscricoes_pagamentos` (
  `id` bigint(20) NOT NULL,
  `nivel` enum('ERROR','WARNING','INFO','SUCCESS') COLLATE utf8mb4_unicode_ci NOT NULL,
  `acao` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `inscricao_id` int(11) DEFAULT NULL,
  `payment_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `evento_id` int(11) DEFAULT NULL,
  `modalidade_id` int(11) DEFAULT NULL,
  `valor_total` decimal(10,2) DEFAULT NULL,
  `forma_pagamento` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_pagamento` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mensagem` text COLLATE utf8mb4_unicode_ci,
  `dados_contexto` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON com dados adicionais',
  `stack_trace` text COLLATE utf8mb4_unicode_ci COMMENT 'Stack trace para erros',
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `logs_inscricoes_pagamentos`
--

INSERT INTO `logs_inscricoes_pagamentos` (`id`, `nivel`, `acao`, `inscricao_id`, `payment_id`, `usuario_id`, `evento_id`, `modalidade_id`, `valor_total`, `forma_pagamento`, `status_pagamento`, `mensagem`, `dados_contexto`, `stack_trace`, `ip`, `user_agent`, `created_at`) VALUES
(1, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 17, 8, 21, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":129,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-21 14:17:18'),
(2, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 9, NULL, 17, 8, 21, 129.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-21 14:17:18'),
(3, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 9, NULL, 17, 8, NULL, 129.00, NULL, NULL, NULL, '{\"external_reference\":\"139279555886\"}', NULL, '192.185.177.54', 'unknown', '2026-01-21 14:17:18'),
(4, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 17, 8, 21, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":129,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-21 14:17:48'),
(5, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 9, NULL, 17, 8, 21, 129.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-21 14:17:48'),
(6, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 9, NULL, 17, 8, NULL, 129.00, NULL, NULL, NULL, '{\"external_reference\":\"139279555886\"}', NULL, '192.185.177.54', 'unknown', '2026-01-21 14:17:48'),
(7, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 17, 8, 21, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":129,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-21 14:18:18'),
(8, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 9, NULL, 17, 8, 21, 129.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-21 14:18:18'),
(9, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 9, NULL, 17, 8, NULL, 129.00, NULL, NULL, NULL, '{\"external_reference\":\"139279555886\"}', NULL, '192.185.177.54', 'unknown', '2026-01-21 14:18:18'),
(10, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 17, 8, 21, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":129,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 14:18:31'),
(11, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 9, NULL, 17, 8, 21, 129.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 14:18:31'),
(12, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 9, NULL, 17, 8, NULL, 129.00, NULL, NULL, NULL, '{\"external_reference\":\"139279555886\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 14:18:31'),
(13, 'SUCCESS', 'PREFERENCE_CRIADA', 9, NULL, NULL, NULL, NULL, 129.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-f36eb8d8-b7b6-4afd-82dc-6152e686d28f\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-f36eb8d8-b7b6-4afd-82dc-6152e686d28f\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 14:18:32'),
(14, 'INFO', 'INICIO_GERACAO_BOLETO', 9, NULL, NULL, NULL, NULL, 129.00, NULL, NULL, NULL, NULL, NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 14:18:47'),
(15, 'ERROR', 'BOLETO_REJEITADO', 9, '142256871907', NULL, NULL, NULL, NULL, NULL, NULL, 'Boleto rejeitado: rejected_by_bank', '{\"status\":\"rejected\",\"status_detail\":\"rejected_by_bank\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 14:18:49'),
(16, 'ERROR', 'ERRO_GERACAO_BOLETO', 9, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Boleto rejeitado pelo Mercado Pago: rejected_by_bank', NULL, '#0 {main}', '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 14:18:49'),
(17, 'INFO', 'INICIO_GERACAO_PIX', 9, NULL, NULL, NULL, NULL, 129.00, NULL, NULL, NULL, NULL, NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 14:19:48'),
(18, 'SUCCESS', 'PIX_GERADO', 9, '142930334552', NULL, NULL, NULL, 129.00, 'pix', 'rejeitado', NULL, '{\"qr_code\":\"000******\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 14:19:50'),
(19, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 17, 8, 21, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":129,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-22 15:05:04'),
(20, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 9, NULL, 17, 8, 21, 129.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-22 15:05:04'),
(21, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 9, NULL, 17, 8, NULL, 129.00, NULL, NULL, NULL, '{\"external_reference\":\"142930334552\"}', NULL, '192.185.177.54', 'unknown', '2026-01-22 15:05:04'),
(22, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 17, 8, 21, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":129,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '187.40.13.74', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 15:07:24'),
(23, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 9, NULL, 17, 8, 21, 129.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '187.40.13.74', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 15:07:24'),
(24, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 9, NULL, 17, 8, NULL, 129.00, NULL, NULL, NULL, '{\"external_reference\":\"142930334552\"}', NULL, '187.40.13.74', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 15:07:24'),
(25, 'SUCCESS', 'PREFERENCE_CRIADA', 9, NULL, NULL, NULL, NULL, 129.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-15f66511-2c89-4e20-9450-0a2c7f6ada3c\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-15f66511-2c89-4e20-9450-0a2c7f6ada3c\"}', NULL, '187.40.13.74', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 15:07:24'),
(26, 'INFO', 'INICIO_GERACAO_BOLETO', 9, NULL, NULL, NULL, NULL, 129.00, NULL, NULL, NULL, NULL, NULL, '187.40.13.74', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 15:07:29'),
(27, 'ERROR', 'BOLETO_REJEITADO', 9, '143073199764', NULL, NULL, NULL, NULL, NULL, NULL, 'Boleto rejeitado: rejected_by_bank', '{\"status\":\"rejected\",\"status_detail\":\"rejected_by_bank\"}', NULL, '187.40.13.74', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 15:07:31'),
(28, 'ERROR', 'ERRO_GERACAO_BOLETO', 9, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Boleto rejeitado pelo Mercado Pago: rejected_by_bank', NULL, '#0 {main}', '187.40.13.74', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 15:07:31'),
(29, 'INFO', 'INICIO_GERACAO_PIX', 9, NULL, NULL, NULL, NULL, 129.00, NULL, NULL, NULL, NULL, NULL, '187.40.13.74', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 15:07:58'),
(30, 'ERROR', 'VALIDACAO_EMAIL_FALHOU', 9, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"email\":\"vazio\"}', NULL, '187.40.13.74', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 15:07:58'),
(31, 'ERROR', 'ERRO_GERACAO_PIX', 9, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Email do pagador não encontrado ou inválido. Verifique os dados da inscrição.', NULL, '#0 {main}', '187.40.13.74', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 15:07:58'),
(32, 'SUCCESS', 'PAGAMENTO_PROCESSADO', NULL, '143074131872', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"status\":\"approved\",\"status_detail\":\"accredited\",\"transaction_amount\":129,\"payment_method_id\":\"master\"}', NULL, '187.40.13.74', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 15:11:02'),
(33, 'SUCCESS', 'PREFERENCE_CRIADA', 9, NULL, NULL, NULL, NULL, 129.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-dd35e05d-55d6-497a-8670-4ebf85c4f568\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-dd35e05d-55d6-497a-8670-4ebf85c4f568\"}', NULL, '187.40.13.74', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 15:13:03'),
(34, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 22, 8, 24, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":109,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-22 20:19:14'),
(35, 'SUCCESS', 'CRIACAO_INSCRICAO', 13, NULL, 22, 8, 24, 109.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0,\"external_reference\":\"MOVAMAZON_1769113154_22\"}', NULL, '192.185.177.54', 'unknown', '2026-01-22 20:19:14'),
(36, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 13, NULL, 22, 8, NULL, 109.00, NULL, NULL, NULL, '{\"external_reference\":\"MOVAMAZON_1769113154_22\"}', NULL, '192.185.177.54', 'unknown', '2026-01-22 20:19:14'),
(37, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 22, 8, 24, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":109,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-22 20:20:14'),
(38, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 13, NULL, 22, 8, 24, 109.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-22 20:20:14'),
(39, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 13, NULL, 22, 8, NULL, 109.00, NULL, NULL, NULL, '{\"external_reference\":\"MOVAMAZON_1769113154_22\"}', NULL, '192.185.177.54', 'unknown', '2026-01-22 20:20:14'),
(40, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 22, 8, 24, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":109,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-22 20:20:44'),
(41, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 13, NULL, 22, 8, 24, 109.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-22 20:20:44'),
(42, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 13, NULL, 22, 8, NULL, 109.00, NULL, NULL, NULL, '{\"external_reference\":\"MOVAMAZON_1769113154_22\"}', NULL, '192.185.177.54', 'unknown', '2026-01-22 20:20:44'),
(43, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 22, 8, 24, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":109,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-22 20:20:44'),
(44, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 13, NULL, 22, 8, 24, 109.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-22 20:20:44'),
(45, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 13, NULL, 22, 8, NULL, 109.00, NULL, NULL, NULL, '{\"external_reference\":\"MOVAMAZON_1769113154_22\"}', NULL, '192.185.177.54', 'unknown', '2026-01-22 20:20:44'),
(46, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 22, 8, 24, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":109,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-22 20:21:14'),
(47, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 13, NULL, 22, 8, 24, 109.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-22 20:21:14'),
(48, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 13, NULL, 22, 8, NULL, 109.00, NULL, NULL, NULL, '{\"external_reference\":\"MOVAMAZON_1769113154_22\"}', NULL, '192.185.177.54', 'unknown', '2026-01-22 20:21:14'),
(49, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 22, 8, 24, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":109,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 20:21:29'),
(50, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 13, NULL, 22, 8, 24, 109.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 20:21:29'),
(51, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 13, NULL, 22, 8, NULL, 109.00, NULL, NULL, NULL, '{\"external_reference\":\"MOVAMAZON_1769113154_22\"}', NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 20:21:29'),
(52, 'SUCCESS', 'PREFERENCE_CRIADA', 13, NULL, NULL, NULL, NULL, 109.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-cd4a8543-0f4f-48c7-93c5-432bb1a78fcf\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-cd4a8543-0f4f-48c7-93c5-432bb1a78fcf\"}', NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 20:21:30'),
(53, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 22, 8, 24, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":109,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-22 20:47:11'),
(54, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 13, NULL, 22, 8, 24, 109.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-22 20:47:11'),
(55, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 13, NULL, 22, 8, NULL, 109.00, NULL, NULL, NULL, '{\"external_reference\":\"MOVAMAZON_1769113154_22\"}', NULL, '192.185.177.54', 'unknown', '2026-01-22 20:47:11'),
(56, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 22, 8, 24, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":109,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 20:47:21'),
(57, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 13, NULL, 22, 8, 24, 109.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 20:47:21'),
(58, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 13, NULL, 22, 8, NULL, 109.00, NULL, NULL, NULL, '{\"external_reference\":\"MOVAMAZON_1769113154_22\"}', NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 20:47:21'),
(59, 'SUCCESS', 'PREFERENCE_CRIADA', 13, NULL, NULL, NULL, NULL, 109.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-f7d45f13-e00d-4e4e-81a7-18e38aa49869\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-f7d45f13-e00d-4e4e-81a7-18e38aa49869\"}', NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 20:47:22'),
(60, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 22, 8, 24, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":109,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 20:56:26'),
(61, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 13, NULL, 22, 8, 24, 109.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 20:56:26'),
(62, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 13, NULL, 22, 8, NULL, 109.00, NULL, NULL, NULL, '{\"external_reference\":\"MOVAMAZON_1769113154_22\"}', NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 20:56:26'),
(63, 'SUCCESS', 'PREFERENCE_CRIADA', 13, NULL, NULL, NULL, NULL, 109.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-9ff9c806-d103-4728-b74d-8165434d555d\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-9ff9c806-d103-4728-b74d-8165434d555d\"}', NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 20:56:27'),
(64, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 22, 8, 21, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":129,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-22 20:59:01'),
(65, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 13, NULL, 22, 8, 21, 129.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-22 20:59:01'),
(66, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 13, NULL, 22, 8, NULL, 129.00, NULL, NULL, NULL, '{\"external_reference\":\"MOVAMAZON_1769113154_22\"}', NULL, '192.185.177.54', 'unknown', '2026-01-22 20:59:01'),
(67, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 22, 8, 21, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":129,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 20:59:05'),
(68, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 13, NULL, 22, 8, 21, 129.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 20:59:05'),
(69, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 13, NULL, 22, 8, NULL, 129.00, NULL, NULL, NULL, '{\"external_reference\":\"MOVAMAZON_1769113154_22\"}', NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 20:59:05'),
(70, 'SUCCESS', 'PREFERENCE_CRIADA', 13, NULL, NULL, NULL, NULL, 129.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-dc0298f8-0e17-4190-abc4-38c58e38260a\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-dc0298f8-0e17-4190-abc4-38c58e38260a\"}', NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 20:59:06'),
(71, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 22, 8, 21, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":129,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 21:02:29'),
(72, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 13, NULL, 22, 8, 21, 129.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 21:02:29'),
(73, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 13, NULL, 22, 8, NULL, 129.00, NULL, NULL, NULL, '{\"external_reference\":\"MOVAMAZON_1769113154_22\"}', NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 21:02:29'),
(74, 'SUCCESS', 'PREFERENCE_CRIADA', 13, NULL, NULL, NULL, NULL, 129.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-f6b8de76-d113-453b-90f9-5341f820febd\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-f6b8de76-d113-453b-90f9-5341f820febd\"}', NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 21:02:30'),
(75, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 18, 8, 21, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":129,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-23 14:35:55'),
(76, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 11, NULL, 18, 8, 21, 129.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-23 14:35:55'),
(77, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 11, NULL, 18, 8, NULL, 129.00, NULL, NULL, NULL, '{\"external_reference\":\"MOVAMAZON_1766772336_18\"}', NULL, '192.185.177.54', 'unknown', '2026-01-23 14:35:55'),
(78, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 18, 8, 21, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":129,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '200.225.112.185', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-23 14:36:10'),
(79, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 11, NULL, 18, 8, 21, 129.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '200.225.112.185', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-23 14:36:10'),
(80, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 11, NULL, 18, 8, NULL, 129.00, NULL, NULL, NULL, '{\"external_reference\":\"MOVAMAZON_1766772336_18\"}', NULL, '200.225.112.185', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-23 14:36:10'),
(81, 'SUCCESS', 'PREFERENCE_CRIADA', 11, NULL, NULL, NULL, NULL, 129.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-4778233b-ffac-4aae-90fd-3a9ea2d7f217\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-4778233b-ffac-4aae-90fd-3a9ea2d7f217\"}', NULL, '200.225.112.185', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-23 14:36:11'),
(82, 'INFO', 'INICIO_GERACAO_PIX', 11, NULL, NULL, NULL, NULL, 129.00, NULL, NULL, NULL, NULL, NULL, '200.225.112.185', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-23 14:36:20'),
(83, 'SUCCESS', 'PIX_GERADO', 11, '142535344817', NULL, NULL, NULL, 129.00, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '200.225.112.185', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-23 14:36:22'),
(84, 'INFO', 'INICIO_GERACAO_PIX', 11, NULL, NULL, NULL, NULL, 129.00, NULL, NULL, NULL, NULL, NULL, '200.225.112.185', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-23 14:36:29'),
(85, 'SUCCESS', 'PIX_GERADO', 11, '142533937639', NULL, NULL, NULL, 129.00, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '200.225.112.185', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-23 14:36:30'),
(86, 'INFO', 'INICIO_GERACAO_PIX', 11, NULL, NULL, NULL, NULL, 129.00, NULL, NULL, NULL, NULL, NULL, '200.225.112.185', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-23 14:39:43'),
(87, 'SUCCESS', 'PIX_GERADO', 11, '143208109084', NULL, NULL, NULL, 129.00, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '200.225.112.185', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-23 14:39:44'),
(88, 'SUCCESS', 'PREFERENCE_CRIADA', 13, NULL, NULL, NULL, NULL, 129.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-b705db57-1c87-4296-970b-fac6996c89a3\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-b705db57-1c87-4296-970b-fac6996c89a3\"}', NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 12:58:05'),
(89, 'INFO', 'INICIO_GERACAO_PIX', 13, NULL, NULL, NULL, NULL, 129.00, NULL, NULL, NULL, NULL, NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 12:58:07'),
(90, 'SUCCESS', 'PIX_GERADO', 13, '142651207047', NULL, NULL, NULL, 129.00, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 12:58:08'),
(91, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 21, 8, 22, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":129,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-26 18:05:07'),
(92, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 7, NULL, 21, 8, 22, 129.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-26 18:05:07'),
(93, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 7, NULL, 21, 8, NULL, 129.00, NULL, NULL, NULL, '{\"external_reference\":\"138832751229\"}', NULL, '192.185.177.54', 'unknown', '2026-01-26 18:05:07'),
(94, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 21, 8, 22, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":129,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '191.189.17.91', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-26 18:05:23'),
(95, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 7, NULL, 21, 8, 22, 129.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '191.189.17.91', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-26 18:05:23'),
(96, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 7, NULL, 21, 8, NULL, 129.00, NULL, NULL, NULL, '{\"external_reference\":\"138832751229\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-26 18:05:23'),
(97, 'SUCCESS', 'PREFERENCE_CRIADA', 7, NULL, NULL, NULL, NULL, 129.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-3b92f457-d175-4e38-bd0d-563f163a240b\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-3b92f457-d175-4e38-bd0d-563f163a240b\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-26 18:05:23'),
(98, 'INFO', 'INICIO_GERACAO_PIX', 7, NULL, NULL, NULL, NULL, 129.00, NULL, NULL, NULL, NULL, NULL, '191.189.17.91', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-26 18:05:27'),
(99, 'SUCCESS', 'PIX_GERADO', 7, '143596976394', NULL, NULL, NULL, 129.00, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-26 18:05:28'),
(100, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 17, 8, 21, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":129,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-26 18:10:31'),
(101, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 9, NULL, 17, 8, 21, 129.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-26 18:10:31'),
(102, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 9, NULL, 17, 8, NULL, 129.00, NULL, NULL, NULL, '{\"external_reference\":\"142930334552\"}', NULL, '192.185.177.54', 'unknown', '2026-01-26 18:10:31'),
(103, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 17, 8, 21, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":129,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 18:10:38'),
(104, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 9, NULL, 17, 8, 21, 129.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 18:10:38'),
(105, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 9, NULL, 17, 8, NULL, 129.00, NULL, NULL, NULL, '{\"external_reference\":\"142930334552\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 18:10:38'),
(106, 'SUCCESS', 'PREFERENCE_CRIADA', 9, NULL, NULL, NULL, NULL, 129.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-09f81432-131a-489c-9f8b-e0cce899d269\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-09f81432-131a-489c-9f8b-e0cce899d269\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 18:10:39'),
(107, 'INFO', 'INICIO_GERACAO_BOLETO', 9, NULL, NULL, NULL, NULL, 129.00, NULL, NULL, NULL, NULL, NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 18:10:43'),
(108, 'ERROR', 'BOLETO_REJEITADO', 9, '142920419809', NULL, NULL, NULL, NULL, NULL, NULL, 'Boleto rejeitado: rejected_by_bank', '{\"status\":\"rejected\",\"status_detail\":\"rejected_by_bank\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 18:10:45'),
(109, 'ERROR', 'ERRO_GERACAO_BOLETO', 9, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Boleto rejeitado pelo Mercado Pago: rejected_by_bank', NULL, '#0 {main}', '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 18:10:45'),
(110, 'SUCCESS', 'PREFERENCE_CRIADA', 13, NULL, NULL, NULL, NULL, 129.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-20ec4c2c-847e-4e21-aa5c-afb634418327\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-20ec4c2c-847e-4e21-aa5c-afb634418327\"}', NULL, '200.165.108.236', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 20:54:02'),
(111, 'INFO', 'INICIO_GERACAO_PIX', 13, NULL, NULL, NULL, NULL, 129.00, NULL, NULL, NULL, NULL, NULL, '200.165.108.236', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 20:54:06'),
(112, 'SUCCESS', 'PIX_GERADO', 13, '143613955998', NULL, NULL, NULL, 129.00, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '200.165.108.236', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 20:54:07'),
(113, 'SUCCESS', 'PREFERENCE_CRIADA', 13, NULL, NULL, NULL, NULL, 129.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-755539ff-4d85-461c-b123-e50065547d2a\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-755539ff-4d85-461c-b123-e50065547d2a\"}', NULL, '200.165.108.236', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 21:00:41'),
(114, 'INFO', 'INICIO_GERACAO_PIX', 13, NULL, NULL, NULL, NULL, 129.00, NULL, NULL, NULL, NULL, NULL, '200.165.108.236', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 21:00:52'),
(115, 'SUCCESS', 'PIX_GERADO', 13, '142940563041', NULL, NULL, NULL, 129.00, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '200.165.108.236', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 21:00:53'),
(116, 'SUCCESS', 'PREFERENCE_CRIADA', 9, NULL, NULL, NULL, NULL, 129.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-80a163c9-e99b-4061-aea0-8a245fca4df3\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-80a163c9-e99b-4061-aea0-8a245fca4df3\"}', NULL, '200.165.108.236', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 21:03:25'),
(117, 'INFO', 'INICIO_GERACAO_BOLETO', 9, NULL, NULL, NULL, NULL, 129.00, NULL, NULL, NULL, NULL, NULL, '200.165.108.236', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 21:03:30'),
(118, 'ERROR', 'BOLETO_REJEITADO', 9, '142941312793', NULL, NULL, NULL, NULL, NULL, NULL, 'Boleto rejeitado: rejected_by_bank', '{\"status\":\"rejected\",\"status_detail\":\"rejected_by_bank\"}', NULL, '200.165.108.236', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 21:03:32'),
(119, 'ERROR', 'ERRO_GERACAO_BOLETO', 9, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Boleto rejeitado pelo Mercado Pago: rejected_by_bank', NULL, '#0 {main}', '200.165.108.236', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 21:03:32'),
(120, 'INFO', 'INICIO_GERACAO_BOLETO', 9, NULL, NULL, NULL, NULL, 129.00, NULL, NULL, NULL, NULL, NULL, '200.165.108.236', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 21:03:37'),
(121, 'ERROR', 'BOLETO_REJEITADO', 9, '142941332677', NULL, NULL, NULL, NULL, NULL, NULL, 'Boleto rejeitado: rejected_by_bank', '{\"status\":\"rejected\",\"status_detail\":\"rejected_by_bank\"}', NULL, '200.165.108.236', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 21:03:39'),
(122, 'ERROR', 'ERRO_GERACAO_BOLETO', 9, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Boleto rejeitado pelo Mercado Pago: rejected_by_bank', NULL, '#0 {main}', '200.165.108.236', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 21:03:39'),
(123, 'INFO', 'INICIO_GERACAO_PIX', 9, NULL, NULL, NULL, NULL, 129.00, NULL, NULL, NULL, NULL, NULL, '200.165.108.236', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 21:04:14'),
(124, 'SUCCESS', 'PIX_GERADO', 9, '142941057097', NULL, NULL, NULL, 129.00, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '200.165.108.236', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 21:04:15'),
(125, 'SUCCESS', 'PREFERENCE_CRIADA', 13, NULL, NULL, NULL, NULL, 129.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-828d8dc3-121c-4a36-ae6f-29d126813857\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-828d8dc3-121c-4a36-ae6f-29d126813857\"}', NULL, '200.165.108.236', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 21:49:38'),
(126, 'SUCCESS', 'PREFERENCE_CRIADA', 13, NULL, NULL, NULL, NULL, 129.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-76b5e11b-3822-46c7-8ae7-2805e1c1101d\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-76b5e11b-3822-46c7-8ae7-2805e1c1101d\"}', NULL, '200.165.108.236', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 21:50:53'),
(127, 'INFO', 'INICIO_GERACAO_BOLETO', 13, NULL, NULL, NULL, NULL, 129.00, NULL, NULL, NULL, NULL, NULL, '200.165.108.236', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 21:51:00'),
(128, 'SUCCESS', 'BOLETO_GERADO', 13, '142947218763', NULL, NULL, NULL, 129.00, 'boleto', 'pendente', NULL, '{\"date_of_expiration\":\"2026-01-29T22:59:59.000-04:00\",\"barcode\":\"422******\"}', NULL, '200.165.108.236', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 21:51:01'),
(129, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 18, 8, 23, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":109,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-26 22:00:39'),
(130, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 11, NULL, 18, 8, 23, 109.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-26 22:00:39'),
(131, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 11, NULL, 18, 8, NULL, 109.00, NULL, NULL, NULL, '{\"external_reference\":\"143208109084\"}', NULL, '192.185.177.54', 'unknown', '2026-01-26 22:00:39'),
(132, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 18, 8, 23, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":109,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '200.225.112.185', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-26 22:00:53'),
(133, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 11, NULL, 18, 8, 23, 109.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '200.225.112.185', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-26 22:00:53'),
(134, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 11, NULL, 18, 8, NULL, 109.00, NULL, NULL, NULL, '{\"external_reference\":\"143208109084\"}', NULL, '200.225.112.185', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-26 22:00:53'),
(135, 'SUCCESS', 'PREFERENCE_CRIADA', 11, NULL, NULL, NULL, NULL, 109.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-e73ed6b8-786f-47a0-a661-2984344fe48f\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-e73ed6b8-786f-47a0-a661-2984344fe48f\"}', NULL, '200.225.112.185', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-26 22:00:54'),
(136, 'INFO', 'INICIO_GERACAO_BOLETO', 11, NULL, NULL, NULL, NULL, 109.00, NULL, NULL, NULL, NULL, NULL, '200.225.112.185', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-26 22:01:17'),
(137, 'SUCCESS', 'BOLETO_GERADO', 11, '142946577853', NULL, NULL, NULL, 109.00, 'boleto', 'pendente', NULL, '{\"date_of_expiration\":\"2026-01-29T22:59:59.000-04:00\",\"barcode\":\"422******\"}', NULL, '200.225.112.185', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-26 22:01:19'),
(138, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 24, 8, 22, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":129,\"valor_extras\":30,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-26 22:01:44'),
(139, 'SUCCESS', 'CRIACAO_INSCRICAO', 14, NULL, 24, 8, 22, 159.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0,\"external_reference\":\"MOVAMAZON_1769464904_24\"}', NULL, '192.185.177.54', 'unknown', '2026-01-26 22:01:44'),
(140, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 14, NULL, 24, 8, NULL, 159.00, NULL, NULL, NULL, '{\"external_reference\":\"MOVAMAZON_1769464904_24\"}', NULL, '192.185.177.54', 'unknown', '2026-01-26 22:01:44'),
(141, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 24, 8, 22, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":129,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '181.77.101.153', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-26 22:02:01'),
(142, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 14, NULL, 24, 8, 22, 129.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '181.77.101.153', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-26 22:02:02'),
(143, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 14, NULL, 24, 8, NULL, 129.00, NULL, NULL, NULL, '{\"external_reference\":\"MOVAMAZON_1769464904_24\"}', NULL, '181.77.101.153', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-26 22:02:02'),
(144, 'SUCCESS', 'PREFERENCE_CRIADA', 14, NULL, NULL, NULL, NULL, 129.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-d1aecc16-bc95-42b4-80d4-6bb31b819e9e\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-d1aecc16-bc95-42b4-80d4-6bb31b819e9e\"}', NULL, '181.77.101.153', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-26 22:02:02'),
(145, 'INFO', 'INICIO_GERACAO_BOLETO', 14, NULL, NULL, NULL, NULL, 129.00, NULL, NULL, NULL, NULL, NULL, '181.77.101.153', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-26 22:02:15'),
(146, 'SUCCESS', 'BOLETO_GERADO', 14, '142946715951', NULL, NULL, NULL, 129.00, 'boleto', 'pendente', NULL, '{\"date_of_expiration\":\"2026-01-29T22:59:59.000-04:00\",\"barcode\":\"422******\"}', NULL, '181.77.101.153', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-26 22:02:16'),
(147, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 22, 8, 25, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":20,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 03:52:52'),
(148, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 13, NULL, 22, 8, 25, 20.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-30 03:52:52'),
(149, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 13, NULL, 22, 8, NULL, 20.00, NULL, NULL, NULL, '{\"external_reference\":\"142947218763\"}', NULL, '192.185.177.54', 'unknown', '2026-01-30 03:52:52'),
(150, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 22, 8, 25, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":20,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 03:53:22'),
(151, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 13, NULL, 22, 8, 25, 20.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-30 03:53:22'),
(152, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 13, NULL, 22, 8, NULL, 20.00, NULL, NULL, NULL, '{\"external_reference\":\"142947218763\"}', NULL, '192.185.177.54', 'unknown', '2026-01-30 03:53:22'),
(153, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 22, 8, 25, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":20,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 03:53:22'),
(154, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 13, NULL, 22, 8, 25, 20.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-30 03:53:22'),
(155, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 13, NULL, 22, 8, NULL, 20.00, NULL, NULL, NULL, '{\"external_reference\":\"142947218763\"}', NULL, '192.185.177.54', 'unknown', '2026-01-30 03:53:22'),
(156, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 22, 8, 25, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":20,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 04:15:27'),
(157, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 13, NULL, 22, 8, 25, 20.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-30 04:15:27'),
(158, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 13, NULL, 22, 8, NULL, 20.00, NULL, NULL, NULL, '{\"external_reference\":\"142947218763\"}', NULL, '192.185.177.54', 'unknown', '2026-01-30 04:15:27'),
(159, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 22, 8, 25, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":20,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 04:15:57'),
(160, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 13, NULL, 22, 8, 25, 20.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-30 04:15:57'),
(161, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 13, NULL, 22, 8, NULL, 20.00, NULL, NULL, NULL, '{\"external_reference\":\"142947218763\"}', NULL, '192.185.177.54', 'unknown', '2026-01-30 04:15:57'),
(162, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 22, 8, 25, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":20,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 04:15:57'),
(163, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 13, NULL, 22, 8, 25, 20.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-30 04:15:57'),
(164, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 13, NULL, 22, 8, NULL, 20.00, NULL, NULL, NULL, '{\"external_reference\":\"142947218763\"}', NULL, '192.185.177.54', 'unknown', '2026-01-30 04:15:57'),
(165, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 22, 8, 22, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":129,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 04:19:13'),
(166, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 13, NULL, 22, 8, 22, 129.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-30 04:19:13'),
(167, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 13, NULL, 22, 8, NULL, 129.00, NULL, NULL, NULL, '{\"external_reference\":\"142947218763\"}', NULL, '192.185.177.54', 'unknown', '2026-01-30 04:19:13'),
(168, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 22, 8, 22, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":129,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 04:19:13'),
(169, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 13, NULL, 22, 8, 22, 129.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-30 04:19:13');
INSERT INTO `logs_inscricoes_pagamentos` (`id`, `nivel`, `acao`, `inscricao_id`, `payment_id`, `usuario_id`, `evento_id`, `modalidade_id`, `valor_total`, `forma_pagamento`, `status_pagamento`, `mensagem`, `dados_contexto`, `stack_trace`, `ip`, `user_agent`, `created_at`) VALUES
(170, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 13, NULL, 22, 8, NULL, 129.00, NULL, NULL, NULL, '{\"external_reference\":\"142947218763\"}', NULL, '192.185.177.54', 'unknown', '2026-01-30 04:19:13'),
(171, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 22, 8, 22, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":129,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 04:19:43'),
(172, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 13, NULL, 22, 8, 22, 129.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-30 04:19:43'),
(173, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 13, NULL, 22, 8, NULL, 129.00, NULL, NULL, NULL, '{\"external_reference\":\"142947218763\"}', NULL, '192.185.177.54', 'unknown', '2026-01-30 04:19:43'),
(174, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 22, 8, 24, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":109,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 04:20:55'),
(175, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 13, NULL, 22, 8, 24, 109.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-30 04:20:55'),
(176, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 13, NULL, 22, 8, NULL, 109.00, NULL, NULL, NULL, '{\"external_reference\":\"142947218763\"}', NULL, '192.185.177.54', 'unknown', '2026-01-30 04:20:55'),
(177, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 22, 8, 24, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":109,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 04:21:55'),
(178, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 13, NULL, 22, 8, 24, 109.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-30 04:21:55'),
(179, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 13, NULL, 22, 8, NULL, 109.00, NULL, NULL, NULL, '{\"external_reference\":\"142947218763\"}', NULL, '192.185.177.54', 'unknown', '2026-01-30 04:21:55'),
(180, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 22, 8, 24, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":109,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 04:21:55'),
(181, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 13, NULL, 22, 8, 24, 109.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-30 04:21:55'),
(182, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 13, NULL, 22, 8, NULL, 109.00, NULL, NULL, NULL, '{\"external_reference\":\"142947218763\"}', NULL, '192.185.177.54', 'unknown', '2026-01-30 04:21:55'),
(183, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 22, 8, 25, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":20,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 04:43:47'),
(184, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 13, NULL, 22, 8, 25, 20.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-30 04:43:47'),
(185, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 13, NULL, 22, 8, NULL, 20.00, NULL, NULL, NULL, '{\"external_reference\":\"142947218763\"}', NULL, '192.185.177.54', 'unknown', '2026-01-30 04:43:47'),
(186, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 22, 8, 25, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":20,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 04:44:17'),
(187, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 13, NULL, 22, 8, 25, 20.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-30 04:44:17'),
(188, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 13, NULL, 22, 8, NULL, 20.00, NULL, NULL, NULL, '{\"external_reference\":\"142947218763\"}', NULL, '192.185.177.54', 'unknown', '2026-01-30 04:44:17'),
(189, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 17, 8, 25, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":20,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 12:33:08'),
(190, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 9, NULL, 17, 8, 25, 20.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-30 12:33:08'),
(191, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 9, NULL, 17, 8, NULL, 20.00, NULL, NULL, NULL, '{\"external_reference\":\"142941057097\"}', NULL, '192.185.177.54', 'unknown', '2026-01-30 12:33:08'),
(192, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 17, 8, 25, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":20,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 12:33:38'),
(193, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 9, NULL, 17, 8, 25, 20.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-30 12:33:38'),
(194, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 9, NULL, 17, 8, NULL, 20.00, NULL, NULL, NULL, '{\"external_reference\":\"142941057097\"}', NULL, '192.185.177.54', 'unknown', '2026-01-30 12:33:38'),
(195, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 17, 8, 25, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":20,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '191.189.17.91', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-30 12:33:48'),
(196, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 9, NULL, 17, 8, 25, 20.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '191.189.17.91', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-30 12:33:48'),
(197, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 9, NULL, 17, 8, NULL, 20.00, NULL, NULL, NULL, '{\"external_reference\":\"142941057097\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-30 12:33:48'),
(198, 'SUCCESS', 'PREFERENCE_CRIADA', 9, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-03913002-1eb9-46b5-99fe-f2197d15a1b3\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-03913002-1eb9-46b5-99fe-f2197d15a1b3\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-30 12:33:49'),
(199, 'INFO', 'INICIO_GERACAO_BOLETO', 9, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, NULL, NULL, '191.189.17.91', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-30 12:33:53'),
(200, 'ERROR', 'BOLETO_REJEITADO', 9, '144090757920', NULL, NULL, NULL, NULL, NULL, NULL, 'Boleto rejeitado: rejected_by_bank', '{\"status\":\"rejected\",\"status_detail\":\"rejected_by_bank\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-30 12:33:54'),
(201, 'ERROR', 'ERRO_GERACAO_BOLETO', 9, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Boleto rejeitado pelo Mercado Pago: rejected_by_bank', NULL, '#0 {main}', '191.189.17.91', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-30 12:33:54'),
(202, 'INFO', 'INICIO_GERACAO_PIX', 9, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, NULL, NULL, '191.189.17.91', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-30 12:36:03'),
(203, 'SUCCESS', 'PIX_GERADO', 9, '144090968038', NULL, NULL, NULL, 21.00, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-30 12:36:04'),
(204, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 17, 8, 21, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":129,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 12:41:01'),
(205, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 9, NULL, 17, 8, 21, 129.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-30 12:41:01'),
(206, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 9, NULL, 17, 8, NULL, 129.00, NULL, NULL, NULL, '{\"external_reference\":\"144090968038\"}', NULL, '192.185.177.54', 'unknown', '2026-01-30 12:41:01'),
(207, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 17, 8, 21, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":129,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 12:41:01'),
(208, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 9, NULL, 17, 8, 21, 129.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-30 12:41:01'),
(209, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 9, NULL, 17, 8, NULL, 129.00, NULL, NULL, NULL, '{\"external_reference\":\"144090968038\"}', NULL, '192.185.177.54', 'unknown', '2026-01-30 12:41:01'),
(210, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 17, 8, 21, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":129,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '191.189.17.91', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-30 12:41:18'),
(211, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 9, NULL, 17, 8, 21, 129.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '191.189.17.91', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-30 12:41:18'),
(212, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 9, NULL, 17, 8, NULL, 129.00, NULL, NULL, NULL, '{\"external_reference\":\"144090968038\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-30 12:41:18'),
(213, 'SUCCESS', 'PREFERENCE_CRIADA', 9, NULL, NULL, NULL, NULL, 135.45, NULL, NULL, NULL, '{\"preference_id\":\"260742905-35273706-a510-4ea6-8525-f3d583412795\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-35273706-a510-4ea6-8525-f3d583412795\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-30 12:41:19'),
(214, 'INFO', 'INICIO_GERACAO_BOLETO', 9, NULL, NULL, NULL, NULL, 135.45, NULL, NULL, NULL, NULL, NULL, '191.189.17.91', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-30 12:41:23'),
(215, 'ERROR', 'BOLETO_REJEITADO', 9, '143414772541', NULL, NULL, NULL, NULL, NULL, NULL, 'Boleto rejeitado: rejected_by_bank', '{\"status\":\"rejected\",\"status_detail\":\"rejected_by_bank\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-30 12:41:25'),
(216, 'ERROR', 'ERRO_GERACAO_BOLETO', 9, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Boleto rejeitado pelo Mercado Pago: rejected_by_bank', NULL, '#0 {main}', '191.189.17.91', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-30 12:41:25'),
(217, 'INFO', 'INICIO_GERACAO_PIX', 9, NULL, NULL, NULL, NULL, 135.45, NULL, NULL, NULL, NULL, NULL, '191.189.17.91', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-30 12:42:03'),
(218, 'SUCCESS', 'PIX_GERADO', 9, '144092960470', NULL, NULL, NULL, 135.45, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-30 12:42:04'),
(219, 'SUCCESS', 'PREFERENCE_CRIADA', 13, NULL, NULL, NULL, NULL, 20.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-62bdfd18-86a0-45dc-8323-06942d3260f5\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-62bdfd18-86a0-45dc-8323-06942d3260f5\"}', NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 13:05:12'),
(220, 'INFO', 'INICIO_GERACAO_PIX', 13, NULL, NULL, NULL, NULL, 20.00, NULL, NULL, NULL, NULL, NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 13:05:18'),
(221, 'SUCCESS', 'PIX_GERADO', 13, '144095812464', NULL, NULL, NULL, 20.00, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 13:05:19'),
(222, 'INFO', 'INICIO_GERACAO_BOLETO', 13, NULL, NULL, NULL, NULL, 20.00, NULL, NULL, NULL, NULL, NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 13:05:56'),
(223, 'SUCCESS', 'BOLETO_GERADO', 13, '144095093266', NULL, NULL, NULL, 20.00, 'boleto', 'pendente', NULL, '{\"date_of_expiration\":\"2026-02-02T22:59:59.000-04:00\",\"barcode\":\"422******\"}', NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 13:05:58'),
(224, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 17, 8, 25, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":20,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 14:02:18'),
(225, 'ERROR', 'ERRO_SALVAMENTO_INSCRICAO', NULL, NULL, 17, 8, NULL, NULL, NULL, NULL, 'Path cannot be empty', NULL, '#0 /home2/brunor90/movamazon.com.br/api/inscricao/save_inscricao.php(66): file_put_contents(\'\', \'{\"location\":\"sa...\', 8)\n#1 {main}', '192.185.177.54', 'unknown', '2026-01-30 14:02:18'),
(226, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 17, 8, 25, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":20,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 14:02:48'),
(227, 'ERROR', 'ERRO_SALVAMENTO_INSCRICAO', NULL, NULL, 17, 8, NULL, NULL, NULL, NULL, 'Path cannot be empty', NULL, '#0 /home2/brunor90/movamazon.com.br/api/inscricao/save_inscricao.php(66): file_put_contents(\'\', \'{\"location\":\"sa...\', 8)\n#1 {main}', '192.185.177.54', 'unknown', '2026-01-30 14:02:48'),
(228, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 17, 8, 25, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":20,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '181.77.100.253', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 14:03:40'),
(229, 'ERROR', 'ERRO_SALVAMENTO_INSCRICAO', NULL, NULL, 17, 8, NULL, NULL, NULL, NULL, 'Path cannot be empty', NULL, '#0 /home2/brunor90/movamazon.com.br/api/inscricao/save_inscricao.php(66): file_put_contents(\'\', \'{\"location\":\"sa...\', 8)\n#1 {main}', '181.77.100.253', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 14:03:40'),
(230, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 17, 8, 25, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":20,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 17:30:35'),
(231, 'ERROR', 'ERRO_SALVAMENTO_INSCRICAO', NULL, NULL, 17, 8, NULL, NULL, NULL, NULL, 'Path cannot be empty', NULL, '#0 /home2/brunor90/movamazon.com.br/api/inscricao/save_inscricao.php(66): file_put_contents(\'\', \'{\"location\":\"sa...\', 8)\n#1 {main}', '192.185.177.54', 'unknown', '2026-01-30 17:30:35'),
(232, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 17, 8, 25, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":20,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 17:31:05'),
(233, 'ERROR', 'ERRO_SALVAMENTO_INSCRICAO', NULL, NULL, 17, 8, NULL, NULL, NULL, NULL, 'Path cannot be empty', NULL, '#0 /home2/brunor90/movamazon.com.br/api/inscricao/save_inscricao.php(66): file_put_contents(\'\', \'{\"location\":\"sa...\', 8)\n#1 {main}', '192.185.177.54', 'unknown', '2026-01-30 17:31:05'),
(234, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 17, 8, 25, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":20,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '181.77.101.8', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-30 17:31:15'),
(235, 'ERROR', 'ERRO_SALVAMENTO_INSCRICAO', NULL, NULL, 17, 8, NULL, NULL, NULL, NULL, 'Path cannot be empty', NULL, '#0 /home2/brunor90/movamazon.com.br/api/inscricao/save_inscricao.php(66): file_put_contents(\'\', \'{\"location\":\"sa...\', 8)\n#1 {main}', '181.77.101.8', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-30 17:31:15'),
(236, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 25, 8, 22, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":129,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 18:15:28'),
(237, 'ERROR', 'ERRO_SALVAMENTO_INSCRICAO', NULL, NULL, 25, 8, NULL, NULL, NULL, NULL, 'Path cannot be empty', NULL, '#0 /home2/brunor90/movamazon.com.br/api/inscricao/save_inscricao.php(66): file_put_contents(\'\', \'{\"location\":\"sa...\', 8)\n#1 {main}', '192.185.177.54', 'unknown', '2026-01-30 18:15:28'),
(238, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 25, 8, 22, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":129,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 18:15:58'),
(239, 'ERROR', 'ERRO_SALVAMENTO_INSCRICAO', NULL, NULL, 25, 8, NULL, NULL, NULL, NULL, 'Path cannot be empty', NULL, '#0 /home2/brunor90/movamazon.com.br/api/inscricao/save_inscricao.php(66): file_put_contents(\'\', \'{\"location\":\"sa...\', 8)\n#1 {main}', '192.185.177.54', 'unknown', '2026-01-30 18:15:58'),
(240, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 25, 8, 22, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":129,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '200.206.115.131', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-30 18:17:04'),
(241, 'ERROR', 'ERRO_SALVAMENTO_INSCRICAO', NULL, NULL, 25, 8, NULL, NULL, NULL, NULL, 'Path cannot be empty', NULL, '#0 /home2/brunor90/movamazon.com.br/api/inscricao/save_inscricao.php(66): file_put_contents(\'\', \'{\"location\":\"sa...\', 8)\n#1 {main}', '200.206.115.131', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-30 18:17:04'),
(242, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 26, 8, 24, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":109,\"valor_extras\":30,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 18:20:12'),
(243, 'ERROR', 'ERRO_SALVAMENTO_INSCRICAO', NULL, NULL, 26, 8, NULL, NULL, NULL, NULL, 'Path cannot be empty', NULL, '#0 /home2/brunor90/movamazon.com.br/api/inscricao/save_inscricao.php(66): file_put_contents(\'\', \'{\"location\":\"sa...\', 8)\n#1 {main}', '192.185.177.54', 'unknown', '2026-01-30 18:20:12'),
(244, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 26, 8, 24, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":109,\"valor_extras\":30,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 18:21:42'),
(245, 'ERROR', 'ERRO_SALVAMENTO_INSCRICAO', NULL, NULL, 26, 8, NULL, NULL, NULL, NULL, 'Path cannot be empty', NULL, '#0 /home2/brunor90/movamazon.com.br/api/inscricao/save_inscricao.php(66): file_put_contents(\'\', \'{\"location\":\"sa...\', 8)\n#1 {main}', '192.185.177.54', 'unknown', '2026-01-30 18:21:42'),
(246, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 26, 8, 24, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":109,\"valor_extras\":30,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 18:21:42'),
(247, 'ERROR', 'ERRO_SALVAMENTO_INSCRICAO', NULL, NULL, 26, 8, NULL, NULL, NULL, NULL, 'Path cannot be empty', NULL, '#0 /home2/brunor90/movamazon.com.br/api/inscricao/save_inscricao.php(66): file_put_contents(\'\', \'{\"location\":\"sa...\', 8)\n#1 {main}', '192.185.177.54', 'unknown', '2026-01-30 18:21:42'),
(248, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 26, 8, 24, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":109,\"valor_extras\":30,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 18:21:42'),
(249, 'ERROR', 'ERRO_SALVAMENTO_INSCRICAO', NULL, NULL, 26, 8, NULL, NULL, NULL, NULL, 'Path cannot be empty', NULL, '#0 /home2/brunor90/movamazon.com.br/api/inscricao/save_inscricao.php(66): file_put_contents(\'\', \'{\"location\":\"sa...\', 8)\n#1 {main}', '192.185.177.54', 'unknown', '2026-01-30 18:21:42'),
(250, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 26, 8, 24, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":109,\"valor_extras\":30,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 18:28:20'),
(251, 'ERROR', 'ERRO_SALVAMENTO_INSCRICAO', NULL, NULL, 26, 8, NULL, NULL, NULL, NULL, 'Path cannot be empty', NULL, '#0 /home2/brunor90/movamazon.com.br/api/inscricao/save_inscricao.php(66): file_put_contents(\'\', \'{\"location\":\"sa...\', 8)\n#1 {main}', '192.185.177.54', 'unknown', '2026-01-30 18:28:20'),
(252, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 26, 8, 24, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":109,\"valor_extras\":30,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 18:28:20'),
(253, 'ERROR', 'ERRO_SALVAMENTO_INSCRICAO', NULL, NULL, 26, 8, NULL, NULL, NULL, NULL, 'Path cannot be empty', NULL, '#0 /home2/brunor90/movamazon.com.br/api/inscricao/save_inscricao.php(66): file_put_contents(\'\', \'{\"location\":\"sa...\', 8)\n#1 {main}', '192.185.177.54', 'unknown', '2026-01-30 18:28:20'),
(254, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 25, 8, 22, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":129,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 19:11:09'),
(255, 'SUCCESS', 'CRIACAO_INSCRICAO', 15, NULL, 25, 8, 22, 129.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0,\"external_reference\":\"MOVAMAZON_1769800269_25\"}', NULL, '192.185.177.54', 'unknown', '2026-01-30 19:11:09'),
(256, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 15, NULL, 25, 8, NULL, 129.00, NULL, NULL, NULL, '{\"external_reference\":\"MOVAMAZON_1769800269_25\"}', NULL, '192.185.177.54', 'unknown', '2026-01-30 19:11:09'),
(257, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 25, 8, 22, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":129,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 19:11:09'),
(258, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 15, NULL, 25, 8, 22, 129.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-30 19:11:09'),
(259, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 15, NULL, 25, 8, NULL, 129.00, NULL, NULL, NULL, '{\"external_reference\":\"MOVAMAZON_1769800269_25\"}', NULL, '192.185.177.54', 'unknown', '2026-01-30 19:11:09'),
(260, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 25, 8, 22, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":129,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', '2026-01-30 19:11:21'),
(261, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 15, NULL, 25, 8, 22, 129.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', '2026-01-30 19:11:21'),
(262, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 15, NULL, 25, 8, NULL, 129.00, NULL, NULL, NULL, '{\"external_reference\":\"MOVAMAZON_1769800269_25\"}', NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', '2026-01-30 19:11:21'),
(263, 'SUCCESS', 'PREFERENCE_CRIADA', 15, NULL, NULL, NULL, NULL, 135.45, NULL, NULL, NULL, '{\"preference_id\":\"260742905-249c39b4-d314-4784-b547-379ed1c9ea7e\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-249c39b4-d314-4784-b547-379ed1c9ea7e\"}', NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', '2026-01-30 19:11:22'),
(264, 'INFO', 'INICIO_GERACAO_BOLETO', 15, NULL, NULL, NULL, NULL, 135.45, NULL, NULL, NULL, NULL, NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', '2026-01-30 19:11:37'),
(265, 'ERROR', 'ERRO_RESPOSTA_MERCADO_PAGO', 15, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Erro HTTP 400: Invalid user identification number - Invalid user identification number', '{\"http_code\":400,\"resposta\":\"{\\\"message\\\":\\\"Invalid user identification number\\\",\\\"error\\\":\\\"bad_request\\\",\\\"status\\\":400,\\\"cause\\\":[{\\\"code\\\":2067,\\\"description\\\":\\\"Invalid user identification number\\\",\\\"data\\\":\\\"30-01-2026T19:11:38UTC;66130041-eff8-4374-8f3e-b14cffad135a\\\"}]}\"}', NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', '2026-01-30 19:11:38'),
(266, 'ERROR', 'ERRO_GERACAO_BOLETO', 15, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Erro HTTP 400: Invalid user identification number - Invalid user identification number', NULL, '#0 {main}', '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', '2026-01-30 19:11:38'),
(267, 'INFO', 'INICIO_GERACAO_PIX', 15, NULL, NULL, NULL, NULL, 135.45, NULL, NULL, NULL, NULL, NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', '2026-01-30 19:12:23'),
(268, 'ERROR', 'ERRO_RESPOSTA_MERCADO_PAGO', 15, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Erro HTTP 400: Invalid user identification number - Invalid user identification number', '{\"http_code\":400,\"resposta\":\"{\\\"cause\\\":[{\\\"code\\\":2067,\\\"data\\\":\\\"30-01-2026T19:12:23UTC;0c2068ec-620d-4e2a-8d82-6fa39d5c32bd\\\",\\\"description\\\":\\\"Invalid user identification number\\\"}],\\\"error\\\":\\\"bad_request\\\",\\\"message\\\":\\\"Invalid user identification number\\\",\\\"status\\\":400}\"}', NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', '2026-01-30 19:12:24'),
(269, 'ERROR', 'ERRO_GERACAO_PIX', 15, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Erro HTTP 400: Invalid user identification number - Invalid user identification number', NULL, '#0 {main}', '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', '2026-01-30 19:12:24'),
(270, 'ERROR', 'ERRO_PROCESSAMENTO_PAGAMENTO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Dados não fornecidos', NULL, '#0 {main}', '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', '2026-01-30 19:13:11'),
(271, 'SUCCESS', 'PREFERENCE_CRIADA', 13, NULL, NULL, NULL, NULL, 20.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-3198e9da-ace4-420c-bd68-c66a86bfd01c\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-3198e9da-ace4-420c-bd68-c66a86bfd01c\"}', NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 19:35:48'),
(272, 'INFO', 'INICIO_GERACAO_PIX', 13, NULL, NULL, NULL, NULL, 20.00, NULL, NULL, NULL, NULL, NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 19:35:52'),
(273, 'SUCCESS', 'PIX_GERADO', 13, '144148112790', NULL, NULL, NULL, 20.00, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 19:35:53'),
(274, 'SUCCESS', 'PREFERENCE_CRIADA', 13, NULL, NULL, NULL, NULL, 20.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-e0d84d4b-8553-4b85-8fc9-b9809e40d4fb\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-e0d84d4b-8553-4b85-8fc9-b9809e40d4fb\"}', NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 19:36:05'),
(275, 'INFO', 'INICIO_GERACAO_BOLETO', 13, NULL, NULL, NULL, NULL, 20.00, NULL, NULL, NULL, NULL, NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 19:36:09'),
(276, 'INFO', 'INICIO_GERACAO_BOLETO', 13, NULL, NULL, NULL, NULL, 20.00, NULL, NULL, NULL, NULL, NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 19:36:09'),
(277, 'SUCCESS', 'BOLETO_GERADO', 13, '144148112822', NULL, NULL, NULL, 20.00, 'boleto', 'pendente', NULL, '{\"date_of_expiration\":\"2026-02-02T22:59:59.000-04:00\",\"barcode\":\"422******\"}', NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 19:36:10'),
(278, 'SUCCESS', 'BOLETO_GERADO', 13, '143469225233', NULL, NULL, NULL, 20.00, 'boleto', 'pendente', NULL, '{\"date_of_expiration\":\"2026-02-02T22:59:59.000-04:00\",\"barcode\":\"422******\"}', NULL, '200.206.115.131', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 19:36:11'),
(279, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 26, 8, 24, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":109,\"valor_extras\":30,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 19:38:58'),
(280, 'SUCCESS', 'CRIACAO_INSCRICAO', 16, NULL, 26, 8, 24, 139.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0,\"external_reference\":\"MOVAMAZON_1769801938_26\"}', NULL, '192.185.177.54', 'unknown', '2026-01-30 19:38:58'),
(281, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 16, NULL, 26, 8, NULL, 139.00, NULL, NULL, NULL, '{\"external_reference\":\"MOVAMAZON_1769801938_26\"}', NULL, '192.185.177.54', 'unknown', '2026-01-30 19:38:58'),
(282, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 26, 8, 24, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":109,\"valor_extras\":30,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 19:38:58'),
(283, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 16, NULL, 26, 8, 24, 139.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-30 19:38:58'),
(284, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 16, NULL, 26, 8, NULL, 139.00, NULL, NULL, NULL, '{\"external_reference\":\"MOVAMAZON_1769801938_26\"}', NULL, '192.185.177.54', 'unknown', '2026-01-30 19:38:58'),
(285, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 26, 8, 24, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":109,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '200.206.115.131', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2026-01-30 19:39:49'),
(286, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 16, NULL, 26, 8, 24, 109.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '200.206.115.131', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2026-01-30 19:39:49'),
(287, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 16, NULL, 26, 8, NULL, 109.00, NULL, NULL, NULL, '{\"external_reference\":\"MOVAMAZON_1769801938_26\"}', NULL, '200.206.115.131', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2026-01-30 19:39:49'),
(288, 'SUCCESS', 'PREFERENCE_CRIADA', 16, NULL, NULL, NULL, NULL, 114.45, NULL, NULL, NULL, '{\"preference_id\":\"260742905-1e9d0f36-f18c-4789-ae7b-425de869326e\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-1e9d0f36-f18c-4789-ae7b-425de869326e\"}', NULL, '200.206.115.131', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2026-01-30 19:39:49'),
(289, 'INFO', 'INICIO_GERACAO_PIX', 16, NULL, NULL, NULL, NULL, 114.45, NULL, NULL, NULL, NULL, NULL, '200.206.115.131', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2026-01-30 19:40:01'),
(290, 'SUCCESS', 'PIX_GERADO', 16, '143470736519', NULL, NULL, NULL, 114.45, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '200.206.115.131', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2026-01-30 19:40:02'),
(291, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 26, 8, 24, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":109,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '200.206.115.131', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2026-01-30 19:41:06'),
(292, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 16, NULL, 26, 8, 24, 109.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '200.206.115.131', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2026-01-30 19:41:06'),
(293, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 16, NULL, 26, 8, NULL, 109.00, NULL, NULL, NULL, '{\"external_reference\":\"143470736519\"}', NULL, '200.206.115.131', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2026-01-30 19:41:06'),
(294, 'SUCCESS', 'PREFERENCE_CRIADA', 16, NULL, NULL, NULL, NULL, 114.45, NULL, NULL, NULL, '{\"preference_id\":\"260742905-78e58925-f22a-4f69-9991-7aa43d42cb9d\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-78e58925-f22a-4f69-9991-7aa43d42cb9d\"}', NULL, '200.206.115.131', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2026-01-30 19:41:06'),
(295, 'INFO', 'INICIO_GERACAO_PIX', 16, NULL, NULL, NULL, NULL, 114.45, NULL, NULL, NULL, NULL, NULL, '200.206.115.131', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2026-01-30 19:41:15'),
(296, 'SUCCESS', 'PIX_GERADO', 16, '144149618330', NULL, NULL, NULL, 114.45, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '200.206.115.131', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2026-01-30 19:41:16'),
(297, 'INFO', 'INICIO_GERACAO_BOLETO', 16, NULL, NULL, NULL, NULL, 114.45, NULL, NULL, NULL, NULL, NULL, '200.206.115.131', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2026-01-30 19:41:30'),
(298, 'SUCCESS', 'BOLETO_GERADO', 16, '143469193757', NULL, NULL, NULL, 114.45, 'boleto', 'pendente', NULL, '{\"date_of_expiration\":\"2026-02-02T22:59:59.000-04:00\",\"barcode\":\"422******\"}', NULL, '200.206.115.131', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2026-01-30 19:41:31'),
(299, 'INFO', 'INICIO_GERACAO_PIX', 16, NULL, NULL, NULL, NULL, 114.45, NULL, NULL, NULL, NULL, NULL, '200.206.115.131', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2026-01-30 19:42:25'),
(300, 'SUCCESS', 'PIX_GERADO', 16, '143470079207', NULL, NULL, NULL, 114.45, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '200.206.115.131', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2026-01-30 19:42:26'),
(301, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 26, 8, 24, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":109,\"valor_extras\":30,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 19:44:06'),
(302, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 16, NULL, 26, 8, 24, 139.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-30 19:44:06'),
(303, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 16, NULL, 26, 8, NULL, 139.00, NULL, NULL, NULL, '{\"external_reference\":\"143470079207\"}', NULL, '192.185.177.54', 'unknown', '2026-01-30 19:44:06'),
(304, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 26, 8, 24, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":109,\"valor_extras\":30,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 19:44:36'),
(305, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 16, NULL, 26, 8, 24, 139.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-30 19:44:36'),
(306, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 16, NULL, 26, 8, NULL, 139.00, NULL, NULL, NULL, '{\"external_reference\":\"143470079207\"}', NULL, '192.185.177.54', 'unknown', '2026-01-30 19:44:36'),
(307, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 26, 8, 24, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":109,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '200.206.115.131', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2026-01-30 19:45:00'),
(308, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 16, NULL, 26, 8, 24, 109.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '200.206.115.131', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2026-01-30 19:45:00'),
(309, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 16, NULL, 26, 8, NULL, 109.00, NULL, NULL, NULL, '{\"external_reference\":\"143470079207\"}', NULL, '200.206.115.131', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2026-01-30 19:45:00'),
(310, 'SUCCESS', 'PREFERENCE_CRIADA', 16, NULL, NULL, NULL, NULL, 114.45, NULL, NULL, NULL, '{\"preference_id\":\"260742905-30959ebf-4bd6-43a4-a823-ca0ca268f0ad\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-30959ebf-4bd6-43a4-a823-ca0ca268f0ad\"}', NULL, '200.206.115.131', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2026-01-30 19:45:00'),
(311, 'INFO', 'INICIO_GERACAO_PIX', 16, NULL, NULL, NULL, NULL, 114.45, NULL, NULL, NULL, NULL, NULL, '200.206.115.131', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2026-01-30 19:45:04'),
(312, 'SUCCESS', 'PIX_GERADO', 16, '143470736925', NULL, NULL, NULL, 114.45, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '200.206.115.131', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_6_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.6 Mobile/15E148 Safari/604.1', '2026-01-30 19:45:04'),
(313, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 26, 8, 24, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":109,\"valor_extras\":30,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 19:47:17'),
(314, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 16, NULL, 26, 8, 24, 139.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-30 19:47:17'),
(315, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 16, NULL, 26, 8, NULL, 139.00, NULL, NULL, NULL, '{\"external_reference\":\"143470736925\"}', NULL, '192.185.177.54', 'unknown', '2026-01-30 19:47:17'),
(316, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 26, 8, 24, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":109,\"valor_extras\":30,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 19:47:17'),
(317, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 16, NULL, 26, 8, 24, 139.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-30 19:47:17'),
(318, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 16, NULL, 26, 8, NULL, 139.00, NULL, NULL, NULL, '{\"external_reference\":\"143470736925\"}', NULL, '192.185.177.54', 'unknown', '2026-01-30 19:47:17'),
(319, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 22, 8, 25, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":20,\"valor_extras\":30,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 20:17:36'),
(320, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 13, NULL, 22, 8, 25, 50.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-30 20:17:36'),
(321, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 13, NULL, 22, 8, NULL, 50.00, NULL, NULL, NULL, '{\"external_reference\":\"143469225233\"}', NULL, '192.185.177.54', 'unknown', '2026-01-30 20:17:36'),
(322, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 22, 8, 25, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":20,\"valor_extras\":30,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 20:18:06'),
(323, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 13, NULL, 22, 8, 25, 50.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-30 20:18:06'),
(324, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 13, NULL, 22, 8, NULL, 50.00, NULL, NULL, NULL, '{\"external_reference\":\"143469225233\"}', NULL, '192.185.177.54', 'unknown', '2026-01-30 20:18:06'),
(325, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 17, 8, 25, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":20,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 20:50:13'),
(326, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 9, NULL, 17, 8, 25, 20.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-30 20:50:13'),
(327, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 9, NULL, 17, 8, NULL, 20.00, NULL, NULL, NULL, '{\"external_reference\":\"144092960470\"}', NULL, '192.185.177.54', 'unknown', '2026-01-30 20:50:13'),
(328, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 17, 8, 25, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":20,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '192.185.177.54', 'unknown', '2026-01-30 20:50:43'),
(329, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 9, NULL, 17, 8, 25, 20.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '192.185.177.54', 'unknown', '2026-01-30 20:50:43'),
(330, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 9, NULL, 17, 8, NULL, 20.00, NULL, NULL, NULL, '{\"external_reference\":\"144092960470\"}', NULL, '192.185.177.54', 'unknown', '2026-01-30 20:50:43'),
(331, 'INFO', 'INICIO_SALVAMENTO_INSCRICAO', NULL, NULL, 17, 8, 25, NULL, NULL, NULL, NULL, '{\"valor_modalidades\":20,\"valor_extras\":0,\"valor_desconto\":0,\"cupom_aplicado\":null,\"seguro_contratado\":0}', NULL, '181.77.100.253', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-30 20:50:47'),
(332, 'WARNING', 'ATUALIZACAO_INSCRICAO_EXISTENTE', 9, NULL, 17, 8, 25, 20.00, NULL, NULL, NULL, '{\"valor_desconto\":0,\"cupom_aplicado\":null}', NULL, '181.77.100.253', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-30 20:50:47'),
(333, 'SUCCESS', 'SALVAMENTO_INSCRICAO_CONCLUIDO', 9, NULL, 17, 8, NULL, 20.00, NULL, NULL, NULL, '{\"external_reference\":\"144092960470\"}', NULL, '181.77.100.253', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-30 20:50:47'),
(334, 'SUCCESS', 'PREFERENCE_CRIADA', 9, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-6d8adb04-3cdb-4d32-9214-d8f39182a13d\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-6d8adb04-3cdb-4d32-9214-d8f39182a13d\"}', NULL, '181.77.100.253', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-30 20:50:48'),
(335, 'INFO', 'INICIO_GERACAO_BOLETO', 9, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, NULL, NULL, '181.77.100.253', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-30 20:50:48'),
(336, 'ERROR', 'BOLETO_REJEITADO', 9, '144158956532', NULL, NULL, NULL, NULL, NULL, NULL, 'Boleto rejeitado: rejected_by_bank', '{\"status\":\"rejected\",\"status_detail\":\"rejected_by_bank\"}', NULL, '181.77.100.253', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-30 20:50:50'),
(337, 'ERROR', 'ERRO_GERACAO_BOLETO', 9, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Boleto rejeitado pelo Mercado Pago: rejected_by_bank', NULL, '#0 {main}', '181.77.100.253', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-30 20:50:50'),
(338, 'INFO', 'INICIO_GERACAO_PIX', 9, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, NULL, NULL, '181.77.100.253', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-30 20:51:32');
INSERT INTO `logs_inscricoes_pagamentos` (`id`, `nivel`, `acao`, `inscricao_id`, `payment_id`, `usuario_id`, `evento_id`, `modalidade_id`, `valor_total`, `forma_pagamento`, `status_pagamento`, `mensagem`, `dados_contexto`, `stack_trace`, `ip`, `user_agent`, `created_at`) VALUES
(339, 'SUCCESS', 'PIX_GERADO', 9, '144159576196', NULL, NULL, NULL, 21.00, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '181.77.100.253', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-30 20:51:33'),
(340, 'SUCCESS', 'PREFERENCE_CRIADA', 9, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-0100e0ff-4dba-417d-8831-98db5200ef4b\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-0100e0ff-4dba-417d-8831-98db5200ef4b\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 22:27:17'),
(341, 'INFO', 'INICIO_GERACAO_BOLETO', 9, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, NULL, NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 22:27:22'),
(342, 'ERROR', 'ERRO_RESPOSTA_MERCADO_PAGO', 9, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Erro HTTP 400: not_result_by_params - No result found for the given parameters', '{\"http_code\":400,\"resposta\":\"{\\\"message\\\":\\\"not_result_by_params\\\",\\\"error\\\":\\\"bad_request\\\",\\\"status\\\":400,\\\"cause\\\":[{\\\"code\\\":10102,\\\"description\\\":\\\"No result found for the given parameters\\\",\\\"data\\\":\\\"30-01-2026T22:27:22UTC;dae94ddf-2016-43d2-9778-654c1ad924f4\\\"}]}\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 22:27:22'),
(343, 'ERROR', 'ERRO_GERACAO_BOLETO', 9, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Erro HTTP 400: not_result_by_params - No result found for the given parameters', NULL, '#0 {main}', '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 22:27:22'),
(344, 'INFO', 'INICIO_GERACAO_PIX', 9, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, NULL, NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 22:28:10'),
(345, 'SUCCESS', 'PIX_GERADO', 9, '144173129018', NULL, NULL, NULL, 21.00, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 22:28:11'),
(346, 'SUCCESS', 'PREFERENCE_CRIADA', 9, NULL, NULL, NULL, NULL, 114.45, NULL, NULL, NULL, '{\"preference_id\":\"260742905-e950afd6-36e7-4d2b-a2d2-6df2eae34a1d\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-e950afd6-36e7-4d2b-a2d2-6df2eae34a1d\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 23:29:52'),
(347, 'INFO', 'INICIO_GERACAO_BOLETO', 9, NULL, NULL, NULL, NULL, 114.45, NULL, NULL, NULL, NULL, NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 23:29:57'),
(348, 'ERROR', 'ERRO_RESPOSTA_MERCADO_PAGO', 9, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Erro HTTP 400: not_result_by_params - No result found for the given parameters', '{\"http_code\":400,\"resposta\":\"{\\\"message\\\":\\\"not_result_by_params\\\",\\\"error\\\":\\\"bad_request\\\",\\\"status\\\":400,\\\"cause\\\":[{\\\"code\\\":10102,\\\"description\\\":\\\"No result found for the given parameters\\\",\\\"data\\\":\\\"30-01-2026T23:29:57UTC;668f092d-45d6-41fb-9449-15e084968dcd\\\"}]}\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 23:29:57'),
(349, 'ERROR', 'ERRO_GERACAO_BOLETO', 9, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Erro HTTP 400: not_result_by_params - No result found for the given parameters', NULL, '#0 {main}', '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 23:29:57'),
(350, 'SUCCESS', 'PREFERENCE_CRIADA', 11, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-2d66e472-fe70-48a3-8a66-4e3765f1b56d\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-2d66e472-fe70-48a3-8a66-4e3765f1b56d\"}', NULL, '200.225.112.185', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-30 23:45:03'),
(351, 'SUCCESS', 'PAGAMENTO_PROCESSADO', NULL, '143506511303', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"status\":\"approved\",\"status_detail\":\"accredited\",\"transaction_amount\":21,\"payment_method_id\":\"master\"}', NULL, '200.225.112.185', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-30 23:48:44'),
(352, 'SUCCESS', 'PREFERENCE_CRIADA', 7, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-74f8ed9b-667e-46a0-a916-d2adc6e83639\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-74f8ed9b-667e-46a0-a916-d2adc6e83639\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-31 01:49:04'),
(353, 'INFO', 'INICIO_GERACAO_PIX', 7, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, NULL, NULL, '191.189.17.91', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-31 01:49:09'),
(354, 'SUCCESS', 'PIX_GERADO', 7, '144201992786', NULL, NULL, NULL, 21.00, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-31 01:49:10'),
(355, 'SUCCESS', 'CUPOM_APLICADO', NULL, NULL, 17, 8, NULL, NULL, NULL, NULL, NULL, '{\"cupom_id\":4,\"codigo_cupom\":\"98***JS\",\"valor_total_inscricao\":0,\"valor_desconto_aplicado\":0,\"tipo_valor\":\"percentual\",\"usos_atuais\":0,\"max_uso\":20}', NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-02 17:30:29'),
(356, 'SUCCESS', 'CUPOM_APLICADO', NULL, NULL, 17, 8, NULL, NULL, NULL, NULL, NULL, '{\"cupom_id\":4,\"codigo_cupom\":\"98***JS\",\"valor_total_inscricao\":0,\"valor_desconto_aplicado\":0,\"tipo_valor\":\"percentual\",\"usos_atuais\":0,\"max_uso\":20}', NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 18:05:11'),
(357, 'SUCCESS', 'PREFERENCE_CRIADA', 9, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-fd09456f-567b-4290-9743-7a37e5336661\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-fd09456f-567b-4290-9743-7a37e5336661\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 18:05:21'),
(358, 'INFO', 'INICIO_GERACAO_BOLETO', 9, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, NULL, NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 18:05:26'),
(359, 'ERROR', 'BOLETO_REJEITADO', 9, '143978139555', NULL, NULL, NULL, NULL, NULL, NULL, 'Boleto rejeitado: rejected_by_bank', '{\"status\":\"rejected\",\"status_detail\":\"rejected_by_bank\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 18:05:28'),
(360, 'ERROR', 'ERRO_GERACAO_BOLETO', 9, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Boleto rejeitado pelo Mercado Pago: rejected_by_bank', NULL, '#0 {main}', '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 18:05:28'),
(361, 'SUCCESS', 'PREFERENCE_CRIADA', 9, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-1815839b-fbe5-42ea-8bfc-a29a9d31016b\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-1815839b-fbe5-42ea-8bfc-a29a9d31016b\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 18:07:10'),
(362, 'INFO', 'INICIO_GERACAO_BOLETO', 9, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, NULL, NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 18:07:15'),
(363, 'ERROR', 'BOLETO_REJEITADO', 9, '143978961005', NULL, NULL, NULL, NULL, NULL, NULL, 'Boleto rejeitado: rejected_by_bank', '{\"status\":\"rejected\",\"status_detail\":\"rejected_by_bank\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 18:07:17'),
(364, 'ERROR', 'ERRO_GERACAO_BOLETO', 9, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Boleto rejeitado pelo Mercado Pago: rejected_by_bank', NULL, '#0 {main}', '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 18:07:17'),
(365, 'INFO', 'INICIO_GERACAO_PIX', 9, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, NULL, NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 18:07:26'),
(366, 'SUCCESS', 'PIX_GERADO', 9, '143978921063', NULL, NULL, NULL, 21.00, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 18:07:27'),
(367, 'SUCCESS', 'CUPOM_APLICADO', NULL, NULL, 17, 8, NULL, NULL, NULL, NULL, NULL, '{\"cupom_id\":4,\"codigo_cupom\":\"98***JS\",\"valor_total_inscricao\":0,\"valor_desconto_aplicado\":0,\"tipo_valor\":\"percentual\",\"usos_atuais\":0,\"max_uso\":20}', NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 18:07:51'),
(368, 'SUCCESS', 'PREFERENCE_CRIADA', 9, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-7a1ef12d-e139-45e8-ab1c-875c3f12d0d0\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-7a1ef12d-e139-45e8-ab1c-875c3f12d0d0\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 18:08:00'),
(369, 'INFO', 'INICIO_GERACAO_PIX', 9, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, NULL, NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 18:08:05'),
(370, 'SUCCESS', 'PIX_GERADO', 9, '143979394703', NULL, NULL, NULL, 21.00, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 18:08:06'),
(371, 'SUCCESS', 'PREFERENCE_CRIADA', 13, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-c18bc6b7-52c3-44d3-8cee-9ff742eb9189\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-c18bc6b7-52c3-44d3-8cee-9ff742eb9189\"}', NULL, '170.83.118.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 19:09:35'),
(372, 'INFO', 'INICIO_GERACAO_PIX', 13, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, NULL, NULL, '170.83.118.245', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 19:09:45'),
(373, 'ERROR', 'ERRO_GERACAO_PIX', 13, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Esta inscrição foi cancelada automaticamente por expiração. Por favor, faça uma nova inscrição.', NULL, '#0 {main}', '170.83.118.245', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 19:09:45'),
(374, 'SUCCESS', 'PREFERENCE_CRIADA', 13, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-f6e0373c-a849-4674-a3de-83beb115eb79\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-f6e0373c-a849-4674-a3de-83beb115eb79\"}', NULL, '170.83.118.223', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 19:10:08'),
(375, 'INFO', 'INICIO_GERACAO_PIX', 13, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, NULL, NULL, '170.83.118.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 19:10:13'),
(376, 'ERROR', 'ERRO_GERACAO_PIX', 13, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Esta inscrição foi cancelada automaticamente por expiração. Por favor, faça uma nova inscrição.', NULL, '#0 {main}', '170.83.118.228', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 19:10:13'),
(377, 'SUCCESS', 'PREFERENCE_CRIADA', 13, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-6fb5b199-471e-4100-8fc8-e283ad33b3f6\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-6fb5b199-471e-4100-8fc8-e283ad33b3f6\"}', NULL, '170.83.118.198', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 21:33:51'),
(378, 'INFO', 'INICIO_GERACAO_PIX', 13, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, NULL, NULL, '170.83.118.130', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 21:33:58'),
(379, 'SUCCESS', 'PIX_GERADO', 13, '144829933062', NULL, NULL, NULL, 21.00, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '170.83.118.130', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 21:33:59'),
(380, 'SUCCESS', 'PREFERENCE_CRIADA', 17, NULL, NULL, NULL, NULL, 20.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-1e8f5316-e87f-4d59-914a-45e224743f2b\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-1e8f5316-e87f-4d59-914a-45e224743f2b\"}', NULL, '177.201.151.166', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-06 02:55:13'),
(381, 'INFO', 'INICIO_GERACAO_PIX', 17, NULL, NULL, NULL, NULL, 20.00, NULL, NULL, NULL, NULL, NULL, '177.201.151.166', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-06 02:55:24'),
(382, 'ERROR', 'ERRO_RESPOSTA_MERCADO_PAGO', 17, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Erro HTTP 400: The name of the following parameters is wrong : [items] - The name of the parameters is wrong.', '{\"http_code\":400,\"resposta\":\"{\\\"cause\\\":[{\\\"code\\\":8,\\\"data\\\":\\\"06-02-2026T02:55:24UTC;befdbcd4-3c06-4d2b-a703-dcbbdcee8953 -\\\\u003e Indication: Check the fields in the request body\\\",\\\"description\\\":\\\"The name of the parameters is wrong.\\\"}],\\\"error\\\":\\\"bad_request\\\",\\\"message\\\":\\\"The name of the following parameters is wrong : [items]\\\",\\\"status\\\":400}\"}', NULL, '177.201.151.166', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-06 02:55:24'),
(383, 'ERROR', 'ERRO_GERACAO_PIX', 17, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Erro HTTP 400: The name of the following parameters is wrong : [items] - The name of the parameters is wrong.', NULL, '#0 {main}', '177.201.151.166', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-06 02:55:24'),
(384, 'INFO', 'INICIO_GERACAO_PIX', 17, NULL, NULL, NULL, NULL, 20.00, NULL, NULL, NULL, NULL, NULL, '177.201.151.166', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-06 02:55:42'),
(385, 'ERROR', 'ERRO_RESPOSTA_MERCADO_PAGO', 17, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Erro HTTP 400: The name of the following parameters is wrong : [items] - The name of the parameters is wrong.', '{\"http_code\":400,\"resposta\":\"{\\\"cause\\\":[{\\\"code\\\":8,\\\"data\\\":\\\"06-02-2026T02:55:42UTC;6d96cd84-1977-4b94-a434-9c8d2cb8edbb -\\\\u003e Indication: Check the fields in the request body\\\",\\\"description\\\":\\\"The name of the parameters is wrong.\\\"}],\\\"error\\\":\\\"bad_request\\\",\\\"message\\\":\\\"The name of the following parameters is wrong : [items]\\\",\\\"status\\\":400}\"}', NULL, '177.201.151.166', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-06 02:55:42'),
(386, 'ERROR', 'ERRO_GERACAO_PIX', 17, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Erro HTTP 400: The name of the following parameters is wrong : [items] - The name of the parameters is wrong.', NULL, '#0 {main}', '177.201.151.166', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-06 02:55:42'),
(387, 'SUCCESS', 'PREFERENCE_CRIADA', 17, NULL, NULL, NULL, NULL, 20.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-35b641d2-f32e-4cb6-b321-bc5c1b13aa13\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-35b641d2-f32e-4cb6-b321-bc5c1b13aa13\"}', NULL, '177.201.151.166', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-06 02:56:31'),
(388, 'INFO', 'INICIO_GERACAO_BOLETO', 17, NULL, NULL, NULL, NULL, 20.00, NULL, NULL, NULL, NULL, NULL, '177.201.151.166', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-06 02:56:44'),
(389, 'ERROR', 'ERRO_RESPOSTA_MERCADO_PAGO', 17, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Erro HTTP 400: The name of the following parameters is wrong : [items] - The name of the parameters is wrong.', '{\"http_code\":400,\"resposta\":\"{\\\"message\\\":\\\"The name of the following parameters is wrong : [items]\\\",\\\"error\\\":\\\"bad_request\\\",\\\"status\\\":400,\\\"cause\\\":[{\\\"code\\\":8,\\\"description\\\":\\\"The name of the parameters is wrong.\\\",\\\"data\\\":\\\"06-02-2026T02:56:44UTC;370c84c7-40bf-4cd9-910e-3a1f1889c914 -> Indication: Check the fields in the request body\\\"}]}\",\"payment_method_id\":\"bolbradesco\"}', NULL, '177.201.151.166', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-06 02:56:44'),
(390, 'ERROR', 'ERRO_GERACAO_BOLETO', 17, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Erro HTTP 400: The name of the following parameters is wrong : [items] - The name of the parameters is wrong.', NULL, '#0 {main}', '177.201.151.166', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-06 02:56:44'),
(391, 'ERROR', 'ERRO_PROCESSAMENTO_PAGAMENTO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Campo obrigatório não fornecido: token', NULL, '#0 {main}', '177.201.151.166', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-06 02:57:22'),
(392, 'SUCCESS', 'PREFERENCE_CRIADA', 17, NULL, NULL, NULL, NULL, 20.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-9795936d-ad27-4bd1-b4bd-95faa250b801\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-9795936d-ad27-4bd1-b4bd-95faa250b801\"}', NULL, '201.139.92.102', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-06 11:47:16'),
(393, 'SUCCESS', 'PREFERENCE_CRIADA', 18, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-72864633-fe9b-46c1-8c76-55f167c7c17c\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-72864633-fe9b-46c1-8c76-55f167c7c17c\"}', NULL, '170.83.118.232', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-06 13:18:37'),
(394, 'INFO', 'INICIO_GERACAO_PIX', 18, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, NULL, NULL, '170.83.118.221', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-06 13:19:10'),
(395, 'SUCCESS', 'PIX_GERADO', 18, '145067143726', NULL, NULL, NULL, 21.00, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '170.83.118.221', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-06 13:19:12'),
(396, 'INFO', 'SYNC_PAYMENT_MANUAL_ORGANIZADOR', 18, '145067143726', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"status_anterior_inscricao\":\"pago\",\"status_novo_inscricao\":\"pago\",\"organizador_id\":4}', NULL, '170.83.118.169', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-06 16:46:55'),
(397, 'INFO', 'SYNC_PAYMENT_MANUAL_ORGANIZADOR', 13, '144829933062', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"status_anterior_inscricao\":\"pago\",\"status_novo_inscricao\":\"pago\",\"organizador_id\":4}', NULL, '170.83.118.169', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-06 16:47:02'),
(398, 'INFO', 'SYNC_PAYMENT_MANUAL_ORGANIZADOR', 9, '143979394703', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"status_anterior_inscricao\":\"pago\",\"status_novo_inscricao\":\"pago\",\"organizador_id\":4}', NULL, '170.83.118.231', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-06 16:47:10'),
(399, 'INFO', 'SYNC_PAYMENT_MANUAL_ORGANIZADOR', 7, '144201992786', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"status_anterior_inscricao\":\"processando\",\"status_novo_inscricao\":\"pago\",\"organizador_id\":4}', NULL, '170.83.118.251', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-06 17:28:12'),
(400, 'ERROR', 'SYNC_PAYMENT_MANUAL_ORGANIZADOR_ERROR', 17, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Erro ao consultar pagamento: Si quieres conocer los recursos de la API que se encuentran disponibles visita el Sitio de Desarrolladores de MercadoLibre (https://developers.mercadopago.com)', NULL, NULL, '170.83.118.225', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-06 17:29:27'),
(401, 'ERROR', 'SYNC_PAYMENT_MANUAL_ORGANIZADOR_ERROR', 17, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Erro ao consultar pagamento: Si quieres conocer los recursos de la API que se encuentran disponibles visita el Sitio de Desarrolladores de MercadoLibre (https://developers.mercadopago.com)', NULL, NULL, '170.83.118.227', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-06 17:30:02'),
(402, 'SUCCESS', 'PREFERENCE_CRIADA', 17, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-d9fd0fb9-eaaa-4f62-b40b-d92433e87146\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-d9fd0fb9-eaaa-4f62-b40b-d92433e87146\"}', NULL, '177.201.151.166', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 03:38:44'),
(403, 'INFO', 'INICIO_GERACAO_PIX', 17, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, NULL, NULL, '177.201.151.166', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 03:39:32'),
(404, 'SUCCESS', 'PIX_GERADO', 17, '145198696440', NULL, NULL, NULL, 21.00, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '177.201.151.166', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 03:39:33'),
(405, 'SUCCESS', 'PREFERENCE_CRIADA', 17, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-1840253a-3ef2-4a20-9543-358d8312c445\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-1840253a-3ef2-4a20-9543-358d8312c445\"}', NULL, '177.201.151.166', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 03:40:53'),
(406, 'INFO', 'INICIO_GERACAO_PIX', 17, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, NULL, NULL, '177.201.151.166', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 03:41:09'),
(407, 'SUCCESS', 'PIX_GERADO', 17, '144511541833', NULL, NULL, NULL, 21.00, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '177.201.151.166', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 03:41:10'),
(408, 'SUCCESS', 'PREFERENCE_CRIADA', 19, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-83f27380-70cf-4eec-b80e-9659673edffe\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-83f27380-70cf-4eec-b80e-9659673edffe\"}', NULL, '170.83.118.173', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 01:19:13'),
(409, 'ERROR', 'ERRO_PROCESSAMENTO_PAGAMENTO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Mercado Pago Payment: The name of the following parameters is wrong : [items]', NULL, '#0 /home2/brunor90/movamazon.com.br/api/inscricao/process_payment_preference.php(130): MercadoPagoClient->createPayment(Array)\n#1 {main}', '170.83.118.219', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 01:21:04'),
(410, 'ERROR', 'ERRO_PROCESSAMENTO_PAGAMENTO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Mercado Pago Payment: The name of the following parameters is wrong : [items]', NULL, '#0 /home2/brunor90/movamazon.com.br/api/inscricao/process_payment_preference.php(130): MercadoPagoClient->createPayment(Array)\n#1 {main}', '170.83.118.152', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 01:21:14'),
(411, 'SUCCESS', 'PREFERENCE_CRIADA', 19, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-0a58cd36-269f-424f-a680-f94eb97c4872\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-0a58cd36-269f-424f-a680-f94eb97c4872\"}', NULL, '170.83.118.176', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 01:31:23'),
(412, 'SUCCESS', 'PREFERENCE_CRIADA', 19, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-901faadf-d709-42bd-9277-03d3893d7e1b\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-901faadf-d709-42bd-9277-03d3893d7e1b\"}', NULL, '170.83.118.253', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 01:33:56'),
(413, 'ERROR', 'ERRO_PROCESSAMENTO_PAGAMENTO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Mercado Pago Payment: The name of the following parameters is wrong : [items]', NULL, '#0 /home2/brunor90/movamazon.com.br/api/inscricao/process_payment_preference.php(130): MercadoPagoClient->createPayment(Array)\n#1 {main}', '170.83.118.208', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 01:35:19'),
(414, 'SUCCESS', 'PREFERENCE_CRIADA', 19, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-9a8d16ee-c287-4f1b-af03-c005e5da1a9a\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-9a8d16ee-c287-4f1b-af03-c005e5da1a9a\"}', NULL, '170.83.118.148', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 01:40:47'),
(415, 'ERROR', 'ERRO_PROCESSAMENTO_PAGAMENTO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Mercado Pago Payment: The name of the following parameters is wrong : [items]', NULL, '#0 /home2/brunor90/movamazon.com.br/api/inscricao/process_payment_preference.php(130): MercadoPagoClient->createPayment(Array)\n#1 {main}', '170.83.118.249', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 01:42:07'),
(416, 'SUCCESS', 'PREFERENCE_CRIADA', 19, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-fc302c3f-58e0-4cdd-a46b-48b5333e279b\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-fc302c3f-58e0-4cdd-a46b-48b5333e279b\"}', NULL, '170.83.118.163', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 01:45:06'),
(417, 'ERROR', 'ERRO_PROCESSAMENTO_PAGAMENTO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Mercado Pago Payment: The name of the following parameters is wrong : [items]', NULL, '#0 /home2/brunor90/movamazon.com.br/api/inscricao/process_payment_preference.php(130): MercadoPagoClient->createPayment(Array)\n#1 {main}', '170.83.118.161', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 01:46:41'),
(418, 'SUCCESS', 'PREFERENCE_CRIADA', 19, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-7d9e6ba1-d6cb-4268-a7ab-d7e2b3c87cd4\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-7d9e6ba1-d6cb-4268-a7ab-d7e2b3c87cd4\"}', NULL, '170.83.118.237', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 02:19:47'),
(419, 'SUCCESS', 'PREFERENCE_CRIADA', 19, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-59b1906e-8aa1-4d0c-9ac5-c91de54f260e\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-59b1906e-8aa1-4d0c-9ac5-c91de54f260e\"}', NULL, '170.83.118.145', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 02:28:00'),
(420, 'ERROR', 'SYNC_PAYMENT_MANUAL_ERROR', 19, 'MOVAMAZON_1770513540_27', NULL, NULL, NULL, NULL, NULL, NULL, 'Erro ao consultar pagamento: Si quieres conocer los recursos de la API que se encuentran disponibles visita el Sitio de Desarrolladores de MercadoLibre (https://developers.mercadopago.com)', NULL, NULL, '170.83.118.238', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 02:38:49'),
(421, 'INFO', 'SYNC_PAYMENT_MANUAL', 19, 'MOVAMAZON_1770513540_27', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"status_anterior_inscricao\":\"pendente\",\"status_novo_inscricao\":\"pago\",\"status_anterior_ml\":\"pendente\",\"status_novo_ml\":\"pago\",\"admin_id\":2}', NULL, '170.83.118.147', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 02:53:32'),
(422, 'SUCCESS', 'PREFERENCE_CRIADA', 20, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-786cf96e-7a73-4418-a191-9c2cd337b05c\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-786cf96e-7a73-4418-a191-9c2cd337b05c\"}', NULL, '170.83.118.152', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 13:35:18'),
(423, 'SUCCESS', 'PREFERENCE_CRIADA', 9, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-645cb4a8-350e-4603-978d-0783033fc74d\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-645cb4a8-350e-4603-978d-0783033fc74d\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-02-12 01:09:45'),
(424, 'SUCCESS', 'PREFERENCE_CRIADA', 9, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-0f9db39d-646c-44bc-bc6e-777baa441db3\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-0f9db39d-646c-44bc-bc6e-777baa441db3\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-02-12 01:10:21'),
(425, 'SUCCESS', 'CUPOM_APLICADO', NULL, NULL, 17, 8, NULL, NULL, NULL, NULL, NULL, '{\"cupom_id\":4,\"codigo_cupom\":\"98***JS\",\"valor_total_inscricao\":0,\"valor_desconto_aplicado\":0,\"tipo_valor\":\"percentual\",\"usos_atuais\":0,\"max_uso\":20}', NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-12 01:24:05'),
(426, 'SUCCESS', 'PREFERENCE_CRIADA', 9, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-522fe8c3-633d-4374-af6d-dd7416d07b72\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-522fe8c3-633d-4374-af6d-dd7416d07b72\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-12 01:24:23'),
(427, 'SUCCESS', 'PREFERENCE_CRIADA', 9, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-90a4e7fc-858e-4104-8bee-4c77b1e7ca53\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-90a4e7fc-858e-4104-8bee-4c77b1e7ca53\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-12 01:28:24'),
(428, 'INFO', 'SYNC_PAYMENT_MANUAL', 17, '145198696440', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"status_anterior_inscricao\":\"pendente\",\"status_novo_inscricao\":\"pago\",\"status_anterior_ml\":\"pendente\",\"status_novo_ml\":\"pago\",\"admin_id\":2}', NULL, '170.83.118.229', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-12 14:29:09'),
(429, 'ERROR', 'SYNC_PAYMENT_MANUAL_ERROR', 20, 'MOVAMAZON_1770557712_29', NULL, NULL, NULL, NULL, NULL, NULL, 'Nenhum pagamento encontrado para a referência: MOVAMAZON_1770557712_29. Verifique se o webhook está configurado (ML_NOTIFICATION_URL no .env e URL no painel do Mercado Pago).', NULL, NULL, '170.83.118.229', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-12 14:29:16'),
(430, 'SUCCESS', 'PREFERENCE_CRIADA', 20, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-c50526eb-2585-4851-b7b9-90d91e41969b\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-c50526eb-2585-4851-b7b9-90d91e41969b\"}', NULL, '187.26.79.44', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-16 12:23:15'),
(431, 'INFO', 'INICIO_GERACAO_PIX', 20, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, NULL, NULL, '187.26.79.44', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-16 12:23:32'),
(432, 'SUCCESS', 'PIX_GERADO', 20, '145792108627', NULL, NULL, NULL, 21.00, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '187.26.79.44', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-16 12:23:33'),
(433, 'SUCCESS', 'PREFERENCE_CRIADA', 9, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-f7b563c9-14e1-4ac7-8410-558498ad2708\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-f7b563c9-14e1-4ac7-8410-558498ad2708\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 01:17:18'),
(434, 'ERROR', 'ERRO_PROCESSAMENTO_PAGAMENTO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Campo obrigatório não fornecido: token', NULL, '#0 {main}', '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 01:17:39'),
(435, 'INFO', 'INICIO_GERACAO_PIX', 11, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, NULL, NULL, '177.39.78.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-02-18 01:18:16'),
(436, 'SUCCESS', 'PIX_GERADO', 11, '145999651247', NULL, NULL, NULL, 21.00, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '177.39.78.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-02-18 01:18:17'),
(437, 'INFO', 'WEBHOOK_PROCESSAMENTO', NULL, '145999651247', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"status\":\"pending\",\"external_reference\":\"142946577853\"}', NULL, '35.245.91.34', 'MercadoPago WebHook v1.0 payment', '2026-02-18 01:19:18'),
(438, 'SUCCESS', 'STATUS_ATUALIZADO', 11, '145999651247', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"status_anterior\":\"pago\",\"status_novo\":\"pendente\",\"valor_pago\":21}', NULL, '35.245.91.34', 'MercadoPago WebHook v1.0 payment', '2026-02-18 01:19:18'),
(439, 'INFO', 'INICIO_GERACAO_BOLETO', 9, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, NULL, NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 01:21:03'),
(440, 'SUCCESS', 'BOLETO_GERADO', 9, '145998751907', NULL, NULL, NULL, 21.00, 'boleto', 'pendente', NULL, '{\"date_of_expiration\":\"2026-02-20T22:59:59.000-04:00\",\"barcode\":\"376******\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 01:21:07'),
(441, 'INFO', 'INICIO_GERACAO_PIX', 11, NULL, NULL, NULL, NULL, 20.00, NULL, NULL, NULL, NULL, NULL, '177.39.78.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-02-18 01:21:31'),
(442, 'SUCCESS', 'PIX_GERADO', 11, '146697234630', NULL, NULL, NULL, 20.00, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '177.39.78.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-02-18 01:21:32'),
(443, 'INFO', 'WEBHOOK_PROCESSAMENTO', NULL, '146697234630', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"status\":\"pending\",\"external_reference\":\"142946577853\"}', NULL, '35.245.91.34', 'MercadoPago WebHook v1.0 payment', '2026-02-18 01:22:33'),
(444, 'SUCCESS', 'STATUS_ATUALIZADO', 11, '146697234630', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"status_anterior\":\"processando\",\"status_novo\":\"pendente\",\"valor_pago\":20}', NULL, '35.245.91.34', 'MercadoPago WebHook v1.0 payment', '2026-02-18 01:22:33'),
(445, 'SUCCESS', 'PREFERENCE_CRIADA', 17, NULL, NULL, NULL, NULL, 52.50, NULL, NULL, NULL, '{\"preference_id\":\"260742905-15baa88a-4a73-46cd-925f-8a13abbf8541\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-15baa88a-4a73-46cd-925f-8a13abbf8541\"}', NULL, '177.201.151.166', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 01:35:58'),
(446, 'ERROR', 'ERRO_PROCESSAMENTO_PAGAMENTO', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Campo obrigatório não fornecido: token', NULL, '#0 {main}', '177.201.151.166', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 01:36:41'),
(447, 'INFO', 'INICIO_GERACAO_PIX', 17, NULL, NULL, NULL, NULL, 52.50, NULL, NULL, NULL, NULL, NULL, '177.201.151.166', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 01:38:30'),
(448, 'SUCCESS', 'PIX_GERADO', 17, '146699740112', NULL, NULL, NULL, 52.50, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '177.201.151.166', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 01:38:31'),
(449, 'INFO', 'WEBHOOK_PROCESSAMENTO', NULL, '146699740112', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"status\":\"pending\",\"external_reference\":\"144511541833\"}', NULL, '35.186.182.146', 'MercadoPago WebHook v1.0 payment', '2026-02-18 01:39:33'),
(450, 'SUCCESS', 'STATUS_ATUALIZADO', 17, '146699740112', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"status_anterior\":\"pago\",\"status_novo\":\"pendente\",\"valor_pago\":52.5}', NULL, '35.186.182.146', 'MercadoPago WebHook v1.0 payment', '2026-02-18 01:39:33'),
(451, 'SUCCESS', 'PREFERENCE_CRIADA', 17, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, '{\"preference_id\":\"260742905-34db0a04-cd69-446d-89ad-f537df4e3850\",\"init_point\":\"https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=260742905-34db0a04-cd69-446d-89ad-f537df4e3850\"}', NULL, '177.201.151.166', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 01:46:52'),
(452, 'INFO', 'INICIO_GERACAO_PIX', 11, NULL, NULL, NULL, NULL, 20.00, NULL, NULL, NULL, NULL, NULL, '177.39.78.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-02-20 23:54:48'),
(453, 'SUCCESS', 'PIX_GERADO', 11, '147124934258', NULL, NULL, NULL, 20.00, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '177.39.78.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-02-20 23:54:49'),
(454, 'INFO', 'INICIO_GERACAO_PIX', 11, NULL, NULL, NULL, NULL, 20.00, NULL, NULL, NULL, NULL, NULL, '177.39.78.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-02-20 23:55:09'),
(455, 'SUCCESS', 'PIX_GERADO', 11, '147122671442', NULL, NULL, NULL, 20.00, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '177.39.78.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-02-20 23:55:10'),
(456, 'INFO', 'INICIO_GERACAO_PIX', 11, NULL, NULL, NULL, NULL, 20.00, NULL, NULL, NULL, NULL, NULL, '177.39.78.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', '2026-02-20 23:58:53'),
(457, 'SUCCESS', 'PIX_GERADO', 11, '147124896532', NULL, NULL, NULL, 20.00, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '177.39.78.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', '2026-02-20 23:58:54'),
(458, 'INFO', 'INICIO_GERACAO_PIX', 11, NULL, NULL, NULL, NULL, 20.00, NULL, NULL, NULL, NULL, NULL, '177.39.78.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', '2026-02-21 13:44:25'),
(459, 'SUCCESS', 'PIX_GERADO', 11, '146476110159', NULL, NULL, NULL, 20.00, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '177.39.78.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', '2026-02-21 13:44:26'),
(460, 'INFO', 'INICIO_GERACAO_PIX', 18, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, NULL, NULL, '170.83.116.122', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-21 18:30:29'),
(461, 'SUCCESS', 'PIX_GERADO', 18, '146514478969', NULL, NULL, NULL, 21.00, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '170.83.116.122', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-21 18:30:30'),
(462, 'INFO', 'INICIO_GERACAO_PIX', 18, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, NULL, NULL, '170.83.116.40', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-21 18:31:27'),
(463, 'SUCCESS', 'PIX_GERADO', 18, '147215024588', NULL, NULL, NULL, 21.00, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '170.83.116.40', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-21 18:31:27'),
(464, 'INFO', 'INICIO_GERACAO_PIX', 18, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, NULL, NULL, '170.83.116.60', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-21 18:45:55'),
(465, 'SUCCESS', 'PIX_GERADO', 18, '147217238134', NULL, NULL, NULL, 21.00, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '170.83.116.60', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-21 18:45:56'),
(466, 'INFO', 'INICIO_GERACAO_PIX', 18, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, NULL, NULL, '170.83.116.78', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-21 18:46:08'),
(467, 'SUCCESS', 'PIX_GERADO', 18, '146515063955', NULL, NULL, NULL, 21.00, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '170.83.116.78', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-21 18:46:08'),
(468, 'INFO', 'INICIO_GERACAO_PIX', 18, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, NULL, NULL, '170.83.116.122', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-21 18:51:30'),
(469, 'SUCCESS', 'PIX_GERADO', 18, '146516920839', NULL, NULL, NULL, 21.00, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '170.83.116.122', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-21 18:51:31'),
(470, 'INFO', 'INICIO_GERACAO_PIX', 18, NULL, NULL, NULL, NULL, 21.00, NULL, NULL, NULL, NULL, NULL, '170.83.116.122', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-21 18:51:37'),
(471, 'SUCCESS', 'PIX_GERADO', 18, '146515476031', NULL, NULL, NULL, 21.00, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '170.83.116.122', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-21 18:51:37'),
(472, 'INFO', 'INICIO_GERACAO_PIX', 11, NULL, NULL, NULL, NULL, 20.00, NULL, NULL, NULL, NULL, NULL, '177.39.78.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', '2026-02-22 16:19:19'),
(473, 'SUCCESS', 'PIX_GERADO', 11, '147317365480', NULL, NULL, NULL, 20.00, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '177.39.78.52', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', '2026-02-22 16:19:20'),
(474, 'INFO', 'INICIO_GERACAO_PIX', 11, NULL, NULL, NULL, NULL, 20.00, NULL, NULL, NULL, NULL, NULL, '200.225.115.23', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', '2026-02-23 22:09:10');
INSERT INTO `logs_inscricoes_pagamentos` (`id`, `nivel`, `acao`, `inscricao_id`, `payment_id`, `usuario_id`, `evento_id`, `modalidade_id`, `valor_total`, `forma_pagamento`, `status_pagamento`, `mensagem`, `dados_contexto`, `stack_trace`, `ip`, `user_agent`, `created_at`) VALUES
(475, 'ERROR', 'ERRO_GERACAO_PIX', 11, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Esta inscrição foi cancelada automaticamente por expiração. Por favor, faça uma nova inscrição.', NULL, '#0 {main}', '200.225.115.23', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', '2026-02-23 22:09:10'),
(476, 'INFO', 'INICIO_GERACAO_PIX', 11, NULL, NULL, NULL, NULL, 20.00, NULL, NULL, NULL, NULL, NULL, '200.225.115.23', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', '2026-02-23 22:09:25'),
(477, 'ERROR', 'ERRO_GERACAO_PIX', 11, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Esta inscrição foi cancelada automaticamente por expiração. Por favor, faça uma nova inscrição.', NULL, '#0 {main}', '200.225.115.23', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 OPR/127.0.0.0', '2026-02-23 22:09:25');

-- --------------------------------------------------------

--
-- Estrutura para tabela `logs_sincronizacao_mp`
--

CREATE TABLE `logs_sincronizacao_mp` (
  `id` bigint(20) NOT NULL,
  `tipo` enum('webhook','manual','automatica') COLLATE utf8mb4_unicode_ci NOT NULL,
  `inicio` datetime NOT NULL,
  `fim` datetime DEFAULT NULL,
  `duracao_ms` int(11) DEFAULT NULL COMMENT 'Duração em milissegundos',
  `transacoes_processadas` int(11) DEFAULT '0',
  `transacoes_novas` int(11) DEFAULT '0',
  `transacoes_atualizadas` int(11) DEFAULT '0',
  `erros` int(11) DEFAULT '0',
  `status` enum('em_progresso','concluido','erro') COLLATE utf8mb4_unicode_ci DEFAULT 'em_progresso',
  `mensagem_erro` text COLLATE utf8mb4_unicode_ci,
  `executado_por` int(11) DEFAULT NULL COMMENT 'ID do usuário que executou (se manual)',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log de sincronizações com Mercado Pago';

-- --------------------------------------------------------

--
-- Estrutura para tabela `lotes_inscricao`
--

CREATE TABLE `lotes_inscricao` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `modalidade_id` int(11) NOT NULL,
  `numero_lote` int(11) NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `preco_por_extenso` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL,
  `vagas_disponiveis` int(11) DEFAULT NULL,
  `taxa_servico` decimal(10,2) DEFAULT '0.00',
  `quem_paga_taxa` enum('organizador','participante') COLLATE utf8mb4_unicode_ci DEFAULT 'participante',
  `idade_min` int(11) DEFAULT '0',
  `idade_max` int(11) DEFAULT '100',
  `desconto_idoso` tinyint(1) DEFAULT '0',
  `ativo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `lotes_inscricao`
--

INSERT INTO `lotes_inscricao` (`id`, `evento_id`, `modalidade_id`, `numero_lote`, `preco`, `preco_por_extenso`, `data_inicio`, `data_fim`, `vagas_disponiveis`, `taxa_servico`, `quem_paga_taxa`, `idade_min`, `idade_max`, `desconto_idoso`, `ativo`, `created_at`, `updated_at`) VALUES
(17, 8, 23, 1, 129.00, 'Cento e vinte e nove reais', '2025-12-20', '2026-10-19', 2000, 8.50, 'participante', 18, 100, 0, 0, '2025-12-20 17:20:32', '2025-12-20 19:48:34'),
(18, 8, 23, 2, 109.00, 'Cento e nove reais', '2025-12-20', '2026-10-19', 2000, 7.50, 'participante', 16, 100, 1, 0, '2025-12-20 20:12:14', '2026-01-22 14:40:31'),
(19, 8, 24, 3, 109.00, 'Cento e nove reais', '2025-12-20', '2026-10-19', 2000, 7.50, 'participante', 16, 100, 1, 0, '2025-12-21 01:50:10', '2026-01-22 14:40:27'),
(20, 8, 21, 4, 129.00, 'Cento e vinte e nove reais', '2025-12-20', '2026-10-19', 2000, 7.50, 'participante', 16, 100, 1, 0, '2025-12-21 01:51:06', '2026-01-22 14:40:22'),
(21, 8, 22, 5, 129.00, 'Cento e vinte e nove reais', '2025-12-20', '2026-10-19', 2000, 7.50, 'participante', 16, 100, 1, 0, '2025-12-21 01:51:54', '2026-01-22 14:40:17'),
(22, 8, 23, 6, 109.00, 'Cento e nove reais', '2026-01-22', '2026-10-19', 1000, 7.00, 'participante', 16, 100, 0, 1, '2026-01-22 14:44:29', '2026-01-22 14:44:29'),
(23, 8, 24, 6, 109.00, 'Cento e nove reais', '2026-01-22', '2026-10-19', 1000, 7.00, 'participante', 16, 100, 0, 1, '2026-01-22 14:44:29', '2026-01-22 14:44:29'),
(24, 8, 21, 7, 129.00, 'Cento e vinte e nove reais', '2026-01-22', '2026-10-19', 1000, 7.00, 'participante', 16, 100, 1, 1, '2026-01-22 15:01:27', '2026-01-22 15:01:27'),
(25, 8, 22, 7, 129.00, 'Cento e vinte e nove reais', '2026-01-22', '2026-10-19', 1000, 7.00, 'participante', 16, 100, 1, 1, '2026-01-22 15:01:27', '2026-01-22 15:01:27'),
(26, 8, 25, 8, 20.00, 'Vinte reais', '2026-01-29', '2026-01-31', 20, 7.00, 'participante', 16, 100, 1, 1, '2026-01-29 15:10:07', '2026-01-29 15:27:11'),
(27, 10, 26, 1, 10.00, 'Dez reais', '2025-02-17', '2025-09-10', 200, 7.00, 'participante', 18, 90, 1, 1, '2026-02-17 04:01:19', '2026-02-17 04:01:19'),
(28, 10, 28, 1, 10.00, 'Dez reais', '2025-02-17', '2025-09-10', 200, 7.00, 'participante', 18, 90, 1, 1, '2026-02-17 04:01:19', '2026-02-17 04:01:19');

-- --------------------------------------------------------

--
-- Estrutura para tabela `modalidades`
--

CREATE TABLE `modalidades` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `distancia` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_prova` enum('corrida','caminhada','ambos') COLLATE utf8mb4_unicode_ci DEFAULT 'corrida',
  `limite_vagas` int(11) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `modalidades`
--

INSERT INTO `modalidades` (`id`, `evento_id`, `categoria_id`, `nome`, `descricao`, `distancia`, `tipo_prova`, `limite_vagas`, `ativo`, `data_criacao`, `updated_at`) VALUES
(21, 8, 16, 'Corrida 10 Km', 'Percurso de 10 Km para o Púbico em geral', '10 km', 'corrida', 500, 1, '2025-12-20 11:56:07', '2025-12-20 11:56:07'),
(22, 8, 17, 'Corrida 5 Km', 'Distância de 5 Km para o público geral', '5 km', 'corrida', 500, 1, '2025-12-20 11:57:14', '2025-12-20 11:57:14'),
(23, 8, 19, 'Corrida 10 Km', 'Distância de 10 Km para a comunidade acadêmica', '10 km', 'corrida', 300, 1, '2025-12-20 11:57:53', '2025-12-20 11:57:53'),
(24, 8, 19, 'Corrida 5 Km', 'Distância de 5 Km comunidade acadêmica', '5 km', 'corrida', 700, 1, '2025-12-20 11:58:23', '2025-12-20 11:58:23'),
(25, 8, 18, 'Corrida de 10 km para teste', 'Teste de Produção da Plataforma', '10k', 'corrida', 20, 1, '2026-01-29 14:57:47', '2026-01-29 14:57:47'),
(26, 10, 20, '40 KM', 'Corrida solo de 40 km', '40km', 'corrida', 200, 1, '2026-02-17 03:57:59', '2026-02-17 03:57:59'),
(27, 10, 20, '40 KM', 'Corrida solo de 40 km', '40km', 'corrida', 200, 0, '2026-02-17 03:57:59', '2026-02-17 03:58:07'),
(28, 10, 21, 'Corrida solo de 80 km', 'Corrida solo de 80 km', '80 km', 'corrida', 200, 1, '2026-02-17 03:58:48', '2026-02-17 03:58:48');

-- --------------------------------------------------------

--
-- Estrutura para tabela `openai_token_usage`
--

CREATE TABLE `openai_token_usage` (
  `id` int(11) NOT NULL,
  `data_hora` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_id` int(11) DEFAULT NULL,
  `endpoint` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modelo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prompt_tokens` int(11) DEFAULT '0',
  `completion_tokens` int(11) DEFAULT '0',
  `total_tokens` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `organizadores`
--

CREATE TABLE `organizadores` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `empresa` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `regiao` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modalidade_esportiva` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantidade_eventos` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `regulamento` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `organizadores`
--

INSERT INTO `organizadores` (`id`, `usuario_id`, `empresa`, `regiao`, `modalidade_esportiva`, `quantidade_eventos`, `regulamento`) VALUES
(3, 15, 'EBL Eventos Esportivos', 'AM', 'corrida-rua', '1', 'sim'),
(4, 16, 'EBL EVENTOS ESPORTIVOS', 'AM', 'corrida-rua', '1', 'sim'),
(5, 30, 'Projeto Primatas.', 'AM', 'corrida-rua', '1', 'sim');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pagamentos`
--

CREATE TABLE `pagamentos` (
  `id` int(11) NOT NULL,
  `inscricao_id` int(11) NOT NULL,
  `forma_pagamento` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_pagamento` datetime DEFAULT NULL,
  `valor_total` decimal(10,2) DEFAULT NULL,
  `valor_desconto` decimal(10,2) DEFAULT NULL,
  `valor_pago` decimal(10,2) DEFAULT NULL,
  `taxa_participante` decimal(10,2) DEFAULT NULL,
  `valor_repasse` decimal(10,2) DEFAULT NULL,
  `status` enum('pendente','pago','cancelado') COLLATE utf8mb4_unicode_ci DEFAULT 'pendente',
  `payment_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID da transacao no Mercado Pago'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `pagamentos`
--

INSERT INTO `pagamentos` (`id`, `inscricao_id`, `forma_pagamento`, `data_pagamento`, `valor_total`, `valor_desconto`, `valor_pago`, `taxa_participante`, `valor_repasse`, `status`, `payment_id`) VALUES
(1, 11, 'pix', '2026-02-17 22:19:18', 20.00, NULL, 21.00, NULL, NULL, 'pendente', '145999651247'),
(2, 11, 'pix', '2026-02-17 22:22:33', 20.00, NULL, 20.00, NULL, NULL, 'pendente', '146697234630'),
(3, 17, 'pix', '2026-02-17 22:39:33', 50.00, NULL, 52.50, NULL, NULL, 'pendente', '146699740112');

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
-- Despejando dados para a tabela `pagamentos_ml`
--

INSERT INTO `pagamentos_ml` (`id`, `inscricao_id`, `preference_id`, `payment_id`, `init_point`, `status`, `data_criacao`, `data_atualizacao`, `dados_pagamento`, `valor_pago`, `metodo_pagamento`, `parcelas`, `taxa_ml`, `user_id`, `created`) VALUES
(6, 18, 'sync_145067143726', '145067143726', 'https://www.mercadopago.com.br/payments/145067143726/ticket?caller_id=3185435143&hash=1d7838f3-2a48-4b69-9763-94dc296538b5', 'pago', '2026-02-06 14:04:16', '2026-02-06 16:46:55', '{\"accounts_info\":null,\"acquirer_reconciliation\":[],\"additional_info\":{\"bank_info\":{\"is_same_bank_account_owner\":false},\"tracking_id\":\"platform:v1-whitelabel,so:ALL,type:N\\/A,security:none\"},\"authorization_code\":null,\"binary_mode\":false,\"brand_id\":null,\"build_version\":\"3.141.0-rc-1\",\"call_for_authorize_id\":null,\"callback_url\":null,\"captured\":true,\"card\":[],\"charges_details\":[{\"accounts\":{\"from\":\"collector\",\"to\":\"mp\"},\"amounts\":{\"original\":0.2099999999999999922284388276239042170345783233642578125,\"refunded\":0},\"client_id\":0,\"date_created\":\"2026-02-06T09:19:11.000-04:00\",\"external_charge_id\":\"01KGSHPHAPDZRPD7WACPVRSNHK\",\"id\":\"145067143726-001\",\"last_updated\":\"2026-02-06T09:19:11.000-04:00\",\"metadata\":{\"reason\":\"\",\"source\":\"proc-svc-charges\",\"source_detail\":\"processing_fee_charge\"},\"name\":\"mercadopago_fee\",\"refund_charges\":[],\"reserve_id\":null,\"type\":\"fee\",\"update_charges\":[]}],\"charges_execution_info\":{\"internal_execution\":{\"date\":\"2026-02-06T09:19:11.200-04:00\",\"execution_id\":\"01KGSHPH9QV4773AQ40NXX24VJ\"}},\"collector_id\":260742905,\"corporation_id\":null,\"counter_currency\":null,\"coupon_amount\":0,\"currency_id\":\"BRL\",\"date_approved\":\"2026-02-06T09:19:51.000-04:00\",\"date_created\":\"2026-02-06T09:19:11.000-04:00\",\"date_last_updated\":\"2026-02-06T09:19:56.000-04:00\",\"date_of_expiration\":\"2026-02-07T09:19:10.000-04:00\",\"deduction_schema\":null,\"description\":\"Inscrição no evento: III CORRIDA SAUIM DE COLEIRA\",\"differential_pricing_id\":null,\"external_reference\":\"MOVAMAZON_1770383714_27\",\"fee_details\":[{\"amount\":0.2099999999999999922284388276239042170345783233642578125,\"fee_payer\":\"collector\",\"type\":\"mercadopago_fee\"}],\"financing_group\":null,\"id\":145067143726,\"installments\":1,\"integrator_id\":null,\"issuer_id\":\"12501\",\"live_mode\":true,\"marketplace_owner\":null,\"merchant_account_id\":null,\"merchant_number\":null,\"metadata\":[],\"money_release_date\":\"2026-02-06T09:19:51.000-04:00\",\"money_release_schema\":null,\"money_release_status\":\"released\",\"notification_url\":\"https:\\/\\/www.movamazon.com.br\\/api\\/mercadolivre\\/webhook.php\",\"operation_type\":\"regular_payment\",\"order\":[],\"payer\":{\"email\":\"XXXXXXXXXXX\",\"entity_type\":null,\"first_name\":null,\"id\":\"3185435143\",\"identification\":{\"number\":\"99999999999\",\"type\":\"CPF\"},\"last_name\":null,\"operator_id\":null,\"phone\":{\"number\":null,\"extension\":null,\"area_code\":null},\"type\":null},\"payment_method\":{\"id\":\"pix\",\"issuer_id\":\"12501\",\"type\":\"bank_transfer\"},\"payment_method_id\":\"pix\",\"payment_type_id\":\"bank_transfer\",\"platform_id\":null,\"point_of_interaction\":{\"application_data\":{\"name\":null,\"operating_system\":null,\"version\":null},\"business_info\":{\"branch\":\"Merchant Services\",\"sub_unit\":\"default\",\"unit\":\"online_payments\"},\"location\":{\"source\":null,\"state_id\":null},\"sub_type\":\"INTER_PSP\",\"transaction_data\":{\"bank_info\":{\"collector\":{\"account_alias\":null,\"account_holder_name\":\"Eudimaci Barboza de Lira\",\"account_id\":30044733964,\"long_name\":\"MERCADO PAGO INSTITUIÇÃO DE PAGAMENTO LTDA.\",\"transfer_account_id\":null},\"is_same_bank_account_owner\":false,\"origin_bank_id\":null,\"origin_wallet_id\":null,\"payer\":{\"account_id\":336179237,\"branch\":\"1\",\"external_account_id\":null,\"id\":null,\"identification\":[],\"long_name\":\"NU PAGAMENTOS S.A. - INSTITUIÇÃO DE PAGAMENTO\"}},\"bank_transfer_id\":121873850501,\"e2e_id\":null,\"financial_institution\":1,\"is_end_consumer\":null,\"merchant_category_code\":null,\"qr_code\":\"00020126580014br.gov.bcb.pix0136fb6df74b-f82a-425b-92ab-55459eab8d69520400005303986540521.005802BR5915EUDIMACIBARBOZA6009Sao Paulo62250521mpqrinter1450671437266304013C\",\"qr_code_base64\":\"iVBORw0KGgoAAAANSUhEUgAABWQAAAVkAQMAAABpQ4TyAAAABlBMVEX\\/\\/\\/8AAABVwtN+AAAKyElEQVR42uzdQW4iWQwG4IdYsOQIHIWjhaNxFI7AMgtEjZqheLarCEx3piHS92+izATqq95Zz89uIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIvL\\/ZjVMsr\\/+r+2vX46tfdx+nvrnNsPQ2rJ\\/6HD9sv3t5+L6ZYvrX5wvn\\/u4\\/ra7fnj8shpaWlpaWlpaWlpaWlpa2m\\/QHsrvlweu+4N2v36O2vWv\\/zKql5cPD8Nn\\/\\/z214NWXdfuaC9fsunahNrS0tLS0tLS0tLS0tLSvrO2V5qraZnartpa447q\\/vtnLKTP01e+\\/GxJ3bVj9f1JS0tLS0tLS0tLS0tL+7O04eQ0PeA0\\/fwmHnaupseu26t6N3nlWvPS0tLS0tLS0tLS0tLS\\/lDt+MWttMy2dHKaPnSvdXbaxDvQ0tLS0tLS0tLS0tLS0v5FbekWXpTielTmB\\/Xz3aF\\/yTZeXF3HQ+Pw4dBq\\/A29zbS0tLS0tLS0tLS0tLR\\/UzudXLSYPaId75zubndNT71cHbuFt6VwfvZL\\/mDOEi0tLS0tLS0tLS0tLe1f036ZxWy38Mek1l2lmrccv+aad3f78uXvqmhpaWlpaWlpaWlpaWlfqR3PLbd3+m9TH+5Ytm5iC23tvx1\\/P\\/b+21ZeeXxyPX5Nw3RpaWlpaWlpaWlpaWlp30yb+2+vh5\\/na7\\/tuf+f0Ie7e1CujieoaYjuR3yfY9SekuS\\/3OKkpaWlpaWlpaWlpaWlpf1KG+rkfcubQ4dy5\\/SiPMZ1Lcs+bKjFD4fFMftS3qfFMV+ElpaWlpaWlpaWlpaW9h21q3I0+9nCptDFdGRtyjJ9eDws7vq0ObTNjkFK\\/17PzL+lpaWlpaWlpaWlpaWlfaF25rAzDR26lKvndGLa75oup8Nz6yvXbuG0OTRV3Q96mmlpaWlpaWlpaWlpaWnfQBt2ce5vNz4XXT2zg\\/Pjdtd02Vtm08XV1l+13U5Oz+kENY0\\/Gl\\/xoZqWlpaWlpaWlpaWlpaW9j9p0+nqqjf41mm97dYtXOcs5SI7XVzd3w6Lz6VCD2X+UJQPN4fS0tLS0tLS0tLS0tLSvkY7ry45pwbfet57p1wNd07D5tBeA5+6\\/tDPe2lpaWlpaWlpaWlpaWnfWVvvnKYRtYukTbVv6Bbu5WtdHDPEAjoXzr1POSyOeWZaLy0tLS0tLS0tLS0tLe0LteNnUv\\/tOHRoZg5ui+VpmFwUtL1wrq8ctqnUV5++Mi0tLS0tLS0tLS0tLS3tn2lX6ebntVI\\/J209oq1Hs+m8d5pFKftbV8+W+au5sp6WlpaWlpaWlpaWlpb2LbStn7IOcehQWtcyc\\/c0DdwNh8T7WPvObA69dAmv70zrfWZqLy0tLS0tLS0tLS0tLe3Ltf1vVr3WrZXnR+wmPsy9anjlOkQ31bxpgcyp\\/Ls9OOelpaWlpaWlpaWlpaWlfbl2VKaln+02unaYblNJ\\/betvOqqjDsKyuHuJtHPfgz7zMkpLS0tLS0tLS0tLS0t7Qu0dRdn\\/ds09za3ztbaN63VnB6\\/hlr32CbbVPoKlqH8E9DS0tLS0tLS0tLS0tLS\\/rE2dQu3ueFDp6S998B0WDzcNoYuyiuf7ki+6hampaWlpaWlpaWlpaWlfa22XSvNUHH2a6NfbwxN2mHS6PvvIfF27pA4DNO9VzjT0tLS0tLS0tLS0tLSvqM2XBfd3\\/Z2nu+Xq+vpndM0ujaok7ZuURni8evM77S0tLS0tLS0tLS0tLTvqA3ZDjVZW6+LDnfS5+HWrSnp91Np2h3SvxstLS0tLS0tLS0tLS0t7Z9qV3N1cTiiPc41+o5\\/vOyV+aG\\/8vWBYVrvMWqX\\/cs2w2RxTDkspqWlpaWlpaWlpaWlpX0rbd20MvOgPr03f2h8ULpzOn1QLpjTBtHLh099XvD9L6GlpaWlpaWlpaWlpaV9C+3s3yzS3dPW5ufgtrlro5dC+TMWzGOhfC7zb1vX1r7lgZaWlpaWlpaWlpaWlvY9tWP5+ln6b4\\/T3+vI2lSuzqYWzGFy0f3m3ccnp7S0tLS0tLS0tLS0tLS0w3PdwmNm5yyt44ikUbss03rr0s9VubA6c77bT5iXaQ3pww01tLS0tLS0tLS0tLS0tC\\/UBvW+6Hute05rWj7yBdV\\/H7CJrxxajbdzE4yG6c6Xflj84JyXlpaWlpaWlpaWlpaW9uXa6eSiNP+2lZo3XFRNJ6e1XF33C6y7udo3LIzphfPjmpeWlpaWlpaWlpaWlpb2VdrwoDS5KJSr9bro9EE1n0U7c3KaCuf6ZXtaWlpaWlpaWlpaWlra99SGmnf8m225gPlx678dyhzc0H+7KUOHpitYFn2LyszkotkvoaWlpaWlpaWlpaWlpaX9Nm06300VetDtJndOhzJoNyyOmU4yGj90SifMveX4wYYaWlpaWlpaWlpaWlpa2pdrp1nP3TkND9rEn6HBdxu7h48PXjmo0wVWWlpaWlpaWlpaWlpa2jfX1slF9dAzPCDVvIfyoPDAq\\/acXj1dYN3E8UdDLJgbLS0tLS0tLS0tLS0t7ZtqN+W6aBpZW7eorOOh56m\\/6lAmGI3K+uAv75zS0tLS0tLS0tLS0tLS0n6jdlX+Ju18GZXndP67uy37zNdF0wbRfWtdGxbG9HPeNu0Wbs9NhaKlpaWlpaWlpaWlpaV9oXa6cSXsfFmX66LHWK4u+6se4rqW1RAWxiwe3zk9TKtvWlpaWlpaWlpaWlpa2nfUtjQv6FpxZu2uhTuo97qFN7cPt648zg3THcq4o2VaHPPcOS8tLS0tLS0tLS0tLS3ta7Sr8tkWr4sOZenn0A89w+SiUZuuj46vvC8XV4db\\/+344WXqw6WlpaWlpaWlpaWlpaWl\\/S5t7tntf5sq8lpczwzY3cQ7p0PZ+XIs571fdgs\\/uaGGlpaWlpaWlpaWlpaW9gXaVal103nveF10plxt8c7p7LXRRZrO21qbndqb1o8eaGlpaWlpaWlpaWlpad9em+6chobfbSxP0ybRqhyvjdb5t63Pwa0tx+muabh7mtaQ0tLS0tLS0tLS0tLS0r6fdrb\\/NqjTiek63jlts\\/23qYn3fnLfbdcOv9EtTEtLS0tLS0tLS0tLS\\/t3tDP6\\/eSwM+\\/i3M2NsJ2+8vnLk9JdmVhUC2daWlpaWlpaWlpaWlpa2u\\/WXrIok4vO6f9+xDunm77zJbUcj6+6n9w1rdpTOnGmpaWlpaWlpaWlpaWlfWftaphkPzmqXaQ5uO1OuZourM5OLDpOX7UP0aWlpaWlpaWlpaWlpaX9EdrDVyenrT9wV\\/5rOuwMNW\\/6UN0k+hHVm2GoNe8zk4toaWlpaWlpaWlpaWlpX6vtrbOrOLp2LFfP06FDQ1+jOU0YPtT7cM+Pxx+FW6+0tLS0tLS0tLS0tLS0tP+LtpUHnNNd010ZkTRdHDNMX3kXW43X082h9eSZlpaWlpaWlpaWlpaW9idpa7dwui46Kpf9Q6nRt7YgpwJ6PNddlt\\/H7uFnNtTQ0tLS0tLS0tLS0tLSvlJbuoXD5KKwOfSiPd55UGg17ndPw9zb3iUcfh6i8skNNbS0tLS0tLS0tLS0tLSv1X45uejeoWe4LlrL1NS0uy+Ti9r1ldOd003ZIPrbc5ZoaWlpaWlpaWlpaWlpaUVERERERERERERERERERERERERERERERERERER+SP4JAAD\\/\\/9oZrIpGL20eAAAAAElFTkSuQmCC\",\"ticket_url\":\"https:\\/\\/www.mercadopago.com.br\\/payments\\/145067143726\\/ticket?caller_id=3185435143&hash=1d7838f3-2a48-4b69-9763-94dc296538b5\",\"transaction_id\":\"PIXE18236120202602061319s01df6eb9c9\"},\"type\":\"OPENPLATFORM\"},\"pos_id\":null,\"processing_mode\":\"aggregator\",\"refunds\":[],\"release_info\":null,\"shipping_amount\":0,\"sponsor_id\":null,\"statement_descriptor\":null,\"status\":\"approved\",\"status_detail\":\"accredited\",\"store_id\":null,\"tags\":null,\"taxes_amount\":0,\"transaction_amount\":21,\"transaction_amount_refunded\":0,\"transaction_details\":{\"acquirer_reference\":null,\"bank_transfer_id\":121873850501,\"external_resource_url\":null,\"financial_institution\":\"1\",\"installment_amount\":0,\"net_received_amount\":20.78999999999999914734871708787977695465087890625,\"overpaid_amount\":0,\"payable_deferral_period\":null,\"payment_method_reference_id\":null,\"total_paid_amount\":21,\"transaction_id\":\"PIXE18236120202602061319s01df6eb9c9\"}}', 21.00, 'pix', 1, 0.21, 27, '2026-02-06 11:04:16'),
(7, 13, '', '144829933062', '', 'pago', '2026-02-06 16:47:02', '2026-02-06 16:47:02', '{\"accounts_info\":null,\"acquirer_reconciliation\":[],\"additional_info\":{\"bank_info\":{\"is_same_bank_account_owner\":false},\"tracking_id\":\"platform:v1-whitelabel,so:ALL,type:N\\/A,security:none\"},\"authorization_code\":null,\"binary_mode\":false,\"brand_id\":null,\"build_version\":\"3.141.0-rc-1\",\"call_for_authorize_id\":null,\"callback_url\":null,\"captured\":true,\"card\":[],\"charges_details\":[{\"accounts\":{\"from\":\"collector\",\"to\":\"mp\"},\"amounts\":{\"original\":0.2099999999999999922284388276239042170345783233642578125,\"refunded\":0},\"client_id\":0,\"date_created\":\"2026-02-04T17:33:59.000-04:00\",\"external_charge_id\":\"01KGN973J6WAEEK1ZCKYYPVPCH\",\"id\":\"144829933062-001\",\"last_updated\":\"2026-02-04T17:33:59.000-04:00\",\"metadata\":{\"reason\":\"\",\"source\":\"proc-svc-charges\",\"source_detail\":\"processing_fee_charge\"},\"name\":\"mercadopago_fee\",\"refund_charges\":[],\"reserve_id\":null,\"type\":\"fee\",\"update_charges\":[]}],\"charges_execution_info\":{\"internal_execution\":{\"date\":\"2026-02-04T17:33:59.247-04:00\",\"execution_id\":\"01KGN973H43JKT65WDC24X2TS4\"}},\"collector_id\":260742905,\"corporation_id\":null,\"counter_currency\":null,\"coupon_amount\":0,\"currency_id\":\"BRL\",\"date_approved\":\"2026-02-04T17:34:31.000-04:00\",\"date_created\":\"2026-02-04T17:33:59.000-04:00\",\"date_last_updated\":\"2026-02-04T17:34:36.000-04:00\",\"date_of_expiration\":\"2026-02-05T17:33:59.000-04:00\",\"deduction_schema\":null,\"description\":\"Inscrição no evento: III CORRIDA SAUIM DE COLEIRA\",\"differential_pricing_id\":null,\"external_reference\":\"143469225233\",\"fee_details\":[{\"amount\":0.2099999999999999922284388276239042170345783233642578125,\"fee_payer\":\"collector\",\"type\":\"mercadopago_fee\"}],\"financing_group\":null,\"id\":144829933062,\"installments\":1,\"integrator_id\":null,\"issuer_id\":\"12501\",\"live_mode\":true,\"marketplace_owner\":null,\"merchant_account_id\":null,\"merchant_number\":null,\"metadata\":[],\"money_release_date\":\"2026-02-04T17:34:31.000-04:00\",\"money_release_schema\":null,\"money_release_status\":\"released\",\"notification_url\":\"https:\\/\\/www.movamazon.com.br\\/api\\/mercadolivre\\/webhook.php\",\"operation_type\":\"regular_payment\",\"order\":[],\"payer\":{\"email\":\"XXXXXXXXXXX\",\"entity_type\":null,\"first_name\":null,\"id\":\"3152606945\",\"identification\":{\"number\":\"99999999999\",\"type\":\"CPF\"},\"last_name\":null,\"operator_id\":null,\"phone\":{\"number\":null,\"extension\":null,\"area_code\":null},\"type\":null},\"payment_method\":{\"id\":\"pix\",\"issuer_id\":\"12501\",\"type\":\"bank_transfer\"},\"payment_method_id\":\"pix\",\"payment_type_id\":\"bank_transfer\",\"platform_id\":null,\"point_of_interaction\":{\"application_data\":{\"name\":null,\"operating_system\":null,\"version\":null},\"business_info\":{\"branch\":\"Merchant Services\",\"sub_unit\":\"default\",\"unit\":\"online_payments\"},\"location\":{\"source\":null,\"state_id\":null},\"sub_type\":\"INTER_PSP\",\"transaction_data\":{\"bank_info\":{\"collector\":{\"account_alias\":null,\"account_holder_name\":\"Eudimaci Barboza de Lira\",\"account_id\":30044733964,\"long_name\":\"MERCADO PAGO INSTITUIÇÃO DE PAGAMENTO LTDA.\",\"transfer_account_id\":null},\"is_same_bank_account_owner\":false,\"origin_bank_id\":null,\"origin_wallet_id\":null,\"payer\":{\"account_id\":336179237,\"branch\":\"1\",\"external_account_id\":null,\"id\":null,\"identification\":[],\"long_name\":\"NU PAGAMENTOS S.A. - INSTITUIÇÃO DE PAGAMENTO\"}},\"bank_transfer_id\":121885891554,\"e2e_id\":null,\"financial_institution\":1,\"is_end_consumer\":null,\"merchant_category_code\":null,\"qr_code\":\"00020126580014br.gov.bcb.pix0136fb6df74b-f82a-425b-92ab-55459eab8d69520400005303986540521.005802BR5915EUDIMACIBARBOZA6009Sao Paulo62250521mpqrinter144829933062630442AA\",\"qr_code_base64\":\"iVBORw0KGgoAAAANSUhEUgAABWQAAAVkAQMAAABpQ4TyAAAABlBMVEX\\/\\/\\/8AAABVwtN+AAAKqUlEQVR42uzdQXIiO7MGUBEMGLIElsLS7KWxFJbA0AMCvWg\\/CimzVJj+7WvoiPNNiO4LVUc9y6tUqoiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIjIf5tNneWQvvL25y9Pfz7P7S93tZaybj86Xh92uH2urg9bXb9xaQ+rtb5ffzw9LIeWlpaWlpaWlpaWlpaW9ge0x\\/Tnw+xLq6bd9rr152etH+2r+z8v2jRdWdDWPw\\/bNW14356WlpaWlpaWlpaWlpb2lbWt0szl6qlpP9Np67UGboXqR19IXz6\\/PD3s\\/fZZgrppN+0htLS0tLS0tLS0tLS0tP+StlPlF5zS73ejndP\\/L1s\\/v7y\\/qt9vS14v1Ly0tLS0tLS0tLS0tLS0\\/6h2Ffpw33r19vqikn601Dq7vzXvdp+0tLS0tLS0tLS0tLS0tL+mTd3Cq1Rcx4p9elHb763tIfv+4Oq2P3Pa\\/bi0VuMf6G2mpaWlpaWlpaWlpaWl\\/U3tfHLRarhFO505fb+dNT23cnU6e7rvC+e\\/eMg35izR0tLS0tLS0tLS0tLS\\/pr2bvIx0XPbSQ217ibUvF9vv+Yzpz8TWlpaWlpaWlpaWlpa2t\\/QTvuWuVzNCbep7PoW2tx\\/u0lXsmznyvdShtuvYZguLS0tLS0tLS0tLS0t7YtpN2F07XXz83Ltt72073V9uOEazS7pIbUNzx2kac+hE\\/jLU5y0tLS0tLS0tLS0tLS0tA9qu7lBh1LStN7LXHnqt2jXaam1f8jULRwnF5VUqS+FlpaWlpaWlpaWlpaW9hW1mzR06KN0N4Wu5iNrQ9ZpX\\/ejf0hJN4eW4Rik0u\\/3\\/s38W1paWlpaWlpaWlpaWtqnaZeHDn2Wp5egC\\/Nuu8I5vLCkmjcvMSx91y+x3O8WpqWlpaWlpaWlpaWlpX2itrRNz\\/CiOi9X621y0XTWdN2WGg6ulrbUtlN6aTumpzT+aHrzg2paWlpaWlpaWlpaWlpa2ke08cxp6xYuJU7rDd3C57bE42jp3cHVw22zeKnMXydBefiELC0tLS0tLS0tLS0tLe1va8tcuZ8dAO3K1domF9WbOparqXAu4ebQVgOfm\\/7Y9ntpaWlpaWlpaWlpaWlpX1m7acdGD30NfCqzm0OXGn7nte\\/g5tDw41NfIHcXxzw4rZeWlpaWlpaWlpaWlpb2Wdqu\\/zY8eLswB7e08jRfgDIvnMMtKrlwLqHmDUumpaWlpaWlpaWlpaWlpf0B7aZ993A7AXoJ2jBoN7wgVuhhazi3HNfRnS\\/HkXYzKutpaWlpaWlpaWlpaWlpX0Lb3Rxa+6FD3XUtdXbnyzpc15IPrg7VbWLROYxBagX0g3OWaGlpaWlpaWlpaWlpaZ+unU8u2vYvipue+dLPectxV+seRjXvdn5wtT3si31eWlpaWlpaWlpaWlpa2qdrpxeFSz9LP7p2O79NJdS+pe+77ebfZmVdvEn0Y6ETmJaWlpaWlpaWlpaWlvZ1tIP+23yas6aDl+EazWMqnPPw3MPCVSz5NpW2c1rn\\/wS0tLS0tLS0tLS0tLS0tN\\/Thm7h4fChc9AOXzg9dHhhTHft6Fu\\/vxt+dK9bmJaWlpaWlpaWlpaWlva52nLdXR3ssoat2nBj6EBbU6NvWPKwW7i0Nx57yb2al5aWlpaWlpaWlpaWlva52nhcNDX6Xq5fipufecd0\\/rDcPfz549VwyWG9f3nPKS0tLS0tLS0tLS0tLe1vawcPzpueSxeh1IVcl75KZ0tjrfu5c7pb+HGlpaWlpaWlpaWlpaWlpf2+dtAtPM1Xyt3C4czp55fX87On+15bm\\/rt9rB1e9iudkObNrS0tLS0tLS0tLS0tLQvr803reQXTVu0XfnaytVzWvJwizYWzPnsab0+LM9QoqWlpaWlpaWlpaWlpX1F7fBFq\\/lvtv32Zhw6lGveNgc3LPGS5t+Wpv3rCp2WlpaWlpaWlpaWlpb2OdqlC1BObfjQ0ujautBCm5p1V+nm0HNael0ef0RLS0tLS0tLS0tLS0tL+w1tdz1LmLMUzqBG9dQlPDX+zluON+nA6mB\\/t+0wx5bjL2+ooaWlpaWlpaWlpaWlpX2itlMfytKln3GL9u3W4Lu05O6s6Xy\\/t4YDrE05bRY\\/MmeJlpaWlpaWlpaWlpaW9pna4eSiMjtrOr1o3b68a5\\/zcnU7P7gaat9p2\\/XY3136dc1LS0tLS0tLS0tLS0tL+yztVK5+pMlFXbm6VPPmMrXlI2lPoybeGpYcat8DLS0tLS0tLS0tLS0t7Wtqu5p3+k7Y7Nz2O6hT62zsv019tzU9JA\\/R7SYXdUN0P5e4e+zmUFpaWlpaWlpaWlpaWlrax7VTt\\/Bw6NDb7fhoaZX6XW13ccx8ktE6jPwNZf2u\\/38FtLS0tLS0tLS0tLS0tK+uDd3C833e0mrfqVzNXcL7pp4\\/pDRtW3KuvmlpaWlpaWlpaWlpaWlfWru7laklvChsenZnTUPNe0wv6l7YrmLZ9g+NSw7zb2svoaWlpaWlpaWlpaWlpX097abNDdrf9i3jTmnov932m57n9pDa17rhIasw7zZc6Dlp8\\/YrLS0tLS0tLS0tLS0tLe0PausXd74MuoXzcdFWoS8dYA37vGXeLVwemApFS0tLS0tLS0tLS0tL+0Rt3Odtte+k3abjoqd+0O66lanH\\/rqWTf\\/jacmDh4QbQ7upvfWxbmFaWlpaWlpaWlpaWlra52hD2tCh7qzptv33Ybfw7vbjEnZM80HWfHPo\\/9LbTEtLS0tLS0tLS0tLS\\/ss7SZ9t3twGzpUQ637XkqYXNQK59Jvfq7C5\\/vtn2Dcfzs\\/\\/UpLS0tLS0tLS0tLS0tL+23t8MRnqMjz9S3n0PgbGn3nZX4c2lT7JQ+7he9N66WlpaWlpaWlpaWlpaV9rrYrV9vkom7LdnvVntLQoWly0bS\\/G6b2TtpTetNgam+YXHSkpaWlpaWlpaWlpaWlfW1tPC46HzpUe21u9C3zWrfWOP92v9ByPFxiOLhKS0tLS0tLS0tLS0tL+5LamuYF7WfHR2PZGubfBvXg5tDlxL7b2k8yOtDS0tLS0tLS0tLS0tK+pnagP8w2O+NdnO\\/p+syFXO7ulL6niUWtBt7050lpaWlpaWlpaWlpaWlpaX9M+5k8uWh6cewWbtN6N23Ldp8Orh5mZ02z9hx2nGlpaWlpaWlpaWlpaWlfWbuZF6uhW7grV8Pln4NyNRwX3Y8mFp36peYhurS0tLS0tLS0tLS0tLT\\/hPZ4b+e0pLOm3YvaZ6x5w\\/zbr24OzTXvI5OLaGlpaWlpaWlpaWlpaZ+rbWdON\\/3o2nyNZk0HVNcLf7\\/pC+dLa+Ldzmvf+Y8LLS0tLS0tLS0tLS0tLe1\\/py3hBdOg3dDoW+9V6B+tIm9LLungagnatu9bv9znpaWlpaWlpaWlpaWlpX017aDht2kn5br9KKS7frTOuoa7A6vhz5P+kRtqaGlpaWlpaWlpaWlpaZ+pTd3C3eSimmre08KLdunA6jzdkqeu4WP68SM31NDS0tLS0tLS0tLS0tI+V3t3ctHSpmd3XHShTF2Fs6fvt+3YwZnT3V\\/snNLS0tLS0tLS0tLS0tLSPqgVEREREREREREREREREREREREREREREREREREReen8XwAAAP\\/\\/jyG5y4CbZQMAAAAASUVORK5CYII=\",\"ticket_url\":\"https:\\/\\/www.mercadopago.com.br\\/payments\\/144829933062\\/ticket?caller_id=3152606945&hash=35b8830f-a3be-4c3c-aa4a-223d17788ef5\",\"transaction_id\":\"PIXE18236120202602042134s0190b3339c\"},\"type\":\"OPENPLATFORM\"},\"pos_id\":null,\"processing_mode\":\"aggregator\",\"refunds\":[],\"release_info\":null,\"shipping_amount\":0,\"sponsor_id\":null,\"statement_descriptor\":null,\"status\":\"approved\",\"status_detail\":\"accredited\",\"store_id\":null,\"tags\":null,\"taxes_amount\":0,\"transaction_amount\":21,\"transaction_amount_refunded\":0,\"transaction_details\":{\"acquirer_reference\":null,\"bank_transfer_id\":121885891554,\"external_resource_url\":null,\"financial_institution\":\"1\",\"installment_amount\":0,\"net_received_amount\":20.78999999999999914734871708787977695465087890625,\"overpaid_amount\":0,\"payable_deferral_period\":null,\"payment_method_reference_id\":null,\"total_paid_amount\":21,\"transaction_id\":\"PIXE18236120202602042134s0190b3339c\"}}', 21.00, 'pix', 1, 0.21, 0, '2026-02-06 13:47:02'),
(8, 9, '', '143979394703', '', 'pago', '2026-02-06 16:47:10', '2026-02-06 16:47:10', '{\"accounts_info\":null,\"acquirer_reconciliation\":[],\"additional_info\":{\"bank_info\":{\"is_same_bank_account_owner\":true},\"tracking_id\":\"platform:v1-whitelabel,so:ALL,type:N\\/A,security:none\"},\"authorization_code\":null,\"binary_mode\":false,\"brand_id\":null,\"build_version\":\"3.141.0-rc-1\",\"call_for_authorize_id\":null,\"callback_url\":null,\"captured\":true,\"card\":[],\"charges_details\":[{\"accounts\":{\"from\":\"collector\",\"to\":\"mp\"},\"amounts\":{\"original\":0.2099999999999999922284388276239042170345783233642578125,\"refunded\":0},\"client_id\":0,\"date_created\":\"2026-02-03T14:08:05.000-04:00\",\"external_charge_id\":\"01KGJB1CRX711AKECF4YAQ8NHE\",\"id\":\"143979394703-001\",\"last_updated\":\"2026-02-03T14:08:05.000-04:00\",\"metadata\":{\"reason\":\"\",\"source\":\"proc-svc-charges\",\"source_detail\":\"processing_fee_charge\"},\"name\":\"mercadopago_fee\",\"refund_charges\":[],\"reserve_id\":null,\"type\":\"fee\",\"update_charges\":[]}],\"charges_execution_info\":{\"internal_execution\":{\"date\":\"2026-02-03T14:08:05.927-04:00\",\"execution_id\":\"01KGJB1CR2WK11T4W9R66BAA7J\"}},\"collector_id\":260742905,\"corporation_id\":null,\"counter_currency\":null,\"coupon_amount\":0,\"currency_id\":\"BRL\",\"date_approved\":\"2026-02-03T14:08:52.000-04:00\",\"date_created\":\"2026-02-03T14:08:05.000-04:00\",\"date_last_updated\":\"2026-02-03T14:08:56.000-04:00\",\"date_of_expiration\":\"2026-02-04T14:08:05.000-04:00\",\"deduction_schema\":null,\"description\":\"Inscrição no evento: III CORRIDA SAUIM DE COLEIRA\",\"differential_pricing_id\":null,\"external_reference\":\"143978921063\",\"fee_details\":[{\"amount\":0.2099999999999999922284388276239042170345783233642578125,\"fee_payer\":\"collector\",\"type\":\"mercadopago_fee\"}],\"financing_group\":null,\"id\":143979394703,\"installments\":1,\"integrator_id\":null,\"issuer_id\":\"12501\",\"live_mode\":true,\"marketplace_owner\":null,\"merchant_account_id\":null,\"merchant_number\":null,\"metadata\":[],\"money_release_date\":\"2026-02-03T14:08:52.000-04:00\",\"money_release_schema\":null,\"money_release_status\":\"released\",\"notification_url\":\"https:\\/\\/www.movamazon.com.br\\/api\\/mercadolivre\\/webhook.php\",\"operation_type\":\"regular_payment\",\"order\":[],\"payer\":{\"email\":\"XXXXXXXXXXX\",\"entity_type\":null,\"first_name\":null,\"id\":\"1748466803\",\"identification\":{\"number\":\"99999999999\",\"type\":\"CPF\"},\"last_name\":null,\"operator_id\":null,\"phone\":{\"number\":null,\"extension\":null,\"area_code\":null},\"type\":null},\"payment_method\":{\"id\":\"pix\",\"issuer_id\":\"12501\",\"type\":\"bank_transfer\"},\"payment_method_id\":\"pix\",\"payment_type_id\":\"bank_transfer\",\"platform_id\":null,\"point_of_interaction\":{\"application_data\":{\"name\":null,\"operating_system\":null,\"version\":null},\"business_info\":{\"branch\":\"Merchant Services\",\"sub_unit\":\"default\",\"unit\":\"online_payments\"},\"location\":{\"source\":null,\"state_id\":null},\"sub_type\":\"INTER_PSP\",\"transaction_data\":{\"bank_info\":{\"collector\":{\"account_alias\":null,\"account_holder_name\":\"Eudimaci Barboza de Lira\",\"account_id\":30044733964,\"long_name\":\"MERCADO PAGO INSTITUIÇÃO DE PAGAMENTO LTDA.\",\"transfer_account_id\":null},\"is_same_bank_account_owner\":true,\"origin_bank_id\":null,\"origin_wallet_id\":null,\"payer\":{\"account_id\":5861326033,\"branch\":\"1549\",\"external_account_id\":null,\"id\":null,\"identification\":[],\"long_name\":\"Caixa Econômica Federal\"}},\"bank_transfer_id\":121853827116,\"e2e_id\":null,\"financial_institution\":1,\"is_end_consumer\":null,\"merchant_category_code\":null,\"qr_code\":\"00020126580014br.gov.bcb.pix0136fb6df74b-f82a-425b-92ab-55459eab8d69520400005303986540521.005802BR5915EUDIMACIBARBOZA6009Sao Paulo62250521mpqrinter1439793947036304FBDB\",\"qr_code_base64\":\"iVBORw0KGgoAAAANSUhEUgAABWQAAAVkAQMAAABpQ4TyAAAABlBMVEX\\/\\/\\/8AAABVwtN+AAAKrklEQVR42uzdQXIqObMGUDkY1JAlsBSWZi+NpbAEDz0gqBftR5FSSmXov92X6ojzTdztC6VTnmWklCoiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIi8u9mmruc7v\\/4Fr\\/8LOW9\\/jnPcym7+Pfz7WGn+8+328OWh1y\\/H7l8+eP25e9fHnoELS0tLS0tLS0tLS0tLe0vaM\\/p\\/78X3McXP\\/5a8Ft5uf3+cvvw7vvL8\\/wV3z\\/+tdAUurKi\\/X7IIbQN6khLS0tLS0tLS0tLS0u7ZW1Umku5+vn9nUZbbm\\/xcfvPRd2XqYt2HzXvx\\/1nadShXarvL1paWlpaWlpaWlpaWtr\\/lrakhUpqelY51M3OKdqux1ub9XhTxyvvVmpeWlpaWlpaWlpaWlpa2v+o9q3Zh9tsmV06qCV9aW3r7HFl8y4tLS0tLS0tLS0tLS0t7Z\\/Rpt3Cb6m4bncPLwstZ03jzGmJ5nAu85svV1uNf2FvMy0tLS0tLS0tLS0tLe2f1PaTi96GLdrlzOnH\\/azpJcrV5ezpsdty\\/NxD\\/sGcJVpaWlpaWlpaWlpaWto\\/pv0xebfwJTqpTa07NTXvg\\/ZriZr3f1PR0tLS0tLS0tLS0tLSvlK79C1zuZoTW2iXMvWSts4O9uMuHdThwdVB+7UZpktLS0tLS0tLS0tLS0u7MW374Fvz83rbb3uNz1X7cD9G5erhdgCzRAf19spv8zjVK1c7gR+e4qSlpaWlpaWlpaWlpaWlfV67fnPotVd+1te17GLY0KGe1psvjqkmF5XU510LLS0tLS0tLS0tLS0t7Ua11b\\/dyta3KFfzyNrSL5jL1WjV5otjBmOQSinpzOnjCp2WlpaWlpaWlpaWlpb2hdpmoTy56HuD77UvU8+jLcfVRSgl1bx9rXsZtl1\\/Di0tLS0tLS0tLS0tLe1rtUutO6eF5pVytcSZ03IfYfsVCx7vB1ivzQrv91evOqnntPKTalpaWlpaWlpaWlpaWlraZ7RT011tBuyWTrtLc5YGlXpJB1dP92bxWpm\\/q\\/9O5faqMy0tLS0tLS0tLS0tLe0WtZX6FJ9NuTYTi+LSz3lY8zYFc0zrbUcAv9\\/7vLt45WZuMC0tLS0tLS0tLS0tLe32tFMcGz3VNfDnqOYtfdnalK9TDNEt6ebQZrfwZ10gVxfHPDmtl5aWlpaWlpaWlpaWlvZV2mr\\/bfPgfbr0cz86Jjo3k4sabX+LSrX\\/dk6vXhXQz1TotLS0tLS0tLS0tLS0tLTPaKf47Ol+AvSatG\\/9qKSmzzv\\/VFxXN4c2fd5qt3CjnUZlPS0tLS0tLS0tLS0tLe0mtO2g3VvFeW2ua5m7O192zULRHB7kVOuWA6v7lWm9z8xZoqWlpaWlpaWlpaWlpX25Nj4zRa2bK8\\/3BOlfdbhbeFDzVgdYs\\/phn5eWlpaWlpaWlpaWlpb25dp+oc\\/4+X6fXFTtw11uUZnrB0\\/9Jt6szB3UZv7toZvAS0tLS0tLS0tLS0tLS7sp7WD\\/7XDBub6Lc+4PXlaF83AObr6KJd+m0lTd8984xUlLS0tLS0tLS0tLS0tL+1jb7BYuo+FDl0b7aMF+YtFn3PXyXvd3K8nPoaWlpaWlpaWlpaWlpX2ttkSZmufgLmdPo+bdpTtedv3u4ePolYe7hUus2BTO8w81Ly0tLS0tLS0tLS0tLe1rtYPjoqG83j7UNj\\/zAv3Dqhzvr\\/o2fOXmfXP1TUtLS0tLS0tLS0tLS7sx7eDBuem5dhHK2sjb26vnq1faWvf9dnB1+GVaWlpaWlpaWlpaWlpa2l\\/QTqO6+P8XyruFmzOn823OUj57eqy1c6jf7w\\/bxcMOczWtd+qbxbS0tLS0tLS0tLS0tLQb0+abVpqFSqPPX1oWas6c9gu1BXM+exqjf6efHkJLS0tLS0tLS0tLS0u7Ce3wM1lb+rOn5\\/4h0X79qgvmpVC+pvm3JbR53\\/JMS0tLS0tLS0tLS0tLu01tdQFK3n97qjulTfOz9OXqMJ+pYF6uHd3P8\\/rm3cedU1paWlpaWlpaWlpaWlra+bndwpUuzVlqz57217QsI5OqUb9RZF+bJnHu70aHud1yHBfH0NLS0tLS0tLS0tLS0m5PW6lPpayUr4MW7SGVpYf6lauzpn2\\/d24OsIZyObj6zJwlWlpaWlpaWlpaWlpa2ldq+8lFS+c0j6zdpybnIX725eo+DrB+jGrf5RXP9d2lj2teWlpaWlpaWlpaWlpa2ldpp2h+ro+uvYY6b51dFmouRPlK2kHn9D29clP7nmhpaWlpaWlpaWlpaWm3qa1q3uUza3dy9ltod+kAZnsRSrqC5S1uURm0X5dXfrpzSktLS0tLS0tLS0tLS0v7pParVudrWq6NLqb1rmmn9JBmktHypUvTYY4y\\/8ENNbS0tLS0tLS0tLS0tLSb0Tbl6rHWVmdOY6Pvpd8lfKzn4eaHDF45zppWElpaWlpaWlpaWlpaWtqNa+c0uSg3PZcFcs17HhXOX\\/UrV8N0mwlGl9Q5neuCudDS0tLS0tLS0tLS0tJuWbsssNyiUuLYaHOLyr5uel6ag6vpAGt1i8pcjz9qO6jNLSq0tLS0tLS0tLS0tLS0tL+ujSyXfjbKVh8t2ktzXDRtOS7p7GlJf4LBbuHy3JwlWlpaWlpaWlpaWlpa2m1oo\\/ada\\/XcDB2KQbu7KFPP9Zbj4StX6n2azrs0iw\\/1AVZaWlpaWlpaWlpaWlra7Wmnpm\\/Zz70d3PUy3C28LBTl6lv\\/\\/5+j60f\\/TueUlpaWlpaWlpaWlpaW9rXa0vQvmwfH0KG5qXU\\/7rrq2Gg+PtpcP1odXJ3vN4cuX97FygdaWlpaWlpaWlpaWlpa2l\\/VHtK1LaeuIs\\/Xt1Q3h57r3cJV8sUxJT3kI\\/2d8pAmWlpaWlpaWlpaWlpa2u1q43hotVv4dD9jOihXS2oOn0fXjw7PmuYzqLv+IbS0tLS0tLS0tLS0tLQb1U6jhUrom13D1c2hpZ5clFPNvz2ubDluzpqWOHvaXENKS0tLS0tLS0tLS0tLuz3tXHdOS6p5B0OHProts3n\\/7dfK3NvS17pN7\\/b8aP4tLS0tLS0tLS0tLS0t7Wu1A\\/3pp2Znc4vKj7n+2CkdPuRcqos9aWlpaWlpaWlpaWlpaWl\\/V\\/uddnJRqNuhQzG5aIqW7TEdXD11Z02z9tJ0nGlpaWlpaWlpaWlpaWm3rJ36YrXZLVyVq9HvrRY6rBwXPY4mFlVbjaO\\/m1+ZlpaWlpaWlpaWlpaWdtPa80+d0xILftS\\/OsSlnys179rNoZX6MM+55n1mchEtLS0tLS0tLS0tLS3ta7Vx5nSqR9dWyv38N1INHzre9+G2+3HfV8YexSvT0tLS0tLS0tLS0tLS0v4L2tIvMNjoG5X61C+0TOvN5f23ct\\/fHBp93\\/lhn5eWlpaWlpaWlpaWlpZ2a9rBht\\/QVn3eud\\/oG\\/3dZnJRe\\/1o3Bh6aTzP3VBDS0tLS0tLS0tLS0tL+0pt2i1cTS6qOqfLzaHDhQ7pwGpZ3XK8i13D5\\/TlZ26ooaWlpaWlpaWlpaWlpX2t9sfJRWtNz+q46FKmpnJ1OXM62MT7ORqiO2jD0tLS0tLS0tLS0tLS0tL+A62IiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIjIpvN\\/AQAA\\/\\/9FjB1TGhRH3gAAAABJRU5ErkJggg==\",\"ticket_url\":\"https:\\/\\/www.mercadopago.com.br\\/payments\\/143979394703\\/ticket?caller_id=1748466803&hash=822d429d-59b0-444d-84f8-f5828a5cc1b5\",\"transaction_id\":\"PIXE00360305202602031808af9e2145e67\"},\"type\":\"OPENPLATFORM\"},\"pos_id\":null,\"processing_mode\":\"aggregator\",\"refunds\":[],\"release_info\":null,\"shipping_amount\":0,\"sponsor_id\":null,\"statement_descriptor\":null,\"status\":\"approved\",\"status_detail\":\"accredited\",\"store_id\":null,\"tags\":null,\"taxes_amount\":0,\"transaction_amount\":21,\"transaction_amount_refunded\":0,\"transaction_details\":{\"acquirer_reference\":null,\"bank_transfer_id\":121853827116,\"external_resource_url\":null,\"financial_institution\":\"1\",\"installment_amount\":0,\"net_received_amount\":20.78999999999999914734871708787977695465087890625,\"overpaid_amount\":0,\"payable_deferral_period\":null,\"payment_method_reference_id\":null,\"total_paid_amount\":21,\"transaction_id\":\"PIXE00360305202602031808af9e2145e67\"}}', 21.00, 'pix', 1, 0.21, 0, '2026-02-06 13:47:10'),
(9, 7, '', '144201992786', '', 'pago', '2026-02-06 17:28:12', '2026-02-06 17:28:12', '{\"accounts_info\":null,\"acquirer_reconciliation\":[],\"additional_info\":{\"bank_info\":{\"is_same_bank_account_owner\":false},\"tracking_id\":\"platform:v1-whitelabel,so:ALL,type:N\\/A,security:none\"},\"authorization_code\":null,\"binary_mode\":false,\"brand_id\":null,\"build_version\":\"3.140.0-rc-1\",\"call_for_authorize_id\":null,\"callback_url\":null,\"captured\":true,\"card\":[],\"charges_details\":[{\"accounts\":{\"from\":\"collector\",\"to\":\"mp\"},\"amounts\":{\"original\":0.2099999999999999922284388276239042170345783233642578125,\"refunded\":0},\"client_id\":0,\"date_created\":\"2026-01-30T21:49:09.000-04:00\",\"external_charge_id\":\"01KG8VTRCA5WWQE2Q639W7Q1Y6\",\"id\":\"144201992786-001\",\"last_updated\":\"2026-01-30T21:49:09.000-04:00\",\"metadata\":{\"reason\":\"\",\"source\":\"proc-svc-charges\",\"source_detail\":\"processing_fee_charge\"},\"name\":\"mercadopago_fee\",\"refund_charges\":[],\"reserve_id\":null,\"type\":\"fee\",\"update_charges\":[]}],\"charges_execution_info\":{\"internal_execution\":{\"date\":\"2026-01-30T21:49:09.908-04:00\",\"execution_id\":\"01KG8VTRBKEG3RZY8TG644P3FN\"}},\"collector_id\":260742905,\"corporation_id\":null,\"counter_currency\":null,\"coupon_amount\":0,\"currency_id\":\"BRL\",\"date_approved\":\"2026-01-30T21:49:57.000-04:00\",\"date_created\":\"2026-01-30T21:49:09.000-04:00\",\"date_last_updated\":\"2026-01-30T21:50:00.000-04:00\",\"date_of_expiration\":\"2026-01-31T21:49:09.000-04:00\",\"deduction_schema\":null,\"description\":\"Inscrição no evento: III CORRIDA SAUIM DE COLEIRA\",\"differential_pricing_id\":null,\"external_reference\":\"143596976394\",\"fee_details\":[{\"amount\":0.2099999999999999922284388276239042170345783233642578125,\"fee_payer\":\"collector\",\"type\":\"mercadopago_fee\"}],\"financing_group\":null,\"id\":144201992786,\"installments\":1,\"integrator_id\":null,\"issuer_id\":\"12501\",\"live_mode\":true,\"marketplace_owner\":null,\"merchant_account_id\":null,\"merchant_number\":null,\"metadata\":[],\"money_release_date\":\"2026-01-30T21:49:57.000-04:00\",\"money_release_schema\":null,\"money_release_status\":\"released\",\"notification_url\":\"https:\\/\\/www.movamazon.com.br\\/api\\/mercadolivre\\/webhook.php\",\"operation_type\":\"regular_payment\",\"order\":[],\"payer\":{\"email\":\"XXXXXXXXXXX\",\"entity_type\":null,\"first_name\":null,\"id\":\"2607138950\",\"identification\":{\"number\":\"99999999999\",\"type\":\"CPF\"},\"last_name\":null,\"operator_id\":null,\"phone\":{\"number\":null,\"extension\":null,\"area_code\":null},\"type\":null},\"payment_method\":{\"id\":\"pix\",\"issuer_id\":\"12501\",\"type\":\"bank_transfer\"},\"payment_method_id\":\"pix\",\"payment_type_id\":\"bank_transfer\",\"platform_id\":null,\"point_of_interaction\":{\"application_data\":{\"name\":null,\"operating_system\":null,\"version\":null},\"business_info\":{\"branch\":\"Merchant Services\",\"sub_unit\":\"default\",\"unit\":\"online_payments\"},\"location\":{\"source\":null,\"state_id\":null},\"sub_type\":\"INTER_PSP\",\"transaction_data\":{\"bank_info\":{\"collector\":{\"account_alias\":null,\"account_holder_name\":\"Eudimaci Barboza de Lira\",\"account_id\":30044733964,\"long_name\":\"MERCADO PAGO INSTITUIÇÃO DE PAGAMENTO LTDA.\",\"transfer_account_id\":null},\"is_same_bank_account_owner\":false,\"origin_bank_id\":null,\"origin_wallet_id\":null,\"payer\":{\"account_id\":137146,\"branch\":\"1374\",\"external_account_id\":null,\"id\":null,\"identification\":[],\"long_name\":\"BANCO BRADESCO S.A.\"}},\"bank_transfer_id\":121707720765,\"e2e_id\":null,\"financial_institution\":1,\"is_end_consumer\":null,\"merchant_category_code\":null,\"qr_code\":\"00020126580014br.gov.bcb.pix0136fb6df74b-f82a-425b-92ab-55459eab8d69520400005303986540521.005802BR5915EUDIMACIBARBOZA6009Sao Paulo62250521mpqrinter1442019927866304DFF8\",\"qr_code_base64\":\"iVBORw0KGgoAAAANSUhEUgAABWQAAAVkAQMAAABpQ4TyAAAABlBMVEX\\/\\/\\/8AAABVwtN+AAAK00lEQVR42uzdQXIaSxIG4CJYsNQROApHs47GUTiCliwIasIamsrM7kbys98DR3z\\/htAzdH81u5zKymoiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIi8u9m12c5\\/vzvb7fP1tqPn\\/\\/xY3y2tv383vT5mdPtYcf75+b2sM3tG9fxsN77+\\/0hrbX9HEFLS0tLS0tLS0tLS0tL+we0p\\/L3pDzcdZub9jJeeLqpT631fh6\\/P\\/x80W7o2oq2\\/3zYfmgT6kBLS0tLS0tLS0tLS0v7ytpRaU7l6kf8Uv57Uvf755RzLKSvt8J5c6txp8+W1EO7Gw+hpaWlpaWlpaWlpaWl\\/Zu0rbwo76Cm7Jd2TkPBfLip3+9L3q7UvLS0tLS0tLS0tLS0tLR\\/qfZBy+xHrHnDj9ZaZw+leXd955SWlpaWlpaWlpaWlpaW9t\\/Qlm7hTSmu\\/1+x9\\/KiqTIfFfqk6\\/MyP\\/24jVbjP9DbTEtLS0tLS0tLS0tLS\\/tfaueTizaLW7Sfte7brfY93bqFp3J1Ont6KC3H333Ib8xZoqWlpaWlpaWlpaWlpf3PtA9Tu4Xz5KLUJXyK5Wqaf9vK9msbNe8\\/U9HS0tLS0tLS0tLS0tI+UzvtWx5WJxeFF05l6z620Nb+2938Spb3JfXC9msapktLS0tLS0tLS0tLS0v7Ytrcf3vb\\/Lze+m2v419CH+77UrkaWminHdRxBctiwpKD5FdOcdLS0tLS0tLS0tLS0tLSnr93ivPYWprW28uZ0zG5aKrEt\\/O7XsZDpm7hPLlo\\/OhxaGlpaWlpaWlpaWlpaV9cO76zGeVqHVmbsi37ulOtex6Fcro4ZmEMUov7vd+Zf0tLS0tLS0tLS0tLS0v7XO3DoUOfDb75NpVx1nRbtl17rIFzzVuXmJa+j0tsX3YL09LS0tLS0tLS0tLS0j5R20vF2e5DhzZl2NB0bDRfo5ledLgfYL2WH13LwdWebgH9fMiXalpaWlpaWlpaWlpaWlraX9eeR5F9vP9jOC46NfqmOUt98fLPdHD1eN8svpYKPZT5fWXJtLS0tLS0tLS0tLS0tC+mDerj+G5KnVj0ft\\/f3c7L1cP9AGu48yXcHJoK51Y2i9PcYFpaWlpaWlpaWlpaWtrX0+7GsdFj3McMo2vf72VrfkHv9QbRFs+cbkoBHbqFP2KLcbg45pvzb2lpaWlpaWlpaWlpaWmfpW1jx7TPhg6FcnWhDzdNLjrFpbelW1TywdX3+JCFJdPS0tLS0tLS0tLS0tLS\\/q52N757vL\\/w+oV2uUKfHlKOjdaW4zbUpyXtbqmsp6WlpaWlpaWlpaWlpX0JbRu7rD0OHQrXtfSlgbuntnznSyqgwz7vmFh0ud35sjCt9ztTe2lpaWlpaWlpaWlpaWmfrh3f2Y1ad\\/6ieufLwlJrrbu45HCAtaq\\/3OelpaWlpaWlpaWlpaWlfbp2nDkNl362OLr2bUww6nH+bVUeShNvVS4W0OsTeGlpaWlpaWlpaWlpaWlfSrsrrbP1u29lDm4r12ieYuHcyynOeqHnVOt+tNltKtPO6UoBTUtLS0tLS0tLS0tLS0v7O9rULbw4fOiStIsvnB46cp13C6c7X7LkcWhpaWlpaWlpaWlpaWmfq2233dWFXdbp7OmoebelW3ibjpemRt+05MVu4TbeeCrq\\/tW0XlpaWlpaWlpaWlpaWtrnaOvNoeeovN6+lDc\\/52dOH7zocF\\/qZnHJvRxkfTy5iJaWlpaWlpaWlpaWlva52oUH103PtYtQ+kpuS9+Us6W51v1RDq6mH39RodPS0tLS0tLS0tLS0tLSfke70C08zVeq3cLpzOnnl7fjss\\/TWPLQ9qH+cX\\/Ydjxs38O03h0tLS0tLS0tLS0tLS3ty2vrTSvpRVOZulkZsHspSz4vXdeSC+Z69nQa\\/bvyvxstLS0tLS0tLS0tLS3tS2kXX7RJZ09by3NwF4+JnuL26zkWzFOhfC3zb9vQ\\/nKFTktLS0tLS0tLS0tLS\\/sc7bR\\/eV7qv+0r5ep0PHS5hbY0627KzaGX+dLXxh\\/R0tLS0tLS0tLS0tLS0v6GNlzPkuYsha7hH\\/HalmlUUpjWW8+g3h5aH5L3d8cO8zZdQ\\/qdqVC0tLS0tLS0tLS0tLS0z9IG9THq042h1zF06G0M3F1fcjhrOt\\/v7ekA61D2cYD1yzlLtLS0tLS0tLS0tLS0tM\\/Urk8uqhOLPuLkorDtOn220mJ8vI89Wqh996P1eNxd+nXNS0tLS0tLS0tLS0tLS\\/ss7VSuntcnF9Wat5cXpZq3R\\/XbfP7ttHM6X3KofY+0tLS0tLS0tLS0tLS0r6kNNe\\/0nbTZ+TZaadOLQv\\/tVPOmTc\\/5FSybcYvKR+y\\/baPW3X\\/v5lBaWlpaWlpaWlpaWlpa2u9rp27h0eibJhZNx0e344VJm86a5jJ\\/PskoXxiTyvp9\\/P8KaGlpaWlpaWlpaWlpaV9VO695e9yiXdvnvcy7hA9Dnc6cpiWH8UdjszjUvrS0tLS0tLS0tLS0tLQvqt2VBt86unba9OxjYlGaf1tfFF54017HrSlpgtGl7Jz2WDA3WlpaWlpaWlpaWlpa2hfVnuILFoYPpf7bt7jpeUlLLgdY65Lb\\/CH1FhVaWlpaWlpaWlpaWlpa2j+ozd3CpdF3Ul6TfmzRXtJx0VGhrx1gDS3HP+Jcpb7yY1paWlpaWlpaWlpaWtoX0+7Kd89xa7Zu0W7LnS\\/bUaae4nUtu\\/jjacl9PrmolW7hfTzASktLS0tLS0tLS0tLS\\/uS2lMsT3t5UTpr2te7hff3g6ot7Zimvz\\/izmlY8i\\/u89LS0tLS0tLS0tLS0tI+Sxsy5gVtytChnmrd97suHButx0fH9aP54Gq\\/X8US+m97fAgtLS0tLS0tLS0tLS0t7R\\/T7su1LcdZRb5J2nHWtJVpvbvxo3pxTCt3wLzHpc13mjstLS0tLS0tLS0tLS3t62rHPu853vkynTFdGD4UGn3n17aEC2NSFqb2putHT7S0tLS0tLS0tLS0tLSvrc3HRedDh9KmZx8Nv2sFc51\\/e1hpOV5cYjq4SktLS0tLS0tLS0tLS\\/uS2l7mBR3K8dEf9zOolzH\\/tu6gtvji89Lc25rcd5sK6CMtLS0tLS0tLS0tLS3ta2oX9MdHm51B22YttCnXhzul72Vi0aiBd+nhtLS0tLS0tLS0tLS0tLR\\/SvuZOrkobNXWbuFw58tU3qeDq8fZWdOqvaQdZ1paWlpaWlpaWlpaWtpX1u7mxWrqFk4Ti8LlnwvlajoueliaWLTYanwZc29paWlpaWlpaWlpaWlpX197erRzGvKjdAenzc50\\/ei5Pbg5NKj3seV49+3JRbS0tLS0tLS0tLS0tLTP1aZN0DG6dtMfJY2uTQnDhw73Ptzcj1u3X+upV1paWlpaWlpaWlpaWlraf0Xb0gvGnS\\/b9Dnfok3dwnnJqdX4bX5zaFv5m5aWlpaWlpaWlpaWlvZv0C40\\/A7tdN3odvxooWwtLcabscTWwo2htXv4OzfU0NLS0tLS0tLS0tLS0j5TW7qFw+SiPi79\\/NR+PHrRw+tawpKnruFT+fHpi35lWlpaWlpaWlpaWlpa2udrH04uWtv0DMdFpzK1lKvTmdNNWnJfOnO6LzeI\\/uM5S7S0tLS0tLS0tLS0tLS0IiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIn9J\\/hcAAP\\/\\/7M30dZTG5DIAAAAASUVORK5CYII=\",\"ticket_url\":\"https:\\/\\/www.mercadopago.com.br\\/payments\\/144201992786\\/ticket?caller_id=2607138950&hash=0fba03b5-b44f-47a0-88e3-3baa436df2db\",\"transaction_id\":\"PIXE60746948202601310149A1374g5qVHM\"},\"type\":\"OPENPLATFORM\"},\"pos_id\":null,\"processing_mode\":\"aggregator\",\"refunds\":[],\"release_info\":null,\"shipping_amount\":0,\"sponsor_id\":null,\"statement_descriptor\":null,\"status\":\"approved\",\"status_detail\":\"accredited\",\"store_id\":null,\"tags\":null,\"taxes_amount\":0,\"transaction_amount\":21,\"transaction_amount_refunded\":0,\"transaction_details\":{\"acquirer_reference\":null,\"bank_transfer_id\":121707720765,\"external_resource_url\":null,\"financial_institution\":\"1\",\"installment_amount\":0,\"net_received_amount\":20.78999999999999914734871708787977695465087890625,\"overpaid_amount\":0,\"payable_deferral_period\":null,\"payment_method_reference_id\":null,\"total_paid_amount\":21,\"transaction_id\":\"PIXE60746948202601310149A1374g5qVHM\"}}', 21.00, 'pix', 1, 0.21, 0, '2026-02-06 14:28:12'),
(10, 17, 'sync_145198696440', '145198696440', 'https://www.mercadopago.com.br/payments/145198696440/ticket?caller_id=1685886032&hash=50cbf39a-b114-43be-acc1-3d6d2717eed4', 'pago', '2026-02-07 03:39:39', '2026-02-12 14:29:09', '{\"accounts_info\":null,\"acquirer_reconciliation\":[],\"additional_info\":{\"bank_info\":{\"is_same_bank_account_owner\":false},\"tracking_id\":\"platform:v1-whitelabel,so:ALL,type:N\\/A,security:none\"},\"authorization_code\":null,\"binary_mode\":false,\"brand_id\":null,\"build_version\":\"3.141.0-rc-1\",\"call_for_authorize_id\":null,\"callback_url\":null,\"captured\":true,\"card\":[],\"charges_details\":[{\"accounts\":{\"from\":\"collector\",\"to\":\"mp\"},\"amounts\":{\"original\":0.2099999999999999922284388276239042170345783233642578125,\"refunded\":0},\"client_id\":0,\"date_created\":\"2026-02-06T23:39:32.000-04:00\",\"external_charge_id\":\"01KGV2XWY1P44V9CXS0QAH9T2Z\",\"id\":\"145198696440-001\",\"last_updated\":\"2026-02-06T23:39:32.000-04:00\",\"metadata\":{\"reason\":\"\",\"source\":\"proc-svc-charges\",\"source_detail\":\"processing_fee_charge\"},\"name\":\"mercadopago_fee\",\"refund_charges\":[],\"reserve_id\":null,\"type\":\"fee\",\"update_charges\":[]}],\"charges_execution_info\":{\"internal_execution\":{\"date\":\"2026-02-06T23:39:32.683-04:00\",\"execution_id\":\"01KGV2XWXC6P424C5ZXM1JRPWW\"}},\"collector_id\":260742905,\"corporation_id\":null,\"counter_currency\":null,\"coupon_amount\":0,\"currency_id\":\"BRL\",\"date_approved\":\"2026-02-06T23:40:23.000-04:00\",\"date_created\":\"2026-02-06T23:39:32.000-04:00\",\"date_last_updated\":\"2026-02-06T23:40:28.000-04:00\",\"date_of_expiration\":\"2026-02-07T23:39:32.000-04:00\",\"deduction_schema\":null,\"description\":\"Inscrição no evento: III CORRIDA SAUIM DE COLEIRA\",\"differential_pricing_id\":null,\"external_reference\":\"MOVAMAZON_1770346141_28\",\"fee_details\":[{\"amount\":0.2099999999999999922284388276239042170345783233642578125,\"fee_payer\":\"collector\",\"type\":\"mercadopago_fee\"}],\"financing_group\":null,\"id\":145198696440,\"installments\":1,\"integrator_id\":null,\"issuer_id\":\"12501\",\"live_mode\":true,\"marketplace_owner\":null,\"merchant_account_id\":null,\"merchant_number\":null,\"metadata\":[],\"money_release_date\":\"2026-02-06T23:40:23.000-04:00\",\"money_release_schema\":null,\"money_release_status\":\"released\",\"notification_url\":\"https:\\/\\/www.movamazon.com.br\\/api\\/mercadolivre\\/webhook.php\",\"operation_type\":\"regular_payment\",\"order\":[],\"payer\":{\"email\":\"XXXXXXXXXXX\",\"entity_type\":null,\"first_name\":null,\"id\":\"1685886032\",\"identification\":{\"number\":\"99999999999\",\"type\":\"CPF\"},\"last_name\":null,\"operator_id\":null,\"phone\":{\"number\":null,\"extension\":null,\"area_code\":null},\"type\":null},\"payment_method\":{\"id\":\"pix\",\"issuer_id\":\"12501\",\"type\":\"bank_transfer\"},\"payment_method_id\":\"pix\",\"payment_type_id\":\"bank_transfer\",\"platform_id\":null,\"point_of_interaction\":{\"application_data\":{\"name\":null,\"operating_system\":null,\"version\":null},\"business_info\":{\"branch\":\"Merchant Services\",\"sub_unit\":\"default\",\"unit\":\"online_payments\"},\"location\":{\"source\":null,\"state_id\":null},\"sub_type\":\"INTER_PSP\",\"transaction_data\":{\"bank_info\":{\"collector\":{\"account_alias\":null,\"account_holder_name\":\"Eudimaci Barboza de Lira\",\"account_id\":30044733964,\"long_name\":\"MERCADO PAGO INSTITUIÇÃO DE PAGAMENTO LTDA.\",\"transfer_account_id\":null},\"is_same_bank_account_owner\":false,\"origin_bank_id\":null,\"origin_wallet_id\":null,\"payer\":{\"account_id\":622226024,\"branch\":\"1\",\"external_account_id\":null,\"id\":null,\"identification\":[],\"long_name\":\"NU PAGAMENTOS S.A. - INSTITUIÇÃO DE PAGAMENTO\"}},\"bank_transfer_id\":121960548180,\"e2e_id\":null,\"financial_institution\":1,\"is_end_consumer\":null,\"merchant_category_code\":null,\"qr_code\":\"00020126580014br.gov.bcb.pix0136fb6df74b-f82a-425b-92ab-55459eab8d69520400005303986540521.005802BR5915EUDIMACIBARBOZA6009Sao Paulo62250521mpqrinter14519869644063044E1E\",\"qr_code_base64\":\"iVBORw0KGgoAAAANSUhEUgAABWQAAAVkAQMAAABpQ4TyAAAABlBMVEX\\/\\/\\/8AAABVwtN+AAAKzElEQVR42uzdQZLiSBIF0MBYsOQIHIWjZR6No3AEliwwNFa0RIS7QkBNZxeU2fubnKxJ0FPv3NzDo4iIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIjIf5vNMMvh179v689bTqV8DZfxQ+vbP00\\/bzmOX3a4\\/1yNX7Ya\\/+J6++jX+Nv3\\/UtKKbs5gpaWlpaWlpaWlpaWlpb2B7TH9Pth\\/J\\/78QHl1wODtlT1sZRhONd\\/2v960KbqyoJ2+PVlu6oN37unpaWlpaWlpaWlpaWl\\/WRtrTSncvXU\\/lHz+3Z8UPn1oennMH\\/lm25ba97v+88S1FU7Vd9nWlpaWlpaWlpaWlpa2r9LW9KDSmp6NtnVzmltu\\/5Ttt5ecT+qv++vvF6oeWlpaWlpaWlpaWlpaWn\\/Uu0qPDgoQ83bfGhpdHZ\\/H95tftLS0tLS0tLS0tLS0tLS\\/jFtmhZepeK6OXvaPChU5sdWN8zL\\/PDhZtT4B2abaWlpaWlpaWlpaWlpaf+kdr65aNVt0d7OnG7HpUPHcVp4Klens6f7NHL86pf8iz1LtLS0tLS0tLS0tLS0tH9M+zB5WvhSO6mh1t2Emvd5+zWfOf2Z0NLS0tLS0tLS0tLS0v4J7dS33C9uLmoeMJWtu3aENs\\/fbuZXsnQPrnbar2GZLi0tLS0tLS0tLS0tLe3naZuMzc\\/rOG97rf9PM4cbrtEs8xHaqYM6vvJq6Kd55WYS+PEpTlpaWlpaWlpaWlpaWlra39Luxn1B02emonpIZ05vylPbol3XZUPhw2FaOG4uKm3TeDcshpaWlpaWlpaWlpaWlvaTteFvVrVczStrQ9a1r3tsa91zLZTDxTGdNUhZ8FrNS0tLS0tLS0tLS0tLS\\/te7dLSoVt5eg0d07DvtvPKozbWvPkVw6uHqrs8nW2mpaWlpaWlpaWlpaWlfZe2e43mqu7BbZYN5aZneNAulav7dnh3euWS7uRsbgENX0JLS0tLS0tLS0tLS0tL+4Paf\\/4mLNit23pXYeD3q738s2nVTp8KB1cP9w9fU4U+lfl55PiVm0NpaWlpaWlpaWlpaWlp36PtqPfD43ynVm2ueWufN2zrXc3PnE76Y+330tLS0tLS0tLS0tLS0n6ydlOPjR5adV5d26l1h3b50HSD6OF+bcsqFdDNtHCjDhfHPN1\\/S0tLS0tLS0tLS0tLS\\/tebQkd07R0qMxvU1l65aAdeq\\/czN+God51uDn0lQqdlpaWlpaWlpaWlpaWlvYV7ab+bX3QNWjDot3Qmo0PWiiuV0lfqvrY0256ZT0tLS0tLS0tLS0tLS3tR2hL7bIO7dKh5rqWcINo7vN2Ro3DOqRDq5uaxduFbb2vbO2lpaWlpaWlpaWlpaWlfbu2\\/s2m1rr1QbHpGZblhlfdpDtfhvaVm5p3Oz+4Wr+svHZzKC0tLS0tLS0tLS0tLe3btPPjoqf0c6gd1KG3\\/7b0Rme3c2XuoIb9t7v29CstLS0tLS0tLS0tLS3t52k787fhb7ft3ts4OhsOXu5qp3R+i8rQDvFOm4vibSpBO\\/zGKU5aWlpaWlpaWlpaWlpa2ufaMC1cxutbwvKhS9B2Hximh\\/PGombl71fb320+9HhUmJaWlpaWlpaWlpaWlva92n8yPSjUvFPZWmveqAvaIQ36hlfuTguX+sRj6jgPz7b10tLS0tLS0tLS0tLS0r5HG28ODU3PentKnhbudEyPC2Xr\\/v6qq+4rh\\/f9zXtOaWlpaWlpaWlpaWlpaf+0tvPFuem5dBFKrXU3qfbdjHO3p\\/SEbTvEe6mvOv9woaWlpaWlpaWlpaWlpaX9t9rOtPC0XylPC4czp7c\\/Xs\\/Pnu5b7VDVX\\/cvW9cv2w2zi2NoaWlpaWlpaWlpaWlpP1mbb1rpPKik8rXe\\/XJJr9xt0caCOZ89HcbaN3xJoaWlpaWlpaWlpaWlpf1MbfdB8dLPMCU8Dfwe519S26\\/ntmCeXvGa9t+Wqs0179P9t7S0tLS0tLS0tLS0tLTv0U79y\\/N8yVDYXHSa17rDwghtGtZdpZtDL+HV5\\/O35fGZU1paWlpaWlpaWlpaWlraF7XNcdH5pZ8l7FvKLdqysDJp\\/NJrGD2ef3jqMMeR46d9XlpaWlpaWlpaWlpaWto3ahv1YaZf1Q1GoUW7nrdid+0rN6PG837vEA6whlddWvlLS0tLS0tLS0tLS0tL+1Ha+eaioZ0OLqHmDdqp7Tr9DNm2I8ed2nfXFszT3aXPa15aWlpaWlpaWlpaWlrad2mnGvecNhed2qbntV6AMo3QhhtDS5rDPSdtp3P61f732aX264GWlpaWlpaWlpaWlpb2M7VNzTv9zb7XOc0177Gdvz3XFbbNQcx0BcuqfvjUzt+WWuvuXthcREtLS0tLS0tLS0tLS0v7f2mrOhTX13rGtKsNZ03jxTHzTUbrtP5oCK+8cHEMLS0tLS0tLS0tLS0t7Qdp65nTUvu7+3trtunzlnbQ9zKfEt5X9fxLStV+p7VHofalpaWlpaWlpaWlpaWl\\/VBt3H8bHhSans0DvtuaNz+oeWD3KpaSXrlbOD+dbaalpaWlpaWlpaWlpaV9j7aEcrW7fKjO317qmdPpLs7mldMB1uYWlSEVzM3B1dC7paWlpaWlpaWlpaWlpaX9QW23Qr8m5TXov++XfV5qRd70e+cHWE\\/z5371poXLq1uhaGlpaWlpaWlpaWlpad+jLelB51TrbtNx0VO7aHddy9Rj++HuK+czp\\/nG0Ljyl5aWlpaWlpaWlpaWlvZDtbn2rcl3vQzL08K7tnBuOqb5IOtX+yXTKx9\\/t89LS0tLS0tLS0tLS0tL+x5tvPgkfHFdOjSkB5WwuWjShlHaw10bD64O95tD45nT+iW0tLS0tLS0tLS0tLS0tD+jzd3VTb3zpVbksbiuZ02bJvGuPXM6pDtfmqVNQ\\/vK3WnhJ7uFaWlpaWlpaWlpaWlpad+o3dRW7f6+dKhp2W4XytVSB30Xjo2u5mdNO1t759PDtLS0tLS0tLS0tLS0tJ+uXV46lGveU9WGgvnYjhw3+2\\/3CyPH4axpePXz45lmWlpaWlpaWlpaWlpa2vdqh7QvaD87PlrqxSfb9sxpmXdQ882hy4lzt6GAPtDS0tLS0tLS0tLS0tJ+prajz8rv9jrNcItK7px21h4tdUq\\/08aiXDjT0tLS0tLS0tLS0tLS0v609pa8uehaK\\/S8dOg4HzkOB1cPs7OmWXtJc8u0tLS0tLS0tLS0tLS0n6vt3PUSpoWDtnP559Jx0X1vY1F31PhS1x7R0tLS0tLS0tLS0tLSfr72+KhzWkZtSQ8q6bLPUPOW8vjm0FMtnHPN+\\/KeJVpaWlpaWlpaWlpaWtq3acOD6+ra1XyUNhxQXS+M3jbLh\\/b3D8fCOSzR7Zx6paWlpaWlpaWlpaWlpaX9T7Rl\\/oA86Dv0WrRhWji+8ve93L\\/U7b2hzM93mNLS0tLS0tLS0tLS0tL+PdrOwG\\/VlnrmdOgN+uYR5FBAT33ddfp90r9yQw0tLS0tLS0tLS0tLS3tO7VpWrjZXJT33p4ePejhdS3NK09Tw8f27GnnvxctLS0tLS0tLS0tLS3t52kfbi5aano2x0VvZeouzd3WM6fxCpahd+Z09xudU1paWlpaWlpaWlpaWlraF7UiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiH53\\/BQAA\\/\\/+p8MIrxhq6mQAAAABJRU5ErkJggg==\",\"ticket_url\":\"https:\\/\\/www.mercadopago.com.br\\/payments\\/145198696440\\/ticket?caller_id=1685886032&hash=50cbf39a-b114-43be-acc1-3d6d2717eed4\",\"transaction_id\":\"PIXE18236120202602070340s129be0dc91\"},\"type\":\"OPENPLATFORM\"},\"pos_id\":null,\"processing_mode\":\"aggregator\",\"refunds\":[],\"release_info\":null,\"shipping_amount\":0,\"sponsor_id\":null,\"statement_descriptor\":null,\"status\":\"approved\",\"status_detail\":\"accredited\",\"store_id\":null,\"tags\":null,\"taxes_amount\":0,\"transaction_amount\":21,\"transaction_amount_refunded\":0,\"transaction_details\":{\"acquirer_reference\":null,\"bank_transfer_id\":121960548180,\"external_resource_url\":null,\"financial_institution\":\"1\",\"installment_amount\":0,\"net_received_amount\":20.78999999999999914734871708787977695465087890625,\"overpaid_amount\":0,\"payable_deferral_period\":null,\"payment_method_reference_id\":null,\"total_paid_amount\":21,\"transaction_id\":\"PIXE18236120202602070340s129be0dc91\"}}', 21.00, 'pix', 1, 0.21, 28, '2026-02-07 00:39:39');
INSERT INTO `pagamentos_ml` (`id`, `inscricao_id`, `preference_id`, `payment_id`, `init_point`, `status`, `data_criacao`, `data_atualizacao`, `dados_pagamento`, `valor_pago`, `metodo_pagamento`, `parcelas`, `taxa_ml`, `user_id`, `created`) VALUES
(11, 19, '', 'MOVAMAZON_1770513540_27', '', 'pago', '2026-02-08 02:53:32', '2026-02-08 02:53:32', '{\"accounts_info\":null,\"acquirer_reconciliation\":[],\"additional_info\":{\"ip_address\":\"170.83.118.216\",\"items\":[{\"category_id\":\"EBL-Evento Desportivo\",\"description\":\"Inscrição no evento: III CORRIDA SAUIM DE COLEIRA\",\"id\":\"MOVAMAZON_19\",\"picture_url\":\"https:\\/\\/http2.mlstatic.com\\/D_NQ_NP_909058-MLB106690718911_022026-F.jpg\",\"quantity\":\"1\",\"title\":\"Corrida de 10 km para teste\",\"unit_price\":\"21\"}],\"payer\":{\"first_name\":\"Davi Sandoval\"},\"tracking_id\":\"platform:v1-blacklabel,so:ALL,type:N\\/A,security:none\"},\"authorization_code\":\"288340\",\"binary_mode\":false,\"brand_id\":null,\"build_version\":\"3.141.0-rc-1\",\"call_for_authorize_id\":null,\"captured\":true,\"card\":{\"bin\":\"43317800\",\"cardholder\":{\"identification\":{\"number\":\"77042050487\",\"type\":\"CPF\"},\"name\":\"Sandoval B Silva\"},\"country\":\"BRA\",\"date_created\":\"2026-02-07T22:29:46.000-04:00\",\"date_last_updated\":\"2026-02-07T22:29:46.000-04:00\",\"expiration_month\":1,\"expiration_year\":2031,\"first_six_digits\":\"433178\",\"id\":\"9744348027\",\"last_four_digits\":\"8931\",\"tags\":[\"credit\"]},\"charges_details\":[{\"accounts\":{\"from\":\"collector\",\"to\":\"mp\"},\"amounts\":{\"original\":1.0500000000000000444089209850062616169452667236328125,\"refunded\":0},\"client_id\":0,\"date_created\":\"2026-02-07T22:29:46.000-04:00\",\"external_charge_id\":\"01KGXHAVV3M69F15JZTA9T6MCE\",\"id\":\"145335180866-001\",\"last_updated\":\"2026-02-07T22:29:46.000-04:00\",\"metadata\":{\"reason\":\"\",\"source\":\"proc-svc-charges\",\"source_detail\":\"processing_fee_charge\"},\"name\":\"mercadopago_fee\",\"refund_charges\":[],\"reserve_id\":null,\"type\":\"fee\",\"update_charges\":[]}],\"charges_execution_info\":{\"internal_execution\":{\"date\":\"2026-02-07T22:29:46.478-04:00\",\"execution_id\":\"01KGXHAVTD5Q23GNT3ZC9DXVF9\"}},\"collector_id\":260742905,\"corporation_id\":null,\"counter_currency\":null,\"coupon_amount\":0,\"currency_id\":\"BRL\",\"date_approved\":\"2026-02-07T22:29:48.000-04:00\",\"date_created\":\"2026-02-07T22:29:46.000-04:00\",\"date_last_updated\":\"2026-02-07T22:29:55.000-04:00\",\"date_of_expiration\":null,\"deduction_schema\":null,\"description\":\"Corrida de 10 km para teste\",\"differential_pricing_id\":null,\"external_reference\":\"MOVAMAZON_1770513540_27\",\"fee_details\":[{\"amount\":1.0500000000000000444089209850062616169452667236328125,\"fee_payer\":\"collector\",\"type\":\"mercadopago_fee\"}],\"financing_group\":null,\"id\":145335180866,\"installments\":1,\"integrator_id\":null,\"issuer_id\":\"25\",\"live_mode\":true,\"marketplace_owner\":null,\"merchant_account_id\":null,\"merchant_number\":null,\"metadata\":{\"inscricao_id\":19,\"evento_nome\":\"III CORRIDA SAUIM DE COLEIRA\",\"usuario_id\":27,\"modalidade_nome\":\"Corrida de 10 km para teste\"},\"money_release_date\":\"2026-02-07T22:29:48.000-04:00\",\"money_release_schema\":null,\"money_release_status\":\"released\",\"notification_url\":\"https:\\/\\/www.movamazon.com.br\\/api\\/mercadolivre\\/webhook.php\",\"operation_type\":\"regular_payment\",\"order\":{\"id\":\"38006581346\",\"type\":\"mercadopago\"},\"payer\":{\"email\":\"sandoval.bezerra@gmail.com\",\"entity_type\":null,\"first_name\":null,\"id\":\"5680427\",\"identification\":{\"number\":\"77042050487\",\"type\":\"CPF\"},\"last_name\":null,\"operator_id\":null,\"phone\":{\"number\":null,\"extension\":null,\"area_code\":null},\"type\":null},\"payment_method\":{\"data\":{\"routing_data\":{\"merchant_account_id\":\"2033\"}},\"id\":\"visa\",\"issuer_id\":\"25\",\"type\":\"credit_card\"},\"payment_method_id\":\"visa\",\"payment_type_id\":\"credit_card\",\"platform_id\":null,\"point_of_interaction\":{\"application_data\":{\"name\":\"checkout-off\",\"operating_system\":null,\"version\":\"v2\"},\"business_info\":{\"branch\":\"Merchant Services\",\"sub_unit\":\"checkout_pro\",\"unit\":\"online_payments\"},\"location\":{\"source\":\"Payer\",\"state_id\":\"BR-PE\"},\"transaction_data\":{\"e2e_id\":null,\"ticket_id\":\"57072380361_7773667b7b797a726737_P\"},\"type\":\"CHECKOUT\"},\"pos_id\":null,\"processing_mode\":\"aggregator\",\"refunds\":[],\"release_info\":null,\"shipping_amount\":0,\"sponsor_id\":null,\"statement_descriptor\":\"EC*MOVAMAZON             \",\"status\":\"approved\",\"status_detail\":\"accredited\",\"store_id\":null,\"tags\":null,\"taxes_amount\":0,\"transaction_amount\":21,\"transaction_amount_refunded\":0,\"transaction_details\":{\"acquirer_reference\":null,\"external_resource_url\":null,\"financial_institution\":null,\"installment_amount\":21,\"net_received_amount\":19.949999999999999289457264239899814128875732421875,\"overpaid_amount\":0,\"payable_deferral_period\":null,\"payment_method_reference_id\":null,\"total_paid_amount\":21}}', 21.00, 'visa', 1, 1.05, 0, '2026-02-07 23:53:32'),
(12, 20, 'sync_145792108627', '145792108627', 'https://www.mercadopago.com.br/payments/145792108627/ticket?caller_id=3206743703&hash=d504f683-92ca-4539-9923-5e145354555a', 'pendente', '2026-02-16 12:23:39', '2026-02-16 12:23:39', '{\"accounts_info\":null,\"acquirer_reconciliation\":[],\"additional_info\":{\"tracking_id\":\"platform:8|8.3.30,so:so;,type:SDK3.8.0,security:none\"},\"authorization_code\":null,\"binary_mode\":false,\"brand_id\":null,\"build_version\":\"3.143.0-rc-4\",\"call_for_authorize_id\":null,\"callback_url\":null,\"captured\":true,\"card\":[],\"charges_details\":[{\"accounts\":{\"from\":\"collector\",\"to\":\"mp\"},\"amounts\":{\"original\":0.2099999999999999922284388276239042170345783233642578125,\"refunded\":0},\"client_id\":0,\"date_created\":\"2026-02-16T08:23:33.000-04:00\",\"external_charge_id\":\"01KHK6FVPNBXF43ZH6PWZXDS9P\",\"id\":\"145792108627-001\",\"last_updated\":\"2026-02-16T08:23:33.000-04:00\",\"metadata\":{\"reason\":\"\",\"source\":\"proc-svc-charges\",\"source_detail\":\"processing_fee_charge\"},\"name\":\"mercadopago_fee\",\"refund_charges\":[],\"reserve_id\":null,\"type\":\"fee\",\"update_charges\":[]}],\"charges_execution_info\":{\"internal_execution\":{\"date\":\"2026-02-16T08:23:33.343-04:00\",\"execution_id\":\"01KHK6FVNSD8CAV0Q92ECZ5WZG\"}},\"collector_id\":260742905,\"corporation_id\":null,\"counter_currency\":null,\"coupon_amount\":0,\"currency_id\":\"BRL\",\"date_approved\":null,\"date_created\":\"2026-02-16T08:23:33.000-04:00\",\"date_last_updated\":\"2026-02-16T08:23:37.000-04:00\",\"date_of_expiration\":\"2026-02-17T08:23:33.000-04:00\",\"deduction_schema\":null,\"description\":\"Inscrição no evento: III CORRIDA SAUIM DE COLEIRA\",\"differential_pricing_id\":null,\"external_reference\":\"MOVAMAZON_1770557712_29\",\"fee_details\":[],\"financing_group\":null,\"id\":145792108627,\"installments\":1,\"integrator_id\":null,\"issuer_id\":\"12501\",\"live_mode\":true,\"marketplace_owner\":null,\"merchant_account_id\":null,\"merchant_number\":null,\"metadata\":[],\"money_release_date\":null,\"money_release_schema\":null,\"money_release_status\":\"released\",\"notification_url\":\"https:\\/\\/www.movamazon.com.br\\/api\\/mercadolivre\\/webhook.php\",\"operation_type\":\"regular_payment\",\"order\":[],\"payer\":{\"email\":null,\"entity_type\":null,\"first_name\":null,\"id\":\"3206743703\",\"identification\":{\"number\":null,\"type\":null},\"last_name\":null,\"operator_id\":null,\"phone\":{\"number\":null,\"extension\":null,\"area_code\":null},\"type\":null},\"payment_method\":{\"id\":\"pix\",\"issuer_id\":\"12501\",\"type\":\"bank_transfer\"},\"payment_method_id\":\"pix\",\"payment_type_id\":\"bank_transfer\",\"platform_id\":null,\"point_of_interaction\":{\"application_data\":{\"name\":null,\"operating_system\":null,\"version\":null},\"business_info\":{\"branch\":\"Merchant Services\",\"sub_unit\":\"sdk\",\"unit\":\"online_payments\"},\"location\":{\"source\":null,\"state_id\":null},\"transaction_data\":{\"bank_info\":{\"collector\":{\"account_alias\":null,\"account_holder_name\":\"Eudimaci Barboza de Lira\",\"account_id\":null,\"long_name\":null,\"transfer_account_id\":null},\"is_same_bank_account_owner\":null,\"origin_bank_id\":null,\"origin_wallet_id\":null,\"payer\":{\"account_id\":null,\"branch\":null,\"external_account_id\":null,\"id\":null,\"identification\":[],\"long_name\":null}},\"bank_transfer_id\":null,\"e2e_id\":null,\"financial_institution\":null,\"is_end_consumer\":null,\"merchant_category_code\":null,\"qr_code\":\"00020126580014br.gov.bcb.pix0136fb6df74b-f82a-425b-92ab-55459eab8d69520400005303986540521.005802BR5915EUDIMACIBARBOZA6009Sao Paulo62250521mpqrinter145792108627630409A9\",\"qr_code_base64\":\"iVBORw0KGgoAAAANSUhEUgAABWQAAAVkAQMAAABpQ4TyAAAABlBMVEX\\/\\/\\/8AAABVwtN+AAAKsUlEQVR42uzdQXLiyBIG4CK8YMkROApHM0fjKByhl14Q6MW4kSozJTB+3WM0Ed+\\/cTDRoE+zy8isrCYiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIi\\/262wyynthmGX60d\\/vnw659\\/tLn9\\/Z334dLavv\\/7zx85t3b78vS3tV3\\/O9x+7PPLu2E4trfbl1v6sf6jtLS0tLS0tLS0tLS0tLR\\/rD2Xz\\/1Bm9tXr7cHVO3vv+mVP7Uft1ccv9z652N82L5rE+pAS0tLS0tLS0tLS0tLu2ZtKl+7MtS844NCuTqU2vdTeZh+JNS6KZfbk3PN21\\/5g5aWlpaWlpaWlpaWlva\\/qD3NytYR8PuBqeZNndPPB17H8nVe6+5iwUxLS0tLS0tLS0tLS0v7H9fmvMcHJm340r3R2cM0dxv+DrS0tLS0tLS0tLS0tLS0P6Ut08ILlXmLD8zaz5yjLowYD9Or5mnh9\\/ilP5ttpqWlpaWlpaWlpaWlpf1J7f3NReNZ08186dB5qc+bpoW\\/9yN\\/Yc8SLS0tLS0tLS0tLS0t7b+uXcxmfta09Vp3LFd7rbtNNW8ZOV5ov4bP7c9DS0tLS0tLS0tLS0tL+5PafZ+7TRuL6uhsz7h06G1YTjhzWl89bS46xyd+3GrfR\\/O3tLS0tLS0tLS0tLS0tK\\/VbuenNw9RO\\/Sm5+cDFtXbXvum9mtdoht+5H1St3Kx56PQ0tLS0tLS0tLS0tLS0n5LGwZ9Fx+QzpqGG0R7sV1\\/5KNPCY93v\\/S1Rwvbe58OLS0tLS0tLS0tLS0t7Tq0i9e15LtejlO5OpTjoh9LBfSmFMj3at63\\/qqtS2hpaWlpaWlpaWlpaWnXqG1lRncba9421w5x6VAY9E3akPnUcKh9z739mg6w0tLS0tLS0tLS0tLS0q5XW1Nr3nqNZlpdGwrndHA1zd\\/WW1TGTmq6PSUP8dLS0tLS0tLS0tLS0tLS\\/gXtfjomGurjXWnV7uKjLw\\/L\\/F6x7+bl\\/vyV81nTZ7ZC0dLS0tLS0tLS0tLS0r5W28vVjzYt1u1l67UP9g5FPZat+7L+qMVXTmdM88hxqnmH3mmmpaWlpaWlpaWlpaWlXaM277+dby4Kn9Mx0Xrny3n2y5s7x0fzEt3+ynn9ES0tLS0tLS0tLS0tLe1KtV\\/+m3jpZ9p\\/O5SR2VSubspVLK0Uzpf5j9DS0tLS0tLS0tLS0tLS\\/kVtzsM7X9q0Z6k++KNs7W39zpd5uX9JS5v6tPC2fKalpaWlpaWlpaWlpaVdpXZfytV+6WedFg7KdFx0m7b2pmtIxz5vvX40HVzd33llWlpaWlpaWlpaWlpa2vVpe5m6kFTzttQ5nde8D8rV99v\\/guNsWji88nfufKGlpaWlpaWlpaWlpaV9gTaXqz27vgc3bS76aunQ6e7w7nU+f7tfOrD69eYiWlpaWlpaWlpaWlpa2ldpw42Yh\\/kU7J0LUFrae5seWA9gzi\\/0XO6c9pq3PdHnpaWlpaWlpaWlpaWlpaX9Wlu39Y53vvx6pkUb7nxpvUV7mxa+lubxtVw\\/OqSVv0n99J0vtLS0tLS0tLS0tLS0tD+rDSc+0w+3OB28SQ84Pjpzeoh\\/f8UNRrnP23\\/k0l\\/1mf23tLS0tLS0tLS0tLS0tK\\/Vttm\\/2ZSVtbn5udgx3cfNRSGnWDi3eP1oKwXzkzUvLS0tLS0tLS0tLS0t7Qu123SdZpq7PUwd03CbSpi\\/HbXzZui2\\/Ehov8733tZ8PJ6\\/paWlpaWlpaWlpaWlpaV9XjvMFu1u5jeHDvFB4dLPURvOnPbKfDev0Hvz+K386MNlTbS0tLS0tLS0tLS0tLSr0C5sLhpvDD3FaeFfSyPGl97XvT\\/ou0mFdNpglA6ubssIMi0tLS0tLS0tLS0tLe0qtW32bzaLN4WmBz3Yf5vWHx1KrTssae9X37S0tLS0tLS0tLS0tLSr0m7LyGxKWDo0js7uvp6\\/7dr6Yylv8y+fpw28jZaWlpaWlpaWlpaWlpb272jHvm65riUv2u17lt5uFflbOiZazpxeU7lf+7u9w\\/yWRo5paWlpaWlpaWlpaWlp16xd2B90ioO+aWvvcs2bRo5TQsF8jD\\/S4qte+qrf\\/eNuNC0tLS0tLS0tLS0tLe2rtdulQd+8dKhqu\\/KtL88936l5D\\/Gs6dA3Fh1jB3Xoo8fjl2hpaWlpaWlpaWlpaWnXqA3q0\\/TAa1L2W1QuixehpFdO7dh0FUvee3uMp11r4UxLS0tLS0tLS0tLS0u7Xu0+nuIM06+HMkL7WZ6O5es5rq5tvfl5KEdAT6WTmtQLpzif6fPS0tLS0tLS0tLS0tLS0j6vTQ86tbxYd8x7bNnO+7oLI8e7sv4oTRHnV66hpaWlpaWlpaWlpaWlXaO27r+t08K70udtLW8uOrfl9UeLPxLOnL7Pzp6GVz896EbT0tLS0tLS0tLS0tLSvlA7zux+RP3Y9Aybi0LntB8TzcdFe+H80RauH90s7r9dvDmUlpaWlpaWlpaWlpaWdr3asHU2PSgdF60jtOlVF5qeh9h2Tdq8ySi96pPbemlpaWlpaWlpaWlpaWlpv3mLyvj5NLVkxxtDr0X71gd9x1fcD\\/kG0bHIPpSR417m11dd+DItLS0tLS0tLS0tLS3tyrRhc1Ea9A2bi+p1LYt3vgy9z9sHfnfza0jfp+tH88hxr3m3X56QpaWlpaWlpaWlpaWlpX21tsUHjZ3TIU0Lv8\\/uesl3viRtvfNluHNzaDprmtYh0dLS0tLS0tLS0tLS0q5PGzqnh0l9TU3POjJ7bOG4aFK32PzcpL+Lm4v284OrX+5ZoqWlpaWlpaWlpaWlpaV9Ulv7vA+vbbnML\\/1c7PO20iwOB1XnW3ov84OrtLS0tLS0tLS0tLS0tOvVttJdPUxnTOvC3bdhOWnVb76GdH5zaFDu4+jxF1PCtLS0tLS0tLS0tLS0tCvQhkHfe5Xn+\\/RfLkn98EdT5zS0X2u1PZQ9uI\\/VtLS0tLS0tLS0tLS0tC\\/X1gtQ2tQxrXO3tdl5SQ\\/sr1ivHw3zt\\/XMad1ctNh+paWlpaWlpaWlpaWlpV2JdlF\\/LaOzuYM6NjvT0qH0yunzvTZsm1fZ9TwpLS0tLS0tLS0tLS0tLe1f1I4\\/HM6c9qVDuVJvsztgxnVHH33t0SFeOxrOnL5P5f1lPmr8vdDS0tLS0tLS0tLS0tL+mHY7P0F6Krp6XDRpwwaj0qINF8cMpXCu2v037nyhpaWlpaWlpaWlpaWlfa32XD73m0Ovtx8eHj5o3vTc9oL5tKSsB1mDhJaWlpaWlpaWlpaWlnb92jQ6eyod1GOcux3m87eLOU36a7mKZTzIunB7St\\/AS0tLS0tLS0tLS0tLS0v772g3i4O+n33dX21ajVSbxW06RpqmhWuzuPVrSEPzuO9Z+naFTktLS0tLS0tLS0tLS\\/tabUtl6nH2paptfdFumy6QGfu7YfR4Yf1R\\/fL\\/V6HT0tLS0tLS0tLS0tLS\\/qT2zrTwkDqnx1jzpkHf9OV7CXe+jBfJJO34+dGP0NLS0tLS0tLS0tLS0r5ce290NpWnSTmkzUX9lX8\\/aP4jrdfAn3O39SqW89L\\/N1paWlpaWlpaWlpaWlraP9OKiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIrDr\\/CwAA\\/\\/+JMgaY6+rZygAAAABJRU5ErkJggg==\",\"ticket_url\":\"https:\\/\\/www.mercadopago.com.br\\/payments\\/145792108627\\/ticket?caller_id=3206743703&hash=d504f683-92ca-4539-9923-5e145354555a\",\"transaction_id\":null},\"type\":\"OPENPLATFORM\"},\"pos_id\":null,\"processing_mode\":\"aggregator\",\"refunds\":[],\"release_info\":null,\"shipping_amount\":0,\"sponsor_id\":null,\"statement_descriptor\":null,\"status\":\"pending\",\"status_detail\":\"pending_waiting_transfer\",\"store_id\":null,\"tags\":null,\"taxes_amount\":0,\"transaction_amount\":21,\"transaction_amount_refunded\":0,\"transaction_details\":{\"acquirer_reference\":null,\"bank_transfer_id\":null,\"external_resource_url\":null,\"financial_institution\":null,\"installment_amount\":0,\"net_received_amount\":0,\"overpaid_amount\":0,\"payable_deferral_period\":null,\"payment_method_reference_id\":null,\"total_paid_amount\":21,\"transaction_id\":null}}', 21.00, 'pix', 1, NULL, 29, '2026-02-16 09:23:39'),
(13, 11, 'sync_142946577853', '142946577853', 'https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=sync_142946577853', 'cancelado', '2026-02-18 01:18:22', '2026-02-23 21:19:44', '{\"accounts_info\":null,\"acquirer_reconciliation\":[],\"additional_info\":{\"tracking_id\":\"platform:v1-whitelabel,so:ALL,type:N\\/A,security:none\"},\"authorization_code\":null,\"barcode\":{\"content\":\"42297134100000109007115000064897310888175222\"},\"binary_mode\":false,\"brand_id\":null,\"build_version\":\"3.140.0-rc-1\",\"call_for_authorize_id\":null,\"captured\":true,\"card\":[],\"charges_details\":[{\"accounts\":{\"from\":\"collector\",\"to\":\"mp\"},\"amounts\":{\"original\":3.4900000000000002131628207280300557613372802734375,\"refunded\":0},\"client_id\":0,\"date_created\":\"2026-01-26T18:01:18.000-04:00\",\"external_charge_id\":\"01KFY56N4GF31DDP4MJTNM8V8D\",\"id\":\"142946577853-001\",\"last_updated\":\"2026-01-26T18:01:18.000-04:00\",\"metadata\":{\"reason\":\"\",\"source\":\"proc-svc-charges\",\"source_detail\":\"processing_fee_charge\"},\"name\":\"mercadopago_fee\",\"refund_charges\":[],\"reserve_id\":null,\"type\":\"fee\",\"update_charges\":[]}],\"charges_execution_info\":{\"internal_execution\":{\"date\":\"2026-01-26T18:01:18.234-04:00\",\"execution_id\":\"01KFY56N3E4WEY6PZ4EGGHZFNY\"}},\"collector_id\":260742905,\"corporation_id\":null,\"counter_currency\":null,\"coupon_amount\":0,\"currency_id\":\"BRL\",\"date_approved\":\"2026-01-26T18:51:10.000-04:00\",\"date_created\":\"2026-01-26T18:01:18.000-04:00\",\"date_last_updated\":\"2026-01-29T17:55:22.000-04:00\",\"date_of_expiration\":\"2026-01-29T22:59:59.000-04:00\",\"deduction_schema\":null,\"description\":\"Inscrição no evento: III CORRIDA SAUIM DE COLEIRA\",\"differential_pricing_id\":null,\"external_reference\":\"143208109084\",\"fee_details\":[{\"amount\":3.4900000000000002131628207280300557613372802734375,\"fee_payer\":\"collector\",\"type\":\"mercadopago_fee\"}],\"financing_group\":null,\"id\":142946577853,\"installments\":1,\"integrator_id\":null,\"issuer_id\":\"2006\",\"live_mode\":true,\"marketplace_owner\":null,\"merchant_account_id\":null,\"merchant_number\":null,\"metadata\":[],\"money_release_date\":\"2026-01-29T17:55:21.000-04:00\",\"money_release_schema\":null,\"money_release_status\":\"released\",\"notification_url\":\"https:\\/\\/www.movamazon.com.br\\/api\\/mercadolivre\\/webhook.php\",\"operation_type\":\"regular_payment\",\"order\":[],\"payer\":{\"email\":\"nandesinfo@gmail.com\",\"entity_type\":null,\"first_name\":null,\"id\":\"3154860678\",\"identification\":{\"number\":\"00571996710\",\"type\":\"CPF\"},\"last_name\":null,\"operator_id\":null,\"phone\":{\"number\":null,\"extension\":null,\"area_code\":null},\"type\":null},\"payment_method\":{\"data\":{\"paid_date\":\"2026-01-26\"},\"forward_data\":{\"agreement_number\":\"\",\"ticket_number\":\"\"},\"id\":\"bolbradesco\",\"issuer_id\":\"2006\",\"type\":\"ticket\"},\"payment_method_id\":\"bolbradesco\",\"payment_type_id\":\"ticket\",\"platform_id\":null,\"point_of_interaction\":{\"business_info\":{\"branch\":\"Merchant Services\",\"sub_unit\":\"default\",\"unit\":\"online_payments\"},\"linked_to\":\"regular\",\"transaction_data\":[],\"type\":\"UNSPECIFIED\"},\"pos_id\":null,\"processing_mode\":\"aggregator\",\"refunds\":[],\"release_info\":null,\"shipping_amount\":0,\"sponsor_id\":null,\"statement_descriptor\":null,\"status\":\"approved\",\"status_detail\":\"accredited\",\"store_id\":null,\"tags\":null,\"taxes_amount\":0,\"transaction_amount\":109,\"transaction_amount_refunded\":0,\"transaction_details\":{\"acquirer_reference\":\"\",\"barcode\":{\"content\":\"42297134100000109007115000064897310888175222\"},\"digitable_line\":\"42297115040006489731708881752227713410000010900\",\"external_resource_url\":\"https:\\/\\/www.mercadopago.com.br\\/payments\\/142946577853\\/ticket?caller_id=3154860678&payment_method_id=bolbradesco&payment_id=142946577853&payment_method_reference_id=10543749857&hash=9fb03836-00ff-4709-a561-82357f55518b\",\"financial_institution\":null,\"installment_amount\":0,\"net_received_amount\":105.5100000000000051159076974727213382720947265625,\"overpaid_amount\":0,\"payable_deferral_period\":null,\"payment_method_reference_id\":\"10543749857\",\"total_paid_amount\":109,\"verification_code\":\"088817522\"}}', 109.00, 'bolbradesco', 1, NULL, 18, '2026-02-17 22:18:22'),
(14, 11, 'sync_142946577853', '145999651247', 'https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=sync_142946577853', 'pendente', '2026-02-18 01:19:18', '2026-02-18 01:19:18', '{\"id\":145999651247,\"acquirer_reconciliation\":[],\"sponsor_id\":null,\"operation_type\":\"regular_payment\",\"order\":[],\"brand_id\":null,\"build_version\":\"3.143.0-rc-4\",\"binary_mode\":false,\"external_reference\":\"142946577853\",\"financing_group\":null,\"status\":\"pending\",\"status_detail\":\"pending_waiting_transfer\",\"store_id\":null,\"taxes_amount\":0,\"date_created\":\"2026-02-17T21:18:16.000-04:00\",\"live_mode\":true,\"date_last_updated\":\"2026-02-17T21:18:20.000-04:00\",\"date_of_expiration\":\"2026-02-18T21:18:16.000-04:00\",\"deduction_schema\":null,\"date_approved\":null,\"money_release_date\":null,\"money_release_schema\":null,\"money_release_status\":\"released\",\"currency_id\":\"BRL\",\"transaction_amount\":21,\"transaction_amount_refunded\":0,\"payer\":{\"type\":null,\"id\":\"3154860678\",\"email\":null,\"identification\":{\"type\":null,\"number\":null},\"first_name\":null,\"last_name\":null,\"entity_type\":null,\"phone\":{\"area_code\":null,\"number\":null,\"extension\":null},\"operator_id\":null},\"collector_id\":260742905,\"counter_currency\":null,\"payment_method_id\":\"pix\",\"payment_method\":{\"id\":\"pix\",\"type\":\"bank_transfer\",\"issuer_id\":\"12501\"},\"payment_type_id\":\"bank_transfer\",\"pos_id\":null,\"transaction_details\":{\"financial_institution\":null,\"net_received_amount\":0,\"total_paid_amount\":21,\"installment_amount\":0,\"overpaid_amount\":0,\"external_resource_url\":null,\"payment_method_reference_id\":null,\"acquirer_reference\":null,\"payable_deferral_period\":null,\"bank_transfer_id\":null,\"transaction_id\":null},\"fee_details\":[],\"differential_pricing_id\":null,\"authorization_code\":null,\"captured\":true,\"card\":[],\"call_for_authorize_id\":null,\"statement_descriptor\":null,\"shipping_amount\":0,\"additional_info\":{\"tracking_id\":\"platform:8|8.3.30,so:so;,type:SDK3.8.0,security:none\"},\"coupon_amount\":0,\"installments\":1,\"description\":\"Inscrição no evento: III CORRIDA SAUIM DE COLEIRA\",\"notification_url\":\"https:\\/\\/www.movamazon.com.br\\/api\\/mercadolivre\\/webhook.php\",\"issuer_id\":\"12501\",\"processing_mode\":\"aggregator\",\"merchant_account_id\":null,\"merchant_number\":null,\"metadata\":[],\"callback_url\":null,\"release_info\":null,\"marketplace_owner\":null,\"integrator_id\":null,\"corporation_id\":null,\"platform_id\":null,\"point_of_interaction\":{\"type\":\"OPENPLATFORM\",\"application_data\":{\"name\":null,\"version\":null},\"transaction_data\":{\"qr_code\":\"00020126580014br.gov.bcb.pix0136fb6df74b-f82a-425b-92ab-55459eab8d69520400005303986540521.005802BR5915EUDIMACIBARBOZA6009Sao Paulo62250521mpqrinter145999651247630429F4\",\"qr_code_base64\":\"iVBORw0KGgoAAAANSUhEUgAABWQAAAVkAQMAAABpQ4TyAAAABlBMVEX\\/\\/\\/8AAABVwtN+AAAKq0lEQVR42uzdQXIayRIG4FawYMkRdBSOJh2No3AEliwIesIyRWVmFwiHPQZHfP+GJwd0f\\/V2OZWVNYmIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIjI\\/5v1vMjux79vfvyv8+VznufDNH3Mp6+ffFz+aZpW\\/Uf7y8N218+3y8PeLt84px9\\/Xn789Y\\/vSwQtLS0tLS0tLS0tLS0t7R\\/Q7svfTfv1osOPP94u2vny9+ryo5+f83zsv9\\/+eNG666Yb2vnH0t+7NqG2tLS0tLS0tLS0tLS0tK+s7ZXmoFz9vKp\\/ruLzqvxZA\\/dC9RgL6XNfcnvI1+dUCuemXfeH0NLS0tLS0tLS0tLS0v5L2mvlWV5wumhD3kc7p+3Hhx+f58uP3nrBPKx5aWlpaWlpaWlpaWlpaf9R7Vvvw60ts6EPN\\/3oVuvs9tq8Gz5paWlpaWlpaWlpaWlpaf+atnQLv5XieupnT8OLWmXeK\\/RQ1qeW47n8eIotx7\\/d20xLS0tLS0tLS0tLS0v7N7XLyUVvwy3avs8buoVbudrOnm5j4fwLD\\/mNOUu0tLS0tLS0tLS0tLS0f017N2\\/LbuG2k5pq3XWqeb\\/Zfh2cOf0zoaWlpaWlpaWlpaWlpf0b2rZvWcvVmo94m8p7bKGt\\/bfr5ZUsnyP1YPs1DdOlpaWlpaWlpaWlpaWlfT1tyGXz89zv4mwJfbjpGs1p2ULbdlD7FSyDdO0pdQLfP8VJS0tLS0tLS0tLS0tLS\\/u4thbT4ebQuZw57ZOLWiW+Sne9lIe0buG5XDuaK\\/VboaWlpaWlpaWlpaWlpX1Fbas8U6X51svVOrI2ZVX2dVute+yF8i62HA\\/GIE1xv\\/fb+be0tLS0tLS0tLS0tLS0r6WtQ4e+ytNz2jFN827Tjukca+Bc89YlpqW\\/xyVO3\\/Y209LS0tLS0tLS0tLS0j5bG4YMTdehQ4NydepnTpc7p6Fc3cbm3bbkqdzJuS9LfERNS0tLS0tLS0tLS0tLS\\/ugdp1e2LuFg\\/ozHhs9RO20\\/KwHV3fXzeJzqdBbmb8qgkduDqWlpaWlpaWlpaWlpaV9jnag3o7OgB7ioN3V8kXv5cfpzpdwc+gUC+emr5vGtLS0tLS0tLS0tLS0tC+qXfdjo7uoHoyu\\/YwNv\\/uy7bqPD0vbrzWrpE4Xxzw4\\/5aWlpaWlpaWlpaWlpb2Wdr2m58PLkOHwpChw7JpNy15X5a+XHLovx0sPR1kpaWlpaWlpaWlpaWlpaX9A9p1\\/26v0M9J2wfs1q3Z3Ph747hobjXu+7yhWzhp16OynpaWlpaWlpaWlpaWlvYltFPfZZ3j0KFwXUu6QXSawl0vq1TrpoOrcxmH9BEPrG5uTOt9cM4SLS0tLS0tLS0tLS0t7XO1y8lFmzLBKN0gOvel1aXWWnc3qnnTBTKn5XbsIzeH0tLS0tLS0tLS0tLS0j5N25Tp0s8pDhu6tYO6VOb5t1W5bOat44\\/mx3ZOaWlpaWlpaWlpaWlpaZ+gzf23y+9ulpOL0jWa+1I4b+MpzuGFnm1y0a3m3WEBTUtLS0tLS0tLS0tLS0v7O9rULTxdrm9Jw4dOSfvdC5cTiw79rpePuL87jVqOaWlpaWlpaWlpaWlpaV9PG7Zmp1Lzpq3aOTb4DrRzafRNSx52C0\\/9jfUCmfm7ab20tLS0tLS0tLS0tLS0z9Hmm0NLo+\\/58qW8+Tl8wf7Gpuf2utS34ZLTemv1TUtLS0tLS0tLS0tLS\\/ti2sGD66bnrYtQ+oPXpfZdX\\/pu6wWem9jEe0o1bvnxREtLS0tLS0tLS0tLS0v7u9r1qC7++aLaLZzOnNY7X\\/Z9yV07d\\/XH9WGr\\/rD3OQxtWqel09LS0tLS0tLS0tLS0r6itt60Ul\\/UtmiHA3ZPZcnH0XUtuWCuZ0\\/nS+2bHnIvtLS0tLS0tLS0tLS0tE\\/UDl+Uy9VpynNw59HQoX3cfj3GgrkVyucy\\/3bq2l+u0GlpaWlpaWlpaWlpaWmfo80XoKTUTc+PMv82lavDDK4f\\/frRZr6ZXn3T0tLS0tLS0tLS0tLS0v6eNl3ymecsde25HxNtRXaY1rtsOV6XA6uDJfcd5lW6hvTbfV5aWlpaWlpaWlpaWlraJ2qDejcNLv3c9M\\/Pst\\/bt2qPfQjR7npxTD24mg+s1jtf7o78paWlpaWlpaWlpaWlpX0p7XBy0XBi0aEcVG3bru0zZbM8uJpq33BhTL+79Pual5aWlpaWlpaWlpaWlvZZ2lbjHpeTi\\/qwoXO5ACWkXv45R\\/XmmybeOS45PGxHS0tLS0tLS0tLS0tL+5raUPO277TNzm3U3um\\/LX23c3rItBiiu0qF83ydg3tMR0JpaWlpaWlpaWlpaWlpaf+U9hjVgwp9msKln99pa5mfJhnlC2NSWf8e\\/1sBLS0tLS0tLS0tLS0t7atrU7fw9uY+76mXq7VLeBvn4Q4K57TkoE4SWlpaWlpaWlpaWlpa2tfVhqFDy9G1bdMzvCDVvPtR4XyM26\\/nPu4oTTA6dX0dnnuvW5iWlpaWlpaWlpaWlpb2idp1f+H2xvChufTf9k3PUzq4Wg6w1iVn5cf1Ik9aWlpaWlpaWlpaWlpa2v9Nm76zLne9THGLdkpnTutx0dJyHP4vOMQ3rtK8pTqc6ZGpULS0tLS0tLS0tLS0tLRP1O6XvbqX1C3aVR+8O1\\/K1bbUfWw5Hi55vM\\/bpvSmHeebk4toaWlpaWlpaWlpaWlpn6u9qwxnTTf9QcNu4dRyHHZM09+H0iU8\\/+rOKS0tLS0tLS0tLS0tLe1zta3mTXeX1MlFc6p1P6cpTS5q2r70MER3Vw6uzqP+2zk+hJaWlpaWlpaWlpaWlpb2z2jr7mr7bqrI34bXtfS\\/j+khw4tjpnIHzGdcWuoWfnBaLy0tLS0tLS0tLS0tLe0TtKHW3V6HDoUt203UDhp9W1LLcdMeyssGU3vT9aN7WlpaWlpaWlpaWlpa2pfX7uN1LXXoUChP69Ch1Oi7L+Vrm3+7vdFyPFzifnGHKS0tLS0tLS0tLS0tLe2raecyL2hbjo9+lFtUPmPLbNh+XR5gPdw755r7btOSd7S0tLS0tLS0tLS0tLSvqR3od2Wzs5erp3KLyt2c7+6UDh+yPE9KS0tLS0tLS0tLS0tLS\\/vHtF8ZTC4K+73L61rCxTHbcnB1tzhrWrWntONMS0tLS0tLS0tLS0tL+8ra9bJYTd3CSbtKE4yGjb7buFlcJxalVuM6RJeWlpaWlpaWlpaWlpb2n9Du7+2chho4vahtgg5r3mm6c3NoUL\\/HYbrrhycX0dLS0tLS0tLS0tLS0j5X28+cruPo2nqN5mNJz99e+3BzP+5wiG449UpLS0tLS0tLS0tLS0tL+79op+ULbjX6ti3arxxLhZ6WHFqNN8ubQ9u+7yP7vLS0tLS0tLS0tLS0tLSvph00\\/HZte\\/Cq\\/2h\\/o\\/V4XnQNhwtj0t+te\\/iRG2poaWlpaWlpaWlpaWlpn6kt3cJhctHc59\\/WM6f1RTdajafUPfx5vX403Bz6KzfU0NLS0tLS0tLS0tLS0j5Xe3dy0a1Nz3BctJWppVxtZ07f0pLn0ZnT91\\/YOaWlpaWlpaWlpaWlpaWlfVArIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIi8tL5LwAA\\/\\/+3wSIVhObN5QAAAABJRU5ErkJggg==\",\"transaction_id\":null,\"bank_transfer_id\":null,\"financial_institution\":null,\"bank_info\":{\"payer\":{\"id\":null,\"account_id\":null,\"long_name\":null,\"external_account_id\":null,\"identification\":[]},\"collector\":{\"account_id\":null,\"long_name\":null,\"account_holder_name\":\"Eudimaci Barboza de Lira\",\"transfer_account_id\":null},\"is_same_bank_account_owner\":null,\"origin_bank_id\":null,\"origin_wallet_id\":null},\"ticket_url\":\"https:\\/\\/www.mercadopago.com.br\\/payments\\/145999651247\\/ticket?caller_id=3154860678&hash=5bb177f9-510c-4f21-bddd-6dcd0619b62b\",\"e2e_id\":null}},\"accounts_info\":null,\"tags\":null,\"refunds\":[]}', 21.00, 'pix', 1, 0.00, 18, '2026-02-17 22:19:18'),
(15, 11, 'sync_142946577853', '146697234630', 'https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=sync_142946577853', 'pendente', '2026-02-18 01:22:33', '2026-02-18 01:22:33', '{\"id\":146697234630,\"acquirer_reconciliation\":[],\"sponsor_id\":null,\"operation_type\":\"regular_payment\",\"order\":[],\"brand_id\":null,\"build_version\":\"3.143.0-rc-4\",\"binary_mode\":false,\"external_reference\":\"142946577853\",\"financing_group\":null,\"status\":\"pending\",\"status_detail\":\"pending_waiting_transfer\",\"store_id\":null,\"taxes_amount\":0,\"date_created\":\"2026-02-17T21:21:31.000-04:00\",\"live_mode\":true,\"date_last_updated\":\"2026-02-17T21:21:34.000-04:00\",\"date_of_expiration\":\"2026-02-18T21:21:31.000-04:00\",\"deduction_schema\":null,\"date_approved\":null,\"money_release_date\":null,\"money_release_schema\":null,\"money_release_status\":\"released\",\"currency_id\":\"BRL\",\"transaction_amount\":20,\"transaction_amount_refunded\":0,\"payer\":{\"type\":null,\"id\":\"3154860678\",\"email\":null,\"identification\":{\"type\":null,\"number\":null},\"first_name\":null,\"last_name\":null,\"entity_type\":null,\"phone\":{\"area_code\":null,\"number\":null,\"extension\":null},\"operator_id\":null},\"collector_id\":260742905,\"counter_currency\":null,\"payment_method_id\":\"pix\",\"payment_method\":{\"id\":\"pix\",\"type\":\"bank_transfer\",\"issuer_id\":\"12501\"},\"payment_type_id\":\"bank_transfer\",\"pos_id\":null,\"transaction_details\":{\"financial_institution\":null,\"net_received_amount\":0,\"total_paid_amount\":20,\"installment_amount\":0,\"overpaid_amount\":0,\"external_resource_url\":null,\"payment_method_reference_id\":null,\"acquirer_reference\":null,\"payable_deferral_period\":null,\"bank_transfer_id\":null,\"transaction_id\":null},\"fee_details\":[],\"differential_pricing_id\":null,\"authorization_code\":null,\"captured\":true,\"card\":[],\"call_for_authorize_id\":null,\"statement_descriptor\":null,\"shipping_amount\":0,\"additional_info\":{\"tracking_id\":\"platform:8|8.3.30,so:so;,type:SDK3.8.0,security:none\"},\"coupon_amount\":0,\"installments\":1,\"description\":\"Inscrição no evento: III CORRIDA SAUIM DE COLEIRA\",\"notification_url\":\"https:\\/\\/www.movamazon.com.br\\/api\\/mercadolivre\\/webhook.php\",\"issuer_id\":\"12501\",\"processing_mode\":\"aggregator\",\"merchant_account_id\":null,\"merchant_number\":null,\"metadata\":[],\"callback_url\":null,\"release_info\":null,\"marketplace_owner\":null,\"integrator_id\":null,\"corporation_id\":null,\"platform_id\":null,\"point_of_interaction\":{\"type\":\"OPENPLATFORM\",\"application_data\":{\"name\":null,\"version\":null},\"transaction_data\":{\"qr_code\":\"00020126580014br.gov.bcb.pix0136fb6df74b-f82a-425b-92ab-55459eab8d69520400005303986540520.005802BR5915EUDIMACIBARBOZA6009Sao Paulo62250521mpqrinter14669723463063041411\",\"qr_code_base64\":\"iVBORw0KGgoAAAANSUhEUgAABWQAAAVkAQMAAABpQ4TyAAAABlBMVEX\\/\\/\\/8AAABVwtN+AAAKoklEQVR42uzdQXIaSxIG4CJYsOQIHIWjwdE4CkdgyUKhnngMTVVmVcv42c9qR3z\\/RqEZ0f3hXb7MyioiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIi8t9mN3W5lM003f75P\\/PP\\/+eU\\/v7xkGspzw+\\/fpayrz8fuf3z4Y\\/H7+eyfX64lHLoEbS0tLS0tLS0tLS0tLS0v0F7Tb\\/3L\\/p8vmDO9vnIj8eLwld+aO\\/Przg9P1zq7+f2ZYeqDagjLS0tLS0tLS0tLS0t7Zq1oXyda95j96JtLVsPVftI\\/5Cm1g35CF+5auevfKelpaWlpaWlpaWlpaX9O7VTKlu34YW55p3L1ecLP+vvudbdtwUzLS0tLS0tLS0tLS0t7V+ubTqnzbztuX1RSR9aGp09vuZum58TLS0tLS0tLS0tLS0tLe2f0qZp4Tzo26jnPm+o2Kf6kFDWN33ec5oWPrUf+rXZZlpaWlpaWlpaWlpaWto\\/qV3aXHR8nTXd9EuHrqnPe32eNT2ORo7feMgv71mipaWlpaWlpaWlpaWl\\/QPaYZq9t1N6Ud5\\/e02Fc9bWDUbT8uaiXwstLS0tLS0tLS0tLS3tn9Qe6txtfeqtH50taR53Ofda605tzRvU2zD5+\\/jQ4Ufzt7S0tLS0tLS0tLS0tLTfri2vi0+aEdqlzUVN7ZtPcabbVJoLPJvOaV5\\/NP97\\/UzHlJaWlpaWlpaWlpaWlpb2Te1hVGQ3L5ras6b72t+tL9yFFy1v690n5b8JLS0tLS0tLS0tLS0t7Tdqd6lsDTVvs7lon24OLe1x0Xu6+2WXat4wgrzt1x9da9X9LJhpaWlpaWlpaWlpaWlpV6kNZer8ws+FP9umQd9GO6h9n1\\/9M2lLKJzzEl1aWlpaWlpaWlpaWlra9WuDurSrawdnTMNx0WsqnC+vF+YzppvQQT2XvLlolx5GS0tLS0tLS0tLS0tLS\\/vL2sPrmGgJL1hauJuvaakPuT+bxPd+1HjOaWHPUv\\/fCmhpaWlpaWlpaWlpaWnXqp3L1dqSzWXrLbVkB8uH5lHj+rB9fci5bRbPK4CvP5hbpqWlpaWlpaWlpaWlpV2ZNp81DbpNv8momRrOg77DzullNHJ8a3u1H\\/Xf6\\/qjmpeWlpaWlpaWlpaWlpb227XXbv425pT23567F8WaN7Rdw\\/7bUrpbVMJXpaWlpaWlpaWlpaWlpaX9jdpGHaaF5wq9qdTPrz1LSxfFhOtHP9O08KYq9\\/VhdVp4l36npaWlpaWlpaWlpaWlXaW2OS4aXnQZnTWdy9bBtt6Fg6vjwjmPGs8XxwyrblpaWlpaWlpaWlpaWtr1aGuZOsitvuiR\\/agJ+kZRfXr+E5y7aeGm5m1GjmlpaWlpaWlpaWlpaWnXqN31Tc9ZNS8fCpuLbu2tKh9hiDd1TnPH9LOfvz20B1ajiJaWlpaWlpaWlpaWlnaN2sHq2ikdxKxl62Df7SHN3x5TxzRc6PlF53T5342WlpaWlpaWlpaWlpaW9t9pm5tWLq8iOxbVtUVbljcXlVL6h3y5uWgKK3+zmpaWlpaWlpaWlpaWlnaN2njmNCiPL2U8e3puz5zmZvFxYYPRWw95Z\\/8tLS0tLS0tLS0tLS0t7fdqS\\/c3m9o5DYO++St+1A8PNhjVkePmoedRz3bQQaWlpaWlpaWlpaWlpaVdo7bZF5Q3FoVytTlrem61g9tUnh3UTX1IqZ3UU7f3dpCvNhfR0tLS0tLS0tLS0tLS0r6v7RfubsLNoeG4aB74vT4r8uurr9sU1\\/v2zpfP8L+f231LeW75h1uhaGlpaWlpaWlpaWlpab9HO9hcdHn1eUuoeZf7vFMY9E1l6yYU0vmr14Oru\\/rVaWlpaWlpaWlpaWlpaderLd3fxJq3vMrW5u6XKe2\\/7Wve3DmNF8gE7XL1TUtLS0tLS0tLS0tLS7sqbexX9rnV\\/bfhRUG5W5i\\/DckHV7ehxg2Fc\\/l6\\/paWlpaWlpaWlpaWlpaW9h1tU6FfXq3afEx0cPlnmBq+pzJ\\/1+qWRo7nDvM2jBzT0tLS0tLS0tLS0tLSrlx7WNgbdHz1dePA77C\\/exi1ZvdPdbjzJX\\/Vj7rq9\\/B1N5qWlpaWlpaWlpaWlpb2u7XNnqBw6ed+dGz0oy4durbNzy9q3mN71nSqG4vObQe1GT1OI8e0tLS0tLS0tLS0tLS0q9I26svrhc2Lpv4WlVzr9s3OXS2Q61Usce\\/tue3Z5sKZlpaWlpaWlpaWlpaWdr3aQ3uK8943P8P+2337oo\\/+w8f21OY+Xas5UOcrWd7p89LS0tLS0tLS0tLS0tLSvq9N17XMi3an4bbe0zSl61kGa48uaVp4Gm3tvdavnENLS0tLS0tLS0tLS0u7Ru1gWvj46vN+hpo3bCy6tj93QdmPHA\\/OnJ4WlbtUKNPS0tLS0tLS0tLS0tKuR5uXDu3qCy5p\\/+1DdyvdBSh94fzQ5nbrpt9\\/u3RzKC0tLS0tLS0tLS0tLe3Ktbv+FpVc84YR2uYrDpueuQ1bRoVz\\/qpvbuulpaWlpaWlpaWlpaWlpX1TG24OndoB3ykt1o3aubieXxS29l66Pm8JynMq8\\/sP09LS0tLS0tLS0tLS0q5PO5wWbhbtlvK68+XcvmheOhSmhe\\/PRbu7dP3olPq8SxfHNGuQaGlpaWlpaWlpaWlpadeoHTRB64sabdP0PKUp4ccLrq+265QGfzehrRr23+b1R3kEmZaWlpaWlpaWlpaWlnZl2l24uyTN3zbJN4fOS4gOrTp85U34ubS5KM\\/h\\/nDPEi0tLS0tLS0tLS0tLS3tT2p37bTw4NqW0rdow82hS5d+1oOrpT6kmRquI8fTG3uWaGlpaWlpaWlpaWlpab9XW8LfDvu75fWicc1bj4ve08hxOMD62SvnO1+uaW650NLS0tLS0tLS0tLS0q5T2wz6LlWep9Ggb1Ou9l95lzqnc9v11s8pT6PR4x9MC9PS0tLS0tLS0tLS0tJ+o3ZKnymvjulUL0CZemUuW1NB\\/FmvH23mb+v6o1Jr4GtaonukpaWlpaWlpaWlpaWlXad2oJ\\/L1GPb9Nw\\/lfvRKc5dgubOaW7DNvO44XPX19AuLS0tLS0tLS0tLS0tLe1v15ZakV9SpV77vfHMaf+iXf1QHT2OZ05Prw9\\/pP9WcC8\\/GVpaWlpaWlpaWlpaWto\\/pt11B0m7a1py6qWfJfV5m1q3Fs5T7fPekvqQCud37nyhpaWlpaWlpaWlpaWl\\/V7t9avO6ePBg7J1vgBlfkGueeeC+TJShjVIH+n0Ky0tLS0tLS0tLS0tLe3atWnpUOyg1vnbPCq7HdbE4SH9EO\\/chh3cnlI38NLS0tLS0tLS0tLS0tLS\\/jfapQp9+yy2m2nhpTK\\/Tgvns6elHmAt7Ve+99t7aWlpaWlpaWlpaWlpaf8G7dyijUuGSmnufBnq8sUxt7ZwnsLmotAcPqSzpkdaWlpaWlpaWlpaWlraNWv7aeGpva4lq7f9oG89sNqcPQ1TwlO4QfTUaZuLYy6l0NLS0tLS0tLS0tLS0q5U228uKqnJGY+HnlJzc2HuNms\\/61feJ921rb5\\/ev8tLS0tLS0tLS0tLS0tLa2IiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIjI35f\\/BQAA\\/\\/94H\\/KuNOSN9wAAAABJRU5ErkJggg==\",\"transaction_id\":null,\"bank_transfer_id\":null,\"financial_institution\":null,\"bank_info\":{\"payer\":{\"id\":null,\"account_id\":null,\"long_name\":null,\"external_account_id\":null,\"identification\":[]},\"collector\":{\"account_id\":null,\"long_name\":null,\"account_holder_name\":\"Eudimaci Barboza de Lira\",\"transfer_account_id\":null},\"is_same_bank_account_owner\":null,\"origin_bank_id\":null,\"origin_wallet_id\":null},\"ticket_url\":\"https:\\/\\/www.mercadopago.com.br\\/payments\\/146697234630\\/ticket?caller_id=3154860678&hash=ab884b2f-7c0b-4365-b157-be4b386a1907\",\"e2e_id\":null}},\"accounts_info\":null,\"tags\":null,\"refunds\":[]}', 20.00, 'pix', 1, 0.00, 18, '2026-02-17 22:22:33'),
(16, 17, 'sync_145198696440', '146699740112', 'https://www.mercadopago.com.br/payments/145198696440/ticket?caller_id=1685886032&hash=50cbf39a-b114-43be-acc1-3d6d2717eed4', 'pendente', '2026-02-18 01:39:33', '2026-02-18 01:39:33', '{\"id\":146699740112,\"acquirer_reconciliation\":[],\"sponsor_id\":null,\"operation_type\":\"regular_payment\",\"order\":[],\"brand_id\":null,\"build_version\":\"3.143.0-rc-4\",\"binary_mode\":false,\"external_reference\":\"144511541833\",\"financing_group\":null,\"status\":\"pending\",\"status_detail\":\"pending_waiting_transfer\",\"store_id\":null,\"taxes_amount\":0,\"date_created\":\"2026-02-17T21:38:31.000-04:00\",\"live_mode\":true,\"date_last_updated\":\"2026-02-17T21:38:36.000-04:00\",\"date_of_expiration\":\"2026-02-18T21:38:31.000-04:00\",\"deduction_schema\":null,\"date_approved\":null,\"money_release_date\":null,\"money_release_schema\":null,\"money_release_status\":\"released\",\"currency_id\":\"BRL\",\"transaction_amount\":52.5,\"transaction_amount_refunded\":0,\"payer\":{\"type\":null,\"id\":\"1685886032\",\"email\":null,\"identification\":{\"type\":null,\"number\":null},\"first_name\":null,\"last_name\":null,\"entity_type\":null,\"phone\":{\"area_code\":null,\"number\":null,\"extension\":null},\"operator_id\":null},\"collector_id\":260742905,\"counter_currency\":null,\"payment_method_id\":\"pix\",\"payment_method\":{\"id\":\"pix\",\"type\":\"bank_transfer\",\"issuer_id\":\"12501\"},\"payment_type_id\":\"bank_transfer\",\"pos_id\":null,\"transaction_details\":{\"financial_institution\":null,\"net_received_amount\":0,\"total_paid_amount\":52.5,\"installment_amount\":0,\"overpaid_amount\":0,\"external_resource_url\":null,\"payment_method_reference_id\":null,\"acquirer_reference\":null,\"payable_deferral_period\":null,\"bank_transfer_id\":null,\"transaction_id\":null},\"fee_details\":[],\"differential_pricing_id\":null,\"authorization_code\":null,\"captured\":true,\"card\":[],\"call_for_authorize_id\":null,\"statement_descriptor\":null,\"shipping_amount\":0,\"additional_info\":{\"tracking_id\":\"platform:8|8.3.30,so:so;,type:SDK3.8.0,security:none\"},\"coupon_amount\":0,\"installments\":1,\"description\":\"Inscrição no evento: III CORRIDA SAUIM DE COLEIRA\",\"notification_url\":\"https:\\/\\/www.movamazon.com.br\\/api\\/mercadolivre\\/webhook.php\",\"issuer_id\":\"12501\",\"processing_mode\":\"aggregator\",\"merchant_account_id\":null,\"merchant_number\":null,\"metadata\":[],\"callback_url\":null,\"release_info\":null,\"marketplace_owner\":null,\"integrator_id\":null,\"corporation_id\":null,\"platform_id\":null,\"point_of_interaction\":{\"type\":\"OPENPLATFORM\",\"application_data\":{\"name\":null,\"version\":null},\"transaction_data\":{\"qr_code\":\"00020126580014br.gov.bcb.pix0136fb6df74b-f82a-425b-92ab-55459eab8d69520400005303986540552.505802BR5915EUDIMACIBARBOZA6009Sao Paulo62250521mpqrinter146699740112630429D3\",\"qr_code_base64\":\"iVBORw0KGgoAAAANSUhEUgAABWQAAAVkAQMAAABpQ4TyAAAABlBMVEX\\/\\/\\/8AAABVwtN+AAAKpklEQVR42uzdQXLi2LIGYBEMGLIElsLS8NK8FJbAkAHBuVFcRJ5MSdjd5e5SR3z\\/xM+vDfpUs7x5TuYgIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIv9sdm2Sz2HT2mUYjr9+uTz\\/bv\\/rl1v8fGT7+PvH\\/3n49XP368NDa9fnhx9flnN6fvj0\\/O\\/jh2toaWlpaWlpaWlpaWlpaX9Aey6\\/jw84tvtTN\\/78v\\/7xwIdu+\\/zwLh7wUF6Xa+zT61XHV+x+Dq8n09LS0tLS0tLS0tLS0q5YG5XmqB2Vj2xCOZar56h5I9dSSF+i9v34ouaND11paWlpaWlpaWlpaWlp\\/4vaz7m\\/38eD21M92+wcC+bP\\/kP7\\/tVupealpaWlpaWlpaWlpaWl\\/Y9qN3Hudla7bd\\/IPak\\/SuE87ZzS0tLS0tLS0tLS0tLS0v4T2nJaeBMPTA8aW7bb6POey5ccX6eG76XM30xf6FzUf+9sMy0tLS0tLS0tLS0tLe2\\/qV2eXDRTru5DG63asXAey9XdX\\/+SH5izREtLS0tLS0tLS0tLS\\/uPa9\\/k+Gx+nl5zcLcL5Wqteev4o8eX3Mud09vzyPFt+JHQ0tLS0tLS0tLS0tLS\\/jvaw9zR2X10UD\\/6pmd3LnepXH1OMNqkAnpWe4iVLOmf4Pim5qWlpaWlpaWlpaWlpaVdgfb6\\/Mz4oEvZojJmHzVvfdBTNyxcBd3ExKKkbXO924GWlpaWlpaWlpaWlpaW9ne1u5hYlP42Veij8jYduBuvuDTyt6vQ90U79M3jaxHR0tLS0tLS0tLS0tLSrlWbps4eWys1bzexqE1r3vTKn5PCuUWzuLtz2vrTwode+53JRbS0tLS0tLS0tLS0tLR\\/THuePLCbf5uani1q4G5k7XTo0C6OFs+2X1O2aQLvl3OWaGlpaWlpaWlpaWlpaVegHVLF+Tw6O3\\/XtD6gzr8tR2i79uuwMP925vYrLS0tLS0tLS0tLS0tLe3vars7p98atNu1aMeDvvVBob7Ez9PkYumbIU1HWlpaWlpaWlpaWlpa2nVquz7v52LZOjzL1FrzdtdFD9HfLQX0PT6c7py2dPR4un6UlpaWlpaWlpaWlpaWdv3aN5OLUvOzrmk59HdO0yjbevR4KHdOu+G5aYguLS0tLS0tLS0tLS0t7fq0uXOamp7xxfm66KnVLSrXuIN67JWX6cNOk\\/m33at2\\/260tLS0tLS0tLS0tLS0tD+gjZbs2KrtpvR261rGab1pam\\/rH1S1KTM7Xw796N9r\\/E5LS0tLS0tLS0tLS0u7Um3eGPqsefOA3WXtm50v44XV4+RLhun60UN\\/avhdaGlpaWlpaWlpaWlpaf+stlN\\/9vpL6ZR2GU8Njw+qr1xq3zFjJ3W7MMnomo4c09LS0tLS0tLS0tLS0q5X+\\/jbodS84xHaUzmPWzun5Y5pvrja+gJ67JymIbozknfTemlpaWlpaWlpaWlpaWlXoG2p+ZnK1NOcKk0s6q6CDpPO6Vgoz7z6MHRDdLs5uI2WlpaWlpaWlpaWlpaW9ke19aBvVOSb6V3TNLmoPmg3N+7oXkb+tnLkOP9vBbS0tLS0tLS0tLS0tLQr1c6ua7nMLf3sytXHh7bT8vQ4OTV8XzgtfEtHjlPBTEtLS0tLS0tLS0tLS7tS7S6mz8bfbsqd03sqUz96bd0cmubdpslF+cLq+OE0\\/ujc93BpaWlpaWlpaWlpaWlp16pN5WpcH93E0dkWte\\/HXMHcJQ7xpkWem1TrxkLPVtqxX2xRoaWlpaWlpaWlpaWlpaX9prY7LRx18abMVepatCndtN70ytfS9+3Wj8bI379TodPS0tLS0tLS0tLS0tKuS7uLKb1Rpt6nte4wvXOaBu0ey53TVPPuFxbH\\/OWal5aWlpaWlpaWlpaWlvbf1aZhQ\\/M1b3uVrfW08JC0pebtXrm2XbudL6nm\\/aLWpaWlpaWlpaWlpaWlpV2BtitXp1lafHLuf9bzt0OMPYpxR\\/Wf4Jaq7dnCmZaWlpaWlpaWlpaWlpb2B7T1QUMU13FtdJtGJdWDvrXsL69cm8XbePKoPHy3QqelpaWlpaWlpaWlpaX9M9ohbVxJQ4fS5KLLF0eN60HfY1kc015Te2\\/p5+zFVVpaWlpaWlpaWlpaWtpVa8cHzDY9T\\/2kon0MHaqvGj\\/HQnkf+tPry7o7p4d+\\/NGQOqm0tLS0tLS0tLS0tLS069WmB4217mffOU1HabfxhYdy7rauHz0udE5rzRud0937OUu0tLS0tLS0tLS0tLS0f1C7Kxsx023OsdnZjbLd9xcu60XMtD2lbk0Zpq98Xmi\\/tu\\/d4qSlpaWlpaWlpaWlpaWl\\/U7n9P8HfaMyT8OGxlPD22jVPorpbfni3fIrDwtHjuPiapbQ0tLS0tLS0tLS0tLSrle78F82s6NrP15Dh7rrojH2qJU+7\\/za0dNk0m7qNNPS0tLS0tLS0tLS0tKuWJvK1M\\/+QUO5czqUpudhsebt7pzu+yPHt6n2XC6s0tLS0tLS0tLS0tLS0q5fG0dnW7ku2h2Z7TqncVR2V8rWmcWeH6\\/2a1fz1i0qtLS0tLS0tLS0tLS0tLQ\\/qN3F3x7LWd104HffXxu9LX9JvPI9fu77U8NvTgt3r0pLS0tLS0tLS0tLS0u7Xu04IzfU9aDvPu6cRn93G7XuzO6XMrkoHzluC9N60yvT0tLS0tLS0tLS0tLSrkybN4dGzbuPnS+hHdJp4bT0s64frbXvUF75d\\/q8tLS0tLS0tLS0tLS0tCvQDqXmHV6bQ1sZXTvOvc13T1Nq+3V2clHamtLtc6GlpaWlpaWlpaWlpaWl\\/RltFNPd9dHL3AbRFrtfWr\\/zZVd+zhw1bmVY06nv86bTwrS0tLS0tLS0tLS0tLTr1e5S5Tk9qzszaDdOCc\\/cPU3aVDh3X\\/L24iotLS0tLS0tLS0tLS3t+rVvzuqeyoHfVsrVaH52m0Pr0eO4c9q96qH\\/suu7Haa0tLS0tLS0tLS0tLS0a9G2fiHKMLcApU4uqp3T7vBuHOKtc283s4VynVw0zlCipaWlpaWlpaWlpaWlXaP2jf6zdE7TFpU0sWjmladt13sppFvp2ba4EjrQ0tLS0tLS0tLS0tLS0v68NhXT7TlYd7xzWk8L54G7pc97f478zV\\/6MdHeUseZlpaWlpaWlpaWlpaWds3aXZvksyz5TLXu2O899y3abm3L8dUsrhOL7l8sjlkqnGlpaWlpaWlpaWlpaWlXpT2X30dtOjXcnRL+GIZp5zTVvGmLylebQ2\\/p3238ElpaWlpaWlpaWlpaWtr1aqPSHLUtfqZmZ\\/d7+lDKNZQxB7cWzjMLPNs3bsjS0tLS0tLS0tLS0tLS0v6m9hKt2qVBu8Prgd2imDSt9xL\\/\\/1O\\/fvQy3Rz612YL09LS0tLS0tLS0tLS0q5LW9e11EG7aV1LK4tjruUC6+U7F12\\/eeeUlpaWlpaWlpaWlpaW9o9rZ08Lp+z7HTB158tueuc0xh\\/NzL0da95Da2nny9LuUlpaWlpaWlpaWlpaWtr1aKeTi4bywJmkBSi56Tn7ZafXJKOZ87fn6JyWgpmWlpaWlpaWlpaWlpaW9m9rRURERERERERERERERERERERERERERERERERERFad\\/wUAAP\\/\\/wihPfFmjs0oAAAAASUVORK5CYII=\",\"transaction_id\":null,\"bank_transfer_id\":null,\"financial_institution\":null,\"bank_info\":{\"payer\":{\"id\":null,\"account_id\":null,\"long_name\":null,\"external_account_id\":null,\"identification\":[]},\"collector\":{\"account_id\":null,\"long_name\":null,\"account_holder_name\":\"Eudimaci Barboza de Lira\",\"transfer_account_id\":null},\"is_same_bank_account_owner\":null,\"origin_bank_id\":null,\"origin_wallet_id\":null},\"ticket_url\":\"https:\\/\\/www.mercadopago.com.br\\/payments\\/146699740112\\/ticket?caller_id=1685886032&hash=01b9cef5-fb31-4251-bb34-e6644a4c291b\",\"e2e_id\":null}},\"accounts_info\":null,\"tags\":null,\"refunds\":[]}', 52.50, 'pix', 1, 0.00, 28, '2026-02-17 22:39:33'),
(17, 11, 'pix_147124934258', '147124934258', 'https://www.mercadopago.com.br/payments/147124934258/ticket?caller_id=3154860678&hash=9b56ac9e-d2ef-4369-a1a9-2bf5ad58e53d', 'processando', '2026-02-20 23:54:49', '2026-02-20 23:54:49', '{\"id\":147124934258,\"acquirer_reconciliation\":[],\"sponsor_id\":null,\"operation_type\":\"regular_payment\",\"order\":[],\"brand_id\":null,\"build_version\":\"3.143.0-rc-4\",\"binary_mode\":false,\"external_reference\":\"142946577853\",\"financing_group\":null,\"status\":\"pending\",\"status_detail\":\"pending_waiting_transfer\",\"store_id\":null,\"taxes_amount\":0,\"date_created\":\"2026-02-20T19:54:48.847-04:00\",\"live_mode\":true,\"date_last_updated\":\"2026-02-20T19:54:48.847-04:00\",\"date_of_expiration\":\"2026-02-21T19:54:48.653-04:00\",\"deduction_schema\":null,\"date_approved\":null,\"money_release_date\":null,\"money_release_schema\":null,\"money_release_status\":\"released\",\"currency_id\":\"BRL\",\"transaction_amount\":20,\"transaction_amount_refunded\":0,\"payer\":{\"type\":null,\"id\":\"3154860678\",\"email\":null,\"identification\":{\"type\":null,\"number\":null},\"first_name\":null,\"last_name\":null,\"entity_type\":null,\"phone\":{\"area_code\":null,\"number\":null,\"extension\":null},\"operator_id\":null},\"collector_id\":260742905,\"counter_currency\":null,\"payment_method_id\":\"pix\",\"payment_method\":{\"id\":\"pix\",\"type\":\"bank_transfer\",\"issuer_id\":\"12501\"},\"payment_type_id\":\"bank_transfer\",\"pos_id\":null,\"transaction_details\":{\"financial_institution\":null,\"net_received_amount\":0,\"total_paid_amount\":20,\"installment_amount\":0,\"overpaid_amount\":0,\"external_resource_url\":null,\"payment_method_reference_id\":null,\"acquirer_reference\":null,\"payable_deferral_period\":null,\"bank_transfer_id\":null,\"transaction_id\":null},\"fee_details\":[],\"differential_pricing_id\":null,\"authorization_code\":null,\"captured\":true,\"card\":[],\"call_for_authorize_id\":null,\"statement_descriptor\":null,\"shipping_amount\":0,\"additional_info\":{\"tracking_id\":\"platform:8|8.3.30,so:so;,type:SDK3.8.0,security:none\"},\"coupon_amount\":0,\"installments\":1,\"description\":\"Inscrição no evento: III CORRIDA SAUIM DE COLEIRA\",\"notification_url\":\"https:\\/\\/www.movamazon.com.br\\/api\\/mercadolivre\\/webhook.php\",\"issuer_id\":\"12501\",\"processing_mode\":\"aggregator\",\"merchant_account_id\":null,\"merchant_number\":null,\"metadata\":[],\"callback_url\":null,\"release_info\":null,\"marketplace_owner\":null,\"integrator_id\":null,\"corporation_id\":null,\"platform_id\":null,\"point_of_interaction\":{\"type\":\"OPENPLATFORM\",\"application_data\":{\"name\":null,\"version\":null},\"transaction_data\":{\"qr_code\":\"00020126580014br.gov.bcb.pix0136fb6df74b-f82a-425b-92ab-55459eab8d69520400005303986540520.005802BR5915EUDIMACIBARBOZA6009Sao Paulo62250521mpqrinter1471249342586304763B\",\"qr_code_base64\":\"iVBORw0KGgoAAAANSUhEUgAABWQAAAVkAQMAAABpQ4TyAAAABlBMVEX\\/\\/\\/8AAABVwtN+AAAKtUlEQVR42uzdQXLi2g4GYFMMPGQJLIWlwdKyFJbAkAEVv+o0zpFkO5B+uY276vsneanb4M+Z6UlHpxMRERERERERERERERERERERERERERERERERERGR\\/zb9MMlbt2m\\/XO7\\/bvfrl1v58Pbjn3z8z\\/2vn\\/2vD3fDcO26w+eX5RzvX3K8\\/\\/fxwzW0tLS0tLS0tLS0tLS0tD+gPZff0wM+lO\\/3n7\\/17UHb+4f79oAP5XW5xj7GV97HVw050NLS0tLS0tLS0tLS0q5Z2yrNoD181rybprzEB25bjXputW4rpC+t9j09qHnbh660tLS0tLS0tLS0tLS0\\/6J2Nrv24KYcWu3bXvV3ofwWP7SLvdFbqXlpaWlpaWlpaWlpaWlp\\/1HtZnZ0ts7dPsh7Up8+C+elziktLS0tLS0tLS0tLS0t7X+hLdPCm\\/bAmSnh0+eUcJgWPpdB31Shd\\/dmcc25qP9stpmWlpaWlpaWlpaWlpb2b2pnNxddFsrVXdO2Vm0\\/nRb+7pf8f3uWaGlpaWlpaWlpaWlpaf+O9osc7s3P8cxp7Zimpmeqefs2JVw7qceyRHe6VPcPQ0tLS0tLS0tLS0tLS\\/t3tPu50dnQ9DzG\\/zR2UM+LHx733+aat2nzmdNRmzqphy9qXlpaWlpaWlpaWlpaWtoXakPTs\\/v8GR5UkzYXzTxoOsQ7Du1umnIXtXke9+vzo7S0tLS0tLS0tLS0tLS0z2vr3qChtWini3Zv7fdUXF+nf4K30iw+xgOsQ\\/twax5f0\\/wyLS0tLS0tLS0tLS0t7Rq1edA3PfhQHnC6f3E6Lpq+5LeyFM6h5g1nTturjrVvejItLS0tLS0tLS0tLS3tKrWh5n1rt6mkY6Ld54PGB+eVta1zOparfRstrtPC02xT2\\/Xh5iJaWlpaWlpaWlpaWlraF2pDn\\/JQjoumi08uCw8Ibdf2JcP09pRxaHd2\\/234u9HS0tLS0tLS0tLS0tLS\\/pS2K13WWlzX1Ui7OOjbzT5ovPul9XkvZVnTsHDmNHyYlpaWlpaWlpaWlpaWdo3amUHfWrYe52rej7J029YezR5c7eLNoV161WPZWDS9fpSWlpaWlpaWlpaWlpZ2rdr2gD5uLspNzy42P+ve26pt6k0ZPe7KmdOw9\\/bpbb20tLS0tLS0tLS0tLS0r9GGzumhND3HLz5NRmiHVvMOcWQ2ra7dlEI5v+qxzN3W6puWlpaWlpaWlpaWlpaW9ge0Xeyu9vdietemho9lavhUzpxOr2uZuTm0fWnQ7lufd2wWP6zQaWlpaWlpaWlpaWlpaV+o7dtJz6ZeWrAb7nwZa9vZA6vXdmC13hzapoZnzp6euwehpaWlpaWlpaWlpaWlfa02qN+6vLmoNTvrDaJjoTxTth6+2oN7ub\\/y7Caja7p9hpaWlpaWlpaWlpaWlna92tma9y3eppLmcbfT+dvz3NqjXeyYhs7p0hLd7tHmIlpaWlpaWlpaWlpaWtp1aIc4OpvL1HSLyjiHmzYW5VcuV7GMhXL4spmLPReOgtLS0tLS0tLS0tLS0tLS\\/oT2bW5aeGlz0fne501be+uZ0y83F+U\\/wfSVaWlpaWlpaWlpaWlpaVepDTO65bqWrh0THcvTUb1fKEsP8Rsu0zOnbR3SrYwcP\\/6b0tLS0tLS0tLS0tLS0r5a27dB31Zx5ss+p\\/tvR+38LSr3V90kba15W8f0ViTP3HNKS0tLS0tLS0tLS0tL+0rttFwd2kUoqXM6UwMn5TC5iqXWvLfSfh3KwdXhi\\/lbWlpaWlpaWlpaWlpaWtrntWFauP2buldps7Bg99aaxOmVr6Xve5lb+fsnFTotLS0tLS0tLS0tLS3turR92dI7xLOnuVU7fVC686VeO7ppU8K76cUxs19CS0tLS0tLS0tLS0tLuzJtP7d1Nte87ZhoKFdTc3Om\\/VpfObVdQwc1vfKDjiktLS0tLS0tLS0tLS3tOrTp0s+WvGwobS4a52\\/rCO0Qa9704fonuKVqu83fPq55aWlpaWlpaWlpaWlpaWmf1NaTnofJYt33pktfXO98qX+CoXzJbuFVR+X+iQqdlpaWlpaWlpaWlpaW9oXarty40re7XtqdL5tW817iwt1x4Pfa1G+x33sp15Cmm0Nvs9PCtLS0tLS0tLS0tLS0tCvXdu0BCzVv2FQUmp7jq04vjglt11F\\/HIbpmdN9XH\\/UpU4qLS0tLS0tLS0tLS0t7Uq1oeZNx0VTuTqrDNohPvDa5m0PC53TWvO2zmn\\/9f5bWlpaWlpaWlpaWlpa2hdq+3IQM53mDNdojqmra7vy4VZA11tTutkjoanWffLmUFpaWlpaWlpaWlpaWlraZ7SzFXoe+G0Ld7et3\\/tRTM9s7V165bS5KI0a34pk+O7\\/n0BLS0tLS0tLS0tLS0v7l7Wpu3qYPGBT9t9u2+ai2uc9fL56bhafykOPpcad\\/XvR0tLS0tLS0tLS0tLSrkzbz12E8p7Ux8m0cKh194s173taf1RHjtNB1j0tLS0tLS0tLS0tLS3tv6Ed+5fX+Gs9LjpM99+mzmlfytZr2X\\/bxfbrWPPOD+\\/S0tLS0tLS0tLS0tLS0v6Utm8PfIufvcSB35ljo+c46Ju39bbyfteWNqWDq3XPUv27Hb44IUtLS0tLS0tLS0tLS0v7Wu358wGhRdtNt\\/WeFmrecdFuqnmnqSPHXdPup19CS0tLS0tLS0tLS0tLu1Jtan6GpP7lF9e1pKnhVK6Gmjd9SZgabgX0NzuntLS0tLS0tLS0tLS0tC\\/Udsv9yjZ\\/G34f7jXvWDDvF171ENuu9UvTrSnh9CstLS0tLS0tLS0tLS0t7Q9ouzjgGyr1y9wNokO6+2XaLO6nyqHcINr2LC1NC9PS0tLS0tLS0tLS0tKuXVv3B6VW7VCOi3bxBtGu1LxL146mbb278uHwyrS0tLS0tLS0tLS0tLRr1vbPzOpuyuaiXK6OA7\\/lzpeg\\/5gSzmdO21nT8Uuu5QIZWlpaWlpaWlpaWlpa2lVqQ0rTc+yUbtJe3G7SOZ1peqa1R12sfWunNG8uSu1XWlpaWlpaWlpaWlpa2pVpZ\\/RjmfoWO6d16VDaWLS09mgXO6b1FpV8irMVzt8JLS0tLS0tLS0tLS0tLe03kyvy4+fPW5sWPsfiekgXx9z17+2Va7M4afP1o7S0tLS0tLS0tLS0tLRr1s6Wq\\/WaljwtfIp7b+u1Lcsbi97TxTH1lWc7zrS0tLS0tLS0tLS0tLTr054nndNN2X+bp4RPXVdq3T7VvNOp4S6++rYV0rXmfXSfCy0tLS0tLS0tLS0tLe0qtK3SHLVD+zkuHWq3qIxzt9v2oWnemz4U0OP+21MsmFOt2z+x\\/5aWlpaWlpaWlpaWlpaW9s+1l7gqaZgu2h0TViSNfd6p8mM6OFw\\/epneHNr2BV+\\/nhampaWlpaWlpaWlpaWlXbf2FBft7mKfty7evcZmcThrennmoGu+fYaWlpaWlpaWlpaWlpZ2vdrptPBQVtbu4h0wY8c0dE7TmdPr9M6X4+erb1vNux+GeudLGjWmpaWlpaWlpaWlpaWlXZ92+QKUmTOnl3IBSroIpZaru4Xadzc9uJrarx0tLS0tLS0tLS0tLS0t7f+vFREREREREREREREREREREREREREREREREREREVl1\\/hcAAP\\/\\/l81mg79\\/No8AAAAASUVORK5CYII=\",\"transaction_id\":null,\"bank_transfer_id\":null,\"financial_institution\":null,\"bank_info\":{\"payer\":{\"id\":null,\"account_id\":null,\"long_name\":null,\"external_account_id\":null,\"identification\":[]},\"collector\":{\"account_id\":null,\"long_name\":null,\"account_holder_name\":\"Eudimaci Barboza de Lira\",\"transfer_account_id\":null},\"is_same_bank_account_owner\":null,\"origin_bank_id\":null,\"origin_wallet_id\":null},\"ticket_url\":\"https:\\/\\/www.mercadopago.com.br\\/payments\\/147124934258\\/ticket?caller_id=3154860678&hash=9b56ac9e-d2ef-4369-a1a9-2bf5ad58e53d\",\"e2e_id\":null}},\"accounts_info\":null,\"tags\":null,\"refunds\":[]}', NULL, 'pix', 1, NULL, 18, '2026-02-20 20:54:49');
INSERT INTO `pagamentos_ml` (`id`, `inscricao_id`, `preference_id`, `payment_id`, `init_point`, `status`, `data_criacao`, `data_atualizacao`, `dados_pagamento`, `valor_pago`, `metodo_pagamento`, `parcelas`, `taxa_ml`, `user_id`, `created`) VALUES
(18, 11, 'pix_147122671442', '147122671442', 'https://www.mercadopago.com.br/payments/147122671442/ticket?caller_id=3154860678&hash=f0aebed8-6004-4401-b0bf-884050dfbfe3', 'processando', '2026-02-20 23:55:10', '2026-02-20 23:55:10', '{\"id\":147122671442,\"acquirer_reconciliation\":[],\"sponsor_id\":null,\"operation_type\":\"regular_payment\",\"order\":[],\"brand_id\":null,\"build_version\":\"3.143.0-rc-4\",\"binary_mode\":false,\"external_reference\":\"142946577853\",\"financing_group\":null,\"status\":\"pending\",\"status_detail\":\"pending_waiting_transfer\",\"store_id\":null,\"taxes_amount\":0,\"date_created\":\"2026-02-20T19:55:10.125-04:00\",\"live_mode\":true,\"date_last_updated\":\"2026-02-20T19:55:10.125-04:00\",\"date_of_expiration\":\"2026-02-21T19:55:09.998-04:00\",\"deduction_schema\":null,\"date_approved\":null,\"money_release_date\":null,\"money_release_schema\":null,\"money_release_status\":\"released\",\"currency_id\":\"BRL\",\"transaction_amount\":20,\"transaction_amount_refunded\":0,\"payer\":{\"type\":null,\"id\":\"3154860678\",\"email\":null,\"identification\":{\"type\":null,\"number\":null},\"first_name\":null,\"last_name\":null,\"entity_type\":null,\"phone\":{\"area_code\":null,\"number\":null,\"extension\":null},\"operator_id\":null},\"collector_id\":260742905,\"counter_currency\":null,\"payment_method_id\":\"pix\",\"payment_method\":{\"id\":\"pix\",\"type\":\"bank_transfer\",\"issuer_id\":\"12501\"},\"payment_type_id\":\"bank_transfer\",\"pos_id\":null,\"transaction_details\":{\"financial_institution\":null,\"net_received_amount\":0,\"total_paid_amount\":20,\"installment_amount\":0,\"overpaid_amount\":0,\"external_resource_url\":null,\"payment_method_reference_id\":null,\"acquirer_reference\":null,\"payable_deferral_period\":null,\"bank_transfer_id\":null,\"transaction_id\":null},\"fee_details\":[],\"differential_pricing_id\":null,\"authorization_code\":null,\"captured\":true,\"card\":[],\"call_for_authorize_id\":null,\"statement_descriptor\":null,\"shipping_amount\":0,\"additional_info\":{\"tracking_id\":\"platform:8|8.3.30,so:so;,type:SDK3.8.0,security:none\"},\"coupon_amount\":0,\"installments\":1,\"description\":\"Inscrição no evento: III CORRIDA SAUIM DE COLEIRA\",\"notification_url\":\"https:\\/\\/www.movamazon.com.br\\/api\\/mercadolivre\\/webhook.php\",\"issuer_id\":\"12501\",\"processing_mode\":\"aggregator\",\"merchant_account_id\":null,\"merchant_number\":null,\"metadata\":[],\"callback_url\":null,\"release_info\":null,\"marketplace_owner\":null,\"integrator_id\":null,\"corporation_id\":null,\"platform_id\":null,\"point_of_interaction\":{\"type\":\"OPENPLATFORM\",\"application_data\":{\"name\":null,\"version\":null},\"transaction_data\":{\"qr_code\":\"00020126580014br.gov.bcb.pix0136fb6df74b-f82a-425b-92ab-55459eab8d69520400005303986540520.005802BR5915EUDIMACIBARBOZA6009Sao Paulo62250521mpqrinter1471226714426304A704\",\"qr_code_base64\":\"iVBORw0KGgoAAAANSUhEUgAABWQAAAVkAQMAAABpQ4TyAAAABlBMVEX\\/\\/\\/8AAABVwtN+AAAKvklEQVR42uzdS44quRIGYCMGDFkCS2FpxdJYCktgyACRV7eaTDvCph59HpUtff8E0X1If1mzkO2IIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiJ\\/Nrupy7lspulaynF6PP\\/RP99L2T4\\/SymH+u\\/fH3Ip5fnj5bOUff18z7WUt+n+\\/v30\\/4e9\\/zg+rD6UlpaWlpaWlpaWlpaWlvaXtZf0vS7wCJ+nRbt9PvL+vlB45Xft7fmK0\\/PHpX4\\/tYsdqjagjrS0tLS0tLS0tLS0tLRr1oby9dwu9K6cF8o1772vfetDmlo35P58Qqx56yvfaGlpaWlpaWlpaWlpaf+L2nNXtm7DgqHmvTxr3eOy4GMuX\\/tad98WzLS0tLS0tLS0tLS0tLT\\/ce28f\\/nPgm\\/Lgtt6hLakH706Ontczt02nxMtLS0tLS0tLS0tLS0t7d\\/SptPCuTIvVd1oa8U+1Yccl\\/\\/yCPu8p3Ra+K390a+dbaalpaWlpaWlpaWlpaX9m9oPOxfNp4Vz06HL633e+srfeMjv6bNES0tLS0tLS0tLS0tL+2e1w8z9bje1XG1q3bf2qZdUOB+7I8clbb+W\\/s7pr4WWlpaWlpaWlpaWlpb2b2oP9dxtVVz7o7MlLfw6t9EIlk3fuejSrnh71r4fnb+lpaWlpaWlpaWlpaWl\\/XFtPXfbHKG9pqO08wKvbnGGhUPbo6lqr\\/1D5r\\/X93ZMaWlpaWlpaWlpaWlpaWm\\/pJ1vfB7b1eaKfF+\\/zwd95\\/3dWmw3p4XLcmr4n1PC8+yXUxnMgLn8iz1oWlpaWlpaWlpaWlpa2p\\/Vzgs23WfrddFS1X3\\/23vdmm0eEu6c9keQt337o\\/CqzRFjWlpaWlpaWlpaWlpa2lVqq3LQ97Zm+7w2ug0173zQt2qb9KeGSyiccxNdWlpaWlpaWlpaWlpa2jVr4wLPynMTat58xzRrU61b+vO3eYrKvJMapqfsahukT7r10tLS0tLS0tLS0tLS0tJ+RZtvfO7StdFr2uedWyUd2pZJ+cjxrT9qPOete+Wp\\/3sdaWlpaWlpaWlpaWlpadesreXqXPOGsvUR9nnDFu3l5UPK885pU+uW9rRwvnO6C38vWlpaWlpaWlpaWlpa2jVqm\\/93bGveMDm02ewsSTlfE+03PTcvDv5uQw08F9Ch\\/dGRlpaWlpaWlpaWlpaWdrXaeeGw6VnSZmfT\\/7Yenc01b8gmjGIJrzrVbdfwqrS0tLS0tLS0tLS0tLS0v1HbqM+l9BX6tWprn6WmuC7txNBbfeV55ksu96fllbfhTmo4pzzR0tLS0tLS0tLS0tLSrlZ7qAs9a9\\/H8KBvGNMy1Zp3OK7luFxcHRfO+cLq3PJ3WHXT0tLS0tLS0tLS0tLSrkdb0gIhoeaNrWtzPnz42\\/NPcOpOCzc176WtvmlpaWlpaWlpaWlpaWnXp93VTc+Q\\/fM3++cAlHnn9Fpb2OZDvLX2vaWJocP2R\\/f6ypeRnpaWlpaWlpaWlpaWlnZ92jgA5fUUlbBz2vwo1L63\\/ipoGOj5wc7pp116aWlpaWlpaWlpaWlpaWm\\/qW0mreSZL9Oy0KPu7+7TndNDqtTPqUKvm8fNHdSwObxNk0OnL8x8oaWlpaWlpaWlpaWlpf0ZbbxzGpTHRbkJC5yWu6b3frP42H5e2w5GcZ\\/31UNq9U1LS0tLS0tLS0tLS0u7Sm3pZpds6s5pOOibX\\/Fef9w0HQqvVjsXPV5vv776TktLS0tLS0tLS0tLS7sybdMvKHcsCjVvc9f01GqHHYx26SHN9mvf93aQMy0tLS0tLS0tLS0tLS3tb9H2DXc3w8mheeZLbZG0q62Swhbtvq\\/Qy3LkuOm3lPd5Cy0tLS0tLS0tLS0tLe06tYPORee0z3tKHYzyPm\\/o1nvsytZNKKTzq9eLq\\/EhtLS0tLS0tLS0tLS0tCvV9v2CBrrcwjb3v+1r3k3YOZ1r3WmkfV1909LS0tLS0tLS0tLS0q5Ku0tHZsMp2E3fuvaajs5e0h3TKRXOU2qHVLPtf9yMH6WlpaWlpaWlpaWlpaWl\\/VVtU6H341oe4aDvPPRzbrRbx7XswsLPV3+ESr0\\/cjzvMDdqWlpaWlpaWlpaWlpa2vVrDy\\/6Bh3bBfOB31ymDhvt7mvBXAfH5Iur99rq9\\/C1O6e0tLS0tLS0tLS0tLS0P6WN8zpDxdlr55o3b37e0pHjpuY9plc8LZNDS1DXGvijyaG0tLS0tLS0tLS0tLS0P6tt1OdlwUdtXRumqOSd01fDPvMUlfkV9702XFilpaWlpaWlpaWlpaWlXb\\/20N7ivNVNz9DCtulk9LbsmN77Hx\\/bW5v7NFYzqy9lMJLl831eWlpaWlpaWlpaWlpaWtqva\\/uORde6RRuUU9+x6EXbo3haeBp17W1euW9\\/REtLS0tLS0tLS0tLS7s+7eC08DEteErNh0pbrl5KHNdybpsP5c3i5s7pcHBMbZ5baGlpaWlpaWlpaWlpaderrbpdXeC8KOdydRu0oTw9tK96S9ut+chxqLbvLyS0tLS0tLS0tLS0tLS069PGa6N1wXBdNC+cfzTY9AzbrgPtW9oh\\/U63XlpaWlpaWlpaWlpaWlraL05RGTTcDTmNro02xfW8UHhIHRwTjxyHWS9hxcGPaWlpaWlpaWlpaWlpaVemHWzRhpq3lGXmS220O49pyaeFb89Gu2GzuNSHhLGjzQTRMH5094UbsrS0tLS0tLS0tLS0tLQ\\/o92FrrOpXH2khQabnvMCl7rtOpz5EgrofZr5MrUPmWhpaWlpaWlpaWlpaWlXq20WPC8LPuqm51QXfuv2VGPzofTKm\\/D5unPRtn\\/1T++c0tLS0tLS0tLS0tLS0tJ+rt2FHrmfjG25h6GffYukW\\/\\/8enG1VOWHp4YnWlpaWlpaWlpaWlpa2tVq88SVwf5uWRYa1Ly56VAeHNNPDt2Ggvk9l1I+PdNMS0tLS0tLS0tLS0tLuwLtB52LzovyUf\\/9vr1z+mEB3eychrGjH\\/ydvnjnlJaWlpaWlpaWlpaWlvYHtVP6TVl2TOO521Pa7Kzla3NttH4+6vjR5vxtbX9U6kMuqYnukZaWlpaWlpaWlpaWlnad2oF+LlPz0dnwFuEW526orjuneRt20Lmo7qAWWlpaWlpaWlpaWlpaWto\\/op0f3Nw5PS0LDWa+DBZ6MThm3jSOnYu+dHGVlpaWlpaWlpaWlpaWdiXaXXeRdNmanZb93ZLGtswTQ7d1TEuY+dLUuMflR\\/kCa6M9fGNCDS0tLS0tLS0tLS0tLe3Pai8f7Zw2Cw\\/L1cNo03NXC+bzSBnaIN2DhJaWlpaWlpaWlpaWlnb92nB09px2UGsL23xUdjuNEp5\\/bPvfhousg+kptQMvLS0tLS0tLS0tLS0tLe2f0W6GB33fddfSDf3MB33r0tc0+2XOftSs6faN3sK0tLS0tLS0tLS0tLS0K9LOW7Rl2LmonhbOD7m1te4842UT9KU9ejw\\/5NBq\\/02FTktLS0tLS0tLS0tLS\\/s3tf1p4akd15LVr2reqd12zWlmvsyDZIJ2\\/r774CG0tLS0tLS0tLS0tLS0P67tOxeVtMkZr4e+pXO2uWB+oW4urjY1b1DmwpmWlpaWlpaWlpaWlpaW9he0IiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIqvO\\/wIAAP\\/\\/nwIUsyTtXMIAAAAASUVORK5CYII=\",\"transaction_id\":null,\"bank_transfer_id\":null,\"financial_institution\":null,\"bank_info\":{\"payer\":{\"id\":null,\"account_id\":null,\"long_name\":null,\"external_account_id\":null,\"identification\":[]},\"collector\":{\"account_id\":null,\"long_name\":null,\"account_holder_name\":\"Eudimaci Barboza de Lira\",\"transfer_account_id\":null},\"is_same_bank_account_owner\":null,\"origin_bank_id\":null,\"origin_wallet_id\":null},\"ticket_url\":\"https:\\/\\/www.mercadopago.com.br\\/payments\\/147122671442\\/ticket?caller_id=3154860678&hash=f0aebed8-6004-4401-b0bf-884050dfbfe3\",\"e2e_id\":null}},\"accounts_info\":null,\"tags\":null,\"refunds\":[]}', NULL, 'pix', 1, NULL, 18, '2026-02-20 20:55:10'),
(19, 11, 'pix_147124896532', '147124896532', 'https://www.mercadopago.com.br/payments/147124896532/ticket?caller_id=3154860678&hash=3a8d4fae-a809-4451-97e7-70bd37bcb448', 'processando', '2026-02-20 23:58:54', '2026-02-20 23:58:54', '{\"id\":147124896532,\"acquirer_reconciliation\":[],\"sponsor_id\":null,\"operation_type\":\"regular_payment\",\"order\":[],\"brand_id\":null,\"build_version\":\"3.143.0-rc-4\",\"binary_mode\":false,\"external_reference\":\"142946577853\",\"financing_group\":null,\"status\":\"pending\",\"status_detail\":\"pending_waiting_transfer\",\"store_id\":null,\"taxes_amount\":0,\"date_created\":\"2026-02-20T19:58:54.067-04:00\",\"live_mode\":true,\"date_last_updated\":\"2026-02-20T19:58:54.067-04:00\",\"date_of_expiration\":\"2026-02-21T19:58:53.871-04:00\",\"deduction_schema\":null,\"date_approved\":null,\"money_release_date\":null,\"money_release_schema\":null,\"money_release_status\":\"released\",\"currency_id\":\"BRL\",\"transaction_amount\":20,\"transaction_amount_refunded\":0,\"payer\":{\"type\":null,\"id\":\"3154860678\",\"email\":null,\"identification\":{\"type\":null,\"number\":null},\"first_name\":null,\"last_name\":null,\"entity_type\":null,\"phone\":{\"area_code\":null,\"number\":null,\"extension\":null},\"operator_id\":null},\"collector_id\":260742905,\"counter_currency\":null,\"payment_method_id\":\"pix\",\"payment_method\":{\"id\":\"pix\",\"type\":\"bank_transfer\",\"issuer_id\":\"12501\"},\"payment_type_id\":\"bank_transfer\",\"pos_id\":null,\"transaction_details\":{\"financial_institution\":null,\"net_received_amount\":0,\"total_paid_amount\":20,\"installment_amount\":0,\"overpaid_amount\":0,\"external_resource_url\":null,\"payment_method_reference_id\":null,\"acquirer_reference\":null,\"payable_deferral_period\":null,\"bank_transfer_id\":null,\"transaction_id\":null},\"fee_details\":[],\"differential_pricing_id\":null,\"authorization_code\":null,\"captured\":true,\"card\":[],\"call_for_authorize_id\":null,\"statement_descriptor\":null,\"shipping_amount\":0,\"additional_info\":{\"tracking_id\":\"platform:8|8.3.30,so:so;,type:SDK3.8.0,security:none\"},\"coupon_amount\":0,\"installments\":1,\"description\":\"Inscrição no evento: III CORRIDA SAUIM DE COLEIRA\",\"notification_url\":\"https:\\/\\/www.movamazon.com.br\\/api\\/mercadolivre\\/webhook.php\",\"issuer_id\":\"12501\",\"processing_mode\":\"aggregator\",\"merchant_account_id\":null,\"merchant_number\":null,\"metadata\":[],\"callback_url\":null,\"release_info\":null,\"marketplace_owner\":null,\"integrator_id\":null,\"corporation_id\":null,\"platform_id\":null,\"point_of_interaction\":{\"type\":\"OPENPLATFORM\",\"application_data\":{\"name\":null,\"version\":null},\"transaction_data\":{\"qr_code\":\"00020126580014br.gov.bcb.pix0136fb6df74b-f82a-425b-92ab-55459eab8d69520400005303986540520.005802BR5915EUDIMACIBARBOZA6009Sao Paulo62250521mpqrinter1471248965326304AB4B\",\"qr_code_base64\":\"iVBORw0KGgoAAAANSUhEUgAABWQAAAVkAQMAAABpQ4TyAAAABlBMVEX\\/\\/\\/8AAABVwtN+AAAKtklEQVR42uzdTZIiuQ4AYBMsWHIEjsLRqo7GUTgCSxYEfjE9ZFpymvp51d3kRHzaEBUD6S97p5EsFSGEEEIIIYQQQgghhBBCCCGEEEIIIYQQQgghhBBCCCGEEEII8WdjVxdxKptaL6Uc6\\/3xpX\\/\\/LmX7+CylHNr3fz3kXMrjx\\/NnKfv2+SsupbzV26+\\/3\\/952K8f54e1h9LS0tLS0tLS0tLS0tLS\\/lh77v5eHnR\\/HFDSQfWfgw8PbXvVUuv18Yr18ePS\\/n6Phx2aNqGOtLS0tLS0tLS0tLS0tGvWpvT19HjwadaND5q06cftISHXTXF7PCHnvO2Vr7S0tLS0tLS0tLS0tLT\\/Oe1llLZOgH8PTDnvuaWrjwPv7e\\/+VfcxYaalpaWlpaWlpaWlpaX9j2tzvHUHvs3anOsOW2ePc99t+Ky0tLS0tLS0tLS0tLS0tH9L23ULDzLzEg\\/MpdpfkTL02lqM6\\/yqm+6Vw49+1ttMS0tLS0tLS0tLS0tL+ze1H04umrqF+6FD52Wdt6Wru+8\\/5PfMWaKlpaWlpaWlpaWlpaX9s9ph5G7hdFCJxc8pbc3dwsuLq4Pya7pz+rOgpaWlpaWlpaWlpaWl\\/ZvaQ+u7bYrLsnW278d9HtfRCpbNcnLROZ54feS+H\\/Xf0tLS0tLS0tLS0tLS0r5c2\\/puQwvtpWulnQ64PLnFee7KsK2JtzbtZfmQ6d\\/rexVTWlpaWlpaWlpaWlpaWtovaacbn8eYZE8Z+b79PTX6TvXdlmzv0kEtQ9\\/HxTF51G+6c\\/rtOi8tLS0tLS0tLS0tLS3ty7QlpqvXmPPmXS\\/vi5z31kqzSZ0Wx\\/QtyNvl+KPpwuqUSJ9oaWlpaWlpaWlpaWlpV6s9ROVg7m2L7ePaaGj0nQ4Ki1DST5ZdwyUlzv0QXVpaWlpaWlpaWlpaWtqVa0M8Ms9Nynnb0KGxtst1y7L\\/tt+iMlVS0\\/aUXfcwWlpaWlpaWlpaWlpaWtofa9ONz91y90tKqqdRSYc4MqlvOb4uW42nSK9+jhdXU6sxLS0tLS0tLS0tLS0t7Yq1aXJRmde1bFrdd99y3jq6LppG\\/gbtKea6JXYL9w\\/ZpX8vWlpaWlpaWlpaWlpa2jVqw39bTi4aFDunA8+x3DqsoG6eNP5uUw48JdBT5fTjnJeWlpaWlpaWlpaWlpb25drp4Gffeevm37bW2aDuW2lPcxNvXea8tZVd06vS0tLS0tLS0tLS0tLS0v5GbTqgDJd9vsXNoengkKGHUb+PV7933cKbpuyLxn2fcqWlpaWlpaWlpaWlpaVdrfbw5KDULVy6NS215bzDdS3H+eLqOHHuL6xOI39bqzEtLS0tLS0tLS0tLS3tKrWlO6AuuoZDhK7h\\/qsfPvwtXly9LBPmNP\\/2REtLS0tLS0tLS0tLS7tO7a4VPXvVqQzumoahQy1tza2zj8+wMTQkzqn\\/9tCNQTp8bXIRLS0tLS0tLS0tLS0t7au0eQHK8y0qqXIafnSIWe9UQb3GRPr+pcppeuVP6ry0tLS0tLS0tLS0tLS0tF\\/RpgG703f7LuF7y9RTnfc2mlhUUobeisf3bv1obS3Htb3613a+0NLS0tLS0tLS0tLS0r5Gm++cJuVxVm7SAe\\/zXdPbslh8jJ+XOMGoH3+0\\/bBYTEtLS0tLS0tLS0tLS7tSbVl8Z9Mqp6nRt3\\/FW\\/vxIVZOayy\\/lpY4Pyu\\/PvublpaWlpaWlpaWlpaWdmXaMC+on1iUct7prmltuzjTtdHhQpRL7L+9P5l7Ow5aWlpaWlpaWlpaWlpa2t+gXX5n020ODddFQ6m2jUjatVFJqUS7X2boZW45DvOWUt9y+fTOKS0tLS0tLS0tLS0tLe2rtIPJRae5zltSzlvmg+vyoDStt8a1LZuUSPev3i6uTg+pH2+ooaWlpaWlpaWlpaWlpX2tNu3vTDlvnlQ0HZResV\\/bcuwecuxy3TrSPs++aWlpaWlpaWlpaWlpaVel3XUts\\/110X03uvYSc97a0tS+\\/\\/bUjTtaXlzdLn\\/8nS0qtLS0tLS0tLS0tLS0tLSfbw4trbr6fF1LaUs\\/3+OBtSvRtle\\/p0x92XI8pflBTUtLS0tLS0tLS0tLS7t+7WGU65ZYoh00\\/PZ3Toel2ZAwt8Ux\\/cXVWxv1e\\/janVNaWlpaWlpaWlpaWlraV2nDQdPSz\\/JUO8h5z6OW45DzHrtXfJ83h\\/aJc3\\/7lZaWlpaWlpaWlpaWlnZ92qA+zQf2o2o3y\\/m351j83CX1covK9Ir7pTZdWKWlpaWlpaWlpaWlpaVdv\\/YQb3FemzKNsL3EYujUKntb\\/jg17e67Uba9+lwGK1k+r\\/PS0tLS0tLS0tLS0tLS0v4\\/2ros0U7xbGLRF8ceDab2hldejj+ipaWlpaWlpaWlpaWlXZ+2T1ev8YH3ZZ23T1fPJa9rOcXhQ\\/tu\\/Wi4c\\/o2J8z51utUcaalpaWlpaWlpaWlpaVds3ZKU3ex6Dkp761L+FIWC1DagemV+\\/Wjm+H82+HmUFpaWlpaWlpaWlpaWtqVasNBZd6i0l8X7Q8uaVTtsOiZyq6DxLmvkH5vWi8tLS0tLS0tLS0tLS0t7Ze2qIR9nXUU76NroyG5ng5KD2mLY3LLcWs9vnUnDn5MS0tLS0tLS0tLS0tLuzLtoESbct5S5p0vbdDutKal7xa+Pgbt7lqXcD\\/+6K2GHTDnWDS+ptuvtLS0tLS0tLS0tLS0tOvVpiJoS1fv3UHTddHcJZwS52OcZBTKrymB3nc7X2p8SKWlpaWlpaWlpaWlpaVdrXbX0tbTfGCYf1vbwW+LmmoePtS98iZ9Pp9ctF2++id1XlpaWlpaWlpaWlpaWlraL2rDjNwP17b0Sz\\/7EUnD5x\\/jj0ucs7RN+uXJtLS0tLS0tLS0tLS0tOvT9htXBvXdUkoq1S4jDB1KLce1DjaHblPCXOeu4U+6hGlpaWlpaWlpaWlpaWlXoP1gctFpTlMHOW99UvRsCXSonIa7ph\\/+O31655SWlpaWlpaWlpaWlpb25dra\\/SbluKHv9r0rdi4P3MXPsH409N+28Uel5cDnbojukZaWlpaWlpaWlpaWlnad2oF+SlP71tllBfWW0tRe3SqngzJsP7moVVALLS0tLS0tLS0tLS0tLe0f0U4PDndO3+eDBjtfBgc9WRyT75y+zd3Ct+7\\/FVzLN4OWlpaWlpaWlpaWlpb2r2l3yxukp3lUbU0l2trVe1uJtnY7X0KOe4wPuXTqQ5c4f2XnCy0tLS0tLS0tLS0tLe1rteePKqfP0tW+WzgVPXctYT6NlCWuZLklCS0tLS0tLS0tLS0tLe36tal19tRVUIdx7lpnhxdXwxaVNgd3usg62J7SJvDS0tLS0tLS0tLS0tLS0v4Zbc7Q26Ddbfs8j4rF\\/cjfS7f7ZYp9zMy3XZfw7rsZOi0tLS0tLS0tLS0tLe1rtVOJtgwnF72PtGFxTLt7eukS534N6TkWi\\/OPaWlpaWlpaWlpaWlpaderXXYL17iupVdvl42+7cLqswg7X6ZFMueu5fjTh9DS0tLS0tLS0tLS0tK+XFs\\/+m6\\/NSVcF52+0ifMTw7ctL7bfdfEe36SONPS0tLS0tLS0tLS0tLS\\/kArhBBCCCGEEEIIIYQQQgghhBBCCCGEEEIIIYQQQgghhBBCCLHq+F8AAAD\\/\\/7\\/ddgAmBPkKAAAAAElFTkSuQmCC\",\"transaction_id\":null,\"bank_transfer_id\":null,\"financial_institution\":null,\"bank_info\":{\"payer\":{\"id\":null,\"account_id\":null,\"long_name\":null,\"external_account_id\":null,\"identification\":[]},\"collector\":{\"account_id\":null,\"long_name\":null,\"account_holder_name\":\"Eudimaci Barboza de Lira\",\"transfer_account_id\":null},\"is_same_bank_account_owner\":null,\"origin_bank_id\":null,\"origin_wallet_id\":null},\"ticket_url\":\"https:\\/\\/www.mercadopago.com.br\\/payments\\/147124896532\\/ticket?caller_id=3154860678&hash=3a8d4fae-a809-4451-97e7-70bd37bcb448\",\"e2e_id\":null}},\"accounts_info\":null,\"tags\":null,\"refunds\":[]}', NULL, 'pix', 1, NULL, 18, '2026-02-20 20:58:54'),
(20, 11, 'pix_146476110159', '146476110159', 'https://www.mercadopago.com.br/payments/146476110159/ticket?caller_id=3154860678&hash=0058b75b-1fda-4875-bff5-a05a106e1e10', 'processando', '2026-02-21 13:44:26', '2026-02-21 13:44:26', '{\"id\":146476110159,\"acquirer_reconciliation\":[],\"sponsor_id\":null,\"operation_type\":\"regular_payment\",\"order\":[],\"brand_id\":null,\"build_version\":\"3.143.0-rc-4\",\"binary_mode\":false,\"external_reference\":\"142946577853\",\"financing_group\":null,\"status\":\"pending\",\"status_detail\":\"pending_waiting_transfer\",\"store_id\":null,\"taxes_amount\":0,\"date_created\":\"2026-02-21T09:44:26.431-04:00\",\"live_mode\":true,\"date_last_updated\":\"2026-02-21T09:44:26.431-04:00\",\"date_of_expiration\":\"2026-02-22T09:44:26.241-04:00\",\"deduction_schema\":null,\"date_approved\":null,\"money_release_date\":null,\"money_release_schema\":null,\"money_release_status\":\"released\",\"currency_id\":\"BRL\",\"transaction_amount\":20,\"transaction_amount_refunded\":0,\"payer\":{\"type\":null,\"id\":\"3154860678\",\"email\":null,\"identification\":{\"type\":null,\"number\":null},\"first_name\":null,\"last_name\":null,\"entity_type\":null,\"phone\":{\"area_code\":null,\"number\":null,\"extension\":null},\"operator_id\":null},\"collector_id\":260742905,\"counter_currency\":null,\"payment_method_id\":\"pix\",\"payment_method\":{\"id\":\"pix\",\"type\":\"bank_transfer\",\"issuer_id\":\"12501\"},\"payment_type_id\":\"bank_transfer\",\"pos_id\":null,\"transaction_details\":{\"financial_institution\":null,\"net_received_amount\":0,\"total_paid_amount\":20,\"installment_amount\":0,\"overpaid_amount\":0,\"external_resource_url\":null,\"payment_method_reference_id\":null,\"acquirer_reference\":null,\"payable_deferral_period\":null,\"bank_transfer_id\":null,\"transaction_id\":null},\"fee_details\":[],\"differential_pricing_id\":null,\"authorization_code\":null,\"captured\":true,\"card\":[],\"call_for_authorize_id\":null,\"statement_descriptor\":null,\"shipping_amount\":0,\"additional_info\":{\"tracking_id\":\"platform:8|8.3.30,so:so;,type:SDK3.8.0,security:none\"},\"coupon_amount\":0,\"installments\":1,\"description\":\"Inscrição no evento: III CORRIDA SAUIM DE COLEIRA\",\"notification_url\":\"https:\\/\\/www.movamazon.com.br\\/api\\/mercadolivre\\/webhook.php\",\"issuer_id\":\"12501\",\"processing_mode\":\"aggregator\",\"merchant_account_id\":null,\"merchant_number\":null,\"metadata\":[],\"callback_url\":null,\"release_info\":null,\"marketplace_owner\":null,\"integrator_id\":null,\"corporation_id\":null,\"platform_id\":null,\"point_of_interaction\":{\"type\":\"OPENPLATFORM\",\"application_data\":{\"name\":null,\"version\":null},\"transaction_data\":{\"qr_code\":\"00020126580014br.gov.bcb.pix0136fb6df74b-f82a-425b-92ab-55459eab8d69520400005303986540520.005802BR5915EUDIMACIBARBOZA6009Sao Paulo62250521mpqrinter14647611015963041214\",\"qr_code_base64\":\"iVBORw0KGgoAAAANSUhEUgAABWQAAAVkAQMAAABpQ4TyAAAABlBMVEX\\/\\/\\/8AAABVwtN+AAAKrklEQVR42uzdQXIiSbIG4MRYsNQROApHQ0fjKByBpRYycqw0JOHuEQhVt7qUZfb9m+p6r8n8snc+7uExiYiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiMh\\/m93c5TRtbv90naaXeX799e+9\\/Pr7e\\/v7R7Yf\\/9bHP+5\\/\\/bn79eNpnt+m6XB\\/WM7x10M+\\/pzTj2toaWlpaWlpaWlpaWlpab9Bey5\\/X15wuP\\/02rRJub39eNde8PGjt8c19vH+kOUTw59TezMtLS0tLS0tLS0tLS3terWt0ly0L7Fc3czz5faij5p3UW5bjXqOL1oK6Uv75NcnNW\\/70RstLS0tLS0tLS0tLS3t363NndLlxYsy\\/dkK5mt7SGq\\/LnkvNS8tLS0tLS0tLS0tLS3tX6r9f407jbSXUus+zjWpX2PhPOyc0tLS0tLS0tLS0tLS0tL+F9oyLbwpU8Lz7UVLyzb0ec\\/lIYf71PC1bxbXnIv6n80209LS0tLS0tLS0tLS0v5J7XBz0eVBuRqmhVurdimc39q08O8+5N\\/tWaKlpaWlpaWlpaWlpaX9M9pPcrg1P6d7J3XbPzBo05nTUzcdfO3PnC7a7wgtLS0tLS0tLS0tLS3tn9HuR6Ozoel5jP+vS3vRgx8v+29zzdu0+czpou3XH9HS0tLS0tLS0tLS0tKuT7tLS4f6F9W8xM1F+UXt9pQ0xLsM7W6a8iVq8zxuWn9ES0tLS0tLS0tLS0tLS\\/vvtOnSz7ls602Ldt\\/b31Nx\\/Zb+LNePLpX5td\\/WO8Xm8VsvoqWlpaWlpaWlpaWlpV2f9jx64RRbtGFj0VyOiz4vnEPNG86ctk9dat\\/0ZlpaWlpaWlpaWlpaWtq1aud2h0nZZBTOnKYm6HLWdNs3PdNDBtPCfbZpA+9X9izR0tLS0tLS0tLS0tLS\\/pR2GlWay6aievHJe\\/+CweaiMkK7aZ84Pdh\\/Ozj9SktLS0tLS0tLS0tLS0v7DdrQZU37lA5dhZ6Piy4VelIvg7+H+0PytaNzt2dpsKTpQEtLS0tLS0tLS0tLS7tO7VKu7tqg77OytdW82zZqvI8HVwcjx+ngal0BfC5zy7S0tLS0tLS0tLS0tLSr1rbjomlz0SYdF51i83NfHhi0aeS47cEN08LpzGkYNf7tbb20tLS0tLS0tLS0tLS0f1YbOqeHWLZe4oOvfbm61LxzGZnth3hzjveHvJdPzdU3LS0tLS0tLS0tLS0tLe03aNO1LXPUzaUyz1t7++ta0sPqgdX6yWFaODSLn1botLS0tLS0tLS0tLS0tD+o3aUX3mrfvGD3eG\\/RDu582T\\/QLgdW682hbWp4cPb0PD0JLS0tLS0tLS0tLS0t7c9qg\\/oU9anpOb7zZe7K1lCuDg+uXm6fPNxk9NY6qCdaWlpaWlpaWlpaWlraNWuHNe8p3qYS5nFf4+aiuWjnOMSbOqahcxo6qJ9OAtPS0tLS0tLS0tLS0tKuVLu8aNeXqcfY5EynOKe0wajfexvUr\\/FhqaDelk\\/dtU+mpaWlpaWlpaWlpaWlpf0e7Wk0Lfxye1G48+V4r8jzwt32ol0s73OO3Z0v78P\\/rYCWlpaWlpaWlpaWlpZ2pdowo5vGd0\\/3vm4oT8O08PBhh\\/iES3\\/mdPm\\/H++fGg6uDg6w0tLS0tLS0tLS0tLS0q5Ju2vbZ\\/vVtXlz0bGcOZ3ue3DDJ7dP3SRtrXmnuP4oSeZPOqe0tLS0tLS0tLS0tLS0a9D25erywrl0Tgc1cM1NPViiO7jQM\\/3y\\/KxzSktLS0tLS0tLS0tLS0v7dW2YFm51cd2rtHmwYHdwXcuhtGoP977uNd318vqwQqelpaWlpaWlpaWlpaX9e7T1uGg6e5pbtcMXHe7beq\\/lHZs2JRwetnzy8CG0tLS0tLS0tLS0tLS0K9PuRltn89Kh471snR+cOR20X9OFMbXtGjqo6ZOf7L2lpaWlpaWlpaWlpaWlXYd2qTT7pUNh2dDHvO1ldGy0zt9Oox\\/XQvo9Vdtp\\/vZJzUtLS0tLS0tLS0tLS0tL+0Vt3W5UL\\/2cu5btlIrrNC2c\\/hPMcTvvtT+wem4\\/Xrb1Pq3QaWlpaWlpaWlpaWlpaX9QO5WbVnbtrpdTGfRt5WrVhZHjU+z3Xso1pOnm0PfhtDAtLS0tLS0tLS0tLS3tyrVTe0GqeZNubs3O\\/lPncnHM3A6qnrqR43DmdN\\/WH6VPnr9Q+9LS0tLS0tLS0tLS0tL+jDbUvOm46KG7ACWfPV1+XI+LtutHL\\/3+27r+KAn2991JEy0tLS0tLS0tLS0tLe0atbtyEPOtn7s9fmX+du5uT6m3pkz9kdC5nOY8P6t5aWlpaWlpaWlpaWlpaWm\\/rg2t2lMc+H15vHC3tWa35cG7x59cVwDXT64SWlpaWlpaWlpaWlpa2vVqU3c1bS66jF60LB167\\/u8h3vtu0lnTl\\/LS4+lxi2dZlpaWlpaWlpaWlpaWtpVauuq2rlXt2nhcHtKmhYe1rzhzOlLO4M63zcXzaVzSktLS0tLS0tLS0tLS\\/tXaOvW2VrzHkd3cabO6a6UrW9l\\/+0U26\\/b9rB\\/UvPS0tLS0tLS0tLS0tLS0v6WtkwLhxVJ84Njo+c46LtL23rbJ4eR43pw9dg9ZEqfSktLS0tLS0tLS0tLS7tG7VTK1OHyoWXgd1zzLot2U83bZ5MK5zpyXB9CS0tLS0tLS0tLS0tLu1Ltvls2NNgfNLiuJV36uW9nTtOPTvHA6vAhg2tHT7S0tLS0tLS0tLS0tLTr1C7zt3X50KU0Oevf51vNe46fPDy4Gtquw87p3IZ4z7S0tLS0tLS0tLS0tLS0369diutdu9yzv7Zl246Npj1LgxelH7\\/E73jvy\\/zUcaalpaWlpaWlpaWlpaVdr7aWq3VWN00LT0md+rzLpydtuHY0betNq3\\/zfzdaWlpaWlpaWlpaWlraNWt3z2d1j\\/c9uPlF7bjonNqvafT4dJ8SDmdOw6emO1\\/2n28soqWlpaWlpaWlpaWlpV2BNqQ0PZfR2cHSobk8eD+6dvQltl03qXPaOqV5c1EqnGlpaWlpaWlpaWlpaWlXph3olzL1NIVbUy6xkxqWDqW7OOspzpfYMX10i0rIefrN0NLS0tLS0tLS0tLS0tL+ZjbtAculn2nAd5s2F6UXtYOr1\\/bJ4aGvnTZfP0pLS0tLS0tLS0tLS0u7Zu2wXN2UGjessF1uEB2Uq+XimLqxaHBw9Rwvjhl0nGlpaWlpaWlpaWlpaWnXpz13ndNN2X9bp4c\\/echhNDU8tU9\\/vY0cD2veeXT9KC0tLS0tLS0tLS0tLe3atGmU9nRrcp7i6tpwB+eHdh\\/L01qu7tqPDqPh3VD71v8UT0\\/I0tLS0tLS0tLS0tLS0tL+S+2lrUr6dNHusiJp6vq8QTndRo7r6t9wc2h6yERLS0tLS0tLS0tLS0v7d2vnUq7u57x4N6j7A6yXrxx03X0yJUxLS0tLS0tLS0tLS0u7Jm0\\/LTyXlbXp7GnYXJQ6p0vN+9bf+XK8f\\/q21by1cN5\\/rXNKS0tLS0tLS0tLS0tL+4PafnPRVJYOzenm0HQBSvqz1rx1\\/22Yw01DvMuP91+oeWlpaWlpaWlpaWlpaWlpv6gVERERERERERERERERERERERERERERERERERERWXX+FwAA\\/\\/8\\/qXNJd4LevwAAAABJRU5ErkJggg==\",\"transaction_id\":null,\"bank_transfer_id\":null,\"financial_institution\":null,\"bank_info\":{\"payer\":{\"id\":null,\"account_id\":null,\"long_name\":null,\"external_account_id\":null,\"identification\":[]},\"collector\":{\"account_id\":null,\"long_name\":null,\"account_holder_name\":\"Eudimaci Barboza de Lira\",\"transfer_account_id\":null},\"is_same_bank_account_owner\":null,\"origin_bank_id\":null,\"origin_wallet_id\":null},\"ticket_url\":\"https:\\/\\/www.mercadopago.com.br\\/payments\\/146476110159\\/ticket?caller_id=3154860678&hash=0058b75b-1fda-4875-bff5-a05a106e1e10\",\"e2e_id\":null}},\"accounts_info\":null,\"tags\":null,\"refunds\":[]}', NULL, 'pix', 1, NULL, 18, '2026-02-21 10:44:26'),
(21, 18, 'pix_146514478969', '146514478969', 'https://www.mercadopago.com.br/payments/146514478969/ticket?caller_id=3206743703&hash=9218d50d-dce5-46d6-8ac8-a4dde61dedbe', 'processando', '2026-02-21 18:30:30', '2026-02-21 18:30:30', '{\"id\":146514478969,\"acquirer_reconciliation\":[],\"sponsor_id\":null,\"operation_type\":\"regular_payment\",\"order\":[],\"brand_id\":null,\"build_version\":\"3.143.0-rc-4\",\"binary_mode\":false,\"external_reference\":\"MOVAMAZON_1771698595_29\",\"financing_group\":null,\"status\":\"pending\",\"status_detail\":\"pending_waiting_transfer\",\"store_id\":null,\"taxes_amount\":0,\"date_created\":\"2026-02-21T14:30:29.563-04:00\",\"live_mode\":true,\"date_last_updated\":\"2026-02-21T14:30:29.563-04:00\",\"date_of_expiration\":\"2026-02-22T14:30:29.357-04:00\",\"deduction_schema\":null,\"date_approved\":null,\"money_release_date\":null,\"money_release_schema\":null,\"money_release_status\":\"released\",\"currency_id\":\"BRL\",\"transaction_amount\":21,\"transaction_amount_refunded\":0,\"payer\":{\"type\":null,\"id\":\"3206743703\",\"email\":null,\"identification\":{\"type\":null,\"number\":null},\"first_name\":null,\"last_name\":null,\"entity_type\":null,\"phone\":{\"area_code\":null,\"number\":null,\"extension\":null},\"operator_id\":null},\"collector_id\":260742905,\"counter_currency\":null,\"payment_method_id\":\"pix\",\"payment_method\":{\"id\":\"pix\",\"type\":\"bank_transfer\",\"issuer_id\":\"12501\"},\"payment_type_id\":\"bank_transfer\",\"pos_id\":null,\"transaction_details\":{\"financial_institution\":null,\"net_received_amount\":0,\"total_paid_amount\":21,\"installment_amount\":0,\"overpaid_amount\":0,\"external_resource_url\":null,\"payment_method_reference_id\":null,\"acquirer_reference\":null,\"payable_deferral_period\":null,\"bank_transfer_id\":null,\"transaction_id\":null},\"fee_details\":[],\"differential_pricing_id\":null,\"authorization_code\":null,\"captured\":true,\"card\":[],\"call_for_authorize_id\":null,\"statement_descriptor\":null,\"shipping_amount\":0,\"additional_info\":{\"tracking_id\":\"platform:8|8.3.30,so:so;,type:SDK3.8.0,security:none\"},\"coupon_amount\":0,\"installments\":1,\"description\":\"Inscrição no evento: III CORRIDA SAUIM DE COLEIRA\",\"notification_url\":\"https:\\/\\/www.movamazon.com.br\\/api\\/mercadolivre\\/webhook.php\",\"issuer_id\":\"12501\",\"processing_mode\":\"aggregator\",\"merchant_account_id\":null,\"merchant_number\":null,\"metadata\":[],\"callback_url\":null,\"release_info\":null,\"marketplace_owner\":null,\"integrator_id\":null,\"corporation_id\":null,\"platform_id\":null,\"point_of_interaction\":{\"type\":\"OPENPLATFORM\",\"application_data\":{\"name\":null,\"version\":null},\"transaction_data\":{\"qr_code\":\"00020126580014br.gov.bcb.pix0136fb6df74b-f82a-425b-92ab-55459eab8d69520400005303986540521.005802BR5915EUDIMACIBARBOZA6009Sao Paulo62250521mpqrinter1465144789696304D4AB\",\"qr_code_base64\":\"iVBORw0KGgoAAAANSUhEUgAABWQAAAVkAQMAAABpQ4TyAAAABlBMVEX\\/\\/\\/8AAABVwtN+AAAKuklEQVR42uzdTY7iSBAGUCMWLDkCR+FoVUfjKByBJYsSHnUNJiMi7WpK\\/YNbet8G9ajAz7MLRWTkICIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiJ\\/Nruxy2nYpM\\/3H5+XH3+8vf\\/FxzAc2t9\\/\\/sh5GO5fenwOw759fuYyDG8\\/vrz\\/8aPb+5eH9GPtR2lpaWlpaWlpaWlpaWlpf1l7Lv9uD7ilz88HXO5\\/d2jq9Mqf2uuPv9jc\\/8vt\\/o3p1UMOTZtQR1paWlpaWlpaWlpaWto1a1P5mmreS3zQ9l62jn3te74rj48fCbVuysf9ybnmba98paWlpaWlpaWlpaWlpf3ntJfP75SydZsemGrecyuY7w+8TeVrX+vuY8FMS0tLS0tLS0tLS0tL+49rw+js0Grc904bvrQ0Ont8zN2OswUzLS0tLS0tLS0tLS0tLe2f1ZZp4TroG9TTsdGgnfq8qSJvo8ZjO7gaKvW3+KVfm22mpaWlpaWlpaWlpaWl\\/Zva2c1Fl3urdt82F6WlQ+e+z1umhb\\/3I7+2Z4mWlpaWlpaWlpaWlpb272hnU8+aTmXqRypXW627SzVvObha269Df+b010JLS0tLS0tLS0tLS0v7N7WHNnfbas5LPzo7dA\\/ajvMJZ07rq6fNRef4xOu99v1q\\/paWlpaWlpaWlpaWlpb2tdrd8sHLYxydne7inFWHzUWp\\/RpefVj8kaFc7PlVaGlpaWlpaWlpaWlpaWmf1y5NDYcHpLOm+9aqbcV2eOVjrND3bQ3S+xC29O7L9t5vh5aWlpaWlpaWlpaWlvY12l3ae1uuawmbi\\/bl5tB0XPSaVtimM6f9CPI2rT9q\\/d7r\\/X9BGDGmpaWlpaWlpaWlpaWlXZl2Kl+vpfa9zP3Ztt2mUrXLbdg6NTy0vbjnYUgjx+H6UVpaWlpaWlpaWlpaWtr1amcfMH2+labqezwuGjYXTeVqP39bb1GZOqfp9pQ8xEtLS0tLS0tLS0tLS0tL+xu06cTnrg34nh7TwrfW502rksIDD2VaOFXiodx\\/m3vloHtmKxQtLS0tLS0tLS0tLS3ta7VpWnh4XNdS73wZU0u2V+8WXrne9RJGjmfnlkdaWlpaWlpaWlpaWlradWrrmdO6uegzUwf1I00NT9rUQW0P2iwcH837b6eR49Z+febOF1paWlpaWlpaWlpaWtqXac\\/xzOlM3roNRuHBMyOzrXBOV7EMP39VWlpaWlpaWlpaWlpaWtrfqA05Ltz5MjxatduFB1\\/TyHEr6\\/dz5f5HGT0eU3\\/36RtEaWlpaWlpaWlpaWlpaV+jPZQZ3Xbp56boa627LQ8K5er06qc4ahyuH62jxu2Vr1\\/\\/P6WlpaWlpaWlpaWlpaV9rXa25g3Kt6hNq2trzXvtlan9Gq4fbW3WUPN+584XWlpaWlpaWlpaWlpa2hdoQ7masm\\/Lh8bH\\/G1YOpT24KaCeUwHVevB1TR\\/e2hrkHo9LS0tLS0tLS0tLS0t7fq0+QKUOgW7cAFKvj0lPbDWvOlCzy86p0\\/fGEpLS0tLS0tLS0tLS0tL+6S2busNS4d+3qL9aOqhtWjv\\/d5bX7Hv+z5yGzl+Rk1LS0tLS0tLS0tLS0v7Wu3QD\\/qO3b7bTXrA+\\/3Mab90aPqR6fPSDrDWPm\\/7kflmMS0tLS0tLS0tLS0tLe1KtUN3d8lm4ebQ0EmtHdOwdCi92ikWzun60aEUzLWHS0tLS0tLS0tLS0tLS7s+7S5dp5nmbo+PjukmnTV977T1ApRd+ZHQfu333s6HlpaWlpaWlpaWlpaWlvY3aPu\\/2aSbQ\\/u9SuHSz6nfO3vmdN9X6K15vF1Y1vTMtl5aWlpaWlpaWlpaWlra12hnNhdNN4a272zKzaFTufqRCufSLA5fDoV02mCUDq5++SO0tLS0tLS0tLS0tLS0a9H2N63kmnd4lK3TtHBeNtTXvGH90bHUuuOcdrn6pqWlpaWlpaWlpaWlpV2VdldGZmfmb8cyOvsW\\/+5czpiOUZtyK0\\/e9l8+PzbwDrS0tLS0tLS0tLS0tLS0v0c79XXbdS37\\/kFLU8OtRZvOnN5SuV\\/7u63DvE0jx7S0tLS0tLS0tLS0tLRr1s7sDzo9Wra3MvA7s3To3DWJh9LXvZWLY+rB1Y+26vfwdTealpaWlpaWlpaWlpaW9tXa3dygb\\/j3pWj39\\/23tfm5VPMe41nTsW0sSj8SRo+nL9HS0tLS0tLS0tLS0tKuURvUp8cDb211bb1F5fKTyz9TOzZdxZL33k7adGCVlpaWlpaWlpaWlpaWdv3aQzzFeY1Nz7HfYBROc6bVtdOXj3Fod18uRKnq89BdyfJMn5eWlpaWlpaWlpaWlpaW9nltv2Qoaf\\/PW9xc1Pd1Z0aO09qjma295\\/bKNbS0tLS0tLS0tLS0tLRr1NZyNWwuWurzDrFcPQ\\/z648mVfqRMS7R\\/Wh93UNU7kqhTEtLS0tLS0tLS0tLS7se7TSze436SVtvEN2Wm0PrDaLX\\/ubQoXvluv5o5uZQWlpaWlpaWlpaWlpa2vVqD63SLJuLbmVT0dD\\/e+jv4pyOjS5r932H9DvbemlpaWlpaWlpaWlpaWlpv6+9f27a1PBnS\\/ZWtNs26BseNL1quTgmjxynu17Sq858mZaWlpaWlpaWlpaWlnZl2l2a0W3TwuHm0HpdS1syVKeFr\\/dFu7s2JdwenPu8b+VHWs27++mZU1paWlpaWlpaWlpaWtpXab848Xks+29n73qZHnB+vOo4e+fLWF75vbRd24+MtLS0tLS0tLS0tLS0tKvV7uZGZ29J+xY\\/pwdNx0WTeojNz036XN5clA+uPrdniZaWlpaWlpaWlpaWlpb2Ke35MSU8LF\\/bsjQtHCr06RXT79eDq63MX5oaHmlpaWlpaWlpaWlpaWlXq6193lDzpoW7YWo45dyt+g1be8e5m0OD8hBHj38yJUxLS0tLS0tLS0tLS0u7Au0uPXC28kwd01Dz1ldOP5o6p+na0S\\/+Pz1zQpaWlpaWlpaWlpaWlpb2tdo6fzs8OqbT3O2tKccyd1uPjZ6GmetHw\\/xtPXNaNxe1g6u0tLS0tLS0tLS0tLS069PO6m+zo7PpLer87VjWH42xc1rbsPt+c1HroA60tLS0tLS0tLS0tLS0tH9EO\\/1wOHOa+rtj7PNuZx\\/UpoUvcfR4ahrnzUXj4wxqGDX+XmhpaWlpaWlpaWlpaWn\\/mnbXHyM9PVbV5inh9u88LZzOnrZydXPXjq3PeynqtET3+3e+0NLS0tLS0tLS0tLS0r5Aey7\\/Pj0eVPfe1geN7QG15p2+dJpTpjVIH0lCS0tLS0tLS0tLS0tLu35tf2w0dFDbCtuxzNlux8WcHvqw\\/zYdZJ25PaVt4KWlpaWlpaWlpaWlpaWl\\/TPaXKG3Rbvb9jmW7bxjv3cp9Xnr9aNjd4No3hdMS0tLS0tLS0tLS0tL+89oh1Smvndfmr\\/zpb845hIL5zFtLkrN4enLz+wWpqWlpaWlpaWlpaWlpX25dmFaOJ85fS81bytbw6Wfp2Ep4c6X6SbRpJ3t3dLS0tLS0tLS0tLS0tKuTbs0OpvK06QM87fn+Mr\\/P6j\\/kUk7zd3u5w6s7vpCmpaWlpaWlpaWlpaWlpb2F7QiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiq85\\/AQAA\\/\\/9Od2Tk66ZIIgAAAABJRU5ErkJggg==\",\"transaction_id\":null,\"bank_transfer_id\":null,\"financial_institution\":null,\"bank_info\":{\"payer\":{\"id\":null,\"account_id\":null,\"long_name\":null,\"external_account_id\":null,\"identification\":[]},\"collector\":{\"account_id\":null,\"long_name\":null,\"account_holder_name\":\"Eudimaci Barboza de Lira\",\"transfer_account_id\":null},\"is_same_bank_account_owner\":null,\"origin_bank_id\":null,\"origin_wallet_id\":null},\"ticket_url\":\"https:\\/\\/www.mercadopago.com.br\\/payments\\/146514478969\\/ticket?caller_id=3206743703&hash=9218d50d-dce5-46d6-8ac8-a4dde61dedbe\",\"e2e_id\":null}},\"accounts_info\":null,\"tags\":null,\"refunds\":[]}', NULL, 'pix', 1, NULL, 29, '2026-02-21 15:30:30'),
(22, 18, 'pix_147215024588', '147215024588', 'https://www.mercadopago.com.br/payments/147215024588/ticket?caller_id=3206743703&hash=5a8a7772-ddc9-4e8f-8b49-6a8f72d77a2f', 'processando', '2026-02-21 18:31:27', '2026-02-21 18:31:27', '{\"id\":147215024588,\"acquirer_reconciliation\":[],\"sponsor_id\":null,\"operation_type\":\"regular_payment\",\"order\":[],\"brand_id\":null,\"build_version\":\"3.143.0-rc-4\",\"binary_mode\":false,\"external_reference\":\"MOVAMAZON_1771698595_29\",\"financing_group\":null,\"status\":\"pending\",\"status_detail\":\"pending_waiting_transfer\",\"store_id\":null,\"taxes_amount\":0,\"date_created\":\"2026-02-21T14:31:27.378-04:00\",\"live_mode\":true,\"date_last_updated\":\"2026-02-21T14:31:27.378-04:00\",\"date_of_expiration\":\"2026-02-22T14:31:27.250-04:00\",\"deduction_schema\":null,\"date_approved\":null,\"money_release_date\":null,\"money_release_schema\":null,\"money_release_status\":\"released\",\"currency_id\":\"BRL\",\"transaction_amount\":21,\"transaction_amount_refunded\":0,\"payer\":{\"type\":null,\"id\":\"3206743703\",\"email\":null,\"identification\":{\"type\":null,\"number\":null},\"first_name\":null,\"last_name\":null,\"entity_type\":null,\"phone\":{\"area_code\":null,\"number\":null,\"extension\":null},\"operator_id\":null},\"collector_id\":260742905,\"counter_currency\":null,\"payment_method_id\":\"pix\",\"payment_method\":{\"id\":\"pix\",\"type\":\"bank_transfer\",\"issuer_id\":\"12501\"},\"payment_type_id\":\"bank_transfer\",\"pos_id\":null,\"transaction_details\":{\"financial_institution\":null,\"net_received_amount\":0,\"total_paid_amount\":21,\"installment_amount\":0,\"overpaid_amount\":0,\"external_resource_url\":null,\"payment_method_reference_id\":null,\"acquirer_reference\":null,\"payable_deferral_period\":null,\"bank_transfer_id\":null,\"transaction_id\":null},\"fee_details\":[],\"differential_pricing_id\":null,\"authorization_code\":null,\"captured\":true,\"card\":[],\"call_for_authorize_id\":null,\"statement_descriptor\":null,\"shipping_amount\":0,\"additional_info\":{\"tracking_id\":\"platform:8|8.3.30,so:so;,type:SDK3.8.0,security:none\"},\"coupon_amount\":0,\"installments\":1,\"description\":\"Inscrição no evento: III CORRIDA SAUIM DE COLEIRA\",\"notification_url\":\"https:\\/\\/www.movamazon.com.br\\/api\\/mercadolivre\\/webhook.php\",\"issuer_id\":\"12501\",\"processing_mode\":\"aggregator\",\"merchant_account_id\":null,\"merchant_number\":null,\"metadata\":[],\"callback_url\":null,\"release_info\":null,\"marketplace_owner\":null,\"integrator_id\":null,\"corporation_id\":null,\"platform_id\":null,\"point_of_interaction\":{\"type\":\"OPENPLATFORM\",\"application_data\":{\"name\":null,\"version\":null},\"transaction_data\":{\"qr_code\":\"00020126580014br.gov.bcb.pix0136fb6df74b-f82a-425b-92ab-55459eab8d69520400005303986540521.005802BR5915EUDIMACIBARBOZA6009Sao Paulo62250521mpqrinter14721502458863040E5C\",\"qr_code_base64\":\"iVBORw0KGgoAAAANSUhEUgAABWQAAAVkAQMAAABpQ4TyAAAABlBMVEX\\/\\/\\/8AAABVwtN+AAAK1klEQVR42uzdQXLawBIG4HFpwZIj6CgczT4aR\\/ERWLJwoVfhScx0a4TJe04gVd+\\/cSUx6FN2Xd3TU0RERERERERERERERERERERERERERERERERERETkz2Y3rXK8\\/eNb\\/ctTKe\\/TVyn7afr49U\\/jNJUy1H\\/\\/nL\\/sePv5Nn\\/Z8iWX61e+z3\\/6mD9c5i\\/LoaWlpaWlpaWlpaWlpaX9Ae1n+vOiPczKq\\/o0P+jU\\/vJw\\/fA0netfHX49aFd1ZUM7\\/Xr1sWoD6kBLS0tLS0tLS0tLS0v7ytpaaS7l6unXZy5zjfs2K4f68\\/qhr+tXLDVvVS\\/afa15P24\\/S1BX7VJ9n2lpaWlpaWlpaWlpaWn\\/LW3snIYHnNLnx7bZ2SmYu6\\/crXlpaWlpaWlpaWlpaWlp\\/1FtU\\/PmkdllDrekD22Nzh5uw7vNT1paWlpaWlpaWlpaWlrav6ZN08K5z1uuP6f0oNrfneqXHNqDq\\/v2zGnz4WbU+Admm2lpaWlpaWlpaWlpaWn\\/pna9uag\\/LVw3FzXTwku5ukwLH74ZOd76kv9jzxItLS0tLS0tLS0tLS3tX9PeTZ4W\\/qqd1FDr7kLNm\\/bfltR+7Zw5\\/ZnQ0tLS0tLS0tLS0tLS\\/g3t0rfM5WrOe3ubytiO0Ob52126kmW\\/Vi5XseT2a1imS0tLS0tLS0tLS0tLS\\/t62iZz8\\/Myz9te6r80c7jhGs3tL2muYOmmeeVmEvj+KU5aWlpaWlpaWlpaWlpa2se1u7Rod1eL6imdOa2bi5ZKfKjLhsbVlyzTwlP32tH3jSnhR2abaWlpaWlpaWlpaWlpaZ+lbdThrpdT\\/fm+Wjq0ZKiv+tnWuudaKB\\/bkePOGqRSSlI\\/UqHT0tLS0tLS0tLS0tLSPkeb7y7p3qJySftvp7Q0d+mYNg8sqebNrxhefWxf8Zv\\/U1paWlpaWlpaWlpaWtrnaj97D5pSuVrWTc9yW2F7DnO31z8c2uHd5ZVLupMzP\\/lbNS0tLS0tLS0tLS0tLS3tb2mrsoQFu3Vb79L3HeqepTJf1xIq9KC+1D7vqWprhb6U+UMQzK860dLS0tLS0tLS0tLS0r6itt\\/vTWnK1SltLhrX5WoqnEu4OTQUzov+s\\/Z7aWlpaWlpaWlpaWlpaV9ZGzcXVWB3dW1+wLC+QbTe9fKWCuhmWvjUFsrNxTG\\/uf+WlpaWlpaWlpaWlpaW9m9rS+2Yhq2z+\\/Wln9sf2oUzqBuvnAvnEl49HGSlpaWlpaWlpaWlpaWlpf0BbTxzOhfZl6CtD+pU6M3gb63Id+uR42l158u0XeYf73SjaWlpaWlpaWlpaWlpaZ+oLbXLOrVLh5rrWrb6vOkVp9DXPaQ\\/141FX\\/N\\/QWdb7yNbe2lpaWlpaWlpaWlpaWmfrq2\\/s6u1bn1QbHqGNmt41d32Et1Q8+7rwdWs\\/rbPS0tLS0tLS0tLS0tLS\\/t0bT1z2lz6WdplQ8scblOurod24wHWet1o8yW5gxr2347th2lpaWlpaWlpaWlpaWlfT7sLo7PrZUP7WrF+pNHZcPByTLVwbr9+tLXuqTe8e173cGlpaWlpaWlpaWlpaWlpf0YbHrQM\\/IblQ1\\/r46L5gU2lvt5Y1Fw7+p6uHc3rj2hpaWlpaWlpaWlpaWlfUVvmlu25tm7rsdFLqnmjLminNOgbXrk7LVzqEzvLc2lpaWlpaWlpaWlpaWlfUZtvDj23ysv8S7H5ue6YntOXNTncXvWt+8rhfT9paWlpaWlpaWlpaWlpX1vb+eLc9Ny6CGXayPzqb+lsaax1r53TsffhB0NLS0tLS0tLS0tLS0tLO\\/32tPCyXylPC4czp9dfHtZnTw+tdqrq99uXDfXLxqnZ1rtbN4tpaWlpaWlpaWlpaWlpX0ybb1oJD1rK1Fi+1rtfvtIrn3sPigVzPns6zbVv+JJ7oaWlpaWlpaWlpaWlpX2itvugWK6WEvfg5mOiYenQYZ4ePqQ26\\/s8glz335aqzXPL3+6\\/paWlpaWlpaWlpaWlpX2Otjk22l0ylFfXhqVDX+Hik3U6149O85dsD+9+3zmlpaWlpaWlpaWlpaWlpZ0emxZeEvYslVRcl6qtRfUQiulw5jQcWO30d2uHOY4cf9vnpaWlpaWlpaWlpaWlpX2itlEfV\\/rlQZe0dGhKynNdQnS8XRyTD67GD+c7X2rzePfYBiNaWlpaWlpaWlpaWlrap2m7m4uWZmdW1o5pSZ3TXK7u6wHWj17tO9bR43p36fc1Ly0tLS0tLS0tLS0tLe2ztM2D8uai6dYxbY6LThtnT8OFKOek7XROQ+Ec1MvOJFpaWlpaWlpaWlpaWtrX0zY17\\/I7h9oEnedtm\\/nb0o7QDlU3pqVD6ytY3uotKqd2\\/ja2Xx\\/unNLS0tLS0tLS0tLS0tLS\\/pY2VOalnRYu7YOGuTIf0tKhZuHusb3rJWwyGmqzuOkw15Hj8\\/09S7S0tLS0tLS0tLS0tLRP19Yzp6HmzWdOhzAtXH82A76Hdnq4OXMaXnlKTePP9v+NlpaWlpaWlpaWlpaW9nW1u7R1dteWp03Ts3nAR1vz5gc1D5y1l3prSthgFA+uhsL53mwzLS0tLS0tLS0tLS0t7RO1nbOn6+sz+7eo1NtUdqFzmq5ieQv7bsMQ77hxBQstLS0tLS0tLS0tLS0t7c9ox\\/Z3tvYslar\\/uF32+RWOi9YKPR9gPa0f+t6bFi4P7FmipaWlpaWlpaWlpaWlfa6226JdtPt0XPTULtodapm6bhZfUsGcz5zmG0Nzv5eWlpaWlpaWlpaWlpb2VbW1PJ1SzbvcHLqvX9SdFh7bpblNxzT8+dS7fnSo6of7vLS0tLS0tLS0tLS0tLTP0Tbl6uF2AcolND3f25\\/LKO3YPrCsR2mPN208uDqt5m+HWuOOtLS0tLS0tLS0tLS0tLQ\\/qp16W45CRd4prktbmZ9DizaV+XFp09S+cnda+JttvbS0tLS0tLS0tLS0tLRP1OY+77nt9y5nTDvlaqmDvnnAt2rzWdPO1t719DAtLS0tLS0tLS0tLS3t62rjcdH10qF858upasOg72cqX2v7tT9yHM6ahlc\\/3\\/kPpaWlpaWlpaWlpaWlpX26dkr7gg7p+Gh3\\/+20Hp3t3hy6nfzh5pWPtLS0tLS0tLS0tLS0tK+p7eiPqdlZy9WvtTbUuiGXu53Sj96HP0tzsSctLS0tLS0tLS0tLS0t7c9qr\\/nvoG\\/eXFRuFXs8c7o0i5fyPhxcPa7OmmbtV+g409LS0tLS0tLS0tLS0r6ydrcuVsO0cNh7O9TLP5sHdY+LHnobi07rV61LdGlpaWlpaWlpaWlpaWn\\/Ce3nvc7pMi28T3tvG+VGzXvn5tBTvUUl17yPbC6ipaWlpaWlpaWlpaWlfa423aLy1r34ZD0qO2z8Y7N86HB71TiPG5bodk690tLS0tLS0tLS0tLS0tL+EW0JDwjbepuf6xZtrtCbV\\/5oR43365tDc+eZlpaWlpaWlpaWlpaW9l\\/SdgZ+q3Y5azrUD+WfxzQlXNL1o6V95YdDS0tLS0tLS0tLS0tL+wraNC3cbC6a6v7bcOY0P2hMr7pOvn60uTl03Dj1SktLS0tLS0tLS0tLS\\/t62rubi7aans1x0eu87bhqdi5nTt\\/CK0+9M6djukH0f96zREtLS0tLS0tLS0tLS0srIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIi8o\\/kPwEAAP\\/\\/Ij5qCBWLKAwAAAAASUVORK5CYII=\",\"transaction_id\":null,\"bank_transfer_id\":null,\"financial_institution\":null,\"bank_info\":{\"payer\":{\"id\":null,\"account_id\":null,\"long_name\":null,\"external_account_id\":null,\"identification\":[]},\"collector\":{\"account_id\":null,\"long_name\":null,\"account_holder_name\":\"Eudimaci Barboza de Lira\",\"transfer_account_id\":null},\"is_same_bank_account_owner\":null,\"origin_bank_id\":null,\"origin_wallet_id\":null},\"ticket_url\":\"https:\\/\\/www.mercadopago.com.br\\/payments\\/147215024588\\/ticket?caller_id=3206743703&hash=5a8a7772-ddc9-4e8f-8b49-6a8f72d77a2f\",\"e2e_id\":null}},\"accounts_info\":null,\"tags\":null,\"refunds\":[]}', NULL, 'pix', 1, NULL, 29, '2026-02-21 15:31:27'),
(23, 18, 'pix_147217238134', '147217238134', 'https://www.mercadopago.com.br/payments/147217238134/ticket?caller_id=3206743703&hash=b68bb367-a0f5-413c-a03a-1d7474f30a9f', 'processando', '2026-02-21 18:45:56', '2026-02-21 18:45:56', '{\"id\":147217238134,\"acquirer_reconciliation\":[],\"sponsor_id\":null,\"operation_type\":\"regular_payment\",\"order\":[],\"brand_id\":null,\"build_version\":\"3.143.0-rc-4\",\"binary_mode\":false,\"external_reference\":\"MOVAMAZON_1771698595_29\",\"financing_group\":null,\"status\":\"pending\",\"status_detail\":\"pending_waiting_transfer\",\"store_id\":null,\"taxes_amount\":0,\"date_created\":\"2026-02-21T14:45:56.257-04:00\",\"live_mode\":true,\"date_last_updated\":\"2026-02-21T14:45:56.257-04:00\",\"date_of_expiration\":\"2026-02-22T14:45:56.063-04:00\",\"deduction_schema\":null,\"date_approved\":null,\"money_release_date\":null,\"money_release_schema\":null,\"money_release_status\":\"released\",\"currency_id\":\"BRL\",\"transaction_amount\":21,\"transaction_amount_refunded\":0,\"payer\":{\"type\":null,\"id\":\"3206743703\",\"email\":null,\"identification\":{\"type\":null,\"number\":null},\"first_name\":null,\"last_name\":null,\"entity_type\":null,\"phone\":{\"area_code\":null,\"number\":null,\"extension\":null},\"operator_id\":null},\"collector_id\":260742905,\"counter_currency\":null,\"payment_method_id\":\"pix\",\"payment_method\":{\"id\":\"pix\",\"type\":\"bank_transfer\",\"issuer_id\":\"12501\"},\"payment_type_id\":\"bank_transfer\",\"pos_id\":null,\"transaction_details\":{\"financial_institution\":null,\"net_received_amount\":0,\"total_paid_amount\":21,\"installment_amount\":0,\"overpaid_amount\":0,\"external_resource_url\":null,\"payment_method_reference_id\":null,\"acquirer_reference\":null,\"payable_deferral_period\":null,\"bank_transfer_id\":null,\"transaction_id\":null},\"fee_details\":[],\"differential_pricing_id\":null,\"authorization_code\":null,\"captured\":true,\"card\":[],\"call_for_authorize_id\":null,\"statement_descriptor\":null,\"shipping_amount\":0,\"additional_info\":{\"tracking_id\":\"platform:8|8.3.30,so:so;,type:SDK3.8.0,security:none\"},\"coupon_amount\":0,\"installments\":1,\"description\":\"Inscrição no evento: III CORRIDA SAUIM DE COLEIRA\",\"notification_url\":\"https:\\/\\/www.movamazon.com.br\\/api\\/mercadolivre\\/webhook.php\",\"issuer_id\":\"12501\",\"processing_mode\":\"aggregator\",\"merchant_account_id\":null,\"merchant_number\":null,\"metadata\":[],\"callback_url\":null,\"release_info\":null,\"marketplace_owner\":null,\"integrator_id\":null,\"corporation_id\":null,\"platform_id\":null,\"point_of_interaction\":{\"type\":\"OPENPLATFORM\",\"application_data\":{\"name\":null,\"version\":null},\"transaction_data\":{\"qr_code\":\"00020126580014br.gov.bcb.pix0136fb6df74b-f82a-425b-92ab-55459eab8d69520400005303986540521.005802BR5915EUDIMACIBARBOZA6009Sao Paulo62250521mpqrinter147217238134630403BE\",\"qr_code_base64\":\"iVBORw0KGgoAAAANSUhEUgAABWQAAAVkAQMAAABpQ4TyAAAABlBMVEX\\/\\/\\/8AAABVwtN+AAAKnklEQVR42uzdQXLiuhYGYFEMGLKELIWlwdJYCkvIkAGFX908C+lIMpDbfQOp+v4JTRe2P2d2SkdHSURERERERERERERERERERERERERERERERERERET+22ymLse0mj9TSttpOvzz\\/XP+\\/df3dfj9101OKeWLwsX58yufKe2nS7jJ100\\/egQtLS0tLS0tLS0tLS0t7V\\/Qnprvx\\/mfu9sDruEBh9sDLl+f4ZW\\/tOd\\/frGa\\/+c6325VLs75KNqA2tHS0tLS0tLS0tLS0tK+szaUr8e5xt3dlPlBY+1XTrNyd7tJVeuGXOYnx5q3vPKZlpaWlpaWlpaWlpaW9tdpQ67hkv38wLs1726+aDf\\/f6h1t3XBTEtLS0tLS0tLS0tLS\\/vLtfmG11kXH1i01UVLrbNl+bX6nGhpaWlpaWlpaWlpaWlpf0rbdAuvFirzattopc3rvKEin+ZF4un2qqvmlauL\\/qy3mZaWlpaWlpaWlpaWlvYntcPJRW23cDt06NSs8\\/bdwt+7yZ\\/NWaKlpaWlpaWlpaWlpaX9Ge0wed7tarhiur\\/tMc1l6ybUvKFwPtY1blvzfk9FS0tLS0tLS0tLS0tL+w7aj9J3W2rOz751tiQPHVpP41R7TttXD5OLTvUTz3Pte6\\/\\/lpaWlpaWlpaWlpaWlva12s3yxstd3Tr7OT9gqN6U2jcsv1avnpqb7G\\/qeBbn\\/YKXlpaWlpaWlpaWlpaWlvZ57f+T6+LhA8Je021Zqi3FduwWLhX6toxBOqTU7EH996GlpaWlpaWlpaWlpaV9rbas84bjWqrJRXmb6GfdLXwpS7Np4fjRvgW5rXnX5VXzTY60tLS0tLS0tLS0tLS076lty9VQ88aUbaPrXvuxeIpK2zVc1b6nsvwaNrDS0tLS0tLS0tLS0tLSvqm2OsNkd\\/uMNW\\/Zc7oeapu+29T337anqOSV1HB6SmzipaWlpaWlpaWlpaWlpaX9O9rmzJc0N\\/rmbuFrWZKtRiWVojqFEUl5ybZU4p\\/3yvxY1j8zFYqWlpaWlpaWlpaWlpb2tdqm0lyFsrVsD22XZAeFc6qXarcLN6lajsNFUyehpaWlpaWlpaWlpaWlfStt\\/E0\\/uSik3SY6qH1LVgvbR+MQ3bD8enpU89LS0tLS0tLS0tLS0tK+XPvoN+Hk0NA6u+7L1F1dOIejWNKD5VdaWlpaWlpaWlpaWlpa2r+rjVk47LM6+2X44GrO0vFW9l+bbuFVUebRv1M9nGnTfKelpaWlpaWlpaWlpaV9S+1HV7ZeQ6NvfuB+YZ23XyRuN7Cm\\/vjRcnFqRv6e7\\/9BaWlpaWlpaWlpaWlpaV+rzdmNntae\\/bK9V\\/PeedB+\\/hMcum7hquZ95swXWlpaWlpaWlpaWlpa2hdqN+WBreo40lUrqO1NFoYO5cJ5MET3YzQG6fHkIlpaWlpaWlpaWlpaWtpXaduzODfLNW\\/ov80XhQdW6uOtkL4+tXL6UElLS0tLS0tLS0tLS0tL+01tfMBcqa8a5dIS7WV41ss8fOjaLB5Xe1DD3tL1QrlPS0tLS0tLS0tLS0tL+37aasdnuHHoDo5nvxxue07boUP5Jvnzs2xgbdd5D4sbVx\\/Mv6WlpaWlpaWlpaWlpaV9rTZ1Z5esmpG1cfFzacU0txwvTC7KN7n0a7bfXEGlpaWlpaWlpaWlpaWlfZV20zyw6rvd3VZMB6eoVNp+MXTT3KRafu3n3t4ZmktLS0tLS0tLS0tLS0tL+2fa8KC2Qq+UU33my9RU6KfbHtPqgdu+Qi+Lx+vmpneHNdHS0tLS0tLS0tLS0tK+hXYwuSifGJo\\/D90e1Dxo91LWdU\\/dYnEqr14V0mGCUdi4umlakGlpaWlpaWlpaWlpaWnfUpu638Satyhjt3Bo8A01bzX+aNfUutNIu1x909LS0tLS0tLS0tLS0r6VdtO0zIZUQ4dy62w7wnbQf1u07c1C1v3Fp9sE3kRLS0tLS0tLS0tLS0tL+3e0ucG3HNeyfTAiKXQNn5syf1PrVsP13VLeV2paWlpaWlpaWlpaWlraN9cO5gcd60bfMLX3Mm8XbScXnfvBu2Vd99ocHNNuXL2UU2c+7q9G09LS0tLS0tLS0tLS0r5aO2z0jUOHeu16vmgdJhcNa95dvdd0KhOLDk3hHFqQd4\\/OfKGlpaWlpaWlpaWlpaV9jbZSH28PvIY+20Oz+NkWzMvLseEoljj3NmvLEF1aWlpaWlpaWlpaWlraX6H9qHdxtjXvtpl\\/u60fdOkv3jVbQI\\/NSmpQD3ZxPrPOS0tLS0tLS0tLS0tLS0v7HW049DMP2p36s17y1N5+XXfQcrxtxh+FLuJ8k0vfr\\/zkCTW0tLS0tLS0tLS0tLS0P61ty9VqbtCu3i4a1nkH3cLt+KNh4VztOd3Xx44G5aYplGlpaWlpaWlpaWlpaWnfUlu6hleh9i0TjNblc5pH11bbRZtXbpdbV8P5t8OTQ2lpaWlpaWlpaWlpaWnfVJtbac\\/hs6l5By204eLBomdYdh0Uzvvbj6ujWJ6Z1ktLS0tLS0tLS0tLS0tL+\\/wpKqEurir06XZi6LXRrkujbypnvoQTRI+3V26HM63D9\\/L4wcW0tLS0tLS0tLS0tLS0b6Yd1Ly7etBu6o9rKUOG2m7h8zxod1O6hNtjSJdajkvNu7m\\/Q5aWlpaWlpaWlpaWlpb2tdqPbsdnO7lo3OhbPjflgbt6klF15stUv\\/Jnv+e0P36UlpaWlpaWlpaWlpaW9v20m9AyGxY7d3Wr7Gdd+1bbRYM61Yufq\\/A5nFz00W9cfWZyES0tLS0tLS0tLS0tLS3tM9r4mwfHtlz6buGqQg+vvLRxtZT566Dvn0xLS0tLS0tLS0tLS0v7rtpm6NA1NPimlMJSbZ9zc\\/jnuRxD2p8cWik\\/6tbjxz3NtLS0tLS0tLS0tLS0tK\\/WVo2+S5XnfqHm7V\\/5zsEx+26C0eDv9HDPKS0tLS0tLS0tLS0tLe3LtVNzTahx25NDp7rvdl22iZ7q0bWbUuu2\\/bd5\\/FE5iiVOLiobV2lpaWlpaWlpaWlpaWnfTzvUX\\/vW2eqz6C4Lrxy+j5dhD\\/WWzzBUN9HS0tLS0tLS0tLS0tLS\\/ifafONqz2kZOjSFIjvVD6weNDw4Jt0WjePkov6Vz+mboaWlpaWlpaWlpaWlpf0x7abfQXrsdO0S7TrUvKUGDku01cEx+SafjToM0X3+zBdaWlpaWlpaWlpaWlraF2pPzfdycuh1vvEUGnz3zekp\\/aLnphTMx5GyummoeWlpaWlpaWlpaWlpaWl\\/hTa0zh6bFdTQdzvV\\/bd35uGmm\\/7aHMWSD\\/YcnJ7SngpKS0tLS0tLS0tLS0tLS\\/u3tYMKPc9ZquYt3S3zp1u3cLv3NJXRvylVw5vO4WJaWlpaWlpaWlpaWlraX6NNoUw9dBeNz3wJ33dTNep3KvqU4vijslicT505\\/4sKnZaWlpaWlpaWlpaWlvYntQvdwlNYOT00NW8pW8PFS6k2ruaTRIO2PTiGlpaWlpaWlpaWlpaW9i21\\/eSi1CxyTo0yF8i59t2Uz+PiA1el73bb6E519f3t+be0tLS0tLS0tLS0tLS0tCIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiK\\/L\\/8LAAD\\/\\/5hhMdZkp3B2AAAAAElFTkSuQmCC\",\"transaction_id\":null,\"bank_transfer_id\":null,\"financial_institution\":null,\"bank_info\":{\"payer\":{\"id\":null,\"account_id\":null,\"long_name\":null,\"external_account_id\":null,\"identification\":[]},\"collector\":{\"account_id\":null,\"long_name\":null,\"account_holder_name\":\"Eudimaci Barboza de Lira\",\"transfer_account_id\":null},\"is_same_bank_account_owner\":null,\"origin_bank_id\":null,\"origin_wallet_id\":null},\"ticket_url\":\"https:\\/\\/www.mercadopago.com.br\\/payments\\/147217238134\\/ticket?caller_id=3206743703&hash=b68bb367-a0f5-413c-a03a-1d7474f30a9f\",\"e2e_id\":null}},\"accounts_info\":null,\"tags\":null,\"refunds\":[]}', NULL, 'pix', 1, NULL, 29, '2026-02-21 15:45:56');
INSERT INTO `pagamentos_ml` (`id`, `inscricao_id`, `preference_id`, `payment_id`, `init_point`, `status`, `data_criacao`, `data_atualizacao`, `dados_pagamento`, `valor_pago`, `metodo_pagamento`, `parcelas`, `taxa_ml`, `user_id`, `created`) VALUES
(24, 18, 'pix_146515063955', '146515063955', 'https://www.mercadopago.com.br/payments/146515063955/ticket?caller_id=3206743703&hash=155c8732-045f-4b9b-91ed-1fb975d6faf4', 'processando', '2026-02-21 18:46:08', '2026-02-21 18:46:08', '{\"id\":146515063955,\"acquirer_reconciliation\":[],\"sponsor_id\":null,\"operation_type\":\"regular_payment\",\"order\":[],\"brand_id\":null,\"build_version\":\"3.143.0-rc-4\",\"binary_mode\":false,\"external_reference\":\"MOVAMAZON_1771698595_29\",\"financing_group\":null,\"status\":\"pending\",\"status_detail\":\"pending_waiting_transfer\",\"store_id\":null,\"taxes_amount\":0,\"date_created\":\"2026-02-21T14:46:08.560-04:00\",\"live_mode\":true,\"date_last_updated\":\"2026-02-21T14:46:08.560-04:00\",\"date_of_expiration\":\"2026-02-22T14:46:08.424-04:00\",\"deduction_schema\":null,\"date_approved\":null,\"money_release_date\":null,\"money_release_schema\":null,\"money_release_status\":\"released\",\"currency_id\":\"BRL\",\"transaction_amount\":21,\"transaction_amount_refunded\":0,\"payer\":{\"type\":null,\"id\":\"3206743703\",\"email\":null,\"identification\":{\"type\":null,\"number\":null},\"first_name\":null,\"last_name\":null,\"entity_type\":null,\"phone\":{\"area_code\":null,\"number\":null,\"extension\":null},\"operator_id\":null},\"collector_id\":260742905,\"counter_currency\":null,\"payment_method_id\":\"pix\",\"payment_method\":{\"id\":\"pix\",\"type\":\"bank_transfer\",\"issuer_id\":\"12501\"},\"payment_type_id\":\"bank_transfer\",\"pos_id\":null,\"transaction_details\":{\"financial_institution\":null,\"net_received_amount\":0,\"total_paid_amount\":21,\"installment_amount\":0,\"overpaid_amount\":0,\"external_resource_url\":null,\"payment_method_reference_id\":null,\"acquirer_reference\":null,\"payable_deferral_period\":null,\"bank_transfer_id\":null,\"transaction_id\":null},\"fee_details\":[],\"differential_pricing_id\":null,\"authorization_code\":null,\"captured\":true,\"card\":[],\"call_for_authorize_id\":null,\"statement_descriptor\":null,\"shipping_amount\":0,\"additional_info\":{\"tracking_id\":\"platform:8|8.3.30,so:so;,type:SDK3.8.0,security:none\"},\"coupon_amount\":0,\"installments\":1,\"description\":\"Inscrição no evento: III CORRIDA SAUIM DE COLEIRA\",\"notification_url\":\"https:\\/\\/www.movamazon.com.br\\/api\\/mercadolivre\\/webhook.php\",\"issuer_id\":\"12501\",\"processing_mode\":\"aggregator\",\"merchant_account_id\":null,\"merchant_number\":null,\"metadata\":[],\"callback_url\":null,\"release_info\":null,\"marketplace_owner\":null,\"integrator_id\":null,\"corporation_id\":null,\"platform_id\":null,\"point_of_interaction\":{\"type\":\"OPENPLATFORM\",\"application_data\":{\"name\":null,\"version\":null},\"transaction_data\":{\"qr_code\":\"00020126580014br.gov.bcb.pix0136fb6df74b-f82a-425b-92ab-55459eab8d69520400005303986540521.005802BR5915EUDIMACIBARBOZA6009Sao Paulo62250521mpqrinter1465150639556304D990\",\"qr_code_base64\":\"iVBORw0KGgoAAAANSUhEUgAABWQAAAVkAQMAAABpQ4TyAAAABlBMVEX\\/\\/\\/8AAABVwtN+AAAKyklEQVR42uzdQXIiOxIGYBEsWPoIHIWj2UfjKByBJQsHNdE9VaVMSWD8up\\/hRXz\\/hukXdtWHdzmZUhYRERERERERERERERERERERERERERERERERERER+Xezm7ocyyZ9fvz6PP\\/64e38E5+l7OvP\\/37IqZT5l9bPUt7q5++cS3n\\/9ctvvx66nX+5pIfVh9LS0tLS0tLS0tLS0tLS\\/rH21Pz7OP\\/Pw3SdX3SdX1Dq576q01f+rb38+onN\\/F+u8+OWrx6yr9qEOtDS0tLS0tLS0tLS0tK+sjaVr8e1xg2f4UXvKy3Uvqf4ol1T66Z8zj+Za976lS+0tLS0tLS0tLS0tLS0\\/0XtYX3Btf78W31hqnlT5\\/QyF8ylPiTVum+xYKalpaWlpaWlpaWlpaX9j2tD5zTUuLXmXbThl26Nzh7WudvwOdHS0tLS0tLS0tLS0tLS\\/pS2mRbe9JV5iS\\/M2rbPW3859Hk\\/Rs3iUzM1\\/M9mm2lpaWlpaWlpaWlpaWl\\/Uju8uehcz5wuNxelS4dOoz7vJU4Lf+8hf3bPEi0tLS0tLS0tLS0tLe3PaIe5ddb0M5Wrtdbdjc6cbuo9uIP2azpz+mehpaWlpaWlpaWlpaWl\\/Untvs7d1prz3I\\/Olu5F22mccOa0\\/erp5qJTfONlrn3vzd\\/S0tLS0tLS0tLS0tLSPle7u33w8hBHZ5ddnEP1nYWe0\\/pVbz2kNIs974WWlpaWlpaWlpaWlpaW9vva9oXhBems6Vtt1dZie9ecOb3UKeFl98uydnRqHvK90NLS0tLS0tLS0tLS0j5XG9T9upa86+VjLlfrtPBnbc2GK2yb9aPtCPI2XX9U+72X+U8QRoxpaWlpaWlpaWlpaWlpX1K7\\/Gytfc+jH9vOndTBoO\\/tNmw7NVzqvbin+tB0gJWWlpaWlpaWlpaWlpb2RbXtFpXS17ztGs10dW14yFKu9vO37RaVpXOatqfsqnZ6dOcLLS0tLS0tLS0tLS0tLe3d05zpxOeuDvge12nhoE5XJeWHpGnhVImHcr\\/\\/yvms6SO3QtHS0tLS0tLS0tLS0tI+V1v7u5eyXqx77MrU3JJNL+qnhcNXbne9hJHjVPNO8SG0tLS0tLS0tLS0tLS0r6f96uai31lq3s80NbzUvmnQt73\\/9jgaOT7HOeXP+lVPX9W8tLS0tLS0tLS0tLS0tE\\/XnuKZ00HeuxuMwotvj8xumlUs5euvSktLS0tLS0tLS0tLS0v7F7Uhhxs7X0rT563HRbe1OdyOHE\\/zzpe+3P\\/sm8aD\\/4+AlpaWlpaWlpaWlpaW9kW1+2ZGty793DT6XLbWz8GLDmu\\/dymc8\\/rRdHB1H7\\/y5f7flJaWlpaWlpaWlpaWlva52mHNG5TvUfs2aoLmFw2nhN\\/nP8FHNy0cat4HN9TQ0tLS0tLS0tLS0tLSPkubbiwqac72uOqWDuo2bVNJW1TSFbbhoGp7cDXN3+7rNUi9npaWlpaWlpaWlpaWlvb1tHkByo0bjELzc2rWaKYX7uIuzlLbsA90Tr+8pZeWlpaWlpaWlpaWlpaW9pvavKal7nw5P9Ki\\/azqErWXqqzN43AGNY0Wb6ty\\/9WZU1paWlpaWlpaWlpaWtrnasOJz\\/TgEqeDN+kFH\\/O9t\\/2lQ8tDls9zPcDa9nnrQwbN4gMtLS0tLS0tLS0tLS3ty2pLt7tkc2NzaOiktoO+7ZnTqbu5qF0\\/WpqCOXRQvzghS0tLS0tLS0tLS0tLS\\/ss7S6t00xzt4e1Y7pJZ00\\/RnO3qRm6ax4S2q\\/9vbdtLvfnb2lpaWlpaWlpaWlpaWlpH9dO3YzuJm0O7e9VCks\\/l37vaT1jGirzt75Cr83j7Y3LmnYP3ApFS0tLS0tLS0tLS0tL+xzt4OaiZWPosVHXDF506prFpX71UEinG4zSwdW7D6GlpaWlpaWlpaWlpaV9FW2\\/aaXVleG08HBq+LA2Pzepc7rUutNIe7v6pqWlpaWlpaWlpaWlpX0p7a4ZmU3zt8uD74zOnpozplPUplybN2\\/7Xz6tN\\/AWWlpaWlpaWlpaWlpaWtq\\/o10GfOu6lrf+RenMaej31hZtOnN6TeV+29+tZf42jRzT0tLS0tLS0tLS0tLSvrJ2cH\\/QcW3ZXpuB33HNm\\/q+Kcuul7Q45rMfOV6u+t3f70bT0tLS0tLS0tLS0tLSPlu7Gw36hn+fG+1bU\\/OGMnVY8x7iWdOp3ljUF87t6VdaWlpaWlpaWlpaWlra19MG9XF94bVeXTvYonK39k3t2LSKJd97u2iHhTMtLS0tLS0tLS0tLS3t62r38RTnrZr3XLp7b+vVtaU2Pw9xaDdsUSmrNqhPpVvJ8kifl5aWlpaWlpaWlpaWlpb2cW0zLdzufPl\\/3mPLtj9jOhg5DtPC0+jW3lP9ym1oaWlpaWlpaWlpaWlpX1Hb3n87KFs\\/msuHSixXT2V8\\/dGiSs3iUDC\\/dwVz+OrHO91oWlpaWlpaWlpaWlpa2idqh1PDbc0bOqfneEw0HxetX\\/VS8vrR9JXbUePB5lBaWlpaWlpaWlpaWlra19Xu69xtM397bW4qKv2\\/S7+LsxneHWvfmw7p927rpaWlpaWlpaWlpaWlpaV9VFu7q6FCrxtDr412Wwd9w4vSrb11ccyURo77h0zptOsjFTotLS0tLS0tLS0tLS3ts7S7NKM7t2qvteYt\\/bqWeslQOy18mS\\/a3dUp4friduR4SVgcE+5QoqWlpaWlpaWlpaWlpX1RbXsJUV+ujgd9U8F8Wr\\/qNNz5MjVfua15ewktLS0tLS0tLS0tLS3t62tLrHmnVKbW2jccF03qEpufm\\/R5++aifHD1y3uWaGlpaWlpaWlpaWlpaWkf1Ab1sTk+2q5t+bgx6NtX6OX2wdVa5rdrSKdv3ApFS0tLS0tLS0tLS0tL+zTtvrt06JoGfNMx0TaneN3RncUxdXNoUO7j6PHu\\/n5TWlpaWlpaWlpaWlpa2hfQ7tILh5Xn+\\/pfPlPN204NJ3W7OOZ9dINR+3d6+MwpLS0tLS0tLS0tLS0t7dO07QKUsnZMl7nbfGx0akZnU9l6XB96nW8uyvO39cxpqTXwqblE90BLS0tLS0tLS0tLS0v7mtqh\\/jocnU3fYtmiso83FYV53Cl2Todt2NJX2ad1aJeWlpaWlpaWlpaWlpaW9q9rlweHM6cfzbRw7fNuhy+q08LnOHq8NI3zzUXTegY1jBp\\/L7S0tLS0tLS0tLS0tLQ\\/pt31x0iP61W1U23JDta2pEuH+puLNrN2qn3ec6NOl+g+uPOFlpaWlpaWlpaWlpaW9rnaU\\/Pv4\\/qi6\\/zgwRW2+9hBHdS8S4f0OFK2B1mDhJaWlpaWlpaWlpaWlvb1tWl09th0UKfuzOmUfmmY46q\\/plUs9SDrYHtKvYGXlpaWlpaWlpaWlpaWlvbf0Q4q9DJPB5\\/rlHDbLC7dMdJzs\\/slHVxtN4hevnG3MC0tLS0tLS0tLS0tLe0LaUsqUz+6X9o2zeG88yVpSxw9PkftlBbHVO0\\/qdBpaWlpaWlpaWlpaWlpf1J7Y1o4nzn9aGreWraGpZ\\/Hcith58uySTRp28UxtLS0tLS0tLS0tLS0tC+pvTU6m8rTpFwK5G19QXhR\\/5BFu8zdvo0OrO76QpqWlpaWlpaWlpaWlpaW9g+0IiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIi+d\\/wUAAP\\/\\/ThEbvU0060YAAAAASUVORK5CYII=\",\"transaction_id\":null,\"bank_transfer_id\":null,\"financial_institution\":null,\"bank_info\":{\"payer\":{\"id\":null,\"account_id\":null,\"long_name\":null,\"external_account_id\":null,\"identification\":[]},\"collector\":{\"account_id\":null,\"long_name\":null,\"account_holder_name\":\"Eudimaci Barboza de Lira\",\"transfer_account_id\":null},\"is_same_bank_account_owner\":null,\"origin_bank_id\":null,\"origin_wallet_id\":null},\"ticket_url\":\"https:\\/\\/www.mercadopago.com.br\\/payments\\/146515063955\\/ticket?caller_id=3206743703&hash=155c8732-045f-4b9b-91ed-1fb975d6faf4\",\"e2e_id\":null}},\"accounts_info\":null,\"tags\":null,\"refunds\":[]}', NULL, 'pix', 1, NULL, 29, '2026-02-21 15:46:08'),
(25, 18, 'pix_146516920839', '146516920839', 'https://www.mercadopago.com.br/payments/146516920839/ticket?caller_id=3206743703&hash=861b5529-aaf8-48f9-a436-45ec52a43c80', 'processando', '2026-02-21 18:51:31', '2026-02-21 18:51:31', '{\"id\":146516920839,\"acquirer_reconciliation\":[],\"sponsor_id\":null,\"operation_type\":\"regular_payment\",\"order\":[],\"brand_id\":null,\"build_version\":\"3.143.0-rc-4\",\"binary_mode\":false,\"external_reference\":\"MOVAMAZON_1771698595_29\",\"financing_group\":null,\"status\":\"pending\",\"status_detail\":\"pending_waiting_transfer\",\"store_id\":null,\"taxes_amount\":0,\"date_created\":\"2026-02-21T14:51:31.121-04:00\",\"live_mode\":true,\"date_last_updated\":\"2026-02-21T14:51:31.121-04:00\",\"date_of_expiration\":\"2026-02-22T14:51:30.930-04:00\",\"deduction_schema\":null,\"date_approved\":null,\"money_release_date\":null,\"money_release_schema\":null,\"money_release_status\":\"released\",\"currency_id\":\"BRL\",\"transaction_amount\":21,\"transaction_amount_refunded\":0,\"payer\":{\"type\":null,\"id\":\"3206743703\",\"email\":null,\"identification\":{\"type\":null,\"number\":null},\"first_name\":null,\"last_name\":null,\"entity_type\":null,\"phone\":{\"area_code\":null,\"number\":null,\"extension\":null},\"operator_id\":null},\"collector_id\":260742905,\"counter_currency\":null,\"payment_method_id\":\"pix\",\"payment_method\":{\"id\":\"pix\",\"type\":\"bank_transfer\",\"issuer_id\":\"12501\"},\"payment_type_id\":\"bank_transfer\",\"pos_id\":null,\"transaction_details\":{\"financial_institution\":null,\"net_received_amount\":0,\"total_paid_amount\":21,\"installment_amount\":0,\"overpaid_amount\":0,\"external_resource_url\":null,\"payment_method_reference_id\":null,\"acquirer_reference\":null,\"payable_deferral_period\":null,\"bank_transfer_id\":null,\"transaction_id\":null},\"fee_details\":[],\"differential_pricing_id\":null,\"authorization_code\":null,\"captured\":true,\"card\":[],\"call_for_authorize_id\":null,\"statement_descriptor\":null,\"shipping_amount\":0,\"additional_info\":{\"tracking_id\":\"platform:8|8.3.30,so:so;,type:SDK3.8.0,security:none\"},\"coupon_amount\":0,\"installments\":1,\"description\":\"Inscrição no evento: III CORRIDA SAUIM DE COLEIRA\",\"notification_url\":\"https:\\/\\/www.movamazon.com.br\\/api\\/mercadolivre\\/webhook.php\",\"issuer_id\":\"12501\",\"processing_mode\":\"aggregator\",\"merchant_account_id\":null,\"merchant_number\":null,\"metadata\":[],\"callback_url\":null,\"release_info\":null,\"marketplace_owner\":null,\"integrator_id\":null,\"corporation_id\":null,\"platform_id\":null,\"point_of_interaction\":{\"type\":\"OPENPLATFORM\",\"application_data\":{\"name\":null,\"version\":null},\"transaction_data\":{\"qr_code\":\"00020126580014br.gov.bcb.pix0136fb6df74b-f82a-425b-92ab-55459eab8d69520400005303986540521.005802BR5915EUDIMACIBARBOZA6009Sao Paulo62250521mpqrinter14651692083963046E55\",\"qr_code_base64\":\"iVBORw0KGgoAAAANSUhEUgAABWQAAAVkAQMAAABpQ4TyAAAABlBMVEX\\/\\/\\/8AAABVwtN+AAAKuUlEQVR42uzdQXLiShIG4CJYsOQIHIWjmaNxFI7AkgWBJp5bUlWmSrY72tNWR3z\\/xuE3Bn3qXU5mZRURERERERERERERERERERERERERERERERERERER+f\\/mMCxyHf+n83+\\/3Et5m38+3\\/\\/7+8\\/TMJSyrx+6jV92nX\\/uxi\\/bjX\\/xGj\\/8K5fxw+\\/\\/8bRE0NLS0tLS0tLS0tLS0tJ+g\\/aWfr8u\\/mhXtcf\\/HjRl\\/\\/7hYXjUPz3\\/96BD1ZUV7TC\\/cqk\\/w5fQ0tLS0tLS0tLS0tLSblVbK82pXL1XZf1Z3rVTjTuMNfCyTJ20x1rzXuafJairdqq+H7S0tLS0tLS0tLS0tLT\\/lraEcrU+INe8TdPzlgrmqe16HtWX+ZX3KzUvLS0tLS0tLS0tLS0t7T+qbWrcPDLbzOGGD62Nzi6HeAdaWlpaWlpaWlpaWlpa2r+oTdPCu1RcN\\/3d5kG13zvULzm3B1eP7ZnT5sPNqPE3zDbT0tLS0tLS0tLS0tLS\\/k3tcnPRrtuirX3efZ0WnsrV6ezpORXOX\\/2SP9izREtLS0tLS0tLS0tLS\\/vXtB+mMy08dVJDrXsINW\\/af5vbr50zp98TWlpaWlpaWlpaWlpa2r+hnfqW55X52zCHe6+3qISaN5Srh3Qly3GpvJTSbb+GZbq0tLS0tLS0tLS0tLS029OW+jdj8\\/M1ztu+6l80c7jhGs3mldOXDHV5bjfNKzeTwB+f4qSlpaWlpaWlpaWlpaWl\\/br2kOriQy2qh3TmtG4umirxfV02dGor8jAtHDcXlbZpfBpWQ0tLS0tLS0tLS0tLS7tFbaOufxNvDH1bLB2asq993bC56FEL5Ws7ctxZg1Tafu+n+29paWlpaWlpaWlpaWlpf1b72dKh9\\/L0FQZ961nT2EENDyyp5s2vGF791L7iJ\\/+mtLS0tLS0tLS0tLS0tBvQ\\/mp61gcNy3J1mDcXTWdN961urnnHn6\\/wnPDK97T+KLRfH7S0tLS0tLS0tLS0tLS036VtuqthwW4+LlqnhZ9jRd4vssPB1evcLH6lCj2W+d1XpqWlpaWlpaWlpaWlpd2YtlFf26VDOff6xZf2gevl6i5t690tz5xO+lt9ZVpaWlpaWlpaWlpaWtotaw\\/12Gi4riWsrn2Fa1vqA3L5ekjTwqGAbj7cqMPFMV\\/Zf0tLS0tLS0tLS0tLS0v7g9rpM3nr7LF36eczfShegLJsx+ZXzjeHNq9eX7nQ0tLS0tLS0tLS0tLS0n6LNp85bSry0KINq5GaCr15UJ0WXhs5Dn3eofsl9dYZWlpaWlpaWlpaWlpa2u1pS+2yDu3SoXBdS0fb3Bya+rtz2RrUdWPRM6xBmgRf21xES0tLS0tLS0tLS0tLuwVt\\/ZtDrXWXD4pZedWhu0Q31LzH9uDqM\\/27fdLnpaWlpaWlpaWlpaWlpf1xbdhcVOYlQ7v0gHibynJoN9e6x6Uyd1DD\\/ttGQktLS0tLS0tLS0tLS7tFbWf+Nvztsb1FJY7OhoOXp8XG3Nx+bWrde1msPQra4TdOcdLS0tLS0tLS0tLS0tLSfq4N08JTsR2WDz2DtvvA6UuDOk8Ld9YgLdcf0dLS0tLS0tLS0tLS0m5PW8aWbWfp0HT2tNa8+Y6X\\/XJ6+Nx75e60cKlP7CzPpaWlpaWlpaWlpaWlpd2itrkx9Drf2zkpX+MfxeZn7pjWcvWwPj18mb8kF8pD93daWlpaWlpaWlpaWlraLWo7X5ybnmsXoQwrGV99l86Wdi72PK18mJaWlpaWlpaWlpaWlpb2G7SHXl3860F5WjicOZ3ufLmlQd9zqx2q+m3+sn39stPQbOuN\\/18BLS0tLS0tLS0tLS0t7Ra1+aaVzoNKKl\\/r3S\\/PMPA7NolzuRoL5nz2dBhr37xDiZaWlpaWlpaWlpaWlnaL2kPvQbvlZzp7cEvv2Oh0c+g5tVnfxhHkuv+2VG2ueT\\/tnNLS0tLS0tLS0tLS0tL+jHbqXz7W52\\/fUrk6dUyHlRHaNKy7SzeHPsOrL+dvS69wpqWlpaWlpaWlpaWlpaX9bW1zPUves1QHfV\\/1mOixrdD3KyuSDunAaqe\\/WzvM+3ANab04hpaWlpaWlpaWlpaWlnZ72kZ9LWuXfr66S4dqq\\/ZRa9\\/rPPCbD67GUeN850ttFn9lzxItLS0tLS0tLS0tLS3tT2q7m4tywlnTSdv8XJarx3qA9dKrfU9twfyor\\/5JaGlpaWlpaWlpaWlpaX9KO9W4j+XmoqG9QfRYL\\/1cXv75SHO4j6TtdE7f2n+fU2q\\/XmlpaWlpaWlpaWlpaWm3qW1q3ulvznMTNM\\/fDlV7S\\/O3eenQ8gqWXT0Cem\\/nb2P79cudU1paWlpaWlpaWlpaWlra4Xemhau6c+a0tBX6h9pQ5udNRvvaLG46zLXMf3y8Z4mWlpaWlpaWlpaWlpZ2M9pQrp7n1mzs89a9t8\\/llPC5qpdfkl+5aRaH2peWlpaWlpaWlpaWlpZ249owPdysrp2ankOaFp5q3vyg5oGj9lVvTQkbjJ51\\/20unK+l0NLS0tLS0tLS0tLS0m5Re0jNzekWlVKPjQ7pFpXa9HyGL0kHWPMrlzrEe09LdE+0tLS0tLS0tLS0tLS0tN+vLfWk57Xt9wZl1F\\/myz6f4bhordCHZdM45LgyLVy+sGeJlpaWlpaWlpaWlpaWdgPa2KoNW3rDcdF7u2h3X8vUW\\/vhQ1r129S6ZbHqt5kWDrUvLS0tLS0tLS0tLS0t7Sa1ofIclne+XGb9sD4tfJo\\/XELHNPx+710\\/uq\\/qL84209LS0tLS0tLS0tLS0v6sNt1dsktLh4bwoMusiyO04dWvszYeXB3mm0PjmdNlD5eWlpaWlpaWlpaWlpaW9o+13ROfoSLfpQW7z1Tex0s\\/U5kfbxAd2lfuTgt\\/sq2XlpaWlpaWlpaWlpaW9me1pT0eOi3abW4QvYw18JS3dnNRZ8C3artnTfMZ1Dw9TEtLS0tLS0tLS0tLS7tdbTwuulw61JSn9cxpX9tdonteGTkOZ01LPXsariGlpaWlpaWlpaWlpaWl3Z52SGXrOR0ffWvL1GM6czos1x6Fm0PXE+duwyajKy0tLS0tLS0tLS0tLe02tR39tdfsnOZuj8vNRUM\\/rw87pZfeh28lnyelpaWlpaWlpaWlpaWlpf027Xvi5qKqfoZp4dwsnsr7cHD1ujhrmrXP9CW0tLS0tLS0tLS0tLS029UelsVqmBbO+2\\/vqda9pUHfc9sszhuL7ulVwxJdWlpaWlpaWlpaWlpa2n9Ce\\/uoc1raKeGY5YNKu3Tog5tD7\\/UWlVzzfmVzES0tLS0tLS0tLS0tLe3PatMtKrvuxSc5YXXteuE8zeHGedy6\\/mjonnqlpaWlpaWlpaWlpaWlpf2\\/aEt6wKueNc2DvuHG0HBhTHzlqbx\\/mw+ulqDNnWdaWlpaWlpaWlpaWlraf0nbGfit2jIq9\\/VDQ1LnKeH6+9TX3affpy\\/9KLS0tLS0tLS0tLS0tLRb0KZp4WZz0VAv\\/byMN4d2H\\/ThqHFJrzxNDd\\/as6dfvKGGlpaWlpaWlpaWlpaW9me1H24uWmt6NsdF32vcZbm6qx3U5pWH3pnTU71B9M\\/2LNHS0tLS0tLS0tLS0tLSioiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiPwj+V8AAAD\\/\\/8tTeS+GiZy1AAAAAElFTkSuQmCC\",\"transaction_id\":null,\"bank_transfer_id\":null,\"financial_institution\":null,\"bank_info\":{\"payer\":{\"id\":null,\"account_id\":null,\"long_name\":null,\"external_account_id\":null,\"identification\":[]},\"collector\":{\"account_id\":null,\"long_name\":null,\"account_holder_name\":\"Eudimaci Barboza de Lira\",\"transfer_account_id\":null},\"is_same_bank_account_owner\":null,\"origin_bank_id\":null,\"origin_wallet_id\":null},\"ticket_url\":\"https:\\/\\/www.mercadopago.com.br\\/payments\\/146516920839\\/ticket?caller_id=3206743703&hash=861b5529-aaf8-48f9-a436-45ec52a43c80\",\"e2e_id\":null}},\"accounts_info\":null,\"tags\":null,\"refunds\":[]}', NULL, 'pix', 1, NULL, 29, '2026-02-21 15:51:31'),
(26, 18, 'pix_146515476031', '146515476031', 'https://www.mercadopago.com.br/payments/146515476031/ticket?caller_id=3206743703&hash=a53416be-bb8e-46e7-ac3c-b758f24c927a', 'processando', '2026-02-21 18:51:37', '2026-02-21 18:51:37', '{\"id\":146515476031,\"acquirer_reconciliation\":[],\"sponsor_id\":null,\"operation_type\":\"regular_payment\",\"order\":[],\"brand_id\":null,\"build_version\":\"3.143.0-rc-4\",\"binary_mode\":false,\"external_reference\":\"MOVAMAZON_1771698595_29\",\"financing_group\":null,\"status\":\"pending\",\"status_detail\":\"pending_waiting_transfer\",\"store_id\":null,\"taxes_amount\":0,\"date_created\":\"2026-02-21T14:51:37.498-04:00\",\"live_mode\":true,\"date_last_updated\":\"2026-02-21T14:51:37.498-04:00\",\"date_of_expiration\":\"2026-02-22T14:51:37.363-04:00\",\"deduction_schema\":null,\"date_approved\":null,\"money_release_date\":null,\"money_release_schema\":null,\"money_release_status\":\"released\",\"currency_id\":\"BRL\",\"transaction_amount\":21,\"transaction_amount_refunded\":0,\"payer\":{\"type\":null,\"id\":\"3206743703\",\"email\":null,\"identification\":{\"type\":null,\"number\":null},\"first_name\":null,\"last_name\":null,\"entity_type\":null,\"phone\":{\"area_code\":null,\"number\":null,\"extension\":null},\"operator_id\":null},\"collector_id\":260742905,\"counter_currency\":null,\"payment_method_id\":\"pix\",\"payment_method\":{\"id\":\"pix\",\"type\":\"bank_transfer\",\"issuer_id\":\"12501\"},\"payment_type_id\":\"bank_transfer\",\"pos_id\":null,\"transaction_details\":{\"financial_institution\":null,\"net_received_amount\":0,\"total_paid_amount\":21,\"installment_amount\":0,\"overpaid_amount\":0,\"external_resource_url\":null,\"payment_method_reference_id\":null,\"acquirer_reference\":null,\"payable_deferral_period\":null,\"bank_transfer_id\":null,\"transaction_id\":null},\"fee_details\":[],\"differential_pricing_id\":null,\"authorization_code\":null,\"captured\":true,\"card\":[],\"call_for_authorize_id\":null,\"statement_descriptor\":null,\"shipping_amount\":0,\"additional_info\":{\"tracking_id\":\"platform:8|8.3.30,so:so;,type:SDK3.8.0,security:none\"},\"coupon_amount\":0,\"installments\":1,\"description\":\"Inscrição no evento: III CORRIDA SAUIM DE COLEIRA\",\"notification_url\":\"https:\\/\\/www.movamazon.com.br\\/api\\/mercadolivre\\/webhook.php\",\"issuer_id\":\"12501\",\"processing_mode\":\"aggregator\",\"merchant_account_id\":null,\"merchant_number\":null,\"metadata\":[],\"callback_url\":null,\"release_info\":null,\"marketplace_owner\":null,\"integrator_id\":null,\"corporation_id\":null,\"platform_id\":null,\"point_of_interaction\":{\"type\":\"OPENPLATFORM\",\"application_data\":{\"name\":null,\"version\":null},\"transaction_data\":{\"qr_code\":\"00020126580014br.gov.bcb.pix0136fb6df74b-f82a-425b-92ab-55459eab8d69520400005303986540521.005802BR5915EUDIMACIBARBOZA6009Sao Paulo62250521mpqrinter1465154760316304F813\",\"qr_code_base64\":\"iVBORw0KGgoAAAANSUhEUgAABWQAAAVkAQMAAABpQ4TyAAAABlBMVEX\\/\\/\\/8AAABVwtN+AAAKvklEQVR42uzdQW4iyRIG4EQsWHIEjsLRzNE4CkdgycKinrqHIiMys2j8PG0Y6fs3llsu6qN3oYiMLCIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiLyd7OZuhzLKv08\\/Pp5\\/vXH69tffJayq3\\/\\/+0NOpdweuv8sZVt\\/\\/s65lI9fD29\\/fej69nBJH1Y\\/lJaWlpaWlpaWlpaWlpb229pT83v\\/omt6weH+gn\\/U6Sv\\/1l5+\\/cXq9i\\/X2yev6sNzdlWbUHtaWlpaWlpaWlpaWlrad9am8vV4q3H3tzI1vWgbC9NQ+57iizZNrTs1D5VaQFft\\/JUvtLS0tLS0tLS0tLS0tP85bcq1\\/v22vjDVvKlz+vuF11nf17rbWDDT0tLS0tLS0tLS0tLS\\/se1oXNaSvloXvhx14aHlkZn9\\/e52\\/BzoqWlpaWlpaWlpaWlpaX9KW0zLbzqK\\/MSX5i1bZ+3Phz6vIdmWvgjPvS92WZaWlpaWlpaWlpaWlran9QONxedb63abd1clJYOnUZ93kucFv7ah3xvzxItLS0tLS0tLS0tLS3tz2iHWfVnTUPHdC5Xa627GZ05XdU9uIP2azpz+r3Q0tLS0tLS0tLS0tLS\\/qR2V+dua8157kdnS\\/ei9TROOHPafvW0uegU33i51b6P5m9paWlpaWlpaWlpaWlpX6vdLB+83MfR2fkuzqH6wYWeU1yiO\\/yQ0lzs+Si0tLS0tLS0tLS0tLS0tF\\/SnkZTw+EF6azptrZqa7G9ac6cXuqU8LwGaXhhzJdDS0tLS0tLS0tLS0tL+3LtFPu86bqWfNfL4Vau1mnhz9qaDSts05nTfgR5ndYf1X7v5fZfEEaMaWlpaWlpaWlpaWlpad9SO\\/9t\\/b1tbqZVtut05rR9uGnDtlPD4cNOtf2aDrDS0tLS0tLS0tLS0tLSvq\\/21PxTW\\/PWM6frXjvF\\/bdTPbia5m\\/bW1TmTmq6PSX3cGlpaWlpaWlpaWlpaWlpv6ttT3xu6oDv8T4tfP3zoO\\/84n0s97d9uf8xKvOD7pmtULS0tLS0tLS0tLS0tLSv1Tbd1VUqW\\/vjolP\\/ol3cYBS0x+bimNKMHA\\/nlidaWlpaWlpaWlpaWlra99RuFlbYpmta5g5qOCYaat806Nvuv+0Hf\\/P+2\\/ngam2\\/PnPnCy0tLS0tLS0tLS0tLe3LtH\\/6m49ug1F48fLI7Kq5iqX8+avS0tLS0tLS0tLS0tLS0v6L2pD9wp0v5d6qXS+8+JJGjtOdL325\\/1lX\\/07NnHL6nZaWlpaWlpaWlpaWlvYttbtmRrde+rmqS4fOTat2+KJQru7v\\/d65cM7Xj6aDq7v4lS+P\\/09paWlpaWlpaWlpaWlpX6sd1rzD2jevrk017q7qhlPC8\\/qjQzctnJfoPr7zhZaWlpaWlpaWlpaWlva12nbZUJiznffgVnVYOjTFsnV4cDXcGJp+D\\/O3u7oGqdfT0tLS0tLS0tLS0tLSvp82X4CysMEoND+n5hrN9MJNX\\/O2F3oudU6Ht4LS0tLS0tLS0tLS0tLS0n5DG4rr421vUGmmg5dbtJ9VXaL2UpX7WLFv+z7yXLH3IlpaWlpaWlpaWlpaWtr304YTn\\/uuRTtPB6\\/SCw63vbdLZ07rz3PcYJT7vPVDQrP4mf23tLS0tLS0tLS0tLS0tK\\/Vlu5vVgs3h4ZOajvoG5YOpa92jIVzun60NAVz28OlpaWlpaWlpaWlpaWlfT\\/tJl2nmeZu9\\/eO6SqdNT2M5m6H+3DPcf72urD3dhxaWlpaWlpaWlpaWlpa2n9B2\\/\\/NKm3p7fcqhTtf5n7v6X7GNFTm275Cr83j9cNlTbS0tLS0tLS0tLS0tLTvqB1sLppvDD3GaeHhzaFPDfquUiGdNhilg6vPzzbT0tLS0tLS0tLS0tLSvlDb37Sy6m8MHUwLD6eG9\\/ep4VXqnM617jTSLlfftLS0tLS0tLS0tLS0tG+lHV76OY\\/OtqtrP6u67ZymXJpat12HNBfO7cOn+wbeQktLS0tLS0tLS0tLS0v772jnvm69rmUbldekPDT93tqiTWdOr6ncb\\/u7tcxfp5FjWlpaWlpaWlpaWlpa2nfWDvYHHe8t22sz8DtYOnQqDy77DF\\/50H1IGDmeV\\/3uHnejaWlpaWlpaWlpaWlpaV+t3YwGfcPv50a7bWre02jkONS8+3jWdKobi\\/rC+St3vtDS0tLS0tLS0tLS0tK+RhvUx\\/sLr3V1bbpFZZ6\\/Xap923Zsuool7709xK94ip1UWlpaWlpaWlpaWlpa2rfW7uIpzktteh67DUZ5721dXVtq87M9+nlsOqlJfSrdlSzP9HlpaWlpaWlpaWlpaWlpaZ\\/XLmewrfej2VjUrz0KF8a0B1frFPH8IZ9DAS0tLS0tLS0tLS0tLe07asNx0f29bL32x0XP8c6XUK6eynj9US2cy\\/DM6UdXMIevfnzQjaalpaWlpaWlpaWlpaV9oXae2b00g77TQuf0HI+J5uOitXN6Kfna0cHIcX3oc\\/RmWlpaWlpaWlpaWlpa2nfVhq2zzeaia7OpqPS\\/l\\/4uzlpAD7Xh39s3P7mtl5aWlpaWlpaWlpaWlpb2K9q5u7q5VebTFG4MvTbadR30DS+av2pzcUweOa6jx5\\/NVx08TEtLS0tLS0tLS0tLS\\/tm2k2a0a3TwuHm0Pa6lrpkqJ0WvtwW7W7qlHB98aq5+2XO\\/NUvaYcSLS0tLS0tLS0tLS0t7ftqhyc+082hg0HfVDCf7l91Gt75MkXltrnzZYofMtHS0tLS0tLS0tLS0tK+rbZ9psSad0ojs6lcnY+LJnWJzc9V+rm8uSgfXH1ucxEtLS0tLS0tLS0tLS0t7Z+1m9qqPTbHR9O1LfMLBtPCfZ+3LB9crWV+ew3p9MSeJVpaWlpaWlpaWlpaWtrXah\\/UvO3C3T6f9azpLvZ356290+jm0HXz8JRGjssToaWlpaWlpaWlpaWlpX2VNgz6LlWeH92gb7g5dBotz900ndN2720YOU7\\/T8+cOaWlpaWlpaWlpaWlpaV9rXZqnkk17rnuv\\/1oat30wtB+vX3o9ba5KM\\/fzuuPljYXDduvtLS0tLS0tLS0tLS0tG+iHeqvw9HZ9C3m+dvUMQ1LiKbYOR20YdvNRfUrF1paWlpaWlpaWlpaWlrav6KdPzicOU393anv87YvqtPC5zh6PDeN8+ai4cHVr4WWlpaWlpaWlpaWlpb2x7Sb\\/iTp8b6qdmpW106pVZuWDqU7X0KNu48fcm7UaYnu12+ooaWlpaWlpaWlpaWlpX2B9tT8fry\\/6Jo6pofuRVN9QVvzzg8dR8p29DhIaGlpaWlpaWlpaWlpad9fm0Znj00Hta6wnZo52\\/W0mONdH\\/bfpoOsg9tT6gZeWlpaWlpaWlpaWlpaWtq\\/o80Vel20u64\\/T32zeP6tvvrc3P0Syv2pu0H0MvwwWlpaWlpaWlpaWlpa2rfXllSmHrqHWm2pi3ZbbYmjxu01pKfm4d3\\/W6HT0tLS0tLS0tLS0tLS\\/qR2YVp4Pi4aNhedY5n6mbRN27VNuPNlvkk0acPo8eKH0NLS0tLS0tLS0tLS0r5cuzQ6m8rTpAzzt6f4lf\\/RLrxwVedut6MDq5u+kKalpaWlpaWlpaWlpaWl\\/YZWRERERERERERERERERERERERERERERERERERE5K3zvwAAAP\\/\\/9ZPJZn7SsjAAAAAASUVORK5CYII=\",\"transaction_id\":null,\"bank_transfer_id\":null,\"financial_institution\":null,\"bank_info\":{\"payer\":{\"id\":null,\"account_id\":null,\"long_name\":null,\"external_account_id\":null,\"identification\":[]},\"collector\":{\"account_id\":null,\"long_name\":null,\"account_holder_name\":\"Eudimaci Barboza de Lira\",\"transfer_account_id\":null},\"is_same_bank_account_owner\":null,\"origin_bank_id\":null,\"origin_wallet_id\":null},\"ticket_url\":\"https:\\/\\/www.mercadopago.com.br\\/payments\\/146515476031\\/ticket?caller_id=3206743703&hash=a53416be-bb8e-46e7-ac3c-b758f24c927a\",\"e2e_id\":null}},\"accounts_info\":null,\"tags\":null,\"refunds\":[]}', NULL, 'pix', 1, NULL, 29, '2026-02-21 15:51:37'),
(27, 11, 'pix_147317365480', '147317365480', 'https://www.mercadopago.com.br/payments/147317365480/ticket?caller_id=3154860678&hash=024301fb-d69b-4e9e-8d0b-24ef5161152a', 'processando', '2026-02-22 16:19:20', '2026-02-22 16:19:20', '{\"id\":147317365480,\"acquirer_reconciliation\":[],\"sponsor_id\":null,\"operation_type\":\"regular_payment\",\"order\":[],\"brand_id\":null,\"build_version\":\"3.143.0-rc-4\",\"binary_mode\":false,\"external_reference\":\"142946577853\",\"financing_group\":null,\"status\":\"pending\",\"status_detail\":\"pending_waiting_transfer\",\"store_id\":null,\"taxes_amount\":0,\"date_created\":\"2026-02-22T12:19:19.749-04:00\",\"live_mode\":true,\"date_last_updated\":\"2026-02-22T12:19:19.749-04:00\",\"date_of_expiration\":\"2026-02-23T12:19:19.555-04:00\",\"deduction_schema\":null,\"date_approved\":null,\"money_release_date\":null,\"money_release_schema\":null,\"money_release_status\":\"released\",\"currency_id\":\"BRL\",\"transaction_amount\":20,\"transaction_amount_refunded\":0,\"payer\":{\"type\":null,\"id\":\"3154860678\",\"email\":null,\"identification\":{\"type\":null,\"number\":null},\"first_name\":null,\"last_name\":null,\"entity_type\":null,\"phone\":{\"area_code\":null,\"number\":null,\"extension\":null},\"operator_id\":null},\"collector_id\":260742905,\"counter_currency\":null,\"payment_method_id\":\"pix\",\"payment_method\":{\"id\":\"pix\",\"type\":\"bank_transfer\",\"issuer_id\":\"12501\"},\"payment_type_id\":\"bank_transfer\",\"pos_id\":null,\"transaction_details\":{\"financial_institution\":null,\"net_received_amount\":0,\"total_paid_amount\":20,\"installment_amount\":0,\"overpaid_amount\":0,\"external_resource_url\":null,\"payment_method_reference_id\":null,\"acquirer_reference\":null,\"payable_deferral_period\":null,\"bank_transfer_id\":null,\"transaction_id\":null},\"fee_details\":[],\"differential_pricing_id\":null,\"authorization_code\":null,\"captured\":true,\"card\":[],\"call_for_authorize_id\":null,\"statement_descriptor\":null,\"shipping_amount\":0,\"additional_info\":{\"tracking_id\":\"platform:8|8.3.30,so:so;,type:SDK3.8.0,security:none\"},\"coupon_amount\":0,\"installments\":1,\"description\":\"Inscrição no evento: III CORRIDA SAUIM DE COLEIRA\",\"notification_url\":\"https:\\/\\/www.movamazon.com.br\\/api\\/mercadolivre\\/webhook.php\",\"issuer_id\":\"12501\",\"processing_mode\":\"aggregator\",\"merchant_account_id\":null,\"merchant_number\":null,\"metadata\":[],\"callback_url\":null,\"release_info\":null,\"marketplace_owner\":null,\"integrator_id\":null,\"corporation_id\":null,\"platform_id\":null,\"point_of_interaction\":{\"type\":\"OPENPLATFORM\",\"application_data\":{\"name\":null,\"version\":null},\"transaction_data\":{\"qr_code\":\"00020126580014br.gov.bcb.pix0136fb6df74b-f82a-425b-92ab-55459eab8d69520400005303986540520.005802BR5915EUDIMACIBARBOZA6009Sao Paulo62250521mpqrinter1473173654806304C80D\",\"qr_code_base64\":\"iVBORw0KGgoAAAANSUhEUgAABWQAAAVkAQMAAABpQ4TyAAAABlBMVEX\\/\\/\\/8AAABVwtN+AAAKoElEQVR42uzdQZIiORIFUGEsWHIEjsLR4GgchSOwZIERY5ONkNwVJFlT2ZVRY+9vmLQeIh6983aXq4iIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIiIjIv5vNNORUVo\\/PUsp2mo6Pz5rj88vrj78\\/\\/ufuv5+bjy9N07WU\\/fNhMYfp9vicwpdzaGlpaWlpaWlpaWlpaWm\\/QXtOf9cX7J9fvT+0t4e+KtePL2\\/aCz6+dH1dY1dt+4ndZ2lvpqWlpaWlpaWlpaWlpV2utlWam75MXT1q3NU0XR4vqtpzq3k\\/cm61biukL+0nH9\\/UvO1LV1paWlpaWlpaWlpaWtq\\/U\\/t5mnLmy\\/tHofyq\\/fpRQO9oaWlpaWlpaWlpaWlp\\/w+0q0e5eh\\/\\/\\/13n9E3uQR0K59nOKS0tLS0tLS0tLS0tLS3tv6FN08KrNCU8PV5UW7Zdn\\/ecHrJ\\/Tg13FXrXNA45J\\/X\\/NttMS0tLS0tLS0tLS0tL+ye1s5uLLi\\/K1W5auLVqN+O08K8+5Pf2LNHS0tLS0tLS0tLS0tL+Ge0naZ3Tf15UUsc0ND1DzZunhj\\/OmN7DmdM2cnwr3xJaWlpaWlpaWlpaWlraP6PdzY3Odk3PQ\\/+Pagf1\\/PLLdf\\/tKhTQWXt41rq3h3oa26+0tLS0tLS0tLS0tLS0y9O2udvSXvTpKc5YriZlCZ3TMszf3l7\\/e6rnSd\\/uTqKlpaWlpaWlpaWlpaWl\\/ao2TQuXF4t2b+3vUFxfZ3\\/64yFdhR4OsFb1evypb6aFaWlpaWlpaWlpaWlpaZehHaeFu2ta6t7bcFy0vqArV1PhXL8cz5y2n1r7vdf+zbS0tLS0tLS0tLS0tLQL1rbO6cxFKPnMaa15wxnUfBVLrW3bT76\\/ePM6bOD92uYiWlpaWlpaWlpaWlpa2p\\/VljQqeynx4pPc7AwXoHTNzn3ffu3mbuvQ7uz+25l5XFpaWlpaWlpaWlpaWlrab9B2Xdage3XmtN31Ei\\/9DGdP98+HxGtHW26vlzTtaWlpaWlpaWlpaWlpaZeprde1fFq2duXqTM378YJdu+slfLndHFrClPAhbSwarx+lpaWlpaWlpaWlpaWlXao2DPqGpUP1uOjUvyhe1zLWvtf+J6\\/S6HFJZ067UeO3+29paWlpaWlpaWlpaWlpf1bbla371PSsDz4OI7RTGJ0NI7OtXM23qMTC+TD81K76pqWlpaWlpaWlpaWlpaX9Hm3pu6ubRzHdXc8Szp7ObOvN17W0bb15adN2rsyfWn9396LDTEtLS0tLS0tLS0tLS7sQbXhBVccFu4fnZ6edUl+3DCPH9\\/B5GKaGZ86ensub0NLS0tLS0tLS0tLS0v6stlOfev2lb3ZOqeYtYXNRGPTNCXtw68Uxs5uM4sFVWlpaWlpaWlpaWlpa2uVqZ2ve0+MFh3Rc9PjcXDRzc2jeWDQNn7fQQX01CUxLS0tLS0tLS0tLS0u7ZG3eXNSVqYe+ydmd4ixl5haVVjjXzunU1h\\/Vh4XTneuwuSi0X2lpaWlpaWlpaWlpaWlpv0d7mpsW\\/urmovCiTdrOm6eFy\\/iv4FdCS0tLS0tLS0tLS0tL+4PafOlnGPTtXlD7usdU64bka0f3T3WeFq4\\/9ZauH6WlpaWlpaWlpaWlpaVdrnbTts+2qeF42ee4uahq1+Nx0bDvNmwumkLhXNIS3fEOU1paWlpaWlpaWlpaWtqlakO52mreVRudndrq2lwDJ2U3xLvtO6irUOse5tYdnfv7XGhpaWlpaWlpaWlpaWlpf0\\/bTQu3unjVjoVu03HRkFurzMNPvqa+76VNC7eVv68qdFpaWlpaWlpaWlpaWtq\\/R7tpW3pLX7Zuv9Ki7V60T2dOQ83bPSz\\/ZFpaWlpaWlpaWlpaWtola8OyoVzzrlqNepmbFv6k\\/XpKDwlt1+7Ol\\/CTpy+cOaWlpaWlpaWlpaWlpaX9cW2tNMfNRaW\\/+CTeFFqeN4jm+dtwc+jU1KGQvoVq+8vTwrS0tLS0tLS0tLS0tLS0v3TmdPZFp2dr9t76u1Nq0eYKvYz6MC08Nos75Ze39dLS0tLS0tLS0tLS0tL+jLa0G1fqi079gO\\/UzpzW61pmR43P7frR1u+9pGtIS7+56DY7LUxLS0tLS0tLS0tLS0u7cG1pL0g1bwnLhqbHcdFW68af2j6ndlA1t1\\/DmdNdv\\/6ohE4qLS0tLS0tLS0tLS0t7UK14YXd7SlTX66GUdp1e2D9cnjhtc3b7l90TnPNG64fPX2tQqelpaWlpaWlpaWlpaX909pNuhEzn+YM87ddDdztv60vSren5FtTyviTw0OmcRKYlpaWlpaWlpaWlpaWlvY3tGXsstYKfVy4u043hq7Tg2fXIN3b6PHsqHFuGk9v\\/3sCLS0tLS0tLS0tLS0t7TK0eXo4ra5dj+XqLl0Ys3\\/Wvqtw5vSYHn7oC+ex00xLS0tLS0tLS0tLS0u7SO0nl37un8uG7umsaZwWflHz5oOr8SGHufYrLS0tLS0tLS0tLS0t7V+i3fSra++h5g13cdblQ6Fz2ilLu04z5Jjar8e+V3umpaWlpaWlpaWlpaWlpf23teO1LdOLY6Pn8ebQ8cxpGe98aX3e2zgt3P1UWlpaWlpaWlpaWlpa2iVqSypTZ5cP1YHf+Zo3by5qD9mmmvfyZlo4rz+ipaWlpaWlpaWlpaWlXZ52l5YN1QHf0L\\/85LqWMDWcy9V9Wp47PuRXOqe0tLS0tLS0tLS0tLS0P65tD57vV7b52+7v6VHznvuf3CW3X19vLpraEO+ZlpaWlpaWlpaWlpaWlvb7tHHRbjs+ehmvbTk8Xzx\\/10s6uNp9edv\\/jttY5oeOMy0tLS0tLS0tLS0tLe1ytblczbO6214bt\\/XWFm1QB2137WjY1htW\\/3ZfPtPS0tLS0tLS0tLS0tIuW7t5P6t7GDYX3cJdL00d7nyJo8fH\\/vrRqT0k3Pmy+3xjES0tLS0tLS0tLS0tLe0CtF1S0zMq6+aiUPOGG0TrEt3r3N7bVeictp8aNxeFwpmWlpaWlpaWlpaWlpZ2YdoZfdhclDun275MzbXulOZyt33H9D4W0t0pzrENS0tLS0tLS0tLS0tLS0v7vdpQTHcDv5f+n6\\/bi869urSLY2qFfkoPPQ7aeP0oLS0tLS0tLS0tLS0t7ZK1m2nIqdfN1LzHF9o6JfxQ541F93DnS\\/vJt9bfnS2caWlpaWlpaWlpaWlpaRelHZcPrcb9t9u29\\/ZYSjsuumsXoIRR47bBKN8c+s\\/I8WzNO724fpSWlpaWlpaWlpaWlpZ2UdpWaVbt1D7r0qGwwWj3uItzGsvV1jkNe3BL2H9ba99ccL89IUtLS0tLS0tLS0tLS0tL+5vaS2rVzizaLc9Kva76vY7Xj5bndHB3\\/ehlvDn013YL09LS0tLS0tLS0tLS0i5We+wX7W5Tn\\/fVQ8NZ08tXDrrG22doaWlpaWlpaWlpaWlpl6sdp4WntLJ2298BUzumuXN67Qd9Zx6Sa95u5PjTu0tpaWlpaWlpaWlpaWlpl6MdNxeVtHSo65xe0gUoef9tHqk99Z3T7Vzh3K096tS0tLS0tLS0tLS0tLS0tL+rFREREREREREREREREREREREREREREREREREREVl0\\/hMAAP\\/\\/lntOfa+3p8IAAAAASUVORK5CYII=\",\"transaction_id\":null,\"bank_transfer_id\":null,\"financial_institution\":null,\"bank_info\":{\"payer\":{\"id\":null,\"account_id\":null,\"long_name\":null,\"external_account_id\":null,\"identification\":[]},\"collector\":{\"account_id\":null,\"long_name\":null,\"account_holder_name\":\"Eudimaci Barboza de Lira\",\"transfer_account_id\":null},\"is_same_bank_account_owner\":null,\"origin_bank_id\":null,\"origin_wallet_id\":null},\"ticket_url\":\"https:\\/\\/www.mercadopago.com.br\\/payments\\/147317365480\\/ticket?caller_id=3154860678&hash=024301fb-d69b-4e9e-8d0b-24ef5161152a\",\"e2e_id\":null}},\"accounts_info\":null,\"tags\":null,\"refunds\":[]}', NULL, 'pix', 1, NULL, 18, '2026-02-22 13:19:20');

-- --------------------------------------------------------

--
-- Estrutura para tabela `papeis`
--

CREATE TABLE `papeis` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `papeis`
--

INSERT INTO `papeis` (`id`, `nome`) VALUES
(1, 'assessoria_admin'),
(2, 'assessor');

-- --------------------------------------------------------

--
-- Estrutura para tabela `participante`
--

CREATE TABLE `participante` (
  `usuario_id` int(11) NOT NULL,
  `total_corridas` int(11) DEFAULT '0',
  `total_km` decimal(8,2) DEFAULT '0.00',
  `melhor_tempo` time DEFAULT NULL,
  `camiseta_tamanho` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modalidade_preferida` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aceita_emails` tinyint(1) DEFAULT '1',
  `data_primeira_corrida` date DEFAULT NULL,
  `data_ultima_corrida` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `inscricao_id` int(11) NOT NULL,
  `numero_pedido` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_pedido` date DEFAULT NULL,
  `hora_pedido` time DEFAULT NULL,
  `status_pedido` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `detalhe_status` text COLLATE utf8mb4_unicode_ci,
  `responsavel_nome` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responsavel_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responsavel_celular` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comentario` text COLLATE utf8mb4_unicode_ci,
  `origem` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `campanha` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valor_produto` decimal(10,2) DEFAULT NULL,
  `valor_repasse_produto` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `periodos_inscricao`
--

CREATE TABLE `periodos_inscricao` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `inicio` date NOT NULL,
  `fim` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `planos_treino_gerados`
--

CREATE TABLE `planos_treino_gerados` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `inscricao_id` int(11) DEFAULT NULL COMMENT 'Vinculação com inscrição na corrida',
  `profissional_id` int(11) DEFAULT NULL,
  `anamnese_id` int(11) NOT NULL,
  `data_criacao_plano` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `bibliografia_plano` text COLLATE utf8mb4_unicode_ci,
  `bibliografia_json` longtext COLLATE utf8mb4_unicode_ci,
  `foco_primario` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duracao_treino_geral` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dias_plano` int(11) DEFAULT '5' COMMENT 'Duração do plano em dias',
  `equipamento_geral` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aceite_termos_treino` tinyint(1) DEFAULT '0' COMMENT '1 = aceitou termos de treino',
  `data_aceite_termos_treino` timestamp NULL DEFAULT NULL COMMENT 'Data/hora do aceite',
  `termos_id_treino` int(11) DEFAULT NULL COMMENT 'ID do termo aceito',
  `periodizacao_json` longtext COLLATE utf8mb4_unicode_ci,
  `schema_version` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metodologia` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assessoria_id` int(11) DEFAULT NULL,
  `programa_id` int(11) DEFAULT NULL,
  `status` enum('rascunho','publicado','revisao','arquivado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'rascunho',
  `versao` int(11) NOT NULL DEFAULT '1',
  `publicado_em` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `planos_treino_gerados`
--

INSERT INTO `planos_treino_gerados` (`id`, `usuario_id`, `inscricao_id`, `profissional_id`, `anamnese_id`, `data_criacao_plano`, `bibliografia_plano`, `bibliografia_json`, `foco_primario`, `duracao_treino_geral`, `dias_plano`, `equipamento_geral`, `aceite_termos_treino`, `data_aceite_termos_treino`, `termos_id_treino`, `periodizacao_json`, `schema_version`, `metodologia`, `assessoria_id`, `programa_id`, `status`, `versao`, `publicado_em`) VALUES
(3, 17, 999, NULL, 5, '2026-02-02 17:21:34', '==Bibliografia Recomendada==\n- ACSM Guidelines for Exercise Testing and Prescription, 11th Edition - https://www.acsm.org/read-research/books/acsms-guidelines-for-exercise-testing-and-prescription\n- Diretrizes de treinamento para corrida de rua - Referências científicas sobre periodização\n', NULL, 'preparacao-corrida', '50 minutos', 5, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'rascunho', 1, NULL),
(4, 18, 999, NULL, 3, '2026-02-03 17:34:38', '==Bibliografia Recomendada==\n- ACSM Guidelines for Exercise Testing and Prescription, 11th Edition - https://www.acsm.org/read-research/books/acsms-guidelines-for-exercise-testing-and-prescription\n- Diretrizes de treinamento para corrida de rua - Referências científicas sobre periodização\n- PubMed - Artigos sobre fisiologia do exercício e prevenção de lesões\n', NULL, 'preparacao-corrida', '50 minutos', 5, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'rascunho', 1, NULL),
(5, 28, 999, NULL, 6, '2026-02-06 11:53:53', '==Bibliografia Recomendada==\n- ACSM Guidelines for Exercise Testing and Prescription, 11th Edition - https://www.acsm.org/read-research/books/acsms-guidelines-for-exercise-testing-and-prescription\n- Diretrizes de treinamento para corrida de rua - Referências científicas sobre periodização\n- PubMed - Artigos sobre fisiologia do exercício e prevenção de lesões\n', NULL, 'preparacao-corrida', '45 minutos', 5, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'rascunho', 1, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos`
--

CREATE TABLE `produtos` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `tipo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `preco` decimal(10,2) DEFAULT '0.00',
  `disponivel_venda` tinyint(1) DEFAULT '1',
  `foto_produto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `produtos`
--

INSERT INTO `produtos` (`id`, `nome`, `descricao`, `tipo`, `preco`, `disponivel_venda`, `foto_produto`, `ativo`, `data_criacao`, `updated_at`) VALUES
(12, 'medalha', 'medalha para os participantes', '', 10.00, 1, 'frontend/assets/img/produtos/produto_1769186178_6973a38276f2b.png', 0, '2025-12-20 03:19:45', '2026-01-23 16:36:29'),
(13, 'medalha', 'medalha para os participantes', '', 10.00, 1, 'frontend/assets/img/produtos/produto_1766200785_694615d1a53a3.jpg', 0, '2025-12-20 03:19:45', '2025-12-20 03:19:51'),
(14, 'Meias comemorativas', 'Vendas de meias comemorativas da corrida', '', 18.00, 1, 'frontend/assets/img/produtos/produto_1766251992_6946ddd8771ac.jpeg', 0, '2025-12-20 17:33:12', '2025-12-20 17:33:34'),
(15, 'camisa', 'Camisa para a prova 10 km', '', 45.00, 1, 'frontend/assets/img/produtos/produto_1766262612_69470754239e9.png', 0, '2025-12-20 20:30:12', '2025-12-21 02:14:47'),
(16, 'camisa', 'Camisa para a prova 10 km', '', 45.00, 1, 'frontend/assets/img/produtos/produto_1766262612_694707545b63c.png', 0, '2025-12-20 20:30:12', '2025-12-20 20:31:04'),
(17, 'camisa', 'Camisa para a prova 10 km', '', 45.00, 1, 'frontend/assets/img/produtos/produto_1766262612_6947075499215.png', 0, '2025-12-20 20:30:12', '2025-12-20 20:30:33'),
(18, 'camisa', 'Camisa para a prova 10 km', '', 45.00, 1, 'frontend/assets/img/produtos/produto_1766262612_694707549b9a1.png', 0, '2025-12-20 20:30:12', '2025-12-20 20:30:42'),
(19, 'camisa', 'Camisa para a prova 10 km', '', 45.00, 1, 'frontend/assets/img/produtos/produto_1766262612_694707549df47.png', 0, '2025-12-20 20:30:12', '2025-12-20 20:30:53'),
(20, 'camisa', 'Camisa para a prova 5 km', '', 45.00, 1, 'frontend/assets/img/produtos/produto_1766262691_694707a30ffb4.png', 0, '2025-12-20 20:30:12', '2025-12-20 20:31:40'),
(21, 'camisa', 'Camisa para a prova 10 km', '', 45.00, 1, 'frontend/assets/img/produtos/produto_1766262612_69470754baf6f.png', 0, '2025-12-20 20:30:12', '2025-12-20 20:31:45'),
(22, 'camisa', 'Camisa para a prova 10 km', '', 45.00, 1, 'frontend/assets/img/produtos/produto_1766262615_694707571e923.png', 0, '2025-12-20 20:30:15', '2025-12-21 02:14:53'),
(23, 'camisa', 'Camisa para a prova 10 km', '', 45.00, 1, 'frontend/assets/img/produtos/produto_1766262618_6947075a04a20.png', 0, '2025-12-20 20:30:18', '2025-12-21 02:14:59'),
(24, 'camisa', 'Camisa para a prova 10 km', '', 45.00, 1, 'frontend/assets/img/produtos/produto_1766262618_6947075a06a0f.png', 0, '2025-12-20 20:30:18', '2025-12-24 03:03:59'),
(25, 'Camisa para corrida 10 e 5 km', 'Camisa para a prova de 5 e 10 km', '', 45.00, 1, 'frontend/assets/img/produtos/produto_1766283970_69475ac2a089b.jpeg', 0, '2025-12-21 02:26:10', '2025-12-24 03:03:54'),
(26, 'Medalha e troféu para as provas de 5 e 10 km', 'Modelo de medalha e troféu adotadas na III Corrida Sauim de Coleira', '', 50.00, 1, 'frontend/assets/img/produtos/produto_1766284069_69475b2587812.jpeg', 0, '2025-12-21 02:27:49', '2025-12-24 03:04:44'),
(27, 'Squeeze para a prova de 5 e 10 km', 'Garrafas adotadas na III Corrida Sauim de Coleira', '', 12.00, 1, 'frontend/assets/img/produtos/produto_1766284202_69475baa8c38b.jpeg', 0, '2025-12-21 02:30:02', '2025-12-24 03:04:51'),
(28, 'Viseiras para as provas de 5 e 10 km', 'Viseira adotada na III Corrida Sauim de Coleira', '', 12.00, 1, 'frontend/assets/img/produtos/produto_1766284263_69475be7d160d.jpeg', 0, '2025-12-21 02:31:03', '2025-12-24 03:04:58'),
(29, 'Camisa para corrida 10 km', 'Camisa para as provas de 10 km', '', 45.00, 1, 'frontend/assets/img/produtos/produto_1766682247_694d6e87eea7a.jpeg', 0, '2025-12-25 17:03:43', '2025-12-26 03:34:09'),
(30, 'Camisa para os 10 km', 'Camisa para a prova dos 10 km', '', 45.00, 1, 'frontend/assets/img/produtos/produto_1769186123_6973a34b0138c.png', 1, '2025-12-25 18:29:54', '2026-01-23 16:35:23'),
(31, 'Camisa para prova de 5 KM', 'Camisa para a prova de 5 km (público em geral e Comunidade Acadêmica)', '', 45.00, 1, 'frontend/assets/img/produtos/produto_1766687464_694d82e896ba7.png', 0, '2025-12-25 18:31:04', '2025-12-25 18:31:15'),
(32, 'Camisa para a prova de 5 Km', 'Camisa para a prova de 5 Km (Público em geral e Comunidade Acadêmica)', '', 45.00, 1, 'frontend/assets/img/produtos/produto_1769186104_6973a338689ef.png', 1, '2025-12-25 18:33:26', '2026-01-23 16:35:04'),
(33, 'Viseira para a prova de 5 e 10 km', 'Viseira para a prova de 5 e 10 km (Comunidade acadêmica e Público em Geral)', '', 15.00, 1, 'frontend/assets/img/produtos/produto_1769186268_6973a3dc69f46.png', 1, '2025-12-25 18:35:09', '2026-01-23 16:37:48'),
(34, 'Squeeze a prova de 5 e 10 km', 'Squeeze para a prova de 5 e 10 km (Comunidade acadêmica e Público em geral).', '', 15.00, 1, 'frontend/assets/img/produtos/produto_1769186230_6973a3b6359c1.png', 1, '2025-12-25 18:36:14', '2026-01-23 16:37:10'),
(35, 'Medalha para prova de 5 e 10 km', 'Medalha para a prova de 5 e 10 Km (Comunidade acadêmica e Público em geral).', '', 15.00, 1, 'frontend/assets/img/produtos/produto_1769186206_6973a39ec4ca9.png', 1, '2025-12-25 18:37:22', '2026-01-23 16:36:46'),
(36, 'Troféus para os primeiros colocados', 'Troféus para os primeiros colocados, conforme regulamento da prova.', '', 40.00, 1, 'frontend/assets/img/produtos/produto_1769186248_6973a3c8eff04.png', 1, '2025-12-25 18:41:03', '2026-01-23 16:37:28'),
(37, 'Kit do Atleta para a prova de 5 e 10 km', 'Kit completo do atleta para a prova de 5 e 10 km (Comunidade Acadêmica e Público Geral).', '', 129.00, 1, 'frontend/assets/img/produtos/produto_1769186140_6973a35c9e31a.jpeg', 1, '2025-12-26 03:29:56', '2026-01-23 16:35:40'),
(38, 'Kit do Atleta para a prova de 5 e 10 km', 'Kit completo do atleta para a prova de 5 e 10 km (Comunidade Acadêmica e Público Geral).', '', 129.00, 1, 'frontend/assets/img/produtos/produto_1766719797_694e01354249c.jpeg', 0, '2025-12-26 03:29:57', '2025-12-26 03:33:33'),
(39, 'Kit do Atleta para a prova de 5 e 10 km', 'Kit completo do atleta para a prova de 5 e 10 km (Comunidade Acadêmica e Público Geral).', '', 129.00, 1, 'frontend/assets/img/produtos/produto_1766719797_694e013549831.jpeg', 0, '2025-12-26 03:29:57', '2025-12-26 03:33:12'),
(40, 'Kit do Atleta para a prova de 5 e 10 km', 'Kit completo do atleta para a prova de 5 e 10 km (Comunidade Acadêmica e Público Geral).', '', 129.00, 1, 'frontend/assets/img/produtos/produto_1766719798_694e013647ddd.jpeg', 0, '2025-12-26 03:29:58', '2025-12-26 03:33:05'),
(41, 'Kit do Atleta para a prova de 5 e 10 km', 'Kit completo do atleta para a prova de 5 e 10 km (Comunidade Acadêmica e Público Geral).', '', 129.00, 1, 'frontend/assets/img/produtos/produto_1766719802_694e013a3abab.jpeg', 0, '2025-12-26 03:30:02', '2025-12-26 03:32:48'),
(42, 'Kit do Atleta para a prova de 5 e 10 km', 'Kit completo do atleta para a prova de 5 e 10 km (Comunidade Acadêmica e Público Geral).', '', 129.00, 1, 'frontend/assets/img/produtos/produto_1766719805_694e013d6ee7f.jpeg', 0, '2025-12-26 03:30:05', '2025-12-26 03:32:56'),
(43, 'Kit do Atleta para a prova de 5 e 10 km - Comunidade Acadêmica', 'Kit completo das modalidades de 5 e 10 KM - Comunidade Acadêmica', '', 109.00, 1, 'frontend/assets/img/produtos/produto_1769188295_6973abc7142f5.jpeg', 1, '2026-01-23 17:11:35', '2026-01-23 17:11:35'),
(44, 'Kit teste da Plataforma', 'Kit teste da Plataforma', '', 20.00, 1, 'frontend/assets/img/produtos/produto_1769699660_697b794ca8511.jpeg', 1, '2026-01-29 15:14:05', '2026-01-29 15:14:20'),
(45, 'Kit para  a prova de 40 km', 'Kit completo para a Prova de 40 km', '', 200.00, 1, 'frontend/assets/img/produtos/produto_1771301411_6993ea23d595a.jpeg', 0, '2026-02-17 04:10:11', '2026-02-17 04:38:25'),
(46, 'Kit para a prova de 80 km', 'Kit completo para a prova de 80 km', '', 200.00, 1, 'frontend/assets/img/produtos/produto_1771301447_6993ea4741b30.jpeg', 1, '2026-02-17 04:10:47', '2026-02-17 04:10:47'),
(47, 'camisa para a prova de 40 km', 'Camisa para a prova de 40 km', '', 200.00, 1, 'frontend/assets/img/produtos/produto_1771303060_6993f094f2b73.jpeg', 1, '2026-02-17 04:37:40', '2026-02-17 04:37:40');

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos_extras`
--

CREATE TABLE `produtos_extras` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `valor` decimal(10,2) NOT NULL,
  `disponivel_venda` tinyint(1) DEFAULT '1',
  `categoria` enum('vestuario','acessorio','seguro','outros') COLLATE utf8mb4_unicode_ci DEFAULT 'outros',
  `ativo` tinyint(1) DEFAULT '1',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `produtos_extras`
--

INSERT INTO `produtos_extras` (`id`, `evento_id`, `nome`, `descricao`, `valor`, `disponivel_venda`, `categoria`, `ativo`, `data_criacao`, `updated_at`) VALUES
(5, 8, 'camiseta da corrida', 'Compra da camiseta do evento', 30.00, 1, 'outros', 1, '2025-12-22 14:24:23', '2025-12-22 14:24:23'),
(6, 10, 'camisa para a prova de 40 km', 'Camisa do evento para a prova de 40 km', 50.00, 1, 'outros', 1, '2026-02-17 04:25:18', '2026-02-17 04:26:38'),
(7, 10, 'camisa para a prova de 80 km', 'camisa para a prova de 80 km', 50.00, 1, 'outros', 1, '2026-02-17 04:27:10', '2026-02-17 04:27:10');

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos_extras_backup`
--

CREATE TABLE `produtos_extras_backup` (
  `id` int(11) NOT NULL DEFAULT '0',
  `produto_id` int(11) DEFAULT NULL,
  `evento_id` int(11) NOT NULL,
  `modalidade_evento_id` int(11) NOT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `valor` decimal(10,2) NOT NULL,
  `categoria` enum('vestuario','acessorio','seguro','outros') COLLATE utf8mb4_unicode_ci DEFAULT 'outros',
  `estoque` int(11) DEFAULT '-1',
  `aplicavel_categorias` text COLLATE utf8mb4_unicode_ci,
  `ativo` tinyint(1) DEFAULT '1',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `produtos_extras_backup`
--

INSERT INTO `produtos_extras_backup` (`id`, `produto_id`, `evento_id`, `modalidade_evento_id`, `nome`, `descricao`, `valor`, `categoria`, `estoque`, `aplicavel_categorias`, `ativo`, `data_criacao`) VALUES
(1, NULL, 2, 1, 'Kit Camisa + Medalha', 'Kit adicional com camisa e medalha para 10KM', 25.00, 'outros', -1, NULL, 1, '2025-07-19 19:26:54'),
(2, NULL, 2, 2, 'Kit Camisa + Medalha', 'Kit adicional com camisa e medalha para 5KM', 20.00, 'outros', -1, NULL, 1, '2025-07-19 19:26:54');

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos_extras_modalidade`
--

CREATE TABLE `produtos_extras_modalidade` (
  `id` int(11) NOT NULL,
  `modalidade_evento_id` int(11) NOT NULL,
  `nome_produto` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `valor` decimal(10,2) NOT NULL,
  `foto_produto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disponivel_venda` tinyint(1) DEFAULT '1',
  `vagas_disponiveis` int(11) NOT NULL DEFAULT '0',
  `vagas_vendidas` int(11) NOT NULL DEFAULT '0',
  `ativo` tinyint(1) DEFAULT '1',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `produto_extra_produtos`
--

CREATE TABLE `produto_extra_produtos` (
  `id` int(11) NOT NULL,
  `produto_extra_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` int(11) DEFAULT '1',
  `ativo` tinyint(1) DEFAULT '1',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `produto_extra_produtos`
--

INSERT INTO `produto_extra_produtos` (`id`, `produto_extra_id`, `produto_id`, `quantidade`, `ativo`, `data_criacao`) VALUES
(10, 5, 25, 1, 1, '2025-12-22 14:24:23'),
(12, 6, 45, 1, 1, '2026-02-17 04:26:38'),
(13, 7, 46, 1, 1, '2026-02-17 04:27:10');

-- --------------------------------------------------------

--
-- Estrutura para tabela `programacao_evento`
--

CREATE TABLE `programacao_evento` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `tipo` enum('percurso','horario_largada','atividade_adicional') COLLATE utf8mb4_unicode_ci NOT NULL,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `hora_inicio` time DEFAULT NULL COMMENT 'Horário específico de largada/atividade desta programação (diferente de eventos.hora_inicio que é o horário geral)',
  `hora_fim` time DEFAULT NULL COMMENT 'Horário de término de atividade (específico desta programação)',
  `local` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Local específico deste item de programação - percurso ou atividade (diferente de eventos.local que é o local geral)',
  `latitude` decimal(10,8) DEFAULT NULL COMMENT 'Latitude do local específico deste item de programação',
  `longitude` decimal(11,8) DEFAULT NULL COMMENT 'Longitude do local específico deste item de programação',
  `ordem` int(11) DEFAULT '0',
  `ativo` tinyint(1) DEFAULT '1',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `programacao_evento`
--

INSERT INTO `programacao_evento` (`id`, `evento_id`, `tipo`, `titulo`, `descricao`, `hora_inicio`, `hora_fim`, `local`, `latitude`, `longitude`, `ordem`, `ativo`, `data_criacao`) VALUES
(11, 8, 'horario_largada', 'Horário de largada ', 'Visuais e cadeirantes : 05:45 - 05:50\nEssa largada é para as pessoas portadores de necessidades especiais.', '05:45:00', '05:50:00', NULL, NULL, NULL, 1, 1, '2025-12-22 23:45:09'),
(12, 8, 'horario_largada', 'Largada da Prova ', 'Público em geral : 06:00 - 06:10\nLargada oficial da prova.', '06:00:00', '06:10:00', NULL, NULL, NULL, 1, 1, '2025-12-24 03:09:26'),
(13, 10, 'percurso', 'Largada da Prova ', 'Rua um', NULL, NULL, 'Rua um', NULL, NULL, 1, 1, '2026-02-17 04:34:54');

-- --------------------------------------------------------

--
-- Estrutura para tabela `progresso_treino`
--

CREATE TABLE `progresso_treino` (
  `id` int(11) NOT NULL,
  `treino_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `data_realizado` date NOT NULL,
  `percepcao_esforco` int(11) DEFAULT NULL COMMENT 'PSE de 0 a 10 (recomendado 3-5 para diabetes)',
  `duracao_minutos` int(11) DEFAULT NULL,
  `glicemia_pre_treino` int(11) DEFAULT NULL COMMENT 'Glicemia antes do treino (mg/dL)',
  `glicemia_pos_treino` int(11) DEFAULT NULL COMMENT 'Glicemia após o treino (mg/dL)',
  `sinais_alerta_observados` text COLLATE utf8mb4_unicode_ci COMMENT 'Tontura, sudorese fria, tremores, fraqueza, visão turva, confusão mental',
  `mal_estar_observado` enum('sim','nao') COLLATE utf8mb4_unicode_ci DEFAULT 'nao' COMMENT 'Houve mal-estar durante ou após o treino',
  `observacoes` text COLLATE utf8mb4_unicode_ci COMMENT 'Alterações glicêmicas, desconfortos anormais e outras observações',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `fonte` enum('atleta','assessor') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'atleta',
  `registrado_por_usuario_id` int(11) DEFAULT NULL,
  `feedback_atleta` text COLLATE utf8mb4_unicode_ci,
  `feedback_assessor` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(17, 8, 0, 'campo', 'texto_aberto', 'atleta', NULL, 'Em caso de incidentes a organização entra em contato com? (*Informe nome e DDD fone)', 1, 0, 1, 'publicada', 'publicada', '2025-12-20 17:27:36'),
(18, 8, 0, 'campo', 'texto_aberto', 'evento', NULL, 'Para você que espécie é considerada o símbolo da cidade de Manaus?', 0, 0, 1, 'publicada', 'publicada', '2025-12-21 02:04:19'),
(19, 8, 0, 'campo', 'texto_aberto', 'evento', NULL, 'Qual ação em prol do meio ambiente que você pratica no seu dia-a-dia (em casa, no trabalho, bairro ou escola)?', 0, 0, 1, 'publicada', 'publicada', '2025-12-21 02:05:07'),
(21, 8, 0, 'campo', 'texto_aberto', 'evento', NULL, 'Você sabe as consequências para todos nós quando um animal é extinto?', 0, 5, 1, 'publicada', 'publicada', '2025-12-21 02:06:16'),
(22, 8, 0, 'campo', 'texto_aberto', 'evento', NULL, 'Você já participou de alguma ação de Educação Ambiental para conservação de alguma espécie em Manaus?', 0, 6, 1, 'publicada', 'publicada', '2025-12-21 02:06:41'),
(23, 8, 0, 'campo', 'texto_aberto', 'atleta', NULL, '	  Declara que este participante está apto fisicamente e leu o REGULAMENTO estando de acordo para participar do evento?', 1, 0, 1, 'publicada', 'publicada', '2025-12-21 02:07:28'),
(24, 8, 0, 'campo', 'texto_aberto', 'atleta', NULL, 'Ciente que a criança recebe apenas camiseta e medalha, e não o kit completo?', 0, 8, 1, 'publicada', 'publicada', '2025-12-21 02:07:49'),
(25, 8, 0, 'campo', 'texto_aberto', 'atleta', NULL, '  Informe primeiro nome ou apelido para número de peito', 0, 0, 1, 'publicada', 'publicada', '2025-12-21 02:08:11'),
(26, 8, 0, 'campo', 'texto_aberto', 'evento', NULL, 'Você conhece qual é o animal que é o Símbolo de Manaus?', 0, 10, 1, 'publicada', 'publicada', '2025-12-21 02:09:14'),
(27, 8, 0, 'campo', 'texto_aberto', 'evento', NULL, 'Você já participou das edições anteriores da corrida sauim-de-coleira?', 0, 11, 1, 'publicada', 'publicada', '2025-12-21 02:09:40'),
(28, 8, 0, 'campo', 'texto_aberto', 'evento', NULL, '  O que você pode fazer para contribuir com a preservação do Sauim-de-Coleira?', 0, 12, 1, 'publicada', 'publicada', '2025-12-21 02:10:11'),
(29, 8, 0, 'campo', 'texto_aberto', 'evento', NULL, '  Você acha importante conservar os animais ameaçados de extinção?', 0, 13, 1, 'publicada', 'publicada', '2025-12-21 02:10:34'),
(30, 10, 0, 'campo', 'texto_aberto', 'atleta', NULL, 'Em caso de incidentes a organização entra em contato com? (*Informe nome e DDD fone)', 1, 0, 1, 'publicada', 'publicada', '2026-02-17 04:04:34'),
(31, 10, 0, 'campo', 'checkbox', 'atleta', NULL, 'QUAL O TAMANHO DA SUA CAMISETA', 1, 2, 1, 'publicada', 'publicada', '2026-02-17 04:05:15'),
(32, 10, 0, 'campo', 'texto_aberto', 'atleta', NULL, 'QUAL O NOME DA EQUIPE', 1, 0, 1, 'publicada', 'publicada', '2026-02-17 04:06:04');

-- --------------------------------------------------------

--
-- Estrutura para tabela `questionario_evento_modalidade`
--

CREATE TABLE `questionario_evento_modalidade` (
  `id` int(11) NOT NULL,
  `questionario_evento_id` int(11) NOT NULL,
  `modalidade_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `questionario_evento_modalidade`
--

INSERT INTO `questionario_evento_modalidade` (`id`, `questionario_evento_id`, `modalidade_id`) VALUES
(8, 21, 23),
(9, 22, 23),
(11, 24, 23),
(13, 26, 23),
(14, 27, 23),
(15, 28, 23),
(16, 29, 23),
(17, 16, 23),
(19, 18, 23),
(20, 19, 23),
(23, 25, 23),
(24, 17, 23),
(25, 17, 24),
(26, 17, 21),
(27, 17, 22),
(28, 23, 23),
(29, 23, 24),
(30, 23, 21),
(31, 23, 22),
(34, 31, 26),
(35, 31, 28),
(38, 30, 26),
(39, 30, 28),
(40, 32, 26),
(41, 32, 28);

-- --------------------------------------------------------

--
-- Estrutura para tabela `repasse_organizadores`
--

CREATE TABLE `repasse_organizadores` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `organizador_id` int(11) NOT NULL,
  `valor_total` decimal(10,2) DEFAULT NULL,
  `data_repasse` datetime DEFAULT NULL,
  `status` enum('pendente','realizado','cancelado') COLLATE utf8mb4_unicode_ci DEFAULT 'pendente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `resultados_evento`
--

CREATE TABLE `resultados_evento` (
  `id` int(11) NOT NULL,
  `inscricao_id` int(11) NOT NULL,
  `tempo_final` time DEFAULT NULL,
  `colocacao` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `retirada_kits_evento`
--

CREATE TABLE `retirada_kits_evento` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `data_retirada` date DEFAULT NULL,
  `horario_inicio` time DEFAULT NULL,
  `horario_fim` time DEFAULT NULL,
  `local_retirada` text COLLATE utf8mb4_unicode_ci,
  `endereco_completo` text COLLATE utf8mb4_unicode_ci,
  `instrucoes_retirada` text COLLATE utf8mb4_unicode_ci,
  `retirada_terceiros` text COLLATE utf8mb4_unicode_ci,
  `documentos_necessarios` text COLLATE utf8mb4_unicode_ci,
  `ativo` tinyint(1) DEFAULT '1',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `retirada_kits_evento`
--

INSERT INTO `retirada_kits_evento` (`id`, `evento_id`, `data_retirada`, `horario_inicio`, `horario_fim`, `local_retirada`, `endereco_completo`, `instrucoes_retirada`, `retirada_terceiros`, `documentos_necessarios`, `ativo`, `data_criacao`) VALUES
(5, 8, '2026-10-23', '10:00:00', '20:00:00', 'Reitoria da Universidade do  Estado do Amazonas', NULL, 'Trazer o comprovante de inscrição.', NULL, 'IDT ou CNH com foto.', 1, '2025-12-20 12:21:44'),
(7, 10, '2026-09-11', '10:00:00', '19:00:00', 'Imbituba -SC', NULL, '', NULL, 'RG com foto e comprovante de inscrição.', 1, '2026-02-17 04:17:56');

-- --------------------------------------------------------

--
-- Estrutura para tabela `solicitacoes_evento`
--

CREATE TABLE `solicitacoes_evento` (
  `id` int(11) NOT NULL,
  `responsavel_nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `responsavel_email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `responsavel_telefone` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `responsavel_documento` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responsavel_rg` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responsavel_cargo` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `empresa` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `regiao` char(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cidade_evento` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uf_evento` char(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `modalidade_esportiva` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantidade_eventos` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nome_evento` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_prevista` date DEFAULT NULL,
  `estimativa_participantes` int(11) DEFAULT NULL,
  `regulamento_status` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link_regulamento` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `possui_autorizacao` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link_autorizacao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `necessidades` text COLLATE utf8mb4_unicode_ci,
  `descricao_evento` text COLLATE utf8mb4_unicode_ci,
  `indicacao` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preferencia_contato` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `documentos_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('novo','em_analise','aprovado','recusado') COLLATE utf8mb4_unicode_ci DEFAULT 'novo',
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `solicitacoes_evento`
--

INSERT INTO `solicitacoes_evento` (`id`, `responsavel_nome`, `responsavel_email`, `responsavel_telefone`, `responsavel_documento`, `responsavel_rg`, `responsavel_cargo`, `empresa`, `regiao`, `cidade_evento`, `uf_evento`, `modalidade_esportiva`, `quantidade_eventos`, `nome_evento`, `data_prevista`, `estimativa_participantes`, `regulamento_status`, `link_regulamento`, `possui_autorizacao`, `link_autorizacao`, `necessidades`, `descricao_evento`, `indicacao`, `preferencia_contato`, `documentos_link`, `status`, `criado_em`, `atualizado_em`) VALUES
(3, 'EUDIMACI BARBOZA DE LIRA', 'eudimaci08@yahoo.com.br', '+55 +55 92982027654', '61.508.962/0001-40', '', 'CEO', 'EBL EVENTOS ESPORTIVOS', 'AM', 'Manaus', 'AM', 'corrida-rua', '1', 'III CORRIDA SAUIM DE COLEIRA', '2026-10-24', 2000, 'sim', 'regulamento_20251219112132_49fc179f.pdf', 'sim', NULL, '', 'A III Corrida Sauim de Coleira engloba três grandes pilares da nossa sociedade, esporte, por meio da prática de atividade física, meio ambiente, por meio da busca pela preservação ambiental e pela proteção do sauim-de-coleira e a Educação, por meio do Projeto de extensão da UEA o Projeto Primatas, que visa levar para a sociedade manauara conhecimentos de conservação da biodiversidade e sensibilização ambiental.', 'Amigos', '', '', 'aprovado', '2025-12-19 14:21:32', '2025-12-19 14:31:48'),
(4, 'EUDIMACI BARBOZA DE LIRA', 'moveromundobrasil@gmail.com', '+55 +55 92981630385', '646.557.334-20', '', 'Ceo', 'Projeto Primatas.', 'AM', 'Manaus', 'AM', 'corrida-rua', '1', 'Rota das Baleias', '2026-09-12', 500, 'sim', NULL, 'em-andamento', NULL, '', 'Evento único em Santa Catarina', '', 'whatsapp', '', 'aprovado', '2026-02-02 19:52:02', '2026-02-17 03:38:54'),
(5, 'EUDIMACI BARBOZA DE LIRA', 'eudimaci08@yahoo.com.br', '+55 +55 92982027654', '61.508.962/0001-40', '', 'CEO', 'EBL Eventos Esportivos', 'AM', 'Manaus', 'AM', 'corrida-rua', '2-4', 'III Corrida Sauim de Coleira', '2026-10-24', 2000, 'sim', 'regulamento_20260207111921_f4ecda5c.pdf', 'sim', NULL, '', 'Evento esportivo para conscientizar pela possível extinção do Sauim de Coleira', 'Internet', 'whatsapp', '', 'aprovado', '2026-02-07 14:19:21', '2026-02-17 03:38:04');

-- --------------------------------------------------------

--
-- Estrutura para tabela `termos_eventos`
--

CREATE TABLE `termos_eventos` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `conteudo` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `versao` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT '1.0',
  `ativo` tinyint(1) DEFAULT '1',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `tipo` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inscricao' COMMENT 'inscricao, anamnese ou treino'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `termos_eventos`
--

INSERT INTO `termos_eventos` (`id`, `titulo`, `conteudo`, `versao`, `ativo`, `data_criacao`, `tipo`) VALUES
(3, 'Termo de Aceite a Plataforma', '<p><strong>TERMO DE USO PARA O USUÁRIO QUE DESEJA SE INSCREVER NO EVENTO.</strong></p><p>Como usuário do sistema <strong>MOVAMAZON</strong>&nbsp;você declara ter conhecimento e aceitar as seguintes cláusulas e condições:</p><ol><li>Estar ciente que:<ul><li>&nbsp;Todo o conteúdo como textos, fotos e vídeos dos eventos publicados no sistema&nbsp;<strong>MOVAMAZON</strong>&nbsp;são de responsabilidade integral do ORGANIZADOR do evento, cujos dados constam da página do evento e que o&nbsp;<strong>MOVAMAZON</strong>&nbsp;não exerce qualquer controle ou supervisão prévia</li><li>&nbsp;O ORGANIZADOR do evento é o único responsável pelo planejamento, organização e realização do evento sendo ele quem define as atrações, programação, local de realização, datas, valores dos ingressos, limite de ingressos disponíveis, idades mínimas e máximas, descontos eventuais que podem ser oferecidos entre outros.</li><li>&nbsp;Qualquer alteração na programação do evento, seja troca de datas, local ou até o seu cancelamento, bem como quaisquer ocorrências durante o EVENTO, são de responsabilidade única e exclusiva do ORGANIZADOR.</li></ul></li></ol><p>&nbsp;</p><ol><li>Que todas as informações por você declaradas no cadastro do sistema e na compra de ingressos e inscrição em eventos através do sistema&nbsp;<strong>MOVAMAZON</strong>&nbsp;são verdadeiras e estão de acordo com as leis vigentes.</li></ol><p>&nbsp;</p><ol><li>Que você está ciente e concorda com as REGRAS, REGULAMENTOS e TERMO ACORDO dos eventos divulgados no sistema&nbsp;<strong>MOVAMAZON</strong>&nbsp;a que for participar.</li></ol><p>&nbsp;</p><ol><li>Que você possui as habilidades técnicas e está apto fisicamente para participar dos eventos que comprar no&nbsp;<strong>MOVAMAZON</strong>&nbsp;isentando o&nbsp;<strong>MOVAMAZON</strong>&nbsp;e o ORGANIZADOR de qualquer problema de saúde que possa ocorrer pelo fato da participação no mesmo.</li></ol><p>&nbsp;</p><ol><li>Para a hipótese de você estar efetuando a inscrição em nome de terceiros, que você possui todos os direitos de representação de terceiros, possuindo autorização do terceiro para fornecimento dos dados de cadastro e, nos casos de realização de compras e inscrições para eles, declarando que o terceiro que participará do evento possui habilidades técnicas e está apto fisicamente a participar dos eventos que comprar no&nbsp;<strong>MOVAMAZON</strong>&nbsp;isentando o&nbsp;<strong>MOVAMAZON</strong>&nbsp;e o ORGANIZADOR de qualquer problema que possa ocorrer pelo fato da participação no mesmo.</li></ol><p>&nbsp;</p><ol><li>Que você autoriza o uso do seu direito de imagem, e dos terceiros por você inscritos e dos quais você possui autorização, para promoção de divulgação do evento, pelo ORGANIZADOR,&nbsp;<strong>MOVAMAZON</strong>&nbsp;e patrocinadores.</li></ol><p>&nbsp;</p><ol><li>Que você está ciente que o simples ato de efetuar o pedido no&nbsp;<strong>MOVAMAZON</strong>&nbsp;não garante o seu ingresso no evento e não garante o seu direito à retirada de eventuais tickets, sendo os mesmos garantidos apenas mediante a confirmação do pagamento do valor integral cobrado, no caso de opção de pagamento via boleto (para usuários no Brasil) ou no momento da autorização dada pelo cartão de crédito para débito do valor integral devido pela inscrição, no caso opção de pagamento via cartões de crédito.</li></ol><p>&nbsp;</p><ol><li>Que você está ciente de que eventuais pagamentos efetuados em valor divergente ao efetivamente devido (seja inferior ou superior) NÃO gerarão uma inscrição válida e não garantirão o seu ingresso no evento ou lhe darão direito à retirada de eventuais kits relativos ao evento.</li></ol><p>&nbsp;</p><ol><li>Que você está ciente de que caso a inscrição dê direito à retirada de kits, os mesmos somente poderão ser retirados nas datas e horários fixados pelo ORGANIZADOR e previstos na \"página de apresentação e inscrições\" do EVENTO. A eventual não retirada dos kits nas datas estabelecidas resultará na PERDA DO SEU DIREITO DE RETIRAR referidos kits.</li></ol><p>&nbsp;</p><ol><li>Em caso de arrependimento da compra, independente do motivo será realizado o estorno do valor pago desde que seja solicitado em até 7 dias corridos após a efetivação do pedido E desde que falte no mínimo 7 dias para o início do evento (ou de sua entrega de kit quando houver). Ambos os requisitos acima deverão ser preenchidos para que o estorno seja efetuado. Portanto, fica expressamente estabelecido que:<br>a) NÃO será efetuado estorno de valores solicitados após o prazo de pagamento de 7 (sete) dias, ainda que a solicitação ocorra faltando 7 (sete) dias ou mais para o início do evento,<br>b) também NÃO será efetuado estorno de valores solicitados dentro do prazo de pagamento de 7 (sete) dias, caso a solicitação ocorra a menos de 7 (sete) dias do início do evento ou da entrega dos kits quando houver.</li></ol><p>&nbsp;</p><ol><li>Nos casos em que for cabível o estorno por terem sido cumpridos cumulativamente os dois requisitos (pedido dentro do prazo de arrependimento e faltando 7 dias ou mais para o início do evento ou entrega dos kits), se o pedido foi pago com cartão de crédito o estorno será realizado no mesmo cartão utilizado sendo creditado em sua próxima fatura ou subsequente. Caso o pedido tenha sido pago com boleto bancário (para usuários no Brasil) ou outra forma de pagamento, o estorno será realizado em conta bancária do responsável pelo pedido em até 30 dias úteis após informada. A solicitação do estorno deve ser realizada por escrito através do link&nbsp;<a href=\"https://ajuda.ticketsports.com.br/hc/pt-br\">ajuda.ticketsports.com.br/pt-br</a>&nbsp;informando o número do pedido, nome, CPF (ou o documento de identificação nacional de acordocom o país do usuário) e e-mail do responsável utilizado para realizar o pedido.</li></ol><p>&nbsp;</p><ol><li>Que você isenta o&nbsp;<strong>MOVAMAZON</strong>&nbsp;de qualquer responsabilidade relacionada ao evento, de modo que quaisquer reclamações, tais como mas não limitadas a pedido de estorno, desistência de inscrições e pedidos de reembolso (ressalvado o disposto no item acima), adiamento ou cancelamento ou quaisquer outros assuntos relacionados ao evento deverão ser formulados diretamente ao ORGANIZADOR.</li></ol><p>&nbsp;</p><ol><li>Que a desistência de participação do evento quando não preenchidos os requisitos estabelecidos no item 10 apenas será possível nos casos e condições estabelecidos pelo organizador, no Regulamento da prova, devendo ser solicitada diretamente a ele. Em sendo possível, a devolução do valor ocorrerá nos termos determinados pelo organizador.</li></ol><p>&nbsp;</p><ol><li>Que você isenta o&nbsp;<strong>MOVAMAZON</strong>&nbsp;de qualquer responsabilidade relacionada à links existentes no \"site\" do&nbsp;<strong>MOVAMAZON</strong>&nbsp;ou a conteúdo assinado por terceiros, tal qual, colunas, blogs, comentários nos fóruns, vídeos, fotos, comentários nas notícias, mapas de treino, entre outros são de inteira responsabilidade de seus autores.</li></ol><p>&nbsp;</p><ol><li>Que você está ciente da impossibilidade de reprodução de qualquer conteúdo divulgado no&nbsp;<strong>MOVAMAZON</strong>, seja integral ou parcial, seja para uso comercial, pessoal ou editorial é proibido, salvo prévia autorização por escrito do&nbsp;<strong>MOVAMAZON</strong>&nbsp;ou pelo seu autor quando identificado.</li></ol><p>&nbsp;</p><ol><li>Que você está ciente que: qualquer contato com a&nbsp;<strong>MOVAMAZON</strong>&nbsp;deverá ocorrer por carta a ser endereçada à Rua Ricardo Severo, 73, Cep: 05010-010 Perdizes, Município de São Paulo, Estado de São Paulo, Brasil;<br>b) que o relacionamento com a&nbsp;<strong>MOVAMAZON</strong>&nbsp;é regido pela legislação brasileira, e,<br>c) que eventual disputa deverá ser solucionada na jurisdição brasileira competente (Foro do Estado de São Paulo).</li></ol><p>&nbsp;</p><ol><li>Que você tem conhecimento de que as regras específicas da prova são regidas pelo Regulamento elaborado pelo organizador e disponível no “site” da&nbsp;<strong>MOVAMAZON</strong>.</li></ol><p>&nbsp;</p><p>Este termo será registrado em cartório e cancela e substitui o termo registrado no 3º Cartório de Títulos e Documentos de São Paulo sob no&nbsp;<strong>9113638</strong>&nbsp;em&nbsp;<strong>03 de outubro de 2023</strong>&nbsp;e qualquer outro termo de compras posterior ao antes citado, registrado ou não.</p><p>&nbsp;</p><p>Este termo poderá ser alterado, a qualquer tempo, mediante registro do novo termo em substituição ao presente, sendo válido para os eventos que ocorrerem a partir do novo registro.</p><p><strong>Manaus, AM em 22 de dezembro de 2025.</strong></p><p>&nbsp;</p><p>MovAmazon.</p>', '1.0', 1, '2025-12-21 02:14:14', 'inscricao');

-- --------------------------------------------------------

--
-- Estrutura para tabela `transacoes_mp_cache`
--

CREATE TABLE `transacoes_mp_cache` (
  `id` bigint(20) NOT NULL,
  `payment_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID único do Mercado Pago',
  `external_reference` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Referência da inscrição',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'approved, rejected, pending, etc',
  `status_detail` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Detalhe específico do status',
  `transaction_amount` decimal(10,2) NOT NULL COMMENT 'Valor total da transação',
  `net_amount` decimal(10,2) DEFAULT NULL COMMENT 'Valor líquido (após taxas)',
  `fee_amount` decimal(10,2) DEFAULT NULL COMMENT 'Total de taxas',
  `payment_method_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'pix, bolbradesco, visa, etc',
  `payment_type_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ticket, credit_card, etc',
  `installments` int(11) DEFAULT '1' COMMENT 'Número de parcelas',
  `date_created` datetime NOT NULL COMMENT 'Data de criação da transação',
  `date_approved` datetime DEFAULT NULL COMMENT 'Data de aprovação',
  `date_last_updated` datetime DEFAULT NULL COMMENT 'Última atualização no MP',
  `payer_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payer_first_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payer_last_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payer_identification_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'CPF, CNPJ, etc',
  `payer_identification_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dados_completos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin COMMENT 'JSON completo da transação',
  `ultima_sincronizacao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Última vez que foi atualizado',
  `origem` enum('webhook','consulta_manual','sincronizacao_automatica') COLLATE utf8mb4_unicode_ci DEFAULT 'consulta_manual' COMMENT 'De onde veio a sincronização'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cache de transações do Mercado Pago para consultas rápidas';

-- --------------------------------------------------------

--
-- Estrutura para tabela `treinos`
--

CREATE TABLE `treinos` (
  `id` int(11) NOT NULL,
  `anamnese_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `plano_treino_gerado_id` int(11) NOT NULL,
  `nome` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `nivel_dificuldade` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'intermediario',
  `dia_semana_id` int(11) DEFAULT NULL COMMENT '1=Domingo, 2=Segunda, etc.',
  `semana_numero` int(11) DEFAULT NULL COMMENT 'Número da semana do plano (1, 2, 3, etc.)',
  `parte_inicial` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON com exercícios de aquecimento',
  `parte_principal` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON com exercícios principais',
  `volta_calma` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON com exercícios de volta à calma',
  `fcmax` int(11) DEFAULT NULL COMMENT 'Frequência cardíaca máxima',
  `volume_total` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grupos_musculares` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_series` int(11) DEFAULT '3',
  `intervalo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_repeticoes` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `intensidade` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `carga_interna` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ativo` tinyint(1) DEFAULT '1',
  `assessoria_id` int(11) DEFAULT NULL,
  `programa_id` int(11) DEFAULT NULL,
  `criado_por_usuario_id` int(11) DEFAULT NULL,
  `status` enum('rascunho','ativo','pausado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'rascunho',
  `publicado_em` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `treinos`
--

INSERT INTO `treinos` (`id`, `anamnese_id`, `usuario_id`, `plano_treino_gerado_id`, `nome`, `descricao`, `nivel_dificuldade`, `dia_semana_id`, `semana_numero`, `parte_inicial`, `parte_principal`, `volta_calma`, `fcmax`, `volume_total`, `grupos_musculares`, `numero_series`, `intervalo`, `numero_repeticoes`, `intensidade`, `carga_interna`, `observacoes`, `data_criacao`, `ativo`, `assessoria_id`, `programa_id`, `criado_por_usuario_id`, `status`, `publicado_em`) VALUES
(18, 5, 17, 3, 'Adaptação Inicial e Mobilidade', 'Treino de adaptação com foco em corrida leve e fortalecimento muscular.', 'iniciante', 1, 1, '[{\"nome_item\":\"Aquecimento em Esteira\",\"detalhes_item\":\"Caminhada leve progredindo para trote suave\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"10 minutos\"}]', '[{\"nome_item\":\"Corrida Contínua Leve\",\"detalhes_item\":\"Corrida em ritmo conversacional, mantendo FC controlada\",\"fc_alvo\":\"65-75% FCmax (117-135 bpm)\",\"tempo_execucao\":\"20 minutos\",\"distancia\":\"3-4 km\",\"velocidade\":\"Ritmo conversacional\",\"observacoes\":\"Manter respiração controlada, poder conversar durante a corrida\"},{\"nome_item\":\"Agachamento Isométrico\",\"detalhes_item\":\"3 séries de 30 segundos\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"30s por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Peso corporal\",\"series\":\"3\",\"repeticoes\":\"30s\"},{\"nome_item\":\"Elevação Pélvica\",\"detalhes_item\":\"3 séries de 15 repetições\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"45s por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Peso corporal\",\"series\":\"3\",\"repeticoes\":\"15\"},{\"nome_item\":\"Cadeira Extensora\",\"detalhes_item\":\"3 séries de 12 repetições, ROM controlado\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"45s por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Leve\",\"series\":\"3\",\"repeticoes\":\"12\"},{\"nome_item\":\"Prancha\",\"detalhes_item\":\"3 séries de 30 segundos\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"30s por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"series\":\"3\",\"repeticoes\":\"30s\"}]', '[{\"nome_item\":\"Alongamento de Membros Inferiores\",\"detalhes_item\":\"Alongamento suave para quadríceps, isquiotibiais e panturrilhas\",\"tempo_execucao\":\"10 minutos\"}]', NULL, '50 minutos', 'Cardiorrespiratório, Pernas, Core', 3, 'N/A', 'N/A', 'N/A', 'N/A', 'Qualquer sinal de dor ou desconforto, interrompa o exercício imediatamente.\n\nJustificativa de Adaptações:\nInício do programa com foco em adaptação cardiovascular e fortalecimento, evitando sobrecarga no joelho.\n\n==Bibliografia Consultada==\n==Bibliografia Recomendada==\n- ACSM Guidelines for Exercise Testing and Prescription, 11th Edition - https://www.acsm.org/read-research/books/acsms-guidelines-for-exercise-testing-and-prescription\n- Diretrizes de treinamento para corrida de rua - Referências científicas sobre periodização\n', '2026-02-02 17:21:34', 1, NULL, NULL, NULL, 'rascunho', NULL),
(19, 5, 17, 3, 'Corrida Intervalada e Fortalecimento', 'Treino de corrida intervalada para melhorar a capacidade aeróbica e fortalecimento muscular.', 'intermediário', 3, 1, '[{\"nome_item\":\"Aquecimento Dinâmico\",\"detalhes_item\":\"Mobilidade articular e ativação muscular\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"10 minutos\"}]', '[{\"nome_item\":\"Corrida Intervalada\",\"detalhes_item\":\"5 x 1 minuto em ritmo forte, 2 minutos de recuperação\",\"fc_alvo\":\"80-90% FCmax (144-162 bpm)\",\"tempo_execucao\":\"15 minutos\",\"distancia\":\"Variante\",\"velocidade\":\"Ritmo forte\",\"observacoes\":\"Manter a técnica durante os intervalos rápidos\"},{\"nome_item\":\"Elevação de Panturrilha\",\"detalhes_item\":\"3 séries de 15 repetições\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"45s por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Peso corporal\",\"series\":\"3\",\"repeticoes\":\"15\"},{\"nome_item\":\"Abdominal Supra\",\"detalhes_item\":\"3 séries de 20 repetições\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"45s por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"series\":\"3\",\"repeticoes\":\"20\"},{\"nome_item\":\"Flexão de Braço\",\"detalhes_item\":\"3 séries de 10 repetições\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"45s por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Peso corporal\",\"series\":\"3\",\"repeticoes\":\"10\"}]', '[{\"nome_item\":\"Alongamento de Corpo Inteiro\",\"detalhes_item\":\"Alongamento estático para relaxamento muscular\",\"tempo_execucao\":\"10 minutos\"}]', NULL, '50 minutos', 'Cardiorrespiratório, Pernas, Core, Peitoral', 3, 'N/A', 'N/A', 'N/A', 'N/A', 'Monitorar a resposta do corpo durante os intervalos rápidos.\n\nJustificativa de Adaptações:\nIncluídos exercícios de baixo impacto para evitar sobrecarga no joelho.', '2026-02-02 17:21:34', 1, NULL, NULL, NULL, 'rascunho', NULL),
(20, 5, 17, 3, 'Corrida Longa e Recuperação Ativa', 'Treino de corrida longa em ritmo confortável e recuperação ativa.', 'intermediário', 5, 1, '[{\"nome_item\":\"Aquecimento com Mobilidade\",\"detalhes_item\":\"Mobilidade dinâmica para membros inferiores\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"10 minutos\"}]', '[{\"nome_item\":\"Corrida Longa\",\"detalhes_item\":\"Corrida contínua em ritmo confortável\",\"fc_alvo\":\"65-75% FCmax (117-135 bpm)\",\"tempo_execucao\":\"40 minutos\",\"distancia\":\"6-7 km\",\"velocidade\":\"Ritmo confortável\",\"observacoes\":\"Focar na técnica de corrida e respiração\"}]', '[{\"nome_item\":\"Caminhada Leve\",\"detalhes_item\":\"Caminhada para desacelerar o ritmo cardíaco\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"10 minutos\"},{\"nome_item\":\"Alongamento de Membros Inferiores\",\"detalhes_item\":\"Alongamento suave para quadríceps, isquiotibiais e panturrilhas\",\"tempo_execucao\":\"10 minutos\"}]', NULL, '60 minutos', 'Cardiorrespiratório, Pernas', 3, 'N/A', 'N/A', 'N/A', 'N/A', 'Manter a hidratação e ajustar a intensidade conforme necessário.\n\nJustificativa de Adaptações:\nCorrida longa em ritmo controlado para melhorar a resistência sem sobrecarregar o joelho.', '2026-02-02 17:21:34', 1, NULL, NULL, NULL, 'rascunho', NULL),
(21, 3, 18, 4, 'Adaptação e Mobilidade', 'Treino de adaptação inicial com foco em corrida leve e exercícios de mobilidade.', 'iniciante', 1, 1, '[{\"nome_item\":\"Caminhada Rápida\",\"detalhes_item\":\"Aquecimento com caminhada rápida para aumentar a temperatura corporal.\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"10 minutos\"}]', '[{\"nome_item\":\"Corrida Leve\",\"detalhes_item\":\"Corrida em ritmo leve, focando na técnica e respiração.\",\"fc_alvo\":\"65-75% FCmax (117-135 bpm)\",\"tempo_execucao\":\"20 minutos\",\"distancia\":\"3 km\",\"velocidade\":\"Ritmo confortável\",\"observacoes\":\"Manter o controle da respiração e postura.\"},{\"nome_item\":\"Exercícios de Mobilidade\",\"detalhes_item\":\"Movimentos dinâmicos para articulações do quadril e tornozelo.\",\"tempo_execucao\":\"10 minutos\"},{\"nome_item\":\"Agachamento Livre\",\"detalhes_item\":\"3 séries de 12 repetições, foco na técnica.\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"45s por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Peso corporal\",\"series\":\"3\",\"repeticoes\":\"12\"},{\"nome_item\":\"Prancha\",\"detalhes_item\":\"Fortalecimento do core, 3 séries de 30 segundos.\",\"fc_alvo\":\"60-65% FCmax (108-117 bpm)\",\"tempo_execucao\":\"30s por série\",\"tempo_recuperacao\":\"30s\",\"tipo_recuperacao\":\"passivo\",\"series\":\"3\",\"repeticoes\":\"30s\"}]', '[{\"nome_item\":\"Alongamento Global\",\"detalhes_item\":\"Alongamento suave para todo o corpo.\",\"tempo_execucao\":\"10 minutos\"}]', NULL, '50 minutos', 'Cardiorrespiratório, Pernas, Core', 3, 'N/A', 'N/A', 'N/A', 'N/A', 'Monitorar sinais de fadiga e ajustar conforme necessário.\n\nJustificativa de Adaptações:\nTreino adaptado para iniciar progressivamente considerando o pré-diabetes e o IMC elevado.\n\n==Bibliografia Consultada==\n==Bibliografia Recomendada==\n- ACSM Guidelines for Exercise Testing and Prescription, 11th Edition - https://www.acsm.org/read-research/books/acsms-guidelines-for-exercise-testing-and-prescription\n- Diretrizes de treinamento para corrida de rua - Referências científicas sobre periodização\n- PubMed - Artigos sobre fisiologia do exercício e prevenção de lesões\n', '2026-02-03 17:34:38', 1, NULL, NULL, NULL, 'rascunho', NULL),
(22, 3, 18, 4, 'Corrida e Fortalecimento', 'Treino focado em corrida contínua e fortalecimento muscular.', 'iniciante', 3, 1, '[{\"nome_item\":\"Caminhada Rápida\",\"detalhes_item\":\"Aquecimento com caminhada rápida.\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"10 minutos\"}]', '[{\"nome_item\":\"Corrida Contínua\",\"detalhes_item\":\"Corrida em ritmo moderado, mantendo controle respiratório.\",\"fc_alvo\":\"65-75% FCmax (117-135 bpm)\",\"tempo_execucao\":\"25 minutos\",\"distancia\":\"4 km\",\"velocidade\":\"Ritmo moderado\",\"observacoes\":\"Focar na postura e técnica de corrida.\"},{\"nome_item\":\"Leg Press\",\"detalhes_item\":\"3 séries de 10 repetições, fortalecimento de membros inferiores.\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"45s por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Moderada\",\"series\":\"3\",\"repeticoes\":\"10\"},{\"nome_item\":\"Flexão de Braço\",\"detalhes_item\":\"3 séries de 8 repetições, fortalecimento de membros superiores.\",\"fc_alvo\":\"60-65% FCmax (108-117 bpm)\",\"tempo_execucao\":\"30s por série\",\"tempo_recuperacao\":\"45s\",\"tipo_recuperacao\":\"passivo\",\"series\":\"3\",\"repeticoes\":\"8\"}]', '[{\"nome_item\":\"Alongamento de Membros Inferiores\",\"detalhes_item\":\"Alongamento focado em quadríceps, panturrilhas e isquiotibiais.\",\"tempo_execucao\":\"10 minutos\"}]', NULL, '55 minutos', 'Cardiorrespiratório, Pernas, Membros Superiores', 3, 'N/A', 'N/A', 'N/A', 'N/A', 'Ajustar a carga dos exercícios de fortalecimento conforme necessário.\n\nJustificativa de Adaptações:\nInclusão de exercícios de fortalecimento para melhorar a resistência muscular e prevenir lesões.', '2026-02-03 17:34:38', 1, NULL, NULL, NULL, 'rascunho', NULL),
(23, 3, 18, 4, 'Natação e Recuperação Ativa', 'Sessão de natação para recuperação ativa e melhora cardiovascular.', 'iniciante', 5, 1, '[{\"nome_item\":\"Aquecimento na Piscina\",\"detalhes_item\":\"Nado leve para aquecimento.\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"10 minutos\"}]', '[{\"nome_item\":\"Natação Livre\",\"detalhes_item\":\"Nado contínuo em ritmo moderado.\",\"fc_alvo\":\"60-70% FCmax (108-126 bpm)\",\"tempo_execucao\":\"30 minutos\",\"distancia\":\"800-1000 metros\",\"velocidade\":\"Ritmo moderado\",\"observacoes\":\"Focar na técnica de nado e respiração.\"}]', '[{\"nome_item\":\"Alongamento na Água\",\"detalhes_item\":\"Alongamento suave no ambiente aquático.\",\"tempo_execucao\":\"10 minutos\"}]', NULL, '50 minutos', 'Cardiorrespiratório, Corpo Total', 3, 'N/A', 'N/A', 'N/A', 'N/A', 'Monitorar a intensidade e ajustar conforme necessário.\n\nJustificativa de Adaptações:\nA natação é uma atividade de baixo impacto, ideal para recuperação e controle do pré-diabetes.', '2026-02-03 17:34:38', 1, NULL, NULL, NULL, 'rascunho', NULL),
(24, 6, 28, 5, 'Corrida Leve e Mobilidade', 'Treino de adaptação com corrida leve e exercícios de mobilidade para preparação inicial.', 'iniciante', 1, 1, '[{\"nome_item\":\"Aquecimento em Esteira\",\"detalhes_item\":\"Caminhada leve progredindo para trote suave\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"10 minutos\"}]', '[{\"nome_item\":\"Corrida Contínua Leve\",\"detalhes_item\":\"Corrida em ritmo conversacional, mantendo FC controlada\",\"fc_alvo\":\"65-75% FCmax (117-135 bpm)\",\"tempo_execucao\":\"20 minutos\",\"distancia\":\"3-4 km\",\"velocidade\":\"Ritmo conversacional\",\"observacoes\":\"Manter respiração controlada, poder conversar durante a corrida\"},{\"nome_item\":\"Agachamento Livre\",\"detalhes_item\":\"3 séries de 15 repetições, foco na técnica\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"45s por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Peso corporal\",\"series\":\"3\",\"repeticoes\":\"15\"},{\"nome_item\":\"Prancha Abdominal\",\"detalhes_item\":\"3 séries de 30 segundos\",\"fc_alvo\":\"60-70% FCmax (108-126 bpm)\",\"tempo_execucao\":\"30 segundos por série\",\"tempo_recuperacao\":\"30s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Peso corporal\",\"series\":\"3\",\"repeticoes\":\"30 segundos\"},{\"nome_item\":\"Mobilidade de Quadril\",\"detalhes_item\":\"Exercícios de mobilidade para quadril\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"5 minutos\"}]', '[{\"nome_item\":\"Alongamento de Membros Inferiores\",\"detalhes_item\":\"Alongamento suave para quadríceps, isquiotibiais e panturrilhas\",\"tempo_execucao\":\"10 minutos\"}]', NULL, '45 minutos', 'Cardiorrespiratório, Pernas, Core', 3, 'N/A', 'N/A', 'N/A', 'N/A', 'Qualquer sinal de dor ou desconforto, interrompa o exercício imediatamente.\n\nJustificativa de Adaptações:\nTreino adaptado para nível iniciante com foco em adaptação cardiovascular e mobilidade articular.\n\n==Bibliografia Consultada==\n==Bibliografia Recomendada==\n- ACSM Guidelines for Exercise Testing and Prescription, 11th Edition - https://www.acsm.org/read-research/books/acsms-guidelines-for-exercise-testing-and-prescription\n- Diretrizes de treinamento para corrida de rua - Referências científicas sobre periodização\n- PubMed - Artigos sobre fisiologia do exercício e prevenção de lesões\n', '2026-02-06 11:53:53', 1, NULL, NULL, NULL, 'rascunho', NULL),
(25, 6, 28, 5, 'Treino Intervalado', 'Treino intervalado para melhorar a capacidade cardiovascular e resistência.', 'intermediário', 3, 1, '[{\"nome_item\":\"Aquecimento Dinâmico\",\"detalhes_item\":\"Movimentos dinâmicos para preparar o corpo\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"10 minutos\"}]', '[{\"nome_item\":\"Corrida Intervalada\",\"detalhes_item\":\"5x 1 minuto de corrida rápida com 2 minutos de trote leve\",\"fc_alvo\":\"75-85% FCmax (135-153 bpm) para corrida rápida\",\"tempo_execucao\":\"15 minutos\",\"distancia\":\"Aproximadamente 2-3 km\",\"velocidade\":\"Rápido durante os intervalos\",\"observacoes\":\"Focar na técnica durante os sprints\"},{\"nome_item\":\"Leg Press\",\"detalhes_item\":\"3 séries de 12 repetições\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"1 minuto por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Moderada\",\"series\":\"3\",\"repeticoes\":\"12\"},{\"nome_item\":\"Flexão de Braço\",\"detalhes_item\":\"3 séries de 10 repetições\",\"fc_alvo\":\"65-70% FCmax (117-126 bpm)\",\"tempo_execucao\":\"30 segundos por série\",\"tempo_recuperacao\":\"30s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Peso corporal\",\"series\":\"3\",\"repeticoes\":\"10\"}]', '[{\"nome_item\":\"Alongamento de Corpo Inteiro\",\"detalhes_item\":\"Alongamento suave para todo o corpo\",\"tempo_execucao\":\"10 minutos\"}]', NULL, '45 minutos', 'Cardiorrespiratório, Pernas, Braços, Core', 3, 'N/A', 'N/A', 'N/A', 'N/A', 'Monitorar a resposta do corpo aos intervalos e ajustar conforme necessário.\n\nJustificativa de Adaptações:\nIntrodução de treino intervalado para aumentar a resistência de forma segura.', '2026-02-06 11:53:53', 1, NULL, NULL, NULL, 'rascunho', NULL),
(26, 6, 28, 5, 'Corrida Longa', 'Corrida longa para aumentar a resistência aeróbica.', 'intermediário', 5, 1, '[{\"nome_item\":\"Aquecimento Articular\",\"detalhes_item\":\"Movimentos circulares para aquecer as articulações\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"10 minutos\"}]', '[{\"nome_item\":\"Corrida Longa\",\"detalhes_item\":\"Corrida em ritmo confortável para aumentar a resistência\",\"fc_alvo\":\"65-75% FCmax (117-135 bpm)\",\"tempo_execucao\":\"45 minutos\",\"distancia\":\"6-7 km\",\"velocidade\":\"Ritmo confortável\",\"observacoes\":\"Manter hidratação adequada durante a corrida\"},{\"nome_item\":\"Elevação de Panturrilha\",\"detalhes_item\":\"3 séries de 15 repetições\",\"fc_alvo\":\"65-70% FCmax (117-126 bpm)\",\"tempo_execucao\":\"30 segundos por série\",\"tempo_recuperacao\":\"30s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Peso corporal\",\"series\":\"3\",\"repeticoes\":\"15\"}]', '[{\"nome_item\":\"Alongamento Focado em Pernas\",\"detalhes_item\":\"Alongamento para quadríceps, isquiotibiais e panturrilhas\",\"tempo_execucao\":\"10 minutos\"}]', NULL, '65 minutos', 'Cardiorrespiratório, Pernas', 3, 'N/A', 'N/A', 'N/A', 'N/A', 'Observar sinais de fadiga e ajustar o ritmo conforme necessário.\n\nJustificativa de Adaptações:\nAumentar gradualmente a distância para construir resistência.', '2026-02-06 11:53:53', 1, NULL, NULL, NULL, 'rascunho', NULL),
(27, 6, 28, 5, 'Recuperação Ativa e Fortalecimento', 'Sessão de recuperação ativa com foco em fortalecimento muscular.', 'iniciante', 7, 1, '[{\"nome_item\":\"Caminhada Leve\",\"detalhes_item\":\"Caminhada em ritmo leve para aquecer\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"10 minutos\"}]', '[{\"nome_item\":\"Circuito de Fortalecimento\",\"detalhes_item\":\"Circuito com 4 exercícios: agachamento, flexão, prancha e elevação de panturrilha\",\"fc_alvo\":\"60-70% FCmax (108-126 bpm)\",\"tempo_execucao\":\"20 minutos\",\"observacoes\":\"Foco na técnica, número de repetições conforme capacidade\"},{\"nome_item\":\"Mobilidade e Flexibilidade\",\"detalhes_item\":\"Exercícios de mobilidade para quadris e tornozelos\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"5 minutos\"}]', '[{\"nome_item\":\"Alongamento Geral\",\"detalhes_item\":\"Alongamento suave para todo o corpo\",\"tempo_execucao\":\"10 minutos\"}]', NULL, '45 minutos', 'Cardiorrespiratório, Pernas, Braços, Core', 3, 'N/A', 'N/A', 'N/A', 'N/A', 'Focar na recuperação e na técnica dos exercícios de fortalecimento.\n\nJustificativa de Adaptações:\nDia de recuperação ativa para promover a recuperação e o fortalecimento.', '2026-02-06 11:53:53', 1, NULL, NULL, NULL, 'rascunho', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `treino_exercicios`
--

CREATE TABLE `treino_exercicios` (
  `id` int(11) NOT NULL,
  `treino_id` int(11) NOT NULL,
  `nome_exercicio` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exercicio_id` int(11) DEFAULT NULL,
  `series` int(11) DEFAULT NULL,
  `repeticoes` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tempo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tempo de duração do exercício',
  `peso` decimal(10,2) DEFAULT NULL,
  `tempo_descanso` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacoes` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON com dados completos do exercício',
  `tipo` enum('repeticao','tempo','livre') COLLATE utf8mb4_unicode_ci DEFAULT 'livre'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `treino_exercicios`
--

INSERT INTO `treino_exercicios` (`id`, `treino_id`, `nome_exercicio`, `exercicio_id`, `series`, `repeticoes`, `tempo`, `peso`, `tempo_descanso`, `observacoes`, `tipo`) VALUES
(26, 10, 'Aquecimento com Corrida Leve', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Aquecimento com Corrida Leve\",\"detalhes_item\":\"Corrida leve para preparar para os intervalos\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(27, 10, 'Intervalos de Corrida', NULL, NULL, NULL, '18 minutos', NULL, '2 minutos', '{\"nome_item\":\"Intervalos de Corrida\",\"detalhes_item\":\"6x 1 minuto de corrida rápida intercalados com 2 minutos de trote leve\",\"fc_alvo\":\"80-90% FCmax durante corrida (144-162 bpm)\",\"tempo_execucao\":\"18 minutos\",\"tempo_recuperacao\":\"2 minutos\",\"tipo_recuperacao\":\"ativo\",\"distancia\":\"Variante\",\"velocidade\":\"Rápido durante intervalos\",\"observacoes\":\"Foco em manter a técnica durante os sprints\"}', 'livre'),
(28, 10, 'Agachamento com Salto', NULL, 3, '10', '30s por série', 0.00, '60s', '{\"nome_item\":\"Agachamento com Salto\",\"detalhes_item\":\"3 séries de 10 repetições\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"30s por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Peso corporal\",\"series\":\"3\",\"repeticoes\":\"10\"}', 'livre'),
(29, 10, 'Caminhada e Alongamento', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Caminhada e Alongamento\",\"detalhes_item\":\"Caminhada leve seguida de alongamento geral\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(30, 11, 'Aquecimento com Corrida Leve', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Aquecimento com Corrida Leve\",\"detalhes_item\":\"Corrida leve para preparar para o tempo run\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(31, 11, 'Tempo Run', NULL, NULL, NULL, '20 minutos', NULL, NULL, '{\"nome_item\":\"Tempo Run\",\"detalhes_item\":\"Corrida contínua em ritmo de prova\",\"fc_alvo\":\"80-85% FCmax (144-153 bpm)\",\"tempo_execucao\":\"20 minutos\",\"distancia\":\"3-4 km\",\"velocidade\":\"Ritmo de prova\",\"observacoes\":\"Foco na manutenção do ritmo e técnica\"}', 'livre'),
(32, 11, 'Fortalecimento de Core', NULL, 3, '30s', '30s por série', 0.00, '60s', '{\"nome_item\":\"Fortalecimento de Core\",\"detalhes_item\":\"3 séries de 30 segundos de prancha\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"30s por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Peso corporal\",\"series\":\"3\",\"repeticoes\":\"30s\"}', 'livre'),
(33, 11, 'Alongamento de Membros Inferiores', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Alongamento de Membros Inferiores\",\"detalhes_item\":\"Alongamento suave para quadríceps, isquiotibiais e panturrilhas\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(34, 12, 'Caminhada Leve', NULL, NULL, NULL, '5 minutos', NULL, NULL, '{\"nome_item\":\"Caminhada Leve\",\"detalhes_item\":\"Caminhada leve para iniciar o treino\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"5 minutos\"}', 'livre'),
(35, 12, 'Corrida Leve', NULL, NULL, NULL, '20 minutos', NULL, NULL, '{\"nome_item\":\"Corrida Leve\",\"detalhes_item\":\"Corrida em ritmo confortável para recuperação ativa\",\"fc_alvo\":\"60-70% FCmax (108-126 bpm)\",\"tempo_execucao\":\"20 minutos\",\"distancia\":\"3-4 km\",\"velocidade\":\"Leve\",\"observacoes\":\"Manter respiração controlada e ritmo confortável\"}', 'livre'),
(36, 12, 'Mobilidade de Ombros', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Mobilidade de Ombros\",\"detalhes_item\":\"Exercícios para melhorar a mobilidade dos ombros\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(37, 12, 'Alongamento Completo', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Alongamento Completo\",\"detalhes_item\":\"Alongamento de todos os grupos musculares trabalhados\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(38, 13, 'Aquecimento com Corrida Leve', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Aquecimento com Corrida Leve\",\"detalhes_item\":\"Corrida leve para preparar para os intervalos\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(39, 13, 'Intervalos de Corrida', NULL, NULL, NULL, '12 minutos', NULL, '2 minutos', '{\"nome_item\":\"Intervalos de Corrida\",\"detalhes_item\":\"4x 1 minuto de corrida rápida intercalados com 2 minutos de trote leve\",\"fc_alvo\":\"75-85% FCmax durante corrida (135-153 bpm)\",\"tempo_execucao\":\"12 minutos\",\"tempo_recuperacao\":\"2 minutos\",\"tipo_recuperacao\":\"ativo\",\"distancia\":\"Variante\",\"velocidade\":\"Rápido durante intervalos\",\"observacoes\":\"Foco na técnica durante os sprints\"}', 'livre'),
(40, 13, 'Caminhada e Alongamento', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Caminhada e Alongamento\",\"detalhes_item\":\"Caminhada leve seguida de alongamento geral\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(41, 14, 'Caminhada Leve', NULL, NULL, NULL, '5 minutos', NULL, NULL, '{\"nome_item\":\"Caminhada Leve\",\"detalhes_item\":\"Caminhada leve para iniciar o treino\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"5 minutos\"}', 'livre'),
(42, 14, 'Corrida Leve', NULL, NULL, NULL, '15 minutos', NULL, NULL, '{\"nome_item\":\"Corrida Leve\",\"detalhes_item\":\"Corrida em ritmo confortável para manter a atividade\",\"fc_alvo\":\"60-70% FCmax (108-126 bpm)\",\"tempo_execucao\":\"15 minutos\",\"distancia\":\"2-3 km\",\"velocidade\":\"Leve\",\"observacoes\":\"Manter respiração controlada e ritmo confortável\"}', 'livre'),
(43, 14, 'Alongamento Completo', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Alongamento Completo\",\"detalhes_item\":\"Alongamento de todos os grupos musculares trabalhados\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(44, 15, 'Aquecimento em Esteira', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Aquecimento em Esteira\",\"detalhes_item\":\"Caminhada leve progredindo para trote suave\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(45, 15, 'Corrida Contínua Leve', NULL, NULL, NULL, '20 minutos', NULL, NULL, '{\"nome_item\":\"Corrida Contínua Leve\",\"detalhes_item\":\"Corrida em ritmo conversacional, mantendo FC controlada\",\"fc_alvo\":\"65-75% FCmax (117-135 bpm)\",\"tempo_execucao\":\"20 minutos\",\"distancia\":\"3-4 km\",\"velocidade\":\"Ritmo conversacional\",\"observacoes\":\"Manter respiração controlada, poder conversar durante a corrida\"}', 'livre'),
(46, 15, 'Agachamento Isométrico', NULL, 3, '30s', '30s por série', 0.00, '60s', '{\"nome_item\":\"Agachamento Isométrico\",\"detalhes_item\":\"3 séries de 30 segundos, foco na técnica\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"30s por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Peso corporal\",\"series\":\"3\",\"repeticoes\":\"30s\"}', 'livre'),
(47, 15, 'Elevação Pélvica', NULL, 3, '15', '45s por série', 0.00, '60s', '{\"nome_item\":\"Elevação Pélvica\",\"detalhes_item\":\"3 séries de 15 repetições\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"45s por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Peso corporal\",\"series\":\"3\",\"repeticoes\":\"15\"}', 'livre'),
(48, 15, 'Cadeira Extensora com ROM Controlado', NULL, 3, '12', '45s por série', 0.00, '60s', '{\"nome_item\":\"Cadeira Extensora com ROM Controlado\",\"detalhes_item\":\"3 séries de 12 repetições\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"45s por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Leve\",\"series\":\"3\",\"repeticoes\":\"12\"}', 'livre'),
(49, 15, 'Prancha Frontal', NULL, 3, '30s', '30s por série', 0.00, '45s', '{\"nome_item\":\"Prancha Frontal\",\"detalhes_item\":\"3 séries de 30 segundos\",\"fc_alvo\":\"65-70% FCmax (117-126 bpm)\",\"tempo_execucao\":\"30s por série\",\"tempo_recuperacao\":\"45s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Peso corporal\",\"series\":\"3\",\"repeticoes\":\"30s\"}', 'livre'),
(50, 15, 'Alongamento Dinâmico', NULL, NULL, NULL, '5 minutos', NULL, NULL, '{\"nome_item\":\"Alongamento Dinâmico\",\"detalhes_item\":\"Movimentos dinâmicos para quadríceps e isquiotibiais\",\"tempo_execucao\":\"5 minutos\"}', 'livre'),
(51, 15, 'Alongamento de Membros Inferiores', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Alongamento de Membros Inferiores\",\"detalhes_item\":\"Alongamento suave para quadríceps, isquiotibiais e panturrilhas\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(52, 16, 'Aquecimento com Mobilidade Articular', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Aquecimento com Mobilidade Articular\",\"detalhes_item\":\"Exercícios de mobilidade para quadris e tornozelos\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(53, 16, 'Intervalado Leve', NULL, NULL, NULL, '20 minutos', NULL, NULL, '{\"nome_item\":\"Intervalado Leve\",\"detalhes_item\":\"1 minuto de corrida leve seguido de 1 minuto de caminhada\",\"fc_alvo\":\"70-80% FCmax (126-144 bpm)\",\"tempo_execucao\":\"20 minutos\",\"distancia\":\"2-3 km\",\"observacoes\":\"Foco na técnica e controle da respiração\"}', 'livre'),
(54, 16, 'Leg Curl', NULL, 3, '12', '45s por série', 0.00, '60s', '{\"nome_item\":\"Leg Curl\",\"detalhes_item\":\"3 séries de 12 repetições\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"45s por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Leve\",\"series\":\"3\",\"repeticoes\":\"12\"}', 'livre'),
(55, 16, 'Elevação de Panturrilha', NULL, 3, '15', '45s por série', 0.00, '60s', '{\"nome_item\":\"Elevação de Panturrilha\",\"detalhes_item\":\"3 séries de 15 repetições\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"45s por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Peso corporal\",\"series\":\"3\",\"repeticoes\":\"15\"}', 'livre'),
(56, 16, 'Prancha Lateral', NULL, 3, '20s', '20s por lado', 0.00, '45s', '{\"nome_item\":\"Prancha Lateral\",\"detalhes_item\":\"3 séries de 20 segundos de cada lado\",\"fc_alvo\":\"65-70% FCmax (117-126 bpm)\",\"tempo_execucao\":\"20s por lado\",\"tempo_recuperacao\":\"45s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Peso corporal\",\"series\":\"3\",\"repeticoes\":\"20s\"}', 'livre'),
(57, 16, 'Caminhada Leve', NULL, NULL, NULL, '5 minutos', NULL, NULL, '{\"nome_item\":\"Caminhada Leve\",\"detalhes_item\":\"Caminhada leve para relaxamento\",\"tempo_execucao\":\"5 minutos\"}', 'livre'),
(58, 17, 'Aquecimento com Alongamentos Dinâmicos', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Aquecimento com Alongamentos Dinâmicos\",\"detalhes_item\":\"Movimentos dinâmicos para aquecer os músculos\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(59, 17, 'Corrida de Recuperação', NULL, NULL, NULL, '25 minutos', NULL, NULL, '{\"nome_item\":\"Corrida de Recuperação\",\"detalhes_item\":\"Corrida leve em ritmo confortável\",\"fc_alvo\":\"60-70% FCmax (108-126 bpm)\",\"tempo_execucao\":\"25 minutos\",\"distancia\":\"3-4 km\",\"observacoes\":\"Manter ritmo confortável, sem esforço excessivo\"}', 'livre'),
(60, 17, 'Exercícios de Mobilidade', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Exercícios de Mobilidade\",\"detalhes_item\":\"Foco em quadris e tornozelos\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(61, 17, 'Alongamento de Corpo Inteiro', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Alongamento de Corpo Inteiro\",\"detalhes_item\":\"Alongamento para relaxamento muscular\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(62, 18, 'Aquecimento em Esteira', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Aquecimento em Esteira\",\"detalhes_item\":\"Caminhada leve progredindo para trote suave\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(63, 18, 'Corrida Contínua Leve', NULL, NULL, NULL, '20 minutos', NULL, NULL, '{\"nome_item\":\"Corrida Contínua Leve\",\"detalhes_item\":\"Corrida em ritmo conversacional, mantendo FC controlada\",\"fc_alvo\":\"65-75% FCmax (117-135 bpm)\",\"tempo_execucao\":\"20 minutos\",\"distancia\":\"3-4 km\",\"velocidade\":\"Ritmo conversacional\",\"observacoes\":\"Manter respiração controlada, poder conversar durante a corrida\"}', 'livre'),
(64, 18, 'Agachamento Isométrico', NULL, 3, '30s', '30s por série', 0.00, '60s', '{\"nome_item\":\"Agachamento Isométrico\",\"detalhes_item\":\"3 séries de 30 segundos\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"30s por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Peso corporal\",\"series\":\"3\",\"repeticoes\":\"30s\"}', 'livre'),
(65, 18, 'Elevação Pélvica', NULL, 3, '15', '45s por série', 0.00, '60s', '{\"nome_item\":\"Elevação Pélvica\",\"detalhes_item\":\"3 séries de 15 repetições\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"45s por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Peso corporal\",\"series\":\"3\",\"repeticoes\":\"15\"}', 'livre'),
(66, 18, 'Cadeira Extensora', NULL, 3, '12', '45s por série', 0.00, '60s', '{\"nome_item\":\"Cadeira Extensora\",\"detalhes_item\":\"3 séries de 12 repetições, ROM controlado\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"45s por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Leve\",\"series\":\"3\",\"repeticoes\":\"12\"}', 'livre'),
(67, 18, 'Prancha', NULL, 3, '30s', '30s por série', NULL, '60s', '{\"nome_item\":\"Prancha\",\"detalhes_item\":\"3 séries de 30 segundos\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"30s por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"series\":\"3\",\"repeticoes\":\"30s\"}', 'livre'),
(68, 18, 'Alongamento de Membros Inferiores', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Alongamento de Membros Inferiores\",\"detalhes_item\":\"Alongamento suave para quadríceps, isquiotibiais e panturrilhas\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(69, 19, 'Aquecimento Dinâmico', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Aquecimento Dinâmico\",\"detalhes_item\":\"Mobilidade articular e ativação muscular\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(70, 19, 'Corrida Intervalada', NULL, NULL, NULL, '15 minutos', NULL, NULL, '{\"nome_item\":\"Corrida Intervalada\",\"detalhes_item\":\"5 x 1 minuto em ritmo forte, 2 minutos de recuperação\",\"fc_alvo\":\"80-90% FCmax (144-162 bpm)\",\"tempo_execucao\":\"15 minutos\",\"distancia\":\"Variante\",\"velocidade\":\"Ritmo forte\",\"observacoes\":\"Manter a técnica durante os intervalos rápidos\"}', 'livre'),
(71, 19, 'Elevação de Panturrilha', NULL, 3, '15', '45s por série', 0.00, '60s', '{\"nome_item\":\"Elevação de Panturrilha\",\"detalhes_item\":\"3 séries de 15 repetições\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"45s por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Peso corporal\",\"series\":\"3\",\"repeticoes\":\"15\"}', 'livre'),
(72, 19, 'Abdominal Supra', NULL, 3, '20', '45s por série', NULL, '60s', '{\"nome_item\":\"Abdominal Supra\",\"detalhes_item\":\"3 séries de 20 repetições\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"45s por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"series\":\"3\",\"repeticoes\":\"20\"}', 'livre'),
(73, 19, 'Flexão de Braço', NULL, 3, '10', '45s por série', 0.00, '60s', '{\"nome_item\":\"Flexão de Braço\",\"detalhes_item\":\"3 séries de 10 repetições\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"45s por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Peso corporal\",\"series\":\"3\",\"repeticoes\":\"10\"}', 'livre'),
(74, 19, 'Alongamento de Corpo Inteiro', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Alongamento de Corpo Inteiro\",\"detalhes_item\":\"Alongamento estático para relaxamento muscular\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(75, 20, 'Aquecimento com Mobilidade', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Aquecimento com Mobilidade\",\"detalhes_item\":\"Mobilidade dinâmica para membros inferiores\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(76, 20, 'Corrida Longa', NULL, NULL, NULL, '40 minutos', NULL, NULL, '{\"nome_item\":\"Corrida Longa\",\"detalhes_item\":\"Corrida contínua em ritmo confortável\",\"fc_alvo\":\"65-75% FCmax (117-135 bpm)\",\"tempo_execucao\":\"40 minutos\",\"distancia\":\"6-7 km\",\"velocidade\":\"Ritmo confortável\",\"observacoes\":\"Focar na técnica de corrida e respiração\"}', 'livre'),
(77, 20, 'Caminhada Leve', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Caminhada Leve\",\"detalhes_item\":\"Caminhada para desacelerar o ritmo cardíaco\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(78, 20, 'Alongamento de Membros Inferiores', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Alongamento de Membros Inferiores\",\"detalhes_item\":\"Alongamento suave para quadríceps, isquiotibiais e panturrilhas\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(79, 21, 'Caminhada Rápida', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Caminhada Rápida\",\"detalhes_item\":\"Aquecimento com caminhada rápida para aumentar a temperatura corporal.\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(80, 21, 'Corrida Leve', NULL, NULL, NULL, '20 minutos', NULL, NULL, '{\"nome_item\":\"Corrida Leve\",\"detalhes_item\":\"Corrida em ritmo leve, focando na técnica e respiração.\",\"fc_alvo\":\"65-75% FCmax (117-135 bpm)\",\"tempo_execucao\":\"20 minutos\",\"distancia\":\"3 km\",\"velocidade\":\"Ritmo confortável\",\"observacoes\":\"Manter o controle da respiração e postura.\"}', 'livre'),
(81, 21, 'Exercícios de Mobilidade', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Exercícios de Mobilidade\",\"detalhes_item\":\"Movimentos dinâmicos para articulações do quadril e tornozelo.\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(82, 21, 'Agachamento Livre', NULL, 3, '12', '45s por série', 0.00, '60s', '{\"nome_item\":\"Agachamento Livre\",\"detalhes_item\":\"3 séries de 12 repetições, foco na técnica.\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"45s por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Peso corporal\",\"series\":\"3\",\"repeticoes\":\"12\"}', 'livre'),
(83, 21, 'Prancha', NULL, 3, '30s', '30s por série', NULL, '30s', '{\"nome_item\":\"Prancha\",\"detalhes_item\":\"Fortalecimento do core, 3 séries de 30 segundos.\",\"fc_alvo\":\"60-65% FCmax (108-117 bpm)\",\"tempo_execucao\":\"30s por série\",\"tempo_recuperacao\":\"30s\",\"tipo_recuperacao\":\"passivo\",\"series\":\"3\",\"repeticoes\":\"30s\"}', 'livre'),
(84, 21, 'Alongamento Global', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Alongamento Global\",\"detalhes_item\":\"Alongamento suave para todo o corpo.\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(85, 22, 'Caminhada Rápida', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Caminhada Rápida\",\"detalhes_item\":\"Aquecimento com caminhada rápida.\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(86, 22, 'Corrida Contínua', NULL, NULL, NULL, '25 minutos', NULL, NULL, '{\"nome_item\":\"Corrida Contínua\",\"detalhes_item\":\"Corrida em ritmo moderado, mantendo controle respiratório.\",\"fc_alvo\":\"65-75% FCmax (117-135 bpm)\",\"tempo_execucao\":\"25 minutos\",\"distancia\":\"4 km\",\"velocidade\":\"Ritmo moderado\",\"observacoes\":\"Focar na postura e técnica de corrida.\"}', 'livre'),
(87, 22, 'Leg Press', NULL, 3, '10', '45s por série', 0.00, '60s', '{\"nome_item\":\"Leg Press\",\"detalhes_item\":\"3 séries de 10 repetições, fortalecimento de membros inferiores.\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"45s por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Moderada\",\"series\":\"3\",\"repeticoes\":\"10\"}', 'livre'),
(88, 22, 'Flexão de Braço', NULL, 3, '8', '30s por série', NULL, '45s', '{\"nome_item\":\"Flexão de Braço\",\"detalhes_item\":\"3 séries de 8 repetições, fortalecimento de membros superiores.\",\"fc_alvo\":\"60-65% FCmax (108-117 bpm)\",\"tempo_execucao\":\"30s por série\",\"tempo_recuperacao\":\"45s\",\"tipo_recuperacao\":\"passivo\",\"series\":\"3\",\"repeticoes\":\"8\"}', 'livre'),
(89, 22, 'Alongamento de Membros Inferiores', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Alongamento de Membros Inferiores\",\"detalhes_item\":\"Alongamento focado em quadríceps, panturrilhas e isquiotibiais.\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(90, 23, 'Aquecimento na Piscina', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Aquecimento na Piscina\",\"detalhes_item\":\"Nado leve para aquecimento.\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(91, 23, 'Natação Livre', NULL, NULL, NULL, '30 minutos', NULL, NULL, '{\"nome_item\":\"Natação Livre\",\"detalhes_item\":\"Nado contínuo em ritmo moderado.\",\"fc_alvo\":\"60-70% FCmax (108-126 bpm)\",\"tempo_execucao\":\"30 minutos\",\"distancia\":\"800-1000 metros\",\"velocidade\":\"Ritmo moderado\",\"observacoes\":\"Focar na técnica de nado e respiração.\"}', 'livre'),
(92, 23, 'Alongamento na Água', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Alongamento na Água\",\"detalhes_item\":\"Alongamento suave no ambiente aquático.\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(93, 24, 'Aquecimento em Esteira', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Aquecimento em Esteira\",\"detalhes_item\":\"Caminhada leve progredindo para trote suave\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(94, 24, 'Corrida Contínua Leve', NULL, NULL, NULL, '20 minutos', NULL, NULL, '{\"nome_item\":\"Corrida Contínua Leve\",\"detalhes_item\":\"Corrida em ritmo conversacional, mantendo FC controlada\",\"fc_alvo\":\"65-75% FCmax (117-135 bpm)\",\"tempo_execucao\":\"20 minutos\",\"distancia\":\"3-4 km\",\"velocidade\":\"Ritmo conversacional\",\"observacoes\":\"Manter respiração controlada, poder conversar durante a corrida\"}', 'livre'),
(95, 24, 'Agachamento Livre', NULL, 3, '15', '45s por série', 0.00, '60s', '{\"nome_item\":\"Agachamento Livre\",\"detalhes_item\":\"3 séries de 15 repetições, foco na técnica\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"45s por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Peso corporal\",\"series\":\"3\",\"repeticoes\":\"15\"}', 'livre'),
(96, 24, 'Prancha Abdominal', NULL, 3, '30 segundos', '30 segundos por série', 0.00, '30s', '{\"nome_item\":\"Prancha Abdominal\",\"detalhes_item\":\"3 séries de 30 segundos\",\"fc_alvo\":\"60-70% FCmax (108-126 bpm)\",\"tempo_execucao\":\"30 segundos por série\",\"tempo_recuperacao\":\"30s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Peso corporal\",\"series\":\"3\",\"repeticoes\":\"30 segundos\"}', 'livre'),
(97, 24, 'Mobilidade de Quadril', NULL, NULL, NULL, '5 minutos', NULL, NULL, '{\"nome_item\":\"Mobilidade de Quadril\",\"detalhes_item\":\"Exercícios de mobilidade para quadril\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"5 minutos\"}', 'livre'),
(98, 24, 'Alongamento de Membros Inferiores', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Alongamento de Membros Inferiores\",\"detalhes_item\":\"Alongamento suave para quadríceps, isquiotibiais e panturrilhas\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(99, 25, 'Aquecimento Dinâmico', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Aquecimento Dinâmico\",\"detalhes_item\":\"Movimentos dinâmicos para preparar o corpo\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(100, 25, 'Corrida Intervalada', NULL, NULL, NULL, '15 minutos', NULL, NULL, '{\"nome_item\":\"Corrida Intervalada\",\"detalhes_item\":\"5x 1 minuto de corrida rápida com 2 minutos de trote leve\",\"fc_alvo\":\"75-85% FCmax (135-153 bpm) para corrida rápida\",\"tempo_execucao\":\"15 minutos\",\"distancia\":\"Aproximadamente 2-3 km\",\"velocidade\":\"Rápido durante os intervalos\",\"observacoes\":\"Focar na técnica durante os sprints\"}', 'livre'),
(101, 25, 'Leg Press', NULL, 3, '12', '1 minuto por série', 0.00, '60s', '{\"nome_item\":\"Leg Press\",\"detalhes_item\":\"3 séries de 12 repetições\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"1 minuto por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Moderada\",\"series\":\"3\",\"repeticoes\":\"12\"}', 'livre'),
(102, 25, 'Flexão de Braço', NULL, 3, '10', '30 segundos por série', 0.00, '30s', '{\"nome_item\":\"Flexão de Braço\",\"detalhes_item\":\"3 séries de 10 repetições\",\"fc_alvo\":\"65-70% FCmax (117-126 bpm)\",\"tempo_execucao\":\"30 segundos por série\",\"tempo_recuperacao\":\"30s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Peso corporal\",\"series\":\"3\",\"repeticoes\":\"10\"}', 'livre'),
(103, 25, 'Alongamento de Corpo Inteiro', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Alongamento de Corpo Inteiro\",\"detalhes_item\":\"Alongamento suave para todo o corpo\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(104, 26, 'Aquecimento Articular', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Aquecimento Articular\",\"detalhes_item\":\"Movimentos circulares para aquecer as articulações\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(105, 26, 'Corrida Longa', NULL, NULL, NULL, '45 minutos', NULL, NULL, '{\"nome_item\":\"Corrida Longa\",\"detalhes_item\":\"Corrida em ritmo confortável para aumentar a resistência\",\"fc_alvo\":\"65-75% FCmax (117-135 bpm)\",\"tempo_execucao\":\"45 minutos\",\"distancia\":\"6-7 km\",\"velocidade\":\"Ritmo confortável\",\"observacoes\":\"Manter hidratação adequada durante a corrida\"}', 'livre'),
(106, 26, 'Elevação de Panturrilha', NULL, 3, '15', '30 segundos por série', 0.00, '30s', '{\"nome_item\":\"Elevação de Panturrilha\",\"detalhes_item\":\"3 séries de 15 repetições\",\"fc_alvo\":\"65-70% FCmax (117-126 bpm)\",\"tempo_execucao\":\"30 segundos por série\",\"tempo_recuperacao\":\"30s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Peso corporal\",\"series\":\"3\",\"repeticoes\":\"15\"}', 'livre'),
(107, 26, 'Alongamento Focado em Pernas', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Alongamento Focado em Pernas\",\"detalhes_item\":\"Alongamento para quadríceps, isquiotibiais e panturrilhas\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(108, 27, 'Caminhada Leve', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Caminhada Leve\",\"detalhes_item\":\"Caminhada em ritmo leve para aquecer\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(109, 27, 'Circuito de Fortalecimento', NULL, NULL, NULL, '20 minutos', NULL, NULL, '{\"nome_item\":\"Circuito de Fortalecimento\",\"detalhes_item\":\"Circuito com 4 exercícios: agachamento, flexão, prancha e elevação de panturrilha\",\"fc_alvo\":\"60-70% FCmax (108-126 bpm)\",\"tempo_execucao\":\"20 minutos\",\"observacoes\":\"Foco na técnica, número de repetições conforme capacidade\"}', 'livre'),
(110, 27, 'Mobilidade e Flexibilidade', NULL, NULL, NULL, '5 minutos', NULL, NULL, '{\"nome_item\":\"Mobilidade e Flexibilidade\",\"detalhes_item\":\"Exercícios de mobilidade para quadris e tornozelos\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"5 minutos\"}', 'livre'),
(111, 27, 'Alongamento Geral', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Alongamento Geral\",\"detalhes_item\":\"Alongamento suave para todo o corpo\",\"tempo_execucao\":\"10 minutos\"}', 'livre');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome_completo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `senha` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_nascimento` date DEFAULT NULL,
  `tipo_documento` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `documento` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sexo` enum('Masculino','Feminino','Outro') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `celular` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `endereco` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `complemento` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bairro` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cidade` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uf` char(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cep` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pais` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_perfil` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Caminho/nome do arquivo da foto de perfil do usuário',
  `status` enum('ativo','inativo') COLLATE utf8mb4_unicode_ci DEFAULT 'ativo',
  `data_cadastro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `papel` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'participante',
  `token_recuperacao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token_expira` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome_completo`, `email`, `senha`, `data_nascimento`, `tipo_documento`, `documento`, `sexo`, `telefone`, `celular`, `endereco`, `numero`, `complemento`, `bairro`, `cidade`, `uf`, `cep`, `pais`, `foto_perfil`, `status`, `data_cadastro`, `papel`, `token_recuperacao`, `token_expira`) VALUES
(16, 'EUDIMACI BARBOZA DE LIRA', 'eudimaci08@yahoo.com.br', '$2y$10$3L5lLXUG6ZM.OFmkW23UBu3w9Y0M6wGlrikNwwwy1mpC/2H3pyxIe', NULL, NULL, NULL, NULL, '+55 +55 92982027654', '+55 +55 92982027654', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', NULL, 'ativo', '2025-12-19 14:31:48', 'organizador', NULL, NULL),
(17, 'Eudimaci Lira', 'eudimaci.pecim@gmail.com', '$2y$10$Tw9IWLdYflHjX18VLaockOGgrXgRmhfCCEgxZLmVbNpd5QjcGuPWy', '1970-08-31', 'CPF', '64655733420', 'Masculino', '92982027654', '92982027654', 'Avenida Efigênio Sales', '530', NULL, 'Adrianópolis', 'Manaus', 'AM', '69057050', 'Brasil', 'frontend/assets/img/perfis/perfil_17_1771376401_69950f11568cc.jpg', 'ativo', '2025-12-19 14:57:45', 'participante', NULL, NULL),
(18, 'Marcio dos Santos Fernandes', 'nandesinfo@gmail.com', '$2y$10$jb2DS7UDKnOHL4h/tp3foO8xW2ls9cff0M7wAaW.07hH67hBybMdu', '1971-02-03', 'CPF', '00571996710', 'Masculino', '21971545687', '21971545687', 'Rua Mediterrâneo', '102', 'Apto 301', 'Córrego Grande', 'Florianópolis', 'SC', '88037610', 'Brasil', 'frontend/assets/img/perfis/perfil_18_1771682084_6999b924e078b.jpg', 'ativo', '2025-12-19 17:08:25', 'participante', NULL, NULL),
(19, 'Clóvis Augusto Pantoja', 'pancap@gmail.com', '$2y$10$tCzDcxn2a5vKdd8Bnk4kpOkVqtS.4EwCkuDnMRGuyvRXt.GXSjlje', '1979-03-10', NULL, NULL, 'Masculino', '92991499040', '92991499040', 'Rua Wagner', '122', NULL, 'da Paz', 'Manaus', 'AM', '69048000', 'Brasil', 'frontend/assets/img/perfis/perfil_19_1766183903_6945d3df28bf3.jpg', 'ativo', '2025-12-19 22:34:05', 'participante', NULL, NULL),
(20, 'Sandoval Bezerra', 'sandoval.bezerra@gmail.com', '$2y$10$3L5lLXUG6ZM.OFmkW23UBu3w9Y0M6wGlrikNwwwy1mpC/2H3pyxIe', NULL, NULL, '77042050487', NULL, NULL, NULL, 'Rua Ceará', '145', NULL, 'Chapada', 'Manaus', 'AM', '69050050', NULL, NULL, 'ativo', '2025-12-23 14:31:46', 'participante', NULL, NULL),
(21, 'LUANA SOUZA MODESTO', 'lumodesto81@gmail.com', '$2y$10$HtvINyapvymTpTHdRnFxJ.DdqEsl2a9DXbftND4No5iNrdfawRhAq', NULL, NULL, '01820351238', NULL, NULL, NULL, 'Avenida Ephigênio Salles', '530', NULL, 'Adrianópolis', 'Manaus', 'AM', '69057050', NULL, NULL, 'ativo', '2025-12-23 22:28:49', 'participante', NULL, NULL),
(22, 'Bruno Rafaga', 'sandovalwizard@outlook.com', '$2y$10$/l4FKCwDsinmu2ckFShsVuJ71.GdGixE8N2tAO.D8dOJ0T2praflW', '1969-08-28', 'CPF', '17171936414', 'Masculino', '92981151287', '8196858574', 'Leonardo Malcher', '854', NULL, 'centro', 'Manaus', 'AM', '69010170', 'Brasil', 'frontend/assets/img/perfis/perfil_22_1769110295_69727b17ef011.jpg', 'ativo', '2025-12-23 23:20:41', 'participante', NULL, NULL),
(23, 'YANNE PACHECO BARBOZA DE LIRA', 'yannepachecob@gmail.com', '$2y$10$b/9zb4IF5lUN3uEwb55hKOiN.zEMtDblIvqxAPNt1kWkQHDAJrUpW', NULL, NULL, '14048969455', NULL, NULL, NULL, 'Avenida Esperança', '872', NULL, 'Manaíra', 'João Pessoa', 'PB', '58038281', NULL, NULL, 'ativo', '2025-12-31 10:57:39', 'participante', NULL, NULL),
(24, 'Helton Jefferson Damasceno Perez', 'notleh.perez144@gmail.com', '$2y$10$PbxHtsu92bPTXxbS95uajedU10x6DtltA4u/wLL3bDr4M9tKV3UrS', NULL, 'CPF', '51467798215', NULL, NULL, NULL, 'Avenida Borba', '1229', 'A', 'Cachoeirinha', 'Manaus', 'AM', '69065030', NULL, NULL, 'ativo', '2026-01-26 21:57:33', 'participante', NULL, NULL),
(25, 'Manoel da Pereira Pinto', 'manoel@gmail.com', '$2y$10$COmNgt18R3ivwDOQxPaCQeOPkiAK.NoKMT8CrKIpVMR5AyugXX7Rm', NULL, 'CPF', '00307850224', NULL, NULL, NULL, 'Rua Henrique Martins', '120', 'Apto 13', 'Centro', 'Manaus', 'AM', '69010010', NULL, NULL, 'ativo', '2026-01-30 18:10:54', 'participante', NULL, NULL),
(26, 'Léo Naval', 'leonardolimanaval@gmail.com', '$2y$10$YD0fuCA5XgHIrnk0mjQlEuhCIyMZc4UMrJ.tFFW6exSxla6wDmh4y', NULL, 'CPF', '00544941250', NULL, NULL, NULL, 'Beco José de Arimatéia', '15', NULL, 'Petrópolis', 'Manaus', 'AM', '69067090', NULL, NULL, 'ativo', '2026-01-30 18:13:27', 'participante', NULL, NULL),
(27, 'Davi Sandoval', 'nobilisandoval@gmail.com', '$2y$10$sT16rY.JhwviFLY6bu9gRu3KInfZDPvFBiRsrcnkoVmkJka.6c4OC', NULL, 'CPF', '42192511469', NULL, NULL, NULL, 'Rua Professor José Calazans', '131', 'casa2', 'San Martin', 'Recife', 'PE', '50761420', NULL, NULL, 'ativo', '2026-02-05 15:46:29', 'participante', NULL, NULL),
(28, 'Fernando Frainer', 'nandofrainer@gmail.com', '$2y$10$2n9l91cPdOWFpc2wyw5u8.krrnHyeR9rgCOiDq.b/uCyQNxtDn.yG', NULL, 'CPF', '81476850097', NULL, NULL, NULL, 'Rua Rivadavia de Azambuja Guimarães', '149', 'casa', 'Nossa Senhora da Saúde', 'Caxias do Sul', 'RS', '95044080', NULL, NULL, 'ativo', '2026-02-06 02:44:08', 'participante', NULL, NULL),
(29, 'solon bezerra da Silva', 'sandoval@sbsystems.com.br', '$2y$10$g6mX.Sm99TkfuAGBnYGEK.GY16Ls5nvaQ9FZnUtlWo3HA0JnYzmG.', NULL, 'CPF', '84110717400', NULL, NULL, NULL, 'Rua Professor José Calazans', '131', 'casa2', 'San Martin', 'Recife', 'PE', '50761420', NULL, NULL, 'ativo', '2026-02-08 13:33:48', 'participante', NULL, NULL),
(30, 'EUDIMACI BARBOZA DE LIRA', 'moveromundobrasil@gmail.com', '$2y$10$GLpD295QjScLuvzl/O/YPOKyjzA3S0cd3VqXDeNMy/CS9Oom27xdm', NULL, 'CPF', '64655733420', NULL, '+55 +55 92981630385', NULL, 'Avenida Ephigênio Salles', '530', NULL, 'Adrianópolis', 'Manaus', 'AM', '69057050', NULL, NULL, 'ativo', '2026-02-10 01:34:52', 'organizador', NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario_admin`
--

CREATE TABLE `usuario_admin` (
  `id` int(11) NOT NULL,
  `nome_completo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Administrador',
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `senha` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('ativo','inativo') COLLATE utf8mb4_unicode_ci DEFAULT 'ativo',
  `data_cadastro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ultimo_acesso` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `token_recuperacao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token_expira` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuario_admin`
--

INSERT INTO `usuario_admin` (`id`, `nome_completo`, `email`, `senha`, `status`, `data_cadastro`, `ultimo_acesso`, `token_recuperacao`, `token_expira`) VALUES
(2, 'Administrador', 'admin@movamazon.com.br', '$2y$10$fHzwjvZo/6tLBFDIWc0ziOkU4pA3.wZYLOH4MTlS5Ih.2FjoLMzoK', 'ativo', '2025-11-25 18:10:01', '2026-02-20 20:07:06', NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario_papeis`
--

CREATE TABLE `usuario_papeis` (
  `usuario_id` int(11) NOT NULL,
  `papel_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuario_papeis`
--

INSERT INTO `usuario_papeis` (`usuario_id`, `papel_id`) VALUES
(3, 1),
(2, 2),
(1, 3),
(12, 4);

-- --------------------------------------------------------

--
-- Estrutura para view `eventos_ativos`
--
DROP TABLE IF EXISTS `eventos_ativos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`brunor90`@`localhost` SQL SECURITY DEFINER VIEW `eventos_ativos`  AS SELECT `eventos`.`id` AS `id`, `eventos`.`nome` AS `nome`, `eventos`.`descricao` AS `descricao`, `eventos`.`data_inicio` AS `data_inicio`, `eventos`.`data_fim` AS `data_fim`, `eventos`.`categoria` AS `categoria`, `eventos`.`genero` AS `genero`, `eventos`.`local` AS `local`, `eventos`.`cep` AS `cep`, `eventos`.`url_mapa` AS `url_mapa`, `eventos`.`logradouro` AS `logradouro`, `eventos`.`numero` AS `numero`, `eventos`.`cidade` AS `cidade`, `eventos`.`estado` AS `estado`, `eventos`.`pais` AS `pais`, `eventos`.`regulamento` AS `regulamento`, `eventos`.`status` AS `status`, `eventos`.`organizador_id` AS `organizador_id`, `eventos`.`taxa_setup` AS `taxa_setup`, `eventos`.`percentual_repasse` AS `percentual_repasse`, `eventos`.`exibir_retirada_kit` AS `exibir_retirada_kit`, `eventos`.`taxa_gratuitas` AS `taxa_gratuitas`, `eventos`.`taxa_pagas` AS `taxa_pagas`, `eventos`.`limite_vagas` AS `limite_vagas`, `eventos`.`data_fim_inscricoes` AS `data_fim_inscricoes`, `eventos`.`hora_fim_inscricoes` AS `hora_fim_inscricoes`, `eventos`.`data_criacao` AS `data_criacao`, `eventos`.`hora_inicio` AS `hora_inicio`, `eventos`.`data_realizacao` AS `data_realizacao`, `eventos`.`imagem` AS `imagem`, `eventos`.`deleted_at` AS `deleted_at`, `eventos`.`deleted_by` AS `deleted_by`, `eventos`.`delete_reason` AS `delete_reason` FROM `eventos` WHERE isnull(`eventos`.`deleted_at`) ;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `aceites_termos`
--
ALTER TABLE `aceites_termos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inscricao_id` (`inscricao_id`),
  ADD KEY `termos_id` (`termos_id`);

--
-- Índices de tabela `anamneses`
--
ALTER TABLE `anamneses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_anamneses_usuario` (`usuario_id`),
  ADD KEY `idx_anamneses_profissional` (`profissional_id`),
  ADD KEY `idx_anamneses_inscricao` (`inscricao_id`),
  ADD KEY `idx_usuario` (`usuario_id`);

--
-- Índices de tabela `assessorias`
--
ALTER TABLE `assessorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_assessorias_cpf_cnpj` (`cpf_cnpj`),
  ADD KEY `idx_assessorias_responsavel` (`responsavel_usuario_id`),
  ADD KEY `idx_assessorias_status` (`status`);

--
-- Índices de tabela `assessoria_atletas`
--
ALTER TABLE `assessoria_atletas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_assessoria_atletas` (`assessoria_id`,`atleta_usuario_id`),
  ADD KEY `idx_assessoria_atletas_assessoria` (`assessoria_id`),
  ADD KEY `idx_assessoria_atletas_atleta` (`atleta_usuario_id`),
  ADD KEY `idx_assessoria_atletas_assessor` (`assessor_usuario_id`),
  ADD KEY `idx_assessoria_atletas_status` (`status`);

--
-- Índices de tabela `assessoria_convites`
--
ALTER TABLE `assessoria_convites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_convites_token` (`token`),
  ADD KEY `idx_convites_assessoria` (`assessoria_id`),
  ADD KEY `idx_convites_atleta` (`atleta_usuario_id`),
  ADD KEY `idx_convites_status` (`status`),
  ADD KEY `idx_convites_enviado_por` (`enviado_por_usuario_id`),
  ADD KEY `idx_convites_assessoria_status` (`assessoria_id`,`status`),
  ADD KEY `idx_convites_atleta_status` (`atleta_usuario_id`,`status`),
  ADD KEY `idx_convites_expira_em` (`expira_em`);

--
-- Índices de tabela `assessoria_equipe`
--
ALTER TABLE `assessoria_equipe`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_assessoria_equipe` (`assessoria_id`,`usuario_id`),
  ADD KEY `idx_assessoria_equipe_assessoria` (`assessoria_id`),
  ADD KEY `idx_assessoria_equipe_usuario` (`usuario_id`),
  ADD KEY `idx_assessoria_equipe_status` (`status`);

--
-- Índices de tabela `assessoria_programas`
--
ALTER TABLE `assessoria_programas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_programas_assessoria` (`assessoria_id`),
  ADD KEY `idx_programas_evento` (`evento_id`),
  ADD KEY `idx_assessoria_programas_status` (`status`);

--
-- Índices de tabela `assessoria_programa_atletas`
--
ALTER TABLE `assessoria_programa_atletas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_programa_atletas` (`programa_id`,`atleta_usuario_id`),
  ADD KEY `idx_programa_atletas_programa` (`programa_id`),
  ADD KEY `idx_programa_atletas_atleta` (`atleta_usuario_id`);

--
-- Índices de tabela `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ativo` (`ativo`),
  ADD KEY `idx_ordem` (`ordem`),
  ADD KEY `idx_datas` (`data_inicio`,`data_fim`);

--
-- Índices de tabela `camisas`
--
ALTER TABLE `camisas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `evento_tamanho` (`evento_id`,`tamanho`),
  ADD KEY `evento_id` (`evento_id`),
  ADD KEY `idx_tamanhos_evento_produto` (`evento_id`,`produto_id`),
  ADD KEY `idx_tamanhos_produto` (`produto_id`),
  ADD KEY `idx_tamanhos_ativo` (`ativo`);

--
-- Índices de tabela `cashback_atletas`
--
ALTER TABLE `cashback_atletas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_inscricao` (`inscricao_id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_evento` (`evento_id`),
  ADD KEY `idx_data_credito` (`data_credito`);

--
-- Índices de tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_evento_nome` (`evento_id`,`nome`);

--
-- Índices de tabela `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_chave` (`chave`),
  ADD KEY `idx_categoria` (`categoria`),
  ADD KEY `idx_updated_by` (`updated_by`);

--
-- Índices de tabela `config_historico`
--
ALTER TABLE `config_historico`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_config_id` (`config_id`),
  ADD KEY `idx_chave` (`chave`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `fk_config_historico_admin` (`alterado_por`);

--
-- Índices de tabela `cupons_remessa`
--
ALTER TABLE `cupons_remessa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_remessa` (`codigo_remessa`),
  ADD KEY `idx_evento` (`evento_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_data_inicio` (`data_inicio`),
  ADD KEY `idx_data_validade` (`data_validade`);

--
-- Índices de tabela `enderecos_entrega`
--
ALTER TABLE `enderecos_entrega`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inscricao_id` (`inscricao_id`);

--
-- Índices de tabela `estoque_kits`
--
ALTER TABLE `estoque_kits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kit_estoque` (`kit_id`),
  ADD KEY `kit_id` (`kit_id`);

--
-- Índices de tabela `estoque_produtos_extras`
--
ALTER TABLE `estoque_produtos_extras`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `produto_estoque` (`produto_extra_id`),
  ADD KEY `produto_extra_id` (`produto_extra_id`);

--
-- Índices de tabela `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organizador_id` (`organizador_id`),
  ADD KEY `idx_eventos_data_realizacao` (`data_realizacao`),
  ADD KEY `idx_deleted_at` (`deleted_at`),
  ADD KEY `fk_evento_deleted_by` (`deleted_by`);

--
-- Índices de tabela `financeiro_beneficiarios`
--
ALTER TABLE `financeiro_beneficiarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fin_beneficiarios_organizador` (`organizador_id`),
  ADD KEY `idx_fin_beneficiarios_status` (`status`);

--
-- Índices de tabela `financeiro_chargebacks`
--
ALTER TABLE `financeiro_chargebacks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fin_chargebacks_evento_status` (`evento_id`,`status`),
  ADD KEY `idx_fin_chargebacks_inscricao` (`inscricao_id`),
  ADD KEY `idx_fin_chargebacks_pagamento_ml` (`pagamento_ml_id`);

--
-- Índices de tabela `financeiro_contas_bancarias`
--
ALTER TABLE `financeiro_contas_bancarias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fin_contas_beneficiario` (`beneficiario_id`),
  ADD KEY `idx_fin_contas_status` (`status`);

--
-- Índices de tabela `financeiro_estornos`
--
ALTER TABLE `financeiro_estornos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fin_estornos_evento_status` (`evento_id`,`status`),
  ADD KEY `idx_fin_estornos_inscricao` (`inscricao_id`),
  ADD KEY `idx_fin_estornos_pagamento_ml` (`pagamento_ml_id`);

--
-- Índices de tabela `financeiro_fechamentos`
--
ALTER TABLE `financeiro_fechamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fin_fechamentos_evento_data` (`evento_id`,`fechado_em`),
  ADD KEY `fk_fin_fechamentos_user` (`fechado_por`);

--
-- Índices de tabela `financeiro_ledger`
--
ALTER TABLE `financeiro_ledger`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fin_ledger_evento_status_ocorrido` (`evento_id`,`status`,`ocorrido_em`),
  ADD KEY `idx_fin_ledger_evento_ocorrido` (`evento_id`,`ocorrido_em`),
  ADD KEY `idx_fin_ledger_evento_disponivel` (`evento_id`,`disponivel_em`),
  ADD KEY `idx_fin_ledger_origem` (`origem_tipo`,`origem_id`),
  ADD KEY `idx_fin_ledger_status_direcao` (`status`,`direcao`),
  ADD KEY `fk_fin_ledger_created_by` (`created_by`);

--
-- Índices de tabela `financeiro_repasses`
--
ALTER TABLE `financeiro_repasses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fin_repasses_evento_status` (`evento_id`,`status`),
  ADD KEY `idx_fin_repasses_agendado` (`agendado_para`,`status`),
  ADD KEY `idx_fin_repasses_beneficiario` (`beneficiario_id`),
  ADD KEY `idx_fin_repasses_conta` (`conta_bancaria_id`);

--
-- Índices de tabela `financeiro_webhook_eventos`
--
ALTER TABLE `financeiro_webhook_eventos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_fin_webhook_gateway_event` (`gateway`,`event_id`),
  ADD KEY `idx_fin_webhook_payment` (`payment_id`),
  ADD KEY `idx_fin_webhook_status` (`status`),
  ADD KEY `idx_fin_webhook_created` (`created_at`);

--
-- Índices de tabela `formas_pagamento_evento`
--
ALTER TABLE `formas_pagamento_evento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evento_id` (`evento_id`);

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
-- Índices de tabela `inscricoes_cupons`
--
ALTER TABLE `inscricoes_cupons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inscricao_id` (`inscricao_id`),
  ADD KEY `cupom_id` (`cupom_id`);

--
-- Índices de tabela `inscricoes_produtos_extras`
--
ALTER TABLE `inscricoes_produtos_extras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inscricao_id` (`inscricao_id`),
  ADD KEY `produto_extra_evento_id` (`produto_extra_evento_id`);

--
-- Índices de tabela `kits_eventos`
--
ALTER TABLE `kits_eventos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evento_id` (`evento_id`),
  ADD KEY `idx_kits_evento` (`evento_id`),
  ADD KEY `idx_kits_modalidade` (`modalidade_evento_id`),
  ADD KEY `idx_kits_ativo` (`ativo`);

--
-- Índices de tabela `kit_modalidade_evento`
--
ALTER TABLE `kit_modalidade_evento`
  ADD PRIMARY KEY (`kit_id`,`modalidade_evento_id`),
  ADD KEY `modalidade_evento_id` (`modalidade_evento_id`);

--
-- Índices de tabela `kit_produtos`
--
ALTER TABLE `kit_produtos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kit_id` (`kit_id`),
  ADD KEY `produto_id` (`produto_id`);

--
-- Índices de tabela `kit_templates`
--
ALTER TABLE `kit_templates`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `kit_template_produtos`
--
ALTER TABLE `kit_template_produtos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kit_template_id` (`kit_template_id`),
  ADD KEY `produto_id` (`produto_id`);

--
-- Índices de tabela `leads_organizadores`
--
ALTER TABLE `leads_organizadores`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `logs_admin`
--
ALTER TABLE `logs_admin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `logs_inscricoes_pagamentos`
--
ALTER TABLE `logs_inscricoes_pagamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_inscricao_id` (`inscricao_id`),
  ADD KEY `idx_payment_id` (`payment_id`),
  ADD KEY `idx_usuario_id` (`usuario_id`),
  ADD KEY `idx_nivel` (`nivel`),
  ADD KEY `idx_acao` (`acao`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_evento_id` (`evento_id`),
  ADD KEY `idx_status_pagamento` (`status_pagamento`);

--
-- Índices de tabela `logs_sincronizacao_mp`
--
ALTER TABLE `logs_sincronizacao_mp`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tipo` (`tipo`),
  ADD KEY `idx_inicio` (`inicio`),
  ADD KEY `idx_status` (`status`);

--
-- Índices de tabela `lotes_inscricao`
--
ALTER TABLE `lotes_inscricao`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_evento_modalidade_lote` (`evento_id`,`modalidade_id`,`numero_lote`),
  ADD KEY `idx_evento` (`evento_id`),
  ADD KEY `idx_modalidade` (`modalidade_id`),
  ADD KEY `idx_datas` (`data_inicio`,`data_fim`),
  ADD KEY `idx_ativo` (`ativo`);

--
-- Índices de tabela `modalidades`
--
ALTER TABLE `modalidades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_evento` (`evento_id`),
  ADD KEY `idx_categoria` (`categoria_id`);

--
-- Índices de tabela `openai_token_usage`
--
ALTER TABLE `openai_token_usage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_openai_usuario` (`usuario_id`),
  ADD KEY `idx_openai_data` (`data_hora`),
  ADD KEY `idx_openai_endpoint` (`endpoint`);

--
-- Índices de tabela `organizadores`
--
ALTER TABLE `organizadores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `pagamentos`
--
ALTER TABLE `pagamentos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_payment_id` (`payment_id`),
  ADD KEY `inscricao_id` (`inscricao_id`);

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
-- Índices de tabela `papeis`
--
ALTER TABLE `papeis`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `participante`
--
ALTER TABLE `participante`
  ADD PRIMARY KEY (`usuario_id`);

--
-- Índices de tabela `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inscricao_id` (`inscricao_id`);

--
-- Índices de tabela `periodos_inscricao`
--
ALTER TABLE `periodos_inscricao`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evento_id` (`evento_id`);

--
-- Índices de tabela `planos_treino_gerados`
--
ALTER TABLE `planos_treino_gerados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_planos_usuario` (`usuario_id`),
  ADD KEY `idx_planos_profissional` (`profissional_id`),
  ADD KEY `idx_planos_anamnese` (`anamnese_id`),
  ADD KEY `idx_planos_inscricao` (`inscricao_id`),
  ADD KEY `idx_planos_inscricao_data` (`inscricao_id`,`data_criacao_plano`),
  ADD KEY `idx_planos_usuario_data` (`usuario_id`,`data_criacao_plano`),
  ADD KEY `idx_planos_assessoria` (`assessoria_id`),
  ADD KEY `idx_planos_programa` (`programa_id`);

--
-- Índices de tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `produtos_extras`
--
ALTER TABLE `produtos_extras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evento_id` (`evento_id`),
  ADD KEY `idx_produtos_extras_ativo` (`ativo`),
  ADD KEY `idx_evento` (`evento_id`),
  ADD KEY `idx_ativo` (`ativo`);

--
-- Índices de tabela `produtos_extras_modalidade`
--
ALTER TABLE `produtos_extras_modalidade`
  ADD PRIMARY KEY (`id`),
  ADD KEY `modalidade_evento_id` (`modalidade_evento_id`);

--
-- Índices de tabela `produto_extra_produtos`
--
ALTER TABLE `produto_extra_produtos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_produto_extra` (`produto_extra_id`),
  ADD KEY `idx_produto` (`produto_id`),
  ADD KEY `idx_ativo` (`ativo`);

--
-- Índices de tabela `programacao_evento`
--
ALTER TABLE `programacao_evento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evento_id` (`evento_id`),
  ADD KEY `idx_hora_inicio` (`hora_inicio`),
  ADD KEY `idx_tipo_hora` (`tipo`,`hora_inicio`);

--
-- Índices de tabela `progresso_treino`
--
ALTER TABLE `progresso_treino`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `idx_progresso_treino` (`treino_id`,`data_realizado`),
  ADD KEY `idx_glicemia_monitoring` (`glicemia_pre_treino`,`glicemia_pos_treino`),
  ADD KEY `idx_progresso_registrado_por` (`registrado_por_usuario_id`);

--
-- Índices de tabela `questionario_evento`
--
ALTER TABLE `questionario_evento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evento_id` (`evento_id`);

--
-- Índices de tabela `questionario_evento_modalidade`
--
ALTER TABLE `questionario_evento_modalidade`
  ADD PRIMARY KEY (`id`),
  ADD KEY `questionario_evento_id` (`questionario_evento_id`),
  ADD KEY `modalidade_id` (`modalidade_id`);

--
-- Índices de tabela `repasse_organizadores`
--
ALTER TABLE `repasse_organizadores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evento_id` (`evento_id`),
  ADD KEY `organizador_id` (`organizador_id`);

--
-- Índices de tabela `resultados_evento`
--
ALTER TABLE `resultados_evento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inscricao_id` (`inscricao_id`);

--
-- Índices de tabela `retirada_kits_evento`
--
ALTER TABLE `retirada_kits_evento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evento_id` (`evento_id`);

--
-- Índices de tabela `solicitacoes_evento`
--
ALTER TABLE `solicitacoes_evento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`responsavel_email`),
  ADD KEY `idx_status` (`status`);

--
-- Índices de tabela `termos_eventos`
--
ALTER TABLE `termos_eventos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tipo_ativo` (`tipo`,`ativo`);

--
-- Índices de tabela `transacoes_mp_cache`
--
ALTER TABLE `transacoes_mp_cache`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_payment_id` (`payment_id`),
  ADD KEY `idx_external_reference` (`external_reference`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_date_created` (`date_created`),
  ADD KEY `idx_ultima_sincronizacao` (`ultima_sincronizacao`),
  ADD KEY `idx_payer_email` (`payer_email`),
  ADD KEY `idx_payment_method` (`payment_method_id`);

--
-- Índices de tabela `treinos`
--
ALTER TABLE `treinos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_treinos_anamnese` (`anamnese_id`),
  ADD KEY `idx_treinos_usuario` (`usuario_id`),
  ADD KEY `idx_treinos_plano` (`plano_treino_gerado_id`),
  ADD KEY `idx_treinos_dia_semana` (`dia_semana_id`),
  ADD KEY `idx_treinos_semana_numero` (`semana_numero`),
  ADD KEY `idx_treinos_assessoria` (`assessoria_id`),
  ADD KEY `idx_treinos_programa` (`programa_id`),
  ADD KEY `idx_treinos_criado_por` (`criado_por_usuario_id`);

--
-- Índices de tabela `treino_exercicios`
--
ALTER TABLE `treino_exercicios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_treino_exercicios_treino` (`treino_id`),
  ADD KEY `idx_treino_exercicios_exercicio` (`exercicio_id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_token_recuperacao` (`token_recuperacao`);

--
-- Índices de tabela `usuario_admin`
--
ALTER TABLE `usuario_admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_token_recuperacao` (`token_recuperacao`);

--
-- Índices de tabela `usuario_papeis`
--
ALTER TABLE `usuario_papeis`
  ADD PRIMARY KEY (`usuario_id`,`papel_id`),
  ADD KEY `papel_id` (`papel_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `aceites_termos`
--
ALTER TABLE `aceites_termos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `anamneses`
--
ALTER TABLE `anamneses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `assessorias`
--
ALTER TABLE `assessorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `assessoria_atletas`
--
ALTER TABLE `assessoria_atletas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `assessoria_convites`
--
ALTER TABLE `assessoria_convites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `assessoria_equipe`
--
ALTER TABLE `assessoria_equipe`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `assessoria_programas`
--
ALTER TABLE `assessoria_programas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `assessoria_programa_atletas`
--
ALTER TABLE `assessoria_programa_atletas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `banners`
--
ALTER TABLE `banners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `camisas`
--
ALTER TABLE `camisas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de tabela `cashback_atletas`
--
ALTER TABLE `cashback_atletas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de tabela `config`
--
ALTER TABLE `config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de tabela `config_historico`
--
ALTER TABLE `config_historico`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `cupons_remessa`
--
ALTER TABLE `cupons_remessa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `enderecos_entrega`
--
ALTER TABLE `enderecos_entrega`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `estoque_kits`
--
ALTER TABLE `estoque_kits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `estoque_produtos_extras`
--
ALTER TABLE `estoque_produtos_extras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `financeiro_beneficiarios`
--
ALTER TABLE `financeiro_beneficiarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `financeiro_chargebacks`
--
ALTER TABLE `financeiro_chargebacks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `financeiro_contas_bancarias`
--
ALTER TABLE `financeiro_contas_bancarias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `financeiro_estornos`
--
ALTER TABLE `financeiro_estornos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `financeiro_fechamentos`
--
ALTER TABLE `financeiro_fechamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `financeiro_ledger`
--
ALTER TABLE `financeiro_ledger`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `financeiro_repasses`
--
ALTER TABLE `financeiro_repasses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `financeiro_webhook_eventos`
--
ALTER TABLE `financeiro_webhook_eventos`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `formas_pagamento_evento`
--
ALTER TABLE `formas_pagamento_evento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `inscricoes`
--
ALTER TABLE `inscricoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `inscricoes_cupons`
--
ALTER TABLE `inscricoes_cupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `inscricoes_produtos_extras`
--
ALTER TABLE `inscricoes_produtos_extras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de tabela `kits_eventos`
--
ALTER TABLE `kits_eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de tabela `kit_produtos`
--
ALTER TABLE `kit_produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT de tabela `kit_templates`
--
ALTER TABLE `kit_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de tabela `kit_template_produtos`
--
ALTER TABLE `kit_template_produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT de tabela `leads_organizadores`
--
ALTER TABLE `leads_organizadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `logs_admin`
--
ALTER TABLE `logs_admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `logs_inscricoes_pagamentos`
--
ALTER TABLE `logs_inscricoes_pagamentos`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=478;

--
-- AUTO_INCREMENT de tabela `logs_sincronizacao_mp`
--
ALTER TABLE `logs_sincronizacao_mp`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `lotes_inscricao`
--
ALTER TABLE `lotes_inscricao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de tabela `modalidades`
--
ALTER TABLE `modalidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de tabela `openai_token_usage`
--
ALTER TABLE `openai_token_usage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `organizadores`
--
ALTER TABLE `organizadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `pagamentos`
--
ALTER TABLE `pagamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `pagamentos_ml`
--
ALTER TABLE `pagamentos_ml`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de tabela `papeis`
--
ALTER TABLE `papeis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `periodos_inscricao`
--
ALTER TABLE `periodos_inscricao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `planos_treino_gerados`
--
ALTER TABLE `planos_treino_gerados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT de tabela `produtos_extras`
--
ALTER TABLE `produtos_extras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `produtos_extras_modalidade`
--
ALTER TABLE `produtos_extras_modalidade`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `produto_extra_produtos`
--
ALTER TABLE `produto_extra_produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `programacao_evento`
--
ALTER TABLE `programacao_evento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `progresso_treino`
--
ALTER TABLE `progresso_treino`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `questionario_evento`
--
ALTER TABLE `questionario_evento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de tabela `questionario_evento_modalidade`
--
ALTER TABLE `questionario_evento_modalidade`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT de tabela `repasse_organizadores`
--
ALTER TABLE `repasse_organizadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `resultados_evento`
--
ALTER TABLE `resultados_evento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `retirada_kits_evento`
--
ALTER TABLE `retirada_kits_evento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `solicitacoes_evento`
--
ALTER TABLE `solicitacoes_evento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `termos_eventos`
--
ALTER TABLE `termos_eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `transacoes_mp_cache`
--
ALTER TABLE `transacoes_mp_cache`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `treinos`
--
ALTER TABLE `treinos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de tabela `treino_exercicios`
--
ALTER TABLE `treino_exercicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de tabela `usuario_admin`
--
ALTER TABLE `usuario_admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `assessorias`
--
ALTER TABLE `assessorias`
  ADD CONSTRAINT `fk_assessorias_responsavel_usuario` FOREIGN KEY (`responsavel_usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `assessoria_atletas`
--
ALTER TABLE `assessoria_atletas`
  ADD CONSTRAINT `fk_assessoria_atletas_assessor` FOREIGN KEY (`assessor_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_assessoria_atletas_assessoria` FOREIGN KEY (`assessoria_id`) REFERENCES `assessorias` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_assessoria_atletas_atleta` FOREIGN KEY (`atleta_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `assessoria_convites`
--
ALTER TABLE `assessoria_convites`
  ADD CONSTRAINT `fk_convites_assessoria` FOREIGN KEY (`assessoria_id`) REFERENCES `assessorias` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_convites_atleta` FOREIGN KEY (`atleta_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_convites_enviado_por` FOREIGN KEY (`enviado_por_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `assessoria_equipe`
--
ALTER TABLE `assessoria_equipe`
  ADD CONSTRAINT `fk_assessoria_equipe_assessoria` FOREIGN KEY (`assessoria_id`) REFERENCES `assessorias` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_assessoria_equipe_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `assessoria_programas`
--
ALTER TABLE `assessoria_programas`
  ADD CONSTRAINT `fk_programas_assessoria` FOREIGN KEY (`assessoria_id`) REFERENCES `assessorias` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_programas_evento` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `assessoria_programa_atletas`
--
ALTER TABLE `assessoria_programa_atletas`
  ADD CONSTRAINT `fk_programa_atletas_atleta` FOREIGN KEY (`atleta_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_programa_atletas_programa` FOREIGN KEY (`programa_id`) REFERENCES `assessoria_programas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `categorias`
--
ALTER TABLE `categorias`
  ADD CONSTRAINT `fk_categorias_evento` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `config_historico`
--
ALTER TABLE `config_historico`
  ADD CONSTRAINT `fk_config_historico_config` FOREIGN KEY (`config_id`) REFERENCES `config` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `eventos`
--
ALTER TABLE `eventos`
  ADD CONSTRAINT `fk_evento_deleted_by` FOREIGN KEY (`deleted_by`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `financeiro_beneficiarios`
--
ALTER TABLE `financeiro_beneficiarios`
  ADD CONSTRAINT `fk_fin_beneficiarios_organizador` FOREIGN KEY (`organizador_id`) REFERENCES `organizadores` (`id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `financeiro_chargebacks`
--
ALTER TABLE `financeiro_chargebacks`
  ADD CONSTRAINT `fk_fin_chargebacks_evento` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_fin_chargebacks_inscricao` FOREIGN KEY (`inscricao_id`) REFERENCES `inscricoes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_fin_chargebacks_pagamento_ml` FOREIGN KEY (`pagamento_ml_id`) REFERENCES `pagamentos_ml` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `financeiro_contas_bancarias`
--
ALTER TABLE `financeiro_contas_bancarias`
  ADD CONSTRAINT `fk_fin_contas_beneficiario` FOREIGN KEY (`beneficiario_id`) REFERENCES `financeiro_beneficiarios` (`id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `financeiro_estornos`
--
ALTER TABLE `financeiro_estornos`
  ADD CONSTRAINT `fk_fin_estornos_evento` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_fin_estornos_inscricao` FOREIGN KEY (`inscricao_id`) REFERENCES `inscricoes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_fin_estornos_pagamento_ml` FOREIGN KEY (`pagamento_ml_id`) REFERENCES `pagamentos_ml` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `financeiro_fechamentos`
--
ALTER TABLE `financeiro_fechamentos`
  ADD CONSTRAINT `fk_fin_fechamentos_evento` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_fin_fechamentos_user` FOREIGN KEY (`fechado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `financeiro_ledger`
--
ALTER TABLE `financeiro_ledger`
  ADD CONSTRAINT `fk_fin_ledger_created_by` FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_fin_ledger_evento` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `financeiro_repasses`
--
ALTER TABLE `financeiro_repasses`
  ADD CONSTRAINT `fk_fin_repasses_beneficiario` FOREIGN KEY (`beneficiario_id`) REFERENCES `financeiro_beneficiarios` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_fin_repasses_conta` FOREIGN KEY (`conta_bancaria_id`) REFERENCES `financeiro_contas_bancarias` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_fin_repasses_evento` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `kit_modalidade_evento`
--
ALTER TABLE `kit_modalidade_evento`
  ADD CONSTRAINT `kit_modalidade_evento_ibfk_1` FOREIGN KEY (`kit_id`) REFERENCES `kits_eventos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `kit_modalidade_evento_ibfk_2` FOREIGN KEY (`modalidade_evento_id`) REFERENCES `modalidades` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `kit_produtos`
--
ALTER TABLE `kit_produtos`
  ADD CONSTRAINT `kit_produtos_ibfk_1` FOREIGN KEY (`kit_id`) REFERENCES `kits_eventos` (`id`),
  ADD CONSTRAINT `kit_produtos_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`);

--
-- Restrições para tabelas `lotes_inscricao`
--
ALTER TABLE `lotes_inscricao`
  ADD CONSTRAINT `fk_lotes_modalidade` FOREIGN KEY (`modalidade_id`) REFERENCES `modalidades` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `modalidades`
--
ALTER TABLE `modalidades`
  ADD CONSTRAINT `fk_modalidades_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`),
  ADD CONSTRAINT `fk_modalidades_evento` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `planos_treino_gerados`
--
ALTER TABLE `planos_treino_gerados`
  ADD CONSTRAINT `fk_planos_assessoria` FOREIGN KEY (`assessoria_id`) REFERENCES `assessorias` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_planos_programa` FOREIGN KEY (`programa_id`) REFERENCES `assessoria_programas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `produto_extra_produtos`
--
ALTER TABLE `produto_extra_produtos`
  ADD CONSTRAINT `fk_produto_extra_produtos_extra` FOREIGN KEY (`produto_extra_id`) REFERENCES `produtos_extras` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_produto_extra_produtos_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `progresso_treino`
--
ALTER TABLE `progresso_treino`
  ADD CONSTRAINT `fk_progresso_registrado_por` FOREIGN KEY (`registrado_por_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Restrições para tabelas `questionario_evento_modalidade`
--
ALTER TABLE `questionario_evento_modalidade`
  ADD CONSTRAINT `fk_qe_modalidade_modalidade` FOREIGN KEY (`modalidade_id`) REFERENCES `modalidades` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_qe_modalidade_pergunta` FOREIGN KEY (`questionario_evento_id`) REFERENCES `questionario_evento` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `treinos`
--
ALTER TABLE `treinos`
  ADD CONSTRAINT `fk_treinos_assessoria` FOREIGN KEY (`assessoria_id`) REFERENCES `assessorias` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_treinos_criado_por` FOREIGN KEY (`criado_por_usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_treinos_programa` FOREIGN KEY (`programa_id`) REFERENCES `assessoria_programas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
