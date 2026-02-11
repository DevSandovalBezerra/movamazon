<?php
ini_set('max_execution_time', 120);

$base_path = dirname(__DIR__);
require_once $base_path . '/../db.php';
require_once $base_path . '/../helpers/config_helper.php';

$openaiKey = ConfigHelper::get('ai.openai.api_key');
if (!$openaiKey) {
    // Fallback para .env se não estiver configurado no banco
    function envValue($key, $default = '') {
        $val = getenv($key);
        if ($val === false) {
            $val = $_ENV[$key] ?? $_SERVER[$key] ?? $default;
        }
        return (string) $val;
    }
    $openaiKey = envValue('OPENAI_API_KEY');
}

if (!$openaiKey) {
    http_response_code(500);
    echo json_encode(["error" => "Chave da API OpenAI ausente."]);
    exit;
}

$model = ConfigHelper::get('ai.openai.model', 'gpt-4o');
$temperature = ConfigHelper::get('ai.openai.temperature', 0.5);

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['input']) || trim($data['input']) === '') {
    http_response_code(400);
    echo json_encode(["error" => "Campo 'input' é obrigatório"]);
    exit;
}

$input = $data['input'];

$payload = [
    "model" => $model,
    "messages" => [
        ["role" => "system", "content" => "Você é um especialista em treinamento físico focado em preparação para corridas de rua. Você cria planos de treino personalizados baseados em anamnese do atleta, distância da corrida e tempo disponível até o evento. Seu foco é preparar o atleta de forma segura e eficiente para completar ou melhorar seu desempenho na corrida."],
        ["role" => "user", "content" => $input]
    ],
    "temperature" => (float) $temperature
];

$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Bearer " . $openaiKey
    ],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => (int) ConfigHelper::get('ai.timeout', 120),
    CURLOPT_CONNECTTIMEOUT => 30
]);

$raw_response = curl_exec($ch);

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error_message = curl_error($ch);
$curl_error_number = curl_errno($ch);
curl_close($ch);

if ($raw_response === false || trim($raw_response) === '') {
    http_response_code(502);
    echo json_encode([
        "error" => "Resposta vazia da OpenAI.",
        "details" => $curl_error_message
    ]);
    exit;
}

if ($curl_error_number !== 0) {
    http_response_code(502);
    echo json_encode([
        "error" => "Falha na comunicação com a OpenAI.",
        "details" => "cURL Error $curl_error_number: $curl_error_message"
    ]);
    exit;
}

if ($httpCode !== 200) {
    http_response_code($httpCode);
    $error_details = json_decode($raw_response, true);
    if (json_last_error() === JSON_ERROR_NONE && isset($error_details['error'])) {
        echo json_encode([
            "error" => "Erro da API OpenAI.",
            "details" => $error_details['error']
        ]);
    } else {
        echo json_encode([
            "error" => "Erro da API OpenAI.",
            "details" => "Código HTTP $httpCode. Resposta não JSON ou formato inesperado: $raw_response"
        ]);
    }
    exit;
}

$response_data = json_decode($raw_response, true);
if (json_last_error() !== JSON_ERROR_NONE || !isset($response_data['choices'][0]['message']['content'])) {
    http_response_code(500);
    echo json_encode(["error" => "Resposta da OpenAI malformada ou incompleta.", "details" => $raw_response]);
    exit;
}

$assistant_content = $response_data['choices'][0]['message']['content'];

try {
    $usage = $response_data['usage'] ?? null;
    $modelo = $response_data['model'] ?? ($payload['model'] ?? 'desconhecido');
    $prompt_tokens = $usage['prompt_tokens'] ?? 0;
    $completion_tokens = $usage['completion_tokens'] ?? 0;
    $total_tokens = $usage['total_tokens'] ?? 0;
    
    $stmt = $pdo->prepare('INSERT INTO openai_token_usage 
        (data_hora, usuario_id, endpoint, modelo, prompt_tokens, completion_tokens, total_tokens) 
        VALUES (NOW(), NULL, :endpoint, :modelo, :prompt_tokens, :completion_tokens, :total_tokens)');
    $stmt->execute([
        'endpoint' => 'api/participante/treino/responder_corrida.php',
        'modelo' => $modelo,
        'prompt_tokens' => $prompt_tokens,
        'completion_tokens' => $completion_tokens,
        'total_tokens' => $total_tokens
    ]);
} catch (Exception $e) {
    error_log('Erro ao registrar uso de tokens OpenAI: ' . $e->getMessage());
}

if (strpos($assistant_content, "\xEF\xBB\xBF") === 0) {
    $assistant_content = substr($assistant_content, 3);
}

$content_parts = preg_split('/==Bibliografia Recomendada==/s', $assistant_content, 2);
$content_before_biblio = trim($content_parts[0]);
$json_treino_string = preg_replace('/^```json\s*/s', '', $content_before_biblio);
$json_treino_string = preg_replace('/\s*```$/s', '', $json_treino_string);
$json_treino_string = trim($json_treino_string);

$json_treino_string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $json_treino_string);

json_decode($json_treino_string);
if (json_last_error() !== JSON_ERROR_NONE) {
    if (strlen($json_treino_string) < 100 && !strpos($json_treino_string, '{')) {
        http_response_code(200);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            "status" => "success",
            "response" => $json_treino_string,
            "type" => "simple_text"
        ]);
        exit;
    }
    
    http_response_code(500);
    echo json_encode([
        "error" => "Falha ao processar o JSON do treino da resposta da IA.",
        "details" => "O conteúdo extraído não é um JSON válido. Tente novamente em alguns minutos. Se o erro persistir, contate o suporte.",
        "json_last_error_msg" => json_last_error_msg(),
        "extracted_content_for_json_debug" => $json_treino_string
    ]);
    exit;
}

http_response_code(200);
header('Content-Type: application/json; charset=UTF-8');
echo $json_treino_string;
exit;

