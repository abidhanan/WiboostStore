@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-6 py-4 font-black text-[#2b3a67] outline-none transition shadow-inner focus:border-[#bde0fe] placeholder-[#a3bbfb]']) }}>