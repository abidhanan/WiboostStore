@php($isHutRiMonth = now(config('app.timezone'))->month === 8)

@if($isHutRiMonth)
    {{-- Hiasan HUT RI ke-81 — bunting bendera merah-putih (flags saja, tanpa garis penghubung). --}}
    <style>
        .wiboost-hutri-bunting-top {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 16px;
            z-index: 55;
            pointer-events: none;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='48' height='16'%3E%3Cpolygon points='0,0 24,0 12,15' fill='%23e11d1d'/%3E%3Cpolygon points='24,0 48,0 36,15' fill='%23ffffff'/%3E%3C/svg%3E") top left / 48px 16px repeat-x;
            filter: drop-shadow(0 2px 2px rgba(0, 0, 0, .18));
        }

        footer { position: relative; }
        footer::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 16px;
            z-index: 5;
            pointer-events: none;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='48' height='16'%3E%3Cpolygon points='0,0 24,0 12,15' fill='%23e11d1d'/%3E%3Cpolygon points='24,0 48,0 36,15' fill='%23ffffff'/%3E%3C/svg%3E") top left / 48px 16px repeat-x;
            filter: drop-shadow(0 2px 2px rgba(0, 0, 0, .18));
        }

        @media print {
            .wiboost-hutri-bunting-top, footer::before { display: none !important; }
        }
    </style>
    <div class="wiboost-hutri-bunting-top" aria-hidden="true"></div>

    @if(\App\Models\Product::isAgustusanPromoActive())
        <style>
            .discount-corner-ribbon__text {
                background: #e11d1d !important;
                box-shadow: 0 12px 26px rgba(225, 29, 29, .3) !important;
            }
        </style>
    @endif
@endif
