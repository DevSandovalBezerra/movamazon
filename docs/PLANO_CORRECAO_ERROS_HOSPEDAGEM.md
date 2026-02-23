# Plano de Correção - Erros do php_errors.log (Hospedagem)

**Data da análise:** 17/02/2026

---

## Resumo da Análise

O log `php_errors.log` contém aproximadamente 1736 linhas, com erros reais e muitas linhas de debug/info. A análise identificou **5 categorias** de problemas.

---

## 1. SMTP: Could not authenticate (Prioridade ALTA)

**Ocorrências:** 12/02, 17/02 - múltiplos destinatários (nandofrainer@gmail.com, eudimaci08@yahoo.com.br, moveromundobrasil@gmail.com)

**Causa:** Falha de autenticação no servidor SMTP. O e-mail é usado para aprovação de solicitações (admin) e outros fluxos.

**Arquivos envolvidos:**
- `api/config/email_config.php` - define host `mail.movamazon.com.br`, porta 465
- `api/helpers/email_helper.php` - usa PHPMailer
- **Observação importante (legado):** `api/auth/recuperar_senha.php` ainda usa SMTP *hardcoded* antigo (`smtp.hostinger.com` / conta `movhealth@moveromundo.com.br`). Esse comportamento é legado e **não representa o padrão atual** da hospedagem.

**Padrão SMTP atual (HostGator - SSL/TLS recomendado):**
- Servidor SMTP: `mail.movamazon.com.br`
- Porta SMTP: `465` (SSL/TLS)
- Autenticação SMTP: obrigatória
- Usuário recomendado para envio sistêmico: `noreply@movamazon.com.br`
- Senha: senha da conta de e-mail no cPanel

**Contas que devem seguir o mesmo padrão SMTP:**
- `admin@movamazon.com.br`
- `contato@movamazon.com.br`
- `eudimaci@movamazon.com.br`
- `noreply@movamazon.com.br`
- `sandoval@movamazon.com.br`

**Ações:**
1. No `.env` da **produção**, definir o remetente padrão como `noreply@movamazon.com.br` e senha correta da conta (`SMTP_PASSWORD`)
2. Ajustar `api/config/email_config.php` para priorizar variáveis de ambiente (`SMTP_HOST`, `SMTP_USERNAME`, `SMTP_PORT`, `SMTP_PASSWORD`) com fallback seguro
3. Corrigir `api/auth/recuperar_senha.php` para remover SMTP hardcoded legado e reutilizar a mesma configuração central (`email_config.php` + `email_helper.php`)
4. No cPanel HostGator, validar autenticação SMTP para as contas: `admin`, `contato`, `eudimaci`, `noreply`, `sandoval` (todas em `@movamazon.com.br`)
5. Manter `465` (SSL/TLS) como padrão; usar `587` apenas como contingência operacional se houver bloqueio de rota

---

## 2. SYNC_PAYMENT_STATUS: Nenhum pagamento encontrado (Prioridade MÉDIA)

**Ocorrências:** 12/02, 16/02 - referência `MOVAMAZON_1770557712_29`

**Causa:** O `PaymentHelper` busca via API do Mercado Pago por `external_reference`. Quando o pagamento não existe na API (usuário não pagou, webhook não configurado ou ambiente errado), lança exceção.

**Arquivos envolvidos:**
- `api/admin/sync_payment_status.php` - chama `PaymentHelper->consultarStatusPagamento()`
- `api/mercadolivre/payment_helper.php` - busca por external_reference e lança exceção se não encontrar

**Ações:**
1. **Configurar webhook** no painel do Mercado Pago: `https://www.movamazon.com.br/api/mercadolivre/webhook.php`
2. Conferir `ML_NOTIFICATION_URL` no `.env` da produção
3. Confirmar se `APP_Access_token` e `APP_Public_Key` são de **produção** (não sandbox)
4. **Tratamento no código:** Em `sync_payment_status.php` ou `PaymentHelper`, quando não encontrar pagamento, retornar resposta amigável (ex: "Pagamento ainda não identificado no Mercado Pago") em vez de exceção que gera stack trace no log. Isso evita poluir o log em casos esperados (inscrição pendente, usuário ainda não pagou)

---

## 3. ADMIN_TERMOS_LIST: Unknown column 't.organizador_id' (Possivelmente RESOLVIDO)

**Ocorrência:** 12/02/2026

**Causa histórica:** Query antiga usava `organizador_id` na tabela `termos_eventos`. A tabela em produção não possui essa coluna.

**Status:** O documento `docs/ERROS_NA_NUVEM_ANALISE.md` indica que o `api/admin/termos-inscricao/list.php` foi atualizado para não depender de `organizador_id`. O código atual não referencia essa coluna.

**Ação:** Verificar na produção se a versão mais recente do `list.php` está em uso. Se o erro voltar a ocorrer, a migration `termos_plataforma_remover_organizador.sql` **não deve** ser executada em produção; a estrutura sem `organizador_id` é a correta.

---

## 4. Termos Gerais: dados_termos null / erro_termos_gerais (Prioridade MÉDIA)

**Ocorrências:** 16/02 (09:22 e 22:51)

**Log:**
```json
"response_termos_length": 2361,
"dados_termos": null,
"termos_gerais_encontrados": false,
"erro_termos_gerais": "Erro desconhecido"
```

**Causa provável:** A API `get_termos.php` retorna ~2361 bytes, mas `json_decode($response_termos, true)` retorna `null`. Possíveis motivos:
- Output (PHP notices, BOM, HTML) antes do JSON
- Resposta com redirect (HTML de login/erro) em vez de JSON
- Inconsistência de URL (movamazon.com.br vs www.movamazon.com.br)

**Arquivos envolvidos:**
- `frontend/paginas/inscricao/termos.php` - faz cURL para `get_termos.php`
- `api/inscricao/get_termos.php` - retorna JSON

**Ações:**
1. Garantir que `get_termos.php` não emite nenhum output antes do `json_encode` (sem BOM, sem `require` que imprima)
2. Verificar se a URL usada no cURL está correta e consistente (mesmo domínio com/sem www)
3. Adicionar tratamento em `termos.php`: se `json_decode` retornar null, checar `json_last_error_msg()` e logar para diagnóstico
4. Considerar chamar a API via `include`/path local em vez de cURL quando estiver no mesmo servidor (evita problemas de rede/redirect)

---

## 5. Termos Modalidade: "Tipo não é modalidade" (Prioridade BAIXA)

**Log:** `"erro_modalidade_25": "Tipo não é modalidade"`

**Causa:** O `api/inscricao/get_termos.php` **ignora** o parâmetro `modalidade_id`. Ele só suporta `evento_id` e `tipo` (inscricao, anamnese, treino). O frontend chama `get_termos.php?evento_id=8&modalidade_id=25` esperando `tipo === 'modalidade'`, mas a API nunca retorna esse tipo.

**Ações:**
1. **Opção A:** Implementar suporte a termos por modalidade no `get_termos.php` (nova lógica/coluna em `termos_eventos` ou tabela relacionada)
2. **Opção B:** Remover a busca por termos por modalidade no `termos.php` (simplificar: usar apenas termos gerais do evento) e evitar chamadas desnecessárias
3. Documentar a decisão no código

---

## 6. Regras Provisórias / Warnings (Prioridade BAIXA)

**Logs:** `[MEUS_TREINOS] ⚠️ REGRA PROVISÓRIA: Inscrição não exigida`, `[GET_TREINO] ⚠️ REGRA PROVISÓRIA: Inscrição não exigida`

**Contexto:** Regras temporárias para permitir gerar treino sem inscrição. Não são erros, mas indicam funcionalidade não finalizada.

**Ação:** Avaliar se a regra provisória deve ser mantida ou substituída pela regra definitiva (exigir inscrição). Não urgente para correção de erros.

---

## 7. Recomendações Gerais

- **Reduzir verbosidade do log em produção:** Muitas linhas são debug (eventos/list, modalidades, etc.). Considerar nível de log configurável ou desabilitar logs excessivos em prod
- **Não expor `erro_termos_gerais` ao usuário final** - manter apenas para log interno; o fluxo já usa `usando_fallback: true` quando necessário
- **Centralizar configuração de e-mail** - um único ponto (email_config.php + .env) para todos os fluxos (aprovação, recuperar senha, etc.)

---

## Ordem Sugerida de Execução

1. SMTP (bloqueia aprovações e e-mails críticos)
2. Tratamento do SYNC_PAYMENT_STATUS (evitar stack traces desnecessários)
3. Termos: diagnóstico do json_decode null e simplificação da lógica de modalidade
4. Verificação do termos-inscricao/list.php em produção
5. Revisão das regras provisórias de treino (conforme prioridade do negócio)
