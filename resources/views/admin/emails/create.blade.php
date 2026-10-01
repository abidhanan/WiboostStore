@extends('layouts.admin')

@section('title', 'Kirim Email')
@section('admin_header_subtitle', 'Kirim informasi kepada pengguna.')

@section('content')
@php
    $scopeLabels = [
        'all' => 'Semua User',
        'buyer' => 'Buyer',
        'member' => 'Member',
        'reseller' => 'Reseller',
        'specific' => 'Email Tertentu',
    ];
    $selectedScope = old('recipient_scope', 'all');
    $specificRecipients = collect($specificRecipients ?? [])->values()->all();
    $selectedRecipientIds = collect(old('recipient_ids', []))->map(fn ($id) => (string) $id)->values()->all();
@endphp

<div
    class="wiboost-font relative z-10 mx-auto max-w-7xl pb-12"
    x-data="{
        recipientScope: @js($selectedScope),
        recipientCounts: @js($recipientCounts),
        scopeLabels: @js($scopeLabels),
        specificRecipients: @js($specificRecipients),
        selectedRecipientIds: @js($selectedRecipientIds),
        recipientSearch: '',
        subject: @js(old('subject', '')),
        bodyText: @js(old('body', '')),
        actionLabel: @js(old('action_label', '')),
        actionUrl: @js(old('action_url', '')),
        recipientCount() {
            return this.recipientScope === 'specific'
                ? this.selectedRecipientIds.length
                : Number(this.recipientCounts[this.recipientScope] || 0);
        },
        selectedSpecificRecipients() {
            return this.specificRecipients.filter((recipient) =>
                this.selectedRecipientIds.includes(String(recipient.id))
            );
        },
        visibleSpecificRecipients() {
            const selected = this.selectedSpecificRecipients();
            const search = this.recipientSearch.trim().toLowerCase();
            if (!search) return selected;
            const matches = this.specificRecipients.filter((recipient) =>
                !this.selectedRecipientIds.includes(String(recipient.id))
                && `${recipient.name} ${recipient.email}`.toLowerCase().includes(search)
            );
            return [...selected, ...matches];
        },
        confirmSend(event) {
            const count = this.recipientCount();
            const label = this.scopeLabels[this.recipientScope] || 'pengguna';
            if (!window.confirm(`Kirim email ini ke ${count} penerima (${label})?`)) {
                event.preventDefault();
            }
        }
    }"
>
    @include('admin.partials.form-header', [
        'backUrl' => route('admin.emails.index'),
        'badge' => 'Email',
        'title' => 'Kirim Email',
        'description' => 'Kirim informasi kepada pengguna.',
    ])

    @if($errors->any())
        <div class="mb-8 flex items-start gap-4 rounded-[2rem] border-4 border-white bg-[#ffe5e5] px-6 py-5 font-black text-[#ff6b6b] shadow-lg shadow-[#ff6b6b]/20">
            <span class="mt-1 shrink-0">@include('partials.wiboost-icon', ['name' => 'alert', 'class' => 'h-7 w-7'])</span>
            <div>
                <p class="mb-3 text-lg">Masih ada data yang perlu diperbaiki:</p>
                <ul class="list-inside list-disc rounded-2xl border border-white bg-white/50 p-4 text-sm font-bold">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.emails.store') }}" method="POST" x-on:submit="confirmSend($event)" class="space-y-6">
        @csrf
        <input type="hidden" name="submission_token" value="{{ $submissionToken }}">

        <section class="rounded-[2rem] border-4 border-white bg-white/95 p-5 shadow-xl shadow-[#bde0fe]/30 sm:rounded-[2.5rem] sm:p-8">
            <div class="mb-6 flex items-center gap-4 border-b-4 border-dashed border-[#f4f9ff] pb-5">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1.2rem] border-4 border-white bg-[#e0fbfc] text-[#4bc6b9] shadow-md">
                    @include('partials.wiboost-icon', ['name' => 'users', 'class' => 'h-6 w-6'])
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-[#8faaf3]">Penerima</p>
                    <h2 class="mt-1 text-xl font-black text-[#2b3a67] sm:text-2xl">Pilih Penerima</h2>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-5">
                @foreach($scopeLabels as $scope => $label)
                    <label
                        class="flex cursor-pointer items-center justify-between gap-4 rounded-[1.5rem] border-4 p-4 shadow-sm transition"
                        :class="recipientScope === @js($scope) ? 'border-[#bde0fe] bg-[#f0f5ff]' : 'border-white bg-[#f8fbff]'"
                    >
                        <span class="min-w-0">
                            <span class="block font-black text-[#2b3a67]">{{ $label }}</span>
                            <span class="mt-1 block text-xs font-bold text-[#8faaf3]" x-text="@js($scope) === 'specific' ? `${selectedRecipientIds.length} dipilih` : `${recipientCounts[@js($scope)] || 0} penerima`"></span>
                        </span>
                        <input type="radio" name="recipient_scope" value="{{ $scope }}" x-model="recipientScope" class="h-5 w-5 shrink-0 text-[#5a76c8]" required>
                    </label>
                @endforeach
            </div>

            <div x-show="recipientScope === 'specific'" x-cloak class="mt-6 border-t-4 border-dashed border-[#f4f9ff] pt-6">
                <template x-for="recipientId in selectedRecipientIds" :key="`selected-input-${recipientId}`">
                    <input type="hidden" name="recipient_ids[]" :value="recipientId">
                </template>

                <label for="recipient_search" class="mb-3 ml-2 block text-sm font-black text-[#5a76c8]">Cari Email</label>
                <input id="recipient_search" type="search" x-model.debounce.150ms="recipientSearch" class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-6 py-4 font-black text-[#2b3a67] shadow-inner outline-none transition focus:border-[#bde0fe]">

                <div x-show="recipientSearch.trim().length > 0 || selectedRecipientIds.length > 0" class="mt-4 max-h-80 space-y-2 overflow-y-auto rounded-[1.5rem] border-4 border-white bg-[#f8fbff] p-3 shadow-inner">
                    <template x-for="recipient in visibleSpecificRecipients()" :key="recipient.id">
                        <label class="flex cursor-pointer items-center gap-3 rounded-[1.1rem] border-2 px-4 py-3 shadow-sm transition hover:border-[#bde0fe]" :class="selectedRecipientIds.includes(String(recipient.id)) ? 'border-[#bde0fe] bg-[#f0f5ff]' : 'border-white bg-white'">
                            <input type="checkbox" :value="recipient.id" x-model="selectedRecipientIds" class="h-5 w-5 shrink-0 rounded text-[#5a76c8]">
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-black text-[#2b3a67]" x-text="recipient.name"></span>
                                <span class="block truncate text-xs font-bold text-[#8faaf3]" x-text="recipient.email"></span>
                            </span>
                        </label>
                    </template>
                    <p x-show="recipientSearch.trim().length > 0 && visibleSpecificRecipients().length === 0" class="px-4 py-6 text-center text-sm font-bold text-[#8faaf3]">Email tidak ditemukan.</p>
                </div>

                <p class="mt-3 text-right text-xs font-black text-[#5a76c8]"><span x-text="selectedRecipientIds.length"></span> email dipilih</p>
            </div>
        </section>

        <section class="rounded-[2rem] border-4 border-white bg-white/95 p-5 shadow-xl shadow-[#bde0fe]/30 sm:rounded-[2.5rem] sm:p-8">
            <div class="mb-6 flex items-center gap-4 border-b-4 border-dashed border-[#f4f9ff] pb-5">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1.2rem] border-4 border-white bg-[#f0f5ff] text-[#5a76c8] shadow-md">
                    @include('partials.wiboost-icon', ['name' => 'mail', 'class' => 'h-6 w-6'])
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-[#8faaf3]">Email</p>
                    <h2 class="mt-1 text-xl font-black text-[#2b3a67] sm:text-2xl">Isi Email</h2>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                <div class="space-y-5">
                    <div>
                        <label for="subject" class="mb-3 ml-2 block text-sm font-black text-[#5a76c8]">Subjek Email</label>
                        <input id="subject" type="text" name="subject" value="{{ old('subject') }}" x-model="subject" maxlength="150" required class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-6 py-4 font-black text-[#2b3a67] shadow-inner outline-none transition focus:border-[#bde0fe]">
                    </div>

                    <div>
                        <label for="body" class="mb-3 ml-2 block text-sm font-black text-[#5a76c8]">Isi Email</label>
                        <textarea id="body" name="body" x-model="bodyText" rows="12" maxlength="20000" required class="w-full resize-y rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-6 py-4 font-bold leading-relaxed text-[#2b3a67] shadow-inner outline-none transition focus:border-[#bde0fe]"></textarea>
                        <p class="mt-2 text-right text-xs font-bold text-[#8faaf3]"><span x-text="bodyText.length"></span>/20.000</p>
                    </div>
                </div>

                <div class="rounded-[2rem] border-4 border-[#dbeafe] bg-[#f8fbff] shadow-inner">
                    <div class="border-b-4 border-dashed border-white bg-[#dbeafe] px-6 py-6 text-center">
                        <img src="{{ asset('images/wiboost-logo-nav.png') }}" alt="Logo Wiboost Store" class="mx-auto h-16 w-16 rounded-full object-contain">
                        <p class="mt-3 text-xl font-black text-[#2b3a67]">Wiboost <span class="text-[#5a76c8]">Store</span></p>
                    </div>
                    <div class="p-6">
                        <h3 class="break-words text-2xl font-black text-[#2b3a67]" x-text="subject || 'Subjek Email'"></h3>
                        <p class="mt-4 whitespace-pre-line break-words text-sm font-bold leading-relaxed text-[#2b3a67]" x-text="bodyText || 'Isi Email'"></p>
                        <template x-if="actionLabel && actionUrl">
                            <div class="mt-6 text-center">
                                <span class="inline-flex rounded-full border-4 border-white bg-[#5a76c8] px-6 py-3 text-sm font-black text-white shadow-md" x-text="actionLabel"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-[2rem] border-4 border-white bg-white/95 p-5 shadow-xl shadow-[#bde0fe]/30 sm:rounded-[2.5rem] sm:p-8">
            <div class="mb-6 flex items-center gap-4 border-b-4 border-dashed border-[#f4f9ff] pb-5">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-[1.2rem] border-4 border-white bg-[#fff7e0] text-[#d38b00] shadow-md">
                    @include('partials.wiboost-icon', ['name' => 'send', 'class' => 'h-6 w-6'])
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-[#8faaf3]">Opsional</p>
                    <h2 class="mt-1 text-xl font-black text-[#2b3a67] sm:text-2xl">Tombol Email</h2>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label for="action_label" class="mb-3 ml-2 block text-sm font-black text-[#5a76c8]">Tulisan Tombol</label>
                        <input id="action_label" type="text" name="action_label" value="{{ old('action_label') }}" x-model="actionLabel" maxlength="80" class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-6 py-4 font-black text-[#2b3a67] shadow-inner outline-none transition focus:border-[#bde0fe]">
                    </div>
                    <div>
                        <label for="action_url" class="mb-3 ml-2 block text-sm font-black text-[#5a76c8]">Tautan Tombol</label>
                        <input id="action_url" type="url" name="action_url" value="{{ old('action_url') }}" x-model="actionUrl" maxlength="500" class="w-full rounded-[1.5rem] border-4 border-white bg-[#f4f9ff] px-6 py-4 font-black text-[#2b3a67] shadow-inner outline-none transition focus:border-[#bde0fe]">
                    </div>
            </div>
        </section>

        <div class="rounded-[2rem] border-4 border-white bg-white/95 p-4 shadow-xl shadow-[#bde0fe]/30 sm:rounded-[2.5rem] sm:p-5">
            <button type="submit" class="flex w-full items-center justify-center gap-3 rounded-full border-4 border-white bg-[#5a76c8] px-6 py-5 text-lg font-black text-white shadow-xl shadow-[#5a76c8]/30 transition-transform active:scale-95">
                @include('partials.wiboost-icon', ['name' => 'send', 'class' => 'h-6 w-6'])
                <span>Kirim ke <span x-text="recipientCount()"></span> User</span>
            </button>
        </div>
    </form>
</div>
@endsection
