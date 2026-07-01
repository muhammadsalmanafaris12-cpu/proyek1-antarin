@extends('layouts.app')
@section('title', 'Dashboard - Order Tersedia')

@section('content')
@include('driver.partials.nav', ['current' => 'dashboard', 'driver' => $driver])

<div class="page-content fade-in">

    @if($driver->user->status === 'rejected' && !is_null($driver->suspend_reason))
    <div style="padding:24px;background:#fff;border:1px solid #fecaca;border-radius:20px;box-shadow:0 10px 15px -3px rgba(0,0,0,0.05);margin-top:20px;margin-bottom:20px;">
        <div style="text-align:center;margin-bottom:20px;">
            <div style="width:80px;height:80px;background:#fef2f2;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:16px;color:#dc2626;box-shadow:0 8px 16px -4px rgba(220, 38, 38, 0.2);">
                <i class="fas fa-ban" style="font-size:36px;"></i>
            </div>
            <h3 style="font-size:18px;font-weight:800;color:#111827;margin-bottom:8px;">Akun Anda Ditangguhkan (Suspend)</h3>
            <p style="font-size:13px;color:#6b7280;max-width:320px;margin:0 auto;line-height:1.5;">
                Sebab: <strong>{{ $driver->suspend_reason }}</strong>
            </p>
        </div>

        <div class="divider" style="margin:20px 0;"></div>

        @if($driver->appeal_reason)
            <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;padding:16px;text-align:center;">
                <i class="fas fa-hourglass-half" style="color:#1d4ed8;font-size:24px;margin-bottom:8px;"></i>
                <p style="font-size:14px;font-weight:700;color:#1e3a8a;margin-bottom:4px;">Banding Telah Diajukan</p>
                <p style="font-size:12px;color:#1e40af;margin-bottom:12px;line-height:1.4;">
                    Banding Anda sedang ditinjau oleh Admin. Harap tunggu konfirmasi lebih lanjut.
                </p>
                <div style="background:#fff;border-radius:8px;padding:10px;text-align:left;border:1px solid #dbeafe;display:inline-block;max-width:100%;box-sizing:border-box;">
                    <span style="font-size:10px;color:#9ca3af;font-weight:700;display:block;text-transform:uppercase;">Alasan Anda:</span>
                    <span style="font-size:12px;color:#374151;word-break:break-word;">{{ $driver->appeal_reason }}</span>
                </div>
            </div>
        @else
            <form method="POST" action="{{ route('driver.appeal') }}">
                @csrf
                <div style="margin-bottom:16px;">
                    <label for="appeal_reason" style="font-size:13px;font-weight:700;color:#374151;display:block;margin-bottom:6px;">Ajukan Banding Pemulihan Akun</label>
                    <textarea name="appeal_reason" id="appeal_reason" 
                        style="width:100%;padding:12px;border:1.5px solid #e5e7eb;border-radius:12px;font-size:13px;color:#111827;resize:none;font-family:inherit;box-sizing:border-box;"
                        rows="4" placeholder="Tuliskan alasan yang jelas, detail, dan jujur mengapa suspend akun Anda harus dicabut..." required>{{ old('appeal_reason') }}</textarea>
                    @error('appeal_reason')
                        <p style="color:#ef4444;font-size:11px;margin-top:4px;font-weight:600;">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary btn-full" style="padding:14px;background:#dc2626;border-color:#dc2626;">
                    <i class="fas fa-paper-plane"></i> Kirim Pengajuan Banding
                </button>
            </form>
        @endif
    </div>
    @elseif(!$driver->is_online)
    <div style="text-align:center;padding:48px 24px;background:linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);border:1px solid #e5e7eb;border-radius:20px;box-shadow:0 10px 15px -3px rgba(0,0,0,0.05);margin-top:20px;margin-bottom:20px;">
        <div style="width:80px;height:80px;background:#fef3c7;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:20px;color:#d97706;box-shadow:0 8px 16px -4px rgba(217, 119, 6, 0.2);">
            <i class="fas fa-power-off" style="font-size:36px;"></i>
        </div>
        <h3 style="font-size:18px;font-weight:800;color:#111827;margin-bottom:8px;">Akun Anda Sedang Offline</h3>
        <p style="font-size:13px;color:#6b7280;max-width:280px;margin:0 auto 24px;line-height:1.5;">Aktifkan status akun Anda agar pesanan di sekitar Anda dapat ditampilkan.</p>
        <form method="POST" action="{{ route('driver.toggle-online') }}">
            @csrf
            <button type="submit" class="btn btn-primary" style="padding:12px 32px;font-size:14px;font-weight:700;border-radius:12px;background:linear-gradient(135deg, #f97316 0%, #ea580c 100%);border:none;color:#fff;cursor:pointer;box-shadow:0 4px 12px rgba(249,115,22,0.35);display:inline-flex;align-items:center;gap:8px;justify-content:center;">
                <i class="fas fa-play" style="font-size:12px;"></i> Aktifkan Sekarang
            </button>
        </form>
    </div>
    @else
    {{-- Alert info filter --}}
    <div class="alert alert-info" style="margin-bottom:16px;">
        <i class="fas fa-filter" style="flex-shrink:0;margin-top:1px;"></i>
        <div>
            <strong style="display:block;font-size:12px;">Filter Modal Aktif</strong>
            <span style="font-size:12px;">Hanya order dengan total ≤ <strong>Rp {{ number_format($driver->modal_saldo,0,',','.') }}</strong> yang ditampilkan.</span>
        </div>
    </div>

    {{-- Section header --}}
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <h2 style="font-size:15px;font-weight:700;color:#111827;">Order Masuk Di Sekitar Anda</h2>
        @if($availableOrders->count() > 0)
        <span class="badge badge-orange">{{ $availableOrders->count() }} Baru</span>
        @endif
    </div>

    {{-- Empty state --}}
    @if($availableOrders->isEmpty() && $filteredOrders->isEmpty())
    <div style="text-align:center;padding:60px 20px;">
        <div style="width:64px;height:64px;background:#f3f4f6;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:16px;">
            <i class="fas fa-box-open" style="font-size:28px;color:#9ca3af;"></i>
        </div>
        <h3 style="font-size:16px;color:#374151;margin-bottom:6px;">Belum ada order</h3>
        <p style="font-size:13px;color:#9ca3af;">Tunggu sebentar, order baru akan muncul di sini.</p>
    </div>
    @endif

    <div style="display:flex;flex-direction:column;gap:14px;">

    {{-- Available Orders --}}
    @foreach($availableOrders as $order)
    @php
        $isSuspicious = $order->is_suspicious;
        $suspicion    = $order->suspicion;
    @endphp

    <div class="card order-card {{ $isSuspicious ? 'suspicious' : '' }}"
         onclick="window.location='{{ route('driver.order.detail', $order) }}'">

        {{-- Corner badge --}}
        @if($isSuspicious)
        <div class="order-badge-corner" style="background:#ef4444;color:#fff;">
            <i class="fas fa-exclamation-triangle" style="font-size:9px;"></i> Indikasi Fiktif
        </div>
        @else
        <div class="order-badge-corner" style="background:#22c55e;color:#fff;">
            <i class="fas fa-check" style="font-size:9px;"></i> Sesuai Modal
        </div>
        @endif

        <div style="padding:14px;">
            {{-- Resto & distance --}}
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;padding-top:4px;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:38px;height:38px;background:#fff7ed;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-store" style="color:#f97316;font-size:16px;"></i>
                    </div>
                    <div>
                        <p style="font-size:13px;font-weight:700;color:#111827;">{{ $order->restaurant->name }}</p>
                        <p style="font-size:11px;color:#6b7280;">{{ $order->items->count() }} item &bull; {{ $order->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                <div style="text-align:right;flex-shrink:0;">
                    <p style="font-size:14px;font-weight:800;color:#111827;">Rp {{ number_format($order->total_amount,0,',','.') }}</p>
                    <p style="font-size:10px;color:#6b7280;">Total Belanja</p>
                </div>
            </div>

            {{-- Route --}}
            <div style="display:flex;gap:10px;margin-bottom:12px;">
                <div class="route-line">
                    <div class="route-dot" style="background:#3b82f6;"></div>
                    <div class="route-line-seg"></div>
                    <div class="route-dot" style="background:#ef4444;"></div>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="margin-bottom:10px;">
                        <p style="font-size:10px;color:#9ca3af;font-weight:500;">JEMPUT</p>
                        <p style="font-size:12px;font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $order->restaurant->address }}</p>
                    </div>
                    <div>
                        <p style="font-size:10px;color:#9ca3af;font-weight:500;">ANTAR KE</p>
                        <p style="font-size:12px;font-weight:600;color:{{ $isSuspicious ? '#ef4444' : '#111827' }};white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            {{ $order->delivery_address }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div style="display:flex;justify-content:space-between;align-items:center;border-top:1px solid #f3f4f6;padding-top:10px;">
                <div style="font-size:12px;">
                    <span style="color:#6b7280;">Ongkir: </span>
                    <span style="font-weight:700;color:#16a34a;">Rp {{ number_format($order->delivery_fee,0,',','.') }}</span>
                </div>
                <a href="{{ route('driver.order.detail', $order) }}"
                   onclick="event.stopPropagation();"
                   class="btn {{ $isSuspicious ? 'btn-outline' : 'btn-primary' }} btn-sm"
                   style="font-size:12px;">
                    {{ $isSuspicious ? 'Cek Detail' : 'Ambil Order' }}
                </a>
            </div>
        </div>
    </div>
    @endforeach

    {{-- Filtered / Over Modal --}}
    @if($filteredOrders->isNotEmpty())
    <div style="margin-top:8px;">
        <p class="section-label" style="margin-bottom:10px;">
            <i class="fas fa-ban"></i> Terfilter Modal (Tidak Dapat Diambil)
        </p>
        @foreach($filteredOrders as $order)
        <div class="card order-card filtered" style="margin-bottom:10px;">
            <div style="padding:12px;display:flex;justify-content:space-between;align-items:center;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:34px;height:34px;background:#f3f4f6;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-ban" style="color:#9ca3af;"></i>
                    </div>
                    <div>
                        <p style="font-size:13px;font-weight:600;color:#6b7280;">{{ $order->restaurant->name }}</p>
                        <p style="font-size:11px;color:#9ca3af;">Melebihi saldo modal Anda</p>
                    </div>
                </div>
                <div style="text-align:right;">
                    <p style="font-size:13px;font-weight:700;color:#ef4444;">Rp {{ number_format($order->total_amount,0,',','.') }}</p>
                    <span class="badge badge-gray" style="font-size:10px;">Terfilter</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    </div>{{-- end order list --}}
    @endif
</div>
@endsection
