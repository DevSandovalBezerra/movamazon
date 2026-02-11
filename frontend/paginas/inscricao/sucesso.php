<?php
session_start();

$status = $_GET['status'] ?? 'success';
$inscricao_id_get = isset($_GET['inscricao_id']) ? (int)$_GET['inscricao_id'] : 0;
$external_reference = $_GET['external_reference'] ?? '';
$collection_status = $_GET['collection_status'] ?? $status;

require_once __DIR__ . '/../../../api/db.php';

$inscricao = null;

// 1) Fluxo Checkout Pro: retorno com external_reference e status/collection_status na URL
if (!empty($external_reference)) {
    $stmt = $pdo->prepare("
        SELECT 
            i.*,
            e.nome as evento_nome,
            m.nome as modalidade_nome,
            u.nome_completo as usuario_nome,
            u.email as usuario_email
        FROM inscricoes i
        INNER JOIN eventos e ON i.evento_id = e.id
        INNER JOIN modalidades m ON i.modalidade_evento_id = m.id
        INNER JOIN usuarios u ON i.usuario_id = u.id
        WHERE i.external_reference = ?
    ");
    $stmt->execute([$external_reference]);
    $inscricao = $stmt->fetch();
    if ($inscricao) {
        $status = ($collection_status === 'approved' || $collection_status === 'success') ? 'success' : (($collection_status === 'pending') ? 'pending' : 'failure');
    }
}
// back_urls podem enviar ?status=pending ou ?status=failure
if (!empty($_GET['status']) && in_array($_GET['status'], ['success', 'pending', 'failure'], true)) {
    $status = $_GET['status'];
}

// 2) Fluxo PIX: redirecionamento com inscricao_id (pagamento já confirmado via webhook/polling)
if (!$inscricao && $inscricao_id_get > 0) {
    $stmt = $pdo->prepare("
        SELECT 
            i.*,
            e.nome as evento_nome,
            m.nome as modalidade_nome,
            u.nome_completo as usuario_nome,
            u.email as usuario_email
        FROM inscricoes i
        INNER JOIN eventos e ON i.evento_id = e.id
        INNER JOIN modalidades m ON i.modalidade_evento_id = m.id
        INNER JOIN usuarios u ON i.usuario_id = u.id
        WHERE i.id = ? AND i.usuario_id = ?
    ");
    $stmt->execute([$inscricao_id_get, $_SESSION['user_id'] ?? 0]);
    $inscricao = $stmt->fetch();
    if ($inscricao && ($inscricao['status_pagamento'] ?? '') === 'pago') {
        $status = 'success';
    }
    if (!$inscricao) {
        header('Location: index.php');
        exit;
    }
}

// 3) Sessão pagamento_ml (fluxo antigo)
if (!$inscricao && isset($_SESSION['pagamento_ml'])) {
    $pagamento = $_SESSION['pagamento_ml'];
    $ref_id = $pagamento['dados_inscricao']['id'] ?? null;
    $stmt = $pdo->prepare("
        SELECT 
            i.*,
            e.nome as evento_nome,
            m.nome as modalidade_nome,
            u.nome_completo as usuario_nome,
            u.email as usuario_email
        FROM inscricoes i
        INNER JOIN eventos e ON i.evento_id = e.id
        INNER JOIN modalidades m ON i.modalidade_evento_id = m.id
        INNER JOIN usuarios u ON i.usuario_id = u.id
        WHERE (i.id = ? OR i.external_reference = ?) AND i.usuario_id = ?
    ");
    $stmt->execute([$ref_id, $ref_id, $_SESSION['user_id'] ?? 0]);
    $inscricao = $stmt->fetch();
    unset($_SESSION['pagamento_ml']);
}

if (!$inscricao) {
    header('Location: index.php');
    exit;
}

if (empty($inscricao)) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Aprovado - MovAmazon</title>
    <link href="../../assets/css/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <?php if ($status === 'success'): ?>
                    <!-- Sucesso -->
                    <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100 mb-6">
                        <i class="fas fa-check text-4xl text-green-600"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">Pagamento Aprovado!</h2>
                    <p class="text-gray-600 mb-8">Sua inscrição foi confirmada com sucesso.</p>

                <?php elseif ($status === 'pending'): ?>
                    <!-- Pendente -->
                    <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-yellow-100 mb-6">
                        <i class="fas fa-clock text-4xl text-yellow-600"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">Pagamento Pendente</h2>
                    <p class="text-gray-600 mb-8">Seu pagamento está sendo processado. Você receberá um email quando for confirmado.</p>

                <?php else: ?>
                    <!-- Falha -->
                    <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-100 mb-6">
                        <i class="fas fa-times text-4xl text-red-600"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">Pagamento Não Aprovado</h2>
                    <p class="text-gray-600 mb-8">Houve um problema com seu pagamento. Tente novamente.</p>
                <?php endif; ?>
            </div>

            <?php if ($inscricao): ?>
                <!-- Detalhes da Inscrição -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Detalhes da Inscrição</h3>

                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Evento:</span>
                            <span class="font-medium"><?= htmlspecialchars($inscricao['evento_nome']) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Modalidade:</span>
                            <span class="font-medium"><?= htmlspecialchars($inscricao['modalidade_nome']) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Participante:</span>
                            <span class="font-medium"><?= htmlspecialchars($inscricao['usuario_nome']) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Valor:</span>
                            <span class="font-medium text-green-600">R$ <?= number_format($inscricao['valor_total'], 2, ',', '.') ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status:</span>
                            <span class="font-medium text-green-600">Confirmada</span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Ações -->
            <div class="space-y-4">
                <?php if ($status === 'success'): ?>
                    <button onclick="window.print()" class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-print mr-2"></i>
                        Imprimir Comprovante
                    </button>
                <?php endif; ?>

                <a href="/dashboard" class="w-full bg-gray-600 text-white py-3 px-4 rounded-lg hover:bg-gray-700 transition-colors text-center block">
                    <i class="fas fa-home mr-2"></i>
                    Ir para Dashboard
                </a>

                <a href="/eventos" class="w-full bg-green-600 text-white py-3 px-4 rounded-lg hover:bg-green-700 transition-colors text-center block">
                    <i class="fas fa-calendar mr-2"></i>
                    Ver Outros Eventos
                </a>

                <!-- Botão de Logout -->
                <button onclick="fazerLogout()" class="w-full bg-red-600 text-white py-3 px-4 rounded-lg hover:bg-red-700 transition-colors">
                    <i class="fas fa-sign-out-alt mr-2"></i>
                    Sair e Fazer Nova Inscrição
                </button>
            </div>

            <!-- Informações Adicionais -->
            <div class="text-center text-sm text-gray-500">
                <p>Você receberá um email de confirmação em breve.</p>
                <p class="mt-2">Em caso de dúvidas, entre em contato conosco.</p>
                <p class="mt-4 text-blue-600 font-medium">
                    <i class="fas fa-clock mr-1"></i>
                    Você será redirecionado para o login em <span id="countdown">5</span> segundos
                </p>
            </div>
        </div>
    </div>

    <script>
        // Contador regressivo para redirecionamento automático
        let countdown = 5;
        const countdownElement = document.getElementById('countdown');

        const timer = setInterval(() => {
            countdown--;
            countdownElement.textContent = countdown;

            if (countdown <= 0) {
                clearInterval(timer);
                fazerLogout();
            }
        }, 1000);

        // Função para fazer logout
        function fazerLogout() {
            // Limpar dados da sessão via AJAX
            fetch('../../api/auth/logout.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                })
                .then(() => {
                    // Redirecionar para página de login
                    window.location.href = '../public/index.php';
                })
                .catch(() => {
                    // Mesmo se der erro, redirecionar
                    window.location.href = '../public/index.php';
                });
        }

        // Cancelar redirecionamento automático se usuário interagir
        document.addEventListener('click', () => {
            clearInterval(timer);
            const countdownElement = document.querySelector('.text-blue-600');
            if (countdownElement) {
                countdownElement.style.display = 'none';
            }
        });
    </script>
    
    <?php include 'includes/footer-inscricao.php'; ?>