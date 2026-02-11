<?php
/**
 * Script para Monitorar o Teste do CRON
 * 
 * Verifica se a inscrição de teste foi cancelada automaticamente
 * e mostra quando foi cancelada e por qual método
 * 
 * Uso: php scripts/monitorar_teste_cron.php
 */

require_once __DIR__ . '/../api/db.php';

echo "========================================\n";
echo "MONITORAMENTO DO TESTE DO CRON\n";
echo "========================================\n\n";

try {
    // Buscar inscrição de teste
    $stmt = $pdo->query("
        SELECT 
            id,
            numero_inscricao,
            data_inscricao,
            status,
            status_pagamento,
            TIMESTAMPDIFF(HOUR, data_inscricao, NOW()) as horas_pendente,
            TIMESTAMPDIFF(MINUTE, data_inscricao, NOW()) as minutos_pendente
        FROM inscricoes
        WHERE numero_inscricao LIKE 'TESTE_CRON_%'
        ORDER BY id DESC
        LIMIT 1
    ");
    
    $teste = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$teste) {
        echo "❌ Nenhuma inscrição de teste encontrada.\n";
        echo "   Execute primeiro: php scripts/testar_cron_producao.php\n";
        exit(1);
    }
    
    echo "📋 INSCRIÇÃO DE TESTE:\n";
    echo "----------------------\n";
    echo "   ID: {$teste['id']}\n";
    echo "   Número: {$teste['numero_inscricao']}\n";
    echo "   Criada em: {$teste['data_inscricao']}\n";
    echo "   Status: {$teste['status']}\n";
    echo "   Status Pagamento: {$teste['status_pagamento']}\n";
    echo "   Tempo pendente: {$teste['horas_pendente']}h ({$teste['minutos_pendente']} minutos)\n\n";
    
    // Verificar se foi cancelada
    if ($teste['status'] === 'cancelada' && $teste['status_pagamento'] === 'cancelado') {
        echo "✅ TESTE CONCLUÍDO COM SUCESSO!\n";
        echo "-------------------------------\n";
        echo "   A inscrição foi cancelada automaticamente.\n\n";
        
        // Tentar descobrir quando foi cancelada (verificar logs)
        echo "📋 VERIFICANDO LOGS DE EXECUÇÃO:\n";
        echo "--------------------------------\n";
        
        $log_execucao_file = __DIR__ . '/../logs/cron_execucoes.log';
        if (file_exists($log_execucao_file)) {
            $linhas = file($log_execucao_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $execucoes_apos_teste = [];
            
            $data_teste = new DateTime($teste['data_inscricao']);
            
            foreach ($linhas as $linha) {
                $exec = json_decode(trim($linha), true);
                if ($exec) {
                    $data_exec = new DateTime($exec['timestamp']);
                    if ($data_exec >= $data_teste) {
                        $execucoes_apos_teste[] = $exec;
                    }
                }
            }
            
            if (count($execucoes_apos_teste) > 0) {
                echo "   📋 Execuções após criação do teste:\n";
                foreach ($execucoes_apos_teste as $exec) {
                    $tipo_icon = ($exec['tipo'] === 'CRON_AUTOMATICO') ? '✅' : '⚠️';
                    $tipo_texto = ($exec['tipo'] === 'CRON_AUTOMATICO') ? 'AUTOMÁTICA (CRON)' : 'MANUAL';
                    echo "      $tipo_icon {$exec['timestamp']} - $tipo_texto\n";
                }
                
                // Verificar se houve execução automática
                $houve_automatica = false;
                foreach ($execucoes_apos_teste as $exec) {
                    if (isset($exec['tipo']) && $exec['tipo'] === 'CRON_AUTOMATICO') {
                        $houve_automatica = true;
                        echo "\n   ✅ CRON EXECUTOU após criação do teste!\n";
                        echo "   ✅ Cancelamento foi feito pelo CRON automático!\n";
                        break;
                    }
                }
                
                if (!$houve_automatica) {
                    echo "\n   ⚠️  Nenhuma execução automática detectada após o teste\n";
                    echo "   ⚠️  Cancelamento pode ter sido feito por fallback ou manualmente\n";
                }
            } else {
                echo "   ⚠️  Nenhuma execução registrada após criação do teste\n";
            }
        } else {
            echo "   ⚠️  Log de execuções não encontrado\n";
        }
        
    } else {
        echo "⏳ TESTE AINDA EM ANDAMENTO\n";
        echo "----------------------------\n";
        
        if ($teste['horas_pendente'] >= 72) {
            echo "   ✅ Inscrição já tem mais de 72 horas pendente\n";
            echo "   ⏳ Deve ser cancelada na próxima execução do CRON\n";
            echo "   ⏳ Próxima execução: 02:00 (verifique crontab)\n\n";
            
            echo "   💡 Para forçar cancelamento agora:\n";
            echo "      php api/cron/cancelar_inscricoes_expiradas.php\n";
        } else {
            $horas_restantes = 72 - $teste['horas_pendente'];
            $minutos_restantes = (72 * 60) - $teste['minutos_pendente'];
            
            echo "   ⏳ Aguardando {$horas_restantes}h ({$minutos_restantes} minutos) para atingir 72h\n";
            echo "   ⏳ Após isso, será cancelada automaticamente\n\n";
            
            echo "   💡 Para testar imediatamente, crie teste com data mais antiga:\n";
            echo "      UPDATE inscricoes SET data_inscricao = DATE_SUB(NOW(), INTERVAL 73 HOUR)\n";
            echo "      WHERE id = {$teste['id']};\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n========================================\n";
echo "Monitoramento concluído em: " . date('Y-m-d H:i:s') . "\n";
echo "========================================\n";
