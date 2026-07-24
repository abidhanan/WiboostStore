@php
    $displayPopupItems = collect();
    $isDashboardDisplayTarget = request()->routeIs('user.dashboard')
        || request()->routeIs('dashboard')
        || request()->is('user/dashboard')
        || request()->path() === 'user/dashboard';
    $shouldRenderDisplayPopup = $isDashboardDisplayTarget
        && (bool) session()->pull('wiboost_show_displays_after_login', false);

    if ($shouldRenderDisplayPopup && class_exists(\App\Models\Display::class) && \Illuminate\Support\Facades\Schema::hasTable('displays')) {
        $displayPopupItems = \App\Models\Display::query()
            ->when(\Illuminate\Support\Facades\Schema::hasColumn('displays', 'is_active'), fn ($query) => $query->where('is_active', true))
            ->where(function ($query) {
                $query->whereNotNull('image')
                    ->orWhereNotNull('title')
                    ->orWhereNotNull('description')
                    ->orWhereNotNull('badge_text')
                    ->orWhereNotNull('emoji');
            })
            ->latest('created_at')
            ->latest('id')
            ->get()
            ->map(fn ($display) => [
                'id' => (int) $display->id,
                'title' => $display->title ?: '',
                'description' => $display->description ?: '',
                'badge' => $display->badge_text ?: '',
                'emoji' => $display->emoji ?: '',
                'theme' => $display->theme ?: 'blue',
                'image' => filled($display->image) ? \Illuminate\Support\Facades\Storage::url($display->image) : '',
                'link' => $display->link ?: '',
            ])
            ->values();
    }

    $firstDisplay = $displayPopupItems->first() ?? [];
    $firstDisplayHasImage = filled($firstDisplay['image'] ?? '');
    $firstDisplayTheme = $firstDisplay['theme'] ?? 'blue';
    $firstDisplayBackground = match ($firstDisplayTheme) {
        'teal' => 'linear-gradient(135deg, #4bc6b9, #3ba398)',
        'orange' => 'linear-gradient(135deg, #fbbf24, #d97706)',
        'rose' => 'linear-gradient(135deg, #fb7185, #e11d48)',
        default => 'linear-gradient(135deg, #8faaf3, #5a76c8)',
    };
@endphp

@if($displayPopupItems->isNotEmpty())
    <style>
        #wiboost-display-overlay {
            position: fixed !important;
            inset: 0 !important;
            z-index: 2147483647 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 16px !important;
            background: rgba(23, 33, 62, .74) !important;
            pointer-events: auto !important;
            -webkit-backdrop-filter: blur(8px);
            backdrop-filter: blur(8px);
        }

        #wiboost-display-card {
            position: relative !important;
            width: min(92vw, 384px) !important;
            max-height: 86vh !important;
            aspect-ratio: 4 / 5 !important;
            overflow: visible !important;
            border: 4px solid #fff !important;
            border-radius: 32px !important;
            color: #fff !important;
            box-shadow: 0 28px 80px rgba(23, 33, 62, .45) !important;
            opacity: 1 !important;
            transform: scale(1) !important;
            transition: opacity .18s ease, transform .18s ease !important;
        }

        #wiboost-display-card.wiboost-display-card-waiting {
            opacity: 0 !important;
            pointer-events: none !important;
            transform: scale(.96) !important;
        }

        #wiboost-display-close {
            position: absolute !important;
            top: -22px !important;
            right: -22px !important;
            z-index: 50 !important;
            width: 44px !important;
            height: 44px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            border: 2px solid #fff !important;
            border-radius: 999px !important;
            background: #fff !important;
            color: #ff4d4d !important;
            box-shadow: 0 12px 28px rgba(255, 77, 77, .28) !important;
            cursor: pointer !important;
        }

        #wiboost-display-link {
            position: relative !important;
            display: block !important;
            width: 100% !important;
            height: 100% !important;
            overflow: hidden !important;
            border-radius: 28px !important;
            color: inherit !important;
            text-decoration: none !important;
        }

        #wiboost-display-image,
        #wiboost-display-shade {
            position: absolute !important;
            inset: 0 !important;
            width: 100% !important;
            height: 100% !important;
        }

        #wiboost-display-image {
            z-index: 1 !important;
            object-fit: cover !important;
        }

        #wiboost-display-shade {
            z-index: 2 !important;
            background: linear-gradient(135deg, rgba(23, 33, 62, .72), rgba(23, 33, 62, .16), rgba(23, 33, 62, .08)) !important;
        }

        #wiboost-display-emoji {
            position: absolute !important;
            top: 64px !important;
            right: 24px !important;
            z-index: 3 !important;
            color: rgba(255, 255, 255, .28) !important;
            font-size: 64px !important;
            font-weight: 900 !important;
            line-height: 1 !important;
        }

        #wiboost-display-copy {
            position: absolute !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            z-index: 4 !important;
            padding: 24px !important;
        }

        #wiboost-display-badge {
            width: fit-content !important;
            margin: 0 0 12px !important;
            padding: 7px 16px !important;
            border: 1px solid rgba(255, 255, 255, .35) !important;
            border-radius: 999px !important;
            background: rgba(255, 255, 255, .2) !important;
            color: #fff !important;
            font-size: 10px !important;
            font-weight: 900 !important;
            letter-spacing: .16em !important;
            text-transform: uppercase !important;
        }

        #wiboost-display-title {
            margin: 0 !important;
            color: #fff !important;
            font-size: 28px !important;
            font-weight: 900 !important;
            line-height: 1.05 !important;
            text-shadow: 0 3px 16px rgba(23, 33, 62, .32) !important;
        }

        #wiboost-display-description {
            margin: 12px 0 0 !important;
            color: rgba(255, 255, 255, .92) !important;
            font-size: 14px !important;
            font-weight: 800 !important;
            line-height: 1.45 !important;
            text-shadow: 0 2px 12px rgba(23, 33, 62, .25) !important;
        }

        .wiboost-display-hidden {
            display: none !important;
        }
    </style>

    <div id="wiboost-display-overlay" aria-modal="true" role="dialog" style="position: fixed; inset: 0; z-index: 2147483647; display: flex; align-items: center; justify-content: center; padding: 16px; background: rgba(23, 33, 62, .74); pointer-events: auto;">
        <div id="wiboost-display-card" style="position: relative; width: min(92vw, 384px); max-height: 86vh; aspect-ratio: 4 / 5; overflow: visible; border: 4px solid #fff; border-radius: 32px; color: #fff; box-shadow: 0 28px 80px rgba(23, 33, 62, .45); background: {{ $firstDisplayBackground }};">
            <button id="wiboost-display-close" type="button" aria-label="Tutup display" onclick="if (window.wiboostDisplayNext) { return window.wiboostDisplayNext(event); } this.closest('#wiboost-display-overlay')?.remove(); document.body.style.overflow=''; return false;" style="position: absolute; top: -22px; right: -22px; z-index: 50; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; border: 2px solid #fff; border-radius: 999px; background: #fff; color: #ff4d4d; box-shadow: 0 12px 28px rgba(255, 77, 77, .28); cursor: pointer;">
                <svg style="width: 20px; height: 20px; display: block;" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
                </svg>
            </button>

            <a id="wiboost-display-link" href="{{ ($firstDisplay['link'] ?? '') ?: '#' }}" style="position: relative; display: block; width: 100%; height: 100%; overflow: hidden; border-radius: 28px; color: inherit; text-decoration: none;">
                <img id="wiboost-display-image" src="{{ $firstDisplayHasImage ? $firstDisplay['image'] : '' }}" alt="{{ $firstDisplay['title'] ?? 'Display Wiboost Store' }}" class="{{ $firstDisplayHasImage ? '' : 'wiboost-display-hidden' }}" style="position: absolute; inset: 0; z-index: 1; width: 100%; height: 100%; object-fit: cover;">
                <div id="wiboost-display-shade" class="{{ $firstDisplayHasImage ? 'wiboost-display-hidden' : '' }}" style="position: absolute; inset: 0; z-index: 2; width: 100%; height: 100%; background: linear-gradient(135deg, rgba(23, 33, 62, .72), rgba(23, 33, 62, .16), rgba(23, 33, 62, .08));"></div>
                <div id="wiboost-display-emoji" style="position: absolute; top: 64px; right: 24px; z-index: 3; color: rgba(255, 255, 255, .28); font-size: 64px; font-weight: 900; line-height: 1;">{{ $firstDisplayHasImage ? '' : ($firstDisplay['emoji'] ?? '') }}</div>
                <div id="wiboost-display-copy" style="position: absolute; left: 0; right: 0; bottom: 0; z-index: 4; padding: 24px;">
                    <p id="wiboost-display-badge" class="{{ (! $firstDisplayHasImage && filled($firstDisplay['badge'] ?? '')) ? '' : 'wiboost-display-hidden' }}" style="width: fit-content; margin: 0 0 12px; padding: 7px 16px; border: 1px solid rgba(255, 255, 255, .35); border-radius: 999px; background: rgba(255, 255, 255, .2); color: #fff; font-size: 10px; font-weight: 900; letter-spacing: .16em; text-transform: uppercase;">{{ $firstDisplay['badge'] ?? '' }}</p>
                    <h2 id="wiboost-display-title" class="{{ (! $firstDisplayHasImage && filled($firstDisplay['title'] ?? '')) ? '' : 'wiboost-display-hidden' }}" style="margin: 0; color: #fff; font-size: 28px; font-weight: 900; line-height: 1.05; text-shadow: 0 3px 16px rgba(23, 33, 62, .32);">{{ $firstDisplay['title'] ?? '' }}</h2>
                    <p id="wiboost-display-description" class="{{ (! $firstDisplayHasImage && filled($firstDisplay['description'] ?? '')) ? '' : 'wiboost-display-hidden' }}" style="margin: 12px 0 0; color: rgba(255, 255, 255, .92); font-size: 14px; font-weight: 800; line-height: 1.45; text-shadow: 0 2px 12px rgba(23, 33, 62, .25);">{{ $firstDisplay['description'] ?? '' }}</p>
                </div>
            </a>
        </div>
    </div>

    <script>
        (() => {
            const displays = @json($displayPopupItems);
            if (!Array.isArray(displays) || displays.length === 0) return;

            const themeMap = {
                blue: 'linear-gradient(135deg, #8faaf3, #5a76c8)',
                teal: 'linear-gradient(135deg, #4bc6b9, #3ba398)',
                orange: 'linear-gradient(135deg, #fbbf24, #d97706)',
                rose: 'linear-gradient(135deg, #fb7185, #e11d48)',
            };

            const overlay = document.getElementById('wiboost-display-overlay');
            const card = document.getElementById('wiboost-display-card');
            const closeButton = document.getElementById('wiboost-display-close');
            const link = document.getElementById('wiboost-display-link');
            const image = document.getElementById('wiboost-display-image');
            const shade = document.getElementById('wiboost-display-shade');
            const badge = document.getElementById('wiboost-display-badge');
            const title = document.getElementById('wiboost-display-title');
            const description = document.getElementById('wiboost-display-description');
            const emoji = document.getElementById('wiboost-display-emoji');
            const displaySwitchDelay = 320;
            let index = 0;
            let isSwitchingDisplay = false;

            const setHidden = (element, hidden) => {
                if (!element) return;
                element.classList.toggle('wiboost-display-hidden', Boolean(hidden));
            };

            const notifyDisplayClosed = () => {
                try {
                    sessionStorage.setItem('wiboost_mascot_force_hello', '1');
                } catch (error) {}

                if (typeof window.wiboostMascotForceHello === 'function') {
                    window.wiboostMascotForceHello(9000);
                }

                window.dispatchEvent(new CustomEvent('wiboost:display-closed'));
            };

            const hideOverlay = () => {
                overlay?.remove();
                document.body.style.overflow = '';
                notifyDisplayClosed();
            };

            const showFallback = (display) => {
                setHidden(image, true);
                setHidden(shade, false);
                badge.textContent = display.badge || '';
                title.textContent = display.title || 'Display Wiboost Store';
                description.textContent = display.description || '';
                emoji.textContent = display.emoji || '';
                setHidden(badge, !display.badge);
                setHidden(title, false);
                setHidden(description, !display.description);
            };

            const showDisplay = () => {
                const display = displays[index];
                if (!display || !overlay || !card || !link) {
                    hideOverlay();
                    return;
                }

                const hasImage = Boolean(display.image);
                card.style.background = themeMap[display.theme] || themeMap.blue;

                if (hasImage) {
                    image.onload = () => {
                        setHidden(image, false);
                        setHidden(shade, true);
                        setHidden(badge, true);
                        setHidden(title, true);
                        setHidden(description, true);
                        emoji.textContent = '';
                    };
                    image.onerror = () => showFallback(display);
                    image.src = display.image;
                    image.alt = display.title || 'Display Wiboost Store';
                    setHidden(image, false);
                    setHidden(shade, true);
                    setHidden(badge, true);
                    setHidden(title, true);
                    setHidden(description, true);
                    emoji.textContent = '';
                } else {
                    image.removeAttribute('src');
                    showFallback(display);
                }

                link.href = display.link || '#';
                document.body.style.overflow = 'hidden';
                window.requestAnimationFrame(() => {
                    card.classList.remove('wiboost-display-card-waiting');
                });
            };

            window.wiboostDisplayNext = (event) => {
                event?.preventDefault();
                event?.stopPropagation();
                event?.stopImmediatePropagation?.();

                if (isSwitchingDisplay) {
                    return false;
                }

                const nextIndex = index + 1;

                if (nextIndex >= displays.length) {
                    hideOverlay();
                    return false;
                }

                isSwitchingDisplay = true;
                closeButton?.setAttribute('disabled', 'disabled');
                card?.classList.add('wiboost-display-card-waiting');

                window.setTimeout(() => {
                    index = nextIndex;
                    showDisplay();
                    closeButton?.removeAttribute('disabled');
                    isSwitchingDisplay = false;
                }, displaySwitchDelay);

                return false;
            };

            closeButton?.addEventListener('click', window.wiboostDisplayNext);

            overlay?.addEventListener('click', (event) => {
                if (event.target !== overlay) return;
                window.wiboostDisplayNext(event);
            });

            document.addEventListener('keydown', (event) => {
                if (event.key !== 'Escape' || !document.getElementById('wiboost-display-overlay')) return;
                window.wiboostDisplayNext(event);
            });

            link?.addEventListener('click', (event) => {
                if (!displays[index]?.link) event.preventDefault();
            });

            showDisplay();
        })();
    </script>
@elseif($shouldRenderDisplayPopup)
    <script>
        (() => {
            try {
                sessionStorage.setItem('wiboost_mascot_force_hello', '1');
            } catch (error) {}

            const forceMascotHello = () => {
                try {
                    sessionStorage.setItem('wiboost_mascot_force_hello', '1');
                } catch (error) {}

                if (typeof window.wiboostMascotForceHello === 'function') {
                    window.wiboostMascotForceHello(9000);
                    return;
                }

                window.addEventListener('wiboost:mascot-ready', () => {
                    window.wiboostMascotForceHello?.(9000);
                }, { once: true });
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', forceMascotHello, { once: true });
            } else {
                forceMascotHello();
            }
        })();
    </script>
@endif
