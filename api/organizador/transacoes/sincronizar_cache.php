<?php
/**
 * 🔄 SINCRONIZADOR DE TRANSAÇÕES DO MERCADO PAGO
 * 
 * Busca transações da API do Mercado Pago e armazena no cache local
 * 
 * MODOS DE EXECUÇÃO:
 * 1. Manual (via interface admin)
 * 2. Automático (via CRON)
 * 3. Webhook (chamado automaticamente)
 */

require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../mercadolivre/config.php';

class SincronizadorTransacoesMP {
    
    private $pdo;
    private $access_token;
    private $log_id;
    private $stats = [
        'processadas' => 0,
        'novas' => 0,
        'atualizadas' => 0,
        'erros' => 0
    ];
    
    public function __construct($pdo, $access_token) {
        $this->pdo = $pdo;
        $this->access_token = $access_token;
    }
    
    /**
     * Sincronizar transações de um período
     */
    public function sincronizar($opcoes = []) {
        $defaults = [
            'begin_date' => date('Y-m-d', strtotime('-30 days')),
            'end_date' => date('Y-m-d'),
            'limit' => 100,
            'tipo' => 'manual',
            'usuario_id' => null
        ];
        
        $opts = array_merge($defaults, $opcoes);
        
        // Iniciar log
        $this->iniciarLog($opts['tipo'], $opts['usuario_id']);
        
        try {
            $offset = 0;
            $has_more = true;
            
            while ($has_more) {
                echo "📡 Buscando offset $offset...\n";
                
                $transacoes = $this->buscarTransacoesMP(
                    $opts['begin_date'],
                    $opts['end_date'],
                    $opts['limit'],
                    $offset
                );
                
                if (empty($transacoes['results'])) {
                    $has_more = false;
                    break;
                }
                
                foreach ($transacoes['results'] as $transacao) {
                    $this->processarTransacao($transacao);
                }
                
                // Verificar se tem mais páginas
                $total = $transacoes['paging']['total'] ?? 0;
                $offset += $opts['limit'];
                $has_more = $offset < $total;
                
                echo "✅ Processados $offset de $total\n";
                
                // Delay para não sobrecarregar API
                usleep(500000); // 0.5s
            }
            
            $this->finalizarLog('concluido');
            
            return [
                'success' => true,
                'stats' => $this->stats,
                'log_id' => $this->log_id
            ];
            
        } catch (Exception $e) {
            $this->finalizarLog('erro', $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Sincronizar uma transação específica (via webhook)
     */
    public function sincronizarTransacao($payment_data) {
        $this->iniciarLog('webhook', null);
        
        try {
            $this->processarTransacao($payment_data);
            $this->finalizarLog('concluido');
            
            return ['success' => true, 'stats' => $this->stats];
        } catch (Exception $e) {
            $this->finalizarLog('erro', $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Buscar transações na API do Mercado Pago
     */
    private function buscarTransacoesMP($begin_date, $end_date, $limit, $offset) {
        $query_params = [
            'access_token' => $this->access_token,
            'begin_date' => $begin_date . 'T00:00:00.000-00:00',
            'end_date' => $end_date . 'T23:59:59.999-00:00',
            'limit' => $limit,
            'offset' => $offset,
            'sort' => 'date_created',
            'criteria' => 'desc'
        ];
        
        $query_string = http_build_query($query_params);
        $url = "https://api.mercadopago.com/v1/payments/search?{$query_string}";
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->access_token,
                'Content-Type: application/json'
            ],
        ]);
        
        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        if ($http_code !== 200) {
            throw new Exception("Erro ao consultar MP: HTTP $http_code");
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Processar e salvar transação no cache
     */
    private function processarTransacao($payment_data) {
        try {
            $this->stats['processadas']++;
            
            // Verificar se já existe
            $stmt = $this->pdo->prepare("SELECT id FROM transacoes_mp_cache WHERE payment_id = ?");
            $stmt->execute([$payment_data['id']]);
            $existe = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Extrair dados
            $dados = [
                'payment_id' => $payment_data['id'],
                'external_reference' => $payment_data['external_reference'] ?? null,
                'status' => $payment_data['status'],
                'status_detail' => $payment_data['status_detail'] ?? null,
                'transaction_amount' => $payment_data['transaction_amount'],
                'net_amount' => $this->calcularValorLiquido($payment_data),
                'fee_amount' => $this->calcularTaxas($payment_data),
                'payment_method_id' => $payment_data['payment_method_id'] ?? null,
                'payment_type_id' => $payment_data['payment_type_id'] ?? null,
                'installments' => $payment_data['installments'] ?? 1,
                'date_created' => $this->formatarData($payment_data['date_created']),
                'date_approved' => $this->formatarData($payment_data['date_approved'] ?? null),
                'date_last_updated' => $this->formatarData($payment_data['date_last_updated'] ?? null),
                'payer_email' => $payment_data['payer']['email'] ?? null,
                'payer_first_name' => $payment_data['payer']['first_name'] ?? null,
                'payer_last_name' => $payment_data['payer']['last_name'] ?? null,
                'payer_identification_type' => $payment_data['payer']['identification']['type'] ?? null,
                'payer_identification_number' => $payment_data['payer']['identification']['number'] ?? null,
                'dados_completos' => json_encode($payment_data, JSON_UNESCAPED_UNICODE),
                'origem' => 'consulta_manual'
            ];
            
            if ($existe) {
                // Atualizar
                $this->atualizarTransacao($dados);
                $this->stats['atualizadas']++;
            } else {
                // Inserir
                $this->inserirTransacao($dados);
                $this->stats['novas']++;
            }
            
        } catch (Exception $e) {
            $this->stats['erros']++;
            error_log("[SINCRONIZADOR] Erro ao processar payment_id {$payment_data['id']}: " . $e->getMessage());
        }
    }
    
    /**
     * Inserir nova transação
     */
    private function inserirTransacao($dados) {
        $sql = "INSERT INTO transacoes_mp_cache (
            payment_id, external_reference, status, status_detail,
            transaction_amount, net_amount, fee_amount,
            payment_method_id, payment_type_id, installments,
            date_created, date_approved, date_last_updated,
            payer_email, payer_first_name, payer_last_name,
            payer_identification_type, payer_identification_number,
            dados_completos, origem
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $dados['payment_id'],
            $dados['external_reference'],
            $dados['status'],
            $dados['status_detail'],
            $dados['transaction_amount'],
            $dados['net_amount'],
            $dados['fee_amount'],
            $dados['payment_method_id'],
            $dados['payment_type_id'],
            $dados['installments'],
            $dados['date_created'],
            $dados['date_approved'],
            $dados['date_last_updated'],
            $dados['payer_email'],
            $dados['payer_first_name'],
            $dados['payer_last_name'],
            $dados['payer_identification_type'],
            $dados['payer_identification_number'],
            $dados['dados_completos'],
            $dados['origem']
        ]);
    }
    
    /**
     * Atualizar transação existente
     */
    private function atualizarTransacao($dados) {
        $sql = "UPDATE transacoes_mp_cache SET
            external_reference = ?,
            status = ?,
            status_detail = ?,
            transaction_amount = ?,
            net_amount = ?,
            fee_amount = ?,
            date_approved = ?,
            date_last_updated = ?,
            dados_completos = ?,
            ultima_sincronizacao = NOW()
        WHERE payment_id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $dados['external_reference'],
            $dados['status'],
            $dados['status_detail'],
            $dados['transaction_amount'],
            $dados['net_amount'],
            $dados['fee_amount'],
            $dados['date_approved'],
            $dados['date_last_updated'],
            $dados['dados_completos'],
            $dados['payment_id']
        ]);
    }
    
    /**
     * Calcular valor líquido (após taxas)
     */
    private function calcularValorLiquido($payment_data) {
        $total = $payment_data['transaction_amount'];
        $taxas = $this->calcularTaxas($payment_data);
        return $total - $taxas;
    }
    
    /**
     * Calcular total de taxas
     */
    private function calcularTaxas($payment_data) {
        $total_taxas = 0;
        if (!empty($payment_data['fee_details']) && is_array($payment_data['fee_details'])) {
            foreach ($payment_data['fee_details'] as $fee) {
                $total_taxas += (float)($fee['amount'] ?? 0);
            }
        }
        return $total_taxas;
    }
    
    /**
     * Formatar data para MySQL
     */
    private function formatarData($data_iso) {
        if (!$data_iso) return null;
        try {
            $dt = new DateTime($data_iso);
            return $dt->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Iniciar log de sincronização
     */
    private function iniciarLog($tipo, $usuario_id) {
        $stmt = $this->pdo->prepare("
            INSERT INTO logs_sincronizacao_mp (tipo, inicio, executado_por, status)
            VALUES (?, NOW(), ?, 'em_progresso')
        ");
        $stmt->execute([$tipo, $usuario_id]);
        $this->log_id = $this->pdo->lastInsertId();
    }
    
    /**
     * Finalizar log de sincronização
     */
    private function finalizarLog($status, $mensagem_erro = null) {
        $stmt = $this->pdo->prepare("
            UPDATE logs_sincronizacao_mp SET
                fim = NOW(),
                duracao_ms = TIMESTAMPDIFF(MICROSECOND, inicio, NOW()) / 1000,
                transacoes_processadas = ?,
                transacoes_novas = ?,
                transacoes_atualizadas = ?,
                erros = ?,
                status = ?,
                mensagem_erro = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $this->stats['processadas'],
            $this->stats['novas'],
            $this->stats['atualizadas'],
            $this->stats['erros'],
            $status,
            $mensagem_erro,
            $this->log_id
        ]);
    }
}

// ========================================
// 🚀 EXECUÇÃO (se chamado diretamente)
// ========================================

if (php_sapi_name() === 'cli' || isset($_GET['executar'])) {
    try {
        $config = require __DIR__ . '/../../mercadolivre/config.php';
        $access_token = $config['accesstoken'];
        
        if (empty($access_token)) {
            throw new Exception('Access token não configurado');
        }
        
        $sincronizador = new SincronizadorTransacoesMP($pdo, $access_token);
        
        // Parâmetros
        $opcoes = [
            'begin_date' => $_GET['begin_date'] ?? date('Y-m-d', strtotime('-7 days')),
            'end_date' => $_GET['end_date'] ?? date('Y-m-d'),
            'tipo' => $_GET['tipo'] ?? 'manual',
            'usuario_id' => $_SESSION['usuario_id'] ?? null
        ];
        
        echo "🔄 Iniciando sincronização...\n";
        echo "📅 Período: {$opcoes['begin_date']} até {$opcoes['end_date']}\n\n";
        
        $resultado = $sincronizador->sincronizar($opcoes);
        
        echo "\n✅ Sincronização concluída!\n";
        echo "📊 Estatísticas:\n";
        echo "  - Processadas: {$resultado['stats']['processadas']}\n";
        echo "  - Novas: {$resultado['stats']['novas']}\n";
        echo "  - Atualizadas: {$resultado['stats']['atualizadas']}\n";
        echo "  - Erros: {$resultado['stats']['erros']}\n";
        
    } catch (Exception $e) {
        echo "❌ Erro: " . $e->getMessage() . "\n";
        exit(1);
    }
}
