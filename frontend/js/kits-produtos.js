// Variáveis globais
let tabAtual = 'produtos';
let produtosData = [];
let kitsData = [];
let eventosData = [];
let modalidadesData = [];
let paginaAtual = 1;
let itensPorPagina = 10;

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    inicializar();
    carregarEventos();
    carregarDados();
    
    // Event listeners para tabs
    document.getElementById('tabProdutos').addEventListener('click', () => {
        tabAtual = 'produtos';
        atualizarTabs();
        renderizarDados();
    });
    
    document.getElementById('tabKits').addEventListener('click', () => {
        tabAtual = 'kits';
        atualizarTabs();
        renderizarDados();
    });
    
    // Event listeners para botões
    document.getElementById('btnNovoProduto').addEventListener('click', abrirModalProduto);
    document.getElementById('btnNovoKit').addEventListener('click', abrirModalKit);
    
    // Event listeners para filtros
    document.getElementById('filtroEvento').addEventListener('change', carregarModalidades);
    document.getElementById('filtroModalidade').addEventListener('change', aplicarFiltros);
    document.getElementById('busca').addEventListener('input', aplicarFiltros);
    
    // Event listeners para formulários
    document.getElementById('formProduto').addEventListener('submit', salvarProduto);
    document.getElementById('formKit').addEventListener('submit', salvarKit);
    
    // Event listeners para paginação
    document.getElementById('btn-anterior').addEventListener('click', paginaAnterior);
    document.getElementById('btn-proximo').addEventListener('click', paginaProximo);
});

// Função de inicialização
function inicializar() {
    atualizarTabs();
}

// Função para atualizar tabs
function atualizarTabs() {
    const tabProdutos = document.getElementById('tabProdutos');
    const tabKits = document.getElementById('tabKits');
    const conteudoProdutos = document.getElementById('conteudoProdutos');
    const conteudoKits = document.getElementById('conteudoKits');
    
    if (tabAtual === 'produtos') {
        tabProdutos.classList.add('border-primary-500', 'text-primary-600');
        tabProdutos.classList.remove('border-transparent', 'text-gray-500');
        tabKits.classList.add('border-transparent', 'text-gray-500');
        tabKits.classList.remove('border-primary-500', 'text-primary-600');
        conteudoProdutos.classList.remove('hidden');
        conteudoKits.classList.add('hidden');
    } else {
        tabKits.classList.add('border-primary-500', 'text-primary-600');
        tabKits.classList.remove('border-transparent', 'text-gray-500');
        tabProdutos.classList.add('border-transparent', 'text-gray-500');
        tabProdutos.classList.remove('border-primary-500', 'text-primary-600');
        conteudoKits.classList.remove('hidden');
        conteudoProdutos.classList.add('hidden');
    }
}

// Função para carregar dados
async function carregarDados() {
    try {
        mostrarLoading();
        
        if (tabAtual === 'produtos') {
            await carregarProdutos();
        } else {
            await carregarKits();
        }
        
        renderizarDados();
    } catch (error) {
        console.error('Erro ao carregar dados:', error);
        mostrarErro('Erro ao carregar dados');
    }
}

// Função para carregar produtos
async function carregarProdutos() {
    try {
        console.log('📡 Carregando produtos...');
        const response = await fetch('../../../api/organizador/produtos/list.php');
        console.log('📥 Resposta produtos:', response.status, response.statusText);
        
        let data;
        try {
            data = await response.json();
            console.log('📊 Dados produtos recebidos:', data);
        } catch (error) {
            console.log('❌ Erro ao parsear JSON produtos:', error);
            if (!response.bodyUsed) {
                const responseText = await response.text();
                console.log('📄 Resposta bruta produtos:', responseText);
            }
            throw new Error('Resposta inválida do servidor');
        }
        
        if (data.success) {
            produtosData = data.data;
            console.log('✅ Produtos carregados:', produtosData.length);
        } else {
            throw new Error(data.error || 'Erro ao carregar produtos');
        }
    } catch (error) {
        console.error('💥 Erro ao carregar produtos:', error);
        throw error;
    }
}

// Função para carregar kits
async function carregarKits() {
    try {
        console.log('📡 Carregando kits...');
        const response = await fetch('../../../api/organizador/kits-evento/list.php');
        console.log('📥 Resposta kits:', response.status, response.statusText);
        
        let data;
        try {
            data = await response.json();
            console.log('📊 Dados kits recebidos:', data);
        } catch (error) {
            console.log('❌ Erro ao parsear JSON kits:', error);
            if (!response.bodyUsed) {
                const responseText = await response.text();
                console.log('📄 Resposta bruta kits:', responseText);
            }
            throw new Error('Resposta inválida do servidor');
        }
        
        if (data.success) {
            kitsData = data.data;
            console.log('✅ Kits carregados:', kitsData.length);
        } else {
            throw new Error(data.error || 'Erro ao carregar kits');
        }
    } catch (error) {
        console.error('💥 Erro ao carregar kits:', error);
        throw error;
    }
}

// Função para carregar eventos
async function carregarEventos() {
    try {
        console.log('📡 Carregando eventos...');
        const response = await fetch('../../../api/organizador/eventos/list.php');
        console.log('📥 Resposta eventos:', response.status, response.statusText);
        
        let data;
        try {
            data = await response.json();
            console.log('📊 Dados eventos recebidos:', data);
        } catch (error) {
            console.log('❌ Erro ao parsear JSON eventos:', error);
            if (!response.bodyUsed) {
                const responseText = await response.text();
                console.log('📄 Resposta bruta eventos:', responseText);
            }
            throw new Error('Resposta inválida do servidor');
        }
        
        if (data.success) {
            eventosData = data.data;
            console.log('✅ Eventos carregados:', eventosData.length);
            preencherSelectEventos();
        } else {
            throw new Error(data.error || 'Erro ao carregar eventos');
        }
    } catch (error) {
        console.error('💥 Erro ao carregar eventos:', error);
    }
}

// Função para preencher select de eventos
function preencherSelectEventos() {
    const selectEvento = document.getElementById('filtroEvento');
    const selectKitEvento = document.getElementById('kit_evento_id');
    
    // Limpar opções existentes
    selectEvento.innerHTML = '<option value="">Todos os eventos</option>';
    selectKitEvento.innerHTML = '<option value="">Selecione um evento</option>';
    
    eventosData.forEach(evento => {
        // Para filtro
        const option = document.createElement('option');
        option.value = evento.id;
        option.textContent = evento.nome;
        selectEvento.appendChild(option);
        
        // Para modal kit
        const optionKit = document.createElement('option');
        optionKit.value = evento.id;
        optionKit.textContent = evento.nome;
        selectKitEvento.appendChild(optionKit);
    });
}

// Função para carregar modalidades
async function carregarModalidades() {
    const eventoId = document.getElementById('filtroEvento').value;
    const selectModalidade = document.getElementById('filtroModalidade');
    const selectKitModalidade = document.getElementById('kit_modalidade_evento_id');
    
    if (!eventoId) {
        selectModalidade.innerHTML = '<option value="">Todas as modalidades</option>';
        selectModalidade.disabled = true;
        selectKitModalidade.innerHTML = '<option value="">Selecione uma modalidade</option>';
        selectKitModalidade.disabled = true;
        return;
    }
    
    try {
        console.log('📡 Carregando modalidades para evento:', eventoId);
        const response = await fetch(`../../../api/organizador/modalidades/list.php?evento_id=${eventoId}`);
        console.log('📥 Resposta modalidades:', response.status, response.statusText);
        
        let data;
        try {
            data = await response.json();
            console.log('📊 Dados modalidades recebidos:', data);
        } catch (error) {
            console.log('❌ Erro ao parsear JSON modalidades:', error);
            if (!response.bodyUsed) {
                const responseText = await response.text();
                console.log('📄 Resposta bruta modalidades:', responseText);
            }
            throw new Error('Resposta inválida do servidor');
        }
        
        if (data.success) {
            modalidadesData = data.modalidades;
            console.log('✅ Modalidades carregadas:', modalidadesData.length);
            
            // Preencher select de filtro
            selectModalidade.innerHTML = '<option value="">Todas as modalidades</option>';
            modalidadesData.forEach(modalidade => {
                const option = document.createElement('option');
                option.value = modalidade.id;
                option.textContent = `${modalidade.nome_categoria} - ${modalidade.nome_modalidade}`;
                selectModalidade.appendChild(option);
            });
            selectModalidade.disabled = false;
            
            // Preencher select do modal kit
            selectKitModalidade.innerHTML = '<option value="">Selecione uma modalidade</option>';
            modalidadesData.forEach(modalidade => {
                const option = document.createElement('option');
                option.value = modalidade.id;
                option.textContent = `${modalidade.nome_categoria} - ${modalidade.nome_modalidade}`;
                selectKitModalidade.appendChild(option);
            });
            selectKitModalidade.disabled = false;
        } else {
            throw new Error(data.error || 'Erro ao carregar modalidades');
        }
    } catch (error) {
        console.error('💥 Erro ao carregar modalidades:', error);
    }
}

// Função para aplicar filtros
function aplicarFiltros() {
    const filtroEvento = document.getElementById('filtroEvento').value;
    const filtroModalidade = document.getElementById('filtroModalidade').value;
    const busca = document.getElementById('busca').value.toLowerCase();
    
    let dadosFiltrados = [];
    
    if (tabAtual === 'produtos') {
        dadosFiltrados = produtosData.filter(produto => {
            const matchEvento = !filtroEvento || produto.evento_id == filtroEvento;
            const matchBusca = !busca || produto.nome.toLowerCase().includes(busca);
            return matchEvento && matchBusca;
        });
    } else {
        dadosFiltrados = kitsData.filter(kit => {
            const matchEvento = !filtroEvento || kit.evento_id == filtroEvento;
            const matchModalidade = !filtroModalidade || kit.modalidade_evento_id == filtroModalidade;
            const matchBusca = !busca || kit.nome.toLowerCase().includes(busca);
            return matchEvento && matchModalidade && matchBusca;
        });
    }
    
    renderizarDados(dadosFiltrados);
}

// Função para renderizar dados
function renderizarDados(dados = null) {
    const dadosParaRenderizar = dados || (tabAtual === 'produtos' ? produtosData : kitsData);
    
    if (tabAtual === 'produtos') {
        renderizarProdutos(dadosParaRenderizar);
    } else {
        renderizarKits(dadosParaRenderizar);
    }
    
    atualizarPaginacao(dadosParaRenderizar);
    ocultarLoading(); // Ocultar loading após renderizar dados
}

// Função para renderizar produtos
function renderizarProdutos(produtos) {
    const container = document.getElementById('produtos-container');
    container.innerHTML = '';
    
    if (produtos.length === 0) {
        container.innerHTML = `
            <div class="text-center py-12">
                <i class="fas fa-cube text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-gray-600 font-semibold mb-2">Nenhum produto encontrado</h3>
                <p class="text-gray-500">Crie seu primeiro produto para começar.</p>
            </div>
        `;
        return;
    }
    
    produtos.forEach(produto => {
        const card = criarCardProduto(produto);
        container.appendChild(card);
    });
}

// Função para renderizar kits
function renderizarKits(kits) {
    const container = document.getElementById('kits-container');
    container.innerHTML = '';
    
    if (kits.length === 0) {
        container.innerHTML = `
            <div class="text-center py-12">
                <i class="fas fa-box text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-gray-600 font-semibold mb-2">Nenhum kit encontrado</h3>
                <p class="text-gray-500">Crie seu primeiro kit para começar.</p>
            </div>
        `;
        return;
    }
    
    kits.forEach(kit => {
        const card = criarCardKit(kit);
        container.appendChild(card);
    });
}

// Função para criar card de produto
function criarCardProduto(produto) {
    const card = document.createElement('div');
    card.className = 'bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow';
    
    const tipoIcon = {
        'camiseta': 'fas fa-tshirt',
        'medalha': 'fas fa-medal',
        'numero': 'fas fa-hashtag',
        'chip': 'fas fa-microchip',
        'outro': 'fas fa-cube'
    };
    
    const tipoColor = {
        'camiseta': 'text-blue-600 bg-blue-100',
        'medalha': 'text-yellow-600 bg-yellow-100',
        'numero': 'text-green-600 bg-green-100',
        'chip': 'text-purple-600 bg-purple-100',
        'outro': 'text-gray-600 bg-gray-100'
    };
    
    card.innerHTML = `
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 ${tipoColor[produto.tipo]} rounded-lg flex items-center justify-center">
                    <i class="${tipoIcon[produto.tipo]} text-lg"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">${produto.nome}</h3>
                    <p class="text-sm text-gray-600">${produto.descricao || 'Sem descrição'}</p>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${tipoColor[produto.tipo]}">
                        ${produto.tipo}
                    </span>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <button onclick="editarProduto(${produto.id})" class="text-blue-600 hover:text-blue-800 p-2 hover:bg-blue-50 rounded">
                    <i class="fas fa-edit"></i>
                </button>
                <button onclick="excluirProduto(${produto.id})" class="text-red-600 hover:text-red-800 p-2 hover:bg-red-50 rounded">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    
    return card;
}

// Função para criar card de kit
function criarCardKit(kit) {
    const card = document.createElement('div');
    card.className = 'bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow';
    
    const statusColor = {
        'ativo': 'text-green-600 bg-green-100',
        'esgotado': 'text-red-600 bg-red-100',
        'inativo': 'text-gray-600 bg-gray-100'
    };
    
    card.innerHTML = `
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-green-500 rounded-lg flex items-center justify-center">
                    <i class="fas fa-box text-white text-lg"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">${kit.nome}</h3>
                    <p class="text-sm text-gray-600">${kit.descricao || 'Sem descrição'}</p>
                    <p class="text-xs text-gray-500">${kit.nome_modalidade}</p>
                </div>
            </div>
            <div class="text-right">
                <div class="text-2xl font-bold text-green-600">R$ ${kit.valor}</div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusColor[kit.status]}">
                    ${kit.status}
                </span>
            </div>
        </div>
        
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div class="bg-blue-50 p-3 rounded-lg">
                <div class="text-xs text-blue-600 font-medium">Produtos</div>
                <div class="text-sm font-semibold">${kit.total_produtos}</div>
            </div>
            <div class="bg-green-50 p-3 rounded-lg">
                <div class="text-xs text-green-600 font-medium">Estoque</div>
                <div class="text-sm font-semibold">${kit.estoque_disponivel}/${kit.estoque_total}</div>
            </div>
        </div>
        
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Ocupação: ${kit.ocupacao_percentual}%
            </div>
            <div class="flex items-center space-x-2">
                <button onclick="editarKit(${kit.id})" class="text-blue-600 hover:text-blue-800 p-2 hover:bg-blue-50 rounded">
                    <i class="fas fa-edit"></i>
                </button>
                <button onclick="excluirKit(${kit.id})" class="text-red-600 hover:text-red-800 p-2 hover:bg-red-50 rounded">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    
    return card;
}

// Funções de estado
function mostrarLoading() {
    document.getElementById('loading').style.display = 'block';
    document.getElementById('error-message').style.display = 'none';
}

function ocultarLoading() {
    document.getElementById('loading').style.display = 'none';
}

function mostrarErro(mensagem) {
    document.getElementById('loading').style.display = 'none';
    document.getElementById('error-message').style.display = 'block';
    document.getElementById('error-message').querySelector('p').textContent = mensagem;
}

// Funções de paginação
function atualizarPaginacao(dados) {
    const totalItens = dados.length;
    const totalPaginas = Math.ceil(totalItens / itensPorPagina);
    
    if (totalPaginas <= 1) {
        document.getElementById('paginacao').style.display = 'none';
        return;
    }
    
    document.getElementById('paginacao').style.display = 'flex';
    
    // Atualizar botões
    document.getElementById('btn-anterior').disabled = paginaAtual <= 1;
    document.getElementById('btn-proximo').disabled = paginaAtual >= totalPaginas;
    
    // Gerar números de página
    const paginasContainer = document.getElementById('paginas');
    paginasContainer.innerHTML = '';
    
    const inicio = Math.max(1, paginaAtual - 2);
    const fim = Math.min(totalPaginas, paginaAtual + 2);
    
    for (let i = inicio; i <= fim; i++) {
        const btn = document.createElement('button');
        btn.className = `px-3 py-2 border rounded-lg ${i === paginaAtual ? 'bg-primary-600 text-white border-primary-600' : 'text-gray-700 border-gray-300 hover:bg-gray-50'}`;
        btn.textContent = i;
        btn.onclick = () => irParaPagina(i);
        paginasContainer.appendChild(btn);
    }
}

function paginaAnterior() {
    if (paginaAtual > 1) {
        paginaAtual--;
        renderizarDados();
    }
}

function paginaProximo() {
    const dados = tabAtual === 'produtos' ? produtosData : kitsData;
    const totalPaginas = Math.ceil(dados.length / itensPorPagina);
    if (paginaAtual < totalPaginas) {
        paginaAtual++;
        renderizarDados();
    }
}

function irParaPagina(pagina) {
    paginaAtual = pagina;
    renderizarDados();
}

// Funções do modal de produto
function abrirModalProduto(produtoId = null) {
    const modal = document.getElementById('modalProduto');
    const titulo = document.getElementById('modalProdutoTitulo');
    const btnTexto = document.getElementById('btnSalvarProdutoTexto');
    const form = document.getElementById('formProduto');
    
    if (produtoId) {
        // Modo edição
        titulo.textContent = 'Editar Produto';
        btnTexto.textContent = 'Atualizar Produto';
        // TODO: Carregar dados do produto
    } else {
        // Modo criação
        titulo.textContent = 'Novo Produto';
        btnTexto.textContent = 'Criar Produto';
        form.reset();
    }
    
    modal.classList.remove('hidden');
}

function fecharModalProduto() {
    document.getElementById('modalProduto').classList.add('hidden');
}

// Funções do modal de kit
function abrirModalKit(kitId = null) {
    const modal = document.getElementById('modalKit');
    const titulo = document.getElementById('modalKitTitulo');
    const btnTexto = document.getElementById('btnSalvarKitTexto');
    const form = document.getElementById('formKit');
    
    if (kitId) {
        // Modo edição
        titulo.textContent = 'Editar Kit';
        btnTexto.textContent = 'Atualizar Kit';
        // TODO: Carregar dados do kit
    } else {
        // Modo criação
        titulo.textContent = 'Novo Kit';
        btnTexto.textContent = 'Criar Kit';
        form.reset();
        limparProdutosKit();
        limparTamanhosKit();
    }
    
    modal.classList.remove('hidden');
}

function fecharModalKit() {
    document.getElementById('modalKit').classList.add('hidden');
}

// Funções para gerenciar produtos do kit
function adicionarProdutoKit() {
    const container = document.getElementById('produtos-kit-container');
    const produtoId = Date.now();
    
    const produtoHtml = `
        <div class="border border-gray-200 rounded-lg p-4" data-produto-id="${produtoId}">
            <div class="flex items-center justify-between mb-3">
                <h4 class="font-medium text-gray-900">Produto ${container.children.length + 1}</h4>
                <button type="button" onclick="removerProdutoKit(${produtoId})" class="text-red-600 hover:text-red-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Produto *</label>
                    <select name="produtos[${produtoId}][produto_id]" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                        <option value="">Selecione um produto</option>
                        ${produtosData.map(produto => `
                            <option value="${produto.id}">${produto.nome} (${produto.tipo})</option>
                        `).join('')}
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantidade</label>
                    <input type="number" name="produtos[${produtoId}][quantidade]" min="1" value="1" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tamanho (se camiseta)</label>
                    <select name="produtos[${produtoId}][tamanho_id]" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="">Sem tamanho</option>
                    </select>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', produtoHtml);
}

function removerProdutoKit(produtoId) {
    const elemento = document.querySelector(`[data-produto-id="${produtoId}"]`);
    if (elemento) {
        elemento.remove();
    }
}

function limparProdutosKit() {
    document.getElementById('produtos-kit-container').innerHTML = '';
}

// Funções para gerenciar tamanhos do kit
function adicionarTamanhoKit() {
    const container = document.getElementById('tamanhos-kit-container');
    const tamanhoId = Date.now();
    
    const tamanhoHtml = `
        <div class="border border-gray-200 rounded-lg p-4" data-tamanho-id="${tamanhoId}">
            <div class="flex items-center justify-between mb-3">
                <h4 class="font-medium text-gray-900">Tamanho ${container.children.length + 1}</h4>
                <button type="button" onclick="removerTamanhoKit(${tamanhoId})" class="text-red-600 hover:text-red-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tamanho *</label>
                    <select name="tamanhos[${tamanhoId}][tamanho_id]" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                        <option value="">Selecione um tamanho</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantidade Disponível</label>
                    <input type="number" name="tamanhos[${tamanhoId}][quantidade_disponivel]" min="1" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', tamanhoHtml);
}

function removerTamanhoKit(tamanhoId) {
    const elemento = document.querySelector(`[data-tamanho-id="${tamanhoId}"]`);
    if (elemento) {
        elemento.remove();
    }
}

function limparTamanhosKit() {
    document.getElementById('tamanhos-kit-container').innerHTML = '';
}

// Funções para salvar dados
async function salvarProduto(e) {
    e.preventDefault();
    
    try {
        const formData = new FormData(e.target);
        const produtoId = formData.get('produto_id');
        
        const url = produtoId ? '../../../api/organizador/produtos/update.php' : '../../../api/organizador/produtos/create.php';
        
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            Swal.fire('Sucesso', data.message, 'success');
            fecharModalProduto();
            carregarDados();
        } else {
            throw new Error(data.error);
        }
    } catch (error) {
        console.error('Erro:', error);
        Swal.fire('Erro', error.message, 'error');
    }
}

async function salvarKit(e) {
    e.preventDefault();
    
    try {
        const formData = new FormData(e.target);
        const kitId = formData.get('kit_id');
        
        // Processar produtos
        const produtos = [];
        const produtosElements = document.querySelectorAll('#produtos-kit-container > div');
        produtosElements.forEach(element => {
            const produtoId = element.querySelector('select[name*="[produto_id]"]').value;
            const quantidade = element.querySelector('input[name*="[quantidade]"]').value;
            const tamanhoId = element.querySelector('select[name*="[tamanho_id]"]').value;
            
            if (produtoId) {
                produtos.push({
                    produto_id: produtoId,
                    quantidade: quantidade,
                    tamanho_id: tamanhoId || null
                });
            }
        });
        
        // Processar tamanhos
        const tamanhos = [];
        const tamanhosElements = document.querySelectorAll('#tamanhos-kit-container > div');
        tamanhosElements.forEach(element => {
            const tamanhoId = element.querySelector('select[name*="[tamanho_id]"]').value;
            const quantidadeDisponivel = element.querySelector('input[name*="[quantidade_disponivel]"]').value;
            
            if (tamanhoId) {
                tamanhos.push({
                    tamanho_id: tamanhoId,
                    quantidade_disponivel: quantidadeDisponivel
                });
            }
        });
        
        // Preparar dados para envio
        const dados = {
            id: kitId || null,
            evento_id: formData.get('kit_evento_id'),
            modalidade_evento_id: formData.get('kit_modalidade_evento_id'),
            nome: formData.get('kit_nome'),
            descricao: formData.get('kit_descricao'),
            valor: formData.get('kit_valor'),
            ativo: 1,
            produtos: produtos,
            tamanhos: tamanhos
        };
        
        const url = kitId ? '../../../api/organizador/kits-evento/update.php' : '../../../api/organizador/kits-evento/create.php';
        
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
            fecharModalKit();
            carregarDados();
        } else {
            throw new Error(data.error);
        }
    } catch (error) {
        console.error('Erro:', error);
        Swal.fire('Erro', error.message, 'error');
    }
}

// Funções para editar e excluir
function editarProduto(produtoId) {
    // TODO: Implementar edição de produto
    console.log('Editar produto:', produtoId);
}

function excluirProduto(produtoId) {
    Swal.fire({
        title: 'Confirmar exclusão',
        text: 'Tem certeza que deseja excluir este produto?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // TODO: Implementar exclusão de produto
            console.log('Excluir produto:', produtoId);
        }
    });
}

function editarKit(kitId) {
    // TODO: Implementar edição de kit
    console.log('Editar kit:', kitId);
}

function excluirKit(kitId) {
    Swal.fire({
        title: 'Confirmar exclusão',
        text: 'Tem certeza que deseja excluir este kit?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // TODO: Implementar exclusão de kit
            console.log('Excluir kit:', kitId);
        }
    });
} 