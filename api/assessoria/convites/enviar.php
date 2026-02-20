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
    $atleta_usuario_id = (int) ($input['atleta_usuario_id'] ?? 0);
    $mensagem = trim($input['mensagem'] ?? '');

    if (!$atleta_usuario_id) {
        throw new Exception('ID do atleta e obrigatorio');
    }

    // Verificar se o atleta existe e esta ativo
    $stmt = $pdo->prepare("SELECT id, nome_completo, email FROM usuarios WHERE id = ? AND status = 'ativo' LIMIT 1");
    $stmt->execute([$atleta_usuario_id]);
    $atleta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$atleta) {
        throw new Exception('Atleta nao encontrado ou inativo');
    }

    // Verificar se ja esta vinculado
    $stmt = $pdo->prepare("
        SELECT id FROM assessoria_atletas 
        WHERE assessoria_id = ? AND atleta_usuario_id = ? AND status = 'ativo' 
        LIMIT 1
    ");
    $stmt->execute([$assessoria_id, $atleta_usuario_id]);
    if ($stmt->fetch()) {
        throw new Exception('Este atleta ja esta vinculado a sua assessoria');
    }

    // Verificar se ja existe convite pendente
    $stmt = $pdo->prepare("
        SELECT id FROM assessoria_convites 
        WHERE assessoria_id = ? AND atleta_usuario_id = ? AND status = 'pendente' 
        LIMIT 1
    ");
    $stmt->execute([$assessoria_id, $atleta_usuario_id]);
    if ($stmt->fetch()) {
        throw new Exception('Ja existe um convite pendente para este atleta');
    }

    // Gerar token unico
    $token = bin2hex(random_bytes(32));

    // Criar convite (expira em 30 dias)
    $stmt = $pdo->prepare("
        INSERT INTO assessoria_convites 
            (assessoria_id, atleta_usuario_id, email_convidado, token, status, 
             enviado_por_usuario_id, mensagem, criado_em, expira_em)
        VALUES (?, ?, ?, ?, 'pendente', ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY))
    ");
    $stmt->execute([
        $assessoria_id,
        $atleta_usuario_id,
        $atleta['email'],
        $token,
        $_SESSION['user_id'],
        $mensagem ?: null
    ]);

    error_log("[ASSESSORIA_CONVITE] Convite enviado para atleta {$atleta_usuario_id} pela assessoria {$assessoria_id}");

    echo json_encode([
        'success' => true,
        'message' => 'Convite enviado com sucesso para ' . $atleta['nome_completo'],
        'convite' => [
            'token' => $token,
            'atleta' => $atleta['nome_completo']
        ]
    ]);
} catch (Exception $e) {
    error_log("[ASSESSORIA_CONVITE] Erro: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
