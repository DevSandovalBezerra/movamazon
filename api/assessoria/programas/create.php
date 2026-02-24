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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodo nao permitido']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);

    $titulo = trim($input['titulo'] ?? '');
    $tipo = $input['tipo'] ?? '';
    $evento_id = !empty($input['evento_id']) ? (int) $input['evento_id'] : null;
    $data_inicio = $input['data_inicio'] ?? null;
    $data_fim = $input['data_fim'] ?? null;
    $objetivo = trim($input['objetivo'] ?? '');
    $metodologia = trim($input['metodologia'] ?? '');

    if (!$titulo || strlen($titulo) < 3) {
        throw new Exception('Titulo e obrigatorio (minimo 3 caracteres)');
    }
    if (!in_array($tipo, ['evento', 'continuo'])) {
        throw new Exception('Tipo deve ser "evento" ou "continuo"');
    }
    if ($tipo === 'evento' && !$evento_id) {
        throw new Exception('Selecione um evento para programas do tipo evento');
    }

    $stmt = $pdo->prepare("
        INSERT INTO assessoria_programas 
            (assessoria_id, titulo, tipo, evento_id, data_inicio, data_fim, objetivo, metodologia, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'rascunho')
    ");
    $stmt->execute([
        $assessoria_id, $titulo, $tipo, $evento_id,
        $data_inicio ?: null, $data_fim ?: null,
        $objetivo ?: null, $metodologia ?: null
    ]);

    $programa_id = $pdo->lastInsertId();

    error_log("[PROGRAMAS_CREATE] Programa {$programa_id} criado pela assessoria {$assessoria_id}");

    echo json_encode([
        'success' => true,
        'message' => 'Programa criado com sucesso',
        'programa_id' => (int) $programa_id
    ]);
} catch (Exception $e) {
    error_log("[PROGRAMAS_CREATE] Erro: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
