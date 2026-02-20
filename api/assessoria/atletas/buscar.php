<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../middleware.php';
requireAssessorAPI();

$assessoria_id = getAssessoriaDoUsuario();
if (!$assessoria_id) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Assessoria nao encontrada']);
    exit;
}

try {
    $termo = trim($_GET['q'] ?? '');

    if (strlen($termo) < 2) {
        echo json_encode(['success' => true, 'atletas' => []]);
        exit;
    }

    $like = '%' . $termo . '%';

    // Buscar usuarios ativos que NAO sao da equipe desta assessoria
    $stmt = $pdo->prepare("
        SELECT u.id, u.nome_completo, u.email, u.telefone, u.documento,
               CASE 
                   WHEN aa.id IS NOT NULL THEN 'vinculado'
                   WHEN ac.id IS NOT NULL AND ac.status = 'pendente' THEN 'convite_pendente'
                   ELSE 'disponivel'
               END as vinculo_status
        FROM usuarios u
        LEFT JOIN assessoria_atletas aa 
            ON aa.atleta_usuario_id = u.id AND aa.assessoria_id = ? AND aa.status = 'ativo'
        LEFT JOIN assessoria_convites ac 
            ON ac.atleta_usuario_id = u.id AND ac.assessoria_id = ? AND ac.status = 'pendente'
        LEFT JOIN assessoria_equipe ae 
            ON ae.usuario_id = u.id AND ae.assessoria_id = ?
        WHERE u.status = 'ativo'
          AND ae.id IS NULL
          AND (u.nome_completo LIKE ? OR u.email LIKE ? OR u.documento LIKE ?)
        ORDER BY u.nome_completo ASC
        LIMIT 20
    ");
    $stmt->execute([$assessoria_id, $assessoria_id, $assessoria_id, $like, $like, $like]);
    $atletas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'atletas' => $atletas
    ]);
} catch (Exception $e) {
    error_log("[ASSESSORIA_ATLETAS_BUSCAR] Erro: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno']);
}
