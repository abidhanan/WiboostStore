@extends('layouts.admin')
@section('title', 'Tambah Pengguna')
@section('content')
<style>
    
    .animate-float { animation: float 6s ease-in-out infinite; }
    .animate-float-delayed { animation: float 6s ease-in-out 3s infinite; }
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
        100% { transform: translateY(0px); }
    }
</style>

<div class="wiboost-font pb-12 max-w-4xl mx-auto relative z-10">
    @include('admin.partials.form-header', [
        'backUrl' => route('admin.users.index'),
        'badge' => 'Registrasi',
        'title' => 'Tambah Pengguna',
        'description' => 'Tambahkan akun baru dan tentukan role aksesnya.',
    ])

    @if ($errors->any())
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-6 py-5 rounded-[2.5rem] mb-8 font-black shadow-lg shadow-[#ff6b6b]/20 flex items-start gap-4 relative z-10">
            @include('partials.wiboost-icon', ['name' => 'alert', 'class' => 'mt-1 h-6 w-6 shrink-0'])
            <ul class="list-disc list-inside text-sm font-bold mt-2 bg-white/50 p-4 rounded-2xl border border-white">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.users.store') }}" method="POST" class="bg-white/95 backdrop-blur-sm rounded-[2.5rem] shadow-xl shadow-[#bde0fe]/30 border-4 border-white p-6 md:p-10 relative z-10 overflow-visible">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Nama Lengkap</label>
                <input type="text" name="name" required value="{{ old('name') }}" data-wiboost-name-only class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" placeholder="John Doe">
            </div>
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Email</label>
                <input type="email" name="email" required value="{{ old('email') }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" placeholder="john@email.com">
            </div>
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Nomor Kontak</label>
                <input type="text" name="whatsapp" required value="{{ old('whatsapp') }}" inputmode="numeric" pattern="[0-9]*" data-wiboost-number-only class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" placeholder="Cth: 081234567890">
            </div>
            <div>
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Kata Sandi</label>
                <input type="password" name="password" required class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] shadow-inner rounded-[1.5rem] px-6 py-4 text-[#2b3a67] font-black outline-none transition placeholder-[#a3bbfb]" placeholder="Minimal 8 karakter">
            </div>
            <div class="md:col-span-2 relative z-30">
                <label class="block text-sm font-black text-[#5a76c8] mb-3 ml-2">Role Akses</label>
                @include('admin.users.partials.role-select', ['selectedRole' => old('role_id', request('role', '2'))])
            </div>
        </div>
        
        <div class="mb-8 p-4 bg-[#e6fff7] rounded-2xl border-2 border-emerald-100 shadow-sm flex gap-3 items-center">
            @include('partials.wiboost-icon', ['name' => 'alert', 'class' => 'h-5 w-5 shrink-0'])
            <p class="text-xs font-bold text-emerald-600">Info: Saldo pengguna baru akan otomatis diatur menjadi <b>Rp 0</b>.</p>
        </div>

        <button type="submit" class="w-full bg-[#5a76c8] hover:bg-[#4760a9] text-white font-black text-xl py-5 rounded-full transition-transform active:scale-95 shadow-xl shadow-[#5a76c8]/30 border-4 border-white flex items-center justify-center gap-2">
            Simpan Pengguna Baru
        </button>
    </form>
</div>
@endsection
