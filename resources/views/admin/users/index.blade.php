@extends('layouts.admin')
@section('title', 'Manajemen Pengguna')
@section('admin_header_subtitle', 'Atur data admin dan pelanggan Wiboost Store.')

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
</style>

<div class="wiboost-font pb-12 relative z-10 w-full min-w-0">
    <!-- Toolbar: Pencarian & Tombol Tambah -->
    <div class="mb-6 sm:mb-8 flex flex-col md:flex-row gap-3 sm:gap-4 relative z-10 w-full min-w-0">
        
        <!-- Form Pencarian (Kiri di Desktop / Atas di Mobile) -->
        <div class="flex-1 rounded-[2rem] sm:rounded-[2.5rem] border-4 border-white bg-white/90 backdrop-blur-sm p-3 sm:p-4 shadow-xl shadow-[#bde0fe]/30 transition-transform duration-300 hover:border-[#bde0fe] min-w-0">
            <form action="{{ route('admin.users.index') }}" method="GET" class="flex gap-2 sm:gap-3 items-center w-full">
                <!-- Input Field Tanpa SVG Kiri -->
                <input type="text" name="search" value="{{ request('search') }}"
                    class="min-w-0 flex-1 rounded-[1.2rem] sm:rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] py-3 sm:py-4 px-5 sm:px-6 font-black text-[#2b3a67] outline-none transition shadow-inner placeholder-[#a3bbfb] focus:border-[#bde0fe] text-xs sm:text-sm truncate"
                    placeholder="Cari Pengguna">
                
                <!-- Tombol Cari Emoji Saja -->
                <button type="submit" class="shrink-0 flex items-center justify-center rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#5a76c8] w-12 h-12 text-white shadow-md shadow-[#5a76c8]/25 transition-transform active:scale-95 hover:bg-[#4760a9] text-base" title="Cari Pengguna">
                    🔍
                </button>
                
                <!-- Tombol Reset Emoji -->
                @if(request('search'))
                    <a href="{{ route('admin.users.index') }}" class="shrink-0 flex items-center justify-center rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#ffe5e5] w-12 h-12 text-[#ff6b6b] shadow-md shadow-[#ff6b6b]/20 transition-transform active:scale-95 hover:bg-[#ffcccc] text-base" title="Reset Pencarian">
                        ✖️
                    </a>
                @endif
            </form>
        </div>

        <!-- Tombol Tambah Pengguna (Kanan di Desktop / Bawah di Mobile) -->
        <a href="{{ route('admin.users.create') }}" class="shrink-0 flex items-center justify-center gap-2 rounded-[1rem] sm:rounded-[1.2rem] border-4 border-white bg-[#5a76c8] px-5 py-3 text-sm font-black text-white shadow-md shadow-[#5a76c8]/25 transition-transform active:scale-95 hover:bg-[#4760a9] w-full md:w-auto md:min-w-[190px] md:px-8 md:py-4 md:text-base min-w-0">
            <span class="text-base">➕</span> Tambah Pengguna
        </a>
    </div>

    <!-- Alert Messages -->
    @if (session('success'))
        <div class="bg-[#e6fff7] border-4 border-white text-emerald-500 px-5 sm:px-6 py-4 rounded-[2rem] mb-6 sm:mb-8 font-black flex items-center gap-3 shadow-lg shadow-emerald-100/50 relative z-10 w-full text-sm sm:text-base">
            <span class="text-2xl sm:text-3xl drop-shadow-sm">✅</span>
            <p>{{ session('success') }}</p>
        </div>
    @endif
    @if (session('error'))
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-5 sm:px-6 py-4 rounded-[2rem] mb-6 sm:mb-8 font-black flex items-center gap-3 shadow-lg shadow-[#ff6b6b]/20 relative z-10 w-full text-sm sm:text-base">
            <span class="text-2xl sm:text-3xl drop-shadow-sm">⚠️</span>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <!-- Tabel Data Pengguna -->
    <div class="bg-white/95 backdrop-blur-sm rounded-[2rem] sm:rounded-[2.5rem] shadow-2xl shadow-[#bde0fe]/30 border-4 border-white flex flex-col overflow-hidden relative z-10 min-w-0 w-full">
        <div class="w-full overflow-x-auto table-scroll pb-2">
            <div class="inline-block min-w-full align-middle p-4 sm:p-6">
                <table class="w-full min-w-[1080px] text-left whitespace-nowrap">
                    <thead>
                        <tr>
                            <th class="px-5 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-4 border-dashed border-[#f4f9ff]">Nama & Email</th>
                            <th class="px-5 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-4 border-dashed border-[#f4f9ff]">Nomor Kontak</th>
                            <th class="px-5 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-4 border-dashed border-[#f4f9ff] text-center">Role Efektif</th>
                            <th class="px-5 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-4 border-dashed border-[#f4f9ff] text-center">Masa Member</th>
                            <th class="px-5 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-4 border-dashed border-[#f4f9ff]">Saldo</th>
                            <th class="px-5 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-4 border-dashed border-[#f4f9ff] text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                        @forelse($users as $user)
                            <tr class="hover:bg-[#f8faff] transition-colors group">
                                <td class="min-w-[300px] px-5 py-4 align-middle sm:py-5">
                                    <p class="mb-1 whitespace-nowrap text-sm font-black text-[#2b3a67]">{{ $user->name }}</p>
                                    <p class="inline-block whitespace-nowrap rounded-md border border-white bg-[#f4f9ff] px-3 py-1 text-[10px] font-bold text-[#8faaf3] sm:text-[11px]">{{ $user->email }}</p>
                                </td>
                                <td class="px-5 py-4 sm:py-5 align-middle">
                                    <p class="font-black text-[#5a76c8] text-sm">{{ $user->whatsapp ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-4 sm:py-5 align-middle text-center">
                                    @if($user->isMember())
                                        <span class="bg-[#ede9fe] text-[#8b5cf6] px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest border-2 border-white shadow-sm inline-block w-24">Member</span>
                                    @elseif($user->role_id == 1)
                                        <span class="bg-[#ffe5e5] text-[#ff6b6b] px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest border-2 border-white shadow-sm inline-block w-24">Admin</span>
                                    @elseif($user->role_id == 3)
                                        <span class="bg-[#fff7d6] text-[#d99b16] px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest border-2 border-white shadow-sm inline-block w-24">Reseller</span>
                                    @else
                                        <span class="bg-[#e0fbfc] text-[#5a76c8] px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest border-2 border-white shadow-sm inline-block w-24">Buyer</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 sm:py-5 align-middle text-center">
                                    @if($user->isMember())
                                        <span class="inline-block rounded-full border-2 border-white bg-[#ede9fe] px-4 py-1.5 text-[9px] font-black uppercase tracking-widest text-[#8b5cf6] shadow-sm">Member</span>
                                        <p class="mt-2 text-[10px] font-bold text-[#8faaf3]">{{ $user->membership_expires_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</p>
                                    @else
                                        <span class="inline-block rounded-full border-2 border-white bg-[#f4f9ff] px-4 py-1.5 text-[9px] font-black uppercase tracking-widest text-[#8faaf3] shadow-sm">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 sm:py-5 align-middle">
                                    <p class="font-black text-[#4bc6b9] text-sm sm:text-base drop-shadow-sm">Rp {{ number_format($user->balance, 0, ',', '.') }}</p>
                                </td>
                                <td class="px-5 py-4 sm:py-5 align-middle text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.users.edit', $user->id) }}" class="w-9 h-9 sm:w-10 sm:h-10 bg-white border-2 border-white rounded-[1rem] flex items-center justify-center text-lg sm:text-xl hover:bg-[#f0f5ff] transition-transform hover:scale-110 active:scale-95 shadow-md shadow-[#bde0fe]/20" title="Edit Pengguna">
                                            ✏️
                                        </a>
                                        @if(auth()->id() != $user->id)
                                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus pengguna ini secara permanen?');" class="inline-block m-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-9 h-9 sm:w-10 sm:h-10 bg-white border-2 border-white rounded-[1rem] flex items-center justify-center text-lg sm:text-xl hover:bg-[#ffe5e5] transition-transform hover:scale-110 active:scale-95 shadow-md shadow-[#ff6b6b]/20" title="Hapus Pengguna">
                                                    🗑️
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-16 text-center">
                                    <div class="inline-flex items-center justify-center w-20 h-20 sm:w-24 sm:h-24 rounded-[2rem] sm:rounded-[2.5rem] bg-[#f0f5ff] border-4 border-white mb-4 sm:mb-5 shadow-inner">
                                        <span class="text-4xl sm:text-5xl animate-float">📭</span>
                                    </div>
                                    <p class="text-[#5a76c8] font-black text-lg sm:text-xl">Belum ada pengguna terdaftar.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if(method_exists($users, 'links') && $users->hasPages())
            <div class="p-5 sm:p-8 border-t-4 border-dashed border-[#f4f9ff] bg-[#f8faff] min-w-0 w-full">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
