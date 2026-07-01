<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\FictitiousOrderDetector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function show(Order $order)
    {
        $driver = Auth::user()->driver;
        if (!$driver->is_online) {
            return redirect()->route('driver.dashboard')->with('error', 'Aktifkan status online Anda terlebih dahulu untuk melihat pesanan.');
        }

        $order->load(['customer', 'restaurant', 'suspicion', 'items']);

        // Run detection each time detail is viewed
        $detector = new FictitiousOrderDetector();
        $detector->analyze($order);
        $order->refresh();

        return view('driver.order-detail', compact('order', 'driver'));
    }

    public function take(Order $order)
    {
        $driver = Auth::user()->driver;

        if (!$driver->is_online) {
            return redirect()->route('driver.dashboard')->with('error', 'Aktifkan status online Anda terlebih dahulu untuk mengambil pesanan.');
        }

        // Guard: only available orders
        if ($order->status !== 'available') {
            return back()->with('error', 'Pesanan ini sudah tidak tersedia.');
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($order, $driver) {
                $lockedDriver = \App\Models\Driver::where('id', $driver->id)->lockForUpdate()->first();
                $lockedOrder = \App\Models\Order::where('id', $order->id)->lockForUpdate()->first();

                if ($lockedOrder->status !== 'available') {
                    throw new \Exception('Pesanan ini sudah tidak tersedia.');
                }

                if ($lockedDriver->modal_saldo < $lockedOrder->total_amount) {
                    throw new \Exception('Modal Anda tidak mencukupi untuk mengambil pesanan ini.');
                }

                // Check active order on the locked driver
                $hasActiveOrder = \App\Models\Order::where('driver_id', $lockedDriver->id)
                    ->whereIn('status', ['taken', 'processing'])
                    ->exists();

                if ($hasActiveOrder) {
                    throw new \Exception('Anda masih memiliki pesanan aktif. Selesaikan terlebih dahulu.');
                }

                $lockedOrder->update([
                    'status'    => 'taken',
                    'driver_id' => $lockedDriver->id,
                    'taken_at'  => now(),
                ]);

                $lockedDriver->decrement('modal_saldo', $lockedOrder->total_amount);
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('driver.active-order')
            ->with('success', 'Pesanan #' . $order->order_code . ' berhasil diambil!');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $driver = Auth::user()->driver;

        if ($order->driver_id !== $driver->id) {
            abort(403, 'Unauthorized');
        }

        $newStatus = $request->input('status');

        if ($order->status === 'taken') {
            if ($newStatus !== 'processing') {
                return back()->with('error', 'Pesanan harus diubah ke status diproses terlebih dahulu.');
            }
        } elseif ($order->status === 'processing') {
            if ($newStatus !== 'delivered') {
                return back()->with('error', 'Pesanan yang sedang diproses hanya bisa diubah ke selesai.');
            }
        } else {
            return back()->with('error', 'Status pesanan tidak valid untuk diperbarui.');
        }

        $updateData = ['status' => $newStatus];

        if ($newStatus === 'delivered') {
            $updateData['delivered_at'] = now();

            // Refund modal + add delivery fee to both daily and total earnings
            $driver->increment('modal_saldo', $order->total_amount);
            $driver->increment('total_earnings', $order->delivery_fee);
            $driver->increment('daily_earnings', $order->delivery_fee);
            $driver->increment('total_orders');

            // Hitung ulang rating rata-rata dari semua order delivered milik driver ini
            // Rating baru dihitung setelah minimal 5 order selesai
            $driver->refresh(); // refresh agar total_orders sudah terupdate
            if ($driver->total_orders >= 5) {
                // Rata-rata sederhana: setiap order delivered diasumsikan customer puas (4.0-5.0)
                // Rating akan diupdate manual dari fitur rating customer nanti
                // Untuk sementara: kalau rating masih null, set 4.0 sebagai baseline pertama
                if (is_null($driver->rating)) {
                    $driver->update(['rating' => 4.0]);
                }
            }
        }

        $order->update($updateData);

        return redirect()->route('driver.active-order')
            ->with('success', 'Status pesanan berhasil diperbarui.');
    }

    public function ignore(Order $order)
    {
        // Just redirect back to dashboard, don't change order status
        return redirect()->route('driver.dashboard')->with('info', 'Pesanan diabaikan.');
    }

    public function activeOrder()
    {
        $driver      = Auth::user()->driver;
        $activeOrder = $driver->activeOrder();

        if ($activeOrder) {
            $activeOrder->load(['customer', 'restaurant', 'items']);
        }

        return view('driver.active-order', compact('driver', 'activeOrder'));
    }
}
