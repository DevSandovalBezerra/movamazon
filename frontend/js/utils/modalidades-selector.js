function renderizarModalidadesCheckboxes(containerId, modalidades, selecionadas = []) {
    const container = document.getElementById(containerId);
    if (!container) {
        console.error(`Container com ID "${containerId}" não encontrado`);
        return;
    }

    container.innerHTML = '';

    if (!modalidades || modalidades.length === 0) {
        container.innerHTML = '<div class="text-gray-400 text-sm">Nenhuma modalidade encontrada para este evento.</div>';
        return;
    }

    modalidades.forEach((modalidade) => {
        const div = document.createElement('div');
        div.className = 'flex items-center space-x-2';
        
        const checkboxId = `modalidade_${modalidade.id}`;
        const isSelected = selecionadas.includes(modalidade.id) || selecionadas.includes(parseInt(modalidade.id));
        
        const labelText = modalidade.categoria_nome 
            ? `${modalidade.categoria_nome} - ${modalidade.nome}`
            : modalidade.nome_modalidade || modalidade.nome || `Modalidade ${modalidade.id}`;
        
        div.innerHTML = `
            <input type="checkbox" 
                   name="modalidades[]" 
                   id="${checkboxId}" 
                   value="${modalidade.id}" 
                   class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                   ${isSelected ? 'checked' : ''}>
            <label for="${checkboxId}" class="text-sm text-gray-700">${labelText}</label>
        `;
        container.appendChild(div);
    });
}

function obterModalidadesSelecionadas(containerId) {
    const container = document.getElementById(containerId);
    if (!container) {
        console.error(`Container com ID "${containerId}" não encontrado`);
        return [];
    }

    const checkboxes = container.querySelectorAll('input[type="checkbox"]:checked');
    return Array.from(checkboxes).map(cb => parseInt(cb.value));
}

function validarSelecaoModalidades(containerId, minimo = 1) {
    const selecionadas = obterModalidadesSelecionadas(containerId);
    
    if (selecionadas.length < minimo) {
        return {
            valido: false,
            mensagem: minimo === 1 
                ? 'Selecione pelo menos uma modalidade' 
                : `Selecione pelo menos ${minimo} modalidade(s)`
        };
    }
    
    return {
        valido: true,
        mensagem: ''
    };
}

