{{-- Perapihan tampilan untuk layar sangat besar / smart TV (mengatasi konten terpotong & overscan).
     Hanya berlaku di layar >= 1536px agar tidak mengubah tampilan/mobile sama sekali. --}}
<style>
    @media screen and (min-width: 1536px) {
        /* Beri jarak aman dari tepi layar supaya konten tidak terpotong (overscan smart TV). */
        body.wiboost-user-shell main,
        body.wiboost-welcome-page main,
        main,
        footer > div {
            padding-left: max(2.5rem, env(safe-area-inset-left, 0px)) !important;
            padding-right: max(2.5rem, env(safe-area-inset-right, 0px)) !important;
        }
    }

    @media screen and (min-width: 1920px) {
        body.wiboost-user-shell main,
        body.wiboost-welcome-page main,
        main,
        footer > div {
            padding-left: max(4rem, env(safe-area-inset-left, 0px)) !important;
            padding-right: max(4rem, env(safe-area-inset-right, 0px)) !important;
        }
    }
</style>
