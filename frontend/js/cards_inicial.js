// Variável global para armazenar dados de estados e cidades
let dadosEstadosCidades = null;

// Função para carregar estados e preencher o dropdown
function carregarEstados() {
    console.log('🌍 Carregando estados...');
    console.log('🔍 Tentando encontrar elemento #filtro-estado...');

    const select = document.getElementById('filtro-estado');
    console.log('📋 Elemento select encontrado:', select);

    if (!select) {
        console.error('❌ Elemento select de estado não encontrado');
        console.log('🔍 Elementos disponíveis na página:');
        console.log(document.querySelectorAll('select'));
        return;
    }

    console.log('📋 Select encontrado, iniciando fetch...');
    const url = '../../assets/estados_cidades/estados-cidades.json';
    console.log('🌐 URL do fetch:', url);
    console.log('🌐 URL completa seria:', window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, '') + '/' + url);

    fetch(url)
        .then(response => {
            console.log('📡 Resposta recebida:', {
                status: response.status,
                statusText: response.statusText,
                ok: response.ok,
                url: response.url
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            return response.json();
        })
        .then(data => {
            console.log('🏛️ Dados JSON recebidos:', data);
            console.log('🔍 Verificando estrutura dos dados...');
            console.log('📊 data.estados existe?', !!data.estados);
            console.log('📊 data.estados é array?', Array.isArray(data.estados));
            console.log('📊 Quantidade de estados:', data.estados ? data.estados.length : 'N/A');

            if (!data.estados || !Array.isArray(data.estados)) {
                console.warn('⚠️ Formato inválido do arquivo estados-cidades.json');
                console.log('📋 Estrutura esperada: { estados: [{ sigla: "AC", nome: "Acre", cidades: [...] }] }');
                console.log('📋 Estrutura recebida:', Object.keys(data));
                return;
            }

            // Armazena dados globalmente para uso posterior
            dadosEstadosCidades = data;
            console.log('💾 Dados armazenados globalmente');

            // Limpa todas as opções exceto a primeira ("Todos os estados")
            const optionsCountBefore = select.options.length;
            select.options.length = 1;
            console.log(`🧹 Limpeza: ${optionsCountBefore} → ${select.options.length} opções`);

            console.log('📝 Adicionando estados ao dropdown...');
            data.estados.forEach((estado, index) => {
                console.log(`📝 Estado ${index + 1}:`, estado);
                const opt = document.createElement('option');
                opt.value = estado.sigla;
                opt.textContent = estado.nome;
                select.appendChild(opt);
                console.log(`✅ Opção adicionada: ${estado.sigla} - ${estado.nome}`);
            });

            console.log(`✅ ${data.estados.length} estados carregados no dropdown`);
            console.log(`📊 Total de opções no select: ${select.options.length}`);

            // Verificar se realmente foram adicionados
            const options = Array.from(select.options).map(opt => ({
                value: opt.value,
                text: opt.textContent
            }));
            console.log('📋 Opções finais no select:', options);
        })
        .catch(error => {
            console.error('💥 Erro ao carregar estados:', error);
            console.error('💥 Stack trace:', error.stack);
        });
}

// Função para carregar cidades de um estado específico
function carregarCidades(uf = '') {
    console.log('🏙️ Carregando cidades para UF:', uf);

    const select = document.getElementById('filtro-cidade');
    if (!select) {
        console.error('❌ Elemento select de cidade não encontrado');
        return;
    }

    // Limpa todas as opções exceto a primeira
    select.options.length = 1;

    if (!uf) {
        console.log('⚠️ UF não informada, mantendo lista vazia');
        return;
    }

    if (!dadosEstadosCidades) {
        console.warn('⚠️ Dados de estados/cidades não carregados ainda');
        return;
    }

    // Busca o estado pela sigla
    const estado = dadosEstadosCidades.estados.find(e => e.sigla === uf);
    if (!estado || !estado.cidades) {
        console.warn(`⚠️ Estado ${uf} não encontrado ou sem cidades`);
        return;
    }

    // Popula o dropdown com as cidades do estado
    estado.cidades.forEach(cidade => {
        const opt = document.createElement('option');
        opt.value = cidade;
        opt.textContent = cidade;
        select.appendChild(opt);
    });

    console.log(`✅ ${estado.cidades.length} cidades carregadas para ${uf}`);
}

// Função para atualizar o estado do contador de eventos
function atualizarContadorEventos(estado, dados = null) {
    const contador = document.getElementById('eventos-count');
    if (!contador) return;

    // Adicionar classe de fade-out para transição suave
    contador.classList.add('fade-out');

    setTimeout(() => {
        switch (estado) {
            case 'carregando':
                contador.innerHTML = `
                    <div class="flex items-center justify-center space-x-2">
                        <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-green-600"></div>
                        <span>Carregando eventos...</span>
                    </div>
                `;
                break;
            case 'sucesso':
                const total = dados ? dados.length : 0;
                contador.innerHTML = `
                    <div class="flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>${total} eventos disponíveis</span>
                    </div>
                `;
                break;
            case 'erro':
                contador.innerHTML = `
                    <div class="flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Erro ao carregar eventos</span>
                    </div>
                `;
                break;
            case 'vazio':
                contador.innerHTML = `
                    <div class="flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.29-1.009-5.824-2.709M15 6.291A7.962 7.962 0 0012 5c-2.34 0-4.29 1.009-5.824 2.709"></path>
                        </svg>
                        <span>Nenhum evento encontrado</span>
                    </div>
                `;
                break;
        }

        // Remover fade-out e adicionar fade-in para transição suave
        contador.classList.remove('fade-out');
        contador.classList.add('fade-in');

        // Remover fade-in após a animação
        setTimeout(() => {
            contador.classList.remove('fade-in');
        }, 300);
    }, 150);
}

// Função para carregar eventos com filtros de cidade e período
function carregarEventos() {
    const cidade = document.getElementById('filtro-cidade') ? document.getElementById('filtro-cidade').value : '';
    const mesAnoDe = document.getElementById('filtro-mes-ano-inicio') ? document.getElementById('filtro-mes-ano-inicio').value : '';
    const mesAnoAte = document.getElementById('filtro-mes-ano-fim') ? document.getElementById('filtro-mes-ano-fim').value : '';

    console.log('🔍 Filtros aplicados:', {
        cidade,
        mesAnoDe,
        mesAnoAte
    });

    // Mostrar spinner de carregamento
    atualizarContadorEventos('carregando');

    let url = '../../../api/evento/list_public.php';
    const params = [];
    if (cidade) params.push('cidade=' + encodeURIComponent(cidade));
    if (mesAnoDe) params.push('mes_ano_de=' + encodeURIComponent(mesAnoDe));
    if (mesAnoAte) params.push('mes_ano_ate=' + encodeURIComponent(mesAnoAte));
    if (params.length) url += '?' + params.join('&');

    console.log('🌐 URL da requisição:', url);

    fetch(url)
        .then(response => {
            console.log('📡 Resposta da API de eventos:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('📊 Dados de eventos recebidos:', data);
            const container = document.getElementById('eventos-dinamicos');
            if (!container) {
                console.error('❌ Container de eventos não encontrado');
                atualizarContadorEventos('erro');
                return;
            }
            container.innerHTML = '';

            if (!data.success || !data.eventos || data.eventos.length === 0) {
                console.log('⚠️ Nenhum evento encontrado');
                atualizarContadorEventos('vazio');
                return;
            }

            console.log(`✅ ${data.eventos.length} eventos encontrados`);
            atualizarContadorEventos('sucesso', data.eventos);

            data.eventos.forEach((evento, index) => {
                const card = renderizarCard(evento, index);
                container.appendChild(card);
            });
        })
        .catch(error => {
            console.error('💥 Erro ao carregar eventos:', error);
            atualizarContadorEventos('erro');
        });
}

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function () {
    console.log('🚀 DOM carregado, iniciando...');
    console.log('🔍 Verificando elementos na página...');

    // Verificar todos os elementos importantes
    const selectEstado = document.getElementById('filtro-estado');
    const selectCidade = document.getElementById('filtro-cidade');
    const btnAplicarFiltros = document.getElementById('btn-aplicar-filtros');

    console.log('🔍 Elementos encontrados:', {
        selectEstado: !!selectEstado,
        selectCidade: !!selectCidade,
        btnAplicarFiltros: !!btnAplicarFiltros
    });

    if (selectEstado) {
        console.log('📋 Select estado encontrado:', selectEstado);
        console.log('📋 Select estado HTML:', selectEstado.outerHTML);
        console.log('📋 Select estado opções iniciais:', selectEstado.options.length);
    } else {
        console.error('❌ Select estado NÃO encontrado!');
        console.log('🔍 Todos os selects na página:', document.querySelectorAll('select'));
        console.log('🔍 Todos os elementos com ID:', document.querySelectorAll('[id]'));
    }

    // Carregar estados primeiro
    console.log('🌍 Iniciando carregamento de estados...');
    carregarEstados();

    // Carregar eventos iniciais após um pequeno delay para garantir que os estados carregaram
    setTimeout(() => {
        console.log('⏰ Timeout executado, carregando eventos...');
        carregarEventos();
    }, 1000); // Aumentei para 1 segundo para dar mais tempo

    // Listeners
    if (selectEstado) {
        selectEstado.addEventListener('change', function () {
            const uf = this.value;
            console.log('🔄 Estado alterado para:', uf);
            carregarCidades(uf);
        });
    }

    if (btnAplicarFiltros) {
        btnAplicarFiltros.addEventListener('click', function () {
            console.log('🔍 Aplicando filtros...');
            carregarEventos();
        });
    }
});

// Função para renderizar um card de evento moderno
function renderizarCard(evento, index) {
    console.log(`🎨 Criando card moderno para evento: ${evento.nome}`);

    // Gerar cores dinâmicas baseadas no nome do evento
    const cores = gerarCoresEvento(evento.nome);

    // Distâncias removidas conforme solicitado

    // Monta o card do evento moderno
    const card = document.createElement('div');
    card.className = 'bg-white rounded-xl shadow-lg hover:shadow-xl hover:border hover:border-green-500 transition-all duration-300 transform hover:-translate-y-1 overflow-hidden animate-fade-in';
    card.style.animationDelay = `${0.1 + (index * 0.1)}s`;

    card.innerHTML = `
        <!-- Seção Visual Superior (60-70% do card) -->
        <div class="relative h-48 overflow-hidden bg-gray-200">
            <!-- Imagem de fundo -->
            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('${getImagemEvento(evento.imagem)}')"></div>
        </div>

        <!-- Seção de Informações Inferior (30-40% do card) -->
        <div class="p-4 bg-white">
            <!-- Título do evento -->
            <h3 class="font-bold text-lg text-gray-800 mb-3 truncate" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${evento.nome}</h3>
            
            <!-- Informações principais -->
            <div class="space-y-3 mb-4">
                <!-- Data e Hora -->
                <div class="flex items-center space-x-2 text-sm text-gray-700">
                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span class="font-medium">${evento.data_formatada || 'Data não informada'}</span>
                    <span class="text-gray-400">•</span>
                    <span>${formatarHora(evento.hora_inicio) || '--:--'}</span>
                </div>
                
                <!-- Localização -->
                <div class="flex items-center space-x-2 text-sm text-gray-700">
                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <span>${evento.local || formatarLocal(evento.cidade, evento.estado)}</span>
                </div>
                
            </div>

            <!-- Informações secundárias -->
            <div class="flex justify-between items-center text-xs text-gray-500 mb-4">
                <div class="flex items-center space-x-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span>${evento.inscritos || 0}/${evento.limite_vagas || 0} inscritos</span>
                </div>

                <div class="text-right">
                    <div class="font-medium text-gray-700 organizador-nome">${getNomeOrganizador(evento)}</div>
                </div>

            </div>

            <!-- Botão de ação -->
            <a href="detalhes-evento.php?id=${evento.id}" 
               class="w-full bg-gradient-to-r from-green-500 to-green-600 text-white px-4 py-3 rounded-lg font-semibold hover:from-green-600 hover:to-green-700 transition-all duration-200 text-sm text-center block shadow-md hover:shadow-lg">
                Inscrições Abertas
            </a>
        </div>
    `;

    console.log(`✅ Card moderno criado para: ${evento.nome}`);
    return card;
}

// Função para gerar cores dinâmicas baseadas no nome do evento
function gerarCoresEvento(nomeEvento) {
    const cores = [{
            primaria: '#3B82F6',
            secundaria: '#1D4ED8'
        }, // Azul
        {
            primaria: '#10B981',
            secundaria: '#047857'
        }, // Verde
        {
            primaria: '#F59E0B',
            secundaria: '#D97706'
        }, // Amarelo
        {
            primaria: '#EF4444',
            secundaria: '#DC2626'
        }, // Vermelho
        {
            primaria: '#8B5CF6',
            secundaria: '#7C3AED'
        }, // Roxo
        {
            primaria: '#EC4899',
            secundaria: '#DB2777'
        }, // Rosa
        {
            primaria: '#06B6D4',
            secundaria: '#0891B2'
        }, // Ciano
        {
            primaria: '#84CC16',
            secundaria: '#65A30D'
        }, // Lima
    ];

    // Usar o hash do nome para escolher cores consistentes
    let hash = 0;
    for (let i = 0; i < nomeEvento.length; i++) {
        hash = nomeEvento.charCodeAt(i) + ((hash << 5) - hash);
    }

    const index = Math.abs(hash) % cores.length;
    return cores[index];
}

// Função para formatar localização
function formatarLocal(cidade, estado) {
    if (!cidade && !estado) return 'Local não informado';

    if (cidade && estado) {
        return `${cidade}/${estado}`;
    } else if (cidade) {
        return cidade;
    } else {
        return estado;
    }
}

// Função para corrigir caminho da imagem do evento (usa window.getEventImageUrl quando disponível)
function getImagemEvento(imagem) {
    if (typeof window.getEventImageUrl === 'function') return window.getEventImageUrl(imagem);
    if (!imagem) return 'https://placehold.co/640x360?text=Evento';
    if (/^https?:\/\//.test(imagem)) return imagem;
    return `../../assets/img/eventos/${imagem}`;
}

// Função para formatar hora (converte 07:00:00 para 07:00)
function formatarHora(hora) {
    if (!hora) return null;

    // Se já estiver no formato correto (07:00), retorna como está
    if (typeof hora === 'string' && hora.match(/^\d{1,2}:\d{2}$/)) {
        return hora;
    }

    // Se estiver no formato 07:00:00, remove os segundos
    if (typeof hora === 'string' && hora.match(/^\d{1,2}:\d{2}:\d{2}$/)) {
        return hora.substring(0, 5);
    }

    return hora;
}

// Função para determinar o nome correto da empresa organizadora
function getNomeOrganizador(evento) {
    // Se for o evento específico da UEA, retornar o nome da empresa
    if (evento.nome && evento.nome.includes('SAUIM DE COLEIRA')) {
        return 'UEA - APOIO TÉCNICO MENTE DE CORREDOR';
    }

    // Caso contrário, usar o campo disponível
    if (evento.organizador) {
        return evento.organizador;
    } else if (evento.organizadora) {
        return evento.organizadora;
    } else {
        return 'Organizador não informado';
    }
}

function truncarTexto(texto, maxCaracteres = 20) {
    if (!texto || texto.length <= maxCaracteres) {
        return texto;
    }

    return texto.substring(0, maxCaracteres) + '...';
}

// Adicionar estilos CSS modernos para os cards
const style = document.createElement('style');
style.textContent = `
    .animate-fade-in {
        animation: fadeIn 0.8s ease-out forwards;
        opacity: 0;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(30px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    
    /* Efeitos de hover modernos */
    .hover\\:shadow-xl:hover {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    /* Gradientes modernos */
    .bg-gradient-to-r {
        background: linear-gradient(to right, #10B981, #059669);
    }
    
    /* Backdrop blur para elementos modernos */
    .backdrop-blur-sm {
        backdrop-filter: blur(4px);
    }
    
    /* Transições suaves */
    .transition-all {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    /* Efeito de elevação no hover */
    .hover\\:-translate-y-1:hover {
        transform: translateY(-4px);
    }
    
    /* Animações de entrada escalonadas */
    .animate-fade-in:nth-child(1) { animation-delay: 0.1s; }
    .animate-fade-in:nth-child(2) { animation-delay: 0.2s; }
    .animate-fade-in:nth-child(3) { animation-delay: 0.3s; }
    .animate-fade-in:nth-child(4) { animation-delay: 0.4s; }
    .animate-fade-in:nth-child(5) { animation-delay: 0.5s; }
    .animate-fade-in:nth-child(6) { animation-delay: 0.6s; }
    .animate-fade-in:nth-child(7) { animation-delay: 0.7s; }
    .animate-fade-in:nth-child(8) { animation-delay: 0.8s; }
    
    /* Melhorar contraste dos textos */
    .text-white {
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    }
    
    /* Efeito de glassmorphism para elementos flutuantes */
    .bg-white\\/20 {
        background-color: rgba(255, 255, 255, 0.2);
    }
    
    /* Melhorar aparência dos botões */
    .shadow-md {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    
    .hover\\:shadow-lg:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }
    
    /* Estilos para truncamento de texto do organizador */
    .truncate {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
    }
    
    /* Garantir que o nome do organizador não quebre linha */
    .organizador-nome {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 150px;
        display: inline-block;
    }
    
    /* Efeito de hover elegante para os cards */
    .hover\\:border:hover {
        border-width: 1px;
    }
    
    .hover\\:border-green-500:hover {
        border-color: #10b981;
    }
    
    /* Sombra discreta no hover */
    .hover\\:shadow-xl:hover {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04), 0 0 0 1px rgba(0, 0, 0, 0.05);
    }
`;
document.head.appendChild(style);