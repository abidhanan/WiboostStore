@once
    <style>
        .admin-promo-theme-select {
            position: relative;
            width: 100%;
            z-index: 40;
        }

        .admin-promo-theme-select.is-open {
            z-index: 180;
        }

        .admin-promo-theme-select-button {
            width: 100%;
            min-height: 4rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 1rem 1.25rem;
            border: 4px solid #ffffff;
            border-radius: 1.5rem;
            background: #f4f9ff;
            color: #2b3a67;
            font-weight: 900;
            text-align: left;
            box-shadow: inset 0 2px 8px rgba(43, 58, 103, 0.06);
            transition: border-color 180ms ease, background-color 180ms ease, box-shadow 180ms ease, transform 180ms ease;
        }

        .admin-promo-theme-select-button:hover,
        .admin-promo-theme-select.is-open .admin-promo-theme-select-button {
            border-color: #bde0fe;
            background: #ffffff;
            box-shadow: 0 14px 32px rgba(189, 224, 254, 0.38);
        }

        .admin-promo-theme-select-button:focus-visible {
            outline: 3px solid rgba(90, 118, 200, 0.28);
            outline-offset: 3px;
        }

        .admin-promo-theme-select-label {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .admin-promo-theme-select-arrow {
            width: 2.35rem;
            height: 2.35rem;
            flex: 0 0 2.35rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: #ffffff;
            color: #5a76c8;
            box-shadow: 0 8px 18px rgba(189, 224, 254, 0.38);
            transition: transform 180ms ease, background-color 180ms ease;
        }

        .admin-promo-theme-select.is-open .admin-promo-theme-select-arrow {
            transform: rotate(180deg);
        }

        .admin-promo-theme-select-menu {
            position: absolute;
            left: 0;
            right: 0;
            top: calc(100% + 0.6rem);
            max-height: min(18rem, 56vh);
            overflow-y: auto;
            padding: 0.55rem;
            border: 4px solid #ffffff;
            border-radius: 1.35rem;
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 22px 46px rgba(90, 118, 200, 0.22);
            opacity: 0;
            pointer-events: none;
            transform: translateY(-0.35rem) scale(0.98);
            transform-origin: top center;
            transition: opacity 160ms ease, transform 160ms ease;
            z-index: 190;
        }

        .admin-promo-theme-select.is-open .admin-promo-theme-select-menu {
            opacity: 1;
            pointer-events: auto;
            transform: translateY(0) scale(1);
        }

        .admin-promo-theme-select-menu.is-floating-open {
            display: block !important;
            opacity: 1 !important;
            pointer-events: auto !important;
            transform: none !important;
        }

        .admin-promo-theme-select-option {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.95rem 1rem;
            border: 2px solid transparent;
            border-radius: 1rem;
            background: #f4f9ff;
            color: #2b3a67;
            font-weight: 900;
            line-height: 1.25;
            text-align: left;
            transition: background-color 160ms ease, border-color 160ms ease, color 160ms ease, transform 160ms ease;
        }

        .admin-promo-theme-select-option + .admin-promo-theme-select-option {
            margin-top: 0.45rem;
        }

        .admin-promo-theme-select-option:hover {
            border-color: #bde0fe;
            background: #e0fbfc;
            color: #4760a9;
            transform: translateX(2px);
        }

        .admin-promo-theme-select-option.is-selected {
            border-color: #ffffff;
            background: #5a76c8;
            color: #ffffff;
            box-shadow: 0 7px 16px rgba(90, 118, 200, .2);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const closePromoSelects = (except = null) => {
                document.querySelectorAll('[data-admin-promo-theme-select].is-open').forEach((dropdown) => {
                    if (dropdown !== except) {
                        dropdown.classList.remove('is-open');
                        dropdown.querySelector('.admin-promo-theme-select-button')?.setAttribute('aria-expanded', 'false');
                        window.wiboostResetFloatingMenu?.(dropdown, '.admin-promo-theme-select-menu');
                    }
                });
            };

            document.querySelectorAll('[data-admin-promo-theme-select]').forEach((dropdown) => {
                const button = dropdown.querySelector('.admin-promo-theme-select-button');
                const label = dropdown.querySelector('[data-admin-promo-theme-select-label]');
                const input = dropdown.querySelector('[data-admin-promo-theme-select-input]');

                if (!button || !label || !input) return;

                button.addEventListener('click', () => {
                    const willOpen = !dropdown.classList.contains('is-open');
                    closePromoSelects(dropdown);
                    dropdown.classList.toggle('is-open', willOpen);
                    button.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                    if (willOpen) {
                        window.requestAnimationFrame(() => window.wiboostPositionFloatingMenu?.(dropdown, '.admin-promo-theme-select-menu', 220, 288));
                    } else {
                        window.wiboostResetFloatingMenu?.(dropdown, '.admin-promo-theme-select-menu');
                    }
                });

                dropdown.querySelectorAll('[data-admin-promo-theme-select-option]').forEach((option) => {
                    option.addEventListener('click', () => {
                        input.value = option.dataset.value ?? '';
                        label.textContent = option.textContent.trim();
                        dropdown.querySelectorAll('[data-admin-promo-theme-select-option]').forEach((item) => {
                            const isSelected = item === option;
                            item.classList.toggle('is-selected', isSelected);
                            item.setAttribute('aria-selected', isSelected ? 'true' : 'false');
                        });
                        dropdown.classList.remove('is-open');
                        button.setAttribute('aria-expanded', 'false');
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                        window.wiboostResetFloatingMenu?.(dropdown, '.admin-promo-theme-select-menu');
                    });
                });
            });

            document.addEventListener('click', (event) => {
                if (!event.target.closest('[data-admin-promo-theme-select]')) {
                    closePromoSelects();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closePromoSelects();
                }
            });

            window.addEventListener('resize', () => {
                document.querySelectorAll('[data-admin-promo-theme-select].is-open').forEach((dropdown) => window.wiboostPositionFloatingMenu?.(dropdown, '.admin-promo-theme-select-menu', 220, 288));
            });
            window.addEventListener('scroll', () => {
                document.querySelectorAll('[data-admin-promo-theme-select].is-open').forEach((dropdown) => window.wiboostPositionFloatingMenu?.(dropdown, '.admin-promo-theme-select-menu', 220, 288));
            }, true);
        });
    </script>
@endonce

@php
    $selectedValue = (string) ($selectedValue ?? '');
    $placeholder = $placeholder ?? 'Pilih opsi';
    $selectedLabel = $placeholder;

    foreach ($options as $option) {
        if ((string) $option['value'] === $selectedValue) {
            $selectedLabel = $option['label'];
            break;
        }
    }
@endphp

<div class="admin-promo-theme-select" data-admin-promo-theme-select>
    <input
        type="hidden"
        name="{{ $name }}"
        id="{{ $id ?? $name }}"
        value="{{ $selectedValue }}"
        data-admin-promo-theme-select-input
    >
    <button type="button" class="admin-promo-theme-select-button" aria-haspopup="listbox" aria-expanded="false">
        <span class="admin-promo-theme-select-label" data-admin-promo-theme-select-label>{{ $selectedLabel }}</span>
        <span class="admin-promo-theme-select-arrow" aria-hidden="true">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"></path>
            </svg>
        </span>
    </button>
    <div class="admin-promo-theme-select-menu" role="listbox">
        @foreach($options as $option)
            @php $value = (string) $option['value']; @endphp
            <button
                type="button"
                class="admin-promo-theme-select-option {{ $selectedValue === $value ? 'is-selected' : '' }}"
                data-value="{{ $value }}"
                data-admin-promo-theme-select-option
                role="option"
                aria-selected="{{ $selectedValue === $value ? 'true' : 'false' }}"
            >
                {{ $option['label'] }}
            </button>
        @endforeach
    </div>
</div>
