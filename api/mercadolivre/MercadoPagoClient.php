<?php
/**
 * Cliente Mercado Pago: usa SDK oficial (dx-php 3.x) quando disponível,
 * com fallback para requisições cURL (comportamento atual).
 * Centraliza criação de preferência, pagamentos e consulta de status.
 */

if (!defined('MP_CLIENT_LOADED')) {
    define('MP_CLIENT_LOADED', true);
}

class MercadoPagoClient
{
    private string $accessToken;
    private string $apiUrl = 'https://api.mercadopago.com';
    private bool $useSdk = false;

    public function __construct(array $config = null)
    {
        if ($config === null) {
            $config = require __DIR__ . '/config.php';
        }
        $token = $config['accesstoken'] ?? '';
        if ($token === '') {
            throw new Exception('Access token do Mercado Pago não configurado.');
        }
        $this->accessToken = $token;
        $this->initSdk($config);
    }

    /**
     * Inicializa o SDK se vendor/autoload e classes MP existirem.
     */
    private function initSdk(array $config): void
    {
        $autoload = __DIR__ . '/../../vendor/autoload.php';
        if (!is_file($autoload)) {
            return;
        }
        require_once $autoload;
        if (!class_exists(\MercadoPago\MercadoPagoConfig::class)) {
            return;
        }
        \MercadoPago\MercadoPagoConfig::setAccessToken($this->accessToken);
        $this->useSdk = true;
    }

    /**
     * Cria uma preferência (Checkout Pro).
     * @param array $data payload para POST /checkout/preferences
     * @return array ['id' => string, 'init_point' => string, ...] ou exceção
     */
    public function createPreference(array $data): array
    {
        if ($this->useSdk && class_exists(\MercadoPago\Client\Preference\PreferenceClient::class)) {
            try {
                $client = new \MercadoPago\Client\Preference\PreferenceClient();
                $preference = $client->create($data);
                return $this->objectToArray($preference);
            } catch (\MercadoPago\Exceptions\MPApiException $e) {
                $content = $e->getApiResponse()->getContent();
                $msg = is_array($content) && isset($content['message']) ? $content['message'] : $e->getMessage();
                throw new Exception('Mercado Pago Preference: ' . $msg);
            }
        }
        return $this->request('POST', '/checkout/preferences', $data);
    }

    /**
     * Cria um pagamento (card, PIX, boleto, etc.).
     * @param array $data payload para POST /v1/payments
     * @return array resposta da API (id, status, ...)
     */
    public function createPayment(array $data): array
    {
        if ($this->useSdk && class_exists(\MercadoPago\Client\Payment\PaymentClient::class)) {
            try {
                $client = new \MercadoPago\Client\Payment\PaymentClient();
                $requestOptions = new \MercadoPago\Client\Common\RequestOptions();
                $requestOptions->setCustomHeaders(['X-Idempotency-Key: ' . uniqid('payment_', true)]);
                $payment = $client->create($data, $requestOptions);
                return $this->objectToArray($payment);
            } catch (\MercadoPago\Exceptions\MPApiException $e) {
                $content = $e->getApiResponse()->getContent();
                $msg = is_array($content) && isset($content['message']) ? $content['message'] : $e->getMessage();
                throw new Exception('Mercado Pago Payment: ' . $msg);
            }
        }
        return $this->request('POST', '/v1/payments', $data, true);
    }

    /**
     * Consulta um pagamento por ID.
     * @return array resposta da API (id, status, status_detail, ...)
     */
    public function getPayment(string $paymentId): array
    {
        if ($this->useSdk && class_exists(\MercadoPago\Client\Payment\PaymentClient::class)) {
            try {
                $client = new \MercadoPago\Client\Payment\PaymentClient();
                $payment = $client->get($paymentId);
                return $this->objectToArray($payment);
            } catch (\MercadoPago\Exceptions\MPApiException $e) {
                $content = $e->getApiResponse()->getContent();
                $msg = is_array($content) && isset($content['message']) ? $content['message'] : $e->getMessage();
                throw new Exception('Mercado Pago getPayment: ' . $msg);
            }
        }
        return $this->request('GET', '/v1/payments/' . $paymentId);
    }

    /**
     * Converte objeto (resposta do SDK) em array para compatibilidade com código que espera JSON.
     */
    private function objectToArray($obj): array
    {
        if (is_object($obj)) {
            $obj = json_decode(json_encode($obj), true);
        }
        return is_array($obj) ? $obj : [];
    }

    /**
     * Requisição HTTP por cURL (fallback quando SDK não está instalado).
     */
    private function request(string $method, string $endpoint, array $data = null, bool $idempotency = false): array
    {
        $url = $this->apiUrl . $endpoint;
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->accessToken,
        ];
        if ($idempotency && $method === 'POST' && $endpoint === '/v1/payments') {
            $headers[] = 'X-Idempotency-Key: ' . uniqid('payment_', true);
        }

        $curl = curl_init();
        $opts = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ];
        curl_setopt_array($curl, $opts);
        if ($method === 'POST' && $data !== null) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            throw new Exception('Erro cURL: ' . $err);
        }
        $decoded = json_decode($response, true);
        if ($httpCode >= 200 && $httpCode < 300) {
            return $decoded ?? [];
        }
        $msg = $decoded['message'] ?? ('Erro MP HTTP ' . $httpCode);
        throw new Exception($msg);
    }

    public function isUsingSdk(): bool
    {
        return $this->useSdk;
    }
}
