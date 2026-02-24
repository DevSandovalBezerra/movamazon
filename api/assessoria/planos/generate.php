<?php
/**
 * Gerar plano de treino para atleta via assessoria
 * Reutiliza a infraestrutura OpenAI do sistema principal
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../assessoria/middleware.php';
require_once __DIR__ . '/../../helpers/config_helper.php';

requireAssessorAPI();

$assessoria_id = getAssessoriaDoUsuario();
if (!$assessoria_id) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Assessoria nao encontrada']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo nao permitido']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $programa_id = (int) ($input['programa_id'] ?? 0);
    $atleta_id = (int) ($input['atleta_id'] ?? 0);
    $dias_semana = (int) ($input['dias_semana'] ?? 5);
    $semanas = (int) ($input['semanas'] ?? 0);
    $foco = trim($input['foco'] ?? '');
    $metodologia = trim($input['metodologia'] ?? '');
    $observacoes_assessor = trim($input['observacoes'] ?? '');

    if (!$programa_id || !$atleta_id) {
        throw new Exception('Programa e atleta sao obrigatorios');
    }
    if ($dias_semana < 2 || $dias_semana > 7) {
        $dias_semana = 5;
    }

    // Validar programa pertence a assessoria
    $stmt = $pdo->prepare("
        SELECT p.*, e.titulo as evento_titulo, 
               COALESCE(e.data_realizacao, e.data_inicio) as evento_data,
               m_sub.distancia as evento_distancia
        FROM assessoria_programas p
        LEFT JOIN eventos e ON p.evento_id = e.id
        LEFT JOIN modalidades m_sub ON e.id = m_sub.evento_id
        WHERE p.id = ? AND p.assessoria_id = ?
        LIMIT 1
    ");
    $stmt->execute([$programa_id, $assessoria_id]);
    $programa = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$programa) {
        throw new Exception('Programa nao encontrado');
    }

    // Validar atleta vinculado ao programa
    $stmt = $pdo->prepare("
        SELECT pa.id FROM assessoria_programa_atletas pa
        WHERE pa.programa_id = ? AND pa.atleta_usuario_id = ? AND pa.status = 'ativo'
        LIMIT 1
    ");
    $stmt->execute([$programa_id, $atleta_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Atleta nao esta vinculado a este programa');
    }

    // Dados do atleta
    $stmt = $pdo->prepare("SELECT nome_completo, email, data_nascimento, genero, telefone FROM usuarios WHERE id = ? LIMIT 1");
    $stmt->execute([$atleta_id]);
    $atleta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$atleta) {
        throw new Exception('Atleta nao encontrado');
    }

    // Anamnese do atleta (se existir)
    $stmt = $pdo->prepare("SELECT * FROM anamneses WHERE usuario_id = ? ORDER BY data_anamnese DESC LIMIT 1");
    $stmt->execute([$atleta_id]);
    $anamnese = $stmt->fetch(PDO::FETCH_ASSOC);

    // Calcular semanas ate o evento
    if ($semanas <= 0 && $programa['evento_data']) {
        $evento_dt = new DateTime($programa['evento_data']);
        $hoje = new DateTime();
        $diff = $hoje->diff($evento_dt);
        $semanas = max(1, (int) ceil($diff->days / 7));
        if ($semanas > 24) $semanas = 24;
    }
    if ($semanas <= 0) $semanas = 8;

    // Calcular idade
    $idade = '';
    if ($atleta['data_nascimento']) {
        $nascimento = new DateTime($atleta['data_nascimento']);
        $idade = $nascimento->diff(new DateTime())->y . ' anos';
    }

    // Montar prompt
    $prompt = montarPromptAssessoria(
        $atleta, $anamnese, $programa, $idade,
        $dias_semana, $semanas, $foco, $metodologia, $observacoes_assessor
    );

    // Chamar OpenAI
    $openaiKey = ConfigHelper::get('ai.openai.api_key');
    if (!$openaiKey) {
        $openaiKey = function_exists('envValue') ? envValue('OPENAI_API_KEY') : ($_ENV['OPENAI_API_KEY'] ?? '');
    }
    if (!$openaiKey) {
        throw new Exception('Chave da API OpenAI nao configurada');
    }

    $model = ConfigHelper::get('ai.openai.model', 'gpt-4o');
    $systemPrompt = 'Voce e um Profissional de Educacao Fisica especialista em preparacao para corridas de rua. '
        . 'Crie planos de treino personalizados, seguros e progressivos. '
        . 'Retorne APENAS JSON valido, sem texto adicional.';

    $payload = [
        "model" => $model,
        "messages" => [
            ["role" => "system", "content" => $systemPrompt],
            ["role" => "user", "content" => $prompt]
        ],
        "temperature" => 0.5,
        "max_tokens" => 8000
    ];

    error_log("[ASSESSORIA_GERAR] Gerando plano para atleta {$atleta_id} no programa {$programa_id}");

    $ch = curl_init("https://api.openai.com/v1/chat/completions");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: Bearer " . $openaiKey
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 120,
        CURLOPT_CONNECTTIMEOUT => 30
    ]);

    $raw = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($raw === false) {
        throw new Exception('Erro de conexao com OpenAI: ' . $curlError);
    }
    if ($httpCode !== 200) {
        $err = json_decode($raw, true);
        $msg = $err['error']['message'] ?? "Erro HTTP {$httpCode}";
        error_log("[ASSESSORIA_GERAR] Erro OpenAI: {$msg}");
        throw new Exception('Erro na API de IA. Tente novamente.');
    }

    $respData = json_decode($raw, true);
    $content = $respData['choices'][0]['message']['content'] ?? '';

    // Extrair JSON da resposta
    $treino_data = extrairJsonResposta($content);
    if (!$treino_data) {
        error_log("[ASSESSORIA_GERAR] Falha ao parsear resposta: " . substr($content, 0, 500));
        throw new Exception('Erro ao interpretar resposta da IA. Tente novamente.');
    }

    // Salvar no banco
    $pdo->beginTransaction();

    $planoDados = [
        'usuario_id' => $atleta_id,
        'profissional_id' => $_SESSION['user_id'],
        'assessoria_id' => $assessoria_id,
        'programa_id' => $programa_id,
        'foco_primario' => $foco ?: ($treino_data['foco_primario'] ?? 'Preparacao geral'),
        'duracao_treino_geral' => $treino_data['duracao_treino_geral'] ?? '60min',
        'dias_plano' => $dias_semana,
        'metodologia' => $metodologia ?: ($treino_data['metodologia'] ?? ''),
        'status' => 'rascunho',
        'versao' => 1,
        'schema_version' => '2.0'
    ];

    if ($anamnese) {
        $planoDados['anamnese_id'] = $anamnese['id'];
    }

    $cols = array_keys($planoDados);
    $placeholders = array_map(fn($c) => ':' . $c, $cols);
    $sql = "INSERT INTO planos_treino_gerados (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $placeholders) . ")";
    $stmt = $pdo->prepare($sql);
    foreach ($planoDados as $k => $v) {
        $stmt->bindValue(':' . $k, $v);
    }
    $stmt->execute();
    $plano_id = (int) $pdo->lastInsertId();

    // Salvar treinos individuais
    $treinos = $treino_data['treinos'] ?? $treino_data['sessoes'] ?? [];
    $treinos_salvos = 0;

    foreach ($treinos as $treino) {
        $stmt = $pdo->prepare("
            INSERT INTO treinos (
                usuario_id, plano_treino_gerado_id, nome, descricao,
                dia_semana_id, semana_numero,
                parte_inicial, parte_principal, volta_calma,
                volume_total, intensidade, observacoes,
                assessoria_id, programa_id, criado_por_usuario_id, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'rascunho')
        ");
        $stmt->execute([
            $atleta_id,
            $plano_id,
            $treino['nome'] ?? $treino['titulo'] ?? 'Treino',
            $treino['descricao'] ?? $treino['objetivo'] ?? '',
            $treino['dia_semana_id'] ?? $treino['dia_semana'] ?? null,
            $treino['semana_numero'] ?? $treino['semana'] ?? 1,
            is_array($treino['parte_inicial'] ?? null) ? json_encode($treino['parte_inicial']) : ($treino['parte_inicial'] ?? null),
            is_array($treino['parte_principal'] ?? null) ? json_encode($treino['parte_principal']) : ($treino['parte_principal'] ?? null),
            is_array($treino['volta_calma'] ?? null) ? json_encode($treino['volta_calma']) : ($treino['volta_calma'] ?? null),
            $treino['volume_total'] ?? $treino['volume'] ?? '',
            $treino['intensidade'] ?? '',
            $treino['observacoes'] ?? '',
            $assessoria_id,
            $programa_id,
            $_SESSION['user_id'],
        ]);
        $treinos_salvos++;
    }

    $pdo->commit();

    error_log("[ASSESSORIA_GERAR] Plano {$plano_id} criado com {$treinos_salvos} treinos");

    echo json_encode([
        'success' => true,
        'message' => "Plano gerado com {$treinos_salvos} sessoes de treino",
        'plano_id' => $plano_id,
        'total_treinos' => $treinos_salvos
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("[ASSESSORIA_GERAR] Erro: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// --- Funcoes auxiliares ---

function montarPromptAssessoria($atleta, $anamnese, $programa, $idade, $dias, $semanas, $foco, $metodologia, $obs) {
    $p = "Crie um plano de treino de corrida em formato JSON com a seguinte estrutura:\n\n";
    $p .= "ATLETA: {$atleta['nome_completo']}";
    if ($idade) $p .= ", {$idade}";
    if ($atleta['genero']) $p .= ", genero: {$atleta['genero']}";
    $p .= "\n";

    if ($anamnese) {
        $p .= "DADOS FISICOS:\n";
        if ($anamnese['peso'] ?? null) $p .= "- Peso: {$anamnese['peso']}kg\n";
        if ($anamnese['altura'] ?? null) $p .= "- Altura: {$anamnese['altura']}m\n";
        if ($anamnese['fc_repouso'] ?? null) $p .= "- FC repouso: {$anamnese['fc_repouso']}bpm\n";
        if ($anamnese['nivel_condicionamento'] ?? null) $p .= "- Nivel: {$anamnese['nivel_condicionamento']}\n";
        if ($anamnese['experiencia_corrida'] ?? null) $p .= "- Experiencia: {$anamnese['experiencia_corrida']}\n";
        if ($anamnese['limitacoes_fisicas'] ?? null) $p .= "- Limitacoes: {$anamnese['limitacoes_fisicas']}\n";
        if ($anamnese['doencas_cronicas'] ?? null) $p .= "- Condicoes: {$anamnese['doencas_cronicas']}\n";
    } else {
        $p .= "DADOS FISICOS: Nao disponivel (usar parametros conservadores)\n";
    }

    $p .= "\nPROGRAMA: {$programa['titulo']}\n";
    $p .= "- Tipo: {$programa['tipo']}\n";
    if ($programa['evento_titulo'] ?? null) {
        $p .= "- Evento: {$programa['evento_titulo']}\n";
        if ($programa['evento_data'] ?? null) $p .= "- Data do evento: {$programa['evento_data']}\n";
        if ($programa['evento_distancia'] ?? null) $p .= "- Distancia: {$programa['evento_distancia']}km\n";
    }
    if ($programa['objetivo'] ?? null) $p .= "- Objetivo do programa: {$programa['objetivo']}\n";

    $p .= "\nCONFIGURACOES:\n";
    $p .= "- Dias por semana: {$dias}\n";
    $p .= "- Total de semanas: {$semanas}\n";
    if ($foco) $p .= "- Foco: {$foco}\n";
    if ($metodologia) $p .= "- Metodologia: {$metodologia}\n";
    if ($obs) $p .= "- Observacoes do assessor: {$obs}\n";

    $p .= "\nRETORNE um JSON com esta estrutura exata:\n";
    $p .= "{\n";
    $p .= "  \"foco_primario\": \"string\",\n";
    $p .= "  \"duracao_treino_geral\": \"string (ex: 45-60min)\",\n";
    $p .= "  \"metodologia\": \"string\",\n";
    $p .= "  \"treinos\": [\n";
    $p .= "    {\n";
    $p .= "      \"nome\": \"string\",\n";
    $p .= "      \"descricao\": \"string\",\n";
    $p .= "      \"semana_numero\": 1,\n";
    $p .= "      \"dia_semana_id\": 2,\n";
    $p .= "      \"volume_total\": \"string (ex: 8km)\",\n";
    $p .= "      \"intensidade\": \"string (ex: moderada)\",\n";
    $p .= "      \"observacoes\": \"string\",\n";
    $p .= "      \"parte_inicial\": [{\"exercicio\": \"string\", \"duracao\": \"string\"}],\n";
    $p .= "      \"parte_principal\": [{\"exercicio\": \"string\", \"distancia\": \"string\", \"ritmo\": \"string\", \"observacao\": \"string\"}],\n";
    $p .= "      \"volta_calma\": [{\"exercicio\": \"string\", \"duracao\": \"string\"}]\n";
    $p .= "    }\n";
    $p .= "  ]\n";
    $p .= "}\n";
    $p .= "\ndia_semana_id: 1=Domingo, 2=Segunda, 3=Terca, 4=Quarta, 5=Quinta, 6=Sexta, 7=Sabado\n";
    $p .= "Gere {$dias} treinos por semana para {$semanas} semanas (total: " . ($dias * $semanas) . " sessoes).\n";

    return $p;
}

function extrairJsonResposta($content) {
    $content = trim($content);

    // Remover markdown code blocks
    if (preg_match('/```(?:json)?\s*([\s\S]*?)```/', $content, $m)) {
        $content = trim($m[1]);
    }

    $data = json_decode($content, true);
    if ($data && (isset($data['treinos']) || isset($data['sessoes']))) {
        return $data;
    }

    // Tentar extrair JSON por chaves
    $start = strpos($content, '{');
    $end = strrpos($content, '}');
    if ($start !== false && $end !== false && $end > $start) {
        $json = substr($content, $start, $end - $start + 1);
        $data = json_decode($json, true);
        if ($data && (isset($data['treinos']) || isset($data['sessoes']))) {
            return $data;
        }
    }

    return null;
}
