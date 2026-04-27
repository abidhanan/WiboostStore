@extends(Auth::user()->role_id == 1 ? 'layouts.admin' : 'layouts.user')

@section('title', 'Pengaturan Akun')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');
    .wiboost-font { font-family: 'Nunito', sans-serif; }

    .animate-float { animation: float 6s ease-in-out infinite; }
    .animate-float-delayed { animation: float 6s ease-in-out 3s infinite; }
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
        100% { transform: translateY(0px); }
    }
</style>

<div class="wiboost-font pb-12 max-w-4xl mx-auto mt-4 relative z-10">
    
    <div class="absolute top-10 -left-10 text-4xl animate-float opacity-50 pointer-events-none hidden md:block">⚙️</div>
    <div class="absolute top-1/2 -right-12 text-3xl animate-float-delayed opacity-50 pointer-events-none hidden md:block">✨</div>
    <div class="absolute bottom-10 left-10 text-5xl animate-float opacity-40 pointer-events-none hidden md:block">☁️</div>

    <div class="mb-10 text-center md:text-left pl-2">
        <div class="inline-block px-4 py-1 bg-[#e0fbfc] text-[#4bc6b9] font-black rounded-full mb-3 text-[10px] border-2 border-white shadow-sm uppercase tracking-widest">
            Area Privasi
        </div>
        <h2 class="text-3xl font-black text-[#2b3a67] tracking-tight">Pengaturan Akun 🛠️</h2>
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