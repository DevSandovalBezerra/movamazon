<?php
$activePage = 'banners';
?>

<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Banners do Carrossel</h1>
            <p class="text-gray-600">Gerencie imagens, textos e links exibidos na landing page.</p>
        </div>
        <button id="btn-novo-banner" class="btn-primary flex items-center gap-2">
            <i class="fas fa-plus"></i> Novo banner
        </button>
    </div>

    <div class="admin-card">
        <div class="admin-card-header flex items-center justify-between">
            <h3 class="admin-card-title">
                <i class="fas fa-images admin-card-icon bg-[#0b4340] text-[#f5c113]"></i>
                Lista de banners
            </h3>
            <button id="btn-atualizar-banners" class="btn-secondary flex items-center gap-2">
                <i class="fas fa-sync"></i> Atualizar
            </button>
        </div>
        <div id="banners-loading" class="admin-loading">
            <div class="admin-spinner"></div>
            <span>Carregando banners...</span>
        </div>
        <div id="banners-empty" class="hidden py-10 text-center text-gray-500">
            Nenhum banner cadastrado.
        </div>
        <div id="banners-lista" class="space-y-4"></div>
    </div>
</div>

<!-- Modal Banner -->
<div id="modal-banner" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <!-- Header do Modal -->
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <h2 id="modal-banner-titulo" class="text-xl font-bold text-secondary-black">Novo Banner</h2>
            <button data-close-modal="modal-banner" class="text-secondary-dark-gray hover:text-secondary-black">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Conteúdo do Modal -->
        <div class="p-6 space-y-5">
            <!-- Título -->
            <div>
                <label class="block text-sm font-medium text-secondary-dark-gray mb-2">Título *</label>
                <input type="text" id="banner-titulo" class="input-primary w-full" placeholder="Ex.: Movimente-se pelo Amazonas">
            </div>

            <!-- Imagem -->
            <div>
                <label class="block text-sm font-medium text-secondary-dark-gray mb-2">Imagem *</label>
                <div class="space-y-2">
                    <div class="flex gap-2">
                        <input type="file" id="banner-imagem-file" accept="image/*" class="input-primary flex-1">
                        <input type="text" id="banner-imagem" class="input-primary flex-1" placeholder="Ou cole URL/caminho">
                    </div>
                    <div id="banner-imagem-preview" class="hidden">
                        <img id="banner-imagem-preview-img" src="" alt="Preview" class="w-full h-40 object-cover rounded border border-gray-200">
                    </div>
                </div>
            </div>

            <!-- Descrição -->
            <div>
                <label class="block text-sm font-medium text-secondary-dark-gray mb-2">Descrição</label>
                <textarea id="banner-descricao" class="input-primary w-full" rows="2" placeholder="Descrição breve do banner"></textarea>
            </div>

            <!-- Link e Texto do Botão -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-secondary-dark-gray mb-2">Link</label>
                    <input type="text" id="banner-link" class="input-primary w-full" placeholder="URL destino">
                </div>
                <div>
                    <label class="block text-sm font-medium text-secondary-dark-gray mb-2">Texto do botão</label>
                    <input type="text" id="banner-texto-botao" class="input-primary w-full" placeholder="Saiba mais">
                </div>
            </div>

            <!-- Período de Exibição -->
            <div>
                <label class="block text-sm font-medium text-secondary-dark-gray mb-2">Período de exibição</label>
                <div class="grid grid-cols-2 gap-4">
                    <input type="datetime-local" id="banner-data-inicio" class="input-primary w-full" placeholder="Início">
                    <input type="datetime-local" id="banner-data-fim" class="input-primary w-full" placeholder="Fim">
                </div>
            </div>

            <!-- Opções -->
            <div class="flex items-center gap-6 pt-2">
                <label class="inline-flex items-center gap-2 text-sm cursor-pointer">
                    <input type="checkbox" id="banner-ativo" class="w-4 h-4 text-primary-purple rounded" checked>
                    <span class="text-secondary-dark-gray">Ativo</span>
                </label>
                <label class="inline-flex items-center gap-2 text-sm cursor-pointer">
                    <input type="checkbox" id="banner-target-blank" class="w-4 h-4 text-primary-purple rounded">
                    <span class="text-secondary-dark-gray">Abrir em nova aba</span>
                </label>
            </div>
            
            <!-- Footer do Modal -->
            <div class="flex justify-end space-x-3 pt-4 border-t">
                <button type="button" data-close-modal="modal-banner" class="btn-secondary">Cancelar</button>
                <button id="btn-salvar-banner" class="btn-primary">Salvar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmação -->
<div id="modal-confirmacao" class="admin-modal hidden">
    <div class="admin-modal-overlay" data-close-modal="modal-confirmacao"></div>
    <div class="admin-modal-content max-w-md">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">Confirmar remoção</h3>
            <button class="admin-modal-close" data-close-modal="modal-confirmacao">&times;</button>
        </div>
        <div class="admin-modal-body">
            <p class="text-gray-600">Tem certeza que deseja remover este banner?</p>
        </div>
        <div class="admin-modal-footer">
            <button class="btn-secondary" data-close-modal="modal-confirmacao">Cancelar</button>
            <button id="btn-confirmar-remocao" class="btn-danger">Remover</button>
        </div>
    </div>
</div>

<script src="../../js/admin/banners.js"></script>

