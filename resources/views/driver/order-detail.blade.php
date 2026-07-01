@extends('layouts.app')
@section('title', 'Detail Pesanan #' . $order->order_code)

@section('content')

<!-- Detail Header -->
<header class="sticky-header" style="padding:12px 16px;display:flex;align-items:center;gap:12px;">
    <a href="{{ route('driver.dashboard') }}" style="width:36px;height:36px;background:#f3f4f6;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#374151;text-decoration:none;">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div>
        <h1 style="font-size:15px;font-weight:700;color:#111827;">Detail Pesanan</h1>
        <p style="font-size:11px;color:#6b7280;">#{{ $order->order_code }}</p>
    </div>
    @if($order->is_suspicious)
    <span class="badge badge-red" style="margin-left:auto;">
        <i class="fas fa-exclamation-triangle"></i> Indikasi Fiktif
    </span>
    @else
    <span class="badge badge-green" style="margin-left:auto;">
        <i class="fas fa-shield-check"></i> Aman
    </span>
    @endif
</header>

<div style="padding:16px;padding-bottom:100px;">

    {{-- ── SUSPICION WARNING ─────────────────────── --}}
    @if($order->is_suspicious && $order->suspicion)
    <div style="background:#fff5f5;border:1.5px solid #fca5a5;border-radius:14px;padding:16px;margin-bottom:16px;" class="fade-in">
        <div style="display:flex;gap:12px;margin-bottom:10px;">
            <div style="width:40px;height:40px;background:#fee2e2;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-shield-exclamation" style="color:#dc2626;font-size:18px;"></i>
            </div>
            <div>
                <p style="font-size:14px;font-weight:800;color:#991b1b;">Peringatan Sistem!</p>
                <p style="font-size:11px;color:#dc2626;margin-top:2px;">Skor Risiko: <strong>{{ $order->suspicion_score }}/100</strong>
                &nbsp;
                <span style="background:#fee2e2;padding:2px 8px;border-radius:99px;font-weight:700;">
                    {{ strtoupper($order->suspicion->level) }}
                </span>
                </p>
            </div>
        </div>
        <div style="display:flex;flex-direction:column;gap:6px;">
            @foreach($order->suspicion->flags as $flag)
            <div style="display:flex;align-items:flex-start;gap:8px;font-size:12px;color:#7f1d1d;">
                <i class="fas fa-times-circle" style="color:#ef4444;margin-top:2px;flex-shrink:0;"></i>
                {{ $flag }}
            </div>
            @endforeach
        </div>
        <div style="margin-top:12px;padding-top:12px;border-top:1px solid #fca5a5;">
            <p style="font-size:11px;color:#b91c1c;font-weight:600;">
                <i class="fas fa-lightbulb"></i>
                Saran: Hubungi customer sebelum membeli makanan atau abaikan order ini.
            </p>
        </div>
    </div>
    @endif

    {{-- ── RESTAURANT ───────────────────────────── --}}
    <p class="section-label">Detail Penjemputan</p>
    <div class="card" style="margin-bottom:16px;">
        <div style="padding:14px;display:flex;align-items:center;justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:44px;height:44px;background:#fff7ed;border-radius:12px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-store" style="color:#f97316;font-size:18px;"></i>
                </div>
                <div>
                    <p style="font-size:14px;font-weight:700;color:#111827;">{{ $order->restaurant->name }}</p>
                    <p style="font-size:11px;color:#6b7280;">{{ $order->restaurant->address }}</p>
                </div>
            </div>
            <a href="https://maps.google.com/?q={{ $order->restaurant->latitude }},{{ $order->restaurant->longitude }}" target="_blank"
               style="width:34px;height:34px;background:#dbeafe;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#2563eb;text-decoration:none;">
                <i class="fas fa-map-location-dot"></i>
            </a>
        </div>
    </div>

    {{-- ── CUSTOMER ─────────────────────────────── --}}
    <p class="section-label">Detail Pengiriman</p>
    <div class="card" style="margin-bottom:16px;">
        <div style="padding:14px;display:flex;align-items:flex-start;justify-content:space-between;gap:10px;">
            <div style="display:flex;align-items:flex-start;gap:12px;flex:1;min-width:0;">
                <div style="width:44px;height:44px;background:#eff6ff;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas fa-user" style="color:#2563eb;font-size:18px;"></i>
                </div>
                <div style="min-width:0;">
                    <p style="font-size:14px;font-weight:700;color:#111827;">{{ $order->customer->name }}</p>
                    <p style="font-size:11px;color:{{ $order->is_suspicious ? '#ef4444' : '#6b7280' }};margin-top:2px;word-break:break-word;">
                        {{ $order->delivery_address }}
                    </p>
                    @if($order->customer->is_flagged)
                    <span class="badge badge-red" style="margin-top:6px;font-size:10px;">
                        <i class="fas fa-flag"></i> Customer Ditandai
                    </span>
                    @endif
                </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:6px;flex-shrink:0;">
                <a href="tel:{{ $order->customer->phone }}"
                   style="width:32px;height:32px;background:#dcfce7;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#16a34a;text-decoration:none;">
                    <i class="fas fa-phone" style="font-size:13px;"></i>
                </a>
                <a href="https://wa.me/{{ preg_replace('/^0/',  '62', $order->customer->phone ?? '') }}" target="_blank"
                   style="width:32px;height:32px;background:#fff7ed;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#f97316;text-decoration:none;">
                    <i class="fab fa-whatsapp" style="font-size:14px;"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- ── ORDER ITEMS ──────────────────────────── --}}
    <p class="section-label">Daftar Pesanan</p>
    <div class="card" style="margin-bottom:20px;">
        <div style="padding:14px;">
            @foreach($order->items as $item)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px dashed #f3f4f6;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <span style="font-size:13px;font-weight:700;color:#f97316;background:#fff7ed;padding:2px 8px;border-radius:6px;">{{ $item->quantity }}x</span>
                    <span style="font-size:13px;color:#374151;">{{ $item->item_name }}</span>
                </div>
                <span style="font-size:13px;font-weight:600;color:#111827;">Rp {{ number_format($item->subtotal,0,',','.') }}</span>
            </div>
            @endforeach

            <div style="margin-top:14px;padding-top:10px;border-top:1.5px solid #f3f4f6;display:flex;flex-direction:column;gap:6px;">
                <div style="display:flex;justify-content:space-between;font-size:12px;color:#6b7280;">
                    <span>Subtotal Makanan</span>
                    <span>Rp {{ number_format($order->subtotal,0,',','.') }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:12px;color:#6b7280;">
                    <span>Ongkir (Pendapatan Anda)</span>
                    <span style="color:#16a34a;font-weight:700;">+ Rp {{ number_format($order->delivery_fee,0,',','.') }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:15px;font-weight:800;color:#111827;padding-top:8px;border-top:1.5px solid #e5e7eb;">
                    <span>Total Modal Dibutuhkan</span>
                    <span style="color:#f97316;">Rp {{ number_format($order->total_amount,0,',','.') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal check --}}
    @if($driver->modal_saldo < $order->total_amount)
    <div class="alert alert-warning">
        <i class="fas fa-wallet"></i>
        <span>Modal Anda tidak mencukupi. Silakan top up terlebih dahulu.</span>
    </div>
    @endif

</div>

<!-- Fixed Bottom Bar -->
<div style="position:fixed;bottom:0;left:50%;transform:translateX(-50%);width:100%;max-width:430px;background:#fff;border-top:1px solid #e5e7eb;padding:12px 16px;display:flex;gap:10px;z-index:50;box-shadow:0 -4px 12px rgba(0,0,0,.08);">
    <form method="POST" action="{{ route('driver.order.ignore', $order) }}" style="flex:1;">
        @csrf
        <button type="submit" class="btn btn-outline btn-full" style="padding:14px;">
            Abaikan
        </button>
    </form>
    <form method="POST" action="{{ route('driver.order.take', $order) }}" style="flex:2;">
        @csrf
        <button type="submit" class="btn btn-primary btn-full" style="padding:14px;"
            {{ $driver->modal_saldo < $order->total_amount ? 'disabled style=opacity:.5;cursor:not-allowed;' : '' }}>
            <i class="fas fa-hand-point-up"></i> Ambil Pesanan
        </button>
    </form>
</div>

@endsection
