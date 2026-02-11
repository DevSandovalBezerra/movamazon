<?php
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    header("Location: index.php?page=meu-perfil");
    exit;
}
?>

<section class="py-8 px-4 max-w-7xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-2">Meu Perfil</h1>
    <p class="text-gray-600 mb-8">Gerencie suas informações pessoais</p>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Conteúdo Principal com Abas -->
        <div class="lg:col-span-2">
            <!-- Sistema de Abas -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="border-b border-gray-200">
                    <nav class="flex space-x-1 px-4" aria-label="Tabs">
                        <button type="button" data-aba="perfil" id="tab-perfil"
                            class="tab-button active px-4 py-3 text-sm font-medium text-gray-700 border-b-2 border-brand-green">
                            Perfil
                        </button>
                        <button type="button" data-aba="anamnese" id="tab-anamnese"
                            class="tab-button px-4 py-3 text-sm font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700 hover:border-gray-300">
                            Anamnese
                        </button>
                    </nav>
                </div>

                <!-- Conteúdo da Aba Perfil -->
                <div id="conteudo-perfil" class="tab-content p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações Pessoais</h3>
                    
                    <!-- Mensagem de sucesso/erro -->
                    <div id="perfil-mensagem" class="hidden mb-4 p-4 rounded-lg"></div>
                    
                    <form id="form-perfil" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">Nome Completo <span class="text-red-500">*</span></label>
                                <input type="text" id="perfil-nome" name="nome_completo" class="form-input" value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>" placeholder="Seu nome completo" required>
                            </div>
                            <div>
                                <label class="form-label">Email</label>
                                <input type="email" id="perfil-email" class="form-input" value="<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>" readonly>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">CPF <span class="text-red-500">*</span></label>
                                <input type="text" id="perfil-cpf" name="cpf" class="form-input" placeholder="000.000.000-00" maxlength="14" required>
                            </div>
                            <div>
                                <label class="form-label">Telefone</label>
                                <input type="tel" id="perfil-telefone" name="telefone" class="form-input" placeholder="(99) 99999-9999">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">Celular</label>
                                <input type="tel" id="perfil-celular" name="celular" class="form-input" placeholder="(99) 99999-9999">
                            </div>
                            <div>
                                <label class="form-label">Data de Nascimento</label>
                                <input type="date" id="perfil-data-nascimento" name="data_nascimento" class="form-input">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">Sexo</label>
                                <select id="perfil-sexo" name="sexo" class="form-input">
                                    <option value="">Selecione...</option>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Feminino">Feminino</option>
                                    <option value="Outro">Outro</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="form-label">CEP <span class="text-red-500">*</span></label>
                            <input type="text" id="perfil-cep" name="cep" class="form-input" placeholder="00000-000" maxlength="10" required>
                        </div>
                        <div>
                            <label class="form-label">Endereço <span class="text-red-500">*</span></label>
                            <input type="text" id="perfil-endereco" name="endereco" class="form-input" placeholder="Rua, Avenida, etc." required>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="form-label">Número <span class="text-red-500">*</span></label>
                                <input type="text" id="perfil-numero" name="numero" class="form-input" placeholder="123" required>
                            </div>
                            <div>
                                <label class="form-label">Complemento</label>
                                <input type="text" id="perfil-complemento" name="complemento" class="form-input" placeholder="Apto, Bloco, etc.">
                            </div>
                            <div>
                                <label class="form-label">Bairro <span class="text-red-500">*</span></label>
                                <input type="text" id="perfil-bairro" name="bairro" class="form-input" placeholder="Nome do bairro" required>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">Cidade <span class="text-red-500">*</span></label>
                                <input type="text" id="perfil-cidade" name="cidade" class="form-input" placeholder="Nome da cidade" required>
                            </div>
                            <div>
                                <label class="form-label">Estado <span class="text-red-500">*</span></label>
                                <select id="perfil-uf" name="uf" class="form-input" required>
                                    <option value="">Selecione o estado...</option>
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
                        </div>
                        <button type="submit" id="btn-salvar-perfil" class="btn-primary">Salvar Alterações</button>
                    </form>
                </div>

                <!-- Conteúdo da Aba Anamnese -->
                <div id="conteudo-anamnese" class="tab-content p-6 hidden">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Anamnese</h3>
                    <p class="text-sm text-gray-600 mb-6">Preencha suas informações de saúde e condicionamento físico</p>

                    <form id="form-anamnese-perfil" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="anamnese-peso" class="block text-sm font-medium text-gray-700 mb-2">
                                    Peso (kg) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" id="anamnese-peso" name="peso" step="0.1" min="30" max="300" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>

                            <div>
                                <label for="anamnese-altura" class="block text-sm font-medium text-gray-700 mb-2">
                                    Altura (cm) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" id="anamnese-altura" name="altura" min="100" max="250" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                IMC Calculado
                            </label>
                            <div id="anamnese-imc-display" class="px-4 py-2 bg-gray-100 rounded-lg text-gray-600">
                                Preencha peso e altura para calcular
                            </div>
                        </div>

                        <div>
                            <label for="anamnese-nivel" class="block text-sm font-medium text-gray-700 mb-2">
                                Nível de Atividade <span class="text-red-500">*</span>
                            </label>
                            <select id="anamnese-nivel" name="nivel_atividade" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Selecione...</option>
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                            </select>
                        </div>

                        <div>
                            <label for="anamnese-historico" class="block text-sm font-medium text-gray-700 mb-2">
                                Histórico de Corridas
                            </label>
                            <textarea id="anamnese-historico" name="historico_corridas" rows="3"
                                placeholder="Ex: Já participei de 2 corridas de 5km, última há 6 meses..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                        </div>

                        <div>
                            <label for="anamnese-limitacoes" class="block text-sm font-medium text-gray-700 mb-2">
                                Limitações Físicas
                            </label>
                            <textarea id="anamnese-limitacoes" name="limitacoes_fisicas" rows="3"
                                placeholder="Ex: Dor no joelho direito, problemas nas costas..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                        </div>

                        <div>
                            <label for="anamnese-doencas" class="block text-sm font-medium text-gray-700 mb-2">
                                Doenças Preexistentes
                            </label>
                            <textarea id="anamnese-doencas" name="doencas_preexistentes" rows="2"
                                placeholder="Ex: Hipertensão, diabetes..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                        </div>

                        <div>
                            <label for="anamnese-medicamentos" class="block text-sm font-medium text-gray-700 mb-2">
                                Uso de Medicamentos
                            </label>
                            <textarea id="anamnese-medicamentos" name="uso_medicamentos" rows="2"
                                placeholder="Ex: Losartana 50mg, 1x ao dia..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                        </div>

                        <div>
                            <label for="anamnese-objetivo" class="block text-sm font-medium text-gray-700 mb-2">
                                Objetivo Principal <span class="text-red-500">*</span>
                            </label>
                            <select id="anamnese-objetivo" name="objetivo_principal" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Selecione...</option>
                                <option value="perda_peso">Perda de Peso</option>
                                <option value="ganho_massa">Ganho de Massa</option>
                                <option value="condicionamento">Condicionamento Físico</option>
                                <option value="saude">Saúde Geral</option>
                                <option value="reabilitacao">Reabilitação</option>
                                <option value="preparacao_corrida">Preparação para Corrida</option>
                            </select>
                        </div>

                        <div>
                            <label for="anamnese-preferencias" class="block text-sm font-medium text-gray-700 mb-2">
                                Preferências de Atividades
                            </label>
                            <textarea id="anamnese-preferencias" name="preferencias_atividades" rows="2"
                                placeholder="Ex: Corrida, caminhada, ciclismo..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                        </div>

                        <div>
                            <label for="anamnese-horarios" class="block text-sm font-medium text-gray-700 mb-2">
                                Disponibilidade de Horários
                            </label>
                            <textarea id="anamnese-horarios" name="disponibilidade_horarios" rows="2"
                                placeholder="Ex: Manhãs (6h-8h), Fins de semana..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                        </div>

                        <div class="flex gap-4">
                            <button type="submit" id="btn-salvar-anamnese" class="flex-1 bg-blue-600 text-white font-semibold py-3 px-6 rounded-lg hover:bg-blue-700 transition-colors">
                                Salvar Anamnese
                            </button>
                        </div>
                    </form>

                    <div id="anamnese-mensagem-sucesso" class="hidden mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                        <p class="font-semibold">Anamnese salva com sucesso!</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Avatar e Estatísticas -->
        <div class="space-y-6">
            <!-- Avatar -->
            <div class="card text-center">
                <div class="w-24 h-24 bg-brand-green rounded-full flex items-center justify-center mx-auto mb-4 overflow-hidden">
                    <img id="avatar-imagem" src="" alt="Avatar" class="hidden w-full h-full object-cover">
                    <span id="avatar-inicial" class="text-white font-bold text-2xl"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?></span>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuário'); ?></h3>
                <p class="text-gray-600 text-sm">Participante desde <?php echo date('Y'); ?></p>
                <input type="file" id="input-foto" accept="image/*" class="hidden">
                <button type="button" id="btn-alterar-foto" class="btn-secondary mt-4">Alterar Foto</button>
            </div>

            <!-- Estatísticas -->
            <div class="card">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Estatísticas</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Inscrições</span>
                        <span class="font-semibold">0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Eventos Concluídos</span>
                        <span class="font-semibold">0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Distância Total</span>
                        <span class="font-semibold">0 km</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script type="module">
    import {
        carregarAnamneseGeral,
        salvarAnamneseGeral
    } from '../../js/participante/perfil.js';

    function mostrarAba(aba) {
        const tabs = document.querySelectorAll('.tab-button');
        const conteudos = document.querySelectorAll('.tab-content');

        tabs.forEach(tab => {
            tab.classList.remove('active', 'border-brand-green', 'text-gray-700');
            tab.classList.add('text-gray-500', 'border-transparent');
        });

        conteudos.forEach(conteudo => {
            conteudo.classList.add('hidden');
        });

        if (aba === 'perfil') {
            const tabPerfil = document.getElementById('tab-perfil');
            const conteudoPerfil = document.getElementById('conteudo-perfil');
            if (tabPerfil) {
                tabPerfil.classList.add('active', 'border-brand-green', 'text-gray-700');
                tabPerfil.classList.remove('text-gray-500', 'border-transparent');
            }
            if (conteudoPerfil) {
                conteudoPerfil.classList.remove('hidden');
            }
        } else if (aba === 'anamnese') {
            const tabAnamnese = document.getElementById('tab-anamnese');
            const conteudoAnamnese = document.getElementById('conteudo-anamnese');
            if (tabAnamnese) {
                tabAnamnese.classList.add('active', 'border-brand-green', 'text-gray-700');
                tabAnamnese.classList.remove('text-gray-500', 'border-transparent');
            }
            if (conteudoAnamnese) {
                conteudoAnamnese.classList.remove('hidden');
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const tabButtons = document.querySelectorAll('.tab-button');
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const aba = this.getAttribute('data-aba');
                mostrarAba(aba);
            });
        });

        const pesoInput = document.getElementById('anamnese-peso');
        const alturaInput = document.getElementById('anamnese-altura');
        const imcDisplay = document.getElementById('anamnese-imc-display');
        const formAnamnese = document.getElementById('form-anamnese-perfil');
        const btnSalvar = document.getElementById('btn-salvar-anamnese');
        const mensagemSucesso = document.getElementById('anamnese-mensagem-sucesso');

        function calcularIMC() {
            const peso = parseFloat(pesoInput.value);
            const altura = parseInt(alturaInput.value);

            if (peso && altura && altura > 0) {
                const alturaMetros = altura / 100;
                const imc = (peso / (alturaMetros * alturaMetros)).toFixed(2);
                imcDisplay.textContent = `IMC: ${imc}`;
            } else {
                imcDisplay.textContent = 'Preencha peso e altura para calcular';
            }
        }

        if (pesoInput && alturaInput) {
            pesoInput.addEventListener('input', calcularIMC);
            alturaInput.addEventListener('input', calcularIMC);
        }

        if (formAnamnese) {
            formAnamnese.addEventListener('submit', async function(e) {
                e.preventDefault();

                btnSalvar.disabled = true;
                btnSalvar.textContent = 'Salvando...';

                const dados = {
                    peso: parseFloat(pesoInput.value),
                    altura: parseInt(alturaInput.value),
                    nivel_atividade: document.getElementById('anamnese-nivel').value,
                    historico_corridas: document.getElementById('anamnese-historico').value,
                    limitacoes_fisicas: document.getElementById('anamnese-limitacoes').value,
                    objetivo_principal: document.getElementById('anamnese-objetivo').value,
                    preferencias_atividades: document.getElementById('anamnese-preferencias').value,
                    disponibilidade_horarios: document.getElementById('anamnese-horarios').value,
                    doencas_preexistentes: document.getElementById('anamnese-doencas').value,
                    uso_medicamentos: document.getElementById('anamnese-medicamentos').value
                };

                try {
                    const resultado = await salvarAnamneseGeral(dados);
                    if (resultado.success) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Anamnese Salva!',
                                text: resultado.message || 'Sua anamnese foi salva com sucesso.',
                                confirmButtonText: 'OK'
                            });
                        }
                        mensagemSucesso.classList.remove('hidden');
                        setTimeout(() => {
                            mensagemSucesso.classList.add('hidden');
                        }, 3000);
                    } else {
                        const mensagem = resultado.message || 'Erro desconhecido ao salvar anamnese';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro ao Salvar',
                                text: mensagem
                            });
                        } else {
                            alert('Erro ao salvar: ' + mensagem);
                        }
                    }
                } catch (error) {
                    const mensagem = error.message || 'Erro desconhecido';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro ao Salvar',
                            text: mensagem
                        });
                    } else {
                        alert('Erro ao salvar anamnese: ' + mensagem);
                    }
                } finally {
                    btnSalvar.disabled = false;
                    btnSalvar.textContent = 'Salvar Anamnese';
                }
            });
        }

        carregarAnamneseGeral();
        
        // Carregar dados do perfil
        carregarPerfil();
        
        // Formulário de perfil
        const formPerfil = document.getElementById('form-perfil');
        const btnSalvarPerfil = document.getElementById('btn-salvar-perfil');
        const mensagemPerfil = document.getElementById('perfil-mensagem');
        
        if (formPerfil) {
            formPerfil.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                btnSalvarPerfil.disabled = true;
                btnSalvarPerfil.textContent = 'Salvando...';
                mensagemPerfil.classList.add('hidden');
                
                // Validação dos campos obrigatórios para boleto
                const nomeCompleto = document.getElementById('perfil-nome').value.trim();
                const cpfValue = document.getElementById('perfil-cpf').value.trim().replace(/[^0-9]/g, '');
                const cep = document.getElementById('perfil-cep').value.trim().replace(/[^0-9]/g, '');
                const endereco = document.getElementById('perfil-endereco').value.trim();
                const numero = document.getElementById('perfil-numero').value.trim();
                const bairro = document.getElementById('perfil-bairro').value.trim();
                const cidade = document.getElementById('perfil-cidade').value.trim();
                const uf = document.getElementById('perfil-uf').value.trim().toUpperCase();
                
                // Validar campos obrigatórios
                const camposObrigatorios = [];
                if (!nomeCompleto || nomeCompleto.length < 3) {
                    camposObrigatorios.push('Nome Completo');
                }
                if (!cpfValue || cpfValue.length !== 11) {
                    camposObrigatorios.push('CPF');
                }
                if (!cep || cep.length !== 8) {
                    camposObrigatorios.push('CEP');
                }
                if (!endereco) {
                    camposObrigatorios.push('Endereço');
                }
                if (!numero) {
                    camposObrigatorios.push('Número');
                }
                if (!bairro) {
                    camposObrigatorios.push('Bairro');
                }
                if (!cidade) {
                    camposObrigatorios.push('Cidade');
                }
                if (!uf || uf.length !== 2) {
                    camposObrigatorios.push('Estado (UF)');
                }
                
                if (camposObrigatorios.length > 0) {
                    const mensagem = 'Os seguintes campos são obrigatórios para pagamento com boleto:\n\n' + camposObrigatorios.join('\n');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Campos Obrigatórios',
                            text: mensagem,
                            confirmButtonText: 'OK'
                        });
                    } else {
                        alert(mensagem);
                    }
                    btnSalvarPerfil.disabled = false;
                    btnSalvarPerfil.textContent = 'Salvar Alterações';
                    return;
                }

                const dados = {
                    nome_completo: nomeCompleto,
                    cpf: cpfValue,
                    telefone: document.getElementById('perfil-telefone').value.trim(),
                    celular: document.getElementById('perfil-celular').value.trim(),
                    data_nascimento: document.getElementById('perfil-data-nascimento').value,
                    endereco: endereco,
                    numero: numero,
                    complemento: document.getElementById('perfil-complemento').value.trim(),
                    bairro: bairro,
                    cidade: cidade,
                    uf: uf,
                    cep: cep,
                    pais: 'Brasil',
                    sexo: document.getElementById('perfil-sexo').value
                };
                
                try {
                    const response = await fetch('../../../api/participante/update_perfil.php', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(dados)
                    });
                    
                    const resultado = await response.json();
                    
                    if (resultado.success) {
                        mensagemPerfil.className = 'mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg';
                        mensagemPerfil.textContent = 'Perfil atualizado com sucesso!';
                        mensagemPerfil.classList.remove('hidden');
                        
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Perfil Atualizado!',
                                text: 'Suas informações foram salvas com sucesso.',
                                confirmButtonText: 'OK'
                            });
                        }
                    } else {
                        mensagemPerfil.className = 'mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg';
                        mensagemPerfil.textContent = resultado.message || 'Erro ao atualizar perfil.';
                        mensagemPerfil.classList.remove('hidden');
                        
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro ao Salvar',
                                text: resultado.message || 'Erro ao atualizar perfil.'
                            });
                        }
                    }
                } catch (error) {
                    mensagemPerfil.className = 'mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg';
                    mensagemPerfil.textContent = 'Erro ao salvar: ' + error.message;
                    mensagemPerfil.classList.remove('hidden');
                    
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro ao Salvar',
                            text: error.message || 'Erro desconhecido.'
                        });
                    }
                } finally {
                    btnSalvarPerfil.disabled = false;
                    btnSalvarPerfil.textContent = 'Salvar Alterações';
                }
            });
        }
        
        // Upload de foto
        const btnAlterarFoto = document.getElementById('btn-alterar-foto');
        const inputFoto = document.getElementById('input-foto');
        
        if (btnAlterarFoto && inputFoto) {
            btnAlterarFoto.addEventListener('click', function() {
                inputFoto.click();
            });
            
            inputFoto.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    if (file.size > 5 * 1024 * 1024) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Arquivo muito grande',
                                text: 'A imagem deve ter no máximo 5MB.'
                            });
                        } else {
                            alert('A imagem deve ter no máximo 5MB.');
                        }
                        return;
                    }
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const avatarImagem = document.getElementById('avatar-imagem');
                        const avatarInicial = document.getElementById('avatar-inicial');
                        
                        if (avatarImagem && avatarInicial) {
                            avatarImagem.src = e.target.result;
                            avatarImagem.classList.remove('hidden');
                            avatarInicial.classList.add('hidden');
                        }
                    };
                    reader.readAsDataURL(file);
                    
                    const formData = new FormData();
                    formData.append('foto_perfil', file);
                    
                    fetch('../../../api/participante/update_perfil.php', {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin'
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Foto atualizada!',
                                    text: 'Sua foto de perfil foi atualizada com sucesso.',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            }
                            
                            if (result.usuario && result.usuario.foto_perfil) {
                                const avatarImagem = document.getElementById('avatar-imagem');
                                if (avatarImagem) {
                                    avatarImagem.src = '../../../' + result.usuario.foto_perfil;
                                }
                            }
                        } else {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erro ao enviar foto',
                                    text: result.message || 'Erro ao atualizar foto de perfil.'
                                });
                            }
                            
                            const avatarImagem = document.getElementById('avatar-imagem');
                            const avatarInicial = document.getElementById('avatar-inicial');
                            if (avatarImagem && avatarInicial) {
                                avatarImagem.classList.add('hidden');
                                avatarInicial.classList.remove('hidden');
                            }
                            inputFoto.value = '';
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro ao enviar foto',
                                text: 'Ocorreu um erro ao fazer upload da foto.'
                            });
                        }
                        
                        const avatarImagem = document.getElementById('avatar-imagem');
                        const avatarInicial = document.getElementById('avatar-inicial');
                        if (avatarImagem && avatarInicial) {
                            avatarImagem.classList.add('hidden');
                            avatarInicial.classList.remove('hidden');
                        }
                        inputFoto.value = '';
                    });
                }
            });
        }
        
        async function carregarPerfil() {
            try {
                const response = await fetch('../../../api/participante/get_perfil.php', {
                    credentials: 'same-origin'
                });
                
                if (!response.ok) {
                    console.error('Erro ao carregar perfil');
                    return;
                }
                
                const data = await response.json();
                
                if (data.success && data.usuario) {
                    const usuario = data.usuario;
                    
                    if (document.getElementById('perfil-nome')) {
                        document.getElementById('perfil-nome').value = usuario.nome_completo || '';
                    }
                    if (document.getElementById('perfil-cpf')) {
                        // Aplicar máscara ao CPF se existir
                        let cpf = usuario.documento || '';
                        if (cpf) {
                            // Aplicar máscara
                            cpf = cpf.replace(/\D/g, '');
                            if (cpf.length === 11) {
                                cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2');
                                cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2');
                                cpf = cpf.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                            }
                        }
                        document.getElementById('perfil-cpf').value = cpf;
                    }
                    if (document.getElementById('perfil-telefone')) {
                        document.getElementById('perfil-telefone').value = usuario.telefone || '';
                    }
                    if (document.getElementById('perfil-celular')) {
                        document.getElementById('perfil-celular').value = usuario.celular || '';
                    }
                    if (document.getElementById('perfil-data-nascimento')) {
                        document.getElementById('perfil-data-nascimento').value = usuario.data_nascimento || '';
                    }
                    if (document.getElementById('perfil-endereco')) {
                        document.getElementById('perfil-endereco').value = usuario.endereco || '';
                    }
                    if (document.getElementById('perfil-numero')) {
                        document.getElementById('perfil-numero').value = usuario.numero || '';
                    }
                    if (document.getElementById('perfil-complemento')) {
                        document.getElementById('perfil-complemento').value = usuario.complemento || '';
                    }
                    if (document.getElementById('perfil-bairro')) {
                        document.getElementById('perfil-bairro').value = usuario.bairro || '';
                    }
                    if (document.getElementById('perfil-cidade')) {
                        document.getElementById('perfil-cidade').value = usuario.cidade || '';
                    }
                    if (document.getElementById('perfil-uf')) {
                        document.getElementById('perfil-uf').value = usuario.uf || '';
                    }
                    if (document.getElementById('perfil-cep')) {
                        let cep = usuario.cep || '';
                        if (cep) {
                            // Aplicar máscara de CEP
                            cep = cep.replace(/\D/g, '');
                            if (cep.length === 8) {
                                cep = cep.replace(/(\d{5})(\d)/, '$1-$2');
                            }
                        }
                        document.getElementById('perfil-cep').value = cep;
                    }
                    if (document.getElementById('perfil-sexo')) {
                        document.getElementById('perfil-sexo').value = usuario.sexo || '';
                    }
                    
                    if (usuario.foto_perfil) {
                        const avatarImagem = document.getElementById('avatar-imagem');
                        const avatarInicial = document.getElementById('avatar-inicial');
                        if (avatarImagem && avatarInicial) {
                            avatarImagem.src = '../../../' + usuario.foto_perfil;
                            avatarImagem.classList.remove('hidden');
                            avatarInicial.classList.add('hidden');
                        }
                    }
                }
            } catch (error) {
                console.error('Erro ao carregar perfil:', error);
            }
        }

        // Máscara de CPF
        const campoCPF = document.getElementById('perfil-cpf');
        if (campoCPF) {
            campoCPF.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length <= 11) {
                    value = value.replace(/(\d{3})(\d)/, '$1.$2');
                    value = value.replace(/(\d{3})(\d)/, '$1.$2');
                    value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                    e.target.value = value;
                } else {
                    e.target.value = value.slice(0, 14);
                }
            });
        }

        // Máscara de CEP e busca ViaCEP
        const campoCEP = document.getElementById('perfil-cep');
        if (campoCEP) {
            // Máscara de CEP
            campoCEP.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length <= 8) {
                    value = value.replace(/(\d{5})(\d)/, '$1-$2');
                    e.target.value = value;
                } else {
                    e.target.value = value.slice(0, 9);
                }
            });

            // Busca ViaCEP quando CEP tiver 8 dígitos
            campoCEP.addEventListener('blur', function(e) {
                const cep = e.target.value.replace(/\D/g, '');
                if (cep.length === 8) {
                    buscarEnderecoPorCEP(cep);
                }
            });
        }

        // Função para buscar endereço via ViaCEP
        async function buscarEnderecoPorCEP(cep) {
            try {
                const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                const data = await response.json();
                
                if (!data.erro) {
                    if (document.getElementById('perfil-endereco')) {
                        document.getElementById('perfil-endereco').value = data.logradouro || '';
                    }
                    if (document.getElementById('perfil-bairro')) {
                        document.getElementById('perfil-bairro').value = data.bairro || '';
                    }
                    if (document.getElementById('perfil-cidade')) {
                        document.getElementById('perfil-cidade').value = data.localidade || '';
                    }
                    if (document.getElementById('perfil-uf')) {
                        document.getElementById('perfil-uf').value = data.uf || '';
                    }
                    // Focar no campo número após preencher
                    if (document.getElementById('perfil-numero')) {
                        document.getElementById('perfil-numero').focus();
                    }
                } else {
                    console.warn('CEP não encontrado');
                }
            } catch (error) {
                console.error('Erro ao buscar CEP:', error);
            }
        }
    });
</script>

<style>
    .tab-button {
        transition: all 0.2s;
    }

    .tab-button.active {
        color: rgb(11, 67, 64);
        border-bottom-color: rgb(11, 67, 64);
    }

    .tab-content {
        min-height: 400px;
    }
</style>