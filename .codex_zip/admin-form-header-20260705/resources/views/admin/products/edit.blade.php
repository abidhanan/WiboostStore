@extends('layouts.admin')

@section('title', 'Edit Produk')

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

<div class="wiboost-font mx-auto max-w-5xl pb-12 relative z-10">
    @include('admin.partials.form-header', [
        'backUrl' => route('admin.products.index'),
        'badge' => 'Mode Edit',
        'title' => 'Edit Produk',
        'description' => 'Mengubah konfigurasi untuk ' . $product->name . '.',
    ])

    <div class="relative z-10">
        @include('admin.products.partials.form', [
            'action' => route('admin.products.update', $product->id),
            'method' => 'PUT',
            'submitLabel' => 'Simpan Perubahan',
            'product' => $product,
        ])
    </div>
</div>
@endsection
