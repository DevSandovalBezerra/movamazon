<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../db.php';

try {
    $evento_id = $_GET['evento_id'] ?? null;
    
    if (!$evento_id) {
        throw new Exception('Evento ID é obrigatório');
    }

    // 1. Verificar estrutura da tabela termos_eventos
    // A tabela pode ter estrutura antiga (evento_id) ou nova (organizador_id)
    try {
        $checkColumn = $pdo->query("SHOW COLUMNS FROM termos_eventos LIKE 'organizador_id'");
        $hasOrganizadorId = $checkColumn->rowCount() > 0;
    } catch (Exception $e) {
        $hasOrganizadorId = false;
    }
    
    if ($hasOrganizadorId) {
        // Usar nova estrutura com organizador_id
        // 1.1. Buscar organizador_id do evento
        $sqlEvento = "SELECT organizador_id FROM eventos WHERE id = ? AND deleted_at IS NULL LIMIT 1";
        $stmtEvento = $pdo->prepare($sqlEvento);
        $stmtEvento->execute([$evento_id]);
        $evento = $stmtEvento->fetch(PDO::FETCH_ASSOC);
        
        if (!$evento || !isset($evento['organizador_id'])) {
            throw new Exception('Evento não encontrado');
        }
        
        $organizador_id = (int)$evento['organizador_id'];
        
        // 1.2. Buscar termo ativo do organizador
        $sql = "
            SELECT 
                te.id,
                te.titulo,
                te.conteudo,
                te.versao,
                'organizador' as tipo
            FROM termos_eventos te
            WHERE te.organizador_id = ? 
              AND te.ativo = 1
            ORDER BY te.data_criacao DESC
            LIMIT 1
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$organizador_id]);
        $termos = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        // Usar estrutura antiga (evento_id) - compatibilidade com banco atual
        $sql = "
            SELECT 
                te.id,
                te.titulo,
                te.conteudo,
                te.versao,
                'evento' as tipo
            FROM termos_eventos te
            WHERE te.evento_id = ? 
              AND te.ativo = 1
            ORDER BY te.data_criacao DESC
            LIMIT 1
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$evento_id]);
        $termos = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Se não encontrou termo do organizador, usar regulamento do evento como fallback
    if (!$termos) {
        $sql = "
            SELECT 
                e.id,
                'Regulamento do Evento' as titulo,
                e.regulamento as conteudo,
                '1.0' as versao,
                'fallback' as tipo
            FROM eventos e
            WHERE e.id = ?
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$evento_id]);
        $termos = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Se ainda não encontrou nada, usar termos padrão
    if (!$termos || empty($termos['conteudo'])) {
        $termos = [
            'id' => 0,
            'titulo' => 'Termos e Condições',
            'conteudo' => 'Ao se inscrever neste evento, você concorda com os termos e condições estabelecidos pela organização.',
            'versao' => '1.0',
            'tipo' => 'padrao'
        ];
    }
    
    echo json_encode([
        'success' => true,
        'termos' => $termos
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
