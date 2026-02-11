<?php
header('Content-Type: application/json');
require_once '../../db.php';
require_once '../../helpers/organizador_context.php';

session_start();

try {
    $kit_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($kit_id <= 0) {
        throw new Exception('ID do kit é obrigatório');
    }
    
    // Usar contexto do organizador
    $ctx = requireOrganizadorContext($pdo);
    $usuario_id = $ctx['usuario_id'];
    $organizador_id = $ctx['organizador_id'];
    
    // Verificar se o kit existe e pertence ao organizador
    $sql = "
        SELECT k.id, k.evento_id
        FROM kits_eventos k
        INNER JOIN eventos e ON k.evento_id = e.id
        WHERE k.id = ? AND (e.organizador_id = ? OR e.organizador_id = ?)
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$kit_id, $organizador_id, $usuario_id]);
    $kit = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$kit) {
        throw new Exception('Kit não encontrado ou não autorizado');
    }
    
    // Buscar produtos do kit
    $stmt = $pdo->prepare("
        SELECT 
            kp.id,
            kp.produto_id,
            kp.tamanho_id,
            kp.quantidade,
            kp.ordem,
            p.nome as produto_nome,
            p.tipo as produto_tipo,
            p.descricao as produto_descricao,
            tce.tamanho
        FROM kit_produtos kp
        INNER JOIN produtos p ON kp.produto_id = p.id
        LEFT JOIN tamanhos_camisetas_evento tce ON kp.tamanho_id = tce.id
        WHERE kp.kit_id = ? AND kp.ativo = 1
        ORDER BY kp.ordem ASC
    ");
    $stmt->execute([$kit_id]);
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $produtos
    ]);
    
} catch (Exception $e) {
    error_log('Erro ao buscar produtos do kit: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>

