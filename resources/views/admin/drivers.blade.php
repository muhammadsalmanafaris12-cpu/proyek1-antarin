@extends('layouts.app')
@section('title', 'Manajemen Driver - Admin')
@section('content')
<header class="sticky-header" style="padding:14px 16px;display:flex;align-items:center;gap:10px;">
    <a href="{{ route('admin.dashboard') }}" style="width:34px;height:34px;background:#f3f4f6;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#374151;text-decoration:none;">
        <i class="fas fa-arrow-left"></i>
    </a>
    <h1 style="font-size:16px;font-weight:800;color:#111827;">Daftar Driver</h1>
</header>

<div class="page-content fade-in" style="padding-bottom:24px;">
    <!-- Tab Controls -->
    <div style="display:flex;background:#e5e7eb;border-radius:10px;padding:4px;margin-bottom:16px;">
        <button onclick="switchTab('active-tab')" id="btn-active-tab" class="tab-btn" style="flex:1;border:none;background:#fff;border-radius:8px;padding:8px;font-size:13px;font-weight:700;color:#111827;cursor:pointer;transition:all 0.2s;">
            Aktif ({{ $drivers->total() }})
            @if($suspendedCount > 0)
            <span style="background:#ef4444;color:#fff;font-size:9px;font-weight:800;padding:1px 6px;border-radius:20px;margin-left:4px;">{{ $suspendedCount }} suspend</span>
            @endif
        </button>
        <button onclick="switchTab('pending-tab')" id="btn-pending-tab" class="tab-btn" style="flex:1;border:none;background:transparent;border-radius:8px;padding:8px;font-size:13px;font-weight:700;color:#4b5563;cursor:pointer;transition:all 0.2s;">
            Persetujuan ({{ count($pendingDrivers) }})
        </button>
    </div>

    <!-- Active Drivers Content -->
    <div id="active-tab" class="tab-content" style="display:block;">
        <div style="display:flex;flex-direction:column;gap:12px;">
            @foreach($drivers as $driver)
            @php
                $isBadRating  = $driver->hasRating() && $driver->rating < 3.5;
                $isLongOffline = !$driver->is_online && $driver->updated_at && $driver->updated_at->diffInHours(now()) >= 72;
                $isWarned     = !is_null($driver->warned_at);
                $isSuspended  = $driver->user && $driver->user->status === 'rejected';
                $hasRisk      = $isBadRating || $isLongOffline || $isWarned;
            @endphp
            <div class="card" style="padding:16px; {{ $isSuspended ? 'opacity:0.7;border:1px solid #fecaca;' : ($hasRisk ? 'border:1px solid #fed7aa;' : '') }}">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;">
                    <div style="display:flex;gap:12px;">
                        <!-- Driver Avatar -->
                        <div style="width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,#f97316,#ea580c);display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:800;color:#fff;flex-shrink:0;position:relative;">
                            {{ strtoupper(substr($driver->user->name ?? 'D', 0, 1)) }}
                            @if($driver->is_online)
                            <div style="position:absolute;bottom:0;right:0;width:12px;height:12px;background:#22c55e;border-radius:50%;border:2px solid #fff;"></div>
                            @else
                            <div style="position:absolute;bottom:0;right:0;width:12px;height:12px;background:#9ca3af;border-radius:50%;border:2px solid #fff;"></div>
                            @endif
                        </div>
                        <div>
                            <div style="display:flex;align-items:center;gap:6px;">
                                <h3 style="font-size:14px;font-weight:800;color:#111827;">{{ $driver->user->name ?? 'N/A' }}</h3>
                                @if($driver->is_verified)
                                <i class="fas fa-check-circle" style="color:#22c55e;font-size:14px;" title="Terverifikasi"></i>
                                @endif
                            </div>
                            <p style="font-size:11px;color:#6b7280;margin-top:2px;">
                                <i class="fas fa-phone" style="font-size:10px;margin-right:2px;"></i> {{ $driver->phone ?? '-' }}
                            </p>
                            <p style="font-size:11px;color:#6b7280;margin-top:2px;">
                                <i class="fas fa-motorcycle" style="font-size:10px;margin-right:2px;"></i> {{ $driver->vehicle_type ?? 'Motor' }} • {{ $driver->vehicle_plate ?? '-' }}
                            </p>
                            @if($driver->operational_area)
                            <span style="display:inline-block;margin-top:4px;background:#fff7ed;color:#c2410c;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;border:1px solid #fed7aa;">
                                <i class="fas fa-location-dot" style="font-size:9px;"></i> {{ $driver->operational_area }}
                            </span>
                            @endif
                        </div>
                    </div>

                    <div style="text-align:right;">
                        <div style="display:flex;align-items:center;justify-content:flex-end;gap:4px;margin-bottom:4px;">
                            @if($driver->hasRating())
                                <i class="fas fa-star" style="color:{{ ($driver->rating) < 3.5 ? '#ef4444' : '#eab308' }};font-size:11px;"></i>
                                <span style="font-size:12px;font-weight:700;color:{{ ($driver->rating) < 3.5 ? '#ef4444' : '#374151' }};">{{ number_format($driver->rating, 1) }}</span>
                            @else
                                <span style="font-size:10px;font-weight:700;background:#f0fdf4;color:#16a34a;padding:2px 8px;border-radius:20px;border:1px solid #bbf7d0;">✨ Baru</span>
                            @endif
                        </div>
                        <span class="badge {{ $isSuspended ? 'badge-red' : ($driver->is_online ? 'badge-green' : 'badge-gray') }}" style="font-size:10px;">
                            {{ $isSuspended ? 'Disuspend' : ($driver->is_online ? 'Online' : 'Offline') }}
                        </span>
                    </div>
                </div>

                {{-- Risk Badges --}}
                @if($isSuspended || $hasRisk)
                <div style="display:flex;flex-direction:column;gap:6px;margin-top:10px;">
                    @if($isSuspended)
                        <span style="background:#fef2f2;color:#991b1b;font-size:10px;font-weight:700;padding:3px 10px;border-radius:20px;border:1px solid #fecaca;display:inline-block;width:fit-content;">
                            <i class="fas fa-ban"></i> Akun Disuspend {{ $driver->suspend_reason ? '— '.$driver->suspend_reason : '' }}
                        </span>
                        @if($driver->appeal_reason)
                        <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:8px 12px;font-size:11px;">
                            <p style="color:#1e40af;font-weight:800;margin:0 0 4px;"><i class="fas fa-paper-plane"></i> Driver Mengajukan Banding Pemulihan:</p>
                            <p style="color:#374151;margin:0;font-style:italic;">"{{ $driver->appeal_reason }}"</p>
                            <p style="color:#9ca3af;font-size:9px;margin:4px 0 0;">Diajukan: {{ $driver->appeal_at ? $driver->appeal_at->diffForHumans() : '-' }}</p>
                        </div>
                        @endif
                    @else
                        <div style="display:flex;flex-wrap:wrap;gap:6px;">
                            @if($isBadRating)
                            <span style="background:#fef2f2;color:#b91c1c;font-size:10px;font-weight:700;padding:3px 10px;border-radius:20px;border:1px solid #fecaca;">
                                <i class="fas fa-star-half-stroke"></i> Rating Buruk (&lt; 3.5)
                            </span>
                            @endif
                            @if($isLongOffline)
                            <span style="background:#f3f4f6;color:#374151;font-size:10px;font-weight:700;padding:3px 10px;border-radius:20px;border:1px solid #d1d5db;">
                                <i class="fas fa-clock"></i> Offline {{ $driver->updated_at->diffForHumans() }}
                            </span>
                            @endif
                            @if($isWarned)
                            <span style="background:#fffbeb;color:#92400e;font-size:10px;font-weight:700;padding:3px 10px;border-radius:20px;border:1px solid #fde68a;">
                                <i class="fas fa-triangle-exclamation"></i> Sudah Diperingatkan {{ $driver->warned_at->diffForHumans() }}
                            </span>
                            @endif
                        </div>
                    @endif
                </div>
                @endif

                <div class="divider" style="margin:12px 0;"></div>

                <!-- Stats & Balance -->
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <p style="font-size:10px;color:#9ca3af;font-weight:600;text-transform:uppercase;">Saldo Modal</p>
                        <p style="font-size:14px;font-weight:800;color:#111827;margin-top:2px;">Rp {{ number_format($driver->modal_saldo,0,',','.') }}</p>
                    </div>
                    <div style="text-align:right;">
                        <p style="font-size:10px;color:#9ca3af;font-weight:600;text-transform:uppercase;">Total Pendapatan</p>
                        <p style="font-size:14px;font-weight:800;color:#16a34a;margin-top:2px;">Rp {{ number_format($driver->total_earnings,0,',','.') }}</p>
                    </div>
                </div>
                
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px;">
                    <span style="font-size:11px;color:#6b7280;">Total Order: <strong>{{ $driver->total_orders }}</strong></span>
                </div>

                @php
                    $activeOrder = $driver->orders->whereIn('status', ['taken', 'processing'])->first();
                    $completedOrders = $driver->orders->where('status', 'delivered');
                @endphp

                <div style="margin-top:12px;background:#f9fafb;border-radius:10px;padding:10px;border:1px solid #f3f4f6;">
                    <p style="font-size:10px;color:#4b5563;font-weight:700;text-transform:uppercase;margin-bottom:6px;">Order Saat Ini</p>
                    @if($activeOrder)
                        <div style="font-size:12px;color:#111827;display:flex;justify-content:space-between;align-items:center;">
                            <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:70%;">
                                <strong>#{{ $activeOrder->order_code }}</strong> 
                                <span style="font-size:10px;color:#6b7280;">({{ $activeOrder->restaurant->name ?? 'Restoran' }})</span>
                            </span>
                            <span class="badge @if($activeOrder->status === 'taken') badge-orange @else badge-yellow @endif" style="font-size:9px;padding:2px 6px;">
                                {{ $activeOrder->status === 'taken' ? 'Diambil' : 'Diproses' }}
                            </span>
                        </div>
                    @else
                        <p style="font-size:11px;color:#9ca3af;margin:0;"><i class="fas fa-circle-info"></i> Tidak ada order aktif</p>
                    @endif

                    <div class="divider" style="margin:8px 0;border-top-style:dashed;"></div>

                    <p style="font-size:10px;color:#4b5563;font-weight:700;text-transform:uppercase;margin-bottom:6px;">Riwayat Order Selesai ({{ $completedOrders->count() }})</p>
                    @if($completedOrders->isNotEmpty())
                        <div style="display:flex;flex-direction:column;gap:4px;max-height:80px;overflow-y:auto;padding-right:4px;">
                            @foreach($completedOrders->take(5) as $co)
                                <div style="font-size:11px;color:#374151;display:flex;justify-content:space-between;align-items:center;">
                                    <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:70%;">#{{ $co->order_code }} <span style="color:#9ca3af;font-size:10px;">({{ $co->restaurant->name ?? 'Restoran' }})</span></span>
                                    <span style="font-weight:700;color:#16a34a;flex-shrink:0;">+Rp {{ number_format($co->delivery_fee,0,',','.') }}</span>
                                </div>
                            @endforeach
                            @if($completedOrders->count() > 5)
                                <p style="font-size:10px;color:#9ca3af;text-align:center;margin-top:2px;margin-bottom:0;">+{{ $completedOrders->count() - 5 }} order lainnya</p>
                            @endif
                        </div>
                    @else
                        <p style="font-size:11px;color:#9ca3af;margin:0;"><i class="fas fa-circle-info"></i> Belum ada order diselesaikan</p>
                    @endif
                </div>

                {{-- Admin Action Buttons --}}
                <div style="display:flex;gap:8px;margin-top:12px;flex-wrap:wrap;">
                    @if($isSuspended)
                        {{-- Pulihkan dari suspend --}}
                        <form method="POST" action="{{ route('admin.drivers.reinstate', $driver->id) }}" style="flex:1;">
                            @csrf
                            <button type="submit" style="width:100%;padding:8px;font-size:12px;font-weight:700;background:#dcfce7;color:#166534;border:1px solid #86efac;border-radius:10px;cursor:pointer;">
                                <i class="fas fa-rotate-left"></i> {{ $driver->appeal_reason ? 'Terima Banding & Pulihkan' : 'Pulihkan Akun' }}
                            </button>
                        </form>
                    @else
                        {{-- Peringatkan atau Cabut Peringatan --}}
                        @if($isWarned)
                            {{-- Sudah diperingatkan: tampilkan tombol Cabut Peringatan --}}
                            <form method="POST" action="{{ route('admin.drivers.unwarn', $driver->id) }}" style="flex:1;">
                                @csrf
                                <button type="submit"
                                    style="width:100%;padding:8px;font-size:12px;font-weight:700;background:#f3f4f6;color:#374151;border:1px solid #d1d5db;border-radius:10px;cursor:pointer;">
                                    <i class="fas fa-xmark"></i> Cabut Peringatan
                                </button>
                            </form>
                        @else
                            {{-- Belum diperingatkan: tampilkan tombol Peringatkan --}}
                            <button type="button"
                                onclick="openWarnModal({{ $driver->id }}, '{{ addslashes($driver->user->name ?? 'driver') }}')"
                                style="flex:1;padding:8px;font-size:12px;font-weight:700;background:#fffbeb;color:#92400e;border:1px solid #fde68a;border-radius:10px;cursor:pointer;">
                                <i class="fas fa-triangle-exclamation"></i> Peringatkan
                            </button>
                        @endif

                        {{-- Suspend --}}
                        <button type="button"
                            onclick="openSuspendModal({{ $driver->id }}, '{{ addslashes($driver->user->name ?? 'driver') }}')"
                            style="flex:1;padding:8px;font-size:12px;font-weight:700;background:#fef2f2;color:#991b1b;border:1px solid #fecaca;border-radius:10px;cursor:pointer;">
                            <i class="fas fa-ban"></i> Suspend
                        </button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        @if($drivers->isEmpty())
        <div style="text-align:center;padding:40px 20px;">
            <i class="fas fa-users-slash" style="font-size:40px;color:#d1d5db;margin-bottom:12px;"></i>
            <p style="font-size:14px;color:#6b7280;">Tidak ada driver aktif.</p>
        </div>
        @endif

        <div style="margin-top:16px;">
            {{ $drivers->links() }}
        </div>
    </div>

    <!-- Pending Drivers Content -->
    <div id="pending-tab" class="tab-content" style="display:none;">
        <div style="display:flex;flex-direction:column;gap:12px;">
            @foreach($pendingDrivers as $user)
            <div class="card" style="padding:16px;">
                <div style="display:flex;gap:12px;align-items:flex-start;">
                    <div style="width:48px;height:48px;border-radius:50%;background:#e8f0fe;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:800;color:#1a73e8;flex-shrink:0;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div style="flex:1;">
                        <h3 style="font-size:14px;font-weight:800;color:#111827;">{{ $user->name }}</h3>
                        <p style="font-size:11px;color:#6b7280;margin-top:2px;">
                            <i class="fas fa-envelope"></i> {{ $user->email }}
                        </p>
                        <p style="font-size:11px;color:#6b7280;margin-top:2px;">
                            <i class="fas fa-phone"></i> {{ $user->phone ?? '-' }}
                        </p>
                        <p style="font-size:11px;color:#6b7280;margin-top:4px;">
                            <strong>Alamat:</strong> {{ $user->address ?? '-' }}
                        </p>
                        @if($user->driver && $user->driver->operational_area)
                        <div style="margin-top:6px;">
                            <span style="display:inline-block;background:#eff6ff;color:#1d4ed8;font-size:10px;font-weight:700;padding:3px 10px;border-radius:20px;border:1px solid #bfdbfe;">
                                <i class="fas fa-map-location-dot" style="font-size:9px;"></i> Wilayah: {{ $user->driver->operational_area }}
                            </span>
                        </div>
                        @else
                        <div style="margin-top:6px;">
                            <span style="display:inline-block;background:#fef2f2;color:#b91c1c;font-size:10px;font-weight:700;padding:3px 10px;border-radius:20px;border:1px solid #fecaca;">
                                <i class="fas fa-triangle-exclamation" style="font-size:9px;"></i> Wilayah belum diisi
                            </span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Document Previews -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:14px;">
                    <div>
                        <p style="font-size:10px;color:#9ca3af;font-weight:700;text-transform:uppercase;margin-bottom:4px;">Foto KTP</p>
                        @if($user->ktp_image)
                            <a href="{{ asset($user->ktp_image) }}" target="_blank">
                                <img src="{{ asset($user->ktp_image) }}" style="width:100%;height:80px;object-fit:cover;border-radius:8px;border:1px solid #e5e7eb;" alt="KTP">
                            </a>
                        @else
                            <div style="width:100%;height:80px;background:#f3f4f6;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#9ca3af;font-size:11px;border:1px solid #e5e7eb;">Tidak ada foto</div>
                        @endif
                    </div>
                    <div>
                        <p style="font-size:10px;color:#9ca3af;font-weight:700;text-transform:uppercase;margin-bottom:4px;">Foto Selfie</p>
                        @if($user->selfie_image)
                            <a href="{{ asset($user->selfie_image) }}" target="_blank">
                                <img src="{{ asset($user->selfie_image) }}" style="width:100%;height:80px;object-fit:cover;border-radius:8px;border:1px solid #e5e7eb;" alt="Selfie">
                            </a>
                        @else
                            <div style="width:100%;height:80px;background:#f3f4f6;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#9ca3af;font-size:11px;border:1px solid #e5e7eb;">Tidak ada foto</div>
                        @endif
                    </div>
                </div>

                <div class="divider" style="margin:12px 0;"></div>

                <!-- Action Buttons -->
                <div style="display:flex;gap:10px;">
                    <form method="POST" action="{{ route('admin.drivers.approve', $user->id) }}" style="flex:1;">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary btn-full" style="background:#22c55e;box-shadow:none;">
                            <i class="fas fa-check"></i> Setujui
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.drivers.reject', $user->id) }}" style="flex:1;">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline btn-full" style="color:#ef4444;border-color:#fca5a5;background:#fff5f5;">
                            <i class="fas fa-times"></i> Tolak
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>

        @if(count($pendingDrivers) === 0)
        <div style="text-align:center;padding:40px 20px;">
            <i class="fas fa-id-card-clip" style="font-size:40px;color:#d1d5db;margin-bottom:12px;"></i>
            <p style="font-size:14px;color:#6b7280;">Tidak ada pengajuan driver pending.</p>
        </div>
        @endif
    </div>
</div>

{{-- ======================================================
     MODAL: Konfirmasi Peringatan
     ====================================================== --}}
<div id="modal-warn" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.45);align-items:flex-end;justify-content:center;">
    <div style="background:#fff;border-radius:20px 20px 0 0;padding:24px 20px 32px;width:100%;max-width:480px;animation:slideUp .25s ease;">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
            <div style="width:44px;height:44px;border-radius:12px;background:#fffbeb;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-triangle-exclamation" style="color:#d97706;font-size:20px;"></i>
            </div>
            <div>
                <p style="font-size:15px;font-weight:800;color:#111827;margin:0;">Kirim Peringatan</p>
                <p id="warn-modal-subtitle" style="font-size:12px;color:#6b7280;margin:2px 0 0;">ke driver</p>
            </div>
        </div>
        <p style="font-size:13px;color:#4b5563;margin-bottom:20px;line-height:1.5;">
            Peringatan akan tercatat di akun driver dan driver akan mendapat notifikasi status peringatan saat login.
        </p>
        <div style="display:flex;gap:10px;">
            <button onclick="closeWarnModal()" style="flex:1;padding:12px;font-size:13px;font-weight:700;background:#f3f4f6;color:#374151;border:none;border-radius:12px;cursor:pointer;">
                Batal
            </button>
            <form id="warn-form" method="POST" style="flex:1;">
                @csrf
                <button type="submit" style="width:100%;padding:12px;font-size:13px;font-weight:700;background:#fffbeb;color:#92400e;border:1px solid #fde68a;border-radius:12px;cursor:pointer;">
                    <i class="fas fa-triangle-exclamation"></i> Ya, Peringatkan
                </button>
            </form>
        </div>
    </div>
</div>

{{-- ======================================================
     MODAL: Suspend Driver
     ====================================================== --}}
<div id="modal-suspend" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.45);align-items:flex-end;justify-content:center;">
    <div style="background:#fff;border-radius:20px 20px 0 0;padding:24px 20px 32px;width:100%;max-width:480px;animation:slideUp .25s ease;">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
            <div style="width:44px;height:44px;border-radius:12px;background:#fef2f2;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-ban" style="color:#dc2626;font-size:20px;"></i>
            </div>
            <div>
                <p style="font-size:15px;font-weight:800;color:#111827;margin:0;">Suspend Driver</p>
                <p id="suspend-modal-subtitle" style="font-size:12px;color:#6b7280;margin:2px 0 0;">driver</p>
            </div>
        </div>
        <p style="font-size:13px;color:#4b5563;margin-bottom:12px;line-height:1.5;">
            Akun driver akan dinonaktifkan dan tidak dapat menerima order baru. Masukkan alasan suspend:
        </p>
        <form id="suspend-form" method="POST" style="display:flex;flex-direction:column;gap:12px;">
            @csrf
            <textarea name="reason" id="suspend-reason-input"
                style="width:100%;padding:10px 12px;border:1.5px solid #e5e7eb;border-radius:10px;font-size:13px;color:#111827;resize:none;font-family:inherit;box-sizing:border-box;"
                rows="3" placeholder="Contoh: Rating buruk, sering offline, dll.">Rating buruk / Tidak aktif</textarea>
            <div style="display:flex;gap:10px;">
                <button type="button" onclick="closeSuspendModal()" style="flex:1;padding:12px;font-size:13px;font-weight:700;background:#f3f4f6;color:#374151;border:none;border-radius:12px;cursor:pointer;">
                    Batal
                </button>
                <button type="submit" style="flex:1;padding:12px;font-size:13px;font-weight:700;background:#fef2f2;color:#991b1b;border:1px solid #fecaca;border-radius:12px;cursor:pointer;">
                    <i class="fas fa-ban"></i> Ya, Suspend
                </button>
            </div>
        </form>
    </div>
</div>

<style>
@keyframes slideUp {
    from { transform: translateY(100%); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}
</style>

<script>
function switchTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
    document.getElementById(tabId).style.display = 'block';
    
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.style.background = 'transparent';
        btn.style.color = '#4b5563';
    });
    
    const activeBtn = document.getElementById('btn-' + tabId);
    activeBtn.style.background = '#fff';
    activeBtn.style.color = '#111827';
}

/* ---- Warn Modal ---- */
function openWarnModal(driverId, driverName) {
    document.getElementById('warn-modal-subtitle').textContent = 'ke driver ' + driverName;
    document.getElementById('warn-form').action = '/admin/drivers/' + driverId + '/warn';
    const modal = document.getElementById('modal-warn');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closeWarnModal() {
    document.getElementById('modal-warn').style.display = 'none';
    document.body.style.overflow = '';
}

/* ---- Suspend Modal ---- */
function openSuspendModal(driverId, driverName) {
    document.getElementById('suspend-modal-subtitle').textContent = driverName;
    document.getElementById('suspend-form').action = '/admin/drivers/' + driverId + '/suspend';
    document.getElementById('suspend-reason-input').value = 'Rating buruk / Tidak aktif';
    const modal = document.getElementById('modal-suspend');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closeSuspendModal() {
    document.getElementById('modal-suspend').style.display = 'none';
    document.body.style.overflow = '';
}

/* Close modal when clicking backdrop */
document.getElementById('modal-warn').addEventListener('click', function(e) {
    if (e.target === this) closeWarnModal();
});
document.getElementById('modal-suspend').addEventListener('click', function(e) {
    if (e.target === this) closeSuspendModal();
});
</script>
@endsection
