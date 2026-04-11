/**
 * BRASALLIS UI v6.1 - LIGHT-HUB SMART ENGINE
 * Interatividade: Vidro Fosco, Auto-Recolhimento e Reset de Estado
 */

document.addEventListener('DOMContentLoaded', function() {
    const body = document.body;
    const sidebar = document.querySelector('.brasallis-sidebar');

    // --- 1. SIDEBAR: Desktop Hover-Intent & Persistence ---
    const savedSidebarState = localStorage.getItem('brasallis_sidebar_state');
    if (savedSidebarState === 'expanded' && window.innerWidth > 991) body.classList.add('brasallis-expanded');

    window.toggleBrasallis = function(forceClose = false) {
        if (forceClose) {
            body.classList.remove('brasallis-expanded');
            document.querySelectorAll('.brasallis-pillar').forEach(p => p.classList.remove('active'));
        } else {
            body.classList.toggle('brasallis-expanded');
        }
        localStorage.setItem('brasallis_sidebar_state', body.classList.contains('brasallis-expanded') ? 'expanded' : 'collapsed');
    };

    // Desktop: Smart Hover Intent
    let hoverTimer;
    if (sidebar) {
        sidebar.addEventListener('mouseenter', () => {
            if (window.innerWidth > 991 && !body.classList.contains('brasallis-expanded')) {
                hoverTimer = setTimeout(() => {
                    body.classList.add('brasallis-expanded');
                }, 150); // Delay sutil para prevenir aberturas muito bruscas só passando o mouse
            }
        });

        sidebar.addEventListener('mouseleave', () => {
            if (window.innerWidth > 991 && localStorage.getItem('brasallis_sidebar_state') !== 'expanded') {
                clearTimeout(hoverTimer);
                toggleBrasallis(true); // Retrai e reseta
            }
        });
    }

    // --- 2. DESKTOP: Accordion Fluid System ---
    const pillars = document.querySelectorAll('.brasallis-pillar');
    pillars.forEach(pillar => {
        const trigger = pillar.querySelector('.brasallis-item');
        trigger.addEventListener('click', (e) => {
            e.stopPropagation(); 
            
            if (body.classList.contains('brasallis-expanded')) {
                const isActive = pillar.classList.contains('active');
                pillars.forEach(p => p.classList.remove('active'));
                if (!isActive) pillar.classList.add('active');
            } else {
                body.classList.add('brasallis-expanded');
                setTimeout(() => pillar.classList.add('active'), 50);
            }
        });
    });

    // --- 3. MOBILE: The iOS Background Scale Engine ---
    const mobileCanvas = document.getElementById('brasallis360Offcanvas');
    if (mobileCanvas) {
        mobileCanvas.addEventListener('show.bs.offcanvas', () => {
            body.classList.add('mobile-scaling-active');
        });
        mobileCanvas.addEventListener('hidden.bs.offcanvas', () => {
            body.classList.remove('mobile-scaling-active');
        });
    }

    // --- 4. SMART INTERACTION: Click Outside to Collapse ---
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 991 && body.classList.contains('brasallis-expanded')) {
            if (sidebar && !sidebar.contains(e.target) && !e.target.closest('.brasallis-toggle')) {
                toggleBrasallis(true); 
            }
        }
    });

    // --- 4. OMNI-SEARCH & SHORTCUTS (/) ---
    document.addEventListener('keydown', e => {
        const isInput = ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName) || document.activeElement.isContentEditable;
        
        if (e.key === '/' && !isInput) {
            e.preventDefault();
            const deskSearch = document.querySelector('.brasallis-search-input');
            
            if (deskSearch) {
                // Focus and visual feedback
                deskSearch.focus();
                deskSearch.parentElement.classList.add('active-pulse');
                setTimeout(() => deskSearch.parentElement.classList.remove('active-pulse'), 500);
            } else {
                if (typeof toggleMobileSearch === 'function') toggleMobileSearch();
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

    // --- 7. DYNAMIC OMNI-PLACEHOLDER ---
    const searchInput = document.querySelector('.brasallis-search-input');
    if (searchInput) {
        const placeholders = [
            "Pesquisar produtos...",
            "Analisar ROI da IA...",
            "Consultar estoque atual...",
            "Buscar novo colaborador...",
            "Digitar comando 360 (/)"
        ];
        let pIndex = 0;
        
        setInterval(() => {
            pIndex = (pIndex + 1) % placeholders.length;
            searchInput.style.opacity = 0;
            setTimeout(() => {
                searchInput.setAttribute('placeholder', placeholders[pIndex]);
                searchInput.style.opacity = 1;
            }, 300);
        }, 4000);
    }

    console.log('Brasallis Light-Hub v7.0 Omni-Engine Active.');
});
