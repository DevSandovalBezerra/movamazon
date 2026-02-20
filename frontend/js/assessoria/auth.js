document.addEventListener('DOMContentLoaded', () => {
    const loginTab = document.getElementById('login-tab');
    const registerTab = document.getElementById('register-tab');
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    const loginFormEl = document.getElementById('login-form-element');
    const registerFormEl = document.getElementById('register-form-element');
    const loginFeedback = document.getElementById('login-feedback');
    const registerFeedback = document.getElementById('register-feedback');

    // Tipo PF/PJ
    const tipoPF = document.getElementById('tipo-pf');
    const tipoPJ = document.getElementById('tipo-pj');
    const labelDoc = document.getElementById('label-doc');
    const inputDoc = document.getElementById('reg-cpf-cnpj');
    const nomeFantasiaWrapper = document.getElementById('nome-fantasia-wrapper');

    // Base URL para API (relativo ao login.php)
    const API_BASE = '../../../../api/assessoria/auth';

    // === Troca de abas ===
    function showTab(tab) {
        if (tab === 'login') {
            loginForm.classList.remove('hidden');
            registerForm.classList.add('hidden');
            loginTab.classList.add('tab-active');
            loginTab.classList.remove('tab-inactive');
            registerTab.classList.add('tab-inactive');
            registerTab.classList.remove('tab-active');
        } else {
            loginForm.classList.add('hidden');
            registerForm.classList.remove('hidden');
            registerTab.classList.add('tab-active');
            registerTab.classList.remove('tab-inactive');
            loginTab.classList.add('tab-inactive');
            loginTab.classList.remove('tab-active');
        }
    }

    loginTab.addEventListener('click', (e) => { e.preventDefault(); showTab('login'); });
    registerTab.addEventListener('click', (e) => { e.preventDefault(); showTab('register'); });

    // === Toggle PF/PJ ===
    function updateTipoDoc() {
        if (tipoPJ && tipoPJ.checked) {
            labelDoc.textContent = 'CNPJ';
            inputDoc.placeholder = '00.000.000/0000-00';
            inputDoc.maxLength = 18;
            nomeFantasiaWrapper.classList.remove('hidden');
        } else {
            labelDoc.textContent = 'CPF';
            inputDoc.placeholder = '000.000.000-00';
            inputDoc.maxLength = 14;
            nomeFantasiaWrapper.classList.add('hidden');
        }
    }

    if (tipoPF) tipoPF.addEventListener('change', updateTipoDoc);
    if (tipoPJ) tipoPJ.addEventListener('change', updateTipoDoc);

    // === Mascara CPF/CNPJ ===
    if (inputDoc) {
        inputDoc.addEventListener('input', (e) => {
            let v = e.target.value.replace(/\D/g, '');
            if (tipoPJ && tipoPJ.checked) {
                v = v.substring(0, 14);
                if (v.length > 12) v = v.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{0,2})/, '$1.$2.$3/$4-$5');
                else if (v.length > 8) v = v.replace(/^(\d{2})(\d{3})(\d{3})(\d{0,4})/, '$1.$2.$3/$4');
                else if (v.length > 5) v = v.replace(/^(\d{2})(\d{3})(\d{0,3})/, '$1.$2.$3');
                else if (v.length > 2) v = v.replace(/^(\d{2})(\d{0,3})/, '$1.$2');
            } else {
                v = v.substring(0, 11);
                if (v.length > 9) v = v.replace(/^(\d{3})(\d{3})(\d{3})(\d{0,2})/, '$1.$2.$3-$4');
                else if (v.length > 6) v = v.replace(/^(\d{3})(\d{3})(\d{0,3})/, '$1.$2.$3');
                else if (v.length > 3) v = v.replace(/^(\d{3})(\d{0,3})/, '$1.$2');
            }
            e.target.value = v;
        });
    }

    // === Mascara telefone ===
    const telInput = document.getElementById('reg-telefone');
    if (telInput) {
        telInput.addEventListener('input', (e) => {
            let v = e.target.value.replace(/\D/g, '').substring(0, 11);
            if (v.length > 6) v = v.replace(/^(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
            else if (v.length > 2) v = v.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
            e.target.value = v;
        });
    }

    // === Login ===
    if (loginFormEl) {
        loginFormEl.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('login-btn');
            btn.disabled = true;
            btn.textContent = 'Entrando...';
            loginFeedback.innerHTML = '';

            const data = {
                email: document.getElementById('login-email').value.trim(),
                senha: document.getElementById('login-senha').value
            };

            try {
                const resp = await fetch(`${API_BASE}/login.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await resp.json();

                if (result.success) {
                    loginFeedback.innerHTML = '<p class="text-green-600">Login realizado! Redirecionando...</p>';
                    setTimeout(() => {
                        window.location.href = '../index.php?page=dashboard';
                    }, 500);
                } else {
                    loginFeedback.innerHTML = `<p class="text-red-600">${result.message}</p>`;
                    btn.disabled = false;
                    btn.textContent = 'Entrar';
                }
            } catch (err) {
                loginFeedback.innerHTML = '<p class="text-red-600">Erro de conexao. Tente novamente.</p>';
                btn.disabled = false;
                btn.textContent = 'Entrar';
            }
        });
    }

    // === Cadastro ===
    if (registerFormEl) {
        registerFormEl.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('register-btn');
            btn.disabled = true;
            btn.textContent = 'Cadastrando...';
            registerFeedback.innerHTML = '';

            const senha = document.getElementById('reg-senha').value;
            const confirmar = document.getElementById('reg-confirmar-senha').value;

            if (senha !== confirmar) {
                registerFeedback.innerHTML = '<p class="text-red-600">As senhas nao conferem</p>';
                btn.disabled = false;
                btn.textContent = 'Cadastrar';
                return;
            }

            if (senha.length < 6) {
                registerFeedback.innerHTML = '<p class="text-red-600">A senha deve ter no minimo 6 caracteres</p>';
                btn.disabled = false;
                btn.textContent = 'Cadastrar';
                return;
            }

            const termos = document.getElementById('reg-termos');
            if (!termos.checked) {
                registerFeedback.innerHTML = '<p class="text-red-600">Aceite os termos para continuar</p>';
                btn.disabled = false;
                btn.textContent = 'Cadastrar';
                return;
            }

            const data = {
                nome: document.getElementById('reg-nome').value.trim(),
                email: document.getElementById('reg-email').value.trim(),
                senha: senha,
                confirmar_senha: confirmar,
                cref: document.getElementById('reg-cref').value.trim(),
                tipo: document.querySelector('input[name="tipo"]:checked').value,
                cpf_cnpj: document.getElementById('reg-cpf-cnpj').value.trim(),
                telefone: document.getElementById('reg-telefone').value.trim(),
                nome_fantasia: document.getElementById('reg-nome-fantasia')?.value.trim() || ''
            };

            try {
                const resp = await fetch(`${API_BASE}/register.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await resp.json();

                if (result.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Cadastro realizado!',
                            text: 'Sua assessoria foi criada com sucesso.',
                            confirmButtonColor: '#7C3AED',
                            timer: 2000,
                            timerProgressBar: true
                        }).then(() => {
                            window.location.href = '../index.php?page=dashboard';
                        });
                    } else {
                        alert('Cadastro realizado com sucesso!');
                        window.location.href = '../index.php?page=dashboard';
                    }
                } else {
                    registerFeedback.innerHTML = `<p class="text-red-600">${result.message}</p>`;
                    btn.disabled = false;
                    btn.textContent = 'Cadastrar';
                }
            } catch (err) {
                registerFeedback.innerHTML = '<p class="text-red-600">Erro de conexao. Tente novamente.</p>';
                btn.disabled = false;
                btn.textContent = 'Cadastrar';
            }
        });
    }
});
