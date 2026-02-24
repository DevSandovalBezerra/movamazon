if (window.getApiBase) { window.getApiBase(); }
/**
 * UtilitÒ¡rio: Debounce
 * Atrasa a execuÒ§Ò£o de uma funÒ§Ò£o atÒ© que um perÒ­odo de tempo tenha passado
 * desde a Òºltima vez que foi invocada.
 * 
 * @param {Function} func - FunÒ§Ò£o a ser executada
 * @param {number} wait - Tempo de espera em milissegundos
 * @param {boolean} immediate - Se true, executa imediatamente na primeira chamada
 * @returns {Function} FunÒ§Ò£o com debounce aplicado
 */
export function debounce(func, wait = 300, immediate = false) {
    let timeout;
    
    return function executedFunction(...args) {
        const later = () => {
            timeout = null;
            if (!immediate) func(...args);
        };
        
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        
        if (callNow) func(...args);
    };
}


