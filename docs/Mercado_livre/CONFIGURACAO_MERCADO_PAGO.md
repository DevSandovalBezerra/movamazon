# Configuração do Mercado Pago - MovAmazon

## Variáveis de Ambiente (.env)

### Ambiente (ML_ENVIRONMENT)

A variável `ML_ENVIRONMENT` controla se o sistema está em **sandbox** (testes) ou **production** (produção).

**Valores aceitos para PRODUÇÃO:**
- `production`
- `prod`
- `on`
- `1`
- `true`

**Valores aceitos para SANDBOX (padrão):**
- `sandbox` (padrão se não especificado)
- `dev`
- `development`
- `test`
- Qualquer outro valor

### Exemplo de Configuração

#### Para SANDBOX (Testes/Desenvolvimento):
```env
ML_ENVIRONMENT=sandbox

# Tokens de SANDBOX (opcionais, mas recomendados)
ML_ACCESS_TOKEN=TEST-seu-token-aqui
ML_PUBLIC_KEY=TEST-sua-public-key-aqui

# OU usar variáveis alternativas (também aceitas):
test_Acess_token=TEST-seu-token-aqui
test_Public_Key=TEST-sua-public-key-aqui
```

#### Para PRODUÇÃO:
```env
ML_ENVIRONMENT=production

# Tokens de PRODUÇÃO (OBRIGATÓRIOS)
APP_Acess_token=APP-seu-token-producao-aqui
APP_Public_Key=APP-sua-public-key-producao-aqui

# OU usar variáveis alternativas (também aceitas):
ML_ACCESS_TOKEN_PROD=APP-seu-token-producao-aqui
ML_PUBLIC_KEY_PROD=APP-sua-public-key-producao-aqui
```

## Variáveis Aceitas pelo Sistema

O sistema aceita múltiplas variações de nomes de variáveis para maior flexibilidade:

### Access Token (Token de Acesso)

**Em PRODUÇÃO:**
1. `APP_Acess_token` (preferencial)
2. `APP_Access_token` (alternativa)
3. `ML_ACCESS_TOKEN_PROD` (alternativa)

**Em SANDBOX:**
1. `APP_Acess_token` (aceita tokens de produção em sandbox)
2. `APP_Access_token` (alternativa)
3. `ML_ACCESS_TOKEN` (padrão sandbox)
4. `test_Acess_token` (alternativa)

### Public Key (Chave Pública)

**Em PRODUÇÃO:**
1. `APP_Public_Key` (preferencial)
2. `APP_Public_Keyee` (aceita variação com dois 'e')
3. `ML_PUBLIC_KEY_PROD` (alternativa)

**Em SANDBOX:**
1. `APP_Public_Key` (aceita chaves de produção em sandbox)
2. `APP_Public_Keyee` (aceita variação com dois 'e')
3. `ML_PUBLIC_KEY` (padrão sandbox)
4. `test_Public_Key` (alternativa)

## Configuração Recomendada

### Para Ambiente de Desenvolvimento/Testes:
```env
ML_ENVIRONMENT=sandbox
ML_ACCESS_TOKEN=TEST-seu-token-sandbox
ML_PUBLIC_KEY=TEST-sua-public-key-sandbox
```

### Para Ambiente de Produção:
```env
ML_ENVIRONMENT=production
APP_Acess_token=APP-seu-token-producao
APP_Public_Key=APP-sua-public-key-producao
```

## Como Obter os Tokens

### Tokens de Sandbox (Testes):
1. Acesse: https://www.mercadopago.com.br/developers/panel
2. Crie uma aplicação de teste
3. Copie o **Access Token** (TEST-...)
4. Copie a **Public Key** (TEST-...)

### Tokens de Produção:
1. Acesse: https://www.mercadopago.com.br/developers/panel
2. Use sua aplicação de produção
3. Copie o **Access Token** (APP-...)
4. Copie a **Public Key** (APP-...)

## Validação

O sistema valida automaticamente:

- **Em PRODUÇÃO**: Tokens são **OBRIGATÓRIOS**. Se não configurados, o sistema lança exceção.
- **Em SANDBOX**: Tokens são **OPCIONAIS**. Se não configurados, o sistema apenas registra um aviso nos logs.

## Troubleshooting

### Erro: "Public key não encontrada na configuração"

**Possíveis causas:**
1. Variável `ML_ENVIRONMENT` não está definida ou está incorreta
2. Tokens não estão configurados no .env
3. Nome da variável está incorreto (verifique maiúsculas/minúsculas)
4. Arquivo .env não está sendo carregado corretamente

**Solução:**
1. Verifique se `ML_ENVIRONMENT` está definido corretamente
2. Verifique se as variáveis de token estão no .env
3. Verifique se os nomes das variáveis estão exatamente como listado acima
4. Reinicie o servidor após alterar o .env

### Erro: "AVISO: ML_ENVIRONMENT=sandbox mas tokens não configurados"

Este é apenas um **AVISO**, não um erro. O sistema continuará funcionando, mas os pagamentos podem falhar.

**Solução:**
Configure os tokens de sandbox no .env conforme mostrado acima.

## Notas Importantes

1. **NUNCA** commite o arquivo `.env` com tokens reais no Git
2. **SEMPRE** use tokens de sandbox em desenvolvimento
3. **SEMPRE** valide os tokens antes de colocar em produção
4. Os tokens de produção começam com `APP-`
5. Os tokens de sandbox começam com `TEST-`

## Exemplo Completo de .env

```env
# ============================================
# CONFIGURAÇÃO MERCADO PAGO
# ============================================

# Ambiente: sandbox (testes) ou production (produção)
ML_ENVIRONMENT=sandbox

# Tokens de SANDBOX (para testes)
ML_ACCESS_TOKEN=TEST-1234567890-123456-abcdef1234567890abcdef1234567890-123456789
ML_PUBLIC_KEY=TEST-abcdef1234567890-123456-abcdef1234567890abcdef1234567890-123456789

# Tokens de PRODUÇÃO (descomente quando for para produção)
# ML_ENVIRONMENT=production
# APP_Acess_token=APP-1234567890-123456-abcdef1234567890abcdef1234567890-123456789
# APP_Public_Key=APP-abcdef1234567890-123456-abcdef1234567890abcdef1234567890-123456789

# URLs de retorno (opcionais - usa padrões se não especificado)
# ML_AUTO_RETURN=https://movamazon.com.br/frontend/paginas/participante/pagamento-sucesso.php
# ML_NOTIFICATION_URL=https://movamazon.com.br/api/mercadolivre/webhook.php
```

## Boleto e status rejected_by_bank

O boleto na conta usa apenas o método **bolbradesco** (único habilitado; conferir em produção via `api/diagnostico/listar_payment_methods.php`).

Se o Mercado Pago/Banco retornar **rejected_by_bank** (pagamento criado com status rejeitado), o sistema:

- **Não** grava a inscrição como boleto gerado.
- Retorna mensagem amigável ao usuário e sugere **PIX ou cartão** (`use_pix: true` no JSON).
- Registra em log (`BOLETO_REJEITADO`) o `payment_id` e `status_detail` para suporte.

Possíveis causas (conforme documentação MP): validação de segurança do banco, dados incorretos (CPF/endereço), antifraude ou restrição da conta. Para saber o motivo exato em um caso concreto, consultar o **suporte do Mercado Pago**. O organizador pode verificar no painel do MP se o boleto está habilitado e sem restrições para a conta de produção.

---

**Última atualização:** 03/02/2026

