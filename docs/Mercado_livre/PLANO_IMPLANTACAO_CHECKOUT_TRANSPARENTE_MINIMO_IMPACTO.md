# Plano Completo de Implantação - Checkout Transparente (Bricks) com Impacto Mínimo

## Revisão de Completude do Fluxo (Status Real)
- O checkout transparente está **ativo no fluxo de participante**.
- O checkout transparente está **ativo no fluxo de inscrição pública**.
- O código de Bricks/PIX/Boleto foi mantido e ativado por flag e visibilidade.
- A implantação atual combina **consistência backend + experiência padronizada**.

### Evidências no código
- frontend/js/inscricao/pagamento.js: USE_CHECKOUT_PRO_REDIRECT = false.
- frontend/js/participante/pagamento-inscricao.js: window.USE_CHECKOUT_PRO_REDIRECT = false.
- frontend/paginas/participante/pagamento-inscricao.php: wrapper #janela-pagamento-mercadopago é exibido/ocultado dinamicamente conforme flag.
- frontend/paginas/inscricao/pagamento.php: wrapper #janela-pagamento-mercadopago presente e exibido/ocultado conforme flag.
- frontend/paginas/participante/pagamento-erro.php: retorno usa sync_payment_status.php (alinhado a sucesso/pendente).

### Conclusão de revisão
- A experiência de pagamento está **padronizada** nas telas de pagamento do sistema.
- O fallback para redirect permanece disponível via flag (`USE_CHECKOUT_PRO_REDIRECT`).

## 1. Objetivo
Implantar Checkout Transparente (Bricks) com máxima estabilidade operacional, aproveitando a base existente, sem mudanças de layout/CSS e com alterações de código apenas no estritamente necessário para produção.

## 2. Princípios de Implementação
- Mudança mínima e incremental.
- Sem alteração visual: layout e CSS não serão modificados.
- Compatibilidade local + produção com variáveis em uppercase.
- Sem caminhos absolutos hardcoded de domínio.
- Observabilidade e rollback em cada etapa.

## 3. Decisões Técnicas Fechadas
### 3.1 EXTERNAL_REFERENCE confiável
- Padrão: `MOVAMAZON_<INSCRICAO_ID>` (compatível com a base atual).
- Regra: imutável após criação da inscrição.
- Não usar `payment_id` como `external_reference`.
- Não usar CPF como `external_reference` (LGPD e colisão semântica).

### 3.2 PAYMENT_ID
- `payment_id` será armazenado separadamente em `pagamentos_ml.payment_id` (e `pagamentos.payment_id`, quando aplicável).
- Pode haver múltiplos `payment_id` para uma mesma inscrição (retentativas), mantendo `external_reference` fixo.

### 3.3 URL base cross-environment
- Adotar `URL_BASE` (uppercase) como base oficial da aplicação.
- Se `URL_BASE` não estiver definido, usar fallback dinâmico por host atual.
- Construir URLs de webhook, retorno e assets via `URL_BASE`.
- Remover dependência de domínio fixo (`https://www.movamazon.com.br`) dentro da integração MP.

### 3.4 Fonte da verdade de status
- Primário: webhook (`api/mercadolivre/webhook.php`).
- Secundário/fallback: sync (`api/participante/sync_payment_status.php` + `api/cron/sync_pending_payments.php`).
- Atualização de status deve ser idempotente.

## 4. Escopo de Interfaces Analisadas
## 4.1 Backend de pagamento
- `api/mercadolivre/config.php`
- `api/mercadolivre/MercadoPagoClient.php`
- `api/mercadolivre/webhook.php`
- `api/mercadolivre/payment_helper.php`
- `api/inscricao/create_preference.php`
- `api/inscricao/create_pix.php`
- `api/inscricao/create_boleto.php`
- `api/participante/sync_payment_status.php`
- `api/participante/update_payment_status.php`
- `api/cron/sync_pending_payments.php`

## 4.2 Frontend funcional (sem alteração visual)
- `frontend/js/inscricao/pagamento.js`
- `frontend/js/participante/pagamento-inscricao.js`
- `frontend/paginas/inscricao/pagamento.php`
- `frontend/paginas/inscricao/sucesso.php`
- `frontend/paginas/participante/pagamento-inscricao.php`
- `frontend/paginas/participante/pagamento-sucesso.php`
- `frontend/paginas/participante/pagamento-pendente.php`
- `frontend/paginas/participante/pagamento-erro.php`

Conclusão: a base de Bricks/PIX/Boleto já existe e pode ser reativada/reestabilizada sem refatoração visual.

## 5. Plano de Implantação (execução)
## Fase 0 - Segurança de rollout (pré-implantação)
1. Gerar backup dos arquivos-alvo de integração MP.
2. Garantir flag de fallback para Checkout Pro ativa em caso de incidente.
3. Definir janela de deploy com monitoramento de logs em tempo real.

## Fase 1 - Base URL e variáveis (mudança mínima)
1. Ajustar `api/mercadolivre/config.php` para priorizar `URL_BASE`.
2. Construir via `URL_BASE`:
- `url_notification_api`
- `back_urls_inscricao`
- `back_urls_participante`
- `item_defaults.picture_url`
3. Manter fallback dinâmico para ambiente local.

## Fase 2 - Integridade do EXTERNAL_REFERENCE
1. Em `create_pix.php` e `create_boleto.php`, parar de sobrescrever `inscricoes.external_reference` com `payment_id`.
2. Manter `external_reference` original da inscrição.
3. Persistir `payment_id` apenas em tabelas de pagamento.

## Fase 3 - Sync resiliente
1. Em `sync_payment_status.php`, consultar por `payment_id` quando existir.
2. Se não existir `payment_id`, consultar MP por `external_reference` (via helper existente) e resolver o `payment_id` real.
3. Manter idempotência e logs de origem (`webhook` vs `sync`).

## Fase 4 - Fluxo transparente sem alterar layout/CSS
1. Reutilizar JS já existente para Brick/PIX/Boleto (sem mexer em HTML/CSS).
2. Manter fallback para redirect (Checkout Pro) por flag de runtime.
3. Não alterar estrutura visual das páginas.

## Fase 5 - Webhook e reconciliação operacional
1. Validar assinatura (`ML_WEBHOOK_SECRET`) em produção.
2. Garantir que webhook responda rápido e processe assíncrono.
3. Validar job `sync_pending_payments.php` como contingência.

## Fase 6 - Validação e go-live gradual
1. Homologar local com `URL_BASE` local.
2. Homologar produção com `URL_BASE` de produção.
3. Liberar por etapas:
- Etapa A: cartão
- Etapa B: PIX
- Etapa C: boleto
4. Monitorar taxa de aprovação, erros e divergências de status por 72h.

## 6. Critérios de Aceite
- Nenhum hardcode de domínio nos endpoints críticos MP.
- `external_reference` imutável e consistente em todo ciclo da inscrição.
- PIX e boleto gerando sem quebrar rastreabilidade.
- Retorno de pagamento atualizando status via webhook/sync com idempotência.
- Zero regressão visual (layout/CSS intactos).

## 7. Riscos e Mitigações
1. Risco: webhook não disparar.
- Mitigação: sync automático e manual com busca por `external_reference`.

2. Risco: pagamento órfão por falha transiente.
- Mitigação: registro prévio de inscrição + reconciliação por referência estável.

3. Risco: regressão em produção.
- Mitigação: deploy incremental + flag de fallback para Checkout Pro.

## 8. Rollback
- Em incidente, reativar fluxo atual de redirect imediatamente via flag.
- Manter ajustes de `URL_BASE` (seguros) e pausar apenas ativação do transparente.
- Preservar dados gerados (`payment_id`, logs e status) para reconciliação posterior.

## 9. Ordem de Execução Técnica (arquivo por arquivo)
1. `api/mercadolivre/config.php`
2. `api/inscricao/create_pix.php`
3. `api/inscricao/create_boleto.php`
4. `api/participante/sync_payment_status.php`
5. validações em páginas de retorno (`pagamento-sucesso.php`, `pagamento-pendente.php`, `pagamento-erro.php`) sem alteração visual
6. testes de ponta a ponta

## 10. Regras de Não Regressão
- Não alterar HTML estrutural, classes CSS, estilos ou identidade visual.
- Não alterar contratos JSON públicos sem necessidade explícita.
- Não remover fallback existente até estabilidade comprovada.

## 11. Variáveis de Ambiente (uppercase)
- `URL_BASE`
- `APP_Acess_token` ou `APP_Access_token` ou `ML_ACCESS_TOKEN_PROD`
- `APP_Public_Key` ou `ML_PUBLIC_KEY_PROD`
- `ML_NOTIFICATION_URL` (opcional; quando ausente, derivar por `URL_BASE`)
- `ML_WEBHOOK_SECRET`
- `ML_AUTO_RETURN` (opcional; quando ausente, derivar por `URL_BASE`)

## 12. Resultado Esperado
Com este plano, o checkout transparente será implantado com baixo risco, mantendo UX atual, sem regressão visual, e com rastreabilidade robusta por `external_reference` estável.

## Checklist de Completude da Experiência (obrigatório antes de go-live)
1. Usuário consegue abrir tela de pagamento e escolher método no próprio site (quando transparente ativo).
2. Cartão via Brick conclui sem redirecionamentos quebrados.
3. PIX gera QR + copia e cola, com polling/sync e saída clara para sucesso.
4. Boleto gera linha digitável + link e mantém rastreabilidade.
5. Retornos sucesso, pendente e erro usam estratégia consistente de sincronização/atualização.
6. Nenhuma etapa depende de external_reference numérico.
7. external_reference permanece imutável em todo o ciclo.

