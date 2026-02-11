<?php
header('Content-Type: application/json');
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

session_start();

if (!isset($_SESSION['papel']) && isset($_SESSION['user_role'])) {
    $_SESSION['papel'] = $_SESSION['user_role'];
}

if (!isset($_SESSION['user_id']) || ($_SESSION['papel'] ?? null) !== 'organizador') {
    http_response_code(401);
    echo json_encode(['error' => 'Acesso negado']);
    exit;
}

try {
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];
    
    $sql = "SELECT 
                fp.*,
                e.nome as evento_nome,
                COALESCE(e.data_realizacao, e.data_inicio) as data_evento
            FROM formas_pagamento_evento fp
            INNER JOIN eventos e ON fp.evento_id = e.id
            WHERE (e.organizador_id = ? OR e.organizador_id = ?) AND e.deleted_at IS NULL
            ORDER BY COALESCE(e.data_realizacao, e.data_inicio) DESC, fp.ordem ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$organizador_id, $usuario_id]);
    $pagamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($pagamentos as &$pagamento) {
        $pagamento['data_evento_formatada'] = date('d/m/Y', strtotime($pagamento['data_evento']));
        $pagamento['ativo_texto'] = $pagamento['ativo'] ? 'Ativo' : 'Inativo';
        $pagamento['ativo_class'] = $pagamento['ativo'] ? 'success' : 'danger';
        
        if ($pagamento['parcelamento_maximo'] > 1) {
            $pagamento['parcelamento_texto'] = 'Até ' . $pagamento['parcelamento_maximo'] . 'x';
        } else {
            $pagamento['parcelamento_texto'] = 'À vista';
        }
    }
    
    echo json_encode(['success' => true, 'data' => $pagamentos]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
?> 
