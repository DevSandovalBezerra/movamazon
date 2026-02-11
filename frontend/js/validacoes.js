/**
 * Sistema de Validação MovAmazon
 * Validações para formulários de eventos, modalidades e categorias
 */

class ValidacaoFormulario {
    constructor(formId, regras) {
        this.form = document.getElementById(formId);
        this.regras = regras;
        this.erros = {};
        this.inicializar();
    }

    inicializar() {
        if (!this.form) return;

        // Adicionar listeners para validação em tempo real
        this.regras.forEach(regra => {
            const campo = this.form.querySelector(`[name="${regra.campo}"]`);
            if (campo) {
                campo.addEventListener('blur', () => this.validarCampo(regra.campo));
                campo.addEventListener('input', () => this.limparErro(regra.campo));
            }
        });

        // Validar no submit
        this.form.addEventListener('submit', (e) => {
            if (!this.validarFormulario()) {
                e.preventDefault();
                this.mostrarErros();
            }
        });
    }

    validarCampo(nomeCampo) {
        const regra = this.regras.find(r => r.campo === nomeCampo);
        if (!regra) return true;

        const campo = this.form.querySelector(`[name="${nomeCampo}"]`);
        const valor = campo.value.trim();
        let valido = true;
        let mensagem = '';

        // Validações específicas
        if (regra.obrigatorio && !valor) {
            valido = false;
            mensagem = 'Este campo é obrigatório';
        } else if (valor) {
            if (regra.minLength && valor.length < regra.minLength) {
                valido = false;
                mensagem = `Mínimo de ${regra.minLength} caracteres`;
            } else if (regra.maxLength && valor.length > regra.maxLength) {
                valido = false;
                mensagem = `Máximo de ${regra.maxLength} caracteres`;
            } else if (regra.pattern && !regra.pattern.test(valor)) {
                valido = false;
                mensagem = regra.mensagemPadrao || 'Formato inválido';
            } else if (regra.validacaoCustomizada) {
                const resultado = regra.validacaoCustomizada(valor);
                if (!resultado.valido) {
                    valido = false;
                    mensagem = resultado.mensagem;
                }
            }
        }

        if (!valido) {
            this.erros[nomeCampo] = mensagem;
            this.mostrarErroCampo(campo, mensagem);
        } else {
            this.limparErro(nomeCampo);
        }

        return valido;
    }

    validarFormulario() {
        this.erros = {};
        let valido = true;

        this.regras.forEach(regra => {
            if (!this.validarCampo(regra.campo)) {
                valido = false;
            }
        });

        return valido;
    }

    mostrarErroCampo(campo, mensagem) {
        // Remover erro anterior
        this.limparErro(campo.name);

        // Adicionar classe de erro
        campo.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');

        // Criar elemento de erro
        const erroElement = document.createElement('p');
        erroElement.className = 'text-red-500 text-sm mt-1';
        erroElement.id = `erro-${campo.name}`;
        erroElement.textContent = mensagem;

        // Inserir após o campo
        campo.parentNode.appendChild(erroElement);
    }

    limparErro(nomeCampo) {
        const campo = this.form.querySelector(`[name="${nomeCampo}"]`);
        if (!campo) return;

        // Remover classes de erro
        campo.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');

        // Remover mensagem de erro
        const erroElement = document.getElementById(`erro-${nomeCampo}`);
        if (erroElement) {
            erroElement.remove();
        }

        // Remover do objeto de erros
        delete this.erros[nomeCampo];
    }

    mostrarErros() {
        if (Object.keys(this.erros).length === 0) return;

        const mensagens = Object.values(this.erros).join('\n');
        Swal.fire({
            icon: 'error',
            title: 'Erro de Validação',
            text: 'Por favor, corrija os seguintes erros:',
            html: `<div class="text-left text-sm">${mensagens.replace(/\n/g, '<br>')}</div>`,
            confirmButtonColor: '#EF4444'
        });
    }

    // Método para validar campos específicos
    validarCampoEspecifico(nomeCampo) {
        return this.validarCampo(nomeCampo);
    }

    // Método para verificar se formulário está válido
    estaValido() {
        return Object.keys(this.erros).length === 0;
    }
}

// Regras de validação para diferentes formulários
const REGRAS_VALIDACAO = {
    // Formulário de Categoria
    categoria: [{
            campo: 'nome',
            obrigatorio: true,
            minLength: 3,
            maxLength: 100,
            mensagemPadrao: 'Nome deve ter entre 3 e 100 caracteres'
        },
        {
            campo: 'tipo_publico',
            obrigatorio: true,
            validacaoCustomizada: (valor) => {
                const tiposValidos = ['comunidade_academica', 'publico_geral', 'ambos'];
                return {
                    valido: tiposValidos.includes(valor),
                    mensagem: 'Tipo de público inválido'
                };
            }
        },
        {
            campo: 'idade_min',
            validacaoCustomizada: (valor) => {
                if (!valor) return {
                    valido: true,
                    mensagem: ''
                };
                const idade = parseInt(valor);
                return {
                    valido: idade >= 0 && idade <= 100,
                    mensagem: 'Idade deve estar entre 0 e 100 anos'
                };
            }
        },
        {
            campo: 'idade_max',
            validacaoCustomizada: (valor) => {
                if (!valor) return {
                    valido: true,
                    mensagem: ''
                };
                const idade = parseInt(valor);
                return {
                    valido: idade >= 0 && idade <= 100,
                    mensagem: 'Idade deve estar entre 0 e 100 anos'
                };
            }
        }
    ],

    // Formulário de Modalidade
    modalidade: [{
            campo: 'nome',
            obrigatorio: true,
            minLength: 3,
            maxLength: 100,
            mensagemPadrao: 'Nome deve ter entre 3 e 100 caracteres'
        },
        {
            campo: 'categoria_id',
            obrigatorio: true,
            validacaoCustomizada: (valor) => {
                return {
                    valido: valor && valor !== '',
                    mensagem: 'Selecione uma categoria'
                };
            }
        },
        {
            campo: 'tipo_prova',
            obrigatorio: true,
            validacaoCustomizada: (valor) => {
                const tiposValidos = ['corrida', 'caminhada', 'ambos'];
                return {
                    valido: tiposValidos.includes(valor),
                    mensagem: 'Tipo de prova inválido'
                };
            }
        },
        {
            campo: 'limite_vagas',
            validacaoCustomizada: (valor) => {
                if (!valor) return {
                    valido: true,
                    mensagem: ''
                };
                const vagas = parseInt(valor);
                return {
                    valido: vagas > 0 && vagas <= 10000,
                    mensagem: 'Limite de vagas deve estar entre 1 e 10.000'
                };
            }
        }
    ],

    // Formulário de Evento
    evento: [{
            campo: 'nome',
            obrigatorio: true,
            minLength: 5,
            maxLength: 200,
            mensagemPadrao: 'Nome deve ter entre 5 e 200 caracteres'
        },
        {
            campo: 'data_evento',
            obrigatorio: true,
            validacaoCustomizada: (valor) => {
                if (!valor) return {
                    valido: false,
                    mensagem: 'Data é obrigatória'
                };

                const dataEvento = new Date(valor);
                const hoje = new Date();
                hoje.setHours(0, 0, 0, 0);

                return {
                    valido: dataEvento > hoje,
                    mensagem: 'Data do evento deve ser futura'
                };
            }
        },
        {
            campo: 'hora_inicio',
            obrigatorio: true,
            validacaoCustomizada: (valor) => {
                return {
                    valido: valor && valor !== '',
                    mensagem: 'Horário de início é obrigatório'
                };
            }
        },
        {
            campo: 'cidade',
            obrigatorio: true,
            minLength: 2,
            maxLength: 100,
            mensagemPadrao: 'Cidade deve ter entre 2 e 100 caracteres'
        },
        {
            campo: 'estado',
            obrigatorio: true,
            validacaoCustomizada: (valor) => {
                const estadosValidos = [
                    'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA',
                    'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN',
                    'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'
                ];
                return {
                    valido: estadosValidos.includes(valor),
                    mensagem: 'Estado inválido'
                };
            }
        },
        {
            campo: 'limite_participantes',
            validacaoCustomizada: (valor) => {
                if (!valor) return {
                    valido: true,
                    mensagem: ''
                };
                const participantes = parseInt(valor);
                return {
                    valido: participantes > 0 && participantes <= 50000,
                    mensagem: 'Limite deve estar entre 1 e 50.000 participantes'
                };
            }
        }
    ]
};

// Funções de validação utilitárias
const ValidacaoUtils = {
    // Validar email
    email: (email) => {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    },

    // Validar CPF
    cpf: (cpf) => {
        cpf = cpf.replace(/[^\d]/g, '');
        if (cpf.length !== 11) return false;

        // Verificar dígitos repetidos
        if (/^(\d)\1{10}$/.test(cpf)) return false;

        // Validar dígitos verificadores
        let soma = 0;
        for (let i = 0; i < 9; i++) {
            soma += parseInt(cpf.charAt(i)) * (10 - i);
        }
        let resto = 11 - (soma % 11);
        let dv1 = resto < 2 ? 0 : resto;

        soma = 0;
        for (let i = 0; i < 10; i++) {
            soma += parseInt(cpf.charAt(i)) * (11 - i);
        }
        resto = 11 - (soma % 11);
        let dv2 = resto < 2 ? 0 : resto;

        return parseInt(cpf.charAt(9)) === dv1 && parseInt(cpf.charAt(10)) === dv2;
    },

    // Validar telefone
    telefone: (telefone) => {
        const regex = /^\(?[1-9]{2}\)? ?(?:[2-8]|9[1-9])[0-9]{3}\-?[0-9]{4}$/;
        return regex.test(telefone);
    },

    // Validar CEP
    cep: (cep) => {
        const regex = /^\d{5}-?\d{3}$/;
        return regex.test(cep);
    },

    // Validar data futura
    dataFutura: (data) => {
        const dataEvento = new Date(data);
        const hoje = new Date();
        hoje.setHours(0, 0, 0, 0);
        return dataEvento > hoje;
    },

    // Validar hora
    hora: (hora) => {
        const regex = /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/;
        return regex.test(hora);
    }
};

// Inicializar validações quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function () {
    // Inicializar validação de categoria se existir
    if (document.getElementById('formCategoria')) {
        window.validacaoCategoria = new ValidacaoFormulario('formCategoria', REGRAS_VALIDACAO.categoria);
    }

    // Inicializar validação de modalidade se existir
    if (document.getElementById('formModalidade')) {
        window.validacaoModalidade = new ValidacaoFormulario('formModalidade', REGRAS_VALIDACAO.modalidade);
    }

    // Inicializar validação de evento se existir
    if (document.getElementById('formEvento')) {
        window.validacaoEvento = new ValidacaoFormulario('formEvento', REGRAS_VALIDACAO.evento);
    }
});

// Exportar para uso global
window.ValidacaoFormulario = ValidacaoFormulario;
window.ValidacaoUtils = ValidacaoUtils;
window.REGRAS_VALIDACAO = REGRAS_VALIDACAO;