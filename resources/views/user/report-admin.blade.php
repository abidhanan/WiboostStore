@extends('layouts.user')

@section('title', 'Lapor Admin')

@section('content')
@php
    $selectedKey = old('category_key', $selectedCategory['key'] ?? '');
    $needsRecord = (bool) ($selectedCategory['needs_record'] ?? false);
    $oldRecordId = old('record_id');
@endphp

<div class="wiboost-font mx-auto max-w-6xl pb-12">
    <div class="mb-10 text-center md:text-left pl-2">
        <div class="inline-block rounded-full border-2 border-white bg-[#e0fbfc] px-4 py-1 text-[10px] font-black uppercase tracking-widest text-[#4bc6b9] shadow-sm">
            Bantuan
        </div>
        <h1 class="mt-3 flex items-center justify-center gap-2 text-3xl font-black tracking-tight text-[#2b3a67] md:justify-start md:text-4xl">
            <span>Lapor Admin</span>
            <span class="text-2xl leading-none">&#128172;</span>
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

    <section class="mb-8 rounded-[2.5rem] border-4 border-white bg-white/95 p-5 shadow-xl shadow-[#bde0fe]/25 md:p-6">
        <p class="mb-4 ml-1 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Pilih Jenis Kendala</p>
        <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
            @foreach($categories as $category)
                <a href="{{ route('user.report.admin', ['type' => $category['key']]) }}"
                   class="rounded-[1.75rem] border-4 px-4 py-3 text-center text-xs font-black transition active:scale-95 {{ $selectedKey === $category['key'] ? 'border-white bg-[#5a76c8] text-white shadow-lg shadow-[#5a76c8]/25' : 'border-white bg-[#f4f9ff] text-[#5a76c8] hover:bg-[#e0fbfc]' }}">
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
        <form method="POST" action="{{ route('user.report.admin.store') }}" enctype="multipart/form-data" class="space-y-6" onsubmit="return lockAdminReportSubmit(this)">
            @csrf
            <input type="hidden" name="category_key" value="{{ $selectedKey }}">

            <div class="grid gap-6 lg:grid-cols-[1fr_0.85fr]">
                <div class="space-y-6">
                    <section class="rounded-[2.5rem] border-4 border-white bg-white/95 p-5 shadow-xl shadow-[#bde0fe]/25 md:p-6">
                        <label for="issue" class="mb-3 ml-1 block text-xs font-black uppercase tracking-widest text-[#5a76c8]">Tulis Kendala</label>
                        <textarea id="issue" name="issue" rows="8" class="w-full rounded-[2rem] border-4 border-white bg-[#f4f9ff] px-5 py-4 text-sm font-bold leading-relaxed text-[#2b3a67] shadow-inner outline-none transition focus:border-[#bde0fe]" placeholder="Cth: Saldo belum masuk, pesanan belum sukses, atau ada error di halaman website.">{{ old('issue') }}</textarea>
                    </section>

                    <section class="rounded-[2.5rem] border-4 border-white bg-white/95 p-5 shadow-xl shadow-[#bde0fe]/25 md:p-6">
                        <label for="proof" class="mb-3 ml-1 block text-xs font-black uppercase tracking-widest text-[#5a76c8]">Bukti Kendala</label>
                        <label class="group flex cursor-pointer flex-col gap-4 rounded-[2rem] border-4 border-dashed border-[#bde0fe] bg-[#f8fbff] p-4 shadow-inner transition hover:border-[#8faaf3] hover:bg-[#e0fbfc]/60 sm:flex-row sm:items-center sm:p-5">
                            <input id="proof" type="file" name="proof" accept="image/*" required class="sr-only" onchange="document.getElementById('proof-file-name').textContent = this.files?.[0]?.name || 'Belum ada gambar dipilih'">
                            <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-[1.5rem] border-4 border-white bg-white text-[#5a76c8] shadow-lg shadow-[#bde0fe]/25 transition group-hover:scale-105">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16.5V18a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-1.5M8 10l4-4m0 0 4 4m-4-4v11" />
                                </svg>
                            </span>
                            <span class="min-w-0 flex-1 text-left">
                                <span class="block text-sm font-black text-[#2b3a67]">Upload screenshot atau foto bukti</span>
                                <span class="mt-1 block text-xs font-bold leading-relaxed text-[#8faaf3]">Kalau tidak bisa screenshot, foto layar dari device lain juga boleh.</span>
                                <span id="proof-file-name" class="mt-3 inline-flex max-w-full rounded-full border-2 border-white bg-white px-4 py-2 text-[10px] font-black uppercase tracking-widest text-[#5a76c8] shadow-sm">
                                    Belum ada gambar dipilih
                                </span>
                            </span>
                        </label>
                    </section>

                    @if($needsRecord)
                        <section class="rounded-[2.5rem] border-4 border-white bg-white/95 p-5 shadow-xl shadow-[#bde0fe]/25 md:p-6">
                            <label class="mb-3 ml-1 block text-xs font-black uppercase tracking-widest text-[#5a76c8]">
                                {{ $selectedKey === 'deposit' ? 'Pilih Mutasi Saldo / Deposit' : 'Pilih Riwayat Pesanan' }}
                            </label>

                            @if($records->isEmpty())
                                <div class="rounded-[2rem] border-4 border-dashed border-[#bde0fe] bg-[#f4f9ff] p-6 text-center">
                                    <p class="text-sm font-black text-[#5a76c8]">Belum ada data terkait yang bisa dipilih.</p>
                                    <p class="mt-2 text-xs font-bold text-[#8faaf3]">Kamu tetap bisa mengirim laporan dengan menjelaskan kendalanya.</p>
                                </div>
                            @else
                                <div class="max-h-[28rem] space-y-3 overflow-y-auto pr-1">
                                    @foreach($records as $record)
                                        @php
                                            $recordInputId = 'report-record-' . $loop->index;
                                        @endphp
                                        <label for="{{ $recordInputId }}" class="block cursor-pointer" data-report-record-option onclick="selectReportRecord(this)">
                                            <input id="{{ $recordInputId }}" type="radio" name="record_id" value="{{ $record['id'] }}" class="sr-only" data-report-record-input onchange="selectReportRecord(this.closest('[data-report-record-option]'))" @checked($oldRecordId === $record['id'])>
                                            <span data-report-record-card class="flex items-start gap-3 rounded-[1.75rem] border-4 {{ $oldRecordId === $record['id'] ? 'border-[#25D366] bg-[#e9fff0]' : 'border-white bg-[#f8fbff]' }} px-5 py-4 text-left shadow-sm transition">
                                                <span data-report-record-dot class="mt-1 flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 {{ $oldRecordId === $record['id'] ? 'border-[#25D366] bg-[#25D366]' : 'border-[#bde0fe] bg-white' }}">
                                                    <span class="h-2 w-2 rounded-full bg-white"></span>
                                                </span>
                                                <span class="min-w-0 flex-1">
                                                    <span class="block text-xs font-black text-[#5a76c8]">{{ $record['invoice'] }}</span>
                                                    <span class="mt-1 block text-base font-black leading-tight text-[#2b3a67]">{{ $record['title'] }}</span>
                                                    <span class="mt-2 block text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">{{ $record['meta'] }}</span>
                                                </span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </section>
                    @endif
                </div>

                <aside class="space-y-6">
                    <section class="rounded-[2.5rem] border-4 border-white bg-white/95 p-5 shadow-xl shadow-[#bde0fe]/25 md:p-6">
                        <p class="mb-3 ml-1 text-xs font-black uppercase tracking-widest text-[#5a76c8]">Alur Laporan</p>
                        <div class="space-y-3 rounded-[2rem] border-4 border-white bg-[#f4f9ff] p-5 text-sm font-bold leading-relaxed text-[#5a76c8] shadow-inner">
                            <p>1. Pilih masalah yang ingin dilaporkan.</p>
                            <p>2. Ceritakan kendalanya dengan singkat dan jelas.</p>
                            <p>3. Upload bukti agar admin lebih cepat mengecek.</p>
                            @if($needsRecord)
                                <p>4. Pilih deposit atau pesanan yang bermasalah jika ada.</p>
                                <p>5. Tekan Kirim Laporan. WhatsApp admin akan terbuka otomatis.</p>
                            @else
                                <p>4. Tekan Kirim Laporan. WhatsApp admin akan terbuka otomatis.</p>
                            @endif
                            <p>Pesan WhatsApp sudah berisi detail laporan dan link bukti. Setelah terkirim, tinggal tunggu balasan admin.</p>
                        </div>
                    </section>

                    <button type="submit"
                            data-report-submit
                            data-submit-label="Mengirim Laporan..."
                            class="w-full rounded-full border-4 border-white bg-[#25D366] px-6 py-4 text-sm font-black text-white shadow-xl shadow-[#25D366]/25 transition hover:bg-[#1fb458] active:scale-95">
                        Kirim Laporan
                    </button>
                </aside>
            </div>
        </form>
    @endif
</div>

<script>
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
    }

    function lockAdminReportSubmit(form) {
        if (!form || form.dataset.submitting === 'true') {
            return false;
        }

        form.dataset.submitting = 'true';

        const button = form.querySelector('[data-report-submit]');
        if (button) {
            button.disabled = true;
            button.dataset.originalText = button.textContent.trim();
            button.textContent = button.dataset.submitLabel || 'Mengirim Laporan...';
            button.classList.add('cursor-not-allowed', 'opacity-70');
        }

        return true;
    }
</script>
@endsection
