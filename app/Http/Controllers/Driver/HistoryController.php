<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    public function index()
    {
        $driver = Auth::user()->driver;

        $orders = Order::with(['customer', 'restaurant', 'items'])
            ->where('driver_id', $driver->id)
            ->whereIn('status', ['delivered', 'cancelled'])
            ->orderByDesc('updated_at')
            ->paginate(15);

        $todayEarnings = Order::where('driver_id', $driver->id)
            ->where('status', 'delivered')
            ->whereDate('delivered_at', today())
            ->sum('delivery_fee');

        $todayCount = Order::where('driver_id', $driver->id)
            ->where('status', 'delivered')
            ->whereDate('delivered_at', today())
            ->count();

        $totalEarnings = $driver->total_earnings;
        $totalOrders   = $driver->total_orders;

        return view('driver.history', compact(
            'driver', 'orders',
            'todayEarnings', 'todayCount',
            'totalEarnings', 'totalOrders'
        ));
    }
}
