<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__, 3) . '/api/admin/auth_middleware.php';

// Rate limiter opcional - verifica se arquivo existe
$rate_limiter_path = dirname(__DIR__, 3) . '/api/admin/rate_limiter.php';
if (file_exists($rate_limiter_path)) {
    require_once $rate_limiter_path;
    $rate_limiter_available = true;
} else {
    $rate_limiter_available = false;
}

if (verificarAdmin()) {
    header('Location: ../admin/index.php?page=dashboard');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rateCheck = ['allowed' => true];
    
    if ($rate_limiter_available && class_exists('RateLimiter')) {
        $rateCheck = RateLimiter::checkRateLimit();
        
        if (!$rateCheck['allowed']) {
            $remainingTime = RateLimiter::getRemainingTimeFormatted($rateCheck['remaining_time']);
            $error = 'Muitas tentativas de login. Tente novamente em ' . $remainingTime;
        }
    }
    
    if ($rateCheck['allowed']) {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $error = 'Preencha todos os campos';
            if ($rate_limiter_available && class_exists('RateLimiter')) {
                RateLimiter::recordAttempt($email, false);
            }
        } else {
            $result = autenticarAdmin($email, $password);
            
            if ($result['success']) {
                if ($rate_limiter_available && class_exists('RateLimiter')) {
                    RateLimiter::recordAttempt($email, true);
                }
                header('Location: ../admin/index.php');
                exit();
            } else {
                if ($rate_limiter_available && class_exists('RateLimiter')) {
                    RateLimiter::recordAttempt($email, false);
                }
                $error = 'Credenciais inválidas';
            }
        }
    }
}

// Calcular caminho base para esta página específica
$project_base = '';
if (isset($_SERVER['REQUEST_URI'])) {
    $request_uri = $_SERVER['REQUEST_URI'];
    if (preg_match('#^/([^/]+)/panel/#', $request_uri, $matches)) {
        $project_base = '/' . $matches[1];
    }
}

// Se não encontrou, tentar pelo SCRIPT_NAME
if (empty($project_base) && isset($_SERVER['SCRIPT_NAME'])) {
    $script_name = $_SERVER['SCRIPT_NAME'];
    $path_parts = explode('/', trim($script_name, '/'));
    if (isset($path_parts[0]) && $path_parts[0] !== '' && $path_parts[0] !== 'api' && $path_parts[0] !== 'frontend') {
        $project_base = '/' . $path_parts[0];
    }
}

// Função helper para assets
$asset_base = $project_base . '/frontend/assets';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Administrativo - MovAmazon</title>
    
    <!-- Tailwind CSS Compilado -->
    <link rel="stylesheet" href="<?php echo $asset_base; ?>/css/tailwind.min.css">
    
    <!-- CSS Customizado -->
    <link rel="stylesheet" href="<?php echo $asset_base; ?>/css/main.css">
    <link rel="stylesheet" href="<?php echo $asset_base; ?>/css/custom.css">
    
    <!-- Font Awesome para ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --brand-green: #0b4340;
            --brand-yellow: #f5c113;
        }
        /* Botão visível e destacado mesmo sem Tailwind */
        .admin-login-btn {
            width: 100%;
            border: none;
            border-radius: 0.75rem;
            background: linear-gradient(90deg, #0b4340, #0f6b65);
            color: #fff;
            font-weight: 700;
            padding: 0.9rem 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: 0 10px 25px rgba(15, 107, 101, 0.2);
            transition: transform 0.15s ease, box-shadow 0.15s ease, filter 0.15s ease;
        }
        .admin-login-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 30px rgba(15, 107, 101, 0.28);
            filter: brightness(1.02);
        }
        .admin-login-btn:active {
            transform: translateY(0);
            box-shadow: 0 8px 18px rgba(15, 107, 101, 0.22);
        }
        .admin-login-btn:focus-visible {
            outline: 2px solid rgba(15, 107, 101, 0.35);
            outline-offset: 3px;
        }
        /* Evitar que algum reset externo esconda o botão */
        .admin-login-btn:disabled {
            opacity: 0.65;
            cursor: not-allowed;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">

<div class="min-h-screen bg-gradient-to-br from-green-50 to-teal-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="bg-white rounded-xl shadow-lg p-8">
            <!-- Logo e Nome do Sistema -->
            <div class="mb-6 pb-4 border-2 border-red-300 rounded-lg bg-green-50 px-4 py-3">
                <div class="flex items-center justify-center gap-3">
                    <img src="<?php echo $asset_base; ?>/img/logo.png" alt="MovAmazon" class="h-10 w-auto">
                    <span class="text-2xl font-bold text-gray-900">MovAmazon</span>
                </div>
            </div>

            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Painel Administrativo</h2>
            </div>

            <?php if ($error): ?>
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2"></i>E-mail
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required 
                        autofocus
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition"
                        placeholder="admin@movamazon.com"
                    >
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2"></i>Senha
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition"
                        placeholder="••••••••"
                    >
                </div>

                <div>
                    <button 
                        type="submit" 
                        class="admin-login-btn"
                    >
                        <i class="fas fa-sign-in-alt"></i>
                        Entrar
                    </button>
                </div>
            </form>
        </div>

        <div class="text-center text-sm text-gray-600">
            <p>Acesso restrito a administradores autorizados</p>
        </div>
    </div>
</div>

</body>
</html>

