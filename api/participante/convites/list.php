<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../auth/auth.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Nao autenticado']);
    exit;
}

try {
    $usuario_id = $_SESSION['user_id'];
    $status = $_GET['status'] ?? 'pendente';
    if (!in_array($status, ['pendente', 'todos'])) {
        $status = 'pendente';
    }

    // Atualizar convites expirados
    $pdo->prepare("
        UPDATE assessoria_convites 
        SET status = 'expirado' 
        WHERE atleta_usuario_id = ? AND status = 'pendente' AND expira_em < NOW()
    ")->execute([$usuario_id]);

    $sql = "
        SELECT c.id, c.status, c.mensagem, c.criado_em, c.expira_em,
               a.nome_fantasia as assessoria_nome, a.logo as assessoria_logo,
               a.cidade as assessoria_cidade, a.uf as assessoria_uf,
               env.nome_completo as enviado_por_nome
        FROM assessoria_convites c
        JOIN assessorias a ON c.assessoria_id = a.id
        JOIN usuarios env ON c.enviado_por_usuario_id = env.id
        WHERE c.atleta_usuario_id = ?
    ";
    $params = [$usuario_id];

    if ($status === 'pendente') {
        $sql .= " AND c.status = 'pendente'";
    } else {
        $sql .= " AND c.status != 'cancelado'";
    }

    $sql .= " ORDER BY c.criado_em DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $convites = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Contar pendentes
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM assessoria_convites 
        WHERE atleta_usuario_id = ? AND status = 'pendente' AND expira_em > NOW()
    ");
    $stmt->execute([$usuario_id]);
    $pendentes = (int) $stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'convites' => $convites,
        'pendentes' => $pendentes
    ]);
} catch (Exception $e) {
    error_log("[PARTICIPANTE_CONVITES_LIST] Erro: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno']);
}
