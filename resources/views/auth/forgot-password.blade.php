@extends('layouts.app')
@section('title', 'Lupa Password')

@section('content')
<div style="min-height:100vh; display:flex; flex-direction:column; justify-content:center; padding:32px 24px; background:linear-gradient(160deg,#fff7ed 0%,#ffffff 60%);">

    <!-- Logo -->
    <div style="text-align:center; margin-bottom:32px;">
        <div style="display:inline-block;margin-bottom:14px;">
            @include('partials.logo', ['size' => 64, 'id_suffix' => 'forgot'])
        </div>
        <h1 style="font-size:22px;font-weight:800;color:#111827;">Lupa Password?</h1>
        <p style="color:#6b7280;font-size:13px;margin-top:4px;">Masukkan email terdaftar untuk melanjutkan</p>
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
            <div style="width:32px;height:32px;border-radius:50%;background:#f97316;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;">1</div>
            <p style="font-size:11px;color:#f97316;font-weight:600;margin-top:4px;">Verifikasi Email</p>
        </div>
        <div style="flex:1;height:2px;background:#e5e7eb;margin-bottom:20px;"></div>
        <div style="flex:1;text-align:center;">
            <div style="width:32px;height:32px;border-radius:50%;background:#e5e7eb;color:#9ca3af;display:inline-flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;">2</div>
            <p style="font-size:11px;color:#9ca3af;margin-top:4px;">Password Baru</p>
        </div>
    </div>

    <!-- Form -->
    <form method="POST" action="{{ route('password.email') }}" style="display:flex;flex-direction:column;gap:16px;">
        @csrf
        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Email Terdaftar</label>
            <div class="input-icon-wrap">
                <i class="fas fa-envelope icon"></i>
                <input type="email" name="email" class="form-control"
                    placeholder="Masukkan email akun Anda"
                    value="{{ old('email') }}" required autofocus>
            </div>
        </div>

        <!-- Info box -->
        <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:12px;padding:12px;display:flex;gap:10px;align-items:flex-start;">
            <i class="fas fa-info-circle" style="color:#f97316;margin-top:2px;flex-shrink:0;"></i>
            <p style="font-size:12px;color:#9a3412;margin:0;line-height:1.5;">
                Setelah email diverifikasi, Anda akan langsung diarahkan ke halaman untuk membuat password baru.
            </p>
        </div>

        <button type="submit" class="btn btn-primary btn-full" style="padding:14px;font-size:15px;border-radius:14px;">
            <i class="fas fa-arrow-right"></i> Lanjutkan
        </button>
    </form>

    <p style="text-align:center;font-size:13px;color:#6b7280;margin-top:24px;">
        Ingat password? <a href="{{ route('login') }}" style="color:#f97316;font-weight:700;text-decoration:none;">Masuk</a>
    </p>
</div>
@endsection
