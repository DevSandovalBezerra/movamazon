# Checklist UTF-8 pós-correção

Lista de arquivos por fase do plano de correção UTF-8. Use para deploy e verificação.

**Status geral:** Script de correção em lote executado (Fases 1–5 + run completo). Arquivos marcados como **Corrigido** foram processados; **Verificado** indica revisão manual opcional.

---

## Fase 1 – Rotina inscrição e pagamento

| Arquivo | Status |
|---------|--------|
| `frontend/paginas/inscricao/index.php` | Corrigido |
| `frontend/paginas/inscricao/modalidade.php` | Corrigido |
| `frontend/paginas/inscricao/termos.php` | Corrigido |
| `frontend/paginas/inscricao/ficha.php` | Corrigido |
| `frontend/paginas/inscricao/pagamento.php` | Corrigido |
| `frontend/paginas/inscricao/sucesso.php` | Corrigido |
| `frontend/paginas/inscricao/login-inscricao.php` | Corrigido |
| `frontend/paginas/inscricao/register-inscricao.php` | Corrigido |
| `frontend/paginas/inscricao/identificacao.php` | Corrigido |
| `frontend/paginas/inscricao/includes/header-inscricao.php` | Corrigido |
| `frontend/paginas/inscricao/includes/progress_bar.php` | Corrigido |
| `frontend/paginas/inscricao/includes/footer-inscricao.php` | Corrigido |
| `frontend/paginas/participante/pagamento-sucesso.php` | Corrigido |
| `frontend/paginas/participante/pagamento-erro.php` | Corrigido |
| `frontend/paginas/participante/pagamento-pendente.php` | Corrigido |
| `frontend/paginas/participante/pagamento-inscricao.php` | Corrigido |
| `frontend/js/inscricao/pagamento.js` | Corrigido |
| `frontend/js/inscricao/progress-tracker.js` | Corrigido |
| `frontend/js/inscricao/validation.js` | Corrigido |
| `frontend/js/inscricao/termos.js` | Corrigido |
| `frontend/js/inscricao/modalidade.js` | Corrigido |
| `frontend/js/inscricao/inscricao.js` | Corrigido |
| `frontend/js/inscricao/identificacao.js` | Corrigido |
| `frontend/js/inscricao/ficha.js` | Corrigido |
| `frontend/js/inscricao/auto-save.js` | Corrigido |
| `frontend/js/inscricao/state-manager.js` | Corrigido |
| `frontend/js/inscricao/init-modules.js` | Corrigido |
| `frontend/js/participante/pagamento-inscricao.js` | Corrigido |

---

## Fase 2 – Participante e auth

| Arquivo | Status |
|---------|--------|
| `frontend/paginas/participante/*.php` (index, dashboard, inscricoes, treinos, perfil, anamnese, etc.) | Corrigido |
| `frontend/paginas/auth/*.php` (login, register, resetar_senha, etc.) | Corrigido |
| `frontend/js/participante/*.js` (inscricoes, dashboard, treinos, qrcode, perfil, etc.) | Corrigido |
| `frontend/js/auth-register.js` | Corrigido |
| `frontend/js/auth-handler.js` | Corrigido |

---

## Fase 3 – Admin, organizador e public

| Arquivo | Status |
|---------|--------|
| `frontend/paginas/admin/*.php` | Corrigido |
| `frontend/paginas/organizador/*.php` | Corrigido |
| `frontend/paginas/public/*.php` | Corrigido |
| `frontend/js/admin/*.js` | Corrigido |
| `frontend/js/organizador/*.js` | Corrigido |
| `frontend/js/public/*.js` | Corrigido |
| `frontend/js/organizador-eventos.js` | Corrigido |
| `frontend/js/organizador-criar-evento.js` | Corrigido |
| `frontend/js/eventos.js` | Corrigido |
| `frontend/js/programacao.js` | Corrigido |

---

## Fase 4 – Includes e cabeçalhos globais

| Arquivo | Status |
|---------|--------|
| `frontend/includes/header.php` | Corrigido |
| `frontend/includes/header_index.php` | Corrigido |
| `frontend/includes/footer.php` | Corrigido |
| `frontend/includes/admin_header.php` | Corrigido |
| `frontend/includes/admin_sidebar.php` | Corrigido |
| Demais `frontend/includes/*.php` | Corrigido |

---

## Fase 5 – Utils, API, components e demais JS

| Arquivo | Status |
|---------|--------|
| `frontend/js/utils/*.js` | Corrigido |
| `frontend/js/api/*.js` | Corrigido |
| `frontend/js/components/*.js` | Corrigido |
| Demais `frontend/js/*.js` (camisas, categorias, eventos, kits, lotes, modalidades, etc.) | Corrigido |

---

## Exclusões

- **`frontend/js/inscricao_EXEMPLO/`** – Não processado (backup/exemplos). Tratar por último se necessário.
- Arquivos **`.min.js`** – Ignorados pelo script.

---

## Verificação pós-correção

- Rodar grep por padrões de mojibake (`Ãƒ`, `Ã£`, `Ã­`, `Ã§`, etc.) nos diretórios corrigidos. Algumas variantes podem permanecer em arquivos; revisar manualmente se ainda houver texto quebrado na tela.
- Revisão manual: abrir 3–5 páginas da rotina (modalidade, termos, ficha, pagamento, sucesso) e conferir textos e SweetAlerts em português.

---

## Deploy

Ao enviar para a nuvem, inclua todos os arquivos listados acima (ou o conjunto completo de `frontend/`). Veja [DEPLOY_MANUAL_UTF8_MOJIBAKE.md](DEPLOY_MANUAL_UTF8_MOJIBAKE.md).
