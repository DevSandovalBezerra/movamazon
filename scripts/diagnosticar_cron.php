<?php
/**
 * Script de Diagnóstico do CRON
 * 
 * Verifica se o CRON está configurado e funcionando corretamente
 * 
 * Uso: php scripts/diagnosticar_cron.php
 */

require_once __DIR__ . '/../api/db.php';

echo "========================================\n";
echo "DIAGNÓSTICO DO CRON - CANCELAMENTO AUTOMÁTICO\n";
echo "========================================\n\n";

$problemas = [];
$avisos = [];
$sucessos = [];

// 1. Verificar se o arquivo do CRON existe
echo "1. VERIFICAÇÃO DE ARQUIVOS:\n";
echo "----------------------------\n";
$arquivo_cron = __DIR__ . '/../api/cron/cancelar_inscricoes_expiradas.php';
if (file_exists($arquivo_cron)) {
    echo "   ✅ Arquivo do CRON encontrado: $arquivo_cron\n";
    $sucessos[] = "Arquivo do CRON existe";
    
    // Verificar permissões
    $perms = fileperms($arquivo_cron);
    $perms_octal = substr(sprintf('%o', $perms), -4);
    echo "   📋 Permissões do arquivo: $perms_octal\n";
    
    if (!is_readable($arquivo_cron)) {
        $problemas[] = "Arquivo do CRON não é legível";
        echo "   ❌ Arquivo não é legível!\n";
    } else {
        echo "   ✅ Arquivo é legível\n";
    }
} else {
    $problemas[] = "Arquivo do CRON não encontrado";
    echo "   ❌ Arquivo do CRON NÃO encontrado: $arquivo_cron\n";
}

// Verificar helper
$arquivo_helper = __DIR__ . '/../api/helpers/cancelar_inscricoes_expiradas_helper.php';
if (file_exists($arquivo_helper)) {
    echo "   ✅ Helper function encontrado\n";
} else {
    $problemas[] = "Helper function não encontrado";
    echo "   ❌ Helper function NÃO encontrado: $arquivo_helper\n";
}

echo "\n";

// 2. Verificar caminho do PHP
echo "2. VERIFICAÇÃO DO PHP:\n";
echo "----------------------\n";
$php_path = exec('which php 2>/dev/null') ?: exec('where php 2>/dev/null') ?: 'php';
echo "   📋 Caminho do PHP: $php_path\n";

// Testar execução do PHP
$test_php = shell_exec("$php_path -v 2>&1");
if ($test_php && strpos($test_php, 'PHP') !== false) {
    echo "   ✅ PHP está acessível\n";
    preg_match('/PHP (\d+\.\d+\.\d+)/', $test_php, $matches);
    if (isset($matches[1])) {
        echo "   📋 Versão do PHP: {$matches[1]}\n";
    }
} else {
    $problemas[] = "PHP não está acessível no caminho: $php_path";
    echo "   ❌ PHP NÃO está acessível!\n";
    echo "   ⚠️  Tente encontrar o caminho correto: which php ou where php\n";
}

echo "\n";

// 3. Testar execução manual do script
echo "3. TESTE DE EXECUÇÃO MANUAL:\n";
echo "-----------------------------\n";
echo "   🔄 Tentando executar o script manualmente...\n";

$output = [];
$return_var = 0;
$comando = "$php_path " . escapeshellarg($arquivo_cron) . " 2>&1";
exec($comando, $output, $return_var);

if ($return_var === 0) {
    echo "   ✅ Script executou com sucesso!\n";
    $sucessos[] = "Script executa manualmente";
    
    // Mostrar últimas linhas da saída
    $ultimas_linhas = array_slice($output, -5);
    if (!empty($ultimas_linhas)) {
        echo "   📋 Últimas linhas da saída:\n";
        foreach ($ultimas_linhas as $linha) {
            echo "      $linha\n";
        }
    }
} else {
    $problemas[] = "Script não executa manualmente (código de retorno: $return_var)";
    echo "   ❌ Script NÃO executou corretamente!\n";
    echo "   📋 Saída do erro:\n";
    foreach ($output as $linha) {
        echo "      $linha\n";
    }
}

echo "\n";

// 4. Verificar execuções reais do CRON
echo "4. VERIFICAÇÃO DE EXECUÇÕES REAIS:\n";
echo "-----------------------------------\n";

$log_execucao_file = __DIR__ . '/../logs/cron_execucoes.log';
if (file_exists($log_execucao_file)) {
    echo "   ✅ Log de execuções encontrado\n";
    
    $linhas = file($log_execucao_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $execucoes_automaticas = 0;
    $execucoes_manuais = 0;
    
    foreach ($linhas as $linha) {
        $exec = json_decode(trim($linha), true);
        if ($exec) {
            if (isset($exec['tipo']) && $exec['tipo'] === 'CRON_AUTOMATICO') {
                $execucoes_automaticas++;
            } else {
                $execucoes_manuais++;
            }
        }
    }
    
    echo "   📋 Execuções automáticas (CRON): $execucoes_automaticas\n";
    echo "   📋 Execuções manuais: $execucoes_manuais\n";
    
    if ($execucoes_automaticas > 0) {
        echo "   ✅ CRON está executando automaticamente!\n";
        $sucessos[] = "CRON executando automaticamente ($execucoes_automaticas execuções)";
    } else {
        echo "   ❌ Nenhuma execução automática detectada!\n";
        $problemas[] = "CRON não está executando automaticamente - apenas execuções manuais detectadas";
    }
    
    if ($execucoes_manuais > 0 && $execucoes_automaticas == 0) {
        echo "   ⚠️  Apenas execuções manuais foram detectadas\n";
        $avisos[] = "CRON pode não estar funcionando - apenas execuções manuais";
    }
} else {
    echo "   ⚠️  Log de execuções não encontrado\n";
    echo "   💡 Execute o CRON uma vez para criar o log\n";
    $avisos[] = "Log de execuções não existe ainda";
}

echo "\n";

// 5. Verificar logs recentes
echo "5. VERIFICAÇÃO DE LOGS:\n";
echo "-----------------------\n";

// Verificar logs do PHP (error_log)
$log_locations = [
    ini_get('error_log'),
    '/var/log/php_errors.log',
    '/var/log/php-fpm/error.log',
    __DIR__ . '/../logs/php_errors.log',
    sys_get_temp_dir() . '/php_errors.log'
];

$log_encontrado = false;
foreach ($log_locations as $log_file) {
    if ($log_file && file_exists($log_file) && is_readable($log_file)) {
        echo "   ✅ Log encontrado: $log_file\n";
        $log_encontrado = true;
        
        // Buscar últimas linhas relacionadas ao cancelamento
        $comando_log = "tail -n 50 " . escapeshellarg($log_file) . " 2>/dev/null | grep -i 'CANCELAR_INSCRICOES' | tail -n 5";
        $log_output = shell_exec($comando_log);
        
        if ($log_output) {
            echo "   📋 Últimas execuções encontradas no log:\n";
            $linhas = explode("\n", trim($log_output));
            foreach ($linhas as $linha) {
                if (!empty($linha)) {
                    echo "      $linha\n";
                }
            }
        } else {
            echo "   ⚠️  Nenhuma execução encontrada nos logs recentes\n";
            $avisos[] = "Nenhuma execução do CRON encontrada nos logs";
        }
        break;
    }
}

if (!$log_encontrado) {
    echo "   ⚠️  Nenhum arquivo de log encontrado nos locais padrão\n";
    echo "   📋 Locais verificados:\n";
    foreach ($log_locations as $loc) {
        if ($loc) {
            echo "      - $loc\n";
        }
    }
    $avisos[] = "Logs não encontrados - pode ser normal se não houver execuções";
}

echo "\n";

// 6. Verificar configuração do CRON (Linux/Unix)
echo "6. VERIFICAÇÃO DO CRON (Linux/Unix):\n";
echo "------------------------------------\n";

if (PHP_OS_FAMILY === 'Linux' || PHP_OS_FAMILY === 'Unix') {
    // Tentar verificar crontab do usuário atual
    $usuario = get_current_user();
    echo "   📋 Usuário atual: $usuario\n";
    
    $crontab_output = shell_exec("crontab -l 2>&1");
    
    if ($crontab_output && strpos($crontab_output, 'cancelar_inscricoes_expiradas') !== false) {
        echo "   ✅ CRON configurado encontrado!\n";
        $sucessos[] = "CRON está configurado";
        
        // Extrair linha do CRON
        $linhas = explode("\n", $crontab_output);
        foreach ($linhas as $linha) {
            if (strpos($linha, 'cancelar_inscricoes_expiradas') !== false && !preg_match('/^#/', trim($linha))) {
                echo "   📋 Linha do CRON:\n";
                echo "      $linha\n";
                
                // Verificar frequência
                if (preg_match('/^(\S+\s+\S+\s+\S+\s+\S+\s+\S+)/', trim($linha), $matches)) {
                    $schedule = $matches[1];
                    echo "   📋 Frequência: $schedule\n";
                    
                    // Interpretar frequência
                    if ($schedule === '0 * * * *') {
                        echo "      → Executa a cada hora\n";
                    } elseif ($schedule === '0 0 * * *') {
                        echo "      → Executa diariamente às 00:00\n";
                    } elseif (preg_match('/^0 (\d+) \* \* \*$/', $schedule, $hora_match)) {
                        $hora = $hora_match[1];
                        echo "      → Executa diariamente às {$hora}:00\n";
                        $hora_atual = (int)date('H');
                        if ($hora_atual < (int)$hora) {
                            echo "      ℹ️  Próxima execução será hoje às {$hora}:00\n";
                        } else {
                            echo "      ℹ️  Próxima execução será amanhã às {$hora}:00\n";
                        }
                    } elseif (preg_match('/^\* \* \* \* \*$/', $schedule)) {
                        echo "      ⚠️  Executa a cada minuto (muito frequente!)\n";
                        $avisos[] = "CRON configurado para executar a cada minuto - pode ser excessivo";
                    } else {
                        echo "      → Frequência: $schedule\n";
                    }
                }
            }
        }
    } elseif ($crontab_output && strpos($crontab_output, 'error') === false) {
        echo "   ⚠️  CRON configurado, mas não encontrou entrada para cancelamento\n";
        echo "   📋 Crontab atual:\n";
        $linhas = explode("\n", $crontab_output);
        foreach (array_slice($linhas, 0, 10) as $linha) {
            echo "      $linha\n";
        }
        $avisos[] = "CRON não está configurado para cancelamento automático";
    } else {
        echo "   ❌ CRON não está configurado ou não foi possível verificar\n";
        $problemas[] = "CRON não está configurado";
        echo "   💡 Para configurar, execute:\n";
        echo "      crontab -e\n";
        echo "   E adicione a linha:\n";
        $caminho_completo = realpath($arquivo_cron);
        echo "      0 * * * * $php_path $caminho_completo >> /caminho/logs/cancelar_inscricoes.log 2>&1\n";
    }
} else {
    echo "   ℹ️  Sistema operacional: " . PHP_OS . "\n";
    if (PHP_OS_FAMILY === 'Windows') {
        echo "   💡 No Windows, use o Agendador de Tarefas (Task Scheduler)\n";
        echo "      - Abra: Agendador de Tarefas\n";
        echo "      - Crie nova tarefa básica\n";
        echo "      - Programa: $php_path\n";
        $caminho_completo = realpath($arquivo_cron);
        echo "      - Argumentos: $caminho_completo\n";
        $avisos[] = "Windows detectado - use Task Scheduler ao invés de CRON";
    }
}

echo "\n";

// 7. Verificar última execução (via banco de dados)
echo "7. VERIFICAÇÃO DE ÚLTIMA EXECUÇÃO:\n";
echo "----------------------------------\n";

try {
    // Verificar inscrições canceladas recentemente
    // Como não há coluna updated_at, verificamos por data_inscricao ou data_pagamento
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            MAX(GREATEST(
                COALESCE(data_inscricao, '1970-01-01'),
                COALESCE(data_pagamento, '1970-01-01')
            )) as ultima_atualizacao
        FROM inscricoes
        WHERE status = 'cancelada'
          AND status_pagamento = 'cancelado'
          AND (
            data_inscricao >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            OR data_pagamento >= DATE_SUB(NOW(), INTERVAL 7 DAY)
          )
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['total'] > 0) {
        echo "   ✅ Encontradas {$result['total']} inscrições canceladas nos últimos 7 dias\n";
        if ($result['ultima_atualizacao'] && $result['ultima_atualizacao'] !== '1970-01-01') {
            $ultima = new DateTime($result['ultima_atualizacao']);
            $agora = new DateTime();
            $diff = $agora->diff($ultima);
            
            echo "   📋 Última cancelamento detectado: {$result['ultima_atualizacao']}\n";
            echo "   📋 Há " . $diff->format('%d dias, %h horas, %i minutos') . "\n";
            
            if ($diff->days > 1) {
                $avisos[] = "Última execução foi há mais de 1 dia";
                echo "   ⚠️  Última execução foi há mais de 1 dia\n";
            } else {
                echo "   ✅ Cancelamentos recentes detectados\n";
                $sucessos[] = "Sistema está cancelando inscrições";
            }
        }
    } else {
        echo "   ⚠️  Nenhuma inscrição cancelada nos últimos 7 dias\n";
        $avisos[] = "Nenhuma execução detectada nos últimos 7 dias";
        
        // Verificar se há inscrições canceladas em geral
        $stmt_total = $pdo->query("
            SELECT COUNT(*) as total
            FROM inscricoes
            WHERE status = 'cancelada'
              AND status_pagamento = 'cancelado'
        ");
        $total_canceladas = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'];
        
        if ($total_canceladas > 0) {
            echo "   ℹ️  Total de inscrições canceladas no sistema: $total_canceladas\n";
            echo "   ℹ️  (Mas nenhuma nos últimos 7 dias)\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Erro ao verificar banco de dados: " . $e->getMessage() . "\n";
    $problemas[] = "Erro ao verificar banco de dados: " . $e->getMessage();
    
    // Tentar verificação alternativa mais simples
    try {
        echo "   🔄 Tentando verificação alternativa...\n";
        $stmt_alt = $pdo->query("
            SELECT COUNT(*) as total
            FROM inscricoes
            WHERE status = 'cancelada'
              AND status_pagamento = 'cancelado'
        ");
        $total_alt = $stmt_alt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "   📋 Total de inscrições canceladas: $total_alt\n";
    } catch (Exception $e2) {
        echo "   ❌ Verificação alternativa também falhou: " . $e2->getMessage() . "\n";
    }
}

echo "\n";

// 7. Resumo e recomendações
echo "========================================\n";
echo "RESUMO DO DIAGNÓSTICO:\n";
echo "========================================\n\n";

if (empty($problemas) && empty($avisos)) {
    echo "✅ TUDO OK! Sistema parece estar funcionando corretamente.\n\n";
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
        echo "❌ PROBLEMAS ENCONTRADOS:\n";
        foreach ($problemas as $problema) {
            echo "   - $problema\n";
        }
        echo "\n";
    }
}

// 8. Próximos passos
echo "========================================\n";
echo "PRÓXIMOS PASSOS:\n";
echo "========================================\n\n";

if (in_array("CRON não está configurado", $problemas)) {
    echo "1. CONFIGURAR O CRON:\n";
    echo "   Execute: crontab -e\n";
    echo "   Adicione a linha:\n";
    $caminho_completo = realpath($arquivo_cron);
    echo "   0 * * * * $php_path $caminho_completo >> /var/log/cancelar_inscricoes.log 2>&1\n";
    echo "   (Isso executa a cada hora)\n\n";
}

if (in_array("Script não executa manualmente", $problemas)) {
    echo "2. CORRIGIR EXECUÇÃO DO SCRIPT:\n";
    echo "   - Verifique se o caminho do PHP está correto\n";
    echo "   - Verifique se todas as dependências estão instaladas\n";
    echo "   - Execute manualmente para ver erros:\n";
    echo "     $php_path $arquivo_cron\n\n";
}

echo "3. VERIFICAR EXECUÇÕES REAIS:\n";
echo "   Execute: php scripts/verificar_execucao_cron.php\n";
echo "   Este script mostra se o CRON está realmente executando automaticamente\n\n";

echo "4. TESTAR MANUALMENTE:\n";
echo "   Execute: php scripts/verificar_cron.php\n";
echo "   Execute: php api/cron/cancelar_inscricoes_expiradas.php\n\n";

echo "5. MONITORAR LOGS:\n";
echo "   - Verifique: cat logs/cron_execucoes.log | tail -5\n";
echo "   - Execute o script de verificação periodicamente\n\n";

echo "========================================\n";
echo "Diagnóstico concluído em: " . date('Y-m-d H:i:s') . "\n";
echo "========================================\n";
