@php
    $form = $form ?? null;
    $method = strtoupper($method ?? 'POST');
    $submitLabel = $submitLabel ?? 'Simpan Formulir';
    $formEditorId = 'wiboost-marketing-form-editor-' . ($form?->id ?? 'create');
    $defaultFields = [
        ['key' => 'nama_lengkap', 'label' => 'Nama Lengkap', 'type' => 'text', 'help' => '', 'options' => [], 'option_images' => [], 'required' => true],
    ];
    $fieldTypes = [
        'text' => 'Teks pendek',
        'textarea' => 'Paragraf',
        'email' => 'Email',
        'number' => 'Nomor',
        'date' => 'Tanggal',
        'select' => 'Pilihan',
        'checkbox' => 'Centang',
        'file' => 'Unggah File',
    ];
    $rawFields = old('fields', $form?->fields ?? $defaultFields);
    $fields = collect($rawFields)->map(function ($field) {
        $options = is_array($field['options'] ?? null) ? $field['options'] : [];
        $optionImages = is_array($field['option_images'] ?? null) ? $field['option_images'] : [];
        $optionItems = is_array($field['option_items'] ?? null)
            ? $field['option_items']
            : collect($options)->map(function ($option) use ($optionImages) {
                $label = trim((string) (is_array($option) ? ($option['label'] ?? '') : $option));

                return [
                    'label' => $label,
                    'existing_image' => $optionImages[$label] ?? (is_array($option) ? ($option['image'] ?? '') : ''),
                    'remove_image' => false,
                ];
            })->filter(fn ($option) => $option['label'] !== '')->values()->all();
        $optionItems = array_values($optionItems);

        return [
            'key' => $field['key'] ?? '',
            'label' => $field['label'] ?? '',
            'type' => $field['type'] ?? 'text',
            'help' => $field['help'] ?? '',
            'option_items' => $optionItems,
            'required' => ! empty($field['required']),
        ];
    })->values();

    $oldBenefitCredentials = old('benefit_credentials');
    $rawBenefitCredentials = is_array($oldBenefitCredentials)
        ? $oldBenefitCredentials
        : ($form?->benefit_credentials ?? []);

    if (! is_array($rawBenefitCredentials)) {
        $rawBenefitCredentials = [];
    }

    if ($oldBenefitCredentials === null && empty($rawBenefitCredentials['link']) && $form?->benefit_url) {
        $rawBenefitCredentials['link'] = $form->benefit_url;
    }

    $benefitCredentials = [];
    foreach (['email', 'password', 'profile', 'pin', 'link', 'tutorial_link', 'notes'] as $credentialKey) {
        $benefitCredentials[$credentialKey] = $rawBenefitCredentials[$credentialKey] ?? '';
    }
@endphp

<style>
    .wiboost-form-toggle-track {
        position: relative;
        display: flex;
        width: 3.5rem;
        height: 2rem;
        flex-shrink: 0;
        align-items: center;
        border-radius: 9999px;
        border: 4px solid #fff;
        background: #dbe8ff;
        padding: .25rem;
        box-shadow: inset 0 2px 7px rgba(189, 224, 254, .55);
        transition: background-color .2s ease, box-shadow .2s ease;
    }

    .wiboost-form-toggle-knob {
        width: 1rem;
        height: 1rem;
        border-radius: 9999px;
        background: #fff;
        box-shadow: 0 6px 14px rgba(43, 58, 103, .18);
        transform: translateX(0);
        transition: transform .2s ease;
    }

    .wiboost-form-toggle-input:checked + .wiboost-form-toggle-track {
        background: #5a76c8;
        box-shadow: inset 0 2px 7px rgba(90, 118, 200, .35);
    }

    .wiboost-form-toggle-input:checked + .wiboost-form-toggle-track .wiboost-form-toggle-knob {
        transform: translateX(1.5rem);
    }

    .wiboost-form-toggle-input:focus-visible + .wiboost-form-toggle-track {
        outline: 4px solid #bde0fe;
        outline-offset: 2px;
    }

    .wiboost-answer-type-select {
        position: relative;
        z-index: 10;
    }

    .wiboost-answer-type-select.is-open {
        z-index: 60;
    }

    .wiboost-answer-type-button {
        display: flex;
        min-height: 3.55rem;
        width: 100%;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        border-radius: 1.3rem;
        border: 4px solid #fff;
        background: #f4f9ff;
        padding: .75rem 1rem .75rem 1.25rem;
        text-align: left;
        font-size: .875rem;
        font-weight: 900;
        color: #2b3a67;
        box-shadow: inset 0 2px 8px rgba(189, 224, 254, .42);
        transition: border-color .2s ease, background-color .2s ease, box-shadow .2s ease, transform .2s ease;
    }

    .wiboost-answer-type-button:hover,
    .wiboost-answer-type-select.is-open .wiboost-answer-type-button {
        border-color: #bde0fe;
        background: #fff;
        box-shadow: 0 12px 24px rgba(143, 170, 243, .16), inset 0 2px 8px rgba(189, 224, 254, .28);
    }

    .wiboost-answer-type-button:focus-visible {
        outline: 4px solid #bde0fe;
        outline-offset: 2px;
    }

    .wiboost-answer-type-arrow {
        display: inline-flex;
        height: 1.75rem;
        width: 1.75rem;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        border-radius: .85rem;
        background: #fff;
        color: #5a76c8;
        box-shadow: 0 6px 14px rgba(143, 170, 243, .2);
        transition: transform .22s ease;
    }

    .wiboost-answer-type-select.is-open .wiboost-answer-type-arrow {
        transform: rotate(180deg);
    }

    .wiboost-answer-type-menu {
        position: absolute;
        top: calc(100% + .45rem);
        left: 0;
        right: 0;
        z-index: 70;
        max-height: 17rem;
        overflow-y: auto;
        border-radius: 1.3rem;
        border: 4px solid #fff;
        background: rgba(255, 255, 255, .98);
        padding: .45rem;
        box-shadow: 0 18px 36px rgba(143, 170, 243, .26);
        opacity: 0;
        pointer-events: none;
        transform: translateY(-6px) scale(.98);
        transform-origin: top;
        transition: opacity .18s ease, transform .18s ease;
    }

    .wiboost-answer-type-select.is-open .wiboost-answer-type-menu {
        opacity: 1;
        pointer-events: auto;
        transform: translateY(0) scale(1);
    }

    .wiboost-answer-type-option {
        display: flex;
        width: 100%;
        align-items: center;
        justify-content: space-between;
        border-radius: 1rem;
        padding: .8rem .95rem;
        text-align: left;
        font-size: .82rem;
        font-weight: 900;
        color: #5a76c8;
        transition: background-color .16s ease, color .16s ease, transform .16s ease;
    }

    .wiboost-answer-type-option:hover,
    .wiboost-answer-type-option.is-selected {
        background: #eef5ff;
        color: #2b3a67;
    }

    .wiboost-answer-type-option:active {
        transform: scale(.98);
    }

    .wiboost-answer-options-wrap {
        display: none;
    }

    .wiboost-answer-options-wrap.is-visible {
        display: block;
    }

    .wiboost-option-image-preview {
        width: 4.25rem;
        height: 4.25rem;
        flex: 0 0 4.25rem;
        overflow: hidden;
        border: 3px solid #fff;
        border-radius: 1rem;
        background: #f4f9ff;
        box-shadow: 0 6px 16px rgba(143, 170, 243, .16);
    }

    .wiboost-option-image-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>

@if ($errors->any())
    <div class="mb-8 rounded-[2.5rem] border-4 border-white bg-[#ffe5e5] px-6 py-5 font-black text-[#ff6b6b] shadow-lg shadow-[#ff6b6b]/20">
        <p class="mb-3 text-lg">Periksa data berikut:</p>
        <ul class="list-inside list-disc rounded-2xl border border-white bg-white/50 p-4 text-sm font-bold">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<label class="mb-6 flex cursor-pointer items-center justify-between gap-4 rounded-[1.6rem] border-4 border-white bg-white/95 px-5 py-4 shadow-lg shadow-[#bde0fe]/25">
    <span class="text-sm font-black text-[#2b3a67]">Formulir aktif</span>
    <input type="hidden" name="is_active" value="0" form="{{ $formEditorId }}">
    <input type="checkbox" name="is_active" value="1" class="wiboost-form-toggle-input sr-only" form="{{ $formEditorId }}" @checked(old('is_active', $form?->is_active ?? true))>
    <span class="wiboost-form-toggle-track" aria-hidden="true">
        <span class="wiboost-form-toggle-knob"></span>
    </span>
</label>

<form id="{{ $formEditorId }}" action="{{ $action }}" method="POST" enctype="multipart/form-data" class="relative z-10 rounded-[2.5rem] border-4 border-white bg-white/95 p-6 shadow-xl shadow-[#bde0fe]/30 backdrop-blur-sm md:p-10">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <section class="mb-8 rounded-[2rem] border-4 border-[#f0f5ff] bg-[#f8faff] p-5 shadow-sm sm:p-6">
        <div class="mb-6 flex items-center gap-3">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1.3rem] border-2 border-white bg-[#e0fbfc] text-[#4bc6b9] shadow-sm">
                @include('partials.wiboost-icon', ['name' => 'note', 'class' => 'h-6 w-6'])
            </span>
            <div class="min-w-0">
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-[#8faaf3]">Data Formulir</p>
                <h2 class="text-2xl font-black text-[#2b3a67]">Identitas Form</h2>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
                <label class="mb-3 ml-2 block text-sm font-black text-[#5a76c8]">Judul Formulir</label>
                <input type="text" name="title" value="{{ old('title', $form?->title) }}" required class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-6 py-4 font-black text-[#2b3a67] shadow-inner outline-none transition focus:border-[#bde0fe]">
            </div>
            <div>
                <label class="mb-3 ml-2 block text-sm font-black text-[#5a76c8]">Slug Tautan (Opsional)</label>
                <input type="text" name="slug" value="{{ old('slug', $form?->slug) }}" class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-6 py-4 font-black text-[#2b3a67] shadow-inner outline-none transition focus:border-[#bde0fe]">
            </div>
        </div>

        <div class="mt-6">
            <label class="mb-3 ml-2 block text-sm font-black text-[#5a76c8]">Deskripsi Formulir</label>
            <textarea name="description" rows="3" class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-6 py-4 font-black text-[#2b3a67] shadow-inner outline-none transition focus:border-[#bde0fe]">{{ old('description', $form?->description) }}</textarea>
        </div>
    </section>

    <section class="mb-8 rounded-[2rem] border-4 border-[#f0f5ff] bg-[#f8faff] p-5 shadow-sm sm:p-6" data-form-builder data-next-index="{{ $fields->count() }}">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex min-w-0 items-center gap-3">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1.3rem] border-2 border-white bg-[#f4f9ff] text-[#5a76c8] shadow-sm">
                    @include('partials.wiboost-icon', ['name' => 'receipt', 'class' => 'h-6 w-6'])
                </span>
                <div class="min-w-0">
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-[#8faaf3]">Pertanyaan</p>
                    <h2 class="text-2xl font-black text-[#2b3a67]">Isi Formulir</h2>
                </div>
            </div>
            <button type="button" data-add-field class="inline-flex shrink-0 items-center justify-center gap-2 rounded-[1.2rem] border-4 border-white bg-[#5a76c8] px-5 py-3 text-sm font-black text-white shadow-md shadow-[#5a76c8]/25 transition-transform active:scale-95">
                @include('partials.wiboost-icon', ['name' => 'plus', 'class' => 'h-5 w-5'])
                Tambah Pertanyaan
            </button>
        </div>

        <div class="space-y-5" data-fields-list>
            @foreach($fields as $index => $field)
                <div class="rounded-[1.8rem] border-4 border-white bg-white p-4 shadow-md shadow-[#bde0fe]/15 sm:p-5" data-field-row data-field-index="{{ $index }}">
                    <input type="hidden" name="fields[{{ $index }}][key]" value="{{ $field['key'] }}">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <p class="rounded-full bg-[#f4f9ff] px-4 py-2 text-xs font-black text-[#5a76c8]" data-field-title>Pertanyaan #{{ $index + 1 }}</p>
                        <button type="button" data-remove-field class="flex h-10 w-10 shrink-0 items-center justify-center rounded-[1rem] border-2 border-white bg-[#ffe5e5] text-[#ff6b6b] shadow-sm transition-transform active:scale-95" title="Hapus Pertanyaan">
                            @include('partials.wiboost-icon', ['name' => 'trash', 'class' => 'h-5 w-5'])
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-[minmax(0,1.4fr)_14rem]">
                        <div>
                            <label class="mb-2 ml-2 block text-xs font-black text-[#5a76c8]">Label Pertanyaan</label>
                            <input type="text" name="fields[{{ $index }}][label]" value="{{ $field['label'] }}" required class="w-full rounded-[1.3rem] border-4 border-white bg-[#f4f9ff] px-5 py-3 text-sm font-black text-[#2b3a67] shadow-inner outline-none transition focus:border-[#bde0fe]">
                        </div>
                        <div>
                            <label class="mb-2 ml-2 block text-xs font-black text-[#5a76c8]">Tipe Jawaban</label>
                            <div class="wiboost-answer-type-select" data-answer-type-select>
                                <input type="hidden" name="fields[{{ $index }}][type]" value="{{ $field['type'] }}" data-answer-type-input>
                                <button type="button" class="wiboost-answer-type-button" aria-haspopup="listbox" aria-expanded="false" data-answer-type-button>
                                    <span class="truncate" data-answer-type-label>{{ $fieldTypes[$field['type']] ?? 'Teks pendek' }}</span>
                                    <span class="wiboost-answer-type-arrow" aria-hidden="true">
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none">
                                            <path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                </button>
                                <div class="wiboost-answer-type-menu" role="listbox" data-answer-type-menu>
                                    @foreach($fieldTypes as $typeValue => $typeLabel)
                                        <button type="button" class="wiboost-answer-type-option {{ $field['type'] === $typeValue ? 'is-selected' : '' }}" role="option" aria-selected="{{ $field['type'] === $typeValue ? 'true' : 'false' }}" data-answer-type-option data-value="{{ $typeValue }}" data-label="{{ $typeLabel }}">{{ $typeLabel }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="mb-2 ml-2 block text-xs font-black text-[#5a76c8]">Deskripsi (Opsional)</label>
                        <input type="text" name="fields[{{ $index }}][help]" value="{{ $field['help'] }}" class="w-full rounded-[1.3rem] border-4 border-white bg-[#f4f9ff] px-5 py-3 text-sm font-black text-[#2b3a67] shadow-inner outline-none transition focus:border-[#bde0fe]">
                    </div>

                    <div class="wiboost-answer-options-wrap mt-4 {{ in_array($field['type'], ['select', 'checkbox'], true) ? 'is-visible' : '' }}" data-answer-options-wrap>
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <label class="ml-2 block text-xs font-black text-[#5a76c8]">Opsi Jawaban</label>
                            <button type="button" data-add-option class="inline-flex items-center gap-2 rounded-xl border-2 border-white bg-[#e0fbfc] px-3 py-2 text-xs font-black text-[#4bc6b9] shadow-sm">
                                @include('partials.wiboost-icon', ['name' => 'plus', 'class' => 'h-4 w-4'])
                                Tambah Opsi
                            </button>
                        </div>
                        <div class="space-y-3" data-option-list>
                            @foreach(($field['option_items'] ?? []) as $optionIndex => $option)
                                @php($existingOptionImage = trim((string) ($option['existing_image'] ?? '')))
                                <div class="flex flex-col gap-3 rounded-[1.25rem] border-2 border-white bg-[#f8fbff] p-3 sm:flex-row sm:items-center" data-option-row>
                                    @if($existingOptionImage)
                                        <div class="wiboost-option-image-preview" data-option-preview>
                                            <img src="{{ Storage::disk('public')->url($existingOptionImage) }}" alt="">
                                        </div>
                                    @else
                                        <div class="wiboost-option-image-preview hidden" data-option-preview><img src="" alt=""></div>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <input type="text" name="fields[{{ $index }}][option_items][{{ $optionIndex }}][label]" value="{{ $option['label'] ?? '' }}" class="w-full rounded-[1.1rem] border-4 border-white bg-white px-4 py-3 text-sm font-black text-[#2b3a67] shadow-inner outline-none focus:border-[#bde0fe]">
                                        <input type="hidden" name="fields[{{ $index }}][option_items][{{ $optionIndex }}][existing_image]" value="{{ $existingOptionImage }}">
                                        <input type="hidden" name="fields[{{ $index }}][option_items][{{ $optionIndex }}][remove_image]" value="0" data-remove-image-input>
                                        <label class="relative mt-2 flex min-h-[3.25rem] w-full cursor-pointer items-center gap-3 overflow-hidden rounded-[1.1rem] border-4 border-white bg-white px-3 py-2 shadow-inner">
                                            <input type="file" name="fields[{{ $index }}][option_items][{{ $optionIndex }}][image]" accept="image/*" class="absolute inset-0 h-full w-full cursor-pointer opacity-0" data-option-image>
                                            <span class="shrink-0 rounded-full bg-[#e0fbfc] px-3 py-2 text-xs font-black text-[#4bc6b9]">Pilih File</span>
                                            <span class="min-w-0 flex-1 truncate text-xs font-bold text-[#8faaf3]" data-option-image-name>{{ $existingOptionImage ? 'Foto tersimpan' : 'Belum ada foto' }}</span>
                                        </label>
                                    </div>
                                    <div class="flex shrink-0 justify-end gap-2">
                                        <button type="button" data-remove-option-image class="{{ $existingOptionImage ? '' : 'hidden' }} h-10 rounded-xl border-2 border-white bg-[#ffe5e5] px-3 text-xs font-black text-[#ff6b6b]" title="Hapus Foto">Hapus Foto</button>
                                        <button type="button" data-remove-option class="flex h-10 w-10 items-center justify-center rounded-xl border-2 border-white bg-[#ffe5e5] text-[#ff6b6b]" title="Hapus Opsi">
                                            @include('partials.wiboost-icon', ['name' => 'trash', 'class' => 'h-4 w-4'])
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <label class="mt-4 ml-2 flex cursor-pointer items-center gap-3">
                        <input type="checkbox" name="fields[{{ $index }}][required]" value="1" class="h-5 w-5 rounded-md border-2 border-[#5a76c8] bg-white text-[#5a76c8] shadow-sm focus:ring-2 focus:ring-[#bde0fe]" style="accent-color: #5a76c8;" @checked($field['required'])>
                        <span class="text-sm font-black text-[#2b3a67]">Wajib diisi</span>
                    </label>
                </div>
            @endforeach
        </div>
    </section>

    <section class="mb-8 rounded-[2rem] border-4 border-[#f0f5ff] bg-[#f8faff] p-5 shadow-sm sm:p-6">
        <div class="mb-6 flex items-center gap-3">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1.3rem] border-2 border-white bg-[#fff7d6] text-[#d99b16] shadow-sm">
                @include('partials.wiboost-icon', ['name' => 'gift', 'class' => 'h-6 w-6'])
            </span>
            <div class="min-w-0">
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-[#8faaf3]">Benefit</p>
                <h2 class="text-2xl font-black text-[#2b3a67]">Aplikasi Premium</h2>
            </div>
        </div>

        <label class="mb-6 flex cursor-pointer items-center gap-3 rounded-[1.5rem] border-4 border-white bg-white px-5 py-4 shadow-sm">
            <input type="checkbox" name="benefit_enabled" value="1" class="h-5 w-5 rounded-md border-2 border-[#d99b16] bg-white text-[#d99b16] shadow-sm focus:ring-2 focus:ring-[#fff0bf]" style="accent-color: #d99b16;" @checked(old('benefit_enabled', $form?->benefit_enabled ?? false))>
            <span class="text-sm font-black text-[#2b3a67]">Berikan benefit</span>
        </label>

        <div>
            <label class="mb-3 ml-2 block text-sm font-black text-[#5a76c8]">Judul Benefit</label>
            <input type="text" name="benefit_title" value="{{ old('benefit_title', $form?->benefit_title) }}" class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-6 py-4 font-black text-[#2b3a67] shadow-inner outline-none transition focus:border-[#bde0fe]">
        </div>

        <div class="mt-6">
            <label class="mb-3 ml-2 block text-sm font-black text-[#5a76c8]">Deskripsi Benefit</label>
            <textarea name="benefit_description" rows="3" class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-6 py-4 font-black text-[#2b3a67] shadow-inner outline-none transition focus:border-[#bde0fe]">{{ old('benefit_description', $form?->benefit_description) }}</textarea>
        </div>

        <div class="mt-6 rounded-[1.8rem] border-4 border-white bg-white p-4 shadow-sm sm:p-5">
            <div class="mb-5 flex items-center gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-[1.2rem] border-2 border-white bg-[#fffcf0] text-[#d99b16] shadow-sm">
                    @include('partials.wiboost-icon', ['name' => 'profile', 'class' => 'h-5 w-5'])
                </span>
                <div class="min-w-0">
                    <p class="text-[10px] font-black uppercase tracking-[0.24em] text-[#d99b16]">Akun Kredensial</p>
                    <h3 class="text-xl font-black text-[#2b3a67]">Data Manfaat Premium</h3>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-3 ml-2 block text-sm font-black text-[#5a76c8]">Email</label>
                    <input type="text" name="benefit_credentials[email]" value="{{ old('benefit_credentials.email', $benefitCredentials['email']) }}" class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-6 py-4 font-black text-[#2b3a67] shadow-inner outline-none transition focus:border-[#bde0fe]">
                </div>
                <div>
                    <label class="mb-3 ml-2 block text-sm font-black text-[#5a76c8]">Kata Sandi</label>
                    <input type="text" name="benefit_credentials[password]" value="{{ old('benefit_credentials.password', $benefitCredentials['password']) }}" class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-6 py-4 font-black text-[#2b3a67] shadow-inner outline-none transition focus:border-[#bde0fe]">
                </div>
                <div>
                    <label class="mb-3 ml-2 block text-sm font-black text-[#5a76c8]">Profil</label>
                    <input type="text" name="benefit_credentials[profile]" value="{{ old('benefit_credentials.profile', $benefitCredentials['profile']) }}" class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-6 py-4 font-black text-[#2b3a67] shadow-inner outline-none transition focus:border-[#bde0fe]">
                </div>
                <div>
                    <label class="mb-3 ml-2 block text-sm font-black text-[#5a76c8]">PIN</label>
                    <input type="text" name="benefit_credentials[pin]" value="{{ old('benefit_credentials.pin', $benefitCredentials['pin']) }}" class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-6 py-4 font-black text-[#2b3a67] shadow-inner outline-none transition focus:border-[#bde0fe]">
                </div>
                <div>
                    <label class="mb-3 ml-2 block text-sm font-black text-[#5a76c8]">Tautan Akses</label>
                    <input type="url" name="benefit_credentials[link]" value="{{ old('benefit_credentials.link', $benefitCredentials['link']) }}" class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-6 py-4 font-black text-[#2b3a67] shadow-inner outline-none transition focus:border-[#bde0fe]">
                </div>
                <div>
                    <label class="mb-3 ml-2 block text-sm font-black text-[#5a76c8]">Tautan Tutorial</label>
                    <input type="url" name="benefit_credentials[tutorial_link]" value="{{ old('benefit_credentials.tutorial_link', $benefitCredentials['tutorial_link']) }}" class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-6 py-4 font-black text-[#2b3a67] shadow-inner outline-none transition focus:border-[#bde0fe]">
                </div>
            </div>

            <div class="mt-5">
                <label class="mb-3 ml-2 block text-sm font-black text-[#5a76c8]">Catatan Masuk</label>
                <textarea name="benefit_credentials[notes]" rows="3" class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-6 py-4 font-black text-[#2b3a67] shadow-inner outline-none transition focus:border-[#bde0fe]">{{ old('benefit_credentials.notes', $benefitCredentials['notes']) }}</textarea>
            </div>
        </div>
    </section>

    <button type="submit" class="flex w-full items-center justify-center rounded-full border-4 border-white bg-[#5a76c8] py-5 text-lg font-black text-white shadow-xl shadow-[#5a76c8]/30 transition-transform hover:bg-[#4760a9] active:scale-95 sm:text-xl">
        {{ $submitLabel }}
    </button>
</form>

<script>
    (() => {
        const root = document.querySelector('[data-form-builder]');
        if (!root) return;

        const list = root.querySelector('[data-fields-list]');
        const addButton = root.querySelector('[data-add-field]');
        let nextIndex = Number(root.dataset.nextIndex || 0);
        const optionTypes = new Set(['select', 'checkbox']);

        const closeTypeDropdown = (dropdown) => {
            dropdown?.classList.remove('is-open');
            dropdown?.querySelector('[data-answer-type-button]')?.setAttribute('aria-expanded', 'false');
        };

        const closeOtherTypeDropdowns = (currentDropdown = null) => {
            document.querySelectorAll('[data-answer-type-select].is-open').forEach((dropdown) => {
                if (dropdown !== currentDropdown) {
                    closeTypeDropdown(dropdown);
                }
            });
        };

        const syncOptionsVisibility = (row) => {
            const value = row?.querySelector('[data-answer-type-input]')?.value || 'text';
            const optionsWrap = row?.querySelector('[data-answer-options-wrap]');
            const visible = optionTypes.has(value);
            optionsWrap?.classList.toggle('is-visible', visible);

            if (visible && row?.querySelectorAll('[data-option-row]').length === 0) {
                addOption(row);
                addOption(row);
            }
        };

        const setupTypeDropdown = (dropdown) => {
            if (!dropdown || dropdown.dataset.ready === 'true') return;
            dropdown.dataset.ready = 'true';

            const button = dropdown.querySelector('[data-answer-type-button]');
            const input = dropdown.querySelector('[data-answer-type-input]');
            const label = dropdown.querySelector('[data-answer-type-label]');
            const row = dropdown.closest('[data-field-row]');

            button?.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                const willOpen = !dropdown.classList.contains('is-open');
                closeOtherTypeDropdowns(dropdown);
                dropdown.classList.toggle('is-open', willOpen);
                button.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            });

            dropdown.querySelectorAll('[data-answer-type-option]').forEach((option) => {
                option.addEventListener('click', (event) => {
                    event.preventDefault();
                    const value = option.dataset.value || 'text';
                    const optionLabel = option.dataset.label || option.textContent.trim();

                    if (input) input.value = value;
                    if (label) label.textContent = optionLabel;

                    dropdown.querySelectorAll('[data-answer-type-option]').forEach((item) => {
                        const selected = item === option;
                        item.classList.toggle('is-selected', selected);
                        item.setAttribute('aria-selected', selected ? 'true' : 'false');
                    });

                    syncOptionsVisibility(row);
                    closeTypeDropdown(dropdown);
                });
            });

            syncOptionsVisibility(row);
        };

        const setupRow = (row) => {
            if (row && !row.dataset.nextOptionIndex) {
                row.dataset.nextOptionIndex = String(row.querySelectorAll('[data-option-row]').length);
            }
            row?.querySelectorAll('[data-answer-type-select]').forEach(setupTypeDropdown);
            syncOptionsVisibility(row);
        };

        const addOption = (row) => {
            const optionList = row?.querySelector('[data-option-list]');
            if (!row || !optionList) return;

            const fieldIndex = row.dataset.fieldIndex || '0';
            const optionIndex = Number(row.dataset.nextOptionIndex || optionList.querySelectorAll('[data-option-row]').length);
            optionList.insertAdjacentHTML('beforeend', optionTemplate(fieldIndex, optionIndex));
            row.dataset.nextOptionIndex = String(optionIndex + 1);
        };

        const renumberFields = () => {
            list?.querySelectorAll('[data-field-row]').forEach((row, index) => {
                const title = row.querySelector('[data-field-title]');

                if (title) {
                    title.textContent = `Pertanyaan #${index + 1}`;
                }
            });
        };

        const fieldTemplate = (index) => `
            <div class="rounded-[1.8rem] border-4 border-white bg-white p-4 shadow-md shadow-[#bde0fe]/15 sm:p-5" data-field-row data-field-index="${index}">
                <input type="hidden" name="fields[${index}][key]" value="">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <p class="rounded-full bg-[#f4f9ff] px-4 py-2 text-xs font-black text-[#5a76c8]" data-field-title>Pertanyaan #${index + 1}</p>
                    <button type="button" data-remove-field class="flex h-10 w-10 shrink-0 items-center justify-center rounded-[1rem] border-2 border-white bg-[#ffe5e5] text-[#ff6b6b] shadow-sm transition-transform active:scale-95" title="Hapus Pertanyaan">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 7h14M9 7V5h6v2M8 10v8M12 10v8M16 10v8M7 7l1 13h8l1-13"/></svg>
                    </button>
                </div>
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-[minmax(0,1.4fr)_14rem]">
                    <div>
                        <label class="mb-2 ml-2 block text-xs font-black text-[#5a76c8]">Label Pertanyaan</label>
                        <input type="text" name="fields[${index}][label]" required class="w-full rounded-[1.3rem] border-4 border-white bg-[#f4f9ff] px-5 py-3 text-sm font-black text-[#2b3a67] shadow-inner outline-none transition focus:border-[#bde0fe]">
                    </div>
                    <div>
                        <label class="mb-2 ml-2 block text-xs font-black text-[#5a76c8]">Tipe Jawaban</label>
                        <div class="wiboost-answer-type-select" data-answer-type-select>
                            <input type="hidden" name="fields[${index}][type]" value="text" data-answer-type-input>
                            <button type="button" class="wiboost-answer-type-button" aria-haspopup="listbox" aria-expanded="false" data-answer-type-button>
                                <span class="truncate" data-answer-type-label>Teks pendek</span>
                                <span class="wiboost-answer-type-arrow" aria-hidden="true">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none">
                                        <path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                            </button>
                            <div class="wiboost-answer-type-menu" role="listbox" data-answer-type-menu>
                                <button type="button" class="wiboost-answer-type-option is-selected" role="option" aria-selected="true" data-answer-type-option data-value="text" data-label="Teks pendek">Teks pendek</button>
                                <button type="button" class="wiboost-answer-type-option" role="option" aria-selected="false" data-answer-type-option data-value="textarea" data-label="Paragraf">Paragraf</button>
                                <button type="button" class="wiboost-answer-type-option" role="option" aria-selected="false" data-answer-type-option data-value="email" data-label="Email">Email</button>
                                <button type="button" class="wiboost-answer-type-option" role="option" aria-selected="false" data-answer-type-option data-value="number" data-label="Nomor">Nomor</button>
                                <button type="button" class="wiboost-answer-type-option" role="option" aria-selected="false" data-answer-type-option data-value="date" data-label="Tanggal">Tanggal</button>
                                <button type="button" class="wiboost-answer-type-option" role="option" aria-selected="false" data-answer-type-option data-value="select" data-label="Pilihan">Pilihan</button>
                                <button type="button" class="wiboost-answer-type-option" role="option" aria-selected="false" data-answer-type-option data-value="checkbox" data-label="Centang">Centang</button>
                                <button type="button" class="wiboost-answer-type-option" role="option" aria-selected="false" data-answer-type-option data-value="file" data-label="Unggah File">Unggah File</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <label class="mb-2 ml-2 block text-xs font-black text-[#5a76c8]">Deskripsi (Opsional)</label>
                    <input type="text" name="fields[${index}][help]" class="w-full rounded-[1.3rem] border-4 border-white bg-[#f4f9ff] px-5 py-3 text-sm font-black text-[#2b3a67] shadow-inner outline-none transition focus:border-[#bde0fe]">
                </div>
                <div class="wiboost-answer-options-wrap mt-4" data-answer-options-wrap>
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <label class="ml-2 block text-xs font-black text-[#5a76c8]">Opsi Jawaban</label>
                        <button type="button" data-add-option class="inline-flex items-center gap-2 rounded-xl border-2 border-white bg-[#e0fbfc] px-3 py-2 text-xs font-black text-[#4bc6b9] shadow-sm">+ Tambah Opsi</button>
                    </div>
                    <div class="space-y-3" data-option-list></div>
                </div>
                <label class="mt-4 ml-2 flex cursor-pointer items-center gap-3">
                    <input type="checkbox" name="fields[${index}][required]" value="1" class="h-5 w-5 rounded-md border-2 border-[#5a76c8] bg-white text-[#5a76c8] shadow-sm focus:ring-2 focus:ring-[#bde0fe]" style="accent-color: #5a76c8;">
                    <span class="text-sm font-black text-[#2b3a67]">Wajib diisi</span>
                </label>
            </div>`;

        const optionTemplate = (fieldIndex, optionIndex) => `
            <div class="flex flex-col gap-3 rounded-[1.25rem] border-2 border-white bg-[#f8fbff] p-3 sm:flex-row sm:items-center" data-option-row>
                <div class="wiboost-option-image-preview hidden" data-option-preview><img src="" alt=""></div>
                <div class="min-w-0 flex-1">
                    <input type="text" name="fields[${fieldIndex}][option_items][${optionIndex}][label]" class="w-full rounded-[1.1rem] border-4 border-white bg-white px-4 py-3 text-sm font-black text-[#2b3a67] shadow-inner outline-none focus:border-[#bde0fe]">
                    <input type="hidden" name="fields[${fieldIndex}][option_items][${optionIndex}][existing_image]" value="">
                    <input type="hidden" name="fields[${fieldIndex}][option_items][${optionIndex}][remove_image]" value="0" data-remove-image-input>
                    <label class="relative mt-2 flex min-h-[3.25rem] w-full cursor-pointer items-center gap-3 overflow-hidden rounded-[1.1rem] border-4 border-white bg-white px-3 py-2 shadow-inner">
                        <input type="file" name="fields[${fieldIndex}][option_items][${optionIndex}][image]" accept="image/*" class="absolute inset-0 h-full w-full cursor-pointer opacity-0" data-option-image>
                        <span class="shrink-0 rounded-full bg-[#e0fbfc] px-3 py-2 text-xs font-black text-[#4bc6b9]">Pilih File</span>
                        <span class="min-w-0 flex-1 truncate text-xs font-bold text-[#8faaf3]" data-option-image-name>Belum ada foto</span>
                    </label>
                </div>
                <div class="flex shrink-0 justify-end gap-2">
                    <button type="button" data-remove-option-image class="hidden h-10 rounded-xl border-2 border-white bg-[#ffe5e5] px-3 text-xs font-black text-[#ff6b6b]">Hapus Foto</button>
                    <button type="button" data-remove-option class="flex h-10 w-10 items-center justify-center rounded-xl border-2 border-white bg-[#ffe5e5] text-[#ff6b6b]" title="Hapus Opsi">×</button>
                </div>
            </div>`;

        addButton?.addEventListener('click', () => {
            list.insertAdjacentHTML('beforeend', fieldTemplate(nextIndex));
            setupRow(list.lastElementChild);
            nextIndex += 1;
            renumberFields();
        });

        list?.addEventListener('click', (event) => {
            const addOptionButton = event.target.closest('[data-add-option]');
            if (addOptionButton) {
                addOption(addOptionButton.closest('[data-field-row]'));
                return;
            }

            const removeOptionButton = event.target.closest('[data-remove-option]');
            if (removeOptionButton) {
                removeOptionButton.closest('[data-option-row]')?.remove();
                return;
            }

            const removeImageButton = event.target.closest('[data-remove-option-image]');
            if (removeImageButton) {
                const optionRow = removeImageButton.closest('[data-option-row]');
                const preview = optionRow?.querySelector('[data-option-preview]');
                const imageInput = optionRow?.querySelector('[data-option-image]');
                const imageName = optionRow?.querySelector('[data-option-image-name]');
                const removeInput = optionRow?.querySelector('[data-remove-image-input]');

                if (preview) {
                    preview.classList.add('hidden');
                    const image = preview.querySelector('img');
                    if (image) image.src = '';
                }
                if (imageInput) imageInput.value = '';
                if (imageName) imageName.textContent = 'Belum ada foto';
                if (removeInput) removeInput.value = '1';
                removeImageButton.classList.add('hidden');
                return;
            }

            const button = event.target.closest('[data-remove-field]');
            if (!button) return;

            const rows = list.querySelectorAll('[data-field-row]');
            if (rows.length <= 1) {
                return;
            }

            button.closest('[data-field-row]')?.remove();
            renumberFields();
        });

        list?.addEventListener('change', (event) => {
            const imageInput = event.target.closest('[data-option-image]');
            if (!imageInput || !imageInput.files?.[0]) return;

            const optionRow = imageInput.closest('[data-option-row]');
            const preview = optionRow?.querySelector('[data-option-preview]');
            const previewImage = preview?.querySelector('img');
            const imageName = optionRow?.querySelector('[data-option-image-name]');
            const removeInput = optionRow?.querySelector('[data-remove-image-input]');
            const removeButton = optionRow?.querySelector('[data-remove-option-image]');

            if (previewImage) previewImage.src = URL.createObjectURL(imageInput.files[0]);
            if (imageName) imageName.textContent = imageInput.files[0].name;
            preview?.classList.remove('hidden');
            if (removeInput) removeInput.value = '0';
            removeButton?.classList.remove('hidden');
        });

        list?.querySelectorAll('[data-field-row]').forEach(setupRow);
        renumberFields();

        document.addEventListener('click', (event) => {
            if (!event.target.closest('[data-answer-type-select]')) {
                closeOtherTypeDropdowns();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeOtherTypeDropdowns();
            }
        });
    })();
</script>
