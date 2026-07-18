@php
    $filterRoute = $filterRoute ?? request()->route()?->getName();
    $filterParam = $filterParam ?? 'filter';
    $activeFilter = (string) ($activeFilter ?? request($filterParam, 'all'));
    $defaultFilter = (string) ($defaultFilter ?? 'all');
    $filterQuery = $filterQuery ?? request()->except($filterParam, 'page');
    $activeColor = $activeColor ?? '#5a76c8';
    $inactiveTextColor = $inactiveTextColor ?? '#5a76c8';
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

<div class="wiboost-filter-tabs relative z-10 mb-6 flex w-full gap-2 overflow-x-auto pb-2 sm:mb-8">
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
