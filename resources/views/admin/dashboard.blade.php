@extends('layouts.app')
@section('title', 'Admin Dashboard')

@section('content')
@php
    $statusMap = [
        'available'  => 'Tersedia',
        'taken'      => 'Diambil',
        'processing' => 'Diproses',
        'delivered'  => 'Selesai',
        'cancelled'  => 'Batal',
    ];
@endphp
{{-- Admin Header --}}
<header class="sticky-header" style="padding:16px;">
    <div style="display:flex;justify-content:space-between;align-items:center;">
        <div style="display:flex;align-items:center;gap:10px;">
            @include('partials.logo', ['size' => 36, 'id_suffix' => 'admin-dash'])
            <div>
                <h1 style="font-size:18px;font-weight:800;color:#111827;">AntarIn Admin</h1>
                <p style="font-size:11px;color:#6b7280;">Panel Manajemen Sistem</p>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            <a href="{{ route('admin.drivers') }}" style="background:#eff6ff;color:#2563eb;border-radius:10px;padding:8px 12px;font-size:12px;font-weight:700;text-decoration:none;position:relative;">
                <i class="fas fa-users"></i> Drivers
                @if($stats['pending_drivers'] > 0)
                <span style="position:absolute;top:-6px;right:-6px;background:#ef4444;color:#fff;border-radius:50%;width:18px;height:18px;font-size:10px;display:flex;align-items:center;justify-content:center;font-weight:800;border:2px solid #fff;">
                    {{ $stats['pending_drivers'] }}
                </span>
                @endif
            </a>
            <a href="{{ route('admin.withdrawals') }}" style="background:#ecfdf5;color:#059669;border-radius:10px;padding:8px 12px;font-size:12px;font-weight:700;text-decoration:none;position:relative;">
                <i class="fas fa-money-bill-transfer"></i> WD
                @if($stats['pending_withdrawals'] > 0)
                <span style="position:absolute;top:-6px;right:-6px;background:#ef4444;color:#fff;border-radius:50%;width:18px;height:18px;font-size:10px;display:flex;align-items:center;justify-content:center;font-weight:800;border:2px solid #fff;">
                    {{ $stats['pending_withdrawals'] }}
                </span>
                @endif
            </a>
            <a href="{{ route('admin.orders') }}" style="background:#fff7ed;color:#f97316;border-radius:10px;padding:8px 12px;font-size:12px;font-weight:700;text-decoration:none;">
                <i class="fas fa-list"></i> Orders
            </a>
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" style="background:#fee2e2;color:#b91c1c;border:none;border-radius:10px;padding:8px 12px;font-size:12px;font-weight:700;cursor:pointer;">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </form>
        </div>
    </div>
</header>

<div class="page-content fade-in" style="padding-bottom:24px;">

    @if($stats['pending_drivers'] > 0)
    <div class="alert alert-info fade-in" style="margin-bottom:16px;align-items:center;">
        <i class="fas fa-id-card-clip" style="font-size:16px;"></i>
        <div style="flex:1;">
            Ada <strong>{{ $stats['pending_drivers'] }}</strong> calon driver menunggu persetujuan.
        </div>
        <a href="{{ route('admin.drivers') }}" class="btn btn-sm" style="background:#2563eb;color:#fff;padding:6px 12px;border-radius:8px;font-size:11px;text-decoration:none;">
            Tinjau
        </a>
    </div>
    @endif

    @if($stats['pending_withdrawals'] > 0)
    <div class="alert alert-info fade-in" style="margin-bottom:16px;align-items:center;background:#ecfdf5;border-color:#a7f3d0;color:#065f46;">
        <i class="fas fa-money-bill-transfer" style="font-size:16px;color:#059669;"></i>
        <div style="flex:1;">
            Ada <strong>{{ $stats['pending_withdrawals'] }}</strong> pengajuan penarikan dana (WD) menunggu peninjauan.
        </div>
        <a href="{{ route('admin.withdrawals') }}" class="btn btn-sm" style="background:#059669;color:#fff;padding:6px 12px;border-radius:8px;font-size:11px;text-decoration:none;border:none;">
            Tinjau WD
        </a>
    </div>
    @endif

    {{-- Stats grid --}}
    <p class="section-label" style="margin-bottom:12px;">Ringkasan Sistem</p>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px;">
        <div class="card" style="padding:14px;grid-column: span 2;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
                <div style="width:36px;height:36px;background:#fff7ed;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-receipt" style="color:#f97316;"></i>
                </div>
                <p style="font-size:11px;color:#6b7280;font-weight:600;">Total Order</p>
            </div>
            <p style="font-size:26px;font-weight:800;color:#111827;">{{ $stats['total_orders'] }}</p>
        </div>
        <div class="card" style="padding:14px;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
                <div style="width:36px;height:36px;background:#f0fdf4;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-check-circle" style="color:#22c55e;"></i>
                </div>
                <p style="font-size:11px;color:#6b7280;font-weight:600;">Terkirim</p>
            </div>
            <p style="font-size:26px;font-weight:800;color:#16a34a;">{{ $stats['delivered_orders'] }}</p>
        </div>
        <div class="card" style="padding:14px;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
                <div style="width:36px;height:36px;background:#eff6ff;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-motorcycle" style="color:#3b82f6;"></i>
                </div>
                <p style="font-size:11px;color:#6b7280;font-weight:600;">Driver Online</p>
            </div>
            <p style="font-size:26px;font-weight:800;color:#2563eb;">{{ $stats['online_drivers'] }}<span style="font-size:14px;color:#9ca3af;">/{{ $stats['total_drivers'] }}</span></p>
        </div>
    </div>



    {{-- Recent Orders --}}
    <p class="section-label" style="margin-bottom:10px;">Order Terbaru</p>
    <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:20px;">
        @foreach($recentOrders as $order)
        <div class="card" style="padding:12px;display:flex;align-items:center;justify-content:space-between;gap:10px;">
            <div style="flex:1;min-width:0;">
                <div style="display:flex;align-items:center;gap:6px;margin-bottom:3px;">
                    <p style="font-size:12px;font-weight:700;color:#111827;">#{{ $order->order_code }}</p>
                    @if($order->is_suspicious)
                    <span style="width:6px;height:6px;border-radius:50%;background:#ef4444;flex-shrink:0;"></span>
                    @endif
                </div>
                <p style="font-size:11px;color:#6b7280;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ $order->restaurant->name }} • Customer: {{ $order->customer->name }}
                    @if($order->driver)
                        • Driver: <strong>{{ $order->driver->user->name ?? '-' }}</strong>
                    @endif
                </p>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;flex-shrink:0;">
                <span class="badge @switch($order->status)
                    @case('available') badge-blue @break
                    @case('taken') badge-orange @break
                    @case('processing') badge-yellow @break
                    @case('delivered') badge-green @break
                    @case('cancelled') badge-red @break
                    @default badge-gray
                @endswitch" style="font-size:10px;">{{ $statusMap[$order->status] ?? ucfirst($order->status) }}</span>
                <p style="font-size:11px;font-weight:700;color:#111827;">Rp {{ number_format($order->total_amount,0,',','.') }}</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Drivers --}}
    <p class="section-label" style="margin-bottom:10px;">Status Driver</p>
    <div style="display:flex;flex-direction:column;gap:8px;">
        @foreach($drivers as $driver)
        <div class="card" style="padding:12px;display:flex;align-items:center;justify-content:space-between;gap:10px;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,#f97316,#ea580c);display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:800;color:#fff;flex-shrink:0;position:relative;">
                    {{ strtoupper(substr($driver->user->name ?? 'D', 0, 1)) }}
                    @if($driver->is_online)
                    <div style="position:absolute;bottom:0;right:0;width:10px;height:10px;background:#22c55e;border-radius:50%;border:2px solid #fff;"></div>
                    @endif
                </div>
                <div>
                    <p style="font-size:13px;font-weight:700;color:#111827;">{{ $driver->user->name ?? 'N/A' }}</p>
                    <p style="font-size:11px;color:#6b7280;">{{ $driver->total_orders }} order • Rp {{ number_format($driver->modal_saldo,0,',','.') }} modal</p>
                </div>
            </div>
            <span class="badge {{ $driver->is_online ? 'badge-green' : 'badge-gray' }}" style="font-size:10px;">
                {{ $driver->is_online ? 'Online' : 'Offline' }}
            </span>
        </div>
        @endforeach
    </div>

</div>
@endsection
