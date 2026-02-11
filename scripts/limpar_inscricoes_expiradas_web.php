<?php
/**
 * Interface Web para Limpeza de Inscrições Expiradas
 * 
 * Requer autenticação de administrador
 * Mostra preview antes de executar limpeza
 */

session_start();
require_once __DIR__ . '/../api/db.php';
require_once __DIR__ . '/../api/admin/auth_middleware.php';

// Verificar se é admin
if (!verificarAdmin()) {
    http_response_code(403);
    die('Acesso negado. Apenas administradores podem executar esta operação.');
}

$pdo = $GLOBALS['pdo'];
$executado = false;
$erro = null;
$resultados = null;

// Processar ação
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    try {
        $pdo->beginTransaction();
        
        $resultados = [
            'inscricoes_canceladas' => 0,
            'produtos_extras_removidos' => 0,
            'aceites_removidos' => 0,
            'pagamentos_ml_atualizados' => 0,
            'produtos_extras_orfaos' => 0,
            'aceites_orfaos' => 0,
            'pagamentos_ml_orfaos' => 0
        ];
        
        if ($_POST['acao'] === 'cancelar_inscricoes') {
            // Cancelar inscrições pendentes há mais de 72 horas
            $stmt = $pdo->prepare("
                UPDATE inscricoes
                SET status_pagamento = 'cancelado',
                    status = 'cancelada'
                WHERE status_pagamento = 'pendente'
                  AND status = 'pendente'
                  AND data_inscricao < DATE_SUB(NOW(), INTERVAL 72 HOUR)
            ");
            $stmt->execute();
            $resultados['inscricoes_canceladas'] += $stmt->rowCount();
            
            // Cancelar boletos expirados
            $stmt = $pdo->prepare("
                UPDATE inscricoes
                SET status_pagamento = 'cancelado',
                    status = 'cancelada'
                WHERE status_pagamento = 'pendente'
                  AND forma_pagamento = 'boleto'
                  AND data_expiracao_pagamento IS NOT NULL
                  AND data_expiracao_pagamento < NOW()
            ");
            $stmt->execute();
            $resultados['inscricoes_canceladas'] += $stmt->rowCount();
            
            // Limpar produtos extras de inscrições canceladas
            $stmt = $pdo->prepare("
                DELETE ipe
                FROM inscricoes_produtos_extras ipe
                INNER JOIN inscricoes i ON ipe.inscricao_id = i.id
                WHERE i.status = 'cancelada'
                  AND i.status_pagamento = 'cancelado'
            ");
            $stmt->execute();
            $resultados['produtos_extras_removidos'] = $stmt->rowCount();
            
            // Limpar aceites de termos de inscrições canceladas
            $stmt = $pdo->prepare("
                DELETE at
                FROM aceites_termos at
                INNER JOIN inscricoes i ON at.inscricao_id = i.id
                WHERE i.status = 'cancelada'
                  AND i.status_pagamento = 'cancelado'
            ");
            $stmt->execute();
            $resultados['aceites_removidos'] = $stmt->rowCount();
            
            // Atualizar pagamentos ML de inscrições canceladas
            $stmt = $pdo->prepare("
                UPDATE pagamentos_ml pm
                INNER JOIN inscricoes i ON pm.inscricao_id = i.id
                SET pm.status = 'cancelado'
                WHERE i.status = 'cancelada'
                  AND i.status_pagamento = 'cancelado'
                  AND pm.status = 'pendente'
            ");
            $stmt->execute();
            $resultados['pagamentos_ml_atualizados'] = $stmt->rowCount();
        }
        
        if ($_POST['acao'] === 'limpar_orfaos' && isset($_POST['confirmar_orfaos'])) {
            // Remover produtos extras órfãos
            $stmt = $pdo->prepare("
                DELETE ipe
                FROM inscricoes_produtos_extras ipe
                LEFT JOIN inscricoes i ON ipe.inscricao_id = i.id
                WHERE i.id IS NULL
            ");
            $stmt->execute();
            $resultados['produtos_extras_orfaos'] = $stmt->rowCount();
            
            // Remover aceites de termos órfãos
            $stmt = $pdo->prepare("
                DELETE at
                FROM aceites_termos at
                LEFT JOIN inscricoes i ON at.inscricao_id = i.id
                WHERE i.id IS NULL
            ");
            $stmt->execute();
            $resultados['aceites_orfaos'] = $stmt->rowCount();
            
            // Atualizar pagamentos ML órfãos
            $stmt = $pdo->prepare("
                UPDATE pagamentos_ml pm
                LEFT JOIN inscricoes i ON pm.inscricao_id = i.id
                SET pm.status = 'cancelado'
                WHERE i.id IS NULL AND pm.status = 'pendente'
            ");
            $stmt->execute();
            $resultados['pagamentos_ml_orfaos'] = $stmt->rowCount();
        }
        
        $pdo->commit();
        $executado = true;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $erro = $e->getMessage();
    }
}

// Buscar estatísticas para preview
$stats = [];

// Inscrições pendentes há mais de 72 horas
$stmt = $pdo->query("
    SELECT COUNT(*) as total
    FROM inscricoes
    WHERE status_pagamento = 'pendente'
      AND status = 'pendente'
      AND data_inscricao < DATE_SUB(NOW(), INTERVAL 72 HOUR)
");
$stats['pendentes_72h'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Boletos expirados
$stmt = $pdo->query("
    SELECT COUNT(*) as total
    FROM inscricoes
    WHERE status_pagamento = 'pendente'
      AND forma_pagamento = 'boleto'
      AND data_expiracao_pagamento IS NOT NULL
      AND data_expiracao_pagamento < NOW()
");
$stats['boletos_expirados'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Dados órfãos
$stmt = $pdo->query("
    SELECT COUNT(*) as total
    FROM inscricoes_produtos_extras ipe
    LEFT JOIN inscricoes i ON ipe.inscricao_id = i.id
    WHERE i.id IS NULL
");
$stats['produtos_extras_orfaos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->query("
    SELECT COUNT(*) as total
    FROM aceites_termos at
    LEFT JOIN inscricoes i ON at.inscricao_id = i.id
    WHERE i.id IS NULL
");
$stats['aceites_orfaos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->query("
    SELECT COUNT(*) as total
    FROM pagamentos_ml pm
    LEFT JOIN inscricoes i ON pm.inscricao_id = i.id
    WHERE i.id IS NULL
");
$stats['pagamentos_ml_orfaos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Listar inscrições que serão canceladas
$stmt = $pdo->query("
    SELECT 
        i.id,
        i.usuario_id,
        i.evento_id,
        i.data_inscricao,
        i.forma_pagamento,
        i.data_expiracao_pagamento,
        i.external_reference,
        TIMESTAMPDIFF(HOUR, i.data_inscricao, NOW()) as horas_pendente,
        CASE 
            WHEN i.data_expiracao_pagamento IS NOT NULL AND i.data_expiracao_pagamento < NOW() THEN 'Boleto expirado'
            WHEN i.data_inscricao < DATE_SUB(NOW(), INTERVAL 72 HOUR) THEN 'Pendente há mais de 72 horas'
            ELSE 'Outro'
        END as motivo
    FROM inscricoes i
    WHERE (
        (i.status_pagamento = 'pendente' AND i.status = 'pendente' AND i.data_inscricao < DATE_SUB(NOW(), INTERVAL 72 HOUR))
        OR
        (i.status_pagamento = 'pendente' AND i.forma_pagamento = 'boleto' AND i.data_expiracao_pagamento IS NOT NULL AND i.data_expiracao_pagamento < NOW())
    )
    ORDER BY i.data_inscricao
");
$inscricoes_preview = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Limpeza de Inscrições Expiradas - MovAmazon</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6 max-w-6xl">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Limpeza de Inscrições Expiradas</h1>
            
            <?php if ($erro): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <strong>Erro:</strong> <?= htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($executado && $resultados): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <h2 class="font-bold mb-2">Limpeza executada com sucesso!</h2>
                    <ul class="list-disc list-inside">
                        <li>Inscrições canceladas: <?= $resultados['inscricoes_canceladas'] ?></li>
                        <li>Produtos extras removidos: <?= $resultados['produtos_extras_removidos'] ?></li>
                        <li>Aceites removidos: <?= $resultados['aceites_removidos'] ?></li>
                        <li>Pagamentos ML atualizados: <?= $resultados['pagamentos_ml_atualizados'] ?></li>
                        <?php if (isset($_POST['confirmar_orfaos'])): ?>
                            <li>Produtos extras órfãos removidos: <?= $resultados['produtos_extras_orfaos'] ?></li>
                            <li>Aceites órfãos removidos: <?= $resultados['aceites_orfaos'] ?></li>
                            <li>Pagamentos ML órfãos atualizados: <?= $resultados['pagamentos_ml_orfaos'] ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <!-- Estatísticas -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-yellow-50 border border-yellow-200 rounded p-4">
                    <h3 class="font-semibold text-yellow-800">Pendentes 72h+</h3>
                    <p class="text-2xl font-bold text-yellow-900"><?= $stats['pendentes_72h'] ?></p>
                </div>
                <div class="bg-red-50 border border-red-200 rounded p-4">
                    <h3 class="font-semibold text-red-800">Boletos Expirados</h3>
                    <p class="text-2xl font-bold text-red-900"><?= $stats['boletos_expirados'] ?></p>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded p-4">
                    <h3 class="font-semibold text-blue-800">Total a Cancelar</h3>
                    <p class="text-2xl font-bold text-blue-900"><?= $stats['pendentes_72h'] + $stats['boletos_expirados'] ?></p>
                </div>
            </div>
            
            <!-- Preview de Inscrições -->
            <?php if (count($inscricoes_preview) > 0): ?>
                <div class="mb-6">
                    <h2 class="text-xl font-semibold mb-3">Inscrições que serão canceladas:</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-300">
                            <thead class="bg-gray-200">
                                <tr>
                                    <th class="px-4 py-2 border">ID</th>
                                    <th class="px-4 py-2 border">Usuário</th>
                                    <th class="px-4 py-2 border">Evento</th>
                                    <th class="px-4 py-2 border">Data Inscrição</th>
                                    <th class="px-4 py-2 border">Horas Pendente</th>
                                    <th class="px-4 py-2 border">Motivo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inscricoes_preview as $insc): ?>
                                    <tr>
                                        <td class="px-4 py-2 border"><?= $insc['id'] ?></td>
                                        <td class="px-4 py-2 border"><?= $insc['usuario_id'] ?></td>
                                        <td class="px-4 py-2 border"><?= $insc['evento_id'] ?></td>
                                        <td class="px-4 py-2 border"><?= date('d/m/Y H:i', strtotime($insc['data_inscricao'])) ?></td>
                                        <td class="px-4 py-2 border"><?= $insc['horas_pendente'] ?>h</td>
                                        <td class="px-4 py-2 border"><?= htmlspecialchars($insc['motivo']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-4">
                    Nenhuma inscrição expirada encontrada. Sistema está limpo!
                </div>
            <?php endif; ?>
            
            <!-- Dados Órfãos -->
            <?php if ($stats['produtos_extras_orfaos'] > 0 || $stats['aceites_orfaos'] > 0 || $stats['pagamentos_ml_orfaos'] > 0): ?>
                <div class="mb-6">
                    <h2 class="text-xl font-semibold mb-3">Dados Órfãos Encontrados:</h2>
                    <ul class="list-disc list-inside space-y-1">
                        <li>Produtos extras órfãos: <?= $stats['produtos_extras_orfaos'] ?></li>
                        <li>Aceites de termos órfãos: <?= $stats['aceites_orfaos'] ?></li>
                        <li>Pagamentos ML órfãos: <?= $stats['pagamentos_ml_orfaos'] ?></li>
                    </ul>
                </div>
            <?php endif; ?>
            
            <!-- Ações -->
            <div class="flex gap-4">
                <?php if (count($inscricoes_preview) > 0): ?>
                    <form method="POST" onsubmit="return confirm('Tem certeza que deseja cancelar <?= count($inscricoes_preview) ?> inscrições? Esta ação não pode ser desfeita.');">
                        <input type="hidden" name="acao" value="cancelar_inscricoes">
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded">
                            Cancelar Inscrições Expiradas
                        </button>
                    </form>
                <?php endif; ?>
                
                <?php if ($stats['produtos_extras_orfaos'] > 0 || $stats['aceites_orfaos'] > 0 || $stats['pagamentos_ml_orfaos'] > 0): ?>
                    <form method="POST" onsubmit="return confirm('Tem certeza que deseja remover os dados órfãos? Esta ação não pode ser desfeita.');">
                        <input type="hidden" name="acao" value="limpar_orfaos">
                        <input type="hidden" name="confirmar_orfaos" value="1">
                        <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-4 rounded">
                            Limpar Dados Órfãos
                        </button>
                    </form>
                <?php endif; ?>
                
                <a href="?" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                    Atualizar
                </a>
            </div>
            
            <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded">
                <p class="text-sm text-yellow-800">
                    <strong>Atenção:</strong> Esta operação cancela inscrições e remove dados relacionados. 
                    Certifique-se de fazer backup do banco de dados antes de executar.
                </p>
            </div>
        </div>
    </div>
</body>
</html>

