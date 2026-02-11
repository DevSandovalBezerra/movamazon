<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Parâmetros do retorno do Mercado Pago
$collection_status = $_GET['collection_status'] ?? 'rejected';
$preference_id = $_GET['preference_id'] ?? '';
$external_reference = $_GET['external_reference'] ?? '';

// Extrair ID da inscrição do external_reference
$inscricao_id = null;
if ($external_reference) {
    // Formato: MOVAMAZON_ID, MOVAMAZONAS_ID, MINDRUNNER_TIMESTAMP_ID, etc.
    if (preg_match('/MOVAMAZON[AS]*_(\d+)/', $external_reference, $matches)) {
        $inscricao_id = $matches[1];
    } elseif (preg_match('/MINDRUNNER_\d+_(\d+)/', $external_reference, $matches)) {
        $inscricao_id = $matches[1];
    } else {
        // Tentar extrair número do final
        $parts = explode('_', $external_reference);
        $last_part = end($parts);
        if (is_numeric($last_part)) {
            $inscricao_id = $last_part;
        }
    }
}

$pageTitle = 'Pagamento Não Aprovado';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Error Card -->
        <div class="bg-white rounded-lg shadow-lg p-8 text-center">

            <!-- Error Icon -->
            <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>

            <!-- Title -->
            <h1 class="text-3xl font-bold text-gray-900 mb-4">Pagamento Não Aprovado</h1>

            <!-- Message -->
            <p class="text-lg text-gray-600 mb-6">
                Infelizmente, seu pagamento não foi aprovado. Você pode tentar novamente ou escolher outro método de pagamento.
            </p>

            <!-- Details -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-600">Status:</span>
                    <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-semibold">
                        Não Aprovado
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
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    Tentar Pagar Novamente
                </a>

                <a href="../public/index.php" class="inline-block w-full bg-gray-100 text-gray-700 font-medium py-2 px-6 rounded-lg hover:bg-gray-200 transition-colors">
                    Explorar Mais Eventos
                </a>
            </div>

            <!-- Additional Info -->
            <div class="mt-8 p-4 bg-red-50 rounded-lg">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                    <div class="text-left">
                        <p class="text-sm font-medium text-red-900 mb-1">Possíveis causas</p>
                        <p class="text-sm text-red-700">
                            • Dados do cartão incorretos<br>
                            • Saldo insuficiente<br>
                            • Cartão bloqueado<br>
                            • Problemas temporários com o banco
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Atualizar status da inscrição no banco
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($inscricao_id): ?>
        const inscricaoId = <?php echo json_encode($inscricao_id); ?>;
        const collectionStatus = <?php echo json_encode($collection_status); ?>;
        const preferenceId = <?php echo json_encode($preference_id); ?>;
        const externalReference = <?php echo json_encode($external_reference); ?>;

        fetch('../../../api/participante/update_payment_status.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                inscricao_id: inscricaoId,
                collection_status: collectionStatus,
                preference_id: preferenceId,
                external_reference: externalReference
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('✅ Status da inscrição atualizado:', data.inscricao);
            } else {
                console.warn('⚠️ Aviso ao atualizar status:', data.message);
            }
        })
        .catch(error => {
            console.error('❌ Erro ao atualizar status:', error);
        });
        <?php endif; ?>
    });
</script>

<?php include '../../includes/footer.php'; ?>
