<?php
// ✅ CRÍTICO: Iniciar output buffering ANTES de incluir header
if (!ob_get_level()) {
    ob_start();
}
$page_title = 'Login - Inscrição no Evento';
include 'includes/header-inscricao.php';

// Verificar se há evento_id na URL
$evento_id = isset($_GET['evento_id']) ? intval($_GET['evento_id']) : 0;
?>

<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Card de Login -->
        <div class="card p-8">
            <div class="text-center mb-8">
                <div class="mx-auto h-12 w-12 bg-brand-green rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-running text-white text-xl"></i>
                </div>
                <h2 class="text-3xl font-bold text-gray-900">Fazer Login</h2>
                <p class="mt-2 text-sm text-gray-600">
                    Para se inscrever no evento, você precisa estar logado
                </p>
            </div>

            <!-- Formulário de Login -->
            <form id="loginForm" class="space-y-6">
                <div>
                    <label for="identificacao" class="block text-sm font-medium text-gray-700 mb-2">
                        E-mail
                    </label>
                    <input type="text" id="identificacao" name="identificacao" required
                           class="input-field" placeholder="Digite seu e-mail">
                </div>

                <div>
                    <label for="senha" class="block text-sm font-medium text-gray-700 mb-2">
                        Senha
                    </label>
                    <div class="relative">
                        <input type="password" id="senha" name="senha" required
                               class="input-field pr-10" placeholder="Digite sua senha">
                        <button type="button" id="toggleSenha" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <i class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="text-sm">
                        <a href="#" id="recuperarSenha" class="text-brand-green hover:text-green-700">
                            Esqueceu sua senha?
                        </a>
                    </div>
                </div>

                <div>
                    <button type="submit" class="btn-primary w-full">
                        <i class="fas fa-sign-in-alt mr-2"></i>Entrar
                    </button>
                </div>
            </form>

            <!-- Divisor -->
            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">ou</span>
                    </div>
                </div>
            </div>

            <!-- Link para Cadastro -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Não tem uma conta?
                    <a href="../auth/register.php?redirect=inscricao&evento_id=<?= $evento_id ?>" 
                       class="text-brand-green hover:text-green-700 font-medium">
                        Criar conta agora
                    </a>
                </p>
            </div>
        </div>

        <!-- Informações do Evento -->
        <?php if ($evento_id > 0): ?>
        <div class="card p-6 bg-blue-50 border-blue-200">
            <div class="flex items-center">
                <i class="fas fa-info-circle text-blue-600 mr-3"></i>
                <div>
                    <h3 class="text-sm font-medium text-blue-800">Evento Selecionado</h3>
                    <p class="text-sm text-blue-600">Após fazer login, você será redirecionado para a inscrição no evento.</p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Variáveis globais
window.eventoId = <?= $evento_id ?>;
window.API_BASE = '../../../api';

// Toggle de senha
document.getElementById('toggleSenha').addEventListener('click', function() {
    const senhaInput = document.getElementById('senha');
    const icon = this.querySelector('i');
    
    if (senhaInput.type === 'password') {
        senhaInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        senhaInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});

// Formulário de login
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {
        email: formData.get('identificacao'),
        senha: formData.get('senha')
    };
    
    try {
        // Mostrar loading
        Swal.fire({
            title: 'Entrando...',
            text: 'Verificando suas credenciais',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Fazer login via API existente
        const response = await fetch(`${window.API_BASE}/auth/login.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Login bem-sucedido
            Swal.fire({
                icon: 'success',
                title: 'Login realizado!',
                text: 'Redirecionando para a inscrição...',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                // Redirecionar para página de inscrição
                if (window.eventoId > 0) {
                    window.location.href = `index.php?evento_id=${window.eventoId}`;
                } else {
                    window.location.href = '../public/index.php';
                }
            });
        } else {
            // Erro no login
            Swal.fire({
                icon: 'error',
                title: 'Erro no login',
                text: result.message || 'Credenciais inválidas',
                confirmButtonText: 'Tentar novamente'
            });
        }
    } catch (error) {
        console.error('Erro no login:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro de conexão',
            text: 'Não foi possível conectar ao servidor. Tente novamente.',
            confirmButtonText: 'OK'
        });
    }
});

// Recuperar senha
document.getElementById('recuperarSenha').addEventListener('click', function(e) {
    e.preventDefault();
    
    Swal.fire({
        title: 'Recuperar Senha',
        text: 'Digite seu e-mail para receber instruções de recuperação:',
        input: 'email',
        inputPlaceholder: 'seu@email.com',
        showCancelButton: true,
        confirmButtonText: 'Enviar',
        cancelButtonText: 'Cancelar',
        inputValidator: (value) => {
            if (!value) {
                return 'Por favor, digite seu e-mail!';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Aqui você pode implementar a recuperação de senha
            Swal.fire({
                icon: 'info',
                title: 'Funcionalidade em desenvolvimento',
                text: 'A recuperação de senha será implementada em breve.',
                confirmButtonText: 'OK'
            });
        }
    });
});
</script>

<?php include 'includes/footer-inscricao.php'; ?>
