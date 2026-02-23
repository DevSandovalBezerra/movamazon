<?php

class MercadoLivrePayment
{
    private array $config;
    private string $access_token;
    private string $api_url;
    private string $notification_url;
    private string $external_reference_prefix;

    /** @var callable */
    private $requestExecutor;

    /** @var callable */
    private $paymentRepository;

    public function __construct(
        ?array $config = null,
        ?callable $requestExecutor = null,
        ?callable $paymentRepository = null
    ) {
        $this->config = $config ?? $this->loadDefaultConfig();
        $this->access_token = (string) ($this->config['accesstoken'] ?? '');

        if ($this->access_token === '') {
            throw new Exception('Access token nao configurado. Verifique APP_Acess_token e APP_Public_Key no .env');
        }

        $this->api_url = (string) ($this->config['api_url'] ?? 'https://api.mercadopago.com');
        $this->notification_url = (string) ($this->config['url_notification_api'] ?? '');
        $this->external_reference_prefix = (string) ($this->config['external_reference_prefix'] ?? 'MOVAMAZON_');

        $this->requestExecutor = $requestExecutor ?? [$this, 'defaultRequestExecutor'];
        $this->paymentRepository = $paymentRepository ?? [$this, 'defaultPaymentRepository'];
    }

    public function criarPagamento($dados_inscricao): array
    {
        try {
            $this->validarDadosInscricao($dados_inscricao);

            if (function_exists('logMercadoPago')) {
                logMercadoPago('preference', 'Criando preference de pagamento', [
                    'inscricao_id' => $dados_inscricao['id'],
                    'valor_total' => $dados_inscricao['valor_total'],
                    'environment' => $this->config['environment'] ?? 'desconhecido',
                ]);
            }

            $preference_data = $this->prepararDadosPreferencia($dados_inscricao);
            $response = $this->fazerRequisicao('POST', '/checkout/preferences', $preference_data);

            if ($response && isset($response['id'])) {
                $this->salvarPagamento((int) $dados_inscricao['id'], $response);

                if (function_exists('logMercadoPago')) {
                    logMercadoPago('preference', 'Preference criada com sucesso', [
                        'preference_id' => $response['id'],
                        'inscricao_id' => $dados_inscricao['id'],
                    ]);
                }

                return [
                    'success' => true,
                    'preference_id' => $response['id'],
                    'init_point' => $response['init_point'] ?? null,
                ];
            }

            throw new Exception('Resposta invalida do Mercado Pago');
        } catch (Exception $e) {
            error_log('ERRO PAGAMENTO: ' . $e->getMessage());
            error_log('AMBIENTE: ' . ($this->config['environment'] ?? 'desconhecido'));
            error_log('TOKENS CONFIGURADOS: ' . (($this->config['has_valid_tokens'] ?? false) ? 'SIM' : 'NAO'));

            if (function_exists('logMercadoPago')) {
                logMercadoPago('error', 'Erro ao criar pagamento', [
                    'mensagem' => $e->getMessage(),
                    'inscricao_id' => $dados_inscricao['id'] ?? null,
                ]);
            }

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function processarPagamentoDireto($dados_pagamento): array
    {
        try {
            $this->validarDadosPagamentoDireto($dados_pagamento);
            $payment_data = $this->prepararDadosPagamentoDireto($dados_pagamento);
            $response = $this->fazerRequisicao('POST', '/v1/payments', $payment_data);

            if ($response && isset($response['id'])) {
                return [
                    'success' => true,
                    'payment_id' => $response['id'],
                    'status' => $response['status'] ?? null,
                    'payment' => $response,
                ];
            }

            throw new Exception('Resposta invalida do Mercado Pago');
        } catch (Exception $e) {
            error_log('Erro ao processar pagamento direto MP: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function consultarStatus($payment_id): array
    {
        try {
            $response = $this->fazerRequisicao('GET', '/v1/payments/' . $payment_id);

            if ($response) {
                return [
                    'success' => true,
                    'payment' => $response,
                ];
            }

            throw new Exception('Pagamento nao encontrado');
        } catch (Exception $e) {
            error_log('Erro ao consultar status MP: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function processarReembolso($payment_id, $amount = null): array
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
                    'refund' => $response,
                ];
            }

            throw new Exception('Erro ao processar reembolso');
        } catch (Exception $e) {
            error_log('Erro ao processar reembolso ML: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function loadDefaultConfig(): array
    {
        return require __DIR__ . '/config.php';
    }

    private function validarDadosInscricao($dados): void
    {
        $required_fields = ['id', 'modalidade_nome', 'valor_total', 'nome_participante', 'email'];

        foreach ($required_fields as $field) {
            if (!isset($dados[$field]) || empty($dados[$field])) {
                throw new Exception("Campo obrigatorio nao informado: $field");
            }
        }

        if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email invalido');
        }

        if (!is_numeric($dados['valor_total']) || $dados['valor_total'] <= 0) {
            throw new Exception('Valor total invalido');
        }
    }

    private function prepararDadosPreferencia($dados_inscricao): array
    {
        $external_reference = $this->external_reference_prefix . $dados_inscricao['id'];
        $back_urls = $this->config['back_urls'] ?? [];

        if (empty($back_urls['success']) || empty($back_urls['pending']) || empty($back_urls['failure'])) {
            error_log('URLs de retorno invalidas: ' . json_encode($back_urls));
            throw new Exception('URLs de retorno nao configuradas corretamente');
        }

        return [
            'back_urls' => $back_urls,
            'external_reference' => $external_reference,
            'notification_url' => $this->notification_url,
            'items' => [
                [
                    'title' => $this->config['item_defaults']['title'] ?? 'Inscricao MovAmazon',
                    'description' => $this->config['item_defaults']['description'] ?? 'Inscricao',
                    'picture_url' => $this->config['item_defaults']['picture_url'] ?? '',
                    'category_id' => $this->config['item_defaults']['category_id'] ?? 'others',
                    'quantity' => 1,
                    'currency_id' => $this->config['item_defaults']['currency_id'] ?? 'BRL',
                    'unit_price' => (float) $dados_inscricao['valor_total'],
                ],
            ],
            'payment_methods' => $this->config['payment_methods'] ?? [],
        ];
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
                $q = isset($e['quantidade']) ? (int) $e['quantidade'] : 1;
                $n = isset($e['nome']) ? $e['nome'] : 'Extra';
                $v = isset($e['valor']) ? number_format((float) $e['valor'], 2, ',', '.') : '0,00';
                return $n . ' x' . $q . ' (R$ ' . $v . ')';
            }, $d['produtos_extras']);
            $p[] = 'Extras: ' . implode('; ', $extras);
        }
        if (!empty($d['cupom'])) {
            $desc = isset($d['valor_desconto']) ? number_format((float) $d['valor_desconto'], 2, ',', '.') : '0,00';
            $p[] = 'Cupom: ' . $d['cupom'] . ' (-R$ ' . $desc . ')';
        }
        if (!empty($d['seguro'])) {
            $p[] = 'Seguro contratado';
        }
        return implode(' | ', $p);
    }

    private function validarDadosPagamentoDireto($dados): void
    {
        $campos_obrigatorios = ['transaction_amount', 'payment_method_id', 'payer'];

        foreach ($campos_obrigatorios as $campo) {
            if (empty($dados[$campo])) {
                throw new Exception("Campo obrigatorio nao informado: {$campo}");
            }
        }

        if (!is_numeric($dados['transaction_amount']) || $dados['transaction_amount'] <= 0) {
            throw new Exception('Valor da transacao deve ser um numero positivo');
        }
    }

    private function prepararDadosPagamentoDireto($dados_pagamento): array
    {
        $payment_data = [
            'transaction_amount' => (float) $dados_pagamento['transaction_amount'],
            'payment_method_id' => $dados_pagamento['payment_method_id'],
            'payer' => $dados_pagamento['payer'],
            'description' => $dados_pagamento['description'] ?? 'Pagamento MovAmazon',
            'external_reference' => $this->external_reference_prefix . $dados_pagamento['inscricao_id'],
        ];

        if (isset($dados_pagamento['installments'])) {
            $payment_data['installments'] = (int) $dados_pagamento['installments'];
        }

        if (isset($dados_pagamento['issuer_id'])) {
            $payment_data['issuer_id'] = $dados_pagamento['issuer_id'];
        }

        if (isset($dados_pagamento['token'])) {
            $payment_data['token'] = $dados_pagamento['token'];
        }

        return $payment_data;
    }

    private function fazerRequisicao(string $method, string $endpoint, ?array $data = null): array
    {
        return call_user_func(
            $this->requestExecutor,
            $method,
            $this->api_url,
            $endpoint,
            $data,
            $this->access_token
        );
    }

    private function defaultRequestExecutor(
        string $method,
        string $apiUrl,
        string $endpoint,
        ?array $data,
        string $accessToken
    ): array {
        $url = $apiUrl . $endpoint;
        $curl = curl_init();

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
        ];

        if ($endpoint === '/v1/payments' && $method === 'POST') {
            $headers[] = 'X-Idempotency-Key: ' . uniqid('payment_', true);
        }

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        if ($method === 'POST' && $data) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        error_log('ML API Request - URL: ' . $url);
        error_log('ML API Request - Method: ' . $method);
        error_log('ML API Request - Data: ' . json_encode($data));
        error_log('ML API Response - HTTP Code: ' . $http_code);
        error_log('ML API Response - Response: ' . $response);

        if ($error) {
            throw new Exception('Erro cURL: ' . $error);
        }

        if ($http_code >= 200 && $http_code < 300) {
            $decoded = json_decode((string) $response, true);
            return is_array($decoded) ? $decoded : [];
        }

        $error_data = json_decode((string) $response, true);
        $error_message = is_array($error_data)
            ? ($error_data['message'] ?? ('Erro na requisicao MP: ' . $http_code))
            : ('Erro na requisicao MP: ' . $http_code);

        throw new Exception($error_message);
    }

    private function salvarPagamento(int $inscricao_id, array $ml_response): void
    {
        call_user_func($this->paymentRepository, $inscricao_id, $ml_response);
    }

    private function defaultPaymentRepository(int $inscricao_id, array $ml_response): void
    {
        try {
            $pdo = $GLOBALS['pdo'] ?? null;
            if (!$pdo instanceof PDO) {
                require_once __DIR__ . '/../db.php';
                $pdo = $GLOBALS['pdo'] ?? null;
            }

            if (!$pdo instanceof PDO) {
                error_log('PDO nao disponivel para salvar pagamento ML');
                return;
            }

            $stmt = $pdo->query("SHOW TABLES LIKE 'pagamentos_ml'");
            if ($stmt->rowCount() === 0) {
                error_log('Tabela pagamentos_ml nao existe - pulando salvamento no banco');
                return;
            }

            $sql = "INSERT INTO pagamentos_ml (
                inscricao_id, preference_id, init_point, status, data_criacao
            ) VALUES (?, ?, ?, 'pending', NOW())";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $inscricao_id,
                $ml_response['id'],
                $ml_response['init_point'] ?? null,
            ]);

            error_log('Pagamento ML salvo - Inscricao ID: ' . $inscricao_id . ', Preference ID: ' . $ml_response['id']);
        } catch (Exception $e) {
            error_log('Erro ao salvar pagamento ML: ' . $e->getMessage());
        }
    }
}
