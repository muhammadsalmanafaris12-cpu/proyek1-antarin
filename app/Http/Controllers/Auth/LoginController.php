<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            $user = Auth::user();
            if (!$user->isAdmin()) {
                $driver = $user->driver;
                $isSuspended = $driver && !is_null($driver->suspend_reason);
                // Driver tersuspend: tetap bisa akses (diarahkan ke dashboard)
                if (!$user->isApproved() && !$isSuspended) {
                    Auth::logout();
                    request()->session()->invalidate();
                    request()->session()->regenerateToken();
                    return view('auth.login')->withErrors(['email' => 'Akses ditolak. Akun Anda bermasalah atau belum disetujui.']);
                }
            }
            return redirect()->route($user->isAdmin() ? 'admin.dashboard' : 'driver.dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 6 karakter.',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            if (!$user->isAdmin()) {
                $driver = $user->driver;

                if ($user->isPending()) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return back()->withErrors([
                        'email' => 'Akun sedang menunggu persetujuan admin.',
                    ])->withInput($request->only('email'));
                }

                // Driver yang status-nya rejected tapi BUKAN karena suspend (tidak punya suspend_reason)
                // → ini kasus ditolak saat pendaftaran, bukan suspend
                if ($user->isRejected() && (!$driver || is_null($driver->suspend_reason))) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return back()->withErrors([
                        'email' => 'Pendaftaran Anda ditolak oleh admin.',
                    ])->withInput($request->only('email'));
                }

                // Driver tersuspend (rejected + punya suspend_reason): izinkan masuk
            }

            $request->session()->regenerate();

            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('driver.dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password tidak sesuai.',
        ])->withInput($request->only('email'));
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $allowedAreas = [
            'Sukasari','Sukajadi','Cidadap','Coblong',
        ];

        $request->validate([
            'name'             => 'required|string|max:100',
            'email'            => 'required|email|unique:users,email',
            'phone'            => 'required|string|max:20',
            'address'          => 'required|string',
            'vehicle_type'     => 'required|string|in:Motor,Mobil,Sepeda',
            'vehicle_plate'    => 'required|string|max:20',
            'operational_area' => 'required|string|in:' . implode(',', $allowedAreas),
            'password'         => 'required|min:6|confirmed',
            'ktp_image'        => 'required|image|max:2048',
            'selfie_image'     => 'required|image|max:2048',
        ], [
            'name.required'             => 'Nama wajib diisi.',
            'email.required'            => 'Email wajib diisi.',
            'email.unique'              => 'Email sudah terdaftar.',
            'phone.required'            => 'Nomor HP wajib diisi.',
            'address.required'          => 'Alamat wajib diisi.',
            'vehicle_type.required'     => 'Jenis kendaraan wajib dipilih.',
            'vehicle_plate.required'    => 'Plat nomor wajib diisi.',
            'operational_area.required' => 'Wilayah operasional wajib dipilih.',
            'operational_area.in'       => 'Wilayah operasional tidak tersedia di area kami.',
            'password.required'         => 'Password wajib diisi.',
            'password.confirmed'        => 'Konfirmasi password tidak cocok.',
            'password.min'              => 'Password minimal 6 karakter.',
            'ktp_image.required'        => 'Foto KTP wajib diunggah.',
            'ktp_image.image'           => 'Berkas KTP harus berupa gambar.',
            'ktp_image.max'             => 'Ukuran foto KTP maksimal 2MB.',
            'selfie_image.required'     => 'Foto selfie wajib diunggah.',
            'selfie_image.image'        => 'Berkas selfie harus berupa gambar.',
            'selfie_image.max'          => 'Ukuran foto selfie maksimal 2MB.',
        ]);

        // Upload files
        $ktpPath = null;
        if ($request->hasFile('ktp_image')) {
            $file = $request->file('ktp_image');
            $filename = time() . '_ktp_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/ktp'), $filename);
            $ktpPath = 'uploads/ktp/' . $filename;
        }

        $selfiePath = null;
        if ($request->hasFile('selfie_image')) {
            $file = $request->file('selfie_image');
            $filename = time() . '_selfie_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/selfie'), $filename);
            $selfiePath = 'uploads/selfie/' . $filename;
        }

        $user = User::create([
            'name'         => $request->name,
            'email'        => $request->email,
            'password'     => Hash::make($request->password),
            'role'         => 'driver',
            'status'       => 'pending',
            'phone'        => $request->phone,
            'address'      => $request->address,
            'ktp_image'    => $ktpPath,
            'selfie_image' => $selfiePath,
        ]);

        // Seed driver location randomly near ULBI campus center
        $lat = \App\Services\CampusAreaService::CENTER_LAT + (mt_rand(-1000, 1000) / 1000000);
        $lng = \App\Services\CampusAreaService::CENTER_LNG + (mt_rand(-1000, 1000) / 1000000);

        Driver::create([
            'user_id'          => $user->id,
            'phone'            => $request->phone,
            'vehicle_type'     => $request->vehicle_type,
            'vehicle_plate'    => $request->vehicle_plate,
            'operational_area' => $request->operational_area,
            'photo'            => $selfiePath, // Selfie automatically becomes profile picture
            'modal_saldo'      => 0,
            'is_online'        => false,
            'is_verified'      => false,
            'latitude'         => $lat,
            'longitude'        => $lng,
            'last_reset_date'  => now()->toDateString(),
        ]);

        return redirect()->route('login')->with('success', 'Pendaftaran berhasil! Akun Anda sedang menunggu persetujuan admin.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
