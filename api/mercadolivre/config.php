<?php
// ✅ Configuração baseada no mercadoPago funcional

// ✅ CRÍTICO: Garantir que db.php foi carregado para ter as variáveis de ambiente
if (!function_exists('envValue')) {
    require_once __DIR__ . '/../db.php';
}

// ✅ Função auxiliar para buscar variáveis de ambiente de múltiplas fontes
// (caso envValue não esteja disponível por algum motivo)
if (!function_exists('getEnvVar')) {
    function getEnvVar($key, $default = '') {
        // Tentar getenv primeiro (variáveis de sistema)
        $val = getenv($key);
        if ($val !== false && $val !== '') {
            return $val;
        }
        // Tentar $_ENV (carregado pelo dotenv)
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return $_ENV[$key];
        }
        // Tentar $_SERVER (algumas configs colocam lá)
        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
            return $_SERVER[$key];
        }
        return $default;
    }
}

// Detectar domínio automaticamente para compatibilidade com hospedagem
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Detectar o caminho base do projeto (movamazon)
$project_path = '';

// Tentar detectar pelo REQUEST_URI primeiro
if (isset($_SERVER['REQUEST_URI'])) {
    $request_uri = $_SERVER['REQUEST_URI'];
    // Se contém /movamazon/, extrair o caminho
    if (preg_match('#(/movamazon/)#', $request_uri, $matches)) {
        $project_path = '/movamazon';
    }
}

// Se não encontrou, tentar pelo SCRIPT_NAME
if (empty($project_path) && isset($_SERVER['SCRIPT_NAME'])) {
    $script_name = $_SERVER['SCRIPT_NAME'];
    $path_parts = explode('/', trim($script_name, '/'));
    if (isset($path_parts[0]) && $path_parts[0] === 'movamazon') {
        $project_path = '/movamazon';
    } elseif (isset($path_parts[0]) && $path_parts[0] !== '' && $path_parts[0] !== 'api') {
        // Se houver outro caminho base (e não for 'api'), usar ele
        $project_path = '/' . $path_parts[0];
    }
}

// Se ainda não encontrou, usar padrão baseado no host
if (empty($project_path) && strpos($host, 'localhost') !== false) {
    // Em localhost, assumir que está em /movamazon
    $project_path = '/movamazon';
}

$base_url = $protocol . '://' . $host . $project_path;

// Sistema funciona apenas em produção - tokens de produção obrigatórios
$is_production = true;

// Buscar tokens de produção (obrigatórios)
$access_token = getEnvVar('APP_Acess_token') ?: getEnvVar('APP_Access_token') ?: getEnvVar('ML_ACCESS_TOKEN_PROD') ?: '';
$public_key = getEnvVar('APP_Public_Key') ?: getEnvVar('APP_Public_Keyee') ?: getEnvVar('ML_PUBLIC_KEY_PROD') ?: '';

// Validação crítica: tokens são obrigatórios
if (empty($access_token) || empty($public_key)) {
    throw new Exception('Tokens do Mercado Pago não configurados. Configure APP_Acess_token e APP_Public_Key no .env');
}

// Verificar se tokens estão configurados
$has_valid_tokens = !empty($access_token) && !empty($public_key);

// URL de produção fixa como fallback para garantir domínio correto
$production_base = 'https://www.movamazon.com.br';
$notification_url = getEnvVar('ML_NOTIFICATION_URL') ?: ($production_base . '/api/mercadolivre/webhook.php');
$auto_return_url = getEnvVar('ML_AUTO_RETURN') ?: ($production_base . '/frontend/paginas/participante/pagamento-sucesso.php');

$item_title = getEnvVar('ML_ITEM_TITLE', 'Inscrição MovAmazonas');
$item_description = getEnvVar('ML_ITEM_DESCRIPTION', 'Inscrição confirmada no MovAmazonas');
$item_picture = getEnvVar('ML_ITEM_PICTURE_URL', 'https://www.movamazon.com.br/frontend/assets/img/logo.png');

// Fatura do cartão (statement_descriptor) - até 22 caracteres, sem acento (exigência MP)
$statement_descriptor = getEnvVar('ML_STATEMENT_DESCRIPTOR', 'MOVAMAZON');
$statement_descriptor = preg_replace('/[^A-Za-z0-9\s]/', '', $statement_descriptor);
$statement_descriptor = substr(trim($statement_descriptor), 0, 22);
if ($statement_descriptor === '') {
    $statement_descriptor = 'MOVAMAZON';
}

// Webhook secret: ML_WEBHOOK_SECRET ou Webhook_Secret (como no .env)
$webhook_secret = getEnvVar('ML_WEBHOOK_SECRET') ?: getEnvVar('Webhook_Secret') ?: '';

// Função de log padronizada para auditoria
// ✅ Proteger contra redeclaração
if (!function_exists('logMercadoPago')) {
    function logMercadoPago($tipo, $mensagem, $dados = []) {
        $log = [
            'timestamp' => date('Y-m-d H:i:s'),
            'environment' => 'production',
            'tipo' => $tipo,
            'mensagem' => $mensagem,
            'dados' => $dados
        ];
        error_log('MP_LOG: ' . json_encode($log, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}

// Checkout Pro: back_urls por origem (rollback: usar back_urls antigo se necessário)
$back_urls_inscricao = [
    'success' => $production_base . '/inscricao/sucesso',
    'pending' => $production_base . '/inscricao/sucesso?status=pending',
    'failure' => $production_base . '/inscricao/sucesso?status=failure'
];
$back_urls_participante = [
    'success' => $production_base . '/frontend/paginas/participante/pagamento-sucesso.php',
    'pending' => $production_base . '/frontend/paginas/participante/pagamento-pendente.php',
    'failure' => $production_base . '/frontend/paginas/participante/pagamento-erro.php'
];

return [
    'url_notification_api' => $notification_url,
    'secret_key_webhook' => $webhook_secret,
    'accesstoken' => $access_token,
    'public_key' => $public_key,
    'is_production' => true,
    'environment' => 'production',
    'has_valid_tokens' => $has_valid_tokens,
    'site_base_url' => $production_base,
    'back_urls' => [
        'success' => $auto_return_url,
        'pending' => $production_base . '/frontend/paginas/participante/pagamento-pendente.php',
        'failure' => $production_base . '/frontend/paginas/participante/pagamento-erro.php'
    ],
    'back_urls_inscricao' => $back_urls_inscricao,
    'back_urls_participante' => $back_urls_participante,
    '_debug_base_url' => $base_url,
    'payment_methods' => [
        'excluded_payment_methods' => [
            ['id' => 'master']
        ],
        'excluded_payment_types' => []
    ],
    'item_defaults' => [
        'title' => $item_title,
        'description' => $item_description,
        'picture_url' => $item_picture,
        'category_id' => getenv('ML_ITEM_CATEGORY') ?: 'EBL-Evento Desportivo',
        'currency_id' => 'BRL'
    ],
    'statement_descriptor' => $statement_descriptor
];
