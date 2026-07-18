@extends('layouts.admin')
@section('title', 'Manajemen Display')
@section('admin_header_subtitle', 'Kelola display popup user.')

@section('content')
<div class="wiboost-font pb-12 relative z-10 w-full min-w-0">
    <div class="relative z-10 mb-10 flex w-full min-w-0 flex-col gap-3 rounded-[2rem] border-4 border-white bg-white/90 p-3 shadow-xl shadow-[#bde0fe]/25 backdrop-blur-sm sm:mb-12 sm:rounded-[2.5rem] sm:p-4 lg:flex-row lg:items-center">
        <div class="min-w-0 flex-1">
            <form action="{{ route('admin.displays.index') }}" method="GET" class="flex w-full items-center gap-2 sm:gap-3">
                <input type="text" name="search" value="{{ request('search') }}"
                    class="min-w-0 flex-1 truncate rounded-[1.2rem] border-4 border-white bg-[#f4f9ff] px-5 py-3 text-xs font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe] sm:rounded-[1.5rem] sm:px-6 sm:py-4 sm:text-sm"
                    placeholder="Cari judul, badge, deskripsi, atau tema...">
                <button type="submit" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] border-4 border-white bg-[#5a76c8] text-white shadow-md shadow-[#5a76c8]/25 transition-transform hover:bg-[#4760a9] active:scale-95 sm:rounded-[1.2rem]" title="Cari Display">
                    @include('partials.wiboost-icon', ['name' => 'search', 'class' => 'h-5 w-5'])
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.displays.index') }}" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] border-4 border-white bg-[#ffe5e5] text-[#ff6b6b] shadow-md shadow-[#ff6b6b]/20 transition-transform hover:bg-[#ffcccc] active:scale-95 sm:rounded-[1.2rem]" title="Reset Pencarian">
                        @include('partials.wiboost-icon', ['name' => 'x', 'class' => 'h-5 w-5'])
                    </a>
                @endif
            </form>
        </div>

        <a href="{{ route('admin.displays.create') }}" class="flex w-full min-w-0 shrink-0 items-center justify-center gap-2 rounded-[1rem] border-4 border-white bg-[#5a76c8] px-5 py-3 text-sm font-black text-white shadow-md shadow-[#5a76c8]/25 transition-transform hover:bg-[#4760a9] active:scale-95 sm:rounded-[1.2rem] lg:w-auto lg:min-w-[190px] lg:px-7">
            @include('partials.wiboost-icon', ['name' => 'plus', 'class' => 'h-5 w-5'])
            Tambah Display
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-[2rem] border-4 border-white bg-[#e6fff7] px-5 py-4 text-sm font-black text-emerald-500 shadow-lg shadow-emerald-100/50 sm:mb-8 sm:px-6 sm:text-base">
            @include('partials.wiboost-icon', ['name' => 'success', 'class' => 'h-6 w-6 shrink-0'])
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 flex items-center gap-3 rounded-[2rem] border-4 border-white bg-[#ffe5e5] px-5 py-4 text-sm font-black text-[#ff6b6b] shadow-lg shadow-[#ff6b6b]/20 sm:mb-8 sm:px-6 sm:text-base">
            @include('partials.wiboost-icon', ['name' => 'alert', 'class' => 'h-6 w-6 shrink-0'])
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-6 lg:grid-cols-3 xl:grid-cols-4">
        @forelse($displays ?? [] as $display)
            @php
                $theme = $display->theme ?? 'blue';
                $bgClass = 'from-[#8faaf3] to-[#5a76c8]';
                if ($theme === 'teal') { $bgClass = 'from-[#4bc6b9] to-[#3ba398]'; }
                elseif ($theme === 'orange') { $bgClass = 'from-[#fbbf24] to-[#d97706]'; }
                elseif ($theme === 'rose') { $bgClass = 'from-[#fb7185] to-[#e11d48]'; }
                $isActive = (bool) ($display->is_active ?? true);
            @endphp

            <div class="group relative flex aspect-[4/5] min-w-0 flex-col justify-between overflow-hidden rounded-[2rem] border-4 border-white p-6 text-white shadow-xl shadow-[#bde0fe]/20 transition-transform duration-300 hover:-translate-y-2 hover:scale-[1.02] sm:rounded-[2.5rem] sm:p-8 {{ empty($display->image) ? 'bg-gradient-to-br ' . $bgClass : 'bg-[#2b3a67]' }} {{ ! $isActive ? 'opacity-70 grayscale-[50%]' : '' }}" style="aspect-ratio: 4 / 5;">
                @if(!empty($display->image))
                    <img src="{{ Storage::url($display->image) }}" alt="{{ $display->title ?: 'Display' }}" class="absolute inset-0 z-0 h-full w-full object-cover opacity-70 transition-transform duration-700 group-hover:scale-105">
                    <div class="absolute inset-0 z-10 bg-gradient-to-br from-black/80 to-transparent"></div>
                @endif

                <div class="relative z-20 flex justify-end">
                    <div class="flex shrink-0 gap-2">
                        <a href="{{ route('admin.displays.edit', $display->id) }}" class="flex h-8 w-8 items-center justify-center rounded-[1rem] border-2 border-white bg-[#fbbf24] text-[0px] text-white shadow-md shadow-[#d99b16]/25 transition-transform hover:scale-105 hover:bg-[#d99b16] sm:h-10 sm:w-10" title="Ubah Display">
                            @include('partials.wiboost-icon', ['name' => 'edit', 'class' => 'h-4 w-4 sm:h-5 sm:w-5'])
                        </a>
                        <form action="{{ route('admin.displays.destroy', $display->id) }}" method="POST" onsubmit="return confirm('Hapus display ini secara permanen?');" class="m-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-[1rem] border-2 border-white bg-[#ff4d4d] text-[0px] text-white shadow-md shadow-[#ff6b6b]/25 transition-transform hover:scale-105 hover:bg-[#dc2626] sm:h-10 sm:w-10" title="Buang Display">
                                @include('partials.wiboost-icon', ['name' => 'trash', 'class' => 'h-4 w-4 sm:h-5 sm:w-5'])
                            </button>
                        </form>
                    </div>
                </div>

                @if(empty($display->image))
                    <div class="absolute right-5 top-1/2 z-0 -translate-y-1/2 text-white/35">
                        @include('partials.wiboost-icon', ['name' => 'image', 'class' => 'h-16 w-16'])
                    </div>
                @endif
            </div>
        @empty
            <div class="col-span-full rounded-[2rem] border-4 border-dashed border-[#bde0fe] bg-white/90 py-16 text-center shadow-lg shadow-[#bde0fe]/20 sm:rounded-[2.5rem] sm:py-20">
                @include('partials.wiboost-icon', ['name' => 'image', 'class' => 'mx-auto h-14 w-14 text-[#8faaf3] opacity-60 sm:h-16 sm:w-16'])
                <p class="mt-4 text-xl font-black text-[#5a76c8] sm:text-2xl">Belum ada display yang dibuat.</p>
                <p class="mt-2 text-sm font-bold text-[#8faaf3] sm:text-base">Tambah display pertama untuk tampil setelah user login.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
