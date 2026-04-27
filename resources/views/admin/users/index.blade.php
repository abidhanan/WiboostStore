@extends('layouts.admin')

@section('title', 'Manajemen Pengguna')
@section('admin_header_subtitle', 'Atur data admin dan pelanggan Wiboost Store.')
@section('admin_header_actions')
    <a href="{{ route('admin.users.create') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-full border-4 border-white bg-[#5a76c8] px-8 py-3.5 text-sm font-extrabold text-white shadow-xl shadow-[#5a76c8]/30 transition-transform active:scale-95 hover:bg-[#4760a9] sm:w-auto">
        ✨ Tambah Pengguna
    </a>
@endsection

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
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
</style>

<div class="wiboost-font pb-12 relative z-10">

    <div class="absolute top-10 right-10 text-5xl animate-float opacity-30 pointer-events-none hidden md:block z-0">👥</div>
    <div class="absolute top-1/3 left-5 text-4xl animate-float-delayed opacity-30 pointer-events-none hidden md:block z-0">✨</div>
    <div class="absolute bottom-20 right-[15%] text-6xl animate-float opacity-20 pointer-events-none hidden md:block z-0">☁️</div>

    <div class="mb-8 rounded-[2.5rem] border-4 border-white bg-white/90 backdrop-blur-sm p-6 shadow-xl shadow-[#bde0fe]/30 transition-transform duration-300 hover:border-[#bde0fe] relative z-10">
        <form action="{{ route('admin.users.index') }}" method="GET" class="flex flex-col gap-4 md:flex-row">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-6 text-[#8faaf3]">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] py-4 pl-16 pr-5 font-black text-[#2b3a67] outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe] shadow-inner"
                    placeholder="Cari nama, email, nomor, admin, atau pelanggan...">
            </div>
            <button type="submit" class="rounded-full border-4 border-white bg-[#5a76c8] px-10 py-4 font-black text-white shadow-xl shadow-[#5a76c8]/30 transition-transform active:scale-95 hover:bg-[#4760a9]">
                Cari Pengguna
            </button>
            @if(request('search'))
                <a href="{{ route('admin.users.index') }}" class="flex items-center justify-center rounded-full border-4 border-white bg-[#ffe5e5] px-8 py-4 font-black text-[#ff6b6b] transition-transform active:scale-95 hover:bg-[#ffcccc] shadow-md shadow-[#ff6b6b]/20">
                    Reset
                </a>
            @endif
        </form>
    </div>

    @if (session('success'))
        <div class="bg-[#e6fff7] border-4 border-white text-emerald-500 px-6 py-4 rounded-[2.5rem] mb-8 font-black shadow-lg shadow-emerald-100/50 flex items-center gap-4 relative z-10">
            <span class="text-3xl drop-shadow-sm">✅</span>
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-6 py-4 rounded-[2.5rem] mb-8 font-black shadow-lg shadow-[#ff6b6b]/20 flex items-center gap-4 relative z-10">
            <span class="text-3xl drop-shadow-sm">⚠️</span>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <div class="bg-white/95 backdrop-blur-sm rounded-[2.5rem] shadow-2xl shadow-[#bde0fe]/30 border-4 border-white flex flex-col overflow-hidden relative z-10">
        <div class="overflow-x-auto table-scroll w-full p-4 md:p-6">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr>
                        <th class="px-5 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-4 border-dashed border-[#f4f9ff]">Nama & Email</th>
                        <th class="px-5 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-4 border-dashed border-[#f4f9ff]">Nomor Kontak</th>
                        <th class="px-5 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-4 border-dashed border-[#f4f9ff] text-center">Role</th>
                        <th class="px-5 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-4 border-dashed border-[#f4f9ff]">Saldo</th>
                        <th class="px-5 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-4 border-dashed border-[#f4f9ff] text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                    @forelse($users as $user)
                        <tr class="hover:bg-[#f8faff] transition-colors group">
                            <td class="px-5 py-5 align-middle">
                                <p class="font-black text-[#2b3a67] text-sm mb-1">{{ $user->name }}</p>
                                <p class="text-[11px] font-bold text-[#8faaf3] bg-[#f4f9ff] inline-block px-3 py-1 rounded-md border border-white">{{ $user->email }}</p>
                            </td>

                            <td class="px-5 py-5 align-middle">
                                <p class="font-black text-[#5a76c8] text-sm">{{ $user->whatsapp ?? '-' }}</p>
                            </td>

                            <td class="px-5 py-5 align-middle text-center">
                                @if($user->role_id == 1)
                                    <span class="bg-[#ffe5e5] text-[#ff6b6b] px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest border-2 border-white shadow-sm inline-block w-24">Admin</span>
                                @else
                                    <span class="bg-[#e0fbfc] text-[#5a76c8] px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest border-2 border-white shadow-sm inline-block w-24">Buyer</span>
                                @endif
                            </td>

                            <td class="px-5 py-5 align-middle">
                                <p class="font-black text-[#4bc6b9] text-base drop-shadow-sm">Rp {{ number_format($user->balance, 0, ',', '.') }}</p>
                            </td>

                            <td class="px-5 py-5 align-middle text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="w-10 h-10 bg-white border-2 border-white rounded-[1rem] flex items-center justify-center text-xl hover:bg-[#f0f5ff] transition-transform hover:scale-110 active:scale-95 shadow-md shadow-[#bde0fe]/20" title="Edit Pengguna">
                                        ✏️
                                    </a>
                                    @if(auth()->id() != $user->id)
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus pengguna ini secara permanen?');" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-10 h-10 bg-white border-2 border-white rounded-[1rem] flex items-center justify-center text-xl hover:bg-[#ffe5e5] transition-transform hover:scale-110 active:scale-95 shadow-md shadow-[#ff6b6b]/20" title="Hapus Pengguna">
                                                🗑️
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-20 text-center">
                                <div class="inline-flex items-center justify-center w-24 h-24 rounded-[2.5rem] bg-[#f0f5ff] border-4 border-white mb-5 shadow-inner">
                                    <span class="text-5xl animate-float">👥</span>
                                </div>
                                <p class="text-[#5a76c8] font-black text-xl">Belum ada pengguna terdaftar.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if(method_exists($users, 'links') && $users->hasPages())
            <div class="p-6 md:p-8 border-t-4 border-dashed border-[#f4f9ff] bg-[#f8faff]">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection