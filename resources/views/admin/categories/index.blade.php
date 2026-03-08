@extends('layouts.admin')

@section('title', 'Kelola Kategori')

@section('content')
<div class="mb-10">
    <div class="flex items-center gap-3 mb-2">
        <span class="bg-indigo-100 text-indigo-600 p-2 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
        </span>
        <h3 class="text-2xl font-extrabold text-slate-800">Struktur Kategori</h3>
    </div>
    <p class="text-slate-500">Organisir layanan Wiboost Store agar pelanggan lebih mudah melakukan pencarian.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
    
    <div class="lg:col-span-4 sticky top-0">
        <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200">
            <h4 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                Tambah Baru
                <span class="w-2 h-2 bg-indigo-500 rounded-full animate-pulse"></span>
            </h4>
            
            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf
                <div class="space-y-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Nama Kategori</label>
                        <input type="text" name="name" 
                               class="w-full px-5 py-3 rounded-2xl border border-slate-200 bg-slate-50 outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all text-sm font-medium" 
                               placeholder="Contoh: Voucher Game" required>
                    </div>
                    
                    <button type="submit" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-100 transition-all active:scale-95">
                        Simpan Kategori
                    </button>
                </div>
            </form>
        </div>

        @if(session('success'))
        <div class="mt-4 bg-emerald-50 border border-emerald-100 text-emerald-600 px-5 py-3 rounded-2xl text-sm font-bold flex items-center gap-3 animate-bounce">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
            {{ session('success') }}
        </div>
        @endif
    </div>

    <div class="lg:col-span-8">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-8 py-5 text-[11px] font-bold text-slate-400 uppercase tracking-[0.2em]">Nama & Slug</th>
                        <th class="px-8 py-5 text-[11px] font-bold text-slate-400 uppercase tracking-[0.2em] text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($categories as $cat)
                    <tr class="group hover:bg-slate-50/50 transition-colors">
                        <td class="px-8 py-5">
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-800">{{ $cat->name }}</span>
                                <span class="text-xs font-mono text-slate-400">/order/{{ $cat->slug }}</span>
                            </div>
                        </td>
                        <td class="px-8 py-5">
                            <div class="flex justify-center">
                                <form action="{{ route('admin.categories.destroy', $cat->id) }}" method="POST" 
                                      onsubmit="return confirm('Peringatan: Menghapus kategori akan berdampak pada produk di dalamnya. Lanjutkan?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" 
                                            class="text-rose-500 hover:bg-rose-50 px-4 py-2 rounded-xl font-bold text-[10px] uppercase tracking-widest transition-all">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" class="px-8 py-16 text-center">
                            <div class="flex flex-col items-center opacity-40">
                                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                <p class="text-sm font-medium">Belum ada kategori yang terdaftar.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection