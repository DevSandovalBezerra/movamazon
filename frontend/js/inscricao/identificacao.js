if (window.getApiBase) { window.getApiBase(); }
// JavaScript para Etapa 3: IdentificaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o (Login/Registro)
class EtapaIdentificacao {
    constructor() {
        this.usuarioIdentificado = false;
        this.dadosUsuario = null;
        // NÃƒÆ’Ã‚Â£o inicializar automaticamente - serÃƒÆ’Ã‚Â¡ chamado externamente
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

        // Event listeners para formulÃƒÆ’Ã‚Â¡rios
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.fazerLogin();
            });
        } else {
            console.warn('FormulÃƒÆ’Ã‚Â¡rio de login nÃƒÆ’Ã‚Â£o encontrado');
        }

        const registroForm = document.getElementById('registroForm');
        if (registroForm) {
            registroForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.fazerRegistro();
            });
        } else {
            console.warn('FormulÃƒÆ’Ã‚Â¡rio de registro nÃƒÆ’Ã‚Â£o encontrado');
        }

        // Event listeners para toggle de senha
        document.querySelectorAll('.toggle-password').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.togglePassword(e.target.closest('.input-group').querySelector('input'));
            });
        });

        // Event listeners para validaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o em tempo real
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
            console.error('Tab button nÃƒÆ’Ã‚Â£o encontrada:', tabName);
        }

        if (tabContent) {
            tabContent.classList.add('active');
            // ForÃƒÆ’Ã‚Â§ar visibilidade via style tambÃƒÆ’Ã‚Â©m
            tabContent.style.display = 'block';
            console.log('Tab content ativada:', tabName);
        } else {
            console.error('Tab content nÃƒÆ’Ã‚Â£o encontrada:', tabName);
        }
    }

    togglePassword(input) {
        const type = input.type === 'password' ? 'text' : 'password';
        input.type = type;

        const icon = input.parentElement.querySelector('.toggle-password i');
        icon.className = type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
    }

    bindValidacaoTempoReal() {
        // ValidaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o de CPF
        const cpfInput = document.getElementById('registroCPF');
        if (cpfInput) {
            cpfInput.addEventListener('input', (e) => {
                this.aplicarMascaraCPF(e.target);
            });
        }

        // ValidaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o de telefone
        const telefoneInput = document.getElementById('registroTelefone');
        if (telefoneInput) {
            telefoneInput.addEventListener('input', (e) => {
                this.aplicarMascaraTelefone(e.target);
            });
        }

        // ValidaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o de confirmaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o de senha
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
            this.mostrarErroCampo('registroConfirmarSenha', 'As senhas nÃƒÆ’Ã‚Â£o coincidem');
        } else {
            this.removerErroCampo('registroConfirmarSenha');
        }
    }

    fazerLogin() {
        const identificacao = document.getElementById('loginIdentificacao').value;
        const senha = document.getElementById('loginSenha').value;

        if (!identificacao || !senha) {
            this.mostrarErro('Preencha todos os campos obrigatÃƒÆ’Ã‚Â³rios');
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
                console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Âµ [DEBUG] Resposta do login:', data);

                if (data.success) {
                    this.usuarioIdentificado = true;
                    this.dadosUsuario = data.usuario;
                    this.atualizarInterface();

                    // Mostrar sucesso e continuar
                    Swal.fire({
                        icon: 'success',
                        title: 'Login realizado com sucesso!',
                        text: 'Continuando para prÃƒÆ’Ã‚Â³xima etapa...',
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
                        text: data.error || 'Credenciais invÃƒÆ’Ã‚Â¡lidas. Verifique e tente novamente.',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Erro no login:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erro de comunicaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o',
                    text: 'NÃƒÆ’Ã‚Â£o foi possÃƒÆ’Ã‚Â­vel conectar ao servidor. Verifique sua conexÃƒÆ’Ã‚Â£o e tente novamente.',
                    confirmButtonText: 'OK'
                });
            });
    }

    fazerRegistro() {
        console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Âµ [DEBUG] ===== INICIANDO REGISTRO =====');
        const formData = new FormData(document.getElementById('registroForm'));
        const dados = Object.fromEntries(formData.entries());

        console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Âµ [DEBUG] Dados do formulÃƒÆ’Ã‚Â¡rio capturados:', dados);
        console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Âµ [DEBUG] FormData entries:', Array.from(formData.entries()));

        // ValidaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Âµes
        console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Âµ [DEBUG] Iniciando validaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o dos dados...');
        if (!this.validarDadosRegistro(dados)) {
            console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Âµ [DEBUG] ValidaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o falhou, interrompendo registro');
            return;
        }
        console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Âµ [DEBUG] ValidaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o passou, prosseguindo com registro...');

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
        console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Âµ [DEBUG] Enviando requisiÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o para:', `${window.API_BASE}/auth/register.php`);
        console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Âµ [DEBUG] MÃƒÆ’Ã‚Â©todo: POST');
        console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Âµ [DEBUG] Body (FormData):', formData);

        fetch(`${window.API_BASE}/auth/register.php`, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Âµ [DEBUG] Resposta recebida - Status:', response.status);
                console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Âµ [DEBUG] Headers da resposta:', response.headers);
                return response.json();
            })
            .then(data => {
                console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Âµ [DEBUG] Dados JSON da resposta:', data);

                if (data.success) {
                    console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Âµ [DEBUG] Registro bem-sucedido!');
                    // Fechar loading
                    Swal.close();
                    // Fazer login automÃƒÆ’Ã‚Â¡tico apÃƒÆ’Ã‚Â³s criar conta
                    this.fazerLoginAposRegistro(dados.email, dados.senha);
                } else {
                    console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Âµ [DEBUG] Erro no registro:', data.message);
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro ao criar conta',
                        text: data.message || 'Ocorreu um erro inesperado. Tente novamente.',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Âµ [DEBUG] Erro na requisiÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o de registro:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Erro de comunicaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o',
                    text: 'NÃƒÆ’Ã‚Â£o foi possÃƒÆ’Ã‚Â­vel conectar ao servidor. Verifique sua conexÃƒÆ’Ã‚Â£o e tente novamente.',
                    confirmButtonText: 'OK'
                });
            });
    }

    fazerLoginAposRegistro(email, senha) {
        console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Âµ [DEBUG] ===== INICIANDO LOGIN AUTOMÃƒÆ’Ã‚ÂTICO =====');
        console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Âµ [DEBUG] Email:', email);
        console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Âµ [DEBUG] Senha: [OCULTA] (tamanho:', senha.length, ')');

        const loginData = {
            identificacao: email,
            senha: senha
        };

        console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Âµ [DEBUG] Dados do login:', loginData);
        console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Âµ [DEBUG] Enviando para:', `${window.API_BASE}/auth/login.php`);

        fetch(`${window.API_BASE}/auth/login.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(loginData)
            })
            .then(response => {
                console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Âµ [DEBUG] Resposta do login - Status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Âµ [DEBUG] Dados do login recebidos:', data);

                if (data.success) {
                    console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Âµ [DEBUG] Login automÃƒÆ’Ã‚Â¡tico bem-sucedido!');
                    this.usuarioIdentificado = true;
                    this.dadosUsuario = data.usuario;
                    console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Âµ [DEBUG] UsuÃƒÆ’Ã‚Â¡rio identificado:', this.dadosUsuario);

                    this.atualizarInterface();

                    // Mostrar SweetAlert de sucesso
                    Swal.fire({
                        icon: 'success',
                        title: 'Conta criada com sucesso!',
                        text: 'Login realizado automaticamente. Continuando para prÃƒÆ’Ã‚Â³xima etapa...',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    }).then(() => {
                        console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Âµ [DEBUG] Redirecionando para pÃƒÆ’Ã‚Â¡gina de inscriÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o...');
                        // Redirecionar para pÃƒÆ’Ã‚Â¡gina de inscriÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o
                        const urlParams = new URLSearchParams(window.location.search);
                        const eventoId = urlParams.get('evento_id');
                        if (eventoId) {
                            window.location.href = `index.php?evento_id=${eventoId}`;
                        } else {
                            window.location.href = '../public/index.php';
                        }
                    });
                } else {
                    console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Âµ [DEBUG] Erro no login automÃƒÆ’Ã‚Â¡tico:', data.error);
                    Swal.fire({
                        icon: 'warning',
                        title: 'Conta criada!',
                        text: 'Mas houve erro no login automÃƒÆ’Ã‚Â¡tico. FaÃƒÆ’Ã‚Â§a login manualmente.',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Âµ [DEBUG] Erro na requisiÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o de login automÃƒÆ’Ã‚Â¡tico:', error);
                this.mostrarErro('Conta criada, mas erro no login automÃƒÆ’Ã‚Â¡tico. FaÃƒÆ’Ã‚Â§a login manualmente.');
            });
    }

    validarDadosRegistro(dados) {
        console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Âµ [DEBUG] Validando dados de registro:', dados);

        // Limpar erros anteriores
        this.limparErrosRegistro();

        let valido = true;

        // Validar nome completo (obrigatÃƒÆ’Ã‚Â³rio)
        if (!dados.nome_completo || dados.nome_completo.trim().length < 3) {
            this.mostrarErroCampo('registroNome', 'Nome completo ÃƒÆ’Ã‚Â© obrigatÃƒÆ’Ã‚Â³rio (mÃƒÆ’Ã‚Â­nimo 3 caracteres)');
            valido = false;
        }

        // Validar email (obrigatÃƒÆ’Ã‚Â³rio)
        if (!dados.email || !this.validarEmail(dados.email)) {
            this.mostrarErroCampo('registroEmail', 'E-mail vÃƒÆ’Ã‚Â¡lido ÃƒÆ’Ã‚Â© obrigatÃƒÆ’Ã‚Â³rio');
            valido = false;
        }

        // Validar data de nascimento (obrigatÃƒÆ’Ã‚Â³rio)
        if (!dados.data_nascimento || !this.validarDataNascimento(dados.data_nascimento)) {
            this.mostrarErroCampo('registroDataNasc', 'Data de nascimento ÃƒÆ’Ã‚Â© obrigatÃƒÆ’Ã‚Â³ria (idade mÃƒÆ’Ã‚Â­nima 16 anos)');
            valido = false;
        }

        // Validar sexo (obrigatÃƒÆ’Ã‚Â³rio)
        if (!dados.sexo) {
            this.mostrarErroCampo('registroSexo', 'Sexo ÃƒÆ’Ã‚Â© obrigatÃƒÆ’Ã‚Â³rio');
            valido = false;
        }

        // Validar telefone (obrigatÃƒÆ’Ã‚Â³rio)
        if (!dados.telefone || dados.telefone.trim().length < 10) {
            this.mostrarErroCampo('registroTelefone', 'Telefone ÃƒÆ’Ã‚Â© obrigatÃƒÆ’Ã‚Â³rio');
            valido = false;
        }

        // Validar documento (se preenchido)
        if (dados.documento && dados.documento.trim() !== '' && !this.validarCPF(dados.documento.replace(/\D/g, ''))) {
            this.mostrarErroCampo('registroCPF', 'CPF invÃƒÆ’Ã‚Â¡lido');
            valido = false;
        }

        // Validar senha (obrigatÃƒÆ’Ã‚Â³rio)
        if (!dados.senha || dados.senha.length < 6) {
            this.mostrarErroCampo('registroSenha', 'Senha ÃƒÆ’Ã‚Â© obrigatÃƒÆ’Ã‚Â³ria (mÃƒÆ’Ã‚Â­nimo 6 caracteres)');
            valido = false;
        }

        // Validar confirmaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o de senha (obrigatÃƒÆ’Ã‚Â³rio)
        if (!dados.confirmar_senha) {
            this.mostrarErroCampo('registroConfirmarSenha', 'ConfirmaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o de senha ÃƒÆ’Ã‚Â© obrigatÃƒÆ’Ã‚Â³ria');
            valido = false;
        } else if (dados.senha !== dados.confirmar_senha) {
            this.mostrarErroCampo('registroConfirmarSenha', 'As senhas nÃƒÆ’Ã‚Â£o coincidem');
            valido = false;
        }

        console.log('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ‚Âµ [DEBUG] ValidaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o concluÃƒÆ’Ã‚Â­da. VÃƒÆ’Ã‚Â¡lido:', valido);
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

        // Verificar se todos os dÃƒÆ’Ã‚Â­gitos sÃƒÆ’Ã‚Â£o iguais
        if (/^(\d)\1{10}$/.test(cpf)) return false;

        // Validar primeiro dÃƒÆ’Ã‚Â­gito verificador
        let soma = 0;
        for (let i = 0; i < 9; i++) {
            soma += parseInt(cpf.charAt(i)) * (10 - i);
        }
        let resto = 11 - (soma % 11);
        let dv1 = resto < 2 ? 0 : resto;

        if (parseInt(cpf.charAt(9)) !== dv1) return false;

        // Validar segundo dÃƒÆ’Ã‚Â­gito verificador
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
        // Verificar se jÃƒÆ’Ã‚Â¡ existe usuÃƒÆ’Ã‚Â¡rio logado na sessÃƒÆ’Ã‚Â£o
        // Fazer uma requisiÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o para verificar o status da sessÃƒÆ’Ã‚Â£o
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
                console.log('VerificaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o de sessÃƒÆ’Ã‚Â£o falhou:', error);
            });
    }

    atualizarInterface() {
        if (this.usuarioIdentificado) {
            // Mostrar interface de usuÃƒÆ’Ã‚Â¡rio logado
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

        // Mostrar interface do usuÃƒÆ’Ã‚Â¡rio logado
        const usuarioContainer = document.querySelector('.usuario-logado-container');
        if (usuarioContainer) {
            usuarioContainer.style.display = 'block';

            // Atualizar dados do usuÃƒÆ’Ã‚Â¡rio
            const nomeElement = document.getElementById('usuario-nome');
            const emailElement = document.getElementById('usuario-email');

            if (nomeElement && this.dadosUsuario) {
                nomeElement.textContent = this.dadosUsuario.nome_completo || this.dadosUsuario.name || 'UsuÃƒÆ’Ã‚Â¡rio';
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

        // Ocultar interface do usuÃƒÆ’Ã‚Â¡rio logado
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

    // FunÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Âµes globais
    alterarUsuario() {
        this.usuarioIdentificado = false;
        this.dadosUsuario = null;
        this.atualizarInterface();
    }

    confirmarUsuario() {
        if (this.usuarioIdentificado && this.dadosUsuario) {
            // Salvar dados na sessÃƒÆ’Ã‚Â£o
            if (window.sistemaInscricao) {
                window.sistemaInscricao.salvarDadosEtapa({
                    usuario_id: this.dadosUsuario.id,
                    usuario_dados: this.dadosUsuario
                });
            }

            // Redirecionar para pÃƒÆ’Ã‚Â¡gina de inscriÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o
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
        // Implementar recuperaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o de senha
        this.mostrarErro('Funcionalidade de recuperaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o de senha serÃƒÆ’Ã‚Â¡ implementada em breve');
    }

    inicializarMascaras() {
        // Inicializar mÃƒÆ’Ã‚Â¡scaras de input apenas se os elementos existirem
        const cpfInput = document.getElementById('registroCPF');
        const telefoneInput = document.getElementById('registroTelefone');

        if (cpfInput) {
            this.aplicarMascaraCPF(cpfInput);
        }
        if (telefoneInput) {
            this.aplicarMascaraTelefone(telefoneInput);
        }
    }

    // UtilitÃƒÆ’Ã‚Â¡rios
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

    // MÃƒÆ’Ã‚Â©todos de acesso
    getUsuarioIdentificado() {
        return this.usuarioIdentificado;
    }

    getDadosUsuario() {
        return this.dadosUsuario;
    }

    // MÃƒÆ’Ã‚Â©todo para testar tabs manualmente
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

// FunÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Âµes globais
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

// FunÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o global para testar tabs
function testarTabsIdentificacao() {
    if (window.sistemaInscricao && window.sistemaInscricao.etapas.identificacao) {
        window.sistemaInscricao.etapas.identificacao.testarTabs();
    } else {
        console.error('Sistema de identificaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o nÃƒÆ’Ã‚Â£o encontrado');
    }
}

// FunÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o global simples para testar tabs diretamente
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
        console.error('Tab registro nÃƒÆ’Ã‚Â£o encontrada');
    }
}

// NÃƒÆ’Ã‚Â£o inicializar automaticamente - serÃƒÆ’Ã‚Â¡ inicializado pelo sistema de inscriÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Âµes

// Exportar para uso em outros mÃƒÆ’Ã‚Â³dulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = EtapaIdentificacao;
}
