<?php
// Desabilitar exibição de erros para não quebrar JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../auth_middleware.php';
require_once __DIR__ . '/../../db.php';

header('Content-Type: application/json; charset=utf-8');

if (!requererAdmin(false)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

$status = isset($_GET['status']) ? trim($_GET['status']) : null;
$regiao = isset($_GET['regiao']) ? trim($_GET['regiao']) : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : null;
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = isset($_GET['per_page']) ? max(1, min(100, (int) $_GET['per_page'])) : 20;

$where = [];
$params = [];

$where[] = "(u.papel = 'organizador' OR o.id IS NOT NULL)";

if ($status !== null && $status !== '') {
    $where[] = 'u.status = :status';
    $params['status'] = $status;
}

if ($regiao !== null && $regiao !== '') {
    $where[] = 'o.regiao = :regiao';
    $params['regiao'] = $regiao;
}

if ($search !== null && $search !== '') {
    $where[] = '(u.nome_completo LIKE :search OR u.email LIKE :search OR o.empresa LIKE :search)';
    $params['search'] = '%' . $search . '%';
}

$sql = "SELECT DISTINCT
            u.id,
            u.nome_completo,
            u.email,
            u.telefone,
            u.celular,
            u.status,
            u.data_cadastro,
            o.id AS organizador_id,
            o.empresa,
            o.regiao,
            o.modalidade_esportiva,
            o.quantidade_eventos,
            (SELECT COUNT(DISTINCT e.id) FROM eventos e WHERE e.organizador_id = o.id) AS total_eventos
        FROM usuarios u
        LEFT JOIN organizadores o ON u.id = o.usuario_id";

if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$countSql = "SELECT COUNT(DISTINCT u.id) as total
             FROM usuarios u
             LEFT JOIN organizadores o ON u.id = o.usuario_id";

if (!empty($where)) {
    $countSql .= ' WHERE ' . implode(' AND ', $where);
}

try {
    $stmtCount = $pdo->prepare($countSql);
    $stmtCount->execute($params);
    $total = (int) $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($total / $perPage);

    $offset = ($page - 1) * $perPage;
    $sql .= ' ORDER BY u.data_cadastro DESC LIMIT :limit OFFSET :offset';

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue(':' . $key, $value);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $organizadores = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $organizadores[] = [
            'id' => (int) $row['id'],
            'nome_completo' => $row['nome_completo'],
            'email' => $row['email'],
            'telefone' => $row['telefone'],
            'celular' => $row['celular'],
            'status' => $row['status'],
            'data_cadastro' => $row['data_cadastro'],
            'organizador_id' => $row['organizador_id'] ? (int) $row['organizador_id'] : null,
            'empresa' => $row['empresa'],
            'regiao' => $row['regiao'],
            'modalidade_esportiva' => $row['modalidade_esportiva'],
            'quantidade_eventos' => $row['quantidade_eventos'],
            'total_eventos' => (int) $row['total_eventos']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $organizadores,
        'pagination' => [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => $totalPages
        ]
    ]);
} catch (Throwable $e) {
    error_log('[ADMIN_ORGANIZADORES_LIST] Erro: ' . $e->getMessage());
    error_log('[ADMIN_ORGANIZADORES_LIST] Stack trace: ' . $e->getTraceAsString());
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false, 
        'message' => 'Erro ao listar organizadores',
        'error' => $e->getMessage()
    ]);
}

