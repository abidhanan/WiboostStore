@extends('layouts.admin')
@section('title', 'Jawaban Formulir')
@section('admin_header_subtitle', 'Lihat data jawaban yang masuk dari link publik.')

@section('content')
<div class="wiboost-font pb-12 relative z-10 w-full min-w-0">
    @include('admin.partials.form-header', [
        'backUrl' => route('admin.forms.index'),
        'badge' => 'Jawaban',
        'title' => $form->title,
        'description' => $form->description ?: 'Formulir publik tanpa login.',
    ])

    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-[2rem] border-4 border-white bg-[#e6fff7] px-5 py-4 text-sm font-black text-emerald-500 shadow-lg shadow-emerald-100/50 sm:mb-8 sm:px-6 sm:text-base">
            @include('partials.wiboost-icon', ['name' => 'success', 'class' => 'h-6 w-6 shrink-0'])
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <div class="mb-8 grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <section class="min-w-0 rounded-[2rem] border-4 border-white bg-white/95 p-5 shadow-xl shadow-[#bde0fe]/25 sm:rounded-[2.5rem] sm:p-6">
            <p class="text-[10px] font-black uppercase tracking-[0.24em] text-[#8faaf3]">Tautan Publik</p>
            <div class="mt-3 flex min-w-0 flex-col gap-2 sm:flex-row sm:items-center">
                <input type="text" readonly value="{{ $form->publicUrl() }}" class="min-w-0 flex-1 truncate rounded-[1rem] border-2 border-white bg-[#f4f9ff] px-4 py-3 text-xs font-black text-[#5a76c8] shadow-inner">
                <button type="button" data-copy-form-link="{{ $form->publicUrl() }}" class="inline-flex w-[8.75rem] shrink-0 items-center justify-center gap-2 rounded-[1rem] border-2 border-white bg-[#5a76c8] px-4 py-3 text-xs font-black text-white shadow-md">
                    @include('partials.wiboost-icon', ['name' => 'note', 'class' => 'h-4 w-4'])
                    <span data-copy-label>Salin Tautan</span>
                </button>
            </div>
        </section>

        <section class="grid grid-cols-3 items-center gap-3 rounded-[2rem] border-4 border-white bg-white/95 p-5 text-center shadow-xl shadow-[#bde0fe]/25 sm:rounded-[2.5rem]">
            <div class="flex min-h-[5rem] flex-col items-center justify-center">
                <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Status</p>
                <p class="mt-2 text-sm font-black {{ $form->is_active ? 'text-[#4bc6b9]' : 'text-[#ff6b6b]' }}">{{ $form->is_active ? 'Aktif' : 'Off' }}</p>
            </div>
            <div class="flex min-h-[5rem] flex-col items-center justify-center">
                <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Pertanyaan</p>
                <p class="mt-2 text-2xl font-black text-[#5a76c8]">{{ $form->fieldCount() }}</p>
            </div>
            <div class="flex min-h-[5rem] flex-col items-center justify-center">
                <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Jawaban</p>
                <p class="mt-2 text-2xl font-black text-[#4bc6b9]">{{ $submissions->total() }}</p>
            </div>
        </section>
    </div>

    <section class="mb-8 rounded-[2rem] border-4 border-white bg-white/95 p-5 shadow-xl shadow-[#bde0fe]/25 sm:rounded-[2.5rem] sm:p-6">
        <div class="mb-5 flex flex-col gap-2 border-b-4 border-dashed border-[#f4f9ff] pb-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-[#8faaf3]">Grafik</p>
                <h2 class="mt-1 text-xl font-black text-[#2b3a67] sm:text-2xl">Hasil Jawaban</h2>
            </div>
            <span class="inline-flex w-fit items-center gap-2 rounded-full bg-[#e0fbfc] px-4 py-2 text-xs font-black text-[#4bc6b9]">
                @include('partials.wiboost-icon', ['name' => 'chart', 'class' => 'h-4 w-4'])
                {{ $submissions->total() }} jawaban
            </span>
        </div>

        @if(! empty($answerCharts))
            <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                @foreach($answerCharts as $chart)
                    <article class="min-w-0 rounded-[1.8rem] border-4 border-[#f0f5ff] bg-[#f8faff] p-4 shadow-inner sm:p-5">
                        @php
                            $visual = $chart['visual'] ?? 'distribution';
                            $icon = match ($visual) {
                                'number' => 'cash',
                                'date' => 'calendar',
                                'file' => 'note',
                                'list' => 'receipt',
                                default => 'chart',
                            };
                        @endphp
                        <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between sm:gap-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-[1rem] border-2 border-white bg-white text-[#5a76c8] shadow-sm">
                                    @include('partials.wiboost-icon', ['name' => $icon, 'class' => 'h-5 w-5'])
                                </span>
                                <h3 class="min-w-0 text-base font-black leading-snug text-[#2b3a67]">{{ $chart['label'] }}</h3>
                            </div>
                            <span class="w-fit shrink-0 rounded-full bg-white px-3 py-1 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">{{ $chart['total'] }} {{ $chart['total_label'] ?? 'jawaban' }}</span>
                        </div>

                        @if($visual === 'distribution')
                            <div class="space-y-3">
                                @foreach($chart['options'] as $option)
                                    <div>
                                        <div class="mb-1.5 flex items-center justify-between gap-3 text-xs font-black">
                                            <span class="min-w-0 truncate text-[#5a76c8]" title="{{ $option['title'] ?? $option['label'] }}">{{ $option['label'] }}</span>
                                            <span class="shrink-0 text-[#2b3a67]">{{ $option['count'] }} <span class="text-[#8faaf3]">({{ rtrim(rtrim(number_format((float) $option['percent'], 1), '0'), '.') }}%)</span></span>
                                        </div>
                                        <div class="h-3 overflow-hidden rounded-full border-2 border-white bg-white shadow-inner">
                                            <span class="block h-full rounded-full bg-gradient-to-r from-[#5a76c8] to-[#4bc6b9]" style="width: {{ max(0, min(100, (float) $option['percent'])) }}%"></span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @elseif($visual === 'number')
                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                                @foreach($chart['stats'] as $stat)
                                    <div class="rounded-[1.2rem] border-2 border-white bg-white px-4 py-3 text-center shadow-sm">
                                        <p class="text-[9px] font-black uppercase tracking-widest text-[#8faaf3]">{{ $stat['label'] }}</p>
                                        <p class="mt-1 truncate text-lg font-black text-[#5a76c8]" title="{{ $stat['value'] }}">{{ $stat['value'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                            @if(! empty($chart['entries']))
                                <div class="mt-4 max-h-52 space-y-2 overflow-y-auto pr-1">
                                    @foreach($chart['entries'] as $entry)
                                        <div class="flex items-center justify-between gap-3 rounded-[1.2rem] border-2 border-white bg-white px-4 py-3 shadow-sm">
                                            <p class="min-w-0 truncate text-sm font-black text-[#2b3a67]" title="{{ $entry['title'] }}">{{ $entry['value'] }}</p>
                                            @if(! empty($entry['submitted_at']))
                                                <p class="shrink-0 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">{{ $entry['submitted_at'] }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            @if(($chart['empty_count'] ?? 0) > 0)
                                <p class="mt-3 text-xs font-black text-[#8faaf3]">{{ $chart['empty_count'] }} jawaban tidak diisi.</p>
                            @endif
                        @elseif($visual === 'date')
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                @foreach($chart['dates'] as $date)
                                    <div class="flex items-center justify-between gap-3 rounded-[1.2rem] border-2 border-white bg-white px-4 py-3 shadow-sm">
                                        <span class="min-w-0 truncate text-sm font-black text-[#5a76c8]">{{ $date['label'] }}</span>
                                        <span class="shrink-0 rounded-full bg-[#e0fbfc] px-3 py-1 text-xs font-black text-[#4bc6b9]">{{ $date['count'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            @if(! empty($chart['entries']))
                                @if($visual === 'file')
                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        @foreach($chart['entries'] as $entry)
                                            <a href="{{ $entry['url'] ?: '#' }}" target="_blank" rel="noopener noreferrer" class="group flex min-w-0 items-center gap-3 rounded-[1.35rem] border-2 border-white bg-white px-4 py-3 shadow-sm transition-transform hover:-translate-y-0.5 {{ empty($entry['url']) ? 'pointer-events-none' : '' }}">
                                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-[1rem] bg-[#e0fbfc] text-[#4bc6b9] shadow-inner">
                                                    @include('partials.wiboost-icon', ['name' => 'note', 'class' => 'h-5 w-5'])
                                                </span>
                                                <span class="min-w-0">
                                                    <span class="block truncate text-sm font-black text-[#2b3a67]" title="{{ $entry['title'] }}">{{ $entry['value'] }}</span>
                                                    @if(! empty($entry['submitted_at']))
                                                        <span class="mt-1 block text-[9px] font-black uppercase tracking-widest text-[#8faaf3]">{{ $entry['submitted_at'] }}</span>
                                                    @endif
                                                </span>
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="max-h-72 space-y-2 overflow-y-auto pr-1">
                                        @foreach($chart['entries'] as $entry)
                                            <div class="rounded-[1.2rem] border-2 border-white bg-white px-4 py-3 shadow-sm">
                                                <p class="break-words text-sm font-black leading-relaxed text-[#2b3a67]" title="{{ $entry['title'] }}">{{ $entry['value'] }}</p>
                                                @if(! empty($entry['submitted_at']))
                                                    <p class="mt-1 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">{{ $entry['submitted_at'] }}</p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @else
                                <div class="rounded-[1.2rem] border-2 border-dashed border-[#bde0fe] bg-white/70 px-4 py-5 text-center">
                                    <p class="text-sm font-black text-[#8faaf3]">Belum ada jawaban terisi.</p>
                                </div>
                            @endif
                            @if(($chart['empty_count'] ?? 0) > 0)
                                <p class="mt-3 text-xs font-black text-[#8faaf3]">{{ $chart['empty_count'] }} jawaban tidak diisi.</p>
                            @endif
                        @endif
                    </article>
                @endforeach
            </div>
        @else
            <div class="rounded-[1.8rem] border-4 border-dashed border-[#bde0fe] bg-[#f8faff] px-5 py-10 text-center">
                @include('partials.wiboost-icon', ['name' => 'chart', 'class' => 'mx-auto h-12 w-12 text-[#8faaf3] opacity-70'])
                <p class="mt-3 text-lg font-black text-[#5a76c8]">Belum ada grafik jawaban.</p>
                <p class="mt-1 text-sm font-bold text-[#8faaf3]">Grafik akan muncul setelah formulir mulai diisi.</p>
            </div>
        @endif
    </section>

    <div class="mb-8 rounded-[2rem] border-4 border-white bg-white/90 p-3 shadow-xl shadow-[#bde0fe]/25 backdrop-blur-sm sm:rounded-[2.5rem] sm:p-4">
        <form action="{{ route('admin.forms.show', $form) }}" method="GET" class="flex w-full items-center gap-2 sm:gap-3">
            <input type="text" name="search" value="{{ $search ?? request('search') }}"
                class="min-w-0 flex-1 truncate rounded-[1.2rem] border-4 border-white bg-[#f4f9ff] px-5 py-3 text-xs font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe] sm:rounded-[1.5rem] sm:px-6 sm:py-4 sm:text-sm"
                >
            <button type="submit" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] border-4 border-white bg-[#5a76c8] text-white shadow-md shadow-[#5a76c8]/25 transition-transform hover:bg-[#4760a9] active:scale-95 sm:rounded-[1.2rem]" title="Cari Jawaban">
                @include('partials.wiboost-icon', ['name' => 'search', 'class' => 'h-5 w-5'])
            </button>
            @if(request('search'))
                <a href="{{ route('admin.forms.show', $form) }}" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] border-4 border-white bg-[#ffe5e5] text-[#ff6b6b] shadow-md shadow-[#ff6b6b]/20 transition-transform hover:bg-[#ffcccc] active:scale-95 sm:rounded-[1.2rem]" title="Reset Pencarian">
                    @include('partials.wiboost-icon', ['name' => 'x', 'class' => 'h-5 w-5'])
                </a>
            @endif
        </form>
    </div>

    <div class="space-y-5">
        @forelse($submissions as $submission)
            <article class="rounded-[2rem] border-4 border-white bg-white/95 p-5 shadow-xl shadow-[#bde0fe]/25 sm:rounded-[2.5rem] sm:p-6">
                <div class="mb-5 flex flex-col gap-2 border-b-4 border-dashed border-[#f4f9ff] pb-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Masuk Pada</p>
                        <p class="mt-1 text-base font-black text-[#2b3a67]">{{ $submission->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</p>
                    </div>
                    <p class="rounded-full bg-[#f4f9ff] px-4 py-2 text-xs font-black text-[#5a76c8]">IP: {{ $submission->ip_address ?: '-' }}</p>
                </div>

                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    @foreach(($submission->answers ?? []) as $answer)
                        @php
                            $value = $answer['value'] ?? null;
                            $isFile = ($answer['type'] ?? '') === 'file' && is_array($value);
                            $isArray = is_array($value) && ! $isFile;
                        @endphp
                        <div class="min-w-0 rounded-[1.4rem] border-2 border-white bg-[#f8fbff] p-4 shadow-inner">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">{{ $answer['label'] ?? 'Jawaban' }}</p>
                            @if($isFile)
                                <a href="{{ $value['url'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-flex max-w-full items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-black text-[#5a76c8] shadow-sm">
                                    @include('partials.wiboost-icon', ['name' => 'note', 'class' => 'h-4 w-4 shrink-0'])
                                    <span class="truncate">{{ $value['name'] ?? 'File jawaban' }}</span>
                                </a>
                            @else
                                <p class="mt-2 break-words text-sm font-black leading-relaxed text-[#2b3a67]">{{ $isArray ? implode(', ', $value) : ($value !== '' && $value !== null ? $value : '-') }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </article>
        @empty
            <div class="rounded-[2rem] border-4 border-dashed border-[#bde0fe] bg-white/90 py-16 text-center shadow-lg shadow-[#bde0fe]/20 sm:rounded-[2.5rem] sm:py-20">
                @include('partials.wiboost-icon', ['name' => 'receipt', 'class' => 'mx-auto h-14 w-14 text-[#8faaf3] opacity-60 sm:h-16 sm:w-16'])
                <p class="mt-4 text-xl font-black text-[#5a76c8] sm:text-2xl">Belum ada jawaban.</p>
                <p class="mt-2 text-sm font-bold text-[#8faaf3] sm:text-base">Bagikan link publik agar orang lain mulai mengisi.</p>
            </div>
        @endforelse
    </div>

    @if($submissions->hasPages())
        <div class="mt-6 w-full min-w-0 sm:mt-8">
            {{ $submissions->links() }}
        </div>
    @endif
</div>
<script>
    (() => {
        const fallbackCopy = (value) => {
            const textarea = document.createElement('textarea');
            textarea.value = value;
            textarea.setAttribute('readonly', '');
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            textarea.setSelectionRange(0, value.length);
            const copied = document.execCommand('copy');
            textarea.remove();
            return copied;
        };

        document.querySelectorAll('[data-copy-form-link]').forEach((button) => {
            button.addEventListener('click', async (event) => {
                event.preventDefault();
                event.stopPropagation();
                const value = button.dataset.copyFormLink || '';
                const label = button.querySelector('[data-copy-label]');
                let copied = false;

                try {
                    if (navigator.clipboard && window.isSecureContext) {
                        await navigator.clipboard.writeText(value);
                        copied = true;
                    } else {
                        copied = fallbackCopy(value);
                    }
                } catch (error) {
                    copied = fallbackCopy(value);
                }

                if (!copied) {
                    window.prompt('Salin tautan formulir:', value);
                    return;
                }

                if (label) label.textContent = 'Tersalin';
                window.setTimeout(() => { if (label) label.textContent = 'Salin Tautan'; }, 1400);
            });
        });
    })();
</script>
@endsection
