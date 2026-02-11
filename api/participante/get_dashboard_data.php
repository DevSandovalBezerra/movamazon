<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../security_middleware.php';
require_once __DIR__ . '/../helpers/cashback.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit();
}

$usuario_id = $_SESSION['user_id'];

try {
    $hoje = date('Y-m-d');
    
    // Registrar cashbacks pendentes (lazy loading - não afeta o fluxo principal)
    try {
        registrarCashbacksPendentes($pdo, $usuario_id);
    } catch (Exception $e) {
        error_log("[CASHBACK] Erro ao registrar cashbacks pendentes: " . $e->getMessage());
    }
    
    // Calcular saldo de cashback
    $saldoCashback = calcularSaldoCashback($pdo, $usuario_id);
    
    $sql = "
        SELECT 
            i.id as inscricao_id,
            i.numero_inscricao,
            i.status,
            i.status_pagamento,
            i.data_inscricao,
            e.id as evento_id,
            e.nome as evento_nome,
            COALESCE(e.data_realizacao, e.data_inicio) as evento_data,
            e.local as evento_local,
            e.imagem as evento_imagem,
            m.nome as modalidade_nome,
            m.distancia as modalidade_distancia,
            k.nome as kit_nome,
            k.id as kit_id
        FROM inscricoes i
        JOIN eventos e ON i.evento_id = e.id
        JOIN modalidades m ON i.modalidade_evento_id = m.id
        LEFT JOIN kits_eventos k ON i.kit_id = k.id
        WHERE i.usuario_id = ?
        ORDER BY COALESCE(e.data_realizacao, e.data_inicio) DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id]);
    $inscricoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $inscricoesAtivas = 0;
    $kitsPendentes = 0;
    $pagamentosOk = 0;

    foreach ($inscricoes as $inscricao) {
        $dataEvento = $inscricao['evento_data'];
        
        if ($dataEvento && $dataEvento >= $hoje && $inscricao['status'] !== 'cancelada' && $inscricao['status'] !== 'cancelado') {
            $inscricoesAtivas++;
        }
        
        if ($inscricao['status_pagamento'] === 'pago') {
            $pagamentosOk++;
            
            if (empty($inscricao['kit_nome']) || $inscricao['kit_id'] === null) {
                $kitsPendentes++;
            }
        }
    }

    $sqlEventos = "
        SELECT 
            e.id,
            e.nome,
            e.descricao,
            COALESCE(e.data_realizacao, e.data_inicio) as data_evento,
            e.local,
            e.cidade,
            e.estado,
            e.imagem,
            e.status
        FROM eventos e
        WHERE e.status = 'ativo' 
          AND e.deleted_at IS NULL
          AND COALESCE(e.data_realizacao, e.data_inicio) >= ?
        ORDER BY COALESCE(e.data_realizacao, e.data_inicio) ASC
        LIMIT 5
    ";

    $stmtEventos = $pdo->prepare($sqlEventos);
    $stmtEventos->execute([$hoje]);
    $eventos = $stmtEventos->fetchAll(PDO::FETCH_ASSOC);

    foreach ($eventos as &$evento) {
        if ($evento['imagem']) {
            if (strpos($evento['imagem'], 'http') !== 0) {
                $evento['imagem'] = 'frontend/assets/img/eventos/' . $evento['imagem'];
            }
        } else {
            $evento['imagem'] = 'frontend/assets/img/default-event.jpg';
        }
        
        $dataEvento = new DateTime($evento['data_evento']);
        $evento['data_formatada'] = $dataEvento->format('d/m/Y');
        $evento['local_formatado'] = $evento['local'] ?: ($evento['cidade'] . ', ' . $evento['estado']);
    }
    
    foreach ($inscricoes as &$inscricao) {
        if ($inscricao['evento_imagem']) {
            if (strpos($inscricao['evento_imagem'], 'http') !== 0) {
                $inscricao['evento_imagem'] = 'frontend/assets/img/eventos/' . $inscricao['evento_imagem'];
            }
        } else {
            $inscricao['evento_imagem'] = 'frontend/assets/img/default-event.jpg';
        }
    }

    $response = [
        'success' => true,
        'estatisticas' => [
            'inscricoes_ativas' => $inscricoesAtivas,
            'proximos_eventos' => count($eventos),
            'kits_pendentes' => $kitsPendentes,
            'pagamentos_ok' => $pagamentosOk,
            'saldo_cashback' => $saldoCashback
        ],
        'inscricoes' => array_slice($inscricoes, 0, 5),
        'eventos' => $eventos
    ];

    echo json_encode($response);

} catch (Exception $e) {
    error_log("[GET_DASHBOARD_DATA] Erro: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar dados do dashboard.']);
}

