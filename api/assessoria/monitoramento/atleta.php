<?php
/**
 * Progresso detalhado de um atleta especifico
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../assessoria/middleware.php';
requireAssessorAPI();

$assessoria_id = getAssessoriaDoUsuario();
if (!$assessoria_id) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Assessoria nao encontrada']);
    exit;
}

try {
    $atleta_id = (int) ($_GET['atleta_id'] ?? 0);
    $periodo = $_GET['periodo'] ?? '30';
    if (!in_array($periodo, ['7', '30', '60', '90'])) $periodo = '30';

    if (!$atleta_id) {
        throw new Exception('ID do atleta e obrigatorio');
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

    // Dados do atleta
    $stmt = $pdo->prepare("SELECT nome_completo, email FROM usuarios WHERE id = ? LIMIT 1");
    $stmt->execute([$atleta_id]);
    $atleta = $stmt->fetch(PDO::FETCH_ASSOC);

    // Historico de progresso
    $stmt = $pdo->prepare("
        SELECT pt.id, pt.treino_id, pt.data_realizado, pt.percepcao_esforco,
               pt.duracao_minutos, pt.glicemia_pre_treino, pt.glicemia_pos_treino,
               pt.sinais_alerta_observados, pt.mal_estar_observado, pt.observacoes,
               pt.fonte, pt.feedback_atleta, pt.feedback_assessor,
               t.nome as treino_nome, t.dia_semana_id, t.semana_numero
        FROM progresso_treino pt
        LEFT JOIN treinos t ON pt.treino_id = t.id
        WHERE pt.usuario_id = ? AND pt.data_realizado >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
        ORDER BY pt.data_realizado DESC
    ");
    $stmt->execute([$atleta_id, (int) $periodo]);
    $historico = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Metricas agregadas
    $total_realizados = count($historico);
    $pse_values = array_filter(array_column($historico, 'percepcao_esforco'), fn($v) => $v !== null);
    $pse_medio = count($pse_values) > 0 ? round(array_sum($pse_values) / count($pse_values), 1) : 0;
    $duracao_values = array_filter(array_column($historico, 'duracao_minutos'), fn($v) => $v !== null && $v > 0);
    $duracao_media = count($duracao_values) > 0 ? round(array_sum($duracao_values) / count($duracao_values)) : 0;
    $alertas = array_filter($historico, fn($h) => ($h['percepcao_esforco'] ?? 0) >= 9 || $h['mal_estar_observado'] === 'sim');

    // Treinos previstos (para calcular aderencia)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM treinos 
        WHERE usuario_id = ? AND ativo = 1 AND (assessoria_id = ? OR assessoria_id IS NULL)
    ");
    $stmt->execute([$atleta_id, $assessoria_id]);
    $treinos_previstos = (int) $stmt->fetchColumn();

    // Calcular aderencia proporcional ao periodo
    $semanas_periodo = max(1, (int)$periodo / 7);
    $previstos_periodo = round($treinos_previstos * $semanas_periodo);
    $aderencia = $previstos_periodo > 0 ? min(100, round(($total_realizados / $previstos_periodo) * 100)) : 0;

    // PSE por dia (para grafico)
    $pse_grafico = [];
    foreach ($historico as $h) {
        if ($h['percepcao_esforco'] !== null) {
            $pse_grafico[] = [
                'data' => $h['data_realizado'],
                'pse' => (int) $h['percepcao_esforco']
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'atleta' => $atleta,
        'metricas' => [
            'total_realizados' => $total_realizados,
            'pse_medio' => $pse_medio,
            'duracao_media' => $duracao_media,
            'aderencia' => $aderencia,
            'total_alertas' => count($alertas)
        ],
        'historico' => $historico,
        'pse_grafico' => array_reverse($pse_grafico)
    ]);
} catch (Exception $e) {
    error_log("[MONITORAMENTO_ATLETA] Erro: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
