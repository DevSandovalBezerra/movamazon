<?php
session_start();
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../security_middleware.php';
require_once __DIR__ . '/../helpers/organizador_context.php';

// Contexto do organizador (organizador_id padrão + usuario_id legado)
$ctx = requireOrganizadorContext($pdo);
$usuario_id = $ctx['usuario_id'];
$organizador_id = $ctx['organizador_id'];

header('Content-Type: application/json; charset=utf-8');

try {
    $response = ['success' => true];

    // Gráfico 1: Inscrições por modalidade (com status)
    $sql_modalidades = "SELECT 
                        m.nome, 
                        SUM(CASE WHEN i.status = 'confirmada' AND i.status_pagamento = 'pago' THEN 1 ELSE 0 END) as confirmadas,
                        SUM(CASE WHEN i.status = 'confirmada' AND i.status_pagamento = 'pendente' THEN 1 ELSE 0 END) as pendentes_pagamento,
                        SUM(CASE WHEN i.status = 'pendente' THEN 1 ELSE 0 END) as pendentes_confirmacao,
                        SUM(CASE WHEN i.status = 'cancelada' OR i.status_pagamento IN ('cancelado', 'rejeitado') THEN 1 ELSE 0 END) as canceladas,
                        COUNT(i.id) as total
                        FROM inscricoes i 
                        JOIN modalidades m ON i.modalidade_evento_id = m.id 
                        JOIN eventos e ON i.evento_id = e.id
                        WHERE (e.organizador_id = ? OR e.organizador_id = ?) AND e.deleted_at IS NULL
                        GROUP BY m.nome
                        ORDER BY confirmadas DESC";
    $stmt = $pdo->prepare($sql_modalidades);
    $stmt->execute([$organizador_id, $usuario_id]);
    $response['inscricoesPorModalidade'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Gráfico 2: Receita por período (com status - últimos 30 dias)
    $sql_receita = "SELECT 
                    DATE(i.data_inscricao) as dia, 
                    SUM(CASE WHEN i.status = 'confirmada' AND i.status_pagamento = 'pago' THEN 1 ELSE 0 END) as inscricoes_confirmadas,
                    SUM(CASE WHEN i.status = 'confirmada' AND i.status_pagamento = 'pendente' THEN 1 ELSE 0 END) as inscricoes_pendentes,
                    SUM(CASE WHEN i.status = 'cancelada' OR i.status_pagamento IN ('cancelado', 'rejeitado') THEN 1 ELSE 0 END) as inscricoes_canceladas,
                    SUM(CASE WHEN i.status = 'confirmada' AND i.status_pagamento = 'pago' THEN i.valor_total ELSE 0 END) as receita_confirmada,
                    SUM(CASE WHEN i.status_pagamento = 'pendente' THEN i.valor_total ELSE 0 END) as receita_pendente
                    FROM inscricoes i 
                    JOIN eventos e ON i.evento_id = e.id
                    WHERE (e.organizador_id = ? OR e.organizador_id = ?) 
                      AND e.deleted_at IS NULL
                      AND i.data_inscricao >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                    GROUP BY dia 
                    ORDER BY dia ASC";
    $stmt = $pdo->prepare($sql_receita);
    $stmt->execute([$organizador_id, $usuario_id]);
    $response['receitaPorPeriodo'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Gráfico 3: Distribuição por status (Donut)
    $sql_status = "SELECT 
                    SUM(CASE WHEN i.status = 'confirmada' AND i.status_pagamento = 'pago' THEN 1 ELSE 0 END) as confirmadas_pagas,
                    SUM(CASE WHEN i.status = 'confirmada' AND i.status_pagamento = 'pendente' THEN 1 ELSE 0 END) as confirmadas_pendentes,
                    SUM(CASE WHEN i.status = 'pendente' THEN 1 ELSE 0 END) as pendentes_confirmacao,
                    SUM(CASE WHEN i.status = 'cancelada' OR i.status_pagamento IN ('cancelado', 'rejeitado') THEN 1 ELSE 0 END) as canceladas
                    FROM inscricoes i
                    JOIN eventos e ON i.evento_id = e.id
                    WHERE (e.organizador_id = ? OR e.organizador_id = ?) AND e.deleted_at IS NULL";
    $stmt = $pdo->prepare($sql_status);
    $stmt->execute([$organizador_id, $usuario_id]);
    $response['distribuicaoPorStatus'] = $stmt->fetch(PDO::FETCH_ASSOC);

    // Gráfico 4: Formas de pagamento (apenas confirmadas e pagas)
    $sql_formas_pagamento = "SELECT 
                            i.forma_pagamento,
                            COUNT(*) as total,
                            SUM(i.valor_total) as valor_total
                            FROM inscricoes i
                            JOIN eventos e ON i.evento_id = e.id
                            WHERE (e.organizador_id = ? OR e.organizador_id = ?) 
                            AND i.status = 'confirmada' 
                            AND i.status_pagamento = 'pago'
                            AND e.deleted_at IS NULL
                            AND i.forma_pagamento IS NOT NULL
                            GROUP BY i.forma_pagamento
                            ORDER BY total DESC";
    $stmt = $pdo->prepare($sql_formas_pagamento);
    $stmt->execute([$organizador_id, $usuario_id]);
    $response['formasPagamento'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
}
