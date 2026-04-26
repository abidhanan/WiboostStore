@extends('layouts.admin')

@section('title', 'Manajemen Pengguna')
@section('admin_header_subtitle', 'Atur data admin dan pelanggan Wiboost Store.')
@section('admin_header_actions')
    <a href="{{ route('admin.users.create') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-full border-2 border-white bg-[#5a76c8] px-6 py-3 text-sm font-extrabold text-white shadow-lg shadow-[#5a76c8]/30 transition-colors hover:bg-[#4760a9] sm:w-auto">
        + Tambah Pengguna
    </a>
@endsection

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
    <div class="mb-8 rounded-[2rem] border-4 border-white bg-white p-5 shadow-lg shadow-[#bde0fe]/20">
        <form action="{{ route('admin.users.index') }}" method="GET" class="flex flex-col gap-4 md:flex-row">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-5 text-[#8faaf3]">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="w-full rounded-[1.5rem] border-2 border-[#e0fbfc] bg-[#f4f9ff] py-4 pl-14 pr-5 font-black text-[#2b3a67] outline-none transition placeholder-[#a3bbfb] focus:border-[#5a76c8]"
                    placeholder="Cari nama, email, nomor kontak, admin, atau pelanggan...">
            </div>
            <button type="submit" class="rounded-[1.5rem] border-2 border-white bg-[#5a76c8] px-10 py-4 font-black text-white shadow-lg shadow-[#5a76c8]/30 transition hover:bg-[#4760a9]">
                Cari Pengguna
            </button>
            @if(request('search'))
                <a href="{{ route('admin.users.index') }}" class="flex items-center justify-center rounded-[1.5rem] border-2 border-white bg-[#ffe5e5] px-8 py-4 font-black text-[#ff6b6b] transition hover:bg-[#ffcccc]">
                    Reset
                </a>
            @endif
        </form>
    </div>

    @if (session('success'))
        <div class="bg-[#e6fff7] border-4 border-white text-emerald-500 px-6 py-4 rounded-[2rem] mb-8 font-black shadow-sm flex items-start gap-4">
            <span class="text-2xl mt-1">OK</span>
            <p class="mt-1.5">{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-6 py-4 rounded-[2rem] mb-8 font-black shadow-sm flex items-start gap-4">
            <span class="text-2xl mt-1">!</span>
            <p class="mt-1.5">{{ session('error') }}</p>
        </div>
    @endif

    <div class="bg-white rounded-[2.5rem] shadow-lg shadow-[#bde0fe]/20 border-4 border-white flex flex-col overflow-hidden">
        <div class="overflow-x-auto table-scroll w-full">
            <table class="w-full text-left border-collapse">
                <thead class="bg-[#f4f9ff]">
                    <tr>
                        <th class="px-6 py-5 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-2 border-dashed border-[#e0fbfc]">Nama & Email</th>
                        <th class="px-6 py-5 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-2 border-dashed border-[#e0fbfc]">Nomor Kontak</th>
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
                                <p class="font-black text-[#5a76c8] text-xs">{{ $user->whatsapp ?? '-' }}</p>
                            </td>

                            <td class="px-6 py-4 align-middle">
                                @if($user->role_id == 1)
                                    <span class="bg-[#ffe5e5] text-[#ff6b6b] px-3 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest border border-white shadow-sm inline-block">Admin</span>
                                @else
                                    <span class="bg-[#e0fbfc] text-[#5a76c8] px-3 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest border border-white shadow-sm inline-block">Buyer</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 align-middle">
                                <p class="font-black text-[#4bc6b9] text-sm">Rp {{ number_format($user->balance, 0, ',', '.') }}</p>
                            </td>

                            <td class="px-6 py-4 align-middle text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="w-8 h-8 bg-white border-2 border-[#f0f5ff] rounded-xl flex items-center justify-center text-[#5a76c8] hover:bg-[#5a76c8] hover:border-[#5a76c8] hover:text-white transition-colors shadow-sm" title="Edit">
                                        E
                                    </a>
                                    @if(auth()->id() != $user->id)
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus pengguna ini?');" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-8 h-8 bg-white border-2 border-[#f0f5ff] rounded-xl flex items-center justify-center text-[#ff6b6b] hover:bg-[#ff6b6b] hover:border-[#ff6b6b] hover:text-white transition-colors shadow-sm" title="Hapus">
                                                X
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="inline-flex items-center justify-center w-14 h-14 rounded-[2rem] bg-[#f0f5ff] border-4 border-white mb-3 shadow-inner"><span class="text-2xl">LIST</span></div>
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
