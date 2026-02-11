document.addEventListener('DOMContentLoaded', function() {
    console.log('JS de registro carregado');
    const form = document.getElementById('form-registro');
    const submitBtn = form.querySelector('button[type="submit"]');
    const msgDiv = document.getElementById('msg-registro');
    const originalText = submitBtn.innerHTML;
    const toggleSenha = document.getElementById('toggle-senha');
    const toggleConfirmar = document.getElementById('toggle-confirmar-senha');
    const inputSenha = document.getElementById('senha');
    const inputConfirmar = document.getElementById('confirmar_senha');
    
    if (!form) {
        console.error('Formulário não encontrado');
        return;
    }
    
    if (toggleSenha && inputSenha) {
        toggleSenha.onclick = function() {
            inputSenha.type = inputSenha.type === 'password' ? 'text' : 'password';
        };
    }

    if (toggleConfirmar && inputConfirmar) {
        toggleConfirmar.onclick = function() {
            inputConfirmar.type = inputConfirmar.type === 'password' ? 'text' : 'password';
        };
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Formulário submetido');
        
        // Limpar mensagens anteriores
        msgDiv.innerHTML = '';
        msgDiv.className = '';
        
        // Validar confirmação de senha
        const senha = form.querySelector('[name="senha"]').value;
        const confirmarSenha = form.querySelector('[name="confirmar_senha"]').value;
        
        console.log('Validando senhas...');
        if (senha !== confirmarSenha) {
            showMessage('As senhas não coincidem.', 'error');
            return;
        }
        
        // Desabilitar botão e mostrar loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg class="animate-spin w-5 h-5 mr-2 fill-none viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3.42 7.938l3-2.647z"></path></svg>Processando...';
        
        // Coletar dados do formulário
        const formData = new FormData(form);
        
        // Remover campo de confirmação de senha (não enviar para API)
        formData.delete('confirmar_senha');
        
        // Log dos dados sendo enviados
        console.log('Dados do formulário:');
        for (let [key, value] of formData.entries()) {
            console.log(key + ': ' + value);
        }
        
        // Enviar via AJAX
        console.log('Enviando para API...');
        fetch('../../../api/auth/register.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Resposta da API:', data);
            
            if (data.success) {
                // SweetAlert para sucesso
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    form.reset();
                    window.location.href = 'login.php';
                });
            } else {
                // SweetAlert para erro
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: data.message
                });
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: 'Erro ao processar formulário. Tente novamente.'
            });
        })
        .finally(() => {
            // Reabilitar botão
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
    
    function showMessage(message, type) {
        console.log('Mostrando mensagem:', message, type);
        msgDiv.textContent = message;
        msgDiv.className = `p-3 rounded-lg ${type === 'success' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200'}`;
    }
}); 