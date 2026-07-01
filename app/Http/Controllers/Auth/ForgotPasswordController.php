<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /**
     * Tampilkan form verifikasi email (Langkah 1)
     */
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Validasi email → langsung redirect ke form ganti password (tanpa kirim email)
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
        ]);

        $user = User::where('email', $request->email)->first();

        // Validasi: email tidak terdaftar
        if (!$user) {
            return back()->withErrors(['email' => 'Email tidak terdaftar dalam sistem.'])->withInput();
        }

        // Validasi: driver pending/rejected tidak bisa reset
        if ($user->role === 'driver' && !$user->isApproved()) {
            if ($user->isPending()) {
                return back()->withErrors(['email' => 'Akun Anda sedang menunggu persetujuan oleh admin, sehingga tidak dapat melakukan reset password.'])->withInput();
            } else {
                return back()->withErrors(['email' => 'Akun Anda telah ditolak oleh admin, sehingga tidak dapat melakukan reset password.'])->withInput();
            }
        }

        // Buat token reset & simpan ke DB
        $token = Str::random(60);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token'      => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // Langsung redirect ke halaman ganti password (tanpa kirim email)
        return redirect()->route('password.reset', [
            'token' => $token,
            'email' => $request->email,
        ])->with('success', 'Email berhasil diverifikasi.');
    }

    /**
     * Tampilkan form ganti password baru (Langkah 2)
     */
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.reset-password', [
            'token' => $token ?? $request->query('token'),
            'email' => $request->query('email'),
        ]);
    }

    /**
     * Proses simpan password baru (Langkah 3)
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token'                 => 'required',
            'email'                 => 'required|email',
            'password'              => 'required|min:6|confirmed',
        ], [
            'email.required'              => 'Email wajib diisi.',
            'email.email'                 => 'Format email tidak valid.',
            'password.required'           => 'Password baru wajib diisi.',
            'password.min'                => 'Password baru minimal 6 karakter.',
            'password.confirmed'          => 'Konfirmasi password tidak cocok.',
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        // Validasi token
        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors(['email' => 'Token reset password tidak valid atau telah kedaluwarsa.']);
        }

        // Cek kedaluwarsa token (60 menit)
        if (now()->subMinutes(60)->gt($record->created_at)) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => 'Token reset password tidak valid atau telah kedaluwarsa.']);
        }

        // Update password
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        // Hapus token setelah berhasil
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')
            ->with('success', 'Password berhasil diubah! Silakan login kembali dengan password baru Anda.');
    }
}
