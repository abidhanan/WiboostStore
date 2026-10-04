@extends('layouts.admin')

@section('title', 'Tambah Data Media Sosial')
@section('admin_header_subtitle', 'Tambahkan statistik bulanan Instagram atau TikTok.')

@section('content')
<div class="wiboost-font relative z-10 mx-auto w-full max-w-6xl pb-12">
    @include('admin.partials.form-header', [
        'backUrl' => route('admin.social-media.index'),
        'badge' => 'Media Sosial',
        'title' => 'Tambah Data',
        'description' => 'Masukkan statistik Instagram atau TikTok untuk bulan dan tahun yang dipilih.',
    ])

    @include('admin.social-media.partials.form')
</div>
@endsection
