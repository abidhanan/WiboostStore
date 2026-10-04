@php
    $isEdit = $socialMediaStat->exists;
    $returnTo = request('return_to', old('return_to', route('admin.social-media.index')));
@endphp

<form action="{{ $isEdit ? route('admin.social-media.update', $socialMediaStat) : route('admin.social-media.store') }}" method="POST" class="space-y-6 rounded-[2rem] border-4 border-white bg-white/95 p-5 shadow-2xl shadow-[#bde0fe]/30 sm:rounded-[2.5rem] sm:p-7 md:p-8">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif
    <input type="hidden" name="return_to" value="{{ $returnTo }}">

    @if($errors->any())
        <div class="rounded-[1.5rem] border-4 border-white bg-[#ffe5e5] px-5 py-4 text-sm font-black text-[#ff6b6b] shadow-sm">
            <ul class="space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label for="social-media-platform" class="mb-2 ml-2 block text-xs font-black text-[#5a76c8]">Kategori</label>
                @php $selectedPlatform = old('platform', $socialMediaStat->platform); @endphp
                <input type="hidden" name="platform" value="{{ $selectedPlatform }}">
                <div id="social-media-platform" class="admin-theme-select" data-admin-theme-select data-input-name="platform">
                    <button type="button" class="admin-theme-select-button rounded-[1.3rem] px-5 py-4 text-sm font-black" aria-haspopup="listbox">
                        <span data-admin-theme-select-label>{{ $selectedPlatform === 'tiktok' ? 'TikTok' : 'Instagram' }}</span>
                        <span class="admin-theme-select-arrow">v</span>
                    </button>
                    <div class="admin-theme-select-menu" role="listbox">
                        <button type="button" class="admin-theme-select-option {{ $selectedPlatform === 'instagram' ? 'is-selected' : '' }}" data-value="instagram" data-label="Instagram">Instagram</button>
                        <button type="button" class="admin-theme-select-option {{ $selectedPlatform === 'tiktok' ? 'is-selected' : '' }}" data-value="tiktok" data-label="TikTok">TikTok</button>
                    </div>
                </div>
            </div>
            <div>
                <label for="social-media-month" class="mb-2 ml-2 block text-xs font-black text-[#5a76c8]">Bulan</label>
                @php $selectedMonth = (int) old('month', $socialMediaStat->month); @endphp
                <input type="hidden" name="month" value="{{ $selectedMonth }}">
                <div id="social-media-month" class="admin-theme-select" data-admin-theme-select data-input-name="month">
                    <button type="button" class="admin-theme-select-button rounded-[1.3rem] px-5 py-4 text-sm font-black" aria-haspopup="listbox">
                        <span data-admin-theme-select-label>{{ $monthLabels[$selectedMonth] ?? 'Pilih Bulan' }}</span>
                        <span class="admin-theme-select-arrow">v</span>
                    </button>
                    <div class="admin-theme-select-menu" role="listbox">
                        @foreach($monthLabels as $monthNumber => $monthLabel)
                            <button type="button" class="admin-theme-select-option {{ $selectedMonth === $monthNumber ? 'is-selected' : '' }}" data-value="{{ $monthNumber }}" data-label="{{ $monthLabel }}">{{ $monthLabel }}</button>
                        @endforeach
                    </div>
                </div>
            </div>
            <div>
                <label for="social-media-year" class="mb-2 ml-2 block text-xs font-black text-[#5a76c8]">Tahun</label>
                <input id="social-media-year" type="number" name="year" min="2020" max="2100" required inputmode="numeric" value="{{ old('year', $socialMediaStat->year) }}" class="w-full rounded-[1.3rem] border-4 border-white bg-[#f4f9ff] px-5 py-4 text-sm font-black text-[#2b3a67] shadow-inner outline-none transition focus:border-[#bde0fe]">
            </div>
        </div>
    </section>

    <section class="pt-2">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
            @foreach([
                'total_posts' => 'Total Postingan',
                'total_followers' => 'Total Followers',
                'reach' => 'Jangkauan',
                'profile_visits' => 'Kunjungan Profil',
                'bio_link_clicks' => 'Klik Link Bio',
            ] as $field => $label)
                <div>
                    <label for="social-media-{{ str_replace('_', '-', $field) }}" class="mb-2 ml-2 block whitespace-nowrap text-xs font-black text-[#5a76c8]">{{ $label }}</label>
                    <input id="social-media-{{ str_replace('_', '-', $field) }}" type="number" name="{{ $field }}" min="0" required inputmode="numeric" value="{{ old($field, $socialMediaStat->{$field} ?? 0) }}" class="w-full rounded-[1.3rem] border-4 border-white bg-[#f4f9ff] px-5 py-4 text-sm font-black text-[#2b3a67] shadow-inner outline-none transition focus:border-[#bde0fe]">
                </div>
            @endforeach
        </div>
    </section>

    <section class="pt-2">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div>
                <label for="social-media-top-post" class="mb-2 ml-2 block text-xs font-black text-[#5a76c8]">Link Postingan Teramai</label>
                <input id="social-media-top-post" type="url" name="top_post_url" value="{{ old('top_post_url', $socialMediaStat->top_post_url) }}" class="w-full rounded-[1.3rem] border-4 border-white bg-[#f4f9ff] px-5 py-4 text-sm font-black text-[#2b3a67] shadow-inner outline-none transition focus:border-[#bde0fe]">
            </div>
            <div>
                <label for="social-media-low-post" class="mb-2 ml-2 block text-xs font-black text-[#5a76c8]">Link Postingan Tersepi</label>
                <input id="social-media-low-post" type="url" name="low_post_url" value="{{ old('low_post_url', $socialMediaStat->low_post_url) }}" class="w-full rounded-[1.3rem] border-4 border-white bg-[#f4f9ff] px-5 py-4 text-sm font-black text-[#2b3a67] shadow-inner outline-none transition focus:border-[#bde0fe]">
            </div>
        </div>
    </section>

    <div class="flex flex-col-reverse gap-3 border-t-4 border-dashed border-[#f4f9ff] pt-6 sm:flex-row sm:justify-end">
        <a href="{{ $returnTo }}" class="inline-flex h-12 items-center justify-center rounded-full border-4 border-white bg-[#f4f9ff] px-7 text-sm font-black text-[#5a76c8] shadow-md transition-colors hover:bg-[#e0fbfc]">Batal</a>
        <button type="submit" class="inline-flex h-12 items-center justify-center gap-2 rounded-full border-4 border-white bg-[#5a76c8] px-7 text-sm font-black text-white shadow-lg shadow-[#5a76c8]/25 transition-colors hover:bg-[#4760a9] active:scale-95">
            @include('partials.wiboost-icon', ['name' => 'save', 'class' => 'h-5 w-5'])
            {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Data' }}
        </button>
    </div>
</form>
