EXEMPLO DE INTEGRAÇÂO COM O CHECKOUT PRO

checkoutpro

https://www.mercadopago.com.br/developers/pt/docs/checkout-pro/configure-development-enviroment  

Instale o SDK do Mercado Pago na linguagem que melhor se adapta à sua integração, utilizando um gerenciador de dependências, conforme demonstrado a seguir.

php node java ruby csharp python go

Para instalar o SDK, execute o seguinte comando no seu terminal utilizando o Composer:

php composer.phar require "mercadopago/dx-php"

Inicializar biblioteca do Mercado Pago

Server-Side

A seguir, crie um arquivo principal (main) no backend do seu projeto com a linguagem de programação que você está utilizando. Insira o seguinte código, substituindo o valor TEST_ACCESS_TOKEN pelo Access Token de teste.

- 

php node java ruby csharp python go

<?php
// SDK do Mercado Pago
use MercadoPago\MercadoPagoConfig;
// Adicione credenciais
MercadoPagoConfig::setAccessToken("TEST_ACCESS_TOKEN");
?>

Depois dessas configurações, seu ambiente de desenvolvimento já está pronto para avançar com a configuração de uma preferência de pagamento.

https://www.mercadopago.com.br/developers/pt/docs/checkout-pro/create-payment-preference#editor_1  

Criar e configurar uma preferência de pagamento

Server-Side

Uma preferência de pagamento é um objeto que reúne informações sobre o produto ou serviço pelo qual você deseja cobrar. No ecossistema do Mercado Pago, esse objeto é denominado preference. Ao criar uma preferência de pagamento, é possível definir atributos essenciais, como preço, quantidade e métodos de pagamento, além de configurar outros aspectos do fluxo de pagamento.

Durante esta etapa, você também irá adicionar os meios de pagamento que deseja oferecer com o Checkout Pro, que por padrão inclui todos os meios de pagamento disponíveis no Mercado Pago.

Para configurar uma preferência de pagamento, utilize o método correspondente à preference no SDK de backend. É necessário criar uma nova preferência de pagamento para cada pedido ou fluxo de pagamento que você deseja iniciar.

Abaixo, você encontrará exemplos práticos de como implementar essa funcionalidade em seu backend utilizando o SDK, disponível em várias linguagens de programação. Certifique-se de preencher os atributos com informações precisas para detalhar cada transação e garantir um processo de pagamento eficiente.

Esses atributos permitem ajustar parcelas, excluir determinados meios de pagamento, modificar a data de vencimento de um pagamento, entre outras opções. Para personalizar sua preferência de pagamento, acesse as documentações da seção de Configurações adicionais.

- 

- 

- 

- 

- 

- 

- 

php node java ruby csharp python go

<?php
$client = new PreferenceClient();
$preference = $client->create([
  "items"=> array(
    array(
      "title" => "Meu produto",
      "quantity" => 1,
      "unit_price" => 2000
    )
  )
]);

echo $preference
?>

Obter o identificador da preferência

O identificador da preferência é um código único que representa uma transação específica para uma solicitação de pagamento. Para obtê-lo, você deve executar sua aplicação.

Na resposta, o identificador da preferência estará localizado na propriedade ID. Guarde esse valor com atenção, pois ele será necessário na próxima etapa para integrar o pagamento ao seu site ou aplicativo móvel.

Veja abaixo um exemplo de como o atributo ID, contendo o identificador de preferência, é exibido em uma resposta:



"id": "787997534-6dad21a1-6145-4f0d-ac21-66bf7a5e7a58"

Escolher o tipo de integração

Após obter o ID da preferência, você deve prosseguir para a configuração do frontend. Para isso, escolha o tipo de integração que melhor atenda às suas necessidades, seja para um site ou um aplicativo móvel.

Selecione o tipo de integração que deseja realizar e siga os passos detalhados para completar a integração do Checkout Pro. Selecione a opção de integração desejada e siga as instruções detalhadas para completar a integração do Checkout Pro.

Continuar a integração para sites

Oferece cobranças com redirecionamento para o Mercado Pago no seu site ou loja online.

Integração web

**Continuar a integração **  

Configurar URLs de retorno

A URL de retorno é o endereço para o qual o usuário é redirecionado após completar o pagamento, seja ele bem-sucedido, falho ou pendente. Esta URL deve ser uma página web controlável, como um servidor com domínio nomeado (DNS).

Esse processo é configurado através do atributo back_urls no backend, na preferência de pagamento associada à sua integração. Com este atributo, você pode definir para qual site o comprador será redirecionado, seja automaticamente ou através do botão "Voltar ao site", de acordo com o estado do pagamento.

Você pode configurar até três URLs de retorno diferentes, correspondendo aos cenários de pagamento pendente, sucesso ou erro.

Em integrações mobile, recomendamos que as URLs de retorno sejam deep links. Para saber mais, acesse a documentação Integração para aplicações móveis.

Definir URLs de retorno

No seu código backend, configure a URL para a qual deseja que o Mercado Pago redirecione o usuário após a conclusão do processo de pagamento.

Se preferir, você também pode configurar as URLs de retorno enviando um POST para a API Criar preferência com o atributo back_urls, especificando as URLs para as quais o comprador deve ser redirecionado após finalizar o pagamento.

A seguir, compartilhamos exemplos de como incluir o atributo back_urls de acordo com a linguagem de programação que você está utilizando, além do detalhamento de cada um dos possíveis parâmetros.

- 

- 

- 

- 

- 

- 

php node java ruby csharp python

<?php
$preference = new MercadoPago\Preference();
//...
$preference->back_urls = array(
    "success" => "https://www.seu-site/success",
    "failure" => "https://www.seu-site/failure",
    "pending" => "https://www.seu-site/pending"
);
$preference->auto_return = "approved";
// ...
?>

















Atributo



Descrição





auto_return



Os compradores são redirecionados automaticamente ao site quando o pagamento é aprovado. O valor padrão é approved. O tempo de redirecionamento será de até 40 segundos e não poderá ser personalizado. Por padrão, também será exibido um botão de "Voltar ao site".





back_urls



URL de retorno ao site. Os cenários possíveis são: success: URL de retorno quando o pagamento é aprovado. pending: URL de retorno quando o pagamento está pendente. failure: URL de retorno quando o pagamento é rejeitado.

Resposta das URLs de retorno

As back_urls fornecem vários parâmetros úteis por meio de uma solicitação GET. A seguir, apresentamos um exemplo de resposta, acompanhado de uma explicação detalhada dos parâmetros incluídos nela.



GET /test?collection_id=106400160592&collection_status=rejected&payment_id=106400160592&status=rejected&external_reference=qweqweqwe&payment_type=credit_card&merchant_order_id=29900492508&preference_id=724484980-ecb2c41d-ee0e-4cf4-9950-8ef2f07d3d82&site_id=MLC&processing_mode=aggregator&merchant_account_id=null HTTP/1.1
Host: yourwebsite.com
Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7
Accept-Encoding: gzip, deflate, br, zstd
Accept-Language: es-419,es;q=0.9
Connection: keep-alive
Referer: https://www.mercadopago.com/checkout/v1/payment/redirect/505f641c-cf04-4407-a7ad-8ca471419ee5/congrats/rejected/?preference-id=724484980-ecb2c41d-ee0e-4cf4-9950-8ef2f07d3d82&router-request-id=0edb64e3-d853-447a-bb95-4f810cbed7f7&p=f2e3a023dd16ac953e65c4ace82bb3ab
Sec-Ch-Ua: "Chromium";v="134", "Not:A-Brand";v="24", "Google Chrome";v="134"
Sec-Ch-Ua-Mobile: ?0
Sec-Ch-Ua-Platform: "macOS"
Sec-Fetch-Dest: document
Sec-Fetch-Mode: navigate
Sec-Fetch-Site: cross-site
Sec-Fetch-User: ?1
Upgrade-Insecure-Requests: 1

















Parâmetro



Descrição





payment_id



ID (identificador) do pagamento do Mercado Pago.





status



Status do pagamento. Por exemplo: approved para um pagamento aprovado ou pending para um pagamento pendente.





external_reference



Referência para sincronização com seu sistema de pagamentos.





merchant_order_id



Identificador (ID) único da ordem de pagamento criada no Mercado Pago.

Resposta para meios de pagamento offline

Os meios de pagamento offline permitem que o comprador selecione um método que exija a utilização de um ponto de pagamento físico para concluir a transação. Nesse fluxo, o Mercado Pago gera um comprovante que o comprador deve apresentar no estabelecimento para realizar o pagamento. Após essa etapa, o comprador será redirecionado para a URL definida no atributo back_urls como pending.

Nesse momento, o pagamento estará em estado pendente, já que o comprador ainda precisa efetuar o pagamento presencialmente no estabelecimento indicado.

Para pagamentos com o estado pending, sugerimos redirecionar o comprador para o seu site e fornecer orientações claras sobre como concluir o pagamento.

Assim que o pagamento for realizado no ponto físico com o comprovante gerado, o Mercado Pago será notificado, e o estado do pagamento será atualizado. Recomendamos que você configure as notificações de pagamento para que seu servidor receba essas atualizações e atualize o estado do pedido na sua base de dados.

Anterior

Criar e configurar uma preferência de pagamento

Crie a preferência de pagamento que te permitirá cobrar com o Mercado Pago.

Próximo

Adicionar o SDK ao frontend e inicializar o checkout

Configure a experiência de pagamento do cliente no seu frontend.  

Adicionar o SDK ao frontend e inicializar o checkout

Client-Side

Uma vez configurado o backend, é necessário configurar o frontend para completar a experiência de pagamento do lado do cliente. Para isso, utilize o SDK MercadoPago.js, que permite capturar pagamentos diretamente no frontend de maneira segura.

Nesta seção, você aprenderá como incluir e inicializar corretamente o SDK, e como renderizar o botão de pagamento do Mercado Pago.

Caso prefira, você pode baixar o SDK MercadoPago.js em nossas bibliotecas oficiais.

Incluir o SDK com HTML/js

Instalar o SDK utilizando React

Incluir o SDK com HTML/js

Para incluir o SDK MercadoPago.js na sua página HTML a partir de um CDN (Content Delivery Network), adicione a tag <script> antes da tag </body> no seu arquivo HTML principal, conforme mostrado no exemplo abaixo:



<!DOCTYPE html>
<html>
<head>
  <title>Minha Integração com Checkout Pro</title>
</head>
<body>

  <!-- Conteúdo da sua página -->

  <script src="https://sdk.mercadopago.com/js/v2"></script>

  <script>
    // Seu código JavaScript irá aqui
  </script>

</body>
</html>

Inicializar o checkout a partir da preferência de pagamento

Após incluir o SDK no seu frontend, é necessário inicializá-lo e, em seguida, iniciar o checkout.

Para continuar, utilize sua credencial Public Key de teste.

Se estiver desenvolvendo para outra pessoa, você poderá acessar as credenciais das aplicações que não administra. Para mais informações, consulte a seção Compartilhar credenciais.

Você também precisará utilizar o identificador da preferência de pagamento que obteve como resposta em Criar e configurar uma preferência de pagamento.

Para inicializar o SDK via CDN, insira o código a seguir dentro da tag <script>. Substitua YOUR_PUBLIC_KEY pela public_key de produção da sua aplicação e YOUR_PREFERENCE_ID pelo identificador da preferência de pagamento.



<script src="https://sdk.mercadopago.com/js/v2"></script>
<script>
  // Configure sua chave pública do Mercado Pago
  const publicKey = "YOUR_PUBLIC_KEY";
  // Configure o ID de preferência que você deve receber do seu backend
  const preferenceId = "YOUR_PREFERENCE_ID";

  // Inicializa o SDK do Mercado Pago
  const mp = new MercadoPago(publicKey);

  // Cria o botão de pagamento
  const bricksBuilder = mp.bricks();
  const renderWalletBrick = async (bricksBuilder) => {
    await bricksBuilder.create("wallet", "walletBrick_container", {
      initialization: {
        preferenceId: "<PREFERENCE_ID>",
      }
});
  };

  renderWalletBrick(bricksBuilder);
</script>

Criar um container HTML para o botão de pagamento

Client-Side

Por fim, adicione um container ao código HTML para definir a localização onde o botão de pagamento do Mercado Pago será exibido. Para criar esse container, insira o seguinte elemento no HTML da página onde o componente será renderizado:



<!-- Container para o botão de pagamento -->
<div id="walletBrick_container"></div>

Renderizar o botão de pagamento

O SDK do Mercado Pago é responsável por renderizar automaticamente o botão de pagamento dentro do elemento definido, permitindo que o comprador seja redirecionado para um formulário de compra no ambiente do Mercado Pago. Veja um exemplo na imagem abaixo:

Uma vez que você tenha finalizado a configuração no frontend, configure as Notificações para que seu servidor receba atualizações em tempo real sobre os eventos ocorridos na sua integração.