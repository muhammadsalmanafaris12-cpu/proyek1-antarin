{{-- AntarIn Logo SVG Partial
     Usage: @include('partials.logo', ['size' => 48, 'id_suffix' => 'unique'])
--}}
@php
    $size       = $size       ?? 48;
    $id_suffix  = $id_suffix  ?? 'logo';
    $show_text  = $show_text  ?? false;
@endphp
<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 80 80" xmlns="http://www.w3.org/2000/svg" style="border-radius:{{ round($size * 0.25) }}px;display:block;">
    <defs>
        <linearGradient id="lg-{{ $id_suffix }}" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#fb923c"/>
            <stop offset="100%" stop-color="#c2410c"/>
        </linearGradient>
    </defs>
    <rect width="80" height="80" rx="18" fill="url(#lg-{{ $id_suffix }})"/>
    <!-- Speed lines -->
    <line x1="5"  y1="33" x2="22" y2="33" stroke="rgba(255,255,255,0.38)" stroke-width="2.5" stroke-linecap="round"/>
    <line x1="3"  y1="40" x2="18" y2="40" stroke="rgba(255,255,255,0.26)" stroke-width="2"   stroke-linecap="round"/>
    <line x1="5"  y1="47" x2="20" y2="47" stroke="rgba(255,255,255,0.18)" stroke-width="2"   stroke-linecap="round"/>
    <!-- Shadow under scooter -->
    <ellipse cx="49" cy="57" rx="15" ry="3.5" fill="rgba(0,0,0,0.18)"/>
    <!-- Rear wheel -->
    <circle cx="37" cy="54" r="8"   fill="none" stroke="white" stroke-width="3.5"/>
    <circle cx="37" cy="54" r="2.8" fill="white"/>
    <!-- Front wheel -->
    <circle cx="60" cy="54" r="8"   fill="none" stroke="white" stroke-width="3.5"/>
    <circle cx="60" cy="54" r="2.8" fill="white"/>
    <!-- Scooter frame -->
    <path d="M38 54 L41 43 L54 41 L64 45 L62 54" fill="none" stroke="white" stroke-width="3.5" stroke-linejoin="round" stroke-linecap="round"/>
    <!-- Seat panel -->
    <path d="M41 43 L45 36 L56 36 L58 41" fill="white" opacity="0.92"/>
    <!-- Delivery box -->
    <rect x="39" y="24" width="17" height="13" rx="3.5" fill="white"/>
    <line x1="47.5" y1="24" x2="47.5" y2="37" stroke="rgba(234,88,12,0.45)" stroke-width="1.5"/>
    <line x1="39"   y1="30.5" x2="56" y2="30.5" stroke="rgba(234,88,12,0.45)" stroke-width="1.5"/>
    <!-- Strap line -->
    <path d="M46 37 Q49 41 52 37" fill="none" stroke="rgba(234,88,12,0.5)" stroke-width="1.5" stroke-linecap="round"/>
    <!-- Rider torso -->
    <path d="M52 39 L54 46 L64 45 L63 38 Z" fill="white" opacity="0.92"/>
    <!-- Rider head / helmet -->
    <circle cx="58" cy="31" r="7" fill="white"/>
    <!-- Helmet visor -->
    <path d="M52.5 31 Q58 25 63.5 31" fill="rgba(234,88,12,0.18)" stroke="rgba(234,88,12,0.55)" stroke-width="1.5" stroke-linecap="round"/>
    <!-- Arm -->
    <line x1="58" y1="38" x2="64" y2="43" stroke="white" stroke-width="3" stroke-linecap="round"/>
    <!-- Text area background pill -->
    <rect x="14" y="60" width="52" height="16" rx="6" fill="rgba(0,0,0,0.18)"/>
    <!-- AntarIn text -->
    <text x="40" y="72" text-anchor="middle" font-family="'Inter','Arial',sans-serif" font-size="10" font-weight="900" fill="white" letter-spacing="-0.3">AntarIn</text>
</svg>
@if($show_text)
<div style="line-height:1.2;">
    <p style="font-size:inherit;font-weight:900;color:#111827;letter-spacing:-0.5px;">AntarIn</p>
    <p style="font-size:11px;color:#6b7280;">Cepat · Aman · Terpercaya</p>
</div>
@endif
