if (window.getApiBase) { window.getApiBase(); }
/**
 * API: Estados e Cidades
 * Funções para carregar e gerenciar dados de estados e cidades
 */

// Variável para armazenar dados de estados e cidades
let dadosEstadosCidades = null;

/**
 * Carrega estados e preenche o dropdown
 * @returns {Promise<void>}
 */
export async function carregarEstados() {
    console.log('📍 Carregando estados...');
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

    try {
        const response = await fetch(url);
        
        console.log('✅ Resposta recebida:', {
            status: response.status,
            statusText: response.statusText,
            ok: response.ok,
            url: response.url
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();
        console.log('🔎 Dados JSON recebidos:', data);
        console.log('🔍 Verificando estrutura dos dados...');
        console.log('✓ data.estados existe?', !!data.estados);
        console.log('✓ data.estados é array?', Array.isArray(data.estados));
        console.log('✓ Quantidade de estados:', data.estados ? data.estados.length : 'N/A');

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

        console.log('📋 Adicionando estados ao dropdown...');
        data.estados.forEach((estado, index) => {
            console.log(`📋 Estado ${index + 1}:`, estado);
            const opt = document.createElement('option');
            opt.value = estado.sigla;
            opt.textContent = estado.nome;
            select.appendChild(opt);
            console.log(`✔ Opção adicionada: ${estado.sigla} - ${estado.nome}`);
        });

        console.log(`✔ ${data.estados.length} estados carregados no dropdown`);
        console.log(`✓ Total de opções no select: ${select.options.length}`);

        // Verificar se realmente foram adicionados
        const options = Array.from(select.options).map(opt => ({
            value: opt.value,
            text: opt.textContent
        }));
        console.log('📋 Opções finais no select:', options);
    } catch (error) {
        console.error('🔥 Erro ao carregar estados:', error);
        console.error('🔥 Stack trace:', error.stack);
    }
}

/**
 * Carrega cidades de um estado específico
 * @param {string} uf - Sigla do estado
 */
export function carregarCidades(uf = '') {
    console.log('🔄 Carregando cidades para UF:', uf);

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

    console.log(`✔ ${estado.cidades.length} cidades carregadas para ${uf}`);
}
