if (window.getApiBase) { window.getApiBase(); }
/**
 * API: Estados e Cidades
 * FunÃƒÂ§ÃƒÂµes para carregar e gerenciar dados de estados e cidades
 */

// VariÃƒÂ¡vel para armazenar dados de estados e cidades
let dadosEstadosCidades = null;

/**
 * Carrega estados e preenche o dropdown
 * @returns {Promise<void>}
 */
export async function carregarEstados() {
    console.log('Ã°Å¸Å’Â Carregando estados...');
    console.log('Ã°Å¸â€Â Tentando encontrar elemento #filtro-estado...');

    const select = document.getElementById('filtro-estado');
    console.log('Ã°Å¸â€œâ€¹ Elemento select encontrado:', select);

    if (!select) {
        console.error('Ã¢ÂÅ’ Elemento select de estado nÃƒÂ£o encontrado');
        console.log('Ã°Å¸â€Â Elementos disponÃƒÂ­veis na pÃƒÂ¡gina:');
        console.log(document.querySelectorAll('select'));
        return;
    }

    console.log('Ã°Å¸â€œâ€¹ Select encontrado, iniciando fetch...');
    const url = '../../assets/estados_cidades/estados-cidades.json';
    console.log('Ã°Å¸Å’Â URL do fetch:', url);

    try {
        const response = await fetch(url);
        
        console.log('Ã°Å¸â€œÂ¡ Resposta recebida:', {
            status: response.status,
            statusText: response.statusText,
            ok: response.ok,
            url: response.url
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();
        console.log('Ã°Å¸Ââ€ºÃ¯Â¸Â Dados JSON recebidos:', data);
        console.log('Ã°Å¸â€Â Verificando estrutura dos dados...');
        console.log('Ã°Å¸â€œÅ  data.estados existe?', !!data.estados);
        console.log('Ã°Å¸â€œÅ  data.estados ÃƒÂ© array?', Array.isArray(data.estados));
        console.log('Ã°Å¸â€œÅ  Quantidade de estados:', data.estados ? data.estados.length : 'N/A');

        if (!data.estados || !Array.isArray(data.estados)) {
            console.warn('Ã¢Å¡Â Ã¯Â¸Â Formato invÃƒÂ¡lido do arquivo estados-cidades.json');
            console.log('Ã°Å¸â€œâ€¹ Estrutura esperada: { estados: [{ sigla: "AC", nome: "Acre", cidades: [...] }] }');
            console.log('Ã°Å¸â€œâ€¹ Estrutura recebida:', Object.keys(data));
            return;
        }

        // Armazena dados globalmente para uso posterior
        dadosEstadosCidades = data;
        console.log('Ã°Å¸â€™Â¾ Dados armazenados globalmente');

        // Limpa todas as opÃƒÂ§ÃƒÂµes exceto a primeira ("Todos os estados")
        const optionsCountBefore = select.options.length;
        select.options.length = 1;
        console.log(`Ã°Å¸Â§Â¹ Limpeza: ${optionsCountBefore} Ã¢â€ â€™ ${select.options.length} opÃƒÂ§ÃƒÂµes`);

        console.log('Ã°Å¸â€œÂ Adicionando estados ao dropdown...');
        data.estados.forEach((estado, index) => {
            console.log(`Ã°Å¸â€œÂ Estado ${index + 1}:`, estado);
            const opt = document.createElement('option');
            opt.value = estado.sigla;
            opt.textContent = estado.nome;
            select.appendChild(opt);
            console.log(`Ã¢Å“â€¦ OpÃƒÂ§ÃƒÂ£o adicionada: ${estado.sigla} - ${estado.nome}`);
        });

        console.log(`Ã¢Å“â€¦ ${data.estados.length} estados carregados no dropdown`);
        console.log(`Ã°Å¸â€œÅ  Total de opÃƒÂ§ÃƒÂµes no select: ${select.options.length}`);

        // Verificar se realmente foram adicionados
        const options = Array.from(select.options).map(opt => ({
            value: opt.value,
            text: opt.textContent
        }));
        console.log('Ã°Å¸â€œâ€¹ OpÃƒÂ§ÃƒÂµes finais no select:', options);
    } catch (error) {
        console.error('Ã°Å¸â€™Â¥ Erro ao carregar estados:', error);
        console.error('Ã°Å¸â€™Â¥ Stack trace:', error.stack);
    }
}

/**
 * Carrega cidades de um estado especÃƒÂ­fico
 * @param {string} uf - Sigla do estado
 */
export function carregarCidades(uf = '') {
    console.log('Ã°Å¸Ââ„¢Ã¯Â¸Â Carregando cidades para UF:', uf);

    const select = document.getElementById('filtro-cidade');
    if (!select) {
        console.error('Ã¢ÂÅ’ Elemento select de cidade nÃƒÂ£o encontrado');
        return;
    }

    // Limpa todas as opÃƒÂ§ÃƒÂµes exceto a primeira
    select.options.length = 1;

    if (!uf) {
        console.log('Ã¢Å¡Â Ã¯Â¸Â UF nÃƒÂ£o informada, mantendo lista vazia');
        return;
    }

    if (!dadosEstadosCidades) {
        console.warn('Ã¢Å¡Â Ã¯Â¸Â Dados de estados/cidades nÃƒÂ£o carregados ainda');
        return;
    }

    // Busca o estado pela sigla
    const estado = dadosEstadosCidades.estados.find(e => e.sigla === uf);
    if (!estado || !estado.cidades) {
        console.warn(`Ã¢Å¡Â Ã¯Â¸Â Estado ${uf} nÃƒÂ£o encontrado ou sem cidades`);
        return;
    }

    // Popula o dropdown com as cidades do estado
    estado.cidades.forEach(cidade => {
        const opt = document.createElement('option');
        opt.value = cidade;
        opt.textContent = cidade;
        select.appendChild(opt);
    });

    console.log(`Ã¢Å“â€¦ ${estado.cidades.length} cidades carregadas para ${uf}`);
}


