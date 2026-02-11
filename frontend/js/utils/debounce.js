/**
 * Utilitário: Debounce
 * Atrasa a execução de uma função até que um período de tempo tenha passado
 * desde a última vez que foi invocada.
 * 
 * @param {Function} func - Função a ser executada
 * @param {number} wait - Tempo de espera em milissegundos
 * @param {boolean} immediate - Se true, executa imediatamente na primeira chamada
 * @returns {Function} Função com debounce aplicado
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


