<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Driver;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\FictitiousOrderDetector;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin ──────────────────────────────────────────
        User::create([
            'name'     => 'Administrator',
            'email'    => 'admin@antarin.id',
            'password' => Hash::make('password'),
            'role'     => 'admin',
            'status'   => 'approved',
        ]);

        // ── Drivers ────────────────────────────────────────
        $driverData = [
            // Budi: berpengalaman, rating bagus
            ['name'=>'Budi Santoso',   'email'=>'budi@driver.com',  'saldo'=>150000,  'online'=>true,  'area'=>'Sukasari', 'orders'=>28, 'rating'=>4.7],
            // Siti: sudah cukup order, rating standar
            ['name'=>'Siti Rahayu',    'email'=>'siti@driver.com',  'saldo'=>300000,  'online'=>true,  'area'=>'Coblong',  'orders'=>12, 'rating'=>3.9],
            // Ahmad: driver baru, belum punya rating
            ['name'=>'Ahmad Fauzi',    'email'=>'ahmad@driver.com', 'saldo'=>50000,   'online'=>false, 'area'=>'Cicendo',  'orders'=>2,  'rating'=>null],
        ];

        $drivers = [];
        foreach ($driverData as $d) {
            $phone = '08' . rand(10000000, 99999999);
            $user = User::create([
                'name'         => $d['name'],
                'email'        => $d['email'],
                'password'     => Hash::make('password'),
                'role'         => 'driver',
                'status'       => 'approved',
                'phone'        => $phone,
                'address'      => 'Jl. Sariasih No. ' . rand(1, 100) . ', Sarijadi, Bandung',
                'ktp_image'    => 'uploads/ktp/seed.png',
                'selfie_image' => 'uploads/selfie/seed.png',
            ]);
            $drivers[] = Driver::create([
                'user_id'          => $user->id,
                'phone'            => $phone,
                'vehicle_type'     => 'Motor',
                'vehicle_plate'    => 'D ' . rand(1000,9999) . ' ABC',
                'operational_area' => $d['area'],
                'modal_saldo'      => $d['saldo'],
                'is_online'        => $d['online'],
                'is_verified'      => true,
                'latitude'         => -6.8770 + (rand(-500, 500) / 1000000),
                'longitude'        => 107.5870 + (rand(-500, 500) / 1000000),
                'total_orders'     => $d['orders'],
                'total_earnings'   => $d['orders'] * rand(8000, 15000),
                'rating'           => $d['rating'],  // null = belum ada rating
                'last_reset_date'  => now()->toDateString(),
            ]);
        }

        // ── Restaurants ────────────────────────────────────
        $restaurants = [
            ['name'=>'Ayam Geprek Bensu Sarijadi', 'address'=>'Jl. Sariasih No. 12, Sarijadi, Bandung', 'lat'=>-6.8780, 'lng'=>107.5865, 'cat'=>'Ayam'],
            ['name'=>'Burger Bangor Sarijadi',     'address'=>'Jl. Sarijadi No. 8, Bandung',            'lat'=>-6.8755, 'lng'=>107.5890, 'cat'=>'Burger'],
            ['name'=>'Sate Taichan Sarijadi',      'address'=>'Jl. Lemahnendeut No. 5, Bandung',        'lat'=>-6.8795, 'lng'=>107.5850, 'cat'=>'Sate'],
            ['name'=>'Nasi Goreng Gila Sarijadi',  'address'=>'Jl. Gegerkalong Hilir No. 20, Bandung',  'lat'=>-6.8760, 'lng'=>107.5840, 'cat'=>'Nasi'],
            ['name'=>'Kopi Kenangan Setiabudi',    'address'=>'Jl. Setiabudi No. 34, Bandung',          'lat'=>-6.8810, 'lng'=>107.5900, 'cat'=>'Minuman'],
            ['name'=>'Mie Ayam Sarimanis',         'address'=>'Jl. Sarimanis No. 88, Bandung',          'lat'=>-6.8730, 'lng'=>107.5875, 'cat'=>'Mie'],
        ];

        $restoModels = [];
        foreach ($restaurants as $r) {
            $restoModels[] = Restaurant::create([
                'name'      => $r['name'],
                'address'   => $r['address'],
                'phone'     => '022' . rand(1000000, 9999999),
                'latitude'  => $r['lat'],
                'longitude' => $r['lng'],
                'category'  => $r['cat'],
                'is_active' => true,
            ]);
        }

        // ── Customers ──────────────────────────────────────
        $customerData = [
            // Normal customers (within ULBI area)
            ['name'=>'Anton Wijaya',   'addr'=>'Kos Permata, Jl. Sariasih No. 12, RT 01/RW 02, Sarijadi, Bandung', 'lat'=>-6.8775, 'lng'=>107.5880, 'cancel'=>0, 'order'=>15, 'age_days'=>365],
            ['name'=>'Dewi Kusuma',    'addr'=>'Kost Putri Sariasih No. 54, Jl. Sariasih, Bandung',                'lat'=>-6.8765, 'lng'=>107.5855, 'cancel'=>1, 'order'=>20, 'age_days'=>180],
            ['name'=>'Reza Pratama',   'addr'=>'Jl. Lemahnendeut No. 44, RT 02/RW 05, Sarijadi, Bandung',          'lat'=>-6.8800, 'lng'=>107.5860, 'cancel'=>0, 'order'=>8,  'age_days'=>90],
            // Suspicious customers
            ['name'=>'Unknown User',   'addr'=>'kosong',                    'lat'=>999,    'lng'=>999,     'cancel'=>5, 'order'=>2,  'age_days'=>1],
            ['name'=>'Test Customer',  'addr'=>'test',                      'lat'=>-6.8770,'lng'=>107.5870,'cancel'=>7, 'order'=>3,  'age_days'=>3],
            ['name'=>'Budi Palsu',     'addr'=>'Tidak jelas, tidak ada RT', 'lat'=>-6.205, 'lng'=>106.840, 'cancel'=>4, 'order'=>5,  'age_days'=>5],
        ];

        $customers = [];
        foreach ($customerData as $i => $c) {
            $customer = Customer::create([
                'name'         => $c['name'],
                'phone'        => '08' . rand(10000000, 99999999),
                'email'        => strtolower(str_replace(' ', '', $c['name'])) . '@mail.com',
                'address'      => $c['addr'],
                'latitude'     => $c['lat'],
                'longitude'    => $c['lng'],
                'cancel_count' => $c['cancel'],
                'order_count'  => $c['order'],
                'is_flagged'   => $c['cancel'] > 3,
                'created_at'   => now()->subDays($c['age_days']),
                'updated_at'   => now()->subDays($c['age_days']),
            ]);
            $customers[] = $customer;
        }

        // ── Orders (Available) ─────────────────────────────
        $orderDefs = [
            // Normal orders sesuai modal
            [
                'customer' => $customers[0], 'resto' => $restoModels[0],
                'addr'  => 'Kos Permata, Jl. Sariasih No. 12, RT 01/RW 02',
                'lat'   => -6.8775, 'lng' => 107.5880,
                'subtotal' => 45000, 'fee' => 12000,
                'items' => [['2x Paket Geprek Level 5', 2, 20000], ['Es Teh Manis', 1, 5000]],
            ],
            [
                'customer' => $customers[1], 'resto' => $restoModels[3],
                'addr'  => 'Kost Putri Sariasih No. 54, Jl. Sariasih',
                'lat'   => -6.8765, 'lng' => 107.5855,
                'subtotal' => 35000, 'fee' => 10000,
                'items' => [['Nasi Goreng Spesial', 1, 25000], ['Telur Ceplok', 2, 5000]],
            ],
            [
                'customer' => $customers[2], 'resto' => $restoModels[4],
                'addr'  => 'Jl. Lemahnendeut No. 44, Sarijadi',
                'lat'   => -6.8800, 'lng' => 107.5860,
                'subtotal' => 28000, 'fee' => 8000,
                'items' => [['Kopi Susu Original', 2, 12000], ['Croissant', 1, 4000]],
            ],
            // Suspicious orders
            [
                'customer' => $customers[3], 'resto' => $restoModels[1],
                'addr'  => 'Alamat tidak jelas / Kosong',
                'lat'   => 999, 'lng' => 999,
                'subtotal' => 120000, 'fee' => 15000,
                'items' => [['Burger Double Beef', 3, 35000], ['Kentang Goreng L', 2, 7500]],
            ],
            [
                'customer' => $customers[4], 'resto' => $restoModels[5],
                'addr'  => 'test',
                'lat'   => -6.8770, 'lng' => 107.5870,
                'subtotal' => 85000, 'fee' => 13000,
                'items' => [['Mie Ayam Komplit', 5, 15000], ['Es Jeruk', 2, 5000]],
            ],
            // Over modal (filtered out) - 200000
            [
                'customer' => $customers[1], 'resto' => $restoModels[2],
                'addr'  => 'Kost Putri Sariasih No. 54, Jl. Sariasih',
                'lat'   => -6.8730, 'lng' => 107.5875,
                'subtotal' => 200000, 'fee' => 18000,
                'items' => [['Sate Taichan 50 Tusuk', 1, 150000], ['Nasi Putih', 2, 10000], ['Es Kelapa Muda', 2, 15000]],
            ],
        ];

        $detector = new FictitiousOrderDetector();

        foreach ($orderDefs as $def) {
            $order = Order::create([
                'order_code'       => Order::generateCode(),
                'customer_id'      => $def['customer']->id,
                'restaurant_id'    => $def['resto']->id,
                'driver_id'        => null,
                'delivery_address' => $def['addr'],
                'delivery_lat'     => $def['lat'],
                'delivery_lng'     => $def['lng'],
                'subtotal'         => $def['subtotal'],
                'delivery_fee'     => $def['fee'],
                'total_amount'     => $def['subtotal'] + $def['fee'],
                'status'           => 'available',
                'notes'            => null,
            ]);

            foreach ($def['items'] as $item) {
                OrderItem::create([
                    'order_id'  => $order->id,
                    'item_name' => $item[0],
                    'quantity'  => $item[1],
                    'price'     => $item[2],
                    'subtotal'  => $item[1] * $item[2],
                ]);
            }

            // Run suspicion detection
            $detector->analyze($order);
        }

        // ── Completed Orders (History) ─────────────────────
        $historyDefs = [
            ['customer'=>$customers[0], 'resto'=>$restoModels[3], 'driver'=>$drivers[0],
             'addr'=>'Jl. Sariasih No. 12', 'sub'=>30000, 'fee'=>15000, 'status'=>'delivered',
             'item'=>['Nasi Goreng Gila Sarijadi',1,30000]],
            ['customer'=>$customers[1], 'resto'=>$restoModels[4], 'driver'=>$drivers[0],
             'addr'=>'Kost Putri Sariasih', 'sub'=>24000, 'fee'=>10000, 'status'=>'delivered',
             'item'=>['Kopi Susu Original',2,12000]],
            ['customer'=>$customers[3], 'resto'=>$restoModels[1], 'driver'=>$drivers[0],
             'addr'=>'kosong', 'sub'=>80000, 'fee'=>0, 'status'=>'cancelled',
             'item'=>['Burger Double Beef',2,40000]],
        ];

        foreach ($historyDefs as $def) {
            $order = Order::create([
                'order_code'       => Order::generateCode(),
                'customer_id'      => $def['customer']->id,
                'restaurant_id'    => $def['resto']->id,
                'driver_id'        => $def['driver']->id,
                'delivery_address' => $def['addr'],
                'delivery_lat'     => $def['customer']->latitude,
                'delivery_lng'     => $def['customer']->longitude,
                'subtotal'         => $def['sub'],
                'delivery_fee'     => $def['fee'],
                'total_amount'     => $def['sub'] + $def['fee'],
                'status'           => $def['status'],
                'taken_at'         => now()->subHours(rand(2,8)),
                'delivered_at'     => $def['status'] === 'delivered' ? now()->subHours(rand(1,3)) : null,
            ]);
            OrderItem::create([
                'order_id'  => $order->id,
                'item_name' => $def['item'][0],
                'quantity'  => $def['item'][1],
                'price'     => $def['item'][2],
                'subtotal'  => $def['item'][1] * $def['item'][2],
            ]);
            $detector->analyze($order);
        }

        $this->command->info('✅ Seeder selesai! Database siap digunakan.');
    }
}
