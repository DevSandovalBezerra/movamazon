# Resumo da Estratégia Implementada para Redundância de Campos

## Problema Identificado

Campos com nomes idênticos existem em tabelas diferentes:
- `eventos.hora_inicio` vs `programacao_evento.hora_inicio`
- `eventos.local` vs `programacao_evento.local`

## Solução Implementada

### 1. Documentação no Banco de Dados ✅
**Arquivo**: `migrations/add_comentarios_campos_redundantes.sql`

**Status**: As colunas já existem (verificado em `programacao_evento.sql`)

Adiciona comentários SQL claros nas colunas para diferenciar propósitos:
- `eventos.hora_inicio`: "Horário geral de início do evento"
- `programacao_evento.hora_inicio`: "Horário específico de largada/atividade desta programação"

### 2. Validação nas APIs ✅

#### `api/organizador/eventos/update.php`
- **Validação**: Ignora campos de programação (`hora_fim`, `latitude`, `longitude`, `tipo`, `titulo`, `ordem`) se enviados acidentalmente
- **Log**: Registra tentativas de envio de campos incorretos
- **Log**: Registra quais campos estão sendo atualizados na tabela `eventos`

#### `api/organizador/programacao/create.php` e `update.php`
- **Validação**: Ignora campos de evento (`nome`, `categoria`, `data_inicio`, etc.) se enviados acidentalmente
- **Log**: Registra tentativas de envio de campos incorretos
- **Log**: Registra quais campos estão sendo inseridos/atualizados na tabela `programacao_evento`

### 3. Estratégia de Prevenção

#### Validação Proativa
- APIs verificam campos recebidos antes de processar
- Campos de contexto incorreto são ignorados e logados
- Não causa erro, apenas previne atualizações incorretas

#### Rastreabilidade
- Logs detalhados em cada operação de UPDATE/INSERT
- Identificação clara de qual tabela está sendo modificada
- Registro de quais campos estão sendo alterados

## Como Funciona

### Cenário 1: Update de Evento
```json
POST /api/organizador/eventos/update.php
{
  "id": 8,
  "hora_inicio": "06:00:00",  // ✅ Válido - atualiza eventos.hora_inicio
  "local": "Manaus - AM",      // ✅ Válido - atualiza eventos.local
  "hora_fim": "08:00:00"       // ⚠️ Ignorado - campo de programação
}
```
**Resultado**: `eventos.hora_inicio` e `eventos.local` são atualizados. `hora_fim` é ignorado e logado.

### Cenário 2: Create de Programação
```json
POST /api/organizador/programacao/create.php
{
  "evento_id": 8,
  "tipo": "horario_largada",
  "hora_inicio": "05:45:00",   // ✅ Válido - insere em programacao_evento.hora_inicio
  "local": "Ponto de largada", // ✅ Válido - insere em programacao_evento.local
  "nome": "Evento Teste"        // ⚠️ Ignorado - campo de evento
}
```
**Resultado**: `programacao_evento.hora_inicio` e `programacao_evento.local` são inseridos. `nome` é ignorado e logado.

## Benefícios

1. **Prevenção de Erros**: Campos incorretos são ignorados antes de causar problemas
2. **Rastreabilidade**: Logs permitem identificar tentativas de uso incorreto
3. **Documentação**: Comentários SQL facilitam entendimento do propósito de cada campo
4. **Manutenibilidade**: Código mais claro sobre qual contexto cada campo pertence

## Próximos Passos

1. **Executar migração SQL**: `migrations/add_comentarios_campos_redundantes.sql`
   - As colunas já existem, este script apenas adiciona comentários
   - Pode ser executado diretamente sem verificações adicionais
2. **Monitorar logs**: Verificar se há tentativas de uso incorreto de campos
3. **Revisar código**: Garantir que frontend não envie campos de contexto errado

## Notas Importantes

- **Redundância é Aceitável**: Ter campos com mesmo nome em tabelas diferentes é normal em bancos relacionais
- **Contexto é Fundamental**: A diferença está no CONTEXTO de uso, não no nome
- **Validação Previne Erros**: As validações implementadas previnem atualizações incorretas sem quebrar funcionalidade existente
- **Logs Ajudam Debug**: Logs detalhados facilitam identificação de problemas futuros

