# 📋 PLANO DETALHADO DE DELEÇÃO DE EVENTOS - MIND RUNNER

## 📊 ANÁLISE DETALHADA DAS LIGAÇÕES DA TABELA `eventos`

### 🔗 **Tabelas que referenciam `eventos` (Foreign Keys)**

| Tabela | Campo FK | CASCADE | Impacto na Deleção |
|--------|----------|---------|-------------------|
| `modalidades` | `evento_id` | ✅ SIM | Deletada automaticamente |
| `lotes_inscricao` | `evento_id` | ✅ SIM | Deletada automaticamente |
| `inscricoes` | `evento_id` | ❌ NÃO | **CRÍTICO** - Dados de participantes |
| `kits_eventos` | `evento_id` | ❌ NÃO | **ALTO** - Kits associados |
| `produtos_extras` | `evento_id` | ❌ NÃO | **ALTO** - Produtos extras |
| `formas_pagamento_evento` | `evento_id` | ❌ NÃO | **MÉDIO** - Configurações de pagamento |
| `programacao_evento` | `evento_id` | ❌ NÃO | **MÉDIO** - Programação do evento |
| `questionario_evento` | `evento_id` | ❌ NÃO | **MÉDIO** - Questionários |
| `retirada_kits_evento` | `evento_id` | ❌ NÃO | **MÉDIO** - Locais de retirada |
| `termos_eventos` | `evento_id` | ❌ NÃO | **MÉDIO** - Termos e condições |
| `cupons_remessa` | `evento_id` | ❌ NÃO | **BAIXO** - Cupons de desconto |
| `repasse_organizadores` | `evento_id` | ❌ NÃO | **CRÍTICO** - Dados financeiros |

### ⚠️ **PROBLEMAS IDENTIFICADOS NO DELETE ATUAL**

O arquivo `api/evento/delete.php` atual possui **SÉRIOS PROBLEMAS**:

1. **❌ Tabelas Incorretas**: 
   - Tenta deletar de `lotes` (não existe)
   - Tenta deletar de `kits_modalidades` (não existe)
   - Tenta deletar de `tamanhos_camisetas` (não existe)

2. **❌ Falta de Cascata**: 
   - Não deleta dados relacionados que ficarão órfãos
   - Deixa registros "soltos" no banco

3. **❌ Não Remove Arquivos**: 
   - Imagens do evento não são removidas
   - Arquivos de regulamento não são limpos

4. **❌ Verificação Insuficiente**: 
   - Só verifica inscrições, mas há outras dependências críticas

---

## 🎯 **ESTRATÉGIA DE DELEÇÃO**

### **NÍVEL 1 - SOFT DELETE (Recomendado)**
- ✅ Marcar evento como "excluído" ao invés de deletar
- ✅ Manter dados para auditoria e relatórios
- ✅ Ocultar da listagem pública
- ✅ Permitir recuperação futura

### **NÍVEL 2 - HARD DELETE (Apenas se necessário)**
- ⚠️ Deletar completamente do banco
- ⚠️ Remover arquivos físicos
- ⚠️ Apenas para eventos sem dados críticos
- ⚠️ **IRREVERSÍVEL**

---

## 🛠️ **IMPLEMENTAÇÃO DETALHADA**

### **FASE 1: ESTRUTURA DO BANCO DE DADOS**

#### **1.1 Adicionar Campos para Soft Delete**

```sql
-- Adicionar campos para controle de exclusão suave
ALTER TABLE eventos 
ADD COLUMN data_exclusao TIMESTAMP NULL DEFAULT NULL,
ADD COLUMN excluido_por INT NULL DEFAULT NULL,
ADD COLUMN motivo_exclusao TEXT NULL DEFAULT NULL,
ADD INDEX idx_excluido (data_exclusao);

-- Adicionar foreign key para usuário que excluiu
ALTER TABLE eventos 
ADD CONSTRAINT fk_evento_excluido_por 
FOREIGN KEY (excluido_por) REFERENCES usuarios(id);
```

#### **1.2 Criar View para Eventos Ativos**

```sql
-- View para listar apenas eventos ativos (não excluídos)
CREATE VIEW eventos_ativos AS
SELECT * FROM eventos 
WHERE data_exclusao IS NULL;
```

#### **1.3 Adicionar Triggers para Auditoria**

```sql
-- Trigger para log de exclusão
DELIMITER $$
CREATE TRIGGER tr_evento_excluido 
AFTER UPDATE ON eventos
FOR EACH ROW
BEGIN
    IF OLD.data_exclusao IS NULL AND NEW.data_exclusao IS NOT NULL THEN
        INSERT INTO logs_admin (usuario_id, acao, data_acao)
        VALUES (NEW.excluido_por, CONCAT('Excluiu evento: ', NEW.nome), NOW());
    END IF;
END$$
DELIMITER ;
```

---

## 📁 **ARQUIVOS A SEREM CRIADOS/MODIFICADOS**

### **Novos Arquivos:**
1. `api/evento/EventoDeleter.php` - Classe principal de deleção
2. `api/evento/check_dependencies.php` - Verificação de dependências
3. `docs/migrations/evento_delete_migration.sql` - Scripts de migração
4. `test_evento_delete.php` - Script de testes
5. `scripts/backup_eventos.sh` - Script de backup
6. `scripts/monitor_eventos.php` - Monitoramento

### **Arquivos a Modificar:**
1. `api/evento/delete.php` - Corrigir completamente
2. `frontend/js/organizador-eventos.js` - Atualizar interface
3. `frontend/paginas/organizador/eventos/index.php` - Ajustar listagem

---

## 🚀 **CRONOGRAMA DE IMPLEMENTAÇÃO**

### **SEMANA 1 - Preparação e Estrutura**
- [ ] **Dia 1-2**: Análise completa do banco de dados
- [ ] **Dia 3**: Criação dos scripts de migração
- [ ] **Dia 4**: Implementação da classe `EventoDeleter`
- [ ] **Dia 5**: Testes unitários da classe

### **SEMANA 2 - APIs e Backend**
- [ ] **Dia 1**: Implementação do endpoint `check_dependencies.php`
- [ ] **Dia 2**: Correção completa do `delete.php`
- [ ] **Dia 3**: Implementação de logs de auditoria
- [ ] **Dia 4**: Testes de integração das APIs
- [ ] **Dia 5**: Documentação das APIs

### **SEMANA 3 - Frontend e Interface**
- [ ] **Dia 1**: Atualização do JavaScript de listagem
- [ ] **Dia 2**: Implementação dos modais de confirmação
- [ ] **Dia 3**: Integração com SweetAlert2
- [ ] **Dia 4**: Testes de interface
- [ ] **Dia 5**: Ajustes de UX/UI

### **SEMANA 4 - Testes e Deploy**
- [ ] **Dia 1**: Testes completos do sistema
- [ ] **Dia 2**: Testes de performance
- [ ] **Dia 3**: Correção de bugs encontrados
- [ ] **Dia 4**: Deploy em ambiente de teste
- [ ] **Dia 5**: Deploy em produção

---

## 🔒 **CONSIDERAÇÕES DE SEGURANÇA**

### **Validações de Segurança:**
- ✅ **Autenticação**: Verificar se usuário está logado
- ✅ **Autorização**: Verificar se evento pertence ao organizador
- ✅ **Validação de entrada**: Sanitizar todos os parâmetros
- ✅ **Prevenção de SQL Injection**: Usar prepared statements
- ✅ **Logs de auditoria**: Registrar todas as operações
- ✅ **Backup automático**: Antes de operações críticas

---

## 📊 **MÉTRICAS DE SUCESSO**

### **KPIs do Sistema:**
- **Tempo de resposta**: < 2 segundos para verificação de dependências
- **Taxa de erro**: < 1% nas operações de deleção
- **Disponibilidade**: 99.9% de uptime
- **Satisfação do usuário**: > 90% nas pesquisas

---

## 🎯 **RESUMO DO PLANO**

### **Implementação Recomendada:**

1. **Imediato**: Corrigir o `delete.php` atual com a lógica correta
2. **Curto prazo**: Implementar soft delete como padrão
3. **Médio prazo**: Adicionar campos de auditoria no banco
4. **Longo prazo**: Interface avançada com opções de exclusão

### **Benefícios:**
- ✅ **Segurança**: Não perde dados importantes
- ✅ **Auditoria**: Rastreamento completo de exclusões
- ✅ **Flexibilidade**: Opções de exclusão suave e completa
- ✅ **Integridade**: Remove todos os dados órfãos
- ✅ **Usabilidade**: Interface clara para o usuário

---

**Data de Criação**: 2025-01-XX  
**Versão**: 1.0  
**Autor**: Sistema MovAmazonas  
**Status**: Pronto para Implementação

---

*Este documento contém o plano completo para implementação de um sistema robusto e seguro de deleção de eventos, evitando dados órfãos e mantendo a integridade do sistema.*
