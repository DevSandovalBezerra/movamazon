/**
 * Módulo para carregar e exibir banners do carrossel
 */

/**
 * Normaliza caminho de imagem usando o caminho base do projeto
 */
function normalizarCaminhoImagem(caminho) {
    if (!caminho) return '';
    // Se já é URL completa, retornar como está
    if (caminho.startsWith('http://') || caminho.startsWith('https://')) {
        return caminho;
    }
    // Detectar caminho base do projeto
    const path = window.location.pathname || '';
    const idx = path.indexOf('/frontend/');
    const basePath = idx > 0 ? path.slice(0, idx) : '';
    // Se começa com /, usar caminho base do projeto
    if (caminho.startsWith('/')) {
        return basePath + caminho;
    }
    // Se não começa com /, adicionar caminho base + /
    return basePath + '/' + caminho;
}

/**
 * Carrega banners da API e atualiza o carrossel
 */
export async function carregarBanners() {
    console.log('[PUBLIC_BANNERS] carregarBanners() chamado');
    try {
        // Garantir que API_BASE está definido (deve estar definido no index.php antes do import)
        if (!window.API_BASE) {
            const path = window.location.pathname || '';
            const idx = path.indexOf('/frontend/');
            window.API_BASE = idx > 0 ? path.slice(0, idx) : '';
            console.warn('[PUBLIC_BANNERS] API_BASE não estava definido, definido agora:', window.API_BASE);
        }
        
        // Construir URL da API
        const apiBase = window.API_BASE || '';
        // Se API_BASE estiver vazio, usar caminho relativo baseado na localização atual
        let apiUrl;
        if (apiBase && apiBase.trim() !== '') {
            // Remover barra final se existir e adicionar /api/...
            const baseClean = apiBase.replace(/\/$/, '');
            apiUrl = `${baseClean}/api/banners/public.php`;
        } else {
            // Tentar construir caminho relativo
            const currentPath = window.location.pathname || '';
            const pathParts = currentPath.split('/').filter(p => p);
            const frontendIdx = pathParts.indexOf('frontend');
            if (frontendIdx >= 0) {
                // Estamos em /.../frontend/..., então a API está em /.../api/...
                const baseParts = pathParts.slice(0, frontendIdx);
                if (baseParts.length > 0) {
                    apiUrl = '/' + baseParts.join('/') + '/api/banners/public.php';
                } else {
                    // Estamos em /frontend/... (raiz), então API está em /api/...
                    apiUrl = '/api/banners/public.php';
                }
            } else {
                // Fallback: assumir que a API está na raiz
                apiUrl = '/api/banners/public.php';
            }
        }
        
        console.log('[PUBLIC_BANNERS] API_BASE:', window.API_BASE);
        console.log('[PUBLIC_BANNERS] Pathname atual:', window.location.pathname);
        console.log('[PUBLIC_BANNERS] URL completa da API:', apiUrl);
        
        const response = await fetch(apiUrl);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        
        console.log('[PUBLIC_BANNERS] Resposta da API:', data);
        
        if (data.success && data.banners && data.banners.length > 0) {
            console.log('[PUBLIC_BANNERS] ✓', data.banners.length, 'banner(s) encontrado(s)');
            atualizarCarrossel(data.banners);
            // Disparar evento SEMPRE após atualizar carrossel (mesmo se Swiper já existir)
            console.log('[PUBLIC_BANNERS] Disparando evento bannersCarregados');
            window.dispatchEvent(new CustomEvent('bannersCarregados', { 
                detail: { banners: data.banners } 
            }));
            // Reinicializar Swiper após atualizar (se já existir)
            if (window.heroSwiper) {
                console.log('[PUBLIC_BANNERS] Swiper já existe, atualizando configurações');
                // Atualizar configurações do Swiper se necessário
                if (window.heroSwiper.params.loop !== true && data.banners.length > 1) {
                    window.heroSwiper.params.loop = true;
                }
                setTimeout(() => {
                    window.heroSwiper.update();
                    window.heroSwiper.slideTo(0, 0);
                    console.log('[PUBLIC_BANNERS] ✓ Swiper atualizado');
                }, 100);
            }
        } else {
            console.log('[PUBLIC_BANNERS] Nenhum banner encontrado no banco de dados');
            // Mostrar fallback apenas se não houver banners
            const fallback = document.querySelector('.fallback-banner');
            if (fallback) {
                // Normalizar caminho da imagem do fallback
                const fallbackImg = fallback.querySelector('img');
                if (fallbackImg) {
                    const originalSrc = fallbackImg.getAttribute('src');
                    // Converter caminho relativo para absoluto e normalizar
                    if (originalSrc && originalSrc.startsWith('../../')) {
                        const absolutePath = '/frontend/' + originalSrc.replace(/^\.\.\/\.\.\//, '');
                        const normalizedSrc = normalizarCaminhoImagem(absolutePath);
                        fallbackImg.src = normalizedSrc;
                        console.log('[PUBLIC_BANNERS] Fallback normalizado:', originalSrc, '→', normalizedSrc);
                    }
                }
                fallback.style.display = 'block';
                // Disparar evento mesmo sem banners para garantir inicialização do Swiper com fallback
                console.log('[PUBLIC_BANNERS] Disparando evento bannersCarregados (fallback)');
                window.dispatchEvent(new CustomEvent('bannersCarregados', { 
                    detail: { banners: [] } 
                }));
                if (window.heroSwiper) {
                    window.heroSwiper.update();
                }
            }
        }
    } catch (error) {
        console.error('[PUBLIC_BANNERS] ✗ Erro ao carregar banners:', error);
        // Em caso de erro, mostrar fallback
        const fallback = document.querySelector('.fallback-banner');
        if (fallback) {
            // Normalizar caminho da imagem do fallback
            const fallbackImg = fallback.querySelector('img');
            if (fallbackImg) {
                const originalSrc = fallbackImg.getAttribute('src');
                // Converter caminho relativo para absoluto e normalizar
                if (originalSrc && originalSrc.startsWith('../../')) {
                    const absolutePath = '/frontend/' + originalSrc.replace(/^\.\.\/\.\.\//, '');
                    const normalizedSrc = normalizarCaminhoImagem(absolutePath);
                    fallbackImg.src = normalizedSrc;
                    console.log('[PUBLIC_BANNERS] Fallback normalizado (erro):', originalSrc, '→', normalizedSrc);
                }
            }
            fallback.style.display = 'block';
            // Disparar evento mesmo com erro para garantir inicialização do Swiper com fallback
            console.log('[PUBLIC_BANNERS] Disparando evento bannersCarregados (erro)');
            window.dispatchEvent(new CustomEvent('bannersCarregados', { 
                detail: { banners: [] } 
            }));
        }
    }
}

/**
 * Atualiza o carrossel com os banners recebidos
 * @param {Array} banners - Array de banners
 */
function atualizarCarrossel(banners) {
    console.log('[PUBLIC_BANNERS] atualizarCarrossel() chamado com', banners?.length || 0, 'banner(s)');
    const swiperWrapper = document.querySelector('.hero-carousel .swiper-wrapper');
    if (!swiperWrapper) {
        console.error('[PUBLIC_BANNERS] ✗ Swiper wrapper não encontrado!');
        return;
    }
    
    // Função para escapar HTML (definir antes de usar)
    const escapeHtml = (str) => {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    };
    
    // Limpar TODOS os slides existentes (incluindo fallback)
    swiperWrapper.innerHTML = '';
    
    // Se não houver banners, não fazer nada (deixar vazio ou usar fallback)
    if (!banners || banners.length === 0) {
        console.log('[PUBLIC_BANNERS] Nenhum banner ativo encontrado');
        return;
    }
    
    console.log('[PUBLIC_BANNERS] Atualizando carrossel com', banners.length, 'banner(s)');
    
    // Criar slides para cada banner
    banners.forEach(banner => {
        const slide = document.createElement('div');
        slide.className = 'swiper-slide';
        
        // Normalizar caminho da imagem usando função helper (igual ao formulário)
        let imgSrc = normalizarCaminhoImagem(banner.imagem);
        if (!imgSrc) {
            // Fallback se não houver imagem - usar normalizarCaminhoImagem também
            imgSrc = normalizarCaminhoImagem('/frontend/assets/img/eventos/evento_4.jpg');
        }
        
        // Fallback para onerror também precisa usar caminho normalizado
        const fallbackImgSrc = normalizarCaminhoImagem('/frontend/assets/img/eventos/evento_4.jpg');
        
        console.log('[PUBLIC_BANNERS] Banner:', banner.titulo, '| Imagem original:', banner.imagem, '| Imagem normalizada:', imgSrc);
        
        const slideContent = `
            <div class="relative w-full h-full">
                <div class="absolute inset-0 w-full h-full">
                    <img 
                        src="${imgSrc}" 
                        alt="${escapeHtml(banner.titulo || 'Banner')}"
                        class="w-full h-full object-cover object-center"
                        loading="lazy"
                        onerror="console.error('[PUBLIC_BANNERS] Erro ao carregar imagem:', '${imgSrc}'); this.onerror=null; this.src='${fallbackImgSrc}';"
                    />
                </div>
                <div class="absolute inset-0 bg-black/30"></div>
                <div class="relative z-10 h-full flex items-center justify-center px-4 sm:px-6 lg:px-8">
                    <div class="max-w-4xl mx-auto text-center text-white">
                        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold mb-4 leading-tight">
                            ${escapeHtml(banner.titulo || 'Encontre sua próxima Corrida')}
                        </h1>
                        ${banner.descricao ? `<p class="text-lg sm:text-xl md:text-2xl mb-6 max-w-3xl mx-auto">${escapeHtml(banner.descricao)}</p>` : ''}
                        ${banner.link && banner.texto_botao ? `
                            <a href="${escapeHtml(banner.link)}" ${banner.target_blank ? 'target="_blank" rel="noopener noreferrer"' : ''} 
                               class="inline-block bg-brand-yellow text-brand-green px-8 py-4 rounded-xl font-bold text-lg hover:bg-yellow-400 transition-colors duration-200">
                                ${escapeHtml(banner.texto_botao)}
                            </a>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
        
        slide.innerHTML = slideContent;
        swiperWrapper.appendChild(slide);
    });
    
    console.log('[PUBLIC_BANNERS] ✓ Carrossel atualizado com', banners.length, 'slide(s)');
}

