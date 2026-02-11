<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    header('Location: ../auth/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Categorias - MovAmazon</title>
    <link rel="stylesheet" href="../../assets/css/tailwind.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <h1 class="text-2xl font-bold text-gray-900">Gerenciar Categorias</h1>
                    <button onclick="openModalCategoria()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Nova Categoria
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Filtros -->
            <div class="bg-white p-4 rounded-lg shadow mb-6">
                <div class="flex flex-wrap gap-4">
                    <div class="flex-1 min-w-64">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                        <input type="text" id="searchInput" placeholder="Nome da categoria..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="w-48">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo Público</label>
                        <select id="filterTipoPublico" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos</option>
                            <option value="comunidade_academica">Comunidade Acadêmica</option>
                            <option value="publico_geral">Público Geral</option>
                            <option value="ambos">Ambos</option>
                        </select>
                    </div>
                    <div class="w-32">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select id="filterStatus" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos</option>
                            <option value="1">Ativo</option>
                            <option value="0">Inativo</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Tabela de Categorias -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo Público</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Faixa Etária</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="categoriasTable" class="bg-white divide-y divide-gray-200">
                            <!-- Dados serão carregados via JavaScript -->
                        </tbody>
                    </table>
                </div>
                <div id="loadingMessage" class="text-center py-8 text-gray-500">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                    <p class="mt-2">Carregando categorias...</p>
                </div>
                <div id="emptyMessage" class="text-center py-8 text-gray-500 hidden">
                    <p>Nenhuma categoria encontrada</p>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Categoria -->
    <div id="modalCategoria" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 id="modalTitle" class="text-lg font-medium text-gray-900">Nova Categoria</h3>
                </div>
                <form id="formCategoria" class="px-6 py-4">
                    <input type="hidden" id="categoriaId" name="id">

                    <div class="mb-4">
                        <label for="nome" class="block text-sm font-medium text-gray-700 mb-2">Nome *</label>
                        <input type="text" id="nome" name="nome" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="mb-4">
                        <label for="descricao" class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
                        <textarea id="descricao" name="descricao" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="tipo_publico" class="block text-sm font-medium text-gray-700 mb-2">Tipo Público *</label>
                        <select id="tipo_publico" name="tipo_publico" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Selecione...</option>
                            <option value="comunidade_academica">Comunidade Acadêmica</option>
                            <option value="publico_geral">Público Geral</option>
                            <option value="ambos">Ambos</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="idade_min" class="block text-sm font-medium text-gray-700 mb-2">Idade Mínima</label>
                            <input type="number" id="idade_min" name="idade_min" min="0" max="100" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="idade_max" class="block text-sm font-medium text-gray-700 mb-2">Idade Máxima</label>
                            <input type="number" id="idade_max" name="idade_max" min="0" max="100" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" id="desconto_idoso" name="desconto_idoso" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Desconto para Idosos</span>
                        </label>
                    </div>

                    <div id="editFields" class="hidden">
                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" id="ativo" name="ativo" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Ativo</span>
                            </label>
                        </div>
                    </div>
                </form>
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button onclick="closeModalCategoria()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">
                        Cancelar
                    </button>
                    <button onclick="saveCategoria()" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md">
                        Salvar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="../../js/validacoes.js"></script>
    <script src="../../js/testes-fluxo.js"></script>
    <script src="../../js/sistema-logs.js"></script>
    <script>
        let categorias = [];
        let isEditing = false;

        // Carregar categorias ao iniciar
        document.addEventListener('DOMContentLoaded', function() {
            loadCategorias();
            setupEventListeners();
        });

        function setupEventListeners() {
            document.getElementById('searchInput').addEventListener('input', filterCategorias);
            document.getElementById('filterTipoPublico').addEventListener('change', filterCategorias);
            document.getElementById('filterStatus').addEventListener('change', filterCategorias);
        }

        async function loadCategorias() {
            try {
                const response = await fetch('/api/categoria/list.php');
                const data = await response.json();

                if (data.success) {
                    categorias = data.categorias;
                    renderCategorias(categorias);
                } else {
                    showError('Erro ao carregar categorias: ' + data.message);
                }
            } catch (error) {
                showError('Erro ao carregar categorias: ' + error.message);
            }
        }

        function renderCategorias(categoriasToRender) {
            const tbody = document.getElementById('categoriasTable');
            const loadingMessage = document.getElementById('loadingMessage');
            const emptyMessage = document.getElementById('emptyMessage');

            loadingMessage.classList.add('hidden');

            if (categoriasToRender.length === 0) {
                emptyMessage.classList.remove('hidden');
                tbody.innerHTML = '';
                return;
            }

            emptyMessage.classList.add('hidden');

            tbody.innerHTML = categoriasToRender.map(categoria => `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div>
                            <div class="text-sm font-medium text-gray-900">${categoria.nome}</div>
                            ${categoria.descricao ? `<div class="text-sm text-gray-500">${categoria.descricao}</div>` : ''}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                            categoria.tipo_publico === 'comunidade_academica' ? 'bg-green-100 text-green-800' :
                            categoria.tipo_publico === 'publico_geral' ? 'bg-blue-100 text-blue-800' :
                            'bg-purple-100 text-purple-800'
                        }">
                            ${getTipoPublicoLabel(categoria.tipo_publico)}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${categoria.idade_min || 0} - ${categoria.idade_max || 100} anos
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                            categoria.ativo == 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                        }">
                            ${categoria.ativo == 1 ? 'Ativo' : 'Inativo'}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button onclick="editCategoria(${categoria.id})" class="text-blue-600 hover:text-blue-900 mr-3">Editar</button>
                        <button onclick="deleteCategoria(${categoria.id})" class="text-red-600 hover:text-red-900">Excluir</button>
                    </td>
                </tr>
            `).join('');
        }

        function getTipoPublicoLabel(tipo) {
            const labels = {
                'comunidade_academica': 'Comunidade Acadêmica',
                'publico_geral': 'Público Geral',
                'ambos': 'Ambos'
            };
            return labels[tipo] || tipo;
        }

        function filterCategorias() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const tipoPublico = document.getElementById('filterTipoPublico').value;
            const status = document.getElementById('filterStatus').value;

            let filtered = categorias.filter(categoria => {
                const matchesSearch = categoria.nome.toLowerCase().includes(searchTerm) ||
                    (categoria.descricao && categoria.descricao.toLowerCase().includes(searchTerm));
                const matchesTipo = !tipoPublico || categoria.tipo_publico === tipoPublico;
                const matchesStatus = status === '' || categoria.ativo.toString() === status;

                return matchesSearch && matchesTipo && matchesStatus;
            });

            renderCategorias(filtered);
        }

        function openModalCategoria() {
            isEditing = false;
            document.getElementById('modalTitle').textContent = 'Nova Categoria';
            document.getElementById('formCategoria').reset();
            document.getElementById('categoriaId').value = '';
            document.getElementById('editFields').classList.add('hidden');
            document.getElementById('modalCategoria').classList.remove('hidden');
        }

        function closeModalCategoria() {
            document.getElementById('modalCategoria').classList.add('hidden');
        }

        async function saveCategoria() {
            const formData = new FormData(document.getElementById('formCategoria'));

            try {
                const url = isEditing ? '/api/categoria/update.php' : '/api/categoria/create.php';
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showSuccess(isEditing ? 'Categoria atualizada com sucesso!' : 'Categoria criada com sucesso!');
                    closeModalCategoria();
                    loadCategorias();
                } else {
                    showError('Erro: ' + data.message);
                }
            } catch (error) {
                showError('Erro ao salvar categoria: ' + error.message);
            }
        }

        async function editCategoria(id) {
            try {
                const response = await fetch(`/api/categoria/get.php?id=${id}`);
                const data = await response.json();

                if (data.success) {
                    const categoria = data.categoria;
                    isEditing = true;

                    document.getElementById('modalTitle').textContent = 'Editar Categoria';
                    document.getElementById('categoriaId').value = categoria.id;
                    document.getElementById('nome').value = categoria.nome;
                    document.getElementById('descricao').value = categoria.descricao || '';
                    document.getElementById('tipo_publico').value = categoria.tipo_publico;
                    document.getElementById('idade_min').value = categoria.idade_min || '';
                    document.getElementById('idade_max').value = categoria.idade_max || '';
                    document.getElementById('desconto_idoso').checked = categoria.desconto_idoso == 1;
                    document.getElementById('ativo').checked = categoria.ativo == 1;

                    document.getElementById('editFields').classList.remove('hidden');
                    document.getElementById('modalCategoria').classList.remove('hidden');
                } else {
                    showError('Erro ao carregar categoria: ' + data.message);
                }
            } catch (error) {
                showError('Erro ao carregar categoria: ' + error.message);
            }
        }

        async function deleteCategoria(id) {
            const result = await Swal.fire({
                title: 'Confirmar exclusão',
                text: 'Tem certeza que deseja excluir esta categoria?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                try {
                    const formData = new FormData();
                    formData.append('id', id);

                    const response = await fetch('/api/categoria/delete.php', {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        showSuccess('Categoria excluída com sucesso!');
                        loadCategorias();
                    } else {
                        showError('Erro ao excluir categoria: ' + data.message);
                    }
                } catch (error) {
                    showError('Erro ao excluir categoria: ' + error.message);
                }
            }
        }

        function showSuccess(message) {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: message,
                timer: 3000,
                showConfirmButton: false
            });
        }

        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: message
            });
        }
    </script>
</body>

</html>
