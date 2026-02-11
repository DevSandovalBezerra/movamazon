/**
 * URL da imagem do evento - script global para páginas que não usam ES modules.
 * Define window.getEventImageBase e window.getEventImageUrl.
 * Mesma lógica que formatters.js (getEventImageBase / getEventImageUrl).
 */
(function () {
    'use strict';

    function getEventImageBase() {
        var pathname = (window.location.pathname || '').replace(/\\/g, '/');
        var idx = pathname.indexOf('frontend');
        if (idx !== -1) {
            return pathname.substring(0, idx + 'frontend'.length);
        }
        return '../../';
    }

    function getEventImageUrl(imagem) {
        if (!imagem || (typeof imagem === 'string' && !imagem.trim())) {
            return 'https://placehold.co/640x360?text=Evento';
        }
        if (/^https?:\/\//.test(imagem)) {
            return imagem;
        }
        var base = getEventImageBase();
        var path = 'assets/img/eventos/' + imagem;
        if (base === '../../') {
            return base + path;
        }
        return base + (base.slice(-1) === '/' ? '' : '/') + path;
    }

    window.getEventImageBase = getEventImageBase;
    window.getEventImageUrl = getEventImageUrl;
})();
