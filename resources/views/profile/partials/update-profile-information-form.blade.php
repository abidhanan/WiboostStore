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

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6"
          x-data="{
              photoPreview: @js($profilePhotoUrl),
              photoError: '',
              profilePhotoChanged: false,
              originalName: @js(old('name', $user->name)),
              originalEmail: @js(old('email', $user->email)),
              originalWhatsapp: @js(old('whatsapp', $user->whatsapp)),
              profileName: @js(old('name', $user->name)),
              profileEmail: @js(old('email', $user->email)),
              profileWhatsapp: @js(old('whatsapp', $user->whatsapp)),
              profileChanged() {
                  return this.profilePhotoChanged
                      || this.profileName !== this.originalName
                      || this.profileEmail !== this.originalEmail
                      || this.profileWhatsapp !== this.originalWhatsapp;
              }
          }">
        @csrf
        @method('patch')

        <div class="flex flex-col items-start gap-4 text-left">
            <label class="block text-sm font-black text-[#5a76c8]">Foto Profil</label>

            <div class="profile-photo-shell">
                <span class="profile-photo-frame">
                    <img :src="photoPreview"
                         src="{{ $profilePhotoUrl }}"
                         onerror="this.onerror=null;this.src='{{ $defaultProfilePhoto }}';"
                         alt="Foto profil {{ $user->name }}"
                         width="128"
                         height="128">
                </span>
                <button type="button"
                        @click="$refs.profilePhotoInput.click(); editingProfile = true"
                        aria-label="Edit foto profil"
                        class="profile-photo-edit-button">
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
                    const input = $event.target;
                    const file = input.files[0];
                    if (!file) return;
                    photoError = '';
                    window.wiboostPrepareProfilePhoto(file, input)
                        .then((preview) => {
                            photoPreview = preview;
                            profilePhotoChanged = true;
                            editingProfile = true;
                        })
                        .catch((message) => {
                            photoError = message;
                            input.value = '';
                        });
                ">

            <p x-show="photoError" x-cloak x-text="photoError" class="mt-1 text-xs font-bold text-[#ff6b6b]"></p>
            <x-input-error class="mt-1 text-xs font-bold text-[#ff6b6b]" :messages="$errors->get('profile_photo')" />
        </div>

        <div>
            <label for="name" class="mb-2 ml-2 block text-sm font-black text-[#5a76c8]">Nama Lengkap</label>
            <input x-model="profileName" x-ref="profileName" id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autocomplete="name" data-wiboost-name-only
                   :readonly="! editingProfile"
                   @click="editingProfile = true"
                   @focus="editingProfile = true"
                   class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-5 py-4 font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe]">
            <x-input-error class="mt-2 pl-2 text-xs font-bold text-[#ff6b6b]" :messages="$errors->get('name')" />
        </div>

        <div>
            <label for="email" class="mb-2 ml-2 block text-sm font-black text-[#5a76c8]">Alamat Email</label>
            <input x-model="profileEmail" x-ref="profileEmail" id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username"
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
            <input x-model="profileWhatsapp" x-ref="profileWhatsapp" id="whatsapp" name="whatsapp" type="text" value="{{ old('whatsapp', $user->whatsapp) }}" required inputmode="numeric" pattern="[0-9]*" data-wiboost-number-only
                   :readonly="! editingProfile"
                   @click="editingProfile = true"
                   @focus="editingProfile = true"
                   class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-5 py-4 font-black text-[#2b3a67] shadow-inner outline-none transition placeholder-[#a3bbfb] focus:border-[#bde0fe]"
                   placeholder="081234567890">
            <x-input-error class="mt-2 pl-2 text-xs font-bold text-[#ff6b6b]" :messages="$errors->get('whatsapp')" />
        </div>

        <div class="grid grid-cols-2 items-center gap-3 pt-2 sm:flex sm:justify-center">
            <button x-show="profileChanged()" x-cloak type="submit" class="min-w-0 rounded-full border-4 border-white bg-[#5a76c8] px-4 py-4 font-black text-white shadow-xl shadow-[#5a76c8]/30 transition-transform active:scale-95 hover:bg-[#4760a9] sm:px-8">
                Simpan
            </button>
            <button x-show="profileChanged()" x-cloak type="button" @click="editingProfile = false; profileName = originalName; profileEmail = originalEmail; profileWhatsapp = originalWhatsapp; $el.closest('form').reset(); photoPreview = @js($profilePhotoUrl); profilePhotoChanged = false; $refs.profilePhotoInput.value = ''" class="min-w-0 rounded-full border-4 border-white bg-[#f4f9ff] px-4 py-4 font-black text-[#5a76c8] shadow-sm transition-transform active:scale-95 sm:px-8">
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

@once
    <script>
        window.wiboostPrepareProfilePhoto = window.wiboostPrepareProfilePhoto || function (file, input) {
            const maxBytes = 5 * 1024 * 1024;

            return new Promise((resolve, reject) => {
                if (!file || !file.type || !file.type.startsWith('image/')) {
                    reject('Foto profil harus berupa gambar.');
                    return;
                }

                const reader = new FileReader();
                reader.onerror = () => reject('Foto profil gagal dibaca. Coba pilih gambar lain.');
                reader.onload = () => {
                    const image = new Image();
                    image.onerror = () => reject('Foto profil gagal diproses. Coba pilih gambar lain.');
                    image.onload = () => {
                        const sourceSize = Math.min(image.naturalWidth || image.width, image.naturalHeight || image.height);

                        if (!sourceSize) {
                            reject('Foto profil gagal diproses. Coba pilih gambar lain.');
                            return;
                        }

                        const canvasSize = Math.min(720, sourceSize);
                        const canvas = document.createElement('canvas');
                        canvas.width = canvasSize;
                        canvas.height = canvasSize;

                        const context = canvas.getContext('2d');
                        const sx = ((image.naturalWidth || image.width) - sourceSize) / 2;
                        const sy = ((image.naturalHeight || image.height) - sourceSize) / 2;

                        context.fillStyle = '#ffffff';
                        context.fillRect(0, 0, canvasSize, canvasSize);
                        context.drawImage(image, sx, sy, sourceSize, sourceSize, 0, 0, canvasSize, canvasSize);

                        canvas.toBlob((blob) => {
                            if (!blob) {
                                reject('Foto profil gagal diproses. Coba pilih gambar lain.');
                                return;
                            }

                            if (blob.size > maxBytes) {
                                reject('Ukuran foto profil terlalu besar. Pilih gambar maksimal 5 MB.');
                                return;
                            }

                            const croppedFile = new File([blob], 'foto-profil.jpg', {
                                type: 'image/jpeg',
                                lastModified: Date.now(),
                            });

                            if (typeof DataTransfer !== 'undefined') {
                                const transfer = new DataTransfer();
                                transfer.items.add(croppedFile);
                                input.files = transfer.files;
                            }

                            resolve(URL.createObjectURL(blob));
                        }, 'image/jpeg', 0.86);
                    };

                    image.src = reader.result;
                };

                reader.readAsDataURL(file);
            });
        };
    </script>
@endonce
