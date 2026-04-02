/**
 * BRASALLIS UI v6.1 - LIGHT-HUB SMART ENGINE
 * Interatividade: Vidro Fosco, Auto-Recolhimento e Reset de Estado
 */

document.addEventListener('DOMContentLoaded', function() {
    const body = document.body;
    const sidebar = document.querySelector('.brasallis-sidebar');

    // --- 1. SIDEBAR: Persistência & Toggle Geral ---
    const savedSidebarState = localStorage.getItem('brasallis_sidebar_state');
    if (savedSidebarState === 'expanded') body.classList.add('brasallis-expanded');

    window.toggleBrasallis = function(forceClose = false) {
        if (forceClose) {
            body.classList.remove('brasallis-expanded');
        } else {
            body.classList.toggle('brasallis-expanded');
        }
        
        localStorage.setItem('brasallis_sidebar_state', body.classList.contains('brasallis-expanded') ? 'expanded' : 'collapsed');
        
        // --- LIMPEZA AGRESSIVA AO FECHAR ---
        // Força a remoção de 'active' em todos os pilares para garantir que abra limpo
        if (!body.classList.contains('brasallis-expanded')) {
            document.querySelectorAll('.brasallis-pillar').forEach(p => {
                p.classList.remove('active');
            });
        }
    };

    // --- 2. DESKTOP: Accordion System (Pillars) ---
    const pillars = document.querySelectorAll('.brasallis-pillar');
    pillars.forEach(pillar => {
        const trigger = pillar.querySelector('.brasallis-item');
        trigger.addEventListener('click', (e) => {
            e.stopPropagation(); // Evita que o clique no item feche o menu pelo detector de fora
            
            if (body.classList.contains('brasallis-expanded')) {
                const isActive = pillar.classList.contains('active');
                
                // Fecha outros para manter o foco (e a limpeza visual desejada pelo usuário)
                pillars.forEach(p => p.classList.remove('active'));
                
                if (!isActive) {
                    pillar.classList.add('active');
                }
            } else {
                // Se estiver em modo Rail, expande primeiro e depois abre o pilar
                toggleBrasallis();
                setTimeout(() => pillar.classList.add('active'), 100);
            }
        });
    });

    // --- 3. SMART INTERACTION: Click Outside to Collapse ---
    document.addEventListener('click', (e) => {
        const isExpanded = body.classList.contains('brasallis-expanded');
        
        // Se clicar fora do sidebar e do botão de toggle, e o menu estiver aberto, ele recolhe
        if (isExpanded && sidebar && !sidebar.contains(e.target) && !e.target.closest('.brasallis-toggle')) {
            toggleBrasallis(true); // Chamada de fechamento forçado com reset de estado
        }
    });

    // --- 4. OMNI-SEARCH & SHORTCUTS (/) ---
    document.addEventListener('keydown', e => {
        if (e.key === '/' && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
            e.preventDefault();
            const deskSearch = document.querySelector('.brasallis-search-input');
            if (deskSearch && window.getComputedStyle(deskSearch.parentElement).display !== 'none') {
                deskSearch.focus();
            } else {
                toggleMobileSearch();
            }
        }
    });

    // --- 6. SEARCH MOBILE OVERLAY ---
    window.toggleMobileSearch = function() {
        const overlay = document.getElementById('brasallis-search-overlay');
        if (!overlay) return;
        const isVisible = overlay.style.display === 'flex';
        overlay.style.display = isVisible ? 'none' : 'flex';
        if (!isVisible) setTimeout(() => document.getElementById('mobile-search-field')?.focus(), 300);
    };

    console.log('Brasallis Light-Hub v6.1 Smart Engine Active.');
});
