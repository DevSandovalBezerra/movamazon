-- Script para adicionar campo init_point na tabela pagamentos_ml
-- Data: <?php echo date('Y-m-d H:i:s'); ?>

-- Verificar se a tabela existe
SELECT 'Verificando se tabela pagamentos_ml existe...' as status;

-- Adicionar campo init_point se não existir
ALTER TABLE `pagamentos_ml` 
ADD COLUMN `init_point` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL 
COMMENT 'URL de inicialização do Mercado Pago' 
AFTER `preference_id`;

-- Verificar estrutura da tabela após alteração
DESCRIBE `pagamentos_ml`;

-- Mostrar mensagem de sucesso
SELECT 'Campo init_point adicionado com sucesso!' as resultado;
