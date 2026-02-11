<?php
// Iniciar output buffering para evitar problemas de "headers already sent"
ob_start();

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/../../../api/auth/auth.php';

// Verificar se há redirecionamento pendente
$redirect = $_GET['redirect'] ?? '';
$eventoId = $_GET['evento_id'] ?? '';

// Processamento do login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = $_POST['email'] ?? '';
  $password = $_POST['password'] ?? '';

  // Usar o novo sistema de autenticação
  $result = authenticateUser($email, $password);

  if ($result['success']) {
    $user = $result['user'];
    $papel = $user['role'];

    error_log('Login bem-sucedido para: ' . $email . ' | Papel: ' . $papel);

    // Limpar buffer antes de redirecionar
    ob_clean();
    
    // Verificar se há redirecionamento para inscrição
    if ($redirect === 'inscricao' && !empty($eventoId)) {
      error_log('Redirecionando para inscrição do evento: ' . $eventoId);
      header('Location: ../public/detalhes-evento.php?id=' . $eventoId . '&inscricao=true');
      exit;
    }

    // Redirecionar baseado no papel
    if ($papel === 'admin') {
      error_log('Redirecionando para admin dashboard');
      header('Location: ../admin/index.php?page=dashboard');
      exit;
    } elseif ($papel === 'organizador') {
      error_log('Redirecionando para organizador dashboard');
      header('Location: ../organizador/index.php?page=dashboard');
      exit;
    } elseif ($papel === 'participante') {
      error_log('Redirecionando para participante dashboard');
      header('Location: ../participante/index.php?page=dashboard');
      exit;
    } else {
      error_log('Papel não reconhecido: ' . $papel);
      $loginError = 'Tipo de usuário não reconhecido.';
    }
  } else {
    error_log('Login falhou para: ' . $email . ' - ' . $result['message']);
    $loginError = $result['message'];
  }
}

$pageTitle = 'Entrar na sua conta';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="min-h-screen flex flex-col justify-center py-2 sm:py-4 lg:py-8 px-3 sm:px-4 lg:px-8">
  <div class="mx-auto w-full max-w-sm sm:max-w-md">
    <a href="../public/index.php" class="flex items-center justify-center text-brand-green hover:text-green-700 mb-2 sm:mb-3 lg:mb-6">
      <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1 sm:mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
      </svg>
      <span class="text-xs sm:text-sm lg:text-base">Voltar para início</span>
    </a>
    <h2 class="text-center text-lg sm:text-xl lg:text-2xl xl:text-3xl font-bold text-gray-900" id="titulo-login">Entrar na sua conta</h2>
    <?php if ($redirect === 'inscricao'): ?>
      <div class="mt-2 sm:mt-3 lg:mt-4 p-2 sm:p-3 lg:p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <p class="text-center text-xs sm:text-sm text-blue-800">
          <i class="fas fa-info-circle mr-1 sm:mr-2"></i>
          Faça login para continuar com sua inscrição no evento
        </p>
      </div>
    <?php endif; ?>
    <p class="mt-1 sm:mt-2 text-center text-xs sm:text-sm text-gray-600">
      Ou <a href="register.php<?php echo $redirect ? '?redirect=' . $redirect . ($eventoId ? '&evento_id=' . $eventoId : '') : ''; ?>" class="font-medium text-brand-green hover:text-green-700 underline" id="link-criar-conta">criar uma nova conta</a>
    </p>
    <div class="mt-2 sm:mt-3 lg:mt-4 text-center">
      <a href="#" id="link-organizador" class="text-xs sm:text-sm text-gray-500 hover:text-brand-green underline">Entrar como organizador</a>
    </div>
  </div>

  <div class="mt-2 sm:mt-3 lg:mt-4 mx-auto w-full max-w-sm sm:max-w-md">
    <div class="bg-white rounded-lg sm:rounded-xl shadow-lg p-3 sm:p-4 lg:p-6 xl:p-8" id="login-container">

      <?php if (isset($loginError)): ?>
        <div class="mb-2 sm:mb-3 lg:mb-4 p-2 sm:p-3 lg:p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg text-xs sm:text-sm lg:text-base">
          <?php echo htmlspecialchars($loginError); ?>
        </div>
      <?php endif; ?>

      <form method="post" action="login.php" class="space-y-2 sm:space-y-3 lg:space-y-4 xl:space-y-6">
        <div>
          <label for="email" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 text-center">E-mail</label>
          <input id="email" name="email" type="email" required class="block w-full rounded-lg border border-gray-300 px-2 sm:px-3 lg:px-4 py-2 sm:py-2.5 lg:py-3 focus:ring-2 focus:ring-brand-green focus:border-brand-green text-gray-900 text-xs sm:text-sm lg:text-base text-center" placeholder="seu@email.com">
        </div>
        <div>
          <label for="password" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 text-center">Senha</label>
          <div class="relative">
            <input id="password" name="password" type="password" required class="block w-full rounded-lg border border-gray-300 px-2 sm:px-3 lg:px-4 py-2 sm:py-2.5 lg:py-3 pr-8 sm:pr-10 focus:ring-2 focus:ring-brand-green focus:border-brand-green text-gray-900 text-xs sm:text-sm lg:text-base text-center" placeholder="Sua senha">
            <span id="toggle-password" class="absolute inset-y-0 right-0 pr-2 sm:pr-3 flex items-center cursor-pointer">
              <svg class="h-3 w-3 sm:h-4 sm:w-4 lg:h-5 lg:w-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268 2.943-9.542 7z" />
              </svg>
            </span>
          </div>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center justify-center gap-1 sm:gap-2 lg:gap-4">
          <div class="flex items-center justify-center">
            <input id="remember-me" name="remember-me" type="checkbox" class="h-3 w-3 sm:h-4 sm:w-4 text-brand-green focus:ring-brand-green border-gray-300 rounded">
            <label for="remember-me" class="ml-1 sm:ml-2 block text-xs sm:text-sm text-gray-900">Lembrar de mim</label>
          </div>
          <div class="text-xs sm:text-sm text-center">
            <a href="#" id="abrir-modal-recuperar" class="font-medium text-brand-green hover:text-green-700 underline">Esqueceu a senha?</a>
          </div>
        </div>
        <button type="submit" class="w-full flex justify-center items-center py-2 sm:py-2.5 lg:py-3 px-3 sm:px-4 border border-transparent rounded-lg shadow-sm text-xs sm:text-sm lg:text-base font-medium text-white bg-brand-green hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-green transition">
          <svg class="w-3 h-3 sm:w-4 sm:h-4 lg:w-5 lg:h-5 mr-1 sm:mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M5 12h14M12 5l7 7-7 7" />
          </svg>
          Entrar
        </button>
      </form>
    </div>
  </div>
</div>

<!-- Modal Recuperar Senha -->
<div id="modal-recuperar" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
  <div class="bg-white rounded-lg sm:rounded-xl shadow-lg p-3 sm:p-4 lg:p-6 xl:p-8 w-full max-w-sm sm:max-w-md mx-2 sm:mx-3 lg:mx-4 relative">
    <button id="fechar-modal-recuperar" class="absolute top-1 right-1 sm:top-2 sm:right-2 text-gray-400 hover:text-gray-700 text-lg sm:text-xl lg:text-2xl">&times;</button>
    <h2 class="text-base sm:text-lg lg:text-xl font-bold mb-2 sm:mb-3 lg:mb-4 text-center">Recuperar senha</h2>
    <form id="form-recuperar-senha" class="space-y-2 sm:space-y-3 lg:space-y-4">
      <div>
        <label for="recuperar-email" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 text-center">E-mail cadastrado</label>
        <input id="recuperar-email" name="email" type="email" required class="block w-full rounded-lg border border-gray-300 px-2 sm:px-3 lg:px-4 py-2 sm:py-2.5 lg:py-3 focus:ring-2 focus:ring-brand-green focus:border-brand-green text-gray-900 text-xs sm:text-sm lg:text-base text-center" placeholder="seu@email.com">
      </div>
      <button type="submit" class="w-full py-2 sm:py-2.5 lg:py-3 px-3 sm:px-4 rounded-lg bg-brand-green text-white font-medium hover:bg-green-700 transition text-xs sm:text-sm lg:text-base">Enviar link de recuperação</button>
    </form>
  </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
<script>
  // Controle do modo organizador
  let modoOrganizador = false;

  function alternarModoOrganizador() {
    modoOrganizador = !modoOrganizador;

    const titulo = document.getElementById('titulo-login');
    const linkCriarConta = document.getElementById('link-criar-conta');
    const linkOrganizador = document.getElementById('link-organizador');
    const loginContainer = document.getElementById('login-container');
    const paragrafoCriarConta = linkCriarConta.parentElement;

    if (modoOrganizador) {
      // Modo organizador ativo
      titulo.textContent = 'Área do Organizador';
      titulo.classList.add('text-brand-green');
      paragrafoCriarConta.style.display = 'none';
      linkOrganizador.textContent = 'Voltar ao login de usuário';
      loginContainer.classList.add('border-2', 'border-brand-green', 'bg-green-50');

      // Adicionar indicador visual
      const indicador = document.createElement('div');
      indicador.className = 'mb-4 p-3 bg-green-100 border border-brand-green rounded-lg';
      indicador.innerHTML = '<p class="text-center text-sm text-brand-green"><i class="fas fa-crown mr-2"></i>Área exclusiva para organizadores</p>';
      loginContainer.insertBefore(indicador, loginContainer.firstChild);
    } else {
      // Modo normal
      titulo.textContent = 'Entrar na sua conta';
      titulo.classList.remove('text-brand-green');
      paragrafoCriarConta.style.display = 'block';
      linkOrganizador.textContent = 'Entrar como organizador';
      loginContainer.classList.remove('border-2', 'border-brand-green', 'bg-green-50');

      // Remover indicador visual
      const indicador = loginContainer.querySelector('.bg-green-100');
      if (indicador) {
        indicador.remove();
      }
    }
  }

  // Event listeners
  document.getElementById('link-organizador').onclick = function(e) {
    e.preventDefault();
    alternarModoOrganizador();
  };

  // Abrir/fechar modal
  const modal = document.getElementById('modal-recuperar');
  document.getElementById('abrir-modal-recuperar').onclick = e => {
    e.preventDefault();
    modal.classList.remove('hidden');
  };
  document.getElementById('fechar-modal-recuperar').onclick = () => modal.classList.add('hidden');
  window.onclick = e => {
    if (e.target === modal) modal.classList.add('hidden');
  };
  // Envio do formulário
  const formRec = document.getElementById('form-recuperar-senha');
  formRec.onsubmit = function(e) {
    e.preventDefault();
    const email = document.getElementById('recuperar-email').value.trim();
    if (!email) return;
    fetch('../../../api/auth/recuperar_senha.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          email
        })
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
          formRec.reset();
        }
      })
      .catch(() => {
        Swal.fire('Erro', 'Não foi possível enviar o e-mail. Tente novamente.', 'error');
      });
  };

  const togglePwd = document.getElementById('toggle-password');
  if (togglePwd) {
    togglePwd.onclick = function() {
      const input = document.getElementById('password');
      if (!input) return;
      input.type = input.type === 'password' ? 'text' : 'password';
    };
  }
</script>

<?php include '../../includes/footer.php'; ?>
