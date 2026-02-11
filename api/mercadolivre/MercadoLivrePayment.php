<?php
require_once __DIR__ . '/../db.php';
// ✅ Removido require_once config.php - será carregado no __construct
// A função logMercadoPago() está protegida contra redeclaração

class MercadoLivrePayment
{
    private $config;
    private $access_token;
    private $api_url;
    private $notification_url;
    private $external_reference_prefix;

    public function __construct()
    {
        // ✅ Carregar config apenas uma vez no construtor
        $this->config = require __DIR__ . '/config.php';
        $this->access_token = $this->config['accesstoken'];
        
        // Validação crítica: se access_token vazio, lançar exceção
        if (empty($this->access_token)) {
            throw new Exception("Access token não configurado. Verifique APP_Acess_token e APP_Public_Key no .env");
        }
        
        $this->api_url = 'https://api.mercadopago.com';
        $this->notification_url = $this->config['url_notification_api'];
        $this->external_reference_prefix = 'MOVAMAZON_';
    }

    /**
     * Criar preferência de pagamento (para Payment Brick)
     */
    public function criarPagamento($dados_inscricao)
    {
        try {
            // Validar dados obrigatórios
            $this->validarDadosInscricao($dados_inscricao);

            // Log de auditoria
            if (function_exists('logMercadoPago')) {
                logMercadoPago('preference', 'Criando preference de pagamento', [
                    'inscricao_id' => $dados_inscricao['id'],
                    'valor_total' => $dados_inscricao['valor_total'],
                    'environment' => $this->config['environment']
                ]);
            }

            // Preparar dados para Mercado Pago
            $preference_data = $this->prepararDadosPreferencia($dados_inscricao);

            // Criar preferência no Mercado Pago
            $response = $this->fazerRequisicao('POST', '/checkout/preferences', $preference_data);

            if ($response && isset($response['id'])) {
                // Salvar dados do pagamento no banco
                $this->salvarPagamento($dados_inscricao['id'], $response);

                if (function_exists('logMercadoPago')) {
                    logMercadoPago('preference', 'Preference criada com sucesso', [
                        'preference_id' => $response['id'],
                        'inscricao_id' => $dados_inscricao['id']
                    ]);
                }

                return [
                    'success' => true,
                    'preference_id' => $response['id'],
                    'init_point' => $response['init_point']
                ];
            }

            throw new Exception('Resposta inválida do Mercado Pago');
        } catch (Exception $e) {
            // Log detalhado do erro
            error_log("ERRO PAGAMENTO: " . $e->getMessage());
            error_log("AMBIENTE: " . ($this->config['environment'] ?? 'desconhecido'));
            error_log("TOKENS CONFIGURADOS: " . ($this->config['has_valid_tokens'] ? 'SIM' : 'NÃO'));
            
            if (function_exists('logMercadoPago')) {
                logMercadoPago('error', 'Erro ao criar pagamento', [
                    'mensagem' => $e->getMessage(),
                    'inscricao_id' => $dados_inscricao['id'] ?? null
                ]);
            }
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    /**
     * Processar pagamento direto (como card.php)
     */
    public function processarPagamentoDireto($dados_pagamento)
    {
        try {
            // Validar dados obrigatórios para pagamento direto
            $this->validarDadosPagamentoDireto($dados_pagamento);

            // Preparar dados para pagamento direto
            $payment_data = $this->prepararDadosPagamentoDireto($dados_pagamento);

            // Processar pagamento no Mercado Pago
            $response = $this->fazerRequisicao('POST', '/v1/payments', $payment_data);

            if ($response && isset($response['id'])) {
                return [
                    'success' => true,
                    'payment_id' => $response['id'],
                    'status' => $response['status'],
                    'payment' => $response
                ];
            }

            throw new Exception('Resposta inválida do Mercado Pago');
        } catch (Exception $e) {
            error_log('Erro ao processar pagamento direto MP: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Consultar status de um pagamento
     */
    public function consultarStatus($payment_id)
    {
        try {
            $response = $this->fazerRequisicao('GET', '/v1/payments/' . $payment_id);

            if ($response) {
                return [
                    'success' => true,
                    'payment' => $response
                ];
            }

            throw new Exception('Pagamento não encontrado');
        } catch (Exception $e) {
            error_log('Erro ao consultar status MP: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Processar reembolso
     */
    public function processarReembolso($payment_id, $amount = null)
    {
        try {
            $data = [];
            if ($amount) {
                $data['amount'] = $amount;
            }

            $response = $this->fazerRequisicao('POST', '/payments/' . $payment_id . '/refunds', $data);

            if ($response) {
                return [
                    'success' => true,
                    'refund' => $response
                ];
            }

            throw new Exception('Erro ao processar reembolso');
        } catch (Exception $e) {
            error_log('Erro ao processar reembolso ML: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validar dados da inscrição
     */
    private function validarDadosInscricao($dados)
    {
        $required_fields = ['id', 'modalidade_nome', 'valor_total', 'nome_participante', 'email'];

        foreach ($required_fields as $field) {
            if (!isset($dados[$field]) || empty($dados[$field])) {
                throw new Exception("Campo obrigatório não informado: $field");
            }
        }

        if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email inválido');
        }

        if (!is_numeric($dados['valor_total']) || $dados['valor_total'] <= 0) {
            throw new Exception('Valor total inválido');
        }
    }

    /**
     * Preparar dados da preferência baseado no preference.php funcional
     */
    private function prepararDadosPreferencia($dados_inscricao)
    {
        $external_reference = $this->external_reference_prefix . $dados_inscricao['id'];
        
        // Garantir que as URLs de retorno sejam válidas
        $back_urls = $this->config['back_urls'];
        
        // Validar que todas as URLs estão definidas
        if (empty($back_urls['success']) || empty($back_urls['pending']) || empty($back_urls['failure'])) {
            error_log("⚠️ URLs de retorno inválidas: " . json_encode($back_urls));
            throw new Exception('URLs de retorno não configuradas corretamente');
        }
        
        // Log para debug
        error_log("🔗 URLs de retorno configuradas:");
        error_log("  - Success: " . $back_urls['success']);
        error_log("  - Pending: " . $back_urls['pending']);
        error_log("  - Failure: " . $back_urls['failure']);
        error_log("  - Base URL: " . ($this->config['_debug_base_url'] ?? 'não definida'));

        $preference_data = [
            'back_urls' => $back_urls,
            'external_reference' => $external_reference,
            'notification_url' => $this->notification_url,
            // Remover auto_return ou usar 'all' - o Mercado Pago pode estar rejeitando 'approved'
            // 'auto_return' => 'approved', // Comentado para evitar erro
            'items' => [
                [
                    'title' => $this->config['item_defaults']['title'],
                    'description' => $this->config['item_defaults']['description'],
                    'picture_url' => $this->config['item_defaults']['picture_url'],
                    'category_id' => $this->config['item_defaults']['category_id'],
                    'quantity' => 1,
                    'currency_id' => $this->config['item_defaults']['currency_id'],
                    'unit_price' => (float)$dados_inscricao['valor_total']
                ]
            ],
            'payment_methods' => $this->config['payment_methods']
        ];
        
        return $preference_data;
    }

    private function montarDescricao(array $d): string
    {
        $p = [];
        if (!empty($d['evento_nome'])) {
            $p[] = 'Evento: ' . $d['evento_nome'];
        }
        if (!empty($d['modalidade_nome'])) {
            $p[] = 'Modalidade: ' . $d['modalidade_nome'];
        }
        if (!empty($d['kit_nome'])) {
            $p[] = 'Kit: ' . $d['kit_nome'];
        }
        if (!empty($d['produtos_extras']) && is_array($d['produtos_extras'])) {
            $extras = array_map(function ($e) {
                $q = isset($e['quantidade']) ? (int)$e['quantidade'] : 1;
                $n = isset($e['nome']) ? $e['nome'] : 'Extra';
                $v = isset($e['valor']) ? number_format((float)$e['valor'], 2, ',', '.') : '0,00';
                return $n . ' x' . $q . ' (R$ ' . $v . ')';
            }, $d['produtos_extras']);
            $p[] = 'Extras: ' . implode('; ', $extras);
        }
        if (!empty($d['cupom'])) {
            $desc = isset($d['valor_desconto']) ? number_format((float)$d['valor_desconto'], 2, ',', '.') : '0,00';
            $p[] = 'Cupom: ' . $d['cupom'] . ' (−R$ ' . $desc . ')';
        }
        if (!empty($d['seguro'])) {
            $p[] = 'Seguro contratado';
        }
        return implode(' | ', $p);
    }

    /**
     * Validar dados obrigatórios para pagamento direto
     */
    private function validarDadosPagamentoDireto($dados)
    {
        $campos_obrigatorios = ['transaction_amount', 'payment_method_id', 'payer'];

        foreach ($campos_obrigatorios as $campo) {
            if (empty($dados[$campo])) {
                throw new Exception("Campo obrigatório não informado: {$campo}");
            }
        }

        if (!is_numeric($dados['transaction_amount']) || $dados['transaction_amount'] <= 0) {
            throw new Exception('Valor da transação deve ser um número positivo');
        }
    }

    /**
     * Preparar dados para pagamento direto (baseado no card.php)
     */
    private function prepararDadosPagamentoDireto($dados_pagamento)
    {
        $payment_data = [
            'transaction_amount' => (float)$dados_pagamento['transaction_amount'],
            'payment_method_id' => $dados_pagamento['payment_method_id'],
            'payer' => $dados_pagamento['payer'],
            'description' => $dados_pagamento['description'] ?? 'Pagamento MovAmazon',
            'external_reference' => $this->external_reference_prefix . $dados_pagamento['inscricao_id']
        ];

        // Adicionar campos opcionais se existirem
        if (isset($dados_pagamento['installments'])) {
            $payment_data['installments'] = (int)$dados_pagamento['installments'];
        }

        if (isset($dados_pagamento['issuer_id'])) {
            $payment_data['issuer_id'] = $dados_pagamento['issuer_id'];
        }

        if (isset($dados_pagamento['token'])) {
            $payment_data['token'] = $dados_pagamento['token'];
        }

        return $payment_data;
    }

    /**
     * Fazer requisição para API do Mercado Pago (baseado no preference.php funcional)
     */
    private function fazerRequisicao($method, $endpoint, $data = null)
    {
        $url = $this->api_url . $endpoint;

        $curl = curl_init();

        // ✅ Headers baseados no card.php funcional
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->access_token
        ];

        // ✅ Adicionar X-Idempotency-Key para pagamentos diretos (como card.php)
        if ($endpoint === '/v1/payments' && $method === 'POST') {
            $headers[] = 'X-Idempotency-Key: ' . uniqid('payment_', true);
        }

        // SSL sempre habilitado (produção)
        $ssl_options = [
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ];

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
        ) + $ssl_options);

        if ($method === 'POST' && $data) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        // Log para debug
        error_log("ML API Request - URL: $url");
        error_log("ML API Request - Method: $method");
        error_log("ML API Request - Data: " . json_encode($data));
        error_log("ML API Response - HTTP Code: $http_code");
        error_log("ML API Response - Response: $response");

        if ($error) {
            throw new Exception('Erro cURL: ' . $error);
        }

        if ($http_code >= 200 && $http_code < 300) {
            return json_decode($response, true);
        }

        $error_data = json_decode($response, true);
        $error_message = $error_data['message'] ?? 'Erro na requisição MP: ' . $http_code;

        throw new Exception($error_message);
    }

    /**
     * Salvar dados do pagamento no banco (opcional - pode ser removido se não necessário)
     */
    private function salvarPagamento($inscricao_id, $ml_response)
    {
        global $pdo;

        try {
            // ✅ Verificar se a tabela existe antes de tentar inserir
            $stmt = $pdo->query("SHOW TABLES LIKE 'pagamentos_ml'");
            if ($stmt->rowCount() == 0) {
                error_log("Tabela pagamentos_ml não existe - pulando salvamento no banco");
                return true; // Não é erro crítico
            }

            $sql = "INSERT INTO pagamentos_ml (
                inscricao_id, preference_id, init_point, status, data_criacao
            ) VALUES (?, ?, ?, 'pending', NOW())";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $inscricao_id,
                $ml_response['id'],
                $ml_response['init_point'] ?? null
            ]);

            error_log("✅ Pagamento ML salvo - Inscrição ID: $inscricao_id, Preference ID: " . $ml_response['id']);
        } catch (Exception $e) {
            error_log('Erro ao salvar pagamento ML: ' . $e->getMessage());
            // Não lançar exceção - não é erro crítico
        }
    }
}
