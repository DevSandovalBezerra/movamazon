<?php
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

try {
    $programa_id = (int) ($_GET['programa_id'] ?? 0);
    $atleta_id = (int) ($_GET['atleta_id'] ?? 0);

    $sql = "
        SELECT ptg.id, ptg.usuario_id, ptg.programa_id, ptg.status, ptg.versao,
               ptg.data_criacao_plano, ptg.publicado_em, ptg.foco_primario,
               ptg.duracao_treino_geral, ptg.dias_plano, ptg.metodologia,
               u.nome_completo as atleta_nome,
               p.titulo as programa_titulo,
               (SELECT COUNT(*) FROM treinos t WHERE t.plano_treino_gerado_id = ptg.id) as total_treinos
        FROM planos_treino_gerados ptg
        JOIN usuarios u ON ptg.usuario_id = u.id
        LEFT JOIN assessoria_programas p ON ptg.programa_id = p.id
        WHERE ptg.assessoria_id = ?
    ";
    $params = [$assessoria_id];

    if ($programa_id) {
        $sql .= " AND ptg.programa_id = ?";
        $params[] = $programa_id;
    }
    if ($atleta_id) {
        $sql .= " AND ptg.usuario_id = ?";
        $params[] = $atleta_id;
    }

    $sql .= " ORDER BY ptg.data_criacao_plano DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $planos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'planos' => $planos]);
} catch (Exception $e) {
    error_log("[PLANOS_LIST] Erro: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno']);
}
