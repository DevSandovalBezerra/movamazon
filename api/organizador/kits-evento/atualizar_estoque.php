<?php
header('Content-Type: application/json');
require_once '../../db.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    http_response_code(401);
    echo json_encode(['error' => 'Acesso negado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Dados inválidos');
    }
    
    $kit_id = isset($input['kit_id']) ? (int)$input['kit_id'] : 0;
    $tamanho_id = isset($input['tamanho_id']) ? (int)$input['tamanho_id'] : null;
    $quantidade = isset($input['quantidade']) ? (int)$input['quantidade'] : 1;
    $acao = isset($input['acao']) ? $input['acao'] : 'reservar'; // 'reservar' ou 'liberar'
    
    if ($kit_id <= 0) {
        throw new Exception('ID do kit é obrigatório');
    }
    
    if ($quantidade <= 0) {
        throw new Exception('Quantidade deve ser maior que zero');
    }
    
    $organizador_id = $_SESSION['user_id'];
    
    // Verificar se o kit existe e pertence ao organizador
    $stmt = $pdo->prepare("
        SELECT k.id, k.nome, k.evento_id, e.nome as evento_nome
        FROM kits_eventos k
        INNER JOIN eventos e ON k.evento_id = e.id
        WHERE k.id = ? AND e.organizador_id = ?
    ");
    $stmt->execute([$kit_id, $organizador_id]);
    $kit = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$kit) {
        throw new Exception('Kit não encontrado ou não pertence a você');
    }
    
    // Iniciar transação
    $pdo->beginTransaction();
    
    if ($tamanho_id) {
        // Atualizar estoque de tamanho específico
        if ($acao === 'reservar') {
            // Verificar se há estoque disponível
            $stmt = $pdo->prepare("
                SELECT quantidade_disponivel 
                FROM kit_tamanhos 
                WHERE kit_id = ? AND tamanho_id = ? AND ativo = 1
            ");
            $stmt->execute([$kit_id, $tamanho_id]);
            $estoque = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$estoque) {
                throw new Exception('Tamanho não encontrado no kit');
            }
            
            if ($estoque['quantidade_disponivel'] < $quantidade) {
                throw new Exception('Estoque insuficiente para este tamanho');
            }
            
            // Atualizar estoque
            $stmt = $pdo->prepare("
                UPDATE kit_tamanhos 
                SET quantidade_disponivel = quantidade_disponivel - ?,
                    quantidade_vendida = quantidade_vendida + ?
                WHERE kit_id = ? AND tamanho_id = ? AND ativo = 1
            ");
            $stmt->execute([$quantidade, $quantidade, $kit_id, $tamanho_id]);
            
        } elseif ($acao === 'liberar') {
            // Liberar estoque (devolver)
            $stmt = $pdo->prepare("
                UPDATE kit_tamanhos 
                SET quantidade_disponivel = quantidade_disponivel + ?,
                    quantidade_vendida = quantidade_vendida - ?
                WHERE kit_id = ? AND tamanho_id = ? AND ativo = 1
            ");
            $stmt->execute([$quantidade, $quantidade, $kit_id, $tamanho_id]);
        }
        
        // Atualizar também o estoque geral de camisetas do evento
        if ($acao === 'reservar') {
            $stmt = $pdo->prepare("
                UPDATE tamanhos_camisetas_evento 
                SET quantidade_disponivel = quantidade_disponivel - ?,
                    quantidade_vendida = quantidade_vendida + ?
                WHERE id = ? AND evento_id = ?
            ");
            $stmt->execute([$quantidade, $quantidade, $tamanho_id, $kit['evento_id']]);
            
        } elseif ($acao === 'liberar') {
            $stmt = $pdo->prepare("
                UPDATE tamanhos_camisetas_evento 
                SET quantidade_disponivel = quantidade_disponivel + ?,
                    quantidade_vendida = quantidade_vendida - ?
                WHERE id = ? AND evento_id = ?
            ");
            $stmt->execute([$quantidade, $quantidade, $tamanho_id, $kit['evento_id']]);
        }
        
    } else {
        // Atualizar estoque geral do kit (sem tamanho específico)
        if ($acao === 'reservar') {
            // Verificar se há estoque disponível em qualquer tamanho
            $stmt = $pdo->prepare("
                SELECT SUM(quantidade_disponivel) as total_disponivel
                FROM kit_tamanhos 
                WHERE kit_id = ? AND ativo = 1
            ");
            $stmt->execute([$kit_id]);
            $estoque = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($estoque['total_disponivel'] < $quantidade) {
                throw new Exception('Estoque insuficiente no kit');
            }
            
            // Distribuir a quantidade entre os tamanhos disponíveis
            $stmt = $pdo->prepare("
                SELECT id, tamanho_id, quantidade_disponivel
                FROM kit_tamanhos 
                WHERE kit_id = ? AND ativo = 1 AND quantidade_disponivel > 0
                ORDER BY quantidade_disponivel DESC
            ");
            $stmt->execute([$kit_id]);
            $tamanhos_disponiveis = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $quantidade_restante = $quantidade;
            
            foreach ($tamanhos_disponiveis as $tamanho) {
                if ($quantidade_restante <= 0) break;
                
                $quantidade_a_reservar = min($quantidade_restante, $tamanho['quantidade_disponivel']);
                
                $stmt = $pdo->prepare("
                    UPDATE kit_tamanhos 
                    SET quantidade_disponivel = quantidade_disponivel - ?,
                        quantidade_vendida = quantidade_vendida + ?
                    WHERE id = ?
                ");
                $stmt->execute([$quantidade_a_reservar, $quantidade_a_reservar, $tamanho['id']]);
                
                $quantidade_restante -= $quantidade_a_reservar;
            }
            
        } elseif ($acao === 'liberar') {
            // Liberar estoque distribuindo entre os tamanhos
            $stmt = $pdo->prepare("
                SELECT id, tamanho_id, quantidade_vendida
                FROM kit_tamanhos 
                WHERE kit_id = ? AND ativo = 1 AND quantidade_vendida > 0
                ORDER BY quantidade_vendida DESC
            ");
            $stmt->execute([$kit_id]);
            $tamanhos_vendidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $quantidade_restante = $quantidade;
            
            foreach ($tamanhos_vendidos as $tamanho) {
                if ($quantidade_restante <= 0) break;
                
                $quantidade_a_liberar = min($quantidade_restante, $tamanho['quantidade_vendida']);
                
                $stmt = $pdo->prepare("
                    UPDATE kit_tamanhos 
                    SET quantidade_disponivel = quantidade_disponivel + ?,
                        quantidade_vendida = quantidade_vendida - ?
                    WHERE id = ?
                ");
                $stmt->execute([$quantidade_a_liberar, $quantidade_a_liberar, $tamanho['id']]);
                
                $quantidade_restante -= $quantidade_a_liberar;
            }
        }
    }
    
    // Commit da transação
    $pdo->commit();
    
    // Log da atualização
    $acao_texto = $acao === 'reservar' ? 'reservado' : 'liberado';
    error_log("Estoque de kit atualizado - Kit ID: $kit_id, Ação: $acao_texto, Quantidade: $quantidade, Organizador: $organizador_id");
    
    echo json_encode([
        'success' => true,
        'message' => "Estoque do kit $acao_texto com sucesso",
        'data' => [
            'kit_id' => $kit_id,
            'acao' => $acao,
            'quantidade' => $quantidade
        ]
    ]);
    
} catch (Exception $e) {
    // Rollback em caso de erro
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log('Erro ao atualizar estoque do kit: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 
