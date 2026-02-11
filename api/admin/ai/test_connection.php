<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../auth_middleware.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../helpers/config_helper.php';

header('Content-Type: application/json');

if (!requererAdmin(false)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true) ?? [];
$provider = $payload['provider'] ?? 'openai';

if ($provider !== 'openai') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Provedor não suportado ainda']);
    exit;
}

$apiKey = ConfigHelper::get('ai.openai.api_key');
if (!$apiKey) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Chave API não configurada']);
    exit;
}

$model = ConfigHelper::get('ai.openai.model', 'gpt-4o');

$testPayload = [
    "model" => $model,
    "messages" => [
        ["role" => "user", "content" => "Responda apenas com 'OK' se você está funcionando."]
    ],
    "max_tokens" => 10
];

$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Bearer " . $apiKey
    ],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($testPayload),
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CONNECTTIMEOUT => 10
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'message' => 'Erro de conexão',
        'details' => $curlError
    ]);
    exit;
}

if ($httpCode === 200) {
    $data = json_decode($response, true);
    if ($data && isset($data['choices'][0]['message']['content'])) {
        echo json_encode([
            'success' => true,
            'message' => 'Conexão bem-sucedida',
            'model' => $data['model'] ?? $model,
            'response' => trim($data['choices'][0]['message']['content'])
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Resposta inesperada da API'
        ]);
    }
} else {
    $errorData = json_decode($response, true);
    $errorMsg = $errorData['error']['message'] ?? 'Erro desconhecido';
    
    http_response_code($httpCode);
    echo json_encode([
        'success' => false,
        'message' => 'Erro da API',
        'details' => $errorMsg,
        'http_code' => $httpCode
    ]);
}


