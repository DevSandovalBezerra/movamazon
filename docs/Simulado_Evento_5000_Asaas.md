# Simulado Financeiro e Operacional — Evento com 5.000 inscritos (Asaas)

Data de referência: 2026-02-13  
Formato: visão para sócios, dev e contador  
Cenário: plataforma de corridas com split (comissão + repasse + retenção)

---

## 1) Parâmetros do simulado

- Inscritos: 5,000  
- Preço por inscrição: R$ 129.00  
- Receita bruta (inscrições): R$ 645,000.00

Regras do negócio:
- Comissão da plataforma: 7%
- Parte do organizador: 93%
- Retenção (garantia): 30% da parte do organizador (até fechamento do evento)
- Liberação imediata ao organizador: 70% da parte do organizador

Premissas de meios de pagamento (editáveis):
- Mix Pix: 70% (3500 pagamentos)
- Mix Cartão (à vista): 30% (1500 pagamentos)

Premissas de taxas Asaas (pós-período promocional, para simulação):
- Pix: R$ 1.99 por transação  
- Cartão: 2.99% + R$ 0.49 por transação

---

## 2) Resultado financeiro (sem estornos/chargeback)

### 2.1 Totais por parte (sobre o bruto)

- Comissão (plataforma): R$ 45,150.00
- Organizador (bruto): R$ 599,850.00

Retenção do organizador:
- Retido (garantia): R$ 179,955.00
- Liberado imediato (organizador): R$ 419,895.00

### 2.2 Taxas do meio de pagamento (estimativa)

- Taxas Pix: R$ 6,965.00
- Taxas Cartão: R$ 6,520.65
- Total taxas: R$ 13,485.65
- Custo médio por atleta: R$ 2.70

---

## 3) Quem paga as taxas? (impacto contábil)

### Cenário A — Organizador paga as taxas (recomendado no MVP)

- Receita da plataforma (comissão): R$ 45,150.00
- Organizador líquido (após taxas): R$ 586,364.35

Se a retenção incidir sobre o **líquido do organizador** (recomendado para conciliação):
- Retido (30% do líquido): R$ 175,909.30
- Liberado imediato (70% do líquido): R$ 410,455.04

### Cenário B — Plataforma paga as taxas (mais “marketável”, pior margem)

- Comissão bruta: R$ 45,150.00
- Menos taxas: R$ 13,485.65
- Resultado plataforma antes impostos/custos: R$ 31,664.35

---

## 4) Regras operacionais (cancelamento, estorno e disputa)

> Objetivo: proteger organizador e plataforma, evitar prejuízo por chargeback e manter conciliação clara.

### 4.1 Definições
- Cancelamento: pedido do atleta antes do evento, dentro da política
- Estorno (refund): devolução do valor ao atleta (Pix/cartão)
- Chargeback: contestação no cartão (pós-evento também pode ocorrer)
- Fechamento do evento: data/hora definida (ex.: D+2 após o término do evento) em que a garantia é liberada

### 4.2 Política sugerida de cancelamento (exemplo)
1) Até 7 dias antes do evento: reembolso de 100% do valor pago  
2) De 6 dias até a véspera: reembolso de 80% (retenção de taxa administrativa)  
3) No dia do evento / após: sem reembolso (salvo exceções previstas)

Observação:
- Se a inscrição envolve “kit” (camiseta, chip etc.), a política pode prever desconto adicional após produção/entrega.

### 4.3 Como tratar o estorno no split (recomendação técnica/contábil)
Ao estornar:
- Registrar no ledger um lançamento **negativo** do bruto
- Registrar a reversão das parcelas do split (plataforma, organizador liberado, organizador retido)
- Se a retenção já tiver sido liberada, o estorno entra como “ajuste” e pode gerar saldo negativo do organizador (precisa estar previsto em contrato)

### 4.4 Regras de responsabilidade (contrato com organizador)
- Chargeback e fraudes: responsabilidade primária do organizador (porque o serviço é dele), salvo erro comprovado da plataforma
- A plataforma pode reter/compensar valores futuros para cobrir chargebacks
- A garantia (30%) existe para cobrir:
  - cancelamentos tardios
  - estornos pós-evento autorizados
  - chargebacks do cartão que ocorram após o evento

### 4.5 Fechamento do evento e liberação da retenção
- O evento fecha em data objetiva (ex.: 48h após a realização)
- No fechamento:
  - calcular saldo retido do evento
  - subtrair estornos/chargebacks pendentes (se existirem)
  - transferir o saldo liberável ao organizador
- Se houver disputa aberta: reter parcialmente até conclusão da disputa (regra simples e conservadora)

---

## 5) Simulação com ocorrências (exemplo prático)

Premissas de ocorrências (editáveis):
- Cancelamentos antes do evento: 3.0% (150 atletas)  
- Chargeback no cartão: 0.50% dos pagamentos em cartão (8 casos)

### 5.1 Cancelamentos (impacto bruto)
- Valor bruto cancelado: R$ 19,350.00
- Distribuição aproximada:
  - Pix: 105 cancelamentos
  - Cartão: 45 cancelamentos

Tratamento:
- Estornar 100% (se dentro do prazo) → reverte comissão e repasse proporcional
- Se houver taxa administrativa (ex.: reembolsar 80%), a diferença vira receita (com nota fiscal) ou abatimento de custos conforme desenho jurídico

### 5.2 Chargeback (impacto bruto)
- Chargebacks estimados: 8
- Valor bruto em chargeback: R$ 1,032.00

Tratamento recomendado:
- Manter parte da garantia até a janela de risco do cartão (ou até concluir disputas)
- Se o chargeback confirmar:
  - registrar ajuste negativo no ledger
  - compensar do organizador (ou da garantia)

---

## 6) Checklist do contador (fechamento por evento)

Relatório de fechamento deve bater:
- Total bruto recebido (por método)
- Total estornado
- Total em disputa/chargeback (provisão)
- Total de taxas do gateway
- Comissão da plataforma (receita)
- Repasse ao organizador (passivo liquidado)
- Retenção (passivo a liberar)

Sugestão de contas (visão contábil):
- Receita: Comissão Plataforma (7%)
- Passivo: Valores a repassar ao organizador
- Passivo: Garantia a liberar (30% do organizador)
- Despesa (se plataforma pagar taxa): Taxas de processamento
- Ajustes: Chargeback/Estornos

---

## 7) Fórmulas rápidas (para ajustar o simulado)

- Bruto total = inscritos × preço  
- Comissão = bruto × 0,07  
- Organizador bruto = bruto − comissão  
- Retido = organizador × 0,30  
- Liberado = organizador × 0,70  

Taxas (com mix):
- Taxa Pix = qtd_pix × fee_pix  
- Taxa Cartão = (bruto_cartão × %cartão) + (qtd_cartão × fee_fixo_cartão)

---

## 8) Observações importantes (para contrato e PRD)

1) A retenção de 30% não é receita da plataforma: é obrigação a pagar ao organizador (passivo) até o fechamento.  
2) Defina explicitamente “o que é fechamento” e em quanto tempo a retenção é liberada.  
3) Preveja em contrato a compensação de chargebacks e estornos após o fechamento.  
4) Se vocês quiserem comissão exatamente 7% do bruto sempre, prefiram split por valor fixo calculado por transação (em centavos), não percentual.

Fim.
