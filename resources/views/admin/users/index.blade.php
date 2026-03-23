@extends('layouts.admin')

@section('title', 'Manajemen Pengguna')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
    .table-scroll::-webkit-scrollbar { height: 6px; }
    .table-scroll::-webkit-scrollbar-track { background: #f0f5ff; border-radius: 10px; }
    .table-scroll::-webkit-scrollbar-thumb { background: #bde0fe; border-radius: 10px; }
    .table-scroll::-webkit-scrollbar-thumb:hover { background: #8faaf3; }
</style>

<div class="wiboost-font pb-12">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 pl-2">
        <div>
            <h2 class="text-3xl font-black text-[#2b3a67] tracking-tight">Manajemen Pengguna 👥</h2>
            <p class="text-sm font-bold text-[#8faaf3] mt-1">Atur data admin dan pelanggan Wiboost Store.</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="inline-flex items-center justify-center gap-2 bg-[#5a76c8] text-white px-6 py-3 rounded-full text-sm font-extrabold hover:bg-[#4760a9] hover:-translate-y-1 transition-all shadow-lg shadow-[#5a76c8]/30 border-2 border-white w-full md:w-auto">
            + Tambah Pengguna
        </a>
    </div>

    @if (session('success'))
        <div class="bg-[#e6fff7] border-4 border-white text-emerald-500 px-6 py-4 rounded-[2rem] mb-8 font-black shadow-sm flex items-start gap-4">
            <span class="text-2xl mt-1">✅</span>
            <p class="mt-1.5">{{ session('success') }}</p>
        </div>
    @endif
    
    @if (session('error'))
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-6 py-4 rounded-[2rem] mb-8 font-black shadow-sm flex items-start gap-4">
            <span class="text-2xl mt-1">⚠️</span>
            <p class="mt-1.5">{{ session('error') }}</p>
        </div>
    @endif

    <div class="bg-white rounded-[2.5rem] shadow-lg shadow-[#bde0fe]/20 border-4 border-white flex flex-col overflow-hidden">
        <div class="overflow-x-auto table-scroll w-full">
            <table class="w-full text-left border-collapse">
                <thead class="bg-[#f4f9ff]">
                    <tr>
                        <th class="px-6 py-5 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-2 border-dashed border-[#e0fbfc]">Nama & Email</th>
                        <th class="px-6 py-5 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-2 border-dashed border-[#e0fbfc]">WhatsApp</th>
                        <th class="px-6 py-5 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-2 border-dashed border-[#e0fbfc]">Role</th>
                        <th class="px-6 py-5 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-2 border-dashed border-[#e0fbfc]">Saldo</th>
                        <th class="px-6 py-5 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-2 border-dashed border-[#e0fbfc] text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-dashed divide-[#f0f5ff]">
                    @forelse($users as $user)
                    <tr class="hover:bg-[#f4f9ff] transition-colors group">
                        
                        <td class="px-6 py-4 align-middle">
                            <p class="font-black text-[#2b3a67] text-sm mb-1">{{ $user->name }}</p>
                            <p class="text-[11px] font-bold text-[#8faaf3]">{{ $user->email }}</p>
                        </td>
                        
                        <td class="px-6 py-4 align-middle">
                            <a href="https://wa.me/{{ preg_replace('/^0/', '62', $user->whatsapp) }}" target="_blank" class="font-black text-[#5a76c8] text-xs hover:underline flex items-center gap-1">
                                💬 {{ $user->whatsapp ?? '-' }}
                            </a>
                        </td>
                        
                        <td class="px-6 py-4 align-middle">
                            @if($user->role_id == 1)
                                <span class="bg-[#ffe5e5] text-[#ff6b6b] px-3 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest border border-white shadow-sm inline-block">👑 Admin</span>
                            @else
                                <span class="bg-[#e0fbfc] text-[#5a76c8] px-3 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest border border-white shadow-sm inline-block">👤 Buyer</span>
                            @endif
                        </td>

                        <td class="px-6 py-4 align-middle">
                            <p class="font-black text-[#4bc6b9] text-sm">Rp {{ number_format($user->balance, 0, ',', '.') }}</p>
                        </td>
                        
                        <td class="px-6 py-4 align-middle text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="w-8 h-8 bg-white border-2 border-[#f0f5ff] rounded-xl flex items-center justify-center text-[#5a76c8] hover:bg-[#5a76c8] hover:border-[#5a76c8] hover:text-white transition-colors shadow-sm" title="Edit">
                                    ✏️
                                </a>
                                @if(auth()->id() != $user->id)
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus pengguna ini?');" class="inline-block">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-8 h-8 bg-white border-2 border-[#f0f5ff] rounded-xl flex items-center justify-center text-[#ff6b6b] hover:bg-[#ff6b6b] hover:border-[#ff6b6b] hover:text-white transition-colors shadow-sm" title="Hapus">
                                        🗑️
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center">
                            <div class="inline-flex items-center justify-center w-14 h-14 rounded-[2rem] bg-[#f0f5ff] border-4 border-white mb-3 shadow-inner"><span class="text-2xl">📭</span></div>
                            <p class="text-[#8faaf3] font-black text-xs">Belum ada pengguna terdaftar.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection