@extends('layouts.guest')

@section('title', $form->title.' - Wiboost Store')

@section('content')
<style>
    .wiboost-public-form-select {
        position: relative;
        z-index: 10;
    }

    .wiboost-public-form-select.is-open {
        z-index: 70;
    }

    .wiboost-public-form-select-button {
        display: flex;
        min-height: 3.85rem;
        width: 100%;
        align-items: center;
        justify-content: space-between;
        gap: .85rem;
        border-radius: 1.4rem;
        border: 4px solid #fff;
        background: #fff;
        padding: 1rem 1.15rem 1rem 1.25rem;
        text-align: left;
        font-weight: 900;
        color: #2b3a67;
        box-shadow: inset 0 2px 8px rgba(143, 170, 243, .16);
        transition: border-color .18s ease, background .18s ease, box-shadow .18s ease;
    }

    .wiboost-public-form-select-button:hover,
    .wiboost-public-form-select.is-open .wiboost-public-form-select-button {
        border-color: #bde0fe;
        background: #f8fbff;
        box-shadow: 0 12px 24px rgba(143, 170, 243, .14), inset 0 2px 8px rgba(143, 170, 243, .1);
    }

    .wiboost-public-form-select-button:focus-visible {
        outline: 4px solid #bde0fe;
        outline-offset: 2px;
    }

    .wiboost-public-form-select-arrow {
        display: inline-flex;
        height: 1.85rem;
        width: 1.85rem;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        border-radius: .9rem;
        background: #f4f9ff;
        color: #5a76c8;
        box-shadow: 0 6px 14px rgba(143, 170, 243, .18);
        transition: transform .22s ease;
    }

    .wiboost-public-form-select.is-open .wiboost-public-form-select-arrow {
        transform: rotate(180deg);
    }

    .wiboost-public-form-select-arrow svg {
        display: block;
        width: 1rem;
        height: 1rem;
    }

    .wiboost-public-form-select-menu {
        position: absolute;
        top: calc(100% + .45rem);
        left: 0;
        right: 0;
        z-index: 80;
        max-height: 17rem;
        overflow-y: auto;
        border-radius: 1.35rem;
        border: 4px solid #fff;
        background: rgba(255, 255, 255, .98);
        padding: .45rem;
        box-shadow: 0 18px 38px rgba(90, 118, 200, .22);
        opacity: 0;
        pointer-events: none;
        transform: translateY(-6px) scale(.98);
        transform-origin: top;
        transition: opacity .18s ease, transform .18s ease;
    }

    .wiboost-public-form-select.is-open .wiboost-public-form-select-menu {
        opacity: 1;
        pointer-events: auto;
        transform: translateY(0) scale(1);
    }

    .wiboost-public-form-select-option {
        display: flex;
        width: 100%;
        align-items: center;
        justify-content: space-between;
        border-radius: 1rem;
        padding: .85rem 1rem;
        text-align: left;
        font-size: .9rem;
        font-weight: 900;
        color: #5a76c8;
        transition: background-color .16s ease, color .16s ease, transform .16s ease;
    }

    .wiboost-public-form-select-option:hover,
    .wiboost-public-form-select-option.is-selected {
        background: #eef5ff;
        color: #2b3a67;
    }

    .wiboost-public-form-select-option:active {
        transform: scale(.98);
    }

    .wiboost-public-form-page {
        box-sizing: border-box;
        width: 100%;
        color: #2b3a67;
        font-family: 'Nunito', sans-serif;
    }

    .wiboost-public-form-heading {
        margin-bottom: 2rem;
        text-align: center;
    }

    .wiboost-public-form-badge {
        display: inline-flex;
        border: 2px solid #fff;
        border-radius: 9999px;
        background: #e0fbfc;
        padding: .5rem 1.25rem;
        color: #4bc6b9;
        font-size: .625rem;
        font-weight: 900;
        letter-spacing: .24em;
        text-transform: uppercase;
        box-shadow: 0 2px 8px rgba(75, 198, 185, .12);
    }

    .wiboost-public-form-title {
        margin: 1.25rem 0 0;
        color: #2b3a67;
        font-size: clamp(1.9rem, 5vw, 2.45rem);
        font-weight: 900;
        line-height: 1.12;
    }

    .wiboost-public-form-description {
        max-width: 42rem;
        margin: .75rem auto 0;
        color: #5a76c8;
        font-size: .95rem;
        font-weight: 800;
        line-height: 1.65;
    }

    .wiboost-public-errors {
        margin-bottom: 1.5rem;
        border: 4px solid #fff;
        border-radius: 2rem;
        background: #ffe5e5;
        padding: 1rem 1.25rem;
        color: #ff6b6b;
        font-weight: 900;
        box-shadow: 0 16px 32px rgba(255, 107, 107, .16);
    }

    .wiboost-public-form {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        margin: 0;
    }

    .wiboost-public-field {
        box-sizing: border-box;
        width: 100%;
        border: 4px solid #fff;
        border-radius: 1.7rem;
        background: #f8fbff;
        padding: 1rem;
        box-shadow: inset 0 2px 14px rgba(189, 224, 254, .32);
    }

    .wiboost-public-label {
        display: block;
        margin: 0 0 .25rem .5rem;
        color: #5a76c8;
        font-size: .9rem;
        font-weight: 900;
    }

    .wiboost-public-help {
        margin: .25rem 0 .75rem .5rem;
        color: #8faaf3;
        font-size: .78rem;
        font-weight: 800;
        line-height: 1.55;
    }

    .wiboost-public-input {
        box-sizing: border-box;
        width: 100%;
        border: 4px solid #fff;
        border-radius: 1.4rem;
        background: #fff;
        padding: 1rem 1.25rem;
        color: #2b3a67;
        font: inherit;
        font-weight: 900;
        box-shadow: inset 0 2px 8px rgba(143, 170, 243, .16);
        outline: none;
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    .wiboost-public-input::placeholder {
        color: #a3bbfb;
    }

    .wiboost-public-input:focus {
        border-color: #bde0fe;
        box-shadow: 0 0 0 4px rgba(189, 224, 254, .34), inset 0 2px 8px rgba(143, 170, 243, .12);
    }

    .wiboost-public-textarea {
        min-height: 9rem;
        resize: vertical;
    }

    .wiboost-public-checkbox-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: .5rem;
    }

    .wiboost-public-checkbox-option {
        display: flex;
        align-items: center;
        gap: .75rem;
        border: 2px solid #fff;
        border-radius: 1.2rem;
        background: #fff;
        padding: .75rem 1rem;
        box-shadow: 0 8px 18px rgba(143, 170, 243, .12);
        cursor: pointer;
    }

    .wiboost-public-checkbox {
        width: 1.25rem;
        height: 1.25rem;
        accent-color: #5a76c8;
        flex-shrink: 0;
    }

    .wiboost-public-checkbox-text {
        color: #2b3a67;
        font-size: .9rem;
        font-weight: 900;
    }

    .wiboost-public-file {
        cursor: pointer;
        color: #8faaf3;
        font-size: .9rem;
        font-weight: 800;
    }

    .wiboost-public-submit {
        display: flex;
        width: 100%;
        align-items: center;
        justify-content: center;
        border: 4px solid #fff;
        border-radius: 9999px;
        background: #5a76c8;
        padding: 1.15rem 1.5rem;
        color: #fff;
        font: inherit;
        font-size: 1.1rem;
        font-weight: 900;
        box-shadow: 0 18px 34px rgba(90, 118, 200, .28);
        cursor: pointer;
        transition: background-color .18s ease, transform .18s ease;
    }

    .wiboost-public-submit:hover {
        background: #4760a9;
    }

    .wiboost-public-submit:active {
        transform: scale(.98);
    }

    @media (min-width: 640px) {
        .wiboost-public-field {
            padding: 1.25rem;
        }

        .wiboost-public-checkbox-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
</style>

<div class="wiboost-font wiboost-public-form-page">
    <div class="wiboost-public-form-heading mb-8 text-center">
        <span class="wiboost-public-form-badge inline-flex rounded-full border-2 border-white bg-[#e0fbfc] px-5 py-2 text-[10px] font-black uppercase tracking-[0.24em] text-[#4bc6b9] shadow-sm">
            Formulir Wiboost
        </span>
        <h1 class="wiboost-public-form-title mt-5 text-3xl font-black leading-tight text-[#2b3a67] sm:text-4xl">{{ $form->title }}</h1>
        @if($form->description)
            <p class="wiboost-public-form-description mx-auto mt-3 max-w-2xl text-sm font-bold leading-relaxed text-[#5a76c8] sm:text-base">{{ $form->description }}</p>
        @endif
    </div>

    @if ($errors->any())
        <div class="wiboost-public-errors mb-6 rounded-[2rem] border-4 border-white bg-[#ffe5e5] px-5 py-4 font-black text-[#ff6b6b] shadow-lg shadow-[#ff6b6b]/20">
            <p class="mb-2 text-sm">Periksa jawaban berikut:</p>
            <ul class="list-inside list-disc rounded-2xl bg-white/55 p-3 text-xs font-bold">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('public.forms.store', ['form' => $form->slug]) }}" method="POST" enctype="multipart/form-data" class="wiboost-public-form space-y-5">
        @csrf

        @foreach(($form->fields ?? []) as $field)
            @php
                $key = (string) ($field['key'] ?? '');
                $type = (string) ($field['type'] ?? 'text');
                $label = (string) ($field['label'] ?? $key);
                $help = (string) ($field['help'] ?? '');
                $required = ! empty($field['required']);
                $options = (array) ($field['options'] ?? []);
                $oldValue = old("answers.$key");
            @endphp

            @continue($key === '')

            <div class="wiboost-public-field rounded-[1.7rem] border-4 border-white bg-[#f8fbff] p-4 shadow-inner sm:p-5">
                <label class="wiboost-public-label mb-1 ml-2 block text-sm font-black text-[#5a76c8]">
                    {{ $label }} @if($required)<span class="text-[#ff6b6b]">*</span>@endif
                </label>
                @if($help)
                    <p class="wiboost-public-help mb-3 ml-2 text-xs font-bold leading-relaxed text-[#8faaf3]">{{ $help }}</p>
                @endif

                @if($type === 'textarea')
                    <textarea name="answers[{{ $key }}]" rows="4" @required($required) class="wiboost-public-input wiboost-public-textarea w-full rounded-[1.4rem] border-4 border-white bg-white px-5 py-4 font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe]" placeholder="Tulis jawabanmu di sini...">{{ old("answers.$key") }}</textarea>
                @elseif($type === 'select')
                    <div class="wiboost-public-form-select" data-public-form-select>
                        <input type="hidden" name="answers[{{ $key }}]" value="{{ $oldValue }}" data-public-form-select-input>
                        <button type="button" class="wiboost-public-form-select-button" aria-haspopup="listbox" aria-expanded="false" data-public-form-select-button>
                            <span class="truncate" data-public-form-select-label>{{ $oldValue ?: 'Pilih jawaban' }}</span>
                            <span class="wiboost-public-form-select-arrow" aria-hidden="true">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none">
                                    <path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                        </button>
                        <div class="wiboost-public-form-select-menu" role="listbox" data-public-form-select-menu>
                            <button type="button" class="wiboost-public-form-select-option {{ $oldValue ? '' : 'is-selected' }}" role="option" aria-selected="{{ $oldValue ? 'false' : 'true' }}" data-public-form-select-option data-value="" data-label="Pilih jawaban">Pilih jawaban</button>
                        @foreach($options as $option)
                            <button type="button" class="wiboost-public-form-select-option {{ $oldValue === $option ? 'is-selected' : '' }}" role="option" aria-selected="{{ $oldValue === $option ? 'true' : 'false' }}" data-public-form-select-option data-value="{{ $option }}" data-label="{{ $option }}">{{ $option }}</button>
                        @endforeach
                        </div>
                    </div>
                @elseif($type === 'checkbox')
                    <div class="wiboost-public-checkbox-grid grid grid-cols-1 gap-2 sm:grid-cols-2">
                        @foreach($options as $option)
                            <label class="wiboost-public-checkbox-option flex cursor-pointer items-center gap-3 rounded-[1.2rem] border-2 border-white bg-white px-4 py-3 shadow-sm">
                                <input type="checkbox" name="answers[{{ $key }}][]" value="{{ $option }}" class="wiboost-public-checkbox h-5 w-5 rounded-md border-2 border-[#5a76c8] bg-white text-[#5a76c8] shadow-sm focus:ring-2 focus:ring-[#bde0fe]" style="accent-color: #5a76c8;" @checked(in_array($option, (array) $oldValue, true))>
                                <span class="wiboost-public-checkbox-text text-sm font-black text-[#2b3a67]">{{ $option }}</span>
                            </label>
                        @endforeach
                    </div>
                @elseif($type === 'file')
                    <input type="file" name="answers[{{ $key }}]" @required($required) class="wiboost-public-input wiboost-public-file w-full cursor-pointer rounded-[1.4rem] border-4 border-white bg-white p-2 text-sm font-bold text-[#8faaf3] shadow-inner file:mr-4 file:rounded-full file:border-0 file:bg-[#e0fbfc] file:px-5 file:py-3 file:text-[#4bc6b9] file:shadow-sm">
                @else
                    <input type="{{ in_array($type, ['email', 'number', 'date'], true) ? $type : 'text' }}" name="answers[{{ $key }}]" value="{{ old("answers.$key") }}" @required($required) class="wiboost-public-input w-full rounded-[1.4rem] border-4 border-white bg-white px-5 py-4 font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe]" placeholder="Isi jawabanmu">
                @endif
            </div>
        @endforeach

        <button type="submit" class="wiboost-public-submit flex w-full items-center justify-center rounded-full border-4 border-white bg-[#5a76c8] py-5 text-lg font-black text-white shadow-xl shadow-[#5a76c8]/30 transition-transform hover:bg-[#4760a9] active:scale-95">
            Kirim Formulir
        </button>
    </form>
</div>

<script>
    (() => {
        const closeSelect = (dropdown) => {
            dropdown?.classList.remove('is-open');
            dropdown?.querySelector('[data-public-form-select-button]')?.setAttribute('aria-expanded', 'false');
        };

        const closeOtherSelects = (currentDropdown = null) => {
            document.querySelectorAll('[data-public-form-select].is-open').forEach((dropdown) => {
                if (dropdown !== currentDropdown) {
                    closeSelect(dropdown);
                }
            });
        };

        document.querySelectorAll('[data-public-form-select]').forEach((dropdown) => {
            const button = dropdown.querySelector('[data-public-form-select-button]');
            const input = dropdown.querySelector('[data-public-form-select-input]');
            const label = dropdown.querySelector('[data-public-form-select-label]');

            button?.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                const willOpen = !dropdown.classList.contains('is-open');
                closeOtherSelects(dropdown);
                dropdown.classList.toggle('is-open', willOpen);
                button.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            });

            dropdown.querySelectorAll('[data-public-form-select-option]').forEach((option) => {
                option.addEventListener('click', (event) => {
                    event.preventDefault();
                    const value = option.dataset.value || '';
                    const optionLabel = option.dataset.label || option.textContent.trim();

                    if (input) input.value = value;
                    if (label) label.textContent = optionLabel;

                    dropdown.querySelectorAll('[data-public-form-select-option]').forEach((item) => {
                        const selected = item === option;
                        item.classList.toggle('is-selected', selected);
                        item.setAttribute('aria-selected', selected ? 'true' : 'false');
                    });

                    closeSelect(dropdown);
                });
            });
        });

        document.addEventListener('click', (event) => {
            if (!event.target.closest('[data-public-form-select]')) {
                closeOtherSelects();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeOtherSelects();
            }
        });
    })();
</script>
@endsection
