<?php
session_start();
require_once '../../../../api/db.php';

// Verificar se o usuário está logado como organizador
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
$evento_data = file_get_contents("../../../../api/evento/get.php?id=" . $evento_id);
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
                    <div class="flex items-center space-x-2 mb-2">
                        <a href="../index.php" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-arrow-left"></i> Eventos
                        </a>
                        <span class="text-gray-400">/</span>
                        <a href="index.php?evento_id=<?php echo $evento_id; ?>" class="text-blue-600 hover:text-blue-800">
                            <?php echo htmlspecialchars($evento['nome']); ?>
                        </a>
                        <span class="text-gray-400">/</span>
                        <span class="font-semibold">Criar Lote</span>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900">Criar Novo Lote</h1>
                    <p class="text-gray-600 mt-2">Configure um novo lote de inscrição para "<?php echo htmlspecialchars($evento['nome']); ?>"</p>
                </div>
                <a href="index.php?evento_id=<?php echo $evento_id; ?>" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Voltar
                </a>
            </div>
        </div>
        <!-- Conteúdo do formulário de criação de lote aqui -->
    </div>
</div> 
