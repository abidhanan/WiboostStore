@extends('layouts.admin')
@section('title', 'Manajemen Pengguna')
@section('admin_header_subtitle', 'Kelola data pengguna dan role akun.')

@section('content')
@php
    $activeRoleFilter = $roleFilter ?? request('role_filter', 'all');
    $searchValue = $search ?? request('search');
    $canManageUsers = (int) (auth()->user()?->role_id ?? 0) === \App\Models\User::ROLE_SUPER_ADMIN;
    $formatRupiah = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    $formatDate = fn ($value) => $value ? $value->timezone(config('app.timezone'))->format('d/m/Y H:i') : '-';
    $roleFilters = [
        'all' => ['label' => 'Semua', 'text' => '#5a76c8', 'bg' => '#f4f9ff'],
        'buyer' => ['label' => 'Buyer', 'text' => '#5a76c8', 'bg' => '#e0fbfc'],
        'reseller' => ['label' => 'Reseller', 'text' => '#d99b16', 'bg' => '#fff7d6'],
        'member' => ['label' => 'Member', 'text' => '#8b5cf6', 'bg' => '#ede9fe'],
        'super_admin' => ['label' => 'Super Admin', 'text' => '#22c55e', 'bg' => '#e6fff7'],
        'keuangan' => ['label' => 'Keuangan', 'text' => '#8b5a2b', 'bg' => '#fff7ed'],
        'gudang' => ['label' => 'Gudang', 'text' => '#111827', 'bg' => '#f3f4f6'],
        'marketing' => ['label' => 'Marketing', 'text' => '#991b1b', 'bg' => '#fee2e2'],
        'customer_service' => ['label' => 'Customer Service', 'text' => '#ec4899', 'bg' => '#fce7f3'],
    ];
    $effectiveRoleMeta = function ($user): array {
        if ($user->isMember()) {
            return ['label' => 'Member', 'bg' => '#ede9fe', 'text' => '#8b5cf6'];
        }

        return match ((int) $user->role_id) {
            1 => ['label' => 'Super Admin', 'bg' => '#e6fff7', 'text' => '#22c55e'],
            3 => ['label' => 'Reseller', 'bg' => '#fff7d6', 'text' => '#d99b16'],
            4 => ['label' => 'Keuangan', 'bg' => '#fff7ed', 'text' => '#8b5a2b'],
            6 => ['label' => 'Gudang', 'bg' => '#f3f4f6', 'text' => '#111827'],
            7 => ['label' => 'Marketing', 'bg' => '#fee2e2', 'text' => '#991b1b'],
            8 => ['label' => 'Customer Service', 'bg' => '#fce7f3', 'text' => '#ec4899'],
            default => ['label' => 'Buyer', 'bg' => '#e0fbfc', 'text' => '#5a76c8'],
        };
    };
    $baseRoleLabel = fn ($user) => method_exists($user, 'baseRoleLabel') ? $user->baseRoleLabel() : 'Buyer';
@endphp

<style>
    .table-scroll::-webkit-scrollbar { height: 8px; }
    .table-scroll::-webkit-scrollbar-track { background: #f0f5ff; border-radius: 10px; }
    .table-scroll::-webkit-scrollbar-thumb { background: #bde0fe; border-radius: 10px; border: 2px solid #f0f5ff; }
    .table-scroll::-webkit-scrollbar-thumb:hover { background: #8faaf3; }
    .admin-detail-modal {
        display: none;
        background: rgba(43, 58, 103, .58) !important;
        -webkit-backdrop-filter: blur(8px);
        backdrop-filter: blur(8px);
        overflow: hidden;
        overscroll-behavior: contain;
        inset: 0 !important;
        align-items: flex-start;
        justify-content: center;
        padding: 7rem .85rem 1rem;
    }
    @media (min-width: 640px) {
        .admin-detail-modal { padding: 7.25rem 1.5rem 1.25rem; }
    }
    @media (min-width: 1280px) {
        .admin-detail-modal {
            padding-top: 7.5rem;
            padding-left: 26rem;
            padding-right: 2rem;
        }
    }
    @media (max-width: 639px) {
        .admin-detail-modal { padding-top: 8rem; }
    }
    .admin-detail-modal.modal-active {
        display: flex;
        opacity: 1 !important;
        pointer-events: auto !important;
    }
    .admin-detail-modal.modal-active .admin-detail-card { transform: scale(1) !important; }
    .admin-detail-card {
        width: min(100%, 52rem);
        max-height: calc(100dvh - 9rem) !important;
        overflow: hidden;
        overflow-wrap: anywhere;
        word-break: break-word;
    }
    .admin-detail-card * { min-width: 0; }
    .admin-detail-card p,
    .admin-detail-card h3,
    .admin-detail-card span {
        overflow-wrap: anywhere;
        word-break: break-word;
    }
    .admin-detail-header {
        border-bottom: 3px dashed #f4f9ff;
        padding: 0 0 .85rem 0;
    }
    .admin-detail-panel {
        overflow: hidden;
        border-radius: 1.2rem;
        border: 1px solid #eaf2ff;
        background: #f8faff;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.9);
        padding: .85rem 1rem;
    }
    .admin-detail-panel-soft {
        border-color: #d9fff5;
        background: #f2fffb;
    }
    .admin-detail-body {
        min-height: 0;
        flex: 1 1 auto;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        padding-right: .9rem;
        padding-bottom: .25rem;
        scrollbar-color: #bde0fe transparent;
        scrollbar-width: thin;
        scrollbar-gutter: stable both-edges;
    }
    .admin-detail-body::-webkit-scrollbar { width: 8px; }
    .admin-detail-body::-webkit-scrollbar-track {
        background: transparent;
        border-radius: 999px;
        margin: .75rem 0;
    }
    .admin-detail-body::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #d8ecff, #8faaf3);
        border: 2px solid #ffffff;
        border-radius: 999px;
    }
    .admin-detail-close {
        box-shadow: 0 10px 22px rgba(255, 107, 107, .14), inset 0 1px 0 rgba(255,255,255,.85);
    }
    .admin-user-photo {
        aspect-ratio: 1 / 1 !important;
        border-radius: 9999px !important;
        display: block !important;
        flex: 0 0 auto !important;
        height: 44px !important;
        max-height: 44px !important;
        max-width: 44px !important;
        min-height: 44px !important;
        min-width: 44px !important;
        object-fit: cover !important;
        width: 44px !important;
    }
    .admin-user-detail-photo {
        aspect-ratio: 1 / 1 !important;
        height: 56px !important;
        max-height: 56px !important;
        max-width: 56px !important;
        min-height: 56px !important;
        min-width: 56px !important;
        object-fit: cover !important;
        width: 56px !important;
    }
</style>

<div class="wiboost-font relative z-10 w-full min-w-0 pb-12">
    <div class="relative z-10 mb-10 flex w-full min-w-0 flex-col gap-3 rounded-[2rem] border-4 border-white bg-white/90 p-3 shadow-xl shadow-[#bde0fe]/25 backdrop-blur-sm sm:mb-12 sm:rounded-[2.5rem] sm:p-4 lg:mb-14 lg:flex-row lg:items-center">
        <div class="min-w-0 flex-1">
            <form action="{{ route('admin.users.index') }}" method="GET" class="flex w-full items-center gap-2 sm:gap-3">
                @if($activeRoleFilter !== 'all')
                    <input type="hidden" name="role_filter" value="{{ $activeRoleFilter }}">
                @endif
                <input
                    type="text"
                    name="search"
                    value="{{ $searchValue }}"
                    class="min-w-0 flex-1 truncate rounded-[1.2rem] border-4 border-white bg-[#f4f9ff] px-5 py-3 text-xs font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe] sm:rounded-[1.5rem] sm:px-6 sm:py-4 sm:text-sm"
                    placeholder="Cari Pengguna"
                >

                <button type="submit" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] border-4 border-white bg-[#5a76c8] text-xs font-black text-white shadow-md shadow-[#5a76c8]/25 transition-transform hover:bg-[#4760a9] active:scale-95 sm:rounded-[1.2rem]" title="Cari Pengguna">
                    @include('partials.wiboost-icon', ['name' => 'search', 'class' => 'h-5 w-5'])
                </button>

                @if(request('search'))
                    <a href="{{ route('admin.users.index', array_filter(['role_filter' => $activeRoleFilter === 'all' ? null : $activeRoleFilter])) }}" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] border-4 border-white bg-[#ffe5e5] text-xs font-black text-[#ff6b6b] shadow-md shadow-[#ff6b6b]/20 transition-transform hover:bg-[#ffcccc] active:scale-95 sm:rounded-[1.2rem]" title="Reset Pencarian">
                        @include('partials.wiboost-icon', ['name' => 'x', 'class' => 'h-5 w-5'])
                    </a>
                @endif
            </form>
        </div>

        @if($canManageUsers)
            <a href="{{ route('admin.users.create') }}" class="flex w-full min-w-0 shrink-0 items-center justify-center gap-2 rounded-[1rem] border-4 border-white bg-[#5a76c8] px-5 py-3 text-sm font-black text-white shadow-md shadow-[#5a76c8]/25 transition-transform hover:bg-[#4760a9] active:scale-95 sm:rounded-[1.2rem] lg:w-auto lg:min-w-[190px] lg:px-7">
                @include('partials.wiboost-icon', ['name' => 'plus', 'class' => 'h-5 w-5'])
                Tambah Pengguna
            </a>
        @endif
    </div>

    @include('admin.partials.filter-tabs', [
        'filters' => $roleFilters,
        'activeFilter' => $activeRoleFilter,
        'filterParam' => 'role_filter',
        'filterRoute' => 'admin.users.index',
        'filterQuery' => ['search' => $searchValue],
    ])

    @if (session('success'))
        <div class="relative z-10 mb-6 flex w-full items-center gap-3 rounded-[2rem] border-4 border-white bg-[#e6fff7] px-5 py-4 text-sm font-black text-emerald-500 shadow-lg shadow-emerald-100/50 sm:mb-8 sm:px-6 sm:text-base">
            @include('partials.wiboost-icon', ['name' => 'success', 'class' => 'h-6 w-6 shrink-0'])
            <p>{{ session('success') }}</p>
        </div>
    @endif
    @if (session('error'))
        <div class="relative z-10 mb-6 flex w-full items-center gap-3 rounded-[2rem] border-4 border-white bg-[#ffe5e5] px-5 py-4 text-sm font-black text-[#ff6b6b] shadow-lg shadow-[#ff6b6b]/20 sm:mb-8 sm:px-6 sm:text-base">
            @include('partials.wiboost-icon', ['name' => 'alert', 'class' => 'h-6 w-6 shrink-0'])
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <div class="relative z-10 flex w-full min-w-0 flex-col overflow-hidden rounded-[2rem] border-4 border-white bg-white/95 shadow-2xl shadow-[#bde0fe]/30 backdrop-blur-sm sm:rounded-[2.5rem]">
        <div class="table-scroll w-full overflow-x-auto pb-2">
            <div class="inline-block min-w-full p-4 align-middle sm:p-6">
                <table class="w-full min-w-[1240px] whitespace-nowrap text-left">
                    <thead>
                        <tr>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-center text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Foto</th>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Nama</th>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Email</th>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Nomor Kontak</th>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-center text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Role</th>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-center text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Masa Member</th>
                            <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Saldo</th>
                            @if($canManageUsers)
                                <th class="border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-center text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                        @forelse($users as $user)
                            <?php $roleMeta = $effectiveRoleMeta($user); ?>
                            <tr onclick="openAdminUserDetail('admin-user-detail-{{ $user->id }}')" class="group cursor-pointer transition-colors hover:bg-[#f8faff]">
                                <td class="px-5 py-4 text-center align-middle sm:py-5">
                                    <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="admin-user-photo mx-auto border-2 border-white shadow-sm" style="border-color: {{ $roleMeta['text'] }};">
                                </td>
                                <td class="min-w-[190px] px-5 py-4 align-middle sm:py-5">
                                    <p class="whitespace-nowrap text-sm font-black text-[#2b3a67]">{{ $user->name }}</p>
                                </td>
                                <td class="min-w-[240px] px-5 py-4 align-middle sm:py-5">
                                    <p class="inline-block whitespace-nowrap rounded-md border border-white bg-[#f4f9ff] px-3 py-1 text-[10px] font-bold text-[#8faaf3] sm:text-[11px]">{{ $user->email }}</p>
                                </td>
                                <td class="px-5 py-4 align-middle sm:py-5">
                                    <p class="text-sm font-black text-[#5a76c8]">{{ $user->whatsapp ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-4 text-center align-middle sm:py-5">
                                    <span class="inline-flex min-w-24 justify-center whitespace-nowrap rounded-full border-2 border-white px-4 py-1.5 text-[9px] font-black uppercase tracking-widest shadow-sm" style="background: {{ $roleMeta['bg'] }}; color: {{ $roleMeta['text'] }};">{{ $roleMeta['label'] }}</span>
                                </td>
                                <td class="px-5 py-4 text-center align-middle sm:py-5">
                                    @if($user->isMember())
                                        <p class="whitespace-nowrap text-[10px] font-black text-[#8b5cf6]">{{ $formatDate($user->membership_expires_at) }}</p>
                                    @else
                                        <span class="text-sm font-black text-[#8faaf3]">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 align-middle sm:py-5">
                                    <p class="text-sm font-black text-[#4bc6b9] drop-shadow-sm sm:text-base">{{ $formatRupiah($user->balance) }}</p>
                                </td>
                                @if($canManageUsers)
                                    <td class="px-5 py-4 text-center align-middle sm:py-5">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('admin.users.edit', $user->id) }}" onclick="event.stopPropagation()" class="flex h-9 w-9 items-center justify-center rounded-[1rem] border-2 border-white bg-white text-[#5a76c8] shadow-md shadow-[#bde0fe]/20 transition-transform hover:scale-110 hover:bg-[#f0f5ff] active:scale-95 sm:h-10 sm:w-10" title="Edit Pengguna">
                                                @include('partials.wiboost-icon', ['name' => 'edit', 'class' => 'h-4 w-4'])
                                            </a>
                                            @if(auth()->id() != $user->id)
                                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="event.stopPropagation(); return confirm('Yakin ingin menghapus pengguna ini secara permanen?');" onclick="event.stopPropagation()" class="m-0 inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="flex h-9 w-9 items-center justify-center rounded-[1rem] border-2 border-white bg-white text-[#ff6b6b] shadow-md shadow-[#ff6b6b]/20 transition-transform hover:scale-110 hover:bg-[#ffe5e5] active:scale-95 sm:h-10 sm:w-10" title="Hapus Pengguna">
                                                        @include('partials.wiboost-icon', ['name' => 'trash', 'class' => 'h-4 w-4'])
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $canManageUsers ? 8 : 7 }}" class="px-5 py-16 text-center">
                                    <div class="mb-4 inline-flex h-20 w-20 items-center justify-center rounded-[2rem] border-4 border-white bg-[#f0f5ff] shadow-inner sm:mb-5 sm:h-24 sm:w-24 sm:rounded-[2.5rem]">
                                        <span class="text-4xl font-black text-[#5a76c8] sm:text-5xl">?</span>
                                    </div>
                                    <p class="text-lg font-black text-[#5a76c8] sm:text-xl">Belum ada pengguna terdaftar.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(method_exists($users, 'links') && $users->hasPages())
            <div class="w-full min-w-0 border-t-4 border-dashed border-[#f4f9ff] bg-[#f8faff] p-5 sm:p-8">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    @foreach($users as $user)
        <?php $detailRoleMeta = $effectiveRoleMeta($user); ?>
        <div id="admin-user-detail-{{ $user->id }}" class="admin-detail-modal fixed z-[9999] items-center justify-center opacity-0 pointer-events-none transition-all duration-200">
            <div class="absolute inset-0" onclick="closeAdminUserDetail('admin-user-detail-{{ $user->id }}')"></div>
            <div class="admin-detail-card relative flex max-h-[calc(100dvh-8.25rem)] w-full max-w-3xl scale-95 flex-col rounded-[1.75rem] border-4 border-white bg-white p-4 shadow-2xl shadow-[#2b3a67]/20 transition-transform duration-200 sm:p-5">
                <div class="admin-detail-header mb-3.5 flex items-start justify-between gap-3 p-3.5">
                    <div class="flex min-w-0 items-center gap-3">
                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="admin-user-detail-photo shrink-0 rounded-full border-2 shadow-sm" style="border-color: {{ $detailRoleMeta['text'] }};">
                        <div class="min-w-0">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Detail Pengguna</p>
                            <h3 class="mt-1 truncate text-xl font-black text-[#2b3a67]">{{ $user->name }}</h3>
                        </div>
                    </div>
                    <button type="button" onclick="closeAdminUserDetail('admin-user-detail-{{ $user->id }}')" class="admin-detail-close flex h-9 w-9 shrink-0 items-center justify-center rounded-[1rem] border-2 border-white bg-[#ffe5e5] text-lg font-black text-[#ff6b6b]">x</button>
                </div>

                <div class="admin-detail-body">
                    <div class="grid gap-3 md:grid-cols-2">
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Nama</p>
                            <p class="mt-1 text-sm font-black text-[#2b3a67]">{{ $user->name }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Email</p>
                            <p class="mt-1 text-sm font-black text-[#2b3a67]">{{ $user->email }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Nomor Kontak</p>
                            <p class="mt-1 text-sm font-black text-[#5a76c8]">{{ $user->whatsapp ?? '-' }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Role</p>
                            <span class="mt-2 inline-block rounded-full border-2 border-white px-4 py-1.5 text-[9px] font-black uppercase tracking-widest shadow-sm" style="background: {{ $detailRoleMeta['bg'] }}; color: {{ $detailRoleMeta['text'] }};">{{ $detailRoleMeta['label'] }}</span>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Role Dasar</p>
                            <p class="mt-1 text-sm font-black text-[#2b3a67]">{{ $baseRoleLabel($user) }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Tanggal Daftar</p>
                            <p class="mt-1 text-sm font-black text-[#2b3a67]">{{ $formatDate($user->created_at) }}</p>
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Masa Member</p>
                            @if($user->isMember())
                                <p class="mt-1 text-sm font-black text-[#8b5cf6]">{{ $formatDate($user->membership_expires_at) }}</p>
                            @else
                                <p class="mt-1 text-sm font-black text-[#8faaf3]">-</p>
                            @endif
                        </div>
                        <div class="admin-detail-panel">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Tanggal Jadi Reseller</p>
                            <p class="mt-1 text-sm font-black text-[#2b3a67]">{{ $formatDate($user->reseller_joined_at) }}</p>
                        </div>
                        <div class="admin-detail-panel admin-detail-panel-soft">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#08a878]">Saldo</p>
                            <p class="mt-1 text-2xl font-black text-[#4bc6b9]">{{ $formatRupiah($user->balance) }}</p>
                        </div>
                        <div class="admin-detail-panel admin-detail-panel-soft">
                            <p class="text-[10px] font-black uppercase tracking-widest text-[#08a878]">Poin</p>
                            <p class="mt-1 text-2xl font-black text-[#5a76c8]">{{ number_format((int) $user->points, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<script>
    function openAdminUserDetail(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.add('modal-active');
        document.body.classList.add('overflow-hidden', 'admin-detail-open');
    }

    function closeAdminUserDetail(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.remove('modal-active');
        document.body.classList.remove('overflow-hidden', 'admin-detail-open');
    }

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;

        document.querySelectorAll('.admin-detail-modal.modal-active').forEach((modal) => {
            modal.classList.remove('modal-active');
        });

        document.body.classList.remove('overflow-hidden', 'admin-detail-open');
    });
</script>
@endsection
