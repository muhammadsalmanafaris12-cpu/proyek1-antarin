<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $driver = Auth::user()->driver;
        return view('driver.profile', compact('driver'));
    }

    public function update(Request $request)
    {
        $user   = Auth::user();
        $driver = $user->driver;

        $request->validate([
            'phone' => 'required|string|max:20',
        ], [
            'phone.required' => 'Nomor HP wajib diisi.',
            'phone.max'      => 'Nomor HP maksimal 20 karakter.',
        ]);

        $driver->update([
            'phone' => $request->phone,
        ]);

        $user->update([
            'phone' => $request->phone,
        ]);

        return back()->with('success', 'Nomor HP berhasil diperbarui.');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:6|confirmed',
        ], [
            'current_password.required' => 'Password lama wajib diisi.',
            'password.required'         => 'Password baru wajib diisi.',
            'password.confirmed'        => 'Konfirmasi password tidak cocok.',
        ]);

        $user = Auth::user();
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama tidak sesuai.']);
        }

        $user->update(['password' => Hash::make($request->password)]);
        return back()->with('success', 'Password berhasil diubah.');
    }

}
