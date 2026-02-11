<?php
header('Content-Type: application/json');
require_once '../../db.php';
require_once __DIR__ . '/../../helpers/organizador_context.php';

// Iniciar sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticação
if (!isset($_SESSION['user_id']) || !isset($_SESSION['papel']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Acesso negado']);
    exit;
}

try {
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];
    $evento_id = isset($_GET['evento_id']) ? (int)$_GET['evento_id'] : null;
    
    // Log para debug
    error_log("Sessão - user_id: $organizador_id, papel: " . ($_SESSION['papel'] ?? 'não definido'));
    error_log("Evento ID solicitado: $evento_id");
    
    if (!$evento_id) {
        echo json_encode(['success' => false, 'error' => 'ID do evento é obrigatório']);
        exit;
    }
    
    $sql = "SELECT 
                rk.*,
                e.nome as evento_nome,
                e.data_inicio as data_evento,
                CONCAT(rk.data_retirada, ' ', rk.horario_inicio) as data_inicio,
                CONCAT(rk.data_retirada, ' ', rk.horario_fim) as data_fim,
                rk.local_retirada as local,
                rk.instrucoes_retirada as instrucoes,
                rk.documentos_necessarios
            FROM retirada_kits_evento rk
            INNER JOIN eventos e ON rk.evento_id = e.id
            WHERE (e.organizador_id = ? OR e.organizador_id = ?) AND e.deleted_at IS NULL AND e.id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$organizador_id, $usuario_id, $evento_id]);
    $locais = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Log para debug
    error_log("Query executada para evento_id: $evento_id, organizador_id: $organizador_id");
    error_log("Resultado da query: " . json_encode($locais));
    
    // Processar cada local
    $locaisProcessados = [];
    foreach ($locais as $local) {
        $local['data_evento_formatada'] = date('d/m/Y', strtotime($local['data_evento']));
        
        // Formatar data de início combinando data_retirada + horario_inicio
        if ($local['data_retirada'] && $local['horario_inicio']) {
            $data_inicio_completa = $local['data_retirada'] . ' ' . $local['horario_inicio'];
            $local['data_inicio'] = $data_inicio_completa;
            $local['data_inicio_formatada'] = date('d/m/Y H:i', strtotime($data_inicio_completa));
        }
        
        // Formatar data de fim combinando data_retirada + horario_fim
        if ($local['data_retirada'] && $local['horario_fim']) {
            $data_fim_completa = $local['data_retirada'] . ' ' . $local['horario_fim'];
            $local['data_fim'] = $data_fim_completa;
            $local['data_fim_formatada'] = date('d/m/Y H:i', strtotime($data_fim_completa));
        }
        
        $locaisProcessados[] = $local;
    }
    
    echo json_encode(['success' => true, 'data' => $locaisProcessados]);
    
} catch (Exception $e) {
    http_response_code(500);
    error_log("Erro em get.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Erro interno do servidor: ' . $e->getMessage()]);
}
?> 
