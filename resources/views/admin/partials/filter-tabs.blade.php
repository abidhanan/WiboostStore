@php
    $filterRoute = $filterRoute ?? request()->route()?->getName();
    $filterParam = $filterParam ?? 'filter';
    $activeFilter = (string) ($activeFilter ?? request($filterParam, 'all'));
    $defaultFilter = (string) ($defaultFilter ?? 'all');
    $filterQuery = $filterQuery ?? request()->except($filterParam, 'page');
    $activeColor = $activeColor ?? '#5a76c8';
    $inactiveTextColor = $inactiveTextColor ?? '#5a76c8';
    $filterLabel = $filterLabel ?? null;
@endphp

@once
    <style>
        .wiboost-filter-tabs {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .wiboost-filter-tabs::-webkit-scrollbar {
            display: none;
            width: 0;
            height: 0;
        }
    </style>
@endonce

<div class="relative z-10 mb-6 flex w-full min-w-0 items-center gap-3 sm:mb-8">
    @if($filterLabel)
        <span class="shrink-0 rounded-full border-2 border-white bg-[#f4f9ff] px-4 py-2 text-[10px] font-black uppercase tracking-widest text-[#8faaf3] shadow-sm">
            {{ $filterLabel }}
        </span>
    @endif
<div class="wiboost-filter-tabs flex min-w-0 flex-1 gap-2 overflow-x-auto pb-2">
    @foreach($filters as $key => $filter)
        @php
            $key = (string) $key;
            $label = is_array($filter) ? ($filter['label'] ?? $key) : $filter;
            $isActiveFilter = $activeFilter === $key;
            $query = array_merge($filterQuery, [
                $filterParam => $key === $defaultFilter ? null : $key,
            ]);
            $query = array_filter($query, fn ($value) => filled($value));
        @endphp
        <a
            href="{{ route($filterRoute, $query) }}"
            class="shrink-0 rounded-full border-2 px-5 py-2.5 text-xs font-black uppercase tracking-widest shadow-sm transition active:scale-95"
            style="{{ $isActiveFilter ? 'background: ' . $activeColor . '; color: #ffffff; border-color: #ffffff;' : 'background: #ffffff; color: ' . $inactiveTextColor . '; border-color: #ffffff;' }}"
        >
            {{ $label }}
        </a>
    @endforeach
</div>
</div>
