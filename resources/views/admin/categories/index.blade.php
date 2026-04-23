@extends('layouts.admin')

@section('title', 'Manajemen Kategori')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
</style>

@php
    $sectionMeta = [
        'main' => [
            'title' => 'Kategori Utama',
            'accent' => 'border-[#8faaf3]',
            'bg' => 'from-[#f0f5ff] to-[#e0ebff]',
            'iconBg' => 'bg-white',
            'empty' => 'Belum ada kategori utama.',
            'badge' => 'Level 0',
        ],
        'level_1' => [
            'title' => 'Kategori Turunan 1',
            'accent' => 'border-[#bde0fe]',
            'bg' => 'from-[#f8fbff] to-[#eef7ff]',
            'iconBg' => 'bg-[#f4f9ff]',
            'empty' => 'Belum ada kategori turunan level 1.',
            'badge' => 'Level 1',
        ],
        'level_2' => [
            'title' => 'Kategori Turunan 2',
            'accent' => 'border-[#cfe9ff]',
            'bg' => 'from-[#fbfdff] to-[#f3f9ff]',
            'iconBg' => 'bg-[#f4f9ff]',
            'empty' => 'Belum ada kategori turunan level 2.',
            'badge' => 'Level 2',
        ],
        'level_3' => [
            'title' => 'Kategori Turunan 3',
            'accent' => 'border-[#dcefff]',
            'bg' => 'from-[#ffffff] to-[#f7fbff]',
            'iconBg' => 'bg-[#f4f9ff]',
            'empty' => 'Belum ada kategori turunan level 3.',
            'badge' => 'Level 3+',
        ],
    ];
@endphp

<div class="wiboost-font mx-auto max-w-7xl pb-12">
    <div class="mb-10 flex flex-col gap-4 pl-2 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-3xl font-black tracking-tight text-[#2b3a67]">Kategori Layanan</h2>
            <p class="mt-1 text-sm font-bold text-[#8faaf3]">Kelola struktur kategori utama dan turunan sampai level 3.</p>
        </div>

        <a href="{{ route('admin.categories.create') }}" class="flex items-center gap-2 rounded-full border-4 border-white bg-[#5a76c8] px-8 py-3.5 font-black text-white shadow-lg shadow-[#5a76c8]/30 transition hover:bg-[#4760a9] active:scale-95">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
            Tambah Kategori
        </a>
    </div>

    @if(session('success'))
        <div class="mb-8 flex items-start gap-3 rounded-[2rem] border-4 border-white bg-[#e6fff7] px-6 py-4 font-black text-emerald-500 shadow-sm">
            <span class="mt-0.5 text-xl">OK</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-8 flex items-start gap-3 rounded-[2rem] border-4 border-white bg-[#ffe5e5] px-6 py-4 font-black text-[#ff6b6b] shadow-sm">
            <span class="mt-0.5 text-xl">!</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="mb-10 rounded-[2rem] border-4 border-white bg-white p-5 shadow-lg shadow-[#bde0fe]/20">
        <form action="{{ route('admin.categories.index') }}" method="GET" class="flex flex-col gap-4 md:flex-row">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-[#8faaf3]">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" name="search" value="{{ $search }}"
                    class="w-full rounded-[1.5rem] border-2 border-[#e0fbfc] bg-[#f4f9ff] py-4 pl-14 pr-5 font-black text-[#2b3a67] outline-none transition placeholder-[#a3bbfb] focus:border-[#5a76c8]"
                    placeholder="Cari nama kategori, slug, deskripsi, atau parent...">
            </div>

            <button type="submit" class="rounded-[1.5rem] border-2 border-white bg-[#5a76c8] px-10 py-4 font-black text-white shadow-lg shadow-[#5a76c8]/30 transition hover:bg-[#4760a9]">
                Cari
            </button>

            @if(filled($search))
                <a href="{{ route('admin.categories.index') }}" class="flex items-center justify-center rounded-[1.5rem] border-2 border-white bg-[#ffe5e5] px-8 py-4 font-black text-[#ff6b6b] transition hover:bg-[#ffcccc]">
                    Reset
                </a>
            @endif
        </form>
    </div>

    @foreach($sectionMeta as $key => $meta)
        @php
            $items = $categorySections[$key] ?? collect();
        @endphp

        <section class="mb-10">
            <div class="mb-5 flex items-center gap-3 pl-2">
                <h3 class="text-xl font-black text-[#2b3a67]">{{ $meta['title'] }}</h3>
                <span class="rounded-full bg-[#f0f5ff] px-3 py-1 text-[10px] font-black uppercase tracking-widest text-[#5a76c8]">{{ $meta['badge'] }}</span>
                <span class="rounded-full bg-[#e6fff7] px-3 py-1 text-[10px] font-black uppercase tracking-widest text-emerald-500">{{ $items->count() }} item</span>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                @forelse($items as $category)
                    <div class="rounded-[2rem] border-4 {{ $meta['accent'] }} bg-gradient-to-r {{ $meta['bg'] }} p-5 shadow-md transition hover:shadow-lg">
                        <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                            <div class="flex flex-1 items-center gap-4">
                                <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl border-2 border-white {{ $meta['iconBg'] }} p-1 shadow-inner">
                                    @if($category->image)
                                        <img src="{{ Storage::url($category->image) }}" alt="{{ $category->name }}" class="h-full w-full object-contain">
                                    @else
                                        <span class="text-3xl">{{ $category->emote ?: 'CAT' }}</span>
                                    @endif
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p class="text-lg font-black leading-tight text-[#2b3a67]">{{ $category->name }}</p>
                                    <div class="mt-2 flex flex-wrap items-center gap-2">
                                        <span class="rounded-md bg-white/80 px-2 py-1 text-[9px] font-black uppercase tracking-widest text-[#8faaf3] shadow-sm">{{ $category->breadcrumb_name }}</span>
                                        <span class="rounded-md bg-[#e6fff7] px-2 py-1 text-[9px] font-black uppercase tracking-widest text-emerald-500 shadow-sm">{{ $category->products_count }} produk</span>
                                        <span class="rounded-md bg-[#fff5eb] px-2 py-1 text-[9px] font-black uppercase tracking-widest text-amber-500 shadow-sm">{{ $category->children_count }} child</span>
                                    </div>
                                    @if($category->slug)
                                        <p class="mt-2 text-xs font-black text-[#5a76c8]">/{{ $category->slug }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-2 border-t-2 border-dashed border-white/70 pt-4 xl:border-l-2 xl:border-t-0 xl:pl-4 xl:pt-0">
                                <a href="{{ route('admin.categories.edit', $category->id) }}" class="flex-1 rounded-xl border-2 border-white bg-white px-4 py-2 text-center text-xs font-black text-[#5a76c8] shadow-sm transition hover:bg-[#5a76c8] hover:text-white xl:flex-none">
                                    Edit
                                </a>

                                <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="flex-1 xl:flex-none" onsubmit="return confirm('Yakin ingin menghapus kategori ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full rounded-xl border-2 border-white bg-[#ffe5e5] px-4 py-2 text-xs font-black text-[#ff6b6b] shadow-sm transition hover:bg-[#ff6b6b] hover:text-white">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full rounded-[2rem] border-4 border-dashed border-[#dcefff] bg-white py-10 text-center">
                        <p class="text-lg font-black text-[#8faaf3]">{{ filled($search) ? 'Tidak ada hasil di section ini.' : $meta['empty'] }}</p>
                    </div>
                @endforelse
            </div>
        </section>
    @endforeach
</div>
@endsection
