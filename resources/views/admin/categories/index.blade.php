@extends('layouts.admin')

@section('title', 'Manajemen Kategori')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
</style>

<div class="wiboost-font pb-12">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4 pl-2">
        <div>
            <h3 class="text-3xl font-black text-[#2b3a67] tracking-tight">Kategori Layanan 🗂️</h3>
            <p class="text-sm text-[#8faaf3] font-bold mt-1">Kelola daftar kategori yang tampil di Halaman Depan.</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="bg-[#5a76c8] hover:bg-[#4760a9] text-white px-6 py-3 rounded-full font-black transition-transform active:scale-95 flex items-center gap-2 shadow-lg shadow-[#5a76c8]/30 border-4 border-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
            Tambah Kategori
        </a>
    </div>

    @if(session('success'))
        <div class="bg-[#e6fff7] border-4 border-white text-emerald-500 px-6 py-4 rounded-[2rem] mb-8 font-black flex items-center gap-3 shadow-sm">
            <span class="text-2xl">🎉</span> {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-[2rem] shadow-lg shadow-[#bde0fe]/20 border-4 border-white overflow-hidden">
        <div class="overflow-x-auto p-4">
            <table class="w-full text-left whitespace-nowrap">
                <thead>
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest w-16">No</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Nama Kategori</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest">Slug (URL)</th>
                        <th class="px-6 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-dashed divide-[#f0f5ff]">
                    @forelse($categories as $index => $category)
                    <tr class="hover:bg-[#f4f9ff] transition-colors rounded-xl group">
                        <td class="px-6 py-4 font-black text-[#8faaf3]">{{ $index + 1 }}</td>
                        <td class="px-6 py-4">
                            <p class="font-black text-[#2b3a67] text-lg">{{ $category->name }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="bg-[#f0f5ff] text-[#5a76c8] px-3 py-1.5 rounded-full text-xs font-bold border border-white shadow-inner">
                                {{ $category->slug }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.categories.edit', $category->id) }}" class="bg-[#fff5eb] text-amber-500 hover:bg-amber-500 hover:text-white p-2.5 rounded-xl transition-colors shadow-sm border-2 border-white" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </a>
                                <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus kategori ini?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="bg-[#ffe5e5] text-[#ff6b6b] hover:bg-[#ff6b6b] hover:text-white p-2.5 rounded-xl transition-colors shadow-sm border-2 border-white" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-[1.5rem] bg-[#f0f5ff] border-4 border-white mb-3 shadow-inner">
                                <span class="text-3xl">📂</span>
                            </div>
                            <p class="text-[#8faaf3] font-black text-sm">Belum ada kategori yang dibuat.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection