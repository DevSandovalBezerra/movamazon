# 02 – Acesso, RBAC e roteamento

## Papéis (RBAC)
Papéis sugeridos:
- `assessoria_admin`: dono/responsável legal. Gerencia assessoria, equipe, atletas, programas, relatórios.
- `assessor`: treinador. Cria/revisa/publica treinos e acompanha atletas atribuídos.
- `atleta`: usuário padrão que executa treinos e registra progresso.

Observação:
- O usuário pode ter múltiplos papéis (ex.: atleta e assessor).
- O sistema já possui `papeis` e `usuario_papeis` — usar isso como fonte oficial.

## Regras de permissão (alto nível)
### `assessoria_admin`
- CRUD assessoria (perfil/identidade)
- CRUD equipe (convidar, ativar/inativar, função)
- Vincular atletas (manual/importar evento/convite)
- CRUD programas
- Acesso total a relatórios e monitoramento

### `assessor`
- Listar atletas vinculados e/ou atribuídos
- Criar/editar/publicar planos/treinos para atletas atribuídos
- Ver progresso de atletas atribuídos
- Registrar feedback do assessor em progresso

### `atleta`
- Ver plano publicado
- Registrar progresso
- Ver evolução/relatórios pessoais

## Roteamento pós-login (decisão /assessoria separado)
- Se o usuário tem `assessoria_admin` ou `assessor`:
  - Acesso ao **Painel /assessoria**
- Se tem apenas atleta:
  - Mantém o **painel atual do atleta**
- Se tem ambos (atleta + assessor/admin):
  - Tela seletora: “Entrar como”
    - Atleta
    - Assessoria

## Padrão de URLs (rotas sugeridas)
- `/assessoria` (home dashboard)
- `/assessoria/atletas`
- `/assessoria/atletas/:id`
- `/assessoria/programas`
- `/assessoria/programas/:id`
- `/assessoria/planos`
- `/assessoria/planos/:id`
- `/assessoria/monitoramento`
- `/assessoria/equipe` (apenas admin)
- `/assessoria/configuracoes` (apenas admin)
