if (window.getApiBase) { window.getApiBase(); }
/**
 * Utilitários: Formatadores
 * Funções para formatar dados exibidos na interface
 */

function countMojibakeMarkers(texto) {
    const matches = (texto || '').match(/Ã|Â|â€|Å|�/g);
    return matches ? matches.length : 0;
}

/**
 * Tenta corrigir textos que vieram em UTF-8 lido como latin1/windows-1252.
 * Mantém o valor original quando não detecta melhoria.
 */
export function corrigirMojibake(texto) {
    if (typeof texto !== 'string' || texto === '') {
        return texto;
    }

    if (!/[ÃÂâÅ�]/.test(texto)) {
        return texto;
    }

    try {
        const bytes = Uint8Array.from(texto, (char) => char.charCodeAt(0) & 0xff);
        const decodificado = new TextDecoder('utf-8').decode(bytes);
        return countMojibakeMarkers(decodificado) < countMojibakeMarkers(texto) ? decodificado : texto;
    } catch (_) {
        return texto;
    }
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
 * Formata localização (cidade/estado)
 */
export function formatarLocal(cidade, estado) {
    const cidadeSafe = corrigirMojibake(cidade);
    const estadoSafe = corrigirMojibake(estado);

    if (!cidadeSafe && !estadoSafe) return 'Local não informado';
    if (cidadeSafe && estadoSafe) return `${cidadeSafe}/${estadoSafe}`;
    return cidadeSafe || estadoSafe;
}

/**
 * Trunca texto se exceder o tamanho máximo
 */
export function truncarTexto(texto, maxCaracteres = 20) {
    const textoSafe = corrigirMojibake(texto);
    if (!textoSafe || textoSafe.length <= maxCaracteres) {
        return textoSafe;
    }
    return textoSafe.substring(0, maxCaracteres) + '...';
}

/**
 * Obtém a base para URLs de assets (até a pasta frontend).
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

    const imagemSafe = corrigirMojibake(String(imagem).trim());

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
 * Obtém o caminho correto da imagem do evento.
 */
export function getImagemEvento(imagem) {
    return getEventImageUrl(imagem);
}

/**
 * Determina o nome correto da empresa organizadora
 */
export function getNomeOrganizador(evento) {
    const nomeEvento = corrigirMojibake(evento?.nome || '');
    if (nomeEvento.includes('SAUIM DE COLEIRA')) {
        return 'UEA - APOIO TÉCNICO MENTE DE CORREDOR';
    }

    const organizador = corrigirMojibake(evento?.organizador || evento?.organizadora || '');
    return organizador || 'Organizador não informado';
}


