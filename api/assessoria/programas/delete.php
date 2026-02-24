<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../assessoria/middleware.php';
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
    $programa_id = (int) ($input['id'] ?? 0);

    if (!$programa_id) {
        throw new Exception('ID do programa e obrigatorio');
    }

    $stmt = $pdo->prepare("
        SELECT p.id, p.status,
               (SELECT COUNT(*) FROM planos_treino_gerados ptg 
                WHERE ptg.programa_id = p.id AND ptg.status = 'publicado') as planos_publicados
        FROM assessoria_programas p
        WHERE p.id = ? AND p.assessoria_id = ?
        LIMIT 1
    ");
    $stmt->execute([$programa_id, $assessoria_id]);
    $programa = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$programa) {
        throw new Exception('Programa nao encontrado');
    }

    if ((int) $programa['planos_publicados'] > 0) {
        // Tem planos publicados: apenas encerrar
        $pdo->prepare("UPDATE assessoria_programas SET status = 'encerrado' WHERE id = ?")->execute([$programa_id]);
        echo json_encode(['success' => true, 'message' => 'Programa encerrado (possui planos publicados)']);
    } else {
        // Sem planos publicados: pode excluir
        $pdo->beginTransaction();
        $pdo->prepare("DELETE FROM assessoria_programa_atletas WHERE programa_id = ?")->execute([$programa_id]);
        $pdo->prepare("DELETE FROM assessoria_programas WHERE id = ? AND assessoria_id = ?")->execute([$programa_id, $assessoria_id]);
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Programa excluido com sucesso']);
    }

    error_log("[PROGRAMAS_DELETE] Programa {$programa_id} processado pela assessoria {$assessoria_id}");
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("[PROGRAMAS_DELETE] Erro: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
