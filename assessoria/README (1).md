# Movamazon – Módulo Painel /assessoria (Treinadores/Assessoria)

Este pacote descreve o planejamento completo do módulo **/assessoria** no Movamazon, separado por responsabilidades/módulos.

## Estrutura dos arquivos
- `01-visao-geral.md` – objetivos, escopo e conceitos
- `02-acesso-rbac.md` – papéis, permissões e roteamento pós-login
- `03-banco-de-dados.md` – modelo e decisões de dados
- `04-migracoes-sql.md` – migração UP/DOWN (MySQL 5.7) + queries de importação
- `05-frontend-ux-painel-assessoria.md` – UI/UX e rotas do Painel /assessoria
- `06-api-endpoints-php.md` – mapa de endpoints + contratos e regras
- `07-roadmap-fases.md` – plano de execução por fases, entregáveis e riscos

## Premissas do sistema atual (confirmadas no dump)
- Já existem: `usuarios`, RBAC (`papeis`, `usuario_papeis`), `eventos`, `inscricoes`
- Treinos já existem: `anamneses`, `planos_treino_gerados`, `treinos`, `treino_exercicios`, `progresso_treino`
- Banco MySQL 5.7 (sem `ADD COLUMN IF NOT EXISTS`)

## Decisão de UI
O módulo será em área **separada**: **/assessoria** (Painel da Assessoria).
