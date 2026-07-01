@extends('layouts.app')
@section('title', 'Daftar Driver')

@section('content')
<div style="min-height:100vh;padding:32px 24px;background:linear-gradient(160deg,#fff7ed 0%,#ffffff 60%);">

    <!-- Back + Title -->
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:28px;">
        <a href="{{ route('login') }}" style="width:38px;height:38px;border-radius:10px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;color:#374151;text-decoration:none;">
            <i class="fas fa-arrow-left"></i>
        </a>
        @include('partials.logo', ['size' => 38, 'id_suffix' => 'reg'])
        <div>
            <h1 style="font-size:20px;font-weight:800;color:#111827;">Daftar Driver</h1>
            <p style="font-size:12px;color:#6b7280;margin-top:2px;">Bergabung dan mulai kirim pesanan</p>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-error fade-in">
        <i class="fas fa-circle-exclamation"></i>
        <div>{{ $errors->first() }}</div>
    </div>
    @endif

     <form method="POST" action="{{ route('register.post') }}" enctype="multipart/form-data" style="display:flex;flex-direction:column;gap:14px;">
        @csrf

        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Nama Lengkap</label>
            <div class="input-icon-wrap">
                <i class="fas fa-user icon"></i>
                <input type="text" name="name" class="form-control" placeholder="Nama sesuai KTP" value="{{ old('name') }}" required>
            </div>
        </div>

        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Email</label>
            <div class="input-icon-wrap">
                <i class="fas fa-envelope icon"></i>
                <input type="email" name="email" class="form-control" placeholder="email@contoh.com" value="{{ old('email') }}" required>
            </div>
        </div>

        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Nomor HP (WhatsApp)</label>
            <div class="input-icon-wrap">
                <i class="fas fa-phone icon"></i>
                <input type="text" name="phone" class="form-control" placeholder="08xxxxxxxxxx" value="{{ old('phone') }}" required>
            </div>
        </div>

        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Alamat Lengkap</label>
            <div class="input-icon-wrap">
                <i class="fas fa-map-marker-alt icon" style="top:18px;"></i>
                <textarea name="address" class="form-control" placeholder="Alamat lengkap sesuai KTP" style="padding-left:40px; min-height:80px; resize:none;" required>{{ old('address') }}</textarea>
            </div>
        </div>

        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Foto KTP</label>
            <div class="input-icon-wrap">
                <i class="fas fa-id-card icon"></i>
                <input type="file" name="ktp_image" class="form-control" style="padding-left:40px; padding-top:10px;" accept="image/*" required>
            </div>
        </div>

        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Foto Selfie dengan KTP</label>
            <div class="input-icon-wrap">
                <i class="fas fa-camera icon"></i>
                <input type="file" name="selfie_image" class="form-control" style="padding-left:40px; padding-top:10px;" accept="image/*" required>
            </div>
        </div>

        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Jenis Kendaraan</label>
            <div class="input-icon-wrap">
                <i class="fas fa-motorcycle icon"></i>
                <input type="text" class="form-control" style="padding-left:40px; background:#f3f4f6; color:#6b7280; cursor:not-allowed; font-weight:700;" value="Motor" readonly>
                <input type="hidden" name="vehicle_type" value="Motor">
            </div>
        </div>

        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Plat Nomor Kendaraan</label>
            <div class="input-icon-wrap">
                <i class="fas fa-id-card-clip icon"></i>
                <input type="text" name="vehicle_plate" class="form-control" placeholder="Contoh: D 1234 ABC" value="{{ old('vehicle_plate') }}" required>
            </div>
        </div>

        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Wilayah Operasional</label>
            <div class="input-icon-wrap">
                <i class="fas fa-map-location-dot icon"></i>
                <select name="operational_area" class="form-control" style="padding-left:40px; height:44px;" required>
                    <option value="" disabled selected>-- Pilih Kecamatan --</option>
                    <optgroup label="Bandung Utara">
                        <option value="Sukasari"          {{ old('operational_area')==='Sukasari'          ? 'selected' : '' }}>Sukasari</option>
                        <option value="Sukajadi"          {{ old('operational_area')==='Sukajadi'          ? 'selected' : '' }}>Sukajadi</option>
                        <option value="Cidadap"           {{ old('operational_area')==='Cidadap'           ? 'selected' : '' }}>Cidadap</option>
                        <option value="Coblong"           {{ old('operational_area')==='Coblong'           ? 'selected' : '' }}>Coblong</option>
                    </optgroup>
                    <optgroup label="Bandung Barat">
                        <option value="Cicendo"           {{ old('operational_area')==='Cicendo'           ? 'selected' : '' }}>Cicendo</option>
                        <option value="Andir"             {{ old('operational_area')==='Andir'             ? 'selected' : '' }}>Andir</option>
                    </optgroup>
                    <optgroup label="Bandung Tengah">
                        <option value="Sumur Bandung"     {{ old('operational_area')==='Sumur Bandung'     ? 'selected' : '' }}>Sumur Bandung</option>
                        <option value="Bandung Wetan"     {{ old('operational_area')==='Bandung Wetan'     ? 'selected' : '' }}>Bandung Wetan</option>
                    </optgroup>
                </select>
            </div>
            <p style="font-size:11px;color:#6b7280;margin-top:5px;padding-left:4px;">
                <i class="fas fa-circle-info" style="color:#f97316;"></i>
                Hanya tersedia untuk area <strong>Kota Bandung</strong> sekitar kampus ULBI.
            </p>
        </div>

        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Password</label>
            <div class="input-icon-wrap" style="position:relative;">
                <i class="fas fa-lock icon"></i>
                <input type="password" name="password" id="reg-password" class="form-control" placeholder="Minimal 6 karakter" style="padding-right:44px;" required>
                <button type="button" id="toggle-reg-password"
                    onclick="togglePassword('reg-password','toggle-reg-password')"
                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9ca3af;padding:4px;display:flex;align-items:center;justify-content:center;transition:color .2s;">
                    <i class="fas fa-eye" style="font-size:15px;"></i>
                </button>
            </div>
        </div>

        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Konfirmasi Password</label>
            <div class="input-icon-wrap" style="position:relative;">
                <i class="fas fa-lock icon"></i>
                <input type="password" name="password_confirmation" id="reg-password-confirm" class="form-control" placeholder="Ulangi password" style="padding-right:44px;" required>
                <button type="button" id="toggle-reg-password-confirm"
                    onclick="togglePassword('reg-password-confirm','toggle-reg-password-confirm')"
                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9ca3af;padding:4px;display:flex;align-items:center;justify-content:center;transition:color .2s;">
                    <i class="fas fa-eye" style="font-size:15px;"></i>
                </button>
            </div>
        </div>

        <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:12px;padding:12px;margin-top:4px;">
            <p style="font-size:12px;color:#9a3412;">
                <i class="fas fa-info-circle"></i>
                Dengan mendaftar, Anda setuju dengan syarat &amp; ketentuan AntarIn. Modal awal Rp 0 — lakukan top up setelah login.
            </p>
        </div>

        <button type="submit" class="btn btn-primary btn-full" style="padding:14px;font-size:15px;border-radius:14px;margin-top:4px;">
            <i class="fas fa-user-plus"></i> Buat Akun Driver
        </button>
    </form>

    <p style="text-align:center;font-size:13px;color:#6b7280;margin-top:24px;">
        Sudah punya akun? <a href="{{ route('login') }}" style="color:#f97316;font-weight:700;text-decoration:none;">Masuk</a>
    </p>
</div>

<script>
function togglePassword(inputId, btnId) {
    const input = document.getElementById(inputId);
    const btn   = document.getElementById(btnId);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
        btn.style.color = '#f97316';
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
        btn.style.color = '#9ca3af';
    }
}
</script>
@endsection
