<?php
// Iniciar output buffering
if (!ob_get_level()) {
    ob_start();
}

$pageTitle = 'Criar Conta';

// Verificar se há redirecionamento pendente
$redirect = $_GET['redirect'] ?? '';
$evento_id = isset($_GET['evento_id']) ? intval($_GET['evento_id']) : 0;

include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full space-y-8">
        <!-- Card de Cadastro -->
        <div class="bg-white rounded-xl shadow-lg p-8">
            <div class="text-center mb-8">
                <div class="mx-auto h-12 w-12 bg-primary-600 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-user-plus text-white text-xl"></i>
                </div>
                <h2 class="text-3xl font-bold text-gray-900">Criar Conta</h2>
                <p class="mt-2 text-sm text-gray-600">
                    Preencha os dados abaixo para se cadastrar<?php echo $redirect === 'inscricao' ? ' e se inscrever no evento' : ''; ?>
                </p>
            </div>

            <!-- Formulário de Cadastro -->
            <form id="registerForm" class="space-y-6">
                <!-- Dados Pessoais -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="nome_completo" class="block text-sm font-medium text-gray-700 mb-2">
                            Nome Completo *
                        </label>
                        <input type="text" id="nome_completo" name="nome_completo" required
                               class="block w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900" placeholder="Seu nome completo">
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            E-mail *
                        </label>
                        <input type="email" id="email" name="email" required
                               class="block w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900" placeholder="seu@email.com">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="documento" class="block text-sm font-medium text-gray-700 mb-2">
                            CPF
                        </label>
                        <input type="text" id="documento" name="documento" 
                               class="block w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900" placeholder="000.000.000-00" maxlength="14">
                    </div>
                    
                    <div>
                        <label for="data_nascimento" class="block text-sm font-medium text-gray-700 mb-2">
                            Data de Nascimento *
                        </label>
                        <input type="date" id="data_nascimento" name="data_nascimento" required
                               class="block w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="sexo" class="block text-sm font-medium text-gray-700 mb-2">
                            Gênero *
                        </label>
                        <select id="sexo" name="sexo" required class="block w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900">
                            <option value="">Selecione</option>
                            <option value="Masculino">Masculino</option>
                            <option value="Feminino">Feminino</option>
                            <option value="Outro">Outro</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="telefone" class="block text-sm font-medium text-gray-700 mb-2">
                            Telefone *
                        </label>
                        <input type="tel" id="telefone" name="telefone" required
                               class="block w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900" placeholder="(00) 00000-0000">
                    </div>
                </div>

                <!-- Senha -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="senha" class="block text-sm font-medium text-gray-700 mb-2">
                            Senha *
                        </label>
                        <div class="relative">
                            <input type="password" id="senha" name="senha" required
                                   class="block w-full rounded-lg border border-gray-300 px-4 py-2 pr-10 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900" placeholder="Mínimo 6 caracteres">
                            <button type="button" id="toggleSenha" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div>
                        <label for="confirmar_senha" class="block text-sm font-medium text-gray-700 mb-2">
                            Confirmar Senha *
                        </label>
                        <div class="relative">
                            <input type="password" id="confirmar_senha" name="confirmar_senha" required
                                   class="block w-full rounded-lg border border-gray-300 px-4 py-2 pr-10 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900" placeholder="Confirme sua senha">
                            <button type="button" id="toggleConfirmarSenha" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <i class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Dados Obrigatórios para Boleto Bancário -->
                <fieldset class="border-2 border-primary-600 rounded-lg p-6 mt-6">
                    <legend class="px-2 text-sm font-semibold text-primary-600">
                        <i class="fas fa-file-invoice-dollar mr-1"></i>
                        Dados Obrigatórios para Boleto Bancário
                    </legend>
                    <p class="text-xs text-gray-600 mb-4">
                        Estes dados são necessários caso você escolha pagar com boleto bancário.
                    </p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="cep" class="block text-sm font-medium text-gray-700 mb-2">
                                CEP *
                            </label>
                            <input type="text" id="cep" name="cep" 
                                   class="block w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900" placeholder="00000-000" maxlength="9">
                        </div>
                        
                        <div class="md:col-span-2">
                            <label for="endereco" class="block text-sm font-medium text-gray-700 mb-2">
                                Logradouro *
                            </label>
                            <input type="text" id="endereco" name="endereco" 
                                   class="block w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900" placeholder="Rua, Avenida, etc.">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                        <div>
                            <label for="numero" class="block text-sm font-medium text-gray-700 mb-2">
                                Número *
                            </label>
                            <input type="text" id="numero" name="numero" 
                                   class="block w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900" placeholder="123">
                        </div>
                        
                        <div>
                            <label for="complemento" class="block text-sm font-medium text-gray-700 mb-2">
                                Complemento
                            </label>
                            <input type="text" id="complemento" name="complemento" 
                                   class="block w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900" placeholder="Apto, Bloco, etc.">
                        </div>
                        
                        <div>
                            <label for="bairro" class="block text-sm font-medium text-gray-700 mb-2">
                                Bairro *
                            </label>
                            <input type="text" id="bairro" name="bairro" 
                                   class="block w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900" placeholder="Nome do bairro">
                        </div>
                        
                        <div>
                            <label for="cidade" class="block text-sm font-medium text-gray-700 mb-2">
                                Cidade *
                            </label>
                            <input type="text" id="cidade" name="cidade" 
                                   class="block w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900" placeholder="Nome da cidade">
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <label for="uf" class="block text-sm font-medium text-gray-700 mb-2">
                            Estado (UF) *
                        </label>
                        <select id="uf" name="uf" class="block w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900">
                            <option value="">Selecione o estado</option>
                            <option value="AC">Acre</option>
                            <option value="AL">Alagoas</option>
                            <option value="AP">Amapá</option>
                            <option value="AM">Amazonas</option>
                            <option value="BA">Bahia</option>
                            <option value="CE">Ceará</option>
                            <option value="DF">Distrito Federal</option>
                            <option value="ES">Espírito Santo</option>
                            <option value="GO">Goiás</option>
                            <option value="MA">Maranhão</option>
                            <option value="MT">Mato Grosso</option>
                            <option value="MS">Mato Grosso do Sul</option>
                            <option value="MG">Minas Gerais</option>
                            <option value="PA">Pará</option>
                            <option value="PB">Paraíba</option>
                            <option value="PR">Paraná</option>
                            <option value="PE">Pernambuco</option>
                            <option value="PI">Piauí</option>
                            <option value="RJ">Rio de Janeiro</option>
                            <option value="RN">Rio Grande do Norte</option>
                            <option value="RS">Rio Grande do Sul</option>
                            <option value="RO">Rondônia</option>
                            <option value="RR">Roraima</option>
                            <option value="SC">Santa Catarina</option>
                            <option value="SP">São Paulo</option>
                            <option value="SE">Sergipe</option>
                            <option value="TO">Tocantins</option>
                        </select>
                    </div>
                </fieldset>

                <!-- Termos -->
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input id="aceitar_termos" name="aceitar_termos" type="checkbox" required
                               class="focus:ring-primary-500 h-4 w-4 text-primary-600 border-gray-300 rounded">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="aceitar_termos" class="text-gray-700">
                            Eu aceito os <a href="#" class="text-primary-600 hover:text-primary-700 underline">Termos de Uso</a> 
                            e a <a href="#" class="text-primary-600 hover:text-primary-700 underline">Política de Privacidade</a>
                        </label>
                    </div>
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-base font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition">
                        <i class="fas fa-user-plus mr-2"></i>
                        <?php echo $redirect === 'inscricao' ? 'Criar Conta e Continuar' : 'Criar Conta'; ?>
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

            <!-- Link para Login -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Já tem uma conta?
                    <a href="login.php<?php echo $redirect ? '?redirect=' . $redirect . ($evento_id ? '&evento_id=' . $evento_id : '') : ''; ?>" 
                       class="text-primary-600 hover:text-primary-700 font-medium underline">
                        Fazer login
                    </a>
                </p>
            </div>
        </div>

        <!-- Informações do Evento -->
        <?php if ($evento_id > 0): ?>
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
            <div class="flex items-center">
                <i class="fas fa-info-circle text-blue-600 mr-3"></i>
                <div>
                    <h3 class="text-sm font-medium text-blue-800">Evento Selecionado</h3>
                    <p class="text-sm text-blue-600">Após criar sua conta, você será redirecionado para a inscrição no evento.</p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

<script>
// Variáveis globais
window.eventoId = <?= $evento_id ?>;
window.redirect = '<?= $redirect ?>';
window.API_BASE = '../../../api';

// Toggle de senhas
function setupPasswordToggle(inputId, buttonId) {
    const btn = document.getElementById(buttonId);
    if (btn) {
        btn.addEventListener('click', function() {
            const input = document.getElementById(inputId);
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
    }
}

setupPasswordToggle('senha', 'toggleSenha');
setupPasswordToggle('confirmar_senha', 'toggleConfirmarSenha');

// Máscara de CPF
document.getElementById('documento').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 11) {
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        e.target.value = value;
    }
});

// Máscara de CEP e busca automática via ViaCEP
document.getElementById('cep').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 8) {
        value = value.replace(/(\d{5})(\d)/, '$1-$2');
        e.target.value = value;
        
        // Buscar endereço quando CEP tiver 8 dígitos
        if (value.replace(/\D/g, '').length === 8) {
            buscarEnderecoPorCEP(value.replace(/\D/g, ''));
        }
    }
});

// Função para buscar endereço via ViaCEP
async function buscarEnderecoPorCEP(cep) {
    try {
        const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
        const data = await response.json();
        
        if (!data.erro) {
            document.getElementById('endereco').value = data.logradouro || '';
            document.getElementById('bairro').value = data.bairro || '';
            document.getElementById('cidade').value = data.localidade || '';
            document.getElementById('uf').value = data.uf || '';
            
            // Focar no campo número após preencher
            document.getElementById('numero').focus();
        } else {
            console.log('CEP não encontrado');
        }
    } catch (error) {
        console.error('Erro ao buscar CEP:', error);
    }
}

// Máscara de telefone
document.getElementById('telefone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 11) {
        value = value.replace(/(\d{2})(\d)/, '($1) $2');
        value = value.replace(/(\d{5})(\d)/, '$1-$2');
        e.target.value = value;
    }
});

// Formulário de cadastro
document.getElementById('registerForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Validar senhas
    const senha = document.getElementById('senha').value;
    const confirmarSenha = document.getElementById('confirmar_senha').value;
    
    if (senha !== confirmarSenha) {
        Swal.fire({
            icon: 'error',
            title: 'Senhas não coincidem',
            text: 'As senhas digitadas não são iguais. Tente novamente.',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    if (senha.length < 6) {
        Swal.fire({
            icon: 'error',
            title: 'Senha muito curta',
            text: 'A senha deve ter pelo menos 6 caracteres.',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    try {
        // Mostrar loading
        Swal.fire({
            title: 'Criando conta...',
            text: 'Aguarde enquanto processamos seus dados',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Fazer cadastro via API existente
        const formDataToSend = new FormData();
        Object.keys(data).forEach(key => {
            if (key !== 'confirmar_senha' && key !== 'aceitar_termos') {
                formDataToSend.append(key, data[key]);
            }
        });
        
        const response = await fetch(`${window.API_BASE}/auth/register.php`, {
            method: 'POST',
            body: formDataToSend
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Cadastro bem-sucedido
            Swal.fire({
                icon: 'success',
                title: 'Conta criada!',
                text: window.redirect === 'inscricao' ? 'Redirecionando para a inscrição...' : 'Redirecionando para login...',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                // Redirecionar baseado no contexto
                if (window.redirect === 'inscricao' && window.eventoId > 0) {
                    window.location.href = `../inscricao/index.php?evento_id=${window.eventoId}`;
                } else if (window.eventoId > 0) {
                    window.location.href = `../inscricao/index.php?evento_id=${window.eventoId}`;
                } else {
                    window.location.href = `login.php${window.redirect ? '?redirect=' + window.redirect : ''}`;
                }
            });
        } else {
            // Erro no cadastro
            Swal.fire({
                icon: 'error',
                title: 'Erro no cadastro',
                text: result.message || 'Não foi possível criar sua conta',
                confirmButtonText: 'Tentar novamente'
            });
        }
    } catch (error) {
        console.error('Erro no cadastro:', error);
        Swal.fire({
            icon: 'error',
            title: 'Erro de conexão',
            text: 'Não foi possível conectar ao servidor. Tente novamente.',
            confirmButtonText: 'OK'
        });
    }
});
</script>
