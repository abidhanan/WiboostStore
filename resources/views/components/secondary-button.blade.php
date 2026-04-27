<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center rounded-full border-4 border-[#f0f5ff] bg-white px-8 py-4 text-base font-black text-[#8faaf3] shadow-md transition-transform hover:bg-[#f4f9ff] hover:text-[#5a76c8] active:scale-95 gap-2']) }}>
    {{ $slot }}
</button>