@extends(Auth::user()->isAdmin() ? 'layouts.admin' : 'layouts.user')

@section('title', 'Pengaturan Akun')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
    [x-cloak] { display: none !important; }
    .profile-photo-shell {
        position: relative;
        display: inline-flex;
        width: 8rem;
        height: 8rem;
        aspect-ratio: 1 / 1;
        flex: 0 0 8rem;
    }
    .profile-photo-frame {
        display: block;
        width: 100%;
        height: 100%;
        overflow: hidden;
        border-radius: 9999px;
        border: 2px solid #5a76c8;
        background: #ffffff;
        box-shadow: 0 16px 35px rgba(189, 224, 254, 0.4);
        outline: 4px solid #ffffff;
    }
    .profile-photo-frame img {
        display: block;
        width: 100%;
        height: 100%;
        max-width: none;
        aspect-ratio: 1 / 1;
        border-radius: 9999px;
        object-fit: cover;
        object-position: center;
    }
    .profile-photo-edit-button {
        position: absolute;
        right: -0.25rem;
        bottom: -0.25rem;
        z-index: 2;
        display: inline-flex;
        width: 2.75rem;
        height: 2.75rem;
        align-items: center;
        justify-content: center;
        border-radius: 9999px;
        border: 4px solid #ffffff;
        background: #5a76c8;
        color: #ffffff;
        box-shadow: 0 12px 22px rgba(90, 118, 200, 0.25);
    }
    .profile-photo-edit-button:hover { background: #4760a9; }
    .profile-photo-edit-button:active { transform: scale(0.95); }
    @media (max-width: 640px) {
        .profile-photo-shell {
            width: 7rem;
            height: 7rem;
            flex-basis: 7rem;
        }
    }
</style>

@php
    $profileHasErrors = $errors->has('name') || $errors->has('email') || $errors->has('whatsapp');
    $passwordHasErrors = $errors->updatePassword->any();
@endphp

<div
    class="wiboost-font pb-12 max-w-6xl mx-auto mt-4 relative z-10"
    x-data="{ editingProfile: {{ $profileHasErrors ? 'true' : 'false' }}, editingPassword: {{ $passwordHasErrors ? 'true' : 'false' }} }"
>
    <div class="mb-10 text-left pl-2">
        <div class="inline-block px-4 py-1 bg-[#e0fbfc] text-[#4bc6b9] font-black rounded-full mb-3 text-[10px] border-2 border-white shadow-sm uppercase tracking-widest">
            Area Privasi
        </div>
        <h2 class="flex items-center justify-start gap-2 text-3xl font-black text-[#2b3a67] tracking-tight">
            <span class="text-[#5a76c8]">@include('partials.wiboost-icon', ['name' => 'profile', 'class' => 'h-8 w-8'])</span>
            <span>Pengaturan Akun</span>
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
