# Checklist de Double-Check Pré-Produção (2026-02-15)

## Escopo validado
- Fluxo de pagamento em `inscricao` (público)
- Fluxo de pagamento em `participante`
- Integração Mercado Pago (config, preference, PIX, boleto, sync, webhook)
- Padronização de UX entre telas de pagamento

## Itens executados
1. Lint PHP das telas de pagamento:
- `frontend/paginas/inscricao/pagamento.php`
- `frontend/paginas/inscricao/sucesso.php`
- `frontend/paginas/participante/pagamento-inscricao.php`
- `frontend/paginas/participante/pagamento-sucesso.php`
- `frontend/paginas/participante/pagamento-pendente.php`
- `frontend/paginas/participante/pagamento-erro.php`
- Resultado: `OK` (sem erros de sintaxe)

2. Lint PHP dos endpoints críticos:
- `api/mercadolivre/config.php`
- `api/inscricao/create_preference.php`
- `api/inscricao/create_pix.php`
- `api/inscricao/create_boleto.php`
- `api/participante/sync_payment_status.php`
- `api/mercadolivre/webhook.php`
- Resultado: `OK` (sem erros de sintaxe)

3. Verificação JS (parse):
- `frontend/js/inscricao/pagamento.js`
- `frontend/js/participante/pagamento-inscricao.js`
- Resultado: `OK` (sem erros de parse)

4. Consistência de flags de checkout:
- `frontend/js/inscricao/pagamento.js`: `USE_CHECKOUT_PRO_REDIRECT = false`
- `frontend/js/participante/pagamento-inscricao.js`: `window.USE_CHECKOUT_PRO_REDIRECT = false`
- Resultado: `OK` (checkout transparente ativo nas duas telas)

5. Consistência de retorno/sync:
- `frontend/paginas/participante/pagamento-sucesso.php` usa `sync_payment_status.php`
- `frontend/paginas/participante/pagamento-pendente.php` usa `sync_payment_status.php`
- `frontend/paginas/participante/pagamento-erro.php` usa `sync_payment_status.php`
- Resultado: `OK`

6. Integridade de `external_reference`:
- `api/inscricao/create_pix.php` mantém `external_reference` estável da inscrição
- `api/inscricao/create_boleto.php` mantém `external_reference` estável da inscrição
- `api/participante/sync_payment_status.php` consulta por `payment_id` e fallback por `external_reference`
- Resultado: `OK`

## Testes de integração (HTTP/DB) - status
1. Teste HTTP local direto (`localhost/movamazon`)
- Resultado: `BLOQUEADO` (host local não estava ativo no ambiente de execução)

2. Teste com servidor temporário embutido para smoke HTTP
- Resultado: `BLOQUEADO` (política do ambiente bloqueou inicialização do processo)

3. Teste de endpoints via CLI simulando método HTTP
- Resultado: `BLOQUEADO PARCIAL`
- Motivo: conexão com banco indisponível no ambiente de execução (`Erro de conexão com banco de dados`)

## Riscos remanescentes antes de produção
- Não foi possível validar E2E real com banco e sessão (cartão/PIX/boleto até confirmação final).
- Não foi possível validar webhook em runtime com evento real do Mercado Pago neste ambiente.

## Recomendação de go-live
- `GO CONDICIONAL`
- Requisito mínimo antes de produção:
1. Executar smoke E2E em ambiente com DB ativo e sessão real:
- Cartão (approved/rejected)
- PIX (geração, polling/sync, confirmação)
- Boleto (geração, persistência, retorno)
2. Validar recebimento de webhook em endpoint público.
3. Confirmar reconciliação por `sync_payment_status.php` em fallback.

## Critério final
- Se os 3 requisitos mínimos acima passarem, liberar produção.
- Se qualquer um falhar, manter fallback por flag (`USE_CHECKOUT_PRO_REDIRECT`) até correção.

