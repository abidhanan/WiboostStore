@extends('layouts.admin')

@section('title', 'Tambah Formulir')

@section('content')
<div class="wiboost-font pb-12 max-w-5xl mx-auto relative z-10">
    @include('admin.partials.form-header', [
        'backUrl' => route('admin.forms.index'),
        'badge' => 'Formulir',
        'title' => 'Tambah Formulir',
        'description' => 'Buat form publik yang bisa diisi tanpa login.',
    ])

    @include('admin.forms.partials.form-fields', [
        'form' => null,
        'action' => route('admin.forms.store'),
        'method' => 'POST',
        'submitLabel' => 'Simpan Formulir',
    ])
</div>
@endsection
