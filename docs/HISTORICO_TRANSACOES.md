# 📊 Histórico de Transações do Mercado Pago

**Data:** 30/01/2026  
**Funcionalidade:** Consulta completa de transações (bem-sucedidas e falhadas) diretamente da API do Mercado Pago

---

## 🎯 Objetivo

Permitir que organizadores consultem **todo o histórico de transações** do Mercado Pago, incluindo:
- ✅ Transações aprovadas
- ❌ Transações rejeitadas
- ⏳ Transações pendentes
- 🔄 Transações em processamento
- 🚫 Transações canceladas
- 💸 Transações reembolsadas

---

## 📁 Arquivos Criados

| Arquivo | Função |
|---------|--------|
| `api/organizador/transacoes/historico_mercadopago.php` | Backend - API que consulta Mercado Pago |
| `frontend/paginas/organizador/transacoes_historico.html` | Frontend - Interface de visualização |

---

## 🔍 Endpoint da API do Mercado Pago

### URL
```
GET https://api.mercadopago.com/v1/payments/search
```

### Documentação Oficial
https://www.mercadopago.com.br/developers/en/reference/payments/_payments_search/get

### Parâmetros Disponíveis

| Parâmetro | Tipo | Descrição | Exemplo |
|-----------|------|-----------|---------|
| `status` | string | Filtrar por status | `approved`, `rejected`, `pending` |
| `begin_date` | datetime | Data início | `2026-01-01T00:00:00.000-00:00` |
| `end_date` | datetime | Data fim | `2026-01-31T23:59:59.999-00:00` |
| `external_reference` | string | Referência externa | `INSCRIÇÃO_123` |
| `limit` | integer | Resultados por página | `100` (max) |
| `offset` | integer | Deslocamento (paginação) | `0`, `100`, `200` |
| `sort` | string | Campo de ordenação | `date_created`, `date_approved` |
| `criteria` | string | Ordem | `asc`, `desc` |

---

## 🚀 Como Usar

### 1. Acessar a Página

```
https://www.movamazon.com.br/frontend/paginas/organizador/transacoes_historico.html
```

### 2. Aplicar Filtros

**Filtros Disponíveis:**
- **Status:** Todos, Aprovado, Rejeitado, Pendente, etc.
- **Data Início:** Data de início do período
- **Data Fim:** Data de fim do período
- **Referência:** Código da inscrição (ex: INSCRIÇÃO_123)

**Exemplo de Uso:**
1. Selecione "Status: Rejeitado"
2. Data Início: 01/01/2026
3. Data Fim: 31/01/2026
4. Clique em "Buscar"

### 3. Visualizar Resultados

**Informações Exibidas:**
- Data da transação
- Payment ID
- Status com cores
- Referência da inscrição
- Nome do participante
- Nome do evento
- Método de pagamento
- Valor da transação

### 4. Ver Detalhes

Clique em qualquer linha para ver detalhes completos:
- Informações completas da transação
- Dados do comprador
- Dados da inscrição vinculada
- Histórico de datas

### 5. Exportar Dados

Clique em "Exportar CSV" para baixar um relatório em formato CSV.

---

## 📊 Estatísticas

O sistema exibe automaticamente:

| Card | Informação |
|------|------------|
| **Aprovados** | Total em R$ + quantidade |
| **Rejeitados** | Total em R$ + quantidade |
| **Pendentes** | Total em R$ + quantidade |
| **Taxa Aprovação** | Percentual de aprovação |

---

## 🔧 Configuração

### Pré-requisitos

1. **Access Token do Mercado Pago** configurado no `.env`:
```env
APP_Acess_token=SEU_TOKEN_DE_PRODUCAO
```

2. **Pasta criada:**
```bash
mkdir -p api/organizador/transacoes
chmod 755 api/organizador/transacoes
```

### Upload dos Arquivos

```
api/organizador/transacoes/historico_mercadopago.php
frontend/paginas/organizador/transacoes_historico.html
```

---

## 📡 Estrutura da API

### Request

```http
GET /api/organizador/transacoes/historico_mercadopago.php?status=rejected&begin_date=2026-01-01&end_date=2026-01-31&limit=50&offset=0
```

### Response

```json
{
  "success": true,
  "filtros_aplicados": {
    "status": "rejected",
    "begin_date": "2026-01-01",
    "end_date": "2026-01-31",
    "limit": 50,
    "offset": 0
  },
  "paginacao": {
    "total": 150,
    "limit": 50,
    "offset": 0,
    "has_next": true,
    "has_prev": false
  },
  "estatisticas": {
    "total_transacoes": 50,
    "por_status": {
      "rejected": {
        "count": 50,
        "valor_total": 7500.00
      }
    },
    "valor_total_aprovado": 0,
    "valor_total_rejeitado": 7500.00,
    "valor_total_pendente": 0,
    "taxa_aprovacao": 0
  },
  "transacoes": [
    {
      "payment_id": 1234567890,
      "status": "rejected",
      "status_detail": "cc_rejected_bad_filled_card_number",
      "external_reference": "INSCRIÇÃO_123",
      "transaction_amount": 150.00,
      "payment_method_id": "pix",
      "date_created": "2026-01-15T10:30:00.000-00:00",
      "payer": {
        "email": "usuario@exemplo.com",
        "first_name": "João",
        "last_name": "Silva"
      },
      "inscricao": {
        "id": 123,
        "usuario_nome": "João Silva",
        "usuario_email": "usuario@exemplo.com",
        "evento_nome": "MovAmazonas 2026",
        "valor_total": 150.00
      },
      "status_traduzido": "Rejeitado",
      "status_cor": "danger"
    }
  ]
}
```

---

## 🎨 Interface

### Dashboard

- **4 Cards de Estatísticas** com hover animado
- **Gráficos visuais** de status
- **Cores intuitivas:**
  - Verde: Aprovado
  - Vermelho: Rejeitado
  - Amarelo: Pendente
  - Azul: Em Processamento

### Tabela

- **Responsiva** (funciona em mobile)
- **Hover effect** nas linhas
- **Paginação automática** (50 registros por página)
- **Loading overlay** durante consultas

### Filtros

- **Design moderno** com gradiente roxo
- **Campos intuitivos** com ícones
- **Validação automática** de datas

---

## 🔍 Casos de Uso

### 1. Investigar Pagamentos Rejeitados

**Objetivo:** Entender por que pagamentos estão falhando

**Passos:**
1. Filtrar por "Status: Rejeitado"
2. Últimos 7 dias
3. Analisar `status_detail` de cada transação
4. Identificar padrões (ex: muitos `cc_rejected_bad_filled_card_number`)

**Ação:** Melhorar validação de formulário

### 2. Reconciliar Pagamentos

**Objetivo:** Confirmar que todos os pagamentos aprovados estão no sistema

**Passos:**
1. Filtrar por "Status: Aprovado"
2. Período específico (ex: mês de Janeiro)
3. Exportar CSV
4. Comparar com relatório local

**Ação:** Identificar pagamentos não sincronizados

### 3. Analisar Taxa de Aprovação

**Objetivo:** Medir qualidade das transações

**Passos:**
1. Filtrar por período (ex: último mês)
2. Ver card "Taxa Aprovação"
3. Se < 80%, investigar rejeitados

**Ação:** Otimizar processo de pagamento

### 4. Suporte ao Cliente

**Objetivo:** Cliente reclama que pagou mas não recebeu confirmação

**Passos:**
1. Buscar por "Referência: INSCRIÇÃO_[ID]"
2. Verificar status real no Mercado Pago
3. Ver `status_detail` para entender problema

**Ação:** Resolver caso específico

---

## ⚠️ Limitações Conhecidas

### 1. Limite da API

- **Máximo 1000 resultados** por consulta
- Se houver mais, usar paginação (`offset`)

### 2. Filtro de Data

- Alguns relatos indicam que `begin_date` e `end_date` podem ter inconsistências
- Recomenda-se validar resultados manualmente

### 3. Delay de Dados

- API pode ter delay de até 2 minutos
- Transações muito recentes podem não aparecer imediatamente

### 4. Performance

- Consultas com muitos resultados podem demorar
- Recomenda-se usar filtros de data para limitar escopo

---

## 🛠️ Troubleshooting

### Problema 1: "Erro ao consultar Mercado Pago"

**Causa:** Token inválido ou expirado

**Solução:**
1. Verificar `.env`: `APP_Acess_token`
2. Confirmar que é token de **PRODUÇÃO**
3. Regenerar token no DevCenter se necessário

### Problema 2: Nenhuma transação retornada

**Possíveis causas:**
- Filtros muito restritivos
- Período sem transações
- Token de teste (não retorna transações de produção)

**Solução:**
1. Limpar todos os filtros
2. Ampliar período de datas
3. Confirmar ambiente de produção

### Problema 3: Transações sem inscrição vinculada

**Causa:** `external_reference` não bate com banco local

**Solução:**
1. Verificar formato: `INSCRIÇÃO_[ID]`
2. Confirmar que inscrição existe no banco
3. Atualizar `external_reference` se necessário

### Problema 4: Loading infinito

**Causa:** Erro de rede ou CORS

**Solução:**
1. Abrir Console do navegador (F12)
2. Ver erro específico
3. Verificar se API está acessível
4. Conferir permissões CORS no servidor

---

## 📈 Métricas de Sucesso

Após implementação, você deve ser capaz de:

- ✅ Ver **todas as transações** (aprovadas e rejeitadas)
- ✅ Filtrar por **status** com 1 clique
- ✅ Identificar **padrões de rejeição**
- ✅ **Reconciliar pagamentos** com banco local
- ✅ **Exportar relatórios** em CSV
- ✅ **Responder clientes** com informações precisas
- ✅ **Melhorar taxa de aprovação** com dados concretos

---

## 🔗 Integrações

Esta funcionalidade se complementa com:

- **`api/mercadolivre/webhook.php`** - Recebe notificações automáticas
- **`api/diagnostico/verificar_missed_feeds.php`** - Verifica notificações perdidas
- **`api/organizador/pagamentos/list.php`** - Lista pagamentos locais

---

## 📝 Exemplo de Consulta via cURL

```bash
# Buscar transações rejeitadas nos últimos 7 dias
curl -X GET "https://api.mercadopago.com/v1/payments/search?access_token=SEU_TOKEN&status=rejected&begin_date=NOW-7DAYS&end_date=NOW&limit=100"

# Buscar por referência específica
curl -X GET "https://api.mercadopago.com/v1/payments/search?access_token=SEU_TOKEN&external_reference=INSCRIÇÃO_123"

# Buscar aprovados com paginação
curl -X GET "https://api.mercadopago.com/v1/payments/search?access_token=SEU_TOKEN&status=approved&limit=100&offset=0"
```

---

## 🆘 Suporte

**Documentação Oficial:**
- https://www.mercadopago.com.br/developers/pt/reference/payments/_payments_search/get
- https://www.mercadopago.com.br/developers/pt/docs

**Comunidade:**
- Discord: Mercado Pago Developers
- Stack Overflow: Tag `mercadopago`

---

**Última atualização:** 30/01/2026  
**Versão:** 1.0  
**Status:** ✅ Pronto para produção
