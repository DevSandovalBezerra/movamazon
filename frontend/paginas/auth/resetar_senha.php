<?php
$pageTitle = 'Redefinir senha';
include '../../includes/header.php';
include '../../includes/navbar.php';
?>
<div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8">
  <div class="sm:mx-auto sm:w-full sm:max-w-md">
    <h2 class="text-center text-3xl font-bold text-gray-900">Redefinir senha</h2>
    <p class="mt-2 text-center text-sm text-gray-600">
      Digite sua nova senha abaixo.
    </p>
  </div>
</div>

<!-- Modal Redefinir Senha -->
<div id="modal-resetar" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40" style="display: none;">
  <div class="bg-white rounded-xl shadow-lg p-8 w-full max-w-md relative">
    <h2 class="text-xl font-bold mb-4">Nova senha</h2>
    <form id="form-resetar-senha" class="space-y-4">
      <input type="hidden" id="reset-token" name="token">
      <div>
        <label for="nova-senha" class="block text-sm font-medium text-gray-700 mb-1">Nova senha</label>
        <input id="nova-senha" name="senha" type="password" required minlength="6" class="block w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900" placeholder="Mínimo 6 caracteres">
      </div>
      <div>
        <label for="confirmar-senha" class="block text-sm font-medium text-gray-700 mb-1">Confirmar nova senha</label>
        <input id="confirmar-senha" name="confirmar_senha" type="password" required minlength="6" class="block w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900" placeholder="Confirme a nova senha">
      </div>
      <button type="submit" class="w-full py-2 px-4 rounded-lg bg-primary-600 text-white font-medium hover:bg-primary-700 transition">Redefinir senha</button>
    </form>
  </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
<script>
// Pega token da URL
function getToken() {
  const params = new URLSearchParams(window.location.search);
  return params.get('token') || '';
}

// Verifica se há token na URL
const token = getToken();
if (token) {
  // Preenche o campo hidden com o token
  document.getElementById('reset-token').value = token;
  // Mostra o modal
  document.getElementById('modal-resetar').style.display = 'flex';
} else {
  // Se não há token, mostra erro
  Swal.fire('Erro', 'Link inválido para redefinição de senha.', 'error');
}

// Envio do formulário
const formReset = document.getElementById('form-resetar-senha');
formReset.onsubmit = function(e) {
  e.preventDefault();
  const senha = document.getElementById('nova-senha').value.trim();
  const confirmar = document.getElementById('confirmar-senha').value.trim();
  
  if (!senha || senha.length < 6) {
    Swal.fire('Atenção', 'A senha deve ter pelo menos 6 caracteres.', 'warning');
    return;
  }
  
  if (senha !== confirmar) {
    Swal.fire('Atenção', 'As senhas não coincidem.', 'warning');
    return;
  }
  
  fetch('../../../api/auth/resetar_senha.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ token, senha })
  })
  .then(r => r.json())
  .then(data => {
    Swal.fire({
      icon: data.success ? 'success' : 'error',
      title: data.success ? 'Senha redefinida!' : 'Erro',
      text: data.message
    });
    if (data.success) {
      setTimeout(() => { window.location.href = 'login.php'; }, 2000);
    }
  })
  .catch(() => {
    Swal.fire('Erro', 'Não foi possível redefinir a senha. Tente novamente.', 'error');
  });
};
</script>

<?php include '../../includes/footer.php'; ?> 
