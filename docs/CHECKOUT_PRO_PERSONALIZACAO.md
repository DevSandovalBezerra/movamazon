# Personalização do Checkout Pro (redirect)

O **Checkout Pro** é a tela de pagamento **hospedada no Mercado Pago**. O usuário é redirecionado para o domínio deles; por isso a **personalização visual (cores, tema, layout)** dessa tela é **limitada** pela API. Não existe parâmetro de “tema” ou “cores” na preferência para mudar a aparência da página deles.

O que **podemos** controlar para deixar o fluxo mais “nosso”:

---

## 1. Conteúdo da preferência (o que aparece no resumo do MP)

Isso já é enviado no `create_preference.php` e define o que o comprador vê no Checkout Pro:

| Parâmetro | Uso |
|-----------|-----|
| **items** | `title`, `description`, `picture_url`, `category_id` – Nome do evento, descrição, **imagem do logo** (usamos `item_defaults.picture_url` do config). |
| **payer** | Nome, e-mail, CPF – Pré-preenchido na tela do MP. |
| **statement_descriptor** | Nome na fatura do cartão (ex.: MOVAMAZON). |
| **additional_info** | Texto extra no resumo (ex.: "Evento: Nome do Evento. Inscrição MovAmazon."). |

**Ajustes feitos no projeto:**

- `picture_url` dos itens usa a URL do config (`item_defaults.picture_url`), com domínio correto (www.movamazon.com.br).
- `additional_info` foi adicionado com o nome do evento e “Inscrição MovAmazon”.

Assim, a **tela do Mercado Pago** mostra logo, itens e texto alinhados à sua marca, dentro do que a API permite.

---

## 2. Nossa página antes do redirect (etapa 4)

Toda a parte **antes** do redirect é 100% nossa e pode ser bem personalizada:

- **Resumo da compra** com evento, modalidade, valor, taxa, total.
- **Botão “Finalizar Compra”** com o estilo do site (cor, tamanho, ícone).
- **Texto claro**: “Você será redirecionado ao Mercado Pago para pagar com cartão, PIX ou boleto.”

Ou seja: a “cara” do fluxo fica na **nossa** página; o Checkout Pro funciona como etapa final só de pagamento.

---

## 3. Limitações do Checkout Pro (página deles)

- **Não há** parâmetros na API de preferência para definir tema (claro/escuro), cores ou layout da **página hospedada** do Mercado Pago.
- A documentação de “configurar aparência do botão” refere-se ao **botão** que fica no **nosso** site (Wallet Brick), não à tela do Checkout Pro.
- Se no futuro o MP oferecer tema/cores via preferência ou painel do vendedor, será preciso conferir a documentação deles.

---

## 4. Se quiser “aparência de Brick” sem CSP

Para ter formulário de pagamento **na nossa página** (estilo Brick), é preciso que os scripts do Brick não sejam bloqueados pela CSP. Opções:

1. **Resolver CSP na hospedagem**  
   Ajustar (ou pedir à HostGator) para não enviar `Content-Security-Policy` com `nonce` na página de pagamento, ou liberar `blob:` e os domínios do Mercado Pago. Aí o Brick volta a carregar na etapa 4.

2. **Manter Checkout Pro (redirect)**  
   Sem Brick na nossa página, sem risco de CSP bloquear. A “personalização” fica no que listamos acima: preferência rica + nossa etapa 4 bem desenhada.

---

## Resumo

- **Checkout Pro** = redirect para a página do MP; personalização **visual** dessa página é limitada.
- **Melhorias possíveis:** preferência com **items** (título, descrição, **imagem**), **payer**, **statement_descriptor** e **additional_info**; e **nossa** etapa 4 com resumo e botão bem apresentados.
- No projeto isso foi aplicado em `api/inscricao/create_preference.php` (picture_url e additional_info). Para mais “cara nossa”, invista no layout e textos da etapa 4 no seu site.
