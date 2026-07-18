@extends('layouts.admin')

@section('title', 'Tambah Display')

@section('content')
<div class="wiboost-font pb-12 max-w-4xl mx-auto relative z-10">
    @include('admin.partials.form-header', [
        'backUrl' => route('admin.displays.index'),
        'badge' => 'Buat Baru',
        'title' => 'Tambah Display',
        'description' => 'Atur display popup 4:5.',
    ])

    @if ($errors->any())
        <div class="mb-8 rounded-[2.5rem] border-4 border-white bg-[#ffe5e5] px-6 py-5 font-black text-[#ff6b6b] shadow-lg shadow-[#ff6b6b]/20">
            <p class="mb-3 text-lg">Periksa data berikut:</p>
            <ul class="list-inside list-disc rounded-2xl border border-white bg-white/50 p-4 text-sm font-bold">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    @php
        $themeOptions = [
            ['value' => 'blue', 'label' => 'Biru Langit'],
            ['value' => 'teal', 'label' => 'Tosca / Hijau'],
            ['value' => 'orange', 'label' => 'Oranye'],
            ['value' => 'rose', 'label' => 'Merah Muda'],
        ];
    @endphp

    <form action="{{ route('admin.displays.store') }}" method="POST" enctype="multipart/form-data" class="relative z-10 overflow-visible rounded-[2.5rem] border-4 border-white bg-white/95 p-6 shadow-xl shadow-[#bde0fe]/30 backdrop-blur-sm md:p-10">
        @csrf
        <input type="hidden" name="submission_token" value="{{ $submissionToken }}">

        @include('admin.displays.partials.form-fields', [
            'display' => null,
            'themeOptions' => $themeOptions,
            'themeSelectId' => 'display_theme_select',
        ])

        <button type="submit" class="flex w-full items-center justify-center rounded-full border-4 border-white bg-[#5a76c8] py-5 text-xl font-black text-white shadow-xl shadow-[#5a76c8]/30 transition-transform hover:bg-[#4760a9] active:scale-95">
            Simpan Display
        </button>
    </form>
</div>
@endsection
