# Plano de Implementacao -- Modulo Assessoria de Corrida

## Status de Progresso

| Fase | Descricao | Status |
|------|-----------|--------|
| Fase 1 | Fundacao (SQL, Login, Cadastro, Middleware) | CONCLUIDA |
| Fase 2 | Painel Dashboard, Configuracoes, Equipe | CONCLUIDA |
| Fase 3 | Gestao de Atletas + Sistema de Convites | CONCLUIDA |
| Fase 4 | Programas e Planos de Treino | CONCLUIDA |
| Fase 5 | Monitoramento e Alertas | CONCLUIDA |

### Migracoes SQL pendentes de execucao
- `migrations/2026-02-20_assessoria_modulo.sql` -- tabelas e papeis RBAC
- `migrations/2026-02-20_assessoria_convites.sql` -- tabela de convites
- `migrations/2026-02-20_assessoria_indexes.sql` -- indexes de performance

**Nota:** A tabela `usuarios` do MovAmazon usa `data_cadastro` (nao `created_at`). O `register.php` da assessoria deve inserir em `usuarios` usando `data_cadastro`. As tabelas do modulo (`assessorias`, `assessoria_equipe`) usam `created_at` conforme a migration.

### Arquivos criados (Fase 5)

**API Backend:**
- `api/assessoria/monitoramento/resumo.php`
- `api/assessoria/monitoramento/atleta.php`
- `api/assessoria/progresso/registrar.php`
- `api/assessoria/progresso/feedback.php`

**Frontend:**
- `frontend/paginas/assessoria/pages/monitoramento.php`
- `frontend/js/assessoria/monitoramento.js`

### Arquivos criados (Fases 1-3)

**API Backend:**
- `api/assessoria/middleware.php`
- `api/assessoria/auth/login.php`
- `api/assessoria/auth/register.php`
- `api/assessoria/me.php`
- `api/assessoria/update.php`
- `api/assessoria/equipe/list.php`
- `api/assessoria/equipe/add.php`
- `api/assessoria/equipe/status.php`
- `api/assessoria/atletas/buscar.php`
- `api/assessoria/atletas/list.php`
- `api/assessoria/atletas/detalhe.php`
- `api/assessoria/atletas/status.php`
- `api/assessoria/atletas/atribuir_assessor.php`
- `api/assessoria/convites/enviar.php`
- `api/assessoria/convites/list.php`
- `api/assessoria/convites/cancelar.php`
- `api/assessoria/convites/reenviar.php`
- `api/participante/convites/list.php`
- `api/participante/convites/responder.php`

**Frontend:**
- `frontend/paginas/assessoria/auth/login.php`
- `frontend/paginas/assessoria/index.php`
- `frontend/paginas/assessoria/pages/dashboard.php`
- `frontend/paginas/assessoria/pages/configuracoes.php`
- `frontend/paginas/assessoria/pages/equipe.php`
- `frontend/paginas/assessoria/pages/atletas.php`
- `frontend/paginas/assessoria/pages/atleta_detalhe.php`
- `frontend/paginas/assessoria/pages/programas.php` (placeholder)
- `frontend/paginas/assessoria/pages/planos.php` (placeholder)
- `frontend/paginas/assessoria/pages/monitoramento.php` (placeholder)
- `frontend/paginas/participante/convites-assessoria.php`
- `frontend/js/assessoria/auth.js`
- `frontend/js/assessoria/atletas.js`

**Fase 4 - API Backend:**
- `api/assessoria/programas/list.php`
- `api/assessoria/programas/create.php`
- `api/assessoria/programas/get.php`
- `api/assessoria/programas/update.php`
- `api/assessoria/programas/delete.php`
- `api/assessoria/programas/atletas/add.php`
- `api/assessoria/programas/atletas/remove.php`
- `api/assessoria/planos/list.php`
- `api/assessoria/planos/generate.php`
- `api/assessoria/planos/publish.php`

**Fase 4 - Frontend:**
- `frontend/paginas/assessoria/pages/programas.php` (listagem + criacao)
- `frontend/paginas/assessoria/pages/programa_detalhe.php` (detalhe + atletas + planos + geracao IA)
- `frontend/paginas/assessoria/pages/planos.php` (visao geral de planos)
- `frontend/js/assessoria/programas.js`

**Arquivos alterados:**
- `api/auth/middleware.php` -- adicionado assessoria_admin/assessor no redirectByRole
- `frontend/includes/header_index.php` -- card Assessoria aponta para login
- `frontend/paginas/participante/index.php` -- rota convites + sidebar com badge
- `frontend/paginas/assessoria/index.php` -- adicionado rota programa-detalhe

---

## Revisao de Qualidade (Fases 1-3) -- Melhorias Identificadas

### BUG -- Ordem de execucao em convites/list.php
**Arquivo:** `api/assessoria/convites/list.php`
**Problema:** O UPDATE que marca convites expirados roda DEPOIS do SELECT. Isso faz o frontend receber dados desatualizados (convites que ja expiraram ainda aparecem como "pendente").
**Correcao:** Mover o UPDATE para ANTES do SELECT.

### BUG -- Falta de transacao em convites/reenviar.php
**Arquivo:** `api/assessoria/convites/reenviar.php`
**Problema:** O cancelamento do convite antigo e a criacao do novo convite sao feitos sem transacao. Se o INSERT falhar apos o UPDATE, o convite antigo fica cancelado sem substituto.
**Correcao:** Envolver as 3 operacoes (UPDATE cancel + SELECT email + INSERT novo) em `beginTransaction/commit/rollBack`.

### BUG -- Null-check em expira_em no responder.php
**Arquivo:** `api/participante/convites/responder.php`
**Problema:** `strtotime($convite['expira_em'])` pode falhar se `expira_em` for NULL.
**Correcao:** Adicionar verificacao `if ($convite['expira_em'] && strtotime(...))`.

### PERFORMANCE -- Indexes ausentes nas migrations
**Arquivo:** `migrations/2026-02-20_assessoria_modulo.sql`
**Problema:** Faltam indexes nas colunas `status` das tabelas `assessorias`, `assessoria_equipe`, `assessoria_atletas`, `assessoria_programas`. Essas colunas sao frequentemente filtradas em queries.
**Correcao:** Adicionar `INDEX idx_<tabela>_status (status)` em cada tabela.

**Arquivo:** `migrations/2026-02-20_assessoria_convites.sql`
**Problema:** Faltam indexes compostos para queries frequentes: `(assessoria_id, status)`, `(atleta_usuario_id, status)`, e index em `expira_em` para checagem de expiracao.
**Correcao:** Adicionar esses indexes na migration.

### UX -- Tabelas sem scroll horizontal em mobile
**Arquivos:** `frontend/paginas/assessoria/pages/equipe.php`, `atletas.php`
**Problema:** As tabelas podem estourar o layout em telas pequenas.
**Correcao:** Envolver tabelas em `<div class="overflow-x-auto">`.

### ROBUSTEZ -- Funcao getAssessoriaLoginUrl() fragil
**Arquivo:** `api/assessoria/middleware.php`
**Problema:** A funcao calcula o path relativo contando barras no `SCRIPT_NAME`, o que pode falhar dependendo de como o servidor resolve os caminhos (especialmente com `.htaccess` rewrite).
**Correcao:** Usar uma constante de base URL ou calcular a partir de `$_SERVER['DOCUMENT_ROOT']`.

### SEGURANCA -- Validacao de status em participante/convites/list.php
**Arquivo:** `api/participante/convites/list.php`
**Problema:** O parametro `$status` da query string nao e validado contra valores permitidos antes de ser usado na query.
**Correcao:** Adicionar `if (!in_array($status, ['pendente', 'todos'])) $status = 'pendente';`.

### STATUS: Todas as melhorias acima foram aplicadas e commitadas.

---

## Contexto e Decisoes de Arquitetura

### Principio Central: MovAmazon e o sistema central e porta de controle

O modulo de assessoria **nao e um sistema separado**. Ele e um modulo dentro do MovAmazon que:

- Usa a **mesma tabela `usuarios`** e o **mesmo banco de dados**
- Usa **sessao PHP** do MovAmazon (mesmo padrao de `$_SESSION`)
- Adiciona **papeis novos** (`assessoria_admin`, `assessor`) ao RBAC existente
- Tem tela de login/cadastro **propria** mas autenticando contra `usuarios` do MovAmazon

### Decisoes confirmadas

1. **Login:** Tela propria na assessoria, autentica na mesma tabela `usuarios`
2. **Cadastro:** Cria conta nova diretamente pela tela da assessoria
3. **Vinculo atleta:** Via **convite com aceite** -- assessor envia convite, atleta aceita no painel de participante
4. **Inspiracao visual:** MovHealth (abas Login/Cadastro na mesma tela), tema roxo

### Diferenca-chave: MovHealth vs Assessoria MovAmazon

- MovHealth: sistema independente, banco proprio, JWT, alunos criados pelo profissional
- Assessoria MovAmazon: modulo integrado, mesmo banco, sessao PHP, atletas ja existem e **aceitam convite**

### Arquitetura de Rotas

```
frontend/paginas/assessoria/          -- Painel do assessor/admin
frontend/paginas/assessoria/auth/     -- Login e cadastro do assessor
api/assessoria/                       -- API endpoints da assessoria
```

---

## FASE 1 -- Fundacao (Login, Cadastro, Banco de Dados)

### 1.1 Migracao SQL (Banco de Dados)

Executar o script ja pronto em `assessoria/asessoria.sql` que cria:

- **Tabelas novas:**
  - `assessorias` (identidade PF/PJ, responsavel, contatos, status)
  - `assessoria_equipe` (vincula usuarios como admin/assessor/suporte)
  - `assessoria_atletas` (vincula atletas existentes a assessoria)
  - `assessoria_programas` (programas macro por evento ou continuo)
  - `assessoria_programa_atletas` (atletas por programa)
- **Papeis RBAC:** inserir `assessoria_admin` e `assessor` na tabela `papeis`
- **Colunas novas** em `planos_treino_gerados`, `treinos`, `progresso_treino`

### 1.2 Alterar Card Assessoria na Index

Arquivo: `frontend/includes/header_index.php` (linhas 283-297)

Mudar texto do botao de "Conhecer" para "Acessar" e link para a tela de login/cadastro da assessoria.

### 1.3 Tela de Login/Cadastro do Assessor

Criar: `frontend/paginas/assessoria/auth/login.php`

Inspirado no `movhealth/views/register.php` -- tela unica com **2 abas** (Login | Cadastro):

- **Aba Login:**
  - Email + Senha
  - POST para `api/assessoria/auth/login.php`
  - Redireciona para `/assessoria/index.php?page=dashboard`
  - Valida se o usuario tem papel `assessoria_admin` ou `assessor`
- **Aba Cadastro (novo assessor):**
  - Nome Completo, Email, Senha, Confirmar Senha
  - CREF (registro profissional)
  - Tipo (PF/PJ)
  - CPF/CNPJ
  - Telefone
  - POST para `api/assessoria/auth/register.php`
  - Cria registro em `usuarios` + `assessorias` + `assessoria_equipe` (como admin)
  - Atribui papel `assessoria_admin` em `usuario_papeis`
- **Design:** tema roxo (purple) seguindo a identidade visual ja existente da assessoria

### 1.4 API de Autenticacao da Assessoria

Criar 2 endpoints:

**`api/assessoria/auth/login.php`**

- Valida credenciais na tabela `usuarios`
- Verifica se tem papel `assessoria_admin` ou `assessor` em `usuario_papeis`
- Cria sessao com dados do usuario + papel da assessoria
- Retorna JSON com sucesso/erro

**`api/assessoria/auth/register.php`**

- Valida dados do formulario
- Verifica email unico
- Cria usuario em `usuarios` (se nao existe)
- Cria assessoria em `assessorias`
- Cria vinculo em `assessoria_equipe` (funcao: admin)
- Insere papel `assessoria_admin` em `usuario_papeis`
- Tudo em transacao SQL

### 1.5 Middleware da Assessoria

Criar: `api/assessoria/middleware.php`

- `requireAssessoriaAdmin()` -- exige papel `assessoria_admin`
- `requireAssessor()` -- exige `assessoria_admin` ou `assessor`
- `getAssessoriaDoUsuario()` -- retorna o `assessoria_id` do usuario logado
- Redireciona para login da assessoria se nao autorizado

### 1.6 Atualizar Middleware Principal

Arquivo: `api/auth/middleware.php`

Adicionar `assessoria_admin` e `assessor` na funcao `redirectByRole()`.

### Entregavel da Fase 1

- Card "Assessoria" abre tela de login/cadastro
- Assessor consegue se cadastrar e logar
- Banco de dados criado com todas as tabelas
- Sessao e middleware funcionais

---

## FASE 2 -- Painel Dashboard e CRUD Basico

### 2.1 Estrutura do Painel /assessoria

```
frontend/paginas/assessoria/
  index.php              -- Router principal (?page=...)
  pages/
    dashboard.php        -- Dashboard com cards de resumo
    configuracoes.php    -- Dados da assessoria (admin)
    equipe.php           -- Gestao da equipe (admin)
```

### 2.2 Layout e Navegacao

- `frontend/includes/navbar_assessoria.php`
- `frontend/includes/header_assessoria.php`

### 2.3 Dashboard

Cards com contadores: Atletas, Programas, Treinos, Aderencia

### 2.4 Configuracoes (Perfil da Assessoria)

Formulario para editar dados da assessoria.

### 2.5 API CRUD Assessoria

```
api/assessoria/me.php
api/assessoria/update.php
api/assessoria/equipe/list.php
api/assessoria/equipe/add.php
api/assessoria/equipe/status.php
```

---

## FASE 3 -- Gestao de Atletas (com sistema de convites)

### 3.1 Tabela de Convites (`assessoria_convites`)

### 3.2 Pagina de Atletas (3 abas: Meus Atletas, Enviar Convite, Convites Pendentes)

### 3.3 Fluxo de Convite (lado do assessor)

### 3.4 Fluxo de Aceite (lado do atleta -- painel participante)

### 3.5 Visao 360 do Atleta

### 3.6 API de Atletas e Convites

---

## FASE 4 -- Programas e Planos de Treino (CONCLUIDA)

### 4.1 CRUD Programas
- Listagem com filtros por status e tipo (cards visuais)
- Criacao via modal (titulo, tipo evento/continuo, datas, objetivo, metodologia)
- Edicao de programa existente
- Exclusao inteligente (encerra se tem planos publicados, exclui se nao)
- Pagina de detalhe do programa com abas (atletas + planos)

### 4.2 Gestao de Atletas por Programa
- Adicionar atletas ao programa (checkbox de atletas vinculados a assessoria)
- Remover atleta do programa (status = encerrado)
- Validacao: so atletas ativos na assessoria podem ser adicionados

### 4.3 Geracao de Planos de Treino com IA
- Geracao via OpenAI GPT-4o (mesma infraestrutura do participante)
- Assessor configura: dias/semana, semanas, foco, metodologia, observacoes
- Semanas calculadas automaticamente a partir da data do evento
- Prompt montado com dados do atleta + anamnese + contexto do programa
- Planos salvos com assessoria_id, programa_id e criado_por_usuario_id
- Status: rascunho -> publicado -> arquivado

### 4.4 Publicacao e Gestao de Planos
- Publicar plano (torna visivel para o atleta)
- Arquivar plano (remove da visao do atleta)
- Visao geral de todos os planos da assessoria (pagina Planos)
- Listagem com filtros por status

---

## FASE 5 -- Monitoramento e Alertas (CONCLUIDA)

### 5.1 Dashboard de Monitoramento
- Cards com metricas: atletas ativos, aderencia geral, PSE medio 30d, alertas 7d
- Aderencia por atleta com barras de progresso visuais (verde/amarelo/vermelho)
- Lista de alertas recentes: PSE >= 9, mal-estar, glicemia > 250

### 5.2 Detalhe do Atleta
- Metricas individuais: treinos realizados, aderencia, PSE medio, duracao media
- Filtro por periodo: 7, 30, 60, 90 dias
- Grafico de evolucao PSE com Chart.js (pontos coloridos por faixa)
- Historico completo de progresso com indicadores de alerta

### 5.3 Feedback do Assessor
- Botao de feedback em cada registro de progresso
- Modal para adicionar feedback textual
- Feedback visivel no historico do atleta

### 5.4 Registro de Progresso pelo Assessor
- Endpoint para assessor registrar progresso em nome do atleta
- Campos: PSE, duracao, glicemia, mal-estar, sinais de alerta, observacoes
- Marcado com fonte='assessor' para diferenciar de registros do atleta
