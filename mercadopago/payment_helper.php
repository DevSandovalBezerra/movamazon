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
     * Consultar status de um pagamento
     * @param string $payment_id ID do pagamento no Mercado Pago
     * @return array Dados do pagamento
     */
    public function consultarStatusPagamento($payment_id) {
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
