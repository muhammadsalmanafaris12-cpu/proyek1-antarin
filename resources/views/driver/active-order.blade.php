@extends('layouts.app')
@section('title', 'Pesanan Aktif')

@section('content')
@include('driver.partials.nav', ['current' => 'active', 'driver' => $driver])

<div class="page-content fade-in">
    <h2 style="font-size:20px;font-weight:800;color:#111827;margin-bottom:20px;">Pesanan Aktif</h2>

    @if(!$activeOrder)
    <div style="text-align:center;padding:60px 20px;">
        <div style="width:80px;height:80px;background:#fff7ed;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:16px;">
            <i class="fas fa-motorcycle" style="font-size:36px;color:#fdba74;"></i>
        </div>
        <h3 style="font-size:16px;color:#374151;margin-bottom:8px;">Tidak ada pesanan aktif</h3>
        <p style="font-size:13px;color:#9ca3af;margin-bottom:24px;">Ambil pesanan dari tab <strong>Tersedia</strong> untuk mulai mengantar.</p>
        <a href="{{ route('driver.dashboard') }}" class="btn btn-primary">
            <i class="fas fa-list-ul"></i> Lihat Order Tersedia
        </a>
    </div>
    @else
    @php $order = $activeOrder; @endphp

    <div class="card" style="overflow:visible;margin-bottom:16px;">

        {{-- Status bar --}}
        <div style="background:linear-gradient(135deg,#fff7ed,#ffedd5);padding:14px 16px;border-bottom:1px solid #fed7aa;display:flex;justify-content:space-between;align-items:center;">
            <span style="font-size:11px;font-weight:700;color:#9a3412;text-transform:uppercase;letter-spacing:.05em;">Status Pengiriman</span>
            <span style="background:#f97316;color:#fff;font-size:11px;font-weight:700;padding:4px 12px;border-radius:99px;" class="blink">
                @if($order->status === 'taken') Menuju Restoran
                @elseif($order->status === 'processing') Menuju Customer
                @endif
            </span>
        </div>

        {{-- Center icon --}}
        <div style="padding:24px 16px;text-align:center;">
            <div style="width:80px;height:80px;background:linear-gradient(135deg,#fff7ed,#ffedd5);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:16px;border:4px solid #fff;box-shadow:0 4px 16px rgba(249,115,22,.2);">
                <i class="fas fa-motorcycle" style="font-size:32px;color:#f97316;"></i>
            </div>
            <h3 style="font-size:17px;font-weight:800;color:#111827;">{{ $order->restaurant->name }}</h3>
            <p style="font-size:13px;color:#6b7280;margin-top:4px;">#{{ $order->order_code }} &bull; Modal: Rp {{ number_format($order->total_amount,0,',','.') }}</p>

            {{-- Progress steps --}}
            <div style="display:flex;align-items:center;justify-content:center;gap:0;margin:20px 0;">
                <div style="display:flex;flex-direction:column;align-items:center;gap:4px;">
                    <div style="width:32px;height:32px;border-radius:50%;background:#f97316;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-store" style="color:#fff;font-size:14px;"></i>
                    </div>
                    <span style="font-size:10px;font-weight:600;color:#f97316;">Restoran</span>
                </div>
                <div style="flex:1;height:3px;background:{{ $order->status==='processing' ? '#f97316' : '#e5e7eb' }};max-width:60px;margin:0 4px;margin-bottom:16px;border-radius:2px;"></div>
                <div style="display:flex;flex-direction:column;align-items:center;gap:4px;">
                    <div style="width:32px;height:32px;border-radius:50%;background:{{ $order->status==='processing' ? '#f97316' : '#e5e7eb' }};display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-user" style="color:{{ $order->status==='processing' ? '#fff' : '#9ca3af' }};font-size:14px;"></i>
                    </div>
                    <span style="font-size:10px;font-weight:600;color:{{ $order->status==='processing' ? '#f97316' : '#9ca3af' }};">Customer</span>
                </div>
                <div style="flex:1;height:3px;background:#e5e7eb;max-width:60px;margin:0 4px;margin-bottom:16px;border-radius:2px;"></div>
                <div style="display:flex;flex-direction:column;align-items:center;gap:4px;">
                    <div style="width:32px;height:32px;border-radius:50%;background:#e5e7eb;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-check" style="color:#9ca3af;font-size:14px;"></i>
                    </div>
                    <span style="font-size:10px;font-weight:600;color:#9ca3af;">Selesai</span>
                </div>
            </div>

            {{-- Action buttons --}}
            <div style="display:flex;flex-direction:column;gap:10px;">
                @if($order->status === 'taken')
                <a href="https://maps.google.com/?q={{ $order->restaurant->latitude }},{{ $order->restaurant->longitude }}" target="_blank" class="btn btn-primary btn-full" style="padding:14px;font-size:14px;">
                    <i class="fas fa-location-arrow"></i> Navigasi ke Restoran
                </a>
                <form method="POST" action="{{ route('driver.order.status', $order) }}">
                    @csrf
                    <input type="hidden" name="status" value="processing">
                    <button type="submit" class="btn btn-outline btn-full" style="padding:14px;font-size:14px;">
                        <i class="fas fa-receipt"></i> Sudah Ambil Makanan <i class="fas fa-arrow-right" style="margin-left:4px;"></i>
                    </button>
                </form>
                @elseif($order->status === 'processing')
                <a href="https://maps.google.com/?q={{ $order->delivery_lat }},{{ $order->delivery_lng }}" target="_blank" class="btn btn-primary btn-full" style="padding:14px;font-size:14px;">
                    <i class="fas fa-location-arrow"></i> Navigasi ke Customer
                </a>
                <form method="POST" action="{{ route('driver.order.status', $order) }}">
                    @csrf
                    <input type="hidden" name="status" value="delivered">
                    <button type="submit" class="btn btn-outline btn-full" style="padding:14px;font-size:14px;color:#16a34a;border-color:#16a34a;">
                        <i class="fas fa-check-circle"></i> Pesanan Terkirim
                    </button>
                </form>
                @endif
            </div>
        </div>

        {{-- Customer strip --}}
        <div style="background:#f9fafb;border-top:1px solid #f3f4f6;padding:14px 16px;display:flex;justify-content:space-between;align-items:center;">
            <div>
                <p style="font-size:11px;color:#9ca3af;font-weight:500;">CUSTOMER</p>
                <p style="font-size:13px;font-weight:700;color:#111827;">{{ $order->customer->name }}</p>
                <p style="font-size:11px;color:#6b7280;">{{ $order->delivery_address }}</p>
            </div>
            <div style="display:flex;gap:8px;">
                <a href="tel:{{ $order->customer->phone }}"
                   style="width:36px;height:36px;background:#dcfce7;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#16a34a;text-decoration:none;">
                    <i class="fas fa-phone" style="font-size:14px;"></i>
                </a>
                <a href="https://wa.me/{{ preg_replace('/^0/',  '62', $order->customer->phone ?? '') }}" target="_blank"
                   style="width:36px;height:36px;background:#fff7ed;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#f97316;text-decoration:none;">
                    <i class="fab fa-whatsapp" style="font-size:16px;"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Order items summary --}}
    <div class="card">
        <div style="padding:14px;">
            <p class="section-label" style="margin-bottom:12px;">Ringkasan Pesanan</p>
            @foreach($order->items as $item)
            <div style="display:flex;justify-content:space-between;font-size:13px;padding:4px 0;">
                <span style="color:#374151;">{{ $item->quantity }}x {{ $item->item_name }}</span>
                <span style="font-weight:600;color:#111827;">Rp {{ number_format($item->subtotal,0,',','.') }}</span>
            </div>
            @endforeach
            <div style="margin-top:12px;padding-top:10px;border-top:1.5px solid #f3f4f6;display:flex;justify-content:space-between;font-size:14px;font-weight:800;">
                <span style="color:#6b7280;">Pendapatan Ongkir</span>
                <span style="color:#16a34a;">+ Rp {{ number_format($order->delivery_fee,0,',','.') }}</span>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
