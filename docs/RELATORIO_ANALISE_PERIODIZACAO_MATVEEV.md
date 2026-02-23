# Relatório de Análise — Implementação do Plano de Periodização Matveyev (MovAmazon)

**Data:** 2026-02-12  
**Objetivo:** Analisar a implementação do plano de periodização Matveyev reduzindo ao máximo o impacto nos fluxos existentes do sistema.

---

## 1. Resumo Executivo

O plano proposto adiciona periodização clássica (Matveyev) ao gerador de treinos, mantendo a UI atual. A análise da implementação atual mostra que:

- ✅ **Fluxo básico funciona**: generate.php → OpenAI → treinos + planos_treino_gerados
- ⚠️ **Gaps identificados**: sem periodização explícita, sem normalização de distância, inconsistência dia_semana_id
- 📋 **Impacto controlável**: as mudanças podem ser feitas incrementalmente com fallbacks seguros

---

## 2. Estado Atual da Implementação

### 2.1 Fluxo Atual (Validado no Código)

| Etapa | Arquivo | Status |
|-------|---------|--------|
| 1. Front chama POST | `treinos.js` → `gerarTreino()` | ✅ Funcional |
| 2. Backend gera e salva | `api/participante/treino/generate.php` | ✅ Funcional |
| 3. Front obtém dados | GET `get.php?inscricao_id=` | ✅ Funcional |
| 4. Renderização | `carregarTreino()` em treinos.js | ✅ Funcional |

### 2.2 Estrutura de Dados Atual

**Tabela `planos_treino_gerados`:**
- Campos: id, usuario_id, inscricao_id, anamnese_id, bibliografia_plano, foco_primario, duracao_treino_geral, equipamento_geral
- **Não possui**: `periodizacao_json`, `schema_version`, `metodologia`

**Tabela `treinos`:**
- Possui: parte_inicial, parte_principal, volta_calma (JSON), semana_numero, dia_semana_id
- **Não possui**: tipo_sessao, fase_macro, mesociclo_numero, rpe_alvo, pace_alvo, distancia_km, duracao_min, carga_srpe

### 2.3 Prompt e Volume Atual

- **generate.php**: Monta prompt dinâmico com anamnese, datas, distância
- **Volume**: Não há cálculo explícito (60% base + 5%/semana); a IA decide
- **Schema de saída**: Espera `{ treinos: [...], bibliografia: [...] }` — sem periodizacao/micros/mesos

---

## 3. Inconsistência Crítica: dia_semana_id

### Situação Encontrada

| Componente | Mapeamento | Fonte |
|------------|------------|-------|
| **Banco (comentário)** | 1=Domingo, 2=Segunda, ... 7=Sábado | brunor90_movamazon.sql L1866 |
| **Frontend (treinos.js)** | 1=Segunda, 2=Terça, ... 7=Domingo (ISO) | L449 |
| **generate.php (prompt)** | 1=Segunda, 2=Terça, ... 7=Domingo (ISO) | L288 |

**Risco:** Se houver dados legados com 1=Domingo, a UI exibirá treinos no dia errado.

**Recomendação do plano:** Padronizar em ISO (1=Segunda … 7=Domingo) e:
1. Corrigir o comentário no banco
2. Executar migração apenas se existir legado com 1=Domingo

---

## 4. Impacto por Área (Minimização)

### 4.1 Banco de Dados

| Mudança | Impacto | Estratégia de Minimização |
|---------|---------|----------------------------|
| Adicionar `periodizacao_json` em planos_treino_gerados | Baixo | Coluna NULL, planos antigos continuam funcionando |
| Adicionar `schema_version`, `metodologia` | Baixo | DEFAULT values, sem alterar INSERTs atuais |
| Colunas opcionais em treinos (tipo_sessao, fase_macro, etc.) | Baixo | Colunas NULL, INSERT atual não precisa preencher |
| Migração dia_semana_id | Médio | Fazer só se houver legado; executar em janela de manutenção |

### 4.2 Backend — generate.php

| Mudança | Impacto | Estratégia de Minimização |
|---------|---------|----------------------------|
| normalizarDistanciaKm() | Baixo | Usar antes de montar prompt; manter fallback atual |
| volumeSemanaKm() + volumes_por_semana | Baixo | Adicionar ao prompt; não alterar fluxo de persistência |
| Novo schema JSON (periodizacao + treinos) | Médio | Aceitar **ambos** formatos: antigo `{ treinos }` e novo `{ plano: { semanas, periodizacao } }` |
| Validação/sanitização extra | Baixo | Validar após parse; em caso de erro, tentar extrair treinos do formato antigo |
| Persistir periodizacao_json | Baixo | Salvar se disponível; NULL para planos antigos |

### 4.3 Backend — get.php

| Mudança | Impacto | Estratégia de Minimização |
|---------|---------|----------------------------|
| Retornar periodizacao_json no objeto plano | Baixo | Já retorna `ptg.*`; basta incluir a nova coluna no SELECT |
| Parse seguro de periodizacao_json | Baixo | Se inválido, retornar null; front faz fallback |

### 4.4 Frontend — treinos.js

| Mudança | Impacto | Estratégia de Minimização |
|---------|---------|----------------------------|
| DOMContentLoaded | Baixo | Envolver init em listener; verificar se já está ok |
| Null-safe em elementos | Baixo | Checagens simples antes de usar |
| Parse seguro de periodizacao_json | Baixo | try/catch; se null ou erro → ocultar blocos de periodização |
| Resumo da Semana + Visão Geral | Médio | Novos blocos HTML; **ocultos** se periodizacao_json não existir |

---

## 5. Compatibilidade com Fluxos Existentes

### 5.1 Regra de Compatibilidade

```
SE periodizacao_json existe E é válido:
  → Exibir Visão Geral + Resumo da Semana + timeline
SENÃO:
  → Comportamento atual (apenas abas de treinos por semana/dia)
```

### 5.2 Compatibilidade de Schema de Resposta da IA

| Cenário | Ação |
|---------|------|
| IA retorna formato antigo `{ treinos, bibliografia }` | Manter lógica atual; periodizacao_json = null |
| IA retorna formato novo `{ plano: { semanas, periodizacao } }` | Extrair treinos de plano.semanas[].sessoes; salvar periodizacao |
| IA retorna JSON inválido/truncado | Manter tentativas de reparo atuais; log + retorno controlado |

---

## 6. Plano de Implementação em Fases (Impacto Mínimo)

### Fase 1 — Base (sem quebrar fluxos)

1. **Migração SQL mínima**
   - `ALTER TABLE planos_treino_gerados ADD COLUMN periodizacao_json LONGTEXT NULL`
   - `ALTER TABLE planos_treino_gerados ADD COLUMN schema_version VARCHAR(50) DEFAULT 'movamazon_treino_v1'`
   - Corrigir comentário `dia_semana_id` no banco para ISO

2. **Funções auxiliares em generate.php**
   - `normalizarDistanciaKm()`
   - `volumeSemanaKm()`
   - Usar no cálculo e no prompt, sem alterar persistência ainda

3. **Prompt**
   - Incluir `volumes_por_semana` calculados no user prompt
   - Manter system prompt atual; opcionalmente adicionar instruções Matveyev sem exigir novo schema

### Fase 2 — Periodização Backend

1. **Aceitar ambos os formatos** de resposta
   - Se tiver `plano.semanas` e `plano.periodizacao` → processar novo formato
   - Caso contrário → processar `treinos` como hoje

2. **Persistência**
   - Se houver periodização no JSON → salvar em `periodizacao_json`
   - Mapear sessões para treinos como no plano (parte_inicial, parte_principal, volta_calma)

### Fase 3 — Frontend Incremental

1. **get.php** — Incluir `periodizacao_json` no SELECT
2. **treinos.js**
   - Parse defensivo de `plano.periodizacao_json`
   - Se existir: exibir Resumo da Semana e Visão Geral
   - Se não existir: manter exibição atual

### Fase 4 — Robustez e dia_semana_id

1. **Validador JSON** (validarEstruturaPlano)
2. **Migração dia_semana_id** (somente se houver legado 1=Domingo)
3. **Logs e observabilidade**

---

## 7. Pontos de Atenção

### 7.1 Risco de Regressão

| Risco | Mitigação |
|-------|-----------|
| Quebra de planos antigos | periodizacao_json NULL; front ignora blocos novos |
| IA parar de retornar formato antigo | Manter dual-parse (antigo + novo) até estabilizar |
| Migração dia_semana_id incorreta | Backup antes; testar em staging; validar com dados reais |

### 7.2 O que NÃO mudar (para reduzir impacto)

- Endpoints (generate.php, get.php) — mesmas URLs
- Estrutura de resposta de sucesso (success, plano, treinos)
- Renderização dos treinos diários (parte_inicial, parte_principal, volta_calma)
- Fluxo de inscrição → anamnese → geração de treino

---

## 8. Conclusão

A implementação do plano de periodização Matveyev pode ser feita com **impacto mínimo** nos fluxos atuais, desde que:

1. **Compatibilidade retroativa** seja mantida (aceitar formato antigo e novo)
2. **Novos elementos de UI** dependam de `periodizacao_json` e fiquem ocultos quando ausente
3. **Migrações de banco** sejam incrementais (colunas NULL)
4. **Correção dia_semana_id** seja aplicada com cautela e apenas se necessário

A ordem recomendada é: Fase 1 → Fase 2 → Fase 3 → Fase 4, com testes em cada etapa.

---

## 9. Checklist de Validação Pós-Implementação

- [ ] Planos antigos (sem periodizacao_json) continuam exibindo normalmente
- [ ] Novos planos gerados exibem Resumo da Semana e Visão Geral
- [ ] Nenhum erro de parse no front quando periodizacao_json está ausente ou inválido
- [ ] dia_semana_id exibe o dia correto (verificar Segunda=1, Domingo=7)
- [ ] Volumes por semana seguem regra 60% + 5% quando aplicável
