# Guia de deploy – Integração Mercado Pago (SDK e MercadoPagoClient)

Este guia descreve como fazer o deploy na hospedagem das alterações da integração com Mercado Pago: uso do SDK oficial (dx-php), `statement_descriptor` e refatoração dos endpoints para o `MercadoPagoClient`.

---

## 1. Requisitos na hospedagem

| Requisito | Detalhe |
|-----------|---------|
| **PHP** | **8.2 ou superior** (exigido pelo SDK mercadopago/dx-php 3.x) |
| **Extensão PHP** | `zip` (necessária para o Composer instalar o SDK) |
| **Composer** | Disponível no servidor (SSH) ou possibilidade de enviar a pasta `vendor/` já gerada |

**Como verificar PHP na hospedagem:**

- Painel da hospedagem (ex.: “Versão do PHP”) ou
- Crie um arquivo `info.php` com `<?php phpinfo(); ?>`, acesse pela URL e confira **PHP Version** e a linha **zip** em “Loaded Configuration” (ou em “PHP Extensions”).

Se a hospedagem não tiver PHP 8.2+, o `MercadoPagoClient` continua funcionando em modo **fallback (cURL)**; nesse caso não instale o SDK e **não** envie a pasta `vendor/mercadopago/`.

---

## 2. Arquivos alterados e novos (o que enviar)

### 2.1 Arquivo novo

| Caminho | Descrição |
|---------|-----------|
| `api/mercadolivre/MercadoPagoClient.php` | Cliente central que usa o SDK quando disponível e cURL como fallback |

### 2.2 Arquivos alterados

| Caminho | Alteração |
|---------|-----------|
| `api/mercadolivre/config.php` | Inclusão de `statement_descriptor` (lido de `.env`, máx. 22 caracteres) |
| `api/inscricao/create_preference.php` | Passa a usar `MercadoPagoClient::createPreference()` |
| `api/inscricao/process_payment_preference.php` | Passa a usar `MercadoPagoClient::createPayment()` e envia `external_reference` |
| `api/inscricao/create_pix.php` | Passa a usar `MercadoPagoClient::createPayment()` |
| `api/inscricao/create_boleto.php` | Passa a usar `MercadoPagoClient::createPayment()` |
| `api/mercadolivre/webhook.php` | Consulta ao pagamento via `MercadoPagoClient::getPayment()` |
| `api/mercadolivre/get_payment_status.php` | Passa a usar `MercadoPagoClient` em vez de `MercadoLivrePayment`; inclui `require db.php` |
| `composer.json` | Dependência `"mercadopago/dx-php": "^3.4"` (versão 3.8.0 corrige avisos PHP 8.2 de dynamic property) |

### 2.3 Pasta vendor (quando for usar o SDK)

- Se na hospedagem você rodar `composer install` (recomendado): **não** é obrigatório enviar `vendor/`.
- Se **não** tiver Composer na hospedagem: envie a pasta **`vendor/`** completa (incluindo `vendor/mercadopago/` e `vendor/autoload.php`), mantendo a mesma estrutura de diretórios.

**Resumo:** Envie todos os arquivos da tabela acima. Para usar o SDK, ou rode `composer install` no servidor ou envie a pasta `vendor/` já gerada no seu ambiente.

---

## 3. Variáveis de ambiente (.env)

O `config.php` do Mercado Pago usa as variáveis abaixo. Garanta que na hospedagem elas existam no `.env` (ou nas variáveis de ambiente do painel).

| Variável | Obrigatório | Descrição / padrão |
|----------|-------------|--------------------|
| `APP_Acess_token` ou `APP_Access_token` ou `ML_ACCESS_TOKEN_PROD` | Sim | Access Token de produção do Mercado Pago |
| `APP_Public_Key` ou `ML_PUBLIC_KEY_PROD` | Sim | Chave pública de produção |
| `ML_STATEMENT_DESCRIPTOR` | Não | Nome na fatura do cartão (máx. 22 caracteres, sem acento). Padrão: `MOVAMAZON` |
| `ML_NOTIFICATION_URL` | Não | URL do webhook. Padrão: `https://www.movamazon.com.br/api/mercadolivre/webhook.php` |
| `ML_WEBHOOK_SECRET` ou `Webhook_Secret` | Recomendado | Secret do webhook para validar assinatura (x-signature) |
| `ML_ITEM_TITLE`, `ML_ITEM_DESCRIPTION`, `ML_ITEM_PICTURE_URL`, `ML_ITEM_CATEGORY` | Não | Título, descrição, imagem e categoria do item (valores padrão no config) |

**Importante:** Não altere o `.env` de outro desenvolvedor sem combinar. Na hospedagem, use o `.env` de produção já existente e apenas inclua/ajuste as variáveis acima se faltarem.

---

## 4. Passo a passo do deploy

### Opção A – Hospedagem com acesso SSH e Composer

1. Fazer backup dos arquivos atuais na hospedagem (principalmente os listados na seção 2).
2. Enviar os arquivos alterados e o novo `MercadoPagoClient.php` (e o `composer.json` atualizado), mantendo a estrutura de pastas.
3. No servidor, na **raiz do projeto** (onde está o `composer.json`):
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
4. Garantir que o `.env` de produção contém as variáveis da seção 3.
5. Testar criação de preferência, pagamento (cartão/PIX/boleto) e webhook (ver seção 5).

### Opção B – Hospedagem sem SSH / sem Composer

1. Fazer backup dos arquivos atuais na hospedagem.
2. No seu **ambiente local** (com PHP 8.2+ e Composer), na raiz do projeto:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
   Isso gera/atualiza a pasta `vendor/` com o SDK.
3. Enviar para a hospedagem:
   - Todos os arquivos listados na seção 2,
   - A pasta **`vendor/`** completa (incluindo `vendor/mercadopago/` e `vendor/autoload.php`).
4. Garantir que o `.env` de produção contém as variáveis da seção 3.
5. Testar conforme a seção 5.

### Opção C – Não instalar o SDK (apenas fallback cURL)

1. Fazer backup dos arquivos atuais.
2. Enviar **apenas** os arquivos alterados e o novo `MercadoPagoClient.php` (e o `composer.json` atualizado).
3. **Não** enviar a pasta `vendor/mercadopago/` e **não** rodar `composer install` na hospedagem.
4. O `MercadoPagoClient` detectará que o SDK não está presente e usará requisições cURL (comportamento equivalente ao anterior).
5. Garantir `.env` e testar.

---

## 5. Verificações após o deploy

1. **Checkout Pro (preferência)**  
   - Fazer um fluxo de inscrição até “Pagar com Mercado Pago”.  
   - Deve abrir a tela do MP e concluir (ou cancelar) sem erro 500.

2. **Pagamento com cartão (Payment Brick / process_payment_preference)**  
   - Concluir um pagamento de teste com cartão e verificar se o status é atualizado (inscrição e, se existir, tabela de pagamentos).

3. **PIX**  
   - Gerar PIX em uma inscrição; verificar se o QR code e o link aparecem e se o webhook atualiza o status ao pagar (em teste).

4. **Boleto**  
   - Gerar boleto; verificar se o código de barras/linha digitável e a URL de PDF aparecem.

5. **Webhook**  
   - Em **Mercado Pago → Sua integração → Webhooks**, a URL deve ser `https://seu-dominio.com.br/api/mercadolivre/webhook.php`.  
   - Conferir os logs do webhook (ex.: `logs/webhook_mp.log` ou equivalente na hospedagem) após um pagamento de teste.

6. **Consulta de status**  
   - Se a aplicação tiver tela ou endpoint que consulta status por `payment_id` ou `preference_id`, testar após um pagamento.

---

## 6. Checklist rápido

- [ ] PHP 8.2+ na hospedagem (ou aceitar uso apenas do fallback cURL)
- [ ] Extensão `zip` habilitada (se for instalar o SDK no servidor)
- [ ] Backup dos arquivos atuais
- [ ] Enviar `api/mercadolivre/MercadoPagoClient.php`
- [ ] Enviar arquivos alterados: `config.php`, `create_preference.php`, `process_payment_preference.php`, `create_pix.php`, `create_boleto.php`, `webhook.php`, `get_payment_status.php`, `composer.json`
- [ ] SDK: rodar `composer install` no servidor **ou** enviar pasta `vendor/` completa (ou deixar só fallback cURL)
- [ ] `.env` de produção com `APP_Acess_token`, `APP_Public_Key` e, se quiser, `ML_STATEMENT_DESCRIPTOR` e `ML_WEBHOOK_SECRET`
- [ ] Testar preferência, pagamento (cartão/PIX/boleto), webhook e consulta de status

---

## 7. Rollback (se precisar voltar)

1. Restaurar da backup os arquivos listados na seção 2 (e, se tiver enviado, a pasta `vendor/` antiga).
2. Se tiver rodado `composer install` no servidor, remover a pasta `vendor/mercadopago/` e rodar `composer update` (ou restaurar o `vendor/` do backup).
3. Revalidar criação de preferência e um pagamento de teste.

---

## 8. Referências no projeto

- `docs/PLANO_ADEQUACAO_MERCADOLIVRE.md` – Plano de adequação às exigências do Mercado Livre/MP  
- `docs/Mercolivre_exigencias.md` – Exigências e boas práticas  
- `docs/CONFIGURACAO_MERCADO_PAGO.md` – Configuração geral do Mercado Pago (se existir)  
- `docs/COMO_VERIFICAR_WEBHOOK.md` – Como verificar o webhook
