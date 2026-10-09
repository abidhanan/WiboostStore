<style>
    html.admin-detail-scroll-locked,
    html.admin-detail-scroll-locked body.wiboost-admin-shell {
        overflow: hidden !important;
        overscroll-behavior: none;
    }

    body.wiboost-admin-shell .admin-detail-modal {
        position: fixed !important;
        background: transparent !important;
        inset: 0 !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: auto !important;
        min-height: 0 !important;
        display: none;
        align-items: center !important;
        justify-content: center !important;
        overflow: hidden !important;
        overscroll-behavior: contain;
        padding: max(1rem, env(safe-area-inset-top)) max(.85rem, env(safe-area-inset-right)) max(1rem, env(safe-area-inset-bottom)) max(.85rem, env(safe-area-inset-left)) !important;
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
        background: rgba(15, 23, 42, .82);
        -webkit-backdrop-filter: blur(8px);
        backdrop-filter: blur(8px);
        clip-path: inset(0);
        pointer-events: none;
    }

    body.wiboost-admin-shell .admin-detail-modal.modal-active {
        display: flex !important;
        opacity: 1 !important;
        pointer-events: none !important;
    }

    body.wiboost-admin-shell .admin-detail-modal.modal-active .admin-detail-card {
        transform: scale(1) !important;
    }

    body.wiboost-admin-shell .admin-detail-modal > .absolute.inset-0 {
        left: 0 !important;
        pointer-events: auto !important;
    }

    body.wiboost-admin-shell .admin-detail-card {
        position: relative !important;
        z-index: 1;
        display: flex !important;
        flex-direction: column !important;
        width: min(100%, var(--admin-detail-card-width, 58rem)) !important;
        max-width: var(--admin-detail-card-width, 58rem) !important;
        max-height: calc(100dvh - 2rem) !important;
        overflow: hidden !important;
        border-radius: 1.75rem !important;
        border: 4px solid #ffffff !important;
        background: linear-gradient(180deg, #ffffff 0%, #eef4ff 100%) !important;
        background-image: linear-gradient(180deg, #ffffff 0%, #eef4ff 100%) !important;
        padding: 0 !important;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.98), inset 0 -3px 7px rgba(163,187,251,.22), 0 22px 54px -20px rgba(15,23,42,.58) !important;
        overflow-wrap: anywhere;
        word-break: break-word;
        transform: scale(.95) !important;
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
        flex: 0 0 auto;
        border: 0 !important;
        border-bottom: 2px solid #ffffff !important;
        border-radius: 0 !important;
        background: linear-gradient(180deg, #ffffff 0%, #eaf2ff 100%) !important;
        box-shadow: inset 0 -2px 5px rgba(163,187,251,.16) !important;
        margin: 0 !important;
        padding: 1rem 1.1rem !important;
    }

    body.wiboost-admin-shell .admin-detail-panel {
        overflow: hidden;
        border-radius: 1.2rem !important;
        border: 1px solid #ffffff !important;
        background: linear-gradient(180deg, #ffffff 0%, #edf4ff 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.98), inset 0 -2px 5px rgba(163,187,251,.20), 0 8px 18px -10px rgba(90,118,200,.30) !important;
        padding: 1rem 1.1rem !important;
    }

    body.wiboost-admin-shell .admin-detail-panel-soft {
        border-color: #ffffff !important;
        background: linear-gradient(180deg, #fffef9 0%, #fff0cd 100%) !important;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.98), inset 0 -2px 5px rgba(217,155,22,.19), 0 8px 18px -10px rgba(217,155,22,.28) !important;
    }

    body.wiboost-admin-shell .admin-detail-subgrid {
        display: grid;
        gap: .75rem;
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }

    body.wiboost-admin-shell .admin-detail-subfield {
        min-width: 0;
        border: 1px solid #ffffff;
        border-radius: 1rem;
        background: linear-gradient(180deg, #ffffff 0%, #edf4ff 100%);
        padding: .8rem .9rem;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.98), inset 0 -2px 5px rgba(163,187,251,.20), 0 8px 18px -10px rgba(90,118,200,.30);
    }

    body.wiboost-admin-shell .admin-detail-panel p,
    body.wiboost-admin-shell .admin-detail-subfield p {
        white-space: pre-line;
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
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
        background: transparent !important;
        background-image: none !important;
        padding: .75rem .65rem .75rem .75rem !important;
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
        background: linear-gradient(180deg, #ffffff 0%, #ffe5e5 100%) !important;
        color: #ff4d4d !important;
        font-size: 0 !important;
        box-shadow: 0 14px 28px rgba(255, 107, 107, .2), inset 0 1px 0 rgba(255,255,255,.9) !important;
        opacity: 1 !important;
        transition: transform .16s ease, background .16s ease, color .16s ease !important;
    }

    body.wiboost-admin-shell .admin-detail-close:hover,
    body.wiboost-admin-shell .admin-detail-header button[onclick*="closeAdmin"]:hover {
        background: linear-gradient(180deg, #fff5f5 0%, #ffcaca 100%) !important;
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

    @media (max-width: 767px) {
        body.wiboost-admin-shell .admin-detail-modal {
            padding: .9rem !important;
            min-height: 0 !important;
        }

        body.wiboost-admin-shell .admin-detail-modal::before {
            clip-path: inset(0) !important;
            background: rgba(15, 23, 42, .84);
        }

        body.wiboost-admin-shell .admin-detail-modal > .absolute.inset-0 {
            left: 0 !important;
        }

        body.wiboost-admin-shell .admin-detail-card {
            max-height: calc(100dvh - 1.8rem) !important;
            transform: scale(.95) !important;
        }

        body.wiboost-admin-shell .admin-detail-modal.modal-active .admin-detail-card {
            transform: scale(1) !important;
        }

        body.wiboost-admin-shell .admin-detail-body {
            padding: .65rem .45rem .65rem .65rem !important;
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
            modal.style.removeProperty('--admin-detail-modal-top');
            modal.style.removeProperty('--admin-detail-modal-height');
        };

        const syncScrollLock = () => {
            document.documentElement.classList.toggle('admin-detail-scroll-locked',
                Boolean(document.querySelector('.admin-detail-modal.modal-active')));
        };

        const closeModal = (modal) => {
            if (!(modal instanceof HTMLElement)) return;
            modal.dispatchEvent(new CustomEvent('admin-detail-closed', { bubbles: true }));
            modal.classList.remove('modal-active');
            syncScrollLock();
            if (!document.querySelector('.admin-detail-modal.modal-active')) {
                document.body.classList.remove('admin-detail-open');
            }
        };

        const fitDetailLinks = (modal) => {
            modal?.querySelectorAll?.('[data-admin-fit-link]').forEach((link) => {
                if (!(link instanceof HTMLElement) || link.clientWidth <= 0) return;

                let size = Number.parseFloat(link.dataset.fitStartSize || '14');
                const minimum = Number.parseFloat(link.dataset.fitMinimumSize || '7');
                link.style.fontSize = `${size}px`;

                while (link.scrollWidth > link.clientWidth && size > minimum) {
                    size = Math.max(minimum, size - 0.5);
                    link.style.fontSize = `${size}px`;
                }
            });
        };

        const prepareModal = (modal) => {
            if (!modal || modal.dataset.adminDetailPrepared === 'true') return;
            modal.dataset.adminDetailPrepared = 'true';

            let backdrop = Array.from(modal.children).find((child) => child.matches?.('[data-admin-detail-backdrop], .absolute.inset-0'));
            if (!backdrop) {
                backdrop = document.createElement('div');
                backdrop.className = 'absolute inset-0';
                modal.prepend(backdrop);
            }
            backdrop.dataset.adminDetailBackdrop = 'true';
            backdrop.setAttribute('aria-hidden', 'true');

            syncFrame(modal);
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
        };

        const syncActive = () => {
            document.querySelectorAll('.admin-detail-modal.modal-active').forEach((modal) => {
                prepareModal(modal);
                syncFrame(modal);
                fitDetailLinks(modal);
            });
        };

        window.openAdminUniversalDetail = window.openAdminUniversalDetail || function (id) {
            const modal = document.getElementById(id);
            if (!modal) return;
            prepareModal(modal);
            syncFrame(modal);
            modal.classList.add('modal-active');
            syncScrollLock();
            document.body.classList.add('admin-detail-open');
            const detailBody = modal.querySelector('.admin-detail-body');
            if (detailBody instanceof HTMLElement) {
                detailBody.scrollTop = 0;
                detailBody.scrollLeft = 0;
            }
            requestAnimationFrame(() => {
                syncFrame(modal);
                fitDetailLinks(modal);
            });
        };

        window.closeAdminUniversalDetail = window.closeAdminUniversalDetail || function (id) {
            const modal = document.getElementById(id);
            closeModal(modal);
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
                    syncScrollLock();
                    requestAnimationFrame(() => {
                        syncFrame(modal);
                        fitDetailLinks(modal);
                    });
                } else if (!document.querySelector('.admin-detail-modal.modal-active')) {
                    document.body.classList.remove('admin-detail-open');
                    syncScrollLock();
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

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') return;
            document.querySelectorAll('.admin-detail-modal.modal-active').forEach(closeModal);
        });

        document.addEventListener('pointerdown', (event) => {
            const target = event.target;
            if (!(target instanceof Element)) return;

            const backdrop = target.closest('[data-admin-detail-backdrop]');
            const modal = backdrop?.closest('.admin-detail-modal.modal-active')
                || (target.matches('.admin-detail-modal.modal-active') ? target : null);
            if (!modal) return;

            closeModal(modal);
        }, true);
    })();
</script>
