@extends('layouts.admin')
@section('title', 'Manajemen Tutorial')
@section('admin_header_subtitle', 'Kelola kategori utama, kategori kedua, dan isi tutorial pelanggan.')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
    .table-scroll::-webkit-scrollbar { height: 8px; }
    .table-scroll::-webkit-scrollbar-track { background: #f0f5ff; border-radius: 10px; }
    .table-scroll::-webkit-scrollbar-thumb { background: #bde0fe; border-radius: 10px; border: 2px solid #f0f5ff; }
    .table-scroll::-webkit-scrollbar-thumb:hover { background: #8faaf3; }
</style>

<div class="wiboost-font pb-12 relative z-10 w-full min-w-0">
    <div class="mb-6 sm:mb-8 flex flex-col md:flex-row gap-3 sm:gap-4 relative z-10 w-full min-w-0">
        <div class="flex-1 rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-white/90 backdrop-blur-sm p-3 sm:p-4 shadow-xl shadow-[#bde0fe]/30 transition-transform duration-300 hover:border-[#bde0fe] min-w-0">
            <form action="{{ route('admin.tutorials.index') }}" method="GET" class="flex gap-2 sm:gap-3 items-center w-full">
                <input type="text" name="search" value="{{ request('search') }}"
                    class="min-w-0 flex-1 rounded-[1.2rem] sm:rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] py-3 sm:py-4 px-5 sm:px-6 font-black text-[#2b3a67] outline-none transition shadow-inner placeholder-[#a3bbfb] focus:border-[#bde0fe] text-xs sm:text-sm truncate"
                    placeholder="Cari judul, kategori utama, kategori kedua, atau isi tutorial...">

                <button type="submit" class="shrink-0 flex items-center justify-center rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#5a76c8] w-12 h-12 text-white shadow-md shadow-[#5a76c8]/25 transition-transform active:scale-95 hover:bg-[#4760a9] text-base" title="Cari Tutorial">
                    <span>🔍</span>
                </button>

                @if(request('search'))
                    <a href="{{ route('admin.tutorials.index') }}" class="shrink-0 flex items-center justify-center rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#ffe5e5] w-12 h-12 text-[#ff6b6b] shadow-md shadow-[#ff6b6b]/20 transition-transform active:scale-95 hover:bg-[#ffcccc] text-base" title="Reset Pencarian">
                        <span>×</span>
                    </a>
                @endif
            </form>
        </div>

        <a href="{{ route('admin.tutorials.create') }}" class="shrink-0 flex items-center justify-center gap-2 rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#5a76c8] px-5 py-3 text-sm font-black text-white shadow-md shadow-[#5a76c8]/25 transition-transform active:scale-95 hover:bg-[#4760a9] w-full md:w-auto md:h-12 min-w-0">
            <span>+</span> Tambah Tutorial
        </a>
    </div>

    @if(session('success'))
        <div class="bg-[#e6fff7] border-4 border-white text-emerald-500 px-5 sm:px-6 py-4 rounded-[2rem] mb-6 sm:mb-8 font-black flex items-center gap-3 shadow-lg shadow-emerald-100/50 relative z-10 w-full text-sm sm:text-base">
            <span class="text-xl sm:text-2xl drop-shadow-sm">✓</span>
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-5 sm:px-6 py-4 rounded-[2rem] mb-6 sm:mb-8 font-black flex items-center gap-3 shadow-lg shadow-[#ff6b6b]/20 relative z-10 w-full text-sm sm:text-base">
            <span class="text-xl sm:text-2xl drop-shadow-sm">!</span>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <div class="bg-white/95 backdrop-blur-sm rounded-[2rem] sm:rounded-[2.5rem] shadow-2xl shadow-[#bde0fe]/30 border-4 border-white flex flex-col overflow-hidden relative z-10 min-w-0 w-full">
        <div class="w-full overflow-x-auto table-scroll pb-2">
            <div class="inline-block min-w-full align-middle p-4 sm:p-6">
                <table class="w-full min-w-[820px] text-left whitespace-nowrap">
                    <thead>
                        <tr>
                            <th class="px-5 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-4 border-dashed border-[#f4f9ff]">Struktur Tutorial</th>
                            <th class="px-5 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-4 border-dashed border-[#f4f9ff]">Isi</th>
                            <th class="px-5 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-4 border-dashed border-[#f4f9ff] text-center">Status</th>
                            <th class="px-5 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-4 border-dashed border-[#f4f9ff] text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                        @forelse($tutorialSections ?? [] as $section)
                            <tr class="bg-[#f8faff]">
                                <td colspan="4" class="px-5 py-3">
                                    <span class="inline-flex rounded-full border-2 border-white bg-white px-4 py-1.5 text-[10px] font-black uppercase tracking-widest text-[#5a76c8] shadow-sm">
                                        Kategori Utama: {{ $section['name'] }}
                                    </span>
                                </td>
                            </tr>

                            @foreach($section['subcategories'] as $subcategory)
                                <tr class="bg-white">
                                    <td colspan="4" class="px-5 py-4">
                                        <div class="flex items-center gap-4 rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-4 py-3 shadow-inner">
                                            <div class="h-14 w-14 shrink-0 overflow-hidden rounded-2xl border-2 border-white bg-white p-2 shadow-sm">
                                                @if(!empty($subcategory['image']))
                                                    <img src="{{ Storage::url($subcategory['image']) }}" alt="{{ $subcategory['name'] }}" class="h-full w-full object-contain">
                                                @else
                                                    <span class="flex h-full w-full items-center justify-center text-xl font-black text-[#5a76c8]">{{ Str::upper(Str::substr($subcategory['name'], 0, 1)) }}</span>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="text-[10px] font-black uppercase tracking-widest text-[#4bc6b9]">Kategori Kedua</p>
                                                <p class="text-base font-black text-[#2b3a67]">{{ $subcategory['name'] }}</p>
                                                <p class="text-[10px] font-bold text-[#8faaf3]">{{ count($subcategory['tutorials']) }} tutorial</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                @foreach($subcategory['tutorials'] as $tutorial)
                                    <tr class="hover:bg-[#f8faff] transition-colors group">
                                        <td class="px-5 py-4 sm:py-5 align-middle min-w-[260px]">
                                            <div class="min-w-0">
                                                <p class="font-black text-[#2b3a67] text-sm mb-1 truncate">{{ $tutorial->title ?? '-' }}</p>
                                                <p class="text-[9px] sm:text-[10px] font-bold text-[#8faaf3] bg-[#f4f9ff] inline-block px-2 py-1 rounded-md border border-white truncate max-w-full">
                                                    {{ $section['name'] }} / {{ $subcategory['name'] }}
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4 sm:py-5 align-middle min-w-[240px] whitespace-normal">
                                            <div class="flex flex-wrap gap-2">
                                                @if($tutorial->youtube_url)
                                                    <span class="bg-[#ffe5e5] text-[#ff6b6b] px-3 py-1.5 rounded-md text-[9px] font-black uppercase tracking-widest border border-white shadow-sm">Video</span>
                                                @endif
                                                @if($tutorial->content)
                                                    <span class="bg-[#fffcf0] text-amber-500 px-3 py-1.5 rounded-md text-[9px] font-black uppercase tracking-widest border border-white shadow-sm">Artikel</span>
                                                @endif
                                                @if(!$tutorial->youtube_url && !$tutorial->content)
                                                    <span class="bg-[#f0f5ff] text-[#8faaf3] px-3 py-1.5 rounded-md text-[9px] font-black uppercase tracking-widest border border-white shadow-sm">Kosong</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-5 py-4 sm:py-5 align-middle text-center">
                                            @if($tutorial->is_active)
                                                <span class="bg-[#e6fff7] text-emerald-500 px-4 py-2 rounded-full text-[9px] font-black uppercase tracking-widest border border-white shadow-sm">Aktif</span>
                                            @else
                                                <span class="bg-[#ffe5e5] text-[#ff6b6b] px-4 py-2 rounded-full text-[9px] font-black uppercase tracking-widest border border-white shadow-sm">Sembunyi</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4 sm:py-5 align-middle text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <a href="{{ route('admin.tutorials.edit', $tutorial->id) }}" class="w-9 h-9 sm:w-10 sm:h-10 bg-white border-2 border-white rounded-[1rem] flex items-center justify-center text-sm font-black text-[#5a76c8] hover:bg-[#f0f5ff] transition-transform hover:scale-110 active:scale-95 shadow-md shadow-[#bde0fe]/20" title="Edit Tutorial">
                                                    Edit
                                                </a>
                                                <form action="{{ route('admin.tutorials.destroy', $tutorial->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus tutorial ini?');" class="inline-block m-0">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="w-9 h-9 sm:w-10 sm:h-10 bg-white border-2 border-white rounded-[1rem] flex items-center justify-center text-sm font-black text-[#ff6b6b] hover:bg-[#ffe5e5] transition-transform hover:scale-110 active:scale-95 shadow-md shadow-[#ff6b6b]/20" title="Hapus Tutorial">
                                                        Del
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-16 text-center">
                                    <div class="inline-flex items-center justify-center w-20 h-20 sm:w-24 sm:h-24 rounded-[2rem] sm:rounded-[2.5rem] bg-[#f0f5ff] border-4 border-white mb-4 sm:mb-5 shadow-inner">
                                        <span class="text-3xl font-black text-[#5a76c8]">?</span>
                                    </div>
                                    <p class="text-[#5a76c8] font-black text-lg sm:text-xl">Belum ada tutorial.</p>
                                    <p class="text-[#8faaf3] font-bold mt-2 text-sm sm:text-base">Buat kategori dan tutorial pertama dari tombol tambah.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
