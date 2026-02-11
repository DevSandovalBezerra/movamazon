<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../auth_middleware.php';
require_once __DIR__ . '/../../db.php';

header('Content-Type: application/json');

if (!requererAdmin(false)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'PATCH') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true) ?? [];

$termoId = isset($payload['id']) ? (int)$payload['id'] : 0;
$titulo = isset($payload['titulo']) ? trim($payload['titulo']) : null;
$conteudo = isset($payload['conteudo']) ? trim($payload['conteudo']) : null;
$versao = isset($payload['versao']) ? trim($payload['versao']) : null;
$ativo = isset($payload['ativo']) !== null ? ($payload['ativo'] === true || $payload['ativo'] === '1' || $payload['ativo'] === 1) : null;

if ($termoId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID do termo é obrigatório']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Verificar se o termo existe
    $stmtCheck = $pdo->prepare("SELECT organizador_id, ativo FROM termos_eventos WHERE id = :id LIMIT 1");
    $stmtCheck->execute(['id' => $termoId]);
    $termoAtual = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if (!$termoAtual) {
        throw new RuntimeException('Termo não encontrado');
    }

    $organizadorId = (int)$termoAtual['organizador_id'];
    $ativoAtual = (bool)$termoAtual['ativo'];

    // Se está ativando este termo, desativar outros termos ativos do mesmo organizador
    if ($ativo !== null && $ativo && !$ativoAtual) {
        $stmtDeactivate = $pdo->prepare("UPDATE termos_eventos SET ativo = 0 WHERE organizador_id = :organizador_id AND id != :termo_id AND ativo = 1");
        $stmtDeactivate->execute([
            'organizador_id' => $organizadorId,
            'termo_id' => $termoId
        ]);
    }

    // Montar query de atualização
    $updates = [];
    $params = ['id' => $termoId];

    if ($titulo !== null) {
        if (empty($titulo)) {
            throw new RuntimeException('Título não pode ser vazio');
        }
        $updates[] = 'titulo = :titulo';
        $params['titulo'] = $titulo;
    }

    if ($conteudo !== null) {
        if (empty($conteudo)) {
            throw new RuntimeException('Conteúdo não pode ser vazio');
        }
        $updates[] = 'conteudo = :conteudo';
        $params['conteudo'] = $conteudo;
    }

    if ($versao !== null) {
        $updates[] = 'versao = :versao';
        $params['versao'] = $versao;
    }

    if ($ativo !== null) {
        $updates[] = 'ativo = :ativo';
        $params['ativo'] = $ativo ? 1 : 0;
    }

    if (empty($updates)) {
        throw new RuntimeException('Nenhum campo para atualizar');
    }

    $sql = "UPDATE termos_eventos SET " . implode(', ', $updates) . " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Termo atualizado com sucesso',
        'data' => [
            'id' => $termoId
        ]
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[ADMIN_TERMOS_UPDATE] ' . $e->getMessage());
    http_response_code(500);
    $message = $e->getMessage();
    echo json_encode(['success' => false, 'message' => $message]);
}

