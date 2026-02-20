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
    $status_filtro = $_GET['status'] ?? '';

    // Marcar convites expirados ANTES de listar
    $pdo->prepare("
        UPDATE assessoria_convites 
        SET status = 'expirado' 
        WHERE assessoria_id = ? AND status = 'pendente' AND expira_em < NOW()
    ")->execute([$assessoria_id]);

    $sql = "
        SELECT c.id, c.status, c.mensagem, c.criado_em, c.respondido_em, c.expira_em,
               u.nome_completo as atleta_nome, u.email as atleta_email,
               env.nome_completo as enviado_por_nome
        FROM assessoria_convites c
        JOIN usuarios u ON c.atleta_usuario_id = u.id
        JOIN usuarios env ON c.enviado_por_usuario_id = env.id
        WHERE c.assessoria_id = ?
    ";
    $params = [$assessoria_id];

    if ($status_filtro && in_array($status_filtro, ['pendente', 'aceito', 'recusado', 'expirado', 'cancelado'])) {
        $sql .= " AND c.status = ?";
        $params[] = $status_filtro;
    }

    $sql .= " ORDER BY c.criado_em DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $convites = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'convites' => $convites
    ]);
} catch (Exception $e) {
    error_log("[ASSESSORIA_CONVITES_LIST] Erro: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno']);
}
