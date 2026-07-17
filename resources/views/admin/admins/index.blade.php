@extends('layouts.admin')

@section('title', 'Halaman Admin')
@section('admin_header_subtitle', 'Kelola akun admin Wiboost Store.')

@section('admin_header_actions')
    <a href="{{ route('admin.users.create', ['role' => 1]) }}" class="inline-flex items-center justify-center rounded-full border-4 border-white bg-[#5a76c8] px-6 py-3 text-sm font-black text-white shadow-lg shadow-[#5a76c8]/25 transition active:scale-95">
        Tambah Admin
    </a>
@endsection

@section('content')
<div class="wiboost-font pb-12">
    <div class="mb-6 rounded-[2rem] border-4 border-white bg-white p-4 shadow-xl shadow-[#bde0fe]/25">
        <form method="GET" action="{{ route('admin.admins.index') }}" class="flex gap-3">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari admin" class="min-w-0 flex-1 rounded-[1.4rem] border-4 border-white bg-[#f4f9ff] px-5 py-3 font-black text-[#2b3a67] shadow-inner outline-none">
            <button class="rounded-[1.2rem] border-4 border-white bg-[#5a76c8] px-5 py-3 text-sm font-black text-white shadow-lg shadow-[#5a76c8]/25">Cari</button>
        </form>
    </div>

    <section class="rounded-[2rem] border-4 border-white bg-white p-5 shadow-xl shadow-[#bde0fe]/25 sm:p-7">
        <p class="text-xs font-black uppercase tracking-[0.22em] text-[#8faaf3]">Total Admin</p>
        <h2 class="mt-2 text-3xl font-black text-[#2b3a67]">{{ $admins->count() }}</h2>

        <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse($admins as $admin)
                <div class="rounded-[1.75rem] border-4 border-[#f4f9ff] bg-[#fbfdff] p-5">
                    <p class="text-lg font-black text-[#2b3a67]">{{ $admin->name }}</p>
                    <p class="mt-1 text-xs font-bold text-[#8faaf3]">{{ $admin->email }}</p>
                    <p class="mt-3 text-xs font-black uppercase tracking-widest text-[#5a76c8]">{{ $admin->whatsapp ?: '-' }}</p>
                    <div class="mt-5 flex gap-2">
                        <a href="{{ route('admin.users.edit', $admin) }}" class="inline-flex rounded-full border-4 border-white bg-[#f4f9ff] px-4 py-2 text-xs font-black text-[#5a76c8] shadow-sm">Edit</a>
                        @if(auth()->id() !== $admin->id)
                            <form method="POST" action="{{ route('admin.users.destroy', $admin) }}" onsubmit="return confirm('Yakin ingin menghapus admin ini?');">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-full border-4 border-white bg-[#ffe5e5] px-4 py-2 text-xs font-black text-[#ff6b6b] shadow-sm">Hapus</button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <p class="rounded-[1.75rem] border-4 border-dashed border-[#d7ecff] bg-[#f8fbff] p-10 text-center text-sm font-black text-[#5a76c8]">Belum ada admin.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
