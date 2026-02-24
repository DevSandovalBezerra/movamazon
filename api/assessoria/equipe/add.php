<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../middleware.php';
requireAssessoriaAdminAPI();

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
    $email = trim($input['email'] ?? '');
    $funcao = $input['funcao'] ?? 'assessor';

    if (empty($email)) {
        throw new Exception('Email e obrigatorio');
    }
    if (!in_array($funcao, ['assessor', 'suporte'])) {
        throw new Exception('Funcao deve ser assessor ou suporte');
    }

    // Buscar usuario pelo email
    $stmt = $pdo->prepare("SELECT id, nome_completo FROM usuarios WHERE email = ? AND status = 'ativo' LIMIT 1");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        throw new Exception('Usuario nao encontrado no MovAmazon. Verifique o email.');
    }

    // Verificar se ja faz parte da equipe
    $stmt = $pdo->prepare("SELECT id FROM assessoria_equipe WHERE assessoria_id = ? AND usuario_id = ? LIMIT 1");
    $stmt->execute([$assessoria_id, $usuario['id']]);
    if ($stmt->fetch()) {
        throw new Exception('Este usuario ja faz parte da equipe');
    }

    $pdo->beginTransaction();

    // Adicionar na equipe
    $stmt = $pdo->prepare("
        INSERT INTO assessoria_equipe (assessoria_id, usuario_id, funcao, status, created_at)
        VALUES (?, ?, ?, 'ativo', NOW())
    ");
    $stmt->execute([$assessoria_id, $usuario['id'], $funcao]);

    // Atribuir papel RBAC de assessor
    $stmt = $pdo->prepare("SELECT id FROM papeis WHERE nome = 'assessor' LIMIT 1");
    $stmt->execute();
    $papel = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($papel) {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO usuario_papeis (usuario_id, papel_id) VALUES (?, ?)
        ");
        $stmt->execute([$usuario['id'], $papel['id']]);
    }

    $pdo->commit();

    error_log("[ASSESSORIA_EQUIPE_ADD] Membro {$usuario['id']} adicionado como {$funcao} na assessoria {$assessoria_id}");

    echo json_encode([
        'success' => true,
        'message' => 'Membro adicionado com sucesso',
        'membro' => [
            'id' => $usuario['id'],
            'nome' => $usuario['nome_completo'],
            'funcao' => $funcao
        ]
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("[ASSESSORIA_EQUIPE_ADD] Erro: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
