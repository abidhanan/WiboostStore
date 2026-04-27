@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-black text-[#5a76c8] mb-3 ml-2']) }}>
    {{ $value ?? $slot }}
</label>