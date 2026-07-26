<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Restaurant;
use App\Models\OrderItem;
use App\Services\FictitiousOrderDetector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;

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

        if ($driver->is_online) {
            $this->generateDummyOrders();
        }

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

    private function generateDummyOrders()
    {
        $customers = Customer::all();
        $restaurants = Restaurant::all();

        if ($customers->isEmpty() || $restaurants->isEmpty()) {
            return;
        }

        $foodMenu = [
            ['name' => 'Ayam Geprek Bensu', 'price' => 20000],
            ['name' => 'Burger Beef Special', 'price' => 25000],
            ['name' => 'Nasi Goreng Gila', 'price' => 22000],
            ['name' => 'Sate Taichan 10 Tusuk', 'price' => 25000],
            ['name' => 'Kopi Kenangan Mantan', 'price' => 18000],
            ['name' => 'Mie Ayam Jamur', 'price' => 17000],
            ['name' => 'Es Teh Manis Jumbo', 'price' => 5000],
            ['name' => 'Kentang Goreng L', 'price' => 15000],
            ['name' => 'Roti Bakar Coklat Keju', 'price' => 16000],
            ['name' => 'Juice Alpukat', 'price' => 12000],
        ];

        $detector = new FictitiousOrderDetector();
        $count = rand(5, 6);

        for ($i = 0; $i < $count; $i++) {
            $restaurant = $restaurants->random();

            $latOffset = (rand(-1500, 1500) / 1000000);
            $lngOffset = (rand(-1500, 1500) / 1000000);
            
            $deliveryLat = -6.8770 + $latOffset;
            $deliveryLng = 107.5870 + $lngOffset;

            // Randomly create a suspicious order scenario
            $isSuspiciousScenario = ($i === 0 && rand(1, 10) > 4); 
            
            if ($isSuspiciousScenario) {
                // Pick a customer with high cancel count if available to increase suspicion score
                $suspiciousCustomer = Customer::where('cancel_count', '>', 3)->inRandomOrder()->first();
                $customer = $suspiciousCustomer ?: $customers->random();
                $address = 'kosong'; // Triggers length < 10 (+30 points) and keyword check (+20 points)
            } else {
                $customer = $customers->random();
                $address = 'Jl. Sariasih No. ' . rand(1, 150) . ', RT 0' . rand(1,9) . '/RW 0' . rand(1,9) . ', Sarijadi, Bandung';
            }

            $itemCount = rand(1, 3);
            $selectedItems = array_values(Arr::random($foodMenu, $itemCount));

            $subtotal = 0;
            foreach ($selectedItems as &$item) {
                $item['qty'] = rand(1, 2);
                $subtotal += $item['price'] * $item['qty'];
            }

            $deliveryFee = rand(8000, 15000);

            $order = Order::create([
                'order_code'       => Order::generateCode(),
                'customer_id'      => $customer->id,
                'restaurant_id'    => $restaurant->id,
                'driver_id'        => null,
                'delivery_address' => $address,
                'delivery_lat'     => $deliveryLat,
                'delivery_lng'     => $deliveryLng,
                'subtotal'         => $subtotal,
                'delivery_fee'     => $deliveryFee,
                'total_amount'     => $subtotal + $deliveryFee,
                'status'           => 'available',
                'notes'            => null,
            ]);

            foreach ($selectedItems as $item) {
                OrderItem::create([
                    'order_id'  => $order->id,
                    'item_name' => $item['name'],
                    'quantity'  => $item['qty'],
                    'price'     => $item['price'],
                    'subtotal'  => $item['price'] * $item['qty'],
                ]);
            }

            $detector->analyze($order);
        }
    }
}
