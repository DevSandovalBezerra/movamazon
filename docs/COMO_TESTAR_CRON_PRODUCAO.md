# Como Testar se o CRON Está Funcionando em Produção

## 🎯 Objetivo

Criar um teste prático que prove se o CRON está realmente executando automaticamente, não apenas se está configurado.

## 📋 Método: Teste com Inscrição Falsa

### Passo 1: Criar Inscrição de Teste

Execute o script que cria uma inscrição de teste que será cancelada automaticamente:

```bash
php scripts/testar_cron_producao.php
```

**O que o script faz:**
- Cria uma inscrição com data de 73 horas atrás
- Esta inscrição será cancelada automaticamente pela regra de 72 horas
- Registra o ID da inscrição de teste

### Passo 2: Monitorar o Teste

Após criar o teste, você pode monitorar de duas formas:

#### Opção A: Aguardar Execução Automática do CRON

1. **Aguardar próxima execução do CRON** (geralmente às 02:00)
2. **Após a execução**, rode:
   ```bash
   php scripts/monitorar_teste_cron.php
   ```
3. **Se a inscrição foi cancelada**, o CRON está funcionando!

#### Opção B: Forçar Execução Manual (para teste rápido)

1. **Forçar execução do script de cancelamento:**
   ```bash
   php api/cron/cancelar_inscricoes_expiradas.php
   ```

2. **Verificar se cancelou:**
   ```bash
   php scripts/monitorar_teste_cron.php
   ```

3. **Verificar log de execuções:**
   ```bash
   cat logs/cron_execucoes.log | tail -1
   ```

   - Se mostrar `"tipo": "CRON_AUTOMATICO"` → Foi execução automática (via CRON)
   - Se mostrar `"tipo": "MANUAL"` → Foi execução manual

### Passo 3: Interpretar Resultados

#### ✅ CRON Funcionando:
- Inscrição foi cancelada automaticamente
- Log mostra execução com `"tipo": "CRON_AUTOMATICO"`
- `"request_method": "CLI"`
- `"sapi": "cli"`

#### ❌ CRON Não Funcionando:
- Inscrição não foi cancelada após execução agendada
- Log mostra apenas execuções `"tipo": "MANUAL"`
- Última execução automática há muito tempo

## 🔍 Verificação Detalhada

### Verificar Log de Execuções

```bash
cat logs/cron_execucoes.log | tail -10
```

Cada linha é um JSON. Procure por:
- `"tipo": "CRON_AUTOMATICO"` → Execução automática
- `"tipo": "MANUAL"` → Execução manual
- `"request_method": "CLI"` → Via linha de comando (CRON)
- `"sapi": "cli"` → PHP CLI (CRON)

### Verificar Status da Inscrição de Teste

```sql
SELECT 
    id,
    numero_inscricao,
    data_inscricao,
    status,
    status_pagamento,
    TIMESTAMPDIFF(HOUR, data_inscricao, NOW()) as horas_pendente
FROM inscricoes
WHERE numero_inscricao LIKE 'TESTE_CRON_%'
ORDER BY id DESC
LIMIT 1;
```

## ⚡ Teste Rápido (Acelerado)

Para testar sem esperar 72 horas:

1. **Criar teste:**
   ```bash
   php scripts/testar_cron_producao.php
   ```

2. **Ajustar data para 73 horas atrás:**
   ```sql
   UPDATE inscricoes 
   SET data_inscricao = DATE_SUB(NOW(), INTERVAL 73 HOUR)
   WHERE numero_inscricao LIKE 'TESTE_CRON_%' 
     AND status = 'pendente';
   ```

3. **Executar cancelamento:**
   ```bash
   php api/cron/cancelar_inscricoes_expiradas.php
   ```

4. **Verificar:**
   ```bash
   php scripts/monitorar_teste_cron.php
   ```

5. **Verificar log:**
   ```bash
   cat logs/cron_execucoes.log | tail -1
   ```

## 🧹 Limpeza

Após o teste, limpar inscrições de teste:

```sql
-- Ver testes
SELECT * FROM inscricoes WHERE numero_inscricao LIKE 'TESTE_CRON_%';

-- Limpar testes cancelados
DELETE FROM inscricoes 
WHERE numero_inscricao LIKE 'TESTE_CRON_%' 
  AND status = 'cancelada';
```

## 📊 Checklist de Verificação

- [ ] Inscrição de teste criada com sucesso
- [ ] Inscrição tem mais de 72 horas pendente
- [ ] CRON executou (verificar log)
- [ ] Inscrição foi cancelada automaticamente
- [ ] Log mostra execução automática (não manual)
- [ ] Próxima execução do CRON cancelará automaticamente

## ⚠️ Importante

- **Testes não afetam dados reais** - são marcados com `TESTE_CRON_`
- **Limpe testes após verificação** para manter banco limpo
- **CRON executa em horário agendado** - pode levar até 24h para testar completamente
- **Use teste acelerado** para verificação rápida

## 🎯 Resultado Esperado

Se o CRON está funcionando:
1. ✅ Inscrição de teste é criada
2. ✅ CRON executa no horário agendado
3. ✅ Inscrição é cancelada automaticamente
4. ✅ Log mostra execução automática
5. ✅ Script de monitoramento confirma cancelamento

Se o CRON não está funcionando:
1. ✅ Inscrição de teste é criada
2. ❌ CRON não executa no horário agendado
3. ❌ Inscrição permanece pendente
4. ❌ Log não mostra execuções automáticas
5. ⚠️ Apenas fallbacks ou execução manual cancelam
