<?php
// ✅ Exemplo de como os endpoints são construídos corretamente

require_once 'MercadoLivrePayment.php';

echo "<h1>Exemplo de Endpoints da API</h1>";

$ml_payment = new MercadoLivrePayment();

echo "<h2>URLs que serão construídas:</h2>";

echo "<h3>1. Para criar preferência (Payment Brick):</h3>";
echo "<code>https://api.mercadopago.com/checkout/preferences</code>";

echo "<h3>2. Para processar pagamento direto:</h3>";
echo "<code>https://api.mercadopago.com/v1/payments</code>";

echo "<h3>3. Para consultar status de pagamento:</h3>";
echo "<code>https://api.mercadopago.com/v1/payments/{payment_id}</code>";

echo "<h2>Estrutura correta:</h2>";
echo "<ul>";
echo "<li><strong>api_url:</strong> https://api.mercadopago.com (URL base)</li>";
echo "<li><strong>endpoint:</strong> /checkout/preferences ou /v1/payments (endpoint específico)</li>";
echo "<li><strong>URL final:</strong> api_url + endpoint</li>";
echo "</ul>";

echo "<h2>Comparação com pasta funcional:</h2>";
echo "<ul>";
echo "<li><strong>preference.php:</strong> https://api.mercadopago.com/checkout/preferences ✅</li>";
echo "<li><strong>card.php:</strong> https://api.mercadopago.com/v1/payments ✅</li>";
echo "<li><strong>pix.php:</strong> https://api.mercadopago.com/v1/payments ✅</li>";
echo "</ul>";
?>
