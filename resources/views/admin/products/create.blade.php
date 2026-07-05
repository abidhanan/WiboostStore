@extends('layouts.admin')

@section('title', 'Tambah Produk Baru')

@section('content')
<div class="wiboost-font mx-auto max-w-5xl pb-12 relative z-10">
    @include('admin.partials.form-header', [
        'backUrl' => route('admin.products.index'),
        'badge' => 'Katalog Baru',
        'title' => 'Tambah Produk',
        'description' => 'Atur data produk.',
    ])

    @include('admin.products.partials.form', [
        'action' => route('admin.products.store'),
        'method' => 'POST',
        'submitLabel' => 'Simpan Produk',
    ])
</div>
@endsection
