@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-black text-sm text-emerald-500 bg-[#e6fff7] border-4 border-white px-6 py-4 rounded-[1.5rem] shadow-sm flex items-start gap-3']) }}>
        <span class="text-xl mt-0.5">✅</span>
        <p>{{ $status }}</p>
    </div>
@endif