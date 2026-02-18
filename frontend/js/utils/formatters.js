if (window.getApiBase) { window.getApiBase(); }
/**
 * UtilitÃƒÂ¡rios: Formatadores
 * FunÃƒÂ§ÃƒÂµes para formatar dados exibidos na interface
 */

/**
 * Formata a hora (converte 07:00:00 para 07:00)
 * @param {string} hora - Hora no formato HH:MM:SS ou HH:MM
 * @returns {string|null} Hora formatada ou null
 */
export function formatarHora(hora) {
    if (!hora) return null;

    // Se jÃƒÂ¡ estiver no formato correto (07:00), retorna como estÃƒÂ¡
    if (typeof hora === 'string' && hora.match(/^\d{1,2}:\d{2}$/)) {
        return hora;
    }

    // Se estiver no formato 07:00:00, remove os segundos
    if (typeof hora === 'string' && hora.match(/^\d{1,2}:\d{2}:\d{2}$/)) {
        return hora.substring(0, 5);
    }

    return hora;
}

/**
 * Formata localizaÃƒÂ§ÃƒÂ£o (cidade/estado)
 * @param {string} cidade - Nome da cidade
 * @param {string} estado - Sigla do estado
 * @returns {string} LocalizaÃƒÂ§ÃƒÂ£o formatada
 */
export function formatarLocal(cidade, estado) {
    if (!cidade && !estado) return 'Local nÃƒÂ£o informado';

    if (cidade && estado) {
        return `${cidade}/${estado}`;
    } else if (cidade) {
        return cidade;
    } else {
        return estado;
    }
}

/**
 * Trunca texto se exceder o tamanho mÃƒÂ¡ximo
 * @param {string} texto - Texto a ser truncado
 * @param {number} maxCaracteres - NÃƒÂºmero mÃƒÂ¡ximo de caracteres
 * @returns {string} Texto truncado com "..."
 */
export function truncarTexto(texto, maxCaracteres = 20) {
    if (!texto || texto.length <= maxCaracteres) {
        return texto;
    }

    return texto.substring(0, maxCaracteres) + '...';
}

/**
 * ObtÃƒÂ©m a base para URLs de assets (atÃƒÂ© a pasta frontend) a partir do pathname atual.
 * Usado para montar a URL da imagem do evento de forma consistente em todas as pÃƒÂ¡ginas.
 * @returns {string} Base (ex: /movamazon/frontend ou ../../ para fallback relativo)
 */
export function getEventImageBase() {
    const pathname = (window.location.pathname || '').replace(/\\/g, '/');
    const idx = pathname.indexOf('frontend');
    if (idx !== -1) {
        return pathname.substring(0, idx + 'frontend'.length);
    }
    return '../../';
}

/**
 * Monta a URL da imagem do evento (nome do arquivo vindo do banco).
 * Base derivada do pathname para funcionar em qualquer pÃƒÂ¡gina (organizador, public, etc.).
 * @param {string} imagem - Nome do arquivo (ex: evento_2.png)
 * @returns {string} URL completa ou relativa para o src da img
 */
export function getEventImageUrl(imagem) {
    if (!imagem || (typeof imagem === 'string' && !imagem.trim())) {
        return 'https://placehold.co/640x360?text=Evento';
    }
    if (/^https?:\/\//.test(imagem)) {
        return imagem;
    }
    const base = getEventImageBase();
    const path = 'assets/img/eventos/' + imagem;
    if (base === '../../') {
        return base + path;
    }
    return base + (base.endsWith('/') ? '' : '/') + path;
}

/**
 * ObtÃƒÂ©m o caminho correto da imagem do evento (usa getEventImageUrl).
 * @param {string} imagem - Nome ou URL da imagem
 * @returns {string} Caminho completo da imagem
 */
export function getImagemEvento(imagem) {
    return getEventImageUrl(imagem);
}

/**
 * Determina o nome correto da empresa organizadora
 * @param {Object} evento - Objeto do evento
 * @returns {string} Nome do organizador
 */
export function getNomeOrganizador(evento) {
    // Se for o evento especÃƒÂ­fico da UEA, retornar o nome da empresa
    if (evento.nome && evento.nome.includes('SAUIM DE COLEIRA')) {
        return 'UEA - APOIO TÃƒâ€°CNICO MENTE DE CORREDOR';
    }

    // Caso contrÃƒÂ¡rio, usar o campo disponÃƒÂ­vel
    if (evento.organizador) {
        return evento.organizador;
    } else if (evento.organizadora) {
        return evento.organizadora;
    } else {
        return 'Organizador nÃƒÂ£o informado';
    }
}


