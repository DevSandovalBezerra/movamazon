(function () {
  if (typeof window === 'undefined') {
    return;
  }

  function normalizePath(path) {
    if (!path) {
      return '';
    }
    return path.replace(/\\/g, '/');
  }

  function detectAppBase() {
    if (typeof window.URL_BASE === 'string' && window.URL_BASE.trim() !== '') {
      return window.URL_BASE.replace(/\/$/, '');
    }

    if (typeof window.API_BASE === 'string' && window.API_BASE.trim() !== '') {
      var apiBase = window.API_BASE.trim();
      if (apiBase.startsWith('http')) {
        return apiBase.replace(/\/api\/?$/, '');
      }
      return apiBase.replace(/\/api\/?$/, '');
    }

    var path = normalizePath(window.location.pathname || '');
    var idx = path.indexOf('/frontend/');
    if (idx >= 0) {
      return path.slice(0, idx);
    }
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
      return window.API_BASE;
    }
    var appBase = ensureAppBase();
    window.API_BASE = appBase ? appBase + '/api' : '/api';
    return window.API_BASE;
  }

  function buildApiUrl(endpoint) {
    var base = ensureApiBase();
    var clean = endpoint || '';
    clean = clean.replace(/^\//, '');
    if (base.endsWith('/')) {
      return base + clean;
    }
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
