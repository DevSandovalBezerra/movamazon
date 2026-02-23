# 06 – API / Endpoints PHP (mapa e contratos)

Este documento lista endpoints e regras. Implementação recomendada: PHP + PDO + transações.

## Princípios
- Toda ação sensível exige verificação de papel (`usuario_papeis`)
- Toda ação deve validar ownership: assessoria do usuário (e função)
- CRUDs devem ser transacionais quando envolverem múltiplas tabelas

## Autorização (middleware)
- `require_role(['assessoria_admin','assessor'])` para /assessoria
- `assessoria_admin` obrigatório para equipe/configurações
- `assessor` pode atuar somente nos atletas atribuídos (ou regra definida pela assessoria)

## Endpoints

### Assessoria
- `POST /api/assessoria/create`
  - cria `assessorias` + vincula `assessoria_equipe` (admin)
- `GET /api/assessoria/me`
  - retorna dados da assessoria do usuário
- `PUT /api/assessoria/update`
  - atualiza perfil da assessoria (admin)

### Equipe (admin)
- `POST /api/assessoria/equipe/add`
  - vincula usuário existente como assessor/admin/suporte
- `GET /api/assessoria/equipe/list`
- `PUT /api/assessoria/equipe/status`
  - ativo/inativo + troca função

### Atletas
- `POST /api/assessoria/atletas/vincular`
  - vincula atleta manualmente
- `POST /api/assessoria/atletas/importar-evento`
  - importa atletas de `inscricoes` por `evento_id`
- `GET /api/assessoria/atletas/list`
  - lista atletas com filtros e atribuição
- `PUT /api/assessoria/atletas/atribuir-assessor`
  - define `assessor_usuario_id`
- `PUT /api/assessoria/atletas/status`
  - ativo/pausado/encerrado

### Programas
- `POST /api/assessoria/programas/create`
- `GET /api/assessoria/programas/list`
- `GET /api/assessoria/programas/:id`
- `POST /api/assessoria/programas/add-atleta`
- `PUT /api/assessoria/programas/status`

### Planos e Treinos
- `POST /api/treinos/gerar-plano`
  - cria/atualiza `planos_treino_gerados` (rascunho)
- `PUT /api/treinos/plano/publicar`
  - muda status, grava publicado_em
  - cria/ativa `treinos` e `treino_exercicios` conforme seu padrão atual
- `PUT /api/treinos/plano/nova-versao`
  - clona e incrementa `versao`
- `GET /api/treinos/atleta/semana`
  - retorna plano publicado por semana/dia

### Progresso
- `POST /api/progresso_treino/registrar`
  - atleta registra: `fonte='atleta'`
  - assessor registra feedback: `fonte='assessor'` e `registrado_por_usuario_id`
- `GET /api/progresso_treino/atleta/resumo`
- `GET /api/progresso_treino/assessoria/alertas`
  - lista alertas por regras (sem registro, PSE alto, etc.)

## Regras de negócio (mínimas)
- Atleta só vê plano publicado
- Assessor só altera atleta vinculado à sua assessoria
- Se usar carteira: assessor só edita atletas atribuídos (`assessoria_atletas.assessor_usuario_id`)
