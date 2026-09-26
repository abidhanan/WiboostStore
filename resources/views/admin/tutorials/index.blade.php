@extends('layouts.admin')
@section('title', 'Manajemen Tutorial')
@section('admin_header_subtitle', 'Kelola panduan dan tutorial pelanggan.')

@section('content')
<style>
    .table-scroll::-webkit-scrollbar { height: 8px; }
    .table-scroll::-webkit-scrollbar-track { background: #f0f5ff; border-radius: 10px; }
    .table-scroll::-webkit-scrollbar-thumb { background: #bde0fe; border-radius: 10px; border: 2px solid #f0f5ff; }
    .table-scroll::-webkit-scrollbar-thumb:hover { background: #8faaf3; }
</style>

@php
    $sections = collect($tutorialSections ?? []);
@endphp

<div class="wiboost-font relative z-10 w-full min-w-0 pb-12">
    <div class="relative z-10 mb-10 flex w-full min-w-0 flex-col gap-3 rounded-[2rem] border-4 border-white bg-white/90 p-3 shadow-xl shadow-[#bde0fe]/25 backdrop-blur-sm sm:mb-12 sm:rounded-[2.5rem] sm:p-4 lg:mb-14 lg:flex-row lg:items-center">
        <div class="min-w-0 flex-1">
            <form action="{{ route('admin.tutorials.index') }}" method="GET" class="flex w-full items-center gap-2 sm:gap-3">
                <input type="text" name="search" value="{{ request('search') }}"
                    class="h-12 min-w-0 flex-1 rounded-[1.2rem] border-4 border-white bg-[#f4f9ff] px-4 text-xs font-black leading-normal text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe] sm:h-14 sm:rounded-[1.5rem] sm:px-5 sm:text-sm"
                    placeholder="Cari tutorial...">

                <button type="submit" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] border-4 border-white bg-[#5a76c8] text-base text-white shadow-md shadow-[#5a76c8]/25 transition-transform hover:bg-[#4760a9] active:scale-95 sm:rounded-[1.2rem]" title="Cari Tutorial">
                    @include('partials.wiboost-icon', ['name' => 'search', 'class' => 'h-5 w-5'])
                </button>

                @if(request('search'))
                    <a href="{{ route('admin.tutorials.index') }}" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1rem] border-4 border-white bg-[#ffe5e5] text-base text-[#ff6b6b] shadow-md shadow-[#ff6b6b]/20 transition-transform hover:bg-[#ffcccc] active:scale-95 sm:rounded-[1.2rem]" title="Reset Pencarian">
                        @include('partials.wiboost-icon', ['name' => 'x', 'class' => 'h-5 w-5'])
                    </a>
                @endif
            </form>
        </div>

        <a href="{{ route('admin.tutorials.create') }}" class="flex w-full min-w-0 shrink-0 items-center justify-center gap-2 rounded-[1rem] border-4 border-white bg-[#5a76c8] px-5 py-3 text-sm font-black text-white shadow-md shadow-[#5a76c8]/25 transition-transform hover:bg-[#4760a9] active:scale-95 sm:rounded-[1.2rem] lg:w-auto lg:min-w-[190px] lg:px-7">
            @include('partials.wiboost-icon', ['name' => 'plus', 'class' => 'h-5 w-5'])
            Tambah Tutorial
        </a>
    </div>

    @if(session('success'))
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

    @if($sections->isNotEmpty())
        <div class="grid grid-cols-1 gap-12 sm:gap-14 lg:gap-16">
            @foreach($sections as $section)
                @php
                    $directTutorials = collect($section['tutorials'] ?? []);
                    $subcategories = collect($section['subcategories'] ?? []);
                @endphp

                <section class="relative z-10 w-full min-w-0 overflow-hidden rounded-[2rem] border-4 border-white bg-white/95 shadow-2xl shadow-[#bde0fe]/30 backdrop-blur-sm sm:rounded-[2.75rem]">
                    <div class="flex flex-col gap-4 border-b-4 border-dashed border-[#f4f9ff] bg-gradient-to-r from-[#f8fbff] via-white to-[#eefbff] px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-7">
                        <div class="min-w-0">
                            <h2 class="truncate text-xl font-black text-[#2b3a67] sm:text-2xl">
                                {{ $section['name'] }}
                            </h2>
                        </div>
                    </div>

                    <div class="space-y-8 p-5 sm:p-8 lg:space-y-10 lg:p-10">
                        @if($directTutorials->isNotEmpty())
                            <div class="overflow-hidden rounded-[1.75rem] border-4 border-[#f4f9ff] bg-white shadow-sm shadow-[#bde0fe]/10">
                                @include('admin.tutorials.partials.rows', ['tutorials' => $directTutorials])
                            </div>
                        @endif

                        @foreach($subcategories as $subcategory)
                            <div class="overflow-hidden rounded-[1.75rem] border-4 border-[#f4f9ff] bg-white shadow-sm shadow-[#bde0fe]/10">
                                <div class="flex items-center gap-4 border-b-4 border-dashed border-[#f4f9ff] bg-[#f8fbff] px-5 py-4 sm:px-6">
                                    <div class="h-12 w-12 shrink-0 overflow-hidden rounded-2xl border-2 border-[#eef6ff] bg-[#f8fbff] p-2 shadow-inner">
                                        @if(!empty($subcategory['image']))
                                            <img src="{{ Storage::url($subcategory['image']) }}" alt="{{ $subcategory['name'] }}" class="h-full w-full object-contain">
                                        @else
                                            <span class="flex h-full w-full items-center justify-center text-lg font-black text-[#5a76c8]">
                                                {{ strtoupper(substr((string) $subcategory['name'], 0, 1)) }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-base font-black text-[#2b3a67]">{{ $subcategory['name'] }}</p>
                                    </div>
                                </div>

                                @include('admin.tutorials.partials.rows', ['tutorials' => collect($subcategory['tutorials'] ?? [])])
                            </div>
                        @endforeach

                        @if($directTutorials->isEmpty() && $subcategories->isEmpty())
                            <div class="rounded-[1.75rem] border-4 border-dashed border-[#e8f2ff] bg-[#f8fbff] px-5 py-10 text-center text-sm font-black text-[#8faaf3]">
                                Belum ada tutorial di kategori utama ini.
                            </div>
                        @endif
                    </div>
                </section>
            @endforeach
        </div>
    @else
        <div class="relative z-10 w-full rounded-[2rem] border-4 border-white bg-white/95 px-5 py-16 text-center shadow-2xl shadow-[#bde0fe]/30 backdrop-blur-sm sm:rounded-[2.5rem]">
            <div class="mb-4 inline-flex h-20 w-20 items-center justify-center rounded-[2rem] border-4 border-white bg-[#f0f5ff] shadow-inner sm:mb-5 sm:h-24 sm:w-24 sm:rounded-[2.5rem]">
                @include('partials.wiboost-icon', ['name' => 'book', 'class' => 'h-10 w-10 text-[#5a76c8]'])
            </div>
            <p class="text-lg font-black text-[#5a76c8] sm:text-xl">Belum ada tutorial.</p>
            <p class="mt-2 text-sm font-bold text-[#8faaf3] sm:text-base">Buat kategori dan tutorial pertama dari tombol tambah.</p>
        </div>
    @endif

    @if(isset($tutorials) && method_exists($tutorials, 'links') && $tutorials->hasPages())
        <div class="p-5 sm:p-8 w-full min-w-0">
            {{ $tutorials->links() }}
        </div>
    @endif
</div>
@endsection
