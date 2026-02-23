# 07 – Roadmap por fases (execução segura)

## Fase 1 – Fundação (baixo risco)
Entregas:
- Migração SQL (tabelas novas + colunas novas)
- RBAC (papeis `assessoria_admin` e `assessor`)
- Painel `/assessoria` com:
  - dashboard simples
  - CRUD assessoria (admin)
  - equipe (admin)
  - atletas: vincular manualmente + listar

Critérios de pronto:
- Admin consegue criar assessoria e adicionar assessor
- Admin/assessor consegue ver lista de atletas vinculados

## Fase 2 – Importação por evento + programas
Entregas:
- Importar atletas por `eventos`/`inscricoes`
- CRUD programas
- Vincular atletas ao programa

Critérios de pronto:
- Com 1 clique, importar inscritos do evento e vincular na assessoria
- Criar programa do evento e adicionar atletas

## Fase 3 – Planos (geração, revisão e publicação)
Entregas:
- Fluxo completo: gerar plano (AI) -> editar -> publicar
- Amarrar `planos_treino_gerados`/`treinos` à assessoria/programa
- Atleta passa a enxergar plano publicado no painel atual

Critérios de pronto:
- Assessor publica e atleta vê e registra execução

## Fase 4 – Monitoramento e alertas
Entregas:
- Monitoramento (aderência, lista de alertas, filtros)
- Feedback do assessor no progresso
- Versionamento (nova versão do plano)

Critérios de pronto:
- Assessor visualiza rapidamente atletas sem registro e sinais de risco

## Fase 5 – Evolução e monetização (opcional)
Entregas:
- Assinatura/contrato com assessoria
- Integrações (wearables, etc.)
- Relatórios avançados por evento/período
