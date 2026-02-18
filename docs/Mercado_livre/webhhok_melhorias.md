# Melhorias de Performance e Segurança — Webhook Mercado Pago

Este documento descreve melhorias recomendadas para um webhook de pagamentos em produção, focando em:

- Performance
- Segurança
- Validação de assinatura
- Resiliência contra duplicação e ataques

---

## 1. Melhorias de Performance

### 1.1 Substituir fila em JSON por fila persistente

Problema atual:

- Arquivo JSON sofre race condition
- Pode corromper com múltiplos acessos
- Crescimento infinito
- Lock de arquivo

### Solução recomendada: fila em banco

```sql
CREATE TABLE webhook_queue (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  payment_id VARCHAR(50),
  payload JSON,
  status ENUM('pending','processing','done','error') DEFAULT 'pending',
  created_at DATETIME DEFAULT NOW(),
  INDEX(payment_id),
  INDEX(status)
);
