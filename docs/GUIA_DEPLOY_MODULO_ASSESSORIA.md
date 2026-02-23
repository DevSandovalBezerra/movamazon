# Guia de Deploy - Modulo Assessoria de Corrida

Deploy do modulo Assessoria na hospedagem (apos o MovAmazon ja estar em producao).

---

## 1. Requisitos

- **PHP** 7.4+ (recomendado 8.0+) com extensoes: `pdo_mysql`, `json`, `mbstring`, `curl`, `session`
- **MySQL** 5.7+ ou MariaDB 10.2+
- **MovAmazon** ja deployado (mesmo banco, mesma sessao)

---

## 2. Banco de Dados

Executar as migrations **nesta ordem** no phpMyAdmin ou cliente MySQL:

| Ordem | Arquivo | Descricao |
|-------|---------|-----------|
| 1 | `migrations/2026-02-20_assessoria_modulo.sql` | Tabelas assessorias, equipe, atletas, programas + colunas em planos_treino_gerados, treinos, progresso_treino + papeis RBAC |
| 2 | `migrations/2026-02-20_assessoria_convites.sql` | Tabela assessoria_convites |
| 3 | `migrations/2026-02-20_assessoria_indexes.sql` | Indexes de performance (compativel MySQL 5.7) |

**Atencao:** A migration principal altera tabelas existentes (`planos_treino_gerados`, `treinos`, `progresso_treino`). Se o servidor nao tiver essas colunas ainda, o script as adiciona. Em caso de erro "column already exists", pode ser necessario comentar as linhas correspondentes ou usar `ADD COLUMN IF NOT EXISTS` (MySQL 8.0).

---

## 3. Arquivos a Enviar

Enviar **apenas** as pastas/arquivos do modulo (ou fazer upload do projeto completo e sobrescrever).

### Estrutura minima do modulo

```
api/
  assessoria/
    auth/           (login.php, register.php)
    middleware.php
    me.php
    update.php
    equipe/         (list.php, add.php, status.php)
    atletas/        (buscar.php, list.php, detalhe.php, status.php, atribuir_assessor.php)
    convites/       (enviar.php, list.php, cancelar.php, reenviar.php)
    programas/      (list.php, create.php, get.php, update.php, delete.php)
    programas/atletas/  (add.php, remove.php)
    planos/         (list.php, generate.php, publish.php)
    monitoramento/  (resumo.php, atleta.php)
    progresso/      (registrar.php, feedback.php)
  participante/
    convites/       (list.php, responder.php)

frontend/
  includes/
    header_index.php   (alterado: card Assessoria)
  paginas/
    assessoria/
      auth/login.php
      index.php
      pages/
        dashboard.php
        configuracoes.php
        equipe.php
        atletas.php
        atleta_detalhe.php
        programas.php
        programa_detalhe.php
        planos.php
        monitoramento.php
    participante/
      index.php          (alterado: rota + sidebar convites)
      convites-assessoria.php
  js/
    assessoria/
      auth.js
      atletas.js
      programas.js
      monitoramento.js

api/auth/middleware.php   (alterado: redirect assessoria_admin/assessor)
migrations/              (os 3 arquivos .sql acima)
```

---

## 4. Configuracao na Hospedagem

### 4.1 Banco e ambiente

- O modulo usa o mesmo `api/db.php` e `.env` do MovAmazon. Nada extra.
- Garantir que `.env` tem `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` corretos.

### 4.2 Geracao de planos com IA (Fase 4)

Se for usar **Gerar plano com IA** (OpenAI):

- No banco: tabela de configuracao do sistema (ex.: `configuracoes`) com chave `ai.openai.api_key` e valor = chave da API OpenAI, **ou**
- No `.env`: `OPENAI_API_KEY=sua_chave`
- Timeout de execucao PHP: aumentar para **120 segundos** (geracao pode demorar). No cPanel: PHP Options / `max_execution_time` = 120.

### 4.3 Sessao

- Sessions sao as mesmas do MovAmazon. Verificar se o dominio/cookie path esta correto para o site principal.
- Em hospedagem compartilhada, conferir se `session.save_path` e gravavel.

### 4.4 URL de acesso

- **Login assessoria:** `https://seusite.com/frontend/paginas/assessoria/auth/login.php`
- **Painel:** `https://seusite.com/frontend/paginas/assessoria/index.php`
- O card "Assessoria" na index do MovAmazon deve apontar para o login (ja alterado em `header_index.php`).

Se a hospedagem usar subpasta (ex.: `public_html/movamazon/`), os links relativos no JS (ex.: `../../../../api/assessoria`) continuam funcionando desde que a raiz do projeto seja a pasta do MovAmazon.

---

## 5. Permissoes

- Pastas de **upload** (se no futuro houver logo da assessoria): gravavel pelo PHP (ex.: 755 ou 775 conforme o servidor).
- Nenhuma pasta do modulo exige permissao especial de escrita alem do que o MovAmazon ja usa (logs, uploads gerais).

---

## 6. Checklist pos-deploy

- [ ] As 3 migrations foram executadas sem erro.
- [ ] Acessar a index do site e clicar no card **Assessoria**; deve abrir a tela de login.
- [ ] Cadastrar uma assessoria (aba Cadastro) e conferir se redireciona para o painel (sem erro de coluna `created_at`).
- [ ] Fazer login com o usuario criado e abrir Dashboard, Atletas, Programas, Monitoramento.
- [ ] (Opcional) Se for usar IA: criar programa, vincular atleta, clicar em "Gerar Plano" e verificar se nao da timeout e se a chave OpenAI e encontrada.

**Nota técnica:** A tabela `usuarios` do MovAmazon usa a coluna `data_cadastro` (não `created_at`). O cadastro da assessoria (`api/assessoria/auth/register.php`) insere em `usuarios` usando `data_cadastro`. As tabelas do módulo (`assessorias`, `assessoria_equipe`) usam `created_at` conforme as migrations.

---

## 7. Resumo de dependencias do modulo

| Dependencia | Onde |
|-------------|------|
| Banco MySQL | `api/db.php` + `.env` |
| Autenticacao RBAC | `api/auth/auth.php`, tabelas `usuarios`, `papeis`, `usuario_papeis` |
| OpenAI (geracao de planos) | `api/helpers/config_helper.php` + config ou `OPENAI_API_KEY` no .env |
| Frontend | Tailwind CSS (CDN), SweetAlert2 (CDN), Chart.js (CDN na pagina monitoramento) |

Nao e necessario instalar dependencias extras via Composer apenas para o modulo Assessoria; ele usa apenas o que o MovAmazon ja utiliza.
