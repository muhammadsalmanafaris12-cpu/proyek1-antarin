@extends('layouts.app')
@section('title', 'Masuk')

@section('content')
<div style="min-height:100vh; display:flex; flex-direction:column; justify-content:center; padding:32px 24px; background:linear-gradient(160deg,#fff7ed 0%,#ffffff 60%);">

    <!-- Logo -->
    <div style="text-align:center; margin-bottom:36px;">
        <div style="display:inline-block;margin-bottom:16px;">
            @include('partials.logo', ['size' => 80, 'id_suffix' => 'login'])
        </div>
        <h1 style="font-size:28px;font-weight:900;color:#111827;letter-spacing:-0.5px;">AntarIn</h1>
        <p style="color:#6b7280;font-size:13px;margin-top:4px;font-weight:500;">Cepat · Aman · Terpercaya</p>
    </div>

    <!-- Alerts -->
    @if($errors->any())
    <div class="alert alert-error fade-in" style="margin-bottom:20px;">
        <i class="fas fa-circle-exclamation"></i>
        <div>{{ $errors->first() }}</div>
    </div>
    @endif

    <!-- Form -->
    <form method="POST" action="{{ route('login.post') }}" style="display:flex;flex-direction:column;gap:16px;">
        @csrf
        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Email</label>
            <div class="input-icon-wrap">
                <i class="fas fa-envelope icon"></i>
                <input type="email" name="email" id="email"
                    class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                    placeholder="Masukkan email Anda"
                    value="{{ old('email') }}"
                    required autofocus>
            </div>
        </div>

        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Password</label>
            <div class="input-icon-wrap" style="position:relative;">
                <i class="fas fa-lock icon"></i>
                <input type="password" name="password" id="login-password"
                    class="form-control"
                    placeholder="Masukkan password Anda"
                    style="padding-right:44px;"
                    required>
                <button type="button" id="toggle-login-password"
                    onclick="togglePassword('login-password','toggle-login-password')"
                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9ca3af;padding:4px;display:flex;align-items:center;justify-content:center;transition:color .2s;">
                    <i class="fas fa-eye" style="font-size:15px;"></i>
                </button>
            </div>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:4px;">
            <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#374151;cursor:pointer;">
                <input type="checkbox" name="remember" style="accent-color:#f97316;width:15px;height:15px;">
                Ingat saya
            </label>
            <a href="{{ route('password.request') }}" style="font-size:13px;color:#f97316;font-weight:600;text-decoration:none;">Lupa password?</a>
        </div>

        <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px;padding:14px;font-size:15px;border-radius:14px;">
            <i class="fas fa-arrow-right-to-bracket"></i> Masuk
        </button>
    </form>

    <p style="text-align:center;font-size:13px;color:#6b7280;margin-top:28px;">
        Belum punya akun?
        <a href="{{ route('register') }}" style="color:#f97316;font-weight:700;text-decoration:none;">Daftar jadi Driver</a>
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
