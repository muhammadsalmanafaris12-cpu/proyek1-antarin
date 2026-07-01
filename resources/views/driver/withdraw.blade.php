@extends('layouts.app')
@section('title', 'Tarik Pendapatan')

@section('content')
@include('driver.partials.nav', ['current' => 'profile', 'driver' => $driver])

<div class="page-content fade-in">

    {{-- Back link --}}
    <a href="{{ route('driver.profile') }}" style="display:inline-flex;align-items:center;gap:6px;color:#f97316;font-size:13px;font-weight:700;text-decoration:none;margin-bottom:16px;">
        <i class="fas fa-arrow-left"></i> Kembali ke Profil
    </a>

    {{-- Alert Success / Errors --}}
    @if(session('success'))
    <div class="alert alert-success" style="margin-bottom:16px;">
        <i class="fas fa-check-circle" style="font-size:16px;"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-error" style="margin-bottom:16px;">
        <i class="fas fa-exclamation-circle" style="font-size:16px;flex-shrink:0;"></i>
        <div>
            @foreach($errors->all() as $error)
            <span style="display:block;font-size:12px;">{{ $error }}</span>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Wallet Balance Card --}}
    <div class="card" style="background:linear-gradient(135deg,#f97316 0%,#ea580c 100%);color:#fff;padding:24px 20px;border-radius:18px;box-shadow:0 10px 20px rgba(249,115,22,0.2);margin-bottom:20px;border:none;">
        <p style="font-size:12px;opacity:.9;margin-bottom:4px;font-weight:500;text-transform:uppercase;letter-spacing:0.5px;">Saldo Dompet WD (Bisa Ditarik)</p>
        <h2 style="font-size:28px;font-weight:800;margin-bottom:6px;color:#fff;">Rp {{ number_format($driver->total_earnings,0,',','.') }}</h2>
        <div style="font-size:11px;opacity:.85;display:flex;align-items:center;gap:6px;">
            <i class="fas fa-info-circle"></i>
            <span>Batas penarikan minimum adalah Rp 10.000.</span>
        </div>
    </div>

    {{-- Withdrawal Form Card --}}
    <div class="card" style="margin-bottom:20px;border-radius:16px;">
        <div style="padding:16px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;gap:8px;">
            <i class="fas fa-money-bill-transfer" style="color:#f97316;"></i>
            <p style="font-size:14px;font-weight:700;color:#111827;">Form Pengajuan Penarikan</p>
        </div>
        <form method="POST" action="{{ route('driver.withdraw.store') }}" style="padding:16px;">
            @csrf
            
            <div class="form-group" style="margin-bottom:14px;">
                <label class="form-label" style="font-size:11px;font-weight:600;color:#4b5563;">Jumlah Penarikan (Rp)</label>
                <div class="input-icon-wrap" style="position:relative;">
                    <i class="fas fa-rupiah-sign icon" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:14px;"></i>
                    <input type="number" name="amount" class="form-control" placeholder="Contoh: 50000" min="10000" max="{{ (int)$driver->total_earnings }}" value="{{ old('amount') }}" required style="padding-left:36px;border-radius:10px;height:44px;font-size:14px;font-weight:700;color:#111827;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom:14px;">
                <label class="form-label" style="font-size:11px;font-weight:600;color:#4b5563;">Metode Penarikan</label>
                <select name="bank_name" class="form-control" required style="border-radius:10px;height:44px;font-size:13px;font-weight:600;color:#374151;">
                    <option value="" disabled selected>-- Pilih Bank / E-Wallet --</option>
                    <option value="BCA" {{ old('bank_name')==='BCA' ? 'selected' : '' }}>Bank Central Asia (BCA)</option>
                    <option value="Mandiri" {{ old('bank_name')==='Mandiri' ? 'selected' : '' }}>Bank Mandiri</option>
                    <option value="BRI" {{ old('bank_name')==='BRI' ? 'selected' : '' }}>Bank Rakyat Indonesia (BRI)</option>
                    <option value="BNI" {{ old('bank_name')==='BNI' ? 'selected' : '' }}>Bank Negara Indonesia (BNI)</option>
                    <option value="GoPay" {{ old('bank_name')==='GoPay' ? 'selected' : '' }}>GoPay</option>
                    <option value="OVO" {{ old('bank_name')==='OVO' ? 'selected' : '' }}>OVO</option>
                    <option value="Dana" {{ old('bank_name')==='Dana' ? 'selected' : '' }}>DANA</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom:14px;">
                <label class="form-label" style="font-size:11px;font-weight:600;color:#4b5563;">Nomor Rekening / No HP</label>
                <input type="text" name="account_number" class="form-control" placeholder="Contoh: 8830123456" value="{{ old('account_number') }}" required style="border-radius:10px;height:44px;font-size:13px;">
            </div>

            <div class="form-group" style="margin-bottom:18px;">
                <label class="form-label" style="font-size:11px;font-weight:600;color:#4b5563;">Nama Pemilik Rekening</label>
                <input type="text" name="account_name" class="form-control" placeholder="Sesuai nama di buku tabungan / e-wallet" value="{{ old('account_name') }}" required style="border-radius:10px;height:44px;font-size:13px;">
            </div>

            <button type="submit" class="btn btn-primary btn-full" style="padding:12px;border-radius:12px;font-size:14px;font-weight:700;background:linear-gradient(135deg,#f97316,#ea580c);border:none;box-shadow:0 4px 12px rgba(249,115,22,0.25);">
                <i class="fas fa-paper-plane" style="margin-right:6px;"></i> Kirim Pengajuan
            </button>
        </form>
    </div>

    {{-- Withdrawal History Card --}}
    <div class="card" style="border-radius:16px;">
        <div style="padding:16px;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center;">
            <div style="display:flex;align-items:center;gap:8px;">
                <i class="fas fa-clock-rotate-left" style="color:#6b7280;"></i>
                <p style="font-size:14px;font-weight:700;color:#111827;">Riwayat Penarikan</p>
            </div>
            <span class="badge badge-gray" style="font-size:10px;">{{ $withdrawals->count() }} Pengajuan</span>
        </div>

        <div style="padding:8px 16px 16px;">
            @if($withdrawals->isEmpty())
            <div style="text-align:center;padding:32px 0;">
                <div style="width:48px;height:48px;background:#f3f4f6;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px;color:#9ca3af;">
                    <i class="fas fa-receipt" style="font-size:20px;"></i>
                </div>
                <p style="font-size:12px;color:#6b7280;">Belum ada riwayat penarikan dana.</p>
            </div>
            @else
            <div style="display:flex;flex-direction:column;gap:12px;margin-top:8px;">
                @foreach($withdrawals as $wd)
                <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:12px;position:relative;">
                    
                    {{-- Status Badge --}}
                    <div style="position:absolute;top:12px;right:12px;">
                        @if($wd->status === 'pending')
                        <span class="badge badge-orange" style="font-size:10px;padding:4px 8px;border-radius:6px;font-weight:700;">
                            <i class="fas fa-spinner fa-spin" style="font-size:8px;margin-right:3px;"></i> Diproses
                        </span>
                        @elseif($wd->status === 'approved')
                        <span class="badge badge-green" style="font-size:10px;padding:4px 8px;border-radius:6px;font-weight:700;background:#dcfce7;color:#15803d;">
                            <i class="fas fa-check-circle" style="font-size:8px;margin-right:3px;"></i> Disetujui
                        </span>
                        @else
                        <span class="badge badge-gray" style="font-size:10px;padding:4px 8px;border-radius:6px;font-weight:700;background:#fee2e2;color:#b91c1c;">
                            <i class="fas fa-times-circle" style="font-size:8px;margin-right:3px;"></i> Ditolak
                        </span>
                        @endif
                    </div>

                    {{-- Content --}}
                    <p style="font-size:14px;font-weight:800;color:#111827;margin-bottom:4px;">Rp {{ number_format($wd->amount,0,',','.') }}</p>
                    <p style="font-size:11px;color:#4b5563;font-weight:600;margin-bottom:2px;">
                        <i class="fas fa-wallet" style="color:#6b7280;margin-right:4px;"></i> {{ $wd->bank_name }} - {{ $wd->account_number }}
                    </p>
                    <p style="font-size:10px;color:#9ca3af;margin-bottom:4px;">A/N: {{ $wd->account_name }} &bull; {{ $wd->created_at->format('d M Y, H:i') }}</p>

                    @if($wd->status === 'rejected' && $wd->admin_notes)
                    <div style="margin-top:8px;padding:8px 10px;background:#fef2f2;border-left:3px solid #ef4444;border-radius:4px;font-size:11px;color:#991b1b;line-height:1.4;">
                        <strong>Catatan Admin:</strong> {{ $wd->admin_notes }}
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
