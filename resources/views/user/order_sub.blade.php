@extends('layouts.user')

@section('title', 'Pilih Aplikasi - ' . $category->name)

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
</style>

<div class="wiboost-font pb-20 max-w-5xl mx-auto mt-4 px-4">
    
    <div class="flex items-center gap-4 mb-10">
        <a href="{{ route('user.dashboard') }}" class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-[#5a76c8] hover:bg-[#f0f5ff] transition-colors border-2 border-white shadow-sm shrink-0 hover:-translate-y-1">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
        </a>
        <div class="flex-1">
            <h2 class="text-3xl md:text-4xl font-black text-[#2b3a67] tracking-tight">{{ $category->name }}</h2>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
        @forelse($category->children as $child)
        <a href="{{ route('user.order.category', $child->slug) }}" class="group bg-white p-6 md:p-8 rounded-[2rem] border-4 border-white hover:border-[#bde0fe] shadow-lg shadow-[#bde0fe]/20 hover:-translate-y-2 transition-all text-center flex flex-col items-center justify-center">
            
            <div class="w-20 h-20 bg-gradient-to-br from-[#f0f5ff] to-[#e0ebff] rounded-2xl mb-5 flex items-center justify-center group-hover:scale-110 group-hover:rotate-3 transition-transform border-2 border-white shadow-inner shrink-0 overflow-hidden">
                @if($child->image)
                    <img src="{{ Storage::url($child->image) }}" alt="{{ $child->name }}" class="w-full h-full object-cover">
                @else
                    <span class="text-4xl drop-shadow-sm">{{ $child->emote ?? '📱' }}</span>
                @endif
            </div>
            
            <span class="text-lg font-black text-[#2b3a67] leading-tight">{{ $child->name }}</span>
        </a>
        @empty
        <div class="col-span-full text-center py-16 bg-white rounded-[2.5rem] border-4 border-dashed border-[#bde0fe]">
            <div class="text-6xl mb-4 opacity-50">🛠️</div>
            <p class="text-[#5a76c8] font-black text-xl">Layanan sedang dipersiapkan...</p>
        </div>
        @endforelse
    </div>

</div>
@endsection