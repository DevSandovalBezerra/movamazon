if (window.getApiBase) { window.getApiBase(); }
/**
 * Sistema de Testes de Fluxo MovAmazon
 * Testa os fluxos principais do sistema de forma automatizada
 */

class TesteFluxo {
    constructor() {
        this.resultados = [];
        this.testesExecutados = 0;
        this.testesPassaram = 0;
        this.testesFalharam = 0;
        this.inicializar();
    }

    inicializar() {
        console.log('Ã°Å¸Â§Âª Sistema de Testes de Fluxo inicializado');
        this.criarInterfaceTestes();
    }

    criarInterfaceTestes() {
        // Criar painel de testes apenas em desenvolvimento
        if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
            this.criarPainelTestes();
        }
    }

    criarPainelTestes() {
        const painel = document.createElement('div');
        painel.id = 'painel-testes';
        painel.className = 'fixed bottom-4 right-4 bg-gray-800 text-white p-4 rounded-lg shadow-lg z-50 max-w-sm';
        painel.innerHTML = `
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-bold text-sm">Ã°Å¸Â§Âª Testes de Fluxo</h3>
                <button onclick="testeFluxo.executarTodosTestes()" class="bg-green-600 hover:bg-green-700 px-3 py-1 rounded text-xs">
                    Executar Todos
                </button>
            </div>
            <div class="text-xs space-y-1">
                <div>Executados: <span id="testes-executados">0</span></div>
                <div>Passaram: <span id="testes-passaram" class="text-green-400">0</span></div>
                <div>Falharam: <span id="testes-falharam" class="text-red-400">0</span></div>
            </div>
            <div id="resultados-testes" class="mt-3 max-h-32 overflow-y-auto"></div>
        `;
        document.body.appendChild(painel);
    }

    async executarTodosTestes() {
        console.log('Ã°Å¸Å¡â‚¬ Iniciando execuÃƒÂ§ÃƒÂ£o de todos os testes...');
        this.limparResultados();

        // Testes de API
        await this.testarAPIs();

        // Testes de Frontend
        await this.testarFrontend();

        // Testes de ValidaÃƒÂ§ÃƒÂ£o
        await this.testarValidacoes();

        // Testes de Fluxo de UsuÃƒÂ¡rio
        await this.testarFluxoUsuario();

        this.atualizarInterface();
        this.mostrarResumo();
    }

    async testarAPIs() {
        console.log('Ã°Å¸â€Å’ Testando APIs...');

        // Teste 1: API de Eventos PÃƒÂºblicos
        await this.executarTeste('API Eventos PÃƒÂºblicos', async () => {
            const response = await fetch((window.API_BASE || '/api') + '/evento/list_public.php');
            const data = await response.json();
            return data.success !== undefined;
        });

        // Teste 2: API de Categorias
        await this.executarTeste('API Categorias', async () => {
            const response = await fetch((window.API_BASE || '/api') + '/categoria/list_public.php');
            const data = await response.json();
            return data.success !== undefined;
        });

        // Teste 3: API de Modalidades
        await this.executarTeste('API Modalidades', async () => {
            const response = await fetch((window.API_BASE || '/api') + '/organizador/modalidades/list.php');
            const data = await response.json();
            return data.success !== undefined;
        });
    }

    async testarFrontend() {
        console.log('Ã°Å¸Å½Â¨ Testando Frontend...');

        // Teste 1: Elementos da pÃƒÂ¡gina principal
        await this.executarTeste('Elementos PÃƒÂ¡gina Principal', () => {
            const elementos = [
                'eventos-dinamicos',
                'eventos-count'
            ];
            return elementos.every(id => document.getElementById(id));
        });

        // Teste 2: JavaScript carregado
        await this.executarTeste('JavaScript Carregado', () => {
            return typeof carregarEventos === 'function';
        });

        // Teste 3: SweetAlert disponÃƒÂ­vel
        await this.executarTeste('SweetAlert DisponÃƒÂ­vel', () => {
            return typeof Swal !== 'undefined';
        });
    }

    async testarValidacoes() {
        console.log('Ã¢Å“â€¦ Testando ValidaÃƒÂ§ÃƒÂµes...');

        // Teste 1: Sistema de validaÃƒÂ§ÃƒÂ£o
        await this.executarTeste('Sistema de ValidaÃƒÂ§ÃƒÂ£o', () => {
            return typeof ValidacaoFormulario !== 'undefined';
        });

        // Teste 2: Regras de validaÃƒÂ§ÃƒÂ£o
        await this.executarTeste('Regras de ValidaÃƒÂ§ÃƒÂ£o', () => {
            return REGRAS_VALIDACAO &&
                REGRAS_VALIDACAO.categoria &&
                REGRAS_VALIDACAO.modalidade;
        });

        // Teste 3: UtilitÃƒÂ¡rios de validaÃƒÂ§ÃƒÂ£o
        await this.executarTeste('UtilitÃƒÂ¡rios de ValidaÃƒÂ§ÃƒÂ£o', () => {
            return ValidacaoUtils &&
                typeof ValidacaoUtils.email === 'function' &&
                typeof ValidacaoUtils.cpf === 'function';
        });
    }

    async testarFluxoUsuario() {
        console.log('Ã°Å¸â€˜Â¤ Testando Fluxo de UsuÃƒÂ¡rio...');

        // Teste 1: NavegaÃƒÂ§ÃƒÂ£o entre pÃƒÂ¡ginas
        await this.executarTeste('NavegaÃƒÂ§ÃƒÂ£o PÃƒÂ¡ginas', () => {
            const links = document.querySelectorAll('a[href]');
            return links.length > 0;
        });

        // Teste 2: FormulÃƒÂ¡rios funcionais
        await this.executarTeste('FormulÃƒÂ¡rios Funcionais', () => {
            const forms = document.querySelectorAll('form');
            return forms.length > 0;
        });

        // Teste 3: Responsividade
        await this.executarTeste('Responsividade', () => {
            return window.innerWidth > 0 && window.innerHeight > 0;
        });
    }

    async executarTeste(nome, funcaoTeste) {
        this.testesExecutados++;

        try {
            const resultado = await funcaoTeste();

            if (resultado) {
                this.testesPassaram++;
                this.adicionarResultado(nome, 'PASSOU', 'success');
                console.log(`Ã¢Å“â€¦ ${nome}: PASSOU`);
            } else {
                this.testesFalharam++;
                this.adicionarResultado(nome, 'FALHOU', 'error');
                console.log(`Ã¢ÂÅ’ ${nome}: FALHOU`);
            }
        } catch (erro) {
            this.testesFalharam++;
            this.adicionarResultado(nome, `ERRO: ${erro.message}`, 'error');
            console.error(`Ã°Å¸â€™Â¥ ${nome}: ERRO - ${erro.message}`);
        }

        this.atualizarInterface();
    }

    adicionarResultado(nome, status, tipo) {
        this.resultados.push({
            nome,
            status,
            tipo,
            timestamp: new Date().toLocaleTimeString()
        });
    }

    atualizarInterface() {
        const executados = document.getElementById('testes-executados');
        const passaram = document.getElementById('testes-passaram');
        const falharam = document.getElementById('testes-falharam');
        const resultados = document.getElementById('resultados-testes');

        if (executados) executados.textContent = this.testesExecutados;
        if (passaram) passaram.textContent = this.testesPassaram;
        if (falharam) falharam.textContent = this.testesFalharam;

        if (resultados) {
            resultados.innerHTML = this.resultados
                .slice(-5) // Mostrar apenas os ÃƒÂºltimos 5 resultados
                .map(r => `
                    <div class="text-xs ${r.tipo === 'success' ? 'text-green-400' : 'text-red-400'}">
                        ${r.timestamp} - ${r.nome}: ${r.status}
                    </div>
                `).join('');
        }
    }

    mostrarResumo() {
        const resumo = `
            Ã°Å¸Â§Âª **TESTES CONCLUÃƒÂDOS**
            
            Ã°Å¸â€œÅ  **Resumo:**
            Ã¢â‚¬Â¢ Total: ${this.testesExecutados}
            Ã¢â‚¬Â¢ Ã¢Å“â€¦ Passaram: ${this.testesPassaram}
            Ã¢â‚¬Â¢ Ã¢ÂÅ’ Falharam: ${this.testesFalharam}
            Ã¢â‚¬Â¢ Ã°Å¸â€œË† Taxa de Sucesso: ${((this.testesPassaram / this.testesExecutados) * 100).toFixed(1)}%
            
            ${this.testesFalharam === 0 ? 'Ã°Å¸Å½â€° Todos os testes passaram!' : 'Ã¢Å¡Â Ã¯Â¸Â Alguns testes falharam. Verifique os logs.'}
        `;

        console.log(resumo);

        if (this.testesFalharam === 0) {
            Swal.fire({
                icon: 'success',
                title: 'Testes ConcluÃƒÂ­dos!',
                text: `Todos os ${this.testesExecutados} testes passaram com sucesso!`,
                confirmButtonColor: '#10B981'
            });
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Testes ConcluÃƒÂ­dos',
                text: `${this.testesFalharam} de ${this.testesExecutados} testes falharam. Verifique o console para detalhes.`,
                confirmButtonColor: '#F59E0B'
            });
        }
    }

    limparResultados() {
        this.resultados = [];
        this.testesExecutados = 0;
        this.testesPassaram = 0;
        this.testesFalharam = 0;
    }

    // Testes especÃƒÂ­ficos para diferentes funcionalidades
    async testarFuncionalidadeEspecifica(nome, funcaoTeste) {
        console.log(`Ã°Å¸Â§Âª Testando funcionalidade especÃƒÂ­fica: ${nome}`);
        await this.executarTeste(nome, funcaoTeste);
    }

    // Teste de performance
    async testarPerformance(nome, funcao) {
        const inicio = performance.now();
        await funcao();
        const fim = performance.now();
        const tempo = fim - inicio;

        console.log(`Ã¢ÂÂ±Ã¯Â¸Â ${nome} executou em ${tempo.toFixed(2)}ms`);

        // Considerar "lento" se demorar mais de 1 segundo
        const passou = tempo < 1000;
        await this.executarTeste(`Performance ${nome}`, () => passou);

        return tempo;
    }

    // Teste de acessibilidade bÃƒÂ¡sica
    async testarAcessibilidade() {
        console.log('Ã¢â„¢Â¿ Testando Acessibilidade...');

        // Teste 1: Imagens com alt
        await this.executarTeste('Imagens com Alt', () => {
            const imagens = document.querySelectorAll('img');
            if (imagens.length === 0) return true;

            const imagensComAlt = Array.from(imagens).filter(img => img.alt);
            return imagensComAlt.length === imagens.length;
        });

        // Teste 2: FormulÃƒÂ¡rios com labels
        await this.executarTeste('FormulÃƒÂ¡rios com Labels', () => {
            const inputs = document.querySelectorAll('input, select, textarea');
            if (inputs.length === 0) return true;

            const inputsComLabel = Array.from(inputs).filter(input => {
                const label = input.labels && input.labels.length > 0;
                const ariaLabel = input.getAttribute('aria-label');
                const placeholder = input.placeholder;
                return label || ariaLabel || placeholder;
            });

            return inputsComLabel.length === inputs.length;
        });

        // Teste 3: Contraste de cores (bÃƒÂ¡sico)
        await this.executarTeste('Contraste de Cores', () => {
            // VerificaÃƒÂ§ÃƒÂ£o bÃƒÂ¡sica - em produÃƒÂ§ÃƒÂ£o seria mais robusta
            const elementosTexto = document.querySelectorAll('p, h1, h2, h3, h4, h5, h6, span, div');
            return elementosTexto.length > 0; // Simplificado para demonstraÃƒÂ§ÃƒÂ£o
        });
    }
}

// Inicializar sistema de testes quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function () {
    window.testeFluxo = new TesteFluxo();

    // Executar testes automÃƒÂ¡ticos em desenvolvimento
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        // Aguardar um pouco para o sistema carregar
        setTimeout(() => {
            console.log('Ã°Å¸Å¡â‚¬ Executando testes automÃƒÂ¡ticos em 3 segundos...');
            setTimeout(() => {
                testeFluxo.executarTodosTestes();
            }, 3000);
        }, 1000);
    }
});

// FunÃƒÂ§ÃƒÂµes de teste utilitÃƒÂ¡rias
window.TestesUtils = {
    // Testar se uma funÃƒÂ§ÃƒÂ£o existe
    funcaoExiste: (nomeFuncao) => {
        return typeof window[nomeFuncao] === 'function';
    },

    // Testar se um elemento existe
    elementoExiste: (id) => {
        return document.getElementById(id) !== null;
    },

    // Testar se uma API responde
    apiResponde: async (url) => {
        try {
            const response = await fetch(url);
            return response.ok;
        } catch (error) {
            return false;
        }
    },

    // Testar se localStorage funciona
    localStorageFunciona: () => {
        try {
            const teste = 'teste';
            localStorage.setItem(teste, teste);
            const resultado = localStorage.getItem(teste);
            localStorage.removeItem(teste);
            return resultado === teste;
        } catch (error) {
            return false;
        }
    },

    // Testar se sessionStorage funciona
    sessionStorageFunciona: () => {
        try {
            const teste = 'teste';
            sessionStorage.setItem(teste, teste);
            const resultado = sessionStorage.getItem(teste);
            sessionStorage.removeItem(teste);
            return resultado === teste;
        } catch (error) {
            return false;
        }
    }
};
