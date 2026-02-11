if (!window.API_BASE) {
    (function () {
        var path = window.location.pathname || '';
        var idx = path.indexOf('/frontend/');
        window.API_BASE = idx > 0 ? path.slice(0, idx) : '';
    })();
}

function getApiUrl(endpoint) {
    const url = `${window.API_BASE}/api/${endpoint}`;
    return url;
}

export async function carregarAnamneseGeral() {
    try {
        const response = await fetch(getApiUrl('participante/anamnese/get_geral.php'), {
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({ message: 'Erro ao carregar anamnese' }));
            console.error('Erro ao carregar anamnese:', errorData.message);
            return;
        }
        
        const data = await response.json();
        
        if (data.success && data.anamnese) {
            const anamnese = data.anamnese;
            
            if (document.getElementById('anamnese-peso')) {
                document.getElementById('anamnese-peso').value = anamnese.peso || '';
            }
            if (document.getElementById('anamnese-altura')) {
                document.getElementById('anamnese-altura').value = anamnese.altura || '';
            }
            if (document.getElementById('anamnese-nivel')) {
                document.getElementById('anamnese-nivel').value = anamnese.nivel_atividade || '';
            }
            if (document.getElementById('anamnese-historico')) {
                document.getElementById('anamnese-historico').value = anamnese.historico_corridas || anamnese.preferencias_atividades || '';
            }
            if (document.getElementById('anamnese-limitacoes')) {
                document.getElementById('anamnese-limitacoes').value = anamnese.limitacoes_fisicas || '';
            }
            if (document.getElementById('anamnese-doencas')) {
                document.getElementById('anamnese-doencas').value = anamnese.doencas_preexistentes || '';
            }
            if (document.getElementById('anamnese-medicamentos')) {
                document.getElementById('anamnese-medicamentos').value = anamnese.uso_medicamentos || '';
            }
            if (document.getElementById('anamnese-objetivo')) {
                document.getElementById('anamnese-objetivo').value = anamnese.objetivo_principal || '';
            }
            if (document.getElementById('anamnese-preferencias')) {
                document.getElementById('anamnese-preferencias').value = anamnese.preferencias_atividades || '';
            }
            if (document.getElementById('anamnese-horarios')) {
                document.getElementById('anamnese-horarios').value = anamnese.disponibilidade_horarios || '';
            }
            
            if (anamnese.peso && anamnese.altura) {
                const alturaMetros = anamnese.altura / 100;
                const imc = (anamnese.peso / (alturaMetros * alturaMetros)).toFixed(2);
                if (document.getElementById('anamnese-imc-display')) {
                    document.getElementById('anamnese-imc-display').textContent = `IMC: ${imc}`;
                }
            }
        }
    } catch (error) {
        console.error('Erro ao carregar anamnese geral:', error);
    }
}

export async function salvarAnamneseGeral(dados) {
    try {
        const response = await fetch(getApiUrl('participante/anamnese/save_geral.php'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify(dados)
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({ message: 'Erro ao salvar anamnese' }));
            return { success: false, message: errorData.message || 'Erro ao salvar anamnese' };
        }

        return await response.json();
    } catch (error) {
        console.error('Erro ao salvar anamnese:', error);
        return { success: false, message: error.message || 'Erro ao salvar anamnese' };
    }
}

