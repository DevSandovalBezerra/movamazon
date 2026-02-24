<?php
/**
 * Dashboard de monitoramento - metricas gerais da assessoria
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
    // IDs dos atletas vinculados
    $stmt = $pdo->prepare("SELECT atleta_usuario_id FROM assessoria_atletas WHERE assessoria_id = ? AND status = 'ativo'");
    $stmt->execute([$assessoria_id]);
    $atleta_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($atleta_ids)) {
        echo json_encode([
            'success' => true,
            'resumo' => [
                'total_atletas' => 0, 'treinos_realizados_semana' => 0,
                'treinos_previstos_semana' => 0, 'aderencia_geral' => 0,
                'pse_medio' => 0, 'alertas' => 0
            ],
            'atletas_aderencia' => [],
            'alertas_recentes' => []
        ]);
        exit;
    }

    $placeholders = implode(',', array_fill(0, count($atleta_ids), '?'));

    // Treinos realizados esta semana
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM progresso_treino 
        WHERE usuario_id IN ({$placeholders}) 
          AND data_realizado >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
    ");
    $stmt->execute($atleta_ids);
    $treinos_realizados = (int) $stmt->fetchColumn();

    // Treinos previstos esta semana (treinos ativos)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM treinos 
        WHERE usuario_id IN ({$placeholders}) AND ativo = 1
          AND (assessoria_id = ? OR assessoria_id IS NULL)
    ");
    $stmt->execute(array_merge($atleta_ids, [$assessoria_id]));
    $treinos_previstos = (int) $stmt->fetchColumn();

    // PSE medio ultimos 30 dias
    $stmt = $pdo->prepare("
        SELECT ROUND(AVG(percepcao_esforco), 1) FROM progresso_treino 
        WHERE usuario_id IN ({$placeholders}) 
          AND data_realizado >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
          AND percepcao_esforco IS NOT NULL
    ");
    $stmt->execute($atleta_ids);
    $pse_medio = (float) ($stmt->fetchColumn() ?: 0);

    // Alertas: PSE >= 9 ou mal_estar = 'sim' nos ultimos 7 dias
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM progresso_treino 
        WHERE usuario_id IN ({$placeholders}) 
          AND data_realizado >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
          AND (percepcao_esforco >= 9 OR mal_estar_observado = 'sim')
    ");
    $stmt->execute($atleta_ids);
    $total_alertas = (int) $stmt->fetchColumn();

    // Aderencia por atleta (ultimos 30 dias)
    $atletas_aderencia = [];
    foreach ($atleta_ids as $aid) {
        $stmt = $pdo->prepare("SELECT nome_completo FROM usuarios WHERE id = ? LIMIT 1");
        $stmt->execute([$aid]);
        $nome = $stmt->fetchColumn();

        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM treinos 
            WHERE usuario_id = ? AND ativo = 1 AND (assessoria_id = ? OR assessoria_id IS NULL)
        ");
        $stmt->execute([$aid, $assessoria_id]);
        $previstos = (int) $stmt->fetchColumn();

        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM progresso_treino 
            WHERE usuario_id = ? AND data_realizado >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ");
        $stmt->execute([$aid]);
        $realizados = (int) $stmt->fetchColumn();

        $aderencia = $previstos > 0 ? round(($realizados / $previstos) * 100, 0) : 0;
        if ($aderencia > 100) $aderencia = 100;

        $atletas_aderencia[] = [
            'usuario_id' => (int) $aid,
            'nome' => $nome,
            'treinos_previstos' => $previstos,
            'treinos_realizados' => $realizados,
            'aderencia' => $aderencia
        ];
    }

    usort($atletas_aderencia, fn($a, $b) => $a['aderencia'] <=> $b['aderencia']);

    // Alertas recentes detalhados (ultimos 7 dias)
    $stmt = $pdo->prepare("
        SELECT pt.id, pt.usuario_id, pt.data_realizado, pt.percepcao_esforco,
               pt.mal_estar_observado, pt.sinais_alerta_observados, pt.observacoes,
               pt.glicemia_pre_treino, pt.glicemia_pos_treino,
               u.nome_completo as atleta_nome,
               t.nome as treino_nome
        FROM progresso_treino pt
        JOIN usuarios u ON pt.usuario_id = u.id
        LEFT JOIN treinos t ON pt.treino_id = t.id
        WHERE pt.usuario_id IN ({$placeholders})
          AND pt.data_realizado >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
          AND (pt.percepcao_esforco >= 9 OR pt.mal_estar_observado = 'sim'
               OR pt.glicemia_pre_treino > 250 OR pt.glicemia_pos_treino > 250)
        ORDER BY pt.data_realizado DESC
        LIMIT 20
    ");
    $stmt->execute($atleta_ids);
    $alertas_recentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $aderencia_geral = count($atletas_aderencia) > 0
        ? round(array_sum(array_column($atletas_aderencia, 'aderencia')) / count($atletas_aderencia), 0)
        : 0;

    echo json_encode([
        'success' => true,
        'resumo' => [
            'total_atletas' => count($atleta_ids),
            'treinos_realizados_semana' => $treinos_realizados,
            'treinos_previstos_semana' => $treinos_previstos,
            'aderencia_geral' => $aderencia_geral,
            'pse_medio' => $pse_medio,
            'alertas' => $total_alertas
        ],
        'atletas_aderencia' => $atletas_aderencia,
        'alertas_recentes' => $alertas_recentes
    ]);
} catch (Exception $e) {
    error_log("[MONITORAMENTO_RESUMO] Erro: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno']);
}
