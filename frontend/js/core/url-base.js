(function () {
  if (typeof window === 'undefined') {
    return;
  }

  // SUPER WARNING: Arquivo canonico de URL base.
  // Nao introduzir fallback por pagina/rota nem recriar logica em modulos locais.
  // Qualquer mudanca aqui exige aprovacao explicita (ADR/ticket).
  window.__URL_BASE_CANONICAL__ = true;

  function normalizeApiBaseValue(value) {
    var base = (value || '').trim();
    if (!base) {
      return '';
    }

    // Remove barras finais
    base = base.replace(/\/+$/, '');

    // Corrige duplicidade de /api no final (ex.: /api/api -> /api)
    base = base.replace(/\/api\/api$/i, '/api');

    return base;
  }

  function detectAppBase() {
    if (typeof window.URL_BASE === 'string' && window.URL_BASE.trim() !== '') {
      return window.URL_BASE.replace(/\/$/, '');
    }

    if (typeof window.APP_BASE === 'string' && window.APP_BASE.trim() !== '') {
      return window.APP_BASE.replace(/\/$/, '');
    }

    if (typeof window.API_BASE === 'string' && window.API_BASE.trim() !== '') {
      var apiBase = window.API_BASE.trim();
      return apiBase.replace(/\/api\/?$/, '');
    }

    // Fallback deterministico sem heuristica por pathname/rota
    // (alinhado ao padrao canônico do helper backend).
    return '';
  }

  function ensureAppBase() {
    if (typeof window.APP_BASE === 'string' && window.APP_BASE !== '') {
      return window.APP_BASE;
    }
    window.APP_BASE = detectAppBase();
    return window.APP_BASE;
  }

  function ensureApiBase() {
    if (typeof window.API_BASE === 'string' && window.API_BASE !== '') {
      window.API_BASE = normalizeApiBaseValue(window.API_BASE);
      if (window.API_BASE === '') {
        window.API_BASE = '/api';
      }
      return window.API_BASE;
    }
    var appBase = ensureAppBase();
    window.API_BASE = normalizeApiBaseValue(appBase ? appBase + '/api' : '/api') || '/api';
    return window.API_BASE;
  }

  function buildApiUrl(endpoint) {
    var base = normalizeApiBaseValue(ensureApiBase()) || '/api';
    var clean = endpoint || '';
    clean = clean.replace(/^\/+/, '').replace(/^api\/+/i, '');
    return base + '/' + clean;
  }

  function buildAssetUrl(path) {
    var base = ensureAppBase();
    var clean = path || '';
    clean = clean.replace(/^\//, '');
    if (base === '') {
      return '/' + clean;
    }
    if (base.endsWith('/')) {
      return base + clean;
    }
    return base + '/' + clean;
  }

  window.getAppBase = ensureAppBase;
  window.getApiBase = ensureApiBase;
  window.buildApiUrl = buildApiUrl;
  window.buildAssetUrl = buildAssetUrl;
})();
