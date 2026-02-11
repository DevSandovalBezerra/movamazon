<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: /frontend/auth/login.php');
    exit();
}
?>

<div class="container mx-auto p-4 sm:p-6 lg:p-8">
    <h1 class="text-2xl sm:text-3xl font-bold mb-4 sm:mb-6">Minhas Inscrições</h1>

    <div id="loading" class="text-center py-10">
        <p>Carregando suas inscrições...</p>
    </div>

    <div id="inscricoes-container" class="space-y-6 hidden">
        <!-- Cards de inscrição serão inseridos aqui via JS -->
    </div>

    <div id="nenhuma-inscricao" class="text-center py-16 bg-white rounded-lg shadow-lg hidden">
        <div class="max-w-md mx-auto">
            <!-- Ícone ilustrativo -->
            <div class="w-24 h-24 bg-gradient-to-br from-blue-100 to-blue-200 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>

            <!-- Título -->
            <h3 class="text-xl font-semibold text-gray-900 mb-3">Nenhuma inscrição encontrada</h3>

            <!-- Descrição -->
            <p class="text-gray-600 mb-6 leading-relaxed">
                Você ainda não se inscreveu em nenhum evento. Que tal explorar os eventos disponíveis e fazer sua primeira inscrição?
            </p>

            <!-- Botões de ação -->
            <div class="space-y-3">
                <a href="/frontend/paginas/public/index.php" class="inline-block w-full bg-brand-green text-white font-semibold py-3 px-6 rounded-lg hover:bg-green-700 transition-colors duration-200 shadow-md hover:shadow-lg touch-target">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Explorar Eventos
                </a>

                <button onclick="window.history.back()" class="inline-block w-full bg-gray-100 text-gray-700 font-medium py-2 px-6 rounded-lg hover:bg-gray-200 transition-colors duration-200 touch-target">
                    Voltar
                </button>
            </div>

            <!-- Dica adicional -->
            <div class="mt-8 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="text-left">
                        <p class="text-sm font-medium text-blue-900 mb-1">Dica:</p>
                        <p class="text-sm text-blue-700">
                            Mantenha seus dados atualizados no perfil para facilitar o processo de inscrição.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para exibir o QR Code -->
<div id="qr-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white p-6 sm:p-8 rounded-lg shadow-2xl text-center max-w-sm w-full">
        <h2 class="text-2xl font-bold mb-4">Retirada de Kit</h2>
        <p class="mb-2">Apresente este QR Code no dia da retirada do seu kit.</p>
        <div id="qr-code-container" class="p-4 border rounded-lg inline-block">
            <!-- Imagem do QR Code será inserida aqui -->
        </div>
        <p class="mt-4 text-sm text-gray-600">Inscrição: <strong id="modal-numero-inscricao"></strong></p>
        <p class="text-sm text-gray-600">Atleta: <strong id="modal-nome-atleta"><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></p>
        <button onclick="closeModal()" class="mt-6 bg-gray-300 text-gray-800 font-bold py-2 px-6 rounded hover:bg-gray-400 touch-target">Fechar</button>
    </div>
</div>

<script src="../../js/participante/qrcode.js" defer></script>
<script src="../../js/participante/inscricoes.js" defer></script>
