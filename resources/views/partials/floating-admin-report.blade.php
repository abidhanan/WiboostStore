@php
    $adminNumber = \App\Support\WiboostAdminContact::whatsappNumber();
    $floatingOffsetClass = $floatingOffsetClass ?? 'bottom-6 md:bottom-8';
    $shouldShowReportButton = $adminNumber
        && (! auth()->check() || ! auth()->user()->isAdmin())
        && ! request()->routeIs('user.report.admin*');
    $reportUrl = auth()->check() && ! auth()->user()->isAdmin()
        ? route('user.report.admin')
        : route('login');
@endphp

@if ($shouldShowReportButton)
    <style>
        @keyframes wiboostCustomerServiceFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .wiboost-whatsapp-float,
        .wiboost-customer-service-float {
            animation: wiboostCustomerServiceFloat 5s ease-in-out infinite;
        }

        @media (prefers-reduced-motion: reduce) {
            .wiboost-whatsapp-float,
            .wiboost-customer-service-float { animation: none; }
        }
    </style>

    <a href="{{ $reportUrl }}"
       aria-label="Buka bantuan customer service"
       class="wiboost-whatsapp-float wiboost-customer-service-float fixed right-4 z-[99] flex h-16 w-16 items-center justify-center rounded-[1.5rem] border-4 border-white bg-[#5a76c8] text-white shadow-xl shadow-[#5a76c8]/35 transition-transform hover:scale-110 hover:bg-[#4d66b1] active:scale-95 sm:right-8 {{ $floatingOffsetClass }}">
        <svg class="h-9 w-9 drop-shadow-sm" fill="none" stroke="currentColor" stroke-width="2.25" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 11.85a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5Zm-6.35 8.05a6.35 6.35 0 0 1 12.7 0"/>
            <g transform="translate(0 -4.4)">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5.9 12.05v-.55a6.1 6.1 0 0 1 12.2 0v.55"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 12.1h1.45v3.05H5.25a1 1 0 0 1-1-1V13.1a1 1 0 0 1 1-1Zm12.05 0h1.45a1 1 0 0 1 1 1v1.05a1 1 0 0 1-1 1H17.3V12.1Z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.05 15.05c-.45 1.55-1.7 2.35-3.65 2.35h-1.05"/>
                <path stroke-linecap="round" d="M12.55 17.4h1.2"/>
            </g>
        </svg>
    </a>
@endif
