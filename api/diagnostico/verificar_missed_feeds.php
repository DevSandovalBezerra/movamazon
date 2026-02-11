<?php
/**
 * 🔍 VERIFICAR NOTIFICAÇÕES PERDIDAS (MISSED FEEDS)
 * 
 * Consulta a API do Mercado Pago para ver notificações que falharam
 * 
 * DOCUMENTAÇÃO: https://developers.mercadolivre.com.br/pt_br/produto-receba-notificacoes
 * 
 * USO:
 * php api/diagnostico/verificar_missed_feeds.php
 * OU acesse via navegador
 */

require_once __DIR__ . '/../../api/mercadolivre/config.php';

echo "🔍 VERIFICADOR DE NOTIFICAÇÕES PERDIDAS (MISSED FEEDS)\n";
echo "=" . str_repeat("=", 70) . "\n\n";

// ========================================
// 🔐 CARREGAR CONFIGURAÇÃO
// ========================================

$config = require __DIR__ . '/../mercadolivre/config.php';
$access_token = $config['accesstoken'] ?? '';

if (empty($access_token)) {
    echo "❌ ERRO: Access token não configurado!\n";
    echo "Configure APP_Acess_token no arquivo .env\n\n";
    exit(1);
}

echo "✅ Access token carregado\n";
echo "🌍 Ambiente: " . ($config['environment'] ?? 'desconhecido') . "\n\n";

// ========================================
// 📡 BUSCAR APPLICATION ID
// ========================================

// Primeiro precisamos do APP_ID
// Geralmente está no painel do Mercado Pago, mas vamos tentar extrair do token

echo "🔍 Consultando informações da aplicação...\n\n";

// Fazer uma requisição teste para pegar o user_id
$curl_test = curl_init();
curl_setopt_array($curl_test, [
    CURLOPT_URL => 'https://api.mercadopago.com/users/me',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $access_token
    ],
]);

$response_test = curl_exec($curl_test);
$http_code_test = curl_getinfo($curl_test, CURLINFO_HTTP_CODE);
curl_close($curl_test);

if ($http_code_test === 200) {
    $user_data = json_decode($response_test, true);
    $user_id = $user_data['id'] ?? null;
    echo "✅ User ID: $user_id\n\n";
} else {
    echo "⚠️  Não foi possível obter User ID (HTTP $http_code_test)\n\n";
    $user_id = null;
}

// ========================================
// ❗ IMPORTANTE: APP_ID NECESSÁRIO
// ========================================

echo "❗ ATENÇÃO: Para consultar missed_feeds, você precisa do APP_ID\n\n";
echo "📝 COMO OBTER O APP_ID:\n";
echo "1. Acesse: https://developers.mercadolivre.com.br/devcenter/\n";
echo "2. Clique na sua aplicação\n";
echo "3. Copie o 'client_id' (esse é o APP_ID)\n";
echo "4. Adicione no .env como: ML_APP_ID=SEU_APP_ID\n\n";

// Tentar buscar do .env
$app_id = getenv('ML_APP_ID') ?: getenv('MP_APP_ID') ?: null;

if (!$app_id) {
    echo "⚠️  APP_ID não configurado no .env\n";
    echo "⚠️  Não é possível consultar missed_feeds sem o APP_ID\n\n";
    
    echo "💡 ALTERNATIVA: Verificar webhook manualmente\n";
    echo "1. Acesse: https://www.movamazon.com.br/api/mercadolivre/webhook.php\n";
    echo "2. Verifique os logs em: logs/webhook_mp.log\n";
    echo "3. Use o script: api/diagnostico/testar_webhook.php\n\n";
    exit(1);
}

echo "✅ APP_ID encontrado: $app_id\n\n";

// ========================================
// 📡 CONSULTAR MISSED FEEDS
// ========================================

echo "📡 Consultando notificações perdidas...\n\n";

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.mercadolibre.com/missed_feeds?app_id=$app_id",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $access_token
    ],
]);

$response = curl_exec($curl);
$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

echo "🌐 HTTP Status: $http_code\n\n";

if ($http_code !== 200) {
    echo "❌ ERRO ao consultar missed_feeds\n";
    echo "Resposta: $response\n\n";
    exit(1);
}

$missed_feeds = json_decode($response, true);

// ========================================
// 📊 ANÁLISE DOS RESULTADOS
// ========================================

if (empty($missed_feeds['messages'])) {
    echo "✅ EXCELENTE! Nenhuma notificação perdida!\n\n";
    echo "Isso significa que:\n";
    echo "- Seu webhook está respondendo HTTP 200 corretamente\n";
    echo "- O tempo de resposta está < 500ms\n";
    echo "- Todas as notificações foram processadas com sucesso\n\n";
} else {
    $total = count($missed_feeds['messages']);
    echo "⚠️  ATENÇÃO: $total notificação(ões) perdida(s) encontrada(s)!\n\n";
    
    echo "📋 DETALHES DAS NOTIFICAÇÕES PERDIDAS:\n";
    echo str_repeat("=", 70) . "\n\n";
    
    foreach ($missed_feeds['messages'] as $index => $message) {
        $num = $index + 1;
        echo "[$num/$total] Notificação:\n";
        echo "  🆔 ID: " . ($message['_id'] ?? 'N/A') . "\n";
        echo "  📌 Tópico: " . ($message['topic'] ?? 'N/A') . "\n";
        echo "  🔗 Resource: " . ($message['resource'] ?? 'N/A') . "\n";
        echo "  👤 User ID: " . ($message['user_id'] ?? 'N/A') . "\n";
        echo "  🔄 Tentativas: " . ($message['attempts'] ?? 'N/A') . "\n";
        echo "  📅 Enviado: " . ($message['sent'] ?? 'N/A') . "\n";
        echo "  📥 Recebido: " . ($message['received'] ?? 'N/A') . "\n";
        
        if (isset($message['response'])) {
            echo "  📊 Resposta:\n";
            echo "    - HTTP Code: " . ($message['response']['http_code'] ?? 'N/A') . "\n";
            echo "    - Tempo: " . ($message['response']['req_time'] ?? 'N/A') . "ms\n";
        }
        
        echo "\n";
    }
    
    echo str_repeat("=", 70) . "\n\n";
    
    // ========================================
    // 🔧 RECOMENDAÇÕES
    // ========================================
    
    echo "🔧 RECOMENDAÇÕES PARA CORRIGIR:\n\n";
    
    $slow_responses = 0;
    $error_responses = 0;
    
    foreach ($missed_feeds['messages'] as $message) {
        if (isset($message['response']['req_time']) && $message['response']['req_time'] > 500) {
            $slow_responses++;
        }
        if (isset($message['response']['http_code']) && $message['response']['http_code'] !== 200) {
            $error_responses++;
        }
    }
    
    if ($slow_responses > 0) {
        echo "⚠️  $slow_responses notificação(ões) com resposta > 500ms\n";
        echo "   Solução: O webhook foi otimizado para responder em < 100ms\n";
        echo "   Faça upload do arquivo webhook.php atualizado\n\n";
    }
    
    if ($error_responses > 0) {
        echo "❌ $error_responses notificação(ões) com erro HTTP (não 200)\n";
        echo "   Solução: Verifique os logs para identificar o erro\n";
        echo "   - logs/webhook_mp.log\n";
        echo "   - logs/php_errors.log\n\n";
    }
    
    echo "📝 PRÓXIMAS AÇÕES:\n";
    echo "1. Faça upload do webhook.php otimizado para hospedagem\n";
    echo "2. Teste com: api/diagnostico/testar_webhook.php\n";
    echo "3. Aguarde novos pagamentos para validar\n";
    echo "4. Execute este script novamente em 24h\n\n";
}

// ========================================
// 📊 FILTRAR POR TÓPICO (OPCIONAL)
// ========================================

echo "💡 DICA: Você pode filtrar por tópico específico:\n";
echo "curl -X GET -H 'Authorization: Bearer TOKEN' \\\n";
echo "  'https://api.mercadolibre.com/missed_feeds?app_id=$app_id&topic=payments'\n\n";

echo "✅ VERIFICAÇÃO CONCLUÍDA!\n\n";
