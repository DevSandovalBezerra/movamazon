<?php
header('Content-Type: application/json');
require_once '../db.php';
require_once '../middleware/auth.php';

// Verificar autenticação usando middleware centralizado
verificarAutenticacao('organizador');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

try {
    error_log('🚀 API soft_delete.php - Iniciando exclusão de evento');
    error_log('🚀 API soft_delete.php - POST data: ' . json_encode($_POST));
    error_log('🚀 API soft_delete.php - REQUEST_METHOD: ' . $_SERVER['REQUEST_METHOD']);
    
    // Validar campos obrigatórios
    if (!isset($_POST['evento_id']) || empty($_POST['evento_id'])) {
        error_log('❌ API soft_delete.php - ID do evento não fornecido');
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID do evento é obrigatório']);
        exit();
    }
    
    $evento_id = (int)$_POST['evento_id'];
    $organizador_id = $_SESSION['user_id'];
    $motivo_exclusao = trim($_POST['motivo_exclusao'] ?? 'Exclusão solicitada pelo organizador');
    
    error_log('🔧 API soft_delete.php - Evento ID: ' . $evento_id . ', Organizador ID: ' . $organizador_id);
    
    // Verificar se o evento existe e pertence ao organizador
    $stmt = $pdo->prepare("SELECT id, nome, status FROM eventos WHERE id = ? AND organizador_id = ? AND deleted_at IS NULL");
    $stmt->execute([$evento_id, $organizador_id]);
    $evento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    error_log('🔍 API soft_delete.php - Evento encontrado: ' . ($evento ? 'SIM' : 'NÃO'));
    if ($evento) {
        error_log('🔍 API soft_delete.php - Dados do evento: ' . json_encode($evento));
    }
    
    if (!$evento) {
        error_log('❌ API soft_delete.php - Evento não encontrado ou não pertence ao organizador');
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Evento não encontrado ou não pertence a você']);
        exit();
    }
    
    // PRIMEIRO: Verificar dependências críticas
    $dependencias_criticas = [];
    
    // Verificar inscrições (CRÍTICO)
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM inscricoes WHERE evento_id = ?");
    $stmt->execute([$evento_id]);
    $inscricoes = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($inscricoes['total'] > 0) {
        $dependencias_criticas[] = [
            'tabela' => 'inscricoes',
            'total' => (int)$inscricoes['total'],
            'descricao' => 'Inscrições de participantes'
        ];
    }
    
    // Verificar repasses financeiros (CRÍTICO)
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM repasse_organizadores WHERE evento_id = ?");
    $stmt->execute([$evento_id]);
    $repasses = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($repasses['total'] > 0) {
        $dependencias_criticas[] = [
            'tabela' => 'repasse_organizadores',
            'total' => (int)$repasses['total'],
            'descricao' => 'Dados financeiros de repasse'
        ];
    }
    
    // Se tem dependências críticas, BLOQUEAR exclusão
    if (!empty($dependencias_criticas)) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'Não é possível excluir este evento',
            'motivo' => 'Evento possui dados críticos que impedem a exclusão',
            'dependencias_criticas' => $dependencias_criticas,
            'sugestao' => 'Entre em contato com o suporte para mais informações'
        ]);
        exit();
    }
    
    // SEGUNDO: Verificar dependências não críticas para avisar
    $dependencias_nao_criticas = [];
    
    $tabelas_verificar = [
        'kits_eventos' => 'Kits associados ao evento',
        'lotes_inscricao' => 'Lotes de inscrição configurados',
        'produtos_extras' => 'Produtos extras configurados',
        'cupons_remessa' => 'Cupons de desconto configurados',
        'formas_pagamento_evento' => 'Formas de pagamento configuradas',
        'programacao_evento' => 'Programação do evento configurada',
        'questionario_evento' => 'Questionário do evento configurado',
        'termos_eventos' => 'Termos e condições configurados',
        'retirada_kits_evento' => 'Locais de retirada de kits configurados',
        'periodos_inscricao' => 'Períodos de inscrição configurados'
    ];
    
    foreach ($tabelas_verificar as $tabela => $descricao) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM {$tabela} WHERE evento_id = ?");
        $stmt->execute([$evento_id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado['total'] > 0) {
            $dependencias_nao_criticas[] = [
                'tabela' => $tabela,
                'total' => (int)$resultado['total'],
                'descricao' => $descricao
            ];
        }
    }
    
    // Iniciar transação
    $pdo->beginTransaction();
    
    try {
        // Realizar soft delete
        error_log('🔄 API soft_delete.php - Executando UPDATE para soft delete');
        $stmt = $pdo->prepare("
            UPDATE eventos 
            SET deleted_at = NOW(), 
                deleted_by = ?, 
                delete_reason = ?
            WHERE id = ? AND organizador_id = ? AND deleted_at IS NULL
        ");
        
        $resultado = $stmt->execute([$organizador_id, $motivo_exclusao, $evento_id, $organizador_id]);
        $rows_affected = $stmt->rowCount();
        
        error_log('🔄 API soft_delete.php - Resultado do UPDATE: ' . ($resultado ? 'true' : 'false') . ', Rows affected: ' . $rows_affected);
        
        if ($resultado && $rows_affected > 0) {
            // Confirmar transação
            $pdo->commit();
            
            // Log da exclusão
            // logSeguranca('Excluiu evento (soft delete)', "Evento ID: {$evento_id}, Nome: {$evento['nome']}, Motivo: {$motivo_exclusao}");
            error_log('✅ API soft_delete.php - Evento excluído com sucesso: ' . $evento['nome']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Evento excluído com sucesso',
                'data' => [
                    'evento_id' => $evento_id,
                    'evento_nome' => $evento['nome'],
                    'data_exclusao' => date('Y-m-d H:i:s'),
                    'dependencias_removidas' => $dependencias_nao_criticas,
                    'total_dependencias_removidas' => count($dependencias_nao_criticas)
                ]
            ]);
        } else {
            // Reverter transação
            $pdo->rollBack();
            error_log('❌ API soft_delete.php - Falha na exclusão: resultado=' . ($resultado ? 'true' : 'false') . ', rows_affected=' . $rows_affected);
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir evento']);
        }
        
    } catch (Exception $e) {
        // Reverter transação em caso de erro
        $pdo->rollBack();
        throw $e;
    }
    
} catch (PDOException $e) {
    error_log("Erro ao excluir evento: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
} catch (Exception $e) {
    error_log("Erro inesperado ao excluir evento: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro inesperado']);
}
?>
