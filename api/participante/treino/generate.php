<?php
error_log('[GERAR_TREINO] Iniciando processo de geração de treino');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base_path = dirname(__DIR__);
require_once $base_path . '/../db.php';
require_once $base_path . '/../helpers/config_helper.php';

if (!isset($_SESSION['user_id'])) {
    error_log('[GERAR_TREINO] Erro: Acesso negado - usuário não autenticado');
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log('[GERAR_TREINO] Erro: Método não permitido - ' . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido. Use POST.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$inscricao_id = isset($data['inscricao_id']) ? (int)$data['inscricao_id'] : null;
$usuario_id = $_SESSION['user_id'];

error_log('[GERAR_TREINO] Dados recebidos - inscricao_id: ' . $inscricao_id . ', usuario_id: ' . $usuario_id);

if (!$inscricao_id) {
    error_log('[GERAR_TREINO] Erro: ID da inscrição não fornecido');
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID da inscrição é obrigatório.']);
    exit();
}

try {
    // Verificar configuração de exigência de inscrição
    error_log('[GERAR_TREINO] Verificando configuração de exigência de inscrição...');
    $exigir_inscricao = ConfigHelper::get('treino.exigir_inscricao', true);
    
    if ($exigir_inscricao) {
        // MODO PRODUÇÃO: Validação completa de inscrição
        error_log('[GERAR_TREINO] Modo PRODUÇÃO: validando inscrição...');
        $verificar_inscricao = $pdo->prepare("
            SELECT i.id, i.usuario_id, e.nome as evento_nome, 
                   COALESCE(e.data_realizacao, e.data_inicio) as evento_data,
                   m.nome as modalidade_nome, m.distancia
            FROM inscricoes i
            JOIN eventos e ON i.evento_id = e.id
            JOIN modalidades m ON i.modalidade_evento_id = m.id
            WHERE i.id = ? AND i.usuario_id = ?
        ");
        $verificar_inscricao->execute([$inscricao_id, $usuario_id]);
        $inscricao_data = $verificar_inscricao->fetch(PDO::FETCH_ASSOC);

        if (!$inscricao_data) {
            error_log('[GERAR_TREINO] Erro: Inscrição não encontrada ou não pertence ao usuário');
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Inscrição não encontrada ou não pertence ao usuário.']);
            exit();
        }
        
        error_log('[GERAR_TREINO] Inscrição encontrada - Evento: ' . $inscricao_data['evento_nome']);
    } else {
        // REGRA PROVISÓRIA: Inscrição não exigida
        error_log('[GERAR_TREINO] ⚠️ REGRA PROVISÓRIA: Inscrição não exigida');
        $inscricao_data = [
            'id' => $inscricao_id,
            'usuario_id' => $usuario_id,
            'evento_nome' => 'Evento de Preparação',
            'evento_data' => date('Y-m-d', strtotime('+90 days')),
            'modalidade_nome' => '10km',
            'distancia' => 10
        ];
        error_log('[GERAR_TREINO] Dados provisórios usados - Evento: ' . $inscricao_data['evento_nome']);
    }

    $evento_data = new DateTime($inscricao_data['evento_data']);
    $hoje = new DateTime();
    
    if ($evento_data < $hoje) {
        error_log('[GERAR_TREINO] Erro: Evento já passou - Data evento: ' . $evento_data->format('Y-m-d') . ', Hoje: ' . $hoje->format('Y-m-d'));
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Não é possível gerar treino para um evento que já aconteceu.']);
        exit();
    }

    // Regra: com inscrição obrigatória = um treino por inscrição; regra provisória = um treino por participante (usuario_id)
    if ($exigir_inscricao) {
        $sql_verificar_treino = "SELECT id FROM planos_treino_gerados WHERE inscricao_id = ? LIMIT 1";
        $stmt_verificar_treino = $pdo->prepare($sql_verificar_treino);
        $stmt_verificar_treino->execute([$inscricao_id]);
    } else {
        $sql_verificar_treino = "SELECT id FROM planos_treino_gerados WHERE usuario_id = ? LIMIT 1";
        $stmt_verificar_treino = $pdo->prepare($sql_verificar_treino);
        $stmt_verificar_treino->execute([$usuario_id]);
    }
    $treino_existente = $stmt_verificar_treino->fetch(PDO::FETCH_ASSOC);

    if ($treino_existente) {
        if ($exigir_inscricao) {
            error_log('[GERAR_TREINO] Erro: Treino já existe para esta inscrição');
            $msg = 'Já existe um treino gerado para esta corrida. Cada corrida pode ter apenas um treino.';
        } else {
            error_log('[GERAR_TREINO] Erro: Participante já possui um treino (regra: um treino por participante)');
            $msg = 'Você já possui um treino gerado. Cada participante pode ter apenas um plano de treino no momento.';
        }
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $msg]);
        exit();
    }

    error_log('[GERAR_TREINO] Verificando anamnese...');
    $sql_anamnese = "SELECT * FROM anamneses 
                     WHERE usuario_id = ? 
                     AND (inscricao_id = ? OR inscricao_id IS NULL)
                     ORDER BY 
                         CASE WHEN inscricao_id = ? THEN 1 ELSE 2 END,
                         data_anamnese DESC
                     LIMIT 1";
    $stmt_anamnese = $pdo->prepare($sql_anamnese);
    $stmt_anamnese->execute([$usuario_id, $inscricao_id, $inscricao_id]);
    $anamnese = $stmt_anamnese->fetch(PDO::FETCH_ASSOC);

    if (!$anamnese) {
        error_log('[GERAR_TREINO] Erro: Anamnese não encontrada para esta inscrição ou usuário');
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Anamnese não encontrada. Preencha a anamnese antes de gerar o treino.']);
        exit();
    }

    error_log('[GERAR_TREINO] Anamnese encontrada - ID: ' . $anamnese['id']);

    $diferenca = $hoje->diff($evento_data);
    $semanas_restantes = max(1, (int)($diferenca->days / 7));
    $distancia_raw = $inscricao_data['distancia'] ?? 5;
    $distancia_km = is_numeric($distancia_raw) ? (int)$distancia_raw : (int)preg_replace('/[^0-9]/', '', $distancia_raw);

    error_log('[GERAR_TREINO] Dados calculados - Semanas restantes: ' . $semanas_restantes . ', Distância: ' . $distancia_km . 'km');

    // Função para extrair dias disponíveis da string de disponibilidade
    function extrairDiasDisponiveis($disponibilidade) {
        if (empty($disponibilidade)) {
            return [1, 2, 3, 4, 5, 6, 7]; // Todos os dias se não especificado
        }
        
        $disponibilidade_lower = strtolower(trim($disponibilidade));
        $dias_disponiveis = [];
        
        // Mapeamento de dias
        $dias_map = [
            'domingo' => 7, 'dom' => 7,
            'segunda' => 1, 'seg' => 1, 'segunda-feira' => 1,
            'terça' => 2, 'terca' => 2, 'ter' => 2, 'terça-feira' => 2, 'terca-feira' => 2,
            'quarta' => 3, 'qua' => 3, 'quarta-feira' => 3,
            'quinta' => 4, 'qui' => 4, 'quinta-feira' => 4,
            'sexta' => 5, 'sex' => 5, 'sexta-feira' => 5,
            'sábado' => 6, 'sabado' => 6, 'sab' => 6
        ];
        
        // Casos especiais
        if (strpos($disponibilidade_lower, 'todos os dias') !== false || strpos($disponibilidade_lower, 'todos') !== false) {
            return [1, 2, 3, 4, 5, 6, 7];
        }
        
        if (strpos($disponibilidade_lower, 'fins de semana') !== false || strpos($disponibilidade_lower, 'fim de semana') !== false) {
            return [6, 7];
        }
        
        if (strpos($disponibilidade_lower, 'dias úteis') !== false || strpos($disponibilidade_lower, 'dias uteis') !== false) {
            return [1, 2, 3, 4, 5];
        }
        
        // Padrão "segunda a sexta" ou "segunda a sexta-feira"
        if (preg_match('/(segunda|seg)[\s\-]*(a|até|ate|to)[\s\-]*(sexta|sex)/i', $disponibilidade_lower, $matches)) {
            return [1, 2, 3, 4, 5];
        }
        
        // Padrão "segunda, quarta, sexta" ou "segunda quarta sexta"
        $dias_encontrados = [];
        foreach ($dias_map as $dia_nome => $dia_id) {
            if (preg_match('/\b' . preg_quote($dia_nome, '/') . '\b/i', $disponibilidade_lower)) {
                if (!in_array($dia_id, $dias_encontrados)) {
                    $dias_encontrados[] = $dia_id;
                }
            }
        }
        
        if (!empty($dias_encontrados)) {
            sort($dias_encontrados);
            return $dias_encontrados;
        }
        
        // Se não encontrou padrão específico, retorna todos os dias
        return [1, 2, 3, 4, 5, 6, 7];
    }
    
    // Extrair dias disponíveis
    $dias_disponiveis = extrairDiasDisponiveis($anamnese['disponibilidade_horarios'] ?? '');
    $dias_disponiveis_str = implode(', ', $dias_disponiveis);
    error_log('[GERAR_TREINO] Dias disponíveis extraídos: ' . $dias_disponiveis_str);

    // Buscar dados do usuário para incluir no prompt
    $sql_usuario = "SELECT id, nome_completo FROM usuarios WHERE id = ? LIMIT 1";
    $stmt_usuario = $pdo->prepare($sql_usuario);
    $stmt_usuario->execute([$usuario_id]);
    $usuario_data = $stmt_usuario->fetch(PDO::FETCH_ASSOC);
    $nome_usuario = $usuario_data['nome_completo'] ?? 'Atleta';

    $prompt = "Você é um Profissional de Educação Física especialista em preparação para corridas de rua. Baseado em diretrizes ACSM, PubMed e diretrizes de treinamento para corrida.\n\n";
    $prompt .= "Gerar plano de treino semanal PERSONALIZADO e SEGURO para preparação de corrida. Segurança é prioridade máxima.\n\n";
    
    $prompt .= "DADOS DO ATLETA:\n";
    $prompt .= "- ID: {$usuario_id} | Nome: {$nome_usuario}\n";
    $prompt .= "- Peso: {$anamnese['peso']} kg | Altura: {$anamnese['altura']} cm | IMC: {$anamnese['imc']}\n";
    
    if (!empty($anamnese['fc_maxima'])) {
        $prompt .= "- FC Máxima: {$anamnese['fc_maxima']} bpm\n";
    }
    if (!empty($anamnese['vo2_max'])) {
        $prompt .= "- VO2 Max: {$anamnese['vo2_max']} ml/kg/min\n";
    }
    if (!empty($anamnese['zona_alvo_treino'])) {
        $prompt .= "- Zona Alvo de Treino: {$anamnese['zona_alvo_treino']}\n";
    }
    if (!empty($anamnese['max_glicemia'])) {
        $prompt .= "- Glicemia Máxima: {$anamnese['max_glicemia']} mg/dL\n";
    }
    
    $prompt .= "- Nível de Atividade: {$anamnese['nivel_atividade']}\n";
    
    if (!empty($anamnese['foco_primario'])) {
        $prompt .= "- Foco Primário: {$anamnese['foco_primario']}\n";
    }
    if (!empty($anamnese['objetivo_principal'])) {
        $prompt .= "- Objetivo Principal: {$anamnese['objetivo_principal']}\n";
    }
    if (!empty($anamnese['doencas_preexistentes'])) {
        $prompt .= "- Doenças Pré-existentes: {$anamnese['doencas_preexistentes']}\n";
    }
    if (!empty($anamnese['uso_medicamentos'])) {
        $prompt .= "- Uso de Medicamentos: {$anamnese['uso_medicamentos']}\n";
    }
    if (!empty($anamnese['limitacoes_fisicas'])) {
        $prompt .= "- Limitações Físicas: {$anamnese['limitacoes_fisicas']}\n";
    }
    if (!empty($anamnese['preferencias_atividades'])) {
        $prompt .= "- Preferências de Atividades: {$anamnese['preferencias_atividades']}\n";
    }
    if (!empty($anamnese['disponibilidade_horarios'])) {
        $prompt .= "- Disponibilidade de Horários: {$anamnese['disponibilidade_horarios']}\n";
    }
    
    // Adicionar instrução crítica sobre disponibilidade
    if (!empty($anamnese['disponibilidade_horarios'])) {
        $prompt .= "\n⚠️ CRÍTICO - DISPONIBILIDADE DE HORÁRIOS:\n";
        $prompt .= "O atleta está disponível APENAS nos seguintes dias da semana (dia_semana_id): {$dias_disponiveis_str}\n";
        $prompt .= "Você DEVE criar treinos SOMENTE nestes dias. NÃO crie treinos em outros dias.\n";
        $prompt .= "Dias permitidos: {$dias_disponiveis_str}\n";
        $prompt .= "Mapeamento: 1=Segunda, 2=Terça, 3=Quarta, 4=Quinta, 5=Sexta, 6=Sábado, 7=Domingo\n\n";
    }
    if (!empty($anamnese['historico_corridas'])) {
        $prompt .= "- Histórico de Corridas: {$anamnese['historico_corridas']}\n";
    }
    
    $data_hoje_formatada = $hoje->format('d/m/Y');
    $data_evento_formatada = $evento_data->format('d/m/Y');
    $dias_totais = $diferenca->days;
    
    $prompt .= "\n⏰ PERÍODO DE TREINAMENTO (CRÍTICO - LEIA COM ATENÇÃO):\n";
    $prompt .= "Data ATUAL (HOJE): {$data_hoje_formatada}\n";
    $prompt .= "Data da PROVA/EVENTO: {$data_evento_formatada}\n";
    $prompt .= "Período total: {$dias_totais} dias ({$semanas_restantes} semana(s))\n";
    $prompt .= "\n⚠️ IMPORTANTE: O treino que você criar DEVE ser aplicável EXATAMENTE neste período.\n";
    $prompt .= "Cada semana_numero corresponde a uma semana REAL do calendário:\n";
    $prompt .= "- semana_numero 1 = Primeira semana (começando em {$data_hoje_formatada})\n";
    if ($semanas_restantes > 1) {
        $semana_final_inicio = clone $hoje;
        $semana_final_inicio->modify('+' . (($semanas_restantes - 1) * 7) . ' days');
        $prompt .= "- semana_numero {$semanas_restantes} = Última semana (terminando em {$data_evento_formatada})\n";
    }
    $prompt .= "\nVocê DEVE considerar:\n";
    $prompt .= "1. A data atual ({$data_hoje_formatada}) como ponto de partida\n";
    $prompt .= "2. A data da prova ({$data_evento_formatada}) como objetivo final\n";
    $prompt .= "3. Criar treinos progressivos que preparem o atleta para a prova nesta data específica\n";
    $prompt .= "4. Cada semana deve ter treinos adequados ao momento da preparação (início, meio, fim)\n";
    
    $prompt .= "\nDADOS DA CORRIDA:\n";
    $prompt .= "- Distância: {$distancia_km} km\n";
    $prompt .= "- Evento: {$inscricao_data['evento_nome']}\n";
    $prompt .= "- Modalidade: {$inscricao_data['modalidade_nome']}\n";
    $prompt .= "- Data do evento: {$data_evento_formatada}\n";
    $prompt .= "- Semanas até o evento: {$semanas_restantes}\n";
    
    $prompt .= "\n📋 REGRAS DE QUANTIDADE DE TREINOS E MÚLTIPLAS SEMANAS:\n";
    if ($semanas_restantes <= 1) {
        $prompt .= "Para 1 semana: Crie 4-5 treinos distribuídos ao longo da semana.\n";
        $prompt .= "Cada treino DEVE ter o campo 'semana_numero' = 1.\n";
        $prompt .= "Estes treinos serão aplicados na semana que começa em {$data_hoje_formatada}.\n";
    } else {
        $prompt .= "⚠️ CRÍTICO: Você DEVE criar treinos para TODAS as {$semanas_restantes} semanas do período.\n";
        $prompt .= "Cada semana_numero (1, 2, 3, ..., {$semanas_restantes}) representa uma semana REAL do calendário.\n";
        $prompt .= "\nEstrutura obrigatória:\n";
        $prompt .= "- semana_numero 1: Primeira semana (início da preparação - adaptação)\n";
        if ($semanas_restantes > 2) {
            $prompt .= "- semana_numero 2 até " . ($semanas_restantes - 1) . ": Semanas intermediárias (desenvolvimento progressivo)\n";
        }
        $prompt .= "- semana_numero {$semanas_restantes}: Última semana (polimento e tapering antes da prova)\n";
        $prompt .= "\nPara cada semana:\n";
        $prompt .= "- Crie 4-6 treinos distribuídos nos dias disponíveis\n";
        $prompt .= "- Aplique progressão gradual: volume e intensidade aumentam nas semanas intermediárias\n";
        $prompt .= "- Última semana: reduza volume (tapering) mantendo intensidade para chegar descansado na prova\n";
        $prompt .= "- Cada treino DEVE ter o campo 'semana_numero' indicando a qual semana pertence (1, 2, 3, ..., {$semanas_restantes})\n";
    }
    $prompt .= "\nCampo dia_semana_id: 1=Segunda, 2=Terça, 3=Quarta, 4=Quinta, 5=Sexta, 6=Sábado, 7=Domingo.\n";
    $prompt .= "Campo semana_numero: OBRIGATÓRIO - número da semana (1 = primeira semana, 2 = segunda semana, etc.)\n";
    
    $prompt .= "\nREGRAS DE SEGURANÇA (CRÍTICO):\n";
    $prompt .= "Analise 'Limitações Físicas' e 'Doenças Pré-existentes'. Evite QUALQUER exercício que agrave a condição.\n\n";
    
    $tem_limitacoes = !empty($anamnese['limitacoes_fisicas']);
    if ($tem_limitacoes) {
        $limitacoes_lower = strtolower($anamnese['limitacoes_fisicas']);
        
        if (strpos($limitacoes_lower, 'joelho') !== false || strpos($limitacoes_lower, 'joelhos') !== false) {
            $prompt .= "LIMITAÇÕES - EXCLUSÕES (Joelho):\n";
            $prompt .= "- PROIBIDO: agachamento com salto, afundo profundo, burpees, leg press fechado, exercícios de alto impacto.\n";
            $prompt .= "- USE: agachamento isométrico, elevação pélvica, cadeira extensora com ROM controlado, exercícios de baixo impacto.\n\n";
        }
        
        if (strpos($limitacoes_lower, 'ombro') !== false || strpos($limitacoes_lower, 'ombros') !== false || strpos($limitacoes_lower, 'manguito') !== false) {
            $prompt .= "LIMITAÇÕES - EXCLUSÕES (Ombro):\n";
            $prompt .= "- PROIBIDO: desenvolvimento com barra, supino pesado, elevações laterais acima de 90 graus.\n";
            $prompt .= "- USE: remadas com retração escapular, rotação externa com elástico, flexões adaptadas.\n\n";
        }
        
        if (strpos($limitacoes_lower, 'coluna') !== false || strpos($limitacoes_lower, 'lombar') !== false || strpos($limitacoes_lower, 'hérnia') !== false || strpos($limitacoes_lower, 'hernia') !== false) {
            $prompt .= "LIMITAÇÕES - EXCLUSÕES (Coluna):\n";
            $prompt .= "- PROIBIDO: agachamento livre pesado, levantamento terra, good morning, exercícios com compressão axial.\n";
            $prompt .= "- USE: core isométrico (pranchas), elevação pélvica, máquinas com apoio total, exercícios de estabilização.\n\n";
        }
    }
    
    $prompt .= "OBRIGATÓRIO: Campo 'justificativa_adaptacoes' em cada treino explicando as modificações feitas (se houver limitações).\n\n";
    
    $prompt .= "INSTRUÇÕES ESPECÍFICAS PARA CORRIDA:\n";
    $prompt .= "- Tipos de treino a incluir: Corrida Longa, Intervalado, Fartlek, Tempo Run, Recuperação Ativa.\n";
    $prompt .= "- Progressão de volume: Aumentar gradualmente seguindo a regra dos 10% por semana.\n";
    $prompt .= "- Dias de descanso: Incluir descanso ativo (caminhada leve, alongamento, mobilidade).\n";
    $prompt .= "- Fortalecimento: 2-3x por semana, foco em membros inferiores (quadríceps, glúteos, panturrilhas) e core.\n";
    $prompt .= "- Mobilidade: Incluir exercícios de mobilidade articular e alongamento dinâmico.\n\n";
    
    $prompt .= "📝 INSTRUÇÕES JSON (OBRIGATÓRIO):\n";
    $prompt .= "1. Resposta DEVE ser um único objeto JSON válido\n";
    $prompt .= "2. Chave principal: 'treinos' (array de objetos)\n";
    $prompt .= "3. Cada treino DEVE conter todas as chaves do exemplo, incluindo 'semana_numero'\n";
    $prompt .= "4. parte_principal DEVE conter pelo menos 6-8 exercícios (mínimo 6, ideal 8+)\n";
    $prompt .= "5. Incluir 'bibliografia' com referências científicas como array\n";
    $prompt .= "6. Para corrida/cardio, fc_alvo é OBRIGATÓRIO\n";
    $prompt .= "7. Cada exercício DEVE ter campos detalhados (fc_alvo, tempo_execucao, tempo_recuperacao, tipo_recuperacao, carga, distancia, velocidade, etc.)\n";
    $prompt .= "8. ⚠️ CRÍTICO: Se há {$semanas_restantes} semanas, você DEVE criar treinos para TODAS as {$semanas_restantes} semanas\n";
    $prompt .= "9. Cada treino DEVE ter 'semana_numero' de 1 até {$semanas_restantes}\n";
    $prompt .= "10. Os treinos devem refletir a progressão do período de {$data_hoje_formatada} até {$data_evento_formatada}\n\n";
    
    $prompt .= "*EXEMPLO DA ESTRUTURA DE SAÍDA JSON (NÃO COPIE OS VALORES, APENAS A ESTRUTURA):*\n\n";
    $prompt .= "{\n";
    $prompt .= "  \"treinos\": [\n";
    $prompt .= "    {\n";
    $prompt .= "      \"nome\": \"Corrida Leve e Mobilidade\",\n";
    $prompt .= "      \"descricao\": \"Treino de adaptação com corrida leve e exercícios de mobilidade para preparação inicial.\",\n";
    $prompt .= "      \"nivel_dificuldade\": \"iniciante\",\n";
    $prompt .= "      \"dia_semana_id\": 1,\n";
    $prompt .= "      \"semana_numero\": 1,\n";
    $prompt .= "      \"justificativa_adaptacoes\": \"Treino adaptado para nível iniciante com foco em adaptação cardiovascular e mobilidade articular.\",\n";
    $prompt .= "      \"parte_inicial\": [\n";
    $prompt .= "        {\n";
    $prompt .= "          \"nome_item\": \"Aquecimento em Esteira\",\n";
    $prompt .= "          \"detalhes_item\": \"Caminhada leve progredindo para trote suave\",\n";
    $prompt .= "          \"fc_alvo\": \"50-60% FCmax (90-108 bpm)\",\n";
    $prompt .= "          \"tempo_execucao\": \"10 minutos\"\n";
    $prompt .= "        }\n";
    $prompt .= "      ],\n";
    $prompt .= "      \"parte_principal\": [\n";
    $prompt .= "        {\n";
    $prompt .= "          \"nome_item\": \"Corrida Contínua Leve\",\n";
    $prompt .= "          \"detalhes_item\": \"Corrida em ritmo conversacional, mantendo FC controlada\",\n";
    $prompt .= "          \"fc_alvo\": \"65-75% FCmax (117-135 bpm)\",\n";
    $prompt .= "          \"tempo_execucao\": \"20 minutos\",\n";
    $prompt .= "          \"distancia\": \"3-4 km\",\n";
    $prompt .= "          \"velocidade\": \"Ritmo conversacional\",\n";
    $prompt .= "          \"observacoes\": \"Manter respiração controlada, poder conversar durante a corrida\"\n";
    $prompt .= "        },\n";
    $prompt .= "        {\n";
    $prompt .= "          \"nome_item\": \"Agachamento Livre\",\n";
    $prompt .= "          \"detalhes_item\": \"3 séries de 15 repetições, foco na técnica\",\n";
    $prompt .= "          \"fc_alvo\": \"70-75% FCmax (126-135 bpm)\",\n";
    $prompt .= "          \"tempo_execucao\": \"45s por série\",\n";
    $prompt .= "          \"tempo_recuperacao\": \"60s\",\n";
    $prompt .= "          \"tipo_recuperacao\": \"passivo\",\n";
    $prompt .= "          \"carga\": \"Peso corporal\",\n";
    $prompt .= "          \"series\": \"3\",\n";
    $prompt .= "          \"repeticoes\": \"15\"\n";
    $prompt .= "        }\n";
    $prompt .= "        // ... mínimo 6-8 exercícios na parte_principal\n";
    $prompt .= "      ],\n";
    $prompt .= "      \"volta_calma\": [\n";
    $prompt .= "        {\n";
    $prompt .= "          \"nome_item\": \"Alongamento de Membros Inferiores\",\n";
    $prompt .= "          \"detalhes_item\": \"Alongamento suave para quadríceps, isquiotibiais e panturrilhas\",\n";
    $prompt .= "          \"tempo_execucao\": \"10 minutos\"\n";
    $prompt .= "        }\n";
    $prompt .= "      ],\n";
    $prompt .= "      \"volume_total\": \"45 minutos\",\n";
    $prompt .= "      \"grupos_musculares\": \"Cardiorrespiratório, Pernas, Core\",\n";
    $prompt .= "      \"fcmax\": \"65-75%\",\n";
    $prompt .= "      \"observacoes\": \"Qualquer sinal de dor ou desconforto, interrompa o exercício imediatamente.\"\n";
    $prompt .= "    }\n";
    $prompt .= "  ],\n";
    $prompt .= "  \"bibliografia\": [\n";
    $prompt .= "    \"ACSM Guidelines for Exercise Testing and Prescription, 11th Edition - https://www.acsm.org/read-research/books/acsms-guidelines-for-exercise-testing-and-prescription\",\n";
    $prompt .= "    \"Diretrizes de treinamento para corrida de rua - Referências científicas sobre periodização\"\n";
    $prompt .= "  ]\n";
    $prompt .= "}\n";

    $openaiKey = ConfigHelper::get('ai.openai.api_key');
    if (!$openaiKey) {
        // Fallback para .env se não estiver configurado no banco
        // Função envValue() já existe em db.php
        $openaiKey = envValue('OPENAI_API_KEY');
    }
    
    if (!$openaiKey) {
        error_log('[GERAR_TREINO] Erro: Chave da API OpenAI não configurada');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Chave da API OpenAI ausente. Entre em contato com o suporte.']);
        exit();
    }

    $model = ConfigHelper::get('ai.openai.model', 'gpt-4o');
    $temperature = ConfigHelper::get('ai.openai.temperature', 0.5);
    $maxTokens = ConfigHelper::get('ai.openai.max_tokens', 8000);
    $promptBase = ConfigHelper::get('ai.prompt_treino_base', 'Você é um Profissional de Educação Física especialista em preparação para corridas de rua, com conhecimento profundo em periodização de treinamento, fisiologia do exercício e prevenção de lesões. Você cria planos de treino personalizados, seguros e progressivos baseados em anamnese completa do atleta, distância da corrida e tempo disponível até o evento. Seu foco é preparar o atleta de forma segura, eficiente e sustentável para completar ou melhorar seu desempenho na corrida, respeitando limitações físicas e adaptando exercícios quando necessário. Você sempre inclui campos detalhados em cada exercício (fc_alvo, tempo_execucao, tempo_recuperacao, carga, etc.) e garante que a parte_principal tenha pelo menos 6-8 exercícios bem estruturados. IMPORTANTE: Retorne APENAS o JSON válido, sem texto adicional antes ou depois. O JSON deve estar completo e bem formatado.');

    error_log('[GERAR_TREINO] Chave OpenAI encontrada, preparando requisição...');
    error_log('[GERAR_TREINO] Tamanho do prompt: ' . strlen($prompt) . ' caracteres');
    error_log('[GERAR_TREINO] Primeiros 500 caracteres do prompt: ' . substr($prompt, 0, 500));

    $payload = [
        "model" => $model,
        "messages" => [
            ["role" => "system", "content" => $promptBase],
            ["role" => "user", "content" => $prompt]
        ],
        "temperature" => (float) $temperature,
        "max_tokens" => (int) $maxTokens
    ];
    
    error_log('[GERAR_TREINO] Payload preparado com max_tokens: 8000');

    $ch = curl_init("https://api.openai.com/v1/chat/completions");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: Bearer " . $openaiKey
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => (int) ConfigHelper::get('ai.timeout', 120),
        CURLOPT_CONNECTTIMEOUT => 30
    ]);

    error_log('[GERAR_TREINO] Enviando requisição para OpenAI...');
    $start_time = microtime(true);
    $raw_response = curl_exec($ch);
    $end_time = microtime(true);
    $request_duration = round($end_time - $start_time, 2);
    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    $curl_info = curl_getinfo($ch);
    curl_close($ch);

    error_log('[GERAR_TREINO] Requisição concluída em ' . $request_duration . ' segundos');
    error_log('[GERAR_TREINO] HTTP Code: ' . $httpCode);
    error_log('[GERAR_TREINO] Tamanho da resposta: ' . strlen($raw_response) . ' bytes');

    if ($raw_response === false) {
        error_log('[GERAR_TREINO] Erro cURL: ' . $curl_error);
        error_log('[GERAR_TREINO] Info cURL: ' . json_encode($curl_info));
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Erro ao conectar com a API OpenAI. Verifique sua conexão com a internet e tente novamente. Se o problema persistir, entre em contato com o suporte.'
        ]);
        exit();
    }

    if ($httpCode !== 200) {
        error_log('[GERAR_TREINO] Erro HTTP da OpenAI: ' . $httpCode);
        error_log('[GERAR_TREINO] Resposta completa: ' . substr($raw_response, 0, 2000));
        $error_data = json_decode($raw_response, true);
        $error_message = 'Erro desconhecido da API OpenAI';
        
        if (isset($error_data['error']['message'])) {
            $error_message = $error_data['error']['message'];
        } elseif (isset($error_data['error'])) {
            $error_message = is_string($error_data['error']) ? $error_data['error'] : json_encode($error_data['error']);
        }
        
        error_log('[GERAR_TREINO] Mensagem de erro: ' . $error_message);
        
        $user_message = 'Erro ao gerar treino via OpenAI. ';
        if ($httpCode === 429) {
            $user_message .= 'Muitas requisições. Aguarde alguns minutos e tente novamente.';
        } elseif ($httpCode === 401) {
            $user_message .= 'Erro de autenticação. Entre em contato com o suporte.';
        } elseif ($httpCode === 500 || $httpCode === 503) {
            $user_message .= 'Serviço temporariamente indisponível. Tente novamente em alguns minutos.';
        } else {
            $user_message .= 'Tente novamente. Se o problema persistir, entre em contato com o suporte.';
        }
        
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $user_message]);
        exit();
    }

    error_log('[GERAR_TREINO] Resposta recebida da OpenAI, processando...');
    error_log('[GERAR_TREINO] Tamanho da resposta: ' . strlen($raw_response) . ' bytes');
    
    $response_data = json_decode($raw_response, true);
    if (!$response_data) {
        error_log('[GERAR_TREINO] Erro: Resposta da OpenAI não é JSON válido');
        error_log('[GERAR_TREINO] Resposta (primeiros 1000 chars): ' . substr($raw_response, 0, 1000));
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao processar resposta da IA. A resposta não está no formato esperado. Tente novamente.']);
        exit();
    }
    
    if (!isset($response_data['choices'][0]['message']['content'])) {
        error_log('[GERAR_TREINO] Erro: Estrutura de resposta inválida');
        error_log('[GERAR_TREINO] Estrutura recebida: ' . json_encode(array_keys($response_data)));
        if (isset($response_data['error'])) {
            error_log('[GERAR_TREINO] Erro da API: ' . json_encode($response_data['error']));
        }
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Resposta da OpenAI inválida. A IA não retornou o conteúdo esperado. Tente novamente.']);
        exit();
    }

    // Verificar se a resposta foi completada ou truncada
    $finish_reason = $response_data['choices'][0]['finish_reason'] ?? 'unknown';
    error_log('[GERAR_TREINO] Finish reason: ' . $finish_reason);
    
    if ($finish_reason === 'length') {
        error_log('[GERAR_TREINO] AVISO CRÍTICO: Resposta foi truncada por limite de tokens!');
        error_log('[GERAR_TREINO] Tokens usados: ' . ($response_data['usage']['total_tokens'] ?? 'N/A'));
        error_log('[GERAR_TREINO] Prompt tokens: ' . ($response_data['usage']['prompt_tokens'] ?? 'N/A'));
        error_log('[GERAR_TREINO] Completion tokens: ' . ($response_data['usage']['completion_tokens'] ?? 'N/A'));
        
        // Tentar aumentar max_tokens na próxima tentativa ou avisar o usuário
        $assistant_content .= "\n\n[AVISO: Resposta pode estar incompleta devido ao limite de tokens]";
    }

    $assistant_content = $response_data['choices'][0]['message']['content'];
    error_log('[GERAR_TREINO] Tamanho do conteúdo da IA: ' . strlen($assistant_content) . ' caracteres');
    error_log('[GERAR_TREINO] Primeiros 500 caracteres do conteúdo: ' . substr($assistant_content, 0, 500));
    error_log('[GERAR_TREINO] Últimos 500 caracteres do conteúdo: ' . substr($assistant_content, -500));
    
    // Garantir que o diretório de logs existe
    $logs_dir = dirname(__DIR__) . '/../logs';
    if (!is_dir($logs_dir)) {
        @mkdir($logs_dir, 0755, true);
    }
    
    // Salvar resposta completa em arquivo temporário para debug (apenas se houver erro)
    $debug_file = $logs_dir . '/openai_response_' . date('Y-m-d_His') . '.txt';
    
    if (strpos($assistant_content, "\xEF\xBB\xBF") === 0) {
        $assistant_content = substr($assistant_content, 3);
    }

    // Extrair JSON da resposta - tentar múltiplas estratégias
    $json_treino_string = '';
    
    // Estratégia 1: Procurar por bloco de código JSON
    if (preg_match('/```json\s*(\{[\s\S]*?\})\s*```/', $assistant_content, $matches)) {
        $json_treino_string = trim($matches[1]);
        error_log('[GERAR_TREINO] JSON extraído usando estratégia 1 (bloco de código)');
    }
    // Estratégia 2: Procurar por objeto JSON que começa com {
    elseif (preg_match('/\{[\s\S]*"treinos"[\s\S]*?\}/', $assistant_content, $matches)) {
        $json_treino_string = trim($matches[0]);
        error_log('[GERAR_TREINO] JSON extraído usando estratégia 2 (regex direto)');
    }
    // Estratégia 3: Procurar do início até a bibliografia
    else {
        $content_parts = preg_split('/==Bibliografia Recomendada==|"bibliografia"/s', $assistant_content, 2);
        $content_before_biblio = trim($content_parts[0]);
        $json_treino_string = preg_replace('/^```json\s*/s', '', $content_before_biblio);
        $json_treino_string = preg_replace('/\s*```$/s', '', $json_treino_string);
        $json_treino_string = trim($json_treino_string);
        error_log('[GERAR_TREINO] JSON extraído usando estratégia 3 (antes da bibliografia)');
    }
    
    // Limpar caracteres de controle
    $json_treino_string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $json_treino_string);
    
    error_log('[GERAR_TREINO] Tamanho do JSON extraído: ' . strlen($json_treino_string) . ' caracteres');
    error_log('[GERAR_TREINO] Primeiros 500 caracteres: ' . substr($json_treino_string, 0, 500));
    error_log('[GERAR_TREINO] Últimos 500 caracteres: ' . substr($json_treino_string, -500));

    // Verificar e corrigir balanceamento de chaves e colchetes
    $chaves_abertas = substr_count($json_treino_string, '{');
    $chaves_fechadas = substr_count($json_treino_string, '}');
    $colchetes_abertos = substr_count($json_treino_string, '[');
    $colchetes_fechados = substr_count($json_treino_string, ']');
    
    error_log('[GERAR_TREINO] Contagem: { = ' . $chaves_abertas . ', } = ' . $chaves_fechadas . ', [ = ' . $colchetes_abertos . ', ] = ' . $colchetes_fechados);
    
    // Se o JSON parece estar cortado, tentar completar
    if ($chaves_abertas > $chaves_fechadas || $colchetes_abertos > $colchetes_fechados) {
        error_log('[GERAR_TREINO] AVISO: JSON parece estar incompleto');
        
        // Encontrar a última posição válida antes do corte
        $pos_ultimo_treino = strrpos($json_treino_string, '}');
        if ($pos_ultimo_treino !== false) {
            // Procurar o último objeto de treino completo
            $json_tentativa = substr($json_treino_string, 0, $pos_ultimo_treino + 1);
            
            // Verificar se precisa fechar arrays
            $chaves_abertas_tentativa = substr_count($json_tentativa, '{');
            $chaves_fechadas_tentativa = substr_count($json_tentativa, '}');
            $colchetes_abertos_tentativa = substr_count($json_tentativa, '[');
            $colchetes_fechados_tentativa = substr_count($json_tentativa, ']');
            
            // Fechar arrays primeiro, depois objetos
            if ($colchetes_abertos_tentativa > $colchetes_fechados_tentativa) {
                $json_tentativa .= str_repeat(']', $colchetes_abertos_tentativa - $colchetes_fechados_tentativa);
            }
            if ($chaves_abertas_tentativa > $chaves_fechadas_tentativa) {
                $json_tentativa .= str_repeat('}', $chaves_abertas_tentativa - $chaves_fechadas_tentativa);
            }
            
            // Se ainda não fechou o array treinos, adicionar
            if (strpos($json_tentativa, '"treinos"') !== false && substr_count($json_tentativa, ']') < substr_count($json_tentativa, '[')) {
                $json_tentativa = rtrim($json_tentativa, '}') . ']' . '}';
            }
            
            $json_treino_string = $json_tentativa;
            error_log('[GERAR_TREINO] JSON corrigido automaticamente');
        }
    }

    error_log('[GERAR_TREINO] Parseando JSON do treino...');
    $treino_json = json_decode($json_treino_string, true);
    $json_error = json_last_error();
    
    if ($json_error !== JSON_ERROR_NONE) {
        error_log('[GERAR_TREINO] Erro ao parsear JSON: ' . json_last_error_msg() . ' (código: ' . $json_error . ')');
        
        // Tentar uma última estratégia: encontrar o último treino completo e fechar tudo
        $pos_ultima_chave = strrpos($json_treino_string, '}');
        if ($pos_ultima_chave !== false) {
            // Pegar tudo até o último treino completo
            $json_tentativa = substr($json_treino_string, 0, $pos_ultima_chave + 1);
            
            // Verificar se tem o array treinos aberto e se precisa fechar
            if (strpos($json_tentativa, '"treinos"') !== false) {
                // Contar quantos arrays e objetos estão abertos
                $chaves_abertas_final = substr_count($json_tentativa, '{');
                $chaves_fechadas_final = substr_count($json_tentativa, '}');
                $colchetes_abertos_final = substr_count($json_tentativa, '[');
                $colchetes_fechados_final = substr_count($json_tentativa, ']');
                
                // Se o array treinos está aberto, fechar primeiro
                if ($colchetes_abertos_final > $colchetes_fechados_final) {
                    $json_tentativa .= str_repeat(']', $colchetes_abertos_final - $colchetes_fechados_final);
                }
                // Depois fechar o objeto principal
                if ($chaves_abertas_final > $chaves_fechadas_final) {
                    $json_tentativa .= str_repeat('}', $chaves_abertas_final - $chaves_fechadas_final);
                }
            }
            
            error_log('[GERAR_TREINO] Tentando última recuperação (tamanho: ' . strlen($json_tentativa) . ')');
            $treino_json = json_decode($json_tentativa, true);
            if ($treino_json && isset($treino_json['treinos'])) {
                error_log('[GERAR_TREINO] JSON recuperado com sucesso na última tentativa');
                $json_error = JSON_ERROR_NONE;
            }
        }
        
        if ($json_error !== JSON_ERROR_NONE) {
            error_log('[GERAR_TREINO] Salvando resposta completa em: ' . $debug_file);
            file_put_contents($debug_file, "=== RESPOSTA COMPLETA DA OPENAI ===\n\n" . $assistant_content . "\n\n=== JSON EXTRAÍDO ===\n\n" . $json_treino_string);
        }
    }
    
    if (!$treino_json || !isset($treino_json['treinos'])) {
        error_log('[GERAR_TREINO] Erro: JSON inválido ou sem treinos após todas as tentativas');
        error_log('[GERAR_TREINO] JSON parse error final: ' . json_last_error_msg());
        error_log('[GERAR_TREINO] Salvando resposta completa em: ' . $debug_file);
        file_put_contents($debug_file, "=== RESPOSTA COMPLETA DA OPENAI ===\n\n" . $assistant_content . "\n\n=== JSON EXTRAÍDO ===\n\n" . $json_treino_string);
        
        $error_message = 'A resposta da IA não pôde ser processada. ';
        if ($finish_reason === 'length') {
            $error_message .= 'O treino gerado foi muito longo e foi cortado. Tente novamente ou entre em contato com o suporte para ajustar o plano.';
        } else {
            $error_message .= 'O treino gerado pode estar incompleto ou mal formatado. Por favor, tente novamente. Se o problema persistir, entre em contato com o suporte.';
        }
        
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => $error_message
        ]);
        exit();
    }

    error_log('[GERAR_TREINO] JSON válido - ' . count($treino_json['treinos']) . ' treinos encontrados');

    // Processar estrutura de múltiplas semanas
    $treinos_processados = [];
    
    // Verificar se há estrutura programa_treino_semanal
    if (isset($treino_json['programa_treino_semanal']) && is_array($treino_json['programa_treino_semanal'])) {
        error_log('[GERAR_TREINO] Estrutura programa_treino_semanal detectada');
        foreach ($treino_json['programa_treino_semanal'] as $semana_num => $semana_treinos) {
            $semana_numero = is_numeric($semana_num) ? (int)$semana_num : (int)substr($semana_num, -1);
            if ($semana_numero < 1) $semana_numero = 1;
            
            if (is_array($semana_treinos)) {
                foreach ($semana_treinos as $treino) {
                    $treino['semana_numero'] = $semana_numero;
                    $treinos_processados[] = $treino;
                }
            }
        }
    } else if (isset($treino_json['treinos']) && is_array($treino_json['treinos'])) {
        // Estrutura direta com array de treinos
        foreach ($treino_json['treinos'] as $treino) {
            // Se não tem semana_numero, calcular baseado na posição
            if (!isset($treino['semana_numero']) || !is_numeric($treino['semana_numero'])) {
                // Calcular semana baseado em dia_semana_id (assumindo que dias 1-7 = semana 1, 8-14 = semana 2, etc.)
                $dia_semana_id = isset($treino['dia_semana_id']) ? (int)$treino['dia_semana_id'] : 1;
                $treino['semana_numero'] = max(1, (int)ceil($dia_semana_id / 7));
                error_log('[GERAR_TREINO] semana_numero calculado para treino: ' . $treino['semana_numero']);
            } else {
                $treino['semana_numero'] = (int)$treino['semana_numero'];
            }
            $treinos_processados[] = $treino;
        }
    }
    
    // Validar disponibilidade de horários
    if (!empty($anamnese['disponibilidade_horarios'])) {
        $treinos_invalidos = [];
        foreach ($treinos_processados as $idx => $treino) {
            $dia_semana_id = isset($treino['dia_semana_id']) ? (int)$treino['dia_semana_id'] : null;
            if ($dia_semana_id && !in_array($dia_semana_id, $dias_disponiveis)) {
                $treinos_invalidos[] = [
                    'index' => $idx,
                    'dia_semana_id' => $dia_semana_id,
                    'nome' => $treino['nome'] ?? 'Treino sem nome'
                ];
            }
        }
        
        if (!empty($treinos_invalidos)) {
            error_log('[GERAR_TREINO] AVISO: Treinos criados em dias não disponíveis: ' . json_encode($treinos_invalidos));
            // Remover treinos em dias não disponíveis
            $treinos_processados = array_filter($treinos_processados, function($treino) use ($dias_disponiveis) {
                $dia_semana_id = isset($treino['dia_semana_id']) ? (int)$treino['dia_semana_id'] : null;
                return $dia_semana_id && in_array($dia_semana_id, $dias_disponiveis);
            });
            $treinos_processados = array_values($treinos_processados); // Reindexar array
            error_log('[GERAR_TREINO] Treinos após filtro de disponibilidade: ' . count($treinos_processados));
        }
    }
    
    if (empty($treinos_processados)) {
        error_log('[GERAR_TREINO] Erro: Nenhum treino válido após processamento');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Nenhum treino válido foi gerado. Verifique se a disponibilidade de horários está correta e tente novamente.'
        ]);
        exit();
    }
    
    error_log('[GERAR_TREINO] Total de treinos processados: ' . count($treinos_processados));
    $semanas_unicas = array_unique(array_column($treinos_processados, 'semana_numero'));
    error_log('[GERAR_TREINO] Semanas únicas encontradas: ' . implode(', ', $semanas_unicas));

    // Processar bibliografia
    $bibliografia_array = [];
    if (isset($treino_json['bibliografia']) && is_array($treino_json['bibliografia'])) {
        $bibliografia_array = $treino_json['bibliografia'];
    } else if (isset($content_parts[1])) {
        // Tentar extrair bibliografia do texto após o separador
        $biblio_text = trim($content_parts[1]);
        $biblio_lines = explode("\n", $biblio_text);
        foreach ($biblio_lines as $line) {
            $line = trim($line);
            if (!empty($line) && (strpos($line, '-') === 0 || strpos($line, '*') === 0)) {
                $bibliografia_array[] = ltrim($line, '-* ');
            }
        }
    }
    
    // Se não encontrou bibliografia estruturada, usar padrão
    if (empty($bibliografia_array)) {
        $bibliografia_array = [
            "ACSM Guidelines for Exercise Testing and Prescription, 11th Edition",
            "Diretrizes de treinamento para corrida de rua"
        ];
    }
    
    $bibliografia = "==Bibliografia Recomendada==\n";
    foreach ($bibliografia_array as $ref) {
        $bibliografia .= "- " . $ref . "\n";
    }

    error_log('[GERAR_TREINO] Iniciando transação no banco de dados...');
    $pdo->beginTransaction();
    
    try {
        $sqlAnamnese = "SELECT id FROM anamneses 
                        WHERE usuario_id = ? 
                        AND (inscricao_id = ? OR inscricao_id IS NULL)
                        ORDER BY 
                            CASE WHEN inscricao_id = ? THEN 1 ELSE 2 END,
                            data_anamnese DESC
                        LIMIT 1";
        $stmtAnamnese = $pdo->prepare($sqlAnamnese);
        $stmtAnamnese->execute([$usuario_id, $inscricao_id, $inscricao_id]);
        $anamnese = $stmtAnamnese->fetch(PDO::FETCH_ASSOC);

        if (!$anamnese) {
            throw new Exception("Anamnese não encontrada para esta inscrição.");
        }

        $anamnese_id = $anamnese['id'];
        error_log('[GERAR_TREINO] Anamnese ID: ' . $anamnese_id);
        $foco_primario = $treinos_processados[0]['foco_primario'] ?? 'preparacao-corrida';
        $duracao_treino_geral = $treinos_processados[0]['volume_total'] ?? null;
        $equipamento_geral = $treinos_processados[0]['equipamento_principal'] ?? null;

        // Salvar bibliografia como JSON estruturado
        $bibliografia_json = json_encode($bibliografia_array, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $bibliografia_texto = $bibliografia; // Manter também como texto para compatibilidade

        $sqlInsertPlano = "INSERT INTO planos_treino_gerados (
            usuario_id, inscricao_id, anamnese_id, bibliografia_plano, foco_primario, duracao_treino_geral, equipamento_geral
        ) VALUES (
            :usuario_id, :inscricao_id, :anamnese_id, :bibliografia_plano, :foco_primario, :duracao_treino_geral, :equipamento_geral
        )";
        
        $stmtInsertPlano = $pdo->prepare($sqlInsertPlano);
        $stmtInsertPlano->execute([
            ':usuario_id' => $usuario_id,
            ':inscricao_id' => $inscricao_id,
            ':anamnese_id' => $anamnese_id,
            ':bibliografia_plano' => $bibliografia_texto,
            ':foco_primario' => $foco_primario,
            ':duracao_treino_geral' => $duracao_treino_geral,
            ':equipamento_geral' => $equipamento_geral
        ]);

        $plano_treino_gerado_id = $pdo->lastInsertId();
        error_log('[GERAR_TREINO] Plano criado - ID: ' . $plano_treino_gerado_id);

        $sqlInsertTreino = "INSERT INTO treinos (
            anamnese_id, usuario_id, plano_treino_gerado_id,
            nome, descricao, nivel_dificuldade, dia_semana_id, semana_numero,
            parte_inicial, parte_principal, volta_calma,
            fcmax, volume_total,
            grupos_musculares, numero_series, intervalo,
            numero_repeticoes, intensidade, carga_interna,
            observacoes, data_criacao, ativo
        ) VALUES (
            :anamnese_id, :usuario_id, :plano_treino_gerado_id,
            :nome, :descricao, :nivel_dificuldade, :dia_semana_id, :semana_numero,
            :parte_inicial, :parte_principal, :volta_calma,
            :fcmax, :volume_total,
            :grupos_musculares, :num_series, :intervalo,
            :num_repeticoes, :intensidade, :carga_interna,
            :observacoes, NOW(), 1
        )";
        
        $stmtInsertTreino = $pdo->prepare($sqlInsertTreino);

        foreach ($treinos_processados as $index => $diaTreino) {
            $nome = $diaTreino['nome'] ?? ('Treino - Dia ' . ($diaTreino['dia_semana_id'] ?? ($index + 1)));
            $descricao = $diaTreino['descricao'] ?? 'Treino gerado para preparação de corrida';
            $nivel_dificuldade = $diaTreino['nivel_dificuldade'] ?? 'intermediario';
            $dia_semana_id = $diaTreino['dia_semana_id'] ?? ($index + 1);
            $semana_numero = isset($diaTreino['semana_numero']) ? (int)$diaTreino['semana_numero'] : 1;
            $justificativa_adaptacoes = $diaTreino['justificativa_adaptacoes'] ?? null;
            
            // Validar quantidade mínima de exercícios na parte_principal
            $parte_principal_array = isset($diaTreino['parte_principal']) && is_array($diaTreino['parte_principal']) 
                ? $diaTreino['parte_principal'] 
                : [];
            $qtd_exercicios = count($parte_principal_array);
            if ($qtd_exercicios < 4) {
                error_log("[GERAR_TREINO] AVISO: Treino {$index} tem apenas {$qtd_exercicios} exercícios na parte_principal (mínimo recomendado: 6)");
            }

            $parte_inicial = isset($diaTreino['parte_inicial']) && is_array($diaTreino['parte_inicial'])
                ? json_encode($diaTreino['parte_inicial'], JSON_UNESCAPED_UNICODE)
                : ($diaTreino['parte_inicial'] ?? 'N/A');
            $parte_principal = isset($diaTreino['parte_principal']) && is_array($diaTreino['parte_principal'])
                ? json_encode($diaTreino['parte_principal'], JSON_UNESCAPED_UNICODE)
                : ($diaTreino['parte_principal'] ?? 'N/A');
            $volta_calma = isset($diaTreino['volta_calma']) && is_array($diaTreino['volta_calma'])
                ? json_encode($diaTreino['volta_calma'], JSON_UNESCAPED_UNICODE)
                : ($diaTreino['volta_calma'] ?? 'N/A');
            
            $volume_total = $diaTreino['volume_total'] ?? 'N/A';
            $grupos_musculares = $diaTreino['grupos_musculares'] ?? 'N/A';
            $numero_series = isset($diaTreino['numero_series']) ? (int)$diaTreino['numero_series'] : 3;
            $intervalo = $diaTreino['intervalo'] ?? 'N/A';
            $numero_repeticoes = $diaTreino['numero_repeticoes'] ?? 'N/A';
            $intensidade = $diaTreino['intensidade'] ?? 'N/A';
            $carga_interna = $diaTreino['carga_interna'] ?? 'N/A';

            $fcmax_val = isset($diaTreino['fcmax']) && is_numeric($diaTreino['fcmax']) ? (int)$diaTreino['fcmax'] : null;
            $observacoes_dia = $diaTreino['observacoes'] ?? '';
            
            // Adicionar justificativa de adaptações às observações se existir
            if (!empty($justificativa_adaptacoes)) {
                $observacoes_dia .= (!empty($observacoes_dia) ? "\n\n" : "") . "Justificativa de Adaptações:\n" . $justificativa_adaptacoes;
            }

            if ($index === 0 && !empty($bibliografia_texto)) {
                $observacoes_dia .= (!empty($observacoes_dia) ? "\n\n" : "") . "==Bibliografia Consultada==\n" . $bibliografia_texto;
            }
            if (empty($observacoes_dia)) {
                $observacoes_dia = null;
            }

            $stmtInsertTreino->execute([
                ':anamnese_id' => $anamnese_id,
                ':usuario_id' => $usuario_id,
                ':plano_treino_gerado_id' => $plano_treino_gerado_id,
                ':nome' => $nome,
                ':descricao' => $descricao,
                ':nivel_dificuldade' => $nivel_dificuldade,
                ':dia_semana_id' => $dia_semana_id,
                ':semana_numero' => $semana_numero,
                ':parte_inicial' => $parte_inicial,
                ':parte_principal' => $parte_principal,
                ':volta_calma' => $volta_calma,
                ':fcmax' => $fcmax_val,
                ':volume_total' => $volume_total,
                ':grupos_musculares' => $grupos_musculares,
                ':num_series' => $numero_series,
                ':intervalo' => $intervalo,
                ':num_repeticoes' => $numero_repeticoes,
                ':intensidade' => $intensidade,
                ':carga_interna' => $carga_interna,
                ':observacoes' => $observacoes_dia
            ]);

            $treino_id = $pdo->lastInsertId();

            $sqlInsertExercicio = "INSERT INTO treino_exercicios (
                treino_id, nome_exercicio, exercicio_id, series, repeticoes, tempo, peso, tempo_descanso, observacoes, tipo
            ) VALUES (
                :treino_id, :nome_exercicio, :exercicio_id, :series, :repeticoes, :tempo, :peso, :tempo_descanso, :observacoes, :tipo
            )";
            
            $stmtInsertExercicio = $pdo->prepare($sqlInsertExercicio);

            $inserirExerciciosParte = function($exerciciosParte) use ($stmtInsertExercicio, $treino_id, $pdo) {
                if (is_string($exerciciosParte)) {
                    $exerciciosParte = json_decode($exerciciosParte, true);
                }
                if (!is_array($exerciciosParte)) return;
                
                foreach ($exerciciosParte as $exercicio) {
                    $nome_exercicio = $exercicio['nome_item'] ?? $exercicio['nome'] ?? $exercicio['nome_exercicio'] ?? null;
                    
                    // Salvar todos os campos do exercício como JSON nas observações para preservar todos os dados
                    $observacoes = json_encode($exercicio, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    
                    // Extrair campos específicos
                    $series = $exercicio['series'] ?? $exercicio['numero_series'] ?? null;
                    $repeticoes = $exercicio['repeticoes'] ?? $exercicio['numero_repeticoes'] ?? null;
                    $tempo = $exercicio['tempo'] ?? $exercicio['tempo_execucao'] ?? null;
                    $peso = $exercicio['peso'] ?? $exercicio['carga'] ?? null;
                    $tempo_descanso = $exercicio['tempo_descanso'] ?? $exercicio['tempo_recuperacao'] ?? null;
                    $tipo = 'livre';
                    
                    // Tentar extrair de detalhes_item se não estiver nos campos diretos
                    if (isset($exercicio['detalhes_item'])) {
                        if ($series === null && preg_match('/(\d+)x(\d+)/i', $exercicio['detalhes_item'], $matches)) {
                            $series = $matches[1];
                            $repeticoes = $matches[2];
                            $tipo = 'repeticao';
                        } elseif ($series === null && preg_match('/(\d+)\s*séries?\s*de\s*(\d+)\s*repet/i', $exercicio['detalhes_item'], $matches)) {
                            $series = $matches[1];
                            $repeticoes = $matches[2];
                            $tipo = 'repeticao';
                        }
                        if ($tempo === null && preg_match('/(\d+)\s*min/i', $exercicio['detalhes_item'], $matches)) {
                            $tempo = $matches[1] . ' min';
                            $tipo = 'tempo';
                        } elseif ($tempo === null && preg_match('/(\d+)\s*seg/i', $exercicio['detalhes_item'], $matches)) {
                            $tempo = $matches[1] . ' seg';
                            $tipo = 'tempo';
                        }
                    }
                    
                    $exercicio_id = $exercicio['exercicio_id'] ?? null;
                    $series = $series !== null ? (is_numeric($series) ? (int)$series : $series) : null;
                    $repeticoes = $repeticoes !== null ? (string)$repeticoes : null;
                    $peso = $peso !== null ? (is_numeric($peso) ? (string)$peso : $peso) : null;
                    $tempo_descanso = $tempo_descanso !== null ? (string)$tempo_descanso : null;
                    
                    $stmtInsertExercicio->execute([
                        ':treino_id' => $treino_id,
                        ':nome_exercicio' => $nome_exercicio,
                        ':exercicio_id' => $exercicio_id,
                        ':series' => $series,
                        ':repeticoes' => $repeticoes,
                        ':tempo' => $tempo,
                        ':peso' => $peso,
                        ':tempo_descanso' => $tempo_descanso,
                        ':observacoes' => $observacoes,
                        ':tipo' => $tipo
                    ]);
                }
            };

            $inserirExerciciosParte(isset($diaTreino['parte_inicial']) && is_array($diaTreino['parte_inicial']) ? $diaTreino['parte_inicial'] : []);
            $inserirExerciciosParte(isset($diaTreino['parte_principal']) && is_array($diaTreino['parte_principal']) ? $diaTreino['parte_principal'] : []);
            $inserirExerciciosParte(isset($diaTreino['volta_calma']) && is_array($diaTreino['volta_calma']) ? $diaTreino['volta_calma'] : []);
            
            error_log('[GERAR_TREINO] Treino ' . ($index + 1) . ' salvo - ID: ' . $treino_id);
        }

        $pdo->commit();
        error_log('[GERAR_TREINO] Transação concluída com sucesso - Plano ID: ' . $plano_treino_gerado_id);

        echo json_encode([
            'success' => true,
            'message' => 'Treino gerado e salvo com sucesso.',
            'plano_id' => $plano_treino_gerado_id
        ]);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
            error_log('[GERAR_TREINO] Transação revertida devido a erro');
        }
        error_log('[GERAR_TREINO] Erro na transação: ' . $e->getMessage());
        throw $e;
    }

} catch (PDOException $e) {
    error_log('[GERAR_TREINO] Erro PDO: ' . $e->getMessage());
    error_log('[GERAR_TREINO] Stack trace: ' . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar treino no banco de dados. Tente novamente.']);
} catch (Exception $e) {
    error_log('[GERAR_TREINO] Erro geral: ' . $e->getMessage());
    error_log('[GERAR_TREINO] Stack trace: ' . $e->getTraceAsString());
    http_response_code(500);
    $message = $e->getMessage();
    if (strpos($message, 'OpenAI') !== false || strpos($message, 'API') !== false) {
        echo json_encode(['success' => false, 'message' => 'Erro ao gerar treino. Tente novamente mais tarde.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao gerar treino: ' . $message]);
    }
}

