# 03 – Banco de dados (modelo e decisões)

## Objetivos de banco
- Não quebrar o legado
- Reutilizar treinos existentes
- Acrescentar ownership (assessoria), contexto (programa), status e versionamento

## Tabelas novas

### `assessorias`
- Identidade PF/PJ e responsável principal (`usuarios.id`)

Campos:
- `id` (PK)
- `tipo` ENUM('PF','PJ')
- `nome_fantasia`
- `razao_social` (PJ)
- `cpf_cnpj` UNIQUE
- `responsavel_usuario_id` FK `usuarios.id`
- contatos e endereço
- `status` ENUM('ativo','pendente','suspenso')
- timestamps

### `assessoria_equipe`
Vincula usuários à assessoria.
- `assessoria_id` FK
- `usuario_id` FK
- `funcao` ENUM('admin','assessor','suporte')
- `status` ENUM('ativo','inativo')
- UNIQUE (`assessoria_id`,`usuario_id`)

### `assessoria_atletas`
Vincula atletas existentes do Movamazon.
- `assessoria_id` FK
- `atleta_usuario_id` FK `usuarios.id`
- `assessor_usuario_id` FK `usuarios.id` (nullable)
- `origem` ENUM('inscricao_evento','convite','manual')
- período, status, observações
- UNIQUE (`assessoria_id`,`atleta_usuario_id`)

### `assessoria_programas`
Programa macro (evento ou contínuo).
- `assessoria_id` FK
- `tipo` ENUM('evento','continuo')
- `evento_id` FK `eventos.id` (nullable)
- datas, objetivo, metodologia, status

### `assessoria_programa_atletas`
Atletas por programa.
- `programa_id` FK
- `atleta_usuario_id` FK
- UNIQUE (`programa_id`,`atleta_usuario_id`)

## Ajustes em tabelas existentes

### `planos_treino_gerados`
Adicionar:
- `assessoria_id` FK `assessorias.id` (nullable)
- `programa_id` FK `assessoria_programas.id` (nullable)
- `status` ENUM('rascunho','publicado','revisao','arquivado')
- `versao` INT DEFAULT 1
- `publicado_em` TIMESTAMP NULL

Regras:
- `profissional_id` continua sendo o usuário assessor (owner do conteúdo)
- O plano “rascunho” não aparece para atleta até publicar

### `treinos`
Adicionar:
- `assessoria_id` (nullable)
- `programa_id` (nullable)
- `criado_por_usuario_id` (assessor)
- `status` ENUM('rascunho','ativo','pausado')
- `publicado_em`

### `progresso_treino`
Adicionar:
- `fonte` ENUM('atleta','assessor')
- `registrado_por_usuario_id` FK `usuarios.id` (nullable)
- `feedback_atleta` TEXT
- `feedback_assessor` TEXT

## Índices e integridade
- Índices em todos os FKs novos
- Uniques de prevenção de duplicidade em `assessoria_equipe`, `assessoria_atletas`, `assessoria_programa_atletas`
