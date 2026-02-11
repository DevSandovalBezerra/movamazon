<?php

/**
 * Página de login simplificada para teste
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Teste Login';
include '../../includes/header.php';
?>

<div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 class="text-center text-3xl font-bold text-gray-900">Teste Login</h2>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white rounded-xl shadow p-8">
            <form method="post" action="login.php" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                    <input id="email" name="email" type="email" required class="block w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900" placeholder="seu@email.com">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                    <input id="password" name="password" type="password" required class="block w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900" placeholder="Sua senha">
                </div>

                <!-- BOTÃO SIMPLES PARA TESTE -->
                <button type="submit" class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-base font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M5 12h14M12 5l7 7-7 7" />
                    </svg>
                    ENTRAR - TESTE
                </button>

                <!-- BOTÃO ALTERNATIVO COM CORES BÁSICAS -->
                <button type="button" class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-base font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition">
                    BOTÃO VERDE (TESTE)
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    /* CSS inline para garantir que funcione */
    .test-button {
        background-color: #2E8B57 !important;
        color: white !important;
        padding: 12px 24px !important;
        border-radius: 8px !important;
        border: none !important;
        cursor: pointer !important;
        font-size: 16px !important;
        font-weight: 500 !important;
        width: 100% !important;
        margin-top: 16px !important;
    }

    .test-button:hover {
        background-color: #0b4340 !important;
    }
</style>

<button class="test-button" onclick="alert('Botão funcionando!')">
    BOTÃO CSS INLINE (TESTE)
</button>

<?php include '../../includes/footer.php'; ?>
