if (window.getApiBase) { window.getApiBase(); }
/**
 * FunÃƒÂ§ÃƒÂµes comuns para pÃƒÂ¡ginas administrativas
 * Inclui utilitÃƒÂ¡rios para SweetAlert2 e outras funcionalidades compartilhadas
 */

// Verificar se SweetAlert2 estÃƒÂ¡ disponÃƒÂ­vel
if (typeof Swal === 'undefined') {
    console.warn('SweetAlert2 nÃƒÂ£o estÃƒÂ¡ carregado. Carregando...');
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
    script.onload = () => {
        console.log('SweetAlert2 carregado com sucesso');
    };
    document.head.appendChild(script);
}

/**
 * Exibe uma mensagem usando SweetAlert2 ou fallback para alert
 * @param {string} type - Tipo da mensagem: 'success', 'error', 'warning', 'info', 'question'
 * @param {string} title - TÃƒÂ­tulo da mensagem
 * @param {string} message - Mensagem (opcional)
 * @param {object} options - OpÃƒÂ§ÃƒÂµes adicionais do SweetAlert2
 * @returns {Promise} Promise que resolve quando o alerta ÃƒÂ© fechado
 */
function showMessage(type = 'info', title = '', message = '', options = {}) {
    const defaultOptions = {
        icon: type,
        title: title || message || 'Aviso',
        text: message && title ? message : '',
        timer: type === 'success' ? 2500 : type === 'error' ? 4000 : 3000,
        showConfirmButton: type === 'question' || type === 'warning',
        confirmButtonText: 'OK',
        confirmButtonColor: getButtonColor(type),
        allowOutsideClick: true,
        allowEscapeKey: true
    };

    const finalOptions = { ...defaultOptions, ...options };

    if (typeof Swal !== 'undefined') {
        return Swal.fire(finalOptions);
    } else {
        // Fallback para alert nativo
        alert((title || '') + (message ? '\n' + message : ''));
        return Promise.resolve({ isConfirmed: true });
    }
}

/**
 * Retorna a cor do botÃƒÂ£o baseado no tipo
 */
function getButtonColor(type) {
    const colors = {
        success: '#10B981',
        error: '#EF4444',
        warning: '#F59E0B',
        info: '#3B82F6',
        question: '#6366F1'
    };
    return colors[type] || colors.info;
}

/**
 * Exibe uma confirmaÃƒÂ§ÃƒÂ£o usando SweetAlert2
 * @param {string} title - TÃƒÂ­tulo da confirmaÃƒÂ§ÃƒÂ£o
 * @param {string} text - Texto da confirmaÃƒÂ§ÃƒÂ£o
 * @param {string} confirmText - Texto do botÃƒÂ£o de confirmaÃƒÂ§ÃƒÂ£o
 * @param {string} cancelText - Texto do botÃƒÂ£o de cancelamento
 * @param {string} icon - ÃƒÂcone: 'warning', 'question', 'error', 'info'
 * @returns {Promise} Promise que resolve com {isConfirmed: boolean}
 */
async function showConfirm(title = 'Confirmar aÃƒÂ§ÃƒÂ£o', text = 'Esta aÃƒÂ§ÃƒÂ£o nÃƒÂ£o pode ser desfeita.', confirmText = 'Sim, confirmar', cancelText = 'Cancelar', icon = 'question') {
    if (typeof Swal !== 'undefined') {
        const result = await Swal.fire({
            title: title,
            text: text,
            icon: icon,
            showCancelButton: true,
            confirmButtonColor: getButtonColor(icon === 'question' ? 'question' : icon),
            cancelButtonColor: '#6b7280',
            confirmButtonText: confirmText,
            cancelButtonText: cancelText,
            reverseButtons: true,
            allowOutsideClick: false,
            allowEscapeKey: true
        });
        return result;
    } else {
        // Fallback para confirm nativo
        const confirmed = confirm(title + '\n\n' + text);
        return { isConfirmed: confirmed, isDismissed: !confirmed };
    }
}

/**
 * Exibe um loading usando SweetAlert2
 * @param {string} title - TÃƒÂ­tulo do loading
 * @param {string} text - Texto do loading
 */
function showLoading(title = 'Carregando...', text = 'Por favor, aguarde') {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: title,
            text: text,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }
}

/**
 * Fecha o loading do SweetAlert2
 */
function hideLoading() {
    if (typeof Swal !== 'undefined') {
        Swal.close();
    }
}

/**
 * Exibe um toast (notificaÃƒÂ§ÃƒÂ£o pequena) usando SweetAlert2
 * @param {string} type - Tipo: 'success', 'error', 'warning', 'info'
 * @param {string} message - Mensagem
 * @param {number} duration - DuraÃƒÂ§ÃƒÂ£o em ms (padrÃƒÂ£o: 3000)
 */
function showToast(type = 'info', message = '', duration = 3000) {
    if (typeof Swal !== 'undefined') {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: duration,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        Toast.fire({
            icon: type,
            title: message
        });
    } else {
        // Fallback
        alert(message);
    }
}

/**
 * Trata erros de API e exibe mensagem apropriada
 * @param {Error|object} error - Erro capturado
 * @param {string} defaultMessage - Mensagem padrÃƒÂ£o se nÃƒÂ£o conseguir extrair do erro
 */
function handleApiError(error, defaultMessage = 'Erro ao processar solicitaÃƒÂ§ÃƒÂ£o') {
    let message = defaultMessage;
    
    if (error instanceof Error) {
        message = error.message;
    } else if (error && typeof error === 'object') {
        if (error.message) {
            message = error.message;
        } else if (error.error) {
            message = error.error;
        } else if (typeof error === 'string') {
            message = error;
        }
    }
    
    console.error('Erro de API:', error);
    showMessage('error', 'Erro', message);
}

/**
 * Formata mensagens de sucesso
 */
function showSuccess(message, title = 'Sucesso!') {
    return showMessage('success', title, message);
}

/**
 * Formata mensagens de erro
 */
function showError(message, title = 'Erro!') {
    return showMessage('error', title, message);
}

/**
 * Formata mensagens de aviso
 */
function showWarning(message, title = 'AtenÃƒÂ§ÃƒÂ£o!') {
    return showMessage('warning', title, message);
}

/**
 * Formata mensagens de informaÃƒÂ§ÃƒÂ£o
 */
function showInfo(message, title = 'InformaÃƒÂ§ÃƒÂ£o') {
    return showMessage('info', title, message);
}

// Exportar funÃƒÂ§ÃƒÂµes para uso global
window.AdminUtils = {
    showMessage,
    showConfirm,
    showLoading,
    hideLoading,
    showToast,
    handleApiError,
    showSuccess,
    showError,
    showWarning,
    showInfo
};

// Compatibilidade: manter funÃƒÂ§ÃƒÂµes globais tambÃƒÂ©m
window.showMessage = showMessage;
window.showConfirm = showConfirm;
window.showLoading = showLoading;
window.hideLoading = hideLoading;
window.showToast = showToast;

