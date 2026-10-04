@extends('layouts.admin')

@section('title', 'Kelola Stok: ' . $product->name)

@section('content')
@php
    $isEditing = isset($editingCredential) && $editingCredential;
    $hasOldInput = count(session()->getOldInput()) > 0;
    $editingValue = fn (string $field, mixed $default = '') => $isEditing
        ? data_get($editingCredential, $field, $default)
        : $default;
    $selectedOtpWhatsapp = (string) old('otp_admin_whatsapp', $editingValue('otp_admin_whatsapp', ''));
    $selectedDurationUnit = (string) old('duration_unit', $editingValue('duration_unit', 'day') ?: 'day');
    $formNeedsOtp = $hasOldInput ? (bool) old('needs_otp') : (bool) $editingValue('needs_otp', false);
    $formRequiresLoginProof = $hasOldInput ? (bool) old('requires_login_proof') : (bool) $editingValue('requires_login_proof', false);
    $formRequiresLogoutProof = $hasOldInput ? (bool) old('requires_logout_proof') : (bool) $editingValue('requires_logout_proof', false);
    $credentialValue = fn (string $field, mixed $default = '') => old($field, $editingValue($field, $default));
    $formErrors = $errors->getBag($isEditing ? 'editCredential' : 'default');
    $credentialFormAction = $isEditing
        ? route('admin.credentials.update', $editingCredential)
        : route('admin.credentials.store', $product->id);
    $cancelEditUrl = route('admin.credentials.index', array_filter([
        'product' => $product->id,
        'return_to' => request('return_to'),
    ]));
@endphp
<style>
    
    .animate-float { animation: float 6s ease-in-out infinite; }
    .animate-float-delayed { animation: float 6s ease-in-out 3s infinite; }
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
        100% { transform: translateY(0px); }
    }

    .warehouse-email-value {
        display: block;
        font-size: clamp(0.625rem, 2.8vw, 0.875rem);
        line-height: 1.35;
        max-width: 100%;
        overflow-x: auto;
        scrollbar-width: none;
        white-space: nowrap;
    }

    .warehouse-email-value::-webkit-scrollbar {
        display: none;
    }

    .warehouse-otp-select .admin-theme-select-button {
        background: #fff8e8 !important;
        color: #b96f00 !important;
        border-color: #ffffff !important;
    }

    .warehouse-otp-select .warehouse-otp-label,
    .warehouse-otp-select .admin-theme-select-option {
        font-size: clamp(0.74rem, 2vw, 0.86rem) !important;
        letter-spacing: 0 !important;
        white-space: nowrap !important;
    }

    .warehouse-otp-select .warehouse-otp-label {
        overflow-x: auto !important;
        scrollbar-width: none;
        text-overflow: clip !important;
    }

    .warehouse-otp-select .warehouse-otp-label::-webkit-scrollbar {
        display: none;
    }

    .warehouse-otp-select .admin-theme-select-option {
        background: #f4f9ff !important;
        color: #6f8bd8 !important;
    }

    .warehouse-otp-select .admin-theme-select-option:hover {
        background: #eef6ff !important;
        color: #4760a9 !important;
    }

    .warehouse-otp-select .admin-theme-select-option.is-selected {
        background: #5a76c8 !important;
        color: #ffffff !important;
    }
</style>

<div class="wiboost-font pb-12 max-w-6xl mx-auto relative z-10">
    @include('admin.partials.form-header', [
        'backUrl' => route('admin.products.index'),
        'badge' => 'Gudang Akun',
        'title' => 'Gudang Stok',
        'description' => $product->name,
    ])

    <div class="mb-10 grid grid-cols-2 gap-3 sm:gap-4">
        <div class="min-w-0 rounded-[1.5rem] border-4 border-white bg-white/90 px-4 py-4 text-center shadow-lg shadow-[#bde0fe]/20 backdrop-blur-sm sm:px-6">
            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Tipe Produk</p>
            <p class="mt-1 truncate text-base font-black uppercase text-[#5a76c8] sm:text-lg">{{ $product->process_type }}</p>
        </div>

        <div class="min-w-0 rounded-[1.5rem] border-4 border-white bg-white/90 px-4 py-4 text-center shadow-lg shadow-[#bde0fe]/20 backdrop-blur-sm sm:px-6">
            <p class="text-[10px] font-black uppercase tracking-widest text-[#8faaf3]">Sisa Stok Total</p>
            <div class="mt-1 flex min-w-0 items-center justify-center gap-2">
                <p class="text-2xl font-black leading-none text-[#5a76c8] sm:text-3xl">{{ $product->available_stock }}</p>
                @if($product->available_stock <= $product->stock_reminder)
                    <span class="shrink-0 rounded-md border border-white bg-[#ffe5e5] px-2.5 py-1 text-[9px] font-black text-[#ff6b6b] shadow-sm animate-pulse sm:px-3 sm:text-[10px]">RE-STOK!</span>
                @endif
            </div>
        </div>
    </div>
    @if (session('success'))
        <div class="bg-[#e6fff7] border-4 border-white text-emerald-500 px-6 py-4 rounded-[2.5rem] mb-8 font-black shadow-lg shadow-emerald-100/50 flex items-center gap-4 relative z-10">
            @include('partials.wiboost-icon', ['name' => 'success', 'class' => 'h-6 w-6 shrink-0'])
            <p>{{ session('success') }}</p>
        </div>
    @endif
    @if (session('error'))
        <div class="bg-[#ffe5e5] border-4 border-white text-[#ff6b6b] px-6 py-4 rounded-[2.5rem] mb-8 font-black shadow-lg shadow-[#ff6b6b]/20 flex items-center gap-4 relative z-10">
            @include('partials.wiboost-icon', ['name' => 'alert', 'class' => 'h-6 w-6 shrink-0'])
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 relative z-10">
        
        <div class="lg:col-span-1">
            <form action="{{ $credentialFormAction }}" method="POST" data-no-submit-lock="true" class="rounded-[2.5rem] border-4 border-white bg-white/95 p-6 shadow-xl shadow-[#bde0fe]/30 backdrop-blur-sm md:p-8 lg:sticky lg:top-6" x-data="{ needsOtp: @js($formNeedsOtp), requiresLogoutProof: @js($formRequiresLogoutProof) }">
                @csrf
                @if($isEditing)
                    @method('PUT')
                    <input type="hidden" name="editing_credential_id" value="{{ $editingCredential->id }}">
                @endif
                @if(request()->filled('return_to'))
                    <input type="hidden" name="return_to" value="{{ request('return_to') }}">
                @endif
                <div class="mb-6 border-b-4 border-dashed border-[#f4f9ff] pb-4">
                    <h2 class="text-2xl font-black text-[#2b3a67]">{{ $isEditing ? 'Edit Stok' : 'Tambah Stok' }}</h2>
                </div>

                @if($formErrors->any())
                    <div class="mb-5 rounded-[1.5rem] border-4 border-white bg-[#ffe5e5] px-5 py-4 text-left shadow-inner">
                        <p class="mb-2 text-xs font-black text-[#ff4d4d]">Data belum dapat disimpan:</p>
                        <ul class="space-y-1 text-xs font-bold text-[#d94a4a]">
                            @foreach($formErrors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <div class="space-y-5">
                    @if($product->process_type == 'account')
                        <div class="p-4 bg-[#e6fff7] border-4 border-white rounded-[1.5rem] mb-5 shadow-sm">
                            <p class="text-xs font-normal leading-relaxed text-emerald-600">Semua form di bawah opsional. Cukup isi yang diperlukan sesuai dengan jenis aplikasi premiumnya.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-[#5a76c8] mb-2 ml-2">Email</label>
                            <input type="text" name="data_1" value="{{ $credentialValue('data_1') === '-' ? '' : $credentialValue('data_1') }}" class="warehouse-email-value w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition shadow-inner placeholder-[#a3bbfb]">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-[#5a76c8] mb-2 ml-2">Kata Sandi</label>
                            <input type="text" name="data_2" value="{{ $credentialValue('data_2') }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition text-sm shadow-inner placeholder-[#a3bbfb]">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-black text-[#5a76c8] mb-2 ml-2">Profil Name</label>
                                <input type="text" name="data_3" value="{{ $credentialValue('data_3') }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition text-sm shadow-inner placeholder-[#a3bbfb]">
                            </div>
                            <div>
                                <label class="block text-xs font-black text-[#5a76c8] mb-2 ml-2">PIN</label>
                                <input type="text" name="data_4" value="{{ $credentialValue('data_4') }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition text-sm shadow-inner placeholder-[#a3bbfb]">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-[#5a76c8] mb-2 ml-2">Tautan Undangan / Tautan Akses</label>
                            <input type="text" name="data_5" value="{{ $credentialValue('data_5') }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition text-sm shadow-inner placeholder-[#a3bbfb]">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-[#5a76c8] mb-2 ml-2">Catatan</label>
                            <textarea name="notes" rows="3" class="w-full resize-none bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition text-sm shadow-inner placeholder-[#a3bbfb]">{{ $credentialValue('notes') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-[#5a76c8] mb-2 ml-2">Tautan Tutorial</label>
                            <input type="url" name="tutorial_link" value="{{ $credentialValue('tutorial_link') }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition text-sm shadow-inner placeholder-[#a3bbfb]">
                        </div>
                        <div class="border-t-4 border-dashed border-[#f4f9ff] pt-5">
                            <label class="block text-xs font-black text-[#5a76c8] mb-2 ml-2">Batas Penggunaan</label>
                            <input type="number" name="max_usage" required min="{{ $isEditing ? max(1, (int) $editingCredential->current_usage) : 1 }}" value="{{ $credentialValue('max_usage', 1) }}" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition text-sm shadow-inner">
                            <div x-cloak x-show="requiresLogoutProof" x-transition.opacity.duration.150ms class="mt-4">
                                <label class="block text-xs font-black text-[#5a76c8] mb-1 ml-2">Durasi</label>
                                <p class="mb-2 ml-2 text-[10px] font-black leading-relaxed text-[#ff4d4d]">Wajib diisi apabila Unggah Bukti Keluar diaktifkan.</p>
                                <div class="grid grid-cols-2 gap-3">
                                    <input type="number" name="duration_value" value="{{ $credentialValue('duration_value') }}" min="1" inputmode="numeric" x-bind:required="requiresLogoutProof" class="w-full bg-[#f4f9ff] border-4 border-white focus:border-[#bde0fe] rounded-[1.5rem] px-5 py-4 text-[#2b3a67] font-black outline-none transition text-sm shadow-inner">
                                    <div class="admin-theme-select" data-admin-theme-select data-input-name="duration_unit">
                                        <input type="hidden" name="duration_unit" value="{{ $selectedDurationUnit }}">
                                        <button type="button" class="admin-theme-select-button rounded-[1.5rem] px-5 py-4 text-sm font-black">
                                            <span data-admin-theme-select-label>{{ ['day' => 'Hari', 'week' => 'Minggu', 'month' => 'Bulan', 'year' => 'Tahun'][$selectedDurationUnit] ?? 'Hari' }}</span>
                                            <span class="admin-theme-select-arrow">v</span>
                                        </button>
                                        <div class="admin-theme-select-menu">
                                            <button type="button" class="admin-theme-select-option {{ $selectedDurationUnit === 'day' ? 'is-selected' : '' }}" data-value="day" data-label="Hari">Hari</button>
                                            <button type="button" class="admin-theme-select-option {{ $selectedDurationUnit === 'week' ? 'is-selected' : '' }}" data-value="week" data-label="Minggu">Minggu</button>
                                            <button type="button" class="admin-theme-select-option {{ $selectedDurationUnit === 'month' ? 'is-selected' : '' }}" data-value="month" data-label="Bulan">Bulan</button>
                                            <button type="button" class="admin-theme-select-option {{ $selectedDurationUnit === 'year' ? 'is-selected' : '' }}" data-value="year" data-label="Tahun">Tahun</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="border-t-4 border-dashed border-[#f4f9ff]"></div>
                    @endif

                    <div class="mt-4 rounded-[1.5rem] border-4 border-white bg-[#fffcf0] p-5 shadow-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="mb-1 text-sm font-black text-amber-600">OTP Admin</p>
                                <p class="pr-4 text-[10px] font-bold leading-tight text-amber-500">Aktifkan jika pelanggan harus chat Admin untuk meminta OTP.</p>
                            </div>
                            <label class="relative inline-flex shrink-0 cursor-pointer items-center">
                                <input type="checkbox" name="needs_otp" class="sr-only peer" value="1" x-model="needsOtp" @checked($formNeedsOtp)>
                                <div class="w-14 h-8 bg-amber-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-amber-300 after:border-2 after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-amber-500 shadow-inner"></div>
                            </label>
                        </div>

                        <div x-cloak x-show="needsOtp" x-transition.opacity.duration.150ms class="mt-4 border-t-2 border-dashed border-amber-100 pt-4">
                            <label class="mb-2 ml-2 block text-[10px] font-black uppercase tracking-widest text-amber-600">Pilih Nomor Admin OTP</label>
                            @if(count($adminOtpAdmins) > 0)
                                <div class="admin-theme-select warehouse-otp-select" data-admin-theme-select data-input-name="otp_admin_whatsapp">
                                    <input type="hidden" name="otp_admin_whatsapp" value="{{ $selectedOtpWhatsapp }}" x-bind:required="needsOtp">
                                    <button type="button" class="admin-theme-select-button rounded-[1.25rem] px-5 py-4 text-sm font-black">
                                        @php $selectedOtpAdmin = collect($adminOtpAdmins)->firstWhere('number', $selectedOtpWhatsapp); @endphp
                                        <span data-admin-theme-select-label class="warehouse-otp-label">{{ $selectedOtpAdmin ? $selectedOtpAdmin['name'] . ' (+' . $selectedOtpAdmin['number'] . ')' : 'Pilih Admin OTP' }}</span>
                                        <span class="admin-theme-select-arrow">v</span>
                                    </button>
                                    <div class="admin-theme-select-menu">
                                        @foreach($adminOtpAdmins as $otpAdmin)
                                            <button type="button" class="admin-theme-select-option {{ (string) $selectedOtpWhatsapp === (string) $otpAdmin['number'] ? 'is-selected' : '' }}" data-value="{{ $otpAdmin['number'] }}" data-label="{{ $otpAdmin['name'] }} (+{{ $otpAdmin['number'] }})">{{ $otpAdmin['name'] }} (+{{ $otpAdmin['number'] }})</button>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <p class="rounded-[1.25rem] border-2 border-dashed border-amber-200 bg-white px-4 py-3 text-[10px] font-black leading-relaxed text-amber-600">Tambahkan nomor Admin OTP terlebih dahulu melalui halaman Laporan.</p>
                            @endif
                            @error('otp_admin_whatsapp')
                                <p class="mt-2 ml-2 text-[10px] font-black text-[#ff6b6b]">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4 bg-[#eef6ff] p-5 rounded-[1.5rem] border-4 border-white flex items-center justify-between shadow-md">
                        <div>
                            <p class="text-sm font-black text-[#5a76c8] mb-1">Unggah Bukti Masuk</p>
                            <p class="text-[10px] font-bold text-[#8faaf3] leading-tight pr-4">Aktifkan jika pembeli wajib upload bukti login dari detail pesanan.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0">
                            <input type="checkbox" name="requires_login_proof" class="sr-only peer" value="1" @checked($formRequiresLoginProof)>
                            <div class="w-14 h-8 bg-[#bde0fe] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-[#8faaf3] after:border-2 after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-[#5a76c8] shadow-inner"></div>
                        </label>
                    </div>

                    <div class="mt-4 bg-[#fff1f1] p-5 rounded-[1.5rem] border-4 border-white flex items-center justify-between shadow-md">
                        <div>
                            <p class="text-sm font-black text-[#ff4d4d] mb-1">Unggah Bukti Keluar</p>
                            <p class="text-[10px] font-bold text-[#ff8a8a] leading-tight pr-4">Aktifkan jika pembeli wajib upload bukti logout setelah durasi habis.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0">
                            <input type="checkbox" name="requires_logout_proof" class="sr-only peer" value="1" x-model="requiresLogoutProof" @checked($formRequiresLogoutProof)>
                            <div class="w-14 h-8 bg-[#ffcaca] peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-[#ff8a8a] after:border-2 after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-[#ff4d4d] shadow-inner"></div>
                        </label>
                    </div>
                </div>

                <button type="submit" class="w-full bg-[#5a76c8] hover:bg-[#4760a9] text-white font-black text-lg py-4 rounded-full transition-transform active:scale-95 shadow-xl shadow-[#5a76c8]/30 border-4 border-white mt-8 flex justify-center items-center gap-2">
                    {{ $isEditing ? 'Simpan Perubahan' : 'Simpan Data' }}
                </button>
                @if($isEditing)
                    <a href="{{ $cancelEditUrl }}" class="mt-3 flex w-full items-center justify-center rounded-full border-4 border-white bg-[#f4f9ff] py-4 text-lg font-black text-[#5a76c8] shadow-md shadow-[#bde0fe]/25 transition-colors hover:bg-[#e8f2ff] active:bg-[#dcecff]">
                        Batal Edit
                    </a>
                @endif
            </form>
        </div>

        <div class="lg:col-span-2 bg-white/95 backdrop-blur-sm rounded-[2.5rem] shadow-2xl shadow-[#bde0fe]/30 border-4 border-white overflow-hidden flex flex-col">
            <div class="px-6 py-6 border-b-4 border-dashed border-[#f4f9ff] bg-[#f8faff]">
                <h2 class="font-black text-[#2b3a67] text-2xl flex items-center gap-3">
                    @include('partials.wiboost-icon', ['name' => 'package', 'class' => 'h-7 w-7 text-[#5a76c8]'])
                    Daftar Data Tersedia
                </h2>
            </div>
            
            <div data-warehouse-table-scroll class="overflow-x-auto flex-1 p-4 md:p-6">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr>
                            <th class="px-5 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest border-b-4 border-dashed border-[#f4f9ff]">Detail Data</th>
                            <th class="px-5 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest text-center border-b-4 border-dashed border-[#f4f9ff]">Kuota Pakai</th>
                            <th class="px-5 py-4 text-[10px] font-black text-[#8faaf3] uppercase tracking-widest text-center border-b-4 border-dashed border-[#f4f9ff]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                    @forelse($credentials as $cred)
                        <tr class="hover:bg-[#f8faff] transition-colors group">
                            <td class="px-5 py-5 align-top">
                                <div class="space-y-2 bg-white p-4 rounded-2xl border-2 border-[#f0f5ff] shadow-sm w-full min-w-[220px] max-w-[360px]">
                                    @if($cred->data_1 && $cred->data_1 !== '-')
                                        <div class="rounded-xl border border-[#dbeafe] bg-[#f4f9ff] px-3 py-2">
                                            <p class="text-[8px] font-black uppercase tracking-widest text-[#8faaf3]">Email</p>
                                            <p class="warehouse-email-value mt-1 font-black text-[#2b3a67]" title="{{ $cred->data_1 }}">{{ $cred->data_1 }}</p>
                                        </div>
                                    @endif
                                    @if($cred->data_2)
                                        <div class="rounded-xl border border-amber-100 bg-amber-50 px-3 py-2">
                                            <p class="text-[8px] font-black uppercase tracking-widest text-amber-500">Kata Sandi</p>
                                            <p class="mt-1 break-all text-sm font-black text-[#2b3a67]">{{ $cred->data_2 }}</p>
                                        </div>
                                    @endif
                                    @if($cred->data_3)
                                        <div class="rounded-xl border border-indigo-100 bg-indigo-50 px-3 py-2">
                                            <p class="text-[8px] font-black uppercase tracking-widest text-indigo-500">Profil</p>
                                            <p class="mt-1 break-all text-sm font-black text-[#2b3a67]">{{ $cred->data_3 }}</p>
                                        </div>
                                    @endif
                                    @if($cred->data_4)
                                        <div class="rounded-xl border border-rose-100 bg-rose-50 px-3 py-2">
                                            <p class="text-[8px] font-black uppercase tracking-widest text-rose-500">PIN</p>
                                            <p class="mt-1 break-all text-sm font-black text-[#2b3a67]">{{ $cred->data_4 }}</p>
                                        </div>
                                    @endif
                                    @if($cred->data_5)
                                        <div class="rounded-xl border border-blue-100 bg-blue-50 px-3 py-2">
                                            <p class="text-[8px] font-black uppercase tracking-widest text-blue-500">Tautan Akses</p>
                                            <p class="mt-1 break-all text-sm font-black text-[#2b3a67]">{{ $cred->data_5 }}</p>
                                        </div>
                                    @endif
                                    @if($cred->notes)
                                        <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">
                                            <p class="text-[8px] font-black uppercase tracking-widest text-slate-500">Catatan</p>
                                            <p class="mt-1 whitespace-pre-line break-words text-left text-sm font-black leading-relaxed text-[#2b3a67]" style="text-align: left; white-space: pre-line;">{{ $cred->notes }}</p>
                                        </div>
                                    @endif
                                    
                                    <div class="flex gap-2 pt-2 flex-wrap">
                                        @if($cred->tutorial_link)
                                            <span class="bg-[#e0fbfc] text-[#5a76c8] px-3 py-1 rounded-md text-[9px] font-black uppercase shadow-sm border border-white">Tutor Bawaan</span>
                                        @endif
                                        @if($cred->needs_otp)
                                            <span class="bg-[#fff5eb] text-amber-500 px-3 py-1 rounded-md text-[9px] font-black uppercase shadow-sm border border-white">Butuh OTP</span>
                                        @endif
                                        @if($cred->requires_login_proof)
                                            <span class="bg-[#eef6ff] text-[#5a76c8] px-3 py-1 rounded-md text-[9px] font-black uppercase shadow-sm border border-white">Perlu Bukti Masuk</span>
                                        @endif
                                        @if($cred->requires_logout_proof)
                                            <span class="bg-[#ffe5e5] text-[#ff4d4d] px-3 py-1 rounded-md text-[9px] font-black uppercase shadow-sm border border-white">Perlu Bukti Keluar</span>
                                        @endif
                                    </div>
                                </div>
                                <p class="text-[9px] font-black text-[#8faaf3] mt-3 uppercase tracking-widest">
                                    Ditambahkan oleh {{ $cred->createdBy->name ?? 'Admin' }} &bull; {{ $cred->created_at?->timezone(config('app.timezone', 'Asia/Jakarta'))->locale('id')->translatedFormat('d F Y, H:i') }}
                                </p>
                            </td>

                            <td class="px-5 py-5 align-middle text-center">
                                @if($cred->current_usage >= $cred->max_usage)
                                    <span class="bg-[#ffe5e5] text-[#ff6b6b] px-4 py-2 rounded-full text-[10px] font-black border-2 border-white shadow-sm inline-block">HABIS ({{ $cred->current_usage }}/{{ $cred->max_usage }})</span>
                                @else
                                    <span class="bg-[#e6fff7] text-emerald-500 px-4 py-2 rounded-full text-[10px] font-black border-2 border-white shadow-sm inline-block">SISA {{ $cred->max_usage - $cred->current_usage }}</span>
                                    <p class="text-[10px] font-bold text-[#8faaf3] mt-2 uppercase tracking-widest">Terpakai: {{ $cred->current_usage }}/{{ $cred->max_usage }}</p>
                                @endif
                            </td>

                            <td class="px-5 py-5 align-middle text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.credentials.index', array_filter(['product' => $product->id, 'edit' => $cred->id, 'return_to' => request('return_to')])) }}" class="flex h-12 w-12 items-center justify-center rounded-[1.2rem] border-4 border-white bg-[#fff5d6] text-amber-500 shadow-md shadow-amber-200/30 transition-transform active:scale-95" title="Edit Data">
                                        @include('partials.wiboost-icon', ['name' => 'edit', 'class' => 'h-5 w-5'])
                                    </a>
                                    <form action="{{ route('admin.credentials.destroy', $cred->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data kredensial ini?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-12 h-12 bg-[#ffe5e5] border-4 border-white rounded-[1.2rem] flex items-center justify-center text-[#ff6b6b] hover:bg-[#ffcccc] transition-transform active:scale-95 shadow-md shadow-[#ff6b6b]/20" title="Hapus Data">
                                        @include('partials.wiboost-icon', ['name' => 'trash', 'class' => 'h-5 w-5'])
                                    </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-24 text-center">
                                <div class="inline-flex items-center justify-center w-20 h-20 rounded-[2rem] bg-[#f0f5ff] border-4 border-white mb-4 text-[#5a76c8] shadow-inner animate-float">
                                    @include('partials.wiboost-icon', ['name' => 'package', 'class' => 'h-9 w-9'])
                                </div>
                                <p class="text-[#5a76c8] font-black text-xl">Belum ada stok data.</p>
                                <p class="text-[#8faaf3] font-bold mt-1">Silakan tambahkan melalui form di samping kiri.</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
