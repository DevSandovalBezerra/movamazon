<?php
/**
 * Assessor registra progresso de um treino para um atleta
 */
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../assessoria/middleware.php';
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

    $treino_id = (int) ($input['treino_id'] ?? 0);
    $atleta_id = (int) ($input['atleta_id'] ?? 0);
    $data_realizado = $input['data_realizado'] ?? date('Y-m-d');
    $percepcao_esforco = isset($input['percepcao_esforco']) ? (int) $input['percepcao_esforco'] : null;
    $duracao_minutos = isset($input['duracao_minutos']) ? (int) $input['duracao_minutos'] : null;
    $glicemia_pre = isset($input['glicemia_pre_treino']) ? (int) $input['glicemia_pre_treino'] : null;
    $glicemia_pos = isset($input['glicemia_pos_treino']) ? (int) $input['glicemia_pos_treino'] : null;
    $mal_estar = ($input['mal_estar_observado'] ?? 'nao') === 'sim' ? 'sim' : 'nao';
    $sinais_alerta = trim($input['sinais_alerta_observados'] ?? '');
    $observacoes = trim($input['observacoes'] ?? '');

    if (!$atleta_id) {
        throw new Exception('ID do atleta e obrigatorio');
    }
    if ($percepcao_esforco !== null && ($percepcao_esforco < 0 || $percepcao_esforco > 10)) {
        throw new Exception('PSE deve ser entre 0 e 10');
    }

    // Verificar vinculo
    $stmt = $pdo->prepare("
        SELECT id FROM assessoria_atletas 
        WHERE assessoria_id = ? AND atleta_usuario_id = ? AND status = 'ativo' LIMIT 1
    ");
    $stmt->execute([$assessoria_id, $atleta_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Atleta nao vinculado a esta assessoria');
    }

    // Verificar duplicata
    if ($treino_id) {
        $stmt = $pdo->prepare("
            SELECT id FROM progresso_treino 
            WHERE treino_id = ? AND usuario_id = ? AND data_realizado = ? LIMIT 1
        ");
        $stmt->execute([$treino_id, $atleta_id, $data_realizado]);
        if ($stmt->fetch()) {
            throw new Exception('Progresso ja registrado para este treino nesta data');
        }
    }

    $stmt = $pdo->prepare("
        INSERT INTO progresso_treino 
            (treino_id, usuario_id, data_realizado, percepcao_esforco, duracao_minutos,
             glicemia_pre_treino, glicemia_pos_treino, mal_estar_observado,
             sinais_alerta_observados, observacoes, fonte, registrado_por_usuario_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'assessor', ?)
    ");
    $stmt->execute([
        $treino_id ?: null, $atleta_id, $data_realizado,
        $percepcao_esforco, $duracao_minutos,
        $glicemia_pre, $glicemia_pos, $mal_estar,
        $sinais_alerta ?: null, $observacoes ?: null,
        $_SESSION['user_id']
    ]);

    echo json_encode(['success' => true, 'message' => 'Progresso registrado com sucesso']);
} catch (Exception $e) {
    error_log("[PROGRESSO_REGISTRAR] Erro: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
