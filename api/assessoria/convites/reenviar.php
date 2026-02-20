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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo nao permitido']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $convite_id = (int) ($input['convite_id'] ?? 0);

    if (!$convite_id) {
        throw new Exception('ID do convite e obrigatorio');
    }

    $stmt = $pdo->prepare("
        SELECT id, status, atleta_usuario_id, assessoria_id 
        FROM assessoria_convites 
        WHERE id = ? AND assessoria_id = ? LIMIT 1
    ");
    $stmt->execute([$convite_id, $assessoria_id]);
    $convite = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$convite) {
        throw new Exception('Convite nao encontrado');
    }
    if (!in_array($convite['status'], ['expirado', 'recusado', 'cancelado'])) {
        throw new Exception('Apenas convites expirados, recusados ou cancelados podem ser reenviados');
    }

    $pdo->beginTransaction();

    // Marcar antigo como cancelado
    $pdo->prepare("UPDATE assessoria_convites SET status = 'cancelado' WHERE id = ?")->execute([$convite_id]);

    // Buscar email do atleta
    $token = bin2hex(random_bytes(32));
    $stmt = $pdo->prepare("SELECT email FROM usuarios WHERE id = ? LIMIT 1");
    $stmt->execute([$convite['atleta_usuario_id']]);
    $email = $stmt->fetchColumn();

    // Criar novo convite
    $stmt = $pdo->prepare("
        INSERT INTO assessoria_convites 
            (assessoria_id, atleta_usuario_id, email_convidado, token, status, 
             enviado_por_usuario_id, criado_em, expira_em)
        VALUES (?, ?, ?, ?, 'pendente', ?, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY))
    ");
    $stmt->execute([
        $assessoria_id,
        $convite['atleta_usuario_id'],
        $email,
        $token,
        $_SESSION['user_id']
    ]);

    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Convite reenviado com sucesso']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
