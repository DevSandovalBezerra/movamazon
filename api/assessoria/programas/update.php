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
    $programa_id = (int) ($input['id'] ?? 0);

    if (!$programa_id) {
        throw new Exception('ID do programa e obrigatorio');
    }

    // Verificar propriedade
    $stmt = $pdo->prepare("SELECT id FROM assessoria_programas WHERE id = ? AND assessoria_id = ? LIMIT 1");
    $stmt->execute([$programa_id, $assessoria_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Programa nao encontrado');
    }

    $campos_permitidos = ['titulo', 'tipo', 'evento_id', 'data_inicio', 'data_fim', 'objetivo', 'metodologia', 'status'];
    $sets = [];
    $valores = [];

    foreach ($campos_permitidos as $campo) {
        if (array_key_exists($campo, $input)) {
            $valor = $input[$campo];
            if ($campo === 'status' && !in_array($valor, ['rascunho', 'ativo', 'encerrado'])) {
                throw new Exception('Status invalido');
            }
            if ($campo === 'tipo' && !in_array($valor, ['evento', 'continuo'])) {
                throw new Exception('Tipo invalido');
            }
            $sets[] = "`{$campo}` = ?";
            $valores[] = is_string($valor) ? trim($valor) : $valor;
        }
    }

    if (empty($sets)) {
        throw new Exception('Nenhum campo para atualizar');
    }

    $valores[] = $programa_id;
    $valores[] = $assessoria_id;
    $sql = "UPDATE assessoria_programas SET " . implode(', ', $sets) . " WHERE id = ? AND assessoria_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($valores);

    echo json_encode(['success' => true, 'message' => 'Programa atualizado com sucesso']);
} catch (Exception $e) {
    error_log("[PROGRAMAS_UPDATE] Erro: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
