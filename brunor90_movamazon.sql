-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 30/01/2026 às 23:12
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
  `status` enum('pendente','em_analise','aprovada','arquivada') COLLATE utf8mb4_unicode_ci DEFAULT 'pendente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `anamneses`
--

INSERT INTO `anamneses` (`id`, `usuario_id`, `inscricao_id`, `profissional_id`, `data_anamnese`, `peso`, `altura`, `imc`, `fc_maxima`, `vo2_max`, `zona_alvo_treino`, `doencas_preexistentes`, `uso_medicamentos`, `nivel_atividade`, `objetivo_principal`, `foco_primario`, `max_glicemia`, `limitacoes_fisicas`, `preferencias_atividades`, `disponibilidade_horarios`, `historico_corridas`, `assinatura_aluno`, `assinatura_responsavel`, `status`) VALUES
(3, 18, NULL, NULL, '2025-12-19 17:14:02', 82.00, 163, 30.86, NULL, NULL, NULL, 'Pré Diabetes', NULL, 'ativo', 'saude', NULL, NULL, NULL, 'Corrida, caminhada, natação', 'Manhã ( 06 as 09h)', 'Já participei de corridas de 10km', NULL, NULL, 'pendente'),
(4, 19, NULL, NULL, '2025-12-19 22:39:41', 63.00, 165, 23.14, NULL, NULL, NULL, NULL, NULL, 'ativo', 'saude', NULL, NULL, NULL, 'Corrida', 'A definir pelo participante', NULL, NULL, NULL, 'pendente'),
(5, 17, NULL, NULL, '2026-01-26 21:07:06', 65.00, 165, 23.88, NULL, NULL, NULL, 'Não tenho.', 'Nenhum.', 'ativo', 'preparacao_corrida', NULL, NULL, 'Dor no joelho esquerdo', 'Musculação e corrida', 'Todos os dias pela manhã', 'Já participei de duas maratonas e uma corrida de 50 km', NULL, NULL, 'pendente');

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
(10, 8, NULL, 'G', 300, 0, 300, 0, 1, '2025-12-20 12:23:09'),
(11, 8, NULL, 'GG', 200, 0, 200, 0, 1, '2025-12-20 12:23:24'),
(12, 8, NULL, 'XG', 100, 0, 100, 0, 1, '2025-12-20 12:23:36'),
(13, 8, NULL, 'XXG', 40, 0, 40, 0, 1, '2025-12-20 12:23:49');

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
(19, 8, 'Comunidade Acadêmica', 'Pessoa do meio acadêmico', 'Docentes em geral', 0, 80, 1, 1, 1, NULL, 1, '2025-12-20 11:33:40', '2025-12-20 11:33:40');

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
(20, 'treino.exigir_inscricao', 'true', 'boolean', 'treino', 'Exigir inscrição confirmada para gerar treino (desativar apenas temporariamente para testes/desenvolvimento)', 1, 1, NULL, '2026-01-31 01:47:27', '2026-01-31 01:47:27', NULL);

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
(9, 'Cupom teste', 'ICOA-PKCV', 50.00, 'percentual', 'ambos', 20, 0, 0, '2026-01-29 12:11:21', '2026-01-25', '2026-01-31', 'ativo', 8, NULL, NULL);

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
(7, 'III Corrida Sauim de Coleira', 'A III Corrida Sauim de Coleira engloba três grandes pilares da nossa sociedade, esporte, por meio da prática de atividade física, meio ambiente, por meio da busca pela preservação ambiental e pela proteção do sauim-de-coleira e a Educação, por meio do Projeto de extensão da UEA o Projeto Primatas, que visa levar para a sociedade manauara conhecimentos de conservação da biodiversidade e sensibilização ambiental. \r\nEsta corrida que já está na terceira versão, é um evento para todos, independentemente da idade ou do nível de condicionamento físico, pois este ano teremos a caminhada em família.', '2026-10-24', NULL, 'corrida-rua', 'Misto', 'Manaus - AM', NULL, NULL, NULL, NULL, 'Manaus', 'AM', 'Brasil', 'sim', 'rascunho', 3, NULL, NULL, 0, NULL, NULL, 2000, NULL, NULL, '2025-12-19 14:13:04', NULL, '2026-10-24', NULL, NULL, NULL, NULL, NULL),
(8, 'III CORRIDA SAUIM DE COLEIRA', 'A III Corrida Sauim de Coleira engloba três grandes pilares da nossa sociedade, esporte, por meio da prática de atividade física, meio ambiente, por meio da busca pela preservação ambiental e pela proteção do sauim-de-coleira e a Educação, por meio do Projeto de extensão da UEA o Projeto Primatas, que visa levar para a sociedade manauara conhecimentos de conservação da biodiversidade e sensibilização ambiental.', '2026-10-24', '2026-10-19', 'corrida_rua', 'misto', 'Manaus - AM', '69050-020', 'https://maps.app.goo.gl/6TBM6FtyMU3iDqZGA?g_st=awb', 'Avenida Darcyr Vargas', '1200', 'Manaus', '', 'Brasil', 'sim', 'ativo', 4, 129.50, 5.00, 0, 2.50, 5.00, 2000, '2026-10-19', '23:59:00', '2025-12-19 14:31:48', '06:00:00', '2026-10-24', 'evento_8.png', NULL, NULL, NULL, 'api/uploads/regulamentos/regulamento_8.pdf');

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
(6, 19, 8, 24, NULL, NULL, NULL, NULL, 'M', NULL, '[]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-23 21:16:05', 'cancelada', 'cancelado', 129.00, 0.00, NULL, NULL, NULL, NULL, 1, 0, 'MOVAMAZON_1766524480_19', '260742905-92184687-d7d0-4f31-93a5-019e1fd373e4', NULL, 0, NULL, NULL),
(7, 21, 8, 25, NULL, NULL, NULL, NULL, 'M', NULL, '[]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-31 01:48:58', 'pendente', 'processando', 20.00, 0.00, NULL, NULL, '2025-12-29 22:59:59', 'pix', 1, 0, '144201992786', '260742905-74f8ed9b-667e-46a0-a916-d2adc6e83639', NULL, 0, NULL, NULL),
(9, 17, 8, 24, NULL, NULL, NULL, NULL, 'P', NULL, '[]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-30 23:29:46', 'pendente', 'pendente', 109.00, 0.00, NULL, NULL, NULL, 'pix', 1, 0, '144173129018', '260742905-e950afd6-36e7-4d2b-a2d2-6df2eae34a1d', NULL, 0, NULL, NULL),
(10, 20, 8, 22, NULL, NULL, NULL, NULL, 'M', NULL, '[]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-25 21:35:36', 'cancelada', 'cancelado', 129.00, 0.00, NULL, NULL, NULL, NULL, 1, 0, 'MOVAMAZON_1766698536_20', '260742905-6cd34709-b2ed-4a24-8e05-bf749b5b6bcc', NULL, 0, NULL, NULL),
(11, 18, 8, 25, NULL, NULL, NULL, NULL, 'M', NULL, '[]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-30 23:44:53', 'pendente', 'pendente', 20.00, 0.00, NULL, NULL, '2026-01-29 22:59:59', 'boleto', 1, 0, '142946577853', '260742905-2d66e472-fe70-48a3-8a66-4e3765f1b56d', NULL, 0, NULL, NULL),
(12, 23, 8, 23, NULL, NULL, NULL, NULL, 'M', NULL, '[]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-31 11:02:50', 'pendente', 'pendente', 109.00, 0.00, NULL, NULL, '2026-01-05 22:59:59', 'boleto', 1, 0, '140145304058', '260742905-8dcf7da1-f849-4967-8db1-d384e891e315', NULL, 0, NULL, NULL),
(13, 22, 8, 25, NULL, NULL, NULL, NULL, 'G', NULL, '[{\"id\":5,\"nome\":\"camiseta da corrida\",\"valor\":30}]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-30 20:18:06', 'pendente', 'pendente', 50.00, 0.00, NULL, NULL, '2026-02-02 22:59:59', 'boleto', 1, 0, '143469225233', '260742905-e0d84d4b-8553-4b85-8fc9-b9809e40d4fb', NULL, 0, NULL, NULL),
(14, 24, 8, 22, NULL, NULL, NULL, NULL, 'M', NULL, '[]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-26 22:02:02', 'pendente', 'pendente', 129.00, 0.00, NULL, NULL, '2026-01-29 22:59:59', 'boleto', 1, 0, '142946715951', '260742905-d1aecc16-bc95-42b4-80d4-6bb31b819e9e', NULL, 0, NULL, NULL),
(15, 25, 8, 22, NULL, NULL, NULL, NULL, 'M', NULL, '[]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-30 19:11:21', 'pendente', 'pendente', 129.00, 0.00, NULL, NULL, NULL, NULL, 1, 0, 'MOVAMAZON_1769800269_25', '260742905-249c39b4-d314-4784-b547-379ed1c9ea7e', NULL, 0, NULL, NULL),
(16, 26, 8, 24, NULL, NULL, NULL, NULL, 'P', NULL, '[{\"id\":5,\"nome\":\"camiseta da corrida\",\"valor\":30}]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-30 19:47:17', 'pendente', 'pendente', 139.00, 0.00, NULL, NULL, '2026-02-02 22:59:59', 'pix', 1, 0, '143470736925', '260742905-30959ebf-4bd6-43a4-a823-ca0ca268f0ad', NULL, 0, NULL, NULL);

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
(21, 13, 5, 1, 'pendente', '2026-01-30 17:18:06');

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
(50, 27, 37, 1, 1, 1, '2026-01-29 18:23:31'),
(51, 28, 37, 1, 1, 1, '2026-01-29 18:23:44'),
(52, 25, 43, 1, 1, 1, '2026-01-29 18:23:55'),
(53, 26, 43, 1, 1, 1, '2026-01-29 18:24:17'),
(54, 29, 30, 1, 1, 1, '2026-01-29 18:24:47');

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
(5, 'Kit do atleta - Público em geral para a prova de 10 Km e 5 km', 'Kit completo para a prova de 10 Km e 5 km - Público em Geral', 129.00, 'kit_template_5.png', 1, 1, '2025-12-20 12:13:47', '2026-01-29 17:16:18'),
(12, 'Kit para a prova de 5 e 10 Km - Comunidade Acadêmica', 'Kit completo para a prova de 5 e 10 Km - Comunidade acadêmica.', 109.00, 'kit_template_12.png', 1, 1, '2025-12-29 22:51:59', '2026-01-29 17:16:33'),
(13, 'Kit Teste', '', 50.00, 'kit_template_13.png', 1, 1, '2026-01-29 17:26:23', '2026-01-29 17:26:23');

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
(86, 5, 37, 1, 1, 1, '2026-01-29 17:16:18'),
(87, 12, 43, 1, 1, 1, '2026-01-29 17:16:33'),
(88, 13, 30, 1, 1, 1, '2026-01-29 17:26:23');

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
(5, 'EUDIMACI BARBOZA DE LIRA', 'eudimaci08@yahoo.com.br', '+55 +55 92982027654', 'EBL EVENTOS ESPORTIVOS', 'AM', 'corrida-rua', '1', 'III CORRIDA SAUIM DE COLEIRA', 'sim', 'Amigos', 'novo', '2025-12-19 14:21:32', NULL, NULL);

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
(354, 'SUCCESS', 'PIX_GERADO', 7, '144201992786', NULL, NULL, NULL, 21.00, 'pix', 'processando', NULL, '{\"qr_code\":\"000******\"}', NULL, '191.189.17.91', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Mobile Safari/537.36', '2026-01-31 01:49:10');

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
(26, 8, 25, 8, 20.00, 'Vinte reais', '2026-01-29', '2026-01-31', 20, 7.00, 'participante', 16, 100, 1, 1, '2026-01-29 15:10:07', '2026-01-29 15:27:11');

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
(25, 8, 18, 'Corrida de 10 km para teste', 'Teste de Produção da Plataforma', '10k', 'corrida', 20, 1, '2026-01-29 14:57:47', '2026-01-29 14:57:47');

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
(4, 16, 'EBL EVENTOS ESPORTIVOS', 'AM', 'corrida-rua', '1', 'sim');

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
  `status` enum('pendente','pago','cancelado') COLLATE utf8mb4_unicode_ci DEFAULT 'pendente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- --------------------------------------------------------

--
-- Estrutura para tabela `papeis`
--

CREATE TABLE `papeis` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `foco_primario` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duracao_treino_geral` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dias_plano` int(11) DEFAULT '5' COMMENT 'Duração do plano em dias',
  `equipamento_geral` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(44, 'Kit teste da Plataforma', 'Kit teste da Plataforma', '', 20.00, 1, 'frontend/assets/img/produtos/produto_1769699660_697b794ca8511.jpeg', 1, '2026-01-29 15:14:05', '2026-01-29 15:14:20');

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
(5, 8, 'camiseta da corrida', 'Compra da camiseta do evento', 30.00, 1, 'outros', 1, '2025-12-22 14:24:23', '2025-12-22 14:24:23');

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
(10, 5, 25, 1, 1, '2025-12-22 14:24:23');

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
(12, 8, 'horario_largada', 'Largada da Prova ', 'Público em geral : 06:00 - 06:10\nLargada oficial da prova.', '06:00:00', '06:10:00', NULL, NULL, NULL, 1, 1, '2025-12-24 03:09:26');

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
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
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
(29, 8, 0, 'campo', 'texto_aberto', 'evento', NULL, '  Você acha importante conservar os animais ameaçados de extinção?', 0, 13, 1, 'publicada', 'publicada', '2025-12-21 02:10:34');

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
(31, 23, 22);

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
(5, 8, '2026-10-23', '10:00:00', '20:00:00', 'Reitoria da Universidade do  Estado do Amazonas', NULL, 'Trazer o comprovante de inscrição.', NULL, 'IDT ou CNH com foto.', 1, '2025-12-20 12:21:44');

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
(2, 'Eudimaci Barboza de Lira', 'eudimaci.pecim@gmail.com.br', '+55 +55 92982027654', '64655733420', '', 'CEO', 'EBL Eventos Esportivos', 'AM', 'Manaus', 'AM', 'corrida-rua', '1', 'III Corrida Sauim de Coleira', '2026-10-24', 2000, 'sim', 'regulamento_20251219104406_d0a87f19.pdf', NULL, NULL, '', 'A III Corrida Sauim de Coleira engloba três grandes pilares da nossa sociedade, esporte, por meio da prática de atividade física, meio ambiente, por meio da busca pela preservação ambiental e pela proteção do sauim-de-coleira e a Educação, por meio do Projeto de extensão da UEA o Projeto Primatas, que visa levar para a sociedade manauara conhecimentos de conservação da biodiversidade e sensibilização ambiental. \r\nEsta corrida que já está na terceira versão, é um evento para todos, independentemente da idade ou do nível de condicionamento físico, pois este ano teremos a caminhada em família.', 'Amigos', 'whatsapp', '', 'recusado', '2025-12-19 13:44:06', '2025-12-19 14:16:53'),
(3, 'EUDIMACI BARBOZA DE LIRA', 'eudimaci08@yahoo.com.br', '+55 +55 92982027654', '61.508.962/0001-40', '', 'CEO', 'EBL EVENTOS ESPORTIVOS', 'AM', 'Manaus', 'AM', 'corrida-rua', '1', 'III CORRIDA SAUIM DE COLEIRA', '2026-10-24', 2000, 'sim', 'regulamento_20251219112132_49fc179f.pdf', 'sim', NULL, '', 'A III Corrida Sauim de Coleira engloba três grandes pilares da nossa sociedade, esporte, por meio da prática de atividade física, meio ambiente, por meio da busca pela preservação ambiental e pela proteção do sauim-de-coleira e a Educação, por meio do Projeto de extensão da UEA o Projeto Primatas, que visa levar para a sociedade manauara conhecimentos de conservação da biodiversidade e sensibilização ambiental.', 'Amigos', '', '', 'aprovado', '2025-12-19 14:21:32', '2025-12-19 14:31:48');

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
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `termos_eventos`
--

INSERT INTO `termos_eventos` (`id`, `titulo`, `conteudo`, `versao`, `ativo`, `data_criacao`) VALUES
(3, 'Termo de Aceite a Plataforma', '<p><strong>TERMO DE USO PARA O USUÁRIO QUE DESEJA SE INSCREVER NO EVENTO.</strong></p><p>Como usuário do sistema <strong>MOVAMAZON</strong>&nbsp;você declara ter conhecimento e aceitar as seguintes cláusulas e condições:</p><ol><li>Estar ciente que:<ul><li>&nbsp;Todo o conteúdo como textos, fotos e vídeos dos eventos publicados no sistema&nbsp;<strong>MOVAMAZON</strong>&nbsp;são de responsabilidade integral do ORGANIZADOR do evento, cujos dados constam da página do evento e que o&nbsp;<strong>MOVAMAZON</strong>&nbsp;não exerce qualquer controle ou supervisão prévia</li><li>&nbsp;O ORGANIZADOR do evento é o único responsável pelo planejamento, organização e realização do evento sendo ele quem define as atrações, programação, local de realização, datas, valores dos ingressos, limite de ingressos disponíveis, idades mínimas e máximas, descontos eventuais que podem ser oferecidos entre outros.</li><li>&nbsp;Qualquer alteração na programação do evento, seja troca de datas, local ou até o seu cancelamento, bem como quaisquer ocorrências durante o EVENTO, são de responsabilidade única e exclusiva do ORGANIZADOR.</li></ul></li></ol><p>&nbsp;</p><ol><li>Que todas as informações por você declaradas no cadastro do sistema e na compra de ingressos e inscrição em eventos através do sistema&nbsp;<strong>MOVAMAZON</strong>&nbsp;são verdadeiras e estão de acordo com as leis vigentes.</li></ol><p>&nbsp;</p><ol><li>Que você está ciente e concorda com as REGRAS, REGULAMENTOS e TERMO ACORDO dos eventos divulgados no sistema&nbsp;<strong>MOVAMAZON</strong>&nbsp;a que for participar.</li></ol><p>&nbsp;</p><ol><li>Que você possui as habilidades técnicas e está apto fisicamente para participar dos eventos que comprar no&nbsp;<strong>MOVAMAZON</strong>&nbsp;isentando o&nbsp;<strong>MOVAMAZON</strong>&nbsp;e o ORGANIZADOR de qualquer problema de saúde que possa ocorrer pelo fato da participação no mesmo.</li></ol><p>&nbsp;</p><ol><li>Para a hipótese de você estar efetuando a inscrição em nome de terceiros, que você possui todos os direitos de representação de terceiros, possuindo autorização do terceiro para fornecimento dos dados de cadastro e, nos casos de realização de compras e inscrições para eles, declarando que o terceiro que participará do evento possui habilidades técnicas e está apto fisicamente a participar dos eventos que comprar no&nbsp;<strong>MOVAMAZON</strong>&nbsp;isentando o&nbsp;<strong>MOVAMAZON</strong>&nbsp;e o ORGANIZADOR de qualquer problema que possa ocorrer pelo fato da participação no mesmo.</li></ol><p>&nbsp;</p><ol><li>Que você autoriza o uso do seu direito de imagem, e dos terceiros por você inscritos e dos quais você possui autorização, para promoção de divulgação do evento, pelo ORGANIZADOR,&nbsp;<strong>MOVAMAZON</strong>&nbsp;e patrocinadores.</li></ol><p>&nbsp;</p><ol><li>Que você está ciente que o simples ato de efetuar o pedido no&nbsp;<strong>MOVAMAZON</strong>&nbsp;não garante o seu ingresso no evento e não garante o seu direito à retirada de eventuais tickets, sendo os mesmos garantidos apenas mediante a confirmação do pagamento do valor integral cobrado, no caso de opção de pagamento via boleto (para usuários no Brasil) ou no momento da autorização dada pelo cartão de crédito para débito do valor integral devido pela inscrição, no caso opção de pagamento via cartões de crédito.</li></ol><p>&nbsp;</p><ol><li>Que você está ciente de que eventuais pagamentos efetuados em valor divergente ao efetivamente devido (seja inferior ou superior) NÃO gerarão uma inscrição válida e não garantirão o seu ingresso no evento ou lhe darão direito à retirada de eventuais kits relativos ao evento.</li></ol><p>&nbsp;</p><ol><li>Que você está ciente de que caso a inscrição dê direito à retirada de kits, os mesmos somente poderão ser retirados nas datas e horários fixados pelo ORGANIZADOR e previstos na \"página de apresentação e inscrições\" do EVENTO. A eventual não retirada dos kits nas datas estabelecidas resultará na PERDA DO SEU DIREITO DE RETIRAR referidos kits.</li></ol><p>&nbsp;</p><ol><li>Em caso de arrependimento da compra, independente do motivo será realizado o estorno do valor pago desde que seja solicitado em até 7 dias corridos após a efetivação do pedido E desde que falte no mínimo 7 dias para o início do evento (ou de sua entrega de kit quando houver). Ambos os requisitos acima deverão ser preenchidos para que o estorno seja efetuado. Portanto, fica expressamente estabelecido que:<br>a) NÃO será efetuado estorno de valores solicitados após o prazo de pagamento de 7 (sete) dias, ainda que a solicitação ocorra faltando 7 (sete) dias ou mais para o início do evento,<br>b) também NÃO será efetuado estorno de valores solicitados dentro do prazo de pagamento de 7 (sete) dias, caso a solicitação ocorra a menos de 7 (sete) dias do início do evento ou da entrega dos kits quando houver.</li></ol><p>&nbsp;</p><ol><li>Nos casos em que for cabível o estorno por terem sido cumpridos cumulativamente os dois requisitos (pedido dentro do prazo de arrependimento e faltando 7 dias ou mais para o início do evento ou entrega dos kits), se o pedido foi pago com cartão de crédito o estorno será realizado no mesmo cartão utilizado sendo creditado em sua próxima fatura ou subsequente. Caso o pedido tenha sido pago com boleto bancário (para usuários no Brasil) ou outra forma de pagamento, o estorno será realizado em conta bancária do responsável pelo pedido em até 30 dias úteis após informada. A solicitação do estorno deve ser realizada por escrito através do link&nbsp;<a href=\"https://ajuda.ticketsports.com.br/hc/pt-br\">ajuda.ticketsports.com.br/pt-br</a>&nbsp;informando o número do pedido, nome, CPF (ou o documento de identificação nacional de acordocom o país do usuário) e e-mail do responsável utilizado para realizar o pedido.</li></ol><p>&nbsp;</p><ol><li>Que você isenta o&nbsp;<strong>MOVAMAZON</strong>&nbsp;de qualquer responsabilidade relacionada ao evento, de modo que quaisquer reclamações, tais como mas não limitadas a pedido de estorno, desistência de inscrições e pedidos de reembolso (ressalvado o disposto no item acima), adiamento ou cancelamento ou quaisquer outros assuntos relacionados ao evento deverão ser formulados diretamente ao ORGANIZADOR.</li></ol><p>&nbsp;</p><ol><li>Que a desistência de participação do evento quando não preenchidos os requisitos estabelecidos no item 10 apenas será possível nos casos e condições estabelecidos pelo organizador, no Regulamento da prova, devendo ser solicitada diretamente a ele. Em sendo possível, a devolução do valor ocorrerá nos termos determinados pelo organizador.</li></ol><p>&nbsp;</p><ol><li>Que você isenta o&nbsp;<strong>MOVAMAZON</strong>&nbsp;de qualquer responsabilidade relacionada à links existentes no \"site\" do&nbsp;<strong>MOVAMAZON</strong>&nbsp;ou a conteúdo assinado por terceiros, tal qual, colunas, blogs, comentários nos fóruns, vídeos, fotos, comentários nas notícias, mapas de treino, entre outros são de inteira responsabilidade de seus autores.</li></ol><p>&nbsp;</p><ol><li>Que você está ciente da impossibilidade de reprodução de qualquer conteúdo divulgado no&nbsp;<strong>MOVAMAZON</strong>, seja integral ou parcial, seja para uso comercial, pessoal ou editorial é proibido, salvo prévia autorização por escrito do&nbsp;<strong>MOVAMAZON</strong>&nbsp;ou pelo seu autor quando identificado.</li></ol><p>&nbsp;</p><ol><li>Que você está ciente que: qualquer contato com a&nbsp;<strong>MOVAMAZON</strong>&nbsp;deverá ocorrer por carta a ser endereçada à Rua Ricardo Severo, 73, Cep: 05010-010 Perdizes, Município de São Paulo, Estado de São Paulo, Brasil;<br>b) que o relacionamento com a&nbsp;<strong>MOVAMAZON</strong>&nbsp;é regido pela legislação brasileira, e,<br>c) que eventual disputa deverá ser solucionada na jurisdição brasileira competente (Foro do Estado de São Paulo).</li></ol><p>&nbsp;</p><ol><li>Que você tem conhecimento de que as regras específicas da prova são regidas pelo Regulamento elaborado pelo organizador e disponível no “site” da&nbsp;<strong>MOVAMAZON</strong>.</li></ol><p>&nbsp;</p><p>Este termo será registrado em cartório e cancela e substitui o termo registrado no 3º Cartório de Títulos e Documentos de São Paulo sob no&nbsp;<strong>9113638</strong>&nbsp;em&nbsp;<strong>03 de outubro de 2023</strong>&nbsp;e qualquer outro termo de compras posterior ao antes citado, registrado ou não.</p><p>&nbsp;</p><p>Este termo poderá ser alterado, a qualquer tempo, mediante registro do novo termo em substituição ao presente, sendo válido para os eventos que ocorrerem a partir do novo registro.</p><p><strong>Manaus, AM em 22 de dezembro de 2025.</strong></p><p>&nbsp;</p><p>MovAmazon.</p>', '1.0', 1, '2025-12-21 02:14:14');

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
  `ativo` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, 5, 'Aquecimento em Esteira', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Aquecimento em Esteira\",\"detalhes_item\":\"Caminhada leve progredindo para trote suave\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(2, 5, 'Corrida Contínua Leve', NULL, NULL, NULL, '20 minutos', NULL, NULL, '{\"nome_item\":\"Corrida Contínua Leve\",\"detalhes_item\":\"Corrida em ritmo conversacional, mantendo FC controlada\",\"fc_alvo\":\"65-75% FCmax (117-135 bpm)\",\"tempo_execucao\":\"20 minutos\",\"distancia\":\"3-4 km\",\"velocidade\":\"Ritmo conversacional\",\"observacoes\":\"Manter respiração controlada, poder conversar durante a corrida\"}', 'livre'),
(3, 5, 'Agachamento Livre', NULL, 3, '15', '45s por série', 0.00, '60s', '{\"nome_item\":\"Agachamento Livre\",\"detalhes_item\":\"3 séries de 15 repetições, foco na técnica\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"45s por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Peso corporal\",\"series\":\"3\",\"repeticoes\":\"15\"}', 'livre'),
(4, 5, 'Flexão de Braço', NULL, 3, '10', '30s por série', 0.00, '60s', '{\"nome_item\":\"Flexão de Braço\",\"detalhes_item\":\"3 séries de 10 repetições\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"30s por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Peso corporal\",\"series\":\"3\",\"repeticoes\":\"10\"}', 'livre'),
(5, 5, 'Prancha Frontal', NULL, 3, '30s', '30s por série', 0.00, '60s', '{\"nome_item\":\"Prancha Frontal\",\"detalhes_item\":\"3 séries de 30 segundos\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"30s por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Peso corporal\",\"series\":\"3\",\"repeticoes\":\"30s\"}', 'livre'),
(6, 5, 'Elevação de Panturrilha', NULL, 3, '15', '30s por série', 0.00, '60s', '{\"nome_item\":\"Elevação de Panturrilha\",\"detalhes_item\":\"3 séries de 15 repetições\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"30s por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Peso corporal\",\"series\":\"3\",\"repeticoes\":\"15\"}', 'livre'),
(7, 5, 'Alongamento de Membros Inferiores', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Alongamento de Membros Inferiores\",\"detalhes_item\":\"Alongamento suave para quadríceps, isquiotibiais e panturrilhas\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(8, 6, 'Caminhada Rápida', NULL, NULL, NULL, '5 minutos', NULL, NULL, '{\"nome_item\":\"Caminhada Rápida\",\"detalhes_item\":\"Caminhada rápida para aumentar a frequência cardíaca\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"5 minutos\"}', 'livre'),
(9, 6, 'Intervalos de Corrida', NULL, NULL, NULL, '15 minutos', NULL, '2 minutos', '{\"nome_item\":\"Intervalos de Corrida\",\"detalhes_item\":\"5x 1 minuto de corrida rápida intercalados com 2 minutos de caminhada\",\"fc_alvo\":\"75-85% FCmax durante corrida (135-153 bpm)\",\"tempo_execucao\":\"15 minutos\",\"tempo_recuperacao\":\"2 minutos\",\"tipo_recuperacao\":\"ativo\",\"distancia\":\"Variante\",\"velocidade\":\"Rápido durante intervalos\",\"observacoes\":\"Foco na técnica de corrida durante os intervalos rápidos\"}', 'livre'),
(10, 6, 'Elevação de Joelho', NULL, 3, '20', '30s por série', 0.00, '60s', '{\"nome_item\":\"Elevação de Joelho\",\"detalhes_item\":\"3 séries de 20 repetições\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"30s por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Peso corporal\",\"series\":\"3\",\"repeticoes\":\"20\"}', 'livre'),
(11, 6, 'Caminhada Lenta', NULL, NULL, NULL, '5 minutos', NULL, NULL, '{\"nome_item\":\"Caminhada Lenta\",\"detalhes_item\":\"Caminhada lenta para baixar a frequência cardíaca\",\"tempo_execucao\":\"5 minutos\"}', 'livre'),
(12, 7, 'Aquecimento Dinâmico', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Aquecimento Dinâmico\",\"detalhes_item\":\"Movimentos dinâmicos para preparar o corpo\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(13, 7, 'Corrida Longa', NULL, NULL, NULL, '30 minutos', NULL, NULL, '{\"nome_item\":\"Corrida Longa\",\"detalhes_item\":\"Corrida contínua em ritmo confortável\",\"fc_alvo\":\"70-80% FCmax (126-144 bpm)\",\"tempo_execucao\":\"30 minutos\",\"distancia\":\"5-6 km\",\"velocidade\":\"Moderada\",\"observacoes\":\"Foco na técnica e respiração\"}', 'livre'),
(14, 7, 'Afundo', NULL, 3, '12 por perna', '40s por série', 0.00, '60s', '{\"nome_item\":\"Afundo\",\"detalhes_item\":\"3 séries de 12 repetições por perna\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"40s por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Peso corporal\",\"series\":\"3\",\"repeticoes\":\"12 por perna\"}', 'livre'),
(15, 7, 'Prancha Lateral', NULL, 3, '30s por lado', '30s por série', 0.00, '60s', '{\"nome_item\":\"Prancha Lateral\",\"detalhes_item\":\"3 séries de 30 segundos por lado\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"30s por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Peso corporal\",\"series\":\"3\",\"repeticoes\":\"30s por lado\"}', 'livre'),
(16, 7, 'Alongamento Completo', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Alongamento Completo\",\"detalhes_item\":\"Alongamento de todos os grupos musculares trabalhados\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(17, 8, 'Aquecimento com Movimentos Articulares', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Aquecimento com Movimentos Articulares\",\"detalhes_item\":\"Movimentos articulares para preparar o corpo\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(18, 8, 'Fartlek', NULL, NULL, NULL, '25 minutos', NULL, NULL, '{\"nome_item\":\"Fartlek\",\"detalhes_item\":\"Corrida com variação de ritmo, alternando entre 2 minutos rápido e 3 minutos lento\",\"fc_alvo\":\"75-85% FCmax durante sprints (135-153 bpm)\",\"tempo_execucao\":\"25 minutos\",\"distancia\":\"4-5 km\",\"velocidade\":\"Variada\",\"observacoes\":\"Foco na variação de ritmo e recuperação ativa\"}', 'livre'),
(19, 8, 'Mobilidade de Quadril', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Mobilidade de Quadril\",\"detalhes_item\":\"Exercícios para melhorar a mobilidade do quadril\",\"tempo_execucao\":\"10 minutos\"}', 'livre'),
(20, 8, 'Caminhada Leve', NULL, NULL, NULL, '5 minutos', NULL, NULL, '{\"nome_item\":\"Caminhada Leve\",\"detalhes_item\":\"Caminhada leve para baixar a frequência cardíaca\",\"tempo_execucao\":\"5 minutos\"}', 'livre'),
(21, 9, 'Aquecimento com Caminhada', NULL, NULL, NULL, '5 minutos', NULL, NULL, '{\"nome_item\":\"Aquecimento com Caminhada\",\"detalhes_item\":\"Caminhada leve para iniciar o fluxo sanguíneo\",\"fc_alvo\":\"50-60% FCmax (90-108 bpm)\",\"tempo_execucao\":\"5 minutos\"}', 'livre'),
(22, 9, 'Corrida Leve', NULL, NULL, NULL, '20 minutos', NULL, NULL, '{\"nome_item\":\"Corrida Leve\",\"detalhes_item\":\"Corrida leve para promover recuperação ativa\",\"fc_alvo\":\"60-70% FCmax (108-126 bpm)\",\"tempo_execucao\":\"20 minutos\",\"distancia\":\"3-4 km\",\"velocidade\":\"Leve\",\"observacoes\":\"Manter ritmo confortável e respiração controlada\"}', 'livre'),
(23, 9, 'Ponte de Glúteos', NULL, 3, '15', '30s por série', 0.00, '60s', '{\"nome_item\":\"Ponte de Glúteos\",\"detalhes_item\":\"3 séries de 15 repetições\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"30s por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Peso corporal\",\"series\":\"3\",\"repeticoes\":\"15\"}', 'livre'),
(24, 9, 'Superman', NULL, 3, '12', '30s por série', 0.00, '60s', '{\"nome_item\":\"Superman\",\"detalhes_item\":\"3 séries de 12 repetições\",\"fc_alvo\":\"70-75% FCmax (126-135 bpm)\",\"tempo_execucao\":\"30s por série\",\"tempo_recuperacao\":\"60s\",\"tipo_recuperacao\":\"passivo\",\"carga\":\"Peso corporal\",\"series\":\"3\",\"repeticoes\":\"12\"}', 'livre'),
(25, 9, 'Alongamento de Core', NULL, NULL, NULL, '5 minutos', NULL, NULL, '{\"nome_item\":\"Alongamento de Core\",\"detalhes_item\":\"Alongamento suave para a região do core\",\"tempo_execucao\":\"5 minutos\"}', 'livre'),
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
(43, 14, 'Alongamento Completo', NULL, NULL, NULL, '10 minutos', NULL, NULL, '{\"nome_item\":\"Alongamento Completo\",\"detalhes_item\":\"Alongamento de todos os grupos musculares trabalhados\",\"tempo_execucao\":\"10 minutos\"}', 'livre');

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
(15, 'Eudimaci Barboza de Lira', 'eudimaci.pecim@gmail.com.br', '$2y$10$vxTxj4hW/NFQqrDzNyRgweZ9DTpilCbmAD/X3BA00AyV6aXFVWE2.', NULL, NULL, NULL, NULL, '+55 +55 92982027654', '+55 +55 92982027654', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', NULL, 'ativo', '2025-12-19 14:13:04', 'organizador', NULL, NULL),
(16, 'EUDIMACI BARBOZA DE LIRA', 'eudimaci08@yahoo.com.br', '$2y$10$3L5lLXUG6ZM.OFmkW23UBu3w9Y0M6wGlrikNwwwy1mpC/2H3pyxIe', NULL, NULL, NULL, NULL, '+55 +55 92982027654', '+55 +55 92982027654', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', NULL, 'ativo', '2025-12-19 14:31:48', 'organizador', NULL, NULL),
(17, 'Eudimaci Lira', 'eudimaci.pecim@gmail.com', '$2y$10$Tw9IWLdYflHjX18VLaockOGgrXgRmhfCCEgxZLmVbNpd5QjcGuPWy', '1970-08-31', 'CPF', '64655733420', 'Masculino', '92982027654', '92982027654', 'Avenida Efigênio Sales', '530', NULL, 'Adrianópolis', 'Manaus', 'AM', '69057050', 'Brasil', 'frontend/assets/img/perfis/perfil_17_1769462865_6977dc51ecda2.jpg', 'ativo', '2025-12-19 14:57:45', 'participante', NULL, NULL),
(18, 'Marcio dos Santos Fernandes', 'nandesinfo@gmail.com', '$2y$10$jb2DS7UDKnOHL4h/tp3foO8xW2ls9cff0M7wAaW.07hH67hBybMdu', '1971-02-03', 'CPF', '00571996710', 'Masculino', '21971545687', '21971545687', 'Rua Mediterrâneo', '102', 'Apto 301', 'Córrego Grande', 'Florianópolis', 'SC', '88037610', 'Brasil', 'frontend/assets/img/perfis/perfil_18_1769177969_69738371a5e25.jpg', 'ativo', '2025-12-19 17:08:25', 'participante', NULL, NULL),
(19, 'Clóvis Augusto Pantoja', 'pancap@gmail.com', '$2y$10$tCzDcxn2a5vKdd8Bnk4kpOkVqtS.4EwCkuDnMRGuyvRXt.GXSjlje', '1979-03-10', NULL, NULL, 'Masculino', '92991499040', '92991499040', 'Rua Wagner', '122', NULL, 'da Paz', 'Manaus', 'AM', '69048000', 'Brasil', 'frontend/assets/img/perfis/perfil_19_1766183903_6945d3df28bf3.jpg', 'ativo', '2025-12-19 22:34:05', 'participante', NULL, NULL),
(20, 'Sandoval Bezerra', 'sandoval.bezerra@gmail.com', '$2y$10$3L5lLXUG6ZM.OFmkW23UBu3w9Y0M6wGlrikNwwwy1mpC/2H3pyxIe', NULL, NULL, '77042050487', NULL, NULL, NULL, 'Rua Ceará', '145', NULL, 'Chapada', 'Manaus', 'AM', '69050050', NULL, NULL, 'ativo', '2025-12-23 14:31:46', 'participante', NULL, NULL),
(21, 'LUANA SOUZA MODESTO', 'lumodesto81@gmail.com', '$2y$10$HtvINyapvymTpTHdRnFxJ.DdqEsl2a9DXbftND4No5iNrdfawRhAq', NULL, NULL, '01820351238', NULL, NULL, NULL, 'Avenida Ephigênio Salles', '530', NULL, 'Adrianópolis', 'Manaus', 'AM', '69057050', NULL, NULL, 'ativo', '2025-12-23 22:28:49', 'participante', NULL, NULL),
(22, 'Bruno Rafaga', 'sandovalwizard@outlook.com', '$2y$10$/l4FKCwDsinmu2ckFShsVuJ71.GdGixE8N2tAO.D8dOJ0T2praflW', '1969-08-28', 'CPF', '17171936414', 'Masculino', '92981151287', '8196858574', 'Leonardo Malcher', '854', NULL, 'centro', 'Manaus', 'AM', '69010170', 'Brasil', 'frontend/assets/img/perfis/perfil_22_1769110295_69727b17ef011.jpg', 'ativo', '2025-12-23 23:20:41', 'participante', NULL, NULL),
(23, 'YANNE PACHECO BARBOZA DE LIRA', 'yannepachecob@gmail.com', '$2y$10$b/9zb4IF5lUN3uEwb55hKOiN.zEMtDblIvqxAPNt1kWkQHDAJrUpW', NULL, NULL, '14048969455', NULL, NULL, NULL, 'Avenida Esperança', '872', NULL, 'Manaíra', 'João Pessoa', 'PB', '58038281', NULL, NULL, 'ativo', '2025-12-31 10:57:39', 'participante', NULL, NULL),
(24, 'Helton Jefferson Damasceno Perez', 'notleh.perez144@gmail.com', '$2y$10$PbxHtsu92bPTXxbS95uajedU10x6DtltA4u/wLL3bDr4M9tKV3UrS', NULL, 'CPF', '51467798215', NULL, NULL, NULL, 'Avenida Borba', '1229', 'A', 'Cachoeirinha', 'Manaus', 'AM', '69065030', NULL, NULL, 'ativo', '2026-01-26 21:57:33', 'participante', NULL, NULL),
(25, 'Manoel da Pereira Pinto', 'manoel@gmail.com', '$2y$10$COmNgt18R3ivwDOQxPaCQeOPkiAK.NoKMT8CrKIpVMR5AyugXX7Rm', NULL, 'CPF', '00307850224', NULL, NULL, NULL, 'Rua Henrique Martins', '120', 'Apto 13', 'Centro', 'Manaus', 'AM', '69010010', NULL, NULL, 'ativo', '2026-01-30 18:10:54', 'participante', NULL, NULL),
(26, 'Léo Naval', 'leonardolimanaval@gmail.com', '$2y$10$YD0fuCA5XgHIrnk0mjQlEuhCIyMZc4UMrJ.tFFW6exSxla6wDmh4y', NULL, 'CPF', '00544941250', NULL, NULL, NULL, 'Beco José de Arimatéia', '15', NULL, 'Petrópolis', 'Manaus', 'AM', '69067090', NULL, NULL, 'ativo', '2026-01-30 18:13:27', 'participante', NULL, NULL);

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
(2, 'Administrador', 'admin@movamazon.com.br', '$2y$10$fHzwjvZo/6tLBFDIWc0ziOkU4pA3.wZYLOH4MTlS5Ih.2FjoLMzoK', 'ativo', '2025-11-25 18:10:01', '2026-01-31 02:10:37', NULL, NULL);

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
  ADD KEY `idx_planos_inscricao` (`inscricao_id`);

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
  ADD KEY `idx_glicemia_monitoring` (`glicemia_pre_treino`,`glicemia_pos_treino`);

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
  ADD PRIMARY KEY (`id`);

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
  ADD KEY `idx_treinos_semana_numero` (`semana_numero`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `banners`
--
ALTER TABLE `banners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `camisas`
--
ALTER TABLE `camisas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `cashback_atletas`
--
ALTER TABLE `cashback_atletas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de tabela `config`
--
ALTER TABLE `config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de tabela `config_historico`
--
ALTER TABLE `config_historico`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `cupons_remessa`
--
ALTER TABLE `cupons_remessa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `formas_pagamento_evento`
--
ALTER TABLE `formas_pagamento_evento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `inscricoes`
--
ALTER TABLE `inscricoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de tabela `inscricoes_cupons`
--
ALTER TABLE `inscricoes_cupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `inscricoes_produtos_extras`
--
ALTER TABLE `inscricoes_produtos_extras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de tabela `kits_eventos`
--
ALTER TABLE `kits_eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de tabela `kit_produtos`
--
ALTER TABLE `kit_produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT de tabela `kit_templates`
--
ALTER TABLE `kit_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `kit_template_produtos`
--
ALTER TABLE `kit_template_produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT de tabela `leads_organizadores`
--
ALTER TABLE `leads_organizadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `logs_admin`
--
ALTER TABLE `logs_admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT de tabela `logs_inscricoes_pagamentos`
--
ALTER TABLE `logs_inscricoes_pagamentos`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=355;

--
-- AUTO_INCREMENT de tabela `logs_sincronizacao_mp`
--
ALTER TABLE `logs_sincronizacao_mp`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `lotes_inscricao`
--
ALTER TABLE `lotes_inscricao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de tabela `modalidades`
--
ALTER TABLE `modalidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de tabela `openai_token_usage`
--
ALTER TABLE `openai_token_usage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `organizadores`
--
ALTER TABLE `organizadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `pagamentos`
--
ALTER TABLE `pagamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pagamentos_ml`
--
ALTER TABLE `pagamentos_ml`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `papeis`
--
ALTER TABLE `papeis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT de tabela `produtos_extras`
--
ALTER TABLE `produtos_extras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `produtos_extras_modalidade`
--
ALTER TABLE `produtos_extras_modalidade`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `produto_extra_produtos`
--
ALTER TABLE `produto_extra_produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `programacao_evento`
--
ALTER TABLE `programacao_evento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `progresso_treino`
--
ALTER TABLE `progresso_treino`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `questionario_evento`
--
ALTER TABLE `questionario_evento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de tabela `questionario_evento_modalidade`
--
ALTER TABLE `questionario_evento_modalidade`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `solicitacoes_evento`
--
ALTER TABLE `solicitacoes_evento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `treino_exercicios`
--
ALTER TABLE `treino_exercicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de tabela `usuario_admin`
--
ALTER TABLE `usuario_admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `categorias`
--
ALTER TABLE `categorias`
  ADD CONSTRAINT `fk_categorias_evento` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `config_historico`
--
ALTER TABLE `config_historico`
  ADD CONSTRAINT `fk_config_historico_admin` FOREIGN KEY (`alterado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_config_historico_config` FOREIGN KEY (`config_id`) REFERENCES `config` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `eventos`
--
ALTER TABLE `eventos`
  ADD CONSTRAINT `fk_evento_deleted_by` FOREIGN KEY (`deleted_by`) REFERENCES `usuarios` (`id`);

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
-- Restrições para tabelas `produto_extra_produtos`
--
ALTER TABLE `produto_extra_produtos`
  ADD CONSTRAINT `fk_produto_extra_produtos_extra` FOREIGN KEY (`produto_extra_id`) REFERENCES `produtos_extras` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_produto_extra_produtos_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `questionario_evento_modalidade`
--
ALTER TABLE `questionario_evento_modalidade`
  ADD CONSTRAINT `fk_qe_modalidade_modalidade` FOREIGN KEY (`modalidade_id`) REFERENCES `modalidades` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_qe_modalidade_pergunta` FOREIGN KEY (`questionario_evento_id`) REFERENCES `questionario_evento` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
