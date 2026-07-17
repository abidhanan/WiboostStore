@extends('layouts.admin')

@section('title', 'Halaman Member')
@section('admin_header_subtitle', 'Pantau member aktif dan masa berlaku.')

@section('content')
<div class="wiboost-font pb-12">
    <div class="mb-6 rounded-[2rem] border-4 border-white bg-white p-4 shadow-xl shadow-[#bde0fe]/25">
        <form method="GET" action="{{ route('admin.members.index') }}" class="flex gap-3">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari member" class="min-w-0 flex-1 rounded-[1.4rem] border-4 border-white bg-[#f4f9ff] px-5 py-3 font-black text-[#2b3a67] shadow-inner outline-none">
            <button class="rounded-[1.2rem] border-4 border-white bg-[#8b5cf6] px-5 py-3 text-sm font-black text-white shadow-lg shadow-[#8b5cf6]/25">Cari</button>
        </form>
    </div>

    <section class="rounded-[2rem] border-4 border-white bg-white p-5 shadow-xl shadow-[#bde0fe]/25 sm:p-7">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.22em] text-[#8b5cf6]">Total Member</p>
                <h2 class="mt-2 text-3xl font-black text-[#2b3a67]">{{ $members->count() }}</h2>
            </div>
        </div>
        <div class="mt-5 overflow-x-auto pb-2">
            <table class="w-full min-w-[760px] text-left">
                <thead>
                    <tr class="border-b-4 border-dashed border-[#f4f9ff]">
                        <th class="px-4 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Member</th>
                        <th class="px-4 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Role Dasar</th>
                        <th class="px-4 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Aktif Sampai</th>
                        <th class="px-4 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Saldo</th>
                        <th class="px-4 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                    @forelse($members as $member)
                        <tr>
                            <td class="px-4 py-4">
                                <p class="text-sm font-black text-[#2b3a67]">{{ $member->name }}</p>
                                <p class="mt-1 text-xs font-bold text-[#8faaf3]">{{ $member->email }}</p>
                            </td>
                            <td class="px-4 py-4 text-sm font-black text-[#5a76c8]">{{ $member->role_id == 3 ? 'Reseller' : 'Buyer' }}</td>
                            <td class="px-4 py-4 text-sm font-black text-[#8b5cf6]">{{ $member->membership_expires_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-4 text-sm font-black text-[#4bc6b9]">Rp {{ number_format($member->balance, 0, ',', '.') }}</td>
                            <td class="px-4 py-4">
                                <a href="{{ route('admin.users.edit', $member) }}" class="inline-flex rounded-full border-4 border-white bg-[#f4f9ff] px-4 py-2 text-xs font-black text-[#5a76c8] shadow-sm">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-12 text-center text-sm font-black text-[#5a76c8]">Belum ada member aktif.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
