<?php
header('Content-Type: application/json');
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['error' => 'Acesso negado']);
    exit;
}

try {
    $evento_id = isset($_GET['evento_id']) ? (int)$_GET['evento_id'] : null;
    
    if (!$evento_id) {
        throw new Exception('ID do evento é obrigatório');
    }
    
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];
    
    // Verificar se o evento pertence ao organizador
    $stmt = $pdo->prepare("SELECT id FROM eventos WHERE id = ? AND (organizador_id = ? OR organizador_id = ?) AND deleted_at IS NULL");
    $stmt->execute([$evento_id, $organizador_id, $usuario_id]);
    
    if (!$stmt->fetch()) {
        throw new Exception('Evento não encontrado ou não pertence a você');
    }
    
    // Buscar tamanhos do evento
    $stmt = $pdo->prepare("
        SELECT 
            id,
            tamanho,
            quantidade_disponivel,
            quantidade_vendida,
            ativo,
            data_criacao
        FROM tamanhos_camisetas_evento
        WHERE evento_id = ? AND ativo = 1
        ORDER BY tamanho ASC
    ");
    $stmt->execute([$evento_id]);
    $tamanhos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcular estatísticas
    $total_disponivel = 0;
    $total_vendido = 0;
    
    foreach ($tamanhos as &$tamanho) {
        $total_disponivel += $tamanho['quantidade_disponivel'];
        $total_vendido += $tamanho['quantidade_vendida'];
        
        // Calcular percentual de ocupação
        $total_tamanho = $tamanho['quantidade_disponivel'] + $tamanho['quantidade_vendida'];
        if ($total_tamanho > 0) {
            $tamanho['ocupacao_percentual'] = round(($tamanho['quantidade_vendida'] / $total_tamanho) * 100, 1);
        } else {
            $tamanho['ocupacao_percentual'] = 0;
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'tamanhos' => $tamanhos,
            'total_disponivel' => $total_disponivel,
            'total_vendido' => $total_vendido,
            'total_geral' => $total_disponivel + $total_vendido
        ]
    ]);
    
} catch (Exception $e) {
    error_log('Erro ao listar tamanhos: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 
