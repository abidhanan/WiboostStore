@php
    $adminNumber = \App\Support\WiboostAdminContact::whatsappNumber();
    $shouldShowReportButton = $adminNumber
        && (! auth()->check() || ! auth()->user()->isAdmin())
        && ! request()->routeIs('user.report.admin*');
    $reportUrl = auth()->check() && ! auth()->user()->isAdmin()
        ? route('user.report.admin')
        : route('login');
    $mascotFiles = [
        'hello' => 'hello.png',
        'idle' => 'idle.png',
        'bored' => 'bored.png',
        'drag' => 'drag.png',
    ];
    $mascotImageSources = [];

    foreach ($mascotFiles as $expression => $fileName) {
        $mascotPath = public_path("images/wiboost-cs-mascot/{$fileName}");
        $mascotImageSources[$expression] = is_file($mascotPath)
            ? 'data:image/png;base64,'.base64_encode(file_get_contents($mascotPath))
            : asset("images/wiboost-cs-mascot/{$fileName}").'?v=20260720-cs8';
    }

    $mascotSessionMarker = sha1(session()->getId().'|'.(auth()->id() ?? 'guest'));
@endphp

@if ($shouldShowReportButton)
    @once
        <style>
            @keyframes wiboostMascotFloatBob {
                0%, 100% { transform: translateY(0) rotate(-1deg); }
                50% { transform: translateY(-9px) rotate(1deg); }
            }

            @keyframes wiboostMascotPop {
                0% { transform: scale(.88) rotate(-5deg); }
                56% { transform: scale(1.08) rotate(3deg); }
                100% { transform: scale(1) rotate(0); }
            }

            .wiboost-mascot-float {
                --wiboost-mascot-size: clamp(5.4rem, 17vw, 7.4rem);
                --wiboost-tilt-x: 0deg;
                --wiboost-tilt-y: 0deg;
                appearance: none;
                background: transparent;
                border: 0;
                bottom: max(1rem, env(safe-area-inset-bottom));
                cursor: grab;
                height: var(--wiboost-mascot-size);
                opacity: 0;
                padding: 0;
                pointer-events: none;
                right: max(1rem, env(safe-area-inset-right));
                touch-action: none;
                user-select: none;
                visibility: hidden;
                width: var(--wiboost-mascot-size);
                -webkit-tap-highlight-color: transparent;
                -webkit-user-drag: none;
            }

            .wiboost-mascot-float.is-positioned {
                bottom: auto;
                opacity: 1;
                pointer-events: auto;
                right: auto;
                visibility: visible;
            }

            .wiboost-mascot-float.is-dragging {
                cursor: grabbing;
                transform: none !important;
                transition: none !important;
            }

            .wiboost-cs-mascot {
                animation: wiboostMascotFloatBob 4.2s ease-in-out infinite;
                display: block;
                height: 100%;
                position: relative;
                transform-origin: 50% 78%;
                width: 100%;
            }

            .wiboost-cs-mascot-core {
                display: block;
                height: 100%;
                position: relative;
                transform: perspective(520px) rotateX(var(--wiboost-tilt-y)) rotateY(var(--wiboost-tilt-x));
                transform-origin: 50% 72%;
                transition: filter .18s ease, transform .18s ease;
                width: 100%;
                z-index: 1;
            }

            .wiboost-mascot-float:active .wiboost-cs-mascot-core,
            .wiboost-mascot-float.is-pressing .wiboost-cs-mascot-core {
                transform: perspective(520px) rotateX(var(--wiboost-tilt-y)) rotateY(var(--wiboost-tilt-x)) scale(.94) translateY(5px);
            }

            .wiboost-mascot-float.is-dragging .wiboost-cs-mascot-core,
            .wiboost-mascot-float.is-dragging:active .wiboost-cs-mascot-core {
                transform: perspective(520px) rotateX(var(--wiboost-tilt-y)) rotateY(var(--wiboost-tilt-x)) !important;
            }

            .wiboost-cs-mascot-image {
                animation: wiboostMascotPop .38s ease both;
                display: block;
                height: 100%;
                inset: 0;
                object-fit: contain;
                opacity: 0;
                pointer-events: none;
                position: absolute;
                transform-origin: 50% 78%;
                transition: opacity .14s ease;
                width: 100%;
                -webkit-user-drag: none;
            }

            .wiboost-cs-mascot-image.is-active {
                opacity: 1;
                z-index: 2;
            }

            .wiboost-mascot-float.is-dragging .wiboost-cs-mascot-image {
                animation: none !important;
                transform: none !important;
            }

            @media (prefers-reduced-motion: reduce) {
                .wiboost-cs-mascot,
                .wiboost-cs-mascot-image {
                    animation: none !important;
                }
            }
        </style>
    @endonce

    <button type="button"
            aria-label="Buka bantuan customer service"
            title="Bantuan Customer Service"
            data-report-url="{{ $reportUrl }}"
            data-wiboost-mascot-float
            class="wiboost-mascot-float fixed z-[99] block transition-transform hover:scale-105 active:scale-100">
        <span class="wiboost-cs-mascot" aria-hidden="true">
            <span class="wiboost-cs-mascot-core">
                <img class="wiboost-cs-mascot-image"
                     data-wiboost-expression="hello"
                     src="{{ $mascotImageSources['hello'] }}"
                     alt=""
                     loading="eager"
                     decoding="async"
                     fetchpriority="high">
                <img class="wiboost-cs-mascot-image"
                     data-wiboost-expression="idle"
                     src="{{ $mascotImageSources['idle'] }}"
                     alt=""
                     loading="eager"
                     decoding="async">
                <img class="wiboost-cs-mascot-image"
                     data-wiboost-expression="bored"
                     src="{{ $mascotImageSources['bored'] }}"
                     alt=""
                     loading="eager"
                     decoding="async">
                <img class="wiboost-cs-mascot-image"
                     data-wiboost-expression="drag"
                     src="{{ $mascotImageSources['drag'] }}"
                     alt=""
                     loading="eager"
                     decoding="async">
            </span>
        </span>
    </button>

    @once
        <script>
            (() => {
                const storageKey = 'wiboost_mascot_float_position';
                const sessionStorageKey = 'wiboost_mascot_float_session';
                const expressionStorageKey = 'wiboost_mascot_float_expression';
                const forceHelloStorageKey = 'wiboost_mascot_force_hello';
                const currentSessionMarker = @json($mascotSessionMarker);
                const dragThreshold = 6;
                const expressionTimers = new WeakMap();
                const validExpressions = new Set(['hello', 'idle', 'bored', 'drag']);

                const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

                const clearExpressionTimers = (element) => {
                    const timers = expressionTimers.get(element) || [];
                    timers.forEach((timer) => window.clearTimeout(timer));
                    expressionTimers.set(element, []);
                };

                const queueExpression = (element, callback, delay) => {
                    const timers = expressionTimers.get(element) || [];
                    const timer = window.setTimeout(callback, delay);
                    timers.push(timer);
                    expressionTimers.set(element, timers);
                };

                const storedExpression = () => {
                    try {
                        const expression = localStorage.getItem(expressionStorageKey) || '';
                        return validExpressions.has(expression) ? expression : null;
                    } catch (error) {
                        return null;
                    }
                };

                const consumeForcedHello = () => {
                    try {
                        const shouldForce = sessionStorage.getItem(forceHelloStorageKey) === '1';
                        sessionStorage.removeItem(forceHelloStorageKey);

                        return shouldForce;
                    } catch (error) {
                        return false;
                    }
                };

                const rememberExpression = (expression) => {
                    if (!validExpressions.has(expression)) {
                        return;
                    }

                    try {
                        localStorage.setItem(expressionStorageKey, expression);
                    } catch (error) {}
                };

                const isGreetingLocked = (element) => Number(element.dataset.wiboostGreetingLockedUntil || 0) > Date.now();

                const isFreshMascotSession = () => {
                    try {
                        return localStorage.getItem(sessionStorageKey) !== currentSessionMarker;
                    } catch (error) {
                        return true;
                    }
                };

                const setExpression = (element, expression, remember = true) => {
                    element.querySelectorAll('[data-wiboost-expression]').forEach((image) => {
                        image.classList.toggle('is-active', image.dataset.wiboostExpression === expression);
                    });

                    if (remember) {
                        rememberExpression(expression);
                    }
                };

                const startExpressionLoop = (element) => {
                    clearExpressionTimers(element);
                    setExpression(element, 'idle');
                    queueExpression(element, () => setExpression(element, 'bored'), 9000);
                    queueExpression(element, () => startExpressionLoop(element), 16500);
                };

                const startHelloSequence = (element, idleDelay = 3600) => {
                    clearExpressionTimers(element);
                    setExpression(element, 'hello');
                    queueExpression(element, () => setExpression(element, 'idle'), idleDelay);
                    queueExpression(element, () => setExpression(element, 'bored'), idleDelay + 9000);
                    queueExpression(element, () => startExpressionLoop(element), idleDelay + 16500);
                };

                const playHelloThenLoop = (element, delay = 3600) => {
                    startHelloSequence(element, delay);
                };

                const resumeStoredExpression = (element, expression = storedExpression()) => {
                    clearExpressionTimers(element);
                    const safeExpression = validExpressions.has(expression) ? expression : 'idle';
                    setExpression(element, safeExpression);

                    if (safeExpression === 'bored') {
                        queueExpression(element, () => startExpressionLoop(element), 7000);
                    } else if (safeExpression === 'hello' || safeExpression === 'drag') {
                        queueExpression(element, () => setExpression(element, 'idle'), 3600);
                        queueExpression(element, () => setExpression(element, 'bored'), 12600);
                        queueExpression(element, () => startExpressionLoop(element), 20100);
                    } else {
                        queueExpression(element, () => setExpression(element, 'bored'), 9000);
                        queueExpression(element, () => startExpressionLoop(element), 16500);
                    }
                };

                const playDragFinishedWelcome = (element) => {
                    element.dataset.wiboostJustDraggedUntil = String(Date.now() + 5400);
                    element.dataset.wiboostGreetingLockedUntil = String(Date.now() + 5400);
                    startHelloSequence(element, 4200);
                    const keepHelloSequence = () => startHelloSequence(element, 4200);
                    window.requestAnimationFrame?.(keepHelloSequence);
                    window.setTimeout(keepHelloSequence, 80);
                };

                const viewportBounds = (element) => {
                    const margin = 8;
                    const width = element.offsetWidth || 92;
                    const height = element.offsetHeight || 92;

                    return {
                        minX: margin,
                        minY: margin,
                        maxX: Math.max(margin, window.innerWidth - width - margin),
                        maxY: Math.max(margin, window.innerHeight - height - margin),
                    };
                };

                const applyPosition = (element, left, top) => {
                    const bounds = viewportBounds(element);
                    element.style.left = `${clamp(left, bounds.minX, bounds.maxX)}px`;
                    element.style.top = `${clamp(top, bounds.minY, bounds.maxY)}px`;
                    element.style.right = 'auto';
                    element.style.bottom = 'auto';
                    element.classList.add('is-positioned');
                };

                const defaultPosition = (element) => {
                    const bounds = viewportBounds(element);
                    applyPosition(element, bounds.maxX - 4, bounds.maxY - 4);
                };

                const restorePosition = (element) => {
                    try {
                        if (localStorage.getItem(sessionStorageKey) !== currentSessionMarker) {
                            localStorage.removeItem(storageKey);
                            localStorage.setItem(sessionStorageKey, currentSessionMarker);
                            defaultPosition(element);
                            return true;
                        }

                        const saved = JSON.parse(localStorage.getItem(storageKey) || 'null');

                        if (saved && Number.isFinite(saved.left) && Number.isFinite(saved.top)) {
                            applyPosition(element, saved.left, saved.top);
                            return false;
                        }
                    } catch (error) {}

                    defaultPosition(element);
                    return true;
                };

                const savePosition = (element) => {
                    const rect = element.getBoundingClientRect();
                    try {
                        localStorage.setItem(sessionStorageKey, currentSessionMarker);
                        localStorage.setItem(storageKey, JSON.stringify({
                            left: Math.round(rect.left),
                            top: Math.round(rect.top),
                        }));
                    } catch (error) {}
                };

                const resetTilt = (element) => {
                    element.style.setProperty('--wiboost-tilt-x', '0deg');
                    element.style.setProperty('--wiboost-tilt-y', '0deg');
                };

                const updateTilt = (element, clientX, clientY) => {
                    const rect = element.getBoundingClientRect();
                    const x = ((clientX - rect.left) / Math.max(1, rect.width)) - .5;
                    const y = ((clientY - rect.top) / Math.max(1, rect.height)) - .5;

                    element.style.setProperty('--wiboost-tilt-x', `${clamp(x * 16, -8, 8)}deg`);
                    element.style.setProperty('--wiboost-tilt-y', `${clamp(y * -12, -6, 6)}deg`);
                };

                const setupMascot = (element) => {
                    if (!element || element.dataset.wiboostMascotReady === 'true') {
                        return;
                    }

                    element.dataset.wiboostMascotReady = 'true';
                    const lastExpression = storedExpression();
                    const hasStoredExpression = Boolean(lastExpression);
                    setExpression(element, lastExpression || 'idle', false);

                    const shouldGreet = restorePosition(element);
                    if (consumeForcedHello()) {
                        playHelloThenLoop(element, 9000);
                    } else if (shouldGreet && !hasStoredExpression) {
                        playHelloThenLoop(element);
                    } else {
                        resumeStoredExpression(element, lastExpression);
                    }

                    let pointerId = null;
                    let startX = 0;
                    let startY = 0;
                    let startLeft = 0;
                    let startTop = 0;
                    let moved = false;
                    let blockClick = false;
                    let suppressClickUntil = 0;
                    let draggedOnce = false;

                    element.setAttribute('draggable', 'false');
                    element.addEventListener('dragstart', (event) => event.preventDefault());

                    element.addEventListener('pointerenter', () => {
                        element.classList.add('is-hovering');
                        clearExpressionTimers(element);
                        setExpression(element, 'hello');
                    });

                    element.addEventListener('pointerleave', () => {
                        if (pointerId === null) {
                            element.classList.remove('is-hovering');
                            resetTilt(element);
                            if (isGreetingLocked(element)) {
                                return;
                            }
                            startExpressionLoop(element);
                        }
                    });

                    element.addEventListener('pointerdown', (event) => {
                        if (event.button !== undefined && event.button !== 0) {
                            return;
                        }

                        const rect = element.getBoundingClientRect();
                        pointerId = event.pointerId;
                        startX = event.clientX;
                        startY = event.clientY;
                        startLeft = rect.left;
                        startTop = rect.top;
                        moved = false;
                        if (Date.now() >= suppressClickUntil) {
                            blockClick = false;
                            draggedOnce = false;
                        }
                        clearExpressionTimers(element);
                        setExpression(element, 'hello');
                        element.classList.add('is-pressing', 'is-hovering');
                        updateTilt(element, event.clientX, event.clientY);
                        element.setPointerCapture?.(pointerId);
                    });

                    element.addEventListener('pointermove', (event) => {
                        updateTilt(element, event.clientX, event.clientY);

                        if (pointerId !== event.pointerId) {
                            return;
                        }

                        const deltaX = event.clientX - startX;
                        const deltaY = event.clientY - startY;

                        if (!moved && Math.hypot(deltaX, deltaY) < dragThreshold) {
                            return;
                        }

                        moved = true;
                        blockClick = true;
                        draggedOnce = true;
                        suppressClickUntil = Date.now() + 1200;
                        element.classList.add('is-dragging');
                        setExpression(element, 'drag');
                        applyPosition(element, startLeft + deltaX, startTop + deltaY);
                        event.preventDefault();
                    });

                    const stopDrag = (event) => {
                        if (pointerId !== event.pointerId) {
                            return;
                        }

                        element.releasePointerCapture?.(pointerId);
                        pointerId = null;
                        element.classList.remove('is-pressing', 'is-dragging');
                        resetTilt(element);

                        if (moved) {
                            event.preventDefault();
                            event.stopPropagation();
                            blockClick = true;
                            draggedOnce = true;
                            suppressClickUntil = Date.now() + 1200;
                            savePosition(element);
                            playDragFinishedWelcome(element);
                            window.setTimeout(() => {
                                blockClick = false;
                                draggedOnce = false;
                            }, 1200);
                        } else {
                            if (element.matches(':hover')) {
                                clearExpressionTimers(element);
                                setExpression(element, 'hello');
                            } else {
                                startExpressionLoop(element);
                            }
                        }
                    };

                    element.addEventListener('pointerup', stopDrag);
                    element.addEventListener('pointercancel', stopDrag);
                    element.addEventListener('lostpointercapture', () => {
                        pointerId = null;
                        element.classList.remove('is-pressing', 'is-dragging');
                        resetTilt(element);
                        if (Number(element.dataset.wiboostJustDraggedUntil || 0) > Date.now()) {
                            return;
                        }
                        if (isGreetingLocked(element)) {
                            return;
                        }
                        if (element.matches(':hover')) {
                            clearExpressionTimers(element);
                            setExpression(element, 'hello');
                        } else {
                            startExpressionLoop(element);
                        }
                    });

                    element.addEventListener('click', (event) => {
                        if (blockClick || draggedOnce || Date.now() < suppressClickUntil) {
                            event.preventDefault();
                            event.stopPropagation();
                            event.stopImmediatePropagation?.();
                            return;
                        }

                        const reportUrl = element.dataset.reportUrl;
                        if (reportUrl) {
                            event.preventDefault();
                            clearExpressionTimers(element);
                            setExpression(element, 'hello');
                            window.setTimeout(() => window.location.assign(reportUrl), 160);
                        }
                    }, true);

                    element.addEventListener('dblclick', (event) => {
                        event.preventDefault();
                        defaultPosition(element);
                        savePosition(element);
                        startExpressionLoop(element);
                    });

                    window.addEventListener('resize', () => {
                        const rect = element.getBoundingClientRect();
                        applyPosition(element, rect.left, rect.top);
                        savePosition(element);
                    }, { passive: true });
                };

                const setupAllMascots = () => {
                    document.querySelectorAll('[data-wiboost-mascot-float]').forEach(setupMascot);
                };

                window.wiboostMascotForceHello = (delay = 9000) => {
                    document.querySelectorAll('[data-wiboost-mascot-float]').forEach((element) => {
                        if (!element || element.dataset.wiboostMascotReady !== 'true') {
                            return;
                        }

                        playHelloThenLoop(element, delay);
                    });
                };

                window.dispatchEvent(new CustomEvent('wiboost:mascot-ready'));

                window.addEventListener('wiboost:display-closed', () => {
                    window.wiboostMascotForceHello?.(9000);
                });

                setupAllMascots();

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', setupAllMascots, { once: true });
                }
            })();
        </script>
    @endonce
@endif
