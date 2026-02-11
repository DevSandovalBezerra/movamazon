<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../db.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit;
}

try {
    $body = json_decode(file_get_contents('php://input'));
    if (!$body) {
        throw new Exception('Dados inválidos');
    }

    $isPix = isset($body->payment_method_id) && $body->payment_method_id === 'pix';

    if (!isset($body->transaction_amount) || !isset($body->payer) || !isset($body->payer->email) || !isset($body->payment_method_id)) {
        throw new Exception('Campos obrigatórios ausentes');
    }

    if (!$isPix && (!isset($body->token) || !isset($body->issuer_id) || !isset($body->installments))) {
        throw new Exception('Dados do cartão incompletos');
    }

    $accessToken = $_ENV['ML_ACCESS_TOKEN'] ?? '';
    if (!$accessToken) {
        throw new Exception('Access token não configurado');
    }

    $notificationUrl = $_ENV['ML_NOTIFICATION_URL'] ?? '';

    $externalPrefix = $_ENV['ML_EXTERNAL_REFERENCE'] ?? 'MOVAMAZON_';
    $inscricaoId = null;
    if (isset($_SESSION['pagamento_ml']['dados_inscricao']['id'])) {
        $inscricaoId = (string)$_SESSION['pagamento_ml']['dados_inscricao']['id'];
    } elseif (isset($_SESSION['inscricao']['id'])) {
        $inscricaoId = (string)$_SESSION['inscricao']['id'];
    }
    if (!$inscricaoId) {
        throw new Exception('Inscrição não encontrada para referência externa');
    }

    $payload = [
        'description' => isset($body->description) ? $body->description : 'Inscrição MovAmazon',
        'payment_method_id' => $body->payment_method_id,
        'transaction_amount' => (float)$body->transaction_amount,
        'payer' => [
            'email' => $body->payer->email,
            'identification' => isset($body->payer->identification) ? [
                'type' => $body->payer->identification->type ?? '',
                'number' => $body->payer->identification->number ?? ''
            ] : null
        ],
        'installments' => $isPix ? 1 : (int)$body->installments
    ];

    if (!$isPix) {
        $payload['issuer_id'] = (string)$body->issuer_id;
        $payload['token'] = (string)$body->token;
    }

    $externalReference = $externalPrefix . $inscricaoId;
    $payload['external_reference'] = $externalReference;
    if ($notificationUrl) {
        $payload['notification_url'] = $notificationUrl;
    }

    try {
        $up = $pdo->prepare("UPDATE inscricoes SET external_reference = ? WHERE id = ?");
        $up->execute([$externalReference, (int)$inscricaoId]);
    } catch (Exception $e) {
        // silencioso para não quebrar o fluxo
        error_log('Falha ao atualizar external_reference em inscricoes: ' . $e->getMessage());
    }

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.mercadopago.com/v1/payments',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
            'X-Idempotency-Key: ' . uniqid('payment_', true)
        ),
    ));

    $response = curl_exec($curl);
    $http = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        throw new Exception('Erro na chamada ao Mercado Pago');
    }

    http_response_code($http);
    echo $response;
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>


