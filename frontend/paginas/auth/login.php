<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../api/auth/auth.php';
require_once __DIR__ . '/../../../api/auth/middleware.php';

// Verificar se já está logado
if (isLoggedIn()) {
    $papel = $_SESSION['papel'] ?? 'participante';
    redirectByRole($papel);
}

// Parâmetros da URL
$area = $_GET['area'] ?? 'auto';
$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? '';
$eventoId = $_GET['evento_id'] ?? $_POST['evento_id'] ?? '';
$inscricaoId = $_GET['inscricao_id'] ?? $_POST['inscricao_id'] ?? '';
$retornoPagamento = $_GET['retorno_pagamento'] ?? $_POST['retorno_pagamento'] ?? '';
$erro = $_GET['erro'] ?? '';

$allowedAreas = ['auto', 'participante', 'organizador'];
if (!in_array($area, $allowedAreas)) {
    $area = 'auto';
}

$loginError = '';
$areaSelecionada = $area;

// Processamento do login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $selectedArea = $_POST['area'] ?? 'auto';

    // Autenticação normal (participante/organizador)
    $result = authenticateUser($email, $password);

    if ($result['success']) {
        $user = $result['user'];
        $papel = $user['role'];

        error_log('Login bem-sucedido para: ' . $email . ' | Papel: ' . $papel . ' | Área selecionada: ' . $selectedArea);

        // Normalizar área selecionada: 'auto' = 'participante' para validação
        $areaParaValidacao = ($selectedArea === 'auto') ? 'participante' : $selectedArea;

        // VALIDAÇÃO RIGOROSA: Verificar se o papel corresponde à área selecionada
        if ($papel !== $areaParaValidacao) {
            $loginError = 'Você não tem acesso à área de ' . ucfirst($areaParaValidacao) . '. Seu perfil é: ' . ucfirst($papel) . '. Por favor, selecione a aba correta.';
            $areaSelecionada = $selectedArea;
            error_log('Acesso negado: Usuário ' . $email . ' (papel: ' . $papel . ') tentou acessar área ' . $areaParaValidacao);
        } else {
            // Verificar se há redirecionamento para inscrição (apenas para participantes)
            if ($redirect === 'inscricao' && !empty($eventoId) && $papel === 'participante') {
                error_log('Redirecionando para inscrição do evento: ' . $eventoId);
                header('Location: ../public/detalhes-evento.php?id=' . $eventoId . '&inscricao=true');
                exit;
            }

            // Retorno do pagamento: ir para Minhas Inscrições (participante)
            if ($redirect === 'minhas-inscricoes' && $papel === 'participante') {
                $url = '../../paginas/participante/index.php?page=minhas-inscricoes';
                if (!empty($inscricaoId)) {
                    $url .= '&inscricao_id=' . (int) $inscricaoId;
                }
                if (!empty($retornoPagamento)) {
                    $url .= '&retorno_pagamento=1';
                }
                header('Location: ' . $url);
                exit;
            }

            // Redirecionar baseado no papel (a função já valida internamente)
            redirectByRole($papel, $selectedArea);
        }
    } else {
        error_log('Login falhou para: ' . $email . ' - ' . $result['message']);
        $loginError = $result['message'];
    }
}

// Mensagens de erro específicas
if ($erro === 'acesso_negado') {
    $loginError = 'Você não tem permissão para acessar esta área.';
}

$pageTitle = 'Entrar na sua conta';
include '../../includes/header.php';
include '../../includes/navbar.php';

// Definir título e cores por área
$areaInfo = [
    'auto' => ['titulo' => 'Entrar na sua conta', 'cor' => 'brand-green', 'icone' => 'fa-user'],
    'participante' => ['titulo' => 'Área do Participante', 'cor' => 'brand-green', 'icone' => 'fa-user'],
    'organizador' => ['titulo' => 'Área do Organizador', 'cor' => 'brand-green', 'icone' => 'fa-user-tie']
];

$info = $areaInfo[$areaSelecionada] ?? $areaInfo['auto'];
?>
<div class="min-h-screen flex flex-col justify-center py-2 sm:py-4 lg:py-8 px-3 sm:px-4 lg:px-8 bg-gray-50">
  <div class="mx-auto w-full max-w-md">
    <a href="../public/index.php" class="flex items-center justify-center text-brand-green hover:text-green-700 mb-4">
      <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1 sm:mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
      </svg>
      <span class="text-xs sm:text-sm lg:text-base">Voltar para início</span>
    </a>

    <!-- Seleção de Área (Tabs) -->
    <div class="bg-white rounded-lg shadow-md mb-4 overflow-hidden">
      <div class="flex border-b border-gray-200">
        <button type="button" onclick="selecionarArea('auto')" class="flex-1 px-4 py-3 text-sm font-medium transition-colors <?php echo $areaSelecionada === 'auto' ? 'bg-brand-green text-white border-b-2 border-brand-yellow' : 'text-gray-600 hover:text-brand-green hover:bg-gray-50'; ?>">
          <i class="fas fa-user mr-2"></i>
          <span class="hidden sm:inline">Participante</span>
        </button>
        <button type="button" onclick="selecionarArea('organizador')" class="flex-1 px-4 py-3 text-sm font-medium transition-colors <?php echo $areaSelecionada === 'organizador' ? 'bg-brand-green text-white border-b-2 border-brand-yellow' : 'text-gray-600 hover:text-brand-green hover:bg-gray-50'; ?>">
          <i class="fas fa-user-tie mr-2"></i>
          <span class="hidden sm:inline">Organizador</span>
        </button>
      </div>
    </div>

    <!-- Título -->
    <h2 class="text-center text-lg sm:text-xl lg:text-2xl font-bold text-gray-900 mb-2">
      <i class="fas <?php echo $info['icone']; ?> mr-2 text-<?php echo $info['cor']; ?>"></i>
      <?php echo $info['titulo']; ?>
    </h2>

    <?php if ($redirect === 'inscricao'): ?>
      <div class="mt-2 p-3 bg-blue-50 border border-blue-200 rounded-lg mb-4">
        <p class="text-center text-sm text-blue-800">
          <i class="fas fa-info-circle mr-2"></i>
          Faça login para continuar com sua inscrição no evento
        </p>
      </div>
    <?php endif; ?>

    <?php if ($retornoPagamento && ($areaSelecionada === 'auto' || $areaSelecionada === 'participante')): ?>
      <div class="mt-2 p-3 bg-amber-50 border border-amber-200 rounded-lg mb-4">
        <p class="text-center text-sm text-amber-800 font-medium">
          <i class="fas fa-check-circle mr-2"></i>
          Acesse sua área para confirmar sua inscrição.
        </p>
      </div>
    <?php endif; ?>

    <!-- Formulário de Login -->
    <div class="bg-white rounded-lg sm:rounded-xl shadow-lg p-4 sm:p-6 lg:p-8">
      <?php if ($loginError): ?>
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
          <i class="fas fa-exclamation-circle mr-2"></i>
          <?php echo htmlspecialchars($loginError); ?>
        </div>
      <?php endif; ?>

      <?php if ($areaSelecionada === 'organizador'): ?>
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg">
          <p class="text-center text-sm text-green-800">
            <i class="fas fa-crown mr-2"></i>
            Área exclusiva para organizadores de eventos. Participantes devem usar a aba "Participante".
          </p>
        </div>
      <?php elseif ($areaSelecionada === 'auto' || $areaSelecionada === 'participante'): ?>
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg">
          <p class="text-center text-sm text-green-800">
            <i class="fas fa-user mr-2"></i>
            Área do participante. Organizadores devem usar a aba "Organizador".
          </p>
        </div>
      <?php endif; ?>

      <form method="post" action="login.php" class="space-y-4">
        <input type="hidden" name="area" id="area-input" value="<?php echo htmlspecialchars($areaSelecionada); ?>">
        <?php if ($redirect): ?><input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>"><?php endif; ?>
        <?php if ($eventoId): ?><input type="hidden" name="evento_id" value="<?php echo htmlspecialchars($eventoId); ?>"><?php endif; ?>
        <?php if ($inscricaoId): ?><input type="hidden" name="inscricao_id" value="<?php echo htmlspecialchars($inscricaoId); ?>"><?php endif; ?>
        <?php if ($retornoPagamento): ?><input type="hidden" name="retorno_pagamento" value="1"><?php endif; ?>
        
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
          <input id="email" name="email" type="email" required autofocus
            class="block w-full rounded-lg border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-brand-green focus:border-brand-green text-gray-900"
            placeholder="seu@email.com">
        </div>

        <div>
          <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
          <div class="relative">
            <input id="password" name="password" type="password" required
              class="block w-full rounded-lg border border-gray-300 px-4 py-3 pr-10 focus:ring-2 focus:ring-brand-green focus:border-brand-green text-gray-900"
              placeholder="Sua senha">
            <span id="toggle-password" class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer">
              <i class="fas fa-eye text-gray-400"></i>
            </span>
          </div>
        </div>

        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-brand-green focus:ring-brand-green border-gray-300 rounded">
            <label for="remember-me" class="ml-2 block text-sm text-gray-900">Lembrar de mim</label>
          </div>
          <div class="text-sm">
            <a href="#" id="abrir-modal-recuperar" class="font-medium text-brand-green hover:text-green-700">Esqueceu a senha?</a>
          </div>
        </div>

        <button type="submit" class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-brand-green hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-green transition">
          <i class="fas fa-sign-in-alt mr-2"></i>
          Entrar
        </button>
      </form>

      <?php if ($areaSelecionada === 'auto' || $areaSelecionada === 'participante'): ?>
        <div class="mt-4 text-center">
          <p class="text-sm text-gray-600">
            Não tem uma conta? 
            <a href="register.php<?php echo $redirect ? '?redirect=' . $redirect . ($eventoId ? '&evento_id=' . $eventoId : '') : ''; ?>" class="font-medium text-brand-green hover:text-green-700 underline">
              Criar conta
            </a>
          </p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Modal Recuperar Senha -->
<div id="modal-recuperar" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
  <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md mx-4 relative">
    <button id="fechar-modal-recuperar" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
    <h2 class="text-lg font-bold mb-4 text-center">Recuperar senha</h2>
    <form id="form-recuperar-senha" class="space-y-4">
      <div>
        <label for="recuperar-email" class="block text-sm font-medium text-gray-700 mb-1">E-mail cadastrado</label>
        <input id="recuperar-email" name="email" type="email" required
          class="block w-full rounded-lg border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-brand-green focus:border-brand-green"
          placeholder="seu@email.com">
      </div>
      <button type="submit" class="w-full py-3 px-4 rounded-lg bg-brand-green text-white font-medium hover:bg-green-700 transition">
        Enviar link de recuperação
      </button>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
<script>
  let areaAtual = '<?php echo $areaSelecionada; ?>';

  function selecionarArea(area) {
    areaAtual = area;
    document.getElementById('area-input').value = area;
    
    // Atualizar URL sem recarregar
    const url = new URL(window.location);
    url.searchParams.set('area', area);
    window.history.pushState({}, '', url);
    
    // Recarregar página para atualizar interface
    window.location.href = url.toString();
  }

  // Toggle senha
  document.getElementById('toggle-password').addEventListener('click', function() {
    const input = document.getElementById('password');
    const icon = this.querySelector('i');
    if (input.type === 'password') {
      input.type = 'text';
      icon.classList.remove('fa-eye');
      icon.classList.add('fa-eye-slash');
    } else {
      input.type = 'password';
      icon.classList.remove('fa-eye-slash');
      icon.classList.add('fa-eye');
    }
  });

  // Modal recuperar senha
  const modal = document.getElementById('modal-recuperar');
  document.getElementById('abrir-modal-recuperar').addEventListener('click', function(e) {
    e.preventDefault();
    modal.classList.remove('hidden');
  });
  
  document.getElementById('fechar-modal-recuperar').addEventListener('click', function() {
    modal.classList.add('hidden');
  });
  
  window.addEventListener('click', function(e) {
    if (e.target === modal) {
      modal.classList.add('hidden');
    }
  });

  // Formulário recuperar senha
  document.getElementById('form-recuperar-senha').addEventListener('submit', function(e) {
    e.preventDefault();
    const email = document.getElementById('recuperar-email').value.trim();
    if (!email) return;
    
    fetch('../../../api/auth/recuperar_senha.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ email })
    })
    .then(r => r.json())
    .then(data => {
      Swal.fire({
        icon: data.success ? 'success' : 'error',
        title: data.success ? 'Verifique seu e-mail!' : 'Erro',
        text: data.message
      });
      if (data.success) {
        modal.classList.add('hidden');
        document.getElementById('form-recuperar-senha').reset();
      }
    })
    .catch(() => {
      Swal.fire('Erro', 'Não foi possível enviar o e-mail. Tente novamente.', 'error');
    });
  });
</script>
<?php include '../../includes/footer.php'; ?>
