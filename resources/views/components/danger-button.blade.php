<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-full border-4 border-white bg-[#ff6b6b] px-8 py-4 text-base font-black text-white shadow-xl shadow-[#ff6b6b]/30 transition-transform hover:bg-[#e55050] active:scale-95 gap-2']) }}>
    {{ $slot }}
</button>