<?php
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
    $programa_id = (int) ($_GET['id'] ?? 0);
    if (!$programa_id) {
        throw new Exception('ID do programa e obrigatorio');
    }

    // Programa
    $stmt = $pdo->prepare("
        SELECT p.*, e.titulo as evento_titulo, e.data_inicio as evento_data
        FROM assessoria_programas p
        LEFT JOIN eventos e ON p.evento_id = e.id
        WHERE p.id = ? AND p.assessoria_id = ?
        LIMIT 1
    ");
    $stmt->execute([$programa_id, $assessoria_id]);
    $programa = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$programa) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Programa nao encontrado']);
        exit;
    }

    // Atletas vinculados
    $stmt = $pdo->prepare("
        SELECT pa.id as vinculo_id, pa.status as vinculo_status, pa.created_at as vinculado_em,
               u.id as usuario_id, u.nome_completo, u.email
        FROM assessoria_programa_atletas pa
        JOIN usuarios u ON pa.atleta_usuario_id = u.id
        WHERE pa.programa_id = ?
        ORDER BY u.nome_completo ASC
    ");
    $stmt->execute([$programa_id]);
    $atletas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Planos gerados neste programa
    $stmt = $pdo->prepare("
        SELECT ptg.id, ptg.usuario_id, ptg.status, ptg.versao, ptg.data_criacao_plano,
               ptg.publicado_em, ptg.foco_primario, ptg.metodologia,
               u.nome_completo as atleta_nome,
               (SELECT COUNT(*) FROM treinos t WHERE t.plano_treino_gerado_id = ptg.id) as total_treinos
        FROM planos_treino_gerados ptg
        JOIN usuarios u ON ptg.usuario_id = u.id
        WHERE ptg.programa_id = ? AND ptg.assessoria_id = ?
        ORDER BY ptg.data_criacao_plano DESC
    ");
    $stmt->execute([$programa_id, $assessoria_id]);
    $planos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'programa' => $programa,
        'atletas' => $atletas,
        'planos' => $planos
    ]);
} catch (Exception $e) {
    error_log("[PROGRAMAS_GET] Erro: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno']);
}
