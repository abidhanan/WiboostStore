@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full px-5 py-3.5 rounded-[1.5rem] text-left font-black bg-[#5a76c8] text-white shadow-md border-4 border-white transition-transform active:scale-95'
            : 'block w-full px-5 py-3.5 rounded-[1.5rem] text-left font-black text-[#8faaf3] hover:bg-[#f4f9ff] hover:text-[#5a76c8] border-4 border-transparent transition-transform active:scale-95';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>