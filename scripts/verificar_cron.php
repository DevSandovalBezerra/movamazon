<?php
/**
 * Script de Verificação do CRON
 * 
 * Verifica se o CRON está funcionando corretamente
 * e mostra estatísticas de cancelamentos
 * 
 * Uso: php scripts/verificar_cron.php
 */

require_once __DIR__ . '/../api/db.php';

echo "========================================\n";
echo "VERIFICAÇÃO DO SISTEMA DE CANCELAMENTO\n";
echo "========================================\n\n";

// 1. Verificar inscrições que devem ser canceladas
echo "1. INSCRIÇÕES QUE DEVEM SER CANCELADAS:\n";
echo "----------------------------------------\n";

// Boletos expirados
$stmt = $pdo->query("
    SELECT COUNT(*) as total
    FROM inscricoes
    WHERE status_pagamento = 'pendente'
      AND forma_pagamento = 'boleto'
      AND data_expiracao_pagamento IS NOT NULL
      AND data_expiracao_pagamento < NOW()
      AND status != 'cancelada'
");
$boletos_expirados = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
echo "   - Boletos expirados: $boletos_expirados\n";

// Pendentes há mais de 72 horas
$stmt = $pdo->query("
    SELECT COUNT(*) as total
    FROM inscricoes
    WHERE status_pagamento = 'pendente'
      AND data_inscricao < DATE_SUB(NOW(), INTERVAL 72 HOUR)
      AND status != 'cancelada'
");
$pendentes_72h = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
echo "   - Pendentes há mais de 72h: $pendentes_72h\n";

// Após data de encerramento
$stmt = $pdo->query("
    SELECT COUNT(*) as total
    FROM inscricoes i
    INNER JOIN eventos e ON i.evento_id = e.id
    WHERE i.status_pagamento = 'pendente'
      AND e.data_fim_inscricoes IS NOT NULL
      AND i.data_inscricao > CONCAT(e.data_fim_inscricoes, ' ', COALESCE(e.hora_fim_inscricoes, '23:59:59'))
      AND i.status != 'cancelada'
");
$apos_encerramento = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
echo "   - Após data de encerramento: $apos_encerramento\n";

// ✅ CONTAGEM REAL (sem duplicatas) - usando mesma lógica do helper
$stmt_real = $pdo->query("
    SELECT COUNT(DISTINCT i.id) as total
    FROM inscricoes i
    INNER JOIN eventos e ON i.evento_id = e.id
    WHERE i.status_pagamento = 'pendente'
      AND i.status != 'cancelada'
      AND (
        -- Boletos expirados
        (i.forma_pagamento = 'boleto'
         AND i.data_expiracao_pagamento IS NOT NULL
         AND i.data_expiracao_pagamento < NOW())
        OR
        -- Pendentes há mais de 72 horas
        (i.data_inscricao < DATE_SUB(NOW(), INTERVAL 72 HOUR))
        OR
        -- Após data de encerramento
        (e.data_fim_inscricoes IS NOT NULL
         AND i.data_inscricao > CONCAT(e.data_fim_inscricoes, ' ', COALESCE(e.hora_fim_inscricoes, '23:59:59')))
      )
");
$total_real = $stmt_real->fetch(PDO::FETCH_ASSOC)['total'];

$total_soma = $boletos_expirados + $pendentes_72h + $apos_encerramento;
$sobreposicao = $total_soma - $total_real;

echo "\n   📊 ANÁLISE:\n";
echo "      - Total por critério (soma): $total_soma\n";
echo "      - Total real (sem duplicatas): $total_real\n";
if ($sobreposicao > 0) {
    echo "      - Sobreposição (inscrições em múltiplos critérios): $sobreposicao\n";
    echo "      ℹ️  Algumas inscrições atendem múltiplos critérios simultaneamente.\n";
    echo "         O sistema cancelará $total_real inscrição(ões) únicas.\n";
}
echo "\n   ✅ TOTAL REAL A CANCELAR: $total_real\n\n";

// 2. Verificar inscrições canceladas recentemente
echo "2. INSCRIÇÕES CANCELADAS RECENTEMENTE (últimas 24h):\n";
echo "----------------------------------------------------\n";
$stmt = $pdo->query("
    SELECT COUNT(*) as total
    FROM inscricoes
    WHERE status = 'cancelada'
      AND status_pagamento = 'cancelado'
      AND (data_inscricao >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
           OR data_pagamento >= DATE_SUB(NOW(), INTERVAL 24 HOUR))
");
$canceladas_24h = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
echo "   Total canceladas nas últimas 24h: $canceladas_24h\n\n";

// 3. Verificar inscrições em processamento (não devem ser canceladas)
echo "3. INSCRIÇÕES EM PROCESSAMENTO (protegidas):\n";
echo "---------------------------------------------\n";
$stmt = $pdo->query("
    SELECT COUNT(*) as total
    FROM inscricoes
    WHERE status_pagamento = 'processando'
      AND status = 'pendente'
");
$em_processamento = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
echo "   Total em processamento: $em_processamento\n\n";

// 4. Estatísticas gerais
echo "4. ESTATÍSTICAS GERAIS:\n";
echo "----------------------\n";
$stmt = $pdo->query("SELECT COUNT(*) as total FROM inscricoes WHERE status_pagamento = 'pendente'");
$total_pendentes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
echo "   Total pendentes: $total_pendentes\n";

$stmt = $pdo->query("SELECT COUNT(*) as total FROM inscricoes WHERE status_pagamento = 'pago'");
$total_pagas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
echo "   Total pagas: $total_pagas\n";

$stmt = $pdo->query("SELECT COUNT(*) as total FROM inscricoes WHERE status = 'cancelada'");
$total_canceladas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
echo "   Total canceladas: $total_canceladas\n\n";

// 5. Recomendações
echo "5. RECOMENDAÇÕES:\n";
echo "----------------\n";
if ($total_real > 0) {
    echo "   ⚠️  ATENÇÃO: Existem $total_real inscrição(ões) que devem ser canceladas!\n";
    echo "   → Execute: php api/cron/cancelar_inscricoes_expiradas.php\n";
    echo "   → Ou via HTTP: GET /api/cron/cancelar_inscricoes_expiradas_http.php?token=SEU_TOKEN\n\n";
} else {
    echo "   ✅ Nenhuma inscrição pendente de cancelamento.\n\n";
}

if ($em_processamento > 0) {
    echo "   ℹ️  Existem $em_processamento inscrição(ões) em processamento (PIX).\n";
    echo "   Estas não serão canceladas automaticamente.\n\n";
}

echo "========================================\n";
echo "Verificação concluída em: " . date('Y-m-d H:i:s') . "\n";
echo "========================================\n";
