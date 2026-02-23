# Plano de Implementação — Periodização Matveyev (MovAmazon) — Anti‑Falhas + Visão Holística

Data: 2026-02-12  
Objetivo: Evoluir o gerador de treinos de corrida para periodização clássica (macrociclo → mesociclos → microciclos) com pico único, mantendo a UI atual (semanas/dias) e adicionando visão de fases/periodização, com prevenção sistemática de falhas (null, JSON inválido, variáveis antes do DOM, inconsistências de semana/dia, etc).

---

## 0) Contexto e premissas (fixas do produto)

Premissas do produto:
- Uma prova‑alvo (pico único).
- 5 dias de treino por semana (sem criar sessões “descanso”; descanso fica implícito).
- Intensidade por RPE/ritmo (pace).
- Regra de volume do produto (não delegar à IA):
  - Semana 1 = 60% da distância da prova (km/semana)
  - Progressão = +5% por microciclo (semana)
- Distância chega como string (ex.: "10" ou "10Km") → normalizar no backend.

Compatibilidade com a UI atual:
- UI agrupa por semana_numero e usa dia_semana_id 1..7.
- Cada treino diário usa 3 blocos: parte_inicial, parte_principal, volta_calma (arrays/JSON).

### 0.1 Padronização obrigatória de dia_semana_id (falha crítica detectada)
Seu banco (dump `treinos`) traz o comentário: `dia_semana_id` "1=Domingo, 2=Segunda, etc."  
Mas o seu frontend (treinos.js) usa o mapeamento **1=Segunda … 7=Domingo** (padrão ISO). Isso gera risco de exibir o dia errado.

Decisão de engenharia (para evitar erro silencioso):
- Padronizar o sistema para **ISO**: 1=Segunda … 7=Domingo (alinhado ao frontend).
- Ajustar o comentário/validação no backend para refletir isso.
- Se existir legado em que 1=Domingo, aplicar correção/migração (ver seção 2.3).

Isso elimina inconsistência UI/DB e evita “plano certo exibido no dia errado” (erro grave).


---

## 1) Arquitetura alvo (visão holística)

### 1.1 Fluxo atual (simplificado)
1. Front chama POST /api/participante/treino/generate.php com inscricao_id.
2. Backend gera plano/treinos e salva no banco.
3. Front chama GET /api/participante/treino/get.php?inscricao_id=... e renderiza por semana/dia.

### 1.2 Fluxo alvo (com periodização)
Sem quebrar o fluxo atual, acrescentar:
- Backend gera e salva:
  1) Sessões diárias (tabela de treinos) — continua igual
  2) Meta de periodização (JSON do plano) no nível do plano — novo campo periodizacao_json (ou equivalente)
- Front passa a renderizar:
  - Visão Geral (timeline dos períodos/mesociclos)
  - Resumo da Semana (microciclo) acima das abas dos dias
  - Continua renderizando os treinos diários como hoje

### 1.3 Impacto global
- Prompt: passa a exigir JSON estruturado com periodizacao + treinos.
- Backend: normaliza distância, calcula volumes por semana, valida/sanitiza JSON, persiste meta e sessões.
- Front: parseia periodizacao_json (se disponível) e exibe resumo/timeline; mantém renderização diária.
- Banco: adiciona campos (ou tabela) para armazenar periodização.

---

## 2) Modelo de dados (banco) — mínimo necessário

### 2.1 Alteração mínima (recomendada)
Adicionar no registro do plano (ex.: planos_treino_gerados ou equivalente):
- schema_version VARCHAR(50) default 'movamazon_treino_v2'
- metodologia VARCHAR(50) default 'matveyev_tradicional'
- periodizacao_json LONGTEXT NULL

Nota: os dumps SQL enviados anteriormente expiraram aqui. Para amarrar nomes exatos de tabelas/campos, reenvie treinos.sql e treino_exercicios.sql.

### 2.2 Alternativa (mais robusta)
Criar tabela plano_periodizacao:
- id, plano_id, schema_version, metodologia, json, created_at, updated_at
Vantagem: versionamento/edição futura.

---

### 2.3 Migração/compatibilidade para dia_semana_id (se houver legado)
Se você confirmar que há registros antigos com 1=Domingo, faça uma migração controlada (em janela de manutenção):
- Converter para ISO (1=Seg … 7=Dom) com regra:
  - antigo 1(Dom) → novo 7(Dom)
  - antigo 2(Seg) → novo 1(Seg)
  - …
  - antigo 7(Sáb) → novo 6(Sáb)

SQL (exemplo):
```sql
UPDATE treinos
SET dia_semana_id = CASE dia_semana_id
  WHEN 1 THEN 7
  WHEN 2 THEN 1
  WHEN 3 THEN 2
  WHEN 4 THEN 3
  WHEN 5 THEN 4
  WHEN 6 THEN 5
  WHEN 7 THEN 6
  ELSE dia_semana_id
END
WHERE dia_semana_id BETWEEN 1 AND 7;
```

Se não houver legado, apenas alinhe o comentário/validação e prossiga.

## 3) Normalização e regras do produto (anti‑falhas)

### 3.1 Normalizar distância (string → km float)
Aceitar "10", "10km", "10 Km", "21.1km".
PHP:

```php
function normalizarDistanciaKm(string $raw): float {
    $s = strtolower(trim($raw));
    $s = str_replace(['kms','km',' '], '', $s);
    $s = str_replace(',', '.', $s);
    $v = floatval($s);
    return $v > 0 ? $v : 0.0;
}
```

Falhas prevenidas:
- distância virando 0
- separador decimal
- espaços/sufixos

### 3.2 Calcular volume semanal (não depender da IA)
```php
function volumeSemanaKm(float $distanciaKm, int $semanaNumero): float {
    $base = 0.60 * $distanciaKm;
    $v = $base * pow(1.05, max(0, $semanaNumero - 1));
    return round($v, 1);
}
```

### 3.3 Distribuição padrão (5 sessões/semana)
1) Leve + técnica (RPE 2-4)  
2) Qualidade (tempo/fartlek/intervalado) (RPE 6-9 conforme fase)  
3) Leve + força/mobilidade (RPE 2-4)  
4) Moderado progressivo (RPE 4-6)  
5) Longo (RPE 3-5) 25-35% do volume semanal

---

## 4) JSON oficial do produto (schema de armazenamento)

### 4.1 Schema alvo (movamazon_treino_v2)
Salvar em periodizacao_json no plano e retornar via API.

Campos:
- schema_version
- metodologia
- periodizacao:
  - macrociclo (inicio, fim, prova)
  - periodos (Preparatório Geral, Preparatório Especial, Competitivo, Transição)
  - mesociclos (blocos de semanas com foco)
  - microciclos (resumo por semana: volume, foco, distribuição intensidade)
- treinos (sessões diárias; também persistidas na tabela de treinos)
- bibliografia

### 4.2 Política de NULL (anti‑falhas)
Regra:
- No front, nada pode ficar indefinido.
- Se desconhecido: null no JSON e render '-' na UI.

Pode ser null:
- pace_confortavel, tempo_referencia, observacoes, cadencia, velocidade, distancia (quando não aplicável)

Nunca pode ser null:
- schema_version
- periodizacao.microciclos[].semana_numero
- treinos[].semana_numero
- treinos[].dia_semana_id
- treinos[].parte_inicial / parte_principal / volta_calma (sempre arrays)

---

## 5) Prompt definitivo (OpenAI) — com travas anti‑alucinação

### 5.1 System prompt (fixo)
- Especialista em corrida + periodização Matveyev.
- Responde apenas JSON válido.
- Intensidade por RPE/pace.
- 5 sessões/semana.
- Não criar sessões descanso.
- Arrays sempre presentes.
- Dados ausentes: null + pendencias.

### 5.2 User prompt (dinâmico) — com volumes já calculados
Para reduzir erro, o backend envia:
- distancia_km_normalizada
- semanas_total
- volumes_por_semana (array calculado em PHP)

Exemplo:
```text
Volumes por semana (km) já calculados:
Semana 1: 6.0
Semana 2: 6.3
Semana 3: 6.6
Use exatamente esses volumes ao distribuir as sessões.
```

---

## 6) Backend — generate.php (plano de implementação)

### 6.1 Ordem segura (checklist)
1) Validar inscricao_id
2) Buscar evento/prova (distância string e data)
3) Normalizar distância (float km)
4) Calcular semanas_total (hoje → data_evento)
5) Calcular volumes_por_semana
6) Montar prompt (system+user)
7) Chamar OpenAI
8) Validar JSON:
   - parse ok
   - contém periodizacao e treinos
   - treinos.length == semanas_total * 5
   - cada treino tem parte_* como arrays
9) Sanitizar:
   - converter strings vazias em null onde fizer sentido
   - garantir arrays não nulos
10) Persistir em transação:
   - salvar/atualizar plano (com periodizacao_json)
   - inserir/atualizar treinos (sessões diárias)
11) Retornar sucesso e plano_id

### 6.2 Validador rígido
Implementar validarEstruturaPlano($json, $semanasTotal): array erros.
Se houver erros: log + retorno controlado.

### 6.3 Persistência (anti‑duplicação)
Definir uma política:
- sobrescrever plano existente da inscrição, ou versionar.
Escolher 1 e documentar.

---

## 7) Backend — get.php

### 7.1 Retorno unificado
Retornar:
- plano (inclui periodizacao_json se existir)
- treinos (sessões do dia)

### 7.2 Segurança de parse
Se periodizacao_json inválido:
- retornar null e logar (front tolera).

---

## 8) Frontend — treinos.js (anti‑falhas e melhorias)

### 8.1 DOM pronto
Nunca acessar elementos antes do DOM:
```js
document.addEventListener('DOMContentLoaded', async () => {
  // chamar carregarInscricoesTreinos() / carregarTreino()
});
```

### 8.2 Null‑safe no DOM
```js
const el = document.getElementById('x');
if (!el) return;
```

### 8.3 Parse seguro
- periodizacao_json: try/catch
- parte_*: se não for array, virar []

### 8.4 UI: Visão Geral + Resumo da Semana
Se periodizacao_json existir:
- mostrar fase atual (com base na semana selecionada)
- mostrar volume_km e foco da semana
- criar “Visão Geral” com timeline de periodos

Se não existir:
- ocultar blocos (fallback).

---

## 9) Estratégia de prevenção de falhas (lista objetiva)

Dados (null/undefined):
- Backend garante arrays; front exibe '-' nos textos.

JSON:
- validar antes de salvar; erro controlado ao usuário; log do bruto.

DOM:
- init após DOMContentLoaded; checar elementos antes de usar.

Consistência:
- 5 sessões/semana; dia_semana_id apenas nos dias disponíveis; sem duplicidade por semana.

Performance:
- periodizacao_json pode ser grande; UI por abas já mitiga.

---

## 10) Plano de execução no Cursor (tarefas em ordem)

Sprint 1 — Base sólida
1) Normalização de distância
2) Semanas_total + volumes_por_semana
3) Prompt com volumes travados
4) Validação/sanitização
5) Persistir periodizacao_json no plano
6) Manter treinos diários como hoje

Sprint 2 — UI/UX
1) get.php retornar periodizacao_json
2) treinos.js render Resumo da Semana
3) Implementar Visão Geral (timeline)
4) Fallback total sem periodização

Sprint 3 — Robustez
1) Logs e observabilidade
2) Versionamento schema
3) Regeneração por semana (opcional)

---

## 11) Entregáveis

- Prompt definitivo (system + user template)
- Funções PHP: normalizar distância, volumeSemanaKm, validador JSON, sanitizador
- Migração SQL
- Ajustes treinos.js: init DOM, null-safe, parse seguro, render de resumo/timeline

---

## 12) Observação sobre arquivos expirados
Os SQL anteriores expiraram no ambiente. Para eu entregar a migração e queries já com os nomes reais das suas tabelas/campos, reenvie:
- treinos.sql
- treino_exercicios.sql

# 10) Versão definitiva do prompt (OpenAI) — Matveev + Periodização + Antifalhas

## 10.1 Objetivo
Gerar um plano completo periodizado (macro/meso/microciclo), focado na corrida ativa do atleta (5 km / 10 km / meia / maratona), com 5 sessões semanais, controlando intensidade por RPE e/ou ritmo (pace) e com regra de volume: base = 60% do volume-alvo de pico e progressão de +5% por microciclo (até o pico), com semanas de descarga planejadas.

A periodização clássica atribuída a Lev P. Matveyev/Matveev organiza o treinamento em macro/meso/microciclos e em fases preparatória, competitiva e transição, com variação planejada de volume e intensidade (ver referências web).

## 10.2 Requisitos de saída (contrato)
O modelo DEVE responder com JSON válido (UTF-8) e somente JSON.  
Nada de markdown, nada de texto antes/depois.

### Regras antifalha (para o LLM)
- Nunca retornar null em campos definidos: usar "", 0, [] ou {}.
- Campo distancia pode vir como string livre ("10", "10k", "10Km"); porém também retornar distancia_km (number) calculada (fallback 0).
- Arrays parte_inicial, parte_principal, volta_calma sempre existem (mesmo vazios).
- Cada sessão tem dia_semana_id 1..7 e semana_numero 1..N.
- Se não houver info suficiente (ex.: pace), usar rpe_alvo + descrição de sensação.

## 10.3 Prompt Base (SYSTEM) — substituir ai.prompt_treino_base
Use isto no config ai.prompt_treino_base (SYSTEM):

```text
Você é um especialista em treinamento de corrida e periodização clássica (macro/meso/microciclo), com base no modelo tradicional atribuído a Lev P. Matveyev (fases preparatória, competitiva e transição) e em princípios modernos de segurança e prevenção de lesões.
Sua tarefa é gerar um plano periodizado, progressivo e seguro para a corrida do atleta, com 5 sessões por semana, respeitando anamnese, limitações e tempo até o evento.

REGRAS DE SAÍDA (OBRIGATÓRIO):
1) Responda SOMENTE com JSON válido (sem markdown, sem comentários).
2) Não use null. Use strings vazias, 0, arrays vazios ou objetos vazios.
3) Sempre inclua: plano.periodizacao, plano.semanas[], e plano.semanas[].sessoes[].
4) Cada sessão deve conter: parte_inicial[], parte_principal[], volta_calma[] (arrays).
5) Campo distancia pode ser string livre, mas também gere distancia_km (number).
6) Use RPE como controle principal; quando houver pace, informe pace_alvo em formato texto.
7) 2 dias sem corrida: NÃO criar sessão de descanso. Descanso fica implícito (UI não exibe esses dias).
8) Volume semanal: base = 60% do volume de pico da prova; aumentar 5% por microciclo até o pico; programar semanas de descarga conforme necessário.
9) Não invente doenças; se algo não estiver na anamnese, ignore.
```

## 10.4 Prompt de usuário (USER) — substituir montagem atual em generate.php
Ao invés de concatenar muitas linhas soltas, gere um bloco único com “contrato + dados”. Exemplo:

```text
DADOS DO ATLETA E PROVA:
- inscricao_id: {{inscricao_id}}
- usuario_id: {{usuario_id}}
- nome: {{nome_usuario}}
- prova_distancia_texto: "{{distancia_evento}}" (pode vir "10", "10Km", "Meia", "Maratona")
- data_evento: "{{evento_data}}" (YYYY-MM-DD) | semanas_ate_evento: {{semanas_ate_evento}}
- alvo: "{{alvo}}" (ex: completar / performance / saúde)
- frequencia: 5 treinos/semana
- metodo_intensidade: RPE + ritmo (quando possível)
- regra_volume: base 60% do pico + 5% por microciclo

ANAMNESE (use para restrições e segurança):
- peso_kg: {{peso}}
- altura_cm: {{altura}}
- imc: {{imc}}
- fc_maxima_bpm: {{fc_maxima}}
- vo2max: {{vo2_max}}
- historico_lesoes: "{{lesoes}}"
- disponibilidade: "{{disponibilidade}}"
- experiencia_corrida: "{{experiencia}}"
- observacoes: "{{observacoes}}"

SAÍDA (JSON):
Gere um objeto JSON no formato do schema abaixo.
```

## 10.5 Schema JSON (ideal para salvar no banco e renderizar na UI)
Este é o “master JSON” do plano (recomendado salvar em plano_treino_gerado.plano_json ou equivalente) e também permite derivar a tabela treinos (1 linha por sessão).

```json
{
  "schema_version": "movamazon.treino.v2",
  "plano": {
    "id_externo": "",
    "foco_primario": "Preparação para Corrida",
    "alvo": "",
    "prova": {
      "distancia_texto": "",
      "distancia_km": 0,
      "data_evento": "",
      "semanas_ate_evento": 0
    },
    "periodizacao": {
      "autor_referencia": "Matveev",
      "macro": {
        "nome": "Macrociclo até a prova",
        "fases": [
          { "fase": "preparatoria", "semanas": [1, 2, 3], "objetivo": "Base aeróbia + técnica + força geral" },
          { "fase": "competitiva", "semanas": [4, 5], "objetivo": "Especificidade + ritmo de prova + pico" },
          { "fase": "transicao", "semanas": [6], "objetivo": "Polimento/taper + recuperação" }
        ]
      },
      "mesos": [
        {
          "mesociclo_numero": 1,
          "nome": "Base",
          "semanas": [1,2,3],
          "foco": "volume e técnica",
          "progressao": "crescente com descarga programada"
        }
      ],
      "micros": [
        {
          "microciclo_numero": 1,
          "semana_numero": 1,
          "tipo": "acumulacao",
          "volume_alvo_km": 0,
          "intensidade_foco": "baixa-moderada",
          "observacoes": ""
        }
      ]
    },
    "metas_semanais": {
      "volume_base_pct_pico": 0.60,
      "incremento_por_micro_pct": 0.05,
      "criterio_descarga": "a cada 3-4 semanas reduzir volume 15-25% mantendo alguma intensidade"
    },
    "bibliografia": [
      { "titulo": "Periodization of Sports Training", "autor": "L.P. Matveyev/Matveev", "tipo": "referencia_classica", "url": "" }
    ],
    "semanas": [
      {
        "semana_numero": 1,
        "volume_planejado_km": 0,
        "carga_planejada_sRPE": 0,
        "objetivo_semana": "",
        "sessoes": [
          {
            "treino_uid": "S1-D2",
            "semana_numero": 1,
            "dia_semana_id": 2,
            "tipo_sessao": "corrida_leve|intervalado|tempo|longao|forca|mobilidade",
            "nome": "",
            "descricao": "",
            "rpe_alvo": 0,
            "pace_alvo": "",
            "duracao_min": 0,
            "distancia": "",
            "distancia_km": 0,
            "volume_sessao_km": 0,
            "fc_alvo": "",
            "observacoes": "",
            "parte_inicial": [
              {
                "nome_item": "",
                "detalhes_item": "",
                "tempo_execucao": "",
                "fc_alvo": "",
                "rpe_alvo": 0
              }
            ],
            "parte_principal": [
              {
                "nome_item": "",
                "detalhes_item": "",
                "tempo_execucao": "",
                "distancia": "",
                "distancia_km": 0,
                "velocidade": "",
                "pace_alvo": "",
                "rpe_alvo": 0,
                "fc_alvo": "",
                "tempo_recuperacao": "",
                "tipo_recuperacao": "",
                "series": "",
                "repeticoes": "",
                "carga": "",
                "observacoes": ""
              }
            ],
            "volta_calma": [
              {
                "nome_item": "",
                "detalhes_item": "",
                "tempo_execucao": ""
              }
            ]
          }
        ]
      }
    ]
  }
}
```

### Mapeamento direto para a tabela treinos
Como sua UI já aceita JSON nas colunas parte_inicial/parte_principal/volta_calma e agrupa por semana_numero, o mapeamento fica simples (sua UI agrupa e faz parse defensivo)【184:5†treinos.js†L1-L19】【184:6†treinos.js†L14-L63】:

- treinos.nome <- sessoes[].nome
- treinos.descricao <- sessoes[].descricao
- treinos.dia_semana_id <- sessoes[].dia_semana_id
- treinos.semana_numero <- sessoes[].semana_numero
- treinos.parte_inicial <- json_encode(sessoes[].parte_inicial)
- treinos.parte_principal <- json_encode(sessoes[].parte_principal)
- treinos.volta_calma <- json_encode(sessoes[].volta_calma)
- treinos.volume_total <- usar duracao_min e/ou distancia (string)
- treinos.observacoes <- consolidar observacoes + notas de periodização (fase/meso/micro)

## 10.6 Referências web (Matveev/Matveyev)
Matveev/Matveyev é associado ao modelo clássico de periodização com organização em ciclos e fases (preparatória, competitiva e transição).  citeturn0search0turn0search1turn0search2turn0search3

# 11) Migração de banco (mínimo impacto, sem quebrar a UI)

## 11.1 Acrescentar metadados de periodização na tabela treinos (recomendado)
Sem mudar sua renderização, acrescente colunas para permitir filtros, auditoria e relatórios:

- tipo_sessao VARCHAR(30) NULL
- fase_macro VARCHAR(20) NULL  (preparatoria|competitiva|transicao)
- mesociclo_numero INT NULL
- microciclo_numero INT NULL
- rpe_alvo TINYINT NULL
- pace_alvo VARCHAR(50) NULL
- distancia_km DECIMAL(6,2) NULL
- duracao_min INT NULL
- carga_srpe INT NULL  (duracao_min * rpe_alvo)

```sql
ALTER TABLE treinos
  ADD COLUMN tipo_sessao VARCHAR(30) NULL AFTER nivel_dificuldade,
  ADD COLUMN fase_macro VARCHAR(20) NULL AFTER tipo_sessao,
  ADD COLUMN mesociclo_numero INT NULL AFTER fase_macro,
  ADD COLUMN microciclo_numero INT NULL AFTER mesociclo_numero,
  ADD COLUMN rpe_alvo TINYINT NULL AFTER microciclo_numero,
  ADD COLUMN pace_alvo VARCHAR(50) NULL AFTER rpe_alvo,
  ADD COLUMN distancia_km DECIMAL(6,2) NULL AFTER pace_alvo,
  ADD COLUMN duracao_min INT NULL AFTER distancia_km,
  ADD COLUMN carga_srpe INT NULL AFTER duracao_min;
```

## 11.2 Salvar o master JSON do plano (recomendado)
Se você já tem uma tabela de plano_treino_gerado, acrescente:

- schema_version VARCHAR(50) NOT NULL DEFAULT 'movamazon.treino.v2'
- plano_json LONGTEXT NOT NULL  (JSON master)

Isso permite:
- Re-renderizar UI sem depender da tabela treinos (futuro).
- Recalcular volumes, cargas, gráficos.

# 12) Ajustes no generate.php (antifalhas, transações, validação JSON)

## 12.1 Estratégia antifalha (2 passos)
1) Chamada OpenAI -> tentar json_decode.
2) Se falhar, chamar “modo reparo” com prompt curto: “Corrija para JSON válido conforme schema, sem alterar conteúdo”.

Também aplicar:
- PDO::beginTransaction() -> salvar plano + treinos -> commit(); qualquer erro -> rollback().

## 12.2 Checklist de prevenção de bugs (Cursor-ready)
- Validar inscricao_id sempre.
- Garantir que evento_data exista e esteja em YYYY-MM-DD.
- Garantir que treino.parte_* sempre sejam arrays (server-side) antes de salvar.
- No JS, sempre checar document.getElementById(...) antes de usar (evitar null DOM)【184:0†treinos.js†L1-L33】.
- Tratar distancia string -> extrair número (regex) -> distancia_km.

# 13) Modelo “definitivo” para exercícios por sessão (compatível com sua UI)
Sua UI busca campos como nome_item, detalhes_item, tempo_execucao, tempo_recuperacao, carga, etc. Portanto o schema acima mantém exatamente esses nomes para não quebrar【184:13†treinos.js†L52-L63】.