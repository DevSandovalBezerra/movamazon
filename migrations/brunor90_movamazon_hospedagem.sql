-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 19/11/2025 às 15:50
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

DELIMITER $$
--
-- Funções
--
DROP FUNCTION IF EXISTS `get_lote_ativo`$$
CREATE FUNCTION `get_lote_ativo` (`modalidade_id` INT, `tipo_publico` VARCHAR(20)) RETURNS INT DETERMINISTIC READS SQL DATA BEGIN
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

DROP TABLE IF EXISTS `aceites_termos`;
CREATE TABLE IF NOT EXISTS `aceites_termos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `inscricao_id` int NOT NULL,
  `termos_id` int NOT NULL,
  `aceito` tinyint(1) DEFAULT '0',
  `data_aceite` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_usuario` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `inscricao_id` (`inscricao_id`),
  KEY `termos_id` (`termos_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `anamneses`
--

DROP TABLE IF EXISTS `anamneses`;
CREATE TABLE IF NOT EXISTS `anamneses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `inscricao_id` int DEFAULT NULL COMMENT 'Vinculação com inscrição na corrida',
  `profissional_id` int DEFAULT NULL,
  `data_anamnese` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `peso` decimal(5,2) NOT NULL,
  `altura` int NOT NULL,
  `imc` decimal(4,2) DEFAULT NULL,
  `fc_maxima` smallint DEFAULT NULL COMMENT 'Frequência Cardíaca Máxima (bpm)',
  `vo2_max` decimal(5,2) DEFAULT NULL COMMENT 'VO2 Máximo (ml/kg/min)',
  `zona_alvo_treino` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Zona Alvo de Treinamento (descritivo ou faixa)',
  `doencas_preexistentes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `uso_medicamentos` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `nivel_atividade` enum('ativo','inativo') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `objetivo_principal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foco_primario` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_glicemia` int DEFAULT NULL COMMENT 'Glicemia máxima (mg/dL) - opcional para anamnese simplificada',
  `limitacoes_fisicas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `preferencias_atividades` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `disponibilidade_horarios` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `historico_corridas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Histórico de corridas do participante',
  `assinatura_aluno` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assinatura_responsavel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pendente','em_analise','aprovada','arquivada') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pendente',
  PRIMARY KEY (`id`),
  KEY `idx_anamneses_usuario` (`usuario_id`),
  KEY `idx_anamneses_profissional` (`profissional_id`),
  KEY `idx_anamneses_inscricao` (`inscricao_id`),
  KEY `idx_usuario` (`usuario_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `anamneses`
--

INSERT INTO `anamneses` (`id`, `usuario_id`, `inscricao_id`, `profissional_id`, `data_anamnese`, `peso`, `altura`, `imc`, `fc_maxima`, `vo2_max`, `zona_alvo_treino`, `doencas_preexistentes`, `uso_medicamentos`, `nivel_atividade`, `objetivo_principal`, `foco_primario`, `max_glicemia`, `limitacoes_fisicas`, `preferencias_atividades`, `disponibilidade_horarios`, `historico_corridas`, `assinatura_aluno`, `assinatura_responsavel`, `status`) VALUES
(1, 7, NULL, NULL, '2025-11-17 19:22:52', 68.00, 174, 22.46, NULL, NULL, NULL, 'nenhuma', 'suplementos vitamínicos', 'ativo', 'condicionamento', NULL, NULL, NULL, 'corro toda semana', 'todas as tardes', 'corro toda semana', NULL, NULL, 'pendente');

-- --------------------------------------------------------

--
-- Estrutura para tabela `camisas`
--

DROP TABLE IF EXISTS `camisas`;
CREATE TABLE IF NOT EXISTS `camisas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `evento_id` int NOT NULL,
  `produto_id` int DEFAULT NULL,
  `tamanho` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantidade_inicial` int NOT NULL DEFAULT '0',
  `quantidade_vendida` int NOT NULL DEFAULT '0',
  `quantidade_disponivel` int NOT NULL DEFAULT '0',
  `quantidade_reservada` int NOT NULL DEFAULT '0',
  `ativo` tinyint(1) DEFAULT '1',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `evento_tamanho` (`evento_id`,`tamanho`),
  KEY `evento_id` (`evento_id`),
  KEY `idx_tamanhos_evento_produto` (`evento_id`,`produto_id`),
  KEY `idx_tamanhos_produto` (`produto_id`),
  KEY `idx_tamanhos_ativo` (`ativo`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `camisas`
--

INSERT INTO `camisas` (`id`, `evento_id`, `produto_id`, `tamanho`, `quantidade_inicial`, `quantidade_vendida`, `quantidade_disponivel`, `quantidade_reservada`, `ativo`, `data_criacao`) VALUES
(1, 2, 1, 'PP', 50, 0, 50, 0, 1, '2025-07-17 05:18:18'),
(2, 2, 1, 'P', 294, 0, 294, 0, 1, '2025-07-17 05:18:18'),
(3, 2, 1, 'M', 386, 0, 386, 0, 1, '2025-07-17 05:18:18'),
(4, 2, 1, 'G', 192, 0, 192, 0, 1, '2025-07-17 05:18:18'),
(5, 2, 1, 'GG', 78, 0, 78, 0, 1, '2025-07-17 05:18:18'),
(6, 6, NULL, 'M', 100, 0, 100, 0, 1, '2025-10-08 21:28:38');

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias`
--

DROP TABLE IF EXISTS `categorias`;
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `evento_id` int NOT NULL,
  `nome` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `tipo_publico` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Publico Geral',
  `idade_min` int DEFAULT '0',
  `idade_max` int DEFAULT '100',
  `desconto_idoso` tinyint(1) DEFAULT '0',
  `exibir_inscricao_geral` tinyint(1) DEFAULT '1',
  `exibir_inscricao_grupos` tinyint(1) DEFAULT '1',
  `titulo_link_oculto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_evento_nome` (`evento_id`,`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `categorias`
--

INSERT INTO `categorias` (`id`, `evento_id`, `nome`, `descricao`, `tipo_publico`, `idade_min`, `idade_max`, `desconto_idoso`, `exibir_inscricao_geral`, `exibir_inscricao_grupos`, `titulo_link_oculto`, `ativo`, `data_criacao`, `updated_at`) VALUES
(1, 2, 'Comunidade Acadêmica - UEA, UFAM, IFAM e Universidades Privadas', 'Categoria para estudantes e funcionários de instituições de ensino', 'comunidade_academica', 17, 100, 1, 1, 1, NULL, 1, '2025-08-25 22:11:01', '2025-09-23 01:03:15'),
(2, 2, 'Público Geral', 'Categoria para público em geral', 'publico_geral', 17, 100, 1, 1, 1, NULL, 1, '2025-08-25 22:11:01', '2025-09-23 01:03:15'),
(4, 2, 'Público Geral - Corrida e Caminhada em Família 1', 'Categoria familiar para público geral', 'publico_geral', 0, 100, 1, 1, 1, NULL, 1, '2025-08-25 22:11:01', '2025-09-23 01:03:15'),
(12, 3, 'Publico Geral', '', 'Publico geral', 18, 65, 1, 1, 1, NULL, 1, '2025-09-23 01:35:05', '2025-09-23 01:35:05');

-- --------------------------------------------------------

--
-- Estrutura para tabela `cupons_remessa`
--

DROP TABLE IF EXISTS `cupons_remessa`;
CREATE TABLE IF NOT EXISTS `cupons_remessa` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_remessa` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor_desconto` decimal(10,2) NOT NULL,
  `tipo_valor` enum('percentual','valor_real','preco_fixo') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_desconto` enum('web','mobile','ambos') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ambos',
  `max_uso` int NOT NULL DEFAULT '1',
  `usos_atuais` int DEFAULT '0',
  `habilita_desconto_itens` tinyint(1) DEFAULT '0',
  `data_criacao` datetime DEFAULT CURRENT_TIMESTAMP,
  `data_inicio` date NOT NULL,
  `data_validade` date NOT NULL,
  `status` enum('ativo','cancelado') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ativo',
  `evento_id` int DEFAULT NULL,
  `aplicavel_modalidades` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `aplicavel_categorias` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo_remessa` (`codigo_remessa`),
  KEY `idx_evento` (`evento_id`),
  KEY `idx_status` (`status`),
  KEY `idx_data_inicio` (`data_inicio`),
  KEY `idx_data_validade` (`data_validade`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `cupons_remessa`
--

INSERT INTO `cupons_remessa` (`id`, `titulo`, `codigo_remessa`, `valor_desconto`, `tipo_valor`, `tipo_desconto`, `max_uso`, `usos_atuais`, `habilita_desconto_itens`, `data_criacao`, `data_inicio`, `data_validade`, `status`, `evento_id`, `aplicavel_modalidades`, `aplicavel_categorias`) VALUES
(1, 'Convidado Especial', 'CONV-ESP-100', 100.00, 'percentual', 'ambos', 1, 0, 0, '2025-07-22 18:56:28', '2025-07-22', '2025-08-21', 'ativo', 2, NULL, NULL),
(2, 'Reitoria_UEA', 'REITORIA-UEA-50', 50.00, 'percentual', 'ambos', 15, 0, 0, '2025-07-22 18:56:28', '2025-07-22', '2025-08-21', 'ativo', 2, NULL, NULL),
(3, 'DEV_Sistema', 'XIGK-RKH3', 50.00, 'percentual', 'ambos', 5, 0, 0, '2025-07-22 21:24:45', '2025-07-25', '2025-07-31', 'ativo', 3, NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `enderecos_entrega`
--

DROP TABLE IF EXISTS `enderecos_entrega`;
CREATE TABLE IF NOT EXISTS `enderecos_entrega` (
  `id` int NOT NULL AUTO_INCREMENT,
  `inscricao_id` int NOT NULL,
  `cep` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `endereco` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `complemento` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bairro` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cidade` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uf` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pais` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inscricao_id` (`inscricao_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `estoque_kits`
--

DROP TABLE IF EXISTS `estoque_kits`;
CREATE TABLE IF NOT EXISTS `estoque_kits` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kit_id` int NOT NULL,
  `quantidade_inicial` int NOT NULL,
  `quantidade_vendida` int DEFAULT '0',
  `quantidade_disponivel` int NOT NULL,
  `data_atualizacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kit_estoque` (`kit_id`),
  KEY `kit_id` (`kit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `estoque_produtos_extras`
--

DROP TABLE IF EXISTS `estoque_produtos_extras`;
CREATE TABLE IF NOT EXISTS `estoque_produtos_extras` (
  `id` int NOT NULL AUTO_INCREMENT,
  `produto_extra_id` int NOT NULL,
  `quantidade_inicial` int NOT NULL,
  `quantidade_vendida` int DEFAULT '0',
  `quantidade_disponivel` int NOT NULL,
  `data_atualizacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `produto_estoque` (`produto_extra_id`),
  KEY `produto_extra_id` (`produto_extra_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `estoque_produtos_extras`
--

INSERT INTO `estoque_produtos_extras` (`id`, `produto_extra_id`, `quantidade_inicial`, `quantidade_vendida`, `quantidade_disponivel`, `data_atualizacao`) VALUES
(1, 1, 60, 0, 60, '2025-07-19 19:26:54'),
(2, 2, 140, 0, 140, '2025-07-19 19:26:54');

-- --------------------------------------------------------

--
-- Estrutura para tabela `eventos`
--

DROP TABLE IF EXISTS `eventos`;
CREATE TABLE IF NOT EXISTS `eventos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `data_inicio` date DEFAULT NULL,
  `data_fim` date DEFAULT NULL,
  `categoria` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `genero` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `local` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `hora_inicio` time DEFAULT NULL,
  `data_realizacao` date DEFAULT NULL,
  `imagem` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` int DEFAULT NULL,
  `delete_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `organizador_id` (`organizador_id`),
  KEY `idx_eventos_data_realizacao` (`data_realizacao`),
  KEY `idx_deleted_at` (`deleted_at`),
  KEY `fk_evento_deleted_by` (`deleted_by`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `eventos`
--

INSERT INTO `eventos` (`id`, `nome`, `descricao`, `data_inicio`, `data_fim`, `categoria`, `genero`, `local`, `cep`, `url_mapa`, `logradouro`, `numero`, `cidade`, `estado`, `pais`, `regulamento`, `status`, `organizador_id`, `taxa_setup`, `percentual_repasse`, `exibir_retirada_kit`, `taxa_gratuitas`, `taxa_pagas`, `limite_vagas`, `data_fim_inscricoes`, `hora_fim_inscricoes`, `data_criacao`, `hora_inicio`, `data_realizacao`, `imagem`, `deleted_at`, `deleted_by`, `delete_reason`) VALUES
(2, 'III CORRIDA SAUIM DE COLEIRA EM 2025', 'A III Corrida do Sauim-de-Coleira se caracteriza por um evento esportivo com três dimensões, integrando, assim três pilares importantes para toda sociedade: a saúde, a educação e o meio ambiente. O evento tem um caráter comemorativo e informativo, uma vez que nos faz lembrar do forte alerta e apelo para uma espécie endêmico da região e criticamente ameaçado, o Saguinus bicolor, popularmente conhecido como sauim-de-coleira.', '2025-11-15', '2025-12-20', 'corrida_rua', 'misto', 'Parque do Mindu - Manaus/AM', '69050-020', 'https://maps.google.com/maps?q=Parque+do+Mindu+Manaus', 'Av. Djalma Batista', '1200', 'Manaus', 'AM', 'Brasil', 'REGULAMENTO DA III CORRIDA SAUIM DE COLEIRA EM 2025\r\n\r\nINFORMAÇÕES GERAIS\r\nDATA: Domingo, 16 de julho de 2025\r\nLOCAL: Parque do Mindu - Manaus/AM\r\nHORÁRIOS DA LARGADA: 07:00\r\n\r\nDISTÂNCIAS: 5km e 10km\r\n\r\nINSCRIÇÕES\r\nPERÍODO: 01 de junho a 15 de julho de 2025\r\nTAXA DE INSCRIÇÃO: \r\n- Comunidade acadêmica: R$ 105,00\r\n- Público em geral: R$ 145,00\r\n\r\nKIT DO ATLETA\r\n- Camiseta oficial\r\n- Número de peito\r\n- Medalha de participação\r\n- Sacochila\r\n- Viseira\r\n\r\nPREMIAÇÃO\r\nTodos os atletas que terminarem a prova receberão medalha de participação.\r\n\r\nREGRAS GERAIS\r\n- Idade mínima: 14 anos para 5km e 17 anos para 10km\r\n- Uso obrigatório do chip de cronometragem\r\n- Não será permitido auxílio externo\r\n- Recomenda-se avaliação médica prévia', 'ativo', 2, 129.90, 85.00, 1, 2.50, 3.00, 1100, '2025-12-15', '23:59:00', '2025-11-16 15:03:02', '07:00:00', '2025-12-20', 'evento_2.png', NULL, NULL, NULL),
(3, 'III CORRIDA MICO LEAO', 'A III CORRIDA MICO LEAO se caracteriza por um evento esportivo com três dimensões, integrando, assim três pilares importantes para toda sociedade: a saúde, a educação e o meio ambiente. O evento tem um caráter comemorativo e informativo, uma vez que nos faz lembrar do forte alerta e apelo para uma espécie endêmico da região e criticamente ameaçado, o Saguinus bicolor, popularmente conhecido como sauim-de-coleira. Na ocasião de sua organização e execução o evento pretende divulgar os trabalhos desenvolvidos com esta espécie dentro do aspecto conservacionista, bem como mostrar que existem diferentes formas de ajudar neste processo de conservação, evitando a extinção dessa espécie tão valiosa para toda a sociedade. Paralelo a isso, o evento também proporcionará um momento de lazer para toda sociedade que busca a saúde e a qualidade de vida, por meio da realização de atividade física.', '2025-07-16', NULL, '', '', 'Manaus/AM', '', '', '', '', 'Recife', 'PE', 'Brasil', '', 'rascunho', 2, 129.90, NULL, 0, 2.50, NULL, 1100, NULL, NULL, '2025-07-16 15:03:02', '07:00:00', NULL, 'evento_3.png', NULL, NULL, NULL),
(5, 'Corrida do macaco', 'uma macacada de corrida sem sentido de direção, mas que pode ser bastante divertida', '2025-09-25', '2025-11-15', 'corrida_rua', '', 'Parque 10 de Novembro', '', '', '', '', 'Manaus', 'AM', 'Brasil', 'o projeto deve ser um sistema que apos preencher o seus dados de cadastro o dono de um posto de combustíveis possa, gerar as paginas de seus LMC diários, o fluxo do sistema será assim, ao informar a capacidade de seus tanque a quantidade recebida de combustível na data inicial (data de recebimento ) e a data final, o sistema vai distribuir automaticamente com base em media diárias de vendas as saídas e preencher os LMC diários, o algoritmo de distribuição aleatória da venda não deve ser valores fixos, não trata-se de dividir 10000l / 15 dias, trata-se de simular vendas com quantidades próximas a media de modo a parecer estar preenchendo verdadeiramente as quantidades vendidas que de forma alguma seriam igual para cada dias, o importante é que ao final do período a distribuição dos 10000 litros ocorra no período vendido, no exemplo 15 dias', 'ativo', 2, 85.00, NULL, 0, NULL, NULL, 1000, NULL, NULL, '2025-09-21 21:22:26', '08:00:00', NULL, 'evento_5.jpg', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `eventos_ativos`
-- (Veja abaixo para a visão atual)
--
DROP VIEW IF EXISTS `eventos_ativos`;
CREATE TABLE IF NOT EXISTS `eventos_ativos` (
`id` int
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
,`organizador_id` int
,`taxa_setup` decimal(10,2)
,`percentual_repasse` decimal(5,2)
,`exibir_retirada_kit` tinyint(1)
,`taxa_gratuitas` decimal(10,2)
,`taxa_pagas` decimal(10,2)
,`limite_vagas` int
,`data_fim_inscricoes` date
,`hora_fim_inscricoes` time
,`data_criacao` timestamp
,`hora_inicio` time
,`data_realizacao` date
,`imagem` varchar(500)
,`deleted_at` timestamp
,`deleted_by` int
,`delete_reason` text
);

-- --------------------------------------------------------

--
-- Estrutura para tabela `formas_pagamento_evento`
--

DROP TABLE IF EXISTS `formas_pagamento_evento`;
CREATE TABLE IF NOT EXISTS `formas_pagamento_evento` (
  `id` int NOT NULL AUTO_INCREMENT,
  `evento_id` int NOT NULL,
  `tipo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `detalhes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parcelamento` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `observacoes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `evento_id` (`evento_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

DROP TABLE IF EXISTS `inscricoes`;
CREATE TABLE IF NOT EXISTS `inscricoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `evento_id` int NOT NULL,
  `modalidade_evento_id` int NOT NULL,
  `lote_inscricao_id` int DEFAULT NULL,
  `tipo_publico` enum('comunidade_academica','publico_geral') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kit_modalidade_id` int DEFAULT NULL,
  `kit_id` int DEFAULT NULL,
  `tamanho_camiseta` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tamanho_id` int DEFAULT NULL,
  `produtos_extras_ids` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `numero_inscricao` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `protocolo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grupo_assessoria` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nome_equipe` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ordem_equipe` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `posicao_legenda` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `escolha_tamanho` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fisicamente_apto` tinyint(1) DEFAULT NULL,
  `apelido_peito` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contato_emergencia_nome` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contato_emergencia_telefone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `equipe_extra` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doc_comprovante_universidade` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `data_inscricao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pendente','confirmada','cancelada') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pendente',
  `status_pagamento` enum('pendente','pago','cancelado','rejeitado','processando') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pendente',
  `valor_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `valor_desconto` decimal(10,2) DEFAULT '0.00',
  `cupom_aplicado` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_pagamento` datetime DEFAULT NULL,
  `forma_pagamento` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parcelas` int DEFAULT '1',
  `seguro_contratado` tinyint(1) DEFAULT '0',
  `external_reference` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preference_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `colocacao` int DEFAULT NULL,
  `aceite_termos` tinyint(1) DEFAULT '0',
  `data_aceite_termos` timestamp NULL DEFAULT NULL,
  `versao_termos` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_usuario_evento` (`usuario_id`,`evento_id`),
  KEY `evento_id` (`evento_id`),
  KEY `modalidade_evento_id` (`modalidade_evento_id`),
  KEY `idx_status_pagamento` (`status_pagamento`),
  KEY `idx_data_pagamento` (`data_pagamento`),
  KEY `idx_external_reference` (`external_reference`),
  KEY `idx_preference_id` (`preference_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `inscricoes`
--

INSERT INTO `inscricoes` (`id`, `usuario_id`, `evento_id`, `modalidade_evento_id`, `lote_inscricao_id`, `tipo_publico`, `kit_modalidade_id`, `kit_id`, `tamanho_camiseta`, `tamanho_id`, `produtos_extras_ids`, `numero_inscricao`, `protocolo`, `grupo_assessoria`, `nome_equipe`, `ordem_equipe`, `posicao_legenda`, `escolha_tamanho`, `fisicamente_apto`, `apelido_peito`, `contato_emergencia_nome`, `contato_emergencia_telefone`, `equipe_extra`, `doc_comprovante_universidade`, `data_inscricao`, `status`, `status_pagamento`, `valor_total`, `valor_desconto`, `cupom_aplicado`, `data_pagamento`, `forma_pagamento`, `parcelas`, `seguro_contratado`, `external_reference`, `preference_id`, `colocacao`, `aceite_termos`, `data_aceite_termos`, `versao_termos`) VALUES
(1, 4, 2, 3, NULL, NULL, NULL, NULL, 'M', NULL, '[{\"id\":2,\"nome\":\"Kit Camisa + Medalha\",\"valor\":20},{\"id\":3,\"nome\":\"Combo VIP\",\"valor\":70}]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-14 01:27:49', 'pendente', 'pendente', 195.00, 0.00, NULL, NULL, NULL, 1, 0, 'MOVAMAZONAS_1', '24368125-e6a55b5d-e6fd-4893-ab51-a203a02eacc3', NULL, 0, NULL, NULL),
(2, 5, 2, 5, NULL, NULL, NULL, NULL, 'M', NULL, '[{\"id\":3,\"nome\":\"Combo VIP\",\"valor\":70},{\"id\":2,\"nome\":\"Kit Camisa + Medalha\",\"valor\":20}]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-06 20:52:11', 'pendente', 'pendente', 205.00, 0.00, NULL, NULL, NULL, 1, 0, 'MOVAMAZONAS_2', '24368125-7442697a-af53-41dc-9d79-052a8c27f56e', NULL, 0, NULL, NULL),
(4, 7, 2, 3, NULL, NULL, NULL, NULL, 'M', NULL, '[{\"id\":3,\"nome\":\"Combo VIP\",\"valor\":70},{\"id\":2,\"nome\":\"Kit Camisa + Medalha\",\"valor\":20}]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-17 18:31:48', 'confirmada', 'pago', 195.00, 0.00, NULL, '2025-11-17 15:10:06', 'mercadolivre', 1, 0, 'MOVAMAZONAS_4', '24368125-ff2f77ae-4580-4521-ac28-447976817632', NULL, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `inscricoes_cupons`
--

DROP TABLE IF EXISTS `inscricoes_cupons`;
CREATE TABLE IF NOT EXISTS `inscricoes_cupons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `inscricao_id` int NOT NULL,
  `cupom_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `inscricao_id` (`inscricao_id`),
  KEY `cupom_id` (`cupom_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `inscricoes_produtos_extras`
--

DROP TABLE IF EXISTS `inscricoes_produtos_extras`;
CREATE TABLE IF NOT EXISTS `inscricoes_produtos_extras` (
  `id` int NOT NULL AUTO_INCREMENT,
  `inscricao_id` int NOT NULL,
  `produto_extra_evento_id` int NOT NULL,
  `quantidade` int DEFAULT '1',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pendente',
  `data_compra` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `inscricao_id` (`inscricao_id`),
  KEY `produto_extra_evento_id` (`produto_extra_evento_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `inscricoes_produtos_extras`
--

INSERT INTO `inscricoes_produtos_extras` (`id`, `inscricao_id`, `produto_extra_evento_id`, `quantidade`, `status`, `data_compra`) VALUES
(9, 2, 3, 1, 'pendente', '2025-11-06 16:52:11'),
(10, 2, 2, 1, 'pendente', '2025-11-06 16:52:11'),
(11, 4, 3, 1, 'pendente', '2025-11-17 14:31:48'),
(12, 4, 2, 1, 'pendente', '2025-11-17 14:31:48');

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
(7, 'Kit Atleta - CORRIDA 10KM', 'Kit Atleta aplicado em CORRIDA 10KM', 3, 9, 1, 149.50, 'kit_template_Kit Atleta.png', 1, 149.50, 1, '2025-07-26 20:46:00', '2025-09-03 17:29:15'),
(8, 'Kit Atleta - CORRIDA 5KM', 'Kit Atleta aplicado em CORRIDA 5KM', 2, 11, 1, 149.50, 'kit_template_Kit Atleta.png', 1, 149.50, 0, '2025-07-26 20:46:00', '2025-11-04 19:35:52'),
(9, 'Kit Promocional - CORRIDA 5KM', 'Kit Promocional aplicado em CORRIDA 5KM | KIT COMPLETO - FAMÍLIA 1', 0, 0, 0, 80.00, 'kit_template_Kit Promocional.png', 1, 80.00, 0, '2025-09-03 16:41:42', '2025-09-04 21:58:51'),
(10, 'KIT Famila - CORRIDA 5KM ', 'KIT Famila aplicado em CORRIDA 5KM ', 2, 5, 3, 32.50, 'kit_template_KIT Famila.png', 1, 32.50, 0, '2025-09-03 17:48:53', '2025-10-08 03:39:13');

-- --------------------------------------------------------

--
-- Estrutura para tabela `kit_modalidade_evento`
--

DROP TABLE IF EXISTS `kit_modalidade_evento`;
CREATE TABLE IF NOT EXISTS `kit_modalidade_evento` (
  `kit_id` int NOT NULL,
  `modalidade_evento_id` int NOT NULL,
  PRIMARY KEY (`kit_id`,`modalidade_evento_id`),
  KEY `modalidade_evento_id` (`modalidade_evento_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `kit_produtos`
--

DROP TABLE IF EXISTS `kit_produtos`;
CREATE TABLE IF NOT EXISTS `kit_produtos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kit_id` int NOT NULL,
  `produto_id` int NOT NULL,
  `quantidade` int NOT NULL DEFAULT '1',
  `ordem` int NOT NULL DEFAULT '0',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `kit_id` (`kit_id`),
  KEY `produto_id` (`produto_id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `kit_produtos`
--

INSERT INTO `kit_produtos` (`id`, `kit_id`, `produto_id`, `quantidade`, `ordem`, `ativo`, `updated_at`) VALUES
(1, 3, 6, 1, 1, 0, '2025-07-26 20:16:09'),
(3, 5, 6, 1, 1, 0, '2025-10-08 03:28:11'),
(4, 5, 1, 1, 2, 0, '2025-10-08 03:28:11'),
(5, 5, 2, 1, 3, 0, '2025-10-08 03:28:11'),
(6, 5, 4, 1, 4, 0, '2025-10-08 03:28:11'),
(7, 5, 3, 1, 5, 0, '2025-10-08 03:28:11'),
(8, 5, 9, 1, 6, 0, '2025-10-08 03:28:11'),
(15, 7, 6, 1, 1, 1, '0000-00-00 00:00:00'),
(16, 7, 1, 1, 2, 1, '0000-00-00 00:00:00'),
(17, 7, 2, 1, 3, 1, '0000-00-00 00:00:00'),
(18, 7, 4, 1, 4, 1, '0000-00-00 00:00:00'),
(19, 7, 3, 1, 5, 1, '0000-00-00 00:00:00'),
(20, 7, 9, 1, 6, 1, '0000-00-00 00:00:00'),
(21, 8, 6, 1, 1, 0, '2025-11-04 19:35:52'),
(22, 8, 1, 1, 2, 0, '2025-11-04 19:35:52'),
(23, 8, 2, 1, 3, 0, '2025-11-04 19:35:52'),
(24, 8, 4, 1, 4, 0, '2025-11-04 19:35:52'),
(25, 8, 3, 1, 5, 0, '2025-11-04 19:35:52'),
(26, 8, 9, 1, 6, 0, '2025-11-04 19:35:52'),
(27, 9, 6, 1, 1, 0, '2025-09-03 17:27:37'),
(28, 10, 1, 1, 1, 0, '2025-10-08 03:39:13');

-- --------------------------------------------------------

--
-- Estrutura para tabela `kit_templates`
--

DROP TABLE IF EXISTS `kit_templates`;
CREATE TABLE IF NOT EXISTS `kit_templates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `preco_base` decimal(10,2) DEFAULT '0.00',
  `foto_kit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disponivel_venda` tinyint(1) DEFAULT '1',
  `ativo` tinyint(1) DEFAULT '1',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `kit_templates`
--

INSERT INTO `kit_templates` (`id`, `nome`, `descricao`, `preco_base`, `foto_kit`, `disponivel_venda`, `ativo`, `data_criacao`, `updated_at`) VALUES
(1, 'Kit Atleta', 'Kit completo com camisa, viseira, boné, medalha, troféu, garrafa, número, selo, número de peito e chip', 149.50, 'kit_template_Kit Atleta.png', 1, 1, '2025-07-19 23:00:52', '2025-10-10 17:31:17'),
(2, 'Kit Promocional', 'Kit promocional com camisa, medalha, número de peito e chip', 80.00, 'kit_template_Kit Promocional.png', 1, 1, '2025-07-19 23:00:52', '2025-10-10 17:31:17'),
(3, 'KIT Famila', 'camisa com tecido especial para sudorese', 32.50, 'kit_template_KIT Famila.png', 1, 1, '2025-09-02 19:16:47', '2025-10-10 17:31:17');

-- --------------------------------------------------------

--
-- Estrutura para tabela `kit_template_produtos`
--

DROP TABLE IF EXISTS `kit_template_produtos`;
CREATE TABLE IF NOT EXISTS `kit_template_produtos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kit_template_id` int NOT NULL,
  `produto_id` int NOT NULL,
  `quantidade` int DEFAULT '1',
  `ordem` int DEFAULT '0',
  `ativo` tinyint(1) DEFAULT '1',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `kit_template_id` (`kit_template_id`),
  KEY `produto_id` (`produto_id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `kit_template_produtos`
--

INSERT INTO `kit_template_produtos` (`id`, `kit_template_id`, `produto_id`, `quantidade`, `ordem`, `ativo`, `data_criacao`) VALUES
(29, 1, 6, 1, 1, 1, '2025-07-26 18:49:41'),
(30, 1, 1, 1, 2, 1, '2025-07-26 18:49:41'),
(31, 1, 2, 1, 3, 1, '2025-07-26 18:49:41'),
(32, 1, 4, 1, 4, 1, '2025-07-26 18:49:41'),
(33, 1, 3, 1, 5, 1, '2025-07-26 18:49:41'),
(34, 1, 9, 1, 6, 1, '2025-07-26 18:49:41'),
(35, 2, 6, 1, 1, 1, '2025-07-26 18:49:57'),
(37, 3, 1, 1, 1, 1, '2025-09-03 17:19:27');

-- --------------------------------------------------------

--
-- Estrutura para tabela `leads_organizadores`
--

DROP TABLE IF EXISTS `leads_organizadores`;
CREATE TABLE IF NOT EXISTS `leads_organizadores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome_completo` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `empresa` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `regiao` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `modalidade_esportiva` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantidade_eventos` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome_evento` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `regulamento` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `indicacao` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('novo','contatado','convertido','descartado') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'novo',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `data_contato` timestamp NULL DEFAULT NULL,
  `observacoes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `leads_organizadores`
--

INSERT INTO `leads_organizadores` (`id`, `nome_completo`, `email`, `telefone`, `empresa`, `regiao`, `modalidade_esportiva`, `quantidade_eventos`, `nome_evento`, `regulamento`, `indicacao`, `status`, `data_criacao`, `data_contato`, `observacoes`) VALUES
(1, 'EUDIMACI BARBOZA DE LIRA', 'eudimaci08@yahoo.com.br', '92982027654', 'EBL Eventos Esportivos', 'AM', 'corrida-rua', '1', 'I Corrida Sauim de Coleira', 'sim', 'Amigos', 'novo', '2025-09-26 21:15:09', NULL, NULL),
(2, 'EUDIMACI BARBOZA DE LIRA', 'eudimaci08@yahoo.com.br', '92982027654', 'Mente de Corredor', 'AM', 'corrida-rua', '2-4', 'I Corrida Sauim de Coleira', 'sim', 'Amigos', 'novo', '2025-10-08 03:43:28', NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `logs_admin`
--

DROP TABLE IF EXISTS `logs_admin`;
CREATE TABLE IF NOT EXISTS `logs_admin` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `acao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_acao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `logs_admin`
--

INSERT INTO `logs_admin` (`id`, `usuario_id`, `acao`, `data_acao`) VALUES
(1, 2, 'Listou eventos', '2025-07-17 13:30:58'),
(2, 2, 'Listou eventos', '2025-07-17 13:31:16'),
(3, 2, 'Listou eventos', '2025-07-17 13:31:25'),
(4, 2, 'Listou eventos', '2025-07-17 13:40:07'),
(5, 2, 'Listou eventos', '2025-07-17 13:44:24'),
(6, 2, 'Listou eventos', '2025-07-17 13:45:08'),
(7, 2, 'Listou eventos', '2025-07-17 14:53:50'),
(8, 2, 'Listou eventos', '2025-07-17 15:28:04'),
(9, 2, 'Listou eventos', '2025-07-17 15:28:55'),
(10, 2, 'Listou eventos', '2025-07-17 15:31:39'),
(11, 2, 'Listou eventos', '2025-07-17 15:38:46'),
(12, 2, 'Listou eventos', '2025-07-17 15:40:36'),
(13, 2, 'Listou eventos', '2025-07-17 15:40:38'),
(14, 2, 'Listou eventos', '2025-07-17 15:41:47'),
(15, 2, 'Listou eventos', '2025-07-17 15:41:49'),
(16, 2, 'Listou eventos', '2025-07-17 15:43:32'),
(17, 2, 'Listou eventos', '2025-07-17 15:44:23'),
(18, 2, 'Listou eventos', '2025-07-17 15:44:55'),
(19, 2, 'Listou eventos', '2025-07-17 15:45:14'),
(20, 2, 'Listou eventos', '2025-07-17 15:46:39'),
(21, 2, 'Listou eventos', '2025-07-17 15:47:38'),
(22, 2, 'Listou eventos', '2025-07-17 15:47:39'),
(23, 2, 'Listou eventos', '2025-07-17 15:47:40'),
(24, 2, 'Listou eventos', '2025-07-17 15:48:03'),
(25, 2, 'Listou eventos', '2025-07-17 15:48:05'),
(26, 2, 'Listou eventos', '2025-07-17 15:48:07'),
(27, 2, 'Listou eventos', '2025-07-17 15:48:50'),
(28, 2, 'Listou eventos', '2025-07-17 15:48:52'),
(29, 2, 'Listou eventos', '2025-07-17 15:49:59'),
(30, 2, 'Listou eventos', '2025-07-17 15:50:41'),
(31, 2, 'Listou eventos', '2025-07-17 15:50:42'),
(32, 2, 'Listou eventos', '2025-07-17 15:51:03'),
(33, 2, 'Listou eventos', '2025-07-17 15:51:05'),
(34, 2, 'Listou eventos', '2025-07-17 15:51:35'),
(35, 2, 'Listou eventos', '2025-07-17 15:51:38'),
(36, 2, 'Listou eventos', '2025-07-17 15:51:48'),
(37, 2, 'Listou eventos', '2025-07-17 15:58:07'),
(38, 2, 'Listou eventos', '2025-07-17 15:58:10'),
(39, 2, 'Listou eventos', '2025-07-17 16:09:05'),
(40, 2, 'Listou eventos', '2025-07-17 16:10:24'),
(41, 2, 'Listou eventos', '2025-07-17 16:10:33'),
(42, 2, 'Listou eventos', '2025-07-17 16:11:13'),
(43, 2, 'Listou eventos', '2025-07-17 16:11:27'),
(44, 2, 'Listou eventos', '2025-07-17 16:22:58'),
(45, 2, 'Listou eventos', '2025-07-17 16:23:49'),
(46, 2, 'Listou eventos', '2025-07-17 16:26:19'),
(47, 2, 'Listou eventos', '2025-07-17 16:33:35'),
(48, 2, 'Listou eventos', '2025-07-17 16:33:37'),
(49, 2, 'Listou eventos', '2025-07-17 16:37:12'),
(50, 2, 'Listou eventos', '2025-07-17 16:44:43'),
(51, 2, 'Listou eventos', '2025-07-17 16:44:47'),
(52, 2, 'Listou eventos', '2025-07-17 16:54:03'),
(53, 2, 'Listou eventos', '2025-07-17 16:55:37'),
(54, 2, 'Listou eventos', '2025-07-17 18:12:00'),
(55, 2, 'Listou eventos', '2025-07-17 18:12:21');

-- --------------------------------------------------------

--
-- Estrutura para tabela `lotes_inscricao`
--

DROP TABLE IF EXISTS `lotes_inscricao`;
CREATE TABLE IF NOT EXISTS `lotes_inscricao` (
  `id` int NOT NULL AUTO_INCREMENT,
  `evento_id` int NOT NULL,
  `modalidade_id` int NOT NULL,
  `numero_lote` int NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `preco_por_extenso` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL,
  `vagas_disponiveis` int DEFAULT NULL,
  `taxa_servico` decimal(10,2) DEFAULT '0.00',
  `quem_paga_taxa` enum('organizador','participante') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'participante',
  `idade_min` int DEFAULT '0',
  `idade_max` int DEFAULT '100',
  `desconto_idoso` tinyint(1) DEFAULT '0',
  `ativo` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_evento_modalidade_lote` (`evento_id`,`modalidade_id`,`numero_lote`),
  KEY `idx_evento` (`evento_id`),
  KEY `idx_modalidade` (`modalidade_id`),
  KEY `idx_datas` (`data_inicio`,`data_fim`),
  KEY `idx_ativo` (`ativo`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `lotes_inscricao`
--

INSERT INTO `lotes_inscricao` (`id`, `evento_id`, `modalidade_id`, `numero_lote`, `preco`, `preco_por_extenso`, `data_inicio`, `data_fim`, `vagas_disponiveis`, `taxa_servico`, `quem_paga_taxa`, `idade_min`, `idade_max`, `desconto_idoso`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 1, 105.00, 'Valor muito alto reales', '2024-07-16', '2024-08-15', 100, 6.50, 'participante', 14, 100, 0, 0, '2025-07-20 23:37:33', '2025-11-04 19:33:46'),
(2, 2, 2, 1, 115.00, 'Setenta e cinco reais', '2024-07-16', '2024-08-15', 100, 6.50, 'participante', 14, 100, 0, 1, '2025-07-20 23:37:33', '2025-09-05 22:40:28'),
(3, 2, 3, 1, 105.00, 'Valor muito alto reales', '2024-07-16', '2024-08-15', 100, 6.50, 'participante', 14, 100, 0, 1, '2025-07-20 23:37:33', '2025-09-06 00:23:31'),
(4, 2, 4, 1, 115.00, 'Setenta e cinco reais', '2024-07-16', '2024-08-15', 100, 6.50, 'participante', 14, 100, 0, 1, '2025-07-20 23:37:33', '2025-09-05 22:40:28'),
(5, 2, 5, 1, 115.00, 'Setenta e cinco reais', '2024-07-16', '2024-08-15', 100, 6.50, 'participante', 14, 100, 0, 1, '2025-07-20 23:37:33', '2025-09-05 22:40:28');

-- --------------------------------------------------------

--
-- Estrutura para tabela `modalidades`
--

DROP TABLE IF EXISTS `modalidades`;
CREATE TABLE IF NOT EXISTS `modalidades` (
  `id` int NOT NULL AUTO_INCREMENT,
  `evento_id` int NOT NULL,
  `categoria_id` int NOT NULL,
  `nome` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `distancia` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_prova` enum('corrida','caminhada','ambos') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'corrida',
  `limite_vagas` int DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_evento` (`evento_id`),
  KEY `idx_categoria` (`categoria_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `modalidades`
--

INSERT INTO `modalidades` (`id`, `evento_id`, `categoria_id`, `nome`, `descricao`, `distancia`, `tipo_prova`, `limite_vagas`, `ativo`, `data_criacao`, `updated_at`) VALUES
(1, 2, 1, 'CORRIDA 10KM', 'Corrida de 10km para público geral', '10km', 'corrida', NULL, 1, '2025-08-25 22:11:01', '2025-09-06 00:17:49'),
(2, 2, 2, 'CORRIDA 10KM | KIT COMPLETO', 'Corrida de 10km com kit completo para público geral', '10km', 'corrida', NULL, 1, '2025-08-25 22:11:01', '2025-08-25 22:11:01'),
(3, 2, 1, 'CORRIDA 5KM', 'Corrida de 5km para público geral', '5km', 'corrida', NULL, 1, '2025-08-25 22:11:01', '2025-09-06 00:18:00'),
(4, 2, 2, 'CORRIDA 5KM | KIT COMPLETO', 'Corrida de 5km com kit completo para público geral', '5km', 'corrida', NULL, 1, '2025-08-25 22:11:01', '2025-08-25 22:11:01'),
(5, 2, 4, 'CORRIDA 5KM | KIT COMPLETO - FAMÍLIA 1', 'Corrida de 5km familiar para público geral', '5km', 'ambos', NULL, 1, '2025-08-25 22:11:01', '2025-08-25 22:11:01'),
(19, 3, 12, 'CORRIDA 10KM', 'Um acorrida para todas as pessoas', '10KM', 'corrida', 1000, 1, '2025-09-23 01:44:03', '2025-09-23 01:44:03');

-- --------------------------------------------------------

--
-- Estrutura para tabela `openai_token_usage`
--

DROP TABLE IF EXISTS `openai_token_usage`;
CREATE TABLE IF NOT EXISTS `openai_token_usage` (
  `id` int NOT NULL AUTO_INCREMENT,
  `data_hora` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_id` int DEFAULT NULL,
  `endpoint` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modelo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prompt_tokens` int DEFAULT '0',
  `completion_tokens` int DEFAULT '0',
  `total_tokens` int DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_openai_usuario` (`usuario_id`),
  KEY `idx_openai_data` (`data_hora`),
  KEY `idx_openai_endpoint` (`endpoint`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `organizadores`
--

DROP TABLE IF EXISTS `organizadores`;
CREATE TABLE IF NOT EXISTS `organizadores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `empresa` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `regiao` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modalidade_esportiva` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantidade_eventos` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `regulamento` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `organizadores`
--

INSERT INTO `organizadores` (`id`, `usuario_id`, `empresa`, `regiao`, `modalidade_esportiva`, `quantidade_eventos`, `regulamento`) VALUES
(1, 2, 'UEA - APOIO TÉCNICO MENTE DE CORREDOR', 'AM', 'Corrida de Rua', '1', 'Sim, Tenho Regulamento do Evento'),
(2, 2, 'UEA - APOIO TÉCNICO MENTE DE CORREDOR', 'AM', 'Corrida de Rua', '1', 'Sim, Tenho Regulamento do Evento');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pagamentos`
--

DROP TABLE IF EXISTS `pagamentos`;
CREATE TABLE IF NOT EXISTS `pagamentos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `inscricao_id` int NOT NULL,
  `forma_pagamento` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_pagamento` datetime DEFAULT NULL,
  `valor_total` decimal(10,2) DEFAULT NULL,
  `valor_desconto` decimal(10,2) DEFAULT NULL,
  `valor_pago` decimal(10,2) DEFAULT NULL,
  `taxa_participante` decimal(10,2) DEFAULT NULL,
  `valor_repasse` decimal(10,2) DEFAULT NULL,
  `status` enum('pendente','pago','cancelado') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pendente',
  PRIMARY KEY (`id`),
  KEY `inscricao_id` (`inscricao_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pagamentos_ml`
--

DROP TABLE IF EXISTS `pagamentos_ml`;
CREATE TABLE IF NOT EXISTS `pagamentos_ml` (
  `id` int NOT NULL AUTO_INCREMENT,
  `inscricao_id` int NOT NULL,
  `preference_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `init_point` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pendente','pago','cancelado','rejeitado','processando') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pendente',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `dados_pagamento` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `valor_pago` decimal(10,2) DEFAULT NULL,
  `metodo_pagamento` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parcelas` int DEFAULT '1',
  `taxa_ml` decimal(10,2) DEFAULT NULL,
  `user_id` int NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_inscricao_id` (`inscricao_id`),
  KEY `idx_preference_id` (`preference_id`),
  KEY `idx_payment_id` (`payment_id`),
  KEY `idx_status` (`status`),
  KEY `idx_data_criacao` (`data_criacao`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `pagamentos_ml`
--

INSERT INTO `pagamentos_ml` (`id`, `inscricao_id`, `preference_id`, `payment_id`, `init_point`, `status`, `data_criacao`, `data_atualizacao`, `dados_pagamento`, `valor_pago`, `metodo_pagamento`, `parcelas`, `taxa_ml`, `user_id`, `created`) VALUES
(1, 2, 'FAKE_1763401123_2', NULL, 'http://localhost/movamazon/frontend/paginas/participante/pagamento-fake.php?preference_id=FAKE_1763401123_2', '', '2025-11-17 17:38:43', '2025-11-17 17:38:43', NULL, NULL, NULL, 1, NULL, 5, '2025-11-17 13:38:43'),
(2, 2, 'FAKE_1763401221_2', NULL, 'http://localhost/movamazon/frontend/paginas/participante/pagamento-fake.php?preference_id=FAKE_1763401221_2', '', '2025-11-17 17:40:21', '2025-11-17 17:40:21', NULL, NULL, NULL, 1, NULL, 5, '2025-11-17 13:40:21'),
(3, 2, 'FAKE_1763401303_2', NULL, 'http://localhost/movamazon/frontend/paginas/participante/pagamento-fake.php?preference_id=FAKE_1763401303_2', '', '2025-11-17 17:41:43', '2025-11-17 17:41:43', NULL, NULL, NULL, 1, NULL, 5, '2025-11-17 13:41:43'),
(4, 2, 'FAKE_1763403982_2', NULL, 'http://localhost/movamazon/frontend/paginas/participante/pagamento-fake.php?preference_id=FAKE_1763403982_2', '', '2025-11-17 18:26:22', '2025-11-17 18:26:22', NULL, NULL, NULL, 1, NULL, 5, '2025-11-17 14:26:22'),
(5, 4, 'FAKE_1763406599_4', NULL, 'http://localhost/movamazon/frontend/paginas/participante/pagamento-fake.php?preference_id=FAKE_1763406599_4', 'pago', '2025-11-17 19:09:59', '2025-11-17 19:10:06', NULL, NULL, NULL, 1, NULL, 7, '2025-11-17 15:09:59');

-- --------------------------------------------------------

--
-- Estrutura para tabela `papeis`
--

DROP TABLE IF EXISTS `papeis`;
CREATE TABLE IF NOT EXISTS `papeis` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `papeis`
--

INSERT INTO `papeis` (`id`, `nome`) VALUES
(1, 'participante'),
(2, 'organizador'),
(3, 'admin');

-- --------------------------------------------------------

--
-- Estrutura para tabela `participante`
--

DROP TABLE IF EXISTS `participante`;
CREATE TABLE IF NOT EXISTS `participante` (
  `usuario_id` int NOT NULL,
  `total_corridas` int DEFAULT '0',
  `total_km` decimal(8,2) DEFAULT '0.00',
  `melhor_tempo` time DEFAULT NULL,
  `camiseta_tamanho` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modalidade_preferida` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aceita_emails` tinyint(1) DEFAULT '1',
  `data_primeira_corrida` date DEFAULT NULL,
  `data_ultima_corrida` date DEFAULT NULL,
  PRIMARY KEY (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pedidos`
--

DROP TABLE IF EXISTS `pedidos`;
CREATE TABLE IF NOT EXISTS `pedidos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `inscricao_id` int NOT NULL,
  `numero_pedido` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_pedido` date DEFAULT NULL,
  `hora_pedido` time DEFAULT NULL,
  `status_pedido` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `detalhe_status` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `responsavel_nome` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responsavel_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `responsavel_celular` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comentario` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `origem` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `campanha` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valor_produto` decimal(10,2) DEFAULT NULL,
  `valor_repasse_produto` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inscricao_id` (`inscricao_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `periodos_inscricao`
--

DROP TABLE IF EXISTS `periodos_inscricao`;
CREATE TABLE IF NOT EXISTS `periodos_inscricao` (
  `id` int NOT NULL AUTO_INCREMENT,
  `evento_id` int NOT NULL,
  `inicio` date NOT NULL,
  `fim` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `evento_id` (`evento_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `planos_treino_gerados`
--

DROP TABLE IF EXISTS `planos_treino_gerados`;
CREATE TABLE IF NOT EXISTS `planos_treino_gerados` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `inscricao_id` int DEFAULT NULL COMMENT 'Vinculação com inscrição na corrida',
  `profissional_id` int DEFAULT NULL,
  `anamnese_id` int NOT NULL,
  `data_criacao_plano` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `bibliografia_plano` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `foco_primario` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duracao_treino_geral` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dias_plano` int DEFAULT '5' COMMENT 'Duração do plano em dias',
  `equipamento_geral` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_planos_usuario` (`usuario_id`),
  KEY `idx_planos_profissional` (`profissional_id`),
  KEY `idx_planos_anamnese` (`anamnese_id`),
  KEY `idx_planos_inscricao` (`inscricao_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos`
--

DROP TABLE IF EXISTS `produtos`;
CREATE TABLE IF NOT EXISTS `produtos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `tipo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `preco` decimal(10,2) DEFAULT '0.00',
  `disponivel_venda` tinyint(1) DEFAULT '1',
  `foto_produto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `produtos`
--

INSERT INTO `produtos` (`id`, `nome`, `descricao`, `tipo`, `preco`, `disponivel_venda`, `foto_produto`, `ativo`, `data_criacao`, `updated_at`) VALUES
(1, 'Camiseta', 'Camiseta oficial do evento', 'camiseta', 25.00, 1, NULL, 1, '2025-07-19 19:26:54', '2025-07-20 00:34:42'),
(2, 'Medalha', 'Medalha de participação', 'medalha', 15.00, 1, NULL, 1, '2025-07-19 19:26:54', '2025-07-20 00:34:42'),
(3, 'Número de peito', 'Número de identificação para a prova', 'numero', 5.00, 1, NULL, 1, '2025-07-19 19:26:54', '2025-07-20 00:34:42'),
(4, 'Chip de cronometragem', 'Chip para cronometragem da prova', 'chip', 30.00, 1, NULL, 1, '2025-07-19 19:26:54', '2025-07-20 00:34:42'),
(5, 'Mochila', 'Mochila do evento', 'outro', 20.00, 1, NULL, 1, '2025-07-19 19:26:54', '2025-07-20 00:34:42'),
(6, 'Boné', 'Boné oficial do evento', 'outro', 20.00, 1, NULL, 1, '2025-07-19 19:26:54', '2025-07-20 00:34:42'),
(7, 'Garrafa', 'Garrafa de água do evento', 'outro', 20.00, 1, NULL, 1, '2025-07-19 19:26:54', '2025-07-20 00:34:42'),
(8, 'Viseira', 'Viseira oficial do evento', 'outro', 20.00, 1, NULL, 1, '2025-07-19 21:31:25', '2025-07-20 00:34:42'),
(9, 'Troféu', 'Troféu para vencedores', 'outro', 20.00, 1, NULL, 1, '2025-07-19 21:31:25', '2025-07-20 00:34:42'),
(10, 'Selo Camisa com GPS', 'Selo promocional do evento', 'outro', 20.00, 1, NULL, 1, '2025-07-19 21:31:25', '2025-11-04 19:34:41'),
(11, 'Kit promocional', 'Kit promocional com produtos especiais', 'outro', 20.00, 1, NULL, 1, '2025-07-19 21:31:25', '2025-07-20 00:34:42');

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos_extras`
--

DROP TABLE IF EXISTS `produtos_extras`;
CREATE TABLE IF NOT EXISTS `produtos_extras` (
  `id` int NOT NULL AUTO_INCREMENT,
  `evento_id` int NOT NULL,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `valor` decimal(10,2) NOT NULL,
  `disponivel_venda` tinyint(1) DEFAULT '1',
  `categoria` enum('vestuario','acessorio','seguro','outros') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'outros',
  `ativo` tinyint(1) DEFAULT '1',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `evento_id` (`evento_id`),
  KEY `idx_produtos_extras_ativo` (`ativo`),
  KEY `idx_evento` (`evento_id`),
  KEY `idx_ativo` (`ativo`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `produtos_extras`
--

INSERT INTO `produtos_extras` (`id`, `evento_id`, `nome`, `descricao`, `valor`, `disponivel_venda`, `categoria`, `ativo`, `data_criacao`, `updated_at`) VALUES
(2, 2, 'Kit Camisa + Medalha', 'Kit adicional com camisa e medalha para 5KM', 20.00, 1, 'outros', 1, '2025-07-19 19:26:54', '2025-10-10 19:05:50'),
(3, 2, 'Combo VIP', 'Boné e Camisa', 70.00, 1, 'outros', 1, '2025-10-13 12:45:47', '2025-10-13 15:48:08');

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos_extras_backup`
--

DROP TABLE IF EXISTS `produtos_extras_backup`;
CREATE TABLE IF NOT EXISTS `produtos_extras_backup` (
  `id` int NOT NULL DEFAULT '0',
  `produto_id` int DEFAULT NULL,
  `evento_id` int NOT NULL,
  `modalidade_evento_id` int NOT NULL,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `valor` decimal(10,2) NOT NULL,
  `categoria` enum('vestuario','acessorio','seguro','outros') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'outros',
  `estoque` int DEFAULT '-1',
  `aplicavel_categorias` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
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

DROP TABLE IF EXISTS `produtos_extras_modalidade`;
CREATE TABLE IF NOT EXISTS `produtos_extras_modalidade` (
  `id` int NOT NULL AUTO_INCREMENT,
  `modalidade_evento_id` int NOT NULL,
  `nome_produto` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `valor` decimal(10,2) NOT NULL,
  `foto_produto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disponivel_venda` tinyint(1) DEFAULT '1',
  `vagas_disponiveis` int NOT NULL DEFAULT '0',
  `vagas_vendidas` int NOT NULL DEFAULT '0',
  `ativo` tinyint(1) DEFAULT '1',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `modalidade_evento_id` (`modalidade_evento_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `produtos_extras_modalidade`
--

INSERT INTO `produtos_extras_modalidade` (`id`, `modalidade_evento_id`, `nome_produto`, `descricao`, `valor`, `foto_produto`, `disponivel_venda`, `vagas_disponiveis`, `vagas_vendidas`, `ativo`, `data_criacao`, `updated_at`) VALUES
(1, 1, 'Kit Camisa + Medalha', 'Kit adicional com camisa e medalha para 10KM', 25.00, NULL, 1, 60, 0, 1, '2025-07-17 05:31:33', '2025-07-19 23:20:31'),
(2, 2, 'Kit Camisa + Medalha', 'Kit adicional com camisa e medalha para 5KM', 20.00, NULL, 1, 140, 0, 1, '2025-07-17 05:31:33', '2025-07-19 23:20:31');

-- --------------------------------------------------------

--
-- Estrutura para tabela `produto_extra_produtos`
--

DROP TABLE IF EXISTS `produto_extra_produtos`;
CREATE TABLE IF NOT EXISTS `produto_extra_produtos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `produto_extra_id` int NOT NULL,
  `produto_id` int NOT NULL,
  `quantidade` int DEFAULT '1',
  `ativo` tinyint(1) DEFAULT '1',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_produto_extra` (`produto_extra_id`),
  KEY `idx_produto` (`produto_id`),
  KEY `idx_ativo` (`ativo`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `produto_extra_produtos`
--

INSERT INTO `produto_extra_produtos` (`id`, `produto_extra_id`, `produto_id`, `quantidade`, `ativo`, `data_criacao`) VALUES
(6, 3, 1, 1, 1, '2025-10-13 15:48:08'),
(7, 3, 6, 1, 1, '2025-10-13 15:48:08'),
(8, 3, 8, 1, 1, '2025-10-13 15:48:08');

-- --------------------------------------------------------

--
-- Estrutura para tabela `programacao_evento`
--

DROP TABLE IF EXISTS `programacao_evento`;
CREATE TABLE IF NOT EXISTS `programacao_evento` (
  `id` int NOT NULL AUTO_INCREMENT,
  `evento_id` int NOT NULL,
  `tipo` enum('percurso','horario_largada','atividade_adicional') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `titulo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ordem` int DEFAULT '0',
  `ativo` tinyint(1) DEFAULT '1',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `evento_id` (`evento_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `programacao_evento`
--

INSERT INTO `programacao_evento` (`id`, `evento_id`, `tipo`, `titulo`, `descricao`, `ordem`, `ativo`, `data_criacao`) VALUES
(1, 2, 'percurso', 'Largada', 'Largada na área interna da UEA-EST', 1, 1, '2025-07-17 01:05:31'),
(2, 2, 'percurso', 'Av Darcy Vargas', 'Av Darcy Vargas', 2, 1, '2025-07-17 01:05:31'),
(3, 2, 'percurso', 'Av Maceió', 'Av Maceió', 3, 1, '2025-07-17 01:05:31'),
(4, 2, 'percurso', 'Av João Valério', 'Av João Valério', 4, 1, '2025-07-17 01:05:31'),
(5, 2, 'percurso', 'Av Djalma Batista', 'Av Djalma Batista', 5, 1, '2025-07-17 01:05:31'),
(6, 2, 'percurso', 'Av Darcy Vargas', 'Av Darcy Vargas', 6, 1, '2025-07-17 01:05:31'),
(7, 2, 'percurso', 'Chegada', 'Chegada na Área Interna da UEA-EST', 7, 1, '2025-07-17 01:05:31'),
(8, 2, 'horario_largada', 'Pelotão PCD 5km', 'Pelotão PCD 5km: 5h45', 8, 1, '2025-07-17 01:05:31'),
(9, 2, 'horario_largada', 'Pelotão de Elite e Público Geral', 'Pelotão de Elite e Público Geral: 5 e 10 km: 6h00', 9, 1, '2025-07-17 01:05:31'),
(10, 2, 'atividade_adicional', 'Atividades Adicionais', 'Teremos tendas com atividades de educação ambiental para crianças, além de apoio da equipe de saúde da Universidade do Estado do Amazonas, com aferição de pressão arterial e avaliação médica gratuita, além de tendas para relaxamento dos atletas com apoio de fisioterapeutas para liberação miofascial após a prova', 10, 1, '2025-07-17 01:05:31');

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
  `sinais_alerta_observados` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Tontura, sudorese fria, tremores, fraqueza, visão turva, confusão mental',
  `mal_estar_observado` enum('sim','nao') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'nao' COMMENT 'Houve mal-estar durante ou após o treino',
  `observacoes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Alterações glicêmicas, desconfortos anormais e outras observações',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `idx_progresso_treino` (`treino_id`,`data_realizado`),
  KEY `idx_glicemia_monitoring` (`glicemia_pre_treino`,`glicemia_pos_treino`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `questionario_evento`
--

DROP TABLE IF EXISTS `questionario_evento`;
CREATE TABLE IF NOT EXISTS `questionario_evento` (
  `id` int NOT NULL AUTO_INCREMENT,
  `evento_id` int NOT NULL,
  `modalidade_id` int NOT NULL,
  `tipo` enum('pergunta','campo') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_resposta` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mascara` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `texto` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `obrigatorio` tinyint(1) DEFAULT '0',
  `ordem` int DEFAULT '0',
  `ativo` tinyint(1) DEFAULT '1',
  `status_site` enum('publicada','rascunho') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'publicada',
  `status_grupo` enum('publicada','rascunho') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'publicada',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `evento_id` (`evento_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `questionario_evento`
--

INSERT INTO `questionario_evento` (`id`, `evento_id`, `modalidade_id`, `tipo`, `tipo_resposta`, `mascara`, `texto`, `obrigatorio`, `ordem`, `ativo`, `status_site`, `status_grupo`, `data_criacao`) VALUES
(2, 2, 0, 'pergunta', NULL, NULL, 'Você sabe o que pode acontecer se o Sauim-de-coleira for extinto?', 0, 1, 1, 'publicada', 'publicada', '2025-07-17 01:05:10'),
(8, 2, 0, 'pergunta', NULL, NULL, 'Você conhece qual é o animal que é o Símbolo de Manaus?', 0, 1, 1, 'publicada', 'publicada', '2025-07-17 01:20:07');

-- --------------------------------------------------------

--
-- Estrutura para tabela `questionario_evento_modalidade`
--

DROP TABLE IF EXISTS `questionario_evento_modalidade`;
CREATE TABLE IF NOT EXISTS `questionario_evento_modalidade` (
  `id` int NOT NULL AUTO_INCREMENT,
  `questionario_evento_id` int NOT NULL,
  `modalidade_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `questionario_evento_id` (`questionario_evento_id`),
  KEY `modalidade_id` (`modalidade_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `repasse_organizadores`
--

DROP TABLE IF EXISTS `repasse_organizadores`;
CREATE TABLE IF NOT EXISTS `repasse_organizadores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `evento_id` int NOT NULL,
  `organizador_id` int NOT NULL,
  `valor_total` decimal(10,2) DEFAULT NULL,
  `data_repasse` datetime DEFAULT NULL,
  `status` enum('pendente','realizado','cancelado') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pendente',
  PRIMARY KEY (`id`),
  KEY `evento_id` (`evento_id`),
  KEY `organizador_id` (`organizador_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `resultados_evento`
--

DROP TABLE IF EXISTS `resultados_evento`;
CREATE TABLE IF NOT EXISTS `resultados_evento` (
  `id` int NOT NULL AUTO_INCREMENT,
  `inscricao_id` int NOT NULL,
  `tempo_final` time DEFAULT NULL,
  `colocacao` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inscricao_id` (`inscricao_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `retirada_kits_evento`
--

DROP TABLE IF EXISTS `retirada_kits_evento`;
CREATE TABLE IF NOT EXISTS `retirada_kits_evento` (
  `id` int NOT NULL AUTO_INCREMENT,
  `evento_id` int NOT NULL,
  `data_retirada` date DEFAULT NULL,
  `horario_inicio` time DEFAULT NULL,
  `horario_fim` time DEFAULT NULL,
  `local_retirada` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `endereco_completo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `instrucoes_retirada` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `retirada_terceiros` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `documentos_necessarios` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ativo` tinyint(1) DEFAULT '1',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `evento_id` (`evento_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `retirada_kits_evento`
--

INSERT INTO `retirada_kits_evento` (`id`, `evento_id`, `data_retirada`, `horario_inicio`, `horario_fim`, `local_retirada`, `endereco_completo`, `instrucoes_retirada`, `retirada_terceiros`, `documentos_necessarios`, `ativo`, `data_criacao`) VALUES
(1, 2, '2023-10-23', '10:00:00', '20:00:00', 'Reitoria da Universidade do Estado do Amazonas-UEA', 'Reitoria da Universidade do Estado do Amazonas-UEA, Av. Djalma Baptista, 3578, Flores, CEP 69050-010, Manaus/AM.', 'Retirada de kits no local especificado', 'Os Kits somente serão entregues pessoalmente para os participantes inscritos, com a apresentação de documento de identidade, que comprove a identificação do mesmo. Nos casos excepcionais, poderão ser retirados Kits por terceiros, contudo com a apresentação de documento de identidade da pessoa inscrita.', 'Documento de identidade original', 1, '2025-07-17 01:05:50'),
(2, 2, '2025-09-20', '22:10:00', '22:10:00', 'CMPM-Colegio Militar-Av. Codajás, s/n - Petrópolis, Manaus - AM, 69065-130', NULL, 'Trazer documento com foto e numero de CPF', NULL, 'CPF,CNH', 1, '2025-09-07 01:10:42'),
(3, 6, '2025-10-29', '10:00:00', '20:00:00', 'rua das flores', NULL, 'apresentar documento com foto', NULL, 'CPF ou documento com foto', 1, '2025-10-08 21:24:44'),
(4, 6, '2025-10-25', '10:00:00', '17:00:00', 'UEA Normal superior', NULL, 'Levar documento com foto', NULL, 'CPF', 1, '2025-10-08 21:26:47');

-- --------------------------------------------------------

--
-- Estrutura para tabela `termos_eventos`
--

DROP TABLE IF EXISTS `termos_eventos`;
CREATE TABLE IF NOT EXISTS `termos_eventos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `evento_id` int NOT NULL,
  `modalidade_id` int DEFAULT NULL,
  `titulo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `conteudo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `versao` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '1.0',
  `ativo` tinyint(1) DEFAULT '1',
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `evento_id` (`evento_id`),
  KEY `modalidade_id` (`modalidade_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `termos_eventos`
--

INSERT INTO `termos_eventos` (`id`, `evento_id`, `modalidade_id`, `titulo`, `conteudo`, `versao`, `ativo`, `data_criacao`) VALUES
(1, 2, NULL, 'Termos Gerais do Evento', '<p><strong>TERMOS E CONDIÇÕES GERAIS</strong> 1. INSCRIÇÃO E PARTICIPAÇÃO 1.1. A inscrição no evento implica na aceitação integral destes termos e condições. 1.2. O participante deve ter idade mínima de 16 anos completos na data do evento. 1.3. Menores de 18 anos devem apresentar autorização dos responsáveis legais. 2. PAGAMENTO E REEMBOLSO 2.1. O pagamento deve ser efetuado conforme as condições estabelecidas. 2.2. Em caso de cancelamento pelo participante: - Até 30 dias antes: reembolso de 80% - Até 15 dias antes: reembolso de 50% - Menos de 15 dias: sem reembolso 2.3. O evento pode ser cancelado por motivos de força maior, com reembolso integral. 3. RESPONSABILIDADES 3.1. O participante é responsável por sua segurança e integridade física. 3.2. É obrigatório o uso de equipamentos de segurança fornecidos. 3.3. O participante deve seguir todas as instruções dos organizadores. 4. IMAGEM E DADOS 4.1. O participante autoriza o uso de sua imagem para fins promocionais. 4.2. Os dados pessoais serão tratados conforme a LGPD. 5. DISPOSIÇÕES GERAIS 5.1. Casos omissos serão resolvidos pelos organizadores. 5.2. Estes termos podem ser alterados a qualquer momento. Data de vigência: 01/01/2024</p>', '1.0', 1, '2025-09-02 16:41:09'),
(2, 2, 1, 'Termos Específicos - Corrida', 'TERMOS ESPECÍFICOS PARA MODALIDADE CORRIDA\r\n\r\n1. EQUIPAMENTOS OBRIGATÓRIOS\r\n1.1. Tênis adequado para corrida\r\n1.2. Roupas confortáveis e apropriadas\r\n1.3. Hidratação durante o percurso\r\n\r\n2. REGRAS DE SEGURANÇA\r\n2.1. Manter-se sempre à direita da pista\r\n2.2. Não ultrapassar outros corredores de forma perigosa\r\n2.3. Respeitar os sinais dos organizadores\r\n\r\n3. MEDICAL\r\n3.1. É recomendável consulta médica antes da participação\r\n3.2. Informar sobre condições médicas preexistentes\r\n3.3. Levar medicamentos de uso pessoal se necessário\r\n\r\n4. PERCURSO\r\n4.1. O percurso será sinalizado e monitorado\r\n4.2. Pontos de hidratação a cada 2km\r\n4.3. Tempo limite: 3 horas para completar o percurso\r\n\r\n5. PREMIAÇÃO\r\n5.1. Medalhas para todos os participantes\r\n5.2. Troféus para os 3 primeiros colocados de cada categoria\r\n5.3. Resultados disponíveis no site do evento\r\n\r\nEstes termos complementam os termos gerais do evento.', '1.0', 1, '2025-09-02 16:41:09');

-- --------------------------------------------------------

--
-- Estrutura para tabela `treinos`
--

DROP TABLE IF EXISTS `treinos`;
CREATE TABLE IF NOT EXISTS `treinos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `anamnese_id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `plano_treino_gerado_id` int NOT NULL,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `nivel_dificuldade` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'intermediario',
  `dia_semana_id` int DEFAULT NULL COMMENT '1=Domingo, 2=Segunda, etc.',
  `parte_inicial` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'JSON com exercícios de aquecimento',
  `parte_principal` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'JSON com exercícios principais',
  `volta_calma` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'JSON com exercícios de volta à calma',
  `fcmax` int DEFAULT NULL COMMENT 'Frequência cardíaca máxima',
  `volume_total` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grupos_musculares` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_series` int DEFAULT '3',
  `intervalo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_repeticoes` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `intensidade` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `carga_interna` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacoes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ativo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_treinos_anamnese` (`anamnese_id`),
  KEY `idx_treinos_usuario` (`usuario_id`),
  KEY `idx_treinos_plano` (`plano_treino_gerado_id`),
  KEY `idx_treinos_dia_semana` (`dia_semana_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `treino_exercicios`
--

DROP TABLE IF EXISTS `treino_exercicios`;
CREATE TABLE IF NOT EXISTS `treino_exercicios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `treino_id` int NOT NULL,
  `nome_exercicio` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exercicio_id` int DEFAULT NULL,
  `series` int DEFAULT NULL,
  `repeticoes` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tempo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tempo de duração do exercício',
  `peso` decimal(10,2) DEFAULT NULL,
  `tempo_descanso` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observacoes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'JSON com dados completos do exercício',
  `tipo` enum('repeticao','tempo','livre') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'livre',
  PRIMARY KEY (`id`),
  KEY `idx_treino_exercicios_treino` (`treino_id`),
  KEY `idx_treino_exercicios_exercicio` (`exercicio_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome_completo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `senha` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_nascimento` date DEFAULT NULL,
  `tipo_documento` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `documento` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sexo` enum('Masculino','Feminino','Outro') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `celular` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `endereco` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `complemento` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bairro` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cidade` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uf` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cep` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pais` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_perfil` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Caminho/nome do arquivo da foto de perfil do usuário',
  `status` enum('ativo','inativo') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ativo',
  `data_cadastro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `papel` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'participante',
  `token_recuperacao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token_expira` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_token_recuperacao` (`token_recuperacao`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome_completo`, `email`, `senha`, `data_nascimento`, `tipo_documento`, `documento`, `sexo`, `telefone`, `celular`, `endereco`, `numero`, `complemento`, `bairro`, `cidade`, `uf`, `cep`, `pais`, `foto_perfil`, `status`, `data_cadastro`, `papel`, `token_recuperacao`, `token_expira`) VALUES
(1, 'UEA - APOIO TÉCNICO MENTE DE CORREDOR', 'organizador2657@exemplo.com', '$2y$10$shP8bFZXQdfiG8ILGJssuef.4e3TGFujMIEdOQx3.IdbCnbZh/yje', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ativo', '2025-07-16 14:32:16', 'participante', NULL, NULL),
(2, 'Eudimaci Lira', 'eudimaci08@yahoo.com.br', '$2y$10$shP8bFZXQdfiG8ILGJssuef.4e3TGFujMIEdOQx3.IdbCnbZh/yje', '1970-08-31', '', '', 'Masculino', '92982027654', '', '', '', '', '', '', '', '', '', NULL, 'ativo', '2025-07-17 02:46:46', 'organizador', NULL, NULL),
(3, 'Sandoval Bezerra', 'sandoval.bezerra@gmail.com', '$2y$10$9Y7uHhtwh6ZCbSSGNuMCVu200HKUbT.Z14uhCSnsLAAxTeDC9mC2K', '1969-08-28', '', '', 'Masculino', '81997661657', '', '', '', '', '', '', '', '', '', NULL, 'ativo', '2025-07-17 03:20:34', 'participante', NULL, NULL),
(4, 'Daniel Dias Filho', 'daniel@gmail.com', '$2y$10$9Y7uHhtwh6ZCbSSGNuMCVu200HKUbT.Z14uhCSnsLAAxTeDC9mC2K', '1982-01-15', '', '89658796587', 'Masculino', '92992000396', '', '', '', '', '', '', '', '', '', NULL, 'ativo', '2025-09-05 02:19:57', 'participante', NULL, NULL),
(5, 'Melvin Marble', 'melvin@yahoo.com.br', '$2y$10$WuBHXPdYYeandaKIMH9bhuDTou4d9me4iXwXJf/nBH3tU5zoIT2GG', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ativo', '2025-09-23 00:40:34', 'participante', NULL, NULL),
(6, 'EUDIMACI BARBOZA DE LIRA', 'eudimaci.pecim@gmail.com', '$2y$10$ueDuIkjuYTyMZW51FQRXmuSkovGLm67xTn5wL3BwLTPeMFQ3uih3m', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ativo', '2025-09-25 14:22:04', 'participante', 'b839cb78085e87576a5e28e42bc12dfe05d054139ba8c0331e2e848119e03bee', '2025-10-08 23:28:57'),
(7, 'Joao corredor', 'joao@yahoo.com.br', '$2y$10$krGIU9.x7p86pJQSmdJZX.xeXhkT8F.rTfEtCvSwZDt9FPJ80Kx7G', '1999-02-15', NULL, NULL, 'Masculino', '(31) 97527-5084', '9298965893', 'rua Dom Expedito moura,112A', '150', 'Casa 2', 'Novo Aleixo', 'Manaus', 'AM', '50761430', 'Brasil', 'frontend/assets/img/perfis/perfil_7_1763410657_691b82e1eef64.png', 'ativo', '2025-11-17 18:29:12', 'participante', NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuario_papeis`
--

DROP TABLE IF EXISTS `usuario_papeis`;
CREATE TABLE IF NOT EXISTS `usuario_papeis` (
  `usuario_id` int NOT NULL,
  `papel_id` int NOT NULL,
  PRIMARY KEY (`usuario_id`,`papel_id`),
  KEY `papel_id` (`papel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuario_papeis`
--

INSERT INTO `usuario_papeis` (`usuario_id`, `papel_id`) VALUES
(3, 1),
(2, 2),
(1, 3);

-- --------------------------------------------------------

--
-- Estrutura para view `eventos_ativos`
--
DROP TABLE IF EXISTS `eventos_ativos`;

DROP VIEW IF EXISTS `eventos_ativos`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY INVOKER VIEW `eventos_ativos`  AS SELECT `eventos`.`id` AS `id`, `eventos`.`nome` AS `nome`, `eventos`.`descricao` AS `descricao`, `eventos`.`data_inicio` AS `data_inicio`, `eventos`.`data_fim` AS `data_fim`, `eventos`.`categoria` AS `categoria`, `eventos`.`genero` AS `genero`, `eventos`.`local` AS `local`, `eventos`.`cep` AS `cep`, `eventos`.`url_mapa` AS `url_mapa`, `eventos`.`logradouro` AS `logradouro`, `eventos`.`numero` AS `numero`, `eventos`.`cidade` AS `cidade`, `eventos`.`estado` AS `estado`, `eventos`.`pais` AS `pais`, `eventos`.`regulamento` AS `regulamento`, `eventos`.`status` AS `status`, `eventos`.`organizador_id` AS `organizador_id`, `eventos`.`taxa_setup` AS `taxa_setup`, `eventos`.`percentual_repasse` AS `percentual_repasse`, `eventos`.`exibir_retirada_kit` AS `exibir_retirada_kit`, `eventos`.`taxa_gratuitas` AS `taxa_gratuitas`, `eventos`.`taxa_pagas` AS `taxa_pagas`, `eventos`.`limite_vagas` AS `limite_vagas`, `eventos`.`data_fim_inscricoes` AS `data_fim_inscricoes`, `eventos`.`hora_fim_inscricoes` AS `hora_fim_inscricoes`, `eventos`.`data_criacao` AS `data_criacao`, `eventos`.`hora_inicio` AS `hora_inicio`, `eventos`.`data_realizacao` AS `data_realizacao`, `eventos`.`imagem` AS `imagem`, `eventos`.`deleted_at` AS `deleted_at`, `eventos`.`deleted_by` AS `deleted_by`, `eventos`.`delete_reason` AS `delete_reason` FROM `eventos` WHERE (`eventos`.`deleted_at` is null) ;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `categorias`
--
ALTER TABLE `categorias`
  ADD CONSTRAINT `fk_categorias_evento` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE;

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

