@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-6 py-2.5 rounded-full border-4 border-white bg-[#5a76c8] text-sm font-black text-white shadow-lg shadow-[#5a76c8]/30 transition-transform hover:scale-105 active:scale-95'
            : 'inline-flex items-center px-6 py-2.5 rounded-full border-4 border-transparent text-sm font-black text-[#8faaf3] hover:bg-[#f0f5ff] hover:text-[#5a76c8] transition-transform hover:scale-105 active:scale-95';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>