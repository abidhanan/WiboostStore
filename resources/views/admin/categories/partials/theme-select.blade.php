@once
    <style>
        .admin-category-theme-select {
            position: relative;
            width: 100%;
            z-index: 30;
        }

        .admin-category-theme-select.is-open {
            z-index: 120;
        }

        .admin-category-theme-select-button {
            display: flex;
            width: 100%;
            min-height: 4rem;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            border: 4px solid #ffffff;
            border-radius: 1.5rem;
            background: var(--category-select-bg, #f4f9ff);
            padding: 1rem 1.25rem 1rem 1.5rem;
            color: #2b3a67;
            font-weight: 900;
            text-align: left;
            box-shadow: inset 0 2px 10px rgba(90, 118, 200, 0.08);
            outline: none;
            transition: border-color 180ms ease, background-color 180ms ease, box-shadow 180ms ease, transform 180ms ease;
        }

        .admin-category-theme-select-button:hover,
        .admin-category-theme-select.is-open .admin-category-theme-select-button {
            border-color: #bde0fe;
            background: #ffffff;
            box-shadow: 0 14px 30px rgba(189, 224, 254, 0.28);
        }

        .admin-category-theme-select-button:focus-visible {
            border-color: #8faaf3;
            box-shadow: 0 0 0 4px rgba(143, 170, 243, 0.22);
        }

        .admin-category-theme-select-label {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .admin-category-theme-select-arrow {
            display: inline-flex;
            width: 1.25rem;
            height: 1.25rem;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            color: #5a76c8;
            transition: transform 180ms ease;
        }

        .admin-category-theme-select.is-open .admin-category-theme-select-arrow {
            transform: rotate(180deg);
        }

        .admin-category-theme-select-menu {
            position: absolute;
            left: 0;
            right: 0;
            top: calc(100% + 0.6rem);
            z-index: 130;
            display: grid;
            max-height: min(18rem, 56vh);
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

        .admin-category-theme-select.is-open .admin-category-theme-select-menu {
            opacity: 1;
            pointer-events: auto;
            transform: translateY(0) scale(1);
        }

        .admin-category-theme-select-menu.is-floating-open {
            display: grid !important;
            opacity: 1 !important;
            pointer-events: auto !important;
            transform: none !important;
        }

        .admin-category-theme-select-option {
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

        .admin-category-theme-select-option:hover {
            border-color: #bde0fe;
            background: #e0fbfc;
            color: #4760a9;
            transform: translateX(0.15rem);
        }

        .admin-category-theme-select-option.is-selected {
            border-color: #ffffff;
            background: #5a76c8;
            color: #ffffff;
            box-shadow: 0 7px 16px rgba(90, 118, 200, .2);
        }

        .admin-category-theme-select-option:disabled {
            cursor: not-allowed;
            opacity: 0.55;
            transform: none;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const closeCategorySelects = (except = null) => {
                document.querySelectorAll('[data-admin-category-theme-select].is-open').forEach((dropdown) => {
                    if (dropdown !== except) {
                        dropdown.classList.remove('is-open');
                        dropdown.querySelector('.admin-category-theme-select-button')?.setAttribute('aria-expanded', 'false');
                        window.wiboostResetFloatingMenu?.(dropdown, '.admin-category-theme-select-menu');
                    }
                });
            };

            document.querySelectorAll('[data-admin-category-theme-select]').forEach((dropdown) => {
                const button = dropdown.querySelector('.admin-category-theme-select-button');
                const label = dropdown.querySelector('[data-admin-category-theme-select-label]');
                const input = dropdown.querySelector('[data-admin-category-theme-select-input]');

                button?.addEventListener('click', function () {
                    const willOpen = !dropdown.classList.contains('is-open');
                    closeCategorySelects(dropdown);
                    dropdown.classList.toggle('is-open', willOpen);
                    button.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                    if (willOpen) {
                        window.requestAnimationFrame(() => window.wiboostPositionFloatingMenu?.(dropdown, '.admin-category-theme-select-menu', 220, 288));
                    } else {
                        window.wiboostResetFloatingMenu?.(dropdown, '.admin-category-theme-select-menu');
                    }
                });

                dropdown.querySelectorAll('[data-admin-category-theme-select-option]').forEach((option) => {
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

                        dropdown.querySelectorAll('[data-admin-category-theme-select-option]').forEach((item) => {
                            const isSelected = item === option;
                            item.classList.toggle('is-selected', isSelected);
                            item.setAttribute('aria-selected', isSelected ? 'true' : 'false');
                        });
                        dropdown.classList.remove('is-open');
                        button?.setAttribute('aria-expanded', 'false');
                        window.wiboostResetFloatingMenu?.(dropdown, '.admin-category-theme-select-menu');
                    });
                });
            });

            document.addEventListener('click', function (event) {
                if (!event.target.closest('[data-admin-category-theme-select]')) {
                    closeCategorySelects();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeCategorySelects();
                }
            });

            window.addEventListener('resize', () => {
                document.querySelectorAll('[data-admin-category-theme-select].is-open').forEach((dropdown) => window.wiboostPositionFloatingMenu?.(dropdown, '.admin-category-theme-select-menu', 220, 288));
            });
            window.addEventListener('scroll', () => {
                document.querySelectorAll('[data-admin-category-theme-select].is-open').forEach((dropdown) => window.wiboostPositionFloatingMenu?.(dropdown, '.admin-category-theme-select-menu', 220, 288));
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

<div class="admin-category-theme-select" data-admin-category-theme-select style="--category-select-bg: {{ $background }};">
    <input
        type="hidden"
        name="{{ $name }}"
        id="{{ $selectId }}"
        value="{{ $selectedValue }}"
        @if(!empty($required)) required @endif
        data-admin-category-theme-select-input
    >
    <button type="button" class="admin-category-theme-select-button" aria-haspopup="listbox" aria-expanded="false">
        <span class="admin-category-theme-select-label" data-admin-category-theme-select-label>{{ $selectedLabel }}</span>
        <span class="admin-category-theme-select-arrow" aria-hidden="true">
            <svg viewBox="0 0 20 20" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="3">
                <path d="M5 8l5 5 5-5" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </span>
    </button>
    <div class="admin-category-theme-select-menu" role="listbox">
        @foreach($options as $option)
            @php
                $value = (string) ($option['value'] ?? '');
                $label = (string) ($option['label'] ?? '');
                $disabled = (bool) ($option['disabled'] ?? false);
            @endphp
            <button
                type="button"
                class="admin-category-theme-select-option {{ $selectedValue === $value ? 'is-selected' : '' }}"
                data-admin-category-theme-select-option
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
