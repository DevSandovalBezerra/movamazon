// Detectar caminho base da API
if (!window.API_BASE) {
    (function () {
        var path = window.location.pathname || '';
        var idx = path.indexOf('/frontend/');
        window.API_BASE = idx > 0 ? path.slice(0, idx) : '';
    })();
}

const API_BASE_PARTICIPANTE = window.API_BASE ? `${window.API_BASE}/api/participante` : '/api/participante';

// Inicializar objeto global para armazenar dados dos exercícios
if (typeof window.exerciciosData === 'undefined') {
    window.exerciciosData = {};
}

// Função global para exibir detalhes do exercício em modal (definida no início para garantir disponibilidade)
window.mostrarDetalhesExercicio = function(exercicioId) {
    if (!exercicioId || !window.exerciciosData) {
        console.error('ID do exercício não fornecido ou dados não disponíveis');
        return;
    }
    
    const exercicio = window.exerciciosData[exercicioId];
    if (!exercicio) {
        console.error('Exercício não encontrado:', exercicioId);
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Exercício não encontrado.',
                confirmButtonColor: '#ef4444'
            });
        }
        return;
    }
    
    try {
        const nomeExercicio = exercicio.nome_item || exercicio.nome || exercicio.nome_exercicio || 'Exercício';
        const detalhes = exercicio.detalhes_item || exercicio.detalhes || '';
        
        let detalhesHtml = '<div class="text-left">';
        
        if (detalhes) {
            detalhesHtml += `<p class="mb-3 text-gray-700">${detalhes}</p>`;
        }
        
        detalhesHtml += '<div class="grid grid-cols-1 gap-2">';
        
        if (exercicio.fc_alvo) {
            detalhesHtml += `<div class="flex items-center gap-2"><span class="px-2 py-1 text-xs font-medium rounded bg-blue-100 text-blue-800">FC alvo:</span><span>${exercicio.fc_alvo}</span></div>`;
        }
        if (exercicio.tempo_execucao) {
            detalhesHtml += `<div class="flex items-center gap-2"><span class="px-2 py-1 text-xs font-medium rounded bg-green-100 text-green-800">Execução:</span><span>${exercicio.tempo_execucao}</span></div>`;
        }
        if (exercicio.tempo_recuperacao) {
            detalhesHtml += `<div class="flex items-center gap-2"><span class="px-2 py-1 text-xs font-medium rounded bg-yellow-100 text-yellow-800">Recuperação:</span><span>${exercicio.tempo_recuperacao}</span></div>`;
        }
        if (exercicio.tipo_recuperacao) {
            detalhesHtml += `<div class="flex items-center gap-2"><span class="px-2 py-1 text-xs font-medium rounded bg-purple-100 text-purple-800">Tipo:</span><span>${exercicio.tipo_recuperacao}</span></div>`;
        }
        if (exercicio.carga) {
            detalhesHtml += `<div class="flex items-center gap-2"><span class="px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-800">Carga:</span><span>${exercicio.carga}</span></div>`;
        }
        if (exercicio.distancia) {
            detalhesHtml += `<div class="flex items-center gap-2"><span class="px-2 py-1 text-xs font-medium rounded bg-pink-100 text-pink-800">Distância:</span><span>${exercicio.distancia}</span></div>`;
        }
        if (exercicio.velocidade) {
            detalhesHtml += `<div class="flex items-center gap-2"><span class="px-2 py-1 text-xs font-medium rounded bg-cyan-100 text-cyan-800">Velocidade:</span><span>${exercicio.velocidade}</span></div>`;
        }
        if (exercicio.cadencia) {
            detalhesHtml += `<div class="flex items-center gap-2"><span class="px-2 py-1 text-xs font-medium rounded bg-orange-100 text-orange-800">Cadência:</span><span>${exercicio.cadencia}</span></div>`;
        }
        if (exercicio.tipo_contracao) {
            detalhesHtml += `<div class="flex items-center gap-2"><span class="px-2 py-1 text-xs font-medium rounded bg-lime-100 text-lime-800">Contração:</span><span>${exercicio.tipo_contracao}</span></div>`;
        }
        if (exercicio.angulo_articular) {
            detalhesHtml += `<div class="flex items-center gap-2"><span class="px-2 py-1 text-xs font-medium rounded bg-teal-100 text-teal-800">Ângulo:</span><span>${exercicio.angulo_articular}</span></div>`;
        }
        if (exercicio.observacoes) {
            detalhesHtml += `<div class="mt-2 pt-2 border-t"><span class="px-2 py-1 text-xs font-medium rounded bg-red-100 text-red-800">Observações:</span><p class="mt-1 text-sm text-gray-700">${exercicio.observacoes}</p></div>`;
        }
        
        detalhesHtml += '</div></div>';
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: nomeExercicio,
                html: detalhesHtml,
                width: '600px',
                confirmButtonText: 'Fechar',
                confirmButtonColor: '#3b82f6',
                showCloseButton: true,
                customClass: {
                    popup: 'text-left'
                }
            });
        } else {
            alert(`${nomeExercicio}\n\n${detalhes}\n\n${JSON.stringify(exercicio, null, 2)}`);
        }
    } catch (error) {
        console.error('Erro ao exibir detalhes do exercício:', error);
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Não foi possível exibir os detalhes do exercício.',
                confirmButtonColor: '#ef4444'
            });
        } else {
            alert('Erro ao exibir detalhes do exercício.');
        }
    }
};

export async function carregarInscricoesTreinos() {
    const container = document.getElementById('inscricoes-container');
    const loading = document.getElementById('loading');
    const nenhumaInscricao = document.getElementById('nenhuma-inscricao');
    const ultimaCorridaDestaque = document.getElementById('ultima-corrida-destaque');
    const btnGerarUltimaCorrida = document.getElementById('btn-gerar-ultima-corrida');

    try {
        const response = await fetch(`${API_BASE_PARTICIPANTE}/get_inscricoes.php`, {
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            console.error('Erro ao buscar inscrições:', response.status, response.statusText);
            loading.classList.add('hidden');
            nenhumaInscricao.classList.remove('hidden');
            return;
        }
        
        const data = await response.json();
        
        loading.classList.add('hidden');

        if (!data.success || !data.inscricoes || data.inscricoes.length === 0) {
            console.log('Nenhuma inscrição encontrada');
            nenhumaInscricao.classList.remove('hidden');
            return;
        }

        // Filtrar apenas inscrições confirmadas (status = 'confirmada')
        // Também incluir se status_pagamento = 'pago' mas status ainda não foi atualizado
        const inscricoesConfirmadas = data.inscricoes.filter(i => {
            const statusConfirmado = i.status === 'confirmada';
            const pagamentoPago = i.status_pagamento === 'pago';
            return statusConfirmado || pagamentoPago;
        });

        console.log('Total de inscrições:', data.inscricoes.length);
        console.log('Inscrições confirmadas:', inscricoesConfirmadas.length);
        console.log('Detalhes das inscrições:', data.inscricoes);

        if (inscricoesConfirmadas.length === 0) {
            console.log('Nenhuma inscrição confirmada encontrada');
            nenhumaInscricao.classList.remove('hidden');
            return;
        }

        // Ordenar por data do evento (mais recente primeiro)
        inscricoesConfirmadas.sort((a, b) => {
            const dataA = new Date(a.evento_data + 'T00:00:00');
            const dataB = new Date(b.evento_data + 'T00:00:00');
            return dataB - dataA;
        });

        const ultimaInscricao = inscricoesConfirmadas[0];
        console.log('Última inscrição selecionada:', ultimaInscricao);
        
        const statusAnamneseUltima = await verificarAnamnese(ultimaInscricao.inscricao_id);
        const statusTreinoUltima = await verificarTreino(ultimaInscricao.inscricao_id);
        
        const dataEventoUltima = new Date(ultimaInscricao.evento_data + 'T00:00:00');
        const dataFormatadaUltima = dataEventoUltima.toLocaleDateString('pt-BR');

        if (ultimaInscricao && !statusTreinoUltima) {
            ultimaCorridaDestaque.classList.remove('hidden');
            ultimaCorridaDestaque.innerHTML = `
                <div class="bg-gradient-to-r from-green-50 to-blue-50 border-2 border-brand-green rounded-lg shadow-lg p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-grow">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="px-3 py-1 text-xs font-bold rounded-full bg-brand-green text-white">
                                    ÚLTIMA CORRIDA INSCRITA
                                </span>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">${ultimaInscricao.evento_nome}</h3>
                            <p class="text-lg text-gray-700 mb-1">${ultimaInscricao.modalidade_nome}</p>
                            <p class="text-sm text-gray-600 mb-4">${dataFormatadaUltima} - ${ultimaInscricao.evento_local || ''}</p>
                            <div class="flex gap-2 mb-4">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full ${
                                    statusAnamneseUltima ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                                }">
                                    ${statusAnamneseUltima ? '✓ Anamnese Preenchida' : '⚠ Anamnese Pendente'}
                                </span>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    ${statusTreinoUltima ? '✓ Treino Gerado' : 'Aguardando Geração'}
                                </span>
                            </div>
                            ${!statusAnamneseUltima ? `
                                <p class="text-sm text-yellow-700 mb-4">
                                    <strong>Próximo passo:</strong> Preencha sua anamnese para gerar um treino personalizado.
                                </p>
                            ` : !statusTreinoUltima ? `
                                <p class="text-sm text-green-700 mb-4">
                                    <strong>Pronto para gerar!</strong> Sua anamnese está completa. Clique no botão abaixo para gerar seu treino personalizado.
                                </p>
                            ` : ''}
                        </div>
                        <div class="flex flex-col gap-2">
                            ${!statusAnamneseUltima ? `
                                <a href="?page=anamnese&inscricao_id=${ultimaInscricao.inscricao_id}" 
                                   class="bg-blue-600 text-white font-semibold py-3 px-6 rounded-lg hover:bg-blue-700 transition-colors text-center whitespace-nowrap">
                                    Preencher Anamnese
                                </a>
                            ` : ''}
                            ${statusAnamneseUltima && !statusTreinoUltima ? `
                                <button onclick="gerarTreinoParaInscricao(${ultimaInscricao.inscricao_id}, event)" 
                                        class="bg-brand-green text-white font-semibold py-3 px-6 rounded-lg hover:bg-green-700 transition-colors whitespace-nowrap">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                        Gerar Treino Agora
                                    </span>
                                </button>
                            ` : ''}
                            ${statusTreinoUltima ? `
                                <a href="?page=ver-treino&inscricao_id=${ultimaInscricao.inscricao_id}" 
                                   class="bg-purple-600 text-white font-semibold py-3 px-6 rounded-lg hover:bg-purple-700 transition-colors text-center whitespace-nowrap">
                                    Ver Meu Treino
                                </a>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;

            if (statusAnamneseUltima && !statusTreinoUltima && btnGerarUltimaCorrida) {
                btnGerarUltimaCorrida.classList.remove('hidden');
                btnGerarUltimaCorrida.onclick = () => gerarTreinoParaInscricao(ultimaInscricao.inscricao_id, { target: btnGerarUltimaCorrida });
            }
        } else if (btnGerarUltimaCorrida) {
            btnGerarUltimaCorrida.classList.add('hidden');
        }

        container.classList.remove('hidden');

        const outrasInscricoes = inscricoesConfirmadas.slice(1);

        if (outrasInscricoes.length > 0) {
            const tituloOutras = document.createElement('h2');
            tituloOutras.className = 'text-xl font-bold text-gray-900 mb-4 mt-8';
            tituloOutras.textContent = 'Outras Corridas';
            container.appendChild(tituloOutras);
        }

        for (const inscricao of outrasInscricoes) {
            const statusAnamnese = await verificarAnamnese(inscricao.inscricao_id);
            const statusTreino = await verificarTreino(inscricao.inscricao_id);
            
            const dataEvento = new Date(inscricao.evento_data + 'T00:00:00');
            const dataFormatada = dataEvento.toLocaleDateString('pt-BR');

            const card = document.createElement('div');
            card.className = 'bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow';
            card.innerHTML = `
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="flex-grow">
                        <h3 class="text-xl font-bold text-gray-900">${inscricao.evento_nome}</h3>
                        <p class="text-gray-600">${inscricao.modalidade_nome}</p>
                        <p class="text-sm text-gray-500">${dataFormatada} - ${inscricao.evento_local || ''}</p>
                        <div class="mt-3 flex gap-2">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full ${
                                statusAnamnese ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                            }">
                                ${statusAnamnese ? '✓ Anamnese' : '⚠ Sem Anamnese'}
                            </span>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full ${
                                statusTreino ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'
                            }">
                                ${statusTreino ? '✓ Treino Gerado' : 'Sem Treino'}
                            </span>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        ${!statusAnamnese ? `
                            <a href="?page=anamnese&inscricao_id=${inscricao.inscricao_id}" 
                               class="bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                                Preencher Anamnese
                            </a>
                        ` : ''}
                        ${statusAnamnese && !statusTreino ? `
                            <button onclick="gerarTreinoParaInscricao(${inscricao.inscricao_id}, event)" 
                                    class="bg-green-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-700 transition-colors">
                                Gerar Treino
                            </button>
                        ` : ''}
                        ${statusTreino ? `
                            <a href="?page=ver-treino&inscricao_id=${inscricao.inscricao_id}" 
                               class="bg-purple-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-purple-700 transition-colors">
                                Ver Treino
                            </a>
                        ` : ''}
                    </div>
                </div>
            `;
            container.appendChild(card);
        }

    } catch (error) {
        console.error('Erro ao carregar inscrições:', error);
        loading.classList.add('hidden');
        nenhumaInscricao.classList.remove('hidden');
    }
}

async function verificarAnamnese(inscricaoId) {
    try {
        const response = await fetch(`${API_BASE_PARTICIPANTE}/anamnese/get.php?inscricao_id=${inscricaoId}`, {
            credentials: 'same-origin'
        });
        const data = await response.json();
        return data.success && data.anamnese !== null;
    } catch (error) {
        console.error('Erro ao verificar anamnese:', error);
        return false;
    }
}

async function verificarTreino(inscricaoId) {
    try {
        const response = await fetch(`${API_BASE_PARTICIPANTE}/treino/get.php?inscricao_id=${inscricaoId}`, {
            credentials: 'same-origin'
        });
        const data = await response.json();
        return data.success && data.plano !== null;
    } catch (error) {
        console.error('Erro ao verificar treino:', error);
        return false;
    }
}

export async function salvarAnamnese(dados) {
    const response = await fetch(`${API_BASE_PARTICIPANTE}/anamnese/create.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin',
        body: JSON.stringify(dados)
    });

    return await response.json();
}

export async function buscarTermosTreino() {
    const apiBase = window.API_BASE || (window.location.pathname.indexOf('/frontend/') > 0 ? window.location.pathname.slice(0, window.location.pathname.indexOf('/frontend/')) : '');
    const url = `${apiBase}/api/inscricao/get_termos.php?tipo=treino`;
    const res = await fetch(url);
    const data = await res.json();
    return (data.success && data.termos && data.termos.conteudo) ? data.termos : null;
}

export async function gerarTreino(inscricaoId, opts = {}) {
    const termosIdTreino = opts.termos_id_treino || null;
    console.log('🚀 [gerarTreino] Iniciando geração de treino para inscrição:', inscricaoId);
    console.log('📡 [gerarTreino] URL da API:', `${API_BASE_PARTICIPANTE}/treino/generate.php`);
    const payload = { inscricao_id: inscricaoId };
    if (termosIdTreino) payload.termos_id_treino = termosIdTreino;
    console.log('📦 [gerarTreino] Payload:', payload);
    
    try {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => {
            console.warn('⏱️ [gerarTreino] Timeout de 180 segundos atingido, abortando requisição...');
            controller.abort();
        }, 180000);
        
        console.log('📡 [gerarTreino] Enviando requisição POST...');
        const startTime = Date.now();
        
        const response = await fetch(`${API_BASE_PARTICIPANTE}/treino/generate.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload),
            signal: controller.signal
        });

        const requestDuration = Date.now() - startTime;
        console.log(`⏱️ [gerarTreino] Requisição concluída em ${requestDuration}ms`);
        console.log('📡 [gerarTreino] Response status:', response.status, response.statusText);
        console.log('📡 [gerarTreino] Response headers:', Object.fromEntries(response.headers.entries()));
        console.log('📡 [gerarTreino] Response ok:', response.ok);
        
        clearTimeout(timeoutId);
        
        console.log('📦 [gerarTreino] Parseando resposta JSON...');
        let data;
        try {
            const responseText = await response.text();
            console.log('📦 [gerarTreino] Response text (tamanho):', responseText.length, 'caracteres');
            console.log('📦 [gerarTreino] Response text (primeiros 500 chars):', responseText.substring(0, 500));
            console.log('📦 [gerarTreino] Response text (últimos 500 chars):', responseText.substring(Math.max(0, responseText.length - 500)));
            
            data = JSON.parse(responseText);
            console.log('✅ [gerarTreino] JSON parseado com sucesso:', data);
        } catch (parseError) {
            console.error('❌ [gerarTreino] Erro ao parsear JSON:', parseError);
            console.error('❌ [gerarTreino] Mensagem do erro:', parseError.message);
            throw new Error('Resposta inválida do servidor. Não foi possível processar o JSON.');
        }
        
        if (!response.ok) {
            console.error('❌ [gerarTreino] Resposta não OK:', {
                status: response.status,
                statusText: response.statusText,
                data: data
            });
            return {
                success: false,
                message: data.message || `Erro ${response.status}: ${response.statusText}`
            };
        }

        console.log('✅ [gerarTreino] Treino gerado com sucesso!', {
            success: data.success,
            message: data.message,
            plano_id: data.plano_id
        });
        
        return data;
    } catch (error) {
        console.error('💥 [gerarTreino] Erro ao gerar treino:', error);
        console.error('💥 [gerarTreino] Tipo do erro:', error.constructor.name);
        console.error('💥 [gerarTreino] Nome do erro:', error.name);
        console.error('💥 [gerarTreino] Mensagem:', error.message);
        console.error('💥 [gerarTreino] Stack trace:', error.stack);
        
        if (error.name === 'AbortError') {
            console.warn('⏱️ [gerarTreino] Requisição abortada por timeout');
            return {
                success: false,
                message: 'A geração do treino está demorando mais que o esperado. Por favor, tente novamente.'
            };
        }
        
        if (error.name === 'TypeError' && error.message.includes('fetch')) {
            console.error('🌐 [gerarTreino] Erro de rede/conexão');
            return {
                success: false,
                message: 'Erro de conexão. Verifique sua internet e tente novamente.'
            };
        }
        
        return {
            success: false,
            message: error.message || 'Erro desconhecido ao gerar treino. Tente novamente.'
        };
    }
}

export async function carregarTreino(inscricaoId) {
    console.log('🔄 [carregarTreino] Iniciando carregamento do treino para inscrição:', inscricaoId);
    
    const container = document.getElementById('treino-container');
    const loading = document.getElementById('loading');
    const semTreino = document.getElementById('sem-treino');
    const planoInfo = document.getElementById('plano-info');
    const treinosList = document.getElementById('treinos-list');

    try {
        console.log('📡 [carregarTreino] Fazendo requisição para:', `${API_BASE_PARTICIPANTE}/treino/get.php?inscricao_id=${inscricaoId}`);
        
        const response = await fetch(`${API_BASE_PARTICIPANTE}/treino/get.php?inscricao_id=${inscricaoId}`, {
            credentials: 'same-origin'
        });
        
        console.log('📡 [carregarTreino] Response status:', response.status, response.statusText);
        console.log('📡 [carregarTreino] Response headers:', Object.fromEntries(response.headers.entries()));
        
        if (!response.ok) {
            console.error('❌ [carregarTreino] Erro HTTP:', response.status, response.statusText);
            throw new Error(`Erro ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        console.log('📦 [carregarTreino] Dados recebidos da API:', data);
        console.log('📦 [carregarTreino] Success:', data.success);
        console.log('📦 [carregarTreino] Plano:', data.plano);
        console.log('📦 [carregarTreino] Número de treinos:', data.treinos ? data.treinos.length : 0);
        
        loading.classList.add('hidden');

        if (!data.success || !data.plano || !data.treinos || data.treinos.length === 0) {
            console.warn('⚠️ [carregarTreino] Nenhum treino encontrado');
            loading.classList.add('hidden');
            semTreino.classList.remove('hidden');
            return;
        }

        console.log('✅ [carregarTreino] Dados válidos, iniciando renderização');
        console.log('📊 [carregarTreino] Estrutura do plano:', {
            id: data.plano.id,
            foco_primario: data.plano.foco_primario,
            duracao_treino_geral: data.plano.duracao_treino_geral,
            equipamento_geral: data.plano.equipamento_geral,
            data_criacao: data.plano.data_criacao_plano
        });
        
        container.classList.remove('hidden');

        const dataCriacao = data.plano.data_criacao_plano 
            ? new Date(data.plano.data_criacao_plano).toLocaleDateString('pt-BR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            })
            : 'N/A';
        
        // Calcular período total do treino
        const hoje = new Date();
        hoje.setHours(0, 0, 0, 0);
        const dataInicio = hoje;
        let dataFim = null;
        let periodoTotal = '';
        
        if (data.plano.evento_data) {
            dataFim = new Date(data.plano.evento_data);
            dataFim.setHours(0, 0, 0, 0);
            
            const dataInicioFormatada = dataInicio.toLocaleDateString('pt-BR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            const dataFimFormatada = dataFim.toLocaleDateString('pt-BR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            
            const semanasTotal = Object.keys(treinosPorSemana).length;
            periodoTotal = `Plano de ${semanasTotal} semana${semanasTotal > 1 ? 's' : ''} (de ${dataInicioFormatada} a ${dataFimFormatada})`;
        }
        
        planoInfo.innerHTML = `
            <div class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-xl border-2 border-blue-200 shadow-lg">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-dumbbell text-white text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-blue-900">${data.plano.foco_primario || 'Preparação para Corrida'}</h3>
                                <p class="text-sm text-blue-600 mt-1">${periodoTotal || 'Treino personalizado para sua corrida'}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <div class="flex items-center gap-2 p-3 bg-white rounded-lg border border-blue-100">
                                <i class="fas fa-clock text-blue-600"></i>
                                <div>
                                    <div class="text-xs text-gray-500">Duração</div>
                                    <div class="text-sm font-semibold text-gray-800">${data.plano.duracao_treino_geral || '-'}</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 p-3 bg-white rounded-lg border border-blue-100">
                                <i class="fas fa-dumbbell text-blue-600"></i>
                                <div>
                                    <div class="text-xs text-gray-500">Equipamento</div>
                                    <div class="text-sm font-semibold text-gray-800">${data.plano.equipamento_geral || '-'}</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 p-3 bg-white rounded-lg border border-blue-100">
                                <i class="fas fa-calendar text-blue-600"></i>
                                <div>
                                    <div class="text-xs text-gray-500">Plano criado em</div>
                                    <div class="text-sm font-semibold text-gray-800">${dataCriacao}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        const diasSemana = {1: 'Segunda', 2: 'Terça', 3: 'Quarta', 4: 'Quinta', 5: 'Sexta', 6: 'Sábado', 7: 'Domingo'};
        
        // Agrupar treinos por semana usando semana_numero do banco
        console.log('📅 [carregarTreino] Agrupando treinos por semana usando semana_numero...');
        const treinosPorSemana = {};
        data.treinos.forEach((treino, idx) => {
            // Usar semana_numero do banco, com fallback para cálculo se não existir (compatibilidade)
            const semanaNum = treino.semana_numero || Math.ceil(treino.dia_semana_id / 7) || Math.ceil((idx + 1) / 7);
            if (!treinosPorSemana[semanaNum]) {
                treinosPorSemana[semanaNum] = [];
            }
            treinosPorSemana[semanaNum].push({...treino, idxOriginal: idx});
            console.log(`📅 [carregarTreino] Treino ${idx} (dia_semana_id: ${treino.dia_semana_id}, semana_numero: ${treino.semana_numero || 'N/A'}) -> Semana ${semanaNum}`);
        });
        
        const numSemanas = Object.keys(treinosPorSemana).length;
        const temMultiplasSemanas = numSemanas > 1;
        console.log('📅 [carregarTreino] Total de semanas:', numSemanas);
        console.log('📅 [carregarTreino] Tem múltiplas semanas:', temMultiplasSemanas);
        
        // Criar navegação por semanas se houver múltiplas semanas
        let navegacaoSemanasHtml = '';
        if (temMultiplasSemanas) {
            navegacaoSemanasHtml = '<div class="mb-6 p-4 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl border-2 border-indigo-200 shadow-md">';
            navegacaoSemanasHtml += '<div class="flex items-center justify-between mb-3">';
            navegacaoSemanasHtml += '<h3 class="text-lg font-bold text-indigo-900 flex items-center gap-2">';
            navegacaoSemanasHtml += '<i class="fas fa-calendar-week text-indigo-600"></i>';
            navegacaoSemanasHtml += `Plano de ${numSemanas} Semana${numSemanas > 1 ? 's' : ''}`;
            navegacaoSemanasHtml += '</h3>';
            navegacaoSemanasHtml += '<div class="flex gap-2" id="navegacao-semanas">';
            Object.keys(treinosPorSemana).sort((a, b) => parseInt(a) - parseInt(b)).forEach((semanaNum, semanaIdx) => {
                const isActiveSemana = semanaIdx === 0;
                navegacaoSemanasHtml += `
                    <button id="semana-btn-${semanaNum}" 
                            class="semana-tab px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 ${isActiveSemana ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-indigo-700 hover:bg-indigo-100 border-2 border-indigo-200'}" 
                            onclick="selecionarSemana(${semanaNum})">
                        Semana ${semanaNum}
                    </button>
                `;
            });
            navegacaoSemanasHtml += '</div>';
            navegacaoSemanasHtml += '</div>';
            navegacaoSemanasHtml += '</div>';
        }

        let abasHtml = '<div class="flex flex-wrap gap-2 mb-4" id="abas-dias-container">';
        let treinoIndexGlobal = 0;
        Object.keys(treinosPorSemana).sort((a, b) => parseInt(a) - parseInt(b)).forEach((semanaNum, semanaIdx) => {
            const treinosSemana = treinosPorSemana[semanaNum];
            treinosSemana.forEach((treino) => {
                const diaNome = diasSemana[treino.dia_semana_id] || `Dia ${treino.dia_semana_id}`;
                const isActive = treinoIndexGlobal === 0;
                const semanaClass = temMultiplasSemanas ? `semana-${semanaNum} ${semanaIdx === 0 ? '' : 'hidden'}` : '';
                abasHtml += `
                    <button id="tab-btn-${treino.idxOriginal}" 
                            class="tab-dia-treino ${semanaClass} px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 ${isActive ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'}" 
                            onclick="selecionarAbaTreino(${treino.idxOriginal})">
                        ${temMultiplasSemanas ? `S${semanaNum} - ` : ''}${diaNome}
                    </button>
                `;
                treinoIndexGlobal++;
            });
        });
        abasHtml += '</div>';

        // Limpar dados anteriores e inicializar contador
        console.log('🧹 [carregarTreino] Limpando dados anteriores e inicializando contador de exercícios');
        window.exerciciosData = {};
        let exercicioCounter = 0;

        console.log('🎨 [carregarTreino] Iniciando renderização dos treinos...');
        let conteudosHtml = '<div class="relative">';
        data.treinos.forEach((treino, idx) => {
            console.log(`🎨 [carregarTreino] Renderizando treino ${idx + 1}/${data.treinos.length}:`, {
                id: treino.id,
                nome: treino.nome,
                dia_semana_id: treino.dia_semana_id,
                parte_inicial: Array.isArray(treino.parte_inicial) ? treino.parte_inicial.length : 0,
                parte_principal: Array.isArray(treino.parte_principal) ? treino.parte_principal.length : 0,
                volta_calma: Array.isArray(treino.volta_calma) ? treino.volta_calma.length : 0
            });
            let parteInicial = [];
            let partePrincipal = [];
            let voltaCalma = [];
            
            if (Array.isArray(treino.parte_inicial)) {
                parteInicial = treino.parte_inicial;
            } else if (typeof treino.parte_inicial === 'string' && treino.parte_inicial.trim()) {
                try {
                    parteInicial = JSON.parse(treino.parte_inicial);
                    if (!Array.isArray(parteInicial)) parteInicial = [];
                } catch (e) {
                    console.warn('Erro ao parsear parte_inicial:', e);
                    parteInicial = [];
                }
            }
            
            if (Array.isArray(treino.parte_principal)) {
                partePrincipal = treino.parte_principal;
            } else if (typeof treino.parte_principal === 'string' && treino.parte_principal.trim()) {
                try {
                    partePrincipal = JSON.parse(treino.parte_principal);
                    if (!Array.isArray(partePrincipal)) partePrincipal = [];
                } catch (e) {
                    console.warn('Erro ao parsear parte_principal:', e);
                    partePrincipal = [];
                }
            }
            
            if (Array.isArray(treino.volta_calma)) {
                voltaCalma = treino.volta_calma;
            } else if (typeof treino.volta_calma === 'string' && treino.volta_calma.trim()) {
                try {
                    voltaCalma = JSON.parse(treino.volta_calma);
                    if (!Array.isArray(voltaCalma)) voltaCalma = [];
                } catch (e) {
                    console.warn('Erro ao parsear volta_calma:', e);
                    voltaCalma = [];
                }
            }

            const diaNome = diasSemana[treino.dia_semana_id] || `Dia ${treino.dia_semana_id}`;
            
            // Debug: verificar estrutura dos dados
            console.log(`🔍 [carregarTreino] Treino ${idx} - Estrutura dos dados:`, {
                parteInicial: {
                    tipo: typeof parteInicial,
                    isArray: Array.isArray(parteInicial),
                    length: Array.isArray(parteInicial) ? parteInicial.length : 'N/A',
                    primeiroItem: parteInicial[0] || null
                },
                partePrincipal: {
                    tipo: typeof partePrincipal,
                    isArray: Array.isArray(partePrincipal),
                    length: Array.isArray(partePrincipal) ? partePrincipal.length : 'N/A',
                    primeiroItem: partePrincipal[0] || null
                },
                voltaCalma: {
                    tipo: typeof voltaCalma,
                    isArray: Array.isArray(voltaCalma),
                    length: Array.isArray(voltaCalma) ? voltaCalma.length : 'N/A',
                    primeiroItem: voltaCalma[0] || null
                }
            });
            
            // Validação: garantir que arrays sejam válidos
            if (!Array.isArray(parteInicial)) {
                console.warn(`⚠️ [carregarTreino] Treino ${idx} - parteInicial não é array, convertendo...`);
                parteInicial = [];
            }
            if (!Array.isArray(partePrincipal)) {
                console.warn(`⚠️ [carregarTreino] Treino ${idx} - partePrincipal não é array, convertendo...`);
                partePrincipal = [];
            }
            if (!Array.isArray(voltaCalma)) {
                console.warn(`⚠️ [carregarTreino] Treino ${idx} - voltaCalma não é array, convertendo...`);
                voltaCalma = [];
            }
            
            console.log(`✅ [carregarTreino] Treino ${idx} - Arrays validados:`, {
                parteInicial: parteInicial.length,
                partePrincipal: partePrincipal.length,
                voltaCalma: voltaCalma.length
            });
            
            conteudosHtml += `
                <div id="treino-card-${idx}" class="treino-conteudo ${idx === 0 ? '' : 'hidden'}">
                    <div class="p-6 border-2 border-gray-200 rounded-xl bg-white shadow-lg">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="font-bold text-2xl text-blue-900 mb-1">${treino.nome || 'Treino'}</h4>
                        </div>
                        <div>
                            <p class="text-base text-gray-700 mb-4 leading-relaxed">${treino.descricao || ''}</p>
                            <h5 class="font-bold text-blue-800 mb-3 text-lg flex items-center gap-2">
                                <i class="fas fa-fire text-orange-500"></i>
                                Aquecimento
                            </h5>
                            <ul class="list-none space-y-3 text-gray-800">
                                ${parteInicial.length > 0 ? parteInicial.map((ex, exIdx) => {
                                    if (typeof ex === 'string') {
                                        return `<li class="flex items-start gap-3 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border-l-4 border-blue-500 hover:from-blue-100 hover:to-indigo-100 transition-all shadow-sm">
                                            <div class="flex-1">
                                                <span class="text-gray-800 font-medium">${ex}</span>
                                            </div>
                                        </li>`;
                                    }
                                    const nomeExercicio = ex.nome_item || ex.nome || ex.nome_exercicio || '-';
                                    const detalhes = ex.detalhes_item || ex.detalhes || '';
                                    const exercicioId = `ex-${exercicioCounter++}`;
                                    window.exerciciosData[exercicioId] = ex;
                                    
                                    // Verificar todas as propriedades possíveis
                                    const temBadges = !!(ex.fc_alvo || ex.tempo_execucao || ex.tempo_recuperacao || ex.tipo_recuperacao || ex.carga || ex.distancia || ex.velocidade || ex.cadencia || ex.tipo_contracao || ex.angulo_articular || ex.observacoes || ex.peso || ex.tempo || ex.series || ex.repeticoes);
                                    
                                    return `<li class="flex items-start gap-3 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border-l-4 border-blue-500 hover:from-blue-100 hover:to-indigo-100 transition-all shadow-sm">
                                        <div class="flex-1">
                                            <div class="flex items-start gap-2 mb-2">
                                                <span class="font-bold text-gray-900 text-base">${nomeExercicio}</span>
                                            </div>
                                            ${detalhes ? `<p class="text-sm text-gray-700 mb-3 leading-relaxed">${detalhes}</p>` : ''}
                                            ${temBadges ? `<div class="flex flex-wrap gap-2 mt-3">
                                                ${ex.fc_alvo ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 border-2 border-blue-300 shadow-sm">💓 FC alvo: ${ex.fc_alvo}</span>` : ''}
                                                ${ex.tempo_execucao ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-green-100 text-green-800 border-2 border-green-300 shadow-sm">⏱️ Execução: ${ex.tempo_execucao}</span>` : ''}
                                                ${ex.tempo_recuperacao ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 border-2 border-yellow-300 shadow-sm">🔄 Recuperação: ${ex.tempo_recuperacao}</span>` : ''}
                                                ${ex.tipo_recuperacao ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 border-2 border-purple-300 shadow-sm">📋 Tipo: ${ex.tipo_recuperacao}</span>` : ''}
                                                ${ex.carga || ex.peso ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 border-2 border-gray-300 shadow-sm">⚖️ Carga: ${ex.carga || ex.peso}</span>` : ''}
                                                ${ex.distancia ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-pink-100 text-pink-800 border-2 border-pink-300 shadow-sm">📏 Distância: ${ex.distancia}</span>` : ''}
                                                ${ex.velocidade ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-cyan-100 text-cyan-800 border-2 border-cyan-300 shadow-sm">🏃 Velocidade: ${ex.velocidade}</span>` : ''}
                                                ${ex.cadencia ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-orange-100 text-orange-800 border-2 border-orange-300 shadow-sm">🎯 Cadência: ${ex.cadencia}</span>` : ''}
                                                ${ex.tipo_contracao ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-lime-100 text-lime-800 border-2 border-lime-300 shadow-sm">💪 Contração: ${ex.tipo_contracao}</span>` : ''}
                                                ${ex.angulo_articular ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-teal-100 text-teal-800 border-2 border-teal-300 shadow-sm">📐 Ângulo: ${ex.angulo_articular}</span>` : ''}
                                                ${ex.series ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800 border-2 border-indigo-300 shadow-sm">🔢 Séries: ${ex.series}</span>` : ''}
                                                ${ex.repeticoes ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-violet-100 text-violet-800 border-2 border-violet-300 shadow-sm">🔁 Repetições: ${ex.repeticoes}</span>` : ''}
                                                ${ex.tempo ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800 border-2 border-emerald-300 shadow-sm">⏰ Tempo: ${ex.tempo}</span>` : ''}
                                                ${ex.observacoes ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-red-100 text-red-800 border-2 border-red-300 shadow-sm">📝 Obs: ${ex.observacoes}</span>` : ''}
                                            </div>` : ''}
                                        </div>
                                        ${temBadges ? `<button onclick="mostrarDetalhesExercicio('${exercicioId}')" 
                                                class="flex-shrink-0 px-4 py-2 text-sm font-semibold text-white bg-blue-600 border-2 border-blue-700 rounded-lg hover:bg-blue-700 hover:border-blue-800 transition-all duration-200 flex items-center gap-2 shadow-md hover:shadow-lg transform hover:scale-105">
                                            <i class="fas fa-info-circle"></i> Detalhes
                                        </button>` : ''}
                                    </li>`;
                                }).join('') : '<li class="text-gray-400 italic p-4 bg-gray-50 rounded-lg">Nenhum exercício</li>'}
                            </ul>
                            <h5 class="font-bold text-blue-800 mt-6 mb-3 text-lg flex items-center gap-2">
                                <i class="fas fa-dumbbell text-green-600"></i>
                                Treino Principal
                            </h5>
                            <ul class="list-none space-y-3 text-gray-800">
                                ${partePrincipal.length > 0 ? partePrincipal.map((ex, exIdx) => {
                                    if (typeof ex === 'string') {
                                        return `<li class="flex items-start gap-3 p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg border-l-4 border-green-500 hover:from-green-100 hover:to-emerald-100 transition-all shadow-sm">
                                            <div class="flex-1">
                                                <span class="text-gray-800 font-medium">${ex}</span>
                                            </div>
                                        </li>`;
                                    }
                                    const nomeExercicio = ex.nome_item || ex.nome || ex.nome_exercicio || '-';
                                    const detalhes = ex.detalhes_item || ex.detalhes || '';
                                    const exercicioId = `ex-${exercicioCounter++}`;
                                    window.exerciciosData[exercicioId] = ex;
                                    const temBadges = !!(ex.fc_alvo || ex.tempo_execucao || ex.tempo_recuperacao || ex.tipo_recuperacao || ex.carga || ex.distancia || ex.velocidade || ex.cadencia || ex.tipo_contracao || ex.angulo_articular || ex.observacoes || ex.peso || ex.tempo || ex.series || ex.repeticoes);
                                    return `<li class="flex items-start gap-3 p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg border-l-4 border-green-500 hover:from-green-100 hover:to-emerald-100 transition-all shadow-sm">
                                        <div class="flex-1">
                                            <div class="flex items-start gap-2 mb-2">
                                                <span class="font-bold text-gray-900 text-base">${nomeExercicio}</span>
                                            </div>
                                            ${detalhes ? `<p class="text-sm text-gray-700 mb-3 leading-relaxed">${detalhes}</p>` : ''}
                                            ${temBadges ? `<div class="flex flex-wrap gap-2 mt-3">
                                                ${ex.fc_alvo ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 border-2 border-blue-300 shadow-sm">💓 FC alvo: ${ex.fc_alvo}</span>` : ''}
                                                ${ex.tempo_execucao ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-green-100 text-green-800 border-2 border-green-300 shadow-sm">⏱️ Execução: ${ex.tempo_execucao}</span>` : ''}
                                                ${ex.tempo_recuperacao ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 border-2 border-yellow-300 shadow-sm">🔄 Recuperação: ${ex.tempo_recuperacao}</span>` : ''}
                                                ${ex.tipo_recuperacao ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 border-2 border-purple-300 shadow-sm">📋 Tipo: ${ex.tipo_recuperacao}</span>` : ''}
                                                ${ex.carga || ex.peso ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 border-2 border-gray-300 shadow-sm">⚖️ Carga: ${ex.carga || ex.peso}</span>` : ''}
                                                ${ex.distancia ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-pink-100 text-pink-800 border-2 border-pink-300 shadow-sm">📏 Distância: ${ex.distancia}</span>` : ''}
                                                ${ex.velocidade ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-cyan-100 text-cyan-800 border-2 border-cyan-300 shadow-sm">🏃 Velocidade: ${ex.velocidade}</span>` : ''}
                                                ${ex.cadencia ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-orange-100 text-orange-800 border-2 border-orange-300 shadow-sm">🎯 Cadência: ${ex.cadencia}</span>` : ''}
                                                ${ex.tipo_contracao ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-lime-100 text-lime-800 border-2 border-lime-300 shadow-sm">💪 Contração: ${ex.tipo_contracao}</span>` : ''}
                                                ${ex.angulo_articular ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-teal-100 text-teal-800 border-2 border-teal-300 shadow-sm">📐 Ângulo: ${ex.angulo_articular}</span>` : ''}
                                                ${ex.series ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800 border-2 border-indigo-300 shadow-sm">🔢 Séries: ${ex.series}</span>` : ''}
                                                ${ex.repeticoes ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-violet-100 text-violet-800 border-2 border-violet-300 shadow-sm">🔁 Repetições: ${ex.repeticoes}</span>` : ''}
                                                ${ex.tempo ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800 border-2 border-emerald-300 shadow-sm">⏰ Tempo: ${ex.tempo}</span>` : ''}
                                                ${ex.observacoes ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-red-100 text-red-800 border-2 border-red-300 shadow-sm">📝 Obs: ${ex.observacoes}</span>` : ''}
                                            </div>` : ''}
                                        </div>
                                        ${temBadges ? `<button onclick="mostrarDetalhesExercicio('${exercicioId}')" 
                                                class="flex-shrink-0 px-4 py-2 text-sm font-semibold text-white bg-green-600 border-2 border-green-700 rounded-lg hover:bg-green-700 hover:border-green-800 transition-all duration-200 flex items-center gap-2 shadow-md hover:shadow-lg transform hover:scale-105">
                                            <i class="fas fa-info-circle"></i> Detalhes
                                        </button>` : ''}
                                    </li>`;
                                }).join('') : '<li class="text-gray-400 italic p-4 bg-gray-50 rounded-lg">Nenhum exercício</li>'}
                            </ul>
                            <h5 class="font-bold text-blue-800 mt-6 mb-3 text-lg flex items-center gap-2">
                                <i class="fas fa-wind text-purple-600"></i>
                                Volta à Calma
                            </h5>
                            <ul class="list-none space-y-3 text-gray-800">
                                ${voltaCalma.length > 0 ? voltaCalma.map((ex, exIdx) => {
                                    if (typeof ex === 'string') {
                                        return `<li class="flex items-start gap-3 p-4 bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg border-l-4 border-purple-500 hover:from-purple-100 hover:to-pink-100 transition-all shadow-sm">
                                            <div class="flex-1">
                                                <span class="text-gray-800 font-medium">${ex}</span>
                                            </div>
                                        </li>`;
                                    }
                                    const nomeExercicio = ex.nome_item || ex.nome || ex.nome_exercicio || '-';
                                    const detalhes = ex.detalhes_item || ex.detalhes || '';
                                    const exercicioId = `ex-${exercicioCounter++}`;
                                    window.exerciciosData[exercicioId] = ex;
                                    const temBadges = !!(ex.fc_alvo || ex.tempo_execucao || ex.tempo_recuperacao || ex.tipo_recuperacao || ex.carga || ex.distancia || ex.velocidade || ex.cadencia || ex.tipo_contracao || ex.angulo_articular || ex.observacoes || ex.peso || ex.tempo || ex.series || ex.repeticoes);
                                    return `<li class="flex items-start gap-3 p-4 bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg border-l-4 border-purple-500 hover:from-purple-100 hover:to-pink-100 transition-all shadow-sm">
                                        <div class="flex-1">
                                            <div class="flex items-start gap-2 mb-2">
                                                <span class="font-bold text-gray-900 text-base">${nomeExercicio}</span>
                                            </div>
                                            ${detalhes ? `<p class="text-sm text-gray-700 mb-3 leading-relaxed">${detalhes}</p>` : ''}
                                            ${temBadges ? `<div class="flex flex-wrap gap-2 mt-3">
                                                ${ex.fc_alvo ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 border-2 border-blue-300 shadow-sm">💓 FC alvo: ${ex.fc_alvo}</span>` : ''}
                                                ${ex.tempo_execucao ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-green-100 text-green-800 border-2 border-green-300 shadow-sm">⏱️ Execução: ${ex.tempo_execucao}</span>` : ''}
                                                ${ex.tempo_recuperacao ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 border-2 border-yellow-300 shadow-sm">🔄 Recuperação: ${ex.tempo_recuperacao}</span>` : ''}
                                                ${ex.tipo_recuperacao ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 border-2 border-purple-300 shadow-sm">📋 Tipo: ${ex.tipo_recuperacao}</span>` : ''}
                                                ${ex.carga || ex.peso ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 border-2 border-gray-300 shadow-sm">⚖️ Carga: ${ex.carga || ex.peso}</span>` : ''}
                                                ${ex.distancia ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-pink-100 text-pink-800 border-2 border-pink-300 shadow-sm">📏 Distância: ${ex.distancia}</span>` : ''}
                                                ${ex.velocidade ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-cyan-100 text-cyan-800 border-2 border-cyan-300 shadow-sm">🏃 Velocidade: ${ex.velocidade}</span>` : ''}
                                                ${ex.cadencia ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-orange-100 text-orange-800 border-2 border-orange-300 shadow-sm">🎯 Cadência: ${ex.cadencia}</span>` : ''}
                                                ${ex.tipo_contracao ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-lime-100 text-lime-800 border-2 border-lime-300 shadow-sm">💪 Contração: ${ex.tipo_contracao}</span>` : ''}
                                                ${ex.angulo_articular ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-teal-100 text-teal-800 border-2 border-teal-300 shadow-sm">📐 Ângulo: ${ex.angulo_articular}</span>` : ''}
                                                ${ex.series ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800 border-2 border-indigo-300 shadow-sm">🔢 Séries: ${ex.series}</span>` : ''}
                                                ${ex.repeticoes ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-violet-100 text-violet-800 border-2 border-violet-300 shadow-sm">🔁 Repetições: ${ex.repeticoes}</span>` : ''}
                                                ${ex.tempo ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800 border-2 border-emerald-300 shadow-sm">⏰ Tempo: ${ex.tempo}</span>` : ''}
                                                ${ex.observacoes ? `<span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full bg-red-100 text-red-800 border-2 border-red-300 shadow-sm">📝 Obs: ${ex.observacoes}</span>` : ''}
                                            </div>` : ''}
                                        </div>
                                        ${temBadges ? `<button onclick="mostrarDetalhesExercicio('${exercicioId}')" 
                                                class="flex-shrink-0 px-4 py-2 text-sm font-semibold text-white bg-purple-600 border-2 border-purple-700 rounded-lg hover:bg-purple-700 hover:border-purple-800 transition-all duration-200 flex items-center gap-2 shadow-md hover:shadow-lg transform hover:scale-105">
                                            <i class="fas fa-info-circle"></i> Detalhes
                                        </button>` : ''}
                                    </li>`;
                                }).join('') : '<li class="text-gray-400 italic p-4 bg-gray-50 rounded-lg">Nenhum exercício</li>'}
                            </ul>
                            <div class="mt-6 p-5 bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl shadow-md border border-gray-200">
                                <h3 class="font-bold text-blue-800 mb-4 text-lg flex items-center gap-2">
                                    <i class="fas fa-info-circle text-blue-600"></i>
                                    Detalhes Avançados do Treino
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="p-3 bg-white rounded-lg border border-gray-200">
                                        <div class="text-xs font-semibold text-gray-500 uppercase mb-1">Foco do dia de Treino</div>
                                        <div class="text-sm text-gray-800">${treino.descricao || '-'}</div>
                                    </div>
                                    <div class="p-3 bg-white rounded-lg border border-gray-200">
                                        <div class="text-xs font-semibold text-gray-500 uppercase mb-1">Grupos Musculares</div>
                                        <div class="text-sm text-gray-800">${treino.grupos_musculares || '-'}</div>
                                    </div>
                                    <div class="p-3 bg-white rounded-lg border border-gray-200">
                                        <div class="text-xs font-semibold text-gray-500 uppercase mb-1">Volume Total</div>
                                        <div class="text-sm font-semibold text-gray-800">${treino.volume_total || '-'}</div>
                                    </div>
                                    <div class="p-3 bg-white rounded-lg border border-gray-200">
                                        <div class="text-xs font-semibold text-gray-500 uppercase mb-1">FC Máxima</div>
                                        <div class="text-sm font-semibold text-gray-800">${(Array.isArray(partePrincipal) && partePrincipal.length > 0 && partePrincipal[0] && partePrincipal[0].fc_alvo) ? partePrincipal[0].fc_alvo : '-'}</div>
                                    </div>
                                    ${treino.observacoes ? `<div class="md:col-span-2 p-3 bg-white rounded-lg border border-gray-200">
                                        <div class="text-xs font-semibold text-gray-500 uppercase mb-1">Observações</div>
                                        <div class="whitespace-pre-line text-sm text-gray-700 mt-1">${treino.observacoes.replace(/==/g, '').trim()}</div>
                                    </div>` : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        conteudosHtml += '</div>';

        let rodapeBibliografia = '';
        if (data.plano.bibliografia_plano) {
            let bibliografia = data.plano.bibliografia_plano;
            // Remover marcadores ==
            bibliografia = bibliografia.replace(/==/g, '');
            // Converter URLs em links clicáveis
            bibliografia = bibliografia.replace(/(https?:\/\/[^\s\)]+)/g, '<a href="$1" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline">$1</a>');
            // Formatar como lista se houver múltiplas linhas
            const linhas = bibliografia.split('\n').filter(l => l.trim());
            if (linhas.length > 1) {
                bibliografia = '<ul class="list-disc list-inside space-y-1">' + 
                    linhas.map(l => `<li>${l.trim()}</li>`).join('') + 
                    '</ul>';
            }
            rodapeBibliografia = `
                <div class="mt-8 p-4 bg-blue-50 border-t-4 border-blue-400 text-blue-900 rounded-b">
                    <h6 class="font-bold mb-2 text-lg">Bibliografia Recomendada</h6>
                    <div class="text-sm">${bibliografia}</div>
                </div>
            `;
        }

        console.log('✅ [carregarTreino] Renderização HTML concluída, inserindo no DOM...');
        treinosList.innerHTML = navegacaoSemanasHtml + abasHtml + conteudosHtml + rodapeBibliografia;
        console.log('✅ [carregarTreino] DOM atualizado com sucesso');
        console.log('📊 [carregarTreino] Resumo final:', {
            totalTreinos: data.treinos.length,
            totalSemanas: numSemanas,
            exerciciosArmazenados: Object.keys(window.exerciciosData).length
        });

        window.selecionarAbaTreino = function(idx) {
            document.querySelectorAll('.tab-dia-treino').forEach((btn, i) => {
                if (i === idx) {
                    btn.classList.remove('bg-gray-100', 'text-gray-600', 'hover:bg-gray-200');
                    btn.classList.add('bg-blue-600', 'text-white');
                } else {
                    btn.classList.remove('bg-blue-600', 'text-white');
                    btn.classList.add('bg-gray-100', 'text-gray-600', 'hover:bg-gray-200');
                }
            });
            document.querySelectorAll('.treino-conteudo').forEach((content, i) => {
                if (i === idx) {
                    content.classList.remove('hidden');
                } else {
                    content.classList.add('hidden');
                }
            });
        };
        
        // Função para selecionar semana (mostrar apenas abas e treinos daquela semana)
        window.selecionarSemana = function(semanaNum) {
            // Atualizar botões de semana
            document.querySelectorAll('.semana-tab').forEach((btn) => {
                const btnSemana = parseInt(btn.id.replace('semana-btn-', ''));
                if (btnSemana === semanaNum) {
                    btn.classList.remove('bg-white', 'text-indigo-700', 'hover:bg-indigo-100', 'border-indigo-200');
                    btn.classList.add('bg-indigo-600', 'text-white', 'shadow-md');
                } else {
                    btn.classList.remove('bg-indigo-600', 'text-white', 'shadow-md');
                    btn.classList.add('bg-white', 'text-indigo-700', 'hover:bg-indigo-100', 'border-2', 'border-indigo-200');
                }
            });
            
            // Mostrar/ocultar abas de dias da semana selecionada
            document.querySelectorAll('.tab-dia-treino').forEach((btn) => {
                if (btn.classList.contains(`semana-${semanaNum}`)) {
                    btn.classList.remove('hidden');
                } else {
                    btn.classList.add('hidden');
                }
            });
            
            // Selecionar primeira aba da semana
            const primeiraAbaSemana = document.querySelector(`.semana-${semanaNum}`);
            if (primeiraAbaSemana) {
                const idx = parseInt(primeiraAbaSemana.id.replace('tab-btn-', ''));
                selecionarAbaTreino(idx);
            }
        };

    } catch (error) {
        console.error('💥 [carregarTreino] Erro ao carregar treino:', error);
        loading.classList.add('hidden');
        if (semTreino) {
            semTreino.classList.remove('hidden');
        }
    }
}

window.gerarTreinoParaInscricao = async function(inscricaoId, event) {
    const button = event ? event.target : document.querySelector(`button[onclick*="${inscricaoId}"]`);
    const originalText = button ? button.innerHTML : 'Gerar Treino';
    const originalDisabled = button ? button.disabled : false;
    
    if (button) {
        button.disabled = true;
        button.innerHTML = '<span class="flex items-center gap-2"><span class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span> Gerando...</span>';
    }

    console.log('🚀 [gerarTreinoParaInscricao] Iniciando geração de treino para inscrição:', inscricaoId);
    
    try {
        let termosIdTreino = null;
        const termosTreino = await buscarTermosTreino();

        if (typeof Swal !== 'undefined') {
            if (termosTreino) {
                console.log('💬 [gerarTreinoParaInscricao] Exibindo termos de treino...');
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
                    focusCancel: false,
                    preConfirm: () => {
                        const checkbox = document.getElementById('swal-aceite-termos-treino');
                        if (!checkbox.checked) {
                            Swal.showValidationMessage('É necessário aceitar o termo para continuar.');
                            return false;
                        }
                        return true;
                    }
                });
                if (!confirmResult.isConfirmed) {
                    if (button) { button.disabled = originalDisabled; button.innerHTML = originalText; }
                    return;
                }
                termosIdTreino = termosTreino.id;
            } else {
                const confirmResult = await Swal.fire({
                    icon: 'question',
                    title: 'Gerar Treino Personalizado?',
                    text: 'Isso pode levar alguns segundos. Deseja continuar?',
                    showCancelButton: true,
                    confirmButtonText: 'Sim, Gerar Treino',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#10b981'
                });
                if (!confirmResult.isConfirmed) {
                    if (button) { button.disabled = originalDisabled; button.innerHTML = originalText; }
                    return;
                }
            }

            Swal.fire({
                icon: 'info',
                title: 'Gerando seu treino...',
                text: 'Por favor, aguarde. Isso pode levar alguns segundos.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => { Swal.showLoading(); }
            });
        } else if (!confirm('Deseja gerar um treino personalizado para esta corrida? Isso pode levar alguns segundos.')) {
            if (button) { button.disabled = originalDisabled; button.innerHTML = originalText; }
            return;
        }

        console.log('⏳ [gerarTreinoParaInscricao] Aguardando resultado da geração...');
        const resultado = await gerarTreino(inscricaoId, termosIdTreino ? { termos_id_treino: termosIdTreino } : {});
        console.log('📦 [gerarTreinoParaInscricao] Resultado recebido:', resultado);
        
        if (typeof Swal !== 'undefined') {
            Swal.close();
        }

        if (resultado.success) {
            console.log('✅ [gerarTreinoParaInscricao] Treino gerado com sucesso!');
            if (typeof Swal !== 'undefined') {
                await Swal.fire({
                    icon: 'success',
                    title: 'Treino Gerado!',
                    text: 'Seu treino personalizado foi criado com sucesso.',
                    confirmButtonText: 'Ver Treino',
                    confirmButtonColor: '#10b981'
                });
                window.location.reload();
            } else {
                alert('Treino gerado com sucesso!');
                window.location.reload();
            }
        } else {
            console.error('❌ [gerarTreinoParaInscricao] Erro ao gerar treino:', resultado);
            const mensagem = resultado.message || 'Erro desconhecido ao gerar treino';
            console.error('❌ [gerarTreinoParaInscricao] Mensagem de erro:', mensagem);
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro ao Gerar Treino',
                    text: mensagem,
                    confirmButtonColor: '#ef4444'
                });
            } else {
                alert('Erro ao gerar treino: ' + mensagem);
            }
            if (button) {
                button.disabled = originalDisabled;
                button.innerHTML = originalText;
            }
        }
    } catch (error) {
        console.error('💥 [gerarTreinoParaInscricao] Erro capturado no catch:', error);
        console.error('💥 [gerarTreinoParaInscricao] Tipo:', error.constructor.name);
        console.error('💥 [gerarTreinoParaInscricao] Mensagem:', error.message);
        console.error('💥 [gerarTreinoParaInscricao] Stack:', error.stack);
        
        if (typeof Swal !== 'undefined') {
            Swal.close();
        }
        const mensagem = error.message || 'Erro desconhecido';
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Erro ao Gerar Treino',
                text: mensagem,
                confirmButtonColor: '#ef4444'
            });
        } else {
            alert('Erro ao gerar treino: ' + mensagem);
        }
        if (button) {
            button.disabled = originalDisabled;
            button.innerHTML = originalText;
        }
    }
}

