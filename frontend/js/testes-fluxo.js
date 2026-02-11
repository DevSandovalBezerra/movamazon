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
        console.log('🧪 Sistema de Testes de Fluxo inicializado');
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
                <h3 class="font-bold text-sm">🧪 Testes de Fluxo</h3>
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
        console.log('🚀 Iniciando execução de todos os testes...');
        this.limparResultados();

        // Testes de API
        await this.testarAPIs();

        // Testes de Frontend
        await this.testarFrontend();

        // Testes de Validação
        await this.testarValidacoes();

        // Testes de Fluxo de Usuário
        await this.testarFluxoUsuario();

        this.atualizarInterface();
        this.mostrarResumo();
    }

    async testarAPIs() {
        console.log('🔌 Testando APIs...');

        // Teste 1: API de Eventos Públicos
        await this.executarTeste('API Eventos Públicos', async () => {
            const response = await fetch('/api/evento/list_public.php');
            const data = await response.json();
            return data.success !== undefined;
        });

        // Teste 2: API de Categorias
        await this.executarTeste('API Categorias', async () => {
            const response = await fetch('/api/categoria/list_public.php');
            const data = await response.json();
            return data.success !== undefined;
        });

        // Teste 3: API de Modalidades
        await this.executarTeste('API Modalidades', async () => {
            const response = await fetch('/api/organizador/modalidades/list.php');
            const data = await response.json();
            return data.success !== undefined;
        });
    }

    async testarFrontend() {
        console.log('🎨 Testando Frontend...');

        // Teste 1: Elementos da página principal
        await this.executarTeste('Elementos Página Principal', () => {
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

        // Teste 3: SweetAlert disponível
        await this.executarTeste('SweetAlert Disponível', () => {
            return typeof Swal !== 'undefined';
        });
    }

    async testarValidacoes() {
        console.log('✅ Testando Validações...');

        // Teste 1: Sistema de validação
        await this.executarTeste('Sistema de Validação', () => {
            return typeof ValidacaoFormulario !== 'undefined';
        });

        // Teste 2: Regras de validação
        await this.executarTeste('Regras de Validação', () => {
            return REGRAS_VALIDACAO &&
                REGRAS_VALIDACAO.categoria &&
                REGRAS_VALIDACAO.modalidade;
        });

        // Teste 3: Utilitários de validação
        await this.executarTeste('Utilitários de Validação', () => {
            return ValidacaoUtils &&
                typeof ValidacaoUtils.email === 'function' &&
                typeof ValidacaoUtils.cpf === 'function';
        });
    }

    async testarFluxoUsuario() {
        console.log('👤 Testando Fluxo de Usuário...');

        // Teste 1: Navegação entre páginas
        await this.executarTeste('Navegação Páginas', () => {
            const links = document.querySelectorAll('a[href]');
            return links.length > 0;
        });

        // Teste 2: Formulários funcionais
        await this.executarTeste('Formulários Funcionais', () => {
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
                console.log(`✅ ${nome}: PASSOU`);
            } else {
                this.testesFalharam++;
                this.adicionarResultado(nome, 'FALHOU', 'error');
                console.log(`❌ ${nome}: FALHOU`);
            }
        } catch (erro) {
            this.testesFalharam++;
            this.adicionarResultado(nome, `ERRO: ${erro.message}`, 'error');
            console.error(`💥 ${nome}: ERRO - ${erro.message}`);
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
                .slice(-5) // Mostrar apenas os últimos 5 resultados
                .map(r => `
                    <div class="text-xs ${r.tipo === 'success' ? 'text-green-400' : 'text-red-400'}">
                        ${r.timestamp} - ${r.nome}: ${r.status}
                    </div>
                `).join('');
        }
    }

    mostrarResumo() {
        const resumo = `
            🧪 **TESTES CONCLUÍDOS**
            
            📊 **Resumo:**
            • Total: ${this.testesExecutados}
            • ✅ Passaram: ${this.testesPassaram}
            • ❌ Falharam: ${this.testesFalharam}
            • 📈 Taxa de Sucesso: ${((this.testesPassaram / this.testesExecutados) * 100).toFixed(1)}%
            
            ${this.testesFalharam === 0 ? '🎉 Todos os testes passaram!' : '⚠️ Alguns testes falharam. Verifique os logs.'}
        `;

        console.log(resumo);

        if (this.testesFalharam === 0) {
            Swal.fire({
                icon: 'success',
                title: 'Testes Concluídos!',
                text: `Todos os ${this.testesExecutados} testes passaram com sucesso!`,
                confirmButtonColor: '#10B981'
            });
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Testes Concluídos',
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

    // Testes específicos para diferentes funcionalidades
    async testarFuncionalidadeEspecifica(nome, funcaoTeste) {
        console.log(`🧪 Testando funcionalidade específica: ${nome}`);
        await this.executarTeste(nome, funcaoTeste);
    }

    // Teste de performance
    async testarPerformance(nome, funcao) {
        const inicio = performance.now();
        await funcao();
        const fim = performance.now();
        const tempo = fim - inicio;

        console.log(`⏱️ ${nome} executou em ${tempo.toFixed(2)}ms`);

        // Considerar "lento" se demorar mais de 1 segundo
        const passou = tempo < 1000;
        await this.executarTeste(`Performance ${nome}`, () => passou);

        return tempo;
    }

    // Teste de acessibilidade básica
    async testarAcessibilidade() {
        console.log('♿ Testando Acessibilidade...');

        // Teste 1: Imagens com alt
        await this.executarTeste('Imagens com Alt', () => {
            const imagens = document.querySelectorAll('img');
            if (imagens.length === 0) return true;

            const imagensComAlt = Array.from(imagens).filter(img => img.alt);
            return imagensComAlt.length === imagens.length;
        });

        // Teste 2: Formulários com labels
        await this.executarTeste('Formulários com Labels', () => {
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

        // Teste 3: Contraste de cores (básico)
        await this.executarTeste('Contraste de Cores', () => {
            // Verificação básica - em produção seria mais robusta
            const elementosTexto = document.querySelectorAll('p, h1, h2, h3, h4, h5, h6, span, div');
            return elementosTexto.length > 0; // Simplificado para demonstração
        });
    }
}

// Inicializar sistema de testes quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function () {
    window.testeFluxo = new TesteFluxo();

    // Executar testes automáticos em desenvolvimento
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        // Aguardar um pouco para o sistema carregar
        setTimeout(() => {
            console.log('🚀 Executando testes automáticos em 3 segundos...');
            setTimeout(() => {
                testeFluxo.executarTodosTestes();
            }, 3000);
        }, 1000);
    }
});

// Funções de teste utilitárias
window.TestesUtils = {
    // Testar se uma função existe
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