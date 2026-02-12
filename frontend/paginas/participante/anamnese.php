<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: /frontend/auth/login.php');
    exit();
}

$inscricao_id = $_GET['inscricao_id'] ?? null;
if (!$inscricao_id) {
    header('Location: index.php?page=meus-treinos');
    exit();
}
?>

<div class="container mx-auto p-4 md:p-8 max-w-3xl">
    <h1 class="text-3xl font-bold mb-2">Anamnese para Treino</h1>
    <p class="text-gray-600 mb-8">Preencha os dados abaixo para receber um treino personalizado</p>

    <!-- Termos de responsabilidade (anamnese) -->
    <div id="termos-anamnese-container" class="mb-6 hidden">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
            <h3 class="font-semibold text-gray-900 mb-2">Termo de Responsabilidade</h3>
            <p class="text-sm text-gray-600 mb-3">Leia atentamente antes de preencher a anamnese:</p>
            <div id="termos-anamnese-conteudo" class="max-h-48 overflow-y-auto text-sm text-gray-700 bg-white rounded p-4 border border-blue-100 mb-3 prose prose-sm max-w-none"></div>
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" id="aceite-termos-anamnese" required class="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="text-sm text-gray-700">Li e concordo com o termo de responsabilidade pelas informações fornecidas na anamnese</span>
            </label>
        </div>
    </div>

    <form id="form-anamnese" class="bg-white rounded-lg shadow-md p-6 space-y-6">
        <input type="hidden" id="inscricao_id" value="<?php echo htmlspecialchars($inscricao_id); ?>">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="peso" class="block text-sm font-medium text-gray-700 mb-2">
                    Peso (kg) <span class="text-red-500">*</span>
                </label>
                <input type="number" id="peso" name="peso" step="0.1" min="30" max="300" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div>
                <label for="altura" class="block text-sm font-medium text-gray-700 mb-2">
                    Altura (cm) <span class="text-red-500">*</span>
                </label>
                <input type="number" id="altura" name="altura" min="100" max="250" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                IMC Calculado
            </label>
            <div id="imc-display" class="px-4 py-2 bg-gray-100 rounded-lg text-gray-600">
                Preencha peso e altura para calcular
            </div>
        </div>

        <div>
            <label for="nivel_condicionamento" class="block text-sm font-medium text-gray-700 mb-2">
                Nível de Condicionamento <span class="text-red-500">*</span>
            </label>
            <select id="nivel_condicionamento" name="nivel_condicionamento" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Selecione...</option>
                <option value="iniciante">Iniciante (nunca correu ou parou há muito tempo)</option>
                <option value="intermediario">Intermediário (corre ocasionalmente)</option>
                <option value="avancado">Avançado (corre regularmente)</option>
            </select>
        </div>

        <div>
            <label for="historico_corridas" class="block text-sm font-medium text-gray-700 mb-2">
                Histórico de Corridas
            </label>
            <textarea id="historico_corridas" name="historico_corridas" rows="3"
                      placeholder="Ex: Já participei de 2 corridas de 5km, última há 6 meses..."
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
        </div>

        <div>
            <label for="limitacoes_fisicas" class="block text-sm font-medium text-gray-700 mb-2">
                Limitações Físicas
            </label>
            <textarea id="limitacoes_fisicas" name="limitacoes_fisicas" rows="3"
                      placeholder="Ex: Dor no joelho direito, problemas nas costas..."
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
        </div>

        <div>
            <label for="objetivo_corrida" class="block text-sm font-medium text-gray-700 mb-2">
                Objetivo com esta Corrida
            </label>
            <textarea id="objetivo_corrida" name="objetivo_corrida" rows="2"
                      placeholder="Ex: Completar a corrida, melhorar meu tempo pessoal..."
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
        </div>

        <div class="flex gap-4">
            <button type="submit" id="btn-salvar" class="flex-1 bg-blue-600 text-white font-semibold py-3 px-6 rounded-lg hover:bg-blue-700 transition-colors">
                Salvar Anamnese
            </button>
            <a href="?page=meus-treinos" class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Cancelar
            </a>
        </div>
    </form>

    <div id="mensagem-sucesso" class="hidden mt-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
        <p class="font-semibold">Anamnese salva com sucesso!</p>
        <p class="mt-2">Agora você pode gerar seu treino personalizado.</p>
        <button id="btn-gerar-treino" class="mt-4 bg-green-600 text-white font-semibold py-2 px-6 rounded-lg hover:bg-green-700 transition-colors">
            Gerar Treino
        </button>
    </div>
</div>

<script type="module">
import { salvarAnamnese, gerarTreino, buscarTermosTreino } from '../../js/participante/treinos.js';

document.addEventListener('DOMContentLoaded', async function() {
    const form = document.getElementById('form-anamnese');
    const pesoInput = document.getElementById('peso');
    const alturaInput = document.getElementById('altura');
    const imcDisplay = document.getElementById('imc-display');
    const btnGerarTreino = document.getElementById('btn-gerar-treino');
    const mensagemSucesso = document.getElementById('mensagem-sucesso');
    const inscricaoId = document.getElementById('inscricao_id').value;
    const termosContainer = document.getElementById('termos-anamnese-container');
    const termosConteudo = document.getElementById('termos-anamnese-conteudo');
    const aceiteTermosCheckbox = document.getElementById('aceite-termos-anamnese');

    // Carregar termos de anamnese
    let termosAnamnese = null;
    try {
        const apiBase = window.API_BASE || (window.location.pathname.indexOf('/frontend/') > 0 ? window.location.pathname.slice(0, window.location.pathname.indexOf('/frontend/')) : '');
        const url = `${apiBase}/api/inscricao/get_termos.php?tipo=anamnese`;
        const res = await fetch(url);
        const data = await res.json();
        if (data.success && data.termos && data.termos.conteudo) {
            termosAnamnese = data.termos;
            termosConteudo.innerHTML = data.termos.conteudo;
            termosContainer.classList.remove('hidden');
            aceiteTermosCheckbox.required = true;
        } else {
            aceiteTermosCheckbox.required = false;
        }
    } catch (e) {
        console.warn('Termos anamnese não carregados:', e);
        aceiteTermosCheckbox.required = false;
    }

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

    pesoInput.addEventListener('input', calcularIMC);
    alturaInput.addEventListener('input', calcularIMC);

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const btnSalvar = document.getElementById('btn-salvar');
        btnSalvar.disabled = true;
        btnSalvar.textContent = 'Salvando...';

        const dados = {
            inscricao_id: inscricaoId,
            peso: parseFloat(pesoInput.value),
            altura: parseInt(alturaInput.value),
            nivel_condicionamento: document.getElementById('nivel_condicionamento').value,
            historico_corridas: document.getElementById('historico_corridas').value,
            limitacoes_fisicas: document.getElementById('limitacoes_fisicas').value,
            objetivo_corrida: document.getElementById('objetivo_corrida').value
        };
        if (termosAnamnese && aceiteTermosCheckbox) {
            dados.aceite_termos_anamnese = aceiteTermosCheckbox.checked ? 1 : 0;
            dados.termos_id_anamnese = termosAnamnese.id;
        }

        try {
            const resultado = await salvarAnamnese(dados);
            if (resultado.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Anamnese Salva!',
                        text: 'Sua anamnese foi salva com sucesso. Agora você pode gerar seu treino personalizado.',
                        confirmButtonText: 'OK'
                    });
                }
                mensagemSucesso.classList.remove('hidden');
                form.classList.add('hidden');
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
                btnSalvar.disabled = false;
                btnSalvar.textContent = 'Salvar Anamnese';
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
            btnSalvar.disabled = false;
            btnSalvar.textContent = 'Salvar Anamnese';
        }
    });

    btnGerarTreino.addEventListener('click', async function() {
        btnGerarTreino.disabled = true;
        btnGerarTreino.textContent = 'Gerando treino...';

        let termosIdTreino = null;
        try {
            const termosTreino = await buscarTermosTreino();
            if (termosTreino && typeof Swal !== 'undefined') {
                btnGerarTreino.disabled = false;
                btnGerarTreino.textContent = 'Gerar Treino';
                const confirmResult = await Swal.fire({
                    icon: 'info',
                    title: 'Termo de Responsabilidade',
                    html: `
                        <div class="text-left max-h-64 overflow-y-auto mb-4 p-4 bg-gray-50 rounded-lg text-sm prose prose-sm max-w-none">${termosTreino.conteudo}</div>
                        <label class="flex items-start gap-3 cursor-pointer mt-4">
                            <input type="checkbox" id="swal-aceite-termos-treino" class="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600">
                            <span class="text-sm">Li e concordo com o termo de responsabilidade pela prática de treinos</span>
                        </label>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Aceitar e Gerar Treino',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#10b981',
                    preConfirm: () => {
                        const cb = document.getElementById('swal-aceite-termos-treino');
                        if (!cb || !cb.checked) {
                            Swal.showValidationMessage('É necessário aceitar o termo para continuar.');
                            return false;
                        }
                        return true;
                    }
                });
                if (!confirmResult.isConfirmed) return;
                termosIdTreino = termosTreino.id;
                btnGerarTreino.disabled = true;
                btnGerarTreino.textContent = 'Gerando treino...';
            } else if (termosTreino) {
                termosIdTreino = termosTreino.id;
            }
        } catch (e) {
            console.warn('Erro ao buscar termos treino:', e);
        }

        try {
            const resultado = await gerarTreino(inscricaoId, termosIdTreino ? { termos_id_treino: termosIdTreino } : {});
            if (resultado.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Treino Gerado!',
                        text: 'Seu treino personalizado foi criado com sucesso.',
                        confirmButtonText: 'Ver Treino'
                    }).then(() => {
                        window.location.href = `?page=ver-treino&inscricao_id=${inscricaoId}`;
                    });
                } else {
                    alert('Treino gerado com sucesso!');
                    window.location.href = `?page=ver-treino&inscricao_id=${inscricaoId}`;
                }
            } else {
                const mensagem = resultado.message || 'Erro desconhecido ao gerar treino';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro ao Gerar Treino',
                        text: mensagem
                    });
                } else {
                    alert('Erro ao gerar treino: ' + mensagem);
                }
                btnGerarTreino.disabled = false;
                btnGerarTreino.textContent = 'Gerar Treino';
            }
        } catch (error) {
            const mensagem = error.message || 'Erro desconhecido';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro ao Gerar Treino',
                    text: mensagem
                });
            } else {
                alert('Erro ao gerar treino: ' + mensagem);
            }
            btnGerarTreino.disabled = false;
            btnGerarTreino.textContent = 'Gerar Treino';
        }
    });
});
</script>

