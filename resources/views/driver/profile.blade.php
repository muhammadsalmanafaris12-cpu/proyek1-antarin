@extends('layouts.app')
@section('title', 'Profil Driver')

@section('content')
@include('driver.partials.nav', ['current' => 'profile', 'driver' => $driver])

<div class="page-content fade-in">

    {{-- Profile card --}}
    <div class="card" style="margin-bottom:16px;overflow:visible;">
        <div style="height:80px;background:linear-gradient(135deg,#f97316,#ea580c);border-radius:16px 16px 0 0;"></div>
        <div style="padding:0 16px 20px;margin-top:-30px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:12px;">
                @if($driver->photo)
                <img src="{{ asset($driver->photo) }}" style="width:64px;height:64px;border-radius:50%;border:4px solid #fff;object-fit:cover;box-shadow:0 4px 6px rgba(0,0,0,0.1);">
                @else
                <div style="width:60px;height:60px;border-radius:50%;background:linear-gradient(135deg,#374151,#111827);border:4px solid #fff;display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:800;color:#fff;">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                @endif
                <span class="badge {{ $driver->is_verified ? 'badge-green' : 'badge-gray' }}">
                    <i class="fas {{ $driver->is_verified ? 'fa-shield-check' : 'fa-clock' }}"></i>
                    {{ $driver->is_verified ? 'Terverifikasi' : 'Menunggu Verifikasi' }}
                </span>
            </div>
            <h2 style="font-size:18px;font-weight:800;color:#111827;">{{ Auth::user()->name }}</h2>
            <p style="font-size:13px;color:#6b7280;margin-top:2px;">{{ Auth::user()->email }}</p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:12px;padding-top:12px;border-top:1px solid #f3f4f6;">
                <div style="display:flex;align-items:center;gap:10px;background:#f9fafb;padding:8px 12px;border-radius:10px;border:1px solid #f3f4f6;">
                    <i class="fas fa-motorcycle" style="color:#6b7280;font-size:16px;"></i>
                    <div>
                        <p style="font-size:13px;font-weight:800;color:#111827;">{{ $driver->total_orders }}</p>
                        <p style="font-size:9px;color:#6b7280;">Total Order</p>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:10px;background:#f9fafb;padding:8px 12px;border-radius:10px;border:1px solid #f3f4f6;">
                    <i class="fas fa-star" style="color:{{ $driver->hasRating() ? '#eab308' : '#d1d5db' }};font-size:16px;"></i>
                    <div>
                        @if($driver->hasRating())
                            <p style="font-size:13px;font-weight:800;color:#111827;">{{ number_format($driver->rating, 1) }}/5</p>
                            <p style="font-size:9px;color:#6b7280;">Rating Driver</p>
                        @else
                            <p style="font-size:13px;font-weight:800;color:#16a34a;">Baru ✨</p>
                            <p style="font-size:9px;color:#6b7280;">{{ $driver->total_orders }}/5 order selesai</p>
                        @endif
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:10px;background:#fdf2f8;padding:8px 12px;border-radius:10px;border:1px solid #fce7f3;">
                    <i class="fas fa-chart-line" style="color:#db2777;font-size:16px;"></i>
                    <div>
                        <p style="font-size:13px;font-weight:800;color:#db2777;">Rp {{ number_format($driver->daily_earnings,0,',','.') }}</p>
                        <p style="font-size:9px;color:#db2777;font-weight:600;">Hari Ini</p>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:10px;background:#ecfdf5;padding:8px 12px;border-radius:10px;border:1px solid #d1fae5;">
                    <i class="fas fa-wallet" style="color:#059669;font-size:16px;"></i>
                    <div>
                        <p style="font-size:13px;font-weight:800;color:#059669;">Rp {{ number_format($driver->total_earnings,0,',','.') }}</p>
                        <p style="font-size:9px;color:#059669;font-weight:600;">Saldo Withdraw</p>
                    </div>
                </div>
            </div>
            <div style="margin-top:16px;">
                <a href="{{ route('driver.withdraw') }}" class="btn btn-primary btn-full" style="background:linear-gradient(135deg,#059669,#10b981);border:none;box-shadow:0 4px 10px rgba(5,150,105,0.25);display:flex;align-items:center;justify-content:center;gap:8px;font-size:13px;font-weight:700;padding:12px 14px;border-radius:10px;text-decoration:none;color:#fff;">
                    <i class="fas fa-money-bill-transfer"></i> Tarik Pendapatan (Withdraw)
                </a>
            </div>
        </div>
    </div>

    {{-- Detail Registrasi Akun (Read-only) --}}
    <div class="card" style="margin-bottom:16px;">
        <div style="padding:16px;border-bottom:1px solid #f3f4f6;background:#f9fafb;">
            <p style="font-size:14px;font-weight:700;color:#111827;display:flex;align-items:center;gap:6px;">
                <i class="fas fa-id-card" style="color:#6b7280;"></i> Detail Registrasi Akun
            </p>
        </div>
        <div style="padding:16px;display:flex;flex-direction:column;gap:12px;">
            <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #f3f4f6;padding-bottom:8px;">
                <span style="font-size:12px;color:#6b7280;">Nama Lengkap</span>
                <span style="font-size:12px;font-weight:700;color:#111827;">{{ Auth::user()->name }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #f3f4f6;padding-bottom:8px;">
                <span style="font-size:12px;color:#6b7280;">Email</span>
                <span style="font-size:12px;font-weight:700;color:#111827;">{{ Auth::user()->email }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #f3f4f6;padding-bottom:8px;">
                <span style="font-size:12px;color:#6b7280;">Jenis Kendaraan</span>
                <span style="font-size:12px;font-weight:700;color:#111827;">
                    <i class="fas @if($driver->vehicle_type === 'Sepeda') fa-bicycle @elseif($driver->vehicle_type === 'Mobil') fa-car @else fa-motorcycle @endif" style="margin-right:4px;"></i> {{ $driver->vehicle_type }}
                </span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding-bottom:4px;">
                <span style="font-size:12px;color:#6b7280;">Plat Nomor</span>
                <span style="font-size:12px;font-weight:700;color:#111827;font-family:monospace;background:#f3f4f6;padding:2px 8px;border-radius:4px;">{{ $driver->vehicle_plate }}</span>
            </div>
        </div>
    </div>

    {{-- Edit profile form (Nomor HP) --}}
    <div class="card" style="margin-bottom:16px;">
        <div style="padding:16px;border-bottom:1px solid #f3f4f6;">
            <p style="font-size:14px;font-weight:700;color:#111827;display:flex;align-items:center;gap:6px;">
                <i class="fas fa-edit" style="color:#f97316;"></i> Ubah Nomor HP
            </p>
        </div>
        <form method="POST" action="{{ route('driver.profile.update') }}" style="padding:16px;">
            @csrf
            <div class="form-group" style="margin-bottom:16px;">
                <label class="form-label">Nomor HP (WhatsApp)</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $driver->phone) }}" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full">
                <i class="fas fa-save"></i> Simpan Perubahan
            </button>
        </form>
    </div>

    {{-- Change password --}}
    <div class="card" style="margin-bottom:16px;">
        <div style="padding:16px;border-bottom:1px solid #f3f4f6;">
            <p style="font-size:14px;font-weight:700;color:#111827;">Ubah Password</p>
        </div>
        <form method="POST" action="{{ route('driver.profile.password') }}" style="padding:16px;">
            @csrf
            @if($errors->has('current_password'))
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first('current_password') }}</div>
            @endif
            <div class="form-group">
                <label class="form-label">Password Lama</label>
                <input type="password" name="current_password" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Password Baru</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-outline btn-full">
                <i class="fas fa-lock"></i> Ubah Password
            </button>
        </form>
    </div>

    {{-- Logout --}}
    <form method="POST" action="{{ route('logout') }}" style="margin-bottom:16px;">
        @csrf
        <button type="submit" class="btn btn-full" style="background:#fee2e2;color:#b91c1c;padding:14px;border-radius:14px;font-size:14px;font-weight:700;border:none;cursor:pointer;">
            <i class="fas fa-arrow-right-from-bracket"></i> Keluar dari Akun
        </button>
    </form>

</div>
@endsection
