<?php
/**
 * Helper de Logging para Inscrições e Pagamentos
 * 
 * Sistema centralizado de logs para rastrear todos os eventos relacionados
 * a inscrições e pagamentos, com armazenamento em arquivo e banco de dados.
 */

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__, 2));
}

/**
 * Obtém informações do cliente (IP e User-Agent)
 * 
 * @return array Array com 'ip' e 'user_agent'
 */
function getClientInfo() {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    // Se houver múltiplos IPs no X-Forwarded-For, pegar o primeiro
    if (strpos($ip, ',') !== false) {
        $ip = trim(explode(',', $ip)[0]);
    }
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    return [
        'ip' => $ip,
        'user_agent' => $user_agent
    ];
}

/**
 * Sanitiza dados sensíveis para logs
 * 
 * @param array $data Dados a serem sanitizados
 * @return array Dados sanitizados
 */
function sanitizeLogData($data) {
    $sensitive_keys = ['cpf', 'senha', 'password', 'token', 'accesstoken', 'secret', 'barcode', 'qr_code', 'qr_code_base64'];
    $sanitized = $data;
    
    foreach ($sanitized as $key => $value) {
        $key_lower = strtolower($key);
        
        // Verificar se a chave contém palavras sensíveis
        foreach ($sensitive_keys as $sensitive) {
            if (strpos($key_lower, $sensitive) !== false) {
                if (is_string($value) && strlen($value) > 0) {
                    // Mostrar apenas primeiros 3 e últimos 3 caracteres
                    if (strlen($value) > 6) {
                        $sanitized[$key] = substr($value, 0, 3) . '***' . substr($value, -3);
                    } else {
                        $sanitized[$key] = '***';
                    }
                }
                break;
            }
        }
        
        // Recursivamente sanitizar arrays aninhados
        if (is_array($value)) {
            $sanitized[$key] = sanitizeLogData($value);
        }
    }
    
    return $sanitized;
}

/**
 * Formata mensagem de log para arquivo
 * 
 * @param string $nivel Nível do log (ERROR, WARNING, INFO, SUCCESS)
 * @param string $acao Ação realizada
 * @param array $dados Dados do contexto
 * @return string Mensagem formatada
 */
function formatLogMessage($nivel, $acao, $dados) {
    $timestamp = date('Y-m-d H:i:s');
    $client_info = getClientInfo();
    
    $parts = [
        "[$timestamp]",
        $nivel,
        "|",
        $acao,
        "|",
        "IP: {$client_info['ip']}"
    ];
    
    // Adicionar IDs principais se existirem
    if (isset($dados['inscricao_id'])) {
        $parts[] = "| InscricaoID: {$dados['inscricao_id']}";
    }
    if (isset($dados['payment_id'])) {
        $parts[] = "| PaymentID: {$dados['payment_id']}";
    }
    if (isset($dados['usuario_id'])) {
        $parts[] = "| UsuarioID: {$dados['usuario_id']}";
    }
    
    // Adicionar detalhes JSON (sanitizado)
    $dados_sanitizados = sanitizeLogData($dados);
    $detalhes_json = json_encode($dados_sanitizados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (strlen($detalhes_json) > 500) {
        $detalhes_json = substr($detalhes_json, 0, 500) . '... (truncated)';
    }
    $parts[] = "| Detalhes: $detalhes_json";
    
    return implode(' ', $parts);
}

/**
 * Função principal de logging para inscrições e pagamentos
 * 
 * @param string $nivel Nível do log: 'ERROR', 'WARNING', 'INFO', 'SUCCESS'
 * @param string $acao Descrição da ação (ex: 'CRIACAO_INSCRICAO', 'GERACAO_BOLETO')
 * @param array $dados Array com dados do contexto
 * @return void
 */
function logInscricaoPagamento($nivel, $acao, $dados = []) {
    // Validar nível
    $niveis_validos = ['ERROR', 'WARNING', 'INFO', 'SUCCESS'];
    if (!in_array($nivel, $niveis_validos)) {
        $nivel = 'INFO';
    }
    
    try {
        $client_info = getClientInfo();
        $dados_completos = array_merge($dados, [
            'ip' => $client_info['ip'],
            'user_agent' => $client_info['user_agent']
        ]);
        
        // Sanitizar dados antes de processar
        $dados_sanitizados = sanitizeLogData($dados_completos);
        
        // 1. Escrever em arquivo
        $log_file = BASE_PATH . '/logs/inscricoes_pagamentos.log';
        $log_message = formatLogMessage($nivel, $acao, $dados_sanitizados);
        
        // Criar diretório se não existir
        $log_dir = dirname($log_file);
        if (!is_dir($log_dir)) {
            @mkdir($log_dir, 0755, true);
        }
        
        // Escrever no arquivo
        @file_put_contents($log_file, $log_message . PHP_EOL, FILE_APPEND | LOCK_EX);
        
        // 2. Inserir no banco de dados
        try {
            // Verificar se db.php já foi incluído
            // Tentar usar $pdo global, se não existir, incluir db.php
            global $pdo;
            
            if (!isset($pdo) || !$pdo) {
                require_once __DIR__ . '/../db.php';
                global $pdo;
            }
            
            if (isset($pdo) && $pdo) {
                // Preparar dados para inserção
                $inscricao_id = $dados['inscricao_id'] ?? null;
                $payment_id = $dados['payment_id'] ?? null;
                $usuario_id = $dados['usuario_id'] ?? null;
                $evento_id = $dados['evento_id'] ?? null;
                $modalidade_id = $dados['modalidade_id'] ?? null;
                $valor_total = isset($dados['valor_total']) ? (float)$dados['valor_total'] : null;
                $forma_pagamento = $dados['forma_pagamento'] ?? null;
                $status_pagamento = $dados['status_pagamento'] ?? null;
                
                // Mensagem principal (erro ou descrição)
                $mensagem = $dados['mensagem'] ?? $dados['erro'] ?? $dados['error'] ?? null;
                
                // Dados de contexto (JSON)
                $dados_contexto = [];
                foreach ($dados_sanitizados as $key => $value) {
                    // Excluir campos que já têm coluna própria
                    if (!in_array($key, ['inscricao_id', 'payment_id', 'usuario_id', 'evento_id', 
                                         'modalidade_id', 'valor_total', 'forma_pagamento', 
                                         'status_pagamento', 'mensagem', 'erro', 'error', 
                                         'stack_trace', 'ip', 'user_agent'])) {
                        $dados_contexto[$key] = $value;
                    }
                }
                $dados_contexto_json = !empty($dados_contexto) ? json_encode($dados_contexto, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;
                
                // Stack trace (apenas para erros)
                $stack_trace = ($nivel === 'ERROR' && isset($dados['stack_trace'])) ? $dados['stack_trace'] : null;
                
                // Inserir no banco
                $stmt = $pdo->prepare("
                    INSERT INTO logs_inscricoes_pagamentos (
                        nivel, acao, inscricao_id, payment_id, usuario_id, evento_id, 
                        modalidade_id, valor_total, forma_pagamento, status_pagamento,
                        mensagem, dados_contexto, stack_trace, ip, user_agent, created_at
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
                    )
                ");
                
                $stmt->execute([
                    $nivel,
                    $acao,
                    $inscricao_id,
                    $payment_id,
                    $usuario_id,
                    $evento_id,
                    $modalidade_id,
                    $valor_total,
                    $forma_pagamento,
                    $status_pagamento,
                    $mensagem,
                    $dados_contexto_json,
                    $stack_trace,
                    $client_info['ip'],
                    $client_info['user_agent']
                ]);
            }
        } catch (Exception $e) {
            // Se falhar ao inserir no banco, apenas logar no arquivo de erros
            // Não quebrar o fluxo principal
            error_log("Erro ao inserir log no banco: " . $e->getMessage());
        }
        
    } catch (Exception $e) {
        // Se falhar completamente, usar error_log padrão
        // Não quebrar o fluxo principal
        error_log("Erro ao registrar log de inscrição/pagamento: " . $e->getMessage());
    }
}

