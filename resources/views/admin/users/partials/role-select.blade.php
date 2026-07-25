@once
    <style>
        .admin-user-role-select {
            position: relative;
            width: 100%;
            z-index: 40;
        }

        .admin-user-role-select.is-open {
            z-index: 260;
        }

        .admin-user-role-select-button {
            display: flex;
            width: 100%;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            border: 4px solid #ffffff;
            border-radius: 1.5rem;
            background: #f4f9ff;
            padding: 1rem 1.25rem 1rem 1.5rem;
            color: #2b3a67;
            font-weight: 900;
            text-align: left;
            box-shadow: inset 0 2px 10px rgba(90, 118, 200, 0.08);
            outline: none;
            transition: border-color 180ms ease, background-color 180ms ease, box-shadow 180ms ease, transform 180ms ease;
        }

        .admin-user-role-select-button:hover,
        .admin-user-role-select.is-open .admin-user-role-select-button {
            border-color: #bde0fe;
            background: #ffffff;
            box-shadow: 0 14px 30px rgba(189, 224, 254, 0.28);
        }

        .admin-user-role-select-button:focus-visible {
            border-color: #8faaf3;
            box-shadow: 0 0 0 4px rgba(143, 170, 243, 0.22);
        }

        .admin-user-role-select-arrow {
            display: inline-flex;
            width: 2.35rem;
            height: 2.35rem;
            flex: 0 0 2.35rem;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: #ffffff;
            color: #5a76c8;
            box-shadow: 0 8px 18px rgba(189, 224, 254, 0.38);
            transition: transform 180ms ease, background-color 180ms ease;
        }

        .admin-user-role-select-label {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .admin-user-role-select.is-open .admin-user-role-select-arrow {
            transform: rotate(180deg);
        }

        .admin-user-role-select-menu {
            position: absolute;
            left: 0;
            right: 0;
            top: calc(100% + 0.6rem);
            z-index: 280;
            display: grid;
            gap: 0.5rem;
            max-height: min(18rem, 56vh);
            overflow-y: auto;
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

        .admin-user-role-select.is-open .admin-user-role-select-menu {
            opacity: 1;
            pointer-events: auto;
            transform: translateY(0) scale(1);
        }

        .admin-user-role-select-menu.is-floating-open {
            display: grid !important;
            opacity: 1 !important;
            pointer-events: auto !important;
            transform: none !important;
        }

        .admin-user-role-select-option {
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

        .admin-user-role-select-option:hover,
        .admin-user-role-select-option.is-selected {
            border-color: #bde0fe;
            background: #e0fbfc;
            color: #4bc6b9;
            transform: translateX(0.15rem);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const closeRoleSelects = (except = null) => {
                document.querySelectorAll('[data-admin-user-role-select].is-open').forEach((dropdown) => {
                    if (dropdown !== except) {
                        dropdown.classList.remove('is-open');
                        dropdown.querySelector('.admin-user-role-select-button')?.setAttribute('aria-expanded', 'false');
                        window.wiboostResetFloatingMenu?.(dropdown, '.admin-user-role-select-menu');
                    }
                });
            };

            document.querySelectorAll('[data-admin-user-role-select]').forEach((dropdown) => {
                const button = dropdown.querySelector('.admin-user-role-select-button');
                const label = dropdown.querySelector('[data-admin-user-role-label]');
                const input = dropdown.querySelector('[data-admin-user-role-input]');

                button?.addEventListener('click', function () {
                    const willOpen = !dropdown.classList.contains('is-open');
                    closeRoleSelects(dropdown);
                    dropdown.classList.toggle('is-open', willOpen);
                    button.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                    if (willOpen) {
                        window.requestAnimationFrame(() => window.wiboostPositionFloatingMenu?.(dropdown, '.admin-user-role-select-menu', 220, 288));
                    } else {
                        window.wiboostResetFloatingMenu?.(dropdown, '.admin-user-role-select-menu');
                    }
                });

                dropdown.querySelectorAll('[data-admin-user-role-option]').forEach((option) => {
                    option.addEventListener('click', function () {
                        const value = option.dataset.value || '';
                        const text = option.dataset.label || option.textContent.trim();

                        if (input) {
                            input.value = value;
                        }

                        if (label) {
                            label.textContent = text;
                        }

                        dropdown.querySelectorAll('[data-admin-user-role-option]').forEach((item) => item.classList.remove('is-selected'));
                        option.classList.add('is-selected');
                        dropdown.classList.remove('is-open');
                        button?.setAttribute('aria-expanded', 'false');
                        window.wiboostResetFloatingMenu?.(dropdown, '.admin-user-role-select-menu');
                    });
                });
            });

            document.addEventListener('click', function (event) {
                if (!event.target.closest('[data-admin-user-role-select]')) {
                    closeRoleSelects();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeRoleSelects();
                }
            });

            window.addEventListener('resize', () => {
                document.querySelectorAll('[data-admin-user-role-select].is-open').forEach((dropdown) => window.wiboostPositionFloatingMenu?.(dropdown, '.admin-user-role-select-menu', 220, 288));
            });
            window.addEventListener('scroll', () => {
                document.querySelectorAll('[data-admin-user-role-select].is-open').forEach((dropdown) => window.wiboostPositionFloatingMenu?.(dropdown, '.admin-user-role-select-menu', 220, 288));
            }, true);
        });
    </script>
@endonce

@php
    $roleOptions = [
        '2' => 'Buyer',
        '3' => 'Reseller',
        'member' => 'Member',
        '1' => 'Super Admin',
        '4' => 'Keuangan',
        '6' => 'Gudang',
        '7' => 'Marketing',
        '8' => 'Customer Service',
    ];
    $selectedRole = (string) ($selectedRole ?? '2');
    if (! array_key_exists($selectedRole, $roleOptions)) {
        $selectedRole = '2';
    }
    $selectedRoleLabel = $roleOptions[$selectedRole] ?? $roleOptions['2'];
@endphp

<div class="admin-user-role-select" data-admin-user-role-select>
    <input type="hidden" name="role_id" value="{{ $selectedRole }}" required data-admin-user-role-input>
    <button type="button" class="admin-user-role-select-button" aria-haspopup="listbox" aria-expanded="false">
        <span class="admin-user-role-select-label" data-admin-user-role-label>{{ $selectedRoleLabel }}</span>
        <span class="admin-user-role-select-arrow" aria-hidden="true">
            <svg viewBox="0 0 20 20" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="3">
                <path d="M5 8l5 5 5-5" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </span>
    </button>
    <div class="admin-user-role-select-menu" role="listbox">
        @foreach($roleOptions as $value => $label)
            <button
                type="button"
                class="admin-user-role-select-option {{ $selectedRole === (string) $value ? 'is-selected' : '' }}"
                data-admin-user-role-option
                data-value="{{ $value }}"
                data-label="{{ $label }}"
                role="option"
                aria-selected="{{ $selectedRole === (string) $value ? 'true' : 'false' }}"
            >
                {{ $label }}
            </button>
        @endforeach
    </div>
</div>
