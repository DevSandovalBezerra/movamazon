<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: /frontend/auth/login.php');
    exit();
}

$inscricao_id = $_GET['inscricao_id'] ?? null;
if (!$inscricao_id) {
    header('Location: index.php?page=meus-treinos');
    exit();
}
?>

<div class="container mx-auto p-4 md:p-8">
    <div class="mb-6">
        <a href="?page=meus-treinos" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">
            ← Voltar para Meus Treinos
        </a>
        <h1 class="text-3xl font-bold mb-2">Meu Treino de Preparação</h1>
        <p class="text-gray-600">Treino personalizado para sua corrida</p>
    </div>

    <div id="loading" class="text-center py-10">
        <p>Carregando treino...</p>
    </div>

    <div id="treino-container" class="hidden">
        <div id="plano-info" class="bg-white rounded-lg shadow-md p-6 mb-6">
        </div>

        <div id="treinos-list" class="space-y-6">
        </div>
    </div>

    <div id="sem-treino" class="hidden text-center py-16 bg-white rounded-lg shadow-lg">
        <div class="max-w-md mx-auto">
            <div class="w-24 h-24 bg-gradient-to-br from-yellow-100 to-yellow-200 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12 text-yellow-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-3">Treino não encontrado</h3>
            <p class="text-gray-600 mb-6">Nenhum treino foi gerado para esta inscrição ainda.</p>
            <a href="?page=meus-treinos" class="inline-block bg-blue-600 text-white font-semibold py-3 px-6 rounded-lg hover:bg-blue-700 transition-colors">
                Voltar
            </a>
        </div>
    </div>
</div>

<script type="module">
import { carregarTreino } from '../../js/participante/treinos.js';

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const inscricaoId = urlParams.get('inscricao_id');
    
    if (inscricaoId) {
        carregarTreino(inscricaoId);
    } else {
        document.getElementById('loading').classList.add('hidden');
        document.getElementById('sem-treino').classList.remove('hidden');
    }
});
</script>

