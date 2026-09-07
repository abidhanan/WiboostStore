@php
    $wiboostNunitoFont = asset('fonts/nunito/nunito-variable.woff2');
@endphp

{{-- Nunito di-host sendiri di server (tidak bergantung Google Fonts) supaya font tidak gagal termuat di HP. --}}
<link rel="preload" as="font" type="font/woff2" href="{{ $wiboostNunitoFont }}" crossorigin>
<style>
    @font-face {
        font-family: 'Nunito';
        font-style: normal;
        font-weight: 400 900;
        font-display: swap;
        src: url('{{ $wiboostNunitoFont }}') format('woff2');
    }
</style>
