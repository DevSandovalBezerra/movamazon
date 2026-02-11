-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 31/10/2025 às 18:54
-- Versão do servidor: 11.8.3-MariaDB-log
-- Versão do PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `u697465806_mindrunner`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `aceites_termos`
--

CREATE TABLE `aceites_termos` (
  `id` int(11) NOT NULL,
  `inscricao_id` int(11) NOT NULL,
  `termos_id` int(11) NOT NULL,
  `aceito` tinyint(1) DEFAULT 0,
  `data_aceite` timestamp NULL DEFAULT current_timestamp(),
  `ip_usuario` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `camisas`
--

CREATE TABLE `camisas` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `produto_id` int(11) DEFAULT NULL,
  `tamanho` varchar(10) NOT NULL,
  `quantidade_inicial` int(11) NOT NULL DEFAULT 0,
  `quantidade_vendida` int(11) NOT NULL DEFAULT 0,
  `quantidade_disponivel` int(11) NOT NULL DEFAULT 0,
  `quantidade_reservada` int(11) NOT NULL DEFAULT 0,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `camisas`
--

INSERT INTO `camisas` (`id`, `evento_id`, `produto_id`, `tamanho`, `quantidade_inicial`, `quantidade_vendida`, `quantidade_disponivel`, `quantidade_reservada`, `ativo`, `data_criacao`) VALUES
(1, 2, 1, 'PP', 50, 0, 50, 0, 1, '2025-07-17 05:18:18'),
(2, 2, 1, 'P', 294, 0, 294, 0, 1, '2025-07-17 05:18:18'),
(3, 2, 1, 'M', 386, 0, 386, 0, 1, '2025-07-17 05:18:18'),
(4, 2, 1, 'G', 192, 0, 192, 0, 1, '2025-07-17 05:18:18'),
(5, 2, 1, 'GG', 78, 0, 78, 0, 1, '2025-07-17 05:18:18'),
(6, 6, NULL, 'M', 100, 0, 100, 0, 1, '2025-10-08 21:28:38'),
(7, 2, NULL, 'XXG', 100, 0, 100, 0, 1, '2025-10-23 01:56:05');

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `tipo_publico` varchar(50) NOT NULL DEFAULT 'Publico Geral',
  `idade_min` int(11) DEFAULT 0,
  `idade_max` int(11) DEFAULT 100,
  `desconto_idoso` tinyint(1) DEFAULT 0,
  `exibir_inscricao_geral` tinyint(1) DEFAULT 1,
  `exibir_inscricao_grupos` tinyint(1) DEFAULT 1,
  `titulo_link_oculto` varchar(255) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE `cupons_remessa` (
  `id` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `codigo_remessa` varchar(50) NOT NULL,
  `valor_desconto` decimal(10,2) NOT NULL,
  `tipo_valor` enum('percentual','valor_real','preco_fixo') NOT NULL,
  `tipo_desconto` enum('web','mobile','ambos') NOT NULL DEFAULT 'ambos',
  `max_uso` int(11) NOT NULL DEFAULT 1,
  `usos_atuais` int(11) DEFAULT 0,
  `habilita_desconto_itens` tinyint(1) DEFAULT 0,
  `data_criacao` datetime DEFAULT current_timestamp(),
  `data_inicio` date NOT NULL,
  `data_validade` date NOT NULL,
  `status` enum('ativo','cancelado') NOT NULL DEFAULT 'ativo',
  `evento_id` int(11) DEFAULT NULL,
  `aplicavel_modalidades` text DEFAULT NULL,
  `aplicavel_categorias` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE `enderecos_entrega` (
  `id` int(11) NOT NULL,
  `inscricao_id` int(11) NOT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `numero` varchar(20) DEFAULT NULL,
  `complemento` varchar(100) DEFAULT NULL,
  `bairro` varchar(100) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `uf` char(2) DEFAULT NULL,
  `pais` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `estoque_kits`
--

CREATE TABLE `estoque_kits` (
  `id` int(11) NOT NULL,
  `kit_id` int(11) NOT NULL,
  `quantidade_inicial` int(11) NOT NULL,
  `quantidade_vendida` int(11) DEFAULT 0,
  `quantidade_disponivel` int(11) NOT NULL,
  `data_atualizacao` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `estoque_produtos_extras`
--

CREATE TABLE `estoque_produtos_extras` (
  `id` int(11) NOT NULL,
  `produto_extra_id` int(11) NOT NULL,
  `quantidade_inicial` int(11) NOT NULL,
  `quantidade_vendida` int(11) DEFAULT 0,
  `quantidade_disponivel` int(11) NOT NULL,
  `data_atualizacao` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE `eventos` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `data_inicio` date DEFAULT NULL,
  `data_fim` date DEFAULT NULL,
  `categoria` varchar(255) NOT NULL,
  `genero` varchar(100) NOT NULL,
  `local` varchar(255) DEFAULT NULL,
  `cep` varchar(20) DEFAULT NULL,
  `url_mapa` varchar(500) DEFAULT NULL,
  `logradouro` varchar(255) DEFAULT NULL,
  `numero` varchar(20) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `pais` varchar(50) DEFAULT 'Brasil',
  `regulamento` text DEFAULT NULL,
  `regulamento_arquivo` varchar(500) DEFAULT NULL COMMENT 'Caminho do arquivo de regulamento (PDF/DOC/DOCX)',
  `status` varchar(50) DEFAULT NULL,
  `organizador_id` int(11) NOT NULL,
  `taxa_setup` decimal(10,2) DEFAULT NULL,
  `percentual_repasse` decimal(5,2) DEFAULT NULL,
  `exibir_retirada_kit` tinyint(1) DEFAULT 0,
  `taxa_gratuitas` decimal(10,2) DEFAULT NULL,
  `taxa_pagas` decimal(10,2) DEFAULT NULL,
  `limite_vagas` int(11) DEFAULT NULL,
  `hora_fim_inscricoes` time DEFAULT NULL,
  `data_criacao` timestamp NULL DEFAULT current_timestamp(),
  `hora_inicio` time DEFAULT NULL,
  `hora_corrida` time DEFAULT NULL COMMENT 'Hora específica da corrida/prova',
  `data_realizacao` date DEFAULT NULL,
  `imagem` varchar(500) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `delete_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `eventos`
--

INSERT INTO `eventos` (`id`, `nome`, `descricao`, `data_inicio`, `data_fim`, `categoria`, `genero`, `local`, `cep`, `url_mapa`, `logradouro`, `numero`, `cidade`, `estado`, `pais`, `regulamento`, `regulamento_arquivo`, `status`, `organizador_id`, `taxa_setup`, `percentual_repasse`, `exibir_retirada_kit`, `taxa_gratuitas`, `taxa_pagas`, `limite_vagas`, `hora_fim_inscricoes`, `data_criacao`, `hora_inicio`, `hora_corrida`, `data_realizacao`, `imagem`, `deleted_at`, `deleted_by`, `delete_reason`) VALUES
(2, 'III CORRIDA SAUIM DE COLEIRA EM 2025', 'A III Corrida do Sauim-de-Coleira se caracteriza por um evento esportivo com três dimensões, integrando, assim três pilares importantes para toda sociedade: a saúde, a educação e o meio ambiente. O evento tem um caráter comemorativo e informativo, uma vez que nos faz lembrar do forte alerta e apelo para uma espécie endêmico da região e criticamente ameaçado, o Saguinus bicolor, popularmente conhecido como sauim-de-coleira.', '2025-07-16', '2025-07-16', 'corrida_rua', 'misto', 'Parque do Mindu - Manaus/AM', '69050-020', 'https://maps.google.com/maps?q=Parque+do+Mindu+Manaus', 'Av. Djalma Batista', '1200', 'Manaus', 'AM', 'Brasil', 'REGULAMENTO DA III CORRIDA SAUIM DE COLEIRA EM 2025\r\n\r\nINFORMAÇÕES GERAIS\r\nDATA: Domingo, 16 de julho de 2025\r\nLOCAL: Parque do Mindu - Manaus/AM\r\nHORÁRIOS DA LARGADA: 07:00\r\n\r\nDISTÂNCIAS: 5km e 10km\r\n\r\nINSCRIÇÕES\r\nPERÍODO: 01 de junho a 15 de julho de 2025\r\nTAXA DE INSCRIÇÃO: \r\n- Comunidade acadêmica: R$ 105,00\r\n- Público em geral: R$ 145,00\r\n\r\nKIT DO ATLETA\r\n- Camiseta oficial\r\n- Número de peito\r\n- Medalha de participação\r\n- Sacochila\r\n- Viseira\r\n\r\nPREMIAÇÃO\r\nTodos os atletas que terminarem a prova receberão medalha de participação.\r\n\r\nREGRAS GERAIS\r\n- Idade mínima: 14 anos para 5km e 17 anos para 10km\r\n- Uso obrigatório do chip de cronometragem\r\n- Não será permitido auxílio externo\r\n- Recomenda-se avaliação médica prévia', NULL, 'ativo', 2, 129.90, 85.00, 1, 2.50, 3.00, 1100, '23:59:00', '2025-07-16 15:03:02', '07:00:00', NULL, '2025-07-16', 'evento_2.png', NULL, NULL, NULL),
(3, 'III CORRIDA MICO LEAO', 'A III CORRIDA MICO LEAO se caracteriza por um evento esportivo com três dimensões, integrando, assim três pilares importantes para toda sociedade: a saúde, a educação e o meio ambiente. O evento tem um caráter comemorativo e informativo, uma vez que nos faz lembrar do forte alerta e apelo para uma espécie endêmico da região e criticamente ameaçado, o Saguinus bicolor, popularmente conhecido como sauim-de-coleira. Na ocasião de sua organização e execução o evento pretende divulgar os trabalhos desenvolvidos com esta espécie dentro do aspecto conservacionista, bem como mostrar que existem diferentes formas de ajudar neste processo de conservação, evitando a extinção dessa espécie tão valiosa para toda a sociedade. Paralelo a isso, o evento também proporcionará um momento de lazer para toda sociedade que busca a saúde e a qualidade de vida, por meio da realização de atividade física.', '2025-07-16', NULL, '', '', 'Manaus/AM', '', '', '', '', 'Recife', 'PE', 'Brasil', '', NULL, 'rascunho', 2, 129.90, NULL, 0, 2.50, NULL, 1100, NULL, '2025-07-16 15:03:02', '07:00:00', NULL, NULL, 'evento_3.png', '2025-10-23 01:10:12', 2, 'Exclusão solicitada pelo organizador'),
(4, 'Corrida do Saci', 'Uma corrida Folclorica', '2025-09-25', '2025-11-05', 'corrida_rua', '', 'Parque do Mindu', '', '', '', '', 'Manaus', 'AM', 'Brasil', 'o projeto deve ser um sistema que apos preencher o seus dados de cadastro o dono de um posto de combustíveis possa, gerar as paginas de seus LMC diários, o fluxo do sistema será assim, ao informar a capacidade de seus tanque a quantidade recebida de combustível na data inicial (data de recebimento ) e a data final, o sistema vai distribuir automaticamente com base em media diárias de vendas as saídas e preencher os LMC diários, o algoritmo de distribuição aleatória da venda não deve ser valores fixos, não trata-se de dividir 10000l / 15 dias, trata-se de simular vendas com quantidades próximas a media de modo a parecer estar preenchendo verdadeiramente as quantidades vendidas que de forma alguma seriam igual para cada dias, o importante é que ao final do período a distribuição dos 10000 litros ocorra no período vendido, no exemplo 15 dias', NULL, 'ativo', 2, 95.00, NULL, 0, NULL, NULL, 500, NULL, '2025-09-21 20:50:07', '08:00:00', NULL, NULL, 'evento_4.jpg', '2025-10-23 01:10:00', 2, 'Exclusão solicitada pelo organizador'),
(5, 'Corrida do macaco', 'uma macacada de corrida sem sentido de direção, mas que pode ser bastante divertida', '2025-09-25', '2025-11-15', 'corrida_rua', '', 'Parque 10 de Novembro', '', '', '', '', 'Manaus', 'AM', 'Brasil', 'o projeto deve ser um sistema que apos preencher o seus dados de cadastro o dono de um posto de combustíveis possa, gerar as paginas de seus LMC diários, o fluxo do sistema será assim, ao informar a capacidade de seus tanque a quantidade recebida de combustível na data inicial (data de recebimento ) e a data final, o sistema vai distribuir automaticamente com base em media diárias de vendas as saídas e preencher os LMC diários, o algoritmo de distribuição aleatória da venda não deve ser valores fixos, não trata-se de dividir 10000l / 15 dias, trata-se de simular vendas com quantidades próximas a media de modo a parecer estar preenchendo verdadeiramente as quantidades vendidas que de forma alguma seriam igual para cada dias, o importante é que ao final do período a distribuição dos 10000 litros ocorra no período vendido, no exemplo 15 dias', NULL, 'ativo', 2, 85.00, NULL, 0, NULL, NULL, 1000, NULL, '2025-09-21 21:22:26', '08:00:00', NULL, NULL, 'evento_5.jpg', '2025-10-23 01:09:54', 2, 'Exclusão solicitada pelo organizador'),
(6, 'I corrida sauim de coleira', 'A I Corrida Sauim de Coleira é um evento que surgiu por iniciativa dos acadêmicos da Escola Normal Superior (ENS) que são integrantes do Projeto Primatas (PPUEA) e que conhecem o status de conservação da espécie Saguinus bicolor (sauim-de-coleira), um primata endêmico de Manaus, Rio Preto da Eva e Itacoatiara, que está criticamente ameaçada, e que por isso, recebeu o título de espécie símbolo da cidade de Manaus.', '2025-10-24', '2025-10-24', 'corrida_rua', '', 'EST', '69050-560', '', 'Darcyr Vargas', '1200', 'Manaus', 'AM', 'Brasil', 'REGULAMENTO DA I CORRIDA SAUIM DE COLEIRA EM 2023\r\n\r\nINFORMAÇÕES GERAIS\r\nDATA\r\nDomingo, 10 de setembro de 2023\r\n\r\nLOCAL: Manaus - AM\r\n\r\n•	Largada na área interna da UEA-EST\r\n•	Av Darcy Vargas \r\n•	Av Maceió \r\n•	Av João Valério \r\n•	Av Djalma Batista \r\n•	Av Darcy Vargas\r\n•	Chegada na Área Interna da UEA-EST\r\n\r\nHORÁRIOS DA LARGADA:\r\n1 - Pelotão PCD 5km: 5h30;\r\n2 - Pelotão de Elite e Público Geral: 5 e 10 km: 5h45.\r\n\r\nDISTÂNCIAS\r\n5km e 10 km\r\nRegras de competição as Normas que regem o Regulamento Geral de Provas de Rua da Confederação Brasileira de Atletismo - CBAt.\r\n________________________________________\r\nCADASTRO E IDADE\r\nSerá considerada para efeito de cadastro e apuração de resultados, a idade dos inscritos e participantes em 31 de dezembro de 2023, conforme Norma da CBAt.\r\n\r\nINSCRIÇÕES\r\nPERÍODO DE INSCRIÇÃO: 28 de junho a 04 de setembro de 2023;\r\nENDEREÇO ELETRÔNICO: www.ticketsports.com.br\r\nTAXA DE INSCRIÇÃO: \r\nComunidade acadêmica da UEA, UFAM, IFAM e Universidades privadas: R$ 105,00 (Cento e cinco reais)\r\nPúblico em geral: R$ 145,00 (cento e quarenta e cinco reais)\r\n\r\nAs inscrições poderão ser encerradas antes do prazo previsto, sem aviso prévio, caso o limite seja alcançado, ou prorrogadas, conforme decisão da organização.\r\n________________________________________\r\nCORTESIAS PARA DOADORES VOLUNTÁRIOS DE SANGUE\r\n\r\nOs doadores voluntários de sangue poderão solicitar a isenção do pagamento de taxa de inscrição, em conformidade com a Lei Municipal nº 391/2014. \r\n(Incluído em cumprimento a Lei Municipal nº 391/2014).\r\nA Organização disponibiliza vagas gratuitas para os doadores, quantidade de 3% a ser definida de acordo com o número de inscritos, que apresentarem comprovantes de 3 (três) doações de Sangue para Homens e 2(duas) para Mulheres nos últimos 12 Meses, no seguinte Local e horário:\r\n\r\nHorário:\r\nDia 08/09/2023 - sexta-feira, (18h00 às 20h00), Local: Escola Normal Superior -ENS-UEA, Av. Djalma Baptista, 2.470, Chapada, CEP 69050-020, Manaus/AM.\r\nObs.: Inscrições presenciais e individuais.\r\n________________________________________\r\nCORTESIAS PARA PESSOAS COM DEFICIÊNCIA (PCD)\r\n\r\nAs Pessoas Portadoras de Deficiência poderão solicitar a isenção do pagamento de taxa de inscrição, em conformidade com o artigo a seguir da LEI N. 5.098, de 14 de janeiro de 2020.\r\n\r\nArt. 1.º Para fazer jus ao incentivo determinado por esta Lei, o competidor deverá atender aos seguintes critérios:\r\nI - Comprovar a deficiência através de laudo médico que ateste suas limitações;\r\nII - Aferir renda mensal de até 03 (três) salários-mínimos.\r\nA Organização disponibiliza vagas gratuitas para PCDs, a ser definida de acordo a apresentação dos comprovantes acima, no seguinte Local e Horário:\r\n\r\nDia 08/09/2023 - sexta-feira, (18h00 às 20h00), Local: Escola Normal Superior -ENS-UEA, Av. Djalma Baptista, 2.470, Chapada, CEP 69050-020, Manaus/AM.\r\nObs.: Inscrições presenciais e individuais.\r\n________________________________________\r\nO KIT DE PARTICIPAÇÃO \r\n\r\nO kit de participação é uma CORTESIA do evento, composto por 01 (uma) sacochila, 01 (uma) camiseta oficial da prova, 01(um) viseira, 01(uma) toalha, 01 (um) adesivo “Amigo do Sauim de Coleira”, número de peito, medalha (pós-prova) e possíveis quaisquer outros brindes, materiais e folders ofertados pelos patrocinadores e apoiadores da prova.\r\n\r\n \r\nO KIT DE PARTICIPAÇÃO DOADOR DE SANGUE VOLUNTÁRIO\r\nNúmero de Peito, adesivo de Amigo do Sauim de Coleira e medalha (pós-prova).\r\n\r\nRETIRADA DO KIT\r\nOs atletas inscritos receberão o ‘KIT CORTESIA do Atleta, somente nos dias e horários abaixo:\r\nDia 08/09/2023 - sexta-feira - (17h00 às 20h00).\r\nDia 09/09/2023 – sábado – (10h00 às 15h00).\r\nLocal: Escola Normal Superior -ENS-UEA, Av. Djalma Baptista, 2.470, Chapada, CEP 69050-020, Manaus/AM.\r\nObs.: Inscrições presenciais e individuais.\r\n________________________________________\r\nPREMIAÇÃO\r\nTodos os atletas inscritos que terminarem a prova nos tempos máximos previstos terão direito a uma medalha de participação.\r\n\r\n\r\nCATEGORIA INDIVIDUAL GERAL (CORRIDA DE 10 km)\r\n\r\nCLASSIFICAÇÃO MASCULINO/FEMININO\r\n1ºLugar - R$ 1.500,00 + Troféu\r\n2ºLugar - R$ 1.000,00 + Troféu\r\n3ºLugar - R$   700,00 + Troféu\r\n\r\nCATEGORIA INDIVIDUAL GERAL (CORRIDA DE 5 km)\r\n\r\nCLASSIFICAÇÃO MASCULINO/FEMININO\r\n1ºLugar - R$ 1.200,00 + Troféu\r\n2ºLugar - R$    800,00 + Troféu\r\n3ºLugar - R$    600,00 + Troféu\r\n\r\nCLASSIFICAÇÃO: VISUAIS 5km Masculino e Feminino\r\n1ºLugar - R$ 500,00 + Troféu\r\n2ºLugar - R$ 400,00 + Troféu\r\n3ºLugar - R$ 300,00 + Troféu\r\n\r\nCLASSIFICAÇÃO: Cadeirantes 5km Masculino e Feminino\r\n1ºLugar - R$ 500,00 + Troféu\r\n2ºLugar - R$ 400,00 + Troféu\r\n3ºLugar - R$ 300,00 + Troféu\r\n\r\nCLASSIFICAÇÃO: EQUIPES com Maior Número de Participantes que finalizarem a Prova.\r\n1ºLugar - R$ 1.200,00\r\n2ºLugar - R$    900,00\r\n3ºLugar - R$   700,00\r\n\r\nObs.1: Os primeiros (as) atletas dos percursos de 5 km e 10km deverão se dirigir ao podium logo após o término do tempo limite da prova. Serão premiados também no dia do evento as Equipes vencedoras da Prova.\r\n________________________________________\r\n\r\nMAPA/PERCURSO DA PROVA:\r\n\r\n \r\n\r\nREGULAMENTO\r\nA I Corrida Sauim de Coleira 2023 é uma realização da Universidade do Estado do Amazonas (UEA) com o apoio técnico da Assessoria MentedeCorredor e será regida pelas normas deste Regulamento.\r\n\r\nPROVA\r\nA I Corrida Sauim de Coleira 2023 será realizada no domingo, 10 de setembro de 2023, às 5h30, na cidade de Manaus/AM.\r\n\r\nA I Corrida Sauim de Coleira 2023 será disputada nas distâncias de 5 km e 10 km que terão como Regras de competição as Normas que regem o Regulamento Geral de Provas de Rua da Confederação Brasileira de Atletismo - CBAt.\r\n\r\nEste regulamento e o percurso serão divulgados no site: www.ticketsports.com.br \r\n\r\nA I Corrida Sauim de Coleira 2023 será disputada nas seguintes categorias masculino e feminino:\r\n\r\nI - INDIVIDUAL, onde cada atleta correrá 5 Km ou 10 Km;\r\n\r\nII – Pessoas que fazem uso da Cadeira de Rodas - Somente para os atletas que necessitam exclusivamente do uso de cadeira de roda esportiva, onde cada atleta correrá a percurso de 5 Km;\r\nDescrição: O Atleta que participa da competição com auxílio de cadeiras de rodas esportivas (somente cadeiras de 3 rodas) ou para competições, não sendo permitido o uso de cadeiras de uso social com duas rodas (diário), cadeiras motorizadas, handcycles ou auxílio de terceiros. É obrigatório o uso de capacete e luva. É de exclusiva e única responsabilidade do atleta a manutenção de sua cadeira de rodas em perfeitas condições de uso para a espécie de EVENTO prevista neste regulamento.\r\nObservação Técnica: A Categoria de Pessoas que fazem uso da Cadeira de Rodas é para atletas que fazem uso da cadeira de três rodas para competição esportiva, e, fazem uso das mãos para o movimento do equipamento através das rodas e NÃO com catracas, pedais manuais, ou nos pés, correntes ou elementos mecânicos e/ou motores elétricos ou à combustão que favoreçam o movimento da cadeira. Não é permitido o uso de Hand Bikes ou Handycicles, nome em inglês para “bicicleta de mão” ou “com as mãos”.\r\n\r\nIII – Pessoas com Deficiência Visual – Somente para os atletas com ausência total de visão e que poderão correr acompanhados por um guia, onde cada atleta correrá o percurso de 5 Km (OBS: O guia não competirá e deverá estar devidamente identificado);\r\n\r\nSerão desclassificados todos os atletas que não observarem a formação acima descrita.\r\n\r\nA Largada e Chegada da Prova será realizada no Estacionamento da Escola Superior de Tecnologia (EST-UEA), Av. Darcyr Vargas, 1200, Parque Dez de Novembro, CEP 69050-020, Manaus/AM, sob quaisquer condições climáticas, nos seguintes horários:\r\n\r\n1 - Pelotão de Pessoas com Deficiências 5km: 5h30; e\r\n2 - Pelotão de Elite e público Geral 5 km e 10 km: 5h45.\r\nO horário da largada da prova ficará sujeito a alterações em razão de problemas de ordem externa, tais como falhas de comunicação, segurança dos atletas, suspensão no fornecimento de energia, entre outros.\r\nPoderão participar da corrida, atletas de ambos os sexos, regularmente inscritos de acordo com o Regulamento Oficial da prova.\r\nINSCRIÇÕES\r\nPERÍODO DE INSCRIÇÃO: 28 de junho a 04 de setembro de 2023;\r\nENDEREÇO ELETRÔNICO: www.ticketsports.com.br\r\nTAXA DE INSCRIÇÃO: Comunidade acadêmica da UEA, UFAM. IFAM e Universidades Privadas: R$ 105,00 (Cento e cinco reais)\r\nPúblico em geral: R$ 145,00 (cento e quarenta e cinco reais)\r\nSerá considerada para efeito de cadastro, classificação por categoria e apuração de resultados, a idade que o atleta terá em 31 de dezembro de 2023, conforme Norma da CBAt.\r\nAs inscrições poderão ser encerradas antes do prazo previsto, sem aviso prévio, caso o limite seja alcançado, ou prorrogadas, conforme decisão da organização.\r\nAo se inscrever com seus dados para participar da I Corrida Sauim de Coleira os participantes concordam com o processamento dos dados pessoais de acordo com a Lei Geral de Proteção de Dados Pessoais – LGPD (Lei Federal n. 13.709/2018) e Política de Privacidade.\r\nOs dados e informações coletados estarão armazenados em ambiente seguro, observado o estado da técnica disponível, e somente poderão ser acessados por pessoas qualificadas e previamente autorizadas pela empresa promotora, em observância à legislação em vigor.\r\nA Promotora assume o compromisso de proteger os dados pessoais cadastrados, mantendo absoluta confidencialidade sobre tais informações, garantindo que, excetuados os casos previstos em lei, não serão vendidas nem cedidas a terceiros a título gratuito.\r\nCORTESIAS PARA DOADORES VOLUNTÁRIOS DE SANGUE\r\nOs doadores voluntários de sangue poderão solicitar a isenção do pagamento de taxa de inscrição, em conformidade com a Lei Municipal nº 391/2014. (Incluído em cumprimento a Lei Municipal nº 391/2014).\r\nA Organização disponibiliza vagas gratuitas para os doadores, quantidade de 3% a ser definida de acordo com o número de inscritos, que apresentarem comprovantes de 3 (três) doações de Sangue para Homens e 2(duas) para Mulheres nos últimos 12 Meses, no seguinte Local e Horário:\r\nDia 08/09/2023 - sexta-feira, (17h00 às 20h00) Local: Escola Normal Superior -ENS-UEA, Av. Djalma Baptista, 2.470, Chapada, CEP 69050-020, Manaus/AM. \r\nObs.: Inscrições presenciais e individuais, podendo ser efetuadas também por representantes legais através da apresentação de procuração. (Um representante por atleta).\r\nCortesias para Pessoas Portadoras de Deficiência\r\nAs Pessoas Portadoras de Deficiência poderão solicitar a isenção do pagamento de taxa de inscrição, em conformidade com o artigo a seguir da LEI N. 5.098, de 14 de janeiro de 2020\r\nArt. 2.º Para fazer jus ao incentivo determinado por esta Lei, o competidor deverá atender aos seguintes critérios:\r\nI - Comprovar a deficiência através de laudo médico que ateste suas limitações;\r\nII - Aferir renda mensal de até 03 (três) salários-mínimos.\r\nA Organização disponibiliza vagas gratuitas para PCDs, a ser definida de acordo a apresentação dos comprovantes acima, no seguinte Local e Horário:\r\nDia 08/09/2023 - sexta-feira, (17h00 às 20h00), Local: Escola Normal Superior -ENS-UEA, Av. Djalma Baptista, 2.470, Chapada, CEP 69050-020, Manaus/AM.\r\nObs.: Inscrições presenciais e individuais, podendo ser efetuadas também por representantes legais através da apresentação de procuração. (Um representante por atleta).\r\nATLETAS MENORES DE IDADE\r\nA idade mínima para participação é de 14 anos para a Prova de 5km e 17 anos para a Prova de 10km. \r\nO responsável deverá realizar a inscrição do atleta menor e o mesmo, deverá acompanhá-lo no dia do evento. Na ausência do pai ou responsável, será exigida a autorização por escrito, com assinatura autenticada e reconhecida e cópia do Documento de Identidade do pai ou responsável.\r\nKIT DO ATLETA\r\nO KIT DE PARTICIPAÇÃO Corrida\r\nO kit de participação é uma CORTESIA do evento, composto por 01 (uma) sacochila, 01 (uma) camiseta oficial da prova, 01(uma) viseira, 01(uma) toalha, 01 (um) adesivo amigo do Sauim de Coleira, número de peito (participação), medalha (pós-prova) e possíveis quaisquer outros brindes, materiais e folders ofertados pelos patrocinadores e apoiadores da prova.\r\nO KIT DE PARTICIPAÇÃO DOADOR DE SANGUE VOLUNTÁRIO\r\nNúmero de Peito, selo do Sauim de Coleira e medalha (pós-prova).\r\nRETIRADA DO KIT\r\nOs atletas inscritos receberão o ‘KIT do Atleta’ CORTESIA do evento, somente nos dias e horários abaixo:\r\nDia 08/09/2023 - sexta-feira, (16h00 às 20h00) e dia 09/09/2023 - sábado, (10h00 às 15h00), Local: Escola Normal Superior -ENS-UEA, Av. Djalma Baptista, 2.470, Chapada, CEP 69050-020, Manaus/AM.\r\nA entrega de kits será impreterivelmente realizada nos dias e horários acima. Não haverá entrega de kit de participação no dia do evento, nem após o mesmo.\r\nOs kits somente serão retirados pelo atleta mediante a apresentação de um documento oficial de identidade com foto, e do comprovante de inscrição enviado pela plataforma Ticket Sports para o e-mail do atleta. Estes comprovantes serão retidos na entrega do Kit.\r\nOBS.: O Tamanho da Camiseta (cortesia vinculada ao Kit do Atleta) será escolhida de acordo com a disponibilidade da Grade de Tamanhos disponíveis na plataforma oficial de Inscrições. Sendo limitadas de acordo com o estoque pré-estabelecido de tamanhos fabricados de forma antecipadas a data do evento. Portanto, a entrega da camiseta estará vinculada ao tamanho escolhido pelo atleta no ato de sua inscrição, não podendo em hipótese alguma, ser trocada no ato de sua entrega.\r\nObs.2: Os atletas IDOSOS não poderão enviar REPRESENTANTES para a retirada de seu kit, os mesmos só serão retirados perante a comprovação através de documento oficial com foto.\r\nNÚMERO DE IDENTIFICAÇÃO\r\nNo kit, o atleta receberá seu número de identificação. No dia da prova o número deverá ser afixado com alfinetes, à frente do uniforme de corrida. Ele é pessoal e intransferível, não podendo ser alterado ou rasurado, e possui 01(uma) senha que poderá ser utilizada pelo inscrito no serviço de guarda-volumes.\r\nCHIP DESCARTÁVEL\r\nOs chips serão entregues aos atletas por ocasião das entregas dos Kits, uma vez que o chip estará fixado no número de peito. Essa ação evitará as filas que se formam no dia da prova para retirada dos chips retornáveis. Caso tenha alguma dúvida, orienta-se a procurar o staff da prova que vai orientar na sua utilização. O uso do \"chip\" é obrigatório aos inscritos, acarretando a desclassificação se não o utilizar.\r\nA utilização do chip é de responsabilidade única do atleta, assim como as consequências de sua não utilização. A utilização inadequada do chip pelo (a) atleta acarreta a não marcação do tempo, isentando a Comissão Organizadora na divulgação dos resultados.\r\nO participante não poderá retirar o chip durante a prova, pois do contrário, ficará sem o direito de ter cronometrado o seu tempo.\r\n\r\nESTRUTURA DO EVENTO\r\nGUARDA-VOLUMES\r\nSerão colocados à disposição dos ATLETAS inscritos, guarda-volumes nas proximidades da largada e chegada. A ORGANIZAÇÃO não recomenda que sejam deixados dinheiro em espécie e objetos de valor no Guarda-Volumes tais como: relógios, roupas ou acessórios de alto valor, equipamentos eletrônicos, de som ou celulares, cheques, cartões de crédito, etc.\r\nA ORGANIZAÇÃO não se responsabilizará por qualquer objeto de valor deixado no Guarda-Volumes, uma vez que se trata de um serviço de cortesia da prova.\r\nPOSTOS DE HIDRATAÇÃO – ÁGUA MINERAL\r\nSerão disponibilizados ao longo do percurso, postos de hidratação com água mineral a cada 2 km aproximadamente, e 01 posto na largada e chegada da prova.\r\nBANHEIROS\r\nSerão disponibilizados banheiros químicos na região da largada e chegada.\r\nNão será permitido o uso do espaço e da estrutura da prova para a realização ou instalação de qualquer material de natureza comercial/promocional, que não seja dos patrocinadores do evento.\r\nNão será utilizado as instalações internas da Escola de Tecnologia da UEA.\r\nRESULTADOS\r\nA I Corrida do Sauim de Coleira 2023 terá seu resultado publicado no site oficial do evento: www.ticketsport.com.br e https://digitimeam.com.br/\r\nA organização não é responsável por resultados publicados em outros sites, que não o oficial da prova.\r\nPREMIAÇÃO\r\nTodos os atletas inscritos que terminarem as provas nos tempos máximos previstos terão direito a uma medalha de participação.\r\nCATEGORIA INDIVIDUAL GERAL (CORRIDA DE 10 km)\r\n\r\nCLASSIFICAÇÃO MASCULINO/FEMININO\r\n1ºLugar - R$ 1.500,00 + Troféu\r\n2ºLugar - R$ 1.000,00 + Troféu\r\n3ºLugar - R$    700,00 + Troféu\r\n\r\nCATEGORIA INDIVIDUAL GERAL (CORRIDA DE 5 km)\r\n\r\nCLASSIFICAÇÃO MASCULINO/FEMININO\r\n1ºLugar - R$ 1.200,00 + Troféu\r\n2ºLugar - R$    800,00 + Troféu\r\n3ºLugar - R$    600,00 + Troféu\r\n\r\nCLASSIFICAÇÃO: VISUAIS 5km Masculino e Feminino\r\n1ºLugar - R$ 500,00 + Troféu\r\n2ºLugar - R$ 400,00 + Troféu\r\n3ºLugar - R$ 300,00 + Troféu\r\n\r\nCLASSIFICAÇÃO: Cadeirantes 5km Masculino e Feminino\r\n1ºLugar - R$ 500,00 + Troféu\r\n2ºLugar - R$ 400,00 + Troféu\r\n3ºLugar - R$ 300,00 + Troféu\r\n\r\nCLASSIFICAÇÃO: EQUIPES com Maior Número de Participantes que finalizarem a Prova.\r\n1ºLugar - R$ 1.200,00 + Troféus\r\n2ºLugar - R$   900,00 + Troféus\r\n3ºLugar - R$   700,00 + Troféus\r\n\r\nObs.1: Os primeiros (as) atletas dos percursos de 5 km e 10km deverão se dirigir ao podium logo após o término do tempo limite da prova. Serão premiados também no dia do evento as Equipes vencedoras da Prova.\r\n\r\nREGRAS GERAIS\r\n\r\nA I Corrida Sauim de Coleira 2023 terá a duração máxima de 02h00, sendo que o atleta que não estiver dentro do tempo projetado (pace chart), em qualquer ponto do percurso, poderá ser convidado a retirar-se da competição, finalizando a prova neste ponto. \r\nTodos os atletas inscritos que terminarem as provas receberão uma medalha de participação.\r\nHaverá, para atendimento emergencial aos atletas, um serviço de apoio com ambulância para prestar o primeiro atendimento e eventuais remoções. A continuidade do atendimento médico, propriamente dito, tanto de emergência como de qualquer outra necessidade, será efetuado na REDE PÚBLICA, sob responsabilidade desta. \r\nA organização não tem responsabilidade sobre as despesas médicas que o atleta venha a ter durante ou após a prova.\r\nTodos os atletas e equipe organizadora (staff) estarão segurados por uma cobertura de acidentes pessoais no valor de R$ 20.000,00 (vinte mil reais), cada.\r\n\r\nO ATLETA ou seu (sua) acompanhante responsável poderá decidir por outro sistema de atendimento médico (remoção/transferência, hospital, serviço de emergência e médico entre outros) eximindo a organização de qualquer responsabilidade, direta ou indireta sobre as consequências desta decisão.\r\nDurante o percurso das provas, o atleta deverá manter-se atento ao fluxo de atletas e à sinalização do staff do local. Devido à perda de atenção com a paisagem ou outras coisas, o atleta deverá evitar correr muito próximo aos limites das laterais do percurso, o que pode gerar tropeços no meio-fio, ou nos objetos de segurança e demarcação.\r\nO atleta deverá percorrer o trajeto traçado pela organização ficando dentro dos limites impostos pelo gradeamento e sinalização, colocados ao longo do percurso, evitando assim, acidentes no contato com veículos, fora destes limites.\r\nA segurança da prova será garantida pelos órgãos competentes e haverá árbitros, fiscais e staffs para a orientação e fiscalização dos participantes.\r\nNão haverá reembolso, por parte da Organização, bem como de seus patrocinadores, apoiadores, e empresas participantes, de nenhum valor correspondente a danos a equipamentos e/ou acessórios utilizados pelos participantes no evento, independente de qual for o motivo, nem por qualquer extravio de materiais ou prejuízos materiais que por ventura os atletas venham a sofrer durante a participação da prova.\r\nRecomendamos rigorosa avaliação médica prévia e a realização de teste ergométrico a todos os participantes.\r\nSomente entrarão no Funil de Chegada os inscritos que estiverem portando seus números de peito bem visíveis.\r\nNão será permitido o auxílio externo aos participantes sob quaisquer hipóteses, a não ser por parte dos membros da Organização.\r\nQualquer reclamação sobre o resultado final da competição deverá ser feita, por escrito, até 30 minutos após a divulgação.\r\nAo participar deste evento, o(a) inscrito(a) assume a responsabilidade por seus dados fornecidos e aceita totalmente o Regulamento da Prova, participando por livre e espontânea vontade, sendo conhecedor de seu estado de saúde e da necessidade de consultar um médico antes da prova, para avaliar suas reais condições de participação.\r\nAo participar deste evento, o(a) inscrito(a) cede outorgando a permissão irrevogável à organização e seus concessionários, todos os direitos de utilização de sua imagem, voz e semelhança, inclusive direito de arena, para finalidades legítimas e promocionais, e em conexão com qualquer meio de comunicação e propaganda, assim como, autoriza o possível envio de mensagens informativas via: e-mails, cartas, torpedos SMS, e por outros meios, para seus telefones e endereços cadastrados, renunciando ao recebimento de qualquer renda que vier a ser auferida com direitos a televisão ou qualquer outro tipo de transmissão, para esta e próximas provas do mesmo evento, e de eventos congêneres, declinando de qualquer compensação financeira relativa ao evento, sendo conhecedor de seu formato e execução.\r\nPoderá o Organizador/Realizador suspender o evento por questões de segurança pública, atos públicos, vandalismo e/ou motivos de força maior, tais como ventos e chuvas fortes, que possam prejudicar o deslocamento dos atletas, podendo ocasionar acidentes, com sérios riscos a integridade física dos participantes.\r\nCaso haja necessidade de alguma modificação operacional ou escrita em algum item deste regulamento, a mesma será feita pela organização do evento.\r\nAs dúvidas ou omissões deste Regulamento serão dirimidas pela Comissão Organizadora de forma soberana, não cabendo recursos a estas decisões.\r\n\r\nDÚVIDAS\r\nDúvidas ou informações técnicas, esclarecer pelo e-mail: assessoriamentedecorredor@gmail.com\r\nInscrições até 04/09/23 23:59.', NULL, 'rascunho', 2, NULL, NULL, 0, NULL, NULL, 2000, '23:59:00', '2025-10-08 03:20:28', '06:00:00', NULL, NULL, 'evento_6.png', '2025-10-14 12:59:09', 2, 'Exclusão solicitada pelo organizador'),
(7, 'Corrida de MANAUS', 'Uma corrida tradicional para os filhos do amazonas que ocorre todo final de ano, venha celebrar a saúde', '2025-11-01', '2025-11-30', 'corrida_rua', 'misto', 'Parque 10 de Novembro', '69055-021', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3984.021964760189!2d-59.94484202633845!3d-3.0888098402503057!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x926c1b5b09b6d177%3A0xd223e5a312e8289e!2sPra%C3%A7a%20-%20Armando%20Mendes%2C%20Manaus%20-%20AM%2C%2069089-140!5e0!3m2!1ses-419!2sbr!4v1760448023280!5m2!1ses-419!2sbr', 'Av. Maneca Marques', '451', 'Manaus', 'AM', 'Brasil', 'Programacao\r\nModalidades\r\nLotes de inscricao\r\nCupons de desconto\r\nQuestionário\r\nProdutos\r\nTemplates de kit\r\nKits do evento\r\nRetirada dos Kits\r\nCamisas\r\nprodutos extras', NULL, 'rascunho', 2, 130.00, 85.00, 0, NULL, 700.00, 3000, '09:00:00', '2025-10-14 17:32:03', '09:00:00', NULL, NULL, 'evento_7.png', '2025-10-23 01:09:45', 2, 'Exclusão solicitada pelo organizador'),
(8, 'corrida do form', 'corrida teste de colocação de novo evento', '2025-11-01', '2025-11-30', 'corrida_rua', 'misto', 'Parque 10 de Novembro', '69055-021', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3984.021964760189!2d-59.94484202633845!3d-3.0888098402503057!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x926c1b5b09b6d177%3A0xd223e5a312e8289e!2sPra%C3%A7a%20-%20Armando%20Mendes%2C%20Manaus%20-%20AM%2C%2069089-140!5e0!3m2!1ses-419!2sbr!4v1760448023280!5m2!1ses-419!2sbr', 'Av. Maneca Marques', '451', 'Manaus', 'AM', 'Brasil', 'Programacao\r\nModalidades\r\nLotes de inscricao\r\nCupons de desconto\r\nQuestionário\r\nProdutos\r\nTemplates de kit\r\nKits do evento\r\nRetirada dos Kits\r\nCamisas\r\nprodutos extras', 'frontend/assets/docs/regulamentos/regulamento_8_1760454898.pdf', 'rascunho', 2, 130.00, 85.00, 0, NULL, 7.00, 4000, '17:00:00', '2025-10-14 19:14:58', '09:00:00', '09:00:00', '2025-11-04', 'evento_8.png', '2025-10-23 01:09:38', 2, 'Exclusão solicitada pelo organizador');

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
  `tipo` varchar(50) NOT NULL,
  `detalhes` varchar(255) DEFAULT NULL,
  `parcelamento` text DEFAULT NULL,
  `observacoes` text DEFAULT NULL
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
  `tipo_publico` enum('comunidade_academica','publico_geral') DEFAULT NULL,
  `kit_modalidade_id` int(11) DEFAULT NULL,
  `kit_id` int(11) DEFAULT NULL,
  `tamanho_camiseta` varchar(10) DEFAULT NULL,
  `tamanho_id` int(11) DEFAULT NULL,
  `produtos_extras_ids` text DEFAULT NULL,
  `numero_inscricao` varchar(50) DEFAULT NULL,
  `protocolo` varchar(50) DEFAULT NULL,
  `grupo_assessoria` varchar(100) DEFAULT NULL,
  `nome_equipe` varchar(100) DEFAULT NULL,
  `ordem_equipe` varchar(50) DEFAULT NULL,
  `posicao_legenda` varchar(50) DEFAULT NULL,
  `escolha_tamanho` varchar(20) DEFAULT NULL,
  `fisicamente_apto` tinyint(1) DEFAULT NULL,
  `apelido_peito` varchar(100) DEFAULT NULL,
  `contato_emergencia_nome` varchar(100) DEFAULT NULL,
  `contato_emergencia_telefone` varchar(20) DEFAULT NULL,
  `equipe_extra` varchar(100) DEFAULT NULL,
  `doc_comprovante_universidade` text DEFAULT NULL,
  `data_inscricao` timestamp NULL DEFAULT current_timestamp(),
  `status` enum('pendente','confirmada','cancelada') DEFAULT 'pendente',
  `status_pagamento` enum('pendente','pago','cancelado','rejeitado','processando') DEFAULT 'pendente',
  `valor_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `valor_desconto` decimal(10,2) DEFAULT 0.00,
  `cupom_aplicado` varchar(50) DEFAULT NULL,
  `data_pagamento` datetime DEFAULT NULL,
  `forma_pagamento` varchar(50) DEFAULT NULL,
  `parcelas` int(11) DEFAULT 1,
  `seguro_contratado` tinyint(1) DEFAULT 0,
  `external_reference` varchar(100) DEFAULT NULL,
  `preference_id` varchar(100) DEFAULT NULL,
  `colocacao` int(11) DEFAULT NULL,
  `aceite_termos` tinyint(1) DEFAULT 0,
  `data_aceite_termos` timestamp NULL DEFAULT NULL,
  `versao_termos` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `inscricoes`
--

INSERT INTO `inscricoes` (`id`, `usuario_id`, `evento_id`, `modalidade_evento_id`, `lote_inscricao_id`, `tipo_publico`, `kit_modalidade_id`, `kit_id`, `tamanho_camiseta`, `tamanho_id`, `produtos_extras_ids`, `numero_inscricao`, `protocolo`, `grupo_assessoria`, `nome_equipe`, `ordem_equipe`, `posicao_legenda`, `escolha_tamanho`, `fisicamente_apto`, `apelido_peito`, `contato_emergencia_nome`, `contato_emergencia_telefone`, `equipe_extra`, `doc_comprovante_universidade`, `data_inscricao`, `status`, `status_pagamento`, `valor_total`, `valor_desconto`, `cupom_aplicado`, `data_pagamento`, `forma_pagamento`, `parcelas`, `seguro_contratado`, `external_reference`, `preference_id`, `colocacao`, `aceite_termos`, `data_aceite_termos`, `versao_termos`) VALUES
(1, 4, 2, 4, NULL, NULL, NULL, NULL, 'M', NULL, '[{\"id\":3,\"nome\":\"Combo VIP\",\"valor\":70}]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-10-14 17:38:33', 'pendente', 'pendente', 185.00, 0.00, NULL, NULL, NULL, 1, 0, 'MINDRUNNER_1760462048_4', '24368125-77abbfb3-36ab-438a-ba97-957d714947b2', NULL, 0, NULL, NULL);

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
  `quantidade` int(11) DEFAULT 1,
  `status` varchar(30) DEFAULT 'pendente',
  `data_compra` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `inscricoes_produtos_extras`
--

INSERT INTO `inscricoes_produtos_extras` (`id`, `inscricao_id`, `produto_extra_evento_id`, `quantidade`, `status`, `data_compra`) VALUES
(5, 1, 3, 1, 'pendente', '2025-10-14 13:38:33');

-- --------------------------------------------------------

--
-- Estrutura para tabela `kits_eventos`
--

CREATE TABLE `kits_eventos` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `evento_id` int(11) NOT NULL,
  `modalidade_evento_id` int(11) NOT NULL,
  `kit_template_id` int(11) DEFAULT NULL,
  `valor` decimal(10,2) NOT NULL,
  `foto_kit` varchar(255) DEFAULT NULL,
  `disponivel_venda` tinyint(1) DEFAULT 1,
  `preco_calculado` decimal(10,2) DEFAULT 0.00,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `kits_eventos`
--

INSERT INTO `kits_eventos` (`id`, `nome`, `descricao`, `evento_id`, `modalidade_evento_id`, `kit_template_id`, `valor`, `foto_kit`, `disponivel_venda`, `preco_calculado`, `ativo`, `data_criacao`, `updated_at`) VALUES
(3, 'Kit Promocional - CORRIDA 5KM', 'Kit Promocional aplicado em CORRIDA 5KM', 3, 17, 2, 80.00, NULL, 1, 80.00, 1, '2025-07-25 08:27:40', '2025-09-03 17:26:53'),
(5, 'Kit Atleta - CORRIDA 10KM', 'Kit Atleta aplicado em CORRIDA 10KM', 2, 1, 1, 149.50, 'kit_template_Kit Atleta.png', 1, 149.50, 0, '2025-07-26 20:46:00', '2025-10-08 03:28:11'),
(6, 'Kit Atleta - CORRIDA 5KM', 'Kit Atleta aplicado em CORRIDA 5KM', 2, 3, 1, 149.50, 'kit_template_Kit Atleta.png', 1, 149.50, 0, '2025-07-26 20:46:00', '2025-09-13 20:30:13'),
(7, 'Kit Atleta - CORRIDA 10KM', 'Kit Atleta aplicado em CORRIDA 10KM', 3, 9, 1, 149.50, 'kit_template_Kit Atleta.png', 1, 149.50, 1, '2025-07-26 20:46:00', '2025-09-03 17:29:15'),
(8, 'Kit Atleta - CORRIDA 5KM', 'Kit Atleta aplicado em CORRIDA 5KM', 2, 11, 1, 149.50, 'kit_template_Kit Atleta.png', 1, 149.50, 1, '2025-07-26 20:46:00', '2025-07-26 20:46:00'),
(9, 'Kit Promocional - CORRIDA 5KM', 'Kit Promocional aplicado em CORRIDA 5KM | KIT COMPLETO - FAMÍLIA 1', 0, 0, 0, 80.00, 'kit_template_Kit Promocional.png', 1, 80.00, 0, '2025-09-03 16:41:42', '2025-09-04 21:58:51'),
(10, 'KIT Famila - CORRIDA 5KM ', 'KIT Famila aplicado em CORRIDA 5KM ', 2, 5, 3, 32.50, 'kit_template_KIT Famila.png', 1, 32.50, 0, '2025-09-03 17:48:53', '2025-10-08 03:39:13');

-- --------------------------------------------------------

--
-- Estrutura para tabela `kit_modalidade_evento`
--

CREATE TABLE `kit_modalidade_evento` (
  `kit_id` int(11) NOT NULL,
  `modalidade_evento_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `kit_produtos`
--

CREATE TABLE `kit_produtos` (
  `id` int(11) NOT NULL,
  `kit_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL DEFAULT 1,
  `ordem` int(11) NOT NULL DEFAULT 0,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(9, 6, 6, 1, 1, 0, '2025-09-13 20:30:13'),
(10, 6, 1, 1, 2, 0, '2025-09-13 20:30:13'),
(11, 6, 2, 1, 3, 0, '2025-09-13 20:30:13'),
(12, 6, 4, 1, 4, 0, '2025-09-13 20:30:13'),
(13, 6, 3, 1, 5, 0, '2025-09-13 20:30:13'),
(14, 6, 9, 1, 6, 0, '2025-09-13 20:30:13'),
(15, 7, 6, 1, 1, 1, '0000-00-00 00:00:00'),
(16, 7, 1, 1, 2, 1, '0000-00-00 00:00:00'),
(17, 7, 2, 1, 3, 1, '0000-00-00 00:00:00'),
(18, 7, 4, 1, 4, 1, '0000-00-00 00:00:00'),
(19, 7, 3, 1, 5, 1, '0000-00-00 00:00:00'),
(20, 7, 9, 1, 6, 1, '0000-00-00 00:00:00'),
(21, 8, 6, 1, 1, 1, '0000-00-00 00:00:00'),
(22, 8, 1, 1, 2, 1, '0000-00-00 00:00:00'),
(23, 8, 2, 1, 3, 1, '0000-00-00 00:00:00'),
(24, 8, 4, 1, 4, 1, '0000-00-00 00:00:00'),
(25, 8, 3, 1, 5, 1, '0000-00-00 00:00:00'),
(26, 8, 9, 1, 6, 1, '0000-00-00 00:00:00'),
(27, 9, 6, 1, 1, 0, '2025-09-03 17:27:37'),
(28, 10, 1, 1, 1, 0, '2025-10-08 03:39:13');

-- --------------------------------------------------------

--
-- Estrutura para tabela `kit_templates`
--

CREATE TABLE `kit_templates` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `preco_base` decimal(10,2) DEFAULT 0.00,
  `foto_kit` varchar(255) DEFAULT NULL,
  `disponivel_venda` tinyint(1) DEFAULT 1,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `kit_templates`
--

INSERT INTO `kit_templates` (`id`, `nome`, `descricao`, `preco_base`, `foto_kit`, `disponivel_venda`, `ativo`, `data_criacao`, `updated_at`) VALUES
(1, 'Kit Atleta', 'Kit completo com camisa, viseira, boné, medalha, troféu, garrafa, número, selo, número de peito e chip', 149.50, 'kit_template_Kit Atleta.png', 1, 1, '2025-07-19 23:00:52', '2025-10-10 17:31:17'),
(2, 'Kit Promocional', 'Kit promocional com camisa, medalha, número de peito e chip', 80.00, 'kit_template_Kit Promocional.png', 1, 1, '2025-07-19 23:00:52', '2025-10-10 17:31:17'),
(3, 'KIT Famila', 'camisa com tecido especial para sudorese', 32.50, 'kit_template_KIT Famila.png', 1, 1, '2025-09-02 19:16:47', '2025-10-10 17:31:17'),
(4, 'Kit', 'Kit completo', 120.00, 'frontend/assets/img/kits/kit_template_Kit.jpeg', 1, 1, '2025-10-08 03:27:43', '2025-10-10 17:31:17');

-- --------------------------------------------------------

--
-- Estrutura para tabela `kit_template_produtos`
--

CREATE TABLE `kit_template_produtos` (
  `id` int(11) NOT NULL,
  `kit_template_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` int(11) DEFAULT 1,
  `ordem` int(11) DEFAULT 0,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE `leads_organizadores` (
  `id` int(11) NOT NULL,
  `nome_completo` varchar(150) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `empresa` varchar(100) NOT NULL,
  `regiao` varchar(50) NOT NULL,
  `modalidade_esportiva` varchar(100) NOT NULL,
  `quantidade_eventos` varchar(50) NOT NULL,
  `nome_evento` varchar(200) NOT NULL,
  `regulamento` varchar(100) NOT NULL,
  `indicacao` varchar(150) DEFAULT NULL,
  `status` enum('novo','contatado','convertido','descartado') DEFAULT 'novo',
  `data_criacao` timestamp NULL DEFAULT current_timestamp(),
  `data_contato` timestamp NULL DEFAULT NULL,
  `observacoes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `leads_organizadores`
--

INSERT INTO `leads_organizadores` (`id`, `nome_completo`, `email`, `telefone`, `empresa`, `regiao`, `modalidade_esportiva`, `quantidade_eventos`, `nome_evento`, `regulamento`, `indicacao`, `status`, `data_criacao`, `data_contato`, `observacoes`) VALUES
(1, 'EUDIMACI BARBOZA DE LIRA', 'eudimaci08@yahoo.com.br', '92982027654', 'EBL Eventos Esportivos', 'AM', 'corrida-rua', '1', 'I Corrida Sauim de Coleira', 'sim', 'Amigos', 'novo', '2025-09-26 21:15:09', NULL, NULL),
(2, 'EUDIMACI BARBOZA DE LIRA', 'eudimaci08@yahoo.com.br', '92982027654', 'Mente de Corredor', 'AM', 'corrida-rua', '2-4', 'I Corrida Sauim de Coleira', 'sim', 'Amigos', 'novo', '2025-10-08 03:43:28', NULL, NULL),
(3, 'EUDIMACI BARBOZA DE LIRA', 'eudimaci08@yahoo.com.br', '92982027654', 'EBL Eventos Esportivos', 'AM', 'corrida-rua', '2-4', 'III Corrida Sauim de Coleira', 'sim', 'Amigos', 'novo', '2025-10-21 00:51:39', NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `logs_admin`
--

CREATE TABLE `logs_admin` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `acao` varchar(255) DEFAULT NULL,
  `data_acao` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE `lotes_inscricao` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `modalidade_id` int(11) NOT NULL,
  `numero_lote` int(11) NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `preco_por_extenso` varchar(255) DEFAULT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL,
  `vagas_disponiveis` int(11) DEFAULT NULL,
  `taxa_servico` decimal(10,2) DEFAULT 0.00,
  `quem_paga_taxa` enum('organizador','participante') DEFAULT 'participante',
  `idade_min` int(11) DEFAULT 0,
  `idade_max` int(11) DEFAULT 100,
  `desconto_idoso` tinyint(1) DEFAULT 0,
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `lotes_inscricao`
--

INSERT INTO `lotes_inscricao` (`id`, `evento_id`, `modalidade_id`, `numero_lote`, `preco`, `preco_por_extenso`, `data_inicio`, `data_fim`, `vagas_disponiveis`, `taxa_servico`, `quem_paga_taxa`, `idade_min`, `idade_max`, `desconto_idoso`, `ativo`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 1, 105.00, 'Valor muito alto reales', '2024-07-16', '2024-08-15', 100, 6.50, 'participante', 14, 100, 0, 1, '2025-07-20 23:37:33', '2025-09-06 00:23:13'),
(2, 2, 2, 1, 115.00, 'Setenta e cinco reais', '2024-07-16', '2024-08-15', 100, 6.50, 'participante', 14, 100, 0, 1, '2025-07-20 23:37:33', '2025-09-05 22:40:28'),
(3, 2, 3, 1, 105.00, 'Valor muito alto reales', '2024-07-16', '2024-08-15', 100, 6.50, 'participante', 14, 100, 0, 1, '2025-07-20 23:37:33', '2025-09-06 00:23:31'),
(4, 2, 4, 1, 115.00, 'Setenta e cinco reais', '2024-07-16', '2024-08-15', 100, 6.50, 'participante', 14, 100, 0, 1, '2025-07-20 23:37:33', '2025-09-05 22:40:28'),
(5, 2, 5, 1, 115.00, 'Setenta e cinco reais', '2024-07-16', '2024-08-15', 100, 6.50, 'participante', 14, 100, 0, 1, '2025-07-20 23:37:33', '2025-09-05 22:40:28'),
(16, 2, 20, 1, 60.00, 'Sessenta reales', '2025-10-24', '2026-10-23', 200, 5.00, 'participante', 18, 100, 1, 1, '2025-10-23 01:36:46', '2025-10-23 01:36:46');

-- --------------------------------------------------------

--
-- Estrutura para tabela `modalidades`
--

CREATE TABLE `modalidades` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `distancia` varchar(50) NOT NULL,
  `tipo_prova` enum('corrida','caminhada','ambos') DEFAULT 'corrida',
  `limite_vagas` int(11) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `modalidades`
--

INSERT INTO `modalidades` (`id`, `evento_id`, `categoria_id`, `nome`, `descricao`, `distancia`, `tipo_prova`, `limite_vagas`, `ativo`, `data_criacao`, `updated_at`) VALUES
(1, 2, 1, 'CORRIDA 10KM', 'Corrida de 10km para público geral', '10km', 'corrida', NULL, 1, '2025-08-25 22:11:01', '2025-09-06 00:17:49'),
(2, 2, 2, 'CORRIDA 10KM | KIT COMPLETO', 'Corrida de 10km com kit completo para público geral', '10km', 'corrida', NULL, 1, '2025-08-25 22:11:01', '2025-08-25 22:11:01'),
(3, 2, 1, 'CORRIDA 5KM', 'Corrida de 5km para público geral', '5km', 'corrida', NULL, 1, '2025-08-25 22:11:01', '2025-09-06 00:18:00'),
(4, 2, 2, 'CORRIDA 5KM | KIT COMPLETO', 'Corrida de 5km com kit completo para público geral', '5km', 'corrida', NULL, 1, '2025-08-25 22:11:01', '2025-08-25 22:11:01'),
(5, 2, 4, 'CORRIDA 5KM | KIT COMPLETO - FAMÍLIA 1', 'Corrida de 5km familiar para público geral', '5km', 'ambos', NULL, 1, '2025-08-25 22:11:01', '2025-08-25 22:11:01'),
(19, 3, 12, 'CORRIDA 10KM', 'Um acorrida para todas as pessoas', '10KM', 'corrida', 1000, 1, '2025-09-23 01:44:03', '2025-09-23 01:44:03'),
(20, 2, 1, 'Caminhada  com Bebês.', 'Caminhada mães e bebês com carinho', '2 km', 'caminhada', 200, 1, '2025-10-23 01:31:25', '2025-10-23 01:31:25');

-- --------------------------------------------------------

--
-- Estrutura para tabela `organizadores`
--

CREATE TABLE `organizadores` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `empresa` varchar(100) DEFAULT NULL,
  `regiao` varchar(50) DEFAULT NULL,
  `modalidade_esportiva` varchar(100) DEFAULT NULL,
  `quantidade_eventos` varchar(50) DEFAULT NULL,
  `regulamento` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE `pagamentos` (
  `id` int(11) NOT NULL,
  `inscricao_id` int(11) NOT NULL,
  `forma_pagamento` varchar(100) DEFAULT NULL,
  `data_pagamento` datetime DEFAULT NULL,
  `valor_total` decimal(10,2) DEFAULT NULL,
  `valor_desconto` decimal(10,2) DEFAULT NULL,
  `valor_pago` decimal(10,2) DEFAULT NULL,
  `taxa_participante` decimal(10,2) DEFAULT NULL,
  `valor_repasse` decimal(10,2) DEFAULT NULL,
  `status` enum('pendente','pago','cancelado') DEFAULT 'pendente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pagamentos_ml`
--

CREATE TABLE `pagamentos_ml` (
  `id` int(11) NOT NULL,
  `inscricao_id` int(11) NOT NULL,
  `payment_id` varchar(100) DEFAULT NULL,
  `preference_id` varchar(100) DEFAULT NULL,
  `init_point` text DEFAULT NULL COMMENT 'URL de inicialização do Mercado Pago',
  `status` enum('pending','approved','rejected','cancelled','in_process','refunded') DEFAULT 'pending',
  `status_detail` varchar(100) DEFAULT NULL,
  `transaction_amount` decimal(10,2) DEFAULT NULL,
  `payment_method_id` varchar(50) DEFAULT NULL,
  `installments` int(11) DEFAULT 1,
  `payer_email` varchar(255) DEFAULT NULL,
  `payer_document` varchar(20) DEFAULT NULL,
  `dados_pagamento` text DEFAULT NULL,
  `data_criacao` timestamp NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `papeis`
--

CREATE TABLE `papeis` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE `participante` (
  `usuario_id` int(11) NOT NULL,
  `total_corridas` int(11) DEFAULT 0,
  `total_km` decimal(8,2) DEFAULT 0.00,
  `melhor_tempo` time DEFAULT NULL,
  `camiseta_tamanho` varchar(10) DEFAULT NULL,
  `modalidade_preferida` varchar(100) DEFAULT NULL,
  `aceita_emails` tinyint(1) DEFAULT 1,
  `data_primeira_corrida` date DEFAULT NULL,
  `data_ultima_corrida` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `payments_ml`
--

CREATE TABLE `payments_ml` (
  `id` int(11) NOT NULL,
  `inscricao_id` int(11) NOT NULL,
  `preference_id` varchar(100) NOT NULL,
  `payment_id` varchar(100) DEFAULT NULL,
  `init_point` text NOT NULL,
  `status` enum('pendente','pago','cancelado','rejeitado','processando') DEFAULT 'pendente',
  `data_criacao` timestamp NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `dados_pagamento` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `valor_pago` decimal(10,2) DEFAULT NULL,
  `metodo_pagamento` varchar(50) DEFAULT NULL,
  `parcelas` int(11) DEFAULT 1,
  `taxa_ml` decimal(10,2) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `inscricao_id` int(11) NOT NULL,
  `numero_pedido` varchar(100) DEFAULT NULL,
  `data_pedido` date DEFAULT NULL,
  `hora_pedido` time DEFAULT NULL,
  `status_pedido` varchar(100) DEFAULT NULL,
  `detalhe_status` text DEFAULT NULL,
  `responsavel_nome` varchar(100) DEFAULT NULL,
  `responsavel_email` varchar(255) DEFAULT NULL,
  `responsavel_celular` varchar(20) DEFAULT NULL,
  `comentario` text DEFAULT NULL,
  `origem` varchar(100) DEFAULT NULL,
  `campanha` varchar(100) DEFAULT NULL,
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
-- Estrutura para tabela `produtos`
--

CREATE TABLE `produtos` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `preco` decimal(10,2) DEFAULT 0.00,
  `disponivel_venda` tinyint(1) DEFAULT 1,
  `foto_produto` varchar(255) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `produtos`
--

INSERT INTO `produtos` (`id`, `nome`, `descricao`, `preco`, `disponivel_venda`, `foto_produto`, `ativo`, `data_criacao`, `updated_at`) VALUES
(1, 'Camiseta', 'Camiseta oficial do evento', 25.00, 1, NULL, 1, '2025-07-19 19:26:54', '2025-07-20 00:34:42'),
(2, 'Medalha', 'Medalha de participação', 15.00, 1, NULL, 1, '2025-07-19 19:26:54', '2025-07-20 00:34:42'),
(3, 'Número de peito', 'Número de identificação para a prova', 5.00, 1, NULL, 1, '2025-07-19 19:26:54', '2025-07-20 00:34:42'),
(4, 'Chip de cronometragem', 'Chip para cronometragem da prova', 30.00, 1, NULL, 1, '2025-07-19 19:26:54', '2025-07-20 00:34:42'),
(5, 'Mochila', 'Mochila do evento', 20.00, 1, NULL, 1, '2025-07-19 19:26:54', '2025-07-20 00:34:42'),
(6, 'Boné', 'Boné oficial do evento', 20.00, 1, NULL, 1, '2025-07-19 19:26:54', '2025-07-20 00:34:42'),
(7, 'Garrafa', 'Garrafa de água do evento', 20.00, 1, NULL, 1, '2025-07-19 19:26:54', '2025-07-20 00:34:42'),
(8, 'Viseira', 'Viseira oficial do evento', 20.00, 1, NULL, 1, '2025-07-19 21:31:25', '2025-07-20 00:34:42'),
(9, 'Troféu', 'Troféu para vencedores', 20.00, 1, NULL, 1, '2025-07-19 21:31:25', '2025-07-20 00:34:42'),
(10, 'Selo (adesivo)', 'Selo promocional do evento', 20.00, 1, NULL, 1, '2025-07-19 21:31:25', '2025-07-20 00:34:42'),
(11, 'Kit promocional', 'Kit promocional com produtos especiais', 20.00, 1, NULL, 1, '2025-07-19 21:31:25', '2025-07-20 00:34:42');

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos_extras`
--

CREATE TABLE `produtos_extras` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `valor` decimal(10,2) NOT NULL,
  `disponivel_venda` tinyint(1) DEFAULT 1,
  `categoria` enum('vestuario','acessorio','seguro','outros') DEFAULT 'outros',
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `produtos_extras`
--

INSERT INTO `produtos_extras` (`id`, `evento_id`, `nome`, `descricao`, `valor`, `disponivel_venda`, `categoria`, `ativo`, `data_criacao`, `updated_at`) VALUES
(2, 2, 'Kit Camisa + Medalha', 'Kit adicional com camisa e medalha para 5KM', 20.00, 1, 'outros', 1, '2025-07-19 19:26:54', '2025-10-10 19:05:50'),
(3, 2, 'Combo VIP', 'Boné e Camisa', 70.00, 1, 'outros', 1, '2025-10-13 12:45:47', '2025-10-13 15:48:08'),
(4, 2, 'Camisa para os bebês', 'Camisa para os bebês que estarão fazendo a caminhada com os seus Pais.', 30.00, 1, 'outros', 1, '2025-10-23 01:57:23', '2025-10-23 01:57:23');

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos_extras_backup`
--

CREATE TABLE `produtos_extras_backup` (
  `id` int(11) NOT NULL DEFAULT 0,
  `produto_id` int(11) DEFAULT NULL,
  `evento_id` int(11) NOT NULL,
  `modalidade_evento_id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `valor` decimal(10,2) NOT NULL,
  `categoria` enum('vestuario','acessorio','seguro','outros') DEFAULT 'outros',
  `estoque` int(11) DEFAULT -1,
  `aplicavel_categorias` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NULL DEFAULT current_timestamp()
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
  `nome_produto` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `valor` decimal(10,2) NOT NULL,
  `foto_produto` varchar(255) DEFAULT NULL,
  `disponivel_venda` tinyint(1) DEFAULT 1,
  `vagas_disponiveis` int(11) NOT NULL DEFAULT 0,
  `vagas_vendidas` int(11) NOT NULL DEFAULT 0,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE `produto_extra_produtos` (
  `id` int(11) NOT NULL,
  `produto_extra_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` int(11) DEFAULT 1,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `produto_extra_produtos`
--

INSERT INTO `produto_extra_produtos` (`id`, `produto_extra_id`, `produto_id`, `quantidade`, `ativo`, `data_criacao`) VALUES
(6, 3, 1, 1, 1, '2025-10-13 15:48:08'),
(7, 3, 6, 1, 1, '2025-10-13 15:48:08'),
(8, 3, 8, 1, 1, '2025-10-13 15:48:08'),
(9, 4, 1, 1, 1, '2025-10-23 01:57:23');

-- --------------------------------------------------------

--
-- Estrutura para tabela `programacao_evento`
--

CREATE TABLE `programacao_evento` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `tipo` enum('percurso','horario_largada','atividade_adicional') NOT NULL,
  `titulo` varchar(255) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `ordem` int(11) DEFAULT 0,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Estrutura para tabela `questionario_evento`
--

CREATE TABLE `questionario_evento` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `modalidade_id` int(11) NOT NULL,
  `tipo` enum('pergunta','campo') NOT NULL,
  `tipo_resposta` varchar(30) DEFAULT NULL,
  `mascara` varchar(30) DEFAULT NULL,
  `texto` text NOT NULL,
  `obrigatorio` tinyint(1) DEFAULT 0,
  `ordem` int(11) DEFAULT 0,
  `ativo` tinyint(1) DEFAULT 1,
  `status_site` enum('publicada','rascunho') DEFAULT 'publicada',
  `status_grupo` enum('publicada','rascunho') DEFAULT 'publicada',
  `data_criacao` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `questionario_evento`
--

INSERT INTO `questionario_evento` (`id`, `evento_id`, `modalidade_id`, `tipo`, `tipo_resposta`, `mascara`, `texto`, `obrigatorio`, `ordem`, `ativo`, `status_site`, `status_grupo`, `data_criacao`) VALUES
(1, 2, 0, 'pergunta', NULL, NULL, 'Você conhece qual é o animal que é o Símbolo de Manaus?', 0, 1, 1, 'publicada', 'publicada', '2025-07-17 01:05:10'),
(2, 2, 0, 'pergunta', NULL, NULL, 'Você sabe o que pode acontecer se o Sauim-de-coleira for extinto?', 0, 1, 1, 'publicada', 'publicada', '2025-07-17 01:05:10'),
(6, 2, 0, 'campo', NULL, NULL, 'Em caso de incidentes a organização entra em contato com? (*Informe nome e DDD fone)', 1, 1, 1, 'publicada', 'publicada', '2025-07-17 01:05:10'),
(7, 2, 0, 'campo', NULL, NULL, 'Informe primeiro nome ou apelido para número de peito', 0, 1, 1, 'publicada', 'publicada', '2025-07-17 01:05:10'),
(12, 2, 0, 'campo', NULL, NULL, 'Declara que este participante está apto fisicamente e leu o REGULAMENTO estando de acordo para participar do evento?', 1, 1, 1, 'publicada', 'publicada', '2025-07-17 01:20:07');

-- --------------------------------------------------------

--
-- Estrutura para tabela `questionario_evento_modalidade`
--

CREATE TABLE `questionario_evento_modalidade` (
  `id` int(11) NOT NULL,
  `questionario_evento_id` int(11) NOT NULL,
  `modalidade_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `status` enum('pendente','realizado','cancelado') DEFAULT 'pendente'
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
  `local_retirada` text DEFAULT NULL,
  `endereco_completo` text DEFAULT NULL,
  `instrucoes_retirada` text DEFAULT NULL,
  `retirada_terceiros` text DEFAULT NULL,
  `documentos_necessarios` text DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `retirada_kits_evento`
--

INSERT INTO `retirada_kits_evento` (`id`, `evento_id`, `data_retirada`, `horario_inicio`, `horario_fim`, `local_retirada`, `endereco_completo`, `instrucoes_retirada`, `retirada_terceiros`, `documentos_necessarios`, `ativo`, `data_criacao`) VALUES
(1, 2, '2023-10-23', '10:00:00', '20:00:00', 'Reitoria da Universidade do Estado do Amazonas-UEA', 'Reitoria da Universidade do Estado do Amazonas-UEA, Av. Djalma Baptista, 3578, Flores, CEP 69050-010, Manaus/AM.', 'Retirada de kits no local especificado', 'Os Kits somente serão entregues pessoalmente para os participantes inscritos, com a apresentação de documento de identidade, que comprove a identificação do mesmo. Nos casos excepcionais, poderão ser retirados Kits por terceiros, contudo com a apresentação de documento de identidade da pessoa inscrita.', 'Documento de identidade original', 1, '2025-07-17 01:05:50'),
(2, 2, '2025-09-20', '22:10:00', '22:10:00', 'CMPM-Colegio Militar-Av. Codajás, s/n - Petrópolis, Manaus - AM, 69065-130', NULL, 'Trazer documento com foto e numero de CPF', NULL, 'CPF,CNH', 1, '2025-09-07 01:10:42'),
(3, 6, '2025-10-29', '10:00:00', '20:00:00', 'rua das flores', NULL, 'apresentar documento com foto', NULL, 'CPF ou documento com foto', 1, '2025-10-08 21:24:44'),
(4, 6, '2025-10-25', '10:00:00', '17:00:00', 'UEA Normal superior', NULL, 'Levar documento com foto', NULL, 'CPF', 1, '2025-10-08 21:26:47'),
(5, 2, '2026-10-23', '14:00:00', '20:00:00', 'Escola Normal Superior da UEA', NULL, 'Apresentar documento legível. ', NULL, 'Identidade com foto e comprovante de inscrição', 1, '2025-10-23 01:52:52'),
(6, 2, '2026-10-24', '06:00:00', '10:00:00', 'Reitoria da Universidade do Estado do Amazonas', NULL, '', NULL, '', 1, '2025-10-30 23:48:35');

-- --------------------------------------------------------

--
-- Estrutura para tabela `termos_eventos`
--

CREATE TABLE `termos_eventos` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `modalidade_id` int(11) DEFAULT NULL,
  `titulo` varchar(255) NOT NULL,
  `conteudo` text NOT NULL,
  `versao` varchar(10) DEFAULT '1.0',
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `termos_eventos`
--

INSERT INTO `termos_eventos` (`id`, `evento_id`, `modalidade_id`, `titulo`, `conteudo`, `versao`, `ativo`, `data_criacao`) VALUES
(1, 2, NULL, 'Termos Gerais do Evento', 'TERMOS E CONDIÇÕES GERAIS\r\n\r\n1. INSCRIÇÃO E PARTICIPAÇÃO\r\n1.1. A inscrição no evento implica na aceitação integral destes termos e condições.\r\n1.2. O participante deve ter idade mínima de 16 anos completos na data do evento.\r\n1.3. Menores de 18 anos devem apresentar autorização dos responsáveis legais.\r\n\r\n2. PAGAMENTO E REEMBOLSO\r\n2.1. O pagamento deve ser efetuado conforme as condições estabelecidas.\r\n2.2. Em caso de cancelamento pelo participante:\r\n   - Até 30 dias antes: reembolso de 80%\r\n   - Até 15 dias antes: reembolso de 50%\r\n   - Menos de 15 dias: sem reembolso\r\n2.3. O evento pode ser cancelado por motivos de força maior, com reembolso integral.\r\n\r\n3. RESPONSABILIDADES\r\n3.1. O participante é responsável por sua segurança e integridade física.\r\n3.2. É obrigatório o uso de equipamentos de segurança fornecidos.\r\n3.3. O participante deve seguir todas as instruções dos organizadores.\r\n\r\n4. IMAGEM E DADOS\r\n4.1. O participante autoriza o uso de sua imagem para fins promocionais.\r\n4.2. Os dados pessoais serão tratados conforme a LGPD.\r\n\r\n5. DISPOSIÇÕES GERAIS\r\n5.1. Casos omissos serão resolvidos pelos organizadores.\r\n5.2. Estes termos podem ser alterados a qualquer momento.\r\n\r\nData de vigência: 01/01/2024', '1.0', 1, '2025-09-02 16:41:09'),
(2, 2, 1, 'Termos Específicos - Corrida', 'TERMOS ESPECÍFICOS PARA MODALIDADE CORRIDA\r\n\r\n1. EQUIPAMENTOS OBRIGATÓRIOS\r\n1.1. Tênis adequado para corrida\r\n1.2. Roupas confortáveis e apropriadas\r\n1.3. Hidratação durante o percurso\r\n\r\n2. REGRAS DE SEGURANÇA\r\n2.1. Manter-se sempre à direita da pista\r\n2.2. Não ultrapassar outros corredores de forma perigosa\r\n2.3. Respeitar os sinais dos organizadores\r\n\r\n3. MEDICAL\r\n3.1. É recomendável consulta médica antes da participação\r\n3.2. Informar sobre condições médicas preexistentes\r\n3.3. Levar medicamentos de uso pessoal se necessário\r\n\r\n4. PERCURSO\r\n4.1. O percurso será sinalizado e monitorado\r\n4.2. Pontos de hidratação a cada 2km\r\n4.3. Tempo limite: 3 horas para completar o percurso\r\n\r\n5. PREMIAÇÃO\r\n5.1. Medalhas para todos os participantes\r\n5.2. Troféus para os 3 primeiros colocados de cada categoria\r\n5.3. Resultados disponíveis no site do evento\r\n\r\nEstes termos complementam os termos gerais do evento.', '1.0', 1, '2025-09-02 16:41:09'),
(3, 2, NULL, 'Termos e Condições Gerais do Evento', 'TERMOS E CONDIÇÕES GERAIS DE PARTICIPAÇÃO\r\n\r\n1. INSCRIÇÃO E PARTICIPAÇÃO\r\n1.1. A inscrição no evento implica na aceitação integral destes termos e condições.\r\n1.2. O participante deve ter idade mínima de 16 anos completos na data do evento.\r\n1.3. Menores de 18 anos devem apresentar autorização dos responsáveis legais no dia do evento.\r\n1.4. A participação é individual e voluntária.\r\n1.5. É obrigatório o cumprimento de todas as regras estabelecidas neste regulamento.\r\n\r\n2. PAGAMENTO E REEMBOLSO\r\n2.1. O pagamento deve ser efetuado conforme as condições estabelecidas no momento da inscrição.\r\n2.2. Em caso de cancelamento pelo participante:\r\n   - Até 30 dias antes do evento: reembolso de 80% do valor pago\r\n   - Até 15 dias antes do evento: reembolso de 50% do valor pago\r\n   - Menos de 15 dias: sem direito a reembolso\r\n2.3. O evento pode ser cancelado ou alterado por motivos de força maior (condições climáticas adversas, pandemia, etc.), com reembolso integral ou transferência para nova data.\r\n2.4. Não haverá reembolso em caso de desistência no dia do evento.\r\n\r\n3. RESPONSABILIDADES E SEGURANÇA\r\n3.1. O participante é responsável por sua segurança e integridade física durante todo o evento.\r\n3.2. É obrigatório o uso de equipamentos de segurança fornecidos pela organização.\r\n3.3. O participante deve seguir todas as instruções dos organizadores, monitores e equipe de segurança.\r\n3.4. É proibido o uso de substâncias ilícitas ou bebidas alcoólicas durante o evento.\r\n3.5. O participante deve estar em condições físicas adequadas para participar da modalidade escolhida.\r\n\r\n4. CONDIÇÕES FÍSICAS E MÉDICAS\r\n4.1. O participante declara estar em condições físicas adequadas para participar do evento.\r\n4.2. É recomendado consulta médica antes da participação em atividades físicas intensas.\r\n4.3. O participante deve informar qualquer condição médica relevante no momento da inscrição.\r\n4.4. A organização não se responsabiliza por problemas de saúde que possam ocorrer durante ou após a participação.\r\n\r\n5. USO DE IMAGEM E DADOS PESSOAIS\r\n5.1. O participante autoriza o uso de sua imagem em fotos e vídeos do evento para fins promocionais e de divulgação.\r\n5.2. Os dados pessoais coletados serão tratados conforme a Lei Geral de Proteção de Dados (LGPD).\r\n5.3. As informações serão utilizadas exclusivamente para fins do evento e comunicação relacionada.\r\n5.4. O participante pode solicitar a exclusão de seus dados após o evento.\r\n\r\n6. REGULAMENTO ESPORTIVO\r\n6.1. O participante deve cumprir todas as regras específicas da modalidade escolhida.\r\n6.2. Será desclassificado o participante que não cumprir as regras ou cometer infrações graves.\r\n6.3. A organização se reserva o direito de excluir participantes que causem transtornos.\r\n6.4. As decisões dos árbitros e organizadores são soberanas e não cabem recursos.\r\n\r\n7. EQUIPAMENTOS E MATERIAIS\r\n7.1. O participante é responsável pelos equipamentos pessoais que trouxer ao evento.\r\n7.2. A organização fornecerá os equipamentos obrigatórios de segurança.\r\n7.3. É proibido o uso de equipamentos que possam causar riscos aos demais participantes.\r\n7.4. O participante deve devolver os equipamentos emprestados em perfeitas condições.\r\n\r\n8. DISPOSIÇÕES GERAIS\r\n8.1. Casos omissos serão resolvidos pelos organizadores do evento.\r\n8.2. Estes termos podem ser alterados a qualquer momento, sendo comunicados aos participantes.\r\n8.3. A participação no evento implica na aceitação de eventuais alterações nos termos.\r\n8.4. Este regulamento está em conformidade com as normas esportivas nacionais e internacionais.\r\n\r\n9. CONTATO E COMUNICAÇÃO\r\n9.1. Todas as comunicações oficiais serão feitas através dos canais oficiais do evento.\r\n9.2. O participante deve manter seus dados de contato atualizados.\r\n9.3. A organização não se responsabiliza por comunicações não recebidas devido a dados incorretos.\r\n\r\n10. LEGISLAÇÃO APLICÁVEL\r\n10.1. Este regulamento está sujeito à legislação brasileira.\r\n10.2. Eventuais disputas serão resolvidas no foro da comarca do local do evento.\r\n\r\nData de vigência: 01/01/2024\r\nVersão: 1.0', '1.0', 1, '2025-10-14 16:00:52'),
(4, 2, 3, 'Termos Específicos - Corrida 5KM', 'TERMOS ESPECÍFICOS PARA CORRIDA 5KM\r\n\r\n1. DISTÂNCIA E PERCURSO\r\n1.1. A corrida terá distância oficial de 5 quilômetros.\r\n1.2. O percurso será marcado e cronometrado eletronicamente.\r\n1.3. É obrigatório seguir o percurso oficialmente estabelecido.\r\n1.4. Desvios do percurso resultarão em desclassificação.\r\n\r\n2. EQUIPAMENTOS OBRIGATÓRIOS\r\n2.1. Tênis adequado para corrida (obrigatório).\r\n2.2. Número de peito fornecido pela organização (obrigatório).\r\n2.3. Chip de cronometragem (fornecido pela organização).\r\n2.4. Recomenda-se uso de roupas leves e confortáveis.\r\n\r\n3. CRONOMETRAGEM E CLASSIFICAÇÃO\r\n3.1. A cronometragem será feita através de chip eletrônico.\r\n3.2. O tempo será contado a partir da linha de largada até a linha de chegada.\r\n3.3. Serão considerados apenas os tempos dos participantes que completarem o percurso oficial.\r\n3.4. A classificação será por categoria de idade e gênero.\r\n\r\n4. CATEGORIAS DE IDADE\r\n4.1. Sub-18: 16 a 17 anos\r\n4.2. Sub-25: 18 a 24 anos\r\n4.3. Sub-35: 25 a 34 anos\r\n4.4. Sub-45: 35 a 44 anos\r\n4.5. Sub-55: 45 a 54 anos\r\n4.6. Master: 55 anos ou mais\r\n\r\n5. REGRAS DE SEGURANÇA ESPECÍFICAS\r\n5.1. É proibido correr descalço ou com calçados inadequados.\r\n5.2. Não é permitido o uso de fones de ouvido durante a corrida.\r\n5.3. O participante deve manter-se sempre à direita do percurso.\r\n5.4. É obrigatório respeitar os sinais dos fiscais de percurso.\r\n\r\n6. HIDRATAÇÃO E ALIMENTAÇÃO\r\n6.1. Pontos de hidratação serão disponibilizados ao longo do percurso.\r\n6.2. É recomendado hidratação adequada antes, durante e após a corrida.\r\n6.3. O participante pode levar sua própria garrafa de água.\r\n6.4. Não é permitido o uso de suplementos durante a corrida.\r\n\r\n7. DESCLASSIFICAÇÕES\r\n7.1. Não seguir o percurso oficial.\r\n7.2. Usar equipamentos inadequados ou proibidos.\r\n7.3. Receber ajuda externa durante a corrida.\r\n7.4. Comportamento inadequado ou agressivo.\r\n7.5. Não usar o número de peito ou chip de cronometragem.\r\n\r\n8. PREMIAÇÃO\r\n8.1. Serão premiados os 3 primeiros colocados de cada categoria.\r\n8.2. A premiação será realizada após a confirmação dos resultados.\r\n8.3. É obrigatória a presença na cerimônia de premiação.\r\n8.4. Não haverá premiação em dinheiro, apenas troféus e medalhas.\r\n\r\n9. TEMPO LIMITE\r\n9.1. O tempo limite para completar a corrida é de 1 hora e 30 minutos.\r\n9.2. Participantes que ultrapassarem este tempo serão recolhidos pelos organizadores.\r\n9.3. Não haverá cronometragem para participantes que ultrapassarem o tempo limite.\r\n\r\n10. CONDIÇÕES CLIMÁTICAS\r\n10.1. A corrida será realizada mesmo em condições de chuva leve.\r\n10.2. Em caso de chuva forte ou condições perigosas, o evento poderá ser cancelado.\r\n10.3. Não haverá reembolso em caso de cancelamento por condições climáticas.\r\n\r\nVersão: 1.0', '1.0', 1, '2025-10-14 16:00:52'),
(5, 2, 1, 'Termos Específicos - Corrida 10KM', 'TERMOS ESPECÍFICOS PARA CORRIDA 10KM\r\n\r\n1. DISTÂNCIA E PERCURSO\r\n1.1. A corrida terá distância oficial de 10 quilômetros.\r\n1.2. O percurso será marcado e cronometrado eletronicamente.\r\n1.3. É obrigatório seguir o percurso oficialmente estabelecido.\r\n1.4. Desvios do percurso resultarão em desclassificação.\r\n\r\n2. EQUIPAMENTOS OBRIGATÓRIOS\r\n2.1. Tênis adequado para corrida de longa distância (obrigatório).\r\n2.2. Número de peito fornecido pela organização (obrigatório).\r\n2.3. Chip de cronometragem (fornecido pela organização).\r\n2.4. Recomenda-se uso de roupas técnicas e confortáveis.\r\n\r\n3. CRONOMETRAGEM E CLASSIFICAÇÃO\r\n3.1. A cronometragem será feita através de chip eletrônico.\r\n3.2. O tempo será contado a partir da linha de largada até a linha de chegada.\r\n3.3. Serão considerados apenas os tempos dos participantes que completarem o percurso oficial.\r\n3.4. A classificação será por categoria de idade e gênero.\r\n\r\n4. CATEGORIAS DE IDADE\r\n4.1. Sub-18: 16 a 17 anos\r\n4.2. Sub-25: 18 a 24 anos\r\n4.3. Sub-35: 25 a 34 anos\r\n4.4. Sub-45: 35 a 44 anos\r\n4.5. Sub-55: 45 a 54 anos\r\n4.6. Master: 55 anos ou mais\r\n\r\n5. REGRAS DE SEGURANÇA ESPECÍFICAS\r\n5.1. É proibido correr descalço ou com calçados inadequados.\r\n5.2. Não é permitido o uso de fones de ouvido durante a corrida.\r\n5.3. O participante deve manter-se sempre à direita do percurso.\r\n5.4. É obrigatório respeitar os sinais dos fiscais de percurso.\r\n5.5. Recomenda-se avaliação médica prévia para esta distância.\r\n\r\n6. HIDRATAÇÃO E ALIMENTAÇÃO\r\n6.1. Pontos de hidratação serão disponibilizados a cada 2,5km.\r\n6.2. Pontos de alimentação serão disponibilizados a cada 5km.\r\n6.3. É recomendado hidratação adequada antes, durante e após a corrida.\r\n6.4. O participante pode levar sua própria garrafa de água.\r\n6.5. Não é permitido o uso de suplementos durante a corrida.\r\n\r\n7. DESCLASSIFICAÇÕES\r\n7.1. Não seguir o percurso oficial.\r\n7.2. Usar equipamentos inadequados ou proibidos.\r\n7.3. Receber ajuda externa durante a corrida.\r\n7.4. Comportamento inadequado ou agressivo.\r\n7.5. Não usar o número de peito ou chip de cronometragem.\r\n\r\n8. PREMIAÇÃO\r\n8.1. Serão premiados os 3 primeiros colocados de cada categoria.\r\n8.2. A premiação será realizada após a confirmação dos resultados.\r\n8.3. É obrigatória a presença na cerimônia de premiação.\r\n8.4. Não haverá premiação em dinheiro, apenas troféus e medalhas.\r\n\r\n9. TEMPO LIMITE\r\n9.1. O tempo limite para completar a corrida é de 2 horas e 30 minutos.\r\n9.2. Participantes que ultrapassarem este tempo serão recolhidos pelos organizadores.\r\n9.3. Não haverá cronometragem para participantes que ultrapassarem o tempo limite.\r\n\r\n10. CONDIÇÕES CLIMÁTICAS\r\n10.1. A corrida será realizada mesmo em condições de chuva leve.\r\n10.2. Em caso de chuva forte ou condições perigosas, o evento poderá ser cancelado.\r\n10.3. Não haverá reembolso em caso de cancelamento por condições climáticas.\r\n\r\n11. ATENÇÃO MÉDICA\r\n11.1. Postos médicos estarão disponíveis ao longo do percurso.\r\n11.2. Participantes com problemas de saúde devem informar a organização.\r\n11.3. É recomendado portar identificação médica durante a corrida.\r\n\r\nVersão: 1.0', '1.0', 1, '2025-10-14 16:00:52'),
(6, 2, 11, 'Termos Específicos - Caminhada', 'TERMOS ESPECÍFICOS PARA CAMINHADA\r\n\r\n1. DISTÂNCIA E PERCURSO\r\n1.1. A caminhada terá distância de 3 quilômetros.\r\n1.2. O percurso será marcado e cronometrado eletronicamente.\r\n1.3. É obrigatório seguir o percurso oficialmente estabelecido.\r\n1.4. Desvios do percurso resultarão em desclassificação.\r\n\r\n2. EQUIPAMENTOS OBRIGATÓRIOS\r\n2.1. Calçado confortável adequado para caminhada (obrigatório).\r\n2.2. Número de peito fornecido pela organização (obrigatório).\r\n2.3. Chip de cronometragem (fornecido pela organização).\r\n2.4. Recomenda-se uso de roupas confortáveis e adequadas ao clima.\r\n\r\n3. CRONOMETRAGEM E CLASSIFICAÇÃO\r\n3.1. A cronometragem será feita através de chip eletrônico.\r\n3.2. O tempo será contado a partir da linha de largada até a linha de chegada.\r\n3.3. Serão considerados apenas os tempos dos participantes que completarem o percurso oficial.\r\n3.4. A classificação será por categoria de idade e gênero.\r\n\r\n4. CATEGORIAS DE IDADE\r\n4.1. Sub-18: 16 a 17 anos\r\n4.2. Sub-25: 18 a 24 anos\r\n4.3. Sub-35: 25 a 34 anos\r\n4.4. Sub-45: 35 a 44 anos\r\n4.5. Sub-55: 45 a 54 anos\r\n4.6. Master: 55 anos ou mais\r\n\r\n5. REGRAS DE SEGURANÇA ESPECÍFICAS\r\n5.1. É proibido caminhar descalço ou com calçados inadequados.\r\n5.2. É permitido o uso de fones de ouvido durante a caminhada.\r\n5.3. O participante deve manter-se sempre à direita do percurso.\r\n5.4. É obrigatório respeitar os sinais dos fiscais de percurso.\r\n5.5. Não é permitido correr durante a caminhada.\r\n\r\n6. HIDRATAÇÃO E ALIMENTAÇÃO\r\n6.1. Pontos de hidratação serão disponibilizados ao longo do percurso.\r\n6.2. É recomendado hidratação adequada antes, durante e após a caminhada.\r\n6.3. O participante pode levar sua própria garrafa de água.\r\n6.4. É permitido o consumo de alimentos leves durante a caminhada.\r\n\r\n7. DESCLASSIFICAÇÕES\r\n7.1. Não seguir o percurso oficial.\r\n7.2. Usar equipamentos inadequados ou proibidos.\r\n7.3. Correr durante a caminhada (exceto em emergências).\r\n7.4. Comportamento inadequado ou agressivo.\r\n7.5. Não usar o número de peito ou chip de cronometragem.\r\n\r\n8. PREMIAÇÃO\r\n8.1. Serão premiados os 3 primeiros colocados de cada categoria.\r\n8.2. A premiação será realizada após a confirmação dos resultados.\r\n8.3. É obrigatória a presença na cerimônia de premiação.\r\n8.4. Não haverá premiação em dinheiro, apenas troféus e medalhas.\r\n\r\n9. TEMPO LIMITE\r\n9.1. O tempo limite para completar a caminhada é de 1 hora.\r\n9.2. Participantes que ultrapassarem este tempo serão recolhidos pelos organizadores.\r\n9.3. Não haverá cronometragem para participantes que ultrapassarem o tempo limite.\r\n\r\n10. CONDIÇÕES CLIMÁTICAS\r\n10.1. A caminhada será realizada mesmo em condições de chuva leve.\r\n10.2. Em caso de chuva forte ou condições perigosas, o evento poderá ser cancelado.\r\n10.3. Não haverá reembolso em caso de cancelamento por condições climáticas.\r\n\r\n11. ACESSIBILIDADE\r\n11.1. O percurso será adaptado para pessoas com mobilidade reduzida.\r\n11.2. Cadeirantes são bem-vindos e terão apoio especializado.\r\n11.3. A organização fornecerá assistência quando necessário.\r\n\r\nVersão: 1.0', '1.0', 1, '2025-10-14 16:00:52'),
(7, 2, NULL, 'Política de Privacidade e Proteção de Dados', 'POLÍTICA DE PRIVACIDADE E PROTEÇÃO DE DADOS PESSOAIS\r\n\r\n1. COLETA DE DADOS\r\n1.1. Coletamos os seguintes dados pessoais:\r\n   - Nome completo\r\n   - Data de nascimento\r\n   - CPF ou documento de identidade\r\n   - Endereço completo\r\n   - Telefone e e-mail\r\n   - Informações médicas relevantes\r\n   - Dados de pagamento\r\n\r\n1.2. Os dados são coletados através do formulário de inscrição e durante o evento.\r\n\r\n2. FINALIDADE DO TRATAMENTO\r\n2.1. Os dados são utilizados para:\r\n   - Processar a inscrição no evento\r\n   - Comunicar informações importantes sobre o evento\r\n   - Emitir certificados de participação\r\n   - Realizar cronometragem e classificação\r\n   - Enviar resultados e fotos do evento\r\n   - Cumprir obrigações legais e fiscais\r\n\r\n3. COMPARTILHAMENTO DE DADOS\r\n3.1. Os dados podem ser compartilhados com:\r\n   - Parceiros do evento (quando necessário)\r\n   - Prestadores de serviços (cronometragem, fotografia)\r\n   - Autoridades competentes (quando exigido por lei)\r\n\r\n3.2. Não vendemos ou alugamos dados pessoais para terceiros.\r\n\r\n4. SEGURANÇA DOS DADOS\r\n4.1. Implementamos medidas técnicas e organizacionais para proteger os dados.\r\n4.2. Os dados são armazenados em servidores seguros.\r\n4.3. Acesso aos dados é restrito a pessoal autorizado.\r\n\r\n5. DIREITOS DO TITULAR\r\n5.1. Você tem direito a:\r\n   - Confirmar a existência de tratamento de dados\r\n   - Acessar seus dados pessoais\r\n   - Corrigir dados incompletos ou inexatos\r\n   - Solicitar anonimização ou eliminação de dados\r\n   - Solicitar portabilidade dos dados\r\n   - Revogar o consentimento\r\n\r\n6. RETENÇÃO DE DADOS\r\n6.1. Os dados serão mantidos pelo período necessário para:\r\n   - Cumprir as finalidades descritas\r\n   - Atender obrigações legais\r\n   - Resolver disputas\r\n\r\n6.2. Após o evento, os dados serão mantidos por 5 anos para fins de histórico.\r\n\r\n7. COOKIES E TECNOLOGIAS SIMILARES\r\n7.1. Utilizamos cookies para melhorar a experiência do usuário.\r\n7.2. Você pode configurar seu navegador para recusar cookies.\r\n\r\n8. ALTERAÇÕES NA POLÍTICA\r\n8.1. Esta política pode ser atualizada periodicamente.\r\n8.2. Alterações significativas serão comunicadas aos participantes.\r\n\r\n9. CONTATO\r\n9.1. Para exercer seus direitos ou esclarecer dúvidas:\r\n   - E-mail: privacidade@movamazonas.com\r\n   - Telefone: (92) 99999-9999\r\n   - Endereço: Rua das Flores, 123 - Manaus/AM\r\n\r\n10. BASE LEGAL\r\n10.1. O tratamento é baseado em:\r\n   - Consentimento do titular\r\n   - Cumprimento de obrigação legal\r\n   - Execução de contrato\r\n   - Legítimo interesse\r\n\r\nEsta política está em conformidade com a Lei Geral de Proteção de Dados (Lei nº 13.709/2018).\r\n\r\nVersão: 1.0', '1.0', 1, '2025-10-14 16:00:52');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome_completo` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `data_nascimento` date DEFAULT NULL,
  `tipo_documento` varchar(50) DEFAULT NULL,
  `documento` varchar(50) DEFAULT NULL,
  `sexo` enum('Masculino','Feminino','Outro') DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `celular` varchar(20) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `numero` varchar(20) DEFAULT NULL,
  `complemento` varchar(100) DEFAULT NULL,
  `bairro` varchar(100) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `uf` char(2) DEFAULT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `pais` varchar(100) DEFAULT NULL,
  `status` enum('ativo','inativo') DEFAULT 'ativo',
  `data_cadastro` timestamp NULL DEFAULT current_timestamp(),
  `papel` varchar(20) DEFAULT 'participante',
  `token_recuperacao` varchar(255) DEFAULT NULL,
  `token_expira` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome_completo`, `email`, `senha`, `data_nascimento`, `tipo_documento`, `documento`, `sexo`, `telefone`, `celular`, `endereco`, `numero`, `complemento`, `bairro`, `cidade`, `uf`, `cep`, `pais`, `status`, `data_cadastro`, `papel`, `token_recuperacao`, `token_expira`) VALUES
(1, 'UEA - APOIO TÉCNICO MENTE DE CORREDOR', 'organizador2657@exemplo.com', '$2y$10$shP8bFZXQdfiG8ILGJssuef.4e3TGFujMIEdOQx3.IdbCnbZh/yje', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ativo', '2025-07-16 14:32:16', 'participante', NULL, NULL),
(2, 'Eudimaci Lira', 'eudimaci08@yahoo.com.br', '$2y$10$shP8bFZXQdfiG8ILGJssuef.4e3TGFujMIEdOQx3.IdbCnbZh/yje', '1970-08-31', '', '', 'Masculino', '92982027654', '', '', '', '', '', '', '', '', '', 'ativo', '2025-07-17 02:46:46', 'organizador', 'c1991a8311bc6f793aac2029053660c27017cb1664554f7e53c92cae5a61690a', '2025-10-21 01:57:00'),
(3, 'Sandoval Bezerra', 'sandoval.bezerra@gmail.com', '$2y$10$9Y7uHhtwh6ZCbSSGNuMCVu200HKUbT.Z14uhCSnsLAAxTeDC9mC2K', '1969-08-28', '', '', 'Masculino', '81997661657', '', '', '', '', '', '', '', '', '', 'ativo', '2025-07-17 03:20:34', 'participante', NULL, NULL),
(4, 'Daniel Dias Filho', 'daniel@gmail.com', '$2y$10$9Y7uHhtwh6ZCbSSGNuMCVu200HKUbT.Z14uhCSnsLAAxTeDC9mC2K', '1982-01-15', '', '89658796587', 'Masculino', '92992000396', '', '', '', '', '', '', '', '', '', 'ativo', '2025-09-05 02:19:57', 'participante', NULL, NULL),
(5, 'Melvin Marble', 'melvin@yahoo.com.br', '$2y$10$WuBHXPdYYeandaKIMH9bhuDTou4d9me4iXwXJf/nBH3tU5zoIT2GG', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ativo', '2025-09-23 00:40:34', 'participante', NULL, NULL),
(7, 'Eudimaci Lira', 'eudimaci@gmail.com', '$2y$10$etODaXa4gRnalPhixMKNpepvGq2N3pOrvDi.qw4OZtpPRzC7xzWW.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ativo', '2025-10-21 00:56:13', 'participante', NULL, NULL);

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
(1, 3);

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
-- Índices de tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_evento_nome` (`evento_id`,`nome`);

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
  ADD KEY `fk_evento_deleted_by` (`deleted_by`),
  ADD KEY `idx_eventos_hora_corrida` (`hora_corrida`),
  ADD KEY `idx_eventos_regulamento_arquivo` (`regulamento_arquivo`);

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
  ADD KEY `idx_preference_id` (`preference_id`);

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
  ADD UNIQUE KEY `unique_payment_id` (`payment_id`),
  ADD KEY `inscricao_id` (`inscricao_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_payment_id` (`payment_id`),
  ADD KEY `idx_preference_id` (`preference_id`);

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
-- Índices de tabela `payments_ml`
--
ALTER TABLE `payments_ml`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_inscricao_id` (`inscricao_id`),
  ADD KEY `idx_preference_id` (`preference_id`),
  ADD KEY `idx_payment_id` (`payment_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_data_criacao` (`data_criacao`);

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
  ADD KEY `evento_id` (`evento_id`);

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
-- Índices de tabela `termos_eventos`
--
ALTER TABLE `termos_eventos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evento_id` (`evento_id`),
  ADD KEY `modalidade_id` (`modalidade_id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
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
-- AUTO_INCREMENT de tabela `camisas`
--
ALTER TABLE `camisas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `cupons_remessa`
--
ALTER TABLE `cupons_remessa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `inscricoes_cupons`
--
ALTER TABLE `inscricoes_cupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `inscricoes_produtos_extras`
--
ALTER TABLE `inscricoes_produtos_extras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `kits_eventos`
--
ALTER TABLE `kits_eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `kit_produtos`
--
ALTER TABLE `kit_produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de tabela `kit_templates`
--
ALTER TABLE `kit_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `kit_template_produtos`
--
ALTER TABLE `kit_template_produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de tabela `leads_organizadores`
--
ALTER TABLE `leads_organizadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `logs_admin`
--
ALTER TABLE `logs_admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT de tabela `lotes_inscricao`
--
ALTER TABLE `lotes_inscricao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de tabela `modalidades`
--
ALTER TABLE `modalidades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de tabela `organizadores`
--
ALTER TABLE `organizadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `pagamentos`
--
ALTER TABLE `pagamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pagamentos_ml`
--
ALTER TABLE `pagamentos_ml`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `papeis`
--
ALTER TABLE `papeis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `payments_ml`
--
ALTER TABLE `payments_ml`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `produtos_extras`
--
ALTER TABLE `produtos_extras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `produtos_extras_modalidade`
--
ALTER TABLE `produtos_extras_modalidade`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `produto_extra_produtos`
--
ALTER TABLE `produto_extra_produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `programacao_evento`
--
ALTER TABLE `programacao_evento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `questionario_evento`
--
ALTER TABLE `questionario_evento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de tabela `questionario_evento_modalidade`
--
ALTER TABLE `questionario_evento_modalidade`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT de tabela `termos_eventos`
--
ALTER TABLE `termos_eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

-- --------------------------------------------------------

--
-- Estrutura para view `eventos_ativos`
--
DROP TABLE IF EXISTS `eventos_ativos`;

CREATE OR REPLACE VIEW `eventos_ativos` AS SELECT `eventos`.`id` AS `id`, `eventos`.`nome` AS `nome`, `eventos`.`descricao` AS `descricao`, `eventos`.`data_inicio` AS `data_inicio`, `eventos`.`data_fim` AS `data_fim`, `eventos`.`categoria` AS `categoria`, `eventos`.`genero` AS `genero`, `eventos`.`local` AS `local`, `eventos`.`cep` AS `cep`, `eventos`.`url_mapa` AS `url_mapa`, `eventos`.`logradouro` AS `logradouro`, `eventos`.`numero` AS `numero`, `eventos`.`cidade` AS `cidade`, `eventos`.`estado` AS `estado`, `eventos`.`pais` AS `pais`, `eventos`.`regulamento` AS `regulamento`, `eventos`.`status` AS `status`, `eventos`.`organizador_id` AS `organizador_id`, `eventos`.`taxa_setup` AS `taxa_setup`, `eventos`.`percentual_repasse` AS `percentual_repasse`, `eventos`.`exibir_retirada_kit` AS `exibir_retirada_kit`, `eventos`.`taxa_gratuitas` AS `taxa_gratuitas`, `eventos`.`taxa_pagas` AS `taxa_pagas`, `eventos`.`limite_vagas` AS `limite_vagas`, `eventos`.`hora_fim_inscricoes` AS `hora_fim_inscricoes`, `eventos`.`data_criacao` AS `data_criacao`, `eventos`.`hora_inicio` AS `hora_inicio`, `eventos`.`data_realizacao` AS `data_realizacao`, `eventos`.`imagem` AS `imagem`, `eventos`.`deleted_at` AS `deleted_at`, `eventos`.`deleted_by` AS `deleted_by`, `eventos`.`delete_reason` AS `delete_reason` FROM `eventos` WHERE `eventos`.`deleted_at` IS NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
