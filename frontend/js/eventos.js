if (window.getApiBase) { window.getApiBase(); }
// Função para carregar dados do dashboard
async function carregarDashboard() {
    console.log(' Iniciando carregamento do dashboard');
    
    try {
        // Mostrar loading nativo (já existe no HTML)
        const loadingEl = document.getElementById('loading');
        const dashboardContent = document.getElementById('dashboard-content');
        const errorMessage = document.getElementById('error-message');
        
        if (loadingEl) loadingEl.style.display = 'block';
        if (dashboardContent) dashboardContent.style.display = 'none';
        if (errorMessage) errorMessage.style.display = 'none';

        console.log(' Fazendo requisição para API...');
        const response = await fetch((window.API_BASE || '/api') + '/organizador/get_dashboard_data.php');
        console.log(' Resposta recebida:', response.status, response.statusText);
        
        let data;
        try {
            data = await response.json();
            console.log(' Dados recebidos:', data);
        } catch (error) {
            console.log(' Erro ao parsear JSON:', error);
            if (!response.bodyUsed) {
                const responseText = await response.text();
                console.log(' Resposta bruta:', responseText);
            }
            throw new Error('Resposta inválida do servidor');
        }

        if (data.success) {
            console.log(' Dashboard carregado com sucesso');
            
            const stats = data.data.estatisticas;
            
            // Atualizar métricas principais (com verificação de existência)
            const elInscricoesConfirmadas = document.getElementById('inscricoes-confirmadas');
            if (elInscricoesConfirmadas) {
                elInscricoesConfirmadas.textContent = (stats.inscricoes_confirmadas_pagas || 0).toLocaleString('pt-BR');
            }
            
            const elReceitaConfirmada = document.getElementById('receita-confirmada');
            if (elReceitaConfirmada) {
                elReceitaConfirmada.textContent = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(stats.receita_confirmada || 0);
            }
            
            const elTaxaConversao = document.getElementById('taxa-conversao');
            if (elTaxaConversao) {
                elTaxaConversao.textContent = `${stats.taxa_conversao || 0}%`;
            }
            
            const elTotalEventos = document.getElementById('total-eventos');
            if (elTotalEventos) {
                elTotalEventos.textContent = stats.totalEventos || 0;
            }
            
            // Atualizar barra de progresso da taxa de conversão
            const taxaConversaoBar = document.getElementById('taxa-conversao-bar');
            if (taxaConversaoBar) {
                taxaConversaoBar.style.width = `${Math.min(stats.taxa_conversao || 0, 100)}%`;
            }
            
            // Atualizar detalhes expandíveis (com verificação)
            const elInscricoesConfirmadasPagas = document.getElementById('inscricoes-confirmadas-pagas');
            if (elInscricoesConfirmadasPagas) {
                elInscricoesConfirmadasPagas.textContent = (stats.inscricoes_confirmadas_pagas || 0).toLocaleString('pt-BR');
            }
            
            const elInscricoesPendentesPagamento = document.getElementById('inscricoes-pendentes-pagamento');
            if (elInscricoesPendentesPagamento) {
                elInscricoesPendentesPagamento.textContent = (stats.inscricoes_confirmadas_pendentes || 0).toLocaleString('pt-BR');
            }
            
            const elInscricoesPendentesConfirmacao = document.getElementById('inscricoes-pendentes-confirmacao');
            if (elInscricoesPendentesConfirmacao) {
                elInscricoesPendentesConfirmacao.textContent = (stats.inscricoes_pendentes_confirmacao || 0).toLocaleString('pt-BR');
            }
            
            const elInscricoesCanceladas = document.getElementById('inscricoes-canceladas');
            if (elInscricoesCanceladas) {
                elInscricoesCanceladas.textContent = (stats.inscricoes_canceladas || 0).toLocaleString('pt-BR');
            }
            
            const elReceitaPendente = document.getElementById('receita-pendente');
            if (elReceitaPendente) {
                elReceitaPendente.textContent = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(stats.receita_pendente || 0);
            }
            
            const elReceitaCancelada = document.getElementById('receita-cancelada');
            if (elReceitaCancelada) {
                elReceitaCancelada.textContent = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(stats.receita_cancelada || 0);
            }
            
            const elReceitaMesAtual = document.getElementById('receita-mes-atual');
            if (elReceitaMesAtual) {
                elReceitaMesAtual.textContent = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(stats.comparacao?.receita?.mes_atual || 0);
            }
            
            const elEventosCompletos = document.getElementById('eventos-completos');
            if (elEventosCompletos) {
                elEventosCompletos.textContent = stats.eventos_completos || 0;
            }
            
            const elEventosIncompletos = document.getElementById('eventos-incompletos');
            if (elEventosIncompletos) {
                elEventosIncompletos.textContent = stats.eventos_incompletos || 0;
            }
            
            // Atualizar variações percentuais
            const variacaoInscricoes = stats.comparacao?.inscricoes?.variacao_percentual || 0;
            const variacaoReceita = stats.comparacao?.receita?.variacao_percentual || 0;
            
            const inscricoesVariacaoEl = document.getElementById('inscricoes-variacao');
            if (inscricoesVariacaoEl) {
                if (variacaoInscricoes > 0) {
                    inscricoesVariacaoEl.textContent = ` ${Math.abs(variacaoInscricoes)}% vs mês anterior`;
                    inscricoesVariacaoEl.className = 'text-xs sm:text-sm text-green-600 mt-1';
                } else if (variacaoInscricoes < 0) {
                    inscricoesVariacaoEl.textContent = `S ${Math.abs(variacaoInscricoes)}% vs mês anterior`;
                    inscricoesVariacaoEl.className = 'text-xs sm:text-sm text-red-600 mt-1';
                } else {
                    inscricoesVariacaoEl.textContent = 'Sem variação';
                    inscricoesVariacaoEl.className = 'text-xs sm:text-sm text-gray-500 mt-1';
                }
            }
            
            const receitaVariacaoEl = document.getElementById('receita-variacao');
            if (receitaVariacaoEl) {
                if (variacaoReceita > 0) {
                    receitaVariacaoEl.textContent = ` ${Math.abs(variacaoReceita)}% vs mês anterior`;
                    receitaVariacaoEl.className = 'text-xs sm:text-sm text-green-600 mt-1';
                } else if (variacaoReceita < 0) {
                    receitaVariacaoEl.textContent = `S ${Math.abs(variacaoReceita)}% vs mês anterior`;
                    receitaVariacaoEl.className = 'text-xs sm:text-sm text-red-600 mt-1';
                } else {
                    receitaVariacaoEl.textContent = 'Sem variação';
                    receitaVariacaoEl.className = 'text-xs sm:text-sm text-gray-500 mt-1';
                }
            }
            
            // Renderizar eventos
            renderizarEventos(data.data.eventos);
            
            // Renderizar atividades (se existirem)
            if (data.data.atividades) {
                renderizarAtividades(data.data.atividades);
            }
            
            // Mostrar conteúdo e fechar loading
            if (loadingEl) loadingEl.style.display = 'none';
            if (dashboardContent) dashboardContent.style.display = 'block';
            if (errorMessage) errorMessage.style.display = 'none';
            
            // Carregar gráficos de forma assíncrona (não bloquear)
            setTimeout(() => {
                if (typeof window.carregarGraficos === 'function') {
                    window.carregarGraficos();
                } else if (typeof carregarGraficos === 'function') {
                    carregarGraficos();
                }
            }, 100);
            
            // Feedback de sucesso (sem SweetAlert para não interromper)
            console.log(` Dashboard atualizado: ${data.data.eventos.length} eventos, ${stats.inscricoes_confirmadas_pagas} inscrições confirmadas`);
        } else {
            throw new Error(data.message || 'Erro ao carregar dados do dashboard');
        }
    } catch (error) {
        console.error('"¥ Erro ao carregar dashboard:', error);
        
        // Esconder loading e mostrar erro
        const loadingEl = document.getElementById('loading');
        const dashboardContent = document.getElementById('dashboard-content');
        const errorMessage = document.getElementById('error-message');
        
        if (loadingEl) loadingEl.style.display = 'none';
        if (dashboardContent) dashboardContent.style.display = 'none';
        if (errorMessage) errorMessage.style.display = 'block';
        
        // Feedback de erro (sem SweetAlert para não travar)
        console.error('Erro ao carregar dashboard:', error.message);
    } finally {
        console.log(' Carregamento do dashboard finalizado');
    }
}

// Função para renderizar eventos no dashboard (otimizada)
function renderizarEventos(eventos) {
    console.log(' Iniciando renderização de eventos:', eventos.length, 'eventos');
    
    const container = document.getElementById('eventos-lista');
    if (!container) {
        console.error(' Container eventos-lista não encontrado');
        return;
    }
    
    // Limpar container
    container.innerHTML = '';
    
    if (!eventos || eventos.length === 0) {
        console.log(' Nenhum evento encontrado');
        container.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-calendar-times text-gray-400 text-4xl mb-4"></i>
                <p class="text-gray-500">Nenhum evento encontrado.</p>
                <a href="?page=criar-evento" class="btn-primary mt-4">
                    <i class="fas fa-plus mr-2"></i>
                    Criar Primeiro Evento
                </a>
            </div>
        `;
        return;
    }
    
    // Usar DocumentFragment para renderização otimizada
    const fragment = document.createDocumentFragment();
    
    eventos.forEach((evento, index) => {
        const dataFormatada = evento.date ? new Date(evento.date).toLocaleDateString('pt-BR') : '';
        const ocupacao = evento.taxa_ocupacao || (evento.maxRegistrations > 0 ? Math.round((evento.inscricoes_confirmadas / evento.maxRegistrations) * 100) : 0);
        const statusClass = evento.status === 'ativo' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
        const completoClass = evento.completo ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
        const completoTexto = evento.completo ? 'Completo' : 'Incompleto';
        const receitaFormatada = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(evento.receita_confirmada || 0);
        
        const eventoDiv = document.createElement('div');
        eventoDiv.className = 'card hover:shadow-lg transition-all duration-200 border border-gray-200 overflow-hidden';
        eventoDiv.innerHTML = `
            <!-- Seção da Imagem -->
            <div class="relative h-56 bg-transparent">
                ${evento.image 
                    ? `<img src="${getEventoImagemUrl(evento.image, evento.id)}" alt="${evento.name}"  class="w-[300px] h-auto object-cover" />`
                    : `<div class="w-full h-full flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-white text-4xl opacity-50"></i>
                       </div>`
                }
                <!-- Status Badges -->
                <div class="absolute top-3 right-3 flex flex-col gap-2">
                    <span class="px-3 py-1 text-xs font-medium rounded-full ${statusClass} shadow-sm">
                        ${evento.status || 'ativo'}
                    </span>
                    <span class="px-3 py-1 text-xs font-medium rounded-full ${completoClass} shadow-sm">
                        ${completoTexto}
                    </span>
                </div>
            </div>

            <!-- ConteÒºdo do Card -->
            <div class="p-6">
                <!-- Header do Card -->
                <div class="mb-4">
                    <div class="flex items-center space-x-2 mb-2">
                        <h3 class="text-xl font-bold text-gray-900">${evento.name}</h3>
                    </div>
                    <p class="text-sm text-gray-600 line-clamp-3">${evento.descricao || 'Sem descrição'}</p>
                </div>

                <!-- Informações do Evento -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4 text-sm">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-calendar text-gray-400"></i>
                        <span class="text-gray-700">${dataFormatada}</span>
                    </div>
                   <div class="flex items-center space-x-2">
                        <i class="fas fa-info-circle text-gray-400"></i>
                        <span class="text-gray-700">${evento.cidade ? `${evento.cidade}/${evento.estado}` : 'Localização não informada'}</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-users text-gray-400"></i>
                        <span class="text-gray-700">${evento.inscricoes_confirmadas || 0}/${evento.maxRegistrations || 0} confirmadas</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-chart-pie text-gray-400"></i>
                        <span class="text-gray-700">${ocupacao}% ocupação</span>
                    </div>
                </div>

                <!-- Métricas Adicionais -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">Receita Confirmada:</span>
                        <span class="text-sm font-bold text-green-600">${receitaFormatada}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">Taxa de Ocupação:</span>
                        <span class="text-sm font-bold ${ocupacao >= 80 ? 'text-green-600' : ocupacao >= 50 ? 'text-yellow-600' : 'text-gray-600'}">${ocupacao}%</span>
                    </div>
                </div>

                <!-- Ações Rápidas -->
                <div class="flex gap-2">
                    <a href="?page=eventos&id=${evento.id}" class="btn-primary text-xs sm:text-sm flex-1 text-center">
                        <i class="fas fa-eye mr-2"></i>
                        Ver Detalhes
                    </a>
                </div>
            </div>
        `;
        
        fragment.appendChild(eventoDiv);
    });
    
    // Adicionar todos os eventos de uma vez (otimizado)
    container.appendChild(fragment);
    
    console.log(' Renderização de eventos concluída');
}

// Função para renderizar atividades recentes
function renderizarAtividades(atividades) {
    console.log(' Iniciando renderização de atividades:', atividades.length, 'atividades');
    
    const container = document.getElementById('atividades-recentes');
    if (!container) {
        console.log(' ï¸ Container atividades-recentes não encontrado');
        return;
    }
    
    container.innerHTML = '';
    
    if (!atividades || atividades.length === 0) {
        console.log(' Nenhuma atividade encontrada');
        container.innerHTML = `
            <p class="text-gray-500 text-center py-4">Nenhuma atividade recente.</p>
        `;
        return;
    }
    
    atividades.forEach((atividade, index) => {
        console.log(` Renderizando atividade ${index + 1}:`, atividade.titulo);
        
        const dataFormatada = new Date(atividade.data).toLocaleDateString('pt-BR');
        const horaFormatada = new Date(atividade.data).toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'});
        
        container.innerHTML += `
            <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center">
                    <i class="fas ${atividade.icone} text-primary-600 text-sm"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900">${atividade.titulo}</p>
                    <p class="text-xs text-gray-500">${dataFormatada} às ${horaFormatada}</p>
                </div>
            </div>
        `;
    });
    
    console.log(' Renderização de atividades concluída');
}

// Função para obter URL da imagem do evento (usa window.getEventImageUrl quando disponível)
function getEventoImagemUrl(imagemNome, eventoId = null) {
    if (typeof window.getEventImageUrl === 'function') {
        var nome = imagemNome || (eventoId ? 'evento_' + eventoId + '.jpg' : null);
        if (!nome) return '../../assets/img/default-event.jpg';
        return window.getEventImageUrl(nome);
    }
    if (!imagemNome) return '../../assets/img/default-event.jpg';
    if (imagemNome.includes('.')) return '../../assets/img/eventos/' + imagemNome;
    var nomeBase = imagemNome || 'evento_' + eventoId;
    return '../../assets/img/eventos/' + nomeBase + '.jpg';
}

// Função para editar evento
async function editarEvento(eventoId) {
    console.log(' Editando evento ID:', eventoId);
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'info',
            title: 'Funcionalidade em desenvolvimento',
            text: 'A edição de eventos será implementada em breve.',
            confirmButtonText: 'OK'
        });
    } else {
        alert('Funcionalidade em desenvolvimento');
    }
}

// Função para excluir evento
async function excluirEvento(eventoId) {
    console.log(' Excluindo evento ID:', eventoId);
    
    let result;
    if (typeof Swal !== 'undefined') {
        result = await Swal.fire({
            title: 'Tem certeza?',
            text: "Esta ação não pode ser desfeita!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        });
    } else {
        result = { isConfirmed: confirm('Tem certeza que deseja excluir este evento?') };
    }

    if (result.isConfirmed) {
        console.log(' Confirmação de exclusão aceita');
        
        try {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Excluindo evento...',
                    text: 'Aguarde enquanto processamos.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            }
            
            // Implementar exclusão aqui
            console.log(' Enviando requisição de exclusão...');
            
            // Simular exclusão por enquanto
            await new Promise(resolve => setTimeout(resolve, 1000));
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Evento excluído!',
                    text: 'O evento foi excluído com sucesso.',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                alert('Evento excluído com sucesso.');
            }
            
            // Recarregar dashboard
            carregarDashboard();
            
        } catch (error) {
            console.error('"¥ Erro ao excluir evento:', error);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro ao excluir evento',
                    text: error.message
                });
            } else {
                alert('Erro ao excluir evento: ' + error.message);
            }
        }
    } else {
        console.log(' Exclusão cancelada pelo usuário');
    }
}

// Carregar dashboard quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
    console.log(' DOMContentLoaded - Iniciando dashboard');
    carregarDashboard();
}); 
