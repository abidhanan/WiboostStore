@extends('layouts.admin')

@section('title', 'Manajemen Kategori')
@section('admin_header_subtitle', 'Kelola struktur kategori utama dan turunan sampai level 3.')
@section('admin_header_actions')
    <a href="{{ route('admin.categories.create') }}" class="flex w-full items-center justify-center gap-2 rounded-full border-4 border-white bg-[#5a76c8] px-8 py-3.5 font-black text-white shadow-xl shadow-[#5a76c8]/30 transition-transform hover:bg-[#4760a9] active:scale-95 sm:w-auto">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
        Tambah Kategori
    </a>
@endsection

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
    
    .animate-float { animation: float 6s ease-in-out infinite; }
    .animate-float-delayed { animation: float 6s ease-in-out 3s infinite; }
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
        100% { transform: translateY(0px); }
    }
</style>

@php
    $sectionMeta = [
        'main' => [
            'title' => 'Kategori Utama',
            'accent' => 'border-white',
            'bg' => 'from-[#e0fbfc] to-[#f4f9ff]',
            'iconBg' => 'bg-white',
            'empty' => 'Belum ada kategori utama.',
            'badge' => 'Level 0',
        ],
        'level_1' => [
            'title' => 'Kategori Turunan 1',
            'accent' => 'border-white',
            'bg' => 'from-[#f4f9ff] to-[#eef7ff]',
            'iconBg' => 'bg-white',
            'empty' => 'Belum ada kategori turunan level 1.',
            'badge' => 'Level 1',
        ],
        'level_2' => [
            'title' => 'Kategori Turunan 2',
            'accent' => 'border-white',
            'bg' => 'from-[#f8fbff] to-[#f4f9ff]',
            'iconBg' => 'bg-white',
            'empty' => 'Belum ada kategori turunan level 2.',
            'badge' => 'Level 2',
        ],
        'level_3' => [
            'title' => 'Kategori Turunan 3',
            'accent' => 'border-white',
            'bg' => 'from-[#ffffff] to-[#f8fbff]',
            'iconBg' => 'bg-[#f4f9ff]',
            'empty' => 'Belum ada kategori turunan level 3.',
            'badge' => 'Level 3+',
        ],
    ];
@endphp

<div class="wiboost-font mx-auto max-w-7xl pb-12 relative z-10">

    <div class="absolute top-10 right-10 text-5xl animate-float opacity-30 pointer-events-none hidden md:block z-0">📂</div>
    <div class="absolute top-1/3 left-5 text-4xl animate-float-delayed opacity-30 pointer-events-none hidden md:block z-0">✨</div>
    <div class="absolute bottom-20 right-[15%] text-6xl animate-float opacity-20 pointer-events-none hidden md:block z-0">☁️</div>

    @if(session('success'))
        <div class="mb-8 flex items-center gap-4 rounded-[2.5rem] border-4 border-white bg-[#e6fff7] px-6 py-4 font-black text-emerald-500 shadow-lg shadow-emerald-100/50 relative z-10">
            <span class="text-3xl drop-shadow-sm">✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-8 flex items-center gap-4 rounded-[2.5rem] border-4 border-white bg-[#ffe5e5] px-6 py-4 font-black text-[#ff6b6b] shadow-lg shadow-[#ff6b6b]/20 relative z-10">
            <span class="text-3xl drop-shadow-sm">⚠️</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="mb-10 rounded-[2.5rem] border-4 border-white bg-white/90 backdrop-blur-sm p-6 shadow-xl shadow-[#bde0fe]/30 transition-transform duration-300 hover:border-[#bde0fe] relative z-10">
        <form action="{{ route('admin.categories.index') }}" method="GET" class="flex flex-col gap-4 md:flex-row">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-6 text-[#8faaf3]">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" name="search" value="{{ $search }}"
                    class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] py-4 pl-16 pr-5 font-black text-[#2b3a67] outline-none transition shadow-inner placeholder-[#a3bbfb] focus:border-[#bde0fe]"
                    placeholder="Cari nama kategori, slug, deskripsi...">
            </div>

            <button type="submit" class="rounded-full border-4 border-white bg-[#5a76c8] px-10 py-4 font-black text-white shadow-xl shadow-[#5a76c8]/30 transition-transform active:scale-95 hover:bg-[#4760a9]">
                Cari Kategori 🔍
            </button>

            @if(filled($search))
                <a href="{{ route('admin.categories.index') }}" class="flex items-center justify-center rounded-full border-4 border-white bg-[#ffe5e5] px-8 py-4 font-black text-[#ff6b6b] shadow-md shadow-[#ff6b6b]/20 transition-transform active:scale-95 hover:bg-[#ffcccc]">
                    Reset
                </a>
            @endif
        </form>
    </div>

    @foreach($sectionMeta as $key => $meta)
        @php
            $items = $categorySections[$key] ?? collect();
        @endphp

        <section class="mb-12 relative z-10">
            <div class="mb-6 flex items-center gap-3 pl-2">
                <h3 class="text-2xl font-black text-[#2b3a67] drop-shadow-sm">{{ $meta['title'] }}</h3>
                <span class="rounded-full bg-[#f4f9ff] px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-[#5a76c8] border-2 border-white shadow-sm">{{ $meta['badge'] }}</span>
                <span class="rounded-full bg-[#e6fff7] px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-emerald-500 border-2 border-white shadow-sm">{{ $items->count() }} item</span>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                @forelse($items as $category)
                    <div class="rounded-[2.5rem] border-4 {{ $meta['accent'] }} bg-gradient-to-br {{ $meta['bg'] }} p-6 shadow-xl shadow-[#bde0fe]/20 transition-transform hover:-translate-y-2 hover:border-[#bde0fe] group relative overflow-hidden">
                        
                        <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between relative z-10">
                            <div class="flex flex-1 items-center gap-4">
                                <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-[1.5rem] border-4 border-white {{ $meta['iconBg'] }} p-2 shadow-inner group-hover:scale-110 transition-transform duration-300">
                                    @if($category->image)
                                        <img src="{{ Storage::url($category->image) }}" alt="{{ $category->name }}" class="h-full w-full object-contain drop-shadow-sm">
                                    @else
                                        <span class="text-4xl drop-shadow-md">{{ $category->emote ?: '📁' }}</span>
                                    @endif
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p class="text-xl font-black leading-tight text-[#2b3a67]">{{ $category->name }}</p>
                                    <div class="mt-2 flex flex-wrap items-center gap-2">
                                        <span class="rounded-md bg-white px-2 py-1 text-[9px] font-black uppercase tracking-widest text-[#8faaf3] shadow-sm border border-[#f0f5ff]">{{ $category->breadcrumb_name }}</span>
                                        <span class="rounded-md bg-[#e6fff7] px-2 py-1 text-[9px] font-black uppercase tracking-widest text-emerald-500 shadow-sm border border-emerald-100">{{ $category->products_count }} produk</span>
                                        <span class="rounded-md bg-[#fff5eb] px-2 py-1 text-[9px] font-black uppercase tracking-widest text-amber-500 shadow-sm border border-amber-100">{{ $category->children_count }} child</span>
                                    </div>
                                    @if($category->slug)
                                        <p class="mt-2 text-xs font-black text-[#8faaf3] bg-white/50 inline-block px-3 py-1 rounded-lg border border-[#e0fbfc]">/{{ $category->slug }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-3 border-t-4 border-dashed border-[#f4f9ff] pt-5 xl:border-l-4 xl:border-t-0 xl:pl-5 xl:pt-0">
                                <a href="{{ route('admin.categories.edit', $category->id) }}" class="flex-1 xl:flex-none rounded-xl border-4 border-white bg-white px-5 py-3 text-center text-sm font-black text-[#5a76c8] shadow-md shadow-[#bde0fe]/20 transition-transform active:scale-95 hover:bg-[#5a76c8] hover:text-white flex items-center justify-center gap-2">
                                    ✏️
                                </a>

                                <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="flex-1 xl:flex-none" onsubmit="return confirm('Yakin ingin menghapus kategori ini secara permanen?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full rounded-xl border-4 border-white bg-[#ffe5e5] px-5 py-3 text-sm font-black text-[#ff6b6b] shadow-md shadow-[#ff6b6b]/20 transition-transform active:scale-95 hover:bg-[#ff6b6b] hover:text-white flex items-center justify-center gap-2">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full rounded-[2.5rem] border-4 border-dashed border-[#bde0fe] bg-white/80 py-16 text-center backdrop-blur-sm shadow-lg shadow-[#bde0fe]/20">
                        <span class="text-6xl opacity-50 block mb-4 animate-float">📂</span>
                        <p class="text-xl font-black text-[#5a76c8]">{{ filled($search) ? 'Tidak ada hasil di section ini.' : $meta['empty'] }}</p>
                    </div>
                @endempty
            </div>
        </section>
    @endforeach
</div>
@endsection