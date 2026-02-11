<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__, 4) . '/api/db.php';

// Verificar se o usuário está logado como organizador
if (!isset($_SESSION['user_id']) || $_SESSION['papel'] !== 'organizador') {
    header('Location: ../../auth/login.php');
    exit();
}

$organizador_id = $_SESSION['user_id'];
?>
<div class="p-6">
    <div class="max-w-6xl mx-auto">
        <!-- Cabeçalho -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Criar Novo Evento</h1>
                    <p class="text-gray-600 mt-2">Preencha os dados do seu evento esportivo</p>
                </div>
                <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Voltar
                </a>
            </div>
        </div>

        <!-- Progresso -->
        <div class="mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2 md:space-x-4 overflow-x-auto pb-2">
                        <div class="flex items-center step-indicator active" data-step="1">
                            <div class="w-10 h-10 bg-gradient-to-r from-primary-600 to-primary-700 text-white rounded-full flex items-center justify-center text-sm font-bold shadow-lg transition-all duration-300" id="step-1-indicator">
                                <i class="fas fa-user text-xs"></i>
                            </div>
                            <span class="ml-3 text-sm font-semibold text-primary-700 hidden sm:block">Dados Básicos</span>
                        </div>

                        <div class="w-8 h-0.5 bg-gradient-to-r from-primary-300 to-gray-300 transition-all duration-500" id="line-1-2"></div>

                        <div class="flex items-center step-indicator" data-step="2">
                            <div class="w-10 h-10 bg-gray-200 text-gray-500 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300" id="step-2-indicator">
                                <i class="fas fa-map-marker-alt text-xs"></i>
                            </div>
                            <span class="ml-3 text-sm font-medium text-gray-500 hidden sm:block">Localização</span>
                        </div>

                        <div class="w-8 h-0.5 bg-gray-300 transition-all duration-500" id="line-2-3"></div>

                        <div class="flex items-center step-indicator" data-step="3">
                            <div class="w-10 h-10 bg-gray-200 text-gray-500 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300" id="step-3-indicator">
                                <i class="fas fa-cog text-xs"></i>
                            </div>
                            <span class="ml-3 text-sm font-medium text-gray-500 hidden sm:block">Configurações</span>
                        </div>

                        <div class="w-8 h-0.5 bg-gray-300 transition-all duration-500" id="line-3-4"></div>

                        <div class="flex items-center step-indicator" data-step="4">
                            <div class="w-10 h-10 bg-gray-200 text-gray-500 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300" id="step-4-indicator">
                                <i class="fas fa-file-alt text-xs"></i>
                            </div>
                            <span class="ml-3 text-sm font-medium text-gray-500 hidden sm:block">Regulamento</span>
                        </div>

                        <div class="w-8 h-0.5 bg-gray-300 transition-all duration-500" id="line-4-5"></div>

                        <div class="flex items-center step-indicator" data-step="5">
                            <div class="w-10 h-10 bg-gray-200 text-gray-500 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300" id="step-5-indicator">
                                <i class="fas fa-check text-xs"></i>
                            </div>
                            <span class="ml-3 text-sm font-medium text-gray-500 hidden sm:block">Finalizar</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulário -->
        <form id="form-criar-evento" class="space-y-8">
            <!-- Etapa 1: Dados Básicos -->
            <div id="step-1" class="step-content">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-lg font-semibold text-gray-900">Informações Básicas do Evento</h3>
                        <p class="text-gray-600">Preencha as informações principais do seu evento</p>
                    </div>
                    <div class="card-body space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nome do Evento *</label>
                                <input type="text" style="text-transform: uppercase;" name="nome" id="nome" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                    placeholder="Ex: III Corrida Sauim de Coleira">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
                                <textarea name="descricao" id="descricao" rows="4"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                    placeholder="Descreva o evento, objetivos, público-alvo..."></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Data de Início das Inscrições *</label>
                                <input type="date" name="data_inicio" id="data_inicio" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Data de Fim das Inscrições</label>
                                <input type="date" name="data_fim" id="data_fim"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Hora de Início *</label>
                                <input type="time" name="hora_inicio" id="hora_inicio" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Categoria</label>
                                <select name="categoria" id="categoria"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    <option value="corrida_rua">Corrida de Rua</option>
                                    <option value="caminhada">Caminhada</option>
                                    <option value="triatlo">Triatlo</option>
                                    <option value="ciclismo">Ciclismo</option>
                                    <option value="natacao">Natação</option>
                                    <option value="outros">Outros</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Gênero</label>
                                <select name="genero" id="genero"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    <option value="">Selecione</option>
                                    <option value="masculino">Masculino</option>
                                    <option value="feminino">Feminino</option>
                                    <option value="misto">Misto</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status do Evento</label>
                                <div class="flex items-center space-x-4">
                                    <!-- Switch para Ativo/Inativo -->
                                    <div class="flex items-center">
                                        <label class="flex items-center cursor-pointer">
                                            <input type="checkbox" name="status_ativo" id="status_ativo"
                                                class="sr-only toggle-switch" value="1">
                                            <div class="relative">
                                                <div class="w-12 h-6 bg-gray-300 rounded-full shadow-inner transition-colors duration-300 ease-in-out"></div>
                                                <div class="toggle-dot absolute w-5 h-5 bg-white rounded-full shadow-lg transform transition-transform duration-300 ease-in-out -top-0.5 left-0.5"></div>
                                            </div>
                                            <span class="ml-3 text-sm font-medium text-gray-700" id="statusLabel">Rascunho</span>
                                        </label>
                                    </div>

                                    <!-- Select para Status Detalhado -->
                                    <div class="flex-1">
                                        <select name="status" id="status" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200">
                                            <option value="rascunho">Rascunho</option>
                                            <option value="ativo">Ativo</option>
                                            <option value="pausado">Pausado</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Etapa 2: Localização -->
            <div id="step-2" class="step-content hidden">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-lg font-semibold text-gray-900">Localização do Evento</h3>
                        <p class="text-gray-600">Informe onde será realizado o evento</p>
                    </div>
                    <div class="card-body space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Local/Nome do Local *</label>
                                <input type="text" name="local" id="local" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                    placeholder="Ex: Parque do Mindu, UEA-EST">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Logradouro</label>
                                <input type="text" name="logradouro" id="logradouro"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                    placeholder="Ex: Av. Darcy Vargas">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Número</label>
                                <input type="text" name="numero" id="numero"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                    placeholder="Ex: 1200">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">CEP</label>
                                <input type="text" name="cep" id="cep"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                    placeholder="Ex: 69050-010">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Cidade *</label>
                                <input type="text" name="cidade" id="cidade" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                    placeholder="Ex: Manaus">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Estado *</label>
                                <select name="estado" id="estado" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    <option value="">Selecione</option>
                                    <option value="AC">Acre</option>
                                    <option value="AL">Alagoas</option>
                                    <option value="AP">Amapá</option>
                                    <option value="AM">Amazonas</option>
                                    <option value="BA">Bahia</option>
                                    <option value="CE">Ceará</option>
                                    <option value="DF">Distrito Federal</option>
                                    <option value="ES">Espírito Santo</option>
                                    <option value="GO">Goiás</option>
                                    <option value="MA">Maranhão</option>
                                    <option value="MT">Mato Grosso</option>
                                    <option value="MS">Mato Grosso do Sul</option>
                                    <option value="MG">Minas Gerais</option>
                                    <option value="PA">Pará</option>
                                    <option value="PB">Paraíba</option>
                                    <option value="PR">Paraná</option>
                                    <option value="PE">Pernambuco</option>
                                    <option value="PI">Piauí</option>
                                    <option value="RJ">Rio de Janeiro</option>
                                    <option value="RN">Rio Grande do Norte</option>
                                    <option value="RS">Rio Grande do Sul</option>
                                    <option value="RO">Rondônia</option>
                                    <option value="RR">Roraima</option>
                                    <option value="SC">Santa Catarina</option>
                                    <option value="SP">São Paulo</option>
                                    <option value="SE">Sergipe</option>
                                    <option value="TO">Tocantins</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">País</label>
                                <input type="text" name="pais" id="pais" value="Brasil"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">URL do Mapa</label>
                                <input type="url" name="url_mapa" id="url_mapa"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                    placeholder="Ex: https://maps.google.com/...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Etapa 3: Configurações -->
            <div id="step-3" class="step-content hidden">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-lg font-semibold text-gray-900">Configurações do Evento</h3>
                        <p class="text-gray-600">Defina as configurações específicas do evento</p>
                    </div>
                    <div class="card-body space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Limite de Vagas</label>
                                <input type="number" name="limite_vagas" id="limite_vagas" min="1"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                    placeholder="Ex: 1000">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Hora de Fim das Inscrições</label>
                                <input type="time" name="hora_fim_inscricoes" id="hora_fim_inscricoes"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Data de Realização do Evento *</label>
                                <input type="date" name="data_realizacao" id="data_realizacao" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Hora da Corrida</label>
                                <input type="time" name="hora_corrida" id="hora_corrida"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Taxa de Setup (R$)</label>
                                <input type="number" name="taxa_setup" id="taxa_setup" step="0.01" min="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                    placeholder="Ex: 129.90">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Percentual de Repasse (%)</label>
                                <input type="number" name="percentual_repasse" id="percentual_repasse" step="0.01" min="0" max="100"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                    placeholder="Ex: 5.00">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Taxa Gratuitas (R$)</label>
                                <input type="number" name="taxa_gratuitas" id="taxa_gratuitas" step="0.01" min="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                    placeholder="Ex: 2.50">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Taxa Pagas (R$)</label>
                                <input type="number" name="taxa_pagas" id="taxa_pagas" step="0.01" min="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                    placeholder="Ex: 5.00">
                            </div>

                            <div class="md:col-span-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="exibir_retirada_kit" id="exibir_retirada_kit" value="1"
                                        class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                    <span class="ml-2 text-sm text-gray-700">Exibir seção de retirada de kits</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Etapa 4: Regulamento -->
            <div id="step-4" class="step-content hidden">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-lg font-semibold text-gray-900">Regulamento e Documentos</h3>
                        <p class="text-gray-600">Adicione o regulamento e outros documentos do evento</p>
                    </div>
                    <div class="card-body space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Termo de Responsabilidade (para inscritos)</label>
                            <textarea name="regulamento" id="regulamento" rows="8"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                placeholder="Digite o termo de responsabilidade que os participantes devem aceitar..."></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Regulamento do Evento (Upload)</label>
                            <input type="file" name="regulamento_arquivo" id="regulamento_arquivo" accept=".pdf,.doc,.docx"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <div id="preview-regulamento" class="mt-2 p-3 bg-gray-50 rounded-lg border hidden">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-file-alt text-blue-500"></i>
                                    <span class="text-sm text-gray-700" id="regulamento-nome"></span>
                                </div>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">Formatos aceitos: PDF, DOC, DOCX. Tamanho máximo: 10MB</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Imagem do Evento</label>
                            <input type="file" name="imagem" id="imagem" accept="image/*"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <div id="preview-imagem-evento" class="mt-2 w-28 h-28 rounded overflow-hidden border flex items-center justify-center text-gray-400"></div>
                            <p class="text-sm text-gray-500 mt-1">Formatos aceitos: JPG, PNG, WEBP. Tamanho máximo: 5MB</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Etapa 5: Resumo -->
            <div id="step-5" class="step-content hidden">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-lg font-semibold text-gray-900">Resumo do Evento</h3>
                        <p class="text-gray-600">Revise as informações antes de criar o evento</p>
                    </div>
                    <div class="card-body">
                        <div id="resumo-evento" class="space-y-4">
                            <!-- Resumo será preenchido via JavaScript -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botões de Navegação -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                    <button type="button" id="btn-anterior" class="btn-secondary hidden group">
                        <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform duration-200"></i>
                        <span>Anterior</span>
                    </button>

                    <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4 w-full sm:w-auto">
                        <button type="button" id="btn-proximo" class="btn-primary group">
                            <span>Próximo</span>
                            <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform duration-200"></i>
                        </button>

                        <button type="submit" id="btn-criar" class="btn-success hidden group">
                            <i class="fas fa-save mr-2 group-hover:rotate-12 transition-transform duration-200"></i>
                            <span>Criar Evento</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="../../js/organizador-criar-evento.js"></script>

<script>
    // Controle do Switch de Status
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOMContentLoaded no criar.php');
        const statusToggle = document.getElementById('status_ativo');
        const statusSelect = document.getElementById('status');
        const statusLabel = document.getElementById('statusLabel');

        // Sincronizar switch com select
        function syncStatus() {
            if (statusToggle.checked) {
                statusSelect.value = 'ativo';
                statusLabel.textContent = 'Ativo';
                statusLabel.style.color = '#0b4340';
                statusLabel.style.fontWeight = '600';
            } else {
                statusSelect.value = 'rascunho';
                statusLabel.textContent = 'Rascunho';
                statusLabel.style.color = '#6b7280';
                statusLabel.style.fontWeight = '500';
            }
        }

        // Event listeners
        statusToggle.addEventListener('change', syncStatus);
        statusSelect.addEventListener('change', function() {
            if (this.value === 'ativo') {
                statusToggle.checked = true;
                statusLabel.textContent = 'Ativo';
                statusLabel.style.color = '#0b4340';
                statusLabel.style.fontWeight = '600';
            } else {
                statusToggle.checked = false;
                statusLabel.textContent = 'Rascunho';
                statusLabel.style.color = '#6b7280';
                statusLabel.style.fontWeight = '500';
            }
        });

        // Inicializar estado
        syncStatus();

        // Preview do arquivo de regulamento
        const regulamentoInput = document.getElementById('regulamento_arquivo');
        const previewRegulamento = document.getElementById('preview-regulamento');
        const regulamentoNome = document.getElementById('regulamento-nome');

        regulamentoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validar tamanho (10MB)
                if (file.size > 10 * 1024 * 1024) {
                    alert('Arquivo muito grande. Tamanho máximo: 10MB');
                    this.value = '';
                    return;
                }

                // Validar extensão
                const allowedExtensions = ['pdf', 'doc', 'docx'];
                const fileExtension = file.name.split('.').pop().toLowerCase();
                if (!allowedExtensions.includes(fileExtension)) {
                    alert('Formato não suportado. Use: PDF, DOC ou DOCX');
                    this.value = '';
                    return;
                }

                regulamentoNome.textContent = file.name;
                previewRegulamento.classList.remove('hidden');
            } else {
                previewRegulamento.classList.add('hidden');
            }
        });
    });
</script>
