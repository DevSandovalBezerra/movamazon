<?php
/**
 * Helper para operacoes com pagamentos do Mercado Pago.
 */

class PaymentHelper
{
    private string $accessToken;
    private string $baseUrl = 'https://api.mercadopago.com/v1';

    /** @var callable */
    private $httpExecutor;

    public function __construct(?array $config = null, ?callable $httpExecutor = null)
    {
        $config = $config ?? $this->loadDefaultConfig();
        $this->accessToken = (string) ($config['accesstoken'] ?? '');

        if ($this->accessToken === '') {
            throw new Exception('Access token do Mercado Pago nao configurado');
        }

        $this->httpExecutor = $httpExecutor ?? [$this, 'defaultHttpExecutor'];
    }

    public function consultarStatusPagamento($payment_id): array
    {
        $payment_id = trim((string) $payment_id);
        if ($payment_id === '') {
            throw new Exception('ID do pagamento ou external_reference e obrigatorio');
        }

        if (!preg_match('/^\d+$/', $payment_id)) {
            $real_id = $this->buscarPaymentIdPorExternalReference($payment_id);
            if ($real_id === null) {
                throw new Exception(
                    'Nenhum pagamento encontrado para a referencia: ' . $payment_id .
                    '. Verifique se o webhook esta configurado (ML_NOTIFICATION_URL no .env e URL no painel do Mercado Pago).'
                );
            }
            $payment_id = $real_id;
        }

        $url = $this->baseUrl . '/payments/' . $payment_id;
        $response = $this->httpRequest('GET', $url);

        if (($response['error'] ?? '') !== '') {
            throw new Exception('Erro de conexao: ' . $response['error']);
        }

        $httpCode = (int) ($response['http_code'] ?? 0);
        $rawBody = (string) ($response['body'] ?? '');

        if ($httpCode !== 200) {
            $errorData = json_decode($rawBody, true);
            $errorMessage = is_array($errorData) ? ($errorData['message'] ?? "Erro HTTP $httpCode") : "Erro HTTP $httpCode";
            throw new Exception('Erro ao consultar pagamento: ' . $errorMessage);
        }

        $paymentData = json_decode($rawBody, true);
        if (!is_array($paymentData) || !isset($paymentData['id'])) {
            throw new Exception('Resposta invalida do Mercado Pago');
        }

        return $paymentData;
    }

    public function buscarPaymentIdPorExternalReference($external_reference): ?string
    {
        $url = $this->baseUrl . '/payments/search?external_reference=' . urlencode((string) $external_reference) . '&limit=20&offset=0';
        $response = $this->httpRequest('GET', $url);
        $httpCode = (int) ($response['http_code'] ?? 0);
        $rawBody = (string) ($response['body'] ?? '');

        if ($httpCode !== 200 || $rawBody === '') {
            return null;
        }

        $data = json_decode($rawBody, true);
        $results = is_array($data) ? ($data['results'] ?? []) : [];
        if (!is_array($results) || empty($results)) {
            return null;
        }

        $best = null;
        $bestTime = -1;
        foreach ($results as $result) {
            if (!is_array($result) || !isset($result['id'])) {
                continue;
            }
            $ts = $result['date_last_updated'] ?? ($result['date_created'] ?? '');
            $time = $ts ? strtotime((string) $ts) : 0;
            if ($time >= $bestTime) {
                $bestTime = $time;
                $best = $result;
            }
        }

        if (!is_array($best) || !isset($best['id'])) {
            return null;
        }

        return (string) $best['id'];
    }

    public function processarReembolso($payment_id, $amount = null): array
    {
        $payload = [];
        if ($amount !== null && $amount > 0) {
            $payload['amount'] = (float) $amount;
        }

        $url = $this->baseUrl . '/payments/' . $payment_id . '/refunds';
        $response = $this->httpRequest('POST', $url, $payload);

        if (($response['error'] ?? '') !== '') {
            throw new Exception('Erro de conexao: ' . $response['error']);
        }

        $httpCode = (int) ($response['http_code'] ?? 0);
        $rawBody = (string) ($response['body'] ?? '');

        if ($httpCode !== 200 && $httpCode !== 201) {
            $errorData = json_decode($rawBody, true);
            $errorMessage = is_array($errorData) ? ($errorData['message'] ?? "Erro HTTP $httpCode") : "Erro HTTP $httpCode";
            throw new Exception('Erro ao processar reembolso: ' . $errorMessage);
        }

        $refundData = json_decode($rawBody, true);
        return is_array($refundData) ? $refundData : [];
    }

    public static function mapearStatus($statusMP): string
    {
        $statusMap = [
            'approved' => 'pago',
            'pending' => 'pendente',
            'in_process' => 'processando',
            'rejected' => 'rejeitado',
            'cancelled' => 'cancelado',
            'refunded' => 'reembolsado',
        ];

        return $statusMap[$statusMP] ?? 'pendente';
    }

    public static function mapearStatusPagamentosML($statusMP): string
    {
        $statusMap = [
            'approved' => 'pago',
            'pending' => 'pendente',
            'in_process' => 'processando',
            'rejected' => 'rejeitado',
            'cancelled' => 'cancelado',
            'refunded' => 'cancelado',
        ];

        return $statusMap[$statusMP] ?? 'pendente';
    }

    private function loadDefaultConfig(): array
    {
        return require __DIR__ . '/config.php';
    }

    private function httpRequest(string $method, string $url, array $payload = []): array
    {
        return call_user_func($this->httpExecutor, $method, $url, $this->accessToken, $payload);
    }

    private function defaultHttpExecutor(string $method, string $url, string $accessToken, array $payload = []): array
    {
        $curl = curl_init();

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json',
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CUSTOMREQUEST => $method,
        ];

        if ($method === 'POST') {
            $options[CURLOPT_POSTFIELDS] = !empty($payload) ? json_encode($payload) : '{}';
        }

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        return [
            'http_code' => (int) $httpCode,
            'body' => (string) $response,
            'error' => (string) $curlError,
        ];
    }
}
