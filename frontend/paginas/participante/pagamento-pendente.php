<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Parâmetros do retorno do Mercado Pago (extrair antes do redirect)
$collection_status = $_GET['collection_status'] ?? 'pending';
$preference_id = $_GET['preference_id'] ?? '';
$external_reference = $_GET['external_reference'] ?? '';

// Extrair ID da inscrição do external_reference
$inscricao_id = null;
if ($external_reference) {
    if (preg_match('/MOVAMAZON[AS]*_(\d+)/', $external_reference, $matches)) {
        $inscricao_id = $matches[1];
    } elseif (preg_match('/MINDRUNNER_\d+_(\d+)/', $external_reference, $matches)) {
        $inscricao_id = $matches[1];
    } else {
        $parts = explode('_', $external_reference);
        $last_part = end($parts);
        if (is_numeric($last_part)) {
            $inscricao_id = $last_part;
        }
    }
}

// Sem sessão: redirect para login com mensagem e params para Minhas Inscrições após login
if (!isset($_SESSION['user_id'])) {
    $params = 'area=participante&redirect=minhas-inscricoes&retorno_pagamento=1';
    if ($inscricao_id) {
        $params .= '&inscricao_id=' . (int) $inscricao_id;
    }
    header('Location: ../auth/login.php?' . $params);
    exit;
}

$pageTitle = 'Pagamento Pendente';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Pending Card -->
        <div class="bg-white rounded-lg shadow-lg p-8 text-center">

            <!-- Pending Icon -->
            <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-yellow-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <!-- Title -->
            <h1 class="text-3xl font-bold text-gray-900 mb-4">Pagamento Pendente</h1>

            <!-- Message -->
            <p class="text-lg text-gray-600 mb-6">
                Seu pagamento está sendo processado. Você receberá uma confirmação por email assim que for aprovado.
            </p>

            <!-- Details -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-600">Status:</span>
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold">
                        Pendente
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Data:</span>
                    <span class="font-medium"><?php echo date('d/m/Y H:i'); ?></span>
                </div>
            </div>

            <!-- Actions -->
            <div class="space-y-3">
                <a href="index.php?page=minhas-inscricoes" class="inline-block w-full bg-brand-green text-white font-semibold py-3 px-6 rounded-lg hover:bg-green-700 transition-colors duration-200 shadow-md hover:shadow-lg">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Ver Minhas Inscrições
                </a>

                <a href="../public/index.php" class="inline-block w-full bg-gray-100 text-gray-700 font-medium py-2 px-6 rounded-lg hover:bg-gray-200 transition-colors">
                    Explorar Mais Eventos
                </a>
            </div>

            <!-- Additional Info -->
            <div class="mt-8 p-4 bg-blue-50 rounded-lg">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="text-left">
                        <p class="text-sm font-medium text-blue-900 mb-1">Informação importante</p>
                        <p class="text-sm text-blue-700">
                            Alguns métodos de pagamento podem levar alguns minutos para serem confirmados.
                            Você receberá um email assim que o pagamento for aprovado.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Sync como fonte da verdade (fallback do webhook) – única atualização no retorno
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($inscricao_id): ?>
        const inscricaoId = <?php echo json_encode($inscricao_id); ?>;
        fetch('../../../api/participante/sync_payment_status.php?inscricao_id=' + encodeURIComponent(inscricaoId), {
            method: 'GET',
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('✅ Sync retorno pagamento:', data.atualizado ? 'atualizado' : 'já completo', data);
            } else {
                console.warn('⚠️ Sync:', data.message);
            }
        })
        .catch(error => console.error('❌ Erro ao sincronizar status:', error));
        <?php endif; ?>
    });
</script>

<?php include '../../includes/footer.php'; ?>
