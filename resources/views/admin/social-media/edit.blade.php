@extends('layouts.admin')

@section('title', 'Ubah Data Media Sosial')
@section('admin_header_subtitle', 'Perbarui statistik bulanan Instagram atau TikTok.')

@section('content')
<div class="wiboost-font relative z-10 mx-auto w-full max-w-6xl pb-12">
    @include('admin.partials.form-header', [
        'backUrl' => route('admin.social-media.index'),
        'badge' => 'Media Sosial',
        'title' => 'Ubah Data',
        'description' => 'Perbarui statistik dan tautan postingan pada periode ini.',
    ])

    @include('admin.social-media.partials.form')
</div>
@endsection
