<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (Auth::user()->role !== $role) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk halaman ini.');
        }

        if ($role === 'driver' && !Auth::user()->isApproved()) {
            $driver = Auth::user()->driver;
            // Driver yang tersuspend (bukan sekadar pending) masih bisa login untuk
            // melihat status dan info pemulihan akun mereka.
            $isSuspended = $driver && !is_null($driver->suspend_reason);
            if (!$isSuspended) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')->withErrors(['email' => 'Akses ditolak. Akun Anda belum disetujui atau tidak aktif.']);
            }
            // Driver tersuspend: lanjutkan akses (dashboard menampilkan banner suspend)
        }

        return $next($request);
    }
}
