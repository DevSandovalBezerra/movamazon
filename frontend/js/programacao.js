if (window.getApiBase) { window.getApiBase(); }
// programacao.js
/**
 * PÁGINA DE PROGRAMAAÇÃO (CHECKLIST) - GERENCIAMENTO DE CONFIGURAÇÕES DO EVENTO
 * 
 * Esta página mantém a flexibilidade de recuperar e atualizar dados de múltiplas tabelas:
 * 
 * 1. TABELA 'eventos' (PRINCIPAL):
 *    - Recupera: carregarEventoSelecionado() -> API eventos/get.php
 *    - Atualiza: salvarEvento() -> API eventos/update.php
 *    - Campos: nome, categoria, data_inicio, hora_inicio, local, cep, url_mapa, 
 *              logradouro, numero, cidade, estado, pais, limite_vagas, 
 *              data_fim, hora_fim_inscricoes, taxas, etc.
 * 
 * 2. TABELA 'modalidades':
 *    - Recupera: carregarModalidades() -> API modalidades/list.php
 *    - Exibe modalidades e lotes do evento
 * 
 * 3. TABELA 'programacao_evento' (NOVA FUNCIONALIDADE):
 *    - Recupera: carregarProgramacao() -> API programacao/list.php
 *    - Cria: criarItemProgramacao() -> API programacao/create.php
 *    - Atualiza: atualizarItemProgramacao() -> API programacao/update.php
 *    - Exclui: excluirItemProgramacao() -> API programacao/delete.php
 *    - Campos: tipo, titulo, descricao, ordem, ativo
 * 
 * 4. FUNAÇÃO DE VERIFICAAÇÃO DE CONFIGURAÇÕES:
 *    - A API check_implementation_status.php verifica se o evento tem programação
 *    - Verifica se há itens ativos na tabela programacao_evento
 *    - Esta verificação é usada pelo checklist na página principal do organizador
 * 
 * IMPORTANTE: Todas as funcionalidades antigas foram preservadas.
 * A página continua sendo uma página de configuração/checklist do evento.
 */

// Variáveis globais
let modoEdicao = false;
let eventoAtual = null;

// Inicialização
window.addEventListener('DOMContentLoaded', function () {
    carregarEventos();
    configurarEventListeners();
});

// Corrigir caminho base para APIs
const API_BASE = (window.API_BASE || '/api') + '/organizador/eventos/'; // API para tabela 'eventos'
const API_PROGRAMACAO = (window.API_BASE || '/api') + '/organizador/programacao/'; // API para tabela 'programacao_evento'

// Configurar event listeners
function configurarEventListeners() {
    // Filtros
    const filtroEvento = document.getElementById('filtro-evento');
    if (filtroEvento) {
        filtroEvento.addEventListener('change', function () {
            const eventoId = this.value;
            if (eventoId) {
                carregarEventoSelecionado(eventoId);
            }
        });
    }
    const filtroStatus = document.getElementById('filtro-status');
    if (filtroStatus) {
        filtroStatus.addEventListener('change', aplicarFiltros);
    }
    
    // Regulamento: botão substituir
    const btnSubstituirRegulamento = document.getElementById('btn-substituir-regulamento');
    if (btnSubstituirRegulamento) {
        btnSubstituirRegulamento.addEventListener('click', substituirRegulamento);
    }
    
    // Regulamento: input de arquivo
    const inputRegulamentoArquivo = document.getElementById('regulamentoArquivo');
    if (inputRegulamentoArquivo) {
        inputRegulamentoArquivo.addEventListener('change', async function() {
            if (!this.files || this.files.length === 0) return;

            const file = this.files[0];
            const maxSize = 10 * 1024 * 1024; // 10MB

            // Validar tamanho
            if (file.size > maxSize) {
                Swal.fire('Erro', 'Arquivo muito grande. Tamanho máximo: 10MB.', 'error');
                this.value = '';
                return;
            }

            // Validar tipo
            const ext = file.name.split('.').pop().toLowerCase();
            if (!['pdf', 'doc', 'docx'].includes(ext)) {
                Swal.fire('Erro', 'Formato não permitido. Use PDF, DOC ou DOCX.', 'error');
                this.value = '';
                return;
            }

            // Upload imediato (modo edição): troca o arquivo na hora
            if (modoEdicao) {
                await salvarRegulamento();
            }
        });
    }

    // Logotipo do evento: Alterar Imagem
    const btnAlterarImagemEvento = document.getElementById('btnAlterarImagemEvento');
    const inputImagemEvento = document.getElementById('inputImagemEvento');
    if (btnAlterarImagemEvento && inputImagemEvento) {
        btnAlterarImagemEvento.addEventListener('click', function () {
            if (!eventoAtual || !eventoAtual.id) {
                Swal.fire('Aviso', 'Selecione um evento primeiro.', 'info');
                return;
            }
            inputImagemEvento.click();
        });
        inputImagemEvento.addEventListener('change', async function () {
            const inputEl = this;
            if (!inputEl.files || inputEl.files.length === 0) return;
            if (!eventoAtual || !eventoAtual.id) return;
            const file = inputEl.files[0];
            const maxSize = 5 * 1024 * 1024; // 5MB
            if (file.size > maxSize) {
                Swal.fire('Erro', 'Imagem muito grande. Tamanho máximo: 5MB.', 'error');
                inputEl.value = '';
                return;
            }
            const ext = file.name.split('.').pop().toLowerCase();
            if (!['jpg', 'jpeg', 'png', 'webp'].includes(ext)) {
                Swal.fire('Erro', 'Formato não permitido. Use JPG, PNG ou WEBP.', 'error');
                inputEl.value = '';
                return;
            }
            try {
                const formData = new FormData();
                formData.append('evento_id', eventoAtual.id);
                formData.append('imagem', file);
                const response = await fetch(API_BASE + 'upload-imagem.php', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.success && data.data && data.data.imagem) {
                    eventoAtual.imagem = data.data.imagem;
                    carregarImagemEvento(eventoAtual.imagem, eventoAtual.id, true);
                    Swal.fire('Sucesso', 'Imagem atualizada com sucesso.', 'success');
                } else {
                    Swal.fire('Erro', data.error || 'Erro ao enviar imagem.', 'error');
                }
                inputEl.value = '';
            } catch (err) {
                console.error(err);
                Swal.fire('Erro', 'Erro ao enviar imagem.', 'error');
                inputEl.value = '';
            }
        });
    }

    // Logotipo do evento: Excluir
    const btnExcluirImagemEvento = document.getElementById('btnExcluirImagemEvento');
    if (btnExcluirImagemEvento) {
        btnExcluirImagemEvento.addEventListener('click', async function () {
            if (!eventoAtual || !eventoAtual.id) {
                Swal.fire('Aviso', 'Selecione um evento primeiro.', 'info');
                return;
            }
            const confirmacao = await Swal.fire({
                title: 'Excluir logotipo?',
                text: 'A imagem do evento será removida.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sim, excluir',
                cancelButtonText: 'Cancelar'
            });
            if (!confirmacao.isConfirmed) return;
            try {
                const nome = document.getElementById('nomeEvento') ? document.getElementById('nomeEvento').value : eventoAtual.nome;
                const response = await fetch(API_BASE + 'update.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: eventoAtual.id, nome: nome, imagem: null })
                });
                const data = await response.json();
                if (data.success) {
                    eventoAtual.imagem = null;
                    limparImagemEvento();
                    Swal.fire('Sucesso', 'Logotipo removido.', 'success');
                } else {
                    Swal.fire('Erro', data.message || 'Erro ao remover imagem.', 'error');
                }
            } catch (err) {
                console.error(err);
                Swal.fire('Erro', 'Erro ao remover imagem.', 'error');
            }
        });
    }
}

// Carregar eventos para o filtro
async function carregarEventos() {
    try {
        const response = await fetch(API_BASE + 'list.php');
        const data = await response.json();
        if (data.success) {
            const select = document.getElementById('filtro-evento');
            select.innerHTML = '<option value="">Selecione um evento</option>';
            if (data.data && data.data.eventos) {
                data.data.eventos.forEach(evento => {
                    const option = document.createElement('option');
                    option.value = evento.id;
                    option.textContent = evento.nome;
                    select.appendChild(option);
                });
            }

            // Adicionar event listener para mudança de evento
            select.addEventListener('change', function () {
                const eventoId = this.value;
                if (eventoId) {
                    carregarEventoSelecionado(eventoId);
                } else {
                    limparFormulario();
                }
            });
        }
    } catch (error) {
        console.error('Erro ao carregar eventos:', error);
    }
}

/**
 * Carregar evento selecionado e preencher formulário
 * 
 * Esta função é CORE da página e deve SEMPRE:
 * 1. Recuperar dados da tabela 'eventos' via API eventos/get.php
 * 2. Preencher o formulário com dados do evento
 * 3. Carregar dados relacionados (modalidades, programação)
 * 
 * MANTIDA E PRESERVADA - Funcionalidade original
 */
async function carregarEventoSelecionado(eventoId) {
    if (eventoId) {
        try {
            console.log(' DEBUG - Carregando evento ID:', eventoId);
            
            // 1. RECUPERAR DADOS DA TABELA 'eventos' (FUNCIONALIDADE ORIGINAL)
            const response = await fetch(API_BASE + `get.php?id=${eventoId}`);
            console.log(' DEBUG - Response status:', response.status);
            const data = await response.json();
            console.log(' DEBUG - Dados recebidos:', data);

            if (data.success && data.data && data.data.id) {
                eventoAtual = data.data;
                console.log(' DEBUG - Evento atual definido:', eventoAtual);

                // 2. CARREGAR DADOS RELACIONADOS (MÚLTIPLAS TABELAS)
                // - Modalidades (tabela 'modalidades')
                await carregarModalidades(eventoId);

                // - Programação (tabela 'programacao_evento') - NOVA FUNCIONALIDADE
                await carregarProgramacao(eventoId);

                // 3. PREENCHER FORMULÁRIO COM DADOS DO EVENTO (FUNCIONALIDADE ORIGINAL)
                preencherFormulario(eventoAtual);
                document.getElementById('filtro-evento').value = eventoId;
            } else {
                console.error(' DEBUG - Evento não encontrado na resposta:', data);
                Swal.fire('Erro', 'Evento não encontrado.', 'error');
            }
        } catch (error) {
            console.error(' DEBUG - Erro ao carregar evento:', error);
        }
    }
}

// Carregar modalidades do evento (versão dinâmica)
async function carregarModalidades(eventoId) {
    try {
        console.log(' DEBUG - Iniciando carregamento de modalidades para evento ID:', eventoId);

        // Mostrar loading
        mostrarLoadingModalidades();

        // Buscar modalidades
        console.log(' DEBUG - Buscando modalidades...');
        const modalidadesResponse = await fetch(`${window.API_BASE || '/api'}/organizador/modalidades/list.php?evento_id=${eventoId}`);
        console.log(' DEBUG - Response modalidades status:', modalidadesResponse.status);
        const modalidadesData = await modalidadesResponse.json();
        console.log(' DEBUG - Dados modalidades:', modalidadesData);

        // Buscar lotes com valores
        console.log(' DEBUG - Buscando lotes...');
        const lotesResponse = await fetch(`${window.API_BASE || '/api'}/organizador/lotes-inscricao/list.php?evento_id=${eventoId}`);
        console.log(' DEBUG - Response lotes status:', lotesResponse.status);
        const lotesData = await lotesResponse.json();
        console.log(' DEBUG - Dados lotes:', lotesData);

        if (modalidadesData.success && lotesData.success) {
            // A API retorna modalidades diretamente no array
            const modalidades = modalidadesData.modalidades || [];
            const lotes = lotesData.lotes || [];
            
            eventoAtual.modalidades = modalidades;
            eventoAtual.lotes = lotes;

            console.log(' DEBUG - Renderizando modalidades dinâmicas...');
            console.log(' DEBUG - Modalidades recebidas:', modalidades);
            console.log(' DEBUG - Lotes recebidos:', lotes);
            console.log(' DEBUG - Total modalidades:', modalidades.length);
            console.log(' DEBUG - Total lotes:', lotes.length);
            
            // Renderizar interface dinâmica
            renderizarModalidadesDinamicas(modalidades, lotes);
        } else {
            console.error(' DEBUG - Erro nas APIs:', {
                modalidades: modalidadesData,
                lotes: lotesData
            });
            let mensagemErro = 'Erro ao carregar modalidades';
            if (!modalidadesData.success) {
                mensagemErro += ': ' + (modalidadesData.message || 'Erro desconhecido');
            }
            if (!lotesData.success) {
                mensagemErro += ' | Erro ao carregar lotes: ' + (lotesData.message || 'Erro desconhecido');
            }
            mostrarErroModalidades(mensagemErro);
        }
    } catch (error) {
        console.error(' DEBUG - Erro ao carregar modalidades:', error);
        eventoAtual.modalidades = [];
        eventoAtual.lotes = [];
        mostrarErroModalidades('Erro ao carregar modalidades');
    }
}

// Aplicar filtros
function aplicarFiltros() {
    const evento = document.getElementById('filtro-evento').value;
    if (evento) {
        carregarEventoSelecionado(evento);
    }
}

// Preencher formulário com dados do evento
function preencherFormulario(evento) {
    console.log(' DEBUG - Preenchendo formulário com dados:', evento);

    // Informações Gerais
    document.getElementById('nomeEvento').value = evento.nome || '';
    document.getElementById('categoriaEvento').value = evento.categoria || '';
    document.getElementById('dataInicio').value = evento.data_inicio || '';
    document.getElementById('horaInicio').value = evento.hora_inicio || '';
    document.getElementById('generoEvento').value = evento.genero || '';
    document.getElementById('dataRealizacao').value = evento.data_realizacao || '';
    document.getElementById('descricaoEvento').value = evento.descricao || '';
    
    // Regulamento: verificar se existe arquivo
    const regulamentoArquivoExistente = document.getElementById('regulamento-arquivo-existente');
    const regulamentoUploadContainer = document.getElementById('regulamento-upload-container');
    const linkRegulamento = document.getElementById('link-regulamento');
    const nomeArquivoRegulamento = document.getElementById('nome-arquivo-regulamento');
    const btnSubstituirRegulamento = document.getElementById('btn-substituir-regulamento');
    const confirmacaoSubstituir = document.getElementById('regulamento-confirmacao-substituir');
    const inputRegulamentoArquivo = document.getElementById('regulamentoArquivo');
    const regulamentoPlaceholder = document.getElementById('regulamento-placeholder');
    
    console.log(' DEBUG - Regulamento arquivo:', evento.regulamento_arquivo);
    console.log(' DEBUG - Elementos encontrados:', {
        regulamentoArquivoExistente: !!regulamentoArquivoExistente,
        linkRegulamento: !!linkRegulamento,
        nomeArquivoRegulamento: !!nomeArquivoRegulamento,
        btnSubstituirRegulamento: !!btnSubstituirRegulamento
    });
    
    if (regulamentoArquivoExistente && linkRegulamento && nomeArquivoRegulamento) {
        if (evento.regulamento_arquivo && evento.regulamento_arquivo.trim() !== '') {
            // Existe arquivo: mostrar link
            const nomeArquivo = evento.regulamento_arquivo.split('/').pop() || evento.regulamento_arquivo.split('\\').pop() || evento.regulamento_arquivo;
            nomeArquivoRegulamento.textContent = nomeArquivo;
            linkRegulamento.href = `${window.API_BASE || '/api'}/uploads/regulamentos/download.php?file=${encodeURIComponent(nomeArquivo)}`;
            regulamentoArquivoExistente.classList.remove('hidden');
            if (regulamentoPlaceholder) regulamentoPlaceholder.classList.add('hidden');
            if (regulamentoUploadContainer) regulamentoUploadContainer.classList.add('hidden');
            if (confirmacaoSubstituir) confirmacaoSubstituir.classList.add('hidden');
            // Mostrar botão substituir apenas em modo edição
            if (btnSubstituirRegulamento) {
                if (modoEdicao) {
                    btnSubstituirRegulamento.classList.remove('hidden');
                } else {
                    btnSubstituirRegulamento.classList.add('hidden');
                }
            }
            console.log('... Regulamento: Link exibido para arquivo:', nomeArquivo);
        } else {
            // Não existe arquivo: esconder link e mostrar input (se em modo edição)
            regulamentoArquivoExistente.classList.add('hidden');
            if (btnSubstituirRegulamento) btnSubstituirRegulamento.classList.add('hidden');
            if (regulamentoPlaceholder) regulamentoPlaceholder.classList.remove('hidden');
            if (modoEdicao) {
                if (regulamentoUploadContainer) regulamentoUploadContainer.classList.remove('hidden');
                console.log('... Regulamento: Input de upload exibido (modo edição)');
            } else {
                if (regulamentoUploadContainer) regulamentoUploadContainer.classList.add('hidden');
            }
            if (confirmacaoSubstituir) confirmacaoSubstituir.classList.add('hidden');
        }
    } else {
        console.error('❌ Regulamento: Elementos HTML não encontrados!');
    }
    
    // Limpar input de arquivo
    if (inputRegulamentoArquivo) {
        inputRegulamentoArquivo.value = '';
    }

    // Local do Evento
    document.getElementById('localEvento').value = evento.local || '';
    document.getElementById('cepEvento').value = evento.cep || '';
    document.getElementById('urlMapa').value = evento.url_mapa || '';
    document.getElementById('logradouro').value = evento.logradouro || '';
    document.getElementById('numero').value = evento.numero || '';
    document.getElementById('cidadeEvento').value = evento.cidade || '';
    document.getElementById('estadoEvento').value = evento.estado || '';
    document.getElementById('paisEvento').value = evento.pais || 'Brasil';

    // Período de Inscrições
    document.getElementById('limiteVagas').value = evento.limite_vagas || '';
    document.getElementById('dataFimInscricoes').value = evento.data_fim_inscricoes || '';
    document.getElementById('horaFimInscricoes').value = evento.hora_fim_inscricoes || '';

    // Taxas
    document.getElementById('taxaGratuitas').value = evento.taxa_gratuitas || '';
    document.getElementById('taxaPagas').value = evento.taxa_pagas || '';
    document.getElementById('taxaSetup').value = evento.taxa_setup || '';
    document.getElementById('percentualRepasse').value = evento.percentual_repasse || '';

    // Organizador
    document.getElementById('organizadorResponsavel').value = evento.organizador_nome || '';

    // Carregar imagem do evento usando padrão evento_{ID}
    carregarImagemEvento(evento.imagem, evento.id);

    console.log(' DEBUG - Formulário preenchido com sucesso');
    console.log(' DEBUG - Campos preenchidos:');
    console.log('  - Nome:', document.getElementById('nomeEvento').value);
    console.log('  - Categoria:', document.getElementById('categoriaEvento').value);
    console.log('  - Data Início:', document.getElementById('dataInicio').value);
    console.log('  - Hora Início:', document.getElementById('horaInicio').value);
    console.log('  - Local:', document.getElementById('localEvento').value);
    console.log('  - Cidade:', document.getElementById('cidadeEvento').value);
    console.log('  - Estado:', document.getElementById('estadoEvento').value);
    console.log('  - Limite Vagas:', document.getElementById('limiteVagas').value);
    console.log('  - Taxa Setup:', document.getElementById('taxaSetup').value);
    console.log('  - Imagem:', evento.imagem);
    console.log('  - Organizador:', document.getElementById('organizadorResponsavel').value);

    // Campos adicionais que existem na tabela mas não no formulário
    console.log('Dados adicionais do evento:', {
        genero: evento.genero,
        descricao: evento.descricao,
        data_fim: evento.data_fim,
        regulamento: evento.regulamento,
        status: evento.status,
        exibir_retirada_kit: evento.exibir_retirada_kit,
        data_realizacao: evento.data_realizacao,
        imagem: evento.imagem,
        data_criacao: evento.data_criacao
    });
}

// Limpar formulário quando nenhum evento estiver selecionado
function limparFormulario() {
    // Informações Gerais
    document.getElementById('nomeEvento').value = '';
    document.getElementById('categoriaEvento').value = '';
    document.getElementById('dataInicio').value = '';
    document.getElementById('horaInicio').value = '';
    document.getElementById('generoEvento').value = '';
    document.getElementById('dataRealizacao').value = '';
    document.getElementById('descricaoEvento').value = '';
    
    // Limpar regulamento
    document.getElementById('regulamento-arquivo-existente').classList.add('hidden');
    document.getElementById('regulamento-upload-container').classList.add('hidden');
    document.getElementById('regulamento-confirmacao-substituir').classList.add('hidden');
    const inputRegulamentoArquivo = document.getElementById('regulamentoArquivo');
    if (inputRegulamentoArquivo) {
        inputRegulamentoArquivo.value = '';
    }

    // Local do Evento
    document.getElementById('localEvento').value = '';
    document.getElementById('cepEvento').value = '';
    document.getElementById('urlMapa').value = '';
    document.getElementById('logradouro').value = '';
    document.getElementById('numero').value = '';
    document.getElementById('cidadeEvento').value = '';
    document.getElementById('estadoEvento').value = '';
    document.getElementById('paisEvento').value = 'Brasil';

    // Período de Inscrições
    document.getElementById('limiteVagas').value = '';
    document.getElementById('dataFimInscricoes').value = '';
    document.getElementById('horaFimInscricoes').value = '';

    // Taxas
    document.getElementById('taxaGratuitas').value = '';
    document.getElementById('taxaPagas').value = '';
    document.getElementById('taxaSetup').value = '';
    document.getElementById('percentualRepasse').value = '';

    // Organizador
    document.getElementById('organizadorResponsavel').value = '';

    // Limpar modalidades
    const container = document.getElementById('modalidades-container');
    const empty = document.getElementById('modalidades-empty');
    if (container) container.innerHTML = '';
    if (empty) empty.classList.remove('hidden');

    // Limpar programação
    mostrarProgramacaoEmpty();

    // Limpar imagem
    limparImagemEvento();

    // Limpar dados do evento atual
    eventoAtual = null;
}

// =====================================================
// FUNÇÕES PARA INTERFACE DINÂMICA DE MODALIDADES
// =====================================================

// Mostrar loading das modalidades
function mostrarLoadingModalidades() {
    const container = document.getElementById('modalidades-container');
    const loading = document.getElementById('modalidades-loading');
    const empty = document.getElementById('modalidades-empty');

    // Mostrar loading e esconder empty
    if (loading) loading.classList.remove('hidden');
    if (empty) empty.classList.add('hidden');
}

// Mostrar erro nas modalidades
function mostrarErroModalidades(mensagem) {
    const container = document.getElementById('modalidades-container');
    container.innerHTML = `
        <div class="text-center py-8">
            <div class="text-red-500">
                <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                <p>${mensagem}</p>
            </div>
        </div>
    `;
}

// Renderizar modalidades dinamicamente
function renderizarModalidadesDinamicas(modalidades, lotes) {
    const container = document.getElementById('modalidades-container');
    const empty = document.getElementById('modalidades-empty');

    if (!modalidades || modalidades.length === 0) {
        container.innerHTML = '';
        empty.classList.remove('hidden');
        return;
    }

    empty.classList.add('hidden');
    container.innerHTML = '';

    modalidades.forEach(modalidade => {
        // Buscar lotes desta modalidade
        const lotesModalidade = lotes.filter(lote => lote.modalidade_id === modalidade.id);

        // Criar card da modalidade
        const card = criarCardModalidade(modalidade, lotesModalidade);
        container.appendChild(card);
    });
}

// Criar card de modalidade
function criarCardModalidade(modalidade, lotes) {
    const card = document.createElement('div');
    card.className = 'modalidade-card bg-white rounded-lg shadow border border-gray-200 p-4 mb-4';
    card.innerHTML = `
        <div class="flex justify-between items-start mb-3">
            <div class="flex-1">
                <h4 class="font-semibold text-lg text-gray-900">${modalidade.nome}</h4>
                <div class="flex items-center space-x-4 text-sm text-gray-600 mt-1">
                    <span class="flex items-center">
                        <i class="fas fa-route mr-1"></i>
                        ${modalidade.distancia}
                    </span>
                    <span class="flex items-center">
                        <i class="fas fa-running mr-1"></i>
                        ${modalidade.tipo_prova}
                    </span>
                    <span class="flex items-center">
                        <i class="fas fa-tag mr-1"></i>
                        ${modalidade.categoria_nome}
                    </span>
                </div>
                ${modalidade.descricao ? `<p class="text-sm text-gray-500 mt-2">${modalidade.descricao}</p>` : ''}
            </div>
            <div class="flex space-x-2 ml-4">
                <button onclick="editarModalidade(${modalidade.id})" 
                        class="text-blue-600 hover:text-blue-800 p-2 rounded-lg hover:bg-blue-50" 
                        title="Editar modalidade">
                    <i class="fas fa-edit"></i>
                </button>
                <button onclick="adicionarLote(${modalidade.id})" 
                        class="text-green-600 hover:text-green-800 p-2 rounded-lg hover:bg-green-50" 
                        title="Adicionar lote">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        
        <div class="lotes-container">
            ${lotes.length > 0 ? lotes.map(lote => criarLoteItem(lote)).join('') : 
                '<div class="text-center py-4 text-gray-500"><i class="fas fa-info-circle mr-2"></i>Nenhum lote cadastrado</div>'}
        </div>
    `;

    return card;
}

// Criar item de lote
function criarLoteItem(lote) {
    const statusClass = lote.status === 'ativo' ? 'bg-green-50 border-green-200' :
        lote.status === 'futuro' ? 'bg-yellow-50 border-yellow-200' :
        lote.status === 'expirado' ? 'bg-red-50 border-red-200' : 'bg-gray-50 border-gray-200';

    const statusIcon = lote.status === 'ativo' ? 'fas fa-check-circle text-green-600' :
        lote.status === 'futuro' ? 'fas fa-clock text-yellow-600' :
        lote.status === 'expirado' ? 'fas fa-times-circle text-red-600' : 'fas fa-pause-circle text-gray-600';

    return `
        <div class="lote-item ${statusClass} border rounded-lg p-3 mb-2">
            <div class="flex justify-between items-center">
                <div class="flex-1">
                    <div class="flex items-center space-x-3">
                        <span class="font-medium text-gray-900">Lote ${lote.numero_lote}</span>
                        <span class="text-lg font-bold text-green-600">${lote.preco_formatado}</span>
                        <span class="text-xs px-2 py-1 rounded-full ${statusClass}">
                            <i class="${statusIcon} mr-1"></i>
                            ${lote.status.charAt(0).toUpperCase() + lote.status.slice(1)}
                        </span>
                    </div>
                    <div class="text-sm text-gray-600 mt-1">
                        <span class="mr-4">
                            <i class="fas fa-calendar mr-1"></i>
                            ${lote.data_inicio_formatada} - ${lote.data_fim_formatada}
                        </span>
                        ${lote.vagas_disponiveis ? `<span><i class="fas fa-users mr-1"></i>${lote.vagas_disponiveis} vagas</span>` : ''}
                    </div>
                </div>
                <div class="flex space-x-1 ml-4">
                    <button onclick="editarLote(${lote.id})" 
                            class="text-blue-600 hover:text-blue-800 p-1 rounded hover:bg-blue-100" 
                            title="Editar lote">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="excluirLote(${lote.id})" 
                            class="text-red-600 hover:text-red-800 p-1 rounded hover:bg-red-100" 
                            title="Excluir lote">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
}

// Alternar modo view/edit
window.alternarModo = function () {
    modoEdicao = !modoEdicao;
    const textoModo = document.getElementById('textoModo');
    const btnAlternarModo = document.getElementById('btnAlternarModo');
    const btnSalvar = document.getElementById('btnSalvar');
    const btnCancelar = document.getElementById('btnCancelar');
    const btnSalvarForm = document.getElementById('btnSalvarForm');

    if (modoEdicao) {
        // Modo edição
        if (textoModo) textoModo.textContent = 'Visualizar';
        if (btnAlternarModo) btnAlternarModo.innerHTML = '<i class="fas fa-eye mr-2"></i><span id="textoModo">Visualizar</span>';
        if (btnAlternarModo) {
            btnAlternarModo.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            btnAlternarModo.classList.add('bg-yellow-600', 'hover:bg-yellow-700');
        }
        if (btnSalvar) btnSalvar.classList.remove('hidden');
        if (btnCancelar) btnCancelar.classList.remove('hidden');
        if (btnSalvarForm) btnSalvarForm.classList.remove('hidden');
        
        // Mostrar botão adicionar programação
        const btnAdicionarProgramacao = document.getElementById('btnAdicionarProgramacao');
        if (btnAdicionarProgramacao) btnAdicionarProgramacao.classList.remove('hidden');
        
        habilitarCampos(true);
        
        // Atualizar visibilidade do regulamento em modo edição
        atualizarVisibilidadeRegulamento();
    } else {
        // Modo visualização
        if (textoModo) textoModo.textContent = 'Editar';
        if (btnAlternarModo) btnAlternarModo.innerHTML = '<i class="fas fa-edit mr-2"></i><span id="textoModo">Editar</span>';
        if (btnAlternarModo) {
            btnAlternarModo.classList.remove('bg-yellow-600', 'hover:bg-yellow-700');
            btnAlternarModo.classList.add('bg-blue-600', 'hover:bg-blue-700');
        }
        if (btnSalvar) btnSalvar.classList.add('hidden');
        if (btnCancelar) btnCancelar.classList.add('hidden');
        if (btnSalvarForm) btnSalvarForm.classList.add('hidden');
        
        // Esconder botão adicionar programação
        const btnAdicionarProgramacao = document.getElementById('btnAdicionarProgramacao');
        if (btnAdicionarProgramacao) btnAdicionarProgramacao.classList.add('hidden');
        
        habilitarCampos(false);
        
        // Atualizar visibilidade do regulamento em modo visualização
        atualizarVisibilidadeRegulamento();
        
        // Restaurar dados originais
        if (eventoAtual) {
            preencherFormulario(eventoAtual);
        }
    }
}

// Função auxiliar para atualizar visibilidade do regulamento
function atualizarVisibilidadeRegulamento() {
    const regulamentoArquivoExistente = document.getElementById('regulamento-arquivo-existente');
    const regulamentoUploadContainer = document.getElementById('regulamento-upload-container');
    const btnSubstituirRegulamento = document.getElementById('btn-substituir-regulamento');
    
    if (!eventoAtual) return;
    
    if (eventoAtual.regulamento_arquivo && eventoAtual.regulamento_arquivo.trim() !== '') {
        // Existe arquivo
        if (regulamentoArquivoExistente) {
            regulamentoArquivoExistente.classList.remove('hidden');
        }
        if (regulamentoUploadContainer) {
            regulamentoUploadContainer.classList.add('hidden');
        }
        if (btnSubstituirRegulamento) {
            if (modoEdicao) {
                btnSubstituirRegulamento.classList.remove('hidden');
            } else {
                btnSubstituirRegulamento.classList.add('hidden');
            }
        }
    } else {
        // Não existe arquivo
        if (regulamentoArquivoExistente) {
            regulamentoArquivoExistente.classList.add('hidden');
        }
        if (btnSubstituirRegulamento) {
            btnSubstituirRegulamento.classList.add('hidden');
        }
        if (regulamentoUploadContainer) {
            if (modoEdicao) {
                regulamentoUploadContainer.classList.remove('hidden');
            } else {
                regulamentoUploadContainer.classList.add('hidden');
            }
        }
    }
}

// Habilitar/desabilitar campos do formulário
function habilitarCampos(habilitar) {
    const campos = [
        'nomeEvento', 'categoriaEvento', 'dataInicio', 'horaInicio',
        'generoEvento', 'dataRealizacao', 'descricaoEvento',
        'localEvento', 'cepEvento', 'urlMapa', 'logradouro', 'numero',
        'cidadeEvento', 'estadoEvento', 'paisEvento', 'limiteVagas',
        'dataFimInscricoes', 'horaFimInscricoes', 'taxaGratuitas',
        'taxaPagas', 'taxaSetup', 'percentualRepasse',
        'organizadorResponsavel'
    ];
    campos.forEach(campoId => {
        const campo = document.getElementById(campoId);
        if (campo) campo.disabled = !habilitar;
    });
    // Radio buttons
    const vagasIlimitado = document.getElementById('vagasIlimitado');
    const vagasLimitado = document.getElementById('vagasLimitado');
    if (vagasIlimitado) vagasIlimitado.disabled = !habilitar;
    if (vagasLimitado) vagasLimitado.disabled = !habilitar;
    
    // Regulamento: mostrar/esconder input e botão substituir
    const regulamentoUploadContainer = document.getElementById('regulamento-upload-container');
    const btnSubstituirRegulamento = document.getElementById('btn-substituir-regulamento');
    const regulamentoArquivoExistente = document.getElementById('regulamento-arquivo-existente');
    
    if (habilitar) {
        // Modo edição: mostrar botão substituir se existe arquivo
        if (regulamentoArquivoExistente && !regulamentoArquivoExistente.classList.contains('hidden')) {
            if (btnSubstituirRegulamento) btnSubstituirRegulamento.classList.remove('hidden');
        } else {
            // Não existe arquivo: mostrar input
            if (regulamentoUploadContainer) regulamentoUploadContainer.classList.remove('hidden');
        }
    } else {
        // Modo visualização: esconder input e botão substituir
        if (regulamentoUploadContainer) regulamentoUploadContainer.classList.add('hidden');
        if (btnSubstituirRegulamento) btnSubstituirRegulamento.classList.add('hidden');
        
        // Se não existe arquivo, esconder também o container de link
        if (regulamentoArquivoExistente && !eventoAtual?.regulamento_arquivo) {
            regulamentoArquivoExistente.classList.add('hidden');
        }
    }
}

// Cancelar edição
window.cancelarEdicao = function () {
    modoEdicao = false;
    alternarModo();
}

/**
 * Salvar evento - ATUALIZA TABELA 'eventos'
 * 
 * Esta função é CORE da página e deve SEMPRE:
 * 1. Coletar dados do formulário
 * 2. Atualizar a tabela 'eventos' via API eventos/update.php
 * 3. Salvar regulamento se houver arquivo
 * 
 * MANTIDA E PRESERVADA - Funcionalidade original
 * A programação (tabela 'programacao_evento') é gerenciada separadamente
 * através das funções adicionarItemProgramacao, editarItemProgramacao, etc.
 */
window.salvarEvento = async function () {
    if (!eventoAtual) {
        Swal.fire('Erro', 'Nenhum evento selecionado', 'error');
        return;
    }
    try {
        // Coletar dados do formulário (tabela 'eventos')
        const dados = coletarDadosFormulario();
        
        // ATUALIZAR TABELA 'eventos' (FUNCIONALIDADE ORIGINAL)
        const response = await fetch(API_BASE + 'update.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(dados)
        });
        const data = await response.json();

        if (data.success) {
            // Salvar regulamento se houver arquivo selecionado
            await salvarRegulamento();

            Swal.fire('Sucesso', 'Evento atualizado com sucesso!', 'success');
            eventoAtual = data.data ? data.data : dados; // Atualiza com o que vier da API
            alternarModo();
        } else {
            Swal.fire('Erro', data.message || 'Erro ao atualizar evento', 'error');
        }
    } catch (error) {
        console.error('Erro ao salvar evento:', error);
        Swal.fire('Erro', 'Erro ao salvar evento', 'error');
    }
}

// Coletar dados do formulário
function coletarDadosFormulario() {
    const getVal = (id) => {
        const el = document.getElementById(id);
        return el ? el.value : '';
    };
    return {
        id: eventoAtual.id,
        nome: getVal('nomeEvento'),
        categoria: getVal('categoriaEvento'),
        data_inicio: getVal('dataInicio'),
        hora_inicio: getVal('horaInicio'),
        local: getVal('localEvento'),
        cep: getVal('cepEvento'),
        url_mapa: getVal('urlMapa'),
        logradouro: getVal('logradouro'),
        numero: getVal('numero'),
        cidade: getVal('cidadeEvento'),
        estado: getVal('estadoEvento'),
        pais: getVal('paisEvento'),
        limite_vagas: getVal('limiteVagas'),
        data_fim: getVal('dataFimInscricoes'),
        hora_fim_inscricoes: getVal('horaFimInscricoes'),
        taxa_gratuitas: getVal('taxaGratuitas'),
        taxa_pagas: getVal('taxaPagas'),
        taxa_setup: getVal('taxaSetup'),
        percentual_repasse: getVal('percentualRepasse'),
        exibir_retirada_kit: 0,
    };
}

// Atualizar valores das modalidades no banco
async function atualizarValoresModalidades(eventoId) {
    if (!eventoAtual.modalidades) return;

    try {
        for (const modalidade of eventoAtual.modalidades) {
            const distancia = modalidade.distancia;
            let novoValor = 0;

            // Determinar novo valor baseado na distância
            if (distancia.includes('5km') || distancia.includes('5KM')) {
                novoValor = parseFloat(document.getElementById('valor5km').value) || 0;
            } else if (distancia.includes('10km') || distancia.includes('10KM')) {
                novoValor = parseFloat(document.getElementById('valor10km').value) || 0;
            } else if (distancia.toLowerCase().includes('caminhada')) {
                novoValor = parseFloat(document.getElementById('valorCaminhada').value) || 0;
            } else if (distancia.toLowerCase().includes('kids')) {
                novoValor = parseFloat(document.getElementById('valorKids').value) || 0;
            }

            // Atualizar modalidade se valor mudou
            if (novoValor > 0 && novoValor !== (modalidade.valor || modalidade.preco || 0)) {
                await fetch(`${window.API_BASE || '/api'}/organizador/modalidades/update.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id: modalidade.id,
                        valor: novoValor
                    })
                });
            }
        }
    } catch (error) {
        console.error('Erro ao atualizar valores das modalidades:', error);
    }
}

// =====================================================
// FUNÇÕES PARA CRUD DE LOTES
// =====================================================

// Adicionar novo lote
async function adicionarLote(modalidadeId) {
    if (!modoEdicao) {
        Swal.fire({
            title: 'Modo Visualização',
            text: 'Ative o modo de edição para adicionar lotes',
            icon: 'info'
        });
        return;
    }

    const {
        value: formValues
    } = await Swal.fire({
        title: 'Novo Lote',
        html: `
            <div class="text-left">
                <label class="block text-sm font-medium text-gray-700 mb-1">Número do Lote</label>
                <input id="numero-lote" type="number" class="w-full border border-gray-300 rounded-lg px-3 py-2 mb-3" placeholder="Ex: 1">
                
                <label class="block text-sm font-medium text-gray-700 mb-1">Preço (R$)</label>
                <input id="preco-lote" type="number" step="0.01" class="w-full border border-gray-300 rounded-lg px-3 py-2 mb-3" placeholder="0,00">
                
                <label class="block text-sm font-medium text-gray-700 mb-1">Data Início</label>
                <input id="data-inicio" type="date" class="w-full border border-gray-300 rounded-lg px-3 py-2 mb-3">
                
                <label class="block text-sm font-medium text-gray-700 mb-1">Data Fim</label>
                <input id="data-fim" type="date" class="w-full border border-gray-300 rounded-lg px-3 py-2 mb-3">
                
                <label class="block text-sm font-medium text-gray-700 mb-1">Vagas Disponíveis</label>
                <input id="vagas" type="number" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Opcional">
            </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Criar Lote',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const numeroLote = document.getElementById('numero-lote').value;
            const preco = document.getElementById('preco-lote').value;
            const dataInicio = document.getElementById('data-inicio').value;
            const dataFim = document.getElementById('data-fim').value;
            const vagas = document.getElementById('vagas').value;

            if (!numeroLote || !preco || !dataInicio || !dataFim) {
                Swal.showValidationMessage('Preencha todos os campos obrigatórios');
                return false;
            }

            if (!eventoAtual || !eventoAtual.id) {
                Swal.showValidationMessage('Selecione um evento primeiro');
                return false;
            }

            return {
                evento_id: eventoAtual.id,
                modalidades: [modalidadeId],
                modalidade_id: modalidadeId,
                numero_lote: numeroLote,
                preco: preco,
                data_inicio: dataInicio,
                data_fim: dataFim,
                vagas_disponiveis: vagas || null
            };
        }
    });

    if (formValues) {
        await criarLote(formValues);
    }
}

// Criar lote via API
async function criarLote(dados) {
    try {
        const response = await fetch((window.API_BASE || '/api') + '/organizador/lotes-inscricao/create.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(dados)
        });

        const data = await response.json();

        if (data.success) {
            Swal.fire({
                title: 'Sucesso!',
                text: 'Lote criado com sucesso!',
                icon: 'success'
            });

            // Recarregar modalidades
            if (eventoAtual.id) {
                await carregarModalidades(eventoAtual.id);
            }
        } else {
            Swal.fire({
                title: 'Erro!',
                text: data.error || 'Erro ao criar lote',
                icon: 'error'
            });
        }
    } catch (error) {
        console.error('Erro ao criar lote:', error);
        Swal.fire({
            title: 'Erro!',
            text: 'Erro ao criar lote',
            icon: 'error'
        });
    }
}

// Editar lote
async function editarLote(loteId) {
    if (!modoEdicao) {
        Swal.fire({
            title: 'Modo Visualização',
            text: 'Ative o modo de edição para editar lotes',
            icon: 'info'
        });
        return;
    }

    // Buscar dados do lote
    const lote = eventoAtual.lotes.find(l => l.id === loteId);
    if (!lote) {
        Swal.fire({
            title: 'Erro!',
            text: 'Lote não encontrado',
            icon: 'error'
        });
        return;
    }

    const {
        value: formValues
    } = await Swal.fire({
        title: 'Editar Lote',
        html: `
            <div class="text-left">
                <label class="block text-sm font-medium text-gray-700 mb-1">Número do Lote</label>
                <input id="numero-lote" type="number" class="w-full border border-gray-300 rounded-lg px-3 py-2 mb-3" value="${lote.numero_lote}">
                
                <label class="block text-sm font-medium text-gray-700 mb-1">Preço (R$)</label>
                <input id="preco-lote" type="number" step="0.01" class="w-full border border-gray-300 rounded-lg px-3 py-2 mb-3" value="${lote.preco}">
                
                <label class="block text-sm font-medium text-gray-700 mb-1">Data Início</label>
                <input id="data-inicio" type="date" class="w-full border border-gray-300 rounded-lg px-3 py-2 mb-3" value="${lote.data_inicio}">
                
                <label class="block text-sm font-medium text-gray-700 mb-1">Data Fim</label>
                <input id="data-fim" type="date" class="w-full border border-gray-300 rounded-lg px-3 py-2 mb-3" value="${lote.data_fim}">
                
                <label class="block text-sm font-medium text-gray-700 mb-1">Vagas Disponíveis</label>
                <input id="vagas" type="number" class="w-full border border-gray-300 rounded-lg px-3 py-2" value="${lote.vagas_disponiveis || ''}">
            </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Atualizar Lote',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const numeroLote = document.getElementById('numero-lote').value;
            const preco = document.getElementById('preco-lote').value;
            const dataInicio = document.getElementById('data-inicio').value;
            const dataFim = document.getElementById('data-fim').value;
            const vagas = document.getElementById('vagas').value;

            if (!numeroLote || !preco || !dataInicio || !dataFim) {
                Swal.showValidationMessage('Preencha todos os campos obrigatórios');
                return false;
            }

            if (!eventoAtual || !eventoAtual.id) {
                Swal.showValidationMessage('Selecione um evento primeiro');
                return false;
            }

            const modalidadesLote = Array.from(new Set(
                (eventoAtual.lotes || [])
                    .filter(l => l.numero_lote === lote.numero_lote)
                    .map(l => l.modalidade_id)
                    .filter(Boolean)
            ));

            return {
                id: loteId,
                evento_id: eventoAtual.id,
                modalidades: modalidadesLote.length ? modalidadesLote : [lote.modalidade_id],
                numero_lote: numeroLote,
                preco: preco,
                data_inicio: dataInicio,
                data_fim: dataFim,
                vagas_disponiveis: vagas || null
            };
        }
    });

    if (formValues) {
        await atualizarLote(formValues);
    }
}

// Atualizar lote via API
async function atualizarLote(dados) {
    try {
        const response = await fetch((window.API_BASE || '/api') + '/organizador/lotes-inscricao/update.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(dados)
        });

        const data = await response.json();

        if (data.success) {
            Swal.fire({
                title: 'Sucesso!',
                text: 'Lote atualizado com sucesso!',
                icon: 'success'
            });

            // Recarregar modalidades
            if (eventoAtual.id) {
                await carregarModalidades(eventoAtual.id);
            }
        } else {
            Swal.fire({
                title: 'Erro!',
                text: data.error || 'Erro ao atualizar lote',
                icon: 'error'
            });
        }
    } catch (error) {
        console.error('Erro ao atualizar lote:', error);
        Swal.fire({
            title: 'Erro!',
            text: 'Erro ao atualizar lote',
            icon: 'error'
        });
    }
}

// Excluir lote
async function excluirLote(loteId) {
    if (!modoEdicao) {
        Swal.fire({
            title: 'Modo Visualização',
            text: 'Ative o modo de edição para excluir lotes',
            icon: 'info'
        });
        return;
    }

    const result = await Swal.fire({
        title: 'Confirmar exclusão',
        text: 'Tem certeza que deseja excluir este lote?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
        try {
            const response = await fetch(`${window.API_BASE || '/api'}/organizador/lotes-inscricao/delete.php?id=${loteId}`, {
                method: 'DELETE'
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    title: 'Sucesso!',
                    text: 'Lote excluído com sucesso!',
                    icon: 'success'
                });

                // Recarregar modalidades
                if (eventoAtual.id) {
                    await carregarModalidades(eventoAtual.id);
                }
            } else {
                Swal.fire({
                    title: 'Erro!',
                    text: data.error || 'Erro ao excluir lote',
                    icon: 'error'
                });
            }
        } catch (error) {
            console.error('Erro ao excluir lote:', error);
            Swal.fire({
                title: 'Erro!',
                text: 'Erro ao excluir lote',
                icon: 'error'
            });
        }
    }
}

// Editar modalidade (placeholder - pode ser implementado futuramente)
function editarModalidade(modalidadeId) {
    Swal.fire({
        title: 'Em desenvolvimento',
        text: 'A edição de modalidades será implementada em breve',
        icon: 'info'
    });
}

/**
 * Carrega a imagem do evento no formulário. Prioriza o valor do banco (nomeImagem); fallback evento_{ID}.png
 * Usa window.getEventImageUrl (eventImageUrl.js) para URL única em todas as páginas.
 * @param {string} nomeImagem - Nome do arquivo (ex: evento_2.png)
 * @param {number} eventoId - ID do evento
 * @param {boolean} [forceReload=false] - Se true, adiciona query de cache-bust para forçar recarregar (ex: após upload)
 */
function carregarImagemEvento(nomeImagem, eventoId, forceReload) {
    const imagemPlaceholder = document.getElementById('imagem-placeholder');
    const imagemEvento = document.getElementById('imagem-evento');
    if (!imagemEvento || !imagemPlaceholder) return;

    var getUrl = typeof window.getEventImageUrl === 'function' ? window.getEventImageUrl : function (nome) {
        return (nome ? '../../assets/img/eventos/' + nome : 'https://placehold.co/640x360?text=Evento');
    };
    const nomePadrao = 'evento_' + eventoId + '.png';
    const urlPadrao = getUrl(nomePadrao);
    const urlDoBanco = (nomeImagem && nomeImagem.trim() !== '') ? getUrl(nomeImagem) : null;

    var urlPrimeira = urlDoBanco || urlPadrao;
    var urlFallback = urlDoBanco ? urlPadrao : null;
    if (forceReload) {
        var bust = '?t=' + Date.now();
        urlPrimeira = urlPrimeira + bust;
        if (urlFallback) urlFallback = urlFallback + bust;
    }

    imagemEvento.alt = `Imagem do evento: ${nomeImagem || nomePadrao}`;
    imagemEvento.src = urlPrimeira;
    imagemEvento.classList.remove('hidden');
    imagemPlaceholder.classList.add('hidden');

    imagemEvento.onerror = function () {
        if (urlFallback) {
            imagemEvento.src = urlFallback;
            imagemEvento.onerror = function () {
                imagemEvento.classList.add('hidden');
                imagemPlaceholder.classList.remove('hidden');
                imagemEvento.src = '';
            };
            return;
        }
        imagemEvento.classList.add('hidden');
        imagemPlaceholder.classList.remove('hidden');
        imagemEvento.src = '';
    };
}

/**
 * Limpa a imagem do evento
 */
function limparImagemEvento() {
    const imagemEvento = document.getElementById('imagem-evento');
    const imagemPlaceholder = document.getElementById('imagem-placeholder');

    imagemEvento.classList.add('hidden');
    imagemPlaceholder.classList.remove('hidden');
    imagemEvento.src = '';
}

/**
 * Substituir regulamento existente
 */
window.substituirRegulamento = function() {
    const regulamentoUploadContainer = document.getElementById('regulamento-upload-container');
    
    if (regulamentoUploadContainer) {
        regulamentoUploadContainer.classList.remove('hidden');
    }
    
    // Focar no input de arquivo
    const inputRegulamentoArquivo = document.getElementById('regulamentoArquivo');
    if (inputRegulamentoArquivo) {
        inputRegulamentoArquivo.focus();
        inputRegulamentoArquivo.click();
    }
}

/**
 * Salvar regulamento (upload de arquivo)
 */
async function salvarRegulamento() {
    if (!eventoAtual || !eventoAtual.id) {
        return; // Sem evento selecionado
    }
    
    const inputRegulamentoArquivo = document.getElementById('regulamentoArquivo');
    if (!inputRegulamentoArquivo || !inputRegulamentoArquivo.files || inputRegulamentoArquivo.files.length === 0) {
        return; // Nenhum arquivo selecionado
    }
    
    try {
        // Se já existe arquivo e o usuário selecionou outro, confirmar substituição
        if (eventoAtual?.regulamento_arquivo && eventoAtual.regulamento_arquivo.trim() !== '') {
            const res = await Swal.fire({
                title: 'Substituir regulamento?',
                text: 'Já existe um arquivo. Ao salvar, ele será substituído.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sim, substituir',
                cancelButtonText: 'Cancelar'
            });
            if (!res.isConfirmed) {
                inputRegulamentoArquivo.value = '';
                const confirmacaoSubstituir = document.getElementById('regulamento-confirmacao-substituir');
                if (confirmacaoSubstituir) confirmacaoSubstituir.classList.add('hidden');
                return;
            }
        }

        const formData = new FormData();
        formData.append('evento_id', eventoAtual.id);
        formData.append('regulamento_arquivo', inputRegulamentoArquivo.files[0]);
        
        const response = await fetch((window.API_BASE || '/api') + '/organizador/eventos/upload-regulamento.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            console.log('... Regulamento salvo com sucesso:', data.data);
            
            // Atualizar interface: mostrar link e esconder input
            const regulamentoArquivoExistente = document.getElementById('regulamento-arquivo-existente');
            const regulamentoUploadContainer = document.getElementById('regulamento-upload-container');
            const linkRegulamento = document.getElementById('link-regulamento');
            const nomeArquivoRegulamento = document.getElementById('nome-arquivo-regulamento');
            const confirmacaoSubstituir = document.getElementById('regulamento-confirmacao-substituir');
            const btnSubstituirRegulamento = document.getElementById('btn-substituir-regulamento');
            
            if (regulamentoArquivoExistente && linkRegulamento && nomeArquivoRegulamento) {
                nomeArquivoRegulamento.textContent = data.data.nome_original || data.data.arquivo;
                const nomeArquivo = (data.data.arquivo || '').split('/').pop() || (data.data.arquivo || '');
                linkRegulamento.href = `${window.API_BASE || '/api'}/uploads/regulamentos/download.php?file=${encodeURIComponent(nomeArquivo)}`;
                regulamentoArquivoExistente.classList.remove('hidden');
            }

            const regulamentoPlaceholder = document.getElementById('regulamento-placeholder');
            if (regulamentoPlaceholder) regulamentoPlaceholder.classList.add('hidden');
            
            if (regulamentoUploadContainer) {
                regulamentoUploadContainer.classList.add('hidden');
            }
            
            if (confirmacaoSubstituir) {
                confirmacaoSubstituir.classList.add('hidden');
            }
            
            if (btnSubstituirRegulamento) {
                btnSubstituirRegulamento.classList.remove('hidden');
            }
            
            // Limpar input
            inputRegulamentoArquivo.value = '';
            
            // Atualizar eventoAtual com novo regulamento_arquivo
            if (eventoAtual) {
                eventoAtual.regulamento_arquivo = data.data.arquivo;
            }
        } else {
            console.error('❌ Erro ao salvar regulamento:', data.error);
            Swal.fire('Erro', data.error || 'Erro ao salvar regulamento', 'error');
        }
    } catch (error) {
        console.error(' Erro ao salvar regulamento:', error);
        Swal.fire('Erro', 'Erro ao salvar regulamento', 'error');
    }
}

// =====================================================
// FUNÇÕES PARA GERENCIAR PROGRAMAAÇÃO
// =====================================================

// Carregar programação do evento
async function carregarProgramacao(eventoId) {
    // Proteção contra múltiplas chamadas simultâneas
    if (window.carregandoProgramacao) {
        return;
    }
    
    if (!eventoId) {
        mostrarProgramacaoEmpty();
        return;
    }

    window.carregandoProgramacao = true;

    try {
        mostrarLoadingProgramacao();
        const response = await fetch(`${API_PROGRAMACAO}list.php?evento_id=${eventoId}`);
        const data = await response.json();

        if (data.success) {
            const programacao = data.data || [];
            renderizarProgramacao(programacao);
        } else {
            mostrarErroProgramacao(data.error || 'Erro ao carregar programação');
        }
    } catch (error) {
        console.error('Erro ao carregar programação:', error);
        mostrarErroProgramacao('Erro ao carregar programação');
    } finally {
        window.carregandoProgramacao = false;
    }
}

// Mostrar loading
function mostrarLoadingProgramacao() {
    const container = document.getElementById('programacao-container');
    const empty = document.getElementById('programacao-empty');
    if (container) {
        container.innerHTML = '<div id="programacao-loading" class="text-center py-8"><div class="inline-flex items-center"><div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mr-2"></div><span class="text-gray-600">Carregando programação...</span></div></div>';
    }
    if (empty) empty.classList.add('hidden');
}

// Mostrar empty
function mostrarProgramacaoEmpty() {
    const container = document.getElementById('programacao-container');
    const empty = document.getElementById('programacao-empty');
    if (container) container.innerHTML = '';
    if (empty) empty.classList.remove('hidden');
}

// Mostrar erro
function mostrarErroProgramacao(mensagem) {
    const container = document.getElementById('programacao-container');
    if (container) {
        container.innerHTML = `<div class="text-center py-8 text-red-500"><i class="fas fa-exclamation-triangle text-2xl mb-2"></i><p>${mensagem}</p></div>`;
    }
}

// Renderizar lista de programação
function renderizarProgramacao(programacao) {
    const container = document.getElementById('programacao-container');
    const empty = document.getElementById('programacao-empty');
    
    if (!container) return;

    if (!programacao || programacao.length === 0) {
        container.innerHTML = '<div class="text-center py-8 text-gray-500"><i class="fas fa-info-circle text-2xl mb-2"></i><p>Nenhum item de programação cadastrado</p></div>';
        if (empty) empty.classList.add('hidden');
        return;
    }

    empty?.classList.add('hidden');
    container.innerHTML = '';

    programacao.forEach(item => {
        const card = criarCardProgramacao(item);
        container.appendChild(card);
    });
}

// Criar card de item de programação
function criarCardProgramacao(item) {
    const card = document.createElement('div');
    card.className = 'bg-white rounded-lg shadow border border-gray-200 p-4';
    
    const tipoLabels = {
        'percurso': 'Percurso',
        'horario_largada': 'Horário de Largada',
        'atividade_adicional': 'Atividade Adicional'
    };
    
    const tipoColors = {
        'percurso': 'bg-blue-100 text-blue-800',
        'horario_largada': 'bg-green-100 text-green-800',
        'atividade_adicional': 'bg-purple-100 text-purple-800'
    };

    card.innerHTML = `
        <div class="flex justify-between items-start">
            <div class="flex-1">
                <div class="flex items-center space-x-3 mb-2">
                    <span class="px-2 py-1 rounded text-xs font-medium ${tipoColors[item.tipo] || 'bg-gray-100 text-gray-800'}">
                        ${tipoLabels[item.tipo] || item.tipo}
                    </span>
                    <span class="text-sm text-gray-500">Ordem: ${item.ordem || 0}</span>
                    ${item.ativo == 1 ? '<span class="px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">Ativo</span>' : '<span class="px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">Inativo</span>'}
                </div>
                <h4 class="font-semibold text-lg text-gray-900 mb-1">${item.titulo || 'Sem título'}</h4>
                ${item.descricao ? `<p class="text-sm text-gray-600">${item.descricao}</p>` : ''}
            </div>
            <div class="flex space-x-2 ml-4">
                <button onclick="editarItemProgramacao(${item.id})" 
                        class="text-blue-600 hover:text-blue-800 p-2 rounded-lg hover:bg-blue-50" 
                        title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button onclick="excluirItemProgramacao(${item.id})" 
                        class="text-red-600 hover:text-red-800 p-2 rounded-lg hover:bg-red-50" 
                        title="Excluir">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;

    return card;
}

// Adicionar item de programação
window.adicionarItemProgramacao = async function() {
    // Proteção contra múltiplos cliques
    if (window.adicionandoProgramacao) {
        console.log('  Adicionar programação já em andamento, ignorando clique');
        return;
    }
    
    if (!eventoAtual || !eventoAtual.id) {
        Swal.fire('Erro', 'Selecione um evento primeiro', 'error');
        return;
    }

    if (!modoEdicao) {
        Swal.fire('Aviso', 'Ative o modo de edição para adicionar itens de programação', 'info');
        return;
    }

    // Desabilitar botão durante processamento
    const btn = document.getElementById('btnAdicionarProgramacao');
    if (btn) {
        btn.disabled = true;
        btn.classList.add('opacity-50', 'cursor-not-allowed');
    }

    // Marcar como processando
    window.adicionandoProgramacao = true;

    try {
        // Criar HTML dinâmico com fieldsets
        const htmlModal = criarHTMLModalProgramacao();
        
        const { value: formValues } = await Swal.fire({
            title: 'Novo Item de Programação',
            html: htmlModal,
            width: '600px',
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Criar',
            cancelButtonText: 'Cancelar',
            didOpen: () => {
                // Adicionar listener para mudança de tipo
                const selectTipo = document.getElementById('tipo-programacao');
                if (selectTipo) {
                    selectTipo.addEventListener('change', atualizarCamposPorTipo);
                    // Inicializar campos
                    atualizarCamposPorTipo();
                }
            },
            preConfirm: () => {
                return validarEColetarDadosProgramacao();
            }
        });

        if (formValues) {
            await criarItemProgramacao(formValues);
        }
    } catch (error) {
        console.error('Erro ao adicionar item de programação:', error);
        Swal.fire('Erro', 'Erro ao processar solicitação', 'error');
    } finally {
        // Liberar flag e reabilitar botão
        window.adicionandoProgramacao = false;
        const btn = document.getElementById('btnAdicionarProgramacao');
        if (btn) {
            btn.disabled = false;
            btn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }
}

// Criar HTML do modal com fieldsets por tipo
function criarHTMLModalProgramacao() {
    return `
        <div class="text-left">
            <!-- Campo Tipo (sempre visível) -->
            <fieldset class="mb-4 border border-gray-200 rounded-lg p-3">
                <legend class="text-sm font-semibold text-gray-700 px-2">Tipo de Programação *</legend>
                <select id="tipo-programacao" class="w-full border border-gray-300 rounded-lg px-3 py-2 mt-2">
                    <option value="">Selecione o tipo</option>
                    <option value="horario_largada">Horário de Largada</option>
                    <option value="percurso">Percurso</option>
                    <option value="atividade_adicional">Atividade Adicional</option>
                </select>
            </fieldset>

            <!-- Campos Gerais (sempre visíveis) -->
            <fieldset class="mb-4 border border-gray-200 rounded-lg p-3">
                <legend class="text-sm font-semibold text-gray-700 px-2">Informações Gerais</legend>
                <div class="mt-2 space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Título *</label>
                        <input id="titulo-programacao" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Ex: Largada 5km">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ordem</label>
                        <input id="ordem-programacao" type="number" class="w-full border border-gray-300 rounded-lg px-3 py-2" value="0" min="0">
                        <p class="text-xs text-gray-500 mt-1">Ordem de exibição (0 = primeiro)</p>
                    </div>
                </div>
            </fieldset>

            <!-- Fieldset: Horário de Largada -->
            <fieldset id="fieldset-horario-largada" class="mb-4 border border-gray-200 rounded-lg p-3 hidden">
                <legend class="text-sm font-semibold text-gray-700 px-2">Horário de Largada</legend>
                <div class="mt-2 space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hora de Início *</label>
                            <input id="hora-inicio-largada" type="time" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Hora de Fim</label>
                            <input id="hora-fim-largada" type="time" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Modalidade/Pelotão</label>
                        <input id="modalidade-largada" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Ex: Pelotão PCD 5km, Elite, Geral">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                        <textarea id="obs-largada" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Informações adicionais sobre o horário"></textarea>
                    </div>
                </div>
            </fieldset>

            <!-- Fieldset: Percurso -->
            <fieldset id="fieldset-percurso" class="mb-4 border border-gray-200 rounded-lg p-3 hidden">
                <legend class="text-sm font-semibold text-gray-700 px-2">Ponto do Percurso</legend>
                <div class="mt-2 space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Local/Endereço *</label>
                        <input id="local-percurso" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Ex: Av Darcy Vargas, Av Maceió">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Latitude (opcional)</label>
                            <input id="latitude-percurso" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="-3.1190275">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Longitude (opcional)</label>
                            <input id="longitude-percurso" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="-60.0217314">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descrição do Ponto</label>
                        <textarea id="desc-percurso" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Descrição do ponto do percurso"></textarea>
                    </div>
                </div>
            </fieldset>

            <!-- Fieldset: Atividade Adicional -->
            <fieldset id="fieldset-atividade" class="mb-4 border border-gray-200 rounded-lg p-3 hidden">
                <legend class="text-sm font-semibold text-gray-700 px-2">Atividade Adicional</legend>
                <div class="mt-2 space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Local da Atividade</label>
                        <input id="local-atividade" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Ex: Tenda de Educação Ambiental">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Horário de Início</label>
                            <input id="hora-inicio-atividade" type="time" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Horário de Fim</label>
                            <input id="hora-fim-atividade" type="time" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descrição Detalhada *</label>
                        <textarea id="desc-atividade" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Descreva detalhadamente a atividade adicional"></textarea>
                    </div>
                </div>
            </fieldset>
        </div>
    `;
}

// Atualizar campos visíveis baseado no tipo selecionado
function atualizarCamposPorTipo() {
    const tipo = document.getElementById('tipo-programacao')?.value || '';
    
    // Esconder todos os fieldsets específicos
    document.getElementById('fieldset-horario-largada')?.classList.add('hidden');
    document.getElementById('fieldset-percurso')?.classList.add('hidden');
    document.getElementById('fieldset-atividade')?.classList.add('hidden');
    
    // Mostrar fieldset correspondente
    if (tipo === 'horario_largada') {
        document.getElementById('fieldset-horario-largada')?.classList.remove('hidden');
    } else if (tipo === 'percurso') {
        document.getElementById('fieldset-percurso')?.classList.remove('hidden');
    } else if (tipo === 'atividade_adicional') {
        document.getElementById('fieldset-atividade')?.classList.remove('hidden');
    }
}

// Validar e coletar dados do formulário
function validarEColetarDadosProgramacao() {
    const tipo = document.getElementById('tipo-programacao')?.value || '';
    const titulo = document.getElementById('titulo-programacao')?.value || '';
    const ordem = parseInt(document.getElementById('ordem-programacao')?.value) || 0;
    
    // Validações básicas
    if (!tipo) {
        Swal.showValidationMessage('Selecione o tipo de programação');
        return false;
    }
    
    if (!titulo.trim()) {
        Swal.showValidationMessage('Preencha o título');
        return false;
    }
    
    // Coletar dados comuns
    const dados = {
        evento_id: eventoAtual.id,
        tipo: tipo,
        titulo: titulo,
        ordem: ordem
    };
    
    // Coletar dados específicos por tipo
    if (tipo === 'horario_largada') {
        const horaInicio = document.getElementById('hora-inicio-largada')?.value || '';
        const horaFim = document.getElementById('hora-fim-largada')?.value || '';
        const modalidade = document.getElementById('modalidade-largada')?.value || '';
        const obs = document.getElementById('obs-largada')?.value || '';
        
        if (!horaInicio) {
            Swal.showValidationMessage('Preencha a hora de início');
            return false;
        }
        
        // Salvar horários em campos específicos
        dados.hora_inicio = horaInicio;
        dados.hora_fim = horaFim || null;
        
        // Montar descrição com os dados (para compatibilidade e exibição)
        let descricao = '';
        if (modalidade) descricao += `${modalidade}`;
        if (horaInicio) descricao += `: ${horaInicio}`;
        if (horaFim) descricao += ` - ${horaFim}`;
        if (obs) descricao += `\n${obs}`;
        
        dados.descricao = descricao.trim();
        
    } else if (tipo === 'percurso') {
        const local = document.getElementById('local-percurso')?.value || '';
        const latitude = document.getElementById('latitude-percurso')?.value || '';
        const longitude = document.getElementById('longitude-percurso')?.value || '';
        const desc = document.getElementById('desc-percurso')?.value || '';
        
        if (!local.trim()) {
            Swal.showValidationMessage('Preencha o local/endereço do percurso');
            return false;
        }
        
        // Salvar local e coordenadas em campos específicos
        dados.local = local;
        dados.latitude = latitude ? parseFloat(latitude) : null;
        dados.longitude = longitude ? parseFloat(longitude) : null;
        
        // Montar descrição (para compatibilidade)
        let descricao = local;
        if (latitude && longitude) {
            descricao += `\nCoordenadas: ${latitude}, ${longitude}`;
        }
        if (desc) {
            descricao += `\n${desc}`;
        }
        
        dados.descricao = descricao.trim();
        
    } else if (tipo === 'atividade_adicional') {
        const local = document.getElementById('local-atividade')?.value || '';
        const horaInicio = document.getElementById('hora-inicio-atividade')?.value || '';
        const horaFim = document.getElementById('hora-fim-atividade')?.value || '';
        const desc = document.getElementById('desc-atividade')?.value || '';
        
        if (!desc.trim()) {
            Swal.showValidationMessage('Preencha a descrição detalhada da atividade');
            return false;
        }
        
        // Salvar horários e local em campos específicos
        dados.hora_inicio = horaInicio || null;
        dados.hora_fim = horaFim || null;
        dados.local = local || null;
        
        // Montar descrição (para compatibilidade)
        let descricao = desc;
        if (local) descricao = `Local: ${local}\n${descricao}`;
        if (horaInicio && horaFim) {
            descricao = `${descricao}\nHorário: ${horaInicio} - ${horaFim}`;
        } else if (horaInicio) {
            descricao = `${descricao}\nHorário: ${horaInicio}`;
        }
        
        dados.descricao = descricao.trim();
    }
    
    return dados;
}

// Criar item via API
async function criarItemProgramacao(dados) {
    try {
        const response = await fetch(`${API_PROGRAMACAO}create.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(dados)
        });

        const data = await response.json();

        if (data.success) {
            // Fechar o modal do Swal antes de mostrar sucesso
            await Swal.close();
            
            // Aguardar um pouco antes de recarregar para evitar conflitos
            setTimeout(async () => {
                await carregarProgramacao(eventoAtual.id);
                Swal.fire('Sucesso!', 'Item de programação criado com sucesso!', 'success');
            }, 300);
        } else {
            Swal.fire('Erro!', data.error || 'Erro ao criar item', 'error');
        }
    } catch (error) {
        console.error('Erro ao criar item:', error);
        Swal.fire('Erro!', 'Erro ao criar item de programação', 'error');
    }
}

// Editar item de programação
window.editarItemProgramacao = async function(itemId) {
    if (!modoEdicao) {
        Swal.fire('Aviso', 'Ative o modo de edição para editar itens', 'info');
        return;
    }

    // Buscar dados do item
    try {
        const response = await fetch(`${API_PROGRAMACAO}list.php?evento_id=${eventoAtual.id}`);
        const data = await response.json();
        
        if (!data.success) {
            Swal.fire('Erro', 'Erro ao carregar dados do item', 'error');
            return;
        }

        const item = data.data.find(i => i.id == itemId);
        if (!item) {
            Swal.fire('Erro', 'Item não encontrado', 'error');
            return;
        }

        // Criar HTML do modal de edição
        const htmlModal = criarHTMLModalProgramacao() + `
            <fieldset class="mb-4 border border-gray-200 rounded-lg p-3">
                <legend class="text-sm font-semibold text-gray-700 px-2">Status</legend>
                <div class="mt-2">
                    <select id="ativo-programacao" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="1" ${item.ativo == 1 ? 'selected' : ''}>Ativo</option>
                        <option value="0" ${item.ativo == 0 ? 'selected' : ''}>Inativo</option>
                    </select>
                </div>
            </fieldset>
        `;
        
        const { value: formValues } = await Swal.fire({
            title: 'Editar Item de Programação',
            html: htmlModal,
            width: '600px',
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Atualizar',
            cancelButtonText: 'Cancelar',
            didOpen: () => {
                // Preencher campos com dados do item
                preencherCamposEdicao(item);
                // Adicionar listener para mudança de tipo
                const selectTipo = document.getElementById('tipo-programacao');
                if (selectTipo) {
                    selectTipo.addEventListener('change', atualizarCamposPorTipo);
                    atualizarCamposPorTipo();
                }
            },
            preConfirm: () => {
                const dados = validarEColetarDadosProgramacao();
                if (!dados) return false;
                
                // Adicionar ID e ativo
                dados.id = itemId;
                dados.ativo = parseInt(document.getElementById('ativo-programacao')?.value || '1');
                
                return dados;
            }
        });

        if (formValues) {
            await atualizarItemProgramacao(formValues);
        }
    } catch (error) {
        console.error('Erro ao editar item:', error);
        Swal.fire('Erro', 'Erro ao carregar dados do item', 'error');
    }
}

// Preencher campos do formulário de edição com dados do item
function preencherCamposEdicao(item) {
    // Campos gerais
    const tipoSelect = document.getElementById('tipo-programacao');
    const tituloInput = document.getElementById('titulo-programacao');
    const ordemInput = document.getElementById('ordem-programacao');
    
    if (tipoSelect) tipoSelect.value = item.tipo || '';
    if (tituloInput) tituloInput.value = item.titulo || '';
    if (ordemInput) ordemInput.value = item.ordem || 0;
    
    // Parsear descrição baseado no tipo
    const descricao = item.descricao || '';
    
    if (item.tipo === 'horario_largada') {
        // Usar campos específicos se disponíveis (prioridade)
        const horaInicioInput = document.getElementById('hora-inicio-largada');
        const horaFimInput = document.getElementById('hora-fim-largada');
        
        if (horaInicioInput && item.hora_inicio) {
            // Converter TIME para formato HH:MM se necessário
            horaInicioInput.value = item.hora_inicio.substring(0, 5);
        }
        if (horaFimInput && item.hora_fim) {
            horaFimInput.value = item.hora_fim.substring(0, 5);
        }
        
        // Fallback: tentar extrair da descrição se campos não estiverem disponíveis
        if (!item.hora_inicio && descricao) {
            const match = descricao.match(/^([^:]+)?:?\s*(\d{2}:\d{2})(?:\s*-\s*(\d{2}:\d{2}))?/);
            if (match) {
                const modalidadeInput = document.getElementById('modalidade-largada');
                if (modalidadeInput && match[1]) modalidadeInput.value = match[1].trim();
                if (horaInicioInput && match[2]) horaInicioInput.value = match[2];
                if (horaFimInput && match[3]) horaFimInput.value = match[3];
            }
        } else {
            // Extrair modalidade da descrição
            const modalidadeInput = document.getElementById('modalidade-largada');
            if (modalidadeInput && descricao) {
                const match = descricao.match(/^([^:]+)/);
                if (match) modalidadeInput.value = match[1].trim();
            }
        }
        
        const obs = descricao.split('\n').slice(1).join('\n').trim();
        const obsInput = document.getElementById('obs-largada');
        if (obsInput && obs) obsInput.value = obs;
        
    } else if (item.tipo === 'percurso') {
        // Usar campos específicos se disponíveis (prioridade)
        const localInput = document.getElementById('local-percurso');
        const latInput = document.getElementById('latitude-percurso');
        const lngInput = document.getElementById('longitude-percurso');
        
        if (localInput && item.local) {
            localInput.value = item.local;
        } else if (localInput && descricao) {
            // Fallback: primeira linha é o local
            const linhas = descricao.split('\n');
            if (linhas[0]) localInput.value = linhas[0].trim();
        }
        
        if (latInput && item.latitude) {
            latInput.value = item.latitude;
        }
        if (lngInput && item.longitude) {
            lngInput.value = item.longitude;
        }
        
        // Fallback: procurar coordenadas na descrição
        if ((!item.latitude || !item.longitude) && descricao) {
            const coordMatch = descricao.match(/Coordenadas:\s*([^,\n]+),\s*([^\n]+)/);
            if (coordMatch) {
                if (latInput && !item.latitude) latInput.value = coordMatch[1].trim();
                if (lngInput && !item.longitude) lngInput.value = coordMatch[2].trim();
            }
        }
        
        // Descrição do ponto
        const descInput = document.getElementById('desc-percurso');
        if (descInput && descricao) {
            const linhas = descricao.split('\n');
            const desc = linhas.filter((l, i) => i > 0 && !l.includes('Coordenadas:')).join('\n').trim();
            if (desc) descInput.value = desc;
        }
        
    } else if (item.tipo === 'atividade_adicional') {
        // Usar campos específicos se disponíveis (prioridade)
        const localInput = document.getElementById('local-atividade');
        const horaInicioInput = document.getElementById('hora-inicio-atividade');
        const horaFimInput = document.getElementById('hora-fim-atividade');
        const descInput = document.getElementById('desc-atividade');
        
        if (localInput && item.local) {
            localInput.value = item.local;
        } else if (localInput && descricao) {
            // Fallback: parsear da descrição
            const localMatch = descricao.match(/Local:\s*([^\n]+)/);
            if (localMatch) localInput.value = localMatch[1].trim();
        }
        
        if (horaInicioInput && item.hora_inicio) {
            horaInicioInput.value = item.hora_inicio.substring(0, 5);
        } else if (horaInicioInput && descricao) {
            // Fallback: parsear da descrição
            const horaMatch = descricao.match(/Horário:\s*(\d{2}:\d{2})(?:\s*-\s*(\d{2}:\d{2}))?/);
            if (horaMatch) {
                horaInicioInput.value = horaMatch[1];
                if (horaFimInput && horaMatch[2]) horaFimInput.value = horaMatch[2];
            }
        }
        
        if (horaFimInput && item.hora_fim) {
            horaFimInput.value = item.hora_fim.substring(0, 5);
        }
        
        // Descrição detalhada
        if (descInput) {
            if (descricao) {
                // Remover Local: e Horário: da descrição para obter apenas a descrição detalhada
                let desc = descricao.replace(/Local:[^\n]+\n?/, '').replace(/Horário:[^\n]+\n?/, '').trim();
                if (desc) descInput.value = desc;
            }
        }
    }
}

// Atualizar item via API
async function atualizarItemProgramacao(dados) {
    try {
        const response = await fetch(`${API_PROGRAMACAO}update.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(dados)
        });

        const data = await response.json();

        if (data.success) {
            Swal.fire('Sucesso!', 'Item atualizado com sucesso!', 'success');
            await carregarProgramacao(eventoAtual.id);
        } else {
            Swal.fire('Erro!', data.error || 'Erro ao atualizar item', 'error');
        }
    } catch (error) {
        console.error('Erro ao atualizar item:', error);
        Swal.fire('Erro!', 'Erro ao atualizar item de programação', 'error');
    }
}

// Excluir item de programação
window.excluirItemProgramacao = async function(itemId) {
    if (!modoEdicao) {
        Swal.fire('Aviso', 'Ative o modo de edição para excluir itens', 'info');
        return;
    }

    const result = await Swal.fire({
        title: 'Confirmar exclusão',
        text: 'Tem certeza que deseja excluir este item?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
        try {
            const response = await fetch(`${API_PROGRAMACAO}delete.php?id=${itemId}`, {
                method: 'DELETE'
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire('Sucesso!', 'Item excluído com sucesso!', 'success');
                await carregarProgramacao(eventoAtual.id);
            } else {
                Swal.fire('Erro!', data.error || 'Erro ao excluir item', 'error');
            }
        } catch (error) {
            console.error('Erro ao excluir item:', error);
            Swal.fire('Erro!', 'Erro ao excluir item de programação', 'error');
        }
    }
}
