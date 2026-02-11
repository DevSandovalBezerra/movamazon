-- =====================================================
-- Script para atualizar datas do Evento 2
-- III CORRIDA SAUIM DE COLEIRA EM 2025
-- =====================================================
-- 
-- Atualizações:
-- - Período de inscrições: 15/11/2025 a 15/12/2025
-- - Dia da corrida: 20/12/2025
-- =====================================================

-- Iniciar transação
START TRANSACTION;

-- Atualizar dados do evento 2
UPDATE eventos 
SET 
    -- Período de inscrições
    data_inicio = '2025-11-15',                    -- Início do período de inscrições
    data_fim_inscricoes = '2025-12-15',            -- Fim do período de inscrições
    hora_fim_inscricoes = '23:59:00',              -- Hora limite para inscrições
    
    -- Dia da corrida/prova
    data_realizacao = '2025-12-20',                -- Data da realização da corrida
    
    -- Data fim do evento (atualizar para refletir a nova data de realização)
    data_fim = '2025-12-20'                        -- Fim do evento (mesmo dia da corrida)
    
WHERE id = 2;

-- Verificar se a atualização foi bem-sucedida
SELECT 
    id,
    nome,
    data_inicio AS 'Início Inscrições',
    data_fim_inscricoes AS 'Fim Inscrições',
    hora_fim_inscricoes AS 'Hora Fim Inscrições',
    data_realizacao AS 'Data da Corrida',
    data_fim AS 'Fim do Evento',
    status
FROM eventos 
WHERE id = 2;

-- Confirmar transação
COMMIT;

-- =====================================================
-- FIM DO SCRIPT
-- =====================================================

