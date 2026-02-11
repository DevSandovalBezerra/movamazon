/**
 * Handler Centralizado de Autenticação
 * 
 * Este arquivo deve ser incluído em todas as páginas que fazem chamadas para APIs autenticadas.
 * Ele detecta automaticamente sessões expiradas e redireciona o usuário.
 */

/**
 * Verifica se a resposta da API indica sessão expirada
 * @param {Response} response Resposta do fetch
 * @param {Object} data Dados parseados da resposta
 * @returns {boolean} true se sessão expirada foi detectada
 */
function verificarSessaoExpirada(response, data) {
    return response.status === 401 && data.code === 'SESSION_EXPIRED';
}

/**
 * Verifica se a resposta da API indica acesso negado
 * @param {Response} response Resposta do fetch
 * @param {Object} data Dados parseados da resposta
 * @returns {boolean} true se acesso negado foi detectado
 */
function verificarAcessoNegado(response, data) {
    return response.status === 403 && data.code === 'ACCESS_DENIED';
}

/**
 * Mostra mensagem de sessão expirada
 * @param {string} mensagem Mensagem a ser exibida
 * @param {string} redirectUrl URL para redirecionamento
 */
function mostrarMensagemSessaoExpirada(mensagem, redirectUrl = '/frontend/paginas/auth/login.php') {
    // Criar modal de sessão expirada
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class="bg-white rounded-lg p-8 max-w-md mx-4 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-yellow-100 rounded-full mb-4">
                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Sessão Expirada</h3>
            <p class="text-gray-600 mb-4">${mensagem}</p>
            <div class="flex items-center justify-center space-x-2 text-sm text-gray-500 mb-4">
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-green-600"></div>
                <span>Redirecionando para login...</span>
            </div>
            <button onclick="window.location.href='${redirectUrl}'" 
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                Ir para Login
            </button>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Redirecionar automaticamente após 3 segundos
    setTimeout(() => {
        window.location.href = redirectUrl;
    }, 3000);
}

/**
 * Mostra mensagem de acesso negado
 * @param {string} mensagem Mensagem a ser exibida
 */
function mostrarMensagemAcessoNegado(mensagem) {
    // Criar modal de acesso negado
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class="bg-white rounded-lg p-8 max-w-md mx-4 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mb-4">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Acesso Negado</h3>
            <p class="text-gray-600 mb-4">${mensagem}</p>
            <button onclick="this.closest('.fixed').remove()" 
                    class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                Fechar
            </button>
        </div>
    `;
    
    document.body.appendChild(modal);
}

/**
 * Wrapper para fetch que trata automaticamente sessões expiradas
 * @param {string} url URL da API
 * @param {Object} options Opções do fetch
 * @returns {Promise<Object>} Resposta parseada da API
 */
async function fetchComTratamentoAuth(url, options = {}) {
    try {
        const response = await fetch(url, options);
        const data = await response.json();
        
        // Verificar sessão expirada
        if (verificarSessaoExpirada(response, data)) {
            console.log('🔒 Sessão expirada detectada, redirecionando...');
            mostrarMensagemSessaoExpirada(data.message, data.redirect);
            return null;
        }
        
        // Verificar acesso negado
        if (verificarAcessoNegado(response, data)) {
            console.log('🚫 Acesso negado detectado');
            mostrarMensagemAcessoNegado(data.message);
            return null;
        }
        
        return data;
        
    } catch (error) {
        console.error('Erro na requisição:', error);
        throw error;
    }
}

/**
 * Verifica se o usuário está logado (verificação básica no frontend)
 * @returns {boolean}
 */
function verificarLoginFrontend() {
    // Esta é uma verificação básica no frontend
    // A verificação real sempre deve ser feita no backend
    return document.cookie.includes('PHPSESSID') || 
           localStorage.getItem('user_logged') === 'true' ||
           sessionStorage.getItem('user_logged') === 'true';
}

/**
 * Logout do usuário (limpa dados locais)
 */
function logoutFrontend() {
    localStorage.removeItem('user_logged');
    sessionStorage.removeItem('user_logged');
    // Redirecionar para login
    window.location.href = '/frontend/paginas/auth/login.php';
}

// Exportar funções para uso global
window.verificarSessaoExpirada = verificarSessaoExpirada;
window.verificarAcessoNegado = verificarAcessoNegado;
window.mostrarMensagemSessaoExpirada = mostrarMensagemSessaoExpirada;
window.mostrarMensagemAcessoNegado = mostrarMensagemAcessoNegado;
window.fetchComTratamentoAuth = fetchComTratamentoAuth;
window.verificarLoginFrontend = verificarLoginFrontend;
window.logoutFrontend = logoutFrontend;
