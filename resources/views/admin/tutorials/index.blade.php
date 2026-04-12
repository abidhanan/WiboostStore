@extends('layouts.admin')
@section('title', 'Manajemen Tutorial')
@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
</style>

<div class="wiboost-font pb-12 max-w-6xl mx-auto">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-10 pl-2">
        <div>
            <h2 class="text-3xl font-black text-[#2b3a67] tracking-tight">Pusat Tutorial & Bantuan 📚</h2>
            <p class="text-[#8faaf3] font-bold text-sm mt-1">Kelola panduan artikel dan video YouTube untuk pelanggan.</p>
        </div>
        <a href="{{ route('admin.tutorials.create') }}" class="bg-[#5a76c8] hover:bg-[#4760a9] text-white px-8 py-3.5 rounded-full font-black transition-transform active:scale-95 shadow-lg shadow-[#5a76c8]/30 border-4 border-white flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
            Tambah Tutorial
        </a>
    </div>

    @if(session('success'))
        <div class="bg-[#e6fff7] border-4 border-white text-emerald-500 px-6 py-4 rounded-[2rem] mb-8 font-black flex items-center gap-3 shadow-sm">
            <span class="text-2xl">🎉</span> {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($tutorials as $tut)
            <div class="bg-white rounded-[2rem] p-6 border-4 border-white shadow-sm hover:border-[#bde0fe] hover:shadow-lg transition-all flex flex-col h-full group">
                <div class="flex justify-between items-start mb-4">
                    <div class="w-14 h-14 bg-[#f4f9ff] rounded-[1.2rem] flex items-center justify-center text-3xl border-2 border-white shadow-inner overflow-hidden shrink-0">
                        @if($tut->image)
                            <img src="{{ Storage::url($tut->image) }}" class="w-full h-full object-cover">
                        @else
                            {{ $tut->icon ?? '📖' }}
                        @endif
                    </div>
                    <div class="flex flex-col items-end gap-1">
                        <span class="bg-[#f0f5ff] text-[#5a76c8] text-[9px] font-black px-2 py-1 rounded-lg uppercase">{{ $tut->category }}</span>
                        @if(!$tut->is_active)
                            <span class="bg-gray-100 text-gray-400 text-[9px] font-black px-2 py-1 rounded-lg uppercase tracking-widest">Sembunyi</span>
                        @endif
                    </div>
                </div>

                <h4 class="font-black text-lg text-[#2b3a67] mb-2 leading-tight">{{ $tut->title }}</h4>
                <p class="text-xs font-bold text-[#8faaf3] mb-6 flex-1 line-clamp-3">{{ $tut->description }}</p>

                <div class="flex items-center gap-2 mt-auto pt-4 border-t-2 border-dashed border-[#f0f5ff]">
                    <a href="{{ route('admin.tutorials.edit', $tut->id) }}" class="flex-1 bg-[#f4f9ff] hover:bg-[#5a76c8] hover:text-white text-[#5a76c8] px-4 py-2.5 rounded-xl font-black text-xs transition-colors border-2 border-white shadow-sm text-center">Edit</a>
                    <form action="{{ route('admin.tutorials.destroy', $tut->id) }}" method="POST" class="flex-1 m-0" onsubmit="return confirm('Yakin ingin menghapus tutorial ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-full bg-[#ffe5e5] hover:bg-[#ff6b6b] hover:text-white text-[#ff6b6b] px-4 py-2.5 rounded-xl font-black text-xs transition-colors border-2 border-white shadow-sm">Hapus</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-20 bg-white rounded-[3rem] border-4 border-dashed border-[#bde0fe]">
                <div class="text-7xl mb-6 opacity-40">📖</div>
                <p class="text-[#5a76c8] font-black text-xl">Belum ada Tutorial tersedia.</p>
                <p class="text-[#8faaf3] font-bold mt-2">Bantu pelangganmu dengan membuat panduan pertama!</p>
            </div>
        @endforelse
    </div>
</div>
@endsection