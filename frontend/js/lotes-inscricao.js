// Variáveis globais
let lotes = [];
let modalidadesDisponiveis = [];
let loteEditando = null;

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    carregarLotes();
    configurarEventos();
});

// Configurar eventos
function configurarEventos() {
    // Evento para carregar modalidades quando selecionar evento
    const eventoSelect = document.getElementById('evento-id');
    if (eventoSelect) {
        eventoSelect.addEventListener('change', function() {
            const eventoId = this.value;
            const container = document.getElementById('modalidades-container');
            
            if (!container) {
                console.warn('Container modalidades-container não encontrado. Modal pode não estar aberto.');
                return;
            }
            
            if (eventoId) {
                carregarModalidades(eventoId);
            } else {
                container.innerHTML = '<p class="text-gray-500 text-sm">Selecione um evento para ver as modalidades disponíveis</p>';
            }
        });
    }

    // Event listener para mudança no filtro de evento
    document.getElementById('filtroEvento').addEventListener('change', function() {
        carregarLotes();
    });

    // Auto-complete para preço por extenso
document.getElementById('preco').addEventListener('input', function() {
        const preco = parseFloat(this.value) || 0;
        const extenso = converterParaExtenso(preco);
        document.getElementById('preco-extenso').value = extenso;
    });
}

// Carregar lotes
async function carregarLotes() {
    try {
        const params = new URLSearchParams();
        const eventoId = document.getElementById('filtroEvento').value;
        const tipoPublico = document.getElementById('filtroTipoPublico').value;
        const ativo = document.getElementById('filtroStatus').value;
        
        if (eventoId && eventoId !== '') params.append('evento_id', eventoId);
        if (tipoPublico && tipoPublico !== '') params.append('tipo_publico', tipoPublico);
        if (ativo !== '') params.append('ativo', ativo);
        
        const response = await fetch(`../../../api/organizador/lotes-inscricao/list.php?${params}`);
        const data = await response.json();
        
        if (data.success) {
            lotes = data.lotes;
            renderizarLotes();
        } else {
            throw new Error(data.message || 'Erro ao carregar lotes');
        }
    } catch (error) {
        console.error('Erro ao carregar lotes:', error);
        mostrarErro('Erro ao carregar lotes: ' + error.message);
    }
}

// Renderizar lotes na tabela
function renderizarLotes() {
    const tbody = document.getElementById('tbody-lotes');
    const container = document.getElementById('tabela-lotes');
    const semLotes = document.getElementById('sem-lotes');
    
    if (lotes.length === 0) {
        container.classList.add('hidden');
        semLotes.classList.remove('hidden');
        return;
    }
    
    container.classList.remove('hidden');
    semLotes.classList.add('hidden');
    
    tbody.innerHTML = lotes.map(lote => `
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-brand-green text-white">
                            Lote ${lote.numero_lote}
                        </span>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">${lote.modalidade_completa}</div>
                        <div class="text-sm text-gray-500">${lote.tipo_publico_formatado} • ${lote.faixa_etaria}</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${lote.evento_nome}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">${lote.preco_formatado}</div>
                ${lote.preco_por_extenso ? `<div class="text-xs text-gray-500">${lote.preco_por_extenso}</div>` : ''}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${lote.data_inicio_formatada}</div>
                <div class="text-sm text-gray-500">até ${lote.data_fim_formatada}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusClass(lote.status)}">
                    ${getStatusText(lote.status)}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                    <button onclick="editarLote(${lote.id})" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-brand-green bg-green-50 hover:bg-green-100">
                        <i class="fas fa-edit mr-1"></i>
                        Editar
                    </button>
                    <button onclick="duplicarLote(${lote.id})" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-blue-600 bg-blue-50 hover:bg-blue-100">
                        <i class="fas fa-copy mr-1"></i>
                        Duplicar
                    </button>
                    <button onclick="toggleLote(${lote.id}, ${!lote.ativo})" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md ${lote.ativo ? 'text-red-600 bg-red-50 hover:bg-red-100' : 'text-green-600 bg-green-50 hover:bg-green-100'}">
                        <i class="fas fa-${lote.ativo ? 'times' : 'check'} mr-1"></i>
                        ${lote.ativo ? 'Desativar' : 'Ativar'}
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Funções de status
function getStatusClass(status) {
    switch (status) {
        case 'ativo': return 'bg-green-100 text-green-800';
        case 'futuro': return 'bg-blue-100 text-blue-800';
        case 'expirado': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}

function getStatusText(status) {
    switch (status) {
        case 'ativo': return 'Ativo';
        case 'futuro': return 'Futuro';
        case 'expirado': return 'Expirado';
        default: return 'Inativo';
    }
}

// Aplicar filtros
function aplicarFiltros() {
    carregarLotes();
}

// Carregar modalidades de um evento (estrutura otimizada)
async function carregarModalidades(eventoId) {
    try {
        const response = await fetch(`../../../api/organizador/lotes-inscricao/modalidades.php?evento_id=${eventoId}`);
        const data = await response.json();
        
        if (data.success) {
            modalidadesDisponiveis = data.modalidades;
            renderizarModalidades();
        } else {
            throw new Error(data.message || 'Erro ao carregar modalidades');
        }
    } catch (error) {
        console.error('Erro ao carregar modalidades:', error);
        Swal.fire('Erro', 'Erro ao carregar modalidades: ' + error.message, 'error');
    }
}

// Renderizar modalidades usando checkboxes (estrutura otimizada)
function renderizarModalidades(selecionadas = []) {
    const container = document.getElementById('modalidades-container');
    if (!container) {
        console.warn('Container modalidades-container não encontrado. Modal pode não estar aberto.');
        return;
    }
    
    if (modalidadesDisponiveis.length === 0) {
        container.innerHTML = '<div class="text-gray-400 text-sm">Nenhuma modalidade disponível</div>';
        return;
    }
    
    renderizarModalidadesCheckboxes('modalidades-container', modalidadesDisponiveis, selecionadas);
}

// Abrir modal para criar novo lote
function abrirModalCriar() {
    loteEditando = null;
    document.getElementById('modal-title').textContent = 'Novo Lote de Inscrição';
    document.getElementById('btn-salvar-text').textContent = 'Salvar Lote';
    document.getElementById('form-lote').reset();
    document.getElementById('lote-id').value = '';
    const container = document.getElementById('modalidades-container');
    if (container) {
        container.innerHTML = '<p class="text-gray-500 text-sm">Selecione um evento para ver as modalidades disponíveis</p>';
    }
    document.getElementById('modal-lote').classList.remove('hidden');
}

// Abrir modal para editar lote
async function editarLote(loteId) {
    try {
        const response = await fetch(`../../../api/organizador/lotes-inscricao/get.php?id=${loteId}`);
        const data = await response.json();
        
        if (data.success) {
            loteEditando = data.lote;
            preencherFormulario(data.lote);
            document.getElementById('modal-title').textContent = 'Editar Lote de Inscrição';
            document.getElementById('btn-salvar-text').textContent = 'Atualizar Lote';
            document.getElementById('modal-lote').classList.remove('hidden');
        } else {
            throw new Error(data.message || 'Erro ao carregar lote');
        }
    } catch (error) {
        console.error('Erro ao carregar lote:', error);
        Swal.fire('Erro', 'Erro ao carregar lote: ' + error.message, 'error');
    }
}

// Preencher formulário com dados do lote (estrutura otimizada)
function preencherFormulario(lote) {
    document.getElementById('lote-id').value = lote.id;
    document.getElementById('evento-id').value = lote.evento_id;
    document.getElementById('numero-lote').value = lote.numero_lote;
    document.getElementById('preco').value = lote.preco;
    document.getElementById('preco-extenso').value = lote.preco_por_extenso || '';
    document.getElementById('data-inicio').value = lote.data_inicio;
    document.getElementById('data-fim').value = lote.data_fim;
    document.getElementById('vagas-disponiveis').value = lote.vagas_disponiveis || '';
    document.getElementById('taxa-servico').value = lote.taxa_servico;
    document.getElementById('quem-paga-taxa').value = lote.quem_paga_taxa;
    document.getElementById('idade-min').value = lote.idade_min;
    document.getElementById('idade-max').value = lote.idade_max;
    document.getElementById('desconto-idoso').checked = lote.desconto_idoso;
    
    // Carregar modalidades e marcar as modalidades do lote
    const modalidadesSelecionadas = lote.modalidades 
        ? lote.modalidades.map(m => m.id || m) 
        : (lote.modalidade_id ? [lote.modalidade_id] : []);
    
    carregarModalidades(lote.evento_id).then(() => {
        setTimeout(() => {
            renderizarModalidades(modalidadesSelecionadas);
        }, 100);
    });
}

// Fechar modal
function fecharModal() {
    document.getElementById('modal-lote').classList.add('hidden');
    loteEditando = null;
}

// Salvar lote (criar ou atualizar)
async function salvarLote(dados) {
    try {
        // Validar seleção de modalidades
        const validacao = validarSelecaoModalidades('modalidades-container', 1);
        if (!validacao.valido) {
            Swal.fire('Atenção', validacao.mensagem, 'warning');
            return;
        }
        
        // Coletar modalidades selecionadas
        const modalidades = obterModalidadesSelecionadas('modalidades-container');
        dados.modalidades = modalidades;
        
        const url = loteEditando ? 
            `../../../api/organizador/lotes-inscricao/update.php` : 
            `../../../api/organizador/lotes-inscricao/create.php`;
        
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(dados)
        });
        
        const data = await response.json();
        
        if (data.success) {
            Swal.fire('Sucesso', data.message, 'success');
            fecharModal();
            carregarLotes();
        } else {
            throw new Error(data.message || 'Erro ao salvar lote');
        }
    } catch (error) {
        console.error('Erro ao salvar lote:', error);
        Swal.fire('Erro', 'Erro ao salvar lote: ' + error.message, 'error');
    }
}

// Duplicar lote
async function duplicarLote(loteId) {
    try {
        const response = await fetch(`../../../api/organizador/lotes-inscricao/get.php?id=${loteId}`);
        const data = await response.json();
        
        if (data.success) {
            const lote = data.lote;
            lote.numero_lote = lote.numero_lote + 1; // Incrementar número do lote
            lote.preco_por_extenso = ''; // Limpar preço por extenso
            
            loteEditando = null; // Não é edição, é criação
            preencherFormulario(lote);
            document.getElementById('modal-title').textContent = 'Duplicar Lote de Inscrição';
            document.getElementById('btn-salvar-text').textContent = 'Salvar Lote';
            document.getElementById('modal-lote').classList.remove('hidden');
        } else {
            throw new Error(data.message || 'Erro ao carregar lote');
        }
    } catch (error) {
        console.error('Erro ao duplicar lote:', error);
        Swal.fire('Erro', 'Erro ao duplicar lote: ' + error.message, 'error');
    }
}

// Toggle ativo/inativo
async function toggleLote(loteId, ativo) {
    const acao = ativo ? 'ativar' : 'desativar';
    
    const result = await Swal.fire({
        title: 'Confirmar ação',
        text: `Deseja ${acao} este lote?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sim',
        cancelButtonText: 'Não'
    });
    
    if (result.isConfirmed) {
        try {
            const response = await fetch('../../../api/organizador/lotes-inscricao/activate.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: loteId,
                    ativo: ativo
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                Swal.fire('Sucesso', data.message, 'success');
                carregarLotes();
            } else {
                throw new Error(data.message || 'Erro ao alterar status');
            }
        } catch (error) {
            console.error('Erro ao alterar status:', error);
            Swal.fire('Erro', 'Erro ao alterar status: ' + error.message, 'error');
        }
    }
}

// Event listener para o formulário (estrutura otimizada)
document.getElementById('form-lote').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Coletar dados do formulário
    const dados = {
        evento_id: parseInt(document.getElementById('evento-id').value),
        numero_lote: parseInt(document.getElementById('numero-lote').value),
        preco: parseFloat(document.getElementById('preco').value),
        preco_por_extenso: document.getElementById('preco-extenso').value || '',
        data_inicio: document.getElementById('data-inicio').value,
        data_fim: document.getElementById('data-fim').value,
        vagas_disponiveis: document.getElementById('vagas-disponiveis').value ? parseInt(document.getElementById('vagas-disponiveis').value) : null,
        taxa_servico: parseFloat(document.getElementById('taxa-servico').value) || 0,
        quem_paga_taxa: document.getElementById('quem-paga-taxa').value,
        idade_min: parseInt(document.getElementById('idade-min').value) || 0,
        idade_max: parseInt(document.getElementById('idade-max').value) || 100,
        desconto_idoso: document.getElementById('desconto-idoso').checked
    };
    
    // Se estiver editando, adicionar ID
    if (loteEditando) {
        dados.id = parseInt(document.getElementById('lote-id').value);
    }
    
    await salvarLote(dados);
});

// Funções auxiliares
function mostrarLoading() {
    document.getElementById('loading-lotes').classList.remove('hidden');
    document.getElementById('tabela-lotes').classList.add('hidden');
    document.getElementById('error-lotes').classList.add('hidden');
    document.getElementById('sem-lotes').classList.add('hidden');
}

function mostrarErro(mensagem) {
    document.getElementById('loading-lotes').classList.add('hidden');
    document.getElementById('tabela-lotes').classList.add('hidden');
    document.getElementById('error-lotes').classList.remove('hidden');
    document.getElementById('error-message').textContent = mensagem;
}

// Converter número para extenso
function converterParaExtenso(numero) {
    const UNIDADES = ['', 'um', 'dois', 'três', 'quatro', 'cinco', 'seis', 'sete', 'oito', 'nove'];
    const DEZ_A_DEZENOVE = ['dez', 'onze', 'doze', 'treze', 'quatorze', 'quinze', 'dezesseis', 'dezessete', 'dezoito', 'dezenove'];
    const DEZENAS = ['', '', 'vinte', 'trinta', 'quarenta', 'cinquenta', 'sessenta', 'setenta', 'oitenta', 'noventa'];
    const CENTENAS = ['', 'cento', 'duzentos', 'trezentos', 'quatrocentos', 'quinhentos', 'seiscentos', 'setecentos', 'oitocentos', 'novecentos'];

    function bloco(n) {
        let t = [];
        const c = Math.floor(n / 100);
        const d = Math.floor((n % 100) / 10);
        const u = n % 10;

        if (n === 0) return '';
        if (n === 100) return 'cem';
        if (c) t.push(CENTENAS[c]);
        if (d === 1) {
            t.push(DEZ_A_DEZENOVE[u]);
        } else {
            if (d) t.push(DEZENAS[d]);
            if (u) t.push(UNIDADES[u]);
        }
        return t.join(' e ');
    }

    function montaExtenso(n) {
        if (n === 0) return 'zero';
        let partes = [];

        const bilhoes = Math.floor(n / 1_000_000_000);
        const milhoes = Math.floor((n % 1_000_000_000) / 1_000_000);
        const milhares = Math.floor((n % 1_000_000) / 1000);
        const centenas = n % 1000;

        if (bilhoes) partes.push(`${bloco(bilhoes)} ${bilhoes === 1 ? 'bilhão' : 'bilhões'}`);
        if (milhoes) partes.push(`${bloco(milhoes)} ${milhoes === 1 ? 'milhão' : 'milhões'}`);
        if (milhares) partes.push(`${bloco(milhares)} mil`);
        if (centenas) partes.push(bloco(centenas));

        return partes.filter(Boolean).join(' e ');
    }

    const inteiro = Math.floor(numero);
    const centavos = Math.round((numero - inteiro) * 100);

    const extensoInteiro = montaExtenso(inteiro);
    const extensoCentavos = centavos ? `${montaExtenso(centavos)} ${centavos === 1 ? 'centavo' : 'centavos'}` : '';

    let resultado = '';
    if (inteiro > 0) {
        resultado = `${extensoInteiro} ${inteiro === 1 ? 'real' : 'reais'}`;
    }
    if (centavos) {
        resultado = resultado ? `${resultado} e ${extensoCentavos}` : extensoCentavos;
    }
    if (!resultado) resultado = 'zero real';

    return resultado.charAt(0).toUpperCase() + resultado.slice(1);
}