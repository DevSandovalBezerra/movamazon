if (window.getApiBase) { window.getApiBase(); }
// URL da imagem do evento (usa window.getEventImageUrl quando disponÃƒÆ’Ã‚Â­vel)
function getEventImageUrlLocal(imagem) {
    if (typeof window.getEventImageUrl === 'function') return window.getEventImageUrl(imagem);
    return imagem ? '../../assets/img/eventos/' + imagem : 'https://placehold.co/400x200?text=Evento';
}

// FunÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o para carregar eventos na pÃƒÆ’Ã‚Â¡gina Meus Eventos
async function carregarMeusEventos(pagina = 1) {
    try {
        console.log('ÃƒÂ°Ã…Â¸ââ‚¬Å“Ã‚Â¡ Iniciando carregamento de eventos - PÃƒÆ’Ã‚Â¡gina:', pagina);
        
        document.getElementById('loading').style.display = 'block';
        document.getElementById('eventos-container').style.display = 'none';
        document.getElementById('error-message').style.display = 'none';
        document.getElementById('paginacao').style.display = 'none';

        // Coletar filtros
        const status = document.getElementById('filtro-status')?.value || '';
        const filtroData = document.getElementById('filtro-data')?.value || '';
        const busca = document.getElementById('busca')?.value || '';
        const porPagina = 10;

        console.log('ÃƒÂ°Ã…Â¸ââ‚¬Å“ââ‚¬Â¹ Filtros coletados:', { status, filtroData, busca, pagina, porPagina });

        // Construir parÃƒÆ’Ã‚Â¢metros
        const params = new URLSearchParams({
            pagina: pagina.toString(),
            por_pagina: porPagina.toString()
        });
        
        if (status) params.append('status', status);
        if (filtroData) params.append('data', filtroData);
        if (busca) params.append('busca', busca);

        const apiUrl = `${window.API_BASE || '/api'}/organizador/eventos/list.php?${params.toString()}`;
        console.log('ÃƒÂ°Ã…Â¸Ã…â€™Ã‚Â URL da requisiÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o:', apiUrl);

        const response = await fetch(apiUrl);
        console.log('[EVENTOS] Response status:', response.status);
        
        const responseText = await response.text();
        console.log('[EVENTOS] Response text:', responseText);
        
        const data = JSON.parse(responseText);
        console.log('[EVENTOS] Data parsed:', data);
        
        if (data.success) {
            console.log('ÃƒÂ¢Ã…â€œââ‚¬Â¦ Eventos carregados com sucesso:', data.data.eventos.length, 'eventos');
            console.log('ÃƒÂ¢Ã…â€œââ‚¬Â¦ Dados completos da API:', data);
            renderizarListaMeusEventos(data.data.eventos);
            renderizarPaginacao(data.data.paginacao, pagina);
            
            document.getElementById('loading').style.display = 'none';
            document.getElementById('eventos-container').style.display = 'block';
            if (data.data.paginacao && data.data.paginacao.total_paginas > 1) {
                document.getElementById('paginacao').style.display = 'block';
            }
        } else {
            console.error('ÃƒÂ¢Ã‚ÂÃ…â€™ Erro na API:', data);
            throw new Error(data.message || 'Erro ao carregar eventos');
        }
    } catch (error) {
        console.error('ÃƒÂ°Ã…Â¸ââ‚¬â„¢Ã‚Â¥ Erro ao carregar eventos:', error);
        document.getElementById('loading').style.display = 'none';
        document.getElementById('error-message').style.display = 'block';
    }
}

// FunÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o para verificar status de implementaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o do evento
async function verificarStatusImplementacao(eventoId) {
    try {
        const response = await fetch(`${window.API_BASE || '/api'}/evento/check_implementation_status.php?id=${eventoId}`);
        const data = await response.json();
        
        if (data.success) {
            return data.status;
        }
        return null;
    } catch (error) {
        console.error('Erro ao verificar status:', error);
        return null;
    }
}

// FunÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o para renderizar checklist de implementaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o com checkboxes individuais
function renderizarChecklistImplementacao(status) {
    if (!status || !status.features) return '';
    
    const progresso = status.progresso || {};
    const concluidos = progresso.concluidos || 0;
    const total = progresso.total || 0;
    const percentual = progresso.percentual || 0;
    const pode_ativar = status.pode_ativar || false;
    const pendencias = status.pendencias_obrigatorias || [];
    
    // Mapear nomes para exibiÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o (trocar ProgramaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o por Checklist)
    const nomeExibicao = {
        'ProgramaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o': 'Checklist',
        'programacao': 'Checklist'
    };
    
    // Mapear pendÃƒÆ’Ã‚Âªncias tambÃƒÆ’Ã‚Â©m
    const pendenciasMapeadas = pendencias.map(p => nomeExibicao[p] || p);
    
    let html = `
        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-tasks text-gray-500"></i>
                    <span class="text-sm font-medium text-gray-700">ConfiguraÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o do Evento</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-sm font-semibold text-gray-700">${concluidos}/${total}</span>
                    <div class="w-20 h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-green-400 to-green-500 rounded-full transition-all duration-500" 
                             style="width: ${percentual}%"></div>
                    </div>
                    <span class="text-xs text-gray-500">${percentual}%</span>
                </div>
            </div>
            
            ${pode_ativar ? `
                <div class="mb-3 p-2 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-check-circle text-green-500"></i>
                        <span class="text-sm font-medium text-green-700">Pronto para ativar</span>
                    </div>
                </div>
            ` : pendencias.length > 0 ? `
                <div class="mb-3 p-2 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center space-x-2 mb-2">
                        <i class="fas fa-exclamation-triangle text-red-500"></i>
                        <span class="text-sm font-medium text-red-700">NÃƒÆ’Ã‚Â£o ÃƒÆ’Ã‚Â© possÃƒÆ’Ã‚Â­vel ativar o evento</span>
                    </div>
                    <p class="text-xs text-red-600 mb-2 font-medium">Configure as seguintes features obrigatÃƒÆ’Ã‚Â³rias:</p>
                    <ul class="text-xs text-red-600 ml-4 space-y-1">
                        ${pendenciasMapeadas.map(p => `<li class="flex items-center space-x-1">
                            <i class="fas fa-circle text-red-400 text-[6px]"></i>
                            <span>${p}</span>
                        </li>`).join('')}
                    </ul>
                </div>
            ` : ''}
            
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
    `;
    
    status.features.forEach(feature => {
        // Verificar se estÃƒÆ’Ã‚Â¡ configurado: usar count > 0 como fonte de verdade principal
        const count = parseInt(feature.count) || 0;
        const isCompleted = count > 0 || feature.configurado === true || feature.configurado === 'true';
        const isObrigatorio = feature.obrigatorio === true || feature.obrigatorio === 'true';
        
        // Trocar nome "ProgramaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o" por "Checklist" apenas na exibiÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o
        const nomeExibir = nomeExibicao[feature.nome] || feature.nome;
        
        let checkboxClass = 'rounded border-2 flex items-center justify-center w-5 h-5 ';
        let textClass = 'text-sm ';
        let iconClass = (feature.icon || 'fas fa-circle') + ' ';
        
        if (isCompleted) {
            checkboxClass += 'bg-green-500 border-green-500 text-white';
            textClass += 'text-gray-700 font-medium';
            iconClass += 'text-green-500';
        } else if (isObrigatorio) {
            checkboxClass += 'bg-red-100 border-red-300 text-red-600';
            textClass += 'text-red-700 font-medium';
            iconClass += 'text-red-400';
        } else {
            checkboxClass += 'bg-gray-100 border-gray-300 text-gray-400';
            textClass += 'text-gray-500';
            iconClass += 'text-gray-400';
        }
        
        const countText = feature.count > 0 ? ` (${feature.count})` : '';
        const linkAttr = !isCompleted && feature.link ? `onclick="window.location.href='${feature.link}'" style="cursor: pointer;"` : '';
        const titleAttr = !isCompleted && feature.link ? `title="Clique para configurar ${nomeExibir}"` : '';
        
        html += `
            <div ${linkAttr} ${titleAttr} class="flex items-center space-x-2 p-2 rounded hover:bg-white transition-colors ${!isCompleted && feature.link ? 'cursor-pointer hover:shadow-sm' : ''}">
                <div class="${checkboxClass}">
                    ${isCompleted ? '<i class="fas fa-check text-xs"></i>' : ''}
                </div>
                <i class="${iconClass} text-xs"></i>
                <span class="${textClass} truncate">${nomeExibir}${countText}</span>
            </div>
        `;
    });
    
    html += `
            </div>
        </div>
    `;
    
    return html;
}

// FunÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o para renderizar lista de eventos
function renderizarListaMeusEventos(eventos) {
    console.log('ÃƒÂ°Ã…Â¸Ã…Â½Ã‚Â¨ Renderizando lista de eventos:', eventos.length, 'eventos');
    console.log('ÃƒÂ°Ã…Â¸Ã…Â½Ã‚Â¨ Eventos recebidos:', eventos);
    
    const container = document.getElementById('eventos-container');
    console.log('ÃƒÂ°Ã…Â¸Ã…Â½Ã‚Â¨ Container encontrado:', container);
    
    if (!eventos || eventos.length === 0) {
        console.log('ÃƒÂ°Ã…Â¸Ã…Â½Ã‚Â¨ Nenhum evento encontrado, exibindo mensagem');
        container.innerHTML = `
            <div class="col-span-full text-center py-12">
                <div class="text-gray-500 text-lg mb-2">Nenhum evento encontrado</div>
                <div class="text-gray-400 text-sm">Crie seu primeiro evento para comeÃƒÆ’Ã‚Â§ar</div>
            </div>
        `;
        return;
    }
    
    let html = '';
    
    for (const evento of eventos) {
        // NormalizaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Âµes para compatibilidade com a API atual
        if (!evento.status_traduzido) {
            const mapa = {
                'ativo': 'Ativo',
                'inativo': 'Inativo',
                'cancelado': 'Cancelado',
                'finalizado': 'Finalizado',
                'pausado': 'Pausado',
                'rascunho': 'Rascunho'
            };
            evento.status_traduzido = mapa[evento.status] || evento.status || 'ÃƒÂ¢ââ€šÂ¬ââ‚¬Â';
        }
        if (!evento.imagem_url) {
            evento.imagem_url = evento.imagem ? getEventImageUrlLocal(evento.imagem) : null;
        }
        if (!evento.data_criacao_formatada) {
            if (evento.data_criacao) {
                const d = new Date((evento.data_criacao || '').replace(' ', 'T'));
                if (!isNaN(d)) {
                    evento.data_criacao_formatada = d.toLocaleString('pt-BR');
                }
            }
        }
        const statusClass = {
            'ativo': 'bg-green-100 text-green-800',
            'inativo': 'bg-gray-100 text-gray-800',
            'cancelado': 'bg-red-100 text-red-800',
            'finalizado': 'bg-blue-100 text-blue-800',
            'pausado': 'bg-yellow-100 text-yellow-800',
            'rascunho': 'bg-gray-100 text-gray-800'
        }[evento.status] || 'bg-gray-100 text-gray-800';
        
        html += `
            <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 border border-gray-200 overflow-hidden">
                <!-- Imagem do Evento -->
                <div class="relative overflow-hidden">
                    <img src="${getEventImageUrlLocal(evento.imagem)}" alt="${evento.nome}" class="w-72 h-56 object-contain group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute top-4 left-4">
                        <span class="px-3 py-1 rounded-full text-xs font-medium ${statusClass}">
                            ${evento.status_traduzido}
                        </span>
                    </div>
                </div>
                
                <!-- ConteÃƒÆ’Ã‚Âºdo do Card -->
                <div class="p-6">
                    <!-- TÃƒÆ’Ã‚Â­tulo do Evento -->
                    <h3 class="text-xl font-bold text-gray-900 mb-2 line-clamp-2">${evento.nome}</h3>
                    
                    <!-- Data e Hora -->
                    <div class="flex items-center text-gray-600 mb-2">
                        <i class="fas fa-calendar-alt text-green-500 mr-2"></i>
                        <span>${evento.data_inicio_formatada}</span>
                        ${evento.hora_inicio ? `<span class="ml-2">${evento.hora_inicio}</span>` : ''}
                    </div>
                    
                    <!-- LocalizaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o -->
                    <div class="flex items-center text-gray-600 mb-4">
                        <i class="fas fa-map-marker-alt text-green-500 mr-2"></i>
                        <span>${evento.cidade ? `${evento.cidade}/${evento.estado}` : 'Local nÃƒÆ’Ã‚Â£o informado'}</span>
                    </div>
                    
                    <!-- DescriÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o -->
                    <p class="text-gray-600 text-sm mb-4 line-clamp-3">${evento.descricao || 'Sem descriÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o'}</p>
                    
                    <!-- Checklist de ImplementaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o -->
                    <div id="checklist-${evento.id}" class="mb-4">
                        <!-- SerÃƒÆ’Ã‚Â¡ preenchido via JavaScript -->
                    </div>
                    
                    <!-- BotÃƒÆ’Ã‚Âµes de AÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o -->
                    <div class="flex flex-wrap gap-2 mb-4">
                        <a href="lotes/index.php?evento_id=${evento.id}" 
                           class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md bg-green-50 text-green-600 hover:bg-green-100 transition-colors">
                            <i class="fas fa-tags mr-1 text-xs"></i>
                            Lotes de InscriÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o
                        </a>
                        <a href="kits/index.php?evento_id=${evento.id}" 
                           class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md bg-purple-50 text-purple-600 hover:bg-purple-100 transition-colors">
                            <i class="fas fa-box mr-1 text-xs"></i>
                            Kits
                        </a>
                        <a href="produtos-extras/index.php?evento_id=${evento.id}" 
                           class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md bg-orange-50 text-orange-600 hover:bg-orange-100 transition-colors">
                            <i class="fas fa-gift mr-1 text-xs"></i>
                            Produtos
                        </a>
                    </div>
                    
                    <!-- BotÃƒÆ’Ã‚Âµes Principais -->
                    <div class="flex space-x-2">
                        <button onclick="editarEvento(${evento.id})" 
                                class="inline-flex items-center bg-blue-600 text-white px-3 py-2 rounded-md hover:bg-blue-700 transition-colors font-medium text-sm">
                            <i class="fas fa-edit mr-1 text-sm"></i>
                            Editar
                        </button>
                        <button onclick="excluirEvento(${evento.id})" 
                                class="inline-flex items-center bg-red-600 text-white px-3 py-2 rounded-md hover:bg-red-700 transition-colors font-medium text-sm">
                            <i class="fas fa-trash mr-1 text-sm"></i>
                            Excluir
                        </button>
                    </div>
                </div>
            </div>
        `;
    }
    
    container.innerHTML = html;
    console.log('ÃƒÂ¢Ã…â€œââ‚¬Â¦ Lista de eventos renderizada com sucesso');
    
    // Carregar checklists de implementaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o para cada evento
    eventos.forEach(async (evento) => {
        try {
            const status = await verificarStatusImplementacao(evento.id);
            if (status) {
                console.log(`[Checklist] Status para evento ${evento.id}:`, status);
                const checklistElement = document.getElementById(`checklist-${evento.id}`);
                if (checklistElement) {
                    checklistElement.innerHTML = renderizarChecklistImplementacao(status);
                }
            } else {
                console.warn(`[Checklist] Status nÃƒÆ’Ã‚Â£o retornado para evento ${evento.id}`);
            }
        } catch (error) {
            console.error(`Erro ao carregar checklist para evento ${evento.id}:`, error);
        }
    });
}

// FunÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o para visualizar evento (placeholder)
function visualizarEvento(eventoId) {
    console.log('ÃƒÂ°Ã…Â¸ââ‚¬ËœÃ‚ÂÃƒÂ¯Ã‚Â¸Ã‚Â Visualizando evento:', eventoId);
    // Implementar visualizaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o do evento
    alert('Funcionalidade de visualizaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o serÃƒÆ’Ã‚Â¡ implementada em breve!');
}

// FunÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o para editar evento
async function editarEvento(eventoId) {
    console.log('ÃƒÂ¢Ã…â€œÃ‚ÂÃƒÂ¯Ã‚Â¸Ã‚Â Editando evento ID:', eventoId);
    
    try {
        // Mostrar loading
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Carregando dados...',
                text: 'Aguarde enquanto buscamos as informaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Âµes do evento.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
        
        // Buscar dados do evento usando API existente
        const response = await fetch(`${window.API_BASE || '/api'}/evento/get.php?id=${eventoId}`);
        const data = await response.json();
        
        if (data.success) {
            console.log('ÃƒÂ¢Ã…â€œââ‚¬Â¦ Dados do evento carregados:', data.evento);
            
            // Fechar loading e abrir modal de ediÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o
            if (typeof Swal !== 'undefined') {
                Swal.close();
            }
            
            // Abrir modal de ediÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o
            abrirModalEdicao(data.evento);
            
        } else {
            throw new Error(data.message || 'Erro ao carregar dados do evento');
        }
        
    } catch (error) {
        console.error('ÃƒÂ°Ã…Â¸ââ‚¬â„¢Ã‚Â¥ Erro ao carregar dados do evento:', error);
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Erro ao carregar evento',
                text: error.message
            });
        } else {
            alert('Erro ao carregar evento: ' + error.message);
        }
    }
}

// FunÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o para abrir modal de ediÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o
function abrirModalEdicao(evento) {
    console.log('ÃƒÂ°Ã…Â¸Ã…Â½Ã‚Â¨ Abrindo modal de ediÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o para:', evento.nome);
    
    // Criar HTML do modal
    const modalHTML = `
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <!-- Header -->
                <div class="flex items-center justify-between p-6 border-b">
                    <h2 class="text-2xl font-bold text-gray-900">Editar Evento</h2>
                    <button onclick="fecharModalEdicao()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <!-- FormulÃƒÆ’Ã‚Â¡rio -->
                <form id="form-editar-evento" class="p-6 space-y-6">
                    <input type="hidden" name="evento_id" value="${evento.id}">
                    
                    <!-- SeÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o: InformaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Âµes BÃƒÆ’Ã‚Â¡sicas -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nome do Evento *</label>
                            <input type="text" name="nome" value="${evento.nome || ''}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <option value="ativo" ${evento.status === 'ativo' ? 'selected' : ''}>Ativo</option>
                                <option value="inativo" ${evento.status === 'inativo' ? 'selected' : ''}>Inativo</option>
                                <option value="cancelado" ${evento.status === 'cancelado' ? 'selected' : ''}>Cancelado</option>
                                <option value="finalizado" ${evento.status === 'finalizado' ? 'selected' : ''}>Finalizado</option>
                                <option value="pausado" ${evento.status === 'pausado' ? 'selected' : ''}>Pausado</option>
                                <option value="rascunho" ${evento.status === 'rascunho' ? 'selected' : ''}>Rascunho</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- SeÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o: Datas e HorÃƒÆ’Ã‚Â¡rio -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Data de InÃƒÆ’Ã‚Â­cio *</label>
                            <input type="date" name="data_inicio" value="${evento.data_inicio && evento.data_inicio !== '0000-00-00' ? evento.data_inicio : ''}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Data de Fim</label>
                            <input type="date" name="data_fim" value="${evento.data_fim && evento.data_fim !== '0000-00-00' ? evento.data_fim : ''}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Hora de InÃƒÆ’Ã‚Â­cio</label>
                            <input type="time" name="hora_inicio" value="${evento.hora_inicio && evento.hora_inicio !== '00:00:00' ? evento.hora_inicio : ''}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                    </div>
                    
                    <!-- SeÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o: Categoria e GÃƒÆ’Ã‚Âªnero -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Categoria</label>
                            <select name="categoria" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <option value="">Selecione...</option>
                                <option value="corrida_rua" ${evento.categoria === 'corrida_rua' ? 'selected' : ''}>Corrida de Rua</option>
                                <option value="caminhada" ${evento.categoria === 'caminhada' ? 'selected' : ''}>Caminhada</option>
                                <option value="ciclismo" ${evento.categoria === 'ciclismo' ? 'selected' : ''}>Ciclismo</option>
                                <option value="natacao" ${evento.categoria === 'natacao' ? 'selected' : ''}>NataÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o</option>
                                <option value="triatlo" ${evento.categoria === 'triatlo' ? 'selected' : ''}>Triatlo</option>
                                <option value="outros" ${evento.categoria === 'outros' ? 'selected' : ''}>Outros</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">GÃƒÆ’Ã‚Âªnero</label>
                            <select name="genero" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <option value="">Selecione...</option>
                                <option value="masculino" ${evento.genero === 'masculino' ? 'selected' : ''}>Masculino</option>
                                <option value="feminino" ${evento.genero === 'feminino' ? 'selected' : ''}>Feminino</option>
                                <option value="misto" ${evento.genero === 'misto' ? 'selected' : ''}>Misto</option>
                                <option value="livre" ${evento.genero === 'livre' ? 'selected' : ''}>Livre</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- SeÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o: EndereÃƒÆ’Ã‚Â§o Completo -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">CEP</label>
                            <input type="text" name="cep" value="${evento.cep || ''}" placeholder="00000-000"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Logradouro</label>
                            <input type="text" name="logradouro" value="${evento.logradouro || ''}" placeholder="Rua, Avenida, etc."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">NÃƒÆ’Ã‚Âºmero</label>
                            <input type="text" name="numero" value="${evento.numero || ''}" placeholder="123"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                    </div>
                    
                    <!-- SeÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o: LocalizaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Local</label>
                            <input type="text" name="local" value="${evento.local || ''}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cidade</label>
                            <input type="text" name="cidade" value="${evento.cidade || ''}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                            <select name="estado" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <option value="">Selecione...</option>
                                <option value="AC" ${evento.estado === 'AC' ? 'selected' : ''}>Acre</option>
                                <option value="AL" ${evento.estado === 'AL' ? 'selected' : ''}>Alagoas</option>
                                <option value="AP" ${evento.estado === 'AP' ? 'selected' : ''}>AmapÃƒÆ’Ã‚Â¡</option>
                                <option value="AM" ${evento.estado === 'AM' ? 'selected' : ''}>Amazonas</option>
                                <option value="BA" ${evento.estado === 'BA' ? 'selected' : ''}>Bahia</option>
                                <option value="CE" ${evento.estado === 'CE' ? 'selected' : ''}>CearÃƒÆ’Ã‚Â¡</option>
                                <option value="DF" ${evento.estado === 'DF' ? 'selected' : ''}>Distrito Federal</option>
                                <option value="ES" ${evento.estado === 'ES' ? 'selected' : ''}>EspÃƒÆ’Ã‚Â­rito Santo</option>
                                <option value="GO" ${evento.estado === 'GO' ? 'selected' : ''}>GoiÃƒÆ’Ã‚Â¡s</option>
                                <option value="MA" ${evento.estado === 'MA' ? 'selected' : ''}>MaranhÃƒÆ’Ã‚Â£o</option>
                                <option value="MT" ${evento.estado === 'MT' ? 'selected' : ''}>Mato Grosso</option>
                                <option value="MS" ${evento.estado === 'MS' ? 'selected' : ''}>Mato Grosso do Sul</option>
                                <option value="MG" ${evento.estado === 'MG' ? 'selected' : ''}>Minas Gerais</option>
                                <option value="PA" ${evento.estado === 'PA' ? 'selected' : ''}>ParÃƒÆ’Ã‚Â¡</option>
                                <option value="PB" ${evento.estado === 'PB' ? 'selected' : ''}>ParaÃƒÆ’Ã‚Â­ba</option>
                                <option value="PR" ${evento.estado === 'PR' ? 'selected' : ''}>ParanÃƒÆ’Ã‚Â¡</option>
                                <option value="PE" ${evento.estado === 'PE' ? 'selected' : ''}>Pernambuco</option>
                                <option value="PI" ${evento.estado === 'PI' ? 'selected' : ''}>PiauÃƒÆ’Ã‚Â­</option>
                                <option value="RJ" ${evento.estado === 'RJ' ? 'selected' : ''}>Rio de Janeiro</option>
                                <option value="RN" ${evento.estado === 'RN' ? 'selected' : ''}>Rio Grande do Norte</option>
                                <option value="RS" ${evento.estado === 'RS' ? 'selected' : ''}>Rio Grande do Sul</option>
                                <option value="RO" ${evento.estado === 'RO' ? 'selected' : ''}>RondÃƒÆ’Ã‚Â´nia</option>
                                <option value="RR" ${evento.estado === 'RR' ? 'selected' : ''}>Roraima</option>
                                <option value="SC" ${evento.estado === 'SC' ? 'selected' : ''}>Santa Catarina</option>
                                <option value="SP" ${evento.estado === 'SP' ? 'selected' : ''}>SÃƒÆ’Ã‚Â£o Paulo</option>
                                <option value="SE" ${evento.estado === 'SE' ? 'selected' : ''}>Sergipe</option>
                                <option value="TO" ${evento.estado === 'TO' ? 'selected' : ''}>Tocantins</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- SeÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o: URL do Mapa -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">URL do Mapa</label>
                        <input type="url" name="url_mapa" value="${evento.url_mapa || ''}" placeholder="https://maps.google.com/..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    
                    <!-- SeÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o: Datas de InscriÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Âµes -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Data Fim das InscriÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Âµes</label>
                            <input type="date" name="data_fim_inscricoes" value="${evento.data_fim_inscricoes && evento.data_fim_inscricoes !== '0000-00-00' ? evento.data_fim_inscricoes : ''}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Hora Fim das InscriÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Âµes</label>
                            <input type="time" name="hora_fim_inscricoes" value="${evento.hora_fim_inscricoes && evento.hora_fim_inscricoes !== '00:00:00' ? evento.hora_fim_inscricoes : ''}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                    </div>
                    
                    <!-- SeÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o: ConfiguraÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Âµes de Vagas e Taxas -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Limite de Vagas</label>
                            <input type="number" name="limite_vagas" value="${evento.limite_vagas || ''}" min="1"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="exibir_retirada_kit" value="1" ${evento.exibir_retirada_kit ? 'checked' : ''}
                                       class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Exibir Retirada de Kit</span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- SeÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o: ConfiguraÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Âµes Financeiras -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Taxa Setup (R$)</label>
                            <input type="number" step="0.01" name="taxa_setup" value="${evento.taxa_setup || ''}" min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Taxa Gratuitas (R$)</label>
                            <input type="number" step="0.01" name="taxa_gratuitas" value="${evento.taxa_gratuitas || ''}" min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Taxa Pagas (R$)</label>
                            <input type="number" step="0.01" name="taxa_pagas" value="${evento.taxa_pagas || ''}" min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">% Repasse</label>
                            <input type="number" step="0.01" name="percentual_repasse" value="${evento.percentual_repasse || ''}" min="0" max="100"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                    </div>
                    
                    <!-- SeÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o: DescriÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">DescriÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o</label>
                        <textarea name="descricao" rows="4" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">${evento.descricao || ''}</textarea>
                    </div>
                    
                    <!-- SeÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o: Regulamento -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Regulamento</label>
                        <textarea name="regulamento" rows="6" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">${evento.regulamento || ''}</textarea>
                    </div>
                    
                    <!-- SeÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o: Upload de Imagem -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Imagem do Evento</label>
                        <div class="flex items-center space-x-4">
                            <div class="flex-1">
                                <input type="file" name="imagem" accept="image/*" onchange="previewNovaImagem(this, ${evento.id})"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <p class="text-xs text-gray-500 mt-1">Formatos: JPG, PNG, WEBP, SVG. MÃƒÆ’Ã‚Â¡ximo 5MB.</p>
                            </div>
                            <div id="preview-imagem-${evento.id}" class="w-20 h-20 rounded-lg overflow-hidden border">
                                ${evento.imagem ? `
                                    <img src="${getEventImageUrlLocal(evento.imagem)}" 
                                         alt="Imagem atual" class="w-full h-full object-cover">
                                ` : `
                                    <div class="w-full h-full flex items-center justify-center bg-gray-100">
                                        <i class="fas fa-image text-gray-400 text-xl"></i>
                                    </div>
                                `}
                            </div>
                        </div>
                    </div>
                    
                    <!-- BotÃƒÆ’Ã‚Âµes -->
                    <div class="flex items-center justify-end space-x-4 pt-6 border-t">
                        <button type="button" onclick="fecharModalEdicao()" 
                                class="px-6 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                            <i class="fas fa-save mr-2"></i>
                            Salvar AlteraÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Âµes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    // Adicionar modal ao DOM
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Configurar envio do formulÃƒÆ’Ã‚Â¡rio
    document.getElementById('form-editar-evento').addEventListener('submit', salvarEdicaoEvento);

    // Verificar checklist para habilitar/desabilitar opÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o "Ativo"
    (async () => {
        try {
            const status = await verificarStatusImplementacao(evento.id);
            if (!status) return;
            
            const pode_ativar = status.pode_ativar || false;
            const pendentes = status.pendencias_obrigatorias || [];
            
            const selectStatus = document.querySelector('#form-editar-evento select[name="status"]');
            const optionAtivo = selectStatus?.querySelector('option[value="ativo"]');
            const statusContainer = selectStatus?.parentElement;
            
            // Remover aviso anterior se existir
            const avisoAnterior = document.getElementById('aviso-status-ativo');
            if (avisoAnterior) {
                avisoAnterior.remove();
            }
            
            // Criar novo aviso
            const aviso = document.createElement('div');
            aviso.id = 'aviso-status-ativo';
            aviso.className = 'mt-2 p-3 rounded-lg text-sm';
            
            if (!pode_ativar && pendentes.length > 0) {
                if (optionAtivo) {
                    optionAtivo.disabled = true;
                    optionAtivo.title = 'Configure todas as features obrigatÃƒÆ’Ã‚Â³rias primeiro';
                }
                aviso.className += ' bg-red-50 border border-red-200';
                aviso.innerHTML = `
                    <div class="flex items-start space-x-2">
                        <i class="fas fa-exclamation-triangle text-red-500 mt-0.5"></i>
                        <div>
                            <p class="font-medium text-red-700 mb-1">NÃƒÆ’Ã‚Â£o ÃƒÆ’Ã‚Â© possÃƒÆ’Ã‚Â­vel ativar o evento</p>
                            <p class="text-red-600 mb-2">Configure primeiro as seguintes features obrigatÃƒÆ’Ã‚Â³rias:</p>
                            <ul class="list-disc list-inside text-red-600 space-y-1">
                                ${pendentes.map(p => `<li>${p}</li>`).join('')}
                            </ul>
                        </div>
                    </div>
                `;
            } else {
                if (optionAtivo) {
                    optionAtivo.disabled = false;
                    optionAtivo.removeAttribute('title');
                }
                aviso.className += ' bg-green-50 border border-green-200';
                aviso.innerHTML = `
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-check-circle text-green-500"></i>
                        <p class="text-green-700 font-medium">Checklist completo. VocÃƒÆ’Ã‚Âª pode ativar o evento.</p>
                    </div>
                `;
            }
            
            if (statusContainer) {
                statusContainer.appendChild(aviso);
            }
        } catch (e) {
            console.warn('NÃƒÆ’Ã‚Â£o foi possÃƒÆ’Ã‚Â­vel validar checklist para status ativo:', e);
        }
    })();
}

// FunÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o para fechar modal de ediÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o
function fecharModalEdicao() {
    const modal = document.querySelector('.fixed.inset-0.bg-black.bg-opacity-50');
    if (modal) {
        modal.remove();
    }
}

// FunÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o para salvar ediÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o do evento
async function salvarEdicaoEvento(event) {
    event.preventDefault();
    
    console.log('ÃƒÂ°Ã…Â¸ââ‚¬â„¢Ã‚Â¾ Salvando ediÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o do evento...');
    
    try {
        // Mostrar loading
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Salvando alteraÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Âµes...',
                text: 'Aguarde enquanto processamos.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
        
        // Preparar dados do formulÃƒÆ’Ã‚Â¡rio
        const formData = new FormData(event.target);
        const eventoId = formData.get('evento_id');
        const statusSelecionado = (formData.get('status') || '').toString();

        // Bloquear ativaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o se checklist incompleto
        if (statusSelecionado === 'ativo') {
            try {
                const status = await verificarStatusImplementacao(eventoId);
                if (status) {
                    const pendentes = obterItensPendentes(status);
                    if (pendentes.length > 0) {
                        if (typeof Swal !== 'undefined') {
                            await Swal.fire({
                                icon: 'warning',
                                title: 'NÃƒÆ’Ã‚Â£o ÃƒÆ’Ã‚Â© possÃƒÆ’Ã‚Â­vel ativar',
                                html: `Conclua antes: <b>${pendentes.join(', ')}</b>.`,
                                confirmButtonColor: '#F59E0B'
                            });
                        } else {
                            alert('NÃƒÆ’Ã‚Â£o ÃƒÆ’Ã‚Â© possÃƒÆ’Ã‚Â­vel ativar. Conclua antes: ' + pendentes.join(', '));
                        }
                        return;
                    }
                }
            } catch (e) {
                console.warn('Falha ao validar checklist antes de ativar:', e);
            }
        }
        
        // Enviar dados usando API existente
        const response = await fetch((window.API_BASE || '/api') + '/evento/update.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            console.log('ÃƒÂ¢Ã…â€œââ‚¬Â¦ Evento atualizado com sucesso');
            
            // Fechar modal
            fecharModalEdicao();
            
            // Feedback de sucesso
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Evento atualizado!',
                    text: 'As alteraÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Âµes foram salvas com sucesso.',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                alert('Evento atualizado com sucesso!');
            }
            
            // Recarregar lista de eventos
            carregarMeusEventos();
            
        } else {
            // Se a resposta tem cÃƒÆ’Ã‚Â³digo 422 (Unprocessable Entity), ÃƒÆ’Ã‚Â© erro de validaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o
            if (response.status === 422 && data.pendencias) {
                const pendentes = Array.isArray(data.pendencias) ? data.pendencias.join(', ') : data.pendencias;
                throw new Error(data.message || `NÃƒÆ’Ã‚Â£o ÃƒÆ’Ã‚Â© possÃƒÆ’Ã‚Â­vel ativar. Configure: ${pendentes}`);
            }
            throw new Error(data.message || 'Erro ao atualizar evento');
        }
        
    } catch (error) {
        console.error('ÃƒÂ°Ã…Â¸ââ‚¬â„¢Ã‚Â¥ Erro ao salvar evento:', error);
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Erro ao salvar evento',
                text: error.message
            });
        } else {
            alert('Erro ao salvar evento: ' + error.message);
        }
    }
}

// FunÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o para excluir evento
async function excluirEvento(eventoId) {
    console.log('ÃƒÂ°Ã…Â¸ââ‚¬â€ââ‚¬ËœÃƒÂ¯Ã‚Â¸Ã‚Â Iniciando exclusÃƒÆ’Ã‚Â£o do evento:', eventoId);
    
    try {
        // 1. Verificar dependÃƒÆ’Ã‚Âªncias primeiro
        const response = await fetch(`${window.API_BASE || '/api'}/evento/check_dependencies.php?evento_id=${eventoId}`);
        const data = await response.json();
        
        if (!data.success) {
            Swal.fire('Erro', data.message || 'Erro ao verificar dependÃƒÆ’Ã‚Âªncias do evento', 'error');
            return;
        }
        
        const { evento, pode_excluir, motivo_bloqueio, dependencias } = data.data;
        
        // 2. Se nÃƒÆ’Ã‚Â£o pode excluir, mostrar motivo
        if (!pode_excluir) {
            Swal.fire({
                title: 'NÃƒÆ’Ã‚Â£o ÃƒÆ’Ã‚Â© possÃƒÆ’Ã‚Â­vel excluir',
                html: `
                    <div class="text-left">
                        <p class="mb-3"><strong>Motivo:</strong> ${motivo_bloqueio}</p>
                        <p class="mb-3"><strong>DependÃƒÆ’Ã‚Âªncias encontradas:</strong></p>
                        <ul class="list-disc list-inside space-y-1">
                            ${dependencias.map(dep => `
                                <li class="text-red-600">
                                    <strong>${dep.tabela}</strong>: ${dep.total} ${dep.descricao}
                                </li>
                            `).join('')}
                        </ul>
                        <p class="mt-3 text-sm text-gray-600">
                            Entre em contato com o suporte para mais informaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Âµes.
                        </p>
                    </div>
                `,
                icon: 'error',
                confirmButtonText: 'Entendi'
            });
            return;
        }
        
        // 3. Se tem dependÃƒÆ’Ã‚Âªncias nÃƒÆ’Ã‚Â£o crÃƒÆ’Ã‚Â­ticas, avisar
        if (dependencias.length > 0) {
            const dependenciasNaoCriticas = dependencias.filter(dep => dep.nivel !== 'CRÃƒÆ’Ã‚ÂTICO');
            
            if (dependenciasNaoCriticas.length > 0) {
                const result = await Swal.fire({
                    title: 'AtenÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o!',
                    html: `
                        <div class="text-left">
                            <p class="mb-3">Este evento possui configuraÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Âµes que serÃƒÆ’Ã‚Â£o perdidas:</p>
                            <ul class="list-disc list-inside space-y-1">
                                ${dependenciasNaoCriticas.map(dep => `
                                    <li class="text-orange-600">
                                        <strong>${dep.tabela}</strong>: ${dep.total} ${dep.descricao}
                                    </li>
                                `).join('')}
                            </ul>
                            <p class="mt-3 text-sm text-gray-600">
                                Tem certeza que deseja continuar?
                            </p>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sim, excluir',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280'
                });
                
                if (!result.isConfirmed) {
                    return;
                }
            }
        }
        
        // 4. Confirmar exclusÃƒÆ’Ã‚Â£o final
        const confirmResult = await Swal.fire({
            title: 'Confirmar ExclusÃƒÆ’Ã‚Â£o',
            html: `
                <div class="text-center">
                    <p class="mb-3">Tem certeza que deseja excluir o evento:</p>
                    <p class="font-bold text-lg">"${evento.nome}"</p>
                    <p class="mt-3 text-sm text-gray-600">
                        Esta aÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o nÃƒÆ’Ã‚Â£o pode ser desfeita.
                    </p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sim, excluir',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280'
        });
        
        if (!confirmResult.isConfirmed) {
            return;
        }
        
        // 5. Executar exclusÃƒÆ’Ã‚Â£o
        const deleteResponse = await fetch((window.API_BASE || '/api') + '/evento/soft_delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `evento_id=${eventoId}&motivo_exclusao=ExclusÃƒÆ’Ã‚Â£o solicitada pelo organizador`
        });
        
        const deleteData = await deleteResponse.json();
        
        if (deleteData.success) {
            Swal.fire({
                title: 'Evento ExcluÃƒÆ’Ã‚Â­do!',
                text: 'O evento foi excluÃƒÆ’Ã‚Â­do com sucesso.',
                icon: 'success',
                confirmButtonText: 'OK'
            }).then(() => {
                // Recarregar a lista de eventos
                carregarMeusEventos();
            });
        } else {
            Swal.fire('Erro', deleteData.message || 'Erro ao excluir evento', 'error');
        }
        
    } catch (error) {
        console.error('Erro ao excluir evento:', error);
        Swal.fire('Erro', 'Erro inesperado ao excluir evento', 'error');
    }
}

// FunÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o para aplicar filtros
function aplicarFiltros() {
    console.log('ÃƒÂ°Ã…Â¸ââ‚¬ÂÃ‚Â Aplicando filtros de eventos');
    
    // Coletar valores dos filtros
    const status = document.getElementById('filtro-status').value;
    const data = document.getElementById('filtro-data').value;
    const busca = document.getElementById('busca').value;
    
    console.log('ÃƒÂ°Ã…Â¸ââ‚¬Å“ââ‚¬Â¹ Filtros aplicados:', { status, data, busca });
    
    // Recarregar eventos com os filtros
    carregarMeusEventos(1);
}

// FunÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o para renderizar paginaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o
function renderizarPaginacao(paginacao, paginaAtual) {
    const container = document.getElementById('paginas');
    const btnAnterior = document.getElementById('btn-anterior');
    const btnProximo = document.getElementById('btn-proximo');
    
    if (!paginacao || paginacao.total_paginas <= 1) {
        document.getElementById('paginacao').style.display = 'none';
        return;
    }
    
    // Configurar botÃƒÆ’Ã‚Âµes anterior/prÃƒÆ’Ã‚Â³ximo
    btnAnterior.disabled = paginaAtual <= 1;
    btnAnterior.onclick = () => carregarMeusEventos(paginaAtual - 1);
    
    btnProximo.disabled = paginaAtual >= paginacao.total_paginas;
    btnProximo.onclick = () => carregarMeusEventos(paginaAtual + 1);
    
    // Gerar pÃƒÆ’Ã‚Â¡ginas
    container.innerHTML = '';
    const inicio = Math.max(1, paginaAtual - 2);
    const fim = Math.min(paginacao.total_paginas, paginaAtual + 2);
    
    for (let i = inicio; i <= fim; i++) {
        const btn = document.createElement('button');
        btn.className = `px-3 py-2 text-sm font-medium rounded-lg ${
            i === paginaAtual 
                ? 'bg-primary-600 text-white' 
                : 'text-gray-500 bg-white border border-gray-300 hover:bg-gray-50'
        }`;
        btn.textContent = i;
        btn.onclick = () => carregarMeusEventos(i);
        container.appendChild(btn);
    }
}

// FunÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o para preview da nova imagem
function previewNovaImagem(input, eventoId) {
    const preview = document.getElementById(`preview-imagem-${eventoId}`);
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview da nova imagem" class="w-full h-full object-cover">`;
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        // Restaurar imagem original se nenhum arquivo selecionado
        preview.innerHTML = `<div class="w-full h-full flex items-center justify-center bg-gray-100">
            <i class="fas fa-image text-gray-400 text-xl"></i>
        </div>`;
    }
}


// Detectar pÃƒÆ’Ã‚Â¡gina e chamar funÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o correta
document.addEventListener('DOMContentLoaded', function() {
    console.log('ÃƒÂ°Ã…Â¸Ã…Â¡ââ€šÂ¬ DOMContentLoaded - Detectando pÃƒÆ’Ã‚Â¡gina');
    
    if (document.getElementById('dashboard-content')) {
        console.log('ÃƒÂ°Ã…Â¸ââ‚¬Å“Ã…Â  PÃƒÆ’Ã‚Â¡gina detectada: Dashboard');
        // carregarDashboard(); // This function is not defined in the original file
    } else if (document.getElementById('eventos-container')) {
        console.log('ÃƒÂ°Ã…Â¸ââ‚¬Å“ââ‚¬Â¦ PÃƒÆ’Ã‚Â¡gina detectada: Meus Eventos');
        console.log('ÃƒÂ°Ã…Â¸ââ‚¬Å“ââ‚¬Â¦ Container encontrado:', document.getElementById('eventos-container'));
        carregarMeusEventos();
    } else {
        console.log('ÃƒÂ¢Ã‚ÂÃ…â€™ Nenhuma pÃƒÆ’Ã‚Â¡gina conhecida detectada');
        console.log('ÃƒÂ¢Ã‚ÂÃ…â€™ Elementos encontrados:', {
            dashboard: document.getElementById('dashboard-content'),
            eventos: document.getElementById('eventos-container')
        });
    }
});

// Helper: obter itens pendentes do checklist
function obterItensPendentes(status) {
    if (!status || !status.features) {
        return [];
    }
    
    // Mapear nomes para exibiÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o (trocar ProgramaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o por Checklist)
    const nomeExibicao = {
        'ProgramaÃƒÆ’Ã‚Â§ÃƒÆ’Ã‚Â£o': 'Checklist',
        'programacao': 'Checklist'
    };
    
    return status.features
        .filter(feature => {
            const count = parseInt(feature.count) || 0;
            const isCompleted = count > 0 || feature.configurado === true || feature.configurado === 'true';
            return feature.obrigatorio && !isCompleted;
        })
        .map(feature => nomeExibicao[feature.nome] || feature.nome);
}
