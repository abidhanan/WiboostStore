@extends('layouts.admin')

@section('title', 'Edit Formulir')

@section('content')
<div class="wiboost-font pb-12 max-w-5xl mx-auto relative z-10">
    @include('admin.partials.form-header', [
        'backUrl' => route('admin.forms.show', $form),
        'badge' => 'Mode Edit',
        'title' => 'Edit Formulir',
        'description' => 'Atur pertanyaan, status, dan benefit aplikasi premium.',
    ])

    @include('admin.forms.partials.form-fields', [
        'form' => $form,
        'action' => route('admin.forms.update', $form),
        'method' => 'PUT',
        'submitLabel' => 'Simpan Perubahan',
    ])
</div>
@endsection
