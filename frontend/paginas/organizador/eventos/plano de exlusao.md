✅ CONCORDO TOTALMENTE!
deleted_at é muito melhor que status porque:
✅ Padrão da indústria (Laravel, Rails, etc.)
✅ Mais simples de implementar
✅ Filtros automáticos (WHERE deleted_at IS NULL)
✅ Timestamp automático da exclusão
✅ Não conflita com status do evento
Criar migração para adicionar campo deleted_at
Implementar API de verificação de dependências
Implementar API de soft delete
Corrigir API de listagem para filtrar excluídos
Implementar frontend com botão de exclusão
Implementar logs de auditoria
Testes completos do sistema
🚀 PLANO DE IMPLEMENTAÇÃO - PASSO A PASSO
FASE 1: ESTRUTURA DO BANCO 🗄️


PASSO 1: Migração do Banco

-- Adicionar campo deleted_at na tabela eventos
ALTER TABLE eventos 
ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL,
ADD COLUMN deleted_by INT NULL DEFAULT NULL,
ADD COLUMN delete_reason TEXT NULL DEFAULT NULL,
ADD INDEX idx_deleted_at (deleted_at);

-- Foreign key para usuário que excluiu
ALTER TABLE eventos 
ADD CONSTRAINT fk_evento_deleted_by 
FOREIGN KEY (deleted_by) REFERENCES usuarios(id);

PASSO 2: View para Eventos Ativos

-- View para listar apenas eventos não excluídos
CREATE VIEW eventos_ativos AS
SELECT * FROM eventos 
WHERE deleted_at IS NULL;

FASE 2: APIs BACKEND 🔧
PASSO 3: API de Verificação de Dependências
Arquivo: api/evento/check_dependencies.php
Verificar inscricoes (CRÍTICO)
Verificar repasse_organizadores (CRÍTICO)
Verificar kits_eventos (ALTO)
Verificar lotes_inscricao (ALTO)
Verificar produtos_extras (ALTO)
Retornar lista de dependências encontradas
PASSO 4: API de Soft Delete
Arquivo: api/evento/soft_delete.php
Verificar dependências primeiro
Se tem dependências críticas = BLOQUEAR
Se tem dependências não críticas = AVISAR
Se não tem dependências = EXCLUIR
Atualizar deleted_at, deleted_by, delete_reason
PASSO 5: Corrigir API de Listagem
Arquivo: api/organizador/eventos/list.php
Adicionar WHERE deleted_at IS NULL em todas as queries
Manter funcionalidade existente
FASE 3: FRONTEND 🎨
PASSO 6: Botão de Exclusão
Arquivo: frontend/paginas/organizador/eventos/index.php
Adicionar botão "Excluir" em cada evento
Integrar com SweetAlert2 para confirmação
PASSO 7: JavaScript de Exclusão
Arquivo: frontend/js/organizador-eventos.js
Função excluirEvento(eventoId)
Chamar API de verificação de dependências
Mostrar modal com dependências encontradas
Confirmar exclusão com SweetAlert2
FASE 4: AUDITORIA E LOGS 📝
PASSO 8: Logs de Auditoria
Arquivo: api/middleware/auth.php (já existe)
Usar função logSeguranca() existente
Registrar todas as exclusões
PASSO 9: Trigger de Auditoria

-- Trigger para log automático
DELIMITER $$
CREATE TRIGGER tr_evento_deleted 
AFTER UPDATE ON eventos
FOR EACH ROW
BEGIN
    IF OLD.deleted_at IS NULL AND NEW.deleted_at IS NOT NULL THEN
        INSERT INTO logs_admin (usuario_id, acao, data_acao)
        VALUES (NEW.deleted_by, CONCAT('Excluiu evento: ', NEW.nome), NOW());
    END IF;
END$$
DELIMITER ;

FASE 5: TESTES 🧪
PASSO 10: Testes Unitários
Testar API de verificação de dependências
Testar API de soft delete
Testar frontend de exclusão
PASSO 11: Testes de Integração
Testar fluxo completo de exclusão
Testar com diferentes cenários de dependências
📋 ORDEM DE IMPLEMENTAÇÃO RECOMENDADA:
PASSO 1 - Migração do banco (5 min)
PASSO 2 - View de eventos ativos (2 min)
PASSO 3 - API de verificação de dependências (30 min)
PASSO 4 - API de soft delete (20 min)
PASSO 5 - Corrigir API de listagem (10 min)
PASSO 6 - Botão de exclusão no frontend (15 min)
PASSO 7 - JavaScript de exclusão (25 min)
PASSO 8 - Logs de auditoria (10 min)
PASSO 9 - Trigger de auditoria (5 min)
PASSO 10 - Testes (20 min)
⏱️ TEMPO TOTAL ESTIMADO: ~2 horas
🎯 QUER QUE EU COMECE IMPLEMENTANDO?
Posso começar pelo PASSO 1 (Migração do banco)?
Ou prefere que eu implemente tudo de uma vez seguindo a ordem acima?
O plano está claro e organizado! 🚀