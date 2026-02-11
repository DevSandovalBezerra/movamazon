<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../../db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

try {
    $organizadorId = $_SESSION['user_id'];
    $termoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($termoId <= 0) {
        throw new Exception('ID do termo é obrigatório');
    }

    $sql = "
        SELECT 
            t.*,
            e.nome as evento_nome,
            COALESCE(m.nome, '') as modalidade_nome
        FROM termos_eventos t
        INNER JOIN eventos e ON t.evento_id = e.id
        LEFT JOIN modalidades m ON t.modalidade_id = m.id
        WHERE t.id = ? AND e.organizador_id = ?
    ";

    $stmt = $pdo->prepare($sql);
    if (!$stmt) {
        throw new Exception('Erro ao preparar consulta SQL');
    }
    
    $stmt->execute([$termoId, $organizadorId]);
    $termo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$termo) {
        throw new Exception('Termo não encontrado ou não pertence a você');
    }

    // Garantir que todos os campos necessários existam
    $termo['id'] = (int)$termo['id'];
    $termo['evento_id'] = (int)$termo['evento_id'];
    $termo['modalidade_id'] = $termo['modalidade_id'] ? (int)$termo['modalidade_id'] : null;
    $termo['ativo'] = (int)$termo['ativo'];
    $termo['titulo'] = $termo['titulo'] ?? '';
    $termo['conteudo'] = $termo['conteudo'] ?? '';
    $termo['versao'] = $termo['versao'] ?? '1.0';

    echo json_encode([
        'success' => true,
        'termo' => $termo
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    error_log('Erro PDO ao buscar termo: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Erro ao buscar termo no banco de dados'
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    error_log('Erro ao buscar termo: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
