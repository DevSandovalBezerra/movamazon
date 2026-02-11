# Resumo das alterações para deploy na hospedagem

Resumo do que foi alterado e o que precisa ser feito no servidor (hospedagem).

---

## 1. Banco de dados

### Migration: `payment_id` na tabela `pagamentos`

- **Arquivo:** `migrations/add_payment_id_pagamentos.sql`
- **O que faz:** Adiciona coluna `payment_id` (VARCHAR 100, NULL) e índice UNIQUE `idx_payment_id` na tabela `pagamentos` (idempotente: pode rodar mais de uma vez).
- **No servidor:** Executar o SQL no phpMyAdmin ou cliente MySQL da hospedagem (conteúdo do arquivo acima).

---

## 2. API – Mercado Libre (webhook)

### `api/mercadolivre/webhook.php`

- **SELECT da inscrição:** Passa a incluir `i.status` para não gravar NULL em `inscricoes.status`.
- **Cálculo de status da inscrição:** Regra explícita: se pago → `confirmada`; senão → mantém `inscricao.status` se for válido (pendente/confirmada/cancelada), senão `pendente`.
- **Config:** Removida carga duplicada do `config.php`.
- **Tabela `pagamentos`:** Idempotência por `payment_id`: primeiro busca por `payment_id`; se existir, UPDATE; senão busca por `inscricao_id` + status pago e atualiza setando `payment_id`; senão INSERT com `payment_id`. Se a coluna `payment_id` ainda não existir (migration não aplicada), usa o fluxo antigo.
- **E-mail:** Envio de e-mail em try/catch; falha só gera log, não quebra o webhook.
- **Retorno em arquivo:** Gravação do retorno (JSON) em `api/mercadolivre/webhook_retorno.txt` (uma linha por requisição, com data/hora).

**Enviar para a hospedagem:** o arquivo `api/mercadolivre/webhook.php` atualizado.

---

## 3. API – Treino e anamnese

### `api/participante/treino/generate.php`

- **Regra provisória (inscrição não exigida):** Verificação “treino já existe” por `usuario_id` (um treino por participante), não por `inscricao_id`. Mensagem de erro ajustada para esse caso.

### `api/participante/treino/get.php`

- **Regra provisória:** Busca do plano por `usuario_id` em vez de `inscricao_id`, para o participante ver o próprio treino quando usa inscrição provisória (ex.: 999).

### `api/participante/anamnese/create.php`

- **Exceção para inscrição 999:** Se `inscricao_id === 999`, não valida a inscrição no banco; permite salvar a anamnese (fluxo provisório).

**Enviar para a hospedagem:** os arquivos `api/participante/treino/generate.php`, `api/participante/treino/get.php` e `api/participante/anamnese/create.php` atualizados.

---

## 4. Frontend – Participante

### `frontend/paginas/participante/index.php`

- **Includes:** Passaram a usar `dirname(__DIR__, 2) . '/includes/...'` em vez de `../../includes/...` para header, navbar, footer, `mobile-menu.php` e `mobile-bottom-nav.php`, evitando “Failed to open stream” quando o diretório de trabalho no servidor é outro.

**Enviar para a hospedagem:** o arquivo `frontend/paginas/participante/index.php` atualizado.

**Conferir na hospedagem:** a pasta `frontend/includes/` existe e contém `mobile-menu.php` e `mobile-bottom-nav.php` (e os demais includes usados).

---

## 5. Diagnóstico e teste (opcional no deploy)

- **`api/mercadolivre/diagnostico_webhook.php`:** Inclui verificação de colunas críticas (`schema_check`) e recomendações (ex.: rodar migration se faltar `payment_id` em `pagamentos`). Útil para validar o ambiente após o deploy.
- **`api/mercadolivre/test_webhook_local.php`:** Ajustado para espelhar a lógica do webhook (SELECT com `i.status`, regra de status, idempotência em `pagamentos`, cenário não aprovado). Uso em ambiente local/dev.

---

## Checklist rápido para deploy

1. [ ] Executar `migrations/add_payment_id_pagamentos.sql` no banco da hospedagem.
2. [ ] Enviar/atualizar na hospedagem:
   - `api/mercadolivre/webhook.php`
   - `api/participante/treino/generate.php`
   - `api/participante/treino/get.php`
   - `api/participante/anamnese/create.php`
   - `frontend/paginas/participante/index.php`
3. [ ] Garantir que `frontend/includes/` existe no servidor com `mobile-menu.php` e `mobile-bottom-nav.php`.
4. [ ] (Opcional) Acessar `diagnostico_webhook.php` após o deploy e conferir `schema_check` e recomendações.

---

*Documento gerado com base nas alterações realizadas no projeto para deploy na hospedagem.*
