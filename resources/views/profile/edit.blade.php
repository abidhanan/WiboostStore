@extends(Auth::user()->role_id == 1 ? 'layouts.admin' : 'layouts.user')

@section('title', 'Pengaturan Akun')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
    [x-cloak] { display: none !important; }
</style>

@php
    $profileHasErrors = $errors->has('name') || $errors->has('email') || $errors->has('whatsapp');
    $passwordHasErrors = $errors->updatePassword->any();
@endphp

<div
    class="wiboost-font pb-12 max-w-6xl mx-auto mt-4 relative z-10"
    x-data="{ editingProfile: {{ $profileHasErrors ? 'true' : 'false' }}, editingPassword: {{ $passwordHasErrors ? 'true' : 'false' }} }"
>
    <div class="mb-10 text-center md:text-left pl-2">
        <div class="inline-block px-4 py-1 bg-[#e0fbfc] text-[#4bc6b9] font-black rounded-full mb-3 text-[10px] border-2 border-white shadow-sm uppercase tracking-widest">
            Area Privasi
        </div>
        <h2 class="flex items-center justify-center gap-2 text-3xl font-black text-[#2b3a67] tracking-tight md:justify-start">
            <span>Pengaturan Akun</span>
            <span class="text-2xl leading-none">&#128736;&#65039;</span>
        </h2>
    </div>

    <div class="space-y-8 relative z-10">
        <div class="bg-white/95 backdrop-blur-sm rounded-[2.5rem] p-8 md:p-10 shadow-xl shadow-[#bde0fe]/30 border-4 border-white transition-transform duration-300 hover:border-[#bde0fe]">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="bg-white/95 backdrop-blur-sm rounded-[2.5rem] p-8 md:p-10 shadow-xl shadow-[#bde0fe]/30 border-4 border-white transition-transform duration-300 hover:border-[#bde0fe]">
            @include('profile.partials.update-password-form')
        </div>

        <div class="bg-[#fff0f0]/95 backdrop-blur-sm rounded-[2.5rem] p-8 md:p-10 shadow-xl shadow-[#ffe5e5]/50 border-4 border-white transition-transform duration-300 hover:border-[#ffcccc]">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</div>
@endsection
