<?php
/**
 * Script de Diagnóstico: Listar Payment Methods do Mercado Pago
 * 
 * Objetivo: Descobrir quais payment_method_id estão disponíveis/ativos
 *           para boletos na conta atual do Mercado Pago
 * 
 * Uso: Acessar via browser: https://www.movamazon.com.br/api/diagnostico/listar_payment_methods.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../db.php';

try {
    $config = require_once __DIR__ . '/../mercadolivre/config.php';
    
    if (!$config['has_valid_tokens']) {
        throw new Exception('Tokens do Mercado Pago não configurados');
    }
    
    $accessToken = $config['accesstoken'];
    
    // Consultar API do Mercado Pago
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.mercadopago.com/v1/payment_methods');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        throw new Exception("Erro cURL: $curlError");
    }
    
    if ($httpCode !== 200) {
        throw new Exception("Erro HTTP $httpCode: $response");
    }
    
    $methods = json_decode($response, true);
    
    if (!is_array($methods)) {
        throw new Exception("Resposta inválida da API");
    }
    
    // Filtrar apenas boletos/tickets
    $boletos = array_filter($methods, function($m) {
        return ($m['payment_type_id'] ?? '') === 'ticket' || 
               stripos($m['id'] ?? '', 'boleto') !== false ||
               stripos($m['id'] ?? '', 'bol') !== false;
    });
    
    // HTML de saída
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Payment Methods - Diagnóstico</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                max-width: 1200px;
                margin: 40px auto;
                padding: 20px;
                background: #f5f5f5;
            }
            .header {
                background: white;
                padding: 20px;
                border-radius: 8px;
                margin-bottom: 20px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .status {
                display: inline-block;
                padding: 4px 12px;
                border-radius: 12px;
                font-size: 12px;
                font-weight: 600;
                margin-left: 8px;
            }
            .status.active {
                background: #d4edda;
                color: #155724;
            }
            .status.inactive {
                background: #f8d7da;
                color: #721c24;
            }
            .method-card {
                background: white;
                padding: 20px;
                border-radius: 8px;
                margin-bottom: 16px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                border-left: 4px solid #ddd;
            }
            .method-card.active {
                border-left-color: #28a745;
            }
            .method-card.inactive {
                border-left-color: #dc3545;
                opacity: 0.7;
            }
            .method-id {
                font-size: 18px;
                font-weight: 600;
                color: #2c3e50;
                margin-bottom: 8px;
            }
            .method-name {
                color: #6c757d;
                margin-bottom: 12px;
            }
            .method-details {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 12px;
                font-size: 14px;
                color: #495057;
            }
            .detail-item {
                display: flex;
                flex-direction: column;
            }
            .detail-label {
                font-weight: 600;
                color: #6c757d;
                font-size: 12px;
                text-transform: uppercase;
                margin-bottom: 4px;
            }
            .code-block {
                background: #282c34;
                color: #abb2bf;
                padding: 16px;
                border-radius: 8px;
                overflow-x: auto;
                margin-top: 20px;
                font-family: 'Courier New', monospace;
                font-size: 13px;
            }
            .recommendation {
                background: #fff3cd;
                border-left: 4px solid #ffc107;
                padding: 16px;
                border-radius: 8px;
                margin-top: 20px;
            }
            .recommendation h3 {
                margin-top: 0;
                color: #856404;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>🔍 Payment Methods - Diagnóstico</h1>
            <p><strong>Status HTTP:</strong> <?= $httpCode ?></p>
            <p><strong>Total de métodos:</strong> <?= count($methods) ?></p>
            <p><strong>Boletos encontrados:</strong> <?= count($boletos) ?></p>
            <p><strong>Timestamp:</strong> <?= date('Y-m-d H:i:s') ?></p>
        </div>
        
        <h2>📋 Boletos/Tickets Disponíveis</h2>
        
        <?php if (empty($boletos)): ?>
            <div class="method-card">
                <p style="color: #dc3545; font-weight: 600;">❌ Nenhum boleto encontrado!</p>
            </div>
        <?php else: ?>
            <?php foreach ($boletos as $boleto): 
                $status = $boleto['status'] ?? 'unknown';
                $isActive = $status === 'active';
                $statusClass = $isActive ? 'active' : 'inactive';
                $statusEmoji = $isActive ? '✅' : '❌';
            ?>
                <div class="method-card <?= $statusClass ?>">
                    <div class="method-id">
                        <?= $statusEmoji ?> <?= htmlspecialchars($boleto['id']) ?>
                        <span class="status <?= $statusClass ?>"><?= strtoupper($status) ?></span>
                    </div>
                    <div class="method-name">
                        <?= htmlspecialchars($boleto['name'] ?? 'N/A') ?>
                    </div>
                    <div class="method-details">
                        <div class="detail-item">
                            <span class="detail-label">Payment Type</span>
                            <span><?= htmlspecialchars($boleto['payment_type_id'] ?? 'N/A') ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Issuer ID</span>
                            <span><?= htmlspecialchars($boleto['issuer_id'] ?? 'N/A') ?></span>
                        </div>
                        <?php if (isset($boleto['min_allowed_amount'])): ?>
                        <div class="detail-item">
                            <span class="detail-label">Valor Mínimo</span>
                            <span>R$ <?= number_format($boleto['min_allowed_amount'], 2, ',', '.') ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (isset($boleto['max_allowed_amount'])): ?>
                        <div class="detail-item">
                            <span class="detail-label">Valor Máximo</span>
                            <span>R$ <?= number_format($boleto['max_allowed_amount'], 2, ',', '.') ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php
        // Identificar o método ativo para usar
        $activeMethod = null;
        foreach ($boletos as $boleto) {
            if (($boleto['status'] ?? '') === 'active') {
                $activeMethod = $boleto['id'];
                break;
            }
        }
        ?>
        
        <?php if ($activeMethod): ?>
            <div class="recommendation">
                <h3>✅ Recomendação</h3>
                <p>Use o seguinte payment_method_id no arquivo <strong>api/inscricao/create_boleto.php</strong> (linha 237):</p>
                <div class="code-block">
"payment_method_id" => "<?= $activeMethod ?>",
                </div>
            </div>
        <?php else: ?>
            <div class="recommendation" style="background: #f8d7da; border-left-color: #dc3545;">
                <h3>❌ Problema Crítico</h3>
                <p>Nenhum método de boleto está ATIVO! Possíveis causas:</p>
                <ul>
                    <li>Conta Mercado Pago não habilitada para boleto</li>
                    <li>Conta em modo sandbox (mas config.php indica produção)</li>
                    <li>Restrição regional ou contratual</li>
                </ul>
                <p><strong>Ação:</strong> Contactar suporte do Mercado Pago</p>
            </div>
        <?php endif; ?>
        
        <h2>🔧 Método Atual no Código</h2>
        <div class="code-block">
// Arquivo: api/inscricao/create_boleto.php (linha 237)
"payment_method_id" => "bolbradesco",  // ❌ VERIFICAR SE ESTÁ ATIVO ACIMA
        </div>
        
        <h2>📊 Resposta Completa da API (JSON)</h2>
        <details>
            <summary style="cursor: pointer; padding: 12px; background: white; border-radius: 8px; margin-top: 20px;">
                Clique para expandir JSON completo
            </summary>
            <div class="code-block">
<?= json_encode($boletos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
            </div>
        </details>
    </body>
    </html>
    <?php
    
} catch (Exception $e) {
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <title>Erro - Diagnóstico</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                max-width: 800px;
                margin: 40px auto;
                padding: 20px;
            }
            .error {
                background: #f8d7da;
                border-left: 4px solid #dc3545;
                padding: 20px;
                border-radius: 8px;
            }
        </style>
    </head>
    <body>
        <div class="error">
            <h2>❌ Erro</h2>
            <p><?= htmlspecialchars($e->getMessage()) ?></p>
            <pre><?= htmlspecialchars($e->getTraceAsString()) ?></pre>
        </div>
    </body>
    </html>
    <?php
}
