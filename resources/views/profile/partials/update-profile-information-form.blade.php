@php
    $defaultProfilePhoto = \App\Models\User::defaultProfilePhotoUrl();
    $profilePhotoUrl = $user->profile_photo_url ?: $defaultProfilePhoto;
@endphp

<section>
    <header class="mb-6 text-left">
        <h3 class="text-xl font-black text-[#2b3a67] md:text-2xl">Informasi Profil</h3>
        <p class="mt-1 text-sm font-bold text-[#8faaf3]">Perbarui informasi profil dan alamat email akunmu.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6" x-data="{ photoPreview: @js($profilePhotoUrl), removeProfilePhoto: false }">
        @csrf
        @method('patch')

        <div class="flex flex-col items-start gap-4 text-left">
            <label class="block text-sm font-black text-[#5a76c8]">Foto Profil</label>

            <div class="relative shrink-0">
                <img :src="photoPreview"
                     src="{{ $profilePhotoUrl }}"
                     onerror="this.onerror=null;this.src='{{ $defaultProfilePhoto }}';"
                     alt="Foto profil {{ $user->name }}"
                     class="h-28 w-28 rounded-full border-2 border-[#5a76c8] bg-white object-cover shadow-xl shadow-[#bde0fe]/40 ring-4 ring-white sm:h-32 sm:w-32">
                <button type="button"
                        @click="$refs.profilePhotoInput.click(); editingProfile = true"
                        aria-label="Edit foto profil"
                        class="absolute bottom-0 right-0 flex h-11 w-11 items-center justify-center rounded-full border-4 border-white bg-[#5a76c8] text-white shadow-lg shadow-[#5a76c8]/25 transition-transform hover:bg-[#4760a9] active:scale-95">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.5 4.5 3 3M4 20h4l10.5-10.5a2.12 2.12 0 0 0-3-3L5 17v3Z" />
                    </svg>
                </button>
            </div>

            <input
                x-ref="profilePhotoInput"
                id="profile_photo"
                name="profile_photo"
                type="file"
                accept="image/jpeg,image/png,image/webp"
                class="hidden"
                @change="
                    const file = $event.target.files[0];
                    if (!file) return;
                    removeProfilePhoto = false;
                    editingProfile = true;
                    const reader = new FileReader();
                    reader.onload = (event) => photoPreview = event.target.result;
                    reader.readAsDataURL(file);
                ">
            <input type="hidden" name="remove_profile_photo" :value="removeProfilePhoto ? 1 : 0">

            <div class="flex flex-col items-start justify-start gap-3 sm:flex-row">
                <button type="button"
                        x-show="photoPreview !== @js($defaultProfilePhoto) && !removeProfilePhoto"
                        x-cloak
                        @click="$refs.profilePhotoInput.value = ''; photoPreview = @js($defaultProfilePhoto); removeProfilePhoto = true; editingProfile = true"
                        class="rounded-full border-4 border-white bg-[#ffe5e5] px-6 py-3 text-sm font-black text-[#ff6b6b] shadow-sm transition-transform hover:bg-[#ffcccc] active:scale-95">
                    Hapus Foto
                </button>
            </div>

            <x-input-error class="mt-1 text-xs font-bold text-[#ff6b6b]" :messages="$errors->get('profile_photo')" />
        </div>

        <div>
            <label for="name" class="mb-2 ml-2 block text-sm font-black text-[#5a76c8]">Nama Lengkap</label>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autocomplete="name"
                   :readonly="! editingProfile"
                   @click="editingProfile = true"
                   @focus="editingProfile = true"
                   class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-5 py-4 font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe]">
            <x-input-error class="mt-2 pl-2 text-xs font-bold text-[#ff6b6b]" :messages="$errors->get('name')" />
        </div>

        <div>
            <label for="email" class="mb-2 ml-2 block text-sm font-black text-[#5a76c8]">Alamat Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username"
                   :readonly="! editingProfile"
                   @click="editingProfile = true"
                   @focus="editingProfile = true"
                   class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-5 py-4 font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe]">
            <x-input-error class="mt-2 pl-2 text-xs font-bold text-[#ff6b6b]" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-4 rounded-[1.5rem] border-4 border-white bg-[#fff5eb] p-5 shadow-sm">
                    <p class="text-sm font-bold text-amber-600">
                        Email kamu belum diverifikasi.
                        <button form="send-verification" class="ml-1 font-black text-amber-500 underline decoration-2 underline-offset-4 transition-colors hover:text-amber-700">
                            Kirim ulang verifikasi.
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-3 inline-block rounded-md border border-white bg-[#e6fff7] px-3 py-1 text-xs font-black uppercase tracking-widest text-emerald-500 shadow-sm">
                            Link verifikasi baru telah dikirim.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <label for="whatsapp" class="mb-2 ml-2 block text-sm font-black text-[#5a76c8]">Nomor WhatsApp</label>
            <input id="whatsapp" name="whatsapp" type="text" value="{{ old('whatsapp', $user->whatsapp) }}" required
                   :readonly="! editingProfile"
                   @click="editingProfile = true"
                   @focus="editingProfile = true"
                   class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-5 py-4 font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe]"
                   placeholder="081234567890">
            <x-input-error class="mt-2 pl-2 text-xs font-bold text-[#ff6b6b]" :messages="$errors->get('whatsapp')" />
        </div>

        <div class="flex flex-col items-center justify-center gap-3 pt-2 sm:flex-row">
            <button x-show="editingProfile" x-cloak type="submit" class="rounded-full border-4 border-white bg-[#5a76c8] px-8 py-4 font-black text-white shadow-xl shadow-[#5a76c8]/30 transition-transform active:scale-95 hover:bg-[#4760a9]">
                Simpan Profil
            </button>
            <button x-show="editingProfile" x-cloak type="button" @click="editingProfile = false; $el.closest('form').reset(); photoPreview = @js($profilePhotoUrl); removeProfilePhoto = false; $refs.profilePhotoInput.value = ''" class="rounded-full border-4 border-white bg-[#f4f9ff] px-8 py-4 font-black text-[#5a76c8] shadow-sm transition-transform active:scale-95">
                Batal
            </button>
        </div>

        @if (session('status') === 'profile-updated')
            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2500)" class="text-center text-sm font-black text-emerald-500 bg-[#e6fff7] px-5 py-3 rounded-full border-2 border-white shadow-sm">
                Profil berhasil disimpan.
            </p>
        @endif
    </form>
</section>
