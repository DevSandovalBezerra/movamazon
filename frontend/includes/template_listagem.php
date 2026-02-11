<?php
/**
 * Template padrão para páginas de listagem
 * 
 * Variáveis necessárias:
 * - $pageTitle: Título da página
 * - $pageSubtitle: Subtítulo da página
 * - $currentPage: Página atual no sidebar
 * - $apiUrl: URL da API para buscar dados
 * - $columns: Array com configuração das colunas
 * - $actions: Array com ações disponíveis
 * - $filters: Array com filtros disponíveis
 * - $createUrl: URL para criar novo item
 */

// Exemplo de uso:
/*
$pageTitle = 'Meus Eventos';
$pageSubtitle = 'Gerencie todos os seus eventos';
$currentPage = 'eventos';
$apiUrl = '../../../api/organizador/eventos/list.php';
$createUrl = 'criar.php';

$columns = [
    ['key' => 'nome', 'label' => 'Nome', 'sortable' => true],
    ['key' => 'data_inicio', 'label' => 'Data', 'type' => 'date'],
    ['key' => 'local', 'label' => 'Local'],
    ['key' => 'total_inscritos', 'label' => 'Inscritos', 'type' => 'number'],
    ['key' => 'status', 'label' => 'Status', 'type' => 'badge']
];

$actions = [
    ['type' => 'view', 'url' => 'visualizar.php?id={id}', 'icon' => 'eye'],
    ['type' => 'edit', 'url' => 'editar.php?id={id}', 'icon' => 'edit'],
    ['type' => 'delete', 'onclick' => 'excluirItem({id})', 'icon' => 'trash']
];

$filters = [
    ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => [
        '' => 'Todos',
        'ativo' => 'Ativo',
        'inativo' => 'Inativo',
        'rascunho' => 'Rascunho'
    ]],
    ['key' => 'busca', 'label' => 'Buscar', 'type' => 'text', 'placeholder' => 'Nome...']
];
*/

// Conteúdo da página
ob_start();
?>

<!-- Header da página -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900"><?php echo $pageTitle; ?></h1>
        <p class="text-gray-600 mt-1"><?php echo $pageSubtitle; ?></p>
    </div>
    <?php if (isset($createUrl)): ?>
        <a href="<?php echo $createUrl; ?>" class="btn-primary">
            <i class="fas fa-plus mr-2"></i>
            Novo <?php echo strtolower($pageTitle); ?>
        </a>
    <?php endif; ?>
</div>

<!-- Filtros -->
<?php if (isset($filters) && !empty($filters)): ?>
    <div class="card mb-6">
        <div class="grid grid-cols-1 md:grid-cols-<?php echo count($filters) + 1; ?> gap-4">
            <?php foreach ($filters as $filter): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo $filter['label']; ?></label>
                    
                    <?php if ($filter['type'] === 'select'): ?>
                        <select id="filtro-<?php echo $filter['key']; ?>" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <?php foreach ($filter['options'] as $value => $label): ?>
                                <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php elseif ($filter['type'] === 'text'): ?>
                        <input type="text" 
                               id="filtro-<?php echo $filter['key']; ?>" 
                               placeholder="<?php echo $filter['placeholder'] ?? ''; ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            
            <div class="flex items-end">
                <button onclick="aplicarFiltros()" class="btn-primary w-full">
                    <i class="fas fa-search mr-2"></i>
                    Filtrar
                </button>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Loading -->
<div id="loading" class="text-center py-8" style="display: none;">
    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
    <p class="mt-2 text-gray-600">Carregando dados...</p>
</div>

<!-- Tabela -->
<div id="table-container" class="card">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <?php foreach ($columns as $column): ?>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <?php echo $column['label']; ?>
                        </th>
                    <?php endforeach; ?>
                    
                    <?php if (isset($actions) && !empty($actions)): ?>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ações
                        </th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody id="table-body" class="bg-white divide-y divide-gray-200">
                <!-- Dados serão carregados aqui -->
            </tbody>
        </table>
    </div>
</div>

<!-- Paginação -->
<div id="paginacao" class="mt-6 flex justify-center" style="display: none;">
    <nav class="flex items-center space-x-2">
        <button id="btn-anterior" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
            <i class="fas fa-chevron-left mr-1"></i>
            Anterior
        </button>
        
        <div id="paginas" class="flex items-center space-x-1">
            <!-- Páginas serão geradas aqui -->
        </div>
        
        <button id="btn-proximo" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
            Próximo
            <i class="fas fa-chevron-right ml-1"></i>
        </button>
    </nav>
</div>

<!-- Erro -->
<div id="error-message" class="text-center py-8" style="display: none;">
    <div class="card">
        <i class="fas fa-exclamation-triangle text-red-500 text-4xl mb-4"></i>
        <p class="text-red-600 mb-4">Erro ao carregar dados.</p>
        <button onclick="carregarDados()" class="btn-primary">
            <i class="fas fa-redo mr-2"></i>
            Tentar novamente
        </button>
    </div>
</div>

<script>
let dados = [];
let paginaAtual = 1;
let itensPorPagina = 10;
let filtros = {};

// Inicializar filtros
<?php if (isset($filters)): ?>
    <?php foreach ($filters as $filter): ?>
        filtros['<?php echo $filter['key']; ?>'] = '';
    <?php endforeach; ?>
<?php endif; ?>

// Carregar dados
async function carregarDados() {
    try {
        showLoading();
        
        const params = new URLSearchParams({
            pagina: paginaAtual,
            limite: itensPorPagina,
            ...filtros
        });
        
        const response = await fetch(`<?php echo $apiUrl; ?>?${params}`);
        const data = await response.json();
        
        if (data.success) {
            dados = data.data.items || data.data.eventos || data.data;
            renderizarTabela();
            renderizarPaginacao(data.data.total, data.data.total_paginas || data.data.paginas);
            hideLoading();
        } else {
            throw new Error(data.message || 'Erro ao carregar dados');
        }
        
    } catch (error) {
        console.error('Erro:', error);
        hideLoading();
        showError();
    }
}

// Renderizar tabela
function renderizarTabela() {
    const tbody = document.getElementById('table-body');
    tbody.innerHTML = '';
    
    if (dados.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="<?php echo count($columns) + (isset($actions) ? 1 : 0); ?>" class="px-6 py-12 text-center">
                    <i class="fas fa-inbox text-gray-400 text-4xl mb-4"></i>
                    <p class="text-gray-500">Nenhum item encontrado.</p>
                </td>
            </tr>
        `;
        return;
    }
    
    dados.forEach(item => {
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50';
        
        // Renderizar colunas
        <?php foreach ($columns as $column): ?>
            const cell<?php echo ucfirst($column['key']); ?> = document.createElement('td');
            cell<?php echo ucfirst($column['key']); ?>.className = 'px-6 py-4 whitespace-nowrap text-sm text-gray-900';
            
            <?php if (isset($column['type'])): ?>
                <?php if ($column['type'] === 'date'): ?>
                    cell<?php echo ucfirst($column['key']); ?>.textContent = new Date(item.<?php echo $column['key']; ?>).toLocaleDateString('pt-BR');
                <?php elseif ($column['type'] === 'number'): ?>
                    cell<?php echo ucfirst($column['key']); ?>.textContent = item.<?php echo $column['key']; ?>.toLocaleString('pt-BR');
                <?php elseif ($column['type'] === 'badge'): ?>
                    cell<?php echo ucfirst($column['key']); ?>.innerHTML = `
                        <span class="px-2 py-1 text-xs font-medium rounded-full ${getStatusClass(item.<?php echo $column['key']; ?>)}">
                            ${item.<?php echo $column['key']; ?>}
                        </span>
                    `;
                <?php else: ?>
                    cell<?php echo ucfirst($column['key']); ?>.textContent = item.<?php echo $column['key']; ?>;
                <?php endif; ?>
            <?php else: ?>
                cell<?php echo ucfirst($column['key']); ?>.textContent = item.<?php echo $column['key']; ?>;
            <?php endif; ?>
            
            row.appendChild(cell<?php echo ucfirst($column['key']); ?>);
        <?php endforeach; ?>
        
        // Renderizar ações
        <?php if (isset($actions)): ?>
            const cellActions = document.createElement('td');
            cellActions.className = 'px-6 py-4 whitespace-nowrap text-right text-sm font-medium';
            
            const actionsHtml = [];
            <?php foreach ($actions as $action): ?>
                <?php if ($action['type'] === 'view'): ?>
                    actionsHtml.push(`
                        <a href="<?php echo $action['url']; ?>" class="btn-secondary text-sm mr-2">
                            <i class="fas fa-<?php echo $action['icon']; ?> mr-1"></i>
                            Ver
                        </a>
                    `);
                <?php elseif ($action['type'] === 'edit'): ?>
                    actionsHtml.push(`
                        <a href="<?php echo $action['url']; ?>" class="btn-primary text-sm mr-2">
                            <i class="fas fa-<?php echo $action['icon']; ?> mr-1"></i>
                            Editar
                        </a>
                    `);
                <?php elseif ($action['type'] === 'delete'): ?>
                    actionsHtml.push(`
                        <button onclick="<?php echo $action['onclick']; ?>" class="btn-danger text-sm">
                            <i class="fas fa-<?php echo $action['icon']; ?> mr-1"></i>
                            Excluir
                        </button>
                    `);
                <?php endif; ?>
            <?php endforeach; ?>
            
            cellActions.innerHTML = actionsHtml.join('');
            row.appendChild(cellActions);
        <?php endif; ?>
        
        tbody.appendChild(row);
    });
}

// Renderizar paginação
function renderizarPaginacao(total, totalPaginas) {
    const paginacao = document.getElementById('paginacao');
    const paginas = document.getElementById('paginas');
    
    if (totalPaginas <= 1) {
        paginacao.style.display = 'none';
        return;
    }
    
    paginacao.style.display = 'flex';
    paginas.innerHTML = '';
    
    for (let i = 1; i <= totalPaginas; i++) {
        const activeClass = i === paginaAtual ? 'bg-primary-600 text-white' : 'bg-white text-gray-500 hover:bg-gray-50';
        paginas.innerHTML += `
            <button onclick="irParaPagina(${i})" 
                    class="px-3 py-2 text-sm font-medium border border-gray-300 rounded-lg ${activeClass}">
                ${i}
            </button>
        `;
    }
    
    // Atualizar botões anterior/próximo
    document.getElementById('btn-anterior').disabled = paginaAtual === 1;
    document.getElementById('btn-proximo').disabled = paginaAtual === totalPaginas;
}

// Funções auxiliares
function getStatusClass(status) {
    switch (status) {
        case 'ativo': return 'bg-green-100 text-green-800';
        case 'inativo': return 'bg-red-100 text-red-800';
        case 'rascunho': return 'bg-yellow-100 text-yellow-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}

function aplicarFiltros() {
    <?php if (isset($filters)): ?>
        <?php foreach ($filters as $filter): ?>
            filtros['<?php echo $filter['key']; ?>'] = document.getElementById('filtro-<?php echo $filter['key']; ?>').value;
        <?php endforeach; ?>
    <?php endif; ?>
    paginaAtual = 1;
    carregarDados();
}

function irParaPagina(pagina) {
    paginaAtual = pagina;
    carregarDados();
}

function showLoading() {
    document.getElementById('loading').style.display = 'block';
    document.getElementById('table-container').style.display = 'none';
    document.getElementById('error-message').style.display = 'none';
}

function hideLoading() {
    document.getElementById('loading').style.display = 'none';
    document.getElementById('table-container').style.display = 'block';
}

function showError() {
    document.getElementById('loading').style.display = 'none';
    document.getElementById('table-container').style.display = 'none';
    document.getElementById('error-message').style.display = 'block';
}

// Carregar dados quando a página carregar
document.addEventListener('DOMContentLoaded', carregarDados);

// Busca em tempo real
<?php if (isset($filters)): ?>
    <?php foreach ($filters as $filter): ?>
        <?php if ($filter['type'] === 'text'): ?>
            document.getElementById('filtro-<?php echo $filter['key']; ?>').addEventListener('input', (e) => {
                if (e.target.value.length >= 3 || e.target.value.length === 0) {
                    aplicarFiltros();
                }
            });
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>
</script>

<?php
$pageContent = ob_get_clean();
include '../../../includes/organizador_layout.php';
?> 
