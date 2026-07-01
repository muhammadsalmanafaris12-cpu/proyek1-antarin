@extends('layouts.app')
@section('title', 'Daftar Order - Admin')
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
<header class="sticky-header" style="padding:14px 16px;display:flex;align-items:center;gap:10px;">
    <a href="{{ route('admin.dashboard') }}" style="width:34px;height:34px;background:#f3f4f6;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#374151;text-decoration:none;">
        <i class="fas fa-arrow-left"></i>
    </a>
    <h1 style="font-size:16px;font-weight:800;color:#111827;">Semua Order</h1>
</header>
<div class="page-content fade-in" style="padding-bottom:24px;">
    <form method="GET" action="{{ route('admin.orders') }}" style="display:flex;gap:8px;margin-bottom:16px;">
        <input type="text" name="search" placeholder="Cari kode..." class="form-control" value="{{ request('search') }}" style="flex:1.5;min-width:0;">
        <select name="status" class="form-control" style="flex:1;min-width:0;">
            <option value="">Semua Status</option>
            @foreach(['available','taken','processing','delivered','cancelled'] as $s)
            <option value="{{ $s }}" {{ request('status')===$s ? 'selected' : '' }}>{{ $statusMap[$s] ?? ucfirst($s) }}</option>
            @endforeach
        </select>
        <select name="suspicious" class="form-control" style="flex:1;min-width:0;">
            <option value="">Semua Deteksi</option>
            <option value="1" {{ request('suspicious')==='1' ? 'selected' : '' }}>Mencurigakan</option>
        </select>
        <button type="submit" class="btn btn-primary btn-sm" style="flex-shrink:0;"><i class="fas fa-search"></i></button>
    </form>
    <div style="display:flex;flex-direction:column;gap:8px;">
        @foreach($orders as $order)
        <div class="card" style="padding:12px;{{ $order->is_suspicious ? 'border-left:3px solid #ef4444;' : '' }}">
            <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                <div style="display:flex;align-items:center;gap:6px;">
                    <p style="font-size:13px;font-weight:700;color:#111827;">#{{ $order->order_code }}</p>
                    @if($order->is_suspicious)<span class="badge badge-red" style="font-size:9px;"><i class="fas fa-exclamation-triangle"></i> Fiktif</span>@endif
                </div>
                <span class="badge @switch($order->status)
                    @case('available') badge-blue @break @case('taken') badge-orange @break
                    @case('processing') badge-yellow @break @case('delivered') badge-green @break
                    @default badge-red @endswitch" style="font-size:10px;">{{ $statusMap[$order->status] ?? ucfirst($order->status) }}</span>
            </div>
            <p style="font-size:12px;color:#6b7280;">{{ $order->restaurant->name }} → {{ Str::limit($order->delivery_address,35) }}</p>
            <div style="display:flex;justify-content:space-between;margin-top:6px;font-size:11px;color:#9ca3af;">
                <div>
                    <span>Customer: {{ $order->customer->name }}</span>
                    @if($order->driver)
                        <span style="margin-left:8px;padding-left:8px;border-left:1px solid #e5e7eb;">
                            Driver: <strong>{{ $order->driver->user->name ?? '-' }}</strong>
                        </span>
                    @endif
                </div>
                <span style="font-weight:700;color:#111827;">Rp {{ number_format($order->total_amount,0,',','.') }}</span>
            </div>
        </div>
        @endforeach
    </div>
    <div style="margin-top:16px;">{{ $orders->withQueryString()->links() }}</div>
</div>
@endsection
