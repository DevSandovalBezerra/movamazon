# Como Testar se o CRON Está Funcionando em Produção

## 🎯 Objetivo

Verificar se o CRON está realmente executando automaticamente, não apenas se está configurado.

## 📋 Método 1: Verificar Log de Execuções

### Passo 1: Executar o script de verificação

```bash
php scripts/verificar_execucao_cron.php
```

Este script verifica:
- ✅ Se há execuções registradas
- ✅ Se foram automáticas (CRON) ou manuais
- ✅ Quando foi a última execução automática
- ✅ Se está dentro do esperado

### Passo 2: Interpretar os resultados

**✅ CRON Funcionando:**
- Mostra execuções com `tipo: CRON_AUTOMATICO`
- `request_method: CLI`
- `sapi: cli`
- Última execução automática recente

**❌ CRON Não Funcionando:**
- Mostra apenas execuções `tipo: MANUAL`
- `request_method: GET` ou `POST`
- Última execução automática há muito tempo ou não existe

## 📋 Método 2: Verificar Log Direto

### Verificar arquivo de log de execuções

```bash
cat logs/cron_execucoes.log | tail -5
```

Cada linha é um JSON com informações da execução. Procure por:
- `"tipo": "CRON_AUTOMATICO"` - execução automática
- `"tipo": "MANUAL"` - execução manual
- `"request_method": "CLI"` - execução via linha de comando (CRON)
- `"sapi": "cli"` - PHP CLI (CRON)

### Exemplo de saída:

```json
{"timestamp":"2026-02-04 02:00:15","tipo":"CRON_AUTOMATICO","sapi":"cli","usuario":"brunor90","request_method":"CLI","user_agent":"CRON","remote_addr":"localhost"}
```

## 📋 Método 3: Teste Controlado

### Passo 1: Limpar log anterior (opcional)

```bash
> logs/cron_execucoes.log
```

### Passo 2: Aguardar próxima execução do CRON

Se o CRON está configurado para executar às 02:00, aguarde até esse horário.

### Passo 3: Verificar após o horário agendado

```bash
php scripts/verificar_execucao_cron.php
```

Se aparecer uma execução automática no horário agendado, o CRON está funcionando.

## 📋 Método 4: Verificar Logs do Sistema

### Linux/Unix (se tiver acesso)

```bash
# Ver logs do CRON
grep cancelar_inscricoes /var/log/cron
# ou
grep cancelar_inscricoes /var/log/syslog

# Ver log específico (se configurado no crontab)
tail -f /var/log/movamazon/cancelar_inscricoes.log
```

### cPanel (hospedagem compartilhada)

1. Acesse **cPanel** → **Cron Jobs**
2. Verifique se o job está ativo
3. Clique em **"View Log"** para ver histórico de execuções

## 📋 Método 5: Teste com Execução Imediata

### Criar CRON de teste (executar em 2 minutos)

```bash
crontab -e
```

Adicionar linha de teste:
```
*/2 * * * * /usr/bin/php /caminho/completo/api/cron/cancelar_inscricoes_expiradas.php >> /tmp/teste_cron.log 2>&1
```

Aguardar 2 minutos e verificar:
```bash
php scripts/verificar_execucao_cron.php
cat /tmp/teste_cron.log
```

**⚠️ IMPORTANTE:** Remover a linha de teste após confirmar!

## 🔍 Diferenças entre Execução Automática e Manual

| Característica | CRON (Automático) | Manual (HTTP/CLI) |
|----------------|-------------------|-------------------|
| `php_sapi_name()` | `cli` | `apache2handler` ou `fpm-fcgi` |
| `REQUEST_METHOD` | Não existe ou vazio | `GET`, `POST`, etc. |
| `HTTP_USER_AGENT` | Não existe | Navegador/curl |
| `REMOTE_ADDR` | `localhost` ou não existe | IP do cliente |
| Tipo no log | `CRON_AUTOMATICO` | `MANUAL` |

## ⚠️ Problemas Comuns

### CRON configurado mas não executa

**Possíveis causas:**
1. Caminho do PHP incorreto no crontab
2. Permissões insuficientes
3. Variáveis de ambiente diferentes
4. CRON desabilitado no servidor

**Solução:**
```bash
# Verificar caminho do PHP usado pelo CRON
which php

# Testar execução direta
/usr/bin/php /caminho/completo/api/cron/cancelar_inscricoes_expiradas.php

# Verificar permissões
chmod +x api/cron/cancelar_inscricoes_expiradas.php
```

### Log mostra apenas execuções manuais

**Significa:** CRON não está executando automaticamente.

**Soluções:**
1. Verificar configuração do crontab: `crontab -l`
2. Verificar se CRON está rodando: `systemctl status cron`
3. Verificar logs do sistema: `/var/log/cron`
4. Contatar suporte da hospedagem se necessário

## ✅ Checklist de Verificação

- [ ] Script de verificação executa sem erros
- [ ] Log de execuções existe e tem conteúdo
- [ ] Há execuções com `tipo: CRON_AUTOMATICO`
- [ ] Última execução automática foi recente (dentro do esperado)
- [ ] CRON está configurado no crontab
- [ ] Caminho do PHP está correto
- [ ] Permissões estão corretas

## 📞 Se o CRON Não Está Funcionando

1. **Verificar configuração:**
   ```bash
   crontab -l
   ```

2. **Testar execução manual:**
   ```bash
   php api/cron/cancelar_inscricoes_expiradas.php
   ```

3. **Verificar logs de erro:**
   ```bash
   tail -f logs/php_errors.log
   ```

4. **Contatar suporte da hospedagem** se necessário

5. **Usar fallbacks:** Lembre-se que o sistema tem fallbacks que executam automaticamente mesmo sem CRON!
