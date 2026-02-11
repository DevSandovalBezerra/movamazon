-- Seed papel de responsável de corrida

INSERT INTO `papeis` (`nome`)
SELECT 'responsavel_corrida'
WHERE NOT EXISTS (SELECT 1 FROM papeis WHERE nome = 'responsavel_corrida');

