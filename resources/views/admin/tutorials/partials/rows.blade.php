<div class="w-full overflow-hidden">
    <div class="w-full overflow-x-auto table-scroll p-4 pb-5 sm:p-6">
        <table class="w-full text-left" style="min-width: 760px; table-layout: fixed;">
            <thead>
                <tr>
                    <th class="whitespace-nowrap border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]" style="width: 330px;">Judul</th>
                    <th class="whitespace-nowrap border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-[10px] font-black uppercase tracking-widest text-[#8faaf3]" style="width: 170px;">Isi</th>
                    <th class="whitespace-nowrap border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-center text-[10px] font-black uppercase tracking-widest text-[#8faaf3]" style="width: 130px;">Status</th>
                    <th class="whitespace-nowrap border-b-4 border-dashed border-[#f4f9ff] px-5 py-4 text-center text-[10px] font-black uppercase tracking-widest text-[#8faaf3]" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y-4 divide-dashed divide-[#f4f9ff]">
                @forelse($tutorials as $tutorial)
                    <tr class="group transition-colors hover:bg-[#f8faff]">
                        <td class="whitespace-normal px-5 py-4 align-middle sm:py-5" style="width: 330px; max-width: 330px;">
                            <p class="text-[12px] font-black leading-tight text-[#2b3a67] sm:text-sm sm:leading-snug" title="{{ $tutorial->title ?? '-' }}" style="display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; overflow: hidden; text-overflow: clip; white-space: normal; word-break: normal; overflow-wrap: anywhere;">
                                {{ $tutorial->title ?? '-' }}
                            </p>
                        </td>
                        <td class="whitespace-normal px-5 py-4 align-middle sm:py-5" style="width: 170px;">
                            <div class="flex flex-wrap gap-2">
                                @if($tutorial->youtube_url)
                                    <span class="rounded-md border border-white bg-[#ffe5e5] px-3 py-1.5 text-[9px] font-black uppercase tracking-widest text-[#ff6b6b] shadow-sm">Video</span>
                                @endif
                                @if($tutorial->content)
                                    <span class="rounded-md border border-white bg-[#fffcf0] px-3 py-1.5 text-[9px] font-black uppercase tracking-widest text-amber-500 shadow-sm">Artikel</span>
                                @endif
                                @if(!$tutorial->youtube_url && !$tutorial->content)
                                    <span class="rounded-md border border-white bg-[#f0f5ff] px-3 py-1.5 text-[9px] font-black uppercase tracking-widest text-[#8faaf3] shadow-sm">Kosong</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-4 text-center align-middle sm:py-5">
                            @if($tutorial->is_active)
                                <span class="rounded-full border border-white bg-[#e6fff7] px-4 py-2 text-[9px] font-black uppercase tracking-widest text-emerald-500 shadow-sm">Aktif</span>
                            @else
                                <span class="rounded-full border border-white bg-[#ffe5e5] px-4 py-2 text-[9px] font-black uppercase tracking-widest text-[#ff6b6b] shadow-sm">Sembunyi</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center align-middle sm:py-5">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.tutorials.edit', $tutorial->id) }}" class="flex h-9 w-9 items-center justify-center rounded-[1rem] border-2 border-white bg-white text-[#d99b16] shadow-md shadow-amber-100/50 transition-transform hover:scale-110 hover:bg-[#fff5eb] active:scale-95 sm:h-10 sm:w-10" title="Edit Tutorial">
                                    @include('partials.wiboost-icon', ['name' => 'edit', 'class' => 'h-4 w-4'])
                                </a>
                                <form action="{{ route('admin.tutorials.destroy', $tutorial->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus tutorial ini?');" class="m-0 inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="flex h-9 w-9 items-center justify-center rounded-[1rem] border-2 border-white bg-white text-[#ff4d4d] shadow-md shadow-[#ff6b6b]/20 transition-transform hover:scale-110 hover:bg-[#ffe5e5] active:scale-95 sm:h-10 sm:w-10" title="Hapus Tutorial">
                                        @include('partials.wiboost-icon', ['name' => 'trash', 'class' => 'h-4 w-4'])
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-8 text-center text-sm font-black text-[#8faaf3]">
                            Belum ada tutorial di bagian ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
