<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../assessoria/middleware.php';
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
    $programa_id = (int) ($input['programa_id'] ?? 0);
    $atleta_ids = $input['atleta_ids'] ?? [];

    if (!$programa_id) {
        throw new Exception('ID do programa e obrigatorio');
    }
    if (empty($atleta_ids) || !is_array($atleta_ids)) {
        throw new Exception('Selecione ao menos um atleta');
    }

    // Verificar propriedade do programa
    $stmt = $pdo->prepare("SELECT id FROM assessoria_programas WHERE id = ? AND assessoria_id = ? LIMIT 1");
    $stmt->execute([$programa_id, $assessoria_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Programa nao encontrado');
    }

    $pdo->beginTransaction();

    $adicionados = 0;
    $ja_vinculados = 0;

    $stmtCheck = $pdo->prepare("SELECT id FROM assessoria_programa_atletas WHERE programa_id = ? AND atleta_usuario_id = ? LIMIT 1");
    $stmtInsert = $pdo->prepare("
        INSERT INTO assessoria_programa_atletas (programa_id, atleta_usuario_id, status) VALUES (?, ?, 'ativo')
    ");
    $stmtVerify = $pdo->prepare("
        SELECT id FROM assessoria_atletas WHERE assessoria_id = ? AND atleta_usuario_id = ? AND status = 'ativo' LIMIT 1
    ");

    foreach ($atleta_ids as $atleta_id) {
        $atleta_id = (int) $atleta_id;
        if (!$atleta_id) continue;

        // Verificar se atleta pertence a assessoria
        $stmtVerify->execute([$assessoria_id, $atleta_id]);
        if (!$stmtVerify->fetch()) continue;

        // Verificar duplicata
        $stmtCheck->execute([$programa_id, $atleta_id]);
        if ($stmtCheck->fetch()) {
            $ja_vinculados++;
            continue;
        }

        $stmtInsert->execute([$programa_id, $atleta_id]);
        $adicionados++;
    }

    $pdo->commit();

    $msg = "{$adicionados} atleta(s) adicionado(s)";
    if ($ja_vinculados > 0) {
        $msg .= " ({$ja_vinculados} ja vinculado(s))";
    }

    echo json_encode(['success' => true, 'message' => $msg, 'adicionados' => $adicionados]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("[PROG_ATLETAS_ADD] Erro: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
