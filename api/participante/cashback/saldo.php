<?php
/**
 * API de Consulta de Saldo e Histórico de Cashback
 * Retorna saldo disponível e lista de cashbacks do participante
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../helpers/cashback.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit();
}

$usuario_id = $_SESSION['user_id'];

try {
    // Registrar cashbacks pendentes antes de consultar (lazy loading)
    registrarCashbacksPendentes($pdo, $usuario_id);
    
    // Obter resumo
    $resumo = getResumoCashback($pdo, $usuario_id);
    
    // Obter histórico completo
    $historico = getHistoricoCashback($pdo, $usuario_id);
    
    // Formatar histórico para exibição
    foreach ($historico as &$item) {
        $item['valor_inscricao_formatado'] = 'R$ ' . number_format($item['valor_inscricao'], 2, ',', '.');
        $item['valor_cashback_formatado'] = 'R$ ' . number_format($item['valor_cashback'], 2, ',', '.');
        $item['data_credito_formatada'] = date('d/m/Y H:i', strtotime($item['data_credito']));
        
        if ($item['data_utilizacao']) {
            $item['data_utilizacao_formatada'] = date('d/m/Y H:i', strtotime($item['data_utilizacao']));
        }
        
        if ($item['evento_data']) {
            $item['evento_data_formatada'] = date('d/m/Y', strtotime($item['evento_data']));
        }
        
        // Status traduzido
        $statusTraduzido = [
            'disponivel' => 'Disponível',
            'utilizado' => 'Utilizado',
            'expirado' => 'Expirado',
            'pendente' => 'Pendente'
        ];
        $item['status_texto'] = $statusTraduzido[$item['status']] ?? $item['status'];
    }
    
    $response = [
        'success' => true,
        'resumo' => [
            'saldo_disponivel' => $resumo['saldo_disponivel'],
            'saldo_disponivel_formatado' => 'R$ ' . number_format($resumo['saldo_disponivel'], 2, ',', '.'),
            'total_acumulado' => $resumo['total_acumulado'],
            'total_acumulado_formatado' => 'R$ ' . number_format($resumo['total_acumulado'], 2, ',', '.'),
            'quantidade_disponivel' => $resumo['disponivel']['quantidade'],
            'quantidade_utilizado' => $resumo['utilizado']['quantidade'],
            'total_utilizado' => $resumo['utilizado']['total'],
            'total_utilizado_formatado' => 'R$ ' . number_format($resumo['utilizado']['total'], 2, ',', '.')
        ],
        'historico' => $historico,
        'total_registros' => count($historico)
    ];
    
    echo json_encode($response);

} catch (Exception $e) {
    error_log("[CASHBACK_SALDO] Erro: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar dados de cashback.']);
}

