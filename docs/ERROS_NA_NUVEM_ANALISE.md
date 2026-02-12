# Análise de Erros na Nuvem (Produção)

**Data da análise:** 12/02/2026

---

## 1. SMTP Error: Could not authenticate

```
Erro ao enviar e-mail para nandofrainer@gmail.com: SMTP Error: Could not authenticate.
```

### Causa
Falha de autenticação SMTP. As credenciais em `SMTP_PASSWORD` (variável do `.env`) estão incorretas, vazias ou o servidor de hospedagem bloqueia a conexão.

### Arquivos envolvidos
- [api/config/email_config.php](api/config/email_config.php) – define `SMTP_PASSWORD` via `envValue('SMTP_PASSWORD', '')`
- [api/helpers/email_helper.php](api/helpers/email_helper.php) – usa PHPMailer com SMTP
- Hospedagem: `mail.movamazon.com.br`, porta 465 (SMTPS)

### O que conferir na hospedagem
1. **`.env`** – verificar se existe `SMTP_PASSWORD` com a senha correta do e-mail `contato@movamazon.com.br`
2. **Painel de e-mail** – confirmar se a senha do e-mail está correta
3. **Bloqueio** – alguns provedores bloqueiam SMTP externo; pode ser necessário usar relé SMTP do próprio host
4. **Porta 465** – conferir se a porta está liberada no firewall
5. **Autenticação** – alguns hosts exigem "Less secure apps" ou "App password" em contas tipo Gmail (não parece ser o caso aqui)

### Ação
- Ajustar `SMTP_PASSWORD` no `.env` da produção
- Em caso de bloqueio de SMTP, usar o relé SMTP recomendado pela hospedagem (ex.: cPanel → "Email Routing" ou configs fornecidas pelo suporte)

---

## 2. SYNC_PAYMENT_STATUS: Nenhum pagamento encontrado

```
[SYNC_PAYMENT_STATUS] Erro: Nenhum pagamento encontrado para a referência: MOVAMAZON_1770557712_29
```

### Causa
O `PaymentHelper` usa a API de busca do Mercado Pago (`/v1/payments/search?external_reference=...`). Quando não encontra pagamento com essa `external_reference`, lança essa exceção.

### Possíveis motivos
1. **Pagamento ainda não existente** – usuário criou a preferência mas ainda não pagou
2. **Ambiente diferente** – pagamento em sandbox e busca em produção (ou o contrário)
3. **Webhook não configurado** – o webhook não está registrado ou a URL em `ML_NOTIFICATION_URL` está incorreta, então o MP não notifica o sistema
4. **Formato da referência** – a `external_reference` enviada na preferência pode ser diferente (ex.: só `MOVAMAZON_29` em vez de `MOVAMAZON_1770557712_29`)

### Onde isso ocorre
- Admin faz sync manual em [api/admin/sync_payment_status.php](api/admin/sync_payment_status.php)
- O sistema recebe `inscricao_id` ou `payment_id`; se vier `external_reference` (como `MOVAMAZON_1770557712_29`), o `PaymentHelper` faz busca na API e não encontra nada

### Ações
1. **Webhook** – conferir em [Mercado Pago → Sua integração → Webhooks](https://www.mercadopago.com.br/developers/panel/app) se a URL está correta (`https://www.movamazon.com.br/api/mercadolivre/webhook.php`)
2. **`.env`** – garantir `ML_NOTIFICATION_URL` apontando para essa URL
3. **Ambiente** – confirmar se `APP_Acess_token` e `APP_Public_Key` são de produção
4. **Sync manual** – usar apenas para inscrições em que o pagamento já foi efetivado no Mercado Pago (e conferir se o `payment_id` numérico está em `pagamentos_ml`)

---

## 3. ADMIN_TERMOS_LIST: Unknown column 't.organizador_id' (resolvido)

```
Column not found: 1054 Unknown column 't.organizador_id' in 'on clause'
```

### Causa (anterior)
A tabela `termos_eventos` na produção não possui a coluna `organizador_id`, enquanto o código antigo esperava essa coluna.

### Solução aplicada
Os termos de inscrição foram adequados para serem **regras da plataforma** (não vinculadas a organizador). O código foi alterado para não depender de `organizador_id`:

- **Não execute** `fix_termos_eventos_add_organizador_id_producao.sql` – a estrutura atual da produção (sem `organizador_id`) é a correta.
- O [api/admin/termos-inscricao/list.php](api/admin/termos-inscricao/list.php) foi atualizado para fazer `SELECT` direto em `termos_eventos`, sem JOIN com organizadores.

---

## Resumo

| Erro | Tipo | Ação |
|------|------|------|
| SMTP Could not authenticate | Configuração | Verificar `SMTP_PASSWORD` no `.env` e configuração de e-mail na hospedagem |
| Nenhum pagamento encontrado | Configuração/Fluxo | Conferir webhook, ambiente (prod/sandbox) e uso correto do sync manual |
| Unknown column organizador_id | Resolvido | Código ajustado; termos são regras da plataforma. Não executar migração de organizador_id |
