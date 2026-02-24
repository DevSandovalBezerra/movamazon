if (window.getApiBase) { window.getApiBase(); }
// JavaScript para Etapa 3: Identificação (Login/Registro)
class EtapaIdentificacao {
    constructor() {
        this.usuarioIdentificado = false;
        this.dadosUsuario = null;
        // Não inicializar automaticamente - será chamado externamente
    }

    init() {
        console.log('Inicializando EtapaIdentificacao...');
        this.bindEvents();
        this.verificarUsuarioLogado();
        this.inicializarMascaras();
        console.log('EtapaIdentificacao inicializada');
    }

    bindEvents() {
        console.log('Bindando eventos...');

        // Event listeners para tabs
        const tabBtns = document.querySelectorAll('.tab-btn');
        console.log('Tab buttons encontradas:', tabBtns.length);

        if (tabBtns.length === 0) {
            console.warn('Nenhuma tab button encontrada. Tentando novamente em 100ms...');
            setTimeout(() => this.bindEvents(), 100);
            return;
        }

        tabBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                console.log('Tab clicada:', e.target.dataset.tab);
                this.trocarTab(e.target.dataset.tab);
            });
        });

        // Event listeners para formulários
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.fazerLogin();
            });
        } else {
            console.warn('Formulário de login não encontrado');
        }

        const registroForm = document.getElementById('registroForm');
        if (registroForm) {
            registroForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.fazerRegistro();
            });
        } else {
            console.warn('Formulário de registro não encontrado');
        }

        // Event listeners para toggle de senha
        document.querySelectorAll('.toggle-password').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.togglePassword(e.target.closest('.input-group').querySelector('input'));
            });
        });

        // Event listeners para validação em tempo real
        this.bindValidacaoTempoReal();
    }

    trocarTab(tabName) {
        console.log('Trocando para tab:', tabName);

        // Remover active de todas as tabs
        const allTabBtns = document.querySelectorAll('.tab-btn');
        const allTabContents = document.querySelectorAll('.tab-content');

        console.log('Removendo active de', allTabBtns.length, 'tab buttons');
        console.log('Removendo active de', allTabContents.length, 'tab contents');

        allTabBtns.forEach(btn => {
            btn.classList.remove('active');
        });
        allTabContents.forEach(content => {
            content.classList.remove('active');
            content.style.display = 'none';
        });

        // Ativar tab selecionada
        const tabBtn = document.querySelector(`[data-tab="${tabName}"]`);
        const tabContent = document.getElementById(tabName);

        console.log('Procurando tab button com data-tab="' + tabName + '"');
        console.log('Tab button encontrada:', tabBtn);
        console.log('Procurando tab content com id="' + tabName + '"');
        console.log('Tab content encontrada:', tabContent);

        if (tabBtn) {
            tabBtn.classList.add('active');
            console.log('Tab button ativada:', tabName);
        } else {
            console.error('Tab button não encontrada:', tabName);
        }

        if (tabContent) {
            tabContent.classList.add('active');
            // Forçar visibilidade via style também
            tabContent.style.display = 'block';
            console.log('Tab content ativada:', tabName);
        } else {
            console.error('Tab content não encontrada:', tabName);
        }
    }

    togglePassword(input) {
        const type = input.type === 'password' ? 'text' : 'password';
        input.type = type;

        const icon = input.parentElement.querySelector('.toggle-password i');
        icon.className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
    }

    bindValidacaoTempoReal() {
        // Validação de CPF
        const cpfInput = document.getElementById('registroCPF');
        if (cpfInput) {
            cpfInput.addEventListener('input', (e) => {
                this.aplicarMascaraCPF(e.target);
            });
        }

        // Validação de telefone
        const telefoneInput = document.getElementById('registroTelefone');
        if (telefoneInput) {
            telefoneInput.addEventListener('input', (e) => {
                this.aplicarMascaraTelefone(e.target);
            });
        }

        // Validação de confirmação de senha
        const senhaInput = document.getElementById('registroSenha');
        const confirmarSenhaInput = document.getElementById('registroConfirmarSenha');

        if (senhaInput && confirmarSenhaInput) {
            [senhaInput, confirmarSenhaInput].forEach(input => {
                input.addEventListener('input', () => {
                    this.validarConfirmacaoSenha();
                });
            });
        }
    }

    aplicarMascaraCPF(input) {
        const cpfInput = document.getElementById('registroCPF');
        if (cpfInput) {
            let value = input.value.replace(/\D/g, '');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            input.value = value;
        }
    }

    aplicarMascaraTelefone(input) {
        if (!input) return;
        let value = input.value.replace(/\D/g, '');
        value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
        value = value.replace(/(\d)(\d{4})$/, '$1-$2');
        input.value = value;
    }

    validarConfirmacaoSenha() {
        const senha = document.getElementById('registroSenha').value;
        const confirmarSenha = document.getElementById('registroConfirmarSenha').value;

        if (confirmarSenha && senha !== confirmarSenha) {
            this.mostrarErroCampo('registroConfirmarSenha', 'As senhas não coincidem');
        } else {
            this.removerErroCampo('registroConfirmarSenha');
        }
    }

    fazerLogin() {
        const identificacao = document.getElementById('loginIdentificacao').value;
        const senha = document.getElementById('loginSenha').value;

        if (!identificacao || !senha) {
            this.mostrarErro('Preencha todos os campos obrigatórios');
            return;
        }

        // Mostrar loading com SweetAlert
        Swal.fire({
            title: 'Fazendo login...',
            text: 'Verificando suas credenciais.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch(`${window.API_BASE}/auth/login.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    identificacao: identificacao,
                    senha: senha
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log(' [DEBUG] Resposta do login:', data);

                if (data.success) {
                    this.usuarioIdentificado = true;
                    this.dadosUsuario = data.usuario;
                    this.atualizarInterface();

                    // Mostrar sucesso e continuar
                    Swal.fire({
                        icon: 'success',
                        title: 'Login realizado com sucesso!',
                        text: 'Continuando para próxima etapa...',
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true
                    }).then(() => {
                        if (window.sistemaInscricao) {
                            window.sistemaInscricao.prosseguirEtapa();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro no login',
                        text: data.error || 'Credenciais inválidas. Verifique e tente novamente.',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Erro no login:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erro de comunicação',
                    text: 'Não foi possível conectar ao servidor. Verifique sua conexão e tente novamente.',
                    confirmButtonText: 'OK'
                });
            });
    }

    fazerRegistro() {
        console.log(' [DEBUG] ===== INICIANDO REGISTRO =====');
        const formData = new FormData(document.getElementById('registroForm'));
        const dados = Object.fromEntries(formData.entries());

        console.log(' [DEBUG] Dados do formulário capturados:', dados);
        console.log(' [DEBUG] FormData entries:', Array.from(formData.entries()));

        // Validações
        console.log(' [DEBUG] Iniciando validação dos dados...');
        if (!this.validarDadosRegistro(dados)) {
            console.log(' [DEBUG] Validação falhou, interrompendo registro');
            return;
        }
        console.log(' [DEBUG] Validação passou, prosseguindo com registro...');

        // Mostrar loading com SweetAlert
        Swal.fire({
            title: 'Criando conta...',
            text: 'Por favor, aguarde enquanto processamos seus dados.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Usar FormData para compatibilidade com a API existente
        console.log(' [DEBUG] Enviando requisição para:', `${window.API_BASE}/auth/register.php`);
        console.log(' [DEBUG] Método: POST');
        console.log(' [DEBUG] Body (FormData):', formData);

        fetch(`${window.API_BASE}/auth/register.php`, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log(' [DEBUG] Resposta recebida - Status:', response.status);
                console.log(' [DEBUG] Headers da resposta:', response.headers);
                return response.json();
            })
            .then(data => {
                console.log(' [DEBUG] Dados JSON da resposta:', data);

                if (data.success) {
                    console.log(' [DEBUG] Registro bem-sucedido!');
                    // Fechar loading
                    Swal.close();
                    // Fazer login automático após criar conta
                    this.fazerLoginAposRegistro(dados.email, dados.senha);
                } else {
                    console.log(' [DEBUG] Erro no registro:', data.message);
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro ao criar conta',
                        text: data.message || 'Ocorreu um erro inesperado. Tente novamente.',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error(' [DEBUG] Erro na requisição de registro:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erro de comunicação',
                    text: 'Não foi possível conectar ao servidor. Verifique sua conexão e tente novamente.',
                    confirmButtonText: 'OK'
                });
            });
    }

    fazerLoginAposRegistro(email, senha) {
        console.log(' [DEBUG] ===== INICIANDO LOGIN AUTOMÁTICO =====');
        console.log(' [DEBUG] Email:', email);
        console.log(' [DEBUG] Senha: [OCULTA] (tamanho:', senha.length, ')');

        const loginData = {
            identificacao: email,
            senha: senha
        };

        console.log(' [DEBUG] Dados do login:', loginData);
        console.log(' [DEBUG] Enviando para:', `${window.API_BASE}/auth/login.php`);

        fetch(`${window.API_BASE}/auth/login.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(loginData)
            })
            .then(response => {
                console.log(' [DEBUG] Resposta do login - Status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log(' [DEBUG] Dados do login recebidos:', data);

                if (data.success) {
                    console.log(' [DEBUG] Login automático bem-sucedido!');
                    this.usuarioIdentificado = true;
                    this.dadosUsuario = data.usuario;
                    console.log(' [DEBUG] Usuário identificado:', this.dadosUsuario);

                    this.atualizarInterface();

                    // Mostrar SweetAlert de sucesso
                    Swal.fire({
                        icon: 'success',
                        title: 'Conta criada com sucesso!',
                        text: 'Login realizado automaticamente. Continuando para próxima etapa...',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    }).then(() => {
                        console.log(' [DEBUG] Redirecionando para página de inscrição...');
                        // Redirecionar para página de inscrição
                        const urlParams = new URLSearchParams(window.location.search);
                        const eventoId = urlParams.get('evento_id');
                        if (eventoId) {
                            window.location.href = `index.php?evento_id=${eventoId}`;
                        } else {
                            window.location.href = '../public/index.php';
                        }
                    });
                } else {
                    console.log(' [DEBUG] Erro no login automático:', data.error);
                    Swal.fire({
                        icon: 'warning',
                        title: 'Conta criada!',
                        text: 'Mas houve erro no login automático. Faça login manualmente.',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error(' [DEBUG] Erro na requisição de login automático:', error);
                this.mostrarErro('Conta criada, mas erro no login automático. Faça login manualmente.');
            });
    }

    validarDadosRegistro(dados) {
        console.log(' [DEBUG] Validando dados de registro:', dados);

        // Limpar erros anteriores
        this.limparErrosRegistro();

        let valido = true;

        // Validar nome completo (obrigatório)
        if (!dados.nome_completo || dados.nome_completo.trim().length < 3) {
            this.mostrarErroCampo('registroNome', 'Nome completo é obrigatório (mínimo 3 caracteres)');
            valido = false;
        }

        // Validar email (obrigatório)
        if (!dados.email || !this.validarEmail(dados.email)) {
            this.mostrarErroCampo('registroEmail', 'E-mail válido é obrigatório');
            valido = false;
        }

        // Validar data de nascimento (obrigatório)
        if (!dados.data_nascimento || !this.validarDataNascimento(dados.data_nascimento)) {
            this.mostrarErroCampo('registroDataNasc', 'Data de nascimento é obrigatória (idade mínima 16 anos)');
            valido = false;
        }

        // Validar sexo (obrigatório)
        if (!dados.sexo) {
            this.mostrarErroCampo('registroSexo', 'Sexo é obrigatório');
            valido = false;
        }

        // Validar telefone (obrigatório)
        if (!dados.telefone || dados.telefone.trim().length < 10) {
            this.mostrarErroCampo('registroTelefone', 'Telefone é obrigatório');
            valido = false;
        }

        // Validar documento (se preenchido)
        if (dados.documento && dados.documento.trim() !== '' && !this.validarCPF(dados.documento.replace(/\D/g, ''))) {
            this.mostrarErroCampo('registroCPF', 'CPF inválido');
            valido = false;
        }

        // Validar senha (obrigatório)
        if (!dados.senha || dados.senha.length < 6) {
            this.mostrarErroCampo('registroSenha', 'Senha é obrigatória (mínimo 6 caracteres)');
            valido = false;
        }

        // Validar confirmação de senha (obrigatório)
        if (!dados.confirmar_senha) {
            this.mostrarErroCampo('registroConfirmarSenha', 'Confirmação de senha é obrigatória');
            valido = false;
        } else if (dados.senha !== dados.confirmar_senha) {
            this.mostrarErroCampo('registroConfirmarSenha', 'As senhas não coincidem');
            valido = false;
        }

        console.log(' [DEBUG] Validação concluída. Válido:', valido);
        return valido;
    }

    limparErrosRegistro() {
        const campos = ['registroNome', 'registroEmail', 'registroDataNasc', 'registroSexo', 'registroTelefone', 'registroCPF', 'registroSenha', 'registroConfirmarSenha'];
        campos.forEach(campoId => {
            this.removerErroCampo(campoId);
        });
    }

    validarCPF(cpf) {
        if (cpf.length !== 11) return false;

        // Verificar se todos os dígitos são iguais
        if (/^(\d)\1{10}$/.test(cpf)) return false;

        // Validar primeiro dígito verificador
        let soma = 0;
        for (let i = 0; i < 9; i++) {
            soma += parseInt(cpf.charAt(i)) * (10 - i);
        }
        let resto = 11 - (soma % 11);
        let dv1 = resto < 2 ? 0 : resto;

        if (parseInt(cpf.charAt(9)) !== dv1) return false;

        // Validar segundo dígito verificador
        soma = 0;
        for (let i = 0; i < 10; i++) {
            soma += parseInt(cpf.charAt(i)) * (11 - i);
        }
        resto = 11 - (soma % 11);
        let dv2 = resto < 2 ? 0 : resto;

        return parseInt(cpf.charAt(10)) === dv2;
    }

    validarEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    validarDataNascimento(data) {
        const dataNasc = new Date(data);
        const hoje = new Date();
        const idade = hoje.getFullYear() - dataNasc.getFullYear();

        return idade >= 16 && idade <= 100;
    }

    mostrarErroCampo(campoId, mensagem) {
        const campo = document.getElementById(campoId);
        if (campo) {
            campo.classList.add('is-invalid');

            // Remover mensagem de erro anterior
            const erroAnterior = campo.parentElement.querySelector('.invalid-feedback');
            if (erroAnterior) {
                erroAnterior.remove();
            }

            // Adicionar nova mensagem de erro
            const div = document.createElement('div');
            div.className = 'invalid-feedback';
            div.textContent = mensagem;
            campo.parentElement.appendChild(div);
        }
    }

    removerErroCampo(campoId) {
        const campo = document.getElementById(campoId);
        if (campo) {
            campo.classList.remove('is-invalid');
            const erro = campo.parentElement.querySelector('.invalid-feedback');
            if (erro) {
                erro.remove();
            }
        }
    }

    verificarUsuarioLogado() {
        // Verificar se já existe usuário logado na sessão
        // Fazer uma requisição para verificar o status da sessão
        fetch(`${window.API_BASE}/auth/check_session.php`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.logged_in) {
                    this.usuarioIdentificado = true;
                    this.dadosUsuario = data.user;
                    this.atualizarInterface();
                }
            })
            .catch(error => {
                console.log('Verificação de sessão falhou:', error);
            });
    }

    atualizarInterface() {
        if (this.usuarioIdentificado) {
            // Mostrar interface de usuário logado
            this.mostrarInterfaceUsuarioLogado();
        } else {
            // Mostrar interface de login/registro
            this.mostrarInterfaceLogin();
        }

        this.atualizarBotaoProximo();
    }

    mostrarInterfaceUsuarioLogado() {
        // Ocultar tabs de login/registro
        const tabsContainer = document.querySelector('.border-b.border-gray-200');
        const loginTab = document.getElementById('login');
        const registroTab = document.getElementById('registro');

        if (tabsContainer) tabsContainer.style.display = 'none';
        if (loginTab) loginTab.style.display = 'none';
        if (registroTab) registroTab.style.display = 'none';

        // Mostrar interface do usuário logado
        const usuarioContainer = document.querySelector('.usuario-logado-container');
        if (usuarioContainer) {
            usuarioContainer.style.display = 'block';

            // Atualizar dados do usuário
            const nomeElement = document.getElementById('usuario-nome');
            const emailElement = document.getElementById('usuario-email');

            if (nomeElement && this.dadosUsuario) {
                nomeElement.textContent = this.dadosUsuario.nome_completo || this.dadosUsuario.name || 'Usuário';
            }
            if (emailElement && this.dadosUsuario) {
                emailElement.textContent = this.dadosUsuario.email || 'email@exemplo.com';
            }
        }
    }

    mostrarInterfaceLogin() {
        // Mostrar tabs de login/registro
        const tabsContainer = document.querySelector('.border-b.border-gray-200');
        const loginTab = document.getElementById('login');
        const registroTab = document.getElementById('registro');

        if (tabsContainer) tabsContainer.style.display = 'block';
        if (loginTab) loginTab.style.display = 'block';
        if (registroTab) registroTab.style.display = 'none';

        // Ocultar interface do usuário logado
        const usuarioContainer = document.querySelector('.usuario-logado-container');
        if (usuarioContainer) {
            usuarioContainer.style.display = 'none';
        }
    }

    atualizarBotaoProximo() {
        const btnProximo = document.getElementById('btn-prosseguir');
        if (btnProximo) {
            btnProximo.disabled = !this.usuarioIdentificado;
        }
    }

    // Funções globais
    alterarUsuario() {
        this.usuarioIdentificado = false;
        this.dadosUsuario = null;
        this.atualizarInterface();
    }

    confirmarUsuario() {
        if (this.usuarioIdentificado && this.dadosUsuario) {
            // Salvar dados na sessão
            if (window.sistemaInscricao) {
                window.sistemaInscricao.salvarDadosEtapa({
                    usuario_id: this.dadosUsuario.id,
                    usuario_dados: this.dadosUsuario
                });
            }

            // Redirecionar para página de inscrição
            const urlParams = new URLSearchParams(window.location.search);
            const eventoId = urlParams.get('evento_id');
            if (eventoId) {
                window.location.href = `index.php?evento_id=${eventoId}`;
            } else {
                window.location.href = '../public/index.php';
            }
        }
    }

    recuperarSenha() {
        // Implementar recuperação de senha
        this.mostrarErro('Funcionalidade de recuperação de senha será implementada em breve');
    }

    inicializarMascaras() {
        // Inicializar máscaras de input apenas se os elementos existirem
        const cpfInput = document.getElementById('registroCPF');
        const telefoneInput = document.getElementById('registroTelefone');

        if (cpfInput) {
            this.aplicarMascaraCPF(cpfInput);
        }
        if (telefoneInput) {
            this.aplicarMascaraTelefone(telefoneInput);
        }
    }

    // Utilitários
    mostrarLoading(mensagem) {
        if (window.sistemaInscricao) {
            window.sistemaInscricao.mostrarLoading(mensagem);
        }
    }

    ocultarLoading() {
        if (window.sistemaInscricao) {
            window.sistemaInscricao.ocultarLoading();
        }
    }

    mostrarErro(mensagem) {
        if (window.sistemaInscricao) {
            window.sistemaInscricao.mostrarErro(mensagem);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: mensagem
            });
        }
    }

    mostrarSucesso(mensagem) {
        if (window.sistemaInscricao) {
            window.sistemaInscricao.mostrarSucesso(mensagem);
        } else {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso',
                text: mensagem
            });
        }
    }

    // Métodos de acesso
    getUsuarioIdentificado() {
        return this.usuarioIdentificado;
    }

    getDadosUsuario() {
        return this.dadosUsuario;
    }

    // Método para testar tabs manualmente
    testarTabs() {
        console.log('=== TESTE DE TABS ===');
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');

        console.log('Tab buttons encontradas:', tabBtns.length);
        tabBtns.forEach((btn, index) => {
            console.log(`Tab ${index}:`, btn.textContent.trim(), 'data-tab:', btn.dataset.tab);
        });

        console.log('Tab contents encontradas:', tabContents.length);
        tabContents.forEach((content, index) => {
            console.log(`Content ${index}:`, content.id, 'display:', content.style.display, 'classList:', content.classList.toString());
        });

        // Testar troca para registro
        console.log('Testando troca para registro...');
        this.trocarTab('registro');
    }
}

// Funções globais
function alterarUsuario() {
    if (window.etapaIdentificacao) {
        window.etapaIdentificacao.alterarUsuario();
    }
}

function confirmarUsuario() {
    if (window.etapaIdentificacao) {
        window.etapaIdentificacao.confirmarUsuario();
    }
}

function recuperarSenha() {
    if (window.etapaIdentificacao) {
        window.etapaIdentificacao.recuperarSenha();
    }
}

// Função global para testar tabs
function testarTabsIdentificacao() {
    if (window.sistemaInscricao && window.sistemaInscricao.etapas.identificacao) {
        window.sistemaInscricao.etapas.identificacao.testarTabs();
    } else {
        console.error('Sistema de identificação não encontrado');
    }
}

// Função global simples para testar tabs diretamente
function testarTabsSimples() {
    console.log('=== TESTE SIMPLES DE TABS ===');

    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    console.log('Tab buttons encontradas:', tabBtns.length);
    tabBtns.forEach((btn, index) => {
        console.log(`Tab ${index}:`, btn.textContent.trim(), 'data-tab:', btn.dataset.tab);
    });

    console.log('Tab contents encontradas:', tabContents.length);
    tabContents.forEach((content, index) => {
        console.log(`Content ${index}:`, content.id, 'display:', content.style.display, 'classList:', content.classList.toString());
    });

    // Testar clique direto
    const registroBtn = document.querySelector('[data-tab="registro"]');
    if (registroBtn) {
        console.log('Clicando na tab registro...');
        registroBtn.click();
    } else {
        console.error('Tab registro não encontrada');
    }
}

// Não inicializar automaticamente - será inicializado pelo sistema de inscrições

// Exportar para uso em outros módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = EtapaIdentificacao;
}
