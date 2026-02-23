# Plano: Implantacao do PHPUnit com foco em seguranca de deploy

## Objetivo final

Garantir que o deploy so aconteca com alta confianca, usando testes automatizados reais, repetiveis e isolados.

Principio do plano:

- Se for necessario alterar codigo de producao para viabilizar teste confiavel, a alteracao deve ser feita.
- E melhor refatorar para testabilidade do que manter acoplamento que gera erro silencioso em producao.

## Contexto atual (baseline)

- Projeto majoritariamente procedural em `api/`, sem suite de testes automatizados.
- `composer.json` ainda sem `require-dev` para PHPUnit.
- Endpoints com acoplamento a `$_SERVER`, `$_SESSION`, `header()`, `echo`, `exit`, `require ../db.php`.
- Modulo Mercado Livre com forte acoplamento a config/env, DB e HTTP real.

## Decisoes tecnicas obrigatorias

1. Arquitetura orientada a teste

- Regras de negocio nao podem depender diretamente de `header()`, `echo`, `exit`, `$_SERVER`, `$_SESSION`.
- Endpoints viram camada fina de transporte (HTTP in/out).
- Regra de negocio vai para funcoes/servicos testaveis.

2. Refatoracao segura em producao (sem mudar comportamento)

- Permitida e obrigatoria quando reduzir risco de regressao.
- Deve preservar contrato externo das rotas.

3. Gate de deploy

- Deploy bloqueado se testes falharem.
- Deploy bloqueado se cobertura de modulos criticos ficar abaixo do minimo definido.

## 1. Dependencias e scripts

Atualizar `composer.json`:

- `require-dev`:
  - `"phpunit/phpunit": "^11.5"` (compativel com ambiente PHP atual).
- `scripts`:
  - `"test": "phpunit --testsuite Unit"`
  - `"test:unit": "phpunit --testsuite Unit"`
  - `"test:integration": "phpunit --testsuite Integration"`
  - `"test:all": "phpunit"`
  - `"test:coverage": "phpunit --coverage-text --coverage-clover build/logs/clover.xml"`

Observacao:

- Usar `composer require --dev phpunit/phpunit:^11.5` para manter lock consistente.

## 2. Configuracao PHPUnit

Criar `phpunit.xml` na raiz com:

- `bootstrap="tests/bootstrap.php"`.
- `cacheDirectory=".phpunit.cache"`.
- `failOnWarning="true"`, `failOnRisky="true"`, `beStrictAboutOutputDuringTests="true"`.
- `executionOrder="depends,defects"`.
- `testsuites`:
  - `Unit` -> `tests/Unit`
  - `Integration` -> `tests/Integration`
- `source/include` com `api/` e `src/` (se existir).
- Exclusao de `vendor/`, logs e arquivos nao-fonte.

## 3. Estrategia de cobertura e qualidade

Metas iniciais:

- Cobertura global minima: 75%.
- Cobertura minima em modulos criticos (`financeiro`, `inscricao`, `mercadolivre`): 90% de linhas.

Evolucao apos estabilizacao:

- Subir cobertura global para 85%.

Regra:

- Nao aprovar deploy com testes vermelhos.
- Nao aprovar deploy com cobertura abaixo da meta minima do ciclo.

## 4. Refatoracao de producao para testabilidade (obrigatoria)

### 4.1 Financeiro

Arquivo principal:

- `api/financeiro/financeiro_service.php`

Acoes:

- Manter funcoes puras como base dos testes unitarios.
- Funcoes com PDO permanecem para integracao, mas com entradas validas e previsiveis.
- Evitar efeitos colaterais fora da funcao.

### 4.2 Inscricao

Arquivos atuais:

- `api/inscricao/save_inscricao.php`
- `api/inscricao/validar_cupom.php`
- `api/inscricao/validate.php` (legado/alternativo)

Acoes obrigatorias:

- Extrair regras para funcoes/servicos:
  - `calcular_valor_total_inscricao(...)`
  - validacoes de cupom
  - persistencia transacional de inscricao
- Endpoint fica apenas com:
  - leitura de request
  - chamada de servico
  - serializacao de resposta HTTP
- Eliminar teste por include direto de script com `exit`; testar a regra no servico.

### 4.3 Mercado Livre

Arquivos atuais:

- `api/mercadolivre/MercadoLivrePayment.php`
- `api/mercadolivre/payment_helper.php`
- `api/mercadolivre/config.php`

Problema:

- Acoplamento forte a `db.php`, token real, curl real e banco real.

Acoes obrigatorias:

- Remover `require ../db.php` do topo de classes de dominio.
- Injetar dependencias no construtor:
  - provider de config
  - cliente HTTP
  - repositorio de pagamento
- Tornar camada HTTP mockavel em teste.
- Manter wrapper de compatibilidade para nao quebrar endpoints existentes.

Resultado esperado:

- Teste unitario de `criarPagamento` sem bater em API externa e sem usar DB real.

## 5. Ambiente de teste isolado

Criar:

- `.env.testing` (separado de `.env` de producao/dev).

Regras:

- Suite de Integration usa apenas DB de teste dedicado.
- Validacao de seguranca no bootstrap: abortar se DB nao for claramente de teste (exemplo: nome termina com `_test`).
- Proibido rodar Integration com credenciais de producao.

## 6. Bootstrap e estrutura de testes

Estrutura:

```text
tests/
  bootstrap.php
  bootstrap.integration.php
  Unit/
  Integration/
  Fixtures/
```

`tests/bootstrap.php`:

- carregar `vendor/autoload.php`;
- definir timezone fixa para previsibilidade (ex.: UTC);
- inicializar utilitarios de teste;
- nao abrir conexao de banco automaticamente.

`tests/bootstrap.integration.php`:

- carregar env de teste;
- criar conexao DB de teste;
- validar nome do banco;
- preparar limpeza por transacao.

## 7. Estrategia de banco para Integration

Padrao:

- Antes de cada teste: `beginTransaction()`.
- Depois de cada teste: `rollback()`.
- Fixtures deterministicas por factory/seed controlado.

Escopo inicial de Integration:

- fluxo de cupom valido/invalido/esgotado;
- fluxo de salvar inscricao;
- operacoes financeiras que usam ledger/saldo;
- fluxo de pagamento com HTTP fake (sem dependencia de rede real).

## 8. Testes prioritarios (ordem de implementacao)

1. Unit - Financeiro puro

- `fin_decimal`
- `fin_to_datetime`
- `fin_status_saldo_considerado`
- `fin_normalizar_metadata`

2. Unit - Inscricao helper/regra

- `sanitizeLogData`
- `getClientInfo` com controle de `$_SERVER`
- `calcular_valor_total_inscricao` (apos extracao)

3. Unit - Mercado Livre

- validacoes de entrada de pagamento
- montagem de payload de preferencia
- mapeamento de status (`PaymentHelper::mapearStatus*`)
- cenario de erro sem token/config

4. Integration - Criticos de negocio

- cupom
- salvamento de inscricao
- saldo e repasse financeiro
- integracao de pagamento sem rede real

## 9. CI/CD e bloqueio de deploy

Pipeline minima:

1. Instalar dependencias
2. Rodar `composer test:unit`
3. Rodar `composer test:integration`
4. Rodar `composer test:coverage`
5. Validar limiares de cobertura
6. Liberar deploy apenas se tudo verde

## 10. Arquivos a criar/alterar

Criar:

- `phpunit.xml`
- `tests/bootstrap.php`
- `tests/bootstrap.integration.php`
- `tests/Unit/*`
- `tests/Integration/*`
- `tests/Fixtures/*`
- `docs/PHPUNIT_GUIA.md`

Alterar:

- `composer.json`
- `composer.lock`
- arquivos de producao necessarios para remover acoplamento e viabilizar testes confiaveis:
  - `api/inscricao/*` (extracao de regra)
  - `api/mercadolivre/*` (DI/mocks/isolamento de infra)
  - eventuais ajustes pontuais em `api/financeiro/*`

## 11. Criterio de pronto (Definition of Done)

So considerar implantacao concluida quando:

- PHPUnit configurado e executando local e CI.
- Suites Unit e Integration estaveis.
- Deploy bloqueado com testes falhando.
- Cobertura minima atingida (global + modulos criticos).
- Fluxos criticos (financeiro, inscricao, pagamento) cobertos por testes confiaveis.
- Guia em `docs/PHPUNIT_GUIA.md` atualizado e aderente ao codigo real.

## 12. Risco e mitigacao

Risco:

- refatoracao em modulo legado quebrar endpoint existente.

Mitigacao:

- manter contrato HTTP atual;
- refatorar em passos pequenos;
- rodar regressao automatizada a cada etapa;
- entregar por fases com gate verde continuo.

---

Resumo executivo:

- O plano prioriza seguranca de deploy acima de custo de refatoracao.
- Alteracao em producao e parte da estrategia, nao excecao.
- O sistema passa a ser governado por testes automatizados com bloqueio real de erro antes de deploy.
