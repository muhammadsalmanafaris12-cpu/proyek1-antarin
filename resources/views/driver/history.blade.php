@extends('layouts.app')
@section('title', 'Riwayat Pesanan')

@section('content')
@include('driver.partials.nav', ['current' => 'history', 'driver' => $driver])

<div class="page-content fade-in">
    <h2 style="font-size:20px;font-weight:800;color:#111827;margin-bottom:16px;">Riwayat Pesanan</h2>

    {{-- Stats grid --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px;">
        <div class="card" style="text-align:center;padding:16px;">
            <p style="font-size:11px;color:#6b7280;font-weight:500;margin-bottom:4px;">Total Selesai</p>
            <p style="font-size:24px;font-weight:800;color:#111827;">{{ $totalOrders }}</p>
        </div>
        <div class="card" style="text-align:center;padding:16px;">
            <p style="font-size:11px;color:#6b7280;font-weight:500;margin-bottom:4px;">Total Pendapatan</p>
            <p style="font-size:18px;font-weight:800;color:#16a34a;">Rp {{ number_format($totalEarnings,0,',','.') }}</p>
        </div>
        <div class="card" style="text-align:center;padding:16px;">
            <p style="font-size:11px;color:#6b7280;font-weight:500;margin-bottom:4px;">Hari Ini</p>
            <p style="font-size:22px;font-weight:800;color:#111827;">{{ $todayCount }}</p>
        </div>
        <div class="card" style="text-align:center;padding:16px;">
            <p style="font-size:11px;color:#6b7280;font-weight:500;margin-bottom:4px;">Pendapatan Hari Ini</p>
            <p style="font-size:17px;font-weight:800;color:#16a34a;">Rp {{ number_format($todayEarnings,0,',','.') }}</p>
        </div>
    </div>

    <p class="section-label">Riwayat Pengiriman</p>

    @if($orders->isEmpty())
    <div style="text-align:center;padding:40px 20px;">
        <i class="fas fa-clock-rotate-left" style="font-size:36px;color:#d1d5db;margin-bottom:12px;display:block;"></i>
        <p style="font-size:14px;color:#9ca3af;">Belum ada riwayat pengiriman.</p>
    </div>
    @else
    <div style="display:flex;flex-direction:column;gap:10px;">
        @foreach($orders as $order)
        <div class="card" style="padding:14px;display:flex;align-items:center;justify-content:space-between;gap:12px;">
            <div style="display:flex;align-items:center;gap:12px;flex:1;min-width:0;">
                <div style="width:42px;height:42px;border-radius:12px;background:{{ $order->status==='delivered' ? '#dcfce7' : '#fee2e2' }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas {{ $order->status==='delivered' ? 'fa-check' : 'fa-times' }}"
                       style="color:{{ $order->status==='delivered' ? '#16a34a' : '#ef4444' }};font-size:16px;"></i>
                </div>
                <div style="min-width:0;">
                    <p style="font-size:13px;font-weight:700;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $order->restaurant->name }}</p>
                    <p style="font-size:10px;color:#9ca3af;margin-top:2px;">
                        {{ $order->status==='delivered' ? 'Selesai' : 'Dibatalkan' }}
                        {{ $order->updated_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }}
                        &bull; #{{ $order->order_code }}
                    </p>
                    @if($order->is_suspicious)
                    <span class="badge badge-red" style="font-size:9px;margin-top:4px;">Indikasi Fiktif</span>
                    @endif
                </div>
            </div>
            <div style="text-align:right;flex-shrink:0;">
                @if($order->status==='delivered')
                <p style="font-size:14px;font-weight:800;color:#16a34a;">+ Rp {{ number_format($order->delivery_fee,0,',','.') }}</p>
                @else
                <span class="badge badge-red" style="font-size:10px;">Batal</span>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div style="margin-top:16px;">
        {{ $orders->links() }}
    </div>
    @endif
</div>
@endsection
