-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 12/11/2025 às 15:05
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
-- Estrutura para tabela `anamneses`
--

DROP TABLE IF EXISTS `anamneses`;
CREATE TABLE IF NOT EXISTS `anamneses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `profissional_id` int DEFAULT NULL,
  `data_anamnese` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `peso` decimal(5,2) NOT NULL,
  `altura` int NOT NULL,
  `imc` decimal(4,2) DEFAULT NULL,
  `fc_maxima` smallint DEFAULT NULL COMMENT 'Frequência Cardíaca Máxima (bpm)',
  `vo2_max` decimal(5,2) DEFAULT NULL COMMENT 'VO2 Máximo (ml/kg/min)',
  `zona_alvo_treino` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Zona Alvo de Treinamento (descritivo ou faixa)',
  `doencas_preexistentes` text COLLATE utf8mb4_unicode_ci,
  `uso_medicamentos` text COLLATE utf8mb4_unicode_ci,
  `nivel_atividade` enum('ativo','inativo') COLLATE utf8mb4_unicode_ci NOT NULL,
  `objetivo_principal` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `foco_primario` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `max_glicemia` int NOT NULL,
  `limitacoes_fisicas` text COLLATE utf8mb4_unicode_ci,
  `preferencias_atividades` text COLLATE utf8mb4_unicode_ci,
  `disponibilidade_horarios` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `assinatura_aluno` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assinatura_responsavel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pendente','em_analise','aprovada','arquivada') COLLATE utf8mb4_unicode_ci DEFAULT 'pendente',
  PRIMARY KEY (`id`),
  KEY `idx_anamneses_usuario` (`usuario_id`),
  KEY `idx_anamneses_profissional` (`profissional_id`),
  KEY `idx_usuario` (`usuario_id`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `anamneses`
--

INSERT INTO `anamneses` (`id`, `usuario_id`, `profissional_id`, `data_anamnese`, `peso`, `altura`, `imc`, `fc_maxima`, `vo2_max`, `zona_alvo_treino`, `doencas_preexistentes`, `uso_medicamentos`, `nivel_atividade`, `objetivo_principal`, `foco_primario`, `max_glicemia`, `limitacoes_fisicas`, `preferencias_atividades`, `disponibilidade_horarios`, `assinatura_aluno`, `assinatura_responsavel`, `status`) VALUES
(2, 2, NULL, '2025-04-21 16:26:11', 80.00, 175, 26.12, NULL, 45.00, '2', 'nenhuma', 'nenhuma', 'inativo', 'prevenção_diabetes', 'weight-loss', 98, 'nenhuma', 'Nadar, musculação', 'todas as tardes', NULL, NULL, 'pendente'),
(3, 3, NULL, '2025-04-21 16:36:53', 96.00, 173, 32.08, NULL, NULL, NULL, 'nenhum', 'nenhum', 'inativo', 'Perda de peso', '', 0, 'nenhum', 'musculação', 'todas as tardes da semana', NULL, NULL, 'pendente'),
(4, 4, NULL, '2025-04-21 22:22:31', 62.50, 167, 22.41, NULL, 52.00, '', 'nenhuma', 'nenhum', 'ativo', 'prevenção_diabetes', 'strength', 98, 'dor no joelho', 'Corrida', 'Segunda, quarta e sexta, de 07h00 às 09h00.', NULL, NULL, 'pendente'),
(5, 7, 5, '2025-05-22 11:01:19', 65.00, 165, 23.88, 145, 45.00, '1', 'nenhuma', 'Vitaminas', 'ativo', 'prevenção_diabetes', 'endurance', 92, 'nenhuma', 'Corrida', 'segunda, quarta e sexta', NULL, NULL, ''),
(6, 10, 5, '2025-05-23 10:55:23', 79.00, 171, 19.75, 165, 44.00, '2', 'nenhuma', 'losartana', 'ativo', 'condicionamento', '', 0, 'ombro operado', 'corrida', 'todas a manhãs de segunda a sábado', NULL, NULL, ''),
(8, 12, 5, '2025-05-23 11:17:53', 75.00, 173, 25.06, 165, 54.00, '3', 'nenhuma', 'nenhuma', 'ativo', 'prevenção_diabetes', 'endurance', 90, 'nenhuma', 'Corrida', 'segunda a sexta a tarde', NULL, NULL, ''),
(9, 13, 5, '2025-05-26 14:24:11', 79.00, 181, 24.11, 165, 54.00, '3', 'nenhuma', 'nenhum', 'ativo', 'condicionamento', '', 0, 'nenhuma', 'correr e saltar', 'manhãs de segunda a sexta', NULL, NULL, ''),
(10, 21, 5, '2025-06-23 15:13:44', 74.00, 173, 24.73, 165, 38.00, '2', 'pressão alta', 'losartana', 'ativo', 'prevenção_diabetes', 'core-strength', 91, 'dor no joelho', 'caminhar e musculação', 'segunda a sexta', NULL, NULL, 'pendente'),
(11, 22, 5, '2025-06-24 01:37:11', 71.00, 155, 29.55, 168, 30.00, '2', 'Não.', 'Não.', 'ativo', 'prevenção_diabetes', 'weight-loss', 98, 'Dor no joelho esquerdo ', 'Musculação  ', 'Segunda a domingo ', NULL, NULL, 'pendente'),
(12, 23, 5, '2025-07-03 00:13:28', 80.00, 171, 27.36, 170, 32.00, '2', 'Asma', 'Alérgia a anti-térmicos(paracetamol, dipirona, ASS)', 'ativo', 'prevenção_diabetes', 'weight-loss', 90, 'Não tem', 'Corrida e natação', 'segunda a sexta', NULL, NULL, 'pendente'),
(13, 25, 24, '2025-07-03 00:56:53', 120.00, 185, 35.06, 190, 25.00, '1', 'Cardiáco', 'antidepressivos e para o coração', 'inativo', 'tratamento_diabetes', 'weight-loss', 200, 'Dores nas articulações e coluna', 'Não tem', 'segunda a sábado', NULL, NULL, 'pendente'),
(14, 29, 27, '2025-07-04 02:00:37', 80.00, 159, 31.64, 40, NULL, '2', 'Obesidade\nPre diabetes', '', 'inativo', 'prevenção_diabetes', 'weight-loss', 90, 'Desgaste no quadril', 'Caminhada', 'Segunda, quarta e sexta-feira ', NULL, NULL, 'pendente'),
(15, 12, 5, '2025-06-01 00:00:00', 75.00, 174, 24.77, NULL, NULL, NULL, NULL, NULL, 'ativo', 'prevenção_diabetes', 'endurance', 120, NULL, NULL, 'manhã', NULL, NULL, 'pendente'),
(16, 12, 5, '2025-07-01 00:00:00', 74.00, 174, 24.44, 145, 45.00, '3', '', '', 'ativo', 'prevenção_diabetes', 'endurance', 115, 'nenhuma', 'corrida', 'manhã', NULL, NULL, 'pendente'),
(17, 30, 15, '2025-07-11 14:50:44', 72.00, 165, 26.45, 170, 40.00, '2', 'Não.', 'Não.', 'ativo', 'prevenção_diabetes', 'aerobic-resistance', 89, 'Não.', 'Meia Maratona', 'Segunda a sexta', NULL, NULL, 'pendente'),
(18, 31, 15, '2025-07-19 16:59:55', 97.00, 178, 30.61, 175, 30.00, '2', 'Não.', 'Não. ', 'ativo', 'prevenção_diabetes', 'weight-loss', 98, 'Não.', 'Musculação e corrida', 'Segunda a sexta feira.', NULL, NULL, 'pendente'),
(19, 32, 15, '2025-07-20 00:15:10', 75.00, 175, 24.49, 150, 30.00, '2', 'Hipertensão e diabetes tipo II', '* Mepranil 2,5mg + 5mg (Ramipril + Amiodipina) - pressão.\n\n* Rusovastatina + Ezetimiba 20mg + 10mg  -  Colesterol\n\n* Enymect  5mg/1.000mg Diabetes .\n\n* Glicazida Milan 60mg - Diabetes.\n\n* Metformina 1000mg - Diabetes.\n\n* Sitagliptima 100 - Diabetes.', 'inativo', 'tratamento_diabetes', 'functional-strength', 130, 'Dores nos joelhos, com dificuldade para andar. ', 'Não tem preferência', 'segunda, quarta, sexta e sábado.', NULL, NULL, 'pendente'),
(20, 33, 15, '2025-07-20 00:42:06', 84.00, 175, 27.43, 160, 30.00, '2', 'Pressão arterial alta', 'Pressão Holmes 20mg', 'inativo', 'prevenção_diabetes', 'aerobic-resistance', 100, 'Não possui', 'Corrida e musculação', 'Segunda, quarta e sexta', NULL, NULL, 'pendente'),
(21, 34, 15, '2025-07-20 01:08:54', 73.00, 165, 26.81, 180, 45.00, '', 'Não', 'Não', 'ativo', 'prevenção_diabetes', 'aerobic-resistance', 76, 'Não', 'Corrida', 'Segunda, quarta, sexta e sábado', NULL, NULL, 'pendente'),
(22, 35, 15, '2025-07-20 01:41:09', 91.00, 173, 30.41, 170, 49.00, '3', 'Não', 'Não', 'ativo', 'prevenção_diabetes', 'aerobic-resistance', 98, 'Não possui', 'ultramaratona', 'Segunda, terça, quarta, quinta, sexta, sábado e domingo', NULL, NULL, 'pendente'),
(23, 37, 15, '2025-07-20 05:03:08', 71.00, 155, 29.55, 168, 30.00, '2', 'Não.', 'Não.', 'ativo', 'prevenção_diabetes', 'weight-loss', 98, 'Dor no joelho esquerdo com dificuldade na articulação', 'Musculação.', 'Segunda a sábado. ', NULL, NULL, 'pendente'),
(24, 38, 15, '2025-07-21 22:48:30', 92.00, 188, 26.03, 180, 30.00, '4', 'Não.', 'Não.', 'ativo', 'prevenção_diabetes', 'hypertrophy', 110, 'Não.', 'musculação e corrida', 'segunda, quarta, quinta, sexta e sábado.', NULL, NULL, 'pendente'),
(25, 39, 15, '2025-07-22 02:08:25', 87.00, 167, 31.20, 160, 30.00, '2', 'Não.', 'Bupropiona 150 mg e sibutramina 15 mg', 'inativo', 'prevenção_diabetes', 'weight-loss', 88, 'Não possui', 'Cross fit ', 'Segunda, quarta e sexta', NULL, NULL, 'pendente'),
(26, 40, 15, '2025-07-23 00:49:24', 110.00, 180, 33.95, 164, 35.00, '2', 'Não possui.', ' Naprix 2,5 mg', 'ativo', 'prevenção_diabetes', 'weight-loss', 109, 'Não possui', 'Corrida e musculação', 'Segunda, quarta, sexta e sábado.', NULL, NULL, 'pendente'),
(27, 41, 15, '2025-07-23 01:00:06', 78.00, 160, 30.47, 160, 30.00, '2', 'Pré-Diabética', 'Glifage XR500mg', 'ativo', 'prevenção_diabetes', 'weight-loss', 120, 'Não possui', 'Corrida e musculação, para perda de peso e controle da pressão arterial', 'Segunda, terça, quarta, quinta e sexta-feira', NULL, NULL, 'pendente'),
(28, 43, 15, '2025-07-23 23:11:20', 94.00, 172, 31.77, 150, 30.00, '2', 'Pressão alta, depressão e insônia', 'diovan (pressão arterial) e bupopriona (antidepressivo) e quetiapina.', 'inativo', 'prevenção_diabetes', 'weight-loss', 150, ' hérnias de disco na lombar e 2 na cervical.', 'Musculação e natação', 'Segunda, quarta e sexta-feira', NULL, NULL, 'pendente'),
(29, 45, 15, '2025-07-26 22:36:02', 75.00, 160, 29.30, 170, 30.00, '2', 'Não. ', 'Escitalopram de 10 mg', 'ativo', 'prevenção_diabetes', 'aerobic-resistance', 98, 'Não possui. ', 'Boxe, musculação ecorrida', 'segunda-feira, terça-feira, quarta-feira, quinta-feira, sexta-feira,sábado.', NULL, NULL, 'pendente'),
(30, 47, 15, '2025-07-30 02:35:10', 82.00, 174, 27.08, 170, 11.00, '2', 'Não Possui', 'Nenhum.', 'inativo', 'prevenção_diabetes', 'aerobic-resistance', 98, 'Nenhuma', 'Corrida e natação', 'Segunda-feira, terça-feira, quarta-feira, quinta-feira, sexta-feira e sábado.', NULL, NULL, 'pendente'),
(31, 48, 15, '2025-08-01 20:39:09', 56.00, 159, 22.15, 170, 20.00, '4', 'Não Possui', 'Não usa', 'inativo', 'prevenção_diabetes', 'hypertrophy', 90, 'Cisto no pulso direito', 'Musculação ', 'Segunda, terça, quinta, sexta e domingo.', NULL, NULL, 'pendente'),
(32, 50, 15, '2025-08-02 15:33:11', 54.00, 150, 24.00, 160, 30.00, '3', 'Não.', 'Losortana 50 mg', 'ativo', 'prevenção_diabetes', 'hypertrophy', 120, 'Não', 'caminhada e corrida', 'segunda, quarta e sexta', NULL, NULL, 'pendente'),
(33, 51, 42, '2025-08-02 15:53:45', 83.00, 171, 28.38, 133, 36.00, '2', 'Epilepsia \nAlergia a alguns medicamentos ', 'Hidantal', 'ativo', 'prevenção_diabetes', 'weight-loss', 98, '', 'Musculação ', 'Pela manhã \n45 minutos', NULL, NULL, 'pendente'),
(34, 57, 15, '2025-08-02 17:33:54', 98.00, 186, 28.33, 170, 30.00, '2', 'Não tem', 'Não tem', 'ativo', 'prevenção_diabetes', 'weight-loss', 95, 'Não tem.', 'Corrida, musculação.', 'segunda, quarta e sexta.', NULL, NULL, 'pendente'),
(35, 58, 15, '2025-08-02 17:51:22', 126.00, 170, 43.60, 180, 30.00, '2', 'nenhuma', 'nenhum', 'ativo', 'prevenção_diabetes', 'weight-loss', 95, 'nehuma', 'musculaçao', 'segunda quarta e sexta', NULL, NULL, 'pendente'),
(36, 59, 15, '2025-08-02 18:40:30', 54.00, 152, 23.37, 170, 30.00, '2', 'nao', 'nao', 'inativo', 'prevenção_diabetes', 'weight-loss', 95, 'nao', 'corrida, força', 'sabado', NULL, NULL, 'pendente'),
(37, 60, 15, '2025-08-02 18:51:18', 90.00, 172, 30.42, 170, 35.00, '2', 'nao', 'nao', 'ativo', 'prevenção_diabetes', 'weight-loss', 90, 'rompitura do ligamento, pé esquerdo com limitacao de articulação', 'musculação, jogar tenis', 'segunda a sexta', NULL, NULL, 'pendente'),
(38, 61, 15, '2025-08-02 19:16:34', 64.00, 160, 25.00, 150, 30.00, '1', 'Pressão, prédiabéticas, sindrome metabólica, os dois joelhos com artrose', 'Losartana 50 mg, glifagem Xl 500, puran t4 25mg', 'ativo', 'prevenção_diabetes', 'aerobic-resistance', 180, 'Artrose nos dois joelhos', 'musculação, natação e hidroginástica', 'Terça e quarta', NULL, NULL, 'pendente'),
(39, 62, 15, '2025-08-02 19:34:38', 80.00, 155, 33.30, 170, 30.00, '2', 'Nnenhuma', 'Não ', 'inativo', 'prevenção_diabetes', 'weight-loss', 80, 'Nenhuma', 'Musculação ou Corrida', 'terça, quinta e sabado', NULL, NULL, 'pendente'),
(40, 63, 15, '2025-08-02 20:41:32', 80.00, 170, 27.68, 170, 35.00, '2', 'Não.', 'Não.', 'ativo', 'prevenção_diabetes', 'weight-loss', 90, 'Nenhuma.', 'musculação', 'segunda, terça, quarta, quinta e sexta-feira.', NULL, NULL, 'pendente'),
(41, 65, 49, '2025-08-03 16:54:01', 65.00, 145, 30.92, 140, 30.00, '2', 'Diabetes', 'Glicazida 60mg', 'ativo', 'tratamento_diabetes', 'weight-loss', 130, 'Osteoporose', 'Academia, caminhada, funcional', 'segunda a sábado - Trade ou noite.', NULL, NULL, 'pendente'),
(42, 66, 49, '2025-08-03 19:09:28', 57.00, 155, 23.73, 130, 35.00, '', 'Não tem', 'Não tem', 'ativo', 'prevenção_diabetes', 'hypertrophy', 90, 'Não tem', 'Musculação e corrida', 'Segunda a sábado', NULL, NULL, 'pendente'),
(43, 67, 49, '2025-08-03 20:50:31', 83.00, 169, 29.06, 89, 85.00, '', 'diabetes tipo 2, psoriase', 'sim, insulina, glicasida, metiformina, sivastantina, aas 100,metrotrexato, adalimomabe,', 'inativo', 'tratamento_diabetes', 'weight-loss', 100, 'nao', 'caminhada ', '3x na semana inicio da manha ou final do dia', NULL, NULL, 'pendente'),
(44, 68, 42, '2025-08-03 21:21:26', 70.00, 169, 24.51, 69, 35.00, '', '', '', 'ativo', 'prevenção_diabetes', 'weight-loss', 80, 'Não', 'Musculação ', 'Todos os dias', NULL, NULL, 'pendente'),
(45, 70, 15, '2025-08-04 00:33:56', 52.00, 150, 23.11, 150, 30.00, '3', 'Pressão arterial alta', 'Losartana 50 mg e  selozok 25 mg', 'inativo', 'prevenção_diabetes', 'hypertrophy', 95, 'Não.', 'musculação', 'Segunda, quarta e sexta', NULL, NULL, 'pendente'),
(46, 71, 15, '2025-08-05 11:57:46', 89.00, 160, 34.77, 160, 20.00, '2', 'Nenhuma', 'Nenhum', 'inativo', 'prevenção_diabetes', 'weight-loss', 95, 'Nenhuma', 'Caminhada', 'segunda, terça, quarta, quinta e sexta-feira.', NULL, NULL, 'pendente'),
(47, 75, 15, '2025-08-06 18:23:42', 62.75, 159, 24.82, 170, 40.00, '4', 'Não possui.', 'Não faz uso.', 'ativo', 'prevenção_diabetes', 'hypertrophy', 92, 'Não possui.', 'Musculação.', 'segunda, terça, quarta, quinta, sexta-feira e sábado.', NULL, NULL, 'pendente'),
(48, 76, 15, '2025-08-08 17:37:43', 65.00, 159, 25.71, 170, 35.00, '4', 'Não possui.', 'Não usa.', 'ativo', 'prevenção_diabetes', 'hypertrophy', 90, 'Não possui', 'Boxe, musculação e corrida', 'Segunda, terça, quarta, quinta, sexta-feira e sábado.', NULL, NULL, 'pendente'),
(49, 77, 15, '2025-08-10 18:58:48', 78.00, 170, 26.99, 170, 45.00, '4', 'Não possui. ', 'Não utiliza', 'ativo', 'prevenção_diabetes', 'hypertrophy', 85, 'Não possui', 'Corrida e Musculação ', 'Segunda a sábado ', NULL, NULL, 'pendente'),
(50, 78, 15, '2025-08-11 17:07:30', 75.00, 158, 30.04, NULL, NULL, NULL, 'Pré-diabetes ', 'Não ', 'ativo', 'prevenção_diabetes', 'hypertrophy', 113, 'Não ', 'Musculação ', 'De Segunda a sexta às 16h', NULL, NULL, 'pendente'),
(51, 79, 15, '2025-08-13 14:50:59', 95.00, 169, 33.26, 160, 30.00, '2', 'Não possui', 'Não usa', 'inativo', 'prevenção_diabetes', 'aerobic-resistance', 110, 'Não possui', 'corrida e musculação', 'Segunda, terça, quarta, quinta e sexta-feira.', NULL, NULL, 'pendente'),
(52, 80, 15, '2025-08-13 16:02:46', 74.00, 176, 23.89, 140, 30.00, '3', 'Não há', 'Não há', 'ativo', 'prevenção_diabetes', 'aerobic-resistance', 100, 'Não há ', 'Musculação e kung fu', 'Segunda, terça, quarta, quinta e sexta-feira.', NULL, NULL, 'pendente'),
(53, 81, 15, '2025-08-13 16:28:33', 50.00, 170, 17.30, 170, 30.00, '', 'Não possui', 'Não usa.', 'inativo', 'prevenção_diabetes', 'hypertrophy', 95, 'Não possui', 'Boxe e musculação', 'Segunda a Sábado.', NULL, NULL, 'pendente'),
(54, 82, 15, '2025-08-13 23:50:15', 50.00, 149, 22.52, 154, 45.00, '3', 'Nenhuma', 'Não ', 'ativo', 'prevenção_diabetes', 'aerobic-resistance', 87, 'Não ', 'corrida', 'Segunda, terça, quarta, quinta, sexta-feira e sábado', NULL, NULL, 'pendente'),
(55, 84, 15, '2025-11-04 19:46:29', 79.00, 174, 26.09, 180, 45.00, '3', 'Nenhuma ', 'Não uso nenhum medicamento ', 'ativo', 'prevenção_diabetes', 'aerobic-resistance', 100, 'Não tenho ', 'Musculação e corrida ', 'Segunda, terça, quarta, quinta, sexta e sábado', NULL, NULL, 'pendente'),
(56, 85, 15, '2025-11-10 20:04:04', 84.00, 160, 32.81, 150, 30.00, '3', 'Discopatias degenerativas \nEspondiloartrose lombar \nOsteofito superior na face articular da patela\nAbaulamentos discais difusos em L3 L4, L5 e S1\nRedução das bases foraminais', 'Pregabalina 15p MG', 'ativo', 'prevenção_diabetes', 'weight-loss', 102, 'Dor no joelho esquerdo\nInchaço das mãos e pés. \n', 'Musculação e ciclismo ', 'Segunda, Terça, quarta, quinta e sexta', NULL, NULL, 'pendente');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
