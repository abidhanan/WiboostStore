@extends('layouts.admin')
@section('title', 'Manajemen Kategori')
@section('admin_header_subtitle', 'Atur kategori layanan dan produk.')

@section('content')
<style>
    .table-scroll::-webkit-scrollbar { height: 8px; }
    .table-scroll::-webkit-scrollbar-track { background: #f0f5ff; border-radius: 10px; }
    .table-scroll::-webkit-scrollbar-thumb { background: #bde0fe; border-radius: 10px; border: 2px solid #f0f5ff; }
    .table-scroll::-webkit-scrollbar-thumb:hover { background: #8faaf3; }
    
    .animate-float { animation: float 6s ease-in-out infinite; }
    .animate-float-delayed { animation: float 6s ease-in-out 3s infinite; }
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
        100% { transform: translateY(0px); }
    }

    .admin-category-slug-cell {
        min-width: 220px !important;
        width: 220px !important;
        white-space: nowrap !important;
        word-break: keep-all !important;
        overflow-wrap: normal !important;
    }

    .admin-category-slug {
        display: inline-flex !important;
        max-width: none !important;
        width: max-content !important;
        white-space: nowrap !important;
        word-break: keep-all !important;
        overflow-wrap: normal !important;
    }
</style>

<div class="wiboost-font pb-12 relative z-10 w-full min-w-0">
    @php
        $searchValue = $search ?? request('search');
        $activeCategoryFilter = $categoryFilter ?? request('category_filter', 'all');
        $categoryFilters = [
            'all' => ['label' => 'Semua'],
            'main' => ['label' => 'Utama'],
            'level_1' => ['label' => 'Level 1'],
            'level_2' => ['label' => 'Level 2'],
            'level_3' => ['label' => 'Level 3+'],
        ];
    @endphp
    <!-- Toolbar: Pencarian & Tombol Tambah -->
    <div class="relative z-10 mb-10 flex w-full min-w-0 flex-col gap-3 rounded-[2rem] border-4 border-white bg-white/90 p-3 shadow-xl shadow-[#bde0fe]/25 backdrop-blur-sm sm:mb-12 sm:rounded-[2.5rem] sm:p-4 lg:mb-14 lg:flex-row lg:items-center">
        
        <!-- Form Pencarian (Kiri di Desktop / Atas di Mobile) -->
        <div class="min-w-0 flex-1">
            <form action="{{ route('admin.categories.index') }}" method="GET" class="flex gap-2 sm:gap-3 items-center w-full">
                @if($activeCategoryFilter !== 'all')
                    <input type="hidden" name="category_filter" value="{{ $activeCategoryFilter }}">
                @endif
                <!-- Input Field -->
                <input type="text" name="search" value="{{ $searchValue }}"
                    class="min-w-0 flex-1 rounded-[1.2rem] sm:rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] py-3 sm:py-4 px-5 sm:px-6 font-black text-[#2b3a67] outline-none transition shadow-inner placeholder-[#a3bbfb] focus:border-[#bde0fe] text-xs sm:text-sm truncate"
                    placeholder="Cari nama kategori...">
                
                <button type="submit" class="shrink-0 flex items-center justify-center rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#5a76c8] w-12 h-12 text-white shadow-md shadow-[#5a76c8]/25 transition-transform active:scale-95 hover:bg-[#4760a9] text-base" title="Cari Kategori">
                    @include('partials.wiboost-icon', ['name' => 'search', 'class' => 'h-5 w-5'])
                </button>
                
                @if($searchValue)
                    <a href="{{ route('admin.categories.index', array_filter(['category_filter' => $activeCategoryFilter === 'all' ? null : $activeCategoryFilter])) }}" class="shrink-0 flex items-center justify-center rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#ffe5e5] w-12 h-12 text-[#ff6b6b] shadow-md shadow-[#ff6b6b]/20 transition-transform active:scale-95 hover:bg-[#ffcccc] text-base" title="Reset Pencarian">
                        @include('partials.wiboost-icon', ['name' => 'x', 'class' => 'h-5 w-5'])
                    </a>
                @endif
            </form>
        </div>

        <!-- Tombol Tambah Kategori (Kanan di Desktop / Bawah di Mobile) -->
        <a href="{{ route('admin.categories.create') }}" class="flex w-full min-w-0 shrink-0 items-center justify-center gap-2 rounded-[1rem] border-4 border-white bg-[#5a76c8] px-5 py-3 text-sm font-black text-white shadow-md shadow-[#5a76c8]/25 transition-transform hover:bg-[#4760a9] active:scale-95 sm:rounded-[1.2rem] lg:w-auto lg:min-w-[190px] lg:px-7">
            @include('partials.wiboost-icon', ['name' => 'plus', 'class' => 'h-5 w-5'])
            Tambah Kategori
        </a>
    </div>

    @include('admin.partials.filter-tabs', [
        'filters' => $categoryFilters,
        'activeFilter' => $activeCategoryFilter,
        'filterParam' => 'category_filter',
        'filterRoute' => 'admin.categories.index',
        'filterQuery' => ['search' => $searchValue],
    ])

    <!-- Alert Messages (Aman dari 500 Error dengan Session Check) -->
    @if (session('success'))
        <div class="bg-[#e6fff7] border-4 border-white text-emerald-500 px-5 sm:px-6 py-4 rounded-[2rem] mb-6 sm:mb-8 font-black flex items-center gap-3 shadow-lg shadow-emerald-100/50 relative z-10 w-full text-sm sm:text-base">
            @include('partials.wiboost-icon', ['name' => 'success', 'class' => 'h-6 w-6 shrink-0'])
            <p>{{ session('success') }}</p>
        </div>
    @endif
    @if (session('error'))
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-5 sm:px-6 py-4 rounded-[2rem] mb-6 sm:mb-8 font-black flex items-center gap-3 shadow-lg shadow-[#ff6b6b]/20 relative z-10 w-full text-sm sm:text-base">
            @include('partials.wiboost-icon', ['name' => 'alert', 'class' => 'h-6 w-6 shrink-0'])
            <p>{{ session('error') }}</p>
        </div>
    @endif

    @php
        $separatedCategorySections = $categorySections ?? ['all' => $categories ?? collect()];
        $separatedCategoryLabels = [
            'main' => 'Kategori Utama',
            'level_1' => 'Kategori Turunan 1',
            'level_2' => 'Kategori Turunan 2',
            'level_3' => 'Kategori Turunan 3+',
            'all' => 'Semua Kategori',
        ];
        $hasSeparatedCategories = collect($separatedCategorySections)->contains(fn ($items) => collect($items)->isNotEmpty());
    @endphp

    @if($hasSeparatedCategories)
        <div class="space-y-8">
            @foreach($separatedCategorySections as $sectionKey => $sectionCategories)
                @php
                    $sectionItems = collect($sectionCategories);
                @endphp

                @continue($sectionItems->isEmpty())

                <section class="bg-white/95 backdrop-blur-sm rounded-[2rem] sm:rounded-[2.5rem] shadow-2xl shadow-[#bde0fe]/30 border-4 border-white flex flex-col overflow-hidden relative z-10 min-w-0 w-full">
                    <div class="border-b-4 border-dashed border-[#f4f9ff] bg-[#f8faff] px-5 py-5 sm:px-6">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-xl font-black text-[#2b3a67] sm:text-2xl">{{ $separatedCategoryLabels[$sectionKey] ?? 'Semua Kategori' }}</h2>
                            </div>
                        </div>
                    </div>

                    <div class="w-full overflow-x-auto table-scroll pb-2">
                        <div class="inline-block min-w-full align-middle p-4 sm:p-6">
                            <table class="w-full min-w-[1080px] text-left whitespace-nowrap">
                                <thead>
                                    <tr>
                                        <th class="px-5 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-4 border-dashed border-[#f4f9ff]">Visual</th>
                                        <th class="px-5 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-4 border-dashed border-[#f4f9ff]">Nama</th>
                                        <th class="admin-category-slug-cell px-5 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-4 border-dashed border-[#f4f9ff]">Slug</th>
                                        <th class="px-5 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-4 border-dashed border-[#f4f9ff]">Tipe</th>
                                        <th class="px-5 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-4 border-dashed border-[#f4f9ff]">Induk</th>
                                        <th class="px-5 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-4 border-dashed border-[#f4f9ff]">Deskripsi</th>
                                        <th class="px-5 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-4 border-dashed border-[#f4f9ff] text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                                    @foreach($sectionItems as $category)
                                        <tr class="hover:bg-[#f8faff] transition-colors group">
                                            <td class="px-5 py-4 sm:py-5 align-middle">
                                                @if(!empty($category->image))
                                                    <div class="h-10 w-10 sm:h-12 sm:w-12 shrink-0 overflow-hidden rounded-xl border-2 border-white bg-[#f4f9ff] shadow-sm">
                                                        <img src="{{ Storage::url($category->image) }}" class="h-full w-full object-cover">
                                                    </div>
                                                @else
                                                    <div class="flex h-10 w-10 sm:h-12 sm:w-12 shrink-0 items-center justify-center rounded-xl border-2 border-white bg-[#f0f5ff] text-xl sm:text-2xl shadow-inner">
                                                        {{ $category->emote ?? '📁' }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-5 py-4 sm:py-5 align-middle min-w-[220px]">
                                                <p class="font-black text-[#2b3a67] text-sm truncate">{{ $category->name ?? '-' }}</p>
                                            </td>
                                            <td class="admin-category-slug-cell px-5 py-4 align-middle sm:py-5">
                                                <p class="admin-category-slug rounded-md border border-white bg-[#f4f9ff] px-2 py-1 text-[9px] font-bold text-[#8faaf3] sm:text-[10px]">{{ $category->slug ?? '-' }}</p>
                                            </td>
                                            <td class="px-5 py-4 sm:py-5 align-middle">
                                                @if(empty($category->parent_id))
                                                    <span class="bg-[#e0fbfc] text-[#4bc6b9] px-3 py-1.5 rounded-md text-[9px] font-black uppercase tracking-widest border border-white shadow-sm">Kategori Utama</span>
                                                @else
                                                    <span class="bg-[#fff5eb] text-amber-500 px-3 py-1.5 rounded-md text-[9px] font-black uppercase tracking-widest border border-white shadow-sm">Kategori Turunan {{ $category->depth }}</span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-4 sm:py-5 align-middle">
                                                <p class="text-[10px] font-bold text-[#8faaf3]">{{ $category->parent->name ?? '-' }}</p>
                                            </td>
                                            <td class="px-5 py-4 sm:py-5 align-middle min-w-[200px] whitespace-normal">
                                                <p class="text-xs font-bold text-[#8faaf3] line-clamp-2 leading-relaxed">
                                                    {{ $category->description ?? '-' }}
                                                </p>
                                            </td>
                                            <td class="px-5 py-4 sm:py-5 align-middle text-center">
                                                <div class="flex items-center justify-center gap-2">
                                                    <a href="{{ route('admin.categories.edit', $category->id) }}" class="w-9 h-9 sm:w-10 sm:h-10 bg-white border-2 border-white rounded-[1rem] flex items-center justify-center text-lg sm:text-xl hover:bg-[#f0f5ff] transition-transform hover:scale-110 active:scale-95 shadow-md shadow-[#bde0fe]/20" title="Edit Kategori">
                                                        @include('partials.wiboost-icon', ['name' => 'edit', 'class' => 'h-4 w-4'])
                                                    </a>
                                                    <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus kategori ini? Jika ini kategori utama, pastikan tidak ada subkategori di dalamnya.');" class="inline-block m-0">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="w-9 h-9 sm:w-10 sm:h-10 bg-white border-2 border-white rounded-[1rem] flex items-center justify-center text-lg sm:text-xl hover:bg-[#ffe5e5] transition-transform hover:scale-110 active:scale-95 shadow-md shadow-[#ff6b6b]/20" title="Hapus Kategori">
                                                            @include('partials.wiboost-icon', ['name' => 'trash', 'class' => 'h-4 w-4'])
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            @endforeach
        </div>

        @if(isset($categories) && method_exists($categories, 'links') && $categories->hasPages())
            <div class="p-5 sm:p-8 w-full min-w-0">
                {{ $categories->links() }}
            </div>
        @endif
    @else
        <div class="bg-white/95 backdrop-blur-sm rounded-[2rem] sm:rounded-[2.5rem] shadow-2xl shadow-[#bde0fe]/30 border-4 border-white relative z-10 min-w-0 w-full px-5 py-16 text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 sm:w-24 sm:h-24 rounded-[2rem] sm:rounded-[2.5rem] bg-[#f0f5ff] border-4 border-white mb-4 sm:mb-5 shadow-inner">
                @include('partials.wiboost-icon', ['name' => 'category', 'class' => 'h-10 w-10 text-[#5a76c8] animate-float'])
            </div>
            <p class="text-[#5a76c8] font-black text-lg sm:text-xl">Belum ada kategori terdaftar.</p>
        </div>
    @endif

    <!-- Tabel Data Kategori -->
    <div class="hidden bg-white/95 backdrop-blur-sm rounded-[2rem] sm:rounded-[2.5rem] shadow-2xl shadow-[#bde0fe]/30 border-4 border-white flex-col overflow-hidden relative z-10 min-w-0 w-full">
        <div class="w-full overflow-x-auto table-scroll pb-2">
            <div class="inline-block min-w-full align-middle p-4 sm:p-6">
                <table class="w-full min-w-[860px] text-left whitespace-nowrap">
                    <thead>
                        <tr>
                            <th class="px-5 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-4 border-dashed border-[#f4f9ff]">Nama & Visual</th>
                            <th class="px-5 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-4 border-dashed border-[#f4f9ff]">Tipe Kategori</th>
                            <th class="px-5 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-4 border-dashed border-[#f4f9ff]">Deskripsi</th>
                            <th class="px-5 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-4 border-dashed border-[#f4f9ff] text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                        @php
                            $sections = $categorySections ?? ['all' => $categories ?? collect()];
                            $sectionLabels = [
                                'main' => 'Kategori Utama',
                                'level_1' => 'Kategori Turunan 1',
                                'level_2' => 'Kategori Turunan 2',
                                'level_3' => 'Kategori Turunan 3',
                                'all' => 'Semua Kategori',
                            ];
                            $hasCategories = collect($sections)->contains(fn ($items) => collect($items)->isNotEmpty());
                        @endphp

                        @if($hasCategories)
                            @foreach($sections as $sectionKey => $sectionCategories)
                                @continue(collect($sectionCategories)->isEmpty())

                                <tr class="bg-[#f8faff]">
                                    <td colspan="4" class="px-5 py-3">
                                        <span class="inline-flex rounded-full border-2 border-white bg-white px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-[#5a76c8] shadow-sm">
                                            {{ $sectionLabels[$sectionKey] ?? 'Semua Kategori' }}
                                        </span>
                                    </td>
                                </tr>

                                @foreach($sectionCategories as $category)
                            <tr class="hover:bg-[#f8faff] transition-colors group">
                                <td class="px-5 py-4 sm:py-5 align-middle min-w-[250px]">
                                    <div class="flex items-center gap-3 sm:gap-4">
                                        <!-- Aman dari Error: Cek Image / Emote secara eksplisit -->
                                        @if(!empty($category->image))
                                            <div class="h-10 w-10 sm:h-12 sm:w-12 shrink-0 overflow-hidden rounded-xl border-2 border-white bg-[#f4f9ff] shadow-sm">
                                                <img src="{{ Storage::url($category->image) }}" class="h-full w-full object-cover">
                                            </div>
                                        @else
                                            <div class="flex h-10 w-10 sm:h-12 sm:w-12 shrink-0 items-center justify-center rounded-xl border-2 border-white bg-[#f0f5ff] text-xl sm:text-2xl shadow-inner">
                                                {{ $category->emote ?? '📁' }}
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="font-black text-[#2b3a67] text-sm mb-1 truncate">{{ $category->name ?? '-' }}</p>
                                            <p class="inline-block max-w-none whitespace-nowrap rounded-md border border-white bg-[#f4f9ff] px-2 py-1 text-[9px] font-bold text-[#8faaf3] sm:text-[10px]" style="white-space:nowrap;word-break:normal;overflow-wrap:normal;">Slug: {{ $category->slug ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 sm:py-5 align-middle">
                                    <!-- Aman dari Error: Cek Null Relasi Parent -->
                                    @if(empty($category->parent_id))
                                        <span class="bg-[#e0fbfc] text-[#4bc6b9] px-3 py-1.5 rounded-md text-[9px] font-black uppercase tracking-widest border border-white shadow-sm">Kategori Utama</span>
                                    @else
                                        <div class="flex flex-col gap-1 items-start">
                                            <span class="bg-[#fff5eb] text-amber-500 px-3 py-1.5 rounded-md text-[9px] font-black uppercase tracking-widest border border-white shadow-sm">Kategori Turunan {{ $category->depth }}</span>
                                            <p class="text-[10px] font-bold text-[#8faaf3]">Dari: {{ $category->parent->name ?? 'Tidak diketahui' }}</p>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 sm:py-5 align-middle min-w-[200px] whitespace-normal">
                                    <p class="text-xs font-bold text-[#8faaf3] line-clamp-2 leading-relaxed">
                                        {{ $category->description ?? '-' }}
                                    </p>
                                </td>
                                <td class="px-5 py-4 sm:py-5 align-middle text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.categories.edit', $category->id) }}" class="w-9 h-9 sm:w-10 sm:h-10 bg-white border-2 border-white rounded-[1rem] flex items-center justify-center text-[0px] text-[#5a76c8] hover:bg-[#f0f5ff] transition-transform hover:scale-110 active:scale-95 shadow-md shadow-[#bde0fe]/20" title="Edit Kategori">
                                            @include('partials.wiboost-icon', ['name' => 'edit', 'class' => 'h-4 w-4'])
                                        </a>
                                        <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus kategori ini? Jika ini kategori utama, pastikan tidak ada subkategori di dalamnya.');" class="inline-block m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-9 h-9 sm:w-10 sm:h-10 bg-white border-2 border-white rounded-[1rem] flex items-center justify-center text-[0px] text-[#ff6b6b] hover:bg-[#ffe5e5] transition-transform hover:scale-110 active:scale-95 shadow-md shadow-[#ff6b6b]/20" title="Hapus Kategori">
                                                @include('partials.wiboost-icon', ['name' => 'trash', 'class' => 'h-4 w-4'])
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                                @endforeach
                            @endforeach
                        @else
                            <tr>
                                <td colspan="4" class="px-5 py-16 text-center">
                                    <div class="inline-flex items-center justify-center w-20 h-20 sm:w-24 sm:h-24 rounded-[2rem] sm:rounded-[2.5rem] bg-[#f0f5ff] border-4 border-white mb-4 sm:mb-5 shadow-inner">
                                        @include('partials.wiboost-icon', ['name' => 'category', 'class' => 'h-10 w-10 text-[#5a76c8] animate-float'])
                                    </div>
                                    <p class="text-[#5a76c8] font-black text-lg sm:text-xl">Belum ada kategori terdaftar.</p>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Aman dari Error: Pengecekan method links() -->
        @if(isset($categories) && method_exists($categories, 'links') && $categories->hasPages())
            <div class="p-5 sm:p-8 w-full min-w-0">
                {{ $categories->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
