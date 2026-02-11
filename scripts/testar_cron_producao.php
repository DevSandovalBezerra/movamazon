<?php
/**
 * Script para Testar se o CRON Está Funcionando em Produção
 * 
 * Cria uma inscrição de teste que será cancelada automaticamente
 * e monitora se o CRON a cancela dentro do tempo esperado
 * 
 * Uso: php scripts/testar_cron_producao.php
 */

require_once __DIR__ . '/../api/db.php';

echo "========================================\n";
echo "TESTE DO CRON EM PRODUÇÃO\n";
echo "========================================\n\n";

$teste_ativo = false;
$inscricao_teste_id = null;

try {
    // 1. Verificar se já existe teste ativo
    echo "1. VERIFICANDO TESTES ANTERIORES:\n";
    echo "----------------------------------\n";
    
    $stmt = $pdo->query("
        SELECT id, data_inscricao, status, status_pagamento
        FROM inscricoes
        WHERE numero_inscricao LIKE 'TESTE_CRON_%'
        ORDER BY id DESC
        LIMIT 5
    ");
    $testes_anteriores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($testes_anteriores) > 0) {
        echo "   📋 Testes anteriores encontrados:\n";
        foreach ($testes_anteriores as $teste) {
            $status_icon = ($teste['status'] === 'cancelada') ? '✅' : '⏳';
            echo "      $status_icon ID: {$teste['id']} - Status: {$teste['status']} - Criada: {$teste['data_inscricao']}\n";
        }
        
        // Verificar se há teste pendente
        $stmt_pendente = $pdo->query("
            SELECT id, data_inscricao, TIMESTAMPDIFF(MINUTE, data_inscricao, NOW()) as minutos_passados
            FROM inscricoes
            WHERE numero_inscricao LIKE 'TESTE_CRON_%'
              AND status = 'pendente'
              AND status_pagamento = 'pendente'
            ORDER BY id DESC
            LIMIT 1
        ");
        $teste_pendente = $stmt_pendente->fetch(PDO::FETCH_ASSOC);
        
        if ($teste_pendente) {
            $teste_ativo = true;
            $inscricao_teste_id = $teste_pendente['id'];
            $minutos_passados = $teste_pendente['minutos_passados'];
            
            echo "\n   ⏳ TESTE ATIVO ENCONTRADO:\n";
            echo "      ID: $inscricao_teste_id\n";
            echo "      Criada há: $minutos_passados minutos\n";
            echo "      Status: Pendente (aguardando cancelamento automático)\n";
        }
    } else {
        echo "   ℹ️  Nenhum teste anterior encontrado\n";
    }
    
    echo "\n";
    
    // 2. Criar novo teste se não houver ativo
    if (!$teste_ativo) {
        echo "2. CRIANDO NOVO TESTE:\n";
        echo "----------------------\n";
        
        // Buscar um evento ativo
        $stmt_evento = $pdo->query("
            SELECT id, nome 
            FROM eventos 
            WHERE status = 'ativo' 
            ORDER BY id DESC 
            LIMIT 1
        ");
        $evento = $stmt_evento->fetch(PDO::FETCH_ASSOC);
        
        if (!$evento) {
            throw new Exception("Nenhum evento ativo encontrado para criar teste");
        }
        
        // Buscar uma modalidade do evento
        $stmt_modalidade = $pdo->query("
            SELECT id 
            FROM modalidades 
            WHERE evento_id = {$evento['id']} 
            LIMIT 1
        ");
        $modalidade = $stmt_modalidade->fetch(PDO::FETCH_ASSOC);
        
        if (!$modalidade) {
            throw new Exception("Nenhuma modalidade encontrada para o evento");
        }
        
        // Criar inscrição de teste que será cancelada automaticamente
        // Criar com data de 73 horas atrás para ser cancelada pela regra de 72h
        $data_73h_atras = date('Y-m-d H:i:s', strtotime('-73 hours'));
        $numero_teste = 'TESTE_CRON_' . date('YmdHis');
        
        $stmt_insert = $pdo->prepare("
            INSERT INTO inscricoes (
                usuario_id, 
                evento_id, 
                modalidade_evento_id,
                numero_inscricao,
                data_inscricao,
                status,
                status_pagamento,
                valor_total,
                forma_pagamento
            ) VALUES (?, ?, ?, ?, ?, 'pendente', 'pendente', 1.00, 'pix')
        ");
        
        // Usar usuário ID 1 ou buscar primeiro usuário
        $stmt_usuario = $pdo->query("SELECT id FROM usuarios LIMIT 1");
        $usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario) {
            throw new Exception("Nenhum usuário encontrado");
        }
        
        $stmt_insert->execute([
            $usuario['id'],
            $evento['id'],
            $modalidade['id'],
            $numero_teste,
            $data_73h_atras, // 73 horas atrás = será cancelada automaticamente
            'pendente',
            'pendente',
            1.00,
            'pix'
        ]);
        
        $inscricao_teste_id = $pdo->lastInsertId();
        $teste_ativo = true;
        
        echo "   ✅ Inscrição de teste criada!\n";
        echo "      ID: $inscricao_teste_id\n";
        echo "      Número: $numero_teste\n";
        echo "      Data criada: $data_73h_atras (73 horas atrás)\n";
        echo "      Status: Pendente\n";
        echo "      ⏳ Será cancelada automaticamente pela regra de 72h\n\n";
    }
    
    // 3. Verificar status atual
    echo "3. STATUS DO TESTE:\n";
    echo "-------------------\n";
    
    $stmt_status = $pdo->prepare("
        SELECT 
            id,
            numero_inscricao,
            data_inscricao,
            status,
            status_pagamento,
            TIMESTAMPDIFF(HOUR, data_inscricao, NOW()) as horas_pendente,
            TIMESTAMPDIFF(MINUTE, data_inscricao, NOW()) as minutos_pendente
        FROM inscricoes
        WHERE id = ?
    ");
    $stmt_status->execute([$inscricao_teste_id]);
    $status = $stmt_status->fetch(PDO::FETCH_ASSOC);
    
    echo "   📋 ID: {$status['id']}\n";
    echo "   📋 Número: {$status['numero_inscricao']}\n";
    echo "   📋 Status: {$status['status']}\n";
    echo "   📋 Status Pagamento: {$status['status_pagamento']}\n";
    echo "   📋 Horas pendente: {$status['horas_pendente']}h ({$status['minutos_pendente']} minutos)\n";
    
    if ($status['status'] === 'cancelada' && $status['status_pagamento'] === 'cancelado') {
        echo "\n   ✅ TESTE CONCLUÍDO: Inscrição foi cancelada automaticamente!\n";
        echo "   ✅ Isso prova que o sistema de cancelamento está funcionando.\n";
    } elseif ($status['horas_pendente'] >= 72) {
        echo "\n   ⏳ Inscrição já tem mais de 72 horas pendente\n";
        echo "   ⏳ Deve ser cancelada na próxima execução do CRON\n";
        echo "   ⏳ Ou pode ser cancelada pelos fallbacks ao gerar pagamento\n";
    } else {
        $horas_restantes = 72 - $status['horas_pendente'];
        echo "\n   ⏳ Aguardando {$horas_restantes} horas para atingir 72h\n";
        echo "   ⏳ Após isso, será cancelada automaticamente\n";
    }
    
    echo "\n";
    
    // 4. Verificar execuções do CRON
    echo "4. VERIFICANDO EXECUÇÕES DO CRON:\n";
    echo "----------------------------------\n";
    
    $log_execucao_file = __DIR__ . '/../logs/cron_execucoes.log';
    if (file_exists($log_execucao_file)) {
        $linhas = file($log_execucao_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $execucoes_automaticas = 0;
        $ultima_automatica = null;
        
        foreach ($linhas as $linha) {
            $exec = json_decode(trim($linha), true);
            if ($exec && isset($exec['tipo']) && $exec['tipo'] === 'CRON_AUTOMATICO') {
                $execucoes_automaticas++;
                $ultima_automatica = $exec;
            }
        }
        
        echo "   📋 Total de execuções automáticas: $execucoes_automaticas\n";
        
        if ($ultima_automatica) {
            $ultima_data = new DateTime($ultima_automatica['timestamp']);
            $agora = new DateTime();
            $diff = $agora->diff($ultima_data);
            
            echo "   📋 Última execução automática: {$ultima_automatica['timestamp']}\n";
            echo "   📋 Há: " . $diff->format('%d dias, %h horas, %i minutos') . "\n";
            
            if ($diff->days === 0 && $diff->h < 2) {
                echo "   ✅ CRON executou recentemente!\n";
            } else {
                echo "   ⚠️  Última execução foi há mais tempo\n";
            }
        } else {
            echo "   ❌ Nenhuma execução automática detectada ainda\n";
        }
    } else {
        echo "   ⚠️  Log de execuções não encontrado\n";
    }
    
    echo "\n";
    
    // 5. Instruções para monitoramento
    echo "5. COMO MONITORAR O TESTE:\n";
    echo "---------------------------\n";
    echo "   Opção 1 - Aguardar próxima execução do CRON:\n";
    echo "   → O CRON executa às 02:00 (verifique crontab)\n";
    echo "   → Após a execução, rode: php scripts/testar_cron_producao.php\n";
    echo "   → Se a inscrição foi cancelada, o CRON está funcionando!\n\n";
    
    echo "   Opção 2 - Forçar execução manual do CRON:\n";
    echo "   → Execute: php api/cron/cancelar_inscricoes_expiradas.php\n";
    echo "   → Depois rode: php scripts/testar_cron_producao.php\n";
    echo "   → Se cancelou, o script funciona (mas CRON pode não estar rodando)\n\n";
    
    echo "   Opção 3 - Usar fallback (gerar pagamento):\n";
    echo "   → Os fallbacks cancelam antes de gerar pagamento\n";
    echo "   → Mas isso não prova que o CRON está funcionando\n\n";
    
    echo "   Opção 4 - Verificar log de execuções:\n";
    echo "   → cat logs/cron_execucoes.log | tail -5\n";
    echo "   → Procure por execuções com 'tipo': 'CRON_AUTOMATICO'\n\n";
    
    // 6. Limpar teste antigo se solicitado
    echo "6. LIMPEZA:\n";
    echo "-----------\n";
    echo "   Para limpar testes antigos cancelados:\n";
    echo "   DELETE FROM inscricoes WHERE numero_inscricao LIKE 'TESTE_CRON_%' AND status = 'cancelada';\n\n";
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "========================================\n";
echo "Teste concluído em: " . date('Y-m-d H:i:s') . "\n";
echo "========================================\n";
