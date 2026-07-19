@extends('layouts.user')

@section('title', 'Lapor Admin')
@section('body_class', 'wiboost-user-report-page')

@section('content')
@php
    $selectedKey = old('category_key', $selectedCategory['key'] ?? '');
    $needsRecord = (bool) ($selectedCategory['needs_record'] ?? false);
    $oldRecordId = old('record_id');
@endphp

@if(session('error') || $errors->any())
    <script>
        (() => {
            const cookieName = 'wiboost_after_admin_report';
            document.cookie = cookieName + '=; Max-Age=0; path=/; SameSite=Lax';

            try {
                window.localStorage?.removeItem(cookieName);
                window.sessionStorage?.removeItem(cookieName);
                window.sessionStorage?.removeItem('wiboost_admin_report_pending');
                window.sessionStorage?.removeItem('wiboost_admin_report_left_page');
            } catch (error) {}
        })();
    </script>
@endif

<style>
    html {
        height: auto !important;
        overflow-x: hidden !important;
        overflow-y: auto !important;
    }

    body.wiboost-user-report-page {
        height: auto !important;
        max-height: none !important;
        overflow-x: hidden !important;
        overflow-y: auto !important;
    }

    body.wiboost-user-report-page main {
        height: auto !important;
        max-height: none !important;
        overflow: visible !important;
    }

    body.wiboost-user-report-page footer {
        display: block !important;
    }

    .report-page-shell {
        display: block;
        height: auto;
        min-height: auto;
        overflow: visible;
    }

    .report-page-header {
        margin-bottom: 2.5rem !important;
    }

    .report-category-panel {
        margin-bottom: 2rem !important;
    }

    .report-form,
    .report-form-grid {
        height: auto !important;
        min-height: auto;
        overflow: visible !important;
    }

    .report-main-column,
    .report-side-column {
        display: block;
        min-height: auto;
        overflow: visible !important;
    }

    .report-main-column > section:not(.report-record-section),
    .report-side-column > section,
    .report-side-column > button {
        flex: none;
    }

    .report-record-section {
        display: block;
        min-height: auto;
        overflow: visible !important;
    }

    .report-record-section > label {
        flex: none;
    }

    .report-record-empty {
        flex: none;
    }

    .report-main-column textarea {
        max-height: none;
        min-height: 12rem;
    }

    #proof-dropzone {
        min-height: 220px;
    }

    .report-side-column .space-y-3 {
        font-size: 0.82rem;
        line-height: 1.45;
    }

    @media (max-width: 1023px) {
        body.wiboost-user-report-page main {
            height: auto !important;
            max-height: none !important;
        }

        .report-form-grid {
            grid-template-columns: minmax(0, 1fr) !important;
        }

        .report-side-column {
            display: block;
        }
    }

    .report-form,
    .report-form-grid,
    .report-main-column,
    .report-side-column,
    .report-side-column > section {
        max-height: none;
    }

    .report-record-list {
        max-height: none;
        overflow: visible !important;
        overscroll-behavior: auto;
        padding-right: 0;
    }

    .report-record-list::-webkit-scrollbar {
        display: initial;
    }

    [data-report-record-card] {
        align-items: center;
    }

    [data-report-record-dot] {
        align-self: center;
        margin-top: 0 !important;
    }

    .report-record-invoice,
    .report-record-title,
    .report-record-meta {
        overflow: visible;
        overflow-wrap: anywhere;
        text-overflow: clip;
        white-space: normal;
        word-break: normal;
    }

    @media (max-width: 640px) {
        .report-record-list {
            max-height: none;
        }

        [data-report-record-card] {
            border-radius: 1.45rem;
            gap: 0.65rem;
            padding: 0.95rem 1rem;
        }

        [data-report-record-dot] {
            height: 1rem;
            margin-top: 0 !important;
            width: 1rem;
        }

        .report-record-title {
            font-size: 0.88rem;
            line-height: 1.25;
        }

        .report-record-meta {
            font-size: 0.62rem;
            letter-spacing: 0.08em;
            line-height: 1.35;
        }
    }

    .report-proof-preview {
        object-fit: cover;
    }

    .proof-remove-button {
        aspect-ratio: 1 / 1;
        height: 3rem !important;
        min-height: 3rem;
        min-width: 3rem;
        padding: 0 !important;
        width: 3rem !important;
    }

    .proof-remove-icon {
        aspect-ratio: 1 / 1;
        display: block;
        flex: 0 0 auto;
        height: 1.35rem !important;
        width: 1.35rem !important;
    }

    .report-type-button {
        align-items: center;
        display: inline-flex;
        justify-content: center;
        min-height: 3.5rem;
        overflow: visible;
        text-wrap: nowrap;
        white-space: nowrap;
    }

    @media (max-width: 430px) {
        .report-type-button {
            border-radius: 1.35rem;
            font-size: clamp(0.68rem, 3.1vw, 0.78rem) !important;
            letter-spacing: -0.01em;
            padding-left: 0.35rem !important;
            padding-right: 0.35rem !important;
        }
    }
</style>

<div class="report-page-shell wiboost-font mx-auto max-w-6xl pb-12">
    <div class="report-page-header mb-10 text-center md:text-left pl-2">
        <div class="inline-block rounded-full border-2 border-white bg-[#e0fbfc] px-4 py-1 text-[10px] font-black uppercase tracking-widest text-[#4bc6b9] shadow-sm">
            Bantuan
        </div>
        <h1 class="mt-3 flex items-center justify-start gap-3 text-3xl font-black tracking-tight text-[#2b3a67] md:text-4xl">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center text-[#5a76c8]">
                <svg class="h-9 w-9 drop-shadow-sm" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 11.85a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5Zm-6.35 8.05a6.35 6.35 0 0 1 12.7 0"/>
                    <g transform="translate(0 -4.4)">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.9 12.05v-.55a6.1 6.1 0 0 1 12.2 0v.55"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 12.1h1.45v3.05H5.25a1 1 0 0 1-1-1V13.1a1 1 0 0 1 1-1Zm12.05 0h1.45a1 1 0 0 1 1 1v1.05a1 1 0 0 1-1 1H17.3V12.1Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.05 15.05c-.45 1.55-1.7 2.35-3.65 2.35h-1.05"/>
                        <path stroke-linecap="round" d="M12.55 17.4h1.2"/>
                    </g>
                </svg>
            </span>
            <span>Lapor Admin</span>
        </h1>
    </div>

    @if(session('error'))
        <div class="mb-6 rounded-[2rem] border-4 border-white bg-[#ffe5e5] px-6 py-4 text-sm font-black text-[#ff6b6b] shadow-lg shadow-[#ff6b6b]/15">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-[2rem] border-4 border-white bg-[#fff7e0] px-6 py-4 text-sm font-black text-[#d38b00] shadow-lg shadow-[#ffd166]/15">
            {{ $errors->first() }}
        </div>
    @endif

    <section class="report-category-panel mb-8 rounded-[2.5rem] border-4 border-white bg-white/95 p-5 shadow-xl shadow-[#bde0fe]/25 md:p-6">
        <p class="mb-4 ml-1 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Pilih Jenis Kendala</p>
        <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
            @foreach($categories as $category)
                <a href="{{ route('user.report.admin', ['type' => $category['key']]) }}"
                   class="report-type-button rounded-[1.75rem] border-4 px-4 py-3 text-center text-xs font-black transition active:scale-95 {{ $selectedKey === $category['key'] ? 'border-white bg-[#5a76c8] text-white shadow-lg shadow-[#5a76c8]/25' : 'border-white bg-[#f4f9ff] text-[#5a76c8] hover:bg-[#e0fbfc]' }}">
                    {{ $category['label'] }}
                </a>
            @endforeach
        </div>
    </section>

    @if(! $selectedCategory)
        <section class="rounded-[2.5rem] border-4 border-white bg-white/95 p-8 text-center shadow-xl shadow-[#bde0fe]/25">
            <p class="text-lg font-black text-[#5a76c8]">Pilih jenis kendala dulu.</p>
            <p class="mt-2 text-sm font-bold text-[#8faaf3]">Setelah dipilih, form laporan akan muncul otomatis.</p>
        </section>
    @elseif(! $adminNumber)
        <section class="rounded-[2.5rem] border-4 border-white bg-white/95 p-8 text-center shadow-xl shadow-[#bde0fe]/25">
            <p class="text-lg font-black text-[#5a76c8]">Nomor WhatsApp admin belum diatur.</p>
            <p class="mt-2 text-sm font-bold text-[#8faaf3]">Hubungi admin website untuk melengkapi konfigurasi kontak.</p>
        </section>
    @else
        <form method="POST" action="{{ route('user.report.admin.store') }}" enctype="multipart/form-data" class="report-form space-y-6" novalidate onsubmit="return lockAdminReportSubmit(this, event)">
            @csrf
            <input type="hidden" name="category_key" value="{{ $selectedKey }}">

            <div class="report-form-grid grid gap-6 lg:grid-cols-[1fr_0.85fr]">
                <div class="report-main-column space-y-6">
                    <section class="rounded-[2.5rem] border-4 border-white bg-white/95 p-5 shadow-xl shadow-[#bde0fe]/25 md:p-6">
                        <label for="issue" class="mb-3 ml-1 block text-xs font-black uppercase tracking-widest text-[#5a76c8]">Tulis Kendala</label>
                        <textarea id="issue" name="issue" rows="8" required minlength="5" class="w-full rounded-[2rem] border-4 border-white bg-[#f4f9ff] px-5 py-4 text-sm font-bold leading-relaxed text-[#2b3a67] shadow-inner outline-none transition focus:border-[#bde0fe]" placeholder="Cth: Saldo belum masuk, pesanan belum sukses, atau ada error di halaman website.">{{ old('issue') }}</textarea>
                    </section>

                    <section class="rounded-[2.5rem] border-4 border-white bg-white/95 p-5 shadow-xl shadow-[#bde0fe]/25 md:p-6">
                        <label for="proof" class="mb-3 ml-1 block text-xs font-black uppercase tracking-widest text-[#5a76c8]">Bukti Kendala</label>
                        <div class="relative">
                            <input id="proof" type="file" name="proof" accept="image/*" required class="sr-only" onchange="handleProofPreview(this)">
                            <label for="proof" id="proof-dropzone" class="group relative flex min-h-[220px] cursor-pointer items-center justify-center overflow-hidden rounded-[2rem] border-4 border-dashed border-[#bde0fe] bg-[#f8fbff] p-5 text-center shadow-inner transition hover:border-[#8faaf3] hover:bg-[#e0fbfc]/60">
                                <img id="proof-preview" src="" alt="Preview bukti kendala" class="report-proof-preview absolute inset-0 hidden h-full w-full">
                                <span id="proof-empty-state" class="relative z-10 flex max-w-md flex-col items-center gap-3">
                                    <span class="flex h-16 w-16 items-center justify-center rounded-[1.5rem] border-4 border-white bg-white text-[#5a76c8] shadow-lg shadow-[#bde0fe]/25 transition group-hover:scale-105">
                                        <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16.5V18a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-1.5M8 10l4-4m0 0 4 4m-4-4v11" />
                                        </svg>
                                    </span>
                                    <span class="block text-sm font-black text-[#2b3a67]">Upload screenshot atau foto bukti</span>
                                    <span class="block text-xs font-bold leading-relaxed text-[#8faaf3]">Kalau tidak bisa screenshot, foto layar dari device lain juga boleh.</span>
                                </span>
                            </label>
                            <button id="proof-remove" type="button" onclick="clearProofPreview(event)" class="proof-remove-button absolute right-4 top-4 z-20 hidden shrink-0 items-center justify-center rounded-2xl border-4 border-white bg-[#ffe5e5] text-[#ff4d4d] shadow-lg shadow-[#ff6b6b]/20 transition hover:scale-105 hover:bg-[#ffcccc]" aria-label="Hapus gambar bukti">
                                <svg class="proof-remove-icon" fill="none" stroke="currentColor" stroke-width="2.6" viewBox="0 0 24 24" preserveAspectRatio="xMidYMid meet" aria-hidden="true" focusable="false">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 11v6M14 11v6" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V5.5A1.5 1.5 0 0 1 10.5 4h3A1.5 1.5 0 0 1 15 5.5V7" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 7 .75 12A2.2 2.2 0 0 0 8.95 21h6.1a2.2 2.2 0 0 0 2.2-2L18 7" />
                                </svg>
                            </button>
                        </div>
                    </section>

                    @if($needsRecord)
                        <section class="report-record-section rounded-[2.5rem] border-4 border-white bg-white/95 p-5 shadow-xl shadow-[#bde0fe]/25 md:p-6">
                            <label class="mb-3 ml-1 block text-xs font-black uppercase tracking-widest text-[#5a76c8]">
                                {{ $selectedKey === 'deposit' ? 'Pilih Riwayat Deposit' : ($selectedKey === 'commission' ? 'Pilih Riwayat Komisi' : 'Pilih Riwayat Pesanan') }}
                            </label>

                            @if($records->isEmpty())
                                <div class="report-record-empty rounded-[2rem] border-4 border-dashed border-[#bde0fe] bg-[#f4f9ff] p-6 text-center">
                                    <p class="text-sm font-black text-[#5a76c8]">Belum ada data terkait yang bisa dipilih.</p>
                                    <p class="mt-2 text-xs font-bold text-[#8faaf3]">Pilih jenis kendala lain yang memiliki riwayat pesanan, deposit, atau komisi.</p>
                                </div>
                            @else
                                <div class="report-record-list space-y-3">
                                    @foreach($records as $record)
                                        @php
                                            $recordInputId = 'report-record-' . $loop->index;
                                        @endphp
                                        <label for="{{ $recordInputId }}" class="block cursor-pointer" data-report-record-option onclick="selectReportRecord(this)">
                                            <input id="{{ $recordInputId }}" type="radio" name="record_id" value="{{ $record['id'] }}" class="sr-only" data-report-record-input onchange="selectReportRecord(this.closest('[data-report-record-option]'))" required @checked($oldRecordId === $record['id'])>
                                            <span data-report-record-card class="flex items-center gap-3 rounded-[1.75rem] border-4 {{ $oldRecordId === $record['id'] ? 'border-[#25D366] bg-[#e9fff0]' : 'border-white bg-[#f8fbff]' }} px-5 py-4 text-left shadow-sm transition">
                                                <span data-report-record-dot class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 {{ $oldRecordId === $record['id'] ? 'border-[#25D366] bg-[#25D366]' : 'border-[#bde0fe] bg-white' }}">
                                                    <span class="h-2 w-2 rounded-full bg-white"></span>
                                                </span>
                                                <span class="min-w-0 flex-1">
                                                    <span class="report-record-invoice block text-xs font-black text-[#5a76c8]">{{ $record['invoice'] }}</span>
                                                    <span class="report-record-title mt-1 block text-base font-black leading-tight text-[#2b3a67]">{{ $record['title'] }}</span>
                                                    <span class="report-record-meta mt-2 block text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">{{ $record['meta'] }}</span>
                                                </span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </section>
                    @endif
                </div>

                <aside class="report-side-column space-y-6">
                    <section class="rounded-[2.5rem] border-4 border-white bg-white/95 p-5 shadow-xl shadow-[#bde0fe]/25 md:p-6">
                        <p class="mb-3 ml-1 text-xs font-black uppercase tracking-widest text-[#5a76c8]">Alur Laporan</p>
                        <div class="space-y-3 rounded-[2rem] border-4 border-white bg-[#f4f9ff] p-5 text-sm font-bold leading-relaxed text-[#5a76c8] shadow-inner">
                            <p>1. Pilih masalah yang ingin dilaporkan.</p>
                            <p>2. Ceritakan kendalanya dengan singkat dan jelas.</p>
                            <p>3. Upload bukti agar admin lebih cepat mengecek.</p>
                            @if($needsRecord)
                                <p>4. Pilih deposit, pesanan, atau komisi yang bermasalah jika ada.</p>
                                <p>5. Tekan Kirim Laporan. WhatsApp admin akan terbuka otomatis.</p>
                            @else
                                <p>4. Tekan Kirim Laporan. WhatsApp admin akan terbuka otomatis.</p>
                            @endif
                            <p>Pesan WhatsApp sudah berisi detail laporan dan link bukti. Setelah terkirim, tinggal tunggu balasan admin.</p>
                        </div>
                    </section>

                    <button type="submit"
                            data-report-submit
                            aria-disabled="true"
                            class="w-full cursor-not-allowed rounded-full border-4 border-white px-6 py-4 text-sm font-black text-white shadow-xl transition active:scale-95"
                            style="background:#cbd5e1;box-shadow:0 18px 30px rgba(148, 163, 184, .2);">
                        Kirim Laporan
                    </button>
                </aside>
            </div>
        </form>
    @endif
</div>

<script>
    let proofPreviewObjectUrl = null;

    function handleProofPreview(input) {
        const file = input?.files?.[0] || null;
        const preview = document.getElementById('proof-preview');
        const emptyState = document.getElementById('proof-empty-state');
        const removeButton = document.getElementById('proof-remove');

        if (!preview || !emptyState || !removeButton) {
            return;
        }

        if (proofPreviewObjectUrl) {
            URL.revokeObjectURL(proofPreviewObjectUrl);
            proofPreviewObjectUrl = null;
        }

        if (!file) {
            preview.removeAttribute('src');
            preview.classList.add('hidden');
            emptyState.classList.remove('hidden');
            removeButton.classList.add('hidden');
            removeButton.classList.remove('flex');
            updateAdminReportSubmitState(input?.form);
            return;
        }

        proofPreviewObjectUrl = URL.createObjectURL(file);
        preview.src = proofPreviewObjectUrl;
        preview.classList.remove('hidden');
        emptyState.classList.add('hidden');
        removeButton.classList.remove('hidden');
        removeButton.classList.add('flex');
        updateAdminReportSubmitState(input?.form);
    }

    function clearProofPreview(event) {
        event?.preventDefault();
        event?.stopPropagation();

        const input = document.getElementById('proof');

        if (input) {
            input.value = '';
        }

        handleProofPreview(input);
        updateAdminReportSubmitState(input?.form);
    }

    function selectReportRecord(option) {
        if (!option) {
            return;
        }

        document.querySelectorAll('[data-report-record-option]').forEach((item) => {
            const input = item.querySelector('[data-report-record-input]');
            const card = item.querySelector('[data-report-record-card]');
            const dot = item.querySelector('[data-report-record-dot]');
            const isActive = item === option;

            if (input) {
                input.checked = isActive;
            }

            if (card) {
                card.classList.toggle('border-[#25D366]', isActive);
                card.classList.toggle('bg-[#e9fff0]', isActive);
                card.classList.toggle('border-white', !isActive);
                card.classList.toggle('bg-[#f8fbff]', !isActive);
            }

            if (dot) {
                dot.classList.toggle('border-[#25D366]', isActive);
                dot.classList.toggle('bg-[#25D366]', isActive);
                dot.classList.toggle('border-[#bde0fe]', !isActive);
                dot.classList.toggle('bg-white', !isActive);
            }
        });

        updateAdminReportSubmitState(option.closest('form'));
    }

    function isAdminReportReady(form) {
        if (!form) {
            return false;
        }

        const category = form.querySelector('input[name="category_key"]');
        const issue = form.querySelector('#issue');
        const proof = form.querySelector('#proof');
        const recordInputs = Array.from(form.querySelectorAll('[data-report-record-input]'));

        return Boolean(category?.value?.trim())
            && Boolean(issue?.value?.trim()) && issue.value.trim().length >= 5
            && Boolean(proof?.files?.length)
            && recordInputs.length > 0
            && recordInputs.some((input) => input.checked);
    }

    function setReportButtonReady(button, isReady) {
        if (!button) {
            return;
        }

        button.setAttribute('aria-disabled', String(!isReady));
        button.classList.toggle('cursor-not-allowed', !isReady);
        button.classList.toggle('hover:bg-[#1fb458]', isReady);
        button.style.background = isReady ? '#25D366' : '#cbd5e1';
        button.style.boxShadow = isReady
            ? '0 18px 30px rgba(37, 211, 102, .25)'
            : '0 18px 30px rgba(148, 163, 184, .2)';
    }

    function updateAdminReportSubmitState(form = document.querySelector('.report-form')) {
        const button = form?.querySelector('[data-report-submit]');

        if (!button || form?.dataset.submitting === 'true') {
            return;
        }

        setReportButtonReady(button, isAdminReportReady(form));
    }

    function setReportSubmitLoading(form, isLoading) {
        const button = form?.querySelector('[data-report-submit]');

        if (!button) {
            return;
        }

        if (isLoading) {
            setReportButtonReady(button, false);
            button.classList.add('cursor-not-allowed', 'opacity-70');
            return;
        }

        button.classList.remove('cursor-not-allowed', 'opacity-70');
        setReportButtonReady(button, isAdminReportReady(form));
    }

    async function compressProofFile(file) {
        if (!file || !file.type.startsWith('image/')) {
            return null;
        }

        const imageUrl = URL.createObjectURL(file);

        try {
            const image = await new Promise((resolve, reject) => {
                const img = new Image();
                img.onload = () => resolve(img);
                img.onerror = reject;
                img.src = imageUrl;
            });

            const maxEdge = 1600;
            const scale = Math.min(1, maxEdge / Math.max(image.naturalWidth || image.width, image.naturalHeight || image.height));

            if (scale >= 1 && file.size <= 3 * 1024 * 1024 && /image\/(jpe?g|png|webp)/i.test(file.type)) {
                return null;
            }

            const canvas = document.createElement('canvas');
            canvas.width = Math.max(1, Math.round((image.naturalWidth || image.width) * scale));
            canvas.height = Math.max(1, Math.round((image.naturalHeight || image.height) * scale));
            canvas.getContext('2d').drawImage(image, 0, 0, canvas.width, canvas.height);

            const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.82));

            if (!blob || blob.size >= file.size) {
                return null;
            }

            const baseName = file.name.replace(/\.[^.]+$/, '') || 'bukti-kendala';
            return new File([blob], baseName + '.jpg', { type: 'image/jpeg', lastModified: Date.now() });
        } catch (error) {
            return null;
        } finally {
            URL.revokeObjectURL(imageUrl);
        }
    }

    async function prepareProofUpload(form) {
        const input = form?.querySelector('#proof');
        const file = input?.files?.[0] || null;

        if (!input || !file) {
            return true;
        }

        const compressedFile = await compressProofFile(file);

        if (!compressedFile) {
            return true;
        }

        try {
            const transfer = new DataTransfer();
            transfer.items.add(compressedFile);
            input.files = transfer.files;
            handleProofPreview(input);
        } catch (error) {
            return true;
        }

        return true;
    }

    function lockAdminReportSubmit(form, event) {
        if (!form || form.dataset.submitting === 'true') {
            return false;
        }

        if (!isAdminReportReady(form)) {
            event?.preventDefault();
            updateAdminReportSubmitState(form);
            return false;
        }

        if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
            return true;
        }

        if (form.dataset.proofPrepared !== 'true') {
            event?.preventDefault();
            setReportSubmitLoading(form, true);

            prepareProofUpload(form)
                .then(() => {
                    form.dataset.proofPrepared = 'true';
                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                    } else {
                        HTMLFormElement.prototype.submit.call(form);
                    }
                })
                .catch(() => {
                    form.dataset.proofPrepared = '';
                    form.dataset.submitting = '';
                    setReportSubmitLoading(form, false);
                    alert('Bukti kendala gagal diproses. Coba pilih screenshot yang lebih kecil.');
                });

            return false;
        }

        form.dataset.submitting = 'true';
        setReportSubmitLoading(form, true);

        return true;
    }

    document.addEventListener('DOMContentLoaded', () => {
        const form = document.querySelector('.report-form');

        if (!form) {
            return;
        }

        form.querySelectorAll('#issue, #proof, [data-report-record-input]').forEach((field) => {
            field.addEventListener('input', () => updateAdminReportSubmitState(form));
            field.addEventListener('change', () => updateAdminReportSubmitState(form));
        });

        updateAdminReportSubmitState(form);
    });
</script>
@endsection
