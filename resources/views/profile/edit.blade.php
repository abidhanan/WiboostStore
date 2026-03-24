@extends(Auth::user()->role_id == 1 ? 'layouts.admin' : 'layouts.user')

@section('title', 'Pengaturan Akun')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }
</style>

<div class="wiboost-font pb-12 max-w-4xl mx-auto mt-4">
    <div class="mb-10 pl-2">
        <h2 class="text-3xl font-black text-[#2b3a67] tracking-tight">Pengaturan Akun ⚙️</h2>
        <p class="text-[#8faaf3] font-bold text-sm mt-1">Kelola informasi profil dan keamanan akunmu di sini.</p>
    </div>

    <div class="space-y-8">
        
        <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-lg shadow-[#bde0fe]/20 border-4 border-white">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-lg shadow-[#bde0fe]/20 border-4 border-white">
            @include('profile.partials.update-password-form')
        </div>

        <div class="bg-[#fff0f0] rounded-[2.5rem] p-8 md:p-10 shadow-lg shadow-[#ffe5e5]/50 border-4 border-white">
            @include('profile.partials.delete-user-form')
        </div>

    </div>
</div>
@endsection