<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../../api/auth/auth.php';

// Se ja esta logado como assessor, redirecionar
if (isLoggedIn() && (hasRole('assessoria_admin') || hasRole('assessor'))) {
    header('Location: ../index.php?page=dashboard');
    exit();
}

$erro = $_GET['erro'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessoria de Corrida - MovAmazon</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../../../assets/img/logo.png">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .input-focus:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(139, 92, 246, 0.5);
            border-color: rgba(139, 92, 246, 0.8);
        }
        .fade-in {
            animation: fadeIn 0.4s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .tab-active {
            border-bottom: 3px solid #8B5CF6;
            color: #7C3AED;
            font-weight: 600;
        }
        .tab-inactive {
            border-bottom: 3px solid transparent;
            color: #6B7280;
        }
        .cpf-cnpj-toggle { transition: all 0.3s ease; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-purple-600 to-purple-800 p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="p-8">
                <!-- Logo e titulo -->
                <div class="text-center mb-6">
                    <div class="flex items-center justify-center gap-2 mb-2">
                        <img src="../../../assets/img/logo.png" alt="MovAmazon" class="h-10 w-10" onerror="this.style.display='none'">
                        <h1 class="text-2xl font-bold text-purple-600">MovAmazon</h1>
                    </div>
                    <p class="text-gray-500 text-sm">Assessoria de Corrida</p>
                </div>

                <!-- Abas Login / Cadastro -->
                <div class="flex mb-6 border-b">
                    <button id="login-tab" class="flex-1 py-3 text-center cursor-pointer tab-active transition-all duration-200">
                        Login
                    </button>
                    <button id="register-tab" class="flex-1 py-3 text-center cursor-pointer tab-inactive transition-all duration-200">
                        Cadastro
                    </button>
                </div>

                <!-- Erro de acesso -->
                <?php if ($erro === 'acesso_negado'): ?>
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                    Acesso negado. Faca login como assessor.
                </div>
                <?php endif; ?>

                <!-- ===== FORMULARIO DE LOGIN ===== -->
                <div id="login-form" class="fade-in">
                    <form id="login-form-element" class="space-y-5">
                        <div>
                            <label for="login-email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" id="login-email" name="email" 
                                   class="input-focus w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm"
                                   placeholder="seu@email.com" required>
                        </div>
                        <div>
                            <label for="login-senha" class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                            <input type="password" id="login-senha" name="senha" 
                                   class="input-focus w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm"
                                   placeholder="Sua senha" required>
                        </div>
                        <button type="submit" id="login-btn"
                                class="w-full py-3 px-4 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-semibold text-sm transition-colors">
                            Entrar
                        </button>
                        <div id="login-feedback" class="text-center text-sm"></div>
                    </form>
                </div>

                <!-- ===== FORMULARIO DE CADASTRO ===== -->
                <div id="register-form" class="fade-in hidden">
                    <form id="register-form-element" class="space-y-4">
                        <div>
                            <label for="reg-nome" class="block text-sm font-medium text-gray-700 mb-1">Nome Completo</label>
                            <input type="text" id="reg-nome" name="nome"
                                   class="input-focus w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm"
                                   placeholder="Seu nome completo" required>
                        </div>
                        <div>
                            <label for="reg-email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" id="reg-email" name="email"
                                   class="input-focus w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm"
                                   placeholder="seu@email.com" required>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="reg-senha" class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                                <input type="password" id="reg-senha" name="senha"
                                       class="input-focus w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm"
                                       placeholder="Min. 6 caracteres" required minlength="6">
                            </div>
                            <div>
                                <label for="reg-confirmar-senha" class="block text-sm font-medium text-gray-700 mb-1">Confirmar</label>
                                <input type="password" id="reg-confirmar-senha" name="confirmar_senha"
                                       class="input-focus w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm"
                                       placeholder="Repita a senha" required minlength="6">
                            </div>
                        </div>
                        <div>
                            <label for="reg-cref" class="block text-sm font-medium text-gray-700 mb-1">CREF</label>
                            <input type="text" id="reg-cref" name="cref"
                                   class="input-focus w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm"
                                   placeholder="Ex: 000000-G/AM" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Pessoa</label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="tipo" value="PF" checked 
                                           class="text-purple-600 focus:ring-purple-500" id="tipo-pf">
                                    <span class="text-sm text-gray-700">Pessoa Fisica</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="tipo" value="PJ" 
                                           class="text-purple-600 focus:ring-purple-500" id="tipo-pj">
                                    <span class="text-sm text-gray-700">Pessoa Juridica</span>
                                </label>
                            </div>
                        </div>
                        <div>
                            <label for="reg-cpf-cnpj" class="block text-sm font-medium text-gray-700 mb-1">
                                <span id="label-doc">CPF</span>
                            </label>
                            <input type="text" id="reg-cpf-cnpj" name="cpf_cnpj"
                                   class="input-focus w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm"
                                   placeholder="000.000.000-00" required>
                        </div>
                        <div id="nome-fantasia-wrapper" class="hidden">
                            <label for="reg-nome-fantasia" class="block text-sm font-medium text-gray-700 mb-1">Nome Fantasia</label>
                            <input type="text" id="reg-nome-fantasia" name="nome_fantasia"
                                   class="input-focus w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm"
                                   placeholder="Nome da assessoria">
                        </div>
                        <div>
                            <label for="reg-telefone" class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                            <input type="tel" id="reg-telefone" name="telefone"
                                   class="input-focus w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm"
                                   placeholder="(00) 00000-0000">
                        </div>
                        <div class="flex items-start gap-2">
                            <input type="checkbox" id="reg-termos" name="termos" required
                                   class="mt-1 h-4 w-4 text-purple-600 border-gray-300 rounded">
                            <label for="reg-termos" class="text-xs text-gray-600">
                                Concordo com os <a href="#" class="text-purple-600 hover:underline">Termos de Uso</a> 
                                e <a href="#" class="text-purple-600 hover:underline">Politica de Privacidade</a>
                            </label>
                        </div>
                        <button type="submit" id="register-btn"
                                class="w-full py-3 px-4 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-semibold text-sm transition-colors">
                            Cadastrar
                        </button>
                        <div id="register-feedback" class="text-center text-sm"></div>
                    </form>
                </div>

                <!-- Link voltar -->
                <div class="text-center mt-6">
                    <a href="../../public/index.php" class="text-sm text-gray-500 hover:text-purple-600 transition-colors">
                        ← Voltar para MovAmazon
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../../js/assessoria/auth.js"></script>
</body>
</html>
