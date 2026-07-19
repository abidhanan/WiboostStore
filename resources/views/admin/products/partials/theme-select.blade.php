@once
    <style>
        .admin-product-theme-select {
            position: relative;
            width: 100%;
        }

        .admin-product-theme-select-button {
            display: flex;
            width: 100%;
            min-height: 4rem;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            border: 4px solid #ffffff;
            border-radius: 1.5rem;
            background: var(--product-select-bg, #f4f9ff);
            padding: 1rem 1.25rem 1rem 1.5rem;
            color: #2b3a67;
            font-weight: 900;
            text-align: left;
            box-shadow: inset 0 2px 10px rgba(90, 118, 200, 0.08);
            outline: none;
            transition: border-color 180ms ease, background-color 180ms ease, box-shadow 180ms ease, transform 180ms ease;
        }

        .admin-product-theme-select-button:hover,
        .admin-product-theme-select.is-open .admin-product-theme-select-button {
            border-color: #bde0fe;
            background: #ffffff;
            box-shadow: 0 14px 30px rgba(189, 224, 254, 0.28);
        }

        .admin-product-theme-select-button:focus-visible {
            border-color: #8faaf3;
            box-shadow: 0 0 0 4px rgba(143, 170, 243, 0.22);
        }

        .admin-product-theme-select-label {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .admin-product-theme-select-arrow {
            display: inline-flex;
            width: 1.25rem;
            height: 1.25rem;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            color: #5a76c8;
            transition: transform 180ms ease;
        }

        .admin-product-theme-select.is-open .admin-product-theme-select-arrow {
            transform: rotate(180deg);
        }

        .admin-product-theme-select-menu {
            position: absolute;
            left: 0;
            right: 0;
            top: calc(100% + 0.6rem);
            z-index: 80;
            display: grid;
            max-height: 18rem;
            gap: 0.5rem;
            overflow-y: auto;
            overscroll-behavior: contain;
            border: 4px solid #ffffff;
            border-radius: 1.5rem;
            background: rgba(255, 255, 255, 0.98);
            padding: 0.6rem;
            box-shadow: 0 22px 44px rgba(43, 58, 103, 0.14);
            opacity: 0;
            pointer-events: none;
            transform: translateY(-0.35rem) scale(0.98);
            transform-origin: top;
            transition: opacity 160ms ease, transform 160ms ease;
        }

        .admin-product-theme-select.is-open .admin-product-theme-select-menu {
            opacity: 1;
            pointer-events: auto;
            transform: translateY(0) scale(1);
        }

        .admin-product-theme-select-menu.is-floating-open {
            display: grid !important;
            opacity: 1 !important;
            pointer-events: auto !important;
            transform: none !important;
        }

        .admin-product-theme-select-option {
            width: 100%;
            border: 2px solid transparent;
            border-radius: 1rem;
            background: #f4f9ff;
            padding: 0.9rem 1rem;
            color: #2b3a67;
            font-size: 0.9rem;
            font-weight: 900;
            text-align: left;
            transition: background-color 160ms ease, border-color 160ms ease, color 160ms ease, transform 160ms ease;
        }

        .admin-product-theme-select-option:hover,
        .admin-product-theme-select-option.is-selected {
            border-color: #bde0fe;
            background: #e0fbfc;
            color: #4bc6b9;
            transform: translateX(0.15rem);
        }

        .admin-product-theme-select-option:disabled {
            cursor: not-allowed;
            opacity: 0.55;
            transform: none;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const closeProductSelects = (except = null) => {
                document.querySelectorAll('[data-admin-product-theme-select].is-open').forEach((dropdown) => {
                    if (dropdown !== except) {
                        dropdown.classList.remove('is-open');
                        dropdown.querySelector('.admin-product-theme-select-button')?.setAttribute('aria-expanded', 'false');
                        window.wiboostResetFloatingMenu?.(dropdown, '.admin-product-theme-select-menu');
                    }
                });
            };

            document.querySelectorAll('[data-admin-product-theme-select]').forEach((dropdown) => {
                const button = dropdown.querySelector('.admin-product-theme-select-button');
                const label = dropdown.querySelector('[data-admin-product-theme-select-label]');
                const input = dropdown.querySelector('[data-admin-product-theme-select-input]');

                button?.addEventListener('click', function () {
                    const willOpen = !dropdown.classList.contains('is-open');
                    closeProductSelects(dropdown);
                    dropdown.classList.toggle('is-open', willOpen);
                    button.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                    if (willOpen) {
                        window.requestAnimationFrame(() => window.wiboostPositionFloatingMenu?.(dropdown, '.admin-product-theme-select-menu', 220, 288));
                    } else {
                        window.wiboostResetFloatingMenu?.(dropdown, '.admin-product-theme-select-menu');
                    }
                });

                dropdown.querySelectorAll('[data-admin-product-theme-select-option]').forEach((option) => {
                    option.addEventListener('click', function () {
                        if (option.disabled) {
                            return;
                        }

                        const value = option.dataset.value || '';
                        const text = option.dataset.label || option.textContent.trim();

                        if (input) {
                            input.value = value;
                            input.dispatchEvent(new Event('change', { bubbles: true }));
                        }

                        if (label) {
                            label.textContent = text;
                        }

                        dropdown.querySelectorAll('[data-admin-product-theme-select-option]').forEach((item) => item.classList.remove('is-selected'));
                        option.classList.add('is-selected');
                        dropdown.classList.remove('is-open');
                        button?.setAttribute('aria-expanded', 'false');
                        window.wiboostResetFloatingMenu?.(dropdown, '.admin-product-theme-select-menu');
                    });
                });
            });

            document.addEventListener('click', function (event) {
                if (!event.target.closest('[data-admin-product-theme-select]')) {
                    closeProductSelects();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeProductSelects();
                }
            });

            window.addEventListener('resize', () => {
                document.querySelectorAll('[data-admin-product-theme-select].is-open').forEach((dropdown) => window.wiboostPositionFloatingMenu?.(dropdown, '.admin-product-theme-select-menu', 220, 288));
            });
            window.addEventListener('scroll', () => {
                document.querySelectorAll('[data-admin-product-theme-select].is-open').forEach((dropdown) => window.wiboostPositionFloatingMenu?.(dropdown, '.admin-product-theme-select-menu', 220, 288));
            }, true);
        });
    </script>
@endonce

@php
    $selectedValue = (string) ($selectedValue ?? '');
    $selectedOption = collect($options ?? [])->first(fn ($option) => (string) ($option['value'] ?? '') === $selectedValue);
    $selectedLabel = $selectedOption['label'] ?? ($placeholder ?? 'Pilih opsi');
    $selectId = $id ?? $name;
    $background = $background ?? '#f4f9ff';
@endphp

<div class="admin-product-theme-select" data-admin-product-theme-select style="--product-select-bg: {{ $background }};">
    <input
        type="hidden"
        name="{{ $name }}"
        id="{{ $selectId }}"
        value="{{ $selectedValue }}"
        @if(!empty($required)) required @endif
        data-admin-product-theme-select-input
    >
    <button type="button" class="admin-product-theme-select-button" aria-haspopup="listbox" aria-expanded="false">
        <span class="admin-product-theme-select-label" data-admin-product-theme-select-label>{{ $selectedLabel }}</span>
        <span class="admin-product-theme-select-arrow" aria-hidden="true">
            <svg viewBox="0 0 20 20" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="3">
                <path d="M5 8l5 5 5-5" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </span>
    </button>
    <div class="admin-product-theme-select-menu" role="listbox">
        @foreach($options as $option)
            @php
                $value = (string) ($option['value'] ?? '');
                $label = (string) ($option['label'] ?? '');
                $disabled = (bool) ($option['disabled'] ?? false);
            @endphp
            <button
                type="button"
                class="admin-product-theme-select-option {{ $selectedValue === $value ? 'is-selected' : '' }}"
                data-admin-product-theme-select-option
                data-value="{{ $value }}"
                data-label="{{ $label }}"
                role="option"
                aria-selected="{{ $selectedValue === $value ? 'true' : 'false' }}"
                @if($disabled) disabled @endif
            >
                {{ $label }}
            </button>
        @endforeach
    </div>
</div>
