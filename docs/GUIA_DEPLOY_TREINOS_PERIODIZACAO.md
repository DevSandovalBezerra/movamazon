# Guia Rápido de Deploy (Treinos + Periodização)

Este guia cobre o deploy das alterações em:
- `api/participante/treino/generate.php`
- `api/participante/treino/save.php`
- `api/participante/treino/get.php`
- `frontend/js/participante/treinos.js`
- `migrations/2026-02-15_treino_idempotencia_sem_unique.sql`

## 1. Pré-deploy (5 min)

1. Confirmar branch/tag que será publicada.
2. Fazer backup do banco de produção.
3. Confirmar credenciais de acesso SSH/FTP e banco.
4. Garantir janela de deploy com baixo tráfego (não exige parada).

## 2. Subir arquivos na hospedagem

Publicar os arquivos alterados para o servidor, preservando caminhos:
- `api/participante/treino/generate.php`
- `api/participante/treino/save.php`
- `api/participante/treino/get.php`
- `frontend/js/participante/treinos.js`
- `migrations/2026-02-15_treino_idempotencia_sem_unique.sql`

## 3. Executar migration (aditiva, sem UNIQUE)

No banco de produção, executar:

```sql
SOURCE migrations/2026-02-15_treino_idempotencia_sem_unique.sql;
```

Ou via CLI:

```bash
mysql -u SEU_USUARIO -p SEU_BANCO < migrations/2026-02-15_treino_idempotencia_sem_unique.sql
```

## 4. Validação pós-deploy (smoke test)

1. Acessar a área de participante e gerar treino para uma inscrição válida.
2. Repetir a ação rapidamente (duplo clique/duas requisições) e validar comportamento idempotente.
3. Abrir a tela de treinos e confirmar carregamento sem erro JS.
4. Validar que plano e treinos são exibidos normalmente.
5. Validar fluxo de pagamento no checkout (sem alteração esperada) e webhook.

## 5. Logs para monitorar (primeira hora)

- Erros em `api/participante/treino/generate.php`
- Erros em `api/participante/treino/save.php`
- Erros JS no navegador (console) na tela de treinos
- Logs de webhook/pagamentos (apenas monitoramento, sem mudança funcional)

## 6. Rollback rápido (se necessário)

1. Reverter apenas os arquivos PHP/JS para a versão anterior.
2. Manter a migration aplicada (colunas novas são opcionais e não quebram versão anterior).
3. Limpar cache/opcache da hospedagem (se aplicável).
4. Revalidar geração e visualização de treinos.

## 7. Observações de segurança

- Não foi aplicado `UNIQUE(inscricao_id)` em `planos_treino_gerados`.
- A estratégia usa lock nomeado + idempotência na aplicação.
- Não há alteração direta no fluxo `api/mercadolivre` de pagamentos.
