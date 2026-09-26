<style>
    body.wiboost-admin-shell .admin-detail-modal {
        background: transparent !important;
        inset: auto 0 0 0 !important;
        top: var(--admin-detail-modal-top, 0px) !important;
        height: var(--admin-detail-modal-height, 100dvh) !important;
        display: none;
        align-items: center !important;
        justify-content: center !important;
        overflow: hidden !important;
        overscroll-behavior: contain;
        padding: 1rem .85rem !important;
        pointer-events: none;
        -webkit-backdrop-filter: none !important;
        backdrop-filter: none !important;
        transition: opacity .2s ease !important;
        z-index: 9999 !important;
    }

    body.wiboost-admin-shell .admin-detail-modal::before {
        content: "";
        position: absolute;
        inset: 0;
        background: rgba(43, 58, 103, .58);
        -webkit-backdrop-filter: blur(8px);
        backdrop-filter: blur(8px);
        clip-path: inset(0 0 0 var(--admin-detail-sidebar-offset, 0px));
        pointer-events: none;
        transition: clip-path .28s ease;
        will-change: clip-path;
    }

    body.wiboost-admin-shell .admin-detail-modal.modal-active {
        display: flex !important;
        opacity: 1 !important;
        pointer-events: none !important;
    }

    body.wiboost-admin-shell .admin-detail-modal.modal-active .admin-detail-card {
        transform: translateX(var(--admin-detail-card-shift, 0px)) scale(1) !important;
    }

    body.wiboost-admin-shell .admin-detail-modal > .absolute.inset-0 {
        left: var(--admin-detail-sidebar-offset, 0px) !important;
        pointer-events: auto !important;
    }

    body.wiboost-admin-shell .admin-detail-card {
        position: relative !important;
        z-index: 1;
        display: flex !important;
        flex-direction: column !important;
        width: min(100%, var(--admin-detail-card-width, 58rem)) !important;
        max-width: var(--admin-detail-card-width, 58rem) !important;
        max-height: calc(var(--admin-detail-modal-height, 100dvh) - 2.5rem) !important;
        overflow: hidden !important;
        border-radius: 1.75rem !important;
        background: #ffffff !important;
        overflow-wrap: anywhere;
        word-break: break-word;
        transform: translateX(var(--admin-detail-card-shift, 0px)) scale(.95) !important;
        transition: transform .28s ease !important;
        will-change: transform;
        pointer-events: auto !important;
    }

    body.wiboost-admin-shell .admin-detail-card * {
        min-width: 0;
    }

    body.wiboost-admin-shell .admin-detail-card p,
    body.wiboost-admin-shell .admin-detail-card h3,
    body.wiboost-admin-shell .admin-detail-card span {
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    body.wiboost-admin-shell .admin-detail-header {
        border: 0 !important;
        border-bottom: 3px dashed #f4f9ff !important;
        border-radius: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        padding: .15rem .15rem .9rem !important;
    }

    body.wiboost-admin-shell .admin-detail-panel {
        overflow: hidden;
        border-radius: 1.2rem !important;
        border: 1px solid #eaf2ff !important;
        background: #f8faff !important;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.9) !important;
        padding: 1rem 1.1rem !important;
    }

    body.wiboost-admin-shell .admin-detail-panel-soft {
        border-color: #fff1d6 !important;
        background: #fffdf7 !important;
    }

    body.wiboost-admin-shell .admin-detail-subgrid {
        display: grid;
        gap: .75rem;
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }

    body.wiboost-admin-shell .admin-detail-subfield {
        min-width: 0;
        border: 1px solid #eaf2ff;
        border-radius: 1rem;
        background: #fff;
        padding: .8rem .9rem;
        box-shadow: 0 6px 16px rgba(189, 224, 254, .12);
    }

    @media (min-width: 768px) {
        body.wiboost-admin-shell .admin-detail-subgrid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    body.wiboost-admin-shell .admin-detail-body {
        min-height: 0;
        flex: 1 1 auto;
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch;
        padding: .1rem .85rem .35rem .15rem !important;
        scrollbar-color: #bde0fe transparent;
        scrollbar-width: thin;
        scrollbar-gutter: stable both-edges;
    }

    body.wiboost-admin-shell .admin-detail-body::-webkit-scrollbar {
        width: 8px;
    }

    body.wiboost-admin-shell .admin-detail-body::-webkit-scrollbar-track {
        background: transparent;
        border-radius: 999px;
        margin: .75rem 0;
    }

    body.wiboost-admin-shell .admin-detail-body::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #d8ecff, #8faaf3);
        border: 2px solid #ffffff;
        border-radius: 999px;
    }

    body.wiboost-admin-shell .admin-detail-body::-webkit-scrollbar-thumb:hover {
        background: #8faaf3;
    }

    body.wiboost-admin-shell .admin-detail-close,
    body.wiboost-admin-shell .admin-detail-header button[onclick*="closeAdmin"] {
        width: 2.75rem !important;
        height: 2.75rem !important;
        flex-shrink: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        border: 2px solid #ffffff !important;
        border-radius: 999px !important;
        background: #ffffff !important;
        color: #ff4d4d !important;
        font-size: 0 !important;
        box-shadow: 0 14px 28px rgba(255, 107, 107, .2), inset 0 1px 0 rgba(255,255,255,.9) !important;
        opacity: 1 !important;
        transition: transform .16s ease, background .16s ease, color .16s ease !important;
    }

    body.wiboost-admin-shell .admin-detail-close:hover,
    body.wiboost-admin-shell .admin-detail-header button[onclick*="closeAdmin"]:hover {
        background: #ffe5e5 !important;
        transform: scale(1.04);
    }

    body.wiboost-admin-shell .admin-detail-close svg,
    body.wiboost-admin-shell .admin-detail-header button[onclick*="closeAdmin"] svg {
        width: 1.15rem !important;
        height: 1.15rem !important;
        stroke-width: 3.2 !important;
    }

    body.wiboost-admin-shell .admin-detail-header button[onclick*="closeAdmin"]:not(:has(svg))::before {
        content: "x";
        font-size: 1.35rem;
        line-height: 1;
        font-weight: 900;
    }

    @media (min-width: 1024px) {
        body.wiboost-admin-shell .admin-detail-modal {
            --admin-detail-sidebar-offset: 18rem;
            --admin-detail-card-shift: 9rem;
        }

        body.wiboost-admin-shell.wiboost-admin-sidebar-collapsed .admin-detail-modal {
            --admin-detail-sidebar-offset: 5.5rem;
            --admin-detail-card-shift: 2.75rem;
        }
    }

    @media (max-width: 767px) {
        body.wiboost-admin-shell .admin-detail-modal {
            padding: .9rem !important;
            min-height: var(--admin-detail-modal-height, 100dvh) !important;
        }

        body.wiboost-admin-shell .admin-detail-modal::before {
            clip-path: inset(0) !important;
            background: rgba(43, 58, 103, .64);
        }

        body.wiboost-admin-shell .admin-detail-modal > .absolute.inset-0 {
            left: 0 !important;
        }

        body.wiboost-admin-shell .admin-detail-card {
            max-height: calc(var(--admin-detail-modal-height, 100dvh) - 1.8rem) !important;
            transform: scale(.95) !important;
        }

        body.wiboost-admin-shell .admin-detail-modal.modal-active .admin-detail-card {
            transform: scale(1) !important;
        }

        body.wiboost-admin-shell .admin-detail-body {
            padding-right: .45rem !important;
            scrollbar-gutter: stable;
        }
    }
</style>

<script>
    (function () {
        if (window.wiboostAdminDetailModalReady) return;
        window.wiboostAdminDetailModalReady = true;

        const syncFrame = (modal) => {
            if (!modal) return;

            const viewportHeight = window.visualViewport?.height || window.innerHeight || document.documentElement.clientHeight || 0;
            const navbar = document.querySelector('.wiboost-admin-navbar');
            const navbarRect = navbar?.getBoundingClientRect();
            const top = Math.max(0, Math.ceil(navbarRect?.bottom || 0));
            const height = Math.max(240, Math.floor(viewportHeight - top));

            modal.style.setProperty('--admin-detail-modal-top', `${top}px`);
            modal.style.setProperty('--admin-detail-modal-height', `${height}px`);
        };

        const prepareModal = (modal) => {
            if (!modal || modal.dataset.adminDetailPrepared === 'true') return;
            modal.dataset.adminDetailPrepared = 'true';
            syncFrame(modal);
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
        };

        const syncActive = () => {
            document.querySelectorAll('.admin-detail-modal.modal-active').forEach((modal) => {
                prepareModal(modal);
                syncFrame(modal);
            });
        };

        window.openAdminUniversalDetail = window.openAdminUniversalDetail || function (id) {
            const modal = document.getElementById(id);
            if (!modal) return;
            prepareModal(modal);
            syncFrame(modal);
            modal.classList.add('modal-active');
            document.body.classList.add('admin-detail-open');
            requestAnimationFrame(() => syncFrame(modal));
        };

        window.closeAdminUniversalDetail = window.closeAdminUniversalDetail || function (id) {
            const modal = document.getElementById(id);
            if (!modal) return;
            modal.classList.remove('modal-active');
            document.body.classList.remove('admin-detail-open');
        };

        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type !== 'attributes' || mutation.attributeName !== 'class') return;
                const modal = mutation.target;
                if (!(modal instanceof HTMLElement) || !modal.classList.contains('admin-detail-modal')) return;

                if (modal.classList.contains('modal-active')) {
                    prepareModal(modal);
                    syncFrame(modal);
                    document.body.classList.add('admin-detail-open');
                    requestAnimationFrame(() => syncFrame(modal));
                }
            });
        });

        const observeModals = (root = document) => {
            root.querySelectorAll?.('.admin-detail-modal').forEach((modal) => {
                prepareModal(modal);
                observer.observe(modal, { attributes: true, attributeFilter: ['class'] });
            });
        };

        document.addEventListener('DOMContentLoaded', () => observeModals());
        observeModals();

        new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node instanceof HTMLElement) {
                        if (node.classList.contains('admin-detail-modal')) {
                            observeModals(node.parentElement || document);
                        } else {
                            observeModals(node);
                        }
                    }
                });
            });
        }).observe(document.documentElement, { childList: true, subtree: true });

        window.addEventListener('resize', syncActive, { passive: true });
        window.visualViewport?.addEventListener('resize', syncActive, { passive: true });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') return;
            document.querySelectorAll('.admin-detail-modal.modal-active').forEach((modal) => modal.classList.remove('modal-active'));
            document.body.classList.remove('admin-detail-open');
        });
    })();
</script>
