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
    $plano_id = (int) ($input['plano_id'] ?? 0);
    $acao = $input['acao'] ?? 'publicar';

    if (!$plano_id) {
        throw new Exception('ID do plano e obrigatorio');
    }
    if (!in_array($acao, ['publicar', 'rascunho', 'arquivar'])) {
        throw new Exception('Acao invalida');
    }

    $stmt = $pdo->prepare("
        SELECT id, status FROM planos_treino_gerados 
        WHERE id = ? AND assessoria_id = ? LIMIT 1
    ");
    $stmt->execute([$plano_id, $assessoria_id]);
    $plano = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$plano) {
        throw new Exception('Plano nao encontrado');
    }

    $statusMap = [
        'publicar' => 'publicado',
        'rascunho' => 'rascunho',
        'arquivar' => 'arquivado'
    ];
    $novoStatus = $statusMap[$acao];

    $sql = "UPDATE planos_treino_gerados SET status = ?";
    $params = [$novoStatus];

    if ($acao === 'publicar') {
        $sql .= ", publicado_em = NOW()";
    }

    $sql .= " WHERE id = ?";
    $params[] = $plano_id;

    $pdo->prepare($sql)->execute($params);

    // Atualizar treinos vinculados
    if ($acao === 'publicar') {
        $pdo->prepare("UPDATE treinos SET status = 'ativo', publicado_em = NOW() WHERE plano_treino_gerado_id = ? AND assessoria_id = ?")
            ->execute([$plano_id, $assessoria_id]);
    }

    error_log("[PLANOS_PUBLISH] Plano {$plano_id} -> {$novoStatus} pela assessoria {$assessoria_id}");

    echo json_encode(['success' => true, 'message' => "Plano {$novoStatus} com sucesso"]);
} catch (Exception $e) {
    error_log("[PLANOS_PUBLISH] Erro: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
