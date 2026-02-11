https://www.mercadopago.com.br/developers/pt/docs/checkout-pro/how-tos/integration-quality

[15:03, 06/02/2026] Eudimaci Manaus: Somente resolver essa ação: SDK do frontend
Instale o SDK MercadoPago.JS V2 para simplificar o uso e interagir de forma segura com nossas APIs, já nos dá 10 pontos.
[15:04, 06/02/2026] Eudimaci Manaus: Essa outra ação: Descrição - Fatura do cartão -  Envie-nos o campo statement_descriptor na solicitação da seção "Pagamentos" para diminuir as chances de contestações, já nos daria mais 10 pontos.
[15:06, 06/02/2026] Eudimaci Manaus: Essa outra ação: SDK do backend Use nossos SDKs de backend e conte com os recursos de server-side das nossas soluções de pagamento on-line, nos daria os pontos necessários para aprovação da integração.


[15:08, 06/02/2026] Eudimaci Manaus: Para instalar o SDK MercadoPago.JS V2 e simplificar a interação com as APIs de forma segura, siga estas etapas:

Inclua o MercadoPago.js no HTML da sua aplicação ou instale o pacote via npm.
HTML:
<script src="https://sdk.mercadopago.com/js/v2"></script>
NPM:
npm install @mercadopago/sdk-js
Adicione a chave pública da conta que está sendo integrada para identificar a conexão com o Mercado Pago.
const mp = new MercadoPago('YOUR_PUBLIC_KEY');
Para mais informações, acesse https://www.mercadopago.com.br/developers/pt/docs/sdks-library/client-side/mp-js-v2
[15:12, 06/02/2026] Eudimaci Manaus: Para utilizar os SDKs de backend do Mercado Pago e acessar os recursos de server-side, siga os passos abaixo:

Instale o SDK: Escolha a linguagem que você prefere (como Java, PHP, Node.js, Python, Ruby, .NET, etc.) e use um gerenciador de pacotes para instalar o SDK. Por exemplo, para PHP, você usaria:
composer require mercadopago/dx-php
Configure seu ambiente: Use suas credenciais de produção para acessar as funcionalidades disponíveis. As credenciais são essenciais para autenticar suas operações com segurança.

Implemente transações: Utilize o SDK para criar e gerenciar preferências de pagamento, processar transações e realizar outras operações seguras. Isso inclui verificar o status de transações, reembolsos e estornos.

ara mais detalhes, visite https://www.mercadopago.com.br/developers/pt/docs/sdks-library/server-side.

Aqui estão algumas informações que podem ser úteis:

Para configurar o campo statement_descriptor e diminuir as chances de contestações, siga estas etapas:

Envie uma solicitação POST ao endpoint de preferências de pagamento.

Inclua o parâmetro statement_descriptor com o nome do seu estabelecimento. Isso permitirá que o nome apareça claramente na fatura do comprador, ajudando na identificação do negócio.

Exemplo de código JSON:

JSON

{
     "statement_descriptor": "MEUNEGOCIO"
   }
Para mais detalhes, consulte https://www.mercadopago.com.br/developers/pt/docs/checkout-pro/additional-settings/preferences/invoice-description.

[15:16, 06/02/2026] Eudimaci Manaus: Aqui estão algumas informações que podem ser úteis:

Para enviar o items.id no request da seção "Pagamentos" e melhorar o índice de aprovação:

Inclua o ID do item na solicitação ao criar uma preferência de pagamento. Isso ajuda o Mercado Pago a coletar mais informações no processo de validação de segurança.
Exemplo de código JSON:

JSON

{
  "items": [
    {
      "id": "1234",
      "title": "Produto Exemplo",
      "quantity": 1,
      "unit_price": 100.0
    }
  ]
}
Esses detalhes ajudam a melhorar a análise de risco e a aprovação dos pagamentos.

Confira mais orientações sobre como melhorar a aprovação de pagamentos https://www.mercadopago.com.br/developers/pt/docs/adobe-commerce/how-tos/improve-payment-approval/recommendations.

Se você precisar de mai…
[15:18, 06/02/2026] Eudimaci Manaus: Para enviar o items.category_id no request da seção "Pagamentos" e melhorar o índice de aprovação:

Inclua a categoria do item ao criar uma preferência de pagamento. Isso fornece mais dados ao Mercado Pago, ajudando a aumentar a segurança e a taxa de aprovação.
Exemplo de código JSON:

JSON

{
  "items": [
    {
      "id": "1234",
      "title": "Produto Exemplo",
      "category_id": "123",  // Categoria do produto
      "quantity": 1,
      "unit_price": 100.0
    }
  ]
}
Você pode ver mais detalhes sobre como melhorar a aprovação de pagamentos https://www.mercadopago.com.br/developers/pt/docs/checkout-bricks/how-tos/improve-payment-approval/recommendation