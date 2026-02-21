# Plano de correção UTF-8 em todo o sistema

Documento de referência do plano executado para eliminar mojibake no frontend (PHP e JS).

---

## Objetivo

Corrigir **na origem** todas as strings com mojibake em arquivos do frontend, para que nenhuma página ou mensagem (incluindo SweetAlert) exiba texto corrompido. Não usar remendos no navegador; garantir arquivos salvos em UTF-8 com strings corretas.

---

## Escopo

- **PHP:** `frontend/paginas/`, `frontend/includes/` (todo HTML visível).
- **JavaScript:** `frontend/js/` – textos em `Swal.fire()`, `alert()`, `innerHTML`, `.html()`, `textContent`, placeholders, labels dinâmicos.
- **Exclusão:** `frontend/js/inscricao_EXEMPLO/` (opcional/por último), arquivos `.min.js`.

---

## Estratégia

1. **Mapeamento único** de sequências de mojibake → caracteres UTF-8 corretos (padrão longo e curto).
2. **Script de correção em lote** (Node.js) que lê em UTF-8, aplica as substituições, grava em UTF-8 sem BOM.
3. **Execução por fases** (1 a 5), com verificação por grep e revisão manual amostral.

---

## Fases

| Fase | Conteúdo |
|------|----------|
| **1** | Rotina inscrição e pagamento (páginas inscrição, participante pagamento*, JS inscricao + pagamento-inscricao.js) |
| **2** | Restante participante + auth (páginas e JS) |
| **3** | Admin, organizador, public (páginas e JS) |
| **4** | Includes e cabeçalhos globais |
| **5** | Utils, API, components e demais JS em `frontend/js/` |

---

## Script de correção

- **Arquivo:** `scripts/fix_utf8_batch.js`
- **Uso:**
  - Processar tudo: `node scripts/fix_utf8_batch.js`
  - Por fase: `node scripts/fix_utf8_batch.js --phase=1` (1 a 5)
  - Só relatório: `node scripts/fix_utf8_batch.js --dry-run`
- **Mapeamento:** LONG_MAP (prefixo longo + sufixos para ã, ç, á, í, ó, â, ª, é, º, ô) e SHORT_MAP (ex.: `Ã§Ã£o`→`ção`, `Ã£o`→`ão`, `Ã­vel`→`ível`, `Ã¡`→`á`, `Ã§`→`ç`, etc.). Ordem: sequências longas primeiro.

---

## Garantia de encoding

- **`.editorconfig`:** `charset = utf-8` para `*` e para `*.js`, `*.php`, `*.html`, `*.css`.
- **`.gitattributes`:** `* text=auto`, `*.js text eol=lf`, `*.php text eol=lf`, etc.
- **Novos arquivos:** Salvar sempre em **UTF-8 sem BOM**.

---

## Documentos relacionados

- [CHECKLIST_UTF8_POS_CORRECAO.md](CHECKLIST_UTF8_POS_CORRECAO.md) – Lista de arquivos por fase e status para deploy e verificação.
- [DEPLOY_MANUAL_UTF8_MOJIBAKE.md](DEPLOY_MANUAL_UTF8_MOJIBAKE.md) – Deploy manual dos arquivos corrigidos na hospedagem.

---

## Resumo da execução

- Script implementado e executado (Fases 1–5 + run completo).
- Centenas de substituições aplicadas em dezenas de arquivos em `frontend/`.
- Verificação: grep por padrões de mojibake pode ainda apontar variantes em alguns arquivos; revisar manualmente se houver texto quebrado na interface.
