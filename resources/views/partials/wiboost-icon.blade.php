@php
    $iconName = $name ?? null;
    $iconLabel = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii((string) ($label ?? '')));
    $iconClass = $class ?? 'h-6 w-6';

    if (! $iconName) {
        $iconName = match (true) {
            \Illuminate\Support\Str::contains($iconLabel, ['suntik', 'sosmed', 'instagram', 'tiktok', 'followers', 'likes']) => 'network',
            \Illuminate\Support\Str::contains($iconLabel, ['top up', 'game', 'genshin', 'diamond', 'mobile legend', 'free fire']) => 'gamepad',
            \Illuminate\Support\Str::contains($iconLabel, ['paket', 'data', 'axis', 'telkomsel', 'xl', 'indosat', 'tri']) => 'signal',
            \Illuminate\Support\Str::contains($iconLabel, ['aplikasi', 'premium', 'capcut', 'canva', 'alight', 'bstation', 'claude', 'youtube', 'spotify']) => 'device',
            \Illuminate\Support\Str::contains($iconLabel, ['global']) => 'globe',
            \Illuminate\Support\Str::contains($iconLabel, ['indonesia']) => 'map',
            \Illuminate\Support\Str::contains($iconLabel, ['tutorial', 'panduan']) => 'book',
            default => 'package',
        };
    }
@endphp

@switch($iconName)
    @case('cart')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="2.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5h2.1l1.7 9.2a2 2 0 0 0 2 1.65h6.5a2 2 0 0 0 1.95-1.55L19.6 8.5H7.15M9.4 20h.01M16.6 20h.01"/></svg>
        @break
    @case('cash')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7.5h16v9H4z"/><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M16 12h.01M12 9.75a2.25 2.25 0 1 1 0 4.5 2.25 2.25 0 0 1 0-4.5Z"/></svg>
        @break
    @case('gift')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 11h16v9H4zM3.5 7h17v4h-17zM12 7v13M8.2 7C6.8 7 6 6.2 6 5.2S6.8 3.5 7.8 3.5c1.8 0 3 3.5 3 3.5M15.8 7c1.4 0 2.2-.8 2.2-1.8s-.8-1.7-1.8-1.7c-1.8 0-3 3.5-3 3.5"/></svg>
        @break
    @case('receipt')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 3.5h12v17l-2-1.2-2 1.2-2-1.2-2 1.2-2-1.2-2 1.2zM9 8h6M9 12h6M9 16h4"/></svg>
        @break
    @case('history')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12a9 9 0 1 0 3-6.7M3 4.5v5h5M12 7.5V12l3 2"/></svg>
        @break
    @case('profile')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 8a7 7 0 0 1 14 0"/></svg>
        @break
    @case('key')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.5 9.5a4 4 0 1 0-3.2 3.9L9 15.7V18h2.3l1.7-1.7h2.2v-2.2l1.5-1.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 9.5h.01"/></svg>
        @break
    @case('note')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 4.5h9l3 3v12H6zM15 4.5v3h3M9 11h6M9 15h6"/></svg>
        @break
    @case('wallet')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7.5h15a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-10a2 2 0 0 1 2-2Zm0 0 12-3v3M17 13.5h.01"/></svg>
        @break
    @case('qris')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4z"/><path stroke-linecap="round" stroke-linejoin="round" d="M14 14h2.5v2.5H14zM18.5 14H20v6h-4M14 20h.01M20 20h.01"/></svg>
        @break
    @case('success')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="2.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 6 9 17l-5-5"/></svg>
        @break
    @case('alert')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4 21 19H3zM12 9v4M12 17h.01"/></svg>
        @break
    @case('stop')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 3.5h8L20.5 8v8L16 20.5H8L3.5 16V8zM8 8l8 8M16 8l-8 8"/></svg>
        @break
    @case('zap')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="2.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m13 2-8 12h6l-1 8 8-12h-6z"/></svg>
        @break
    @case('battery')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h12a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2ZM21 11v2M8 8l8 8"/></svg>
        @break
    @case('book')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 5.5A2.5 2.5 0 0 1 7 3h4.2c.95 0 1.8.38 2.43 1 .63-.62 1.48-1 2.43-1H20a1 1 0 0 1 1 1v14.5a1 1 0 0 1-1 1h-4.25a3.5 3.5 0 0 0-2.1.7l-.15.1-.15-.1a3.5 3.5 0 0 0-2.1-.7H7A2.5 2.5 0 0 1 4.5 17V5.5Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.25v16M7.5 7h2.5M7.5 10h2.5M15.5 7H18M15.5 10H18"/></svg>
        @break
    @case('points')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3.5 14.6 9l6 .8-4.35 4.15 1.05 5.85L12 17l-5.3 2.8 1.05-5.85L3.4 9.8l6-.8L12 3.5Z"/></svg>
        @break
    @case('tutorial')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="2.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 5.5A2.5 2.5 0 0 1 7 3h11.5v16H7a2.5 2.5 0 0 0-2.5 2.5v-16Zm0 0v16M8.25 7.25h6.5M8.25 10.75h5M8.25 14.25h4"/></svg>
        @break
    @case('rocket')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14 4c3.5-.9 5.7.4 6 0-.5 3.7-1.8 6.6-4 8.7L11 18l-5-5 5.3-5A13 13 0 0 1 14 4Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M8 16l-3 3M15.5 8.5h.01"/></svg>
        @break
    @case('x')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18"/></svg>
        @break
    @case('globe')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M3.5 12h17M12 3.5c2.2 2.4 3.2 5.2 3.2 8.5s-1 6.1-3.2 8.5c-2.2-2.4-3.2-5.2-3.2-8.5s1-6.1 3.2-8.5Z"/></svg>
        @break
    @case('map')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6.5 9 4l6 2.5L20 4v13.5L15 20l-6-2.5L4 20zM9 4v13.5M15 6.5V20"/></svg>
        @break
    @case('device')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24" aria-hidden="true"><rect x="7" y="3" width="10" height="18" rx="2.2"/><path stroke-linecap="round" stroke-linejoin="round" d="M10 17.5h4M10 6.5h4"/></svg>
        @break
    @case('gamepad')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 9h9A4.5 4.5 0 0 1 21 13.5v1.2a2.8 2.8 0 0 1-4.7 2L14.7 15H9.3l-1.6 1.7a2.8 2.8 0 0 1-4.7-2v-1.2A4.5 4.5 0 0 1 7.5 9ZM7 12h3M8.5 10.5v3M16 12h.01M18 14h.01"/></svg>
        @break
    @case('signal')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 19h14M7 16v3M11 12v7M15 8v11M19 4v15"/></svg>
        @break
    @case('network')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24" aria-hidden="true"><circle cx="7" cy="7" r="3"/><circle cx="17" cy="7" r="3"/><circle cx="12" cy="17" r="3"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.5 8.5 11 14M14.5 8.5 13 14"/></svg>
        @break
    @case('play')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 5.5v13l10-6.5z"/></svg>
        @break
    @case('sparkles')
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l1.5 5L18 10l-4.5 2L12 17l-1.5-5L6 10l4.5-2zM19 15l.8 2.2L22 18l-2.2.8L19 21l-.8-2.2L16 18l2.2-.8zM5 14l.6 1.6L7 16l-1.4.4L5 18l-.6-1.6L3 16l1.4-.4z"/></svg>
        @break
    @default
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 8 12 4l7.5 4v8L12 20l-7.5-4z"/><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 8 12 12l7.5-4M12 12v8"/></svg>
@endswitch
