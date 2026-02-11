<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../auth_middleware.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../helpers/config_helper.php';

header('Content-Type: application/json');

if (!requererAdmin(false)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

$key = isset($_GET['key']) ? trim($_GET['key']) : '';

if ($key === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Parâmetro key é obrigatório']);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT c.*, u.nome_completo AS atualizado_por FROM config c LEFT JOIN usuarios u ON u.id = c.updated_by WHERE c.chave = :chave LIMIT 1');
    $stmt->execute(['chave' => $key]);
    $config = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$config) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Configuração não encontrada']);
        exit;
    }

    $response = [
        'id' => (int) $config['id'],
        'chave' => $config['chave'],
        'valor' => ConfigHelper::castValue($config['valor'], $config['tipo']),
        'tipo' => $config['tipo'],
        'categoria' => $config['categoria'],
        'descricao' => $config['descricao'],
        'editavel' => (bool) $config['editavel'],
        'visivel' => (bool) $config['visivel'],
        'validacao' => $config['validacao'],
        'updated_at' => $config['updated_at'],
        'updated_by' => $config['updated_by'],
        'atualizado_por' => $config['atualizado_por']
    ];

    echo json_encode(['success' => true, 'data' => $response]);
} catch (Throwable $e) {
    error_log('[ADMIN_CONFIG_GET] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao carregar configuração']);
}

