# 05 – Frontend/UI/UX do Painel /assessoria

## Objetivo de UX
- Separar claramente a experiência do assessor/admin em `/assessoria`
- Manter atleta no painel atual
- Permitir alternância de perfil quando necessário

## Navegação principal (/assessoria)
- `/assessoria` – Dashboard
- `/assessoria/atletas` – Lista + importação
- `/assessoria/atletas/:id` – Visão 360 do atleta
- `/assessoria/programas` – Programas
- `/assessoria/programas/:id` – Detalhe do programa
- `/assessoria/planos` – Planos gerados
- `/assessoria/planos/:id` – Editor/Revisão/Publicação
- `/assessoria/monitoramento` – Alertas, adesão e execução
- `/assessoria/equipe` – Equipe (admin)
- `/assessoria/configuracoes` – Dados da assessoria (admin)

## Tela: Dashboard
Cards recomendados:
- Atletas ativos
- Treinos planejados na semana
- Treinos registrados na semana (aderência %)
- Alertas (PSE alto, mal-estar, glicemia fora do alvo, sem registro)
- Próximos eventos (por atleta e por programa)

## Tela: Atletas
### Aba 1: Importar do Evento
- Select evento
- Filtros: modalidade, status_pagamento, período
- Tabela inscritos
- Ações:
  - Vincular à assessoria
  - Vincular e atribuir assessor
  - Vincular e adicionar ao programa do evento

### Aba 2: Buscar atleta
- busca: nome/email/documento
- vincular manualmente

### Aba 3: Convites
- gerar link/token
- lista de convites pendentes/aceitos

## Tela: Atleta (visão 360)
Seções:
- Perfil (dados básicos)
- Histórico de inscrições (eventos/inscricoes)
- Anamnese (listar, criar, editar)
- Planos (rascunhos e publicados)
- Progresso (linha do tempo + estatísticas)
- Observações internas do assessor

## Tela: Programas
- Criar programa
  - tipo: evento (linka `eventos.id`) ou contínuo
  - datas, objetivo, metodologia
- Adicionar atletas ao programa
- Lista de programas e status

## Tela: Planos (geração e publicação)
Fluxo sugerido:
1. Selecionar atleta e contexto (programa/evento/contínuo)
2. Anamnese (reusar)
3. Gerar plano (AI) -> `planos_treino_gerados` status rascunho
4. Editor por semana/dia (clonar semana, ajustar carga)
5. Publicar
   - plano status publicado, `publicado_em`
   - treinos status ativo

## Tela: Monitoramento
- Filtros: programa, atleta, período
- Tabela: treinos planejados vs registrados (aderência)
- Alertas por atleta (com severidade)
- Ações rápidas:
  - abrir atleta
  - solicitar ajuste (criar nova versão do plano)
  - registrar feedback do assessor
