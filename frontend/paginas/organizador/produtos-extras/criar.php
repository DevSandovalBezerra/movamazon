<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    header('Location: ../../../auth/login.php');
    exit();
}

$evento_id = isset($_GET['evento_id']) ? (int)$_GET['evento_id'] : 0;
if (!$evento_id) {
    header('Location: ../index.php');
    exit();
}

// Buscar dados do evento
$evento_data = file_get_contents("../../../api/evento/get.php?id=" . $evento_id);
$evento = json_decode($evento_data, true);

if (!$evento || $evento['organizador_id'] != $_SESSION['user_id']) {
    header('Location: ../index.php');
    exit();
}
?>
<div class="p-6">
    <div class="max-w-4xl mx-auto">
        <!-- Cabeçalho -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Criar Produto Extra</h1>
                    <p class="text-gray-600 mt-2">Adicione um novo produto extra para o evento "<?php echo htmlspecialchars($evento['nome']); ?>"</p>
                </div>
                <a href="index.php?evento_id=<?php echo $evento_id; ?>" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Voltar
                </a>
            </div>
        </div>
        <!-- Conteúdo do formulário de criação de produto extra aqui -->
    </div>
</div> 
