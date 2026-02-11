<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

error_log('📡 API participantes/list.php - Iniciando requisição');

// Verificar se o usuário está logado como organizador
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    error_log('❌ API participantes/list.php - Usuário não autorizado: ' . ($_SESSION['user_id'] ?? 'não definido') . ' - Papel: ' . ($_SESSION['papel'] ?? 'não definido'));
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit();
}

$ctx = requireOrganizadorContext($pdo);
$usuario_id = $ctx['usuario_id'];
$organizador_id = $ctx['organizador_id'];
$evento_id = isset($_GET['evento_id']) ? (int)$_GET['evento_id'] : null;

error_log('📋 API participantes/list.php - Organizador ID: ' . $organizador_id . ' - Evento ID: ' . ($evento_id ?? 'todos'));

try {
    error_log('🔍 API participantes/list.php - Buscando participantes');

    // Query base: JOIN por lote_inscricao_id para evitar duplicatas; LEFT JOIN pagamentos_ml para valor pago quando inscrição paga
    $sql = "SELECT 
                i.*,
                e.nome as evento_nome,
                e.data_inicio as data_evento,
                m.nome as modalidade_nome,
                u.nome_completo as participante_nome,
                u.email as participante_email,
                li.numero_lote,
                li.preco as preco_lote,
                (SELECT pm.valor_pago FROM pagamentos_ml pm WHERE pm.inscricao_id = i.id ORDER BY pm.data_atualizacao DESC LIMIT 1) as valor_pago_ml
            FROM inscricoes i
            INNER JOIN eventos e ON i.evento_id = e.id
            INNER JOIN modalidades m ON i.modalidade_evento_id = m.id
            INNER JOIN usuarios u ON i.usuario_id = u.id
            LEFT JOIN lotes_inscricao li ON i.lote_inscricao_id = li.id
            WHERE (e.organizador_id = ? OR e.organizador_id = ?) AND e.deleted_at IS NULL";

    $params = [$organizador_id, $usuario_id];

    // Se foi especificado um evento, filtrar por ele
    if ($evento_id) {
        error_log('🎯 API participantes/list.php - Filtrando por evento ID: ' . $evento_id);

        // Verificar se o evento pertence ao organizador
        $stmt = $pdo->prepare("SELECT id FROM eventos WHERE id = ? AND (organizador_id = ? OR organizador_id = ?) AND deleted_at IS NULL");
        $stmt->execute([$evento_id, $organizador_id, $usuario_id]);
        $evento = $stmt->fetch();

        if (!$evento) {
            error_log('❌ API participantes/list.php - Evento não encontrado ou não autorizado: Evento ID ' . $evento_id . ' - Organizador ID ' . $organizador_id);
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Evento não encontrado ou não autorizado']);
            exit();
        }

        $sql .= " AND i.evento_id = ?";
        $params[] = $evento_id;
    }

    $sql .= " ORDER BY i.data_inscricao DESC";

    error_log('📝 API participantes/list.php - SQL: ' . $sql);
    error_log('📋 API participantes/list.php - Params: ' . json_encode($params));

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $participantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    error_log('✅ API participantes/list.php - Participantes encontrados: ' . count($participantes));

    foreach ($participantes as &$participante) {
        $participante['status_class'] = getStatusClass($participante['status']);
        $participante['status_pagamento_class'] = getStatusPagamentoClass($participante['status_pagamento']);
        $participante['data_inscricao_formatada'] = date('d/m/Y H:i', strtotime($participante['data_inscricao']));
        $participante['data_evento_formatada'] = date('d/m/Y', strtotime($participante['data_evento']));

        // Valor: preco_lote > 0, senão valor_total, senão valor_pago_ml (evita R$ 0,00 quando pago)
        $valorLote = isset($participante['preco_lote']) ? (float) $participante['preco_lote'] : 0;
        $valorTotal = isset($participante['valor_total']) ? (float) $participante['valor_total'] : 0;
        $valorML = isset($participante['valor_pago_ml']) ? (float) $participante['valor_pago_ml'] : 0;
        $valor = ($valorLote > 0) ? $valorLote : (($valorTotal > 0) ? $valorTotal : $valorML);
        $participante['valor_formatado'] = 'R$ ' . number_format($valor, 2, ',', '.');

        // Adicionar informações do lote se disponível
        if (!empty($participante['numero_lote'])) {
            $participante['modalidade_nome'] = ($participante['modalidade_nome'] ?? '') . ' (Lote ' . $participante['numero_lote'] . ')';
        }
    }

    echo json_encode(['success' => true, 'data' => $participantes]);
} catch (Exception $e) {
    error_log('💥 API participantes/list.php - Erro: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
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
