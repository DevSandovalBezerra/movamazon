/**
 * Script para gerenciar o formulário de contato público
 */

(function() {
    'use strict';

    // Definir API_BASE se não estiver definido
    if (!window.API_BASE) {
        const path = window.location.pathname || '';
        const idx = path.indexOf('/frontend/');
        if (idx > 0) {
            window.API_BASE = path.slice(0, idx);
        } else if (idx === 0) {
            window.API_BASE = '';
        } else {
            const pathParts = path.split('/').filter(p => p);
            const frontendIdx = pathParts.indexOf('frontend');
            if (frontendIdx > 0) {
                window.API_BASE = '/' + pathParts.slice(0, frontendIdx).join('/');
            } else {
                window.API_BASE = '';
            }
        }
    }

    const form = document.getElementById('form-contato');
    const btnEnviar = document.getElementById('btn-enviar');
    const btnText = document.getElementById('btn-text');
    const btnLoading = document.getElementById('btn-loading');

    if (!form || !btnEnviar) {
        console.error('[CONTATO] Formulário ou botão não encontrado');
        return;
    }

    /**
     * Limpar erros de validação
     */
    function limparErros() {
        const errorSpans = document.querySelectorAll('[id^="erro-"]');
        errorSpans.forEach(span => {
            span.classList.add('hidden');
            span.textContent = '';
        });

        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.classList.remove('border-red-500');
            input.classList.add('border-gray-300');
        });
    }

    /**
     * Mostrar erro em um campo
     */
    function mostrarErro(campo, mensagem) {
        const input = form.querySelector(`[name="${campo}"]`);
        const errorSpan = document.getElementById(`erro-${campo}`);

        if (input) {
            input.classList.remove('border-gray-300');
            input.classList.add('border-red-500');
        }

        if (errorSpan) {
            errorSpan.textContent = mensagem;
            errorSpan.classList.remove('hidden');
        }
    }

    /**
     * Validar formulário
     */
    function validarFormulario() {
        limparErros();
        let valido = true;

        const nome = form.querySelector('[name="nome"]').value.trim();
        const email = form.querySelector('[name="email"]').value.trim();
        const telefone = form.querySelector('[name="telefone"]').value.trim();
        const assunto = form.querySelector('[name="assunto"]').value.trim();
        const mensagem = form.querySelector('[name="mensagem"]').value.trim();

        if (!nome) {
            mostrarErro('nome', 'Nome é obrigatório');
            valido = false;
        }

        if (!email) {
            mostrarErro('email', 'E-mail é obrigatório');
            valido = false;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            mostrarErro('email', 'E-mail inválido');
            valido = false;
        }

        if (!telefone) {
            mostrarErro('telefone', 'Telefone é obrigatório');
            valido = false;
        }

        if (!assunto) {
            mostrarErro('assunto', 'Selecione um assunto');
            valido = false;
        }

        if (!mensagem) {
            mostrarErro('mensagem', 'Mensagem é obrigatória');
            valido = false;
        } else if (mensagem.length < 10) {
            mostrarErro('mensagem', 'Mensagem deve ter pelo menos 10 caracteres');
            valido = false;
        }

        return valido;
    }

    /**
     * Enviar formulário
     */
    async function enviarFormulario(e) {
        e.preventDefault();

        if (!validarFormulario()) {
            return;
        }

        // Desabilitar botão e mostrar loading
        btnEnviar.disabled = true;
        btnText.textContent = 'Enviando...';
        btnLoading.classList.remove('hidden');

        const formData = {
            nome: form.querySelector('[name="nome"]').value.trim(),
            email: form.querySelector('[name="email"]').value.trim(),
            telefone: form.querySelector('[name="telefone"]').value.trim(),
            assunto: form.querySelector('[name="assunto"]').value.trim(),
            mensagem: form.querySelector('[name="mensagem"]').value.trim()
        };

        try {
            const apiBase = window.API_BASE || '';
            const apiUrl = apiBase ? `${apiBase}/api/public/contato.php` : '/api/public/contato.php';

            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                // Mostrar erros de validação se houver
                if (data.errors) {
                    Object.keys(data.errors).forEach(campo => {
                        mostrarErro(campo, data.errors[campo]);
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: data.message || 'Erro ao enviar mensagem. Tente novamente.',
                        confirmButtonColor: '#0b4340'
                    });
                }
                return;
            }

            // Sucesso
            Swal.fire({
                icon: 'success',
                title: 'Mensagem Enviada!',
                text: data.message || 'Entraremos em contato em breve.',
                confirmButtonColor: '#0b4340'
            }).then(() => {
                form.reset();
                limparErros();
            });

        } catch (error) {
            console.error('[CONTATO] Erro ao enviar:', error);
            Swal.fire({
                icon: 'error',
                title: 'Erro de Conexão',
                text: 'Não foi possível enviar sua mensagem. Verifique sua conexão e tente novamente.',
                confirmButtonColor: '#0b4340'
            });
        } finally {
            // Reabilitar botão e ocultar loading
            btnEnviar.disabled = false;
            btnText.textContent = 'Enviar Mensagem';
            btnLoading.classList.add('hidden');
        }
    }

    // Adicionar máscara de telefone
    const telefoneInput = form.querySelector('[name="telefone"]');
    if (telefoneInput) {
        telefoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                if (value.length <= 10) {
                    value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
                } else {
                    value = value.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
                }
                e.target.value = value.trim();
            }
        });
    }

    // Event listener para submit
    form.addEventListener('submit', enviarFormulario);

    console.log('[CONTATO] Script de contato carregado');
})();
