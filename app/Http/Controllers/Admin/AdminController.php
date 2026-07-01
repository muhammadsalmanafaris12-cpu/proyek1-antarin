<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Driver;
use App\Models\Customer;
use App\Models\Restaurant;
use App\Models\OrderSuspicion;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_orders'        => Order::count(),
            'available_orders'    => Order::where('status','available')->count(),
            'active_orders'       => Order::whereIn('status',['taken','processing'])->count(),
            'delivered_orders'    => Order::where('status','delivered')->count(),
            'suspicious_orders'   => Order::where('is_suspicious', true)->count(),
            'total_drivers'       => Driver::whereHas('user', function($q) {
                                         $q->where('status', 'approved')
                                           ->orWhere(function($sq) {
                                               $sq->where('status', 'rejected')->whereNotNull('drivers.suspend_reason');
                                           });
                                     })->count(),
            'online_drivers'      => Driver::where('is_online', true)->whereHas('user', fn($q) => $q->where('status', 'approved'))->count(),
            'total_customers'     => Customer::count(),
            'flagged_customers'   => Customer::where('is_flagged', true)->count(),
            'pending_drivers'     => User::where('role', 'driver')->where('status', 'pending')->count(),
            'pending_withdrawals' => Withdrawal::where('status', 'pending')->count(),
        ];

        $recentOrders      = Order::with(['customer','restaurant','driver.user','suspicion'])
            ->orderByDesc('created_at')->take(10)->get();

        $suspiciousOrders  = Order::with(['customer','restaurant','suspicion'])
            ->where('is_suspicious', true)
            ->orderByDesc('suspicion_score')->take(10)->get();

        $drivers = Driver::with('user')
            ->whereHas('user', function($q) {
                $q->where('status', 'approved')
                  ->orWhere(function($sq) {
                      $sq->where('status', 'rejected')->whereNotNull('drivers.suspend_reason');
                  });
            })
            ->orderByDesc('is_online')->get();

        return view('admin.dashboard', compact('stats','recentOrders','suspiciousOrders','drivers'));
    }

    public function orders(Request $request)
    {
        $query  = Order::with(['customer','restaurant','driver.user','suspicion']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('suspicious')) {
            $query->where('is_suspicious', true);
        }
        if ($request->filled('search')) {
            $query->where('order_code', 'like', '%' . $request->search . '%');
        }

        $orders = $query->orderByDesc('created_at')->paginate(20);
        return view('admin.orders', compact('orders'));
    }

    public function drivers()
    {
        // Tampilkan driver approved DAN driver yang disuspend (agar admin bisa reinstate)
        $drivers = Driver::with(['user', 'orders.restaurant'])
            ->whereHas('user', fn($q) => $q->whereIn('status', ['approved', 'rejected']))
            ->whereNotNull('drivers.user_id')
            // Exclude driver yang ditolak saat pendaftaran (bukan suspended) = tidak punya suspend_reason
            ->where(function ($q) {
                $q->whereHas('user', fn($u) => $u->where('status', 'approved'))
                  ->orWhereNotNull('suspend_reason');
            })
            ->orderByRaw("CASE WHEN suspend_reason IS NOT NULL THEN 1 ELSE 0 END") // suspended di bawah
            ->orderByDesc('created_at')
            ->paginate(20, ['*'], 'approved_page');

        $suspendedCount = Driver::whereNotNull('suspend_reason')->count();

        $pendingDrivers = User::where('role', 'driver')
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.drivers', compact('drivers', 'pendingDrivers', 'suspendedCount'));
    }

    public function approveDriver(User $user)
    {
        $user->update(['status' => 'approved']);
        
        if ($user->driver) {
            $user->driver->update(['is_verified' => true]);
        }
        
        return back()->with('success', 'Driver ' . $user->name . ' telah disetujui.');
    }

    public function rejectDriver(User $user)
    {
        $user->update(['status' => 'rejected']);
        
        if ($user->driver) {
            $user->driver->update(['is_verified' => false]);
        }
        
        return back()->with('success', 'Driver ' . $user->name . ' telah ditolak.');
    }

    /**
     * Kirim peringatan ke driver (rating buruk / sering offline)
     */
    public function warnDriver(Driver $driver)
    {
        $driver->update(['warned_at' => now()]);
        return back()->with('success', 'Peringatan berhasil dikirim ke driver ' . ($driver->user->name ?? '-') . '.');
    }

    /**
     * Cabut peringatan dari driver
     */
    public function unwarnDriver(Driver $driver)
    {
        $driver->update(['warned_at' => null]);
        return back()->with('success', 'Peringatan driver ' . ($driver->user->name ?? '-') . ' telah dicabut.');
    }

    /**
     * Suspend driver (nonaktifkan akun)
     */
    public function suspendDriver(Driver $driver)
    {
        $driver->update([
            'is_online'      => false,
            'suspend_reason' => request('reason', 'Pelanggaran performa'),
        ]);
        $driver->user->update(['status' => 'rejected']);
        return back()->with('success', 'Driver ' . ($driver->user->name ?? '-') . ' telah disuspend.');
    }

    /**
     * Pulihkan akun driver yang disuspend
     */
    public function reinstateDriver(Driver $driver)
    {
        $driver->update([
            'suspend_reason' => null,
            'warned_at'      => null,
            'appeal_reason'  => null,
            'appeal_at'      => null,
        ]);
        $driver->user->update(['status' => 'approved']);
        return back()->with('success', 'Driver ' . ($driver->user->name ?? '-') . ' telah dipulihkan.');
    }

    public function reviewSuspicion(Request $request, OrderSuspicion $suspicion)
    {
        $suspicion->update([
            'reviewed'    => true,
            'admin_notes' => $request->admin_notes,
        ]);
        return back()->with('success', 'Ulasan tersimpan.');
    }

    public function withdrawals()
    {
        $withdrawals = Withdrawal::with('driver.user')
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.withdrawals', compact('withdrawals'));
    }

    public function approveWithdrawal(Withdrawal $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Transaksi ini sudah diproses sebelumnya.');
        }

        $withdrawal->update(['status' => 'approved']);

        return back()->with('success', 'Penarikan dana sebesar Rp ' . number_format($withdrawal->amount, 0, ',', '.') . ' telah disetujui.');
    }

    public function rejectWithdrawal(Request $request, Withdrawal $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Transaksi ini sudah diproses sebelumnya.');
        }

        // Return the amount back to driver total_earnings
        $withdrawal->driver->increment('total_earnings', $withdrawal->amount);

        $withdrawal->update([
            'status'      => 'rejected',
            'admin_notes' => $request->admin_notes,
        ]);

        return back()->with('success', 'Penarikan dana sebesar Rp ' . number_format($withdrawal->amount, 0, ',', '.') . ' telah ditolak.');
    }
}
