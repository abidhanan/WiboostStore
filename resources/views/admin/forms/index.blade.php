@extends('layouts.admin')
@section('title', 'Manajemen Formulir')
@section('admin_header_subtitle', 'Kelola form publik dan benefit aplikasi premium.')

@section('content')
@php
    $searchValue = $search ?? request('search');
    $activeFormFilter = $formFilter ?? request('form_filter', 'all');
    $indexReturnUrl = request()->fullUrl();
    $formFilters = [
        'all' => ['label' => 'Semua'],
        'active' => ['label' => 'Aktif'],
        'inactive' => ['label' => 'Nonaktif'],
    ];
@endphp

<style>
    .forms-table-scroll::-webkit-scrollbar { height: 8px; }
    .forms-table-scroll::-webkit-scrollbar-track { background: #f0f5ff; border-radius: 10px; }
    .forms-table-scroll::-webkit-scrollbar-thumb { background: #bde0fe; border: 2px solid #f0f5ff; border-radius: 10px; }
    .forms-table-scroll::-webkit-scrollbar-thumb:hover { background: #8faaf3; }
    .admin-detail-link-no-scrollbar { scrollbar-width: none; -ms-overflow-style: none; }
    .admin-detail-link-no-scrollbar::-webkit-scrollbar { display: none; }
    body.wiboost-admin-shell .wiboost-form-action-button,
    body.wiboost-admin-shell .wiboost-form-action-button:hover,
    body.wiboost-admin-shell .wiboost-form-action-button:focus,
    body.wiboost-admin-shell .wiboost-form-action-button:active {
        opacity: 1 !important;
        filter: none !important;
        transform: none !important;
        transition: none !important;
    }
    body.wiboost-admin-shell .wiboost-form-action-button svg { color: inherit !important; }
    body.wiboost-admin-shell .wiboost-form-action-button--view { background: #e0fbfc !important; color: #4bc6b9 !important; box-shadow: 0 10px 22px rgba(75, 198, 185, .2) !important; }
    body.wiboost-admin-shell .wiboost-form-action-button--copy { background: #eef3ff !important; color: #5a76c8 !important; box-shadow: 0 10px 22px rgba(90, 118, 200, .18) !important; }
    body.wiboost-admin-shell .wiboost-form-action-button--copy [data-copy-success] { display: none; }
    body.wiboost-admin-shell .wiboost-form-action-button--copy.is-copied { animation: wiboost-copy-pop .42s ease; background: #e6fff7 !important; color: #20a68f !important; }
    body.wiboost-admin-shell .wiboost-form-action-button--copy.is-copied [data-copy-default] { display: none; }
    body.wiboost-admin-shell .wiboost-form-action-button--copy.is-copied [data-copy-success] { display: block; }
    @keyframes wiboost-copy-pop { 0% { transform: scale(1); } 45% { transform: scale(1.14); } 100% { transform: scale(1); } }
    body.wiboost-admin-shell .wiboost-form-action-button--edit { background: linear-gradient(135deg, #ffd85a 0%, #fbbf24 48%, #f59e0b 100%) !important; color: #fff !important; box-shadow: 0 12px 24px rgba(245, 158, 11, .32) !important; }
    body.wiboost-admin-shell .wiboost-form-action-button--delete { background: linear-gradient(135deg, #ff7a7a 0%, #ff4d4d 48%, #f43f5e 100%) !important; color: #fff !important; box-shadow: 0 12px 24px rgba(244, 63, 94, .32) !important; }
</style>

<div class="wiboost-font relative z-10 w-full min-w-0 pb-12">
    <div class="relative z-10 mb-10 w-full min-w-0 rounded-[2rem] border-4 border-white bg-white/90 p-3 shadow-xl shadow-[#bde0fe]/25 backdrop-blur-sm sm:mb-12 sm:rounded-[2.5rem] sm:p-4">
        <form action="{{ route('admin.forms.index') }}" method="GET" class="flex w-full flex-col gap-3">
            <div class="flex w-full min-w-0 flex-col gap-3 lg:flex-row lg:items-center">
                <div class="flex min-w-0 flex-1 items-center gap-2 sm:gap-3">
                @if($activeFormFilter !== 'all')
                    <input type="hidden" name="form_filter" value="{{ $activeFormFilter }}">
                @endif
                <input type="text" name="search" value="{{ $searchValue }}" placeholder="Cari judul, slug, atau deskripsi..." class="min-w-0 flex-1 truncate rounded-[1.2rem] border-4 border-white bg-[#f4f9ff] px-5 py-3 text-xs font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe] sm:rounded-[1.5rem] sm:px-6 sm:py-4 sm:text-sm">
                <button type="submit" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] border-4 border-white bg-[#5a76c8] text-white shadow-md shadow-[#5a76c8]/25 transition-transform hover:bg-[#4760a9] active:scale-95 sm:rounded-[1.2rem]" title="Cari Formulir">@include('partials.wiboost-icon', ['name' => 'search', 'class' => 'h-5 w-5'])</button>
                @if($searchValue)
                    <a href="{{ route('admin.forms.index', array_filter(['form_filter' => $activeFormFilter === 'all' ? null : $activeFormFilter])) }}" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] border-4 border-white bg-[#ffe5e5] text-[#ff6b6b] shadow-md shadow-[#ff6b6b]/20 transition-transform hover:bg-[#ffcccc] active:scale-95 sm:rounded-[1.2rem]" title="Reset Pencarian">@include('partials.wiboost-icon', ['name' => 'x', 'class' => 'h-5 w-5'])</a>
                @endif
                </div>
                <a href="{{ route('admin.forms.create', ['return_to' => $indexReturnUrl]) }}" class="flex h-12 w-full min-w-0 shrink-0 items-center justify-center gap-2 rounded-[1rem] border-4 border-white bg-[#5a76c8] px-5 text-sm font-black text-white shadow-md shadow-[#5a76c8]/25 transition-transform hover:bg-[#4760a9] active:scale-95 sm:rounded-[1.2rem] lg:w-auto lg:min-w-[190px] lg:px-7">
                    @include('partials.wiboost-icon', ['name' => 'plus', 'class' => 'h-5 w-5'])
                    Tambah Formulir
                </a>
            </div>
        </form>
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

    <div data-admin-table-card class="overflow-hidden rounded-[2rem] border-4 border-white bg-white/95 shadow-2xl shadow-[#bde0fe]/30 sm:rounded-[2.5rem]">
        <div data-admin-table-scroll class="forms-table-scroll w-full overflow-x-auto pb-2">
            <div class="inline-block min-w-full p-4 align-middle sm:p-6">
                <table class="w-full text-left" style="min-width:2260px;table-layout:fixed;">
                    <thead>
                        <tr>
                            <th style="width:175px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Waktu Dibuat</th>
                            <th style="width:240px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Judul</th>
                            <th style="width:210px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Slug</th>
                            <th style="width:320px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Deskripsi</th>
                            <th style="width:170px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Dibuat Oleh</th>
                            <th style="width:120px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-center text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Pertanyaan</th>
                            <th style="width:110px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-center text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Jawaban</th>
                            <th style="width:125px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Status</th>
                            <th style="width:100px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-center text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Benefit</th>
                            <th style="width:380px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Tautan</th>
                            <th style="width:190px;" class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-center text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                        @forelse($forms as $form)
                            @php $publicUrl = $form->publicUrl(); @endphp
                            <tr onclick="openAdminUniversalDetail('admin-form-detail-{{ $form->id }}')" class="cursor-pointer transition-colors hover:bg-[#f8faff]">
                                <td class="px-5 py-5 align-middle"><p class="whitespace-nowrap text-xs font-bold text-[#8faaf3]">{{ $form->created_at?->format('d M Y, H:i') ?? '-' }}</p></td>
                                <td class="px-5 py-5 align-middle"><p class="truncate text-sm font-black text-[#2b3a67]" title="{{ $form->title }}">{{ $form->title }}</p></td>
                                <td class="px-5 py-5 align-middle"><p class="truncate text-xs font-black text-[#5a76c8]" title="{{ $form->slug }}">{{ $form->slug }}</p></td>
                                <td class="px-5 py-5 align-middle"><p class="line-clamp-2 text-xs font-bold leading-relaxed text-[#5a76c8]" title="{{ $form->description }}">{{ $form->description ?: '-' }}</p></td>
                                <td class="px-5 py-5 align-middle"><p class="truncate text-xs font-black text-[#2b3a67]">{{ $form->creator?->short_name ?? 'Admin' }}</p></td>
                                <td class="px-5 py-5 text-center align-middle"><span class="font-black text-[#5a76c8]">{{ $form->fieldCount() }}</span></td>
                                <td class="px-5 py-5 text-center align-middle"><span class="font-black text-[#4bc6b9]">{{ $form->submissions_count }}</span></td>
                                <td class="px-5 py-5 align-middle"><span class="inline-flex whitespace-nowrap rounded-full border-2 border-white px-4 py-1.5 text-[10px] font-black uppercase tracking-widest shadow-sm {{ $form->is_active ? 'bg-[#e0fbfc] text-[#4bc6b9]' : 'bg-[#ffe5e5] text-[#ff6b6b]' }}">{{ $form->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                <td class="px-5 py-5 text-center align-middle">
                                    <span class="inline-flex whitespace-nowrap rounded-full border-2 border-white px-4 py-1.5 text-[10px] font-black uppercase tracking-widest shadow-sm {{ $form->benefit_enabled ? 'bg-[#e6fff7] text-emerald-500' : 'bg-[#f4f9ff] text-[#8faaf3]' }}">
                                        {{ $form->benefit_enabled ? 'Ada' : 'Tidak Ada' }}
                                    </span>
                                </td>
                                <td class="px-5 py-5 align-middle">
                                    <div class="flex min-w-0 items-center gap-2">
                                        <a href="{{ $publicUrl }}" target="_blank" rel="noopener noreferrer" onclick="event.stopPropagation()" class="min-w-0 flex-1 truncate whitespace-nowrap text-xs font-black text-[#5a76c8] underline decoration-current decoration-2 underline-offset-4" title="{{ $publicUrl }}">{{ $publicUrl }}</a>
                                        @include('admin.forms.partials.copy-link-button', ['url' => $publicUrl])
                                    </div>
                                </td>
                                <td class="px-5 py-5 align-middle">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.forms.show', ['form' => $form, 'return_to' => $indexReturnUrl]) }}" onclick="event.stopPropagation()" class="wiboost-form-action-button wiboost-form-action-button--view flex h-10 w-10 items-center justify-center rounded-[1rem] border-2 border-white" title="Lihat Formulir">@include('partials.wiboost-icon', ['name' => 'eye', 'class' => 'h-5 w-5'])</a>
                                        <a href="{{ route('admin.forms.edit', ['form' => $form, 'return_to' => $indexReturnUrl]) }}" onclick="event.stopPropagation()" class="wiboost-form-action-button wiboost-form-action-button--edit flex h-10 w-10 items-center justify-center rounded-[1rem] border-2 border-white" title="Edit Formulir">@include('partials.wiboost-icon', ['name' => 'edit', 'class' => 'h-5 w-5 text-white'])</a>
                                        <form action="{{ route('admin.forms.destroy', $form) }}" method="POST" onclick="event.stopPropagation()" onsubmit="return confirm('Hapus formulir dan semua jawabannya?');" class="m-0">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="return_to" value="{{ $indexReturnUrl }}">
                                            <button type="submit" class="wiboost-form-action-button wiboost-form-action-button--delete flex h-10 w-10 items-center justify-center rounded-[1rem] border-2 border-white" title="Hapus Formulir">@include('partials.wiboost-icon', ['name' => 'trash', 'class' => 'h-5 w-5 text-white'])</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-6 py-16 text-center">
                                    @include('partials.wiboost-icon', ['name' => 'note', 'class' => 'mx-auto h-14 w-14 text-[#8faaf3] opacity-60'])
                                    <p class="mt-4 text-xl font-black text-[#5a76c8]">Belum ada formulir.</p>
                                    <p class="mt-2 text-sm font-bold text-[#8faaf3]">Tambah formulir pertama untuk dibagikan tanpa login.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(isset($forms) && $forms->hasPages())
            <div class="wiboost-table-pagination-footer w-full min-w-0 p-5 sm:p-8">{{ $forms->links() }}</div>
        @endif
    </div>

    @foreach($forms as $form)
        @php $publicUrl = $form->publicUrl(); @endphp
        <div id="admin-form-detail-{{ $form->id }}" class="admin-detail-modal fixed inset-0 z-[9999] flex items-center justify-center opacity-0 pointer-events-none transition-all duration-200">
            <div class="absolute inset-0" onclick="closeAdminUniversalDetail('admin-form-detail-{{ $form->id }}')"></div>
            <div class="admin-detail-card relative flex w-full flex-col rounded-[1.75rem] border-4 border-white bg-white p-4 shadow-2xl shadow-[#2b3a67]/20 transform scale-95 transition-transform duration-200 sm:p-5">
                <div class="admin-detail-header mb-3.5 flex items-start justify-between gap-3 p-3.5">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Detail Formulir</p>
                        <h3 class="mt-1 break-words text-xl font-black text-[#2b3a67]">{{ $form->title }}</h3>
                        <p class="mt-1 text-xs font-bold text-[#8faaf3]">{{ $form->created_at?->format('d M Y, H:i') ?? '-' }}</p>
                    </div>
                    <button type="button" onclick="closeAdminUniversalDetail('admin-form-detail-{{ $form->id }}')" class="admin-detail-close flex h-11 w-11 shrink-0 items-center justify-center border-2 border-white">@include('partials.wiboost-icon', ['name' => 'x', 'class' => 'h-4 w-4'])</button>
                </div>
                <div class="admin-detail-body">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Judul</p>
                            <p class="mt-1 break-words text-sm font-black text-[#2b3a67]">{{ $form->title }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Slug</p>
                            <p class="mt-1 break-all text-sm font-black text-[#5a76c8]">{{ $form->slug }}</p>
                        </div>
                        <div class="admin-detail-panel md:col-span-2">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Deskripsi</p>
                            <p class="mt-1 whitespace-pre-line break-words text-left text-sm font-bold leading-relaxed text-[#2b3a67]">{{ $form->description ?: '-' }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Status</p>
                            <span class="mt-2 inline-flex rounded-full border-2 border-white px-4 py-1.5 text-[10px] font-black uppercase tracking-widest shadow-sm {{ $form->is_active ? 'bg-[#e0fbfc] text-[#4bc6b9]' : 'bg-[#ffe5e5] text-[#ff6b6b]' }}">{{ $form->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Benefit</p>
                            <span class="mt-2 inline-flex whitespace-nowrap rounded-full border-2 border-white px-4 py-1.5 text-[10px] font-black uppercase tracking-widest shadow-sm {{ $form->benefit_enabled ? 'bg-[#e6fff7] text-emerald-500' : 'bg-[#f4f9ff] text-[#8faaf3]' }}">
                                {{ $form->benefit_enabled ? 'Ada' : 'Tidak Ada' }}
                            </span>
                        </div>
                        <div class="admin-detail-panel"><p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Pertanyaan</p><p class="mt-1 text-2xl font-black text-[#5a76c8]">{{ $form->fieldCount() }}</p></div>
                        <div class="admin-detail-panel"><p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Jawaban</p><p class="mt-1 text-2xl font-black text-[#4bc6b9]">{{ $form->submissions_count }}</p></div>
                        <div class="admin-detail-panel"><p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Dibuat Oleh</p><p class="mt-1 text-sm font-black text-[#2b3a67]">{{ $form->creator?->short_name ?? 'Admin' }}</p></div>
                        <div class="admin-detail-panel"><p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Waktu Dibuat</p><p class="mt-1 whitespace-nowrap text-sm font-black text-[#2b3a67]">{{ $form->created_at?->format('d M Y, H:i') ?? '-' }}</p></div>
                        <div class="admin-detail-panel md:col-span-2">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Tautan Publik</p>
                            <div class="mt-2 flex min-w-0 items-center gap-2">
                                <a href="{{ $publicUrl }}" target="_blank" rel="noopener noreferrer" class="admin-detail-link-no-scrollbar block min-w-0 flex-1 overflow-x-auto whitespace-nowrap text-sm font-black text-[#5a76c8] underline decoration-current decoration-2 underline-offset-4" title="{{ $publicUrl }}">{{ $publicUrl }}</a>
                                @include('admin.forms.partials.copy-link-button', ['url' => $publicUrl])
                            </div>
                        </div>
                        <div class="admin-detail-panel admin-detail-panel-soft md:col-span-2">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Aksi</p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <a href="{{ route('admin.forms.show', ['form' => $form, 'return_to' => $indexReturnUrl]) }}" class="rounded-full border-2 border-white bg-[#e0fbfc] px-4 py-2 text-[10px] font-black uppercase tracking-widest text-[#4bc6b9] shadow-sm">Lihat Formulir</a>
                                <a href="{{ route('admin.forms.edit', ['form' => $form, 'return_to' => $indexReturnUrl]) }}" class="rounded-full border-2 border-white bg-[#fbbf24] px-4 py-2 text-[10px] font-black uppercase tracking-widest text-white shadow-sm">Edit Formulir</a>
                                <form action="{{ route('admin.forms.destroy', $form) }}" method="POST" onsubmit="return confirm('Hapus formulir dan semua jawabannya?');" class="m-0">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="return_to" value="{{ $indexReturnUrl }}">
                                    <button type="submit" class="rounded-full border-2 border-white bg-[#ff6b6b] px-4 py-2 text-[10px] font-black uppercase tracking-widest text-white shadow-sm">Hapus Formulir</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
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

        document.addEventListener('click', async (event) => {
            const button = event.target.closest('[data-copy-form-link]');
            if (!button) return;

            event.preventDefault();
            event.stopPropagation();
            const value = button.dataset.copyFormLink || '';
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
            button.classList.remove('is-copied');
            void button.offsetWidth;
            button.classList.add('is-copied');
            window.setTimeout(() => {
                button.classList.remove('is-copied');
            }, 1400);
        }, true);
    })();
</script>
@endsection
