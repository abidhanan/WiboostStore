@if ($paginator->hasPages())
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        $desktopWindow = 10;
        $mobileWindow = 5;
        $desktopStart = (int) (floor(($currentPage - 1) / $desktopWindow) * $desktopWindow) + 1;
        $desktopEnd = min($lastPage, $desktopStart + $desktopWindow - 1);
        $mobileStart = (int) (floor(($currentPage - 1) / $mobileWindow) * $mobileWindow) + 1;
        $mobileEnd = min($lastPage, $mobileStart + $mobileWindow - 1);
    @endphp

    <style>
        .wiboost-pagination-wrap {
            align-items: center;
            background: rgba(255, 255, 255, .9);
            border: 4px solid #fff;
            border-radius: 2.2rem;
            box-shadow: 0 18px 34px rgba(189, 224, 254, .28);
            display: flex;
            gap: 1rem;
            justify-content: space-between;
            min-width: 0;
            overflow-x: auto;
            -ms-overflow-style: none;
            padding: 1.2rem 1.35rem;
            scrollbar-width: none;
        }

        .wiboost-pagination-wrap::-webkit-scrollbar {
            display: none;
            width: 0;
            height: 0;
        }

        .wiboost-pagination-info {
            color: #5a76c8;
            font-size: .9rem;
            font-weight: 900;
        }

        .wiboost-pagination-nav {
            align-items: center;
            display: flex;
            flex-wrap: nowrap;
            gap: .45rem;
            justify-content: center;
            min-width: max-content;
            white-space: nowrap;
        }

        .wiboost-pagination-link,
        .wiboost-pagination-disabled,
        .wiboost-pagination-active {
            align-items: center;
            border: 2px solid #fff;
            border-radius: 9999px;
            display: inline-flex;
            font-size: .78rem;
            font-weight: 900;
            height: 2.45rem;
            justify-content: center;
            min-width: 2.45rem;
            padding: 0 .72rem;
            transition: all 120ms ease-out;
            white-space: nowrap;
        }

        .wiboost-pagination-link {
            background: #f4f9ff;
            color: #5a76c8;
            box-shadow: 0 10px 24px rgba(189, 224, 254, .25);
        }

        .wiboost-pagination-link:hover {
            background: #e0fbfc;
            border-color: #bde0fe;
            color: #2b3a67;
            transform: translateY(-1px);
        }

        .wiboost-pagination-active {
            background: #5a76c8;
            color: #fff;
            box-shadow: 0 12px 24px rgba(90, 118, 200, .28);
        }

        .wiboost-pagination-disabled {
            background: #f8faff;
            color: #b9c8ef;
            cursor: not-allowed;
        }

        .wiboost-pagination-mobile {
            display: none;
        }

        @media (max-width: 767px) {
            .wiboost-pagination-wrap {
                flex-direction: column;
                gap: .85rem;
                padding: 1rem;
            }

            .wiboost-pagination-info,
            .wiboost-pagination-desktop {
                display: none !important;
            }

            .wiboost-pagination-mobile {
                display: flex !important;
            }
        }
    </style>

    <div class="wiboost-pagination-wrap">
        <p class="wiboost-pagination-info">
            Menampilkan {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} dari {{ $paginator->total() }} data
        </p>

        <nav class="wiboost-pagination-nav wiboost-pagination-desktop" aria-label="Pagination">
            @if ($paginator->onFirstPage())
                <span class="wiboost-pagination-disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="wiboost-pagination-link" rel="prev" aria-label="@lang('pagination.previous')">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
                </a>
            @endif

            @for ($page = $desktopStart; $page <= $desktopEnd; $page++)
                @if ($page === $currentPage)
                    <span class="wiboost-pagination-active" aria-current="page">{{ $page }}</span>
                @else
                    <a href="{{ $paginator->url($page) }}" class="wiboost-pagination-link">{{ $page }}</a>
                @endif
            @endfor

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="wiboost-pagination-link" rel="next" aria-label="@lang('pagination.next')">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path></svg>
                </a>
            @else
                <span class="wiboost-pagination-disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path></svg>
                </span>
            @endif
        </nav>

        <nav class="wiboost-pagination-nav wiboost-pagination-mobile" aria-label="Pagination mobile">
            @if ($paginator->onFirstPage())
                <span class="wiboost-pagination-disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="wiboost-pagination-link" rel="prev" aria-label="@lang('pagination.previous')">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
                </a>
            @endif

            @for ($page = $mobileStart; $page <= $mobileEnd; $page++)
                @if ($page === $currentPage)
                    <span class="wiboost-pagination-active" aria-current="page">{{ $page }}</span>
                @else
                    <a href="{{ $paginator->url($page) }}" class="wiboost-pagination-link">{{ $page }}</a>
                @endif
            @endfor

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="wiboost-pagination-link" rel="next" aria-label="@lang('pagination.next')">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path></svg>
                </a>
            @else
                <span class="wiboost-pagination-disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path></svg>
                </span>
            @endif
        </nav>
    </div>
@endif
