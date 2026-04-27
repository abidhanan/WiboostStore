<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-full border-4 border-white bg-[#5a76c8] px-8 py-4 text-base font-black text-white shadow-xl shadow-[#5a76c8]/30 transition-transform hover:bg-[#4760a9] active:scale-95 gap-2']) }}>
    {{ $slot }}
</button>