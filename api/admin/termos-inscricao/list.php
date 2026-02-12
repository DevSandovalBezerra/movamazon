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
$tipo = isset($_GET['tipo']) ? trim($_GET['tipo']) : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : null;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = isset($_GET['per_page']) ? max(1, min(100, (int)$_GET['per_page'])) : 20;

$where = [];
$params = [];

if ($status !== null && $status !== '') {
    $where[] = 't.ativo = :status';
    $params['status'] = $status === 'ativo' || $status === '1' ? 1 : 0;
}

if ($tipo !== null && $tipo !== '') {
    $where[] = 'COALESCE(t.tipo, \'inscricao\') = :tipo';
    $params['tipo'] = $tipo;
}

if ($search !== null && $search !== '') {
    $where[] = '(t.titulo LIKE :search OR t.conteudo LIKE :search)';
    $params['search'] = '%' . $search . '%';
}

$sql = "SELECT 
            t.id,
            t.titulo,
            t.conteudo,
            t.versao,
            t.ativo,
            COALESCE(t.tipo, 'inscricao') as tipo,
            t.data_criacao
        FROM termos_eventos t";

if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$countSql = "SELECT COUNT(*) as total FROM termos_eventos t";
if (!empty($where)) {
    $countSql .= ' WHERE ' . implode(' AND ', $where);
}

try {
    $stmtCount = $pdo->prepare($countSql);
    $stmtCount->execute($params);
    $total = (int)$stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($total / $perPage);

    $offset = ($page - 1) * $perPage;
    $sql .= ' ORDER BY t.data_criacao DESC LIMIT :limit OFFSET :offset';

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue(':' . $key, $value);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $termos = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $termos[] = [
            'id' => (int)$row['id'],
            'titulo' => $row['titulo'],
            'conteudo' => $row['conteudo'],
            'versao' => $row['versao'],
            'ativo' => (bool)$row['ativo'],
            'tipo' => $row['tipo'] ?? 'inscricao',
            'data_criacao' => $row['data_criacao']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $termos,
        'pagination' => [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => $totalPages
        ]
    ]);
} catch (Throwable $e) {
    error_log('[ADMIN_TERMOS_LIST] Erro: ' . $e->getMessage());
    error_log('[ADMIN_TERMOS_LIST] Stack trace: ' . $e->getTraceAsString());
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao listar termos',
        'error' => $e->getMessage()
    ]);
}
