<?php
/**
 * Script para Verificar se o CRON Está Executando Automaticamente
 * 
 * Verifica quando foi a última execução REAL do CRON (não manual)
 * 
 * Uso: php scripts/verificar_execucao_cron.php
 */

require_once __DIR__ . '/../api/db.php';

echo "========================================\n";
echo "VERIFICAÇÃO DE EXECUÇÃO DO CRON\n";
echo "========================================\n\n";

$log_execucao_file = __DIR__ . '/../logs/cron_execucoes.log';
$problemas = [];
$avisos = [];
$sucessos = [];

// 1. Verificar se arquivo de log existe
echo "1. VERIFICAÇÃO DO LOG DE EXECUÇÕES:\n";
echo "------------------------------------\n";

if (!file_exists($log_execucao_file)) {
    echo "   ⚠️  Arquivo de log não encontrado: $log_execucao_file\n";
    echo "   ℹ️  Isso pode significar que o CRON nunca executou automaticamente\n";
    $avisos[] = "Log de execuções não encontrado - CRON pode nunca ter executado";
} else {
    echo "   ✅ Arquivo de log encontrado\n";
    $sucessos[] = "Log de execuções existe";
    
    // Ler últimas execuções
    $linhas = file($log_execucao_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $execucoes = [];
    
    foreach ($linhas as $linha) {
        $execucao = json_decode(trim($linha), true);
        if ($execucao) {
            $execucoes[] = $execucao;
        }
    }
    
    $total_execucoes = count($execucoes);
    echo "   📋 Total de execuções registradas: $total_execucoes\n\n";
    
    if ($total_execucoes > 0) {
        // Pegar últimas 5 execuções
        $ultimas_execucoes = array_slice($execucoes, -5);
        
        echo "2. ÚLTIMAS EXECUÇÕES REGISTRADAS:\n";
        echo "----------------------------------\n";
        
        foreach (array_reverse($ultimas_execucoes) as $idx => $exec) {
            $num = count($ultimas_execucoes) - $idx;
            echo "   $num. {$exec['timestamp']}\n";
            echo "      Tipo: {$exec['tipo']}\n";
            echo "      Usuário: {$exec['usuario']}\n";
            echo "      Servidor: {$exec['server_name']}\n";
            echo "      Método: {$exec['request_method']}\n";
            
            // Detectar se foi execução manual ou automática
            $eh_automatico = (
                isset($exec['tipo']) && $exec['tipo'] === 'CRON_AUTOMATICO' ||
                (isset($exec['sapi']) && $exec['sapi'] === 'cli') ||
                $exec['request_method'] === 'CLI' ||
                ($exec['user_agent'] === 'CRON' && $exec['remote_addr'] === 'localhost') ||
                (!isset($exec['request_method']) || empty($exec['request_method']))
            );
            
            if ($eh_automatico) {
                echo "      ✅ Execução AUTOMÁTICA (CRON)\n";
            } else {
                echo "      ⚠️  Execução MANUAL (via HTTP/curl)\n";
                $avisos[] = "Execução manual detectada em {$exec['timestamp']}";
            }
            echo "\n";
        }
        
        // Analisar última execução
        $ultima_execucao = end($execucoes);
        $ultima_data = new DateTime($ultima_execucao['timestamp']);
        $agora = new DateTime();
        $diff = $agora->diff($ultima_data);
        
        echo "3. ANÁLISE DA ÚLTIMA EXECUÇÃO:\n";
        echo "------------------------------\n";
        echo "   📋 Data/Hora: {$ultima_execucao['timestamp']}\n";
        echo "   📋 Há: " . $diff->format('%d dias, %h horas, %i minutos') . "\n";
        
        $eh_ultima_automatica = (
            (isset($ultima_execucao['tipo']) && $ultima_execucao['tipo'] === 'CRON_AUTOMATICO') ||
            (isset($ultima_execucao['sapi']) && $ultima_execucao['sapi'] === 'cli') ||
            $ultima_execucao['request_method'] === 'CLI' ||
            ($ultima_execucao['user_agent'] === 'CRON' && $ultima_execucao['remote_addr'] === 'localhost')
        );
        
        if ($eh_ultima_automatica) {
            echo "   ✅ Última execução foi AUTOMÁTICA (CRON)\n";
            $sucessos[] = "CRON está executando automaticamente";
            
            // Verificar se está dentro do esperado
            $horas_desde_ultima = ($diff->days * 24) + $diff->h;
            
            if ($horas_desde_ultima > 25) {
                echo "   ⚠️  Última execução foi há mais de 24 horas\n";
                $avisos[] = "CRON pode não estar executando regularmente";
            } elseif ($horas_desde_ultima > 1) {
                echo "   ℹ️  Última execução foi há {$horas_desde_ultima} horas (normal se CRON executa diariamente)\n";
            } else {
                echo "   ✅ Execução recente detectada\n";
            }
        } else {
            echo "   ❌ Última execução foi MANUAL (não via CRON)\n";
            $problemas[] = "CRON não está executando automaticamente - última execução foi manual";
        }
    } else {
        echo "   ⚠️  Nenhuma execução registrada no log\n";
        $avisos[] = "Nenhuma execução registrada";
    }
}

echo "\n";

// 2. Verificar logs do sistema (se acessível)
echo "4. VERIFICAÇÃO DE LOGS DO SISTEMA:\n";
echo "-----------------------------------\n";

$log_locations = [
    '/var/log/movamazon/cancelar_inscricoes.log',
    '/var/log/cron',
    '/var/log/syslog'
];

$log_encontrado = false;
foreach ($log_locations as $log_file) {
    if (file_exists($log_file) && is_readable($log_file)) {
        echo "   ✅ Log encontrado: $log_file\n";
        $log_encontrado = true;
        
        // Buscar últimas linhas relacionadas
        $comando = "tail -n 20 " . escapeshellarg($log_file) . " 2>/dev/null";
        $log_output = shell_exec($comando);
        
        if ($log_output) {
            echo "   📋 Últimas linhas:\n";
            $linhas = explode("\n", trim($log_output));
            foreach (array_slice($linhas, -5) as $linha) {
                if (!empty($linha)) {
                    echo "      $linha\n";
                }
            }
        }
        break;
    }
}

if (!$log_encontrado) {
    echo "   ℹ️  Logs do sistema não acessíveis (normal em hospedagem compartilhada)\n";
}

echo "\n";

// 3. Verificar configuração do CRON
echo "5. VERIFICAÇÃO DA CONFIGURAÇÃO:\n";
echo "-------------------------------\n";

$crontab_output = shell_exec("crontab -l 2>&1");
if ($crontab_output && strpos($crontab_output, 'cancelar_inscricoes_expiradas') !== false) {
    echo "   ✅ CRON está configurado\n";
    
    // Extrair linha
    $linhas = explode("\n", $crontab_output);
    foreach ($linhas as $linha) {
        if (strpos($linha, 'cancelar_inscricoes_expiradas') !== false && !preg_match('/^#/', trim($linha))) {
            echo "   📋 Linha: $linha\n";
            
            // Verificar frequência
            if (preg_match('/^(\S+\s+\S+\s+\S+\s+\S+\s+\S+)/', trim($linha), $matches)) {
                $schedule = $matches[1];
                if ($schedule === '0 2 * * *') {
                    echo "   📋 Frequência: Diariamente às 02:00\n";
                } elseif ($schedule === '0 * * * *') {
                    echo "   📋 Frequência: A cada hora\n";
                }
            }
        }
    }
} else {
    echo "   ❌ CRON não está configurado\n";
    $problemas[] = "CRON não está configurado";
}

echo "\n";

// 4. Resumo e recomendações
echo "========================================\n";
echo "RESUMO:\n";
echo "========================================\n\n";

if (empty($problemas) && empty($avisos)) {
    echo "✅ TUDO OK! CRON está executando automaticamente.\n\n";
} else {
    if (!empty($sucessos)) {
        echo "✅ SUCESSOS:\n";
        foreach ($sucessos as $sucesso) {
            echo "   - $sucesso\n";
        }
        echo "\n";
    }
    
    if (!empty($avisos)) {
        echo "⚠️  AVISOS:\n";
        foreach ($avisos as $aviso) {
            echo "   - $aviso\n";
        }
        echo "\n";
    }
    
    if (!empty($problemas)) {
        echo "❌ PROBLEMAS:\n";
        foreach ($problemas as $problema) {
            echo "   - $problema\n";
        }
        echo "\n";
    }
}

// 5. Teste recomendado
echo "========================================\n";
echo "TESTE RECOMENDADO:\n";
echo "========================================\n\n";
echo "Para testar se o CRON está funcionando:\n\n";
echo "1. Aguarde a próxima execução agendada (verifique o horário acima)\n";
echo "2. Execute novamente este script após a execução:\n";
echo "   php scripts/verificar_execucao_cron.php\n";
echo "3. Se aparecer execução AUTOMÁTICA, o CRON está funcionando\n";
echo "4. Se aparecer apenas execuções MANUAIS, o CRON não está executando\n\n";

echo "Para forçar uma execução de teste (via CRON):\n";
echo "1. Execute manualmente: php api/cron/cancelar_inscricoes_expiradas.php\n";
echo "2. Verifique o log: cat logs/cron_execucoes.log | tail -1\n";
echo "3. Se mostrar 'request_method': 'CLI', foi execução automática\n\n";

echo "========================================\n";
echo "Verificação concluída em: " . date('Y-m-d H:i:s') . "\n";
echo "========================================\n";
