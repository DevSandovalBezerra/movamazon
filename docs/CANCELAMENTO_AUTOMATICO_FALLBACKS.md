# Sistema de Cancelamento Automático - Fallbacks e Configuração

## 📋 Visão Geral

O sistema possui **múltiplos fallbacks** para garantir que inscrições expiradas sejam canceladas automaticamente, mesmo se o CRON não estiver funcionando.

## 🔄 Fallbacks Implementados

### 1. **CRON Job (Principal)**
- **Arquivo**: `api/cron/cancelar_inscricoes_expiradas.php`
- **Frequência recomendada**: A cada hora ou diariamente
- **Como configurar**: Ver seção "Configuração do CRON" abaixo

### 2. **Fallback 1: Consulta de Inscrição**
- **Arquivo**: `api/participante/get_inscricao.php`
- **Quando executa**: Sempre que um usuário consulta sua inscrição
- **Vantagem**: Execução automática e transparente

### 3. **Fallback 2: Geração de Pagamento**
- **Arquivos**: 
  - `api/inscricao/create_pix.php`
  - `api/inscricao/create_boleto.php`
- **Quando executa**: Antes de gerar novo pagamento PIX ou Boleto
- **Vantagem**: Garante que inscrições expiradas sejam canceladas antes de tentar pagar
- **Importante**: Este é o ponto crítico - verifica cancelamentos ANTES de gerar pagamento

### 4. **Fallback 3: Endpoint HTTP Manual**
- **Arquivo**: `api/cron/cancelar_inscricoes_expiradas_http.php`
- **Quando executa**: Quando chamado manualmente via HTTP
- **Uso**: Backup manual ou monitoramento externo
- **Exemplo**: `GET /api/cron/cancelar_inscricoes_expiradas_http.php?token=SEU_TOKEN`

## ⚙️ Configuração do CRON

### Linux/Unix (cPanel, VPS, etc.)

1. **Acessar crontab**:
   ```bash
   crontab -e
   ```

2. **Adicionar linha** (executar a cada hora):
   ```bash
   0 * * * * /usr/bin/php /caminho/completo/para/api/cron/cancelar_inscricoes_expiradas.php >> /caminho/logs/cancelar_inscricoes.log 2>&1
   ```

3. **Ou executar diariamente às 00:00**:
   ```bash
   0 0 * * * /usr/bin/php /caminho/completo/para/api/cron/cancelar_inscricoes_expiradas.php >> /caminho/logs/cancelar_inscricoes.log 2>&1
   ```

### Windows (Task Scheduler)

1. Abrir **Agendador de Tarefas**
2. Criar nova tarefa básica
3. Configurar:
   - **Nome**: Cancelar Inscrições Expiradas
   - **Gatilho**: Diariamente às 00:00
   - **Ação**: Iniciar programa
   - **Programa**: `C:\caminho\para\php.exe`
   - **Argumentos**: `C:\caminho\para\api\cron\cancelar_inscricoes_expiradas.php`

### Laragon (Windows - Desenvolvimento)

1. Abrir **Laragon** → **Menu** → **Tools** → **Quick add**
2. Criar arquivo `.bat`:
   ```batch
   @echo off
   cd C:\laragon\www\movamazon
   C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe api\cron\cancelar_inscricoes_expiradas.php
   ```
3. Agendar via Task Scheduler do Windows

## 🔒 Segurança do Endpoint HTTP

O endpoint `cancelar_inscricoes_expiradas_http.php` pode ser protegido de duas formas:

### Opção 1: Token (Recomendado)

1. Adicionar no `.env`:
   ```
   CANCELAR_INSCRICOES_TOKEN=seu_token_secreto_aqui
   ```

2. Chamar endpoint:
   ```
   GET /api/cron/cancelar_inscricoes_expiradas_http.php?token=seu_token_secreto_aqui
   ```

### Opção 2: IP Whitelist

Editar `api/cron/cancelar_inscricoes_expiradas_http.php` e descomentar a seção de IP whitelist, adicionando os IPs permitidos.

## 📊 Monitoramento

### Verificar se CRON está funcionando

1. **Verificar logs**:
   ```bash
   tail -f /caminho/logs/cancelar_inscricoes.log
   ```

2. **Verificar última execução**:
   ```sql
   SELECT * FROM inscricoes 
   WHERE status = 'cancelada' 
   ORDER BY data_inscricao DESC 
   LIMIT 10;
   ```

3. **Testar endpoint HTTP manualmente**:
   ```bash
   curl "https://seusite.com/api/cron/cancelar_inscricoes_expiradas_http.php?token=SEU_TOKEN"
   ```

### Identificar inscrições que devem ser canceladas

Execute a query SQL em `migrations/query_identificar_inscricoes_expiradas.sql` para ver quais inscrições devem ser canceladas.

## 🎯 Regras de Cancelamento

O sistema cancela automaticamente inscrições que atendam **qualquer uma** das condições:

1. **Boletos Expirados**:
   - `forma_pagamento = 'boleto'`
   - `status_pagamento = 'pendente'`
   - `data_expiracao_pagamento < NOW()`

2. **Pendentes por Mais de 72 Horas**:
   - `status_pagamento = 'pendente'`
   - `data_inscricao < NOW() - 72 HOURS`

3. **Após Data de Encerramento**:
   - `status_pagamento = 'pendente'`
   - `data_inscricao > evento.data_fim_inscricoes`

## ⚠️ Importante

- **Não cancela** inscrições com `status_pagamento = 'processando'` (PIX em andamento)
- **Não cancela** inscrições já pagas ou canceladas
- Todos os fallbacks executam **silenciosamente** (sem impacto na performance)
- O helper function garante que não haja duplicação de cancelamentos
- **Verificação acontece ANTES da geração do pagamento**, não no webhook (que processa notificações de pagamentos já gerados)

## 🛠️ Troubleshooting

### Como Verificar se o CRON Está Funcionando

#### 1. Executar Script de Diagnóstico Completo

```bash
php scripts/diagnosticar_cron.php
```

Este script verifica:
- ✅ Se os arquivos necessários existem
- ✅ Se o PHP está acessível
- ✅ Se o script executa manualmente
- ✅ Se há logs de execução
- ✅ Se o CRON está configurado
- ✅ Últimas execuções detectadas

#### 2. Verificar Execução Manual

```bash
# Testar se o script executa
php api/cron/cancelar_inscricoes_expiradas.php

# Verificar resultado
php scripts/verificar_cron.php
```

#### 3. Verificar Logs do CRON

**Linux/Unix:**
```bash
# Ver logs do sistema
tail -f /var/log/cron
tail -f /var/log/syslog | grep CRON

# Ver logs do PHP
tail -f /var/log/php_errors.log | grep CANCELAR_INSCRICOES

# Ver logs específicos (se configurado)
tail -f /caminho/logs/cancelar_inscricoes.log
```

**Windows:**
- Abrir Visualizador de Eventos
- Navegar para: Logs do Windows > Sistema
- Filtrar por "Agendador de Tarefas"

#### 4. Verificar Configuração do CRON

**Linux/Unix:**
```bash
# Ver crontab atual
crontab -l

# Verificar se há entrada para cancelamento
crontab -l | grep cancelar_inscricoes_expiradas
```

**Windows:**
- Abrir Agendador de Tarefas
- Verificar se há tarefa para cancelamento
- Verificar histórico de execução

#### 5. Verificar Última Execução no Banco

```sql
-- Ver inscrições canceladas recentemente
SELECT 
    COUNT(*) as total,
    MAX(updated_at) as ultima_atualizacao
FROM inscricoes
WHERE status = 'cancelada'
  AND status_pagamento = 'cancelado'
  AND updated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY);
```

### Problemas Comuns e Soluções

#### CRON não está executando

1. **Verificar caminho do PHP:**
   ```bash
   which php
   # ou
   where php
   ```

2. **Verificar permissões:**
   ```bash
   chmod +x api/cron/cancelar_inscricoes_expiradas.php
   ```

3. **Testar execução manual:**
   ```bash
   php api/cron/cancelar_inscricoes_expiradas.php
   ```

4. **Verificar logs do sistema:**
   - Linux: `/var/log/cron` ou `/var/log/syslog`
   - Verificar se há erros de execução

5. **Verificar se CRON está rodando:**
   ```bash
   # Linux
   systemctl status cron
   # ou
   service cron status
   ```

#### CRON configurado mas não executa

1. **Verificar sintaxe do crontab:**
   ```bash
   crontab -l
   ```
   - Verificar se não há espaços extras
   - Verificar se o caminho está completo e correto
   - Verificar se não está comentado (começando com #)

2. **Verificar variáveis de ambiente:**
   - O CRON pode não ter acesso às mesmas variáveis do shell
   - Usar caminhos absolutos no crontab

3. **Verificar permissões de escrita:**
   - Se o script escreve logs, verificar permissões da pasta

#### Fallbacks não estão funcionando

1. **Verificar se o helper está sendo carregado:**
   ```bash
   # Verificar se o arquivo existe
   ls -la api/helpers/cancelar_inscricoes_expiradas_helper.php
   ```

2. **Verificar logs de erro do PHP:**
   ```bash
   tail -f /var/log/php_errors.log
   ```

3. **Testar cada fallback individualmente:**
   - Testar geração de PIX
   - Testar consulta de inscrição
   - Verificar se cancelamento é executado

#### Inscrições não estão sendo canceladas

1. **Verificar critérios:**
   ```sql
   -- Ver inscrições que devem ser canceladas
   SELECT * FROM inscricoes
   WHERE status_pagamento = 'pendente'
     AND (
       (forma_pagamento = 'boleto' 
        AND data_expiracao_pagamento < NOW())
       OR
       (data_inscricao < DATE_SUB(NOW(), INTERVAL 72 HOUR))
     );
   ```

2. **Verificar se não estão em processamento:**
   ```sql
   SELECT * FROM inscricoes
   WHERE status_pagamento = 'processando';
   ```

3. **Executar manualmente para debug:**
   ```bash
   php api/cron/cancelar_inscricoes_expiradas.php
   ```

### Checklist de Diagnóstico

- [ ] Arquivo do CRON existe e é legível
- [ ] PHP está acessível no caminho configurado
- [ ] Script executa manualmente sem erros
- [ ] CRON está configurado no crontab
- [ ] Logs mostram execuções recentes
- [ ] Banco de dados mostra cancelamentos recentes
- [ ] Fallbacks estão funcionando (testar geração de PIX)
- [ ] Não há erros nos logs do PHP

### Comandos Úteis

```bash
# Diagnóstico completo
php scripts/diagnosticar_cron.php

# Verificar status atual
php scripts/verificar_cron.php

# Executar cancelamento manualmente
php api/cron/cancelar_inscricoes_expiradas.php

# Ver logs em tempo real
tail -f /var/log/php_errors.log | grep CANCELAR_INSCRICOES

# Verificar crontab
crontab -l

# Editar crontab
crontab -e
```
