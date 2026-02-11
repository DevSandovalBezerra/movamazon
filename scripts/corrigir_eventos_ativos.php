<?php
/**
 * Script para corrigir eventos com status "ativo" mas sem configurações obrigatórias
 * 
 * Este script identifica eventos que estão ativos mas não podem receber inscrições
 * e altera o status para "rascunho" automaticamente.
 * 
 * Uso: php scripts/corrigir_eventos_ativos.php
 */

require_once __DIR__ . '/../api/db.php';
require_once __DIR__ . '/../api/evento/validate_event_ready.php';

echo "========================================\n";
echo "Correção de Eventos Ativos\n";
echo "========================================\n\n";

try {
    // Buscar todos os eventos com status "ativo"
    $stmt = $pdo->prepare("SELECT id, nome, status FROM eventos WHERE status = 'ativo' AND deleted_at IS NULL");
    $stmt->execute();
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Eventos encontrados com status 'ativo': " . count($eventos) . "\n\n";
    
    $corrigidos = 0;
    $mantidos = 0;
    
    foreach ($eventos as $evento) {
        $evento_id = $evento['id'];
        $evento_nome = $evento['nome'];
        
        // Validar se evento pode receber inscrições
        $validacao = eventoPodeReceberInscricoes($pdo, $evento_id);
        
        if (!$validacao['pode_receber']) {
            // Evento não pode receber inscrições - alterar para rascunho
            $stmt_update = $pdo->prepare("UPDATE eventos SET status = 'rascunho' WHERE id = ?");
            $stmt_update->execute([$evento_id]);
            
            echo "❌ Evento ID {$evento_id} - '{$evento_nome}'\n";
            echo "   Status alterado: ativo → rascunho\n";
            echo "   Pendências: " . implode(', ', $validacao['pendencias']) . "\n";
            echo "   Detalhes: Modalidades={$validacao['detalhes']['modalidades']}, Lotes={$validacao['detalhes']['lotes']}, Programação={$validacao['detalhes']['programacao']}\n\n";
            
            $corrigidos++;
        } else {
            // Evento está OK
            echo "✅ Evento ID {$evento_id} - '{$evento_nome}' - OK\n";
            $mantidos++;
        }
    }
    
    echo "\n========================================\n";
    echo "Resumo:\n";
    echo "  Eventos corrigidos: {$corrigidos}\n";
    echo "  Eventos mantidos: {$mantidos}\n";
    echo "  Total processado: " . count($eventos) . "\n";
    echo "========================================\n";
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

