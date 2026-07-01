@extends('layouts.app')
@section('title', 'Buat Password Baru')

@section('content')
<div style="min-height:100vh; display:flex; flex-direction:column; justify-content:center; padding:32px 24px; background:linear-gradient(160deg,#fff7ed 0%,#ffffff 60%);">

    <!-- Logo -->
    <div style="text-align:center; margin-bottom:32px;">
        <div style="display:inline-block;margin-bottom:14px;">
            @include('partials.logo', ['size' => 64, 'id_suffix' => 'reset'])
        </div>
        <h1 style="font-size:22px;font-weight:800;color:#111827;">Buat Password Baru</h1>
        <p style="color:#6b7280;font-size:13px;margin-top:4px;">Masukkan password baru untuk akun Anda</p>
    </div>

    <!-- Error -->
    @if($errors->any())
    <div class="alert alert-error fade-in" style="margin-bottom:20px;">
        <i class="fas fa-circle-exclamation"></i>
        <div>{{ $errors->first() }}</div>
    </div>
    @endif

    <!-- Step indicator -->
    <div style="display:flex;align-items:center;gap:0;margin-bottom:28px;">
        <div style="flex:1;text-align:center;">
            <div style="width:32px;height:32px;border-radius:50%;background:#22c55e;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;">
                <i class="fas fa-check" style="font-size:12px;"></i>
            </div>
            <p style="font-size:11px;color:#22c55e;font-weight:600;margin-top:4px;">Verifikasi Email</p>
        </div>
        <div style="flex:1;height:2px;background:#f97316;margin-bottom:20px;"></div>
        <div style="flex:1;text-align:center;">
            <div style="width:32px;height:32px;border-radius:50%;background:#f97316;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;">2</div>
            <p style="font-size:11px;color:#f97316;font-weight:600;margin-top:4px;">Password Baru</p>
        </div>
    </div>

    <!-- Email badge -->
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:12px 16px;display:flex;align-items:center;gap:10px;margin-bottom:20px;">
        <i class="fas fa-circle-check" style="color:#22c55e;"></i>
        <div>
            <p style="font-size:11px;color:#6b7280;margin:0;">Akun terverifikasi</p>
            <p style="font-size:13px;font-weight:700;color:#111827;margin:0;">{{ $email }}</p>
        </div>
    </div>

    <!-- Form -->
    <form method="POST" action="{{ route('password.update') }}" style="display:flex;flex-direction:column;gap:16px;">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        <!-- Password Baru -->
        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Password Baru</label>
            <div class="input-icon-wrap" style="position:relative;">
                <i class="fas fa-lock icon"></i>
                <input type="password" name="password" id="new-password"
                    class="form-control"
                    placeholder="Minimal 6 karakter"
                    style="padding-right:44px;"
                    required autofocus>
                <button type="button" id="toggle-new-password"
                    onclick="togglePassword('new-password','toggle-new-password')"
                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9ca3af;padding:4px;display:flex;align-items:center;transition:color .2s;">
                    <i class="fas fa-eye" style="font-size:15px;"></i>
                </button>
            </div>
        </div>

        <!-- Konfirmasi Password -->
        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Konfirmasi Password Baru</label>
            <div class="input-icon-wrap" style="position:relative;">
                <i class="fas fa-lock icon"></i>
                <input type="password" name="password_confirmation" id="confirm-password"
                    class="form-control"
                    placeholder="Ulangi password baru"
                    style="padding-right:44px;"
                    required>
                <button type="button" id="toggle-confirm-password"
                    onclick="togglePassword('confirm-password','toggle-confirm-password')"
                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9ca3af;padding:4px;display:flex;align-items:center;transition:color .2s;">
                    <i class="fas fa-eye" style="font-size:15px;"></i>
                </button>
            </div>
        </div>

        <!-- Password strength hint -->
        <div style="background:#f9fafb;border-radius:10px;padding:10px 14px;">
            <p style="font-size:12px;color:#6b7280;margin:0;">
                <i class="fas fa-shield-halved" style="color:#f97316;margin-right:6px;"></i>
                Tips: Gunakan kombinasi huruf, angka, dan simbol agar password lebih aman.
            </p>
        </div>

        <button type="submit" class="btn btn-primary btn-full" style="padding:14px;font-size:15px;border-radius:14px;margin-top:4px;">
            <i class="fas fa-floppy-disk"></i> Simpan Password Baru
        </button>
    </form>

    <p style="text-align:center;font-size:13px;color:#6b7280;margin-top:24px;">
        <a href="{{ route('password.request') }}" style="color:#f97316;font-weight:600;text-decoration:none;">
            <i class="fas fa-arrow-left" style="font-size:11px;"></i> Ulangi dari awal
        </a>
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
