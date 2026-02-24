if (window.getApiBase) { window.getApiBase(); }
/**
 * Utilitarios: formatadores
 * Funcoes para formatar dados exibidos na interface
 */

/**
 * Normaliza texto para exibicao sem recodificacao em runtime.
 */
export function normalizarTexto(texto) {
    if (typeof texto !== 'string' || texto === '') {
        return texto;
    }
    return texto.normalize('NFC');
}

/**
 * Formata a hora (converte 07:00:00 para 07:00)
 */
export function formatarHora(hora) {
    if (!hora) return null;

    if (typeof hora === 'string' && hora.match(/^\d{1,2}:\d{2}$/)) {
        return hora;
    }

    if (typeof hora === 'string' && hora.match(/^\d{1,2}:\d{2}:\d{2}$/)) {
        return hora.substring(0, 5);
    }

    return hora;
}

/**
 * Formata localizacao (cidade/estado)
 */
export function formatarLocal(cidade, estado) {
    const cidadeSafe = normalizarTexto(cidade);
    const estadoSafe = normalizarTexto(estado);

    if (!cidadeSafe && !estadoSafe) return 'Local não informado';
    if (cidadeSafe && estadoSafe) return `${cidadeSafe}/${estadoSafe}`;
    return cidadeSafe || estadoSafe;
}

/**
 * Trunca texto se exceder o tamanho maximo
 */
export function truncarTexto(texto, maxCaracteres = 20) {
    const textoSafe = normalizarTexto(texto);
    if (!textoSafe || textoSafe.length <= maxCaracteres) {
        return textoSafe;
    }
    return textoSafe.substring(0, maxCaracteres) + '...';
}

/**
 * Obtem a base para URLs de assets (ate a pasta frontend).
 */
export function getEventImageBase() {
    const pathname = (window.location.pathname || '').replace(/\\/g, '/');
    const idx = pathname.indexOf('frontend');
    if (idx !== -1) {
        return pathname.substring(0, idx + 'frontend'.length);
    }
    return '';
}

/**
 * Monta a URL da imagem do evento.
 */
export function getEventImageUrl(imagem) {
    if (!imagem || (typeof imagem === 'string' && !imagem.trim())) {
        return 'https://placehold.co/640x360?text=Evento';
    }

    const imagemSafe = normalizarTexto(String(imagem).trim());

    if (/^https?:\/\//i.test(imagemSafe)) {
        return imagemSafe;
    }

    if (imagemSafe.startsWith('/')) {
        return imagemSafe;
    }

    if (imagemSafe.includes('/assets/')) {
        return '/' + imagemSafe.replace(/^\/+/, '');
    }

    const base = getEventImageBase();
    const path = 'assets/img/eventos/' + imagemSafe.replace(/^\/+/, '');
    return base ? `${base}/${path}` : `/${path}`;
}

/**
 * Obtem o caminho correto da imagem do evento.
 */
export function getImagemEvento(imagem) {
    return getEventImageUrl(imagem);
}

/**
 * Determina o nome correto da empresa organizadora
 */
export function getNomeOrganizador(evento) {
    const nomeEvento = normalizarTexto(evento?.nome || '');
    if (nomeEvento.includes('SAUIM DE COLEIRA')) {
        return 'UEA - APOIO TÉCNICO MENTE DE CORREDOR';
    }

    const organizador = normalizarTexto(evento?.organizador || evento?.organizadora || '');
    return organizador || 'Organizador não informado';
}
