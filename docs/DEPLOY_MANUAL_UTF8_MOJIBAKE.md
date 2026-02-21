# Deploy manual – correções UTF-8 / mojibake

**Use este guia** quando quiser **apenas** corrigir os textos com mojibake na nuvem (inscrição e pagamento).  
Para um deploy completo (webhook, treino, migrations, etc.), use o [RESUMO_DEPLOY_HOSPEDAGEM.md](RESUMO_DEPLOY_HOSPEDAGEM.md) e inclua os arquivos deste guia no passo 4 do checklist.

**Deploy é manual** (FTP, cPanel, rsync, etc.). Não há GitHub Actions nem pipeline automático.

Estas correções **não usam banco de dados** e **não exigem scripts rodando no servidor**. Basta enviar os arquivos listados abaixo para a hospedagem (sobrescrevendo os existentes).

---

## O que foi corrigido

- Texto com mojibake em **todo o frontend** (PHP e JS): inscrição, pagamento, participante, admin, organizador, includes, utils, API, componentes. Veja [PLANO_CORRECAO_UTF8_SISTEMA.md](PLANO_CORRECAO_UTF8_SISTEMA.md) e [CHECKLIST_UTF8_POS_CORRECAO.md](CHECKLIST_UTF8_POS_CORRECAO.md).
- Mensagens SweetAlert, labels, placeholders e textos estáticos em `.js` e `.php`.
- Prevenção: charset UTF-8 no editor e no Git (`.editorconfig`, `.gitattributes`) e no servidor (`.htaccess`). **Novos arquivos devem ser salvos em UTF-8 (sem BOM).**

---

## Arquivos a enviar para a nuvem

Para correção **completa** de UTF-8 no sistema, envie todos os arquivos listados em [CHECKLIST_UTF8_POS_CORRECAO.md](CHECKLIST_UTF8_POS_CORRECAO.md) (por fase), mantendo a mesma estrutura de pastas.

**Mínimo (inscrição e pagamento):**

| Caminho | Observação |
|---------|------------|
| `frontend/js/inscricao/pagamento.js` | Mensagens de erro e textos visíveis corrigidos |
| `frontend/js/inscricao/progress-tracker.js` | Textos do progresso da inscrição corrigidos |
| `frontend/paginas/inscricao/pagamento.php` | **Contém o botão verde "Pagar com PIX"** – deve estar em UTF-8 |
| `.htaccess` | Inclui `AddDefaultCharset UTF-8` (raiz do projeto) |
| `.editorconfig` | Charset UTF-8 (opcional; ajuda quem edita no servidor) |
| `.gitattributes` | LF e texto UTF-8 (opcional; só importa no Git) |

**Não é necessário:**

- Rodar qualquer script no servidor.
- Executar migration ou SQL no banco.
- Reiniciar serviços (apenas garantir que o servidor sirva os `.js` atualizados, o que ocorre ao sobrescrever os arquivos).

---

## Ordem sugerida no deploy manual

1. Fazer backup dos arquivos atuais na hospedagem (sobretudo `pagamento.js` e `progress-tracker.js`), se quiser.
2. Enviar os 5 arquivos acima para os mesmos caminhos no servidor (sobrescrever).
3. Limpar cache do navegador ou testar em aba anônima na página de inscrição para conferir os textos.

Se a hospedagem não permitir arquivos que começam com ponto: envie ao menos `frontend/js/inscricao/pagamento.js`, `frontend/js/inscricao/progress-tracker.js`, `frontend/paginas/inscricao/pagamento.php` e `.htaccess` — são os que afetam diretamente o que o usuário vê (incluindo o botão verde "Pagar com PIX") e o charset das respostas.

---

## Por que “nada mudou” na tela?

Todas as correções foram feitas **nos arquivos do seu projeto, no seu computador**. O site que você abre no navegador (na **hospedagem/nuvem**) continua usando a **versão antiga** desses arquivos até você **enviá-los** para o servidor.

- **O que fizemos:** Corrigimos textos com mojibake em `pagamento.js`, `progress-tracker.js`, `pagamento.php`, etc., e criamos `.editorconfig` e `.gitattributes` para evitar novos problemas.
- **Por que o botão ainda aparece quebrado:** O servidor ainda está servindo o arquivo antigo (ou uma versão salva com encoding errado). O botão "Pagar com PIX" está no **HTML** de `frontend/paginas/inscricao/pagamento.php`; se esse arquivo na nuvem não for o corrigido em UTF-8, o texto continua com mojibake.
- **O que fazer:** Fazer o **deploy manual**: enviar os arquivos da tabela acima para a hospedagem (sobrescrevendo os atuais), depois **limpar o cache do navegador** ou testar em aba anônima.
