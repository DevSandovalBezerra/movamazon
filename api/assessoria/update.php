<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/middleware.php';
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

    $campos_permitidos = [
        'nome_fantasia', 'razao_social', 'tipo',
        'email_contato', 'telefone_contato', 'site', 'instagram',
        'endereco', 'cidade', 'uf', 'cep'
    ];

    $sets = [];
    $valores = [];

    foreach ($campos_permitidos as $campo) {
        if (isset($input[$campo])) {
            $sets[] = "`{$campo}` = ?";
            $valores[] = trim($input[$campo]);
        }
    }

    if (empty($sets)) {
        throw new Exception('Nenhum campo para atualizar');
    }

    $valores[] = $assessoria_id;
    $sql = "UPDATE assessorias SET " . implode(', ', $sets) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($valores);

    error_log("[ASSESSORIA_UPDATE] Assessoria {$assessoria_id} atualizada por usuario " . $_SESSION['user_id']);

    echo json_encode([
        'success' => true,
        'message' => 'Dados atualizados com sucesso'
    ]);
} catch (Exception $e) {
    error_log("[ASSESSORIA_UPDATE] Erro: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
