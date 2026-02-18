if (window.getApiBase) { window.getApiBase(); }
/**
 * UtilitÃƒÂ¡rio: Debounce
 * Atrasa a execuÃƒÂ§ÃƒÂ£o de uma funÃƒÂ§ÃƒÂ£o atÃƒÂ© que um perÃƒÂ­odo de tempo tenha passado
 * desde a ÃƒÂºltima vez que foi invocada.
 * 
 * @param {Function} func - FunÃƒÂ§ÃƒÂ£o a ser executada
 * @param {number} wait - Tempo de espera em milissegundos
 * @param {boolean} immediate - Se true, executa imediatamente na primeira chamada
 * @returns {Function} FunÃƒÂ§ÃƒÂ£o com debounce aplicado
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


