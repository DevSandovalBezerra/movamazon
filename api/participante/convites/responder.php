<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../auth/auth.php';
require_once __DIR__ . '/../../db.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Nao autenticado']);
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
    $resposta = $input['resposta'] ?? '';
    $usuario_id = $_SESSION['user_id'];

    if (!$convite_id || !in_array($resposta, ['aceitar', 'recusar'])) {
        throw new Exception('Dados invalidos');
    }

    // Buscar convite
    $stmt = $pdo->prepare("
        SELECT c.*, a.nome_fantasia 
        FROM assessoria_convites c
        JOIN assessorias a ON c.assessoria_id = a.id
        WHERE c.id = ? AND c.atleta_usuario_id = ? AND c.status = 'pendente'
        LIMIT 1
    ");
    $stmt->execute([$convite_id, $usuario_id]);
    $convite = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$convite) {
        throw new Exception('Convite nao encontrado ou ja respondido');
    }

    // Verificar expiracao
    if ($convite['expira_em'] && strtotime($convite['expira_em']) < time()) {
        $pdo->prepare("UPDATE assessoria_convites SET status = 'expirado' WHERE id = ?")->execute([$convite_id]);
        throw new Exception('Este convite expirou');
    }

    $pdo->beginTransaction();

    if ($resposta === 'aceitar') {
        // Atualizar convite
        $stmt = $pdo->prepare("UPDATE assessoria_convites SET status = 'aceito', respondido_em = NOW() WHERE id = ?");
        $stmt->execute([$convite_id]);

        // Criar vinculo em assessoria_atletas (se nao existir)
        $stmt = $pdo->prepare("
            SELECT id FROM assessoria_atletas 
            WHERE assessoria_id = ? AND atleta_usuario_id = ? LIMIT 1
        ");
        $stmt->execute([$convite['assessoria_id'], $usuario_id]);

        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare("
                INSERT INTO assessoria_atletas 
                    (assessoria_id, atleta_usuario_id, origem, data_inicio, status, created_at)
                VALUES (?, ?, 'convite', CURDATE(), 'ativo', NOW())
            ");
            $stmt->execute([$convite['assessoria_id'], $usuario_id]);
        } else {
            // Reativar se existia encerrado
            $pdo->prepare("
                UPDATE assessoria_atletas SET status = 'ativo', data_inicio = CURDATE() 
                WHERE assessoria_id = ? AND atleta_usuario_id = ?
            ")->execute([$convite['assessoria_id'], $usuario_id]);
        }

        $pdo->commit();

        error_log("[CONVITE_ACEITO] Atleta {$usuario_id} aceitou convite da assessoria {$convite['assessoria_id']}");

        echo json_encode([
            'success' => true,
            'message' => 'Convite aceito! Voce agora faz parte da assessoria ' . $convite['nome_fantasia']
        ]);
    } else {
        // Recusar
        $stmt = $pdo->prepare("UPDATE assessoria_convites SET status = 'recusado', respondido_em = NOW() WHERE id = ?");
        $stmt->execute([$convite_id]);

        $pdo->commit();

        error_log("[CONVITE_RECUSADO] Atleta {$usuario_id} recusou convite da assessoria {$convite['assessoria_id']}");

        echo json_encode([
            'success' => true,
            'message' => 'Convite recusado'
        ]);
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("[CONVITE_RESPONDER] Erro: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
