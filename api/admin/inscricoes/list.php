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

$evento_id = isset($_GET['evento_id']) ? (int)$_GET['evento_id'] : null;
$status = isset($_GET['status']) ? trim($_GET['status']) : null;
$status_pagamento = isset($_GET['status_pagamento']) ? trim($_GET['status_pagamento']) : null;
$busca = isset($_GET['busca']) ? trim($_GET['busca']) : null;

try {
    // Query base: JOIN por lote_inscricao_id para evitar duplicatas (um lote por inscrição)
    $sql = "SELECT 
                i.*,
                e.nome as evento_nome,
                e.data_inicio as data_evento,
                m.nome as modalidade_nome,
                u.nome_completo as participante_nome,
                u.email as participante_email,
                li.numero_lote,
                li.preco as preco_lote
            FROM inscricoes i
            INNER JOIN eventos e ON i.evento_id = e.id
            INNER JOIN modalidades m ON i.modalidade_evento_id = m.id
            INNER JOIN usuarios u ON i.usuario_id = u.id
            LEFT JOIN lotes_inscricao li ON i.lote_inscricao_id = li.id
            WHERE e.deleted_at IS NULL";

    $params = [];

    // Filtros opcionais
    if ($evento_id) {
        $sql .= " AND i.evento_id = ?";
        $params[] = $evento_id;
    }

    if ($status && in_array($status, ['confirmada', 'pendente', 'cancelada'])) {
        $sql .= " AND i.status = ?";
        $params[] = $status;
    }

    if ($status_pagamento && in_array($status_pagamento, ['pendente', 'pago', 'cancelado', 'rejeitado', 'processando'])) {
        $sql .= " AND i.status_pagamento = ?";
        $params[] = $status_pagamento;
    }

    if ($busca) {
        $sql .= " AND (u.nome_completo LIKE ? OR u.email LIKE ? OR i.numero_inscricao LIKE ?)";
        $buscaParam = '%' . $busca . '%';
        $params[] = $buscaParam;
        $params[] = $buscaParam;
        $params[] = $buscaParam;
    }

    $sql .= " ORDER BY i.data_inscricao DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $inscricoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($inscricoes as &$inscricao) {
        $inscricao['status_class'] = getStatusClass($inscricao['status']);
        $inscricao['status_pagamento_class'] = getStatusPagamentoClass($inscricao['status_pagamento']);
        $inscricao['data_inscricao_formatada'] = date('d/m/Y H:i', strtotime($inscricao['data_inscricao']));
        $inscricao['data_evento_formatada'] = date('d/m/Y', strtotime($inscricao['data_evento']));

        // Usar valor do lote se disponível, senão usar valor_total
        $valor = $inscricao['preco_lote'] ?? $inscricao['valor_total'] ?? 0;
        $inscricao['valor_formatado'] = 'R$ ' . number_format($valor, 2, ',', '.');

        // Adicionar informações do lote se disponível
        if ($inscricao['numero_lote']) {
            $inscricao['modalidade_nome'] .= ' (Lote ' . $inscricao['numero_lote'] . ')';
        }
    }

    echo json_encode(['success' => true, 'data' => $inscricoes]);
} catch (Exception $e) {
    error_log('[ADMIN_INSCRICOES_LIST] Erro: ' . $e->getMessage());
    error_log('[ADMIN_INSCRICOES_LIST] Stack trace: ' . $e->getTraceAsString());
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false, 
        'message' => 'Erro ao listar inscrições',
        'error' => $e->getMessage()
    ]);
}

function getStatusClass($status)
{
    switch ($status) {
        case 'confirmada':
            return 'success';
        case 'pendente':
            return 'warning';
        case 'cancelada':
            return 'danger';
        default:
            return 'secondary';
    }
}

function getStatusPagamentoClass($status_pagamento)
{
    switch ($status_pagamento) {
        case 'pago':
            return 'success';
        case 'pendente':
            return 'warning';
        case 'processando':
            return 'info';
        case 'cancelado':
        case 'rejeitado':
            return 'danger';
        default:
            return 'secondary';
    }
}
