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

$termoId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$organizadorId = isset($_GET['organizador_id']) ? (int)$_GET['organizador_id'] : null;

if (!$termoId && !$organizadorId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID do termo ou organizador_id é obrigatório']);
    exit;
}

try {
    if ($termoId) {
        // Buscar por ID
        $sql = "SELECT 
                    t.id,
                    t.organizador_id,
                    t.titulo,
                    t.conteudo,
                    t.versao,
                    t.ativo,
                    t.data_criacao,
                    o.id as organizador_id_table,
                    o.empresa,
                    u.nome_completo as organizador_nome,
                    u.email as organizador_email
                FROM termos_eventos t
                INNER JOIN organizadores o ON t.organizador_id = o.id
                INNER JOIN usuarios u ON o.usuario_id = u.id
                WHERE t.id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $termoId]);
    } else {
        // Buscar termo ativo do organizador
        $sql = "SELECT 
                    t.id,
                    t.organizador_id,
                    t.titulo,
                    t.conteudo,
                    t.versao,
                    t.ativo,
                    t.data_criacao,
                    o.id as organizador_id_table,
                    o.empresa,
                    u.nome_completo as organizador_nome,
                    u.email as organizador_email
                FROM termos_eventos t
                INNER JOIN organizadores o ON t.organizador_id = o.id
                INNER JOIN usuarios u ON o.usuario_id = u.id
                WHERE t.organizador_id = :organizador_id AND t.ativo = 1
                ORDER BY t.data_criacao DESC
                LIMIT 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['organizador_id' => $organizadorId]);
    }

    $termo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$termo) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Termo não encontrado']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'id' => (int)$termo['id'],
            'organizador_id' => (int)$termo['organizador_id'],
            'titulo' => $termo['titulo'],
            'conteudo' => $termo['conteudo'],
            'versao' => $termo['versao'],
            'ativo' => (bool)$termo['ativo'],
            'data_criacao' => $termo['data_criacao'],
            'organizador' => [
                'id' => (int)$termo['organizador_id_table'],
                'empresa' => $termo['empresa'],
                'nome' => $termo['organizador_nome'],
                'email' => $termo['organizador_email']
            ]
        ]
    ]);
} catch (Throwable $e) {
    error_log('[ADMIN_TERMOS_GET] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar termo']);
}

