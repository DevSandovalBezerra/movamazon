# Módulo Financeiro – Plataforma de Eventos de Corrida  
PRD + Plano Técnico Completo (Menu, Banco MySQL, Migrations, Funcionalidades, Endpoints)  
Versão: 1.0

Observação: alguns anexos antigos desta conversa podem ter expirado no ambiente. Se você quiser que eu valide algo além do dump atual, reenvie os anexos antigos. O plano abaixo não depende deles.

---

## 1) Visão geral (PRD)

### 1.1 Objetivo
Desenvolver a área financeira do organizador, com foco em:
- Clareza do dinheiro: quanto entrou, quanto está a liberar, quanto pode ser repassado agora, quanto já foi repassado.
- Rastreabilidade total: cada movimentação tem origem, status, datas e evidências (comprovante/webhook).
- Segurança operacional: regras de repasse e travas para evitar inconsistências.
- Boa experiência do usuário: cards e extrato consistentes, linguagem simples e ações guiadas.

### 1.2 Resultados esperados
- Organizador resolve o financeiro do evento sem suporte.
- Extrato auditável: nenhum “mistério”, cada item explica a origem.
- Repasse previsível: mostra janela de liberação, regras e taxa de solicitação.
- Fechamento com snapshot final do evento e relatórios.

### 1.3 Escopo (MVP e evolução)

#### MVP (primeira entrega)
- Visão Geral (cards + alertas)
- Extrato Financeiro (filtro + exportação + detalhes)
- Repasse (saldo disponível + agendar + histórico + contas bancárias)
- Receita (por período e forma de pagamento)
- Estorno (listagem + status + impacto no saldo)
- Chargeback (listagem + status + impacto no saldo)
- Fechamento (pré-fechamento + fechamento final)

#### Evolução (fase 2)
- Notas Fiscais (NFSe / upload manual / vínculo a repasse)
- Conciliação automática (API/arquivo gateway)
- Split por parceiros/assessorias/afiliados
- Multi-beneficiário por evento
- DRE simplificada por evento (receitas x custos)

### 1.4 Personas
- Organizador: precisa ver saldo e repassar de forma simples.
- Financeiro interno/backoffice: conciliação, bloqueios, ajustes e auditoria.
- Suporte: precisa rastrear caso por inscrição/pedido/pagamento rapidamente.

---

## 2) Requisitos de UX e experiência do usuário

### 2.1 Cards (semântica fixa)
Sempre manter a mesma semântica, em todos os eventos:
- Receita inscrições (bruta ou líquida, definido por regra do produto)
- Outras receitas
- Receita total do evento
- Já repassado
- Lançamentos futuros (a liberar)
- Débitos (estornos, chargebacks, ajustes)
- Saldo disponível para repasse
- (Opcional) Saldo produtos/fornecedores, se existir módulo de produtos

### 2.2 Extrato contábil amigável
Tabela com:
- Número (id do ledger / referência)
- Data
- Tipo
- Descritivo (texto humano)
- Crédito
- Débito
- Comprovante (link quando existir)
- Ações: Detalhes (drawer/modal) e Baixar (comprovante/export)

### 2.3 Ações guiadas
- Repasse: “Agendar repasse” com validações e regras visíveis (cutoff, D+1/D+2 etc.)
- Extrato: Detalhes mostra a cadeia (inscrição → pagamento → taxas → estorno/chargeback/repasse)
- Exportação: CSV/PDF do que estiver filtrado

### 2.4 Filtros em tudo que lista dados
- Período
- Status
- Tipo (origem)
- Forma de pagamento
- Busca por inscrição/pedido/referência externa

---

## 3) Arquitetura de Menu e rotas (Organizador)

Menu principal: Financeiro
1. Visão Geral  
2. Repasse  
   2.1 Agendar repasse  
   2.2 Histórico de repasses  
   2.3 Contas bancárias  
3. Extrato Financeiro  
4. Análise Financeira  
5. Receita  
6. Estorno  
7. Chargeback  
8. Fechamento  
9. Notas Fiscais (opcional, fase 2)

Rotas sugeridas no padrão de painel:
- /Evento/{evento_id}/Financeiro/VisaoGeral
- /Evento/{evento_id}/Financeiro/Repasse
- /Evento/{evento_id}/Financeiro/Extrato
- /Evento/{evento_id}/Financeiro/Analise
- /Evento/{evento_id}/Financeiro/Receita
- /Evento/{evento_id}/Financeiro/Estorno
- /Evento/{evento_id}/Financeiro/Chargeback
- /Evento/{evento_id}/Financeiro/Fechamento
- /Evento/{evento_id}/Financeiro/NotasFiscais (fase 2)

Backoffice (interno)
- /backoffice/financeiro/conciliação
- /backoffice/financeiro/ajustes
- /backoffice/financeiro/regras

---

## 4) Princípio central do banco: Ledger imutável

A fonte da verdade do financeiro deve ser um razão (ledger):
- Toda movimentação vira uma linha (crédito/débito) com status e origem.
- Não edita movimentação antiga: estorno/chargeback/ajuste cria novas linhas (reversão/compensação).
- Saldo é soma (com status e regras).

Benefícios:
- Auditável
- Conciliação mais fácil
- Relatórios consistentes
- “Sem divergência” entre telas

---

## 5) Modelo de dados (MySQL) – Tabelas novas

### 5.1 Tabelas
- financeiro_beneficiarios  
- financeiro_contas_bancarias  
- financeiro_ledger  
- financeiro_repasses  
- financeiro_estornos  
- financeiro_chargebacks  
- financeiro_fechamentos  
- financeiro_webhook_eventos (idempotência de webhooks)

Observação:
- Integra com suas tabelas existentes: eventos, organizadores, inscricoes, pagamentos_ml.

---

## 6) Migrations SQL (no estilo do seu projeto)

### 6.1 CREATE TABLE

```sql
-- =========================
-- FINANCEIRO (NOVAS TABELAS)
-- =========================

CREATE TABLE `financeiro_beneficiarios` (
  `id` int(11) NOT NULL,
  `organizador_id` int(11) NOT NULL,
  `tipo` enum('PF','PJ') COLLATE utf8mb4_unicode_ci NOT NULL,
  `nome` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `documento` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('ativo','inativo','pendente_validacao') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente_validacao',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `financeiro_contas_bancarias` (
  `id` int(11) NOT NULL,
  `beneficiario_id` int(11) NOT NULL,
  `banco_codigo` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `banco_nome` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `agencia` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `conta` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `conta_dv` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_conta` enum('corrente','poupanca','pagamento') COLLATE utf8mb4_unicode_ci NOT NULL,
  `titular_nome` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `titular_documento` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('ativa','inativa','pendente_validacao') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente_validacao',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `financeiro_ledger` (
  `id` bigint(20) NOT NULL,
  `evento_id` int(11) NOT NULL,

  `origem_tipo` enum('inscricao','pagamento','taxa','repasse','estorno','chargeback','ajuste_manual','produto','outro') COLLATE utf8mb4_unicode_ci NOT NULL,
  `origem_id` bigint(20) DEFAULT NULL,

  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direcao` enum('credito','debito') COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` decimal(10,2) NOT NULL DEFAULT 0.00,

  `status` enum('pendente','disponivel','liquidado','revertido','bloqueado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente',
  `ocorrido_em` datetime NOT NULL,
  `disponivel_em` datetime DEFAULT NULL,

  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `financeiro_repasses` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `beneficiario_id` int(11) NOT NULL,
  `conta_bancaria_id` int(11) NOT NULL,

  `valor_solicitado` decimal(10,2) NOT NULL,
  `valor_taxa_repasse` decimal(10,2) NOT NULL DEFAULT 0.00,
  `valor_liquido` decimal(10,2) NOT NULL,

  `status` enum('criado','agendado','processando','pago','falhou','cancelado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'criado',
  `agendado_para` date DEFAULT NULL,
  `solicitado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `processado_em` datetime DEFAULT NULL,

  `comprovante_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gateway_transfer_id` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `motivo_falha` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `financeiro_estornos` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `inscricao_id` int(11) DEFAULT NULL,
  `pagamento_ml_id` int(11) DEFAULT NULL,

  `valor` decimal(10,2) NOT NULL,
  `motivo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('solicitado','em_processamento','concluido','negado','falhou') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'solicitado',
  `solicitado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `concluido_em` datetime DEFAULT NULL,

  `gateway_refund_id` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `raw_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `financeiro_chargebacks` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `inscricao_id` int(11) DEFAULT NULL,
  `pagamento_ml_id` int(11) DEFAULT NULL,

  `valor` decimal(10,2) NOT NULL,
  `status` enum('aberto','em_disputa','ganho','perdido','cancelado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aberto',
  `aberto_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `encerrado_em` datetime DEFAULT NULL,

  `motivo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prazo_resposta` date DEFAULT NULL,
  `evidencias_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `raw_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `financeiro_fechamentos` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `fechado_por` int(11) DEFAULT NULL,
  `fechado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,

  `receita_bruta` decimal(10,2) NOT NULL DEFAULT 0.00,
  `taxas` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estornos` decimal(10,2) NOT NULL DEFAULT 0.00,
  `chargebacks` decimal(10,2) NOT NULL DEFAULT 0.00,
  `repasses` decimal(10,2) NOT NULL DEFAULT 0.00,
  `saldo_final` decimal(10,2) NOT NULL DEFAULT 0.00,

  `snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `financeiro_webhook_eventos` (
  `id` bigint(20) NOT NULL,
  `gateway` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event_id` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_id` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 6.2 Índices, AUTO_INCREMENT e FKs
Incluídos nas seções 6.2, 6.3 e 6.4 acima.

---

## 9) Integrações e melhores práticas
- Webhooks idempotentes (financeiro_webhook_eventos)
- DECIMAL (nunca float)
- Transações em ações sensíveis
- Logs/auditoria para repasse/ajustes
- Índices para consultas principais
- Permissões por perfil

---

## 10) Roadmap
Fase 0: base técnica  
Fase 1: MVP UX  
Fase 2: riscos e fechamento  
Fase 3: conciliação e NF

---

## 11) Implementação PHP (seguindo padrão atual)
Arquivos sugeridos e contratos do service detalhados na seção 11 do plano.

---

## 12) Checklist final
- Cards batendo com ledger
- Extrato exportando filtro exato
- Repasse impedindo exceder saldo
- Webhook sem duplicar
- Fechamento com snapshot consistente
- Índices cobrindo consultas
