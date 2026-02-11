<?php
session_start();
require_once '../../../api/db.php';

// Verificar se o usuário está logado como organizador
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    header('Location: ../../auth/login.php');
    exit();
}

$organizador_id = $_SESSION['user_id'];

// Verificar se foi passado um ID de evento
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$evento_id = (int)$_GET['id'];

// Buscar dados do evento
try {
    $stmt = $pdo->prepare("
        SELECT * FROM eventos 
        WHERE id = ? AND organizador_id = ?
    ");
    $stmt->execute([$evento_id, $organizador_id]);
    $evento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$evento) {
        header('Location: index.php');
        exit();
    }
} catch (Exception $e) {
    header('Location: index.php');
    exit();
}
?>
<div class="p-6">
    <div class="max-w-4xl mx-auto">
        <!-- Cabeçalho -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Editar Evento</h1>
                    <p class="text-gray-600 mt-2">Modifique os dados do seu evento esportivo</p>
                </div>
                <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Voltar
                </a>
            </div>
        </div>
        <!-- Conteúdo do formulário de edição aqui -->
    </div>
</div> 
