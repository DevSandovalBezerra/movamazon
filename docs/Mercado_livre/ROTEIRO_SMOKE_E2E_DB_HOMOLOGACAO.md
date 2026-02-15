# Roteiro Operacional - Smoke E2E com DB (Homologação)

Data de referência: 15/02/2026

## 1. Objetivo
Validar ponta a ponta, com banco ativo, que os fluxos de pagamento (Cartão, PIX, Boleto) funcionam e atualizam status corretamente.

## 2. Ambiente
Use um único ambiente por execução:
1. Produção: `https://www.movamazon.com.br`
2. Local: `http://localhost/movamazon` (ajuste se seu path local for diferente)

## 3. Pré-check técnico (obrigatório)
1. Confirmar `.env` com:
- `URL_BASE`
- `APP_Acess_token` (ou `APP_Access_token`/`ML_ACCESS_TOKEN_PROD`)
- `APP_Public_Key` (ou `ML_PUBLIC_KEY_PROD`)
- `ML_WEBHOOK_SECRET`
2. Confirmar banco conectado.
3. Confirmar usuário de teste com CPF válido.
4. Confirmar evento ativo com inscrição possível.

## 4. SQL de apoio (copiar e usar durante o teste)
Defina o ID da inscrição em teste:

```sql
SET @INSCRICAO_ID := 0;
```

Snapshot inicial da inscrição:

```sql
SELECT 
  i.id,
  i.usuario_id,
  i.evento_id,
  i.status,
  i.status_pagamento,
  i.forma_pagamento,
  i.valor_total,
  i.external_reference,
  i.preference_id,
  i.data_pagamento,
  i.data_expiracao_pagamento
FROM inscricoes i
WHERE i.id = @INSCRICAO_ID;
```

Histórico em `pagamentos_ml`:

```sql
SELECT
  pm.id,
  pm.inscricao_id,
  pm.preference_id,
  pm.payment_id,
  pm.status,
  pm.valor_pago,
  pm.metodo_pagamento,
  pm.data_criacao,
  pm.data_atualizacao
FROM pagamentos_ml pm
WHERE pm.inscricao_id = @INSCRICAO_ID
ORDER BY pm.id DESC;
```

Histórico em `pagamentos`:

```sql
SELECT
  p.id,
  p.inscricao_id,
  p.forma_pagamento,
  p.status,
  p.valor_pago,
  p.data_pagamento
FROM pagamentos p
WHERE p.inscricao_id = @INSCRICAO_ID
ORDER BY p.id DESC;
```

Verificar external_reference duplicado indevido:

```sql
SELECT external_reference, COUNT(*) qtd
FROM inscricoes
WHERE external_reference IS NOT NULL AND external_reference <> ''
GROUP BY external_reference
HAVING COUNT(*) > 1;
```

Conferir se `external_reference` virou número de payment indevidamente:

```sql
SELECT id, external_reference
FROM inscricoes
WHERE id = @INSCRICAO_ID
  AND external_reference REGEXP '^[0-9]+$';
```

## 5. Caso A - Cartão aprovado
1. Abrir tela de pagamento:
- Inscrição pública (`/inscricao`, etapa pagamento)
- Participante (`/frontend/paginas/participante/pagamento-inscricao.php?inscricao_id=...`)
2. Validar UX:
- Formulário de cartão visível na página.
- Opções PIX e Boleto visíveis no mesmo bloco de pagamento.
3. Concluir pagamento com cartão de teste aprovado.
4. Validar retorno:
- Página de sucesso carregada sem erro.
5. Validar banco:
- `inscricoes.status_pagamento = 'pago'`
- `inscricoes.status = 'confirmada'`
- `pagamentos_ml.payment_id` preenchido
- `external_reference` permanece estável (não trocar para número do pagamento)

## 6. Caso B - Cartão rejeitado
1. Repetir fluxo com cartão de teste rejeitado.
2. Validar retorno:
- Página de erro/pagamento não aprovado.
3. Validar banco:
- Não marcar `inscricoes.status_pagamento` como `pago`.
- Registrar tentativa em `pagamentos_ml` com status coerente (`rejeitado`/`pendente` conforme retorno).

## 7. Caso C - PIX
1. Clicar em `Pagar com PIX`.
2. Validar:
- QR Code exibido.
- Código copia-e-cola exibido.
3. Simular/quitar pagamento PIX.
4. Validar atualização:
- Via webhook ou via sync.
5. Validar banco:
- `inscricoes.forma_pagamento = 'pix'` (quando aplicável)
- `inscricoes.status_pagamento` convergindo para `pago`
- `pagamentos_ml.payment_id` preenchido
- `external_reference` imutável

## 8. Caso D - Boleto
1. Clicar em `Pagar com Boleto`.
2. Validar:
- Link/linha digitável disponível.
- Data de expiração preenchida (quando retornada pelo MP).
3. Validar banco:
- `inscricoes.forma_pagamento = 'boleto'` (quando aplicável)
- status inicial `pendente/processando`
- sem quebra de rastreabilidade (`external_reference` estável)

## 9. Caso E - Retorno e reconciliação
1. Validar páginas:
- sucesso
- pendente
- erro
2. Confirmar que todas fazem sync coerente quando necessário.
3. Teste manual do sync:
- abrir endpoint com sessão válida:
`/api/participante/sync_payment_status.php?inscricao_id=<ID>`
4. Esperado:
- resposta JSON `success=true` sem erro fatal.
- status no banco atualizado conforme MP.

## 10. Webhook (obrigatório)
1. Confirmar URL de webhook configurada no MP para o ambiente.
2. Executar 1 pagamento real de teste (preferência nova).
3. Confirmar evidência:
- log de webhook sem erro
- atualização de `inscricoes` e `pagamentos_ml`

## 11. Critérios de aprovação (Go/No-Go)
`GO` somente se todos abaixo forem verdadeiros:
1. Cartão aprovado: OK
2. Cartão rejeitado: OK
3. PIX: geração + confirmação: OK
4. Boleto: geração: OK
5. Retornos (sucesso/pendente/erro): OK
6. `external_reference` não muda para `payment_id`: OK
7. webhook + fallback sync funcionando: OK

`NO-GO` se qualquer item falhar.

## 12. Plano de contingência rápido
Se houver incidente:
1. Ativar fallback de redirect por flag:
- `frontend/js/inscricao/pagamento.js` -> `USE_CHECKOUT_PRO_REDIRECT = true`
- `frontend/js/participante/pagamento-inscricao.js` -> `window.USE_CHECKOUT_PRO_REDIRECT = true`
2. Repetir somente casos críticos (Cartão aprovado + Retorno sucesso + Sync).
3. Corrigir e revalidar antes de nova tentativa de go-live.

## 13. Evidências que devem ser anexadas ao deploy
1. Prints da tela de pagamento (inscrição e participante).
2. Print do sucesso/pendente/erro.
3. JSON de resposta do sync para 1 inscrição por método.
4. Resultado das queries SQL (antes/depois) para Cartão, PIX e Boleto.
5. Trecho de log do webhook processando um pagamento de teste.

