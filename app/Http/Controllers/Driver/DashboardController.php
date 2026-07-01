<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\FictitiousOrderDetector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $driver = Auth::user()->driver;

        if (!$driver) {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect()->route('login')->withErrors(['email' => 'Profil driver tidak ditemukan.']);
        }

        if ($driver->user->status === 'rejected' && !is_null($driver->suspend_reason)) {
            if ($driver->is_online) {
                $driver->update(['is_online' => false]);
            }
            $availableOrders = collect();
            $filteredOrders  = collect();
        } elseif ($driver->is_online) {
            // Available orders filtered by driver modal saldo and ULBI area
            $availableOrders = Order::with(['customer','restaurant','suspicion','items'])
                ->where('status', 'available')
                ->where('total_amount', '<=', $driver->modal_saldo)
                ->orderByDesc('is_suspicious') // show suspicious ones visually (for demo)
                ->orderBy('created_at', 'desc')
                ->get()
                ->filter(function ($order) {
                    return \App\Services\CampusAreaService::isWithinOperationalArea($order->delivery_lat, $order->delivery_lng)
                        && \App\Services\CampusAreaService::isWithinOperationalArea($order->restaurant->latitude, $order->restaurant->longitude);
                });

            // Orders that exceeded driver modal (shown as greyed out) and filtered by ULBI area
            $filteredOrders = Order::with(['restaurant'])
                ->where('status', 'available')
                ->where('total_amount', '>', $driver->modal_saldo)
                ->orderBy('created_at', 'desc')
                ->get()
                ->filter(function ($order) {
                    return \App\Services\CampusAreaService::isWithinOperationalArea($order->delivery_lat, $order->delivery_lng)
                        && \App\Services\CampusAreaService::isWithinOperationalArea($order->restaurant->latitude, $order->restaurant->longitude);
                })
                ->take(3);
        } else {
            $availableOrders = collect();
            $filteredOrders = collect();
        }

        $activeOrder = $driver->activeOrder();

        return view('driver.dashboard', compact('driver','availableOrders','filteredOrders','activeOrder'));
    }

    public function topUp(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000|max:5000000',
        ], [
            'amount.required' => 'Jumlah top up wajib diisi.',
            'amount.min'      => 'Minimal top up Rp 10.000.',
            'amount.max'      => 'Maksimal top up Rp 5.000.000.',
        ]);

        $driver = Auth::user()->driver;
        $driver->increment('modal_saldo', $request->amount);

        return back()->with('success', 'Top up modal berhasil! Saldo bertambah Rp ' . number_format($request->amount, 0, ',', '.'));
    }

    public function toggleOnline()
    {
        $driver = Auth::user()->driver;
        
        if ($driver->user->status === 'rejected' && !is_null($driver->suspend_reason)) {
            return back()->with('error', 'Akun Anda sedang ditangguhkan (suspend). Anda tidak dapat mengaktifkan status online.');
        }

        $driver->update(['is_online' => !$driver->is_online]);

        $status = $driver->is_online ? 'Aktif Menerima Order' : 'Tidak Aktif';
        return back()->with('success', "Status berubah menjadi: $status");
    }

    public function appeal(Request $request)
    {
        $request->validate([
            'appeal_reason' => 'required|string|min:20|max:1000',
        ], [
            'appeal_reason.required' => 'Alasan banding wajib diisi.',
            'appeal_reason.min'      => 'Berikan alasan banding yang jelas (minimal 20 karakter).',
            'appeal_reason.max'      => 'Alasan banding maksimal 1000 karakter.',
        ]);

        $driver = Auth::user()->driver;

        if ($driver->user->status !== 'rejected' || is_null($driver->suspend_reason)) {
            return back()->with('error', 'Akun Anda tidak dalam status suspend.');
        }

        $driver->update([
            'appeal_reason' => $request->appeal_reason,
            'appeal_at'     => now(),
        ]);

        return back()->with('success', 'Pengajuan banding Anda telah dikirim dan sedang menunggu konfirmasi dari Admin.');
    }
}
