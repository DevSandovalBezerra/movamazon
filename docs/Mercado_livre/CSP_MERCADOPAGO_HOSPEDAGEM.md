# CSP na hospedagem – Payment Brick Mercado Pago bloqueado

## Problema

Na etapa de pagamento (inscrição), o console mostra:

```text
Loading the script 'blob:https://secure-fields.mercadopago.com/...' violates the following Content Security Policy directive:
"script-src 'nonce-...' 'sha256-...'"
```

Isso indica que a **CSP em vigor** não é a que definimos no projeto (`.htaccess` ou `index.php`), e sim uma CSP **mais restritiva** com `nonce` e `sha256`, definida **no servidor/hospedagem**. Enquanto essa CSP estiver ativa, os scripts em `blob:` do Mercado Pago continuarão bloqueados.

---

## O que já está no projeto

- **`frontend/paginas/inscricao/.htaccess`** – tenta remover a CSP do servidor (`Header unset`) e definir a nossa (com `blob:`, domínios MP/ML, CDNs, Google Analytics).
- **`frontend/paginas/inscricao/index.php`** (etapa 4) – envia a mesma CSP via `header()` em PHP.

Se a hospedagem **sobrescrever** ou **adicionar** a CSP depois (por configuração global, ModSecurity ou painel), a política restritiva com nonce/sha256 continua valendo.

---

## O que fazer na hospedagem (HostGator / cPanel)

### 1. Confirmar qual CSP está valendo

1. No navegador, abra a página de pagamento:  
   `https://movamazon.com.br/frontend/paginas/inscricao/index.php?evento_id=8&etapa=4`
2. Abra as ferramentas do desenvolvedor (F12) → aba **Rede**.
3. Recarregue a página e clique na requisição do documento (a primeira, `index.php?...`).
4. Em **Cabeçalhos** (Headers) da **resposta**, procure **Content-Security-Policy**.

- Se aparecer algo como `script-src 'nonce-...' 'sha256-...'` **sem** `blob:` nem os domínios do Mercado Pago, a CSP da **hospedagem** está em uso.

### 2. Onde a CSP costuma ser definida

Em hospedagens cPanel/HostGator, a CSP restritiva costuma vir de:

- **ModSecurity** (regras que injetam headers de segurança).
- **“Security Headers”** ou **“Content Security Policy”** no cPanel.
- **.htaccess** em um diretório pai (por exemplo na raiz do domínio ou em `public_html`) que define `Header set Content-Security-Policy`.

### 3. Ações recomendadas

**Opção A – Desativar a CSP global que usa nonce/sha256**

- No cPanel, procure por **ModSecurity** ou **Security** / **Headers**.
- Se houver opção de **Content Security Policy** ou **Security Headers**, desative para o site ou para o diretório da inscrição, **ou**
- Se usar ModSecurity, desative-o para o diretório do fluxo de inscrição (conforme a documentação do painel).

Assim, a CSP que enviamos no `.htaccess` ou no `index.php` da inscrição passa a ser a única e o Payment Brick pode carregar (`blob:` e domínios permitidos).

**Opção B – Ajustar a CSP do servidor em vez de desativar**

- No mesmo lugar em que a CSP é configurada (painel ou ModSecurity), edite a política para incluir em **script-src**:
  - `blob:`
  - `https://sdk.mercadopago.com`
  - `https://secure-fields.mercadopago.com`
  - `https://www.mercadopago.com`
  - `https://www.mercadopago.com.br`
  - `https://http2.mlstatic.com`
- E em **connect-src** e **frame-src** os domínios necessários do Mercado Pago/Mercado Libre (conforme o que já está no nosso `.htaccess`).

Assim o Payment Brick passa a funcionar sem desativar a CSP do servidor.

**Opção C – Suporte da hospedagem**

- Se não achar onde a CSP é definida, abra um chamado com a HostGator e peça:
  - para **remover** ou **não enviar** o header `Content-Security-Policy` nas requisições para a pasta do fluxo de inscrição (ex.: `.../frontend/paginas/inscricao/`), **ou**
  - para incluir na CSP atual as permissões acima (script-src com `blob:` e domínios do Mercado Pago).

---

## Google Analytics (infird.com)

Foi adicionado em **connect-src** na nossa CSP:

- `https://www.google-analytics.com`
- `https://www.googletagmanager.com`

Assim, quando a **nossa** CSP estiver em vigor, o script do infird.com que envia dados para o Google Analytics deixará de ser bloqueado por CSP. Se a CSP da hospedagem continuar ativa e não tiver esses domínios, o bloqueio ao GA pode continuar até a CSP do servidor ser ajustada ou desativada conforme as opções acima.

---

## Resumo

| Sintoma | Causa provável | Ação |
|--------|-----------------|------|
| Erro com `script-src 'nonce-...' 'sha256-...'` e bloqueio a `blob:https://secure-fields.mercadopago.com` | CSP definida na hospedagem (ModSecurity / cPanel) | Desativar ou alterar essa CSP no servidor; nossa CSP está em `.htaccess` e `index.php`. |
| “Connecting to google-analytics.com violates... connect-src” | Nossa CSP não permite GA | Já incluído `google-analytics.com` e `googletagmanager.com` em connect-src; passa a valer quando nossa CSP estiver ativa. |

Depois que a CSP do servidor for corrigida (ou desativada) para a pasta da inscrição, faça um novo teste na etapa de pagamento e confira de novo o header **Content-Security-Policy** na aba Rede.
