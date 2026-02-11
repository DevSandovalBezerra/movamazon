<?php
/**
 * Helper para operações com pagamentos do Mercado Pago
 * Centraliza consultas, reembolsos e outras operações
 */

require_once __DIR__ . '/config.php';

class PaymentHelper {
    private $accessToken;
    private $baseUrl = 'https://api.mercadopago.com/v1';
    
    public function __construct() {
        $config = require __DIR__ . '/config.php';
        $this->accessToken = $config['accesstoken'] ?? '';
        
        if (empty($this->accessToken)) {
            throw new Exception('Access token do Mercado Pago não configurado');
        }
    }
    
    /**
     * Consultar status de um pagamento.
     * Aceita payment_id numérico ou external_reference (ex: MOVAMAZON_27).
     * Se for external_reference, busca via API de search e depois consulta o payment.
     * @param string $payment_id ID do pagamento no Mercado Pago ou external_reference
     * @return array Dados do pagamento
     */
    public function consultarStatusPagamento($payment_id) {
        $payment_id = trim((string) $payment_id);
        if ($payment_id === '') {
            throw new Exception('ID do pagamento ou external_reference é obrigatório');
        }

        // Se não for só dígitos, tratar como external_reference (ex: MOVAMAZON_27)
        if (!preg_match('/^\d+$/', $payment_id)) {
            $real_id = $this->buscarPaymentIdPorExternalReference($payment_id);
            if ($real_id === null) {
                throw new Exception('Nenhum pagamento encontrado para a referência: ' . $payment_id . '. Verifique se o webhook está configurado (ML_NOTIFICATION_URL no .env e URL no painel do Mercado Pago).');
            }
            $payment_id = $real_id;
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->baseUrl . '/payments/' . $payment_id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken,
                'Content-Type: application/json'
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        if ($curlError) {
            throw new Exception("Erro de conexão: $curlError");
        }

        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['message'] ?? "Erro HTTP $httpCode";
            throw new Exception("Erro ao consultar pagamento: $errorMessage");
        }

        $paymentData = json_decode($response, true);

        if (!isset($paymentData['id'])) {
            throw new Exception('Resposta inválida do Mercado Pago');
        }

        return $paymentData;
    }

    /**
     * Busca o payment_id numérico pela external_reference (API GET /v1/payments/search).
     * @param string $external_reference Ex: MOVAMAZON_27
     * @return string|null ID do pagamento ou null se não encontrado
     */
    public function buscarPaymentIdPorExternalReference($external_reference) {
        $url = $this->baseUrl . '/payments/search?external_reference=' . urlencode($external_reference) . '&limit=1&offset=0';
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken,
                'Content-Type: application/json'
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode !== 200 || !$response) {
            return null;
        }

        $data = json_decode($response, true);
        $results = $data['results'] ?? [];
        if (empty($results) || !isset($results[0]['id'])) {
            return null;
        }

        return (string) $results[0]['id'];
    }
    
    /**
     * Processar reembolso de um pagamento
     * @param string $payment_id ID do pagamento
     * @param float|null $amount Valor do reembolso (null = reembolso total)
     * @return array Dados do reembolso
     */
    public function processarReembolso($payment_id, $amount = null) {
        $payload = [];
        if ($amount !== null && $amount > 0) {
            $payload['amount'] = (float)$amount;
        }
        
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->baseUrl . '/payments/' . $payment_id . '/refunds',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => !empty($payload) ? json_encode($payload) : '{}',
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->accessToken,
                'Content-Type: application/json'
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);
        
        if ($curlError) {
            throw new Exception("Erro de conexão: $curlError");
        }
        
        if ($httpCode !== 200 && $httpCode !== 201) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['message'] ?? "Erro HTTP $httpCode";
            throw new Exception("Erro ao processar reembolso: $errorMessage");
        }
        
        $refundData = json_decode($response, true);
        
        return $refundData;
    }
    
    /**
     * Mapear status do Mercado Pago para status interno
     * @param string $statusMP Status do Mercado Pago
     * @return string Status interno
     */
    public static function mapearStatus($statusMP) {
        $statusMap = [
            'approved' => 'pago',
            'pending' => 'pendente',
            'in_process' => 'processando',
            'rejected' => 'rejeitado',
            'cancelled' => 'cancelado',
            'refunded' => 'reembolsado'
        ];
        
        return $statusMap[$statusMP] ?? 'pendente';
    }
    
    /**
     * Mapear status interno para status da tabela pagamentos_ml
     * @param string $statusMP Status do Mercado Pago
     * @return string Status para pagamentos_ml
     */
    public static function mapearStatusPagamentosML($statusMP) {
        $statusMap = [
            'approved' => 'pago',
            'pending' => 'pendente',
            'in_process' => 'processando',
            'rejected' => 'rejeitado',
            'cancelled' => 'cancelado',
            'refunded' => 'cancelado'
        ];
        
        return $statusMap[$statusMP] ?? 'pendente';
    }
}
