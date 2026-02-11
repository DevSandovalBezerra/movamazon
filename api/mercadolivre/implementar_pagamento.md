# 💳 **PLANO DE IMPLEMENTAÇÃO - SISTEMA DE PAGAMENTO (Mercado Pago/Mercado Livre) – MovAmazon**

## 📋 **VISÃO GERAL**

Este documento detalha a implementação do sistema de pagamento integrado ao ecossistema Mercado Pago/Mercado Livre para o MovAmazon, reaproveitando a lógica já validada em `api/mercadoPago/` e integrando ao fluxo de inscrição do projeto. Pontos-chave aprovados:

- Renderizar o formulário do Mercado Pago dentro do bloco da etapa de pagamento em `frontend/paginas/inscricao/pagamento.php` (pode usar modal SweetAlert, sem abrir nova página);
- Registrar o financeiro nas tabelas oficiais do MovAmazon (`pagamentos`) e conciliar com `pagamentos_ml`; atualizar `inscricoes`;
- Usar `notification.php` (adaptado) como endpoint único de webhook;
- Nenhuma restrição de métodos de pagamento (crédito/débito/PIX) nem de parcelas nesta fase;
- Foco no financeiro; repasses ficam para etapa posterior;
- Todo conteúdo exibido ao usuário em Português (BR);
- Manter arquivos da integração anterior como referência, sem exclusões.

---

## 🏗️ **ARQUITETURA DO SISTEMA**

### **1. ESTRUTURA DE ARQUIVOS IMPLEMENTADA**

```
api/mercadolivre/
├── MercadoLivrePayment.php      # Classe principal de integração
├── create_payment.php           # API para criar pagamentos
├── webhook.php                  # Receber notificações do ML
└── get_payment_status.php       # Consultar status de pagamentos

frontend/paginas/inscricao/
├── pagamento.php                # Página de pagamento
├── sucesso.php                  # Página de retorno (sucesso)
├── falha.php                    # Página de retorno (falha)
└── pendente.php                 # Página de retorno (pendente)

frontend/js/inscricao/
├── pagamento.js                 # Lógica de pagamento
└── mercadolivre.js              # Integração específica ML

docs/
├── implementar_pagamento.md     # Este documento
└── criar_tabelas_pagamento_ml.sql # Scripts de banco
```

Observação: A pasta `api/mercadoPago/` e seus arquivos associados serão mantidos como referência e fonte de lógica validada (PIX/BRICKS/webhook), sem exclusão.

---

## 🔧 **CONFIGURAÇÃO DO AMBIENTE**

### **1. Instalação do dotenv**

```bash
composer require vlucas/phpdotenv
```

### **2. Arquivo .env**

```env
# Configurações do Banco
DB_HOST=localhost
DB_NAME=movamazon
DB_USER=root
DB_PASS=

# Configurações Mercado Livre
ML_ACCESS_TOKEN=SEU_ACCESS_TOKEN
ML_CLIENT_ID=SEU_CLIENT_ID
ML_CLIENT_SECRET=SEU_CLIENT_SECRET
ML_ENVIRONMENT=sandbox
ML_AUTO_RETURN=https://movamazon.com/inscricao/sucesso
ML_NOTIFICATION_URL=https://movamazon.com/api/mercadolivre/webhook.php
ML_EXTERNAL_REFERENCE=MOVAMAZON_

# Configurações da Aplicação
APP_URL=https://movamazon.com
APP_ENV=development
```

### **3. Atualização do db.php**

O arquivo `api/db.php` foi atualizado para carregar automaticamente as variáveis de ambiente:

```php
// Carregar variáveis de ambiente
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Usar variáveis de ambiente
$host = $_ENV['DB_HOST'] ?? 'localhost';
$db = $_ENV['DB_NAME'] ?? 'movamazon';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';
```

---

## 🗄️ **ESTRUTURA DO BANCO DE DADOS**

### **1. Tabela `inscricoes` (Atualizada)**

```sql
ALTER TABLE inscricoes ADD COLUMN (
    status_pagamento ENUM('pendente', 'pago', 'cancelado', 'rejeitado', 'processando', 'reembolsado') DEFAULT 'pendente',
    valor_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    valor_desconto DECIMAL(10,2) DEFAULT 0.00,
    cupom_aplicado VARCHAR(50) NULL,
    data_pagamento DATETIME NULL,
    forma_pagamento VARCHAR(50) NULL,
    parcelas INT DEFAULT 1,
    seguro_contratado BOOLEAN DEFAULT FALSE,
    produtos_extras TEXT NULL,
    external_reference VARCHAR(100) NULL,
    payment_id VARCHAR(100) NULL
);
```

### **2. Tabela `pagamentos_ml` (Nova)**

```sql
CREATE TABLE pagamentos_ml (
    id INT PRIMARY KEY AUTO_INCREMENT,
    inscricao_id INT NOT NULL,
    preference_id VARCHAR(100) NOT NULL,
    payment_id VARCHAR(100) NULL,
    init_point TEXT NOT NULL,
    status ENUM('pendente', 'pago', 'cancelado', 'rejeitado', 'processando', 'reembolsado') DEFAULT 'pendente',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    dados_pagamento JSON NULL,
    FOREIGN KEY (inscricao_id) REFERENCES inscricoes(id) ON DELETE CASCADE
);
```

### **3. Tabela `produtos_extras` (Nova)**

```sql
CREATE TABLE produtos_extras (
    id INT PRIMARY KEY AUTO_INCREMENT,
    evento_id INT NOT NULL,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT NULL,
    preco DECIMAL(10,2) NOT NULL,
    foto VARCHAR(255) NULL,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE
);
```

---

## 💻 **IMPLEMENTAÇÃO DETALHADA**

### **1. Classe MercadoLivrePayment**

**Arquivo:** `api/mercadolivre/MercadoLivrePayment.php`

#### **Funcionalidades Principais:**
- **Criar pagamento** no Mercado Livre
- **Consultar status** de pagamentos
- **Processar reembolsos**
- **Validação** de dados de entrada
- **Integração** com banco de dados

#### **Métodos Principais:**
```php
public function criarPagamento($dados_inscricao)
public function consultarStatus($payment_id)
public function processarReembolso($payment_id, $amount = null)
```

### **4. Tabela `pagamentos` (Oficial MovAmazon – Registro Financeiro)**

Será utilizada para consolidar o financeiro local da inscrição (foco desta fase). Campos principais já existem no `brunor90_movamazon.sql`:

- `inscricao_id`
- `forma_pagamento`
- `data_pagamento`
- `valor_total`
- `valor_desconto`
- `valor_pago`
- `taxa_participante`
- `valor_repasse`
- `status`

Nesta etapa, registraremos ao menos: `inscricao_id`, `forma_pagamento`, `data_pagamento`, `valor_total`, `valor_pago`, `status`. O cálculo de repasse ficará para uma fase posterior.

#### **Exemplo de Uso:**
```php
$ml_payment = new MercadoLivrePayment();
$resultado = $ml_payment->criarPagamento([
    'id' => 'insc_123',
    'modalidade_nome' => 'CORRIDA 10KM',
    'valor_total' => 149.50,
    'nome_participante' => 'João Silva',
    'email' => 'joao@email.com',
    'evento_nome' => 'III CORRIDA SAUIM DE COLEIRA'
]);
```

### **2. API create_payment.php**

**Arquivo:** `api/mercadolivre/create_payment.php`

#### **Funcionalidades:**
- **Receber dados** via POST JSON
- **Validar usuário** autenticado
- **Buscar dados** do usuário no banco
- **Criar pagamento** via MercadoLivrePayment
- **Salvar dados** na sessão

#### **Fluxo:**
1. Verificar método POST
2. Validar autenticação
3. Decodificar dados JSON
4. Buscar dados do usuário
5. Criar pagamento no ML
6. Retornar init_point para redirecionamento

Adaptação deste projeto:
- A criação de preferência usará `inscricoes.id` como `external_reference` (espelhando a lógica validada em `api/mercadoPago/api/preference.php`).
- Após a criação, salvar em `pagamentos_ml` (`preference_id`, `init_point`, `status='pendente'`) e atualizar `inscricoes.external_reference`.
- Opcionalmente, salvar um registro inicial em `pagamentos` com `status='pendente'` e valores totais previstos.

### **3. API webhook.php**

**Arquivo:** `api/mercadolivre/webhook.php`

#### **Funcionalidades:**
- **Receber notificações** do Mercado Livre
- **Processar atualizações** de status
- **Atualizar banco** de dados
- **Enviar emails** de confirmação

#### **Fluxo:**
1. Receber dados do webhook
2. Validar tipo de notificação
3. Consultar status no ML
4. Localizar a inscrição pelo `external_reference`
5. Atualizar `pagamentos_ml` (payment_id, status, valor_pago, metodo_pagamento, parcelas, taxa_ml, dados_pagamento)
6. Atualizar `inscricoes` (`status_pagamento`, `data_pagamento`, `forma_pagamento`, `parcelas`)
7. Registrar/atualizar em `pagamentos` os campos financeiros principais
8. Enviar email se aprovado (PHPMailer)

Observação: O endpoint será apontado para `api/mercadoPago/api/notification.php` adaptado para atualizar as tabelas do MovAmazon, mantendo a lógica robusta já validada.

### **4. Página de Pagamento**

**Arquivo:** `frontend/paginas/inscricao/pagamento.php`

#### **Funcionalidades:**
- **Exibir modalidades** selecionadas
- **Produtos extras** disponíveis
- **Seguro opcional** de inscrição
- **Resumo da compra** dinâmico
- **Integração** com Mercado Livre

#### **Interface:**
- **Layout responsivo** com Tailwind CSS
- **Cards de produtos** com imagens
- **Cálculo automático** de totais
- **Botão de pagamento** integrado
- **Formulário Mercado Pago renderizado dentro do mesmo bloco** (sem abrir nova página)
- Alternativamente, pode ser aberto em modal SweetAlert, mantendo a renderização na mesma etapa.

### **5. JavaScript de Pagamento**

**Arquivo:** `frontend/js/inscricao/pagamento.js`

#### **Classe PagamentoController:**
```javascript
class PagamentoController {
    constructor() {
        this.produtosExtrasSelecionados = [];
        this.seguroContratado = false;
        this.totalModalidades = 0;
        this.valorSeguro = 25.00;
    }
    
    // Métodos principais
    adicionarProdutoExtra(btn)
    removerProdutoExtra(produtoId)
    atualizarResumoCompra()
    iniciarPagamentoML()
    validarDados()
    prepararDadosPagamento()
}
```

#### **Funcionalidades:**
- **Gestão de produtos** extras
- **Cálculo dinâmico** de totais
- **Validação** de dados
- **Integração** com API ML
- **Feedback visual** para usuário
 - **Chamada PIX** dentro do container da etapa (reutilizando a lógica de `api/mercadoPago/api/pix.php`)

---

## 🔄 **FLUXO COMPLETO DE PAGAMENTO**

### **1. Início do Pagamento**
```
Usuário acessa /inscricao/pagamento.php
↓
Sistema carrega modalidades selecionadas
↓
Sistema carrega produtos extras disponíveis
↓
Usuário seleciona produtos extras e seguro
↓
Sistema calcula total automaticamente
```

### **2. Processamento do Pagamento**
```
Usuário clica "Pagar com Mercado Livre"
↓
JavaScript valida dados
↓
JavaScript chama create_payment.php
↓
API cria preferência no ML
↓
API retorna init_point
↓
Usuário é redirecionado para ML
```

Alternativa com BRICKS dentro da página:
```
Usuário clica "Finalizar Compra"
↓
Formulário Mercado Pago é exibido no mesmo bloco/modal
↓
Usuário preenche e envia
↓
Backend cria pagamento (POST /v1/payments)
↓
Exibe tela de status e aguarda webhook
```

### **3. Pagamento no Mercado Livre**
```
Usuário escolhe forma de pagamento
↓
Usuário preenche dados do cartão
↓
ML processa pagamento
↓
ML redireciona para sucesso.php
↓
ML envia webhook para webhook.php
```

### **4. Confirmação e Finalização**
```
Webhook atualiza status no banco
↓
Sistema envia email de confirmação
↓
Usuário vê página de sucesso
↓
Inscrição fica confirmada
```

---

## 🛡️ **SEGURANÇA E VALIDAÇÕES**

### **1. Validações Server-Side**
- **Autenticação** obrigatória
- **Sanitização** de dados de entrada
- **Validação** de tipos de dados
- **Verificação** de limites de valor

### **2. Segurança Mercado Livre**
- **Tokens** de acesso seguros
- **Webhooks** verificados
- **External reference** único
- **Logs** detalhados de transações
 - **X-Idempotency-Key** nas criações de pagamentos

### **3. Validações de Negócio**
- **Disponibilidade** de modalidades
- **Limites** de valor mínimo/máximo
- **Regras** de produtos extras
- **Validação** de cupons

---

## 📱 **INTERFACE E UX**

### **1. Design Responsivo**
- **Mobile-first** approach
- **Tailwind CSS** para estilização
- **Componentes** reutilizáveis
- **Animações** suaves

### **2. Feedback Visual**
- **Loading states** durante processamento
- **Mensagens** de erro claras
- **Confirmações** de ações
- **Progress indicators**

### **3. Acessibilidade**
- **Labels** descritivos
- **Contraste** adequado
- **Navegação** por teclado
- **Screen readers** compatíveis

Observação de idioma: Todo o conteúdo exibido ao usuário neste projeto será em Português (BR), mantendo consistência com as demais páginas.

---

## 🧪 **TESTES E VALIDAÇÃO**

### **1. Testes de Sandbox**
```bash
# Configurar ambiente de teste
ML_ENVIRONMENT=sandbox
ML_ACCESS_TOKEN=TEST-123456789
```

### **2. Cenários de Teste**
- **Pagamento aprovado** imediatamente
- **Pagamento pendente** (boleto)
- **Pagamento rejeitado** (cartão)
- **Timeout** de pagamento
- **Webhook** não recebido

### **3. Validações de Dados**
- **Valores** negativos ou zero
- **Emails** inválidos
- **Dados** de usuário incompletos
- **Modalidades** não selecionadas

---

## 📊 **MONITORAMENTO E LOGS**

### **1. Logs de Sistema**
```php
error_log("✅ Pagamento ML criado - Preference ID: " . $preference_id);
error_log("🔔 Webhook ML recebido: " . $input);
error_log("💥 Erro no webhook ML: " . $e->getMessage());
```

### **2. Métricas Importantes**
- **Taxa de conversão** por etapa
- **Tempo médio** de processamento
- **Erros** de pagamento
- **Webhooks** recebidos

### **3. Alertas**
- **Falhas** na integração ML
- **Webhooks** não processados
- **Pagamentos** pendentes há muito tempo
- **Erros** de validação

---

## 🚀 **DEPLOY E CONFIGURAÇÃO**

### **1. Configuração de Produção**
```env
ML_ENVIRONMENT=production
ML_ACCESS_TOKEN=APP-123456789
ML_AUTO_RETURN=https://movamazon.com/inscricao/sucesso
ML_NOTIFICATION_URL=https://movamazon.com/api/mercadolivre/webhook.php
```

### **2. Configuração de Webhooks**
- **URL:** `https://movamazon.com/api/mercadolivre/webhook.php`
- **Eventos:** `payment.created`, `payment.updated`
- **Método:** POST
- **Formato:** JSON

Observação: Para este projeto, o webhook será processado por `api/mercadoPago/api/notification.php` adaptado para atualizar as tabelas `pagamentos_ml`, `inscricoes` e `pagamentos` do MovAmazon.

### **3. URLs de Retorno**
- **Sucesso:** `https://movamazon.com/inscricao/sucesso?status=success`
- **Falha:** `https://movamazon.com/inscricao/sucesso?status=failure`
- **Pendente:** `https://movamazon.com/inscricao/sucesso?status=pending`

---

## 💰 **CUSTOS E TAXAS**

### **1. Mercado Livre**
- **Cartão de crédito:** 4.99% + R$ 0.40
- **PIX:** 1.99%
- **Boleto:** R$ 3.49
- **Parcelamento:** Sem taxa adicional

### **2. Exemplo de Cálculo**
```
Inscrição: R$ 149,50
Taxa ML (4.99%): R$ 7.46
Taxa fixa: R$ 0.40
Total ML: R$ 7.86
Valor líquido: R$ 141.64
```

---

## 🔧 **MANUTENÇÃO E SUPORTE**

### **1. Monitoramento Diário**
- **Logs** de erro
- **Webhooks** recebidos
- **Pagamentos** pendentes
- **Performance** da API

### **2. Atualizações**
- **SDK** do Mercado Livre
- **Dependências** PHP
- **Configurações** de segurança
- **Documentação** da API

### **3. Backup e Recuperação**
- **Backup** diário do banco
- **Logs** de transações
- **Configurações** de ambiente
- **Planos** de contingência

---

## 📋 **CHECKLIST DE IMPLEMENTAÇÃO**

### **✅ Configuração Inicial**
- [ ] Instalar dotenv via Composer
- [ ] Criar arquivo .env com credenciais
- [ ] Atualizar db.php para usar variáveis de ambiente
- [ ] Executar scripts SQL para criar tabelas

### **✅ APIs Implementadas**
- [ ] MercadoLivrePayment.php criado
- [ ] create_payment.php funcionando
- [ ] webhook.php configurado
- [ ] get_payment_status.php implementado
 - [ ] `api/mercadoPago/api/notification.php` adaptado para tabelas MovAmazon

### **✅ Frontend Implementado**
- [ ] Página de pagamento criada
- [ ] JavaScript de integração funcionando
- [ ] Páginas de retorno criadas
- [ ] Interface responsiva implementada

### **✅ Testes Realizados**
- [ ] Testes em sandbox do ML
- [ ] Validação de dados funcionando
- [ ] Webhooks recebidos corretamente
- [ ] Fluxo completo testado

### **✅ Deploy em Produção**
- [ ] Configurações de produção aplicadas
- [ ] Webhooks configurados no ML
- [ ] URLs de retorno configuradas
- [ ] Monitoramento ativo

---

## 🎯 **PRÓXIMOS PASSOS**

### **1. Configuração Imediata**
1. **Criar arquivo .env** com suas credenciais
2. **Executar script SQL** para criar tabelas
3. **Testar APIs** em ambiente de desenvolvimento
4. **Configurar webhook** no painel do ML

### **2. Testes e Validação**
1. **Testar fluxo completo** em sandbox
2. **Validar webhooks** funcionando
3. **Testar diferentes cenários** de pagamento
4. **Verificar logs** e monitoramento

### **3. Deploy em Produção**
1. **Configurar ambiente** de produção
2. **Atualizar URLs** de retorno
3. **Configurar webhooks** reais
4. **Monitorar** primeiras transações

---

## 📞 **SUPORTE E DOCUMENTAÇÃO**

### **1. Documentação Técnica**
- **API Reference** do Mercado Livre
- **Webhooks** documentation
- **SDK** e bibliotecas
- **Exemplos** de integração

### **2. Suporte**
- **Logs** detalhados para debug
- **Métricas** de performance
- **Alertas** automáticos
- **Documentação** de troubleshooting

### **3. Contatos**
- **Desenvolvedor:** [Seu Nome]
- **Email:** [seu@email.com]
- **Documentação:** [URL da documentação]
- **Repositório:** [URL do repositório]

---

## ✅ Decisões confirmadas desta tarefa

- Renderização dentro do bloco de `pagamento.php` (pode usar modal SweetAlert).
- Registro financeiro: usar `pagamentos` (oficial) e conciliar com `pagamentos_ml`.
- PIX: manter lógica comprovada da pasta `mercadoPago` dentro do mesmo container.
- Métodos de pagamento: sem restrições (crédito, débito, PIX), parcelas livres.
- Webhook: apontar para `notification.php` adaptado às tabelas do MovAmazon.
- Repasse: ficará para outra fase – foco agora é o financeiro básico.
- Idioma: padrão do projeto em Português (BR).
- Arquivos antigos: mantidos como referência, sem exclusões.

---

**📅 Atualizado em:** 16 de Setembro de 2025  
**🔧 Status:** Em andamento (documentação ajustada ao novo plano)  
**🎯 Objetivo:** Integração completa com foco financeiro e conciliação  
**👥 Responsáveis:** Assistente AI + Usuário
