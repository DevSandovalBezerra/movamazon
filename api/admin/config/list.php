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

$categoria = isset($_GET['categoria']) ? trim($_GET['categoria']) : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : null;

$where = [];
$params = [];

if ($categoria !== null && $categoria !== '') {
    $where[] = 'c.categoria = :categoria';
    $params['categoria'] = $categoria;
}

if ($search !== null && $search !== '') {
    $where[] = '(c.chave LIKE :search OR c.descricao LIKE :search)';
    $params['search'] = '%' . $search . '%';
}

$sql = "SELECT 
            c.id,
            c.chave,
            c.valor,
            c.tipo,
            c.categoria,
            c.descricao,
            c.editavel,
            c.visivel,
            c.updated_at,
            c.updated_by,
            u.nome_completo AS atualizado_por
        FROM config c
        LEFT JOIN usuarios u ON u.id = c.updated_by";

if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$sql .= ' ORDER BY c.categoria, c.chave';

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $configs = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $configs[] = [
            'id' => (int) $row['id'],
            'chave' => $row['chave'],
            'valor' => ConfigHelper::castValue($row['valor'], $row['tipo']),
            'tipo' => $row['tipo'],
            'categoria' => $row['categoria'],
            'descricao' => $row['descricao'],
            'editavel' => (bool) $row['editavel'],
            'visivel' => (bool) $row['visivel'],
            'updated_at' => $row['updated_at'],
            'updated_by' => $row['updated_by'],
            'atualizado_por' => $row['atualizado_por']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $configs
    ]);
} catch (Throwable $e) {
    error_log('[ADMIN_CONFIG_LIST] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao listar configurações']);
}

