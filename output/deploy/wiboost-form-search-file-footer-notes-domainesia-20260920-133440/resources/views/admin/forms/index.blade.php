@extends('layouts.admin')
@section('title', 'Manajemen Formulir')
@section('admin_header_subtitle', 'Kelola form publik dan benefit aplikasi premium.')

@section('content')
@php
    $searchValue = $search ?? request('search');
    $activeFormFilter = $formFilter ?? request('form_filter', 'all');
    $formFilters = [
        'all' => ['label' => 'Semua'],
        'active' => ['label' => 'Aktif'],
        'inactive' => ['label' => 'Nonaktif'],
    ];
@endphp

<style>
    body.wiboost-admin-shell main .wiboost-form-action-button,
    body.wiboost-admin-shell main .wiboost-form-action-button:hover,
    body.wiboost-admin-shell main .wiboost-form-action-button:focus,
    body.wiboost-admin-shell main .wiboost-form-action-button:active {
        opacity: 1 !important;
        filter: none !important;
        transform: none !important;
        transition: none !important;
    }

    body.wiboost-admin-shell main .wiboost-form-action-button svg {
        color: inherit !important;
    }

    body.wiboost-admin-shell main a.wiboost-form-action-button.wiboost-form-action-button--view,
    body.wiboost-admin-shell main a.wiboost-form-action-button.wiboost-form-action-button--view:hover,
    body.wiboost-admin-shell main a.wiboost-form-action-button.wiboost-form-action-button--view:focus,
    body.wiboost-admin-shell main a.wiboost-form-action-button.wiboost-form-action-button--view:active {
        background: #e0fbfc !important;
        color: #4bc6b9 !important;
        box-shadow: 0 10px 22px rgba(75, 198, 185, .2) !important;
    }

    body.wiboost-admin-shell main a.wiboost-form-action-button.wiboost-form-action-button--edit,
    body.wiboost-admin-shell main a.wiboost-form-action-button.wiboost-form-action-button--edit:hover,
    body.wiboost-admin-shell main a.wiboost-form-action-button.wiboost-form-action-button--edit:focus,
    body.wiboost-admin-shell main a.wiboost-form-action-button.wiboost-form-action-button--edit:active {
        background: linear-gradient(135deg, #ffd85a 0%, #fbbf24 48%, #f59e0b 100%) !important;
        color: #ffffff !important;
        box-shadow: 0 12px 24px rgba(245, 158, 11, .32) !important;
    }

    body.wiboost-admin-shell main button.wiboost-form-action-button.wiboost-form-action-button--delete,
    body.wiboost-admin-shell main button.wiboost-form-action-button.wiboost-form-action-button--delete:hover,
    body.wiboost-admin-shell main button.wiboost-form-action-button.wiboost-form-action-button--delete:focus,
    body.wiboost-admin-shell main button.wiboost-form-action-button.wiboost-form-action-button--delete:active {
        background: linear-gradient(135deg, #ff7a7a 0%, #ff4d4d 48%, #f43f5e 100%) !important;
        color: #ffffff !important;
        box-shadow: 0 12px 24px rgba(244, 63, 94, .32) !important;
    }
</style>

<div class="wiboost-font pb-12 relative z-10 w-full min-w-0">
    <div class="relative z-10 mb-10 flex w-full min-w-0 flex-col gap-3 rounded-[2rem] border-4 border-white bg-white/90 p-3 shadow-xl shadow-[#bde0fe]/25 backdrop-blur-sm sm:mb-12 sm:rounded-[2.5rem] sm:p-4 lg:flex-row lg:items-center">
        <div class="min-w-0 flex-1">
            <form action="{{ route('admin.forms.index') }}" method="GET" class="flex w-full items-center gap-2 sm:gap-3">
                @if($activeFormFilter !== 'all')
                    <input type="hidden" name="form_filter" value="{{ $activeFormFilter }}">
                @endif
                <input type="text" name="search" value="{{ $searchValue }}"
                    placeholder="Cari judul, slug, atau deskripsi..."
                    class="min-w-0 flex-1 truncate rounded-[1.2rem] border-4 border-white bg-[#f4f9ff] px-5 py-3 text-xs font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe] sm:rounded-[1.5rem] sm:px-6 sm:py-4 sm:text-sm">
                <button type="submit" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] border-4 border-white bg-[#5a76c8] text-white shadow-md shadow-[#5a76c8]/25 transition-transform hover:bg-[#4760a9] active:scale-95 sm:rounded-[1.2rem]" title="Cari Formulir">
                    @include('partials.wiboost-icon', ['name' => 'search', 'class' => 'h-5 w-5'])
                </button>
                @if($searchValue)
                    <a href="{{ route('admin.forms.index', array_filter(['form_filter' => $activeFormFilter === 'all' ? null : $activeFormFilter])) }}" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] border-4 border-white bg-[#ffe5e5] text-[#ff6b6b] shadow-md shadow-[#ff6b6b]/20 transition-transform hover:bg-[#ffcccc] active:scale-95 sm:rounded-[1.2rem]" title="Reset Pencarian">
                        @include('partials.wiboost-icon', ['name' => 'x', 'class' => 'h-5 w-5'])
                    </a>
                @endif
            </form>
        </div>

        <a href="{{ route('admin.forms.create') }}" class="flex w-full min-w-0 shrink-0 items-center justify-center gap-2 rounded-[1rem] border-4 border-white bg-[#5a76c8] px-5 py-3 text-sm font-black text-white shadow-md shadow-[#5a76c8]/25 transition-transform hover:bg-[#4760a9] active:scale-95 sm:rounded-[1.2rem] lg:w-auto lg:min-w-[190px] lg:px-7">
            @include('partials.wiboost-icon', ['name' => 'plus', 'class' => 'h-5 w-5'])
            Tambah Formulir
        </a>
    </div>

    @include('admin.partials.filter-tabs', [
        'filters' => $formFilters,
        'activeFilter' => $activeFormFilter,
        'filterParam' => 'form_filter',
        'filterRoute' => 'admin.forms.index',
        'filterQuery' => ['search' => $searchValue],
    ])

    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-[2rem] border-4 border-white bg-[#e6fff7] px-5 py-4 text-sm font-black text-emerald-500 shadow-lg shadow-emerald-100/50 sm:mb-8 sm:px-6 sm:text-base">
            @include('partials.wiboost-icon', ['name' => 'success', 'class' => 'h-6 w-6 shrink-0'])
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
        @forelse($forms as $form)
            @php
                $publicUrl = $form->publicUrl();
            @endphp
            <article onclick="openAdminUniversalDetail('admin-form-detail-{{ $form->id }}')" class="min-w-0 cursor-pointer rounded-[2rem] border-4 border-white bg-white/95 p-5 shadow-xl shadow-[#bde0fe]/25 transition-colors hover:border-[#bde0fe] sm:rounded-[2.5rem] sm:p-6">
                <div class="flex min-w-0 flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            <span class="inline-flex rounded-full border-2 border-white bg-[#e0fbfc] px-4 py-1 text-[10px] font-black uppercase tracking-widest text-[#4bc6b9] shadow-sm">
                                {{ $form->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                            @if($form->benefit_enabled)
                                <span class="inline-flex rounded-full border-2 border-white bg-[#fff7d6] px-4 py-1 text-[10px] font-black uppercase tracking-widest text-[#d99b16] shadow-sm">
                                    Benefit
                                </span>
                            @endif
                        </div>
                        <h2 class="truncate text-2xl font-black text-[#2b3a67]">{{ $form->title }}</h2>
                        @if($form->description)
                            <p class="mt-3 line-clamp-2 text-sm font-bold leading-relaxed text-[#5a76c8]">{{ $form->description }}</p>
                        @endif
                    </div>

                    <div class="flex w-full shrink-0 items-center justify-end gap-2 sm:w-auto">
                        <a href="{{ route('admin.forms.show', $form) }}" onclick="event.stopPropagation()" class="wiboost-form-action-button wiboost-form-action-button--view flex h-10 w-10 items-center justify-center rounded-[1rem] border-2 border-white" aria-label="Lihat Jawaban">
                            @include('partials.wiboost-icon', ['name' => 'receipt', 'class' => 'h-5 w-5'])
                        </a>
                        <a href="{{ route('admin.forms.edit', $form) }}" onclick="event.stopPropagation()" class="wiboost-form-action-button wiboost-form-action-button--edit flex h-10 w-10 items-center justify-center rounded-[1rem] border-2 border-white" aria-label="Edit Formulir">
                            @include('partials.wiboost-icon', ['name' => 'edit', 'class' => 'h-5 w-5 text-white'])
                        </a>
                        <form action="{{ route('admin.forms.destroy', $form) }}" method="POST" onclick="event.stopPropagation()" onsubmit="return confirm('Hapus formulir dan semua jawabannya?');" class="m-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="wiboost-form-action-button wiboost-form-action-button--delete flex h-10 w-10 items-center justify-center rounded-[1rem] border-2 border-white" aria-label="Hapus Formulir">
                                @include('partials.wiboost-icon', ['name' => 'trash', 'class' => 'h-5 w-5 text-white'])
                            </button>
                        </form>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <div class="rounded-[1.3rem] border-2 border-white bg-[#f4f9ff] p-4 text-center shadow-inner">
                        <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Pertanyaan</p>
                        <p class="mt-1 text-2xl font-black text-[#5a76c8]">{{ $form->fieldCount() }}</p>
                    </div>
                    <div class="rounded-[1.3rem] border-2 border-white bg-[#f4f9ff] p-4 text-center shadow-inner">
                        <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Jawaban</p>
                        <p class="mt-1 text-2xl font-black text-[#4bc6b9]">{{ $form->submissions_count }}</p>
                    </div>
                    <div class="col-span-2 rounded-[1.3rem] border-2 border-white bg-[#f4f9ff] p-4 shadow-inner sm:col-span-1">
                        <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Dibuat</p>
                        <p class="mt-1 truncate text-sm font-black text-[#2b3a67]">{{ $form->creator?->short_name ?? 'Admin' }}</p>
                    </div>
                </div>

                <div class="mt-4 flex min-w-0 flex-col gap-2 rounded-[1.5rem] border-2 border-white bg-[#f8fbff] p-3 sm:flex-row sm:items-center">
                    <input type="text" readonly onclick="event.stopPropagation()" value="{{ $publicUrl }}" class="min-w-0 flex-1 truncate rounded-[1rem] border-2 border-white bg-white px-4 py-3 text-xs font-black text-[#5a76c8] shadow-inner">
                    <button type="button" onclick="event.stopPropagation()" data-copy-form-link="{{ $publicUrl }}" class="inline-flex w-[8.75rem] shrink-0 items-center justify-center gap-2 rounded-[1rem] border-2 border-white bg-[#5a76c8] px-4 py-3 text-xs font-black text-white shadow-md">
                        @include('partials.wiboost-icon', ['name' => 'note', 'class' => 'h-4 w-4'])
                        <span data-copy-label>Salin Tautan</span>
                    </button>
                </div>
            </article>
            <div id="admin-form-detail-{{ $form->id }}" class="admin-detail-modal fixed inset-0 z-[9999] flex items-center justify-center opacity-0 pointer-events-none transition-all duration-200">
                <div class="absolute inset-0" onclick="closeAdminUniversalDetail('admin-form-detail-{{ $form->id }}')"></div>
                <div class="admin-detail-card relative flex w-full flex-col rounded-[1.75rem] border-4 border-white bg-white p-4 shadow-2xl shadow-[#2b3a67]/20 transform scale-95 transition-transform duration-200 sm:p-5">
                    <div class="admin-detail-header mb-3.5 flex items-start justify-between gap-3 p-3.5">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Detail Formulir</p>
                            <h3 class="mt-1 break-words text-xl font-black text-[#2b3a67]">{{ $form->title }}</h3>
                            <p class="mt-1 text-xs font-bold text-[#8faaf3]">{{ $form->created_at?->format('d M Y, H:i') ?? '-' }}</p>
                        </div>
                        <button type="button" onclick="closeAdminUniversalDetail('admin-form-detail-{{ $form->id }}')" class="admin-detail-close flex h-11 w-11 shrink-0 items-center justify-center border-2 border-white">
                            @include('partials.wiboost-icon', ['name' => 'x', 'class' => 'h-4 w-4'])
                        </button>
                    </div>

                    <div class="admin-detail-body">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="admin-detail-panel">
                                <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Status</p>
                                <span class="mt-2 inline-flex rounded-full border-2 border-white px-4 py-1.5 text-[10px] font-black uppercase tracking-widest shadow-sm {{ $form->is_active ? 'bg-[#e0fbfc] text-[#4bc6b9]' : 'bg-[#ffe5e5] text-[#ff6b6b]' }}">
                                    {{ $form->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                            <div class="admin-detail-panel">
                                <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Benefit</p>
                                <span class="mt-2 inline-flex rounded-full border-2 border-white px-4 py-1.5 text-[10px] font-black uppercase tracking-widest shadow-sm {{ $form->benefit_enabled ? 'bg-[#fff7d6] text-[#d99b16]' : 'bg-[#f4f9ff] text-[#8faaf3]' }}">
                                    {{ $form->benefit_enabled ? 'Benefit' : 'Tanpa Benefit' }}
                                </span>
                            </div>
                            <div class="admin-detail-panel md:col-span-2">
                                <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Deskripsi</p>
                                <p class="mt-1 text-sm font-bold text-[#2b3a67]">{{ $form->description ?: '-' }}</p>
                            </div>
                            <div class="admin-detail-panel">
                                <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Pertanyaan</p>
                                <p class="mt-1 text-2xl font-black text-[#5a76c8]">{{ $form->fieldCount() }}</p>
                            </div>
                            <div class="admin-detail-panel">
                                <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Jawaban</p>
                                <p class="mt-1 text-2xl font-black text-[#4bc6b9]">{{ $form->submissions_count }}</p>
                            </div>
                            <div class="admin-detail-panel md:col-span-2">
                                <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Tautan Publik</p>
                                <p class="mt-1 break-all text-sm font-black text-[#5a76c8]">{{ $publicUrl }}</p>
                            </div>
                            <div class="admin-detail-panel admin-detail-panel-soft md:col-span-2">
                                <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Aksi</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <a href="{{ route('admin.forms.show', $form) }}" class="rounded-full border-2 border-white bg-[#e0fbfc] px-4 py-2 text-[10px] font-black uppercase tracking-widest text-[#4bc6b9] shadow-sm">Lihat Jawaban</a>
                                    <a href="{{ route('admin.forms.edit', $form) }}" class="rounded-full border-2 border-white bg-[#fbbf24] px-4 py-2 text-[10px] font-black uppercase tracking-widest text-white shadow-sm">Edit Formulir</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-[2rem] border-4 border-dashed border-[#bde0fe] bg-white/90 py-16 text-center shadow-lg shadow-[#bde0fe]/20 sm:rounded-[2.5rem] sm:py-20">
                @include('partials.wiboost-icon', ['name' => 'note', 'class' => 'mx-auto h-14 w-14 text-[#8faaf3] opacity-60 sm:h-16 sm:w-16'])
                <p class="mt-4 text-xl font-black text-[#5a76c8] sm:text-2xl">Belum ada formulir.</p>
                <p class="mt-2 text-sm font-bold text-[#8faaf3] sm:text-base">Tambah formulir pertama untuk dibagikan tanpa login.</p>
            </div>
        @endforelse
    </div>

    @if(isset($forms) && $forms->hasPages())
        <div class="mt-6 w-full min-w-0 sm:mt-8">
            {{ $forms->links() }}
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
