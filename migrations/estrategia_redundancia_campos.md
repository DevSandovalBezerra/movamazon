# Estratégia para Tratamento de Redundância de Campos

## Análise do Problema

### Campos Redundantes Identificados

#### 1. `hora_inicio`
- **Tabela `eventos`**: Horário geral de início do evento (ex: 06:00:00)
- **Tabela `programacao_evento`**: Horário específico de largada de uma modalidade/atividade (ex: 05:45:00 para Pelotão PCD)

#### 2. `local`
- **Tabela `eventos`**: Local geral do evento (ex: "Manaus - AM", "Avenida Darcyr Vargas, 1200")
- **Tabela `programacao_evento`**: Local específico de percurso ou atividade (ex: "Ponto de largada - Praça da Matriz")

### Riscos Identificados

1. **Conflito de Nomenclatura**: APIs podem receber JSON com os mesmos nomes de campos
2. **Confusão no Código**: Desenvolvedores podem usar o campo errado
3. **Erro Silencioso**: Atualização pode ser feita na tabela errada sem erro SQL
4. **Manutenção**: Dificuldade em entender qual campo usar em cada contexto

## Estratégia de Solução

### 1. Documentação no Banco de Dados

Adicionar comentários SQL claros nas colunas para diferenciar os propósitos:

```sql
-- Tabela eventos
ALTER TABLE `eventos` 
MODIFY COLUMN `hora_inicio` TIME NULL COMMENT 'Horário geral de início do evento (não confundir com programacao_evento.hora_inicio)',
MODIFY COLUMN `local` VARCHAR(255) NULL COMMENT 'Local geral do evento - cidade/endereço principal (não confundir com programacao_evento.local)';

-- Tabela programacao_evento
ALTER TABLE `programacao_evento`
MODIFY COLUMN `hora_inicio` TIME NULL COMMENT 'Horário específico de largada/atividade desta programação (diferente de eventos.hora_inicio)',
MODIFY COLUMN `local` VARCHAR(255) NULL COMMENT 'Local específico deste item de programação (diferente de eventos.local)';
```

### 2. Validação nas APIs

#### API `api/organizador/eventos/update.php`
- **DEVE ACEITAR**: `hora_inicio`, `local` (referentes ao evento)
- **NÃO DEVE ACEITAR**: Campos de programação (`hora_fim`, `latitude`, `longitude` sem contexto de programação)
- **Validação**: Verificar que campos de programação não sejam enviados acidentalmente

#### API `api/organizador/programacao/create.php` e `update.php`
- **DEVE ACEITAR**: `hora_inicio`, `hora_fim`, `local`, `latitude`, `longitude` (referentes à programação)
- **Validação**: Garantir que `evento_id` seja obrigatório e válido
- **Validação**: Verificar que campos não sejam atualizados na tabela `eventos` por engano

### 3. Convenção de Nomenclatura no Frontend

#### JavaScript - Variáveis e Funções
- **Evento**: `evento.hora_inicio`, `evento.local`
- **Programação**: `item.hora_inicio`, `item.local`, `item.hora_fim`

#### Formulários HTML
- **Evento**: IDs como `horaInicio`, `localEvento`
- **Programação**: IDs como `hora-inicio-largada`, `local-percurso`

### 4. Validação de Integridade

Criar função helper para validar contexto antes de updates:

```php
// helpers/validate_field_context.php
function validarContextoCampo($campo, $tabela) {
    $camposEvento = ['hora_inicio', 'local', 'data_inicio', 'data_realizacao'];
    $camposProgramacao = ['hora_inicio', 'hora_fim', 'local', 'latitude', 'longitude'];
    
    if ($tabela === 'eventos' && in_array($campo, $camposProgramacao) && !in_array($campo, $camposEvento)) {
        throw new Exception("Campo '$campo' não pertence ao contexto de eventos");
    }
    
    if ($tabela === 'programacao_evento' && in_array($campo, $camposEvento) && !in_array($campo, $camposProgramacao)) {
        throw new Exception("Campo '$campo' não pertence ao contexto de programação");
    }
}
```

### 5. Logs e Rastreabilidade

Adicionar logs detalhados nas APIs para rastrear qual tabela está sendo atualizada:

```php
error_log("UPDATE eventos: hora_inicio={$hora_inicio}, local={$local}");
error_log("UPDATE programacao_evento: hora_inicio={$hora_inicio}, local={$local}, tipo={$tipo}");
```

## Implementação

### Fase 1: Documentação (Imediato)
- Adicionar comentários SQL nas colunas
- Atualizar documentação de APIs

### Fase 2: Validação (Curto Prazo)
- Implementar validações nas APIs
- Adicionar logs de rastreamento

### Fase 3: Monitoramento (Médio Prazo)
- Revisar logs periodicamente
- Identificar padrões de uso incorreto

## Checklist de Validação

Antes de fazer UPDATE em qualquer tabela:

- [ ] Verificar que o campo pertence ao contexto correto
- [ ] Validar que `evento_id` existe e pertence ao organizador
- [ ] Garantir que campos de outras tabelas não sejam atualizados por engano
- [ ] Logar qual tabela e quais campos estão sendo atualizados
- [ ] Testar que update não afeta dados de outras tabelas

## Notas Importantes

1. **Redundância é Aceitável**: Ter campos com mesmo nome em tabelas diferentes é normal em bancos relacionais
2. **Contexto é Fundamental**: A diferença está no CONTEXTO de uso, não no nome
3. **Validação Previne Erros**: Validações adequadas previnem atualizações incorretas
4. **Documentação Ajuda**: Comentários SQL e documentação clara facilitam manutenção

