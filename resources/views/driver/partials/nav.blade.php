{{-- Reusable driver navigation component --}}
@php $current = $current ?? ''; @endphp

<!-- Sticky Header -->
<header class="sticky-header" style="padding:16px 16px 0;">
    <div style="display:flex;justify-content:space-between;align-items:center;padding-bottom:12px;">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="position:relative;">
                @if($driver->photo)
                <img src="{{ asset($driver->photo) }}" style="width:42px;height:42px;border-radius:12px;object-fit:cover;border:2px solid #ea580c;display:block;">
                @else
                <div style="width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,#f97316,#ea580c);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:18px;">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                @endif
                @if($driver->is_online)
                <div style="position:absolute;bottom:-2px;right:-2px;width:12px;height:12px;background:#22c55e;border-radius:50%;border:2px solid #fff;" class="pulse"></div>
                @endif
            </div>
            <div>
                <p style="font-size:13px;font-weight:700;color:#111827;">{{ Auth::user()->name }}</p>
                <p style="font-size:11px;color:{{ $driver->is_online ? '#16a34a' : '#6b7280' }};font-weight:600;">
                    <i class="fas fa-circle" style="font-size:7px;"></i>
                    {{ $driver->is_online ? 'Aktif Menerima Order' : 'Sedang Offline' }}
                </p>
            </div>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
            <form method="POST" action="{{ route('driver.toggle-online') }}">
                @csrf
                <button type="submit" style="background:{{ $driver->is_online ? '#dcfce7' : '#f3f4f6' }};color:{{ $driver->is_online ? '#15803d' : '#6b7280' }};border:none;border-radius:8px;padding:6px 10px;font-size:11px;font-weight:700;cursor:pointer;">
                    <i class="fas fa-power-off"></i>
                </button>
            </form>
            <div style="position:relative;">
                <div style="width:36px;height:36px;background:#f3f4f6;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#374151;">
                    <i class="fas fa-bell" style="font-size:15px;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Wallet -->
    <div class="wallet-card" style="margin-bottom:12px;">
        <p style="font-size:11px;opacity:.85;margin-bottom:4px;font-weight:500;">Modal Aktif Anda</p>
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <p style="font-size:24px;font-weight:800;">Rp {{ number_format($driver->modal_saldo,0,',','.') }}</p>
            <button onclick="document.getElementById('topup-modal').classList.add('open')"
                style="background:rgba(255,255,255,.2);color:#fff;border:1.5px solid rgba(255,255,255,.4);border-radius:10px;padding:8px 14px;font-size:12px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px;">
                <i class="fas fa-plus"></i> Top Up
            </button>
        </div>
        <div style="display:flex;gap:16px;margin-top:10px;font-size:11px;opacity:.85;">
            <span><i class="fas fa-motorcycle"></i> {{ $driver->total_orders }} order selesai</span>
            <span><i class="fas fa-star"></i> {{ $driver->hasRating() ? 'Rating '.number_format($driver->rating, 1).'/5' : 'Belum ada rating' }}</span>
        </div>
    </div>

    {{-- Banner Peringatan --}}
    @if(!is_null($driver->warned_at) && $driver->user->status !== 'rejected')
    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:12px;padding:10px 14px;margin-bottom:10px;display:flex;align-items:flex-start;gap:10px;">
        <i class="fas fa-triangle-exclamation" style="color:#d97706;font-size:16px;margin-top:2px;flex-shrink:0;"></i>
        <div>
            <p style="font-size:12px;font-weight:800;color:#92400e;margin:0 0 2px;">Akun Anda Mendapat Peringatan</p>
            <p style="font-size:11px;color:#78350f;margin:0;line-height:1.4;">
                Peringatan diberikan {{ $driver->warned_at->diffForHumans() }}. Tingkatkan performa Anda agar tidak terkena suspend.
            </p>
        </div>
    </div>
    @endif

    {{-- Banner Suspend --}}
    @if($driver->user->status === 'rejected' && !is_null($driver->suspend_reason))
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:10px 14px;margin-bottom:10px;display:flex;align-items:flex-start;gap:10px;">
        <i class="fas fa-ban" style="color:#dc2626;font-size:16px;margin-top:2px;flex-shrink:0;"></i>
        <div>
            <p style="font-size:12px;font-weight:800;color:#991b1b;margin:0 0 2px;">Akun Anda Disuspend</p>
            <p style="font-size:11px;color:#7f1d1d;margin:0 0 6px;line-height:1.4;">
                Alasan: <strong>{{ $driver->suspend_reason }}</strong>
            </p>
            <p style="font-size:11px;color:#7f1d1d;margin:0;line-height:1.4;">
                Akun Anda sedang dinonaktifkan oleh admin. Hubungi admin untuk mengajukan pemulihan akun.
            </p>
        </div>
    </div>
    @endif
</header>

<!-- Bottom Navigation -->
<nav class="bottom-nav">
    <a href="{{ route('driver.dashboard') }}" class="nav-item {{ $current==='dashboard' ? 'active' : '' }}">
        <i class="fas fa-list-ul"></i>
        <span>Tersedia</span>
    </a>
    <a href="{{ route('driver.active-order') }}" class="nav-item {{ $current==='active' ? 'active' : '' }}" style="position:relative;">
        @if($activeOrder = Auth::user()->driver->activeOrder())
        <div class="nav-badge"></div>
        @endif
        <i class="fas fa-motorcycle"></i>
        <span>Aktif</span>
    </a>
    <a href="{{ route('driver.history') }}" class="nav-item {{ $current==='history' ? 'active' : '' }}">
        <i class="fas fa-clock-rotate-left"></i>
        <span>Riwayat</span>
    </a>
    <a href="{{ route('driver.profile') }}" class="nav-item {{ $current==='profile' ? 'active' : '' }}">
        <i class="fas fa-user"></i>
        <span>Profil</span>
    </a>
</nav>

<!-- Top Up Modal -->
<div class="modal-overlay" id="topup-modal">
    <div class="modal-sheet">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h3 style="font-size:18px;font-weight:800;">Top Up Modal</h3>
            <button onclick="document.getElementById('topup-modal').classList.remove('open')"
                style="background:#f3f4f6;border:none;border-radius:8px;width:32px;height:32px;cursor:pointer;font-size:16px;">×</button>
        </div>
        <form method="POST" action="{{ route('driver.top-up') }}">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:16px;">
                @foreach([50000,100000,150000,200000,300000,500000] as $nominal)
                <button type="button" onclick="setTopup({{ $nominal }})"
                    style="padding:12px;border:1.5px solid #e5e7eb;border-radius:10px;font-size:13px;font-weight:700;background:#fff;cursor:pointer;color:#374151;">
                    Rp {{ number_format($nominal,0,',','.') }}
                </button>
                @endforeach
            </div>
            <div class="input-icon-wrap" style="margin-bottom:16px;">
                <i class="fas fa-money-bill icon"></i>
                <input type="number" name="amount" id="topup-amount" class="form-control" placeholder="Jumlah lainnya..." min="10000" max="5000000">
            </div>
            <button type="submit" class="btn btn-primary btn-full" style="padding:14px;">
                <i class="fas fa-plus-circle"></i> Konfirmasi Top Up
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
function setTopup(val) {
    document.getElementById('topup-amount').value = val;
    document.querySelectorAll('#topup-modal button[type=button]').forEach(b => b.style.borderColor = '#e5e7eb');
    event.target.style.borderColor = '#f97316';
}
</script>
@endpush
