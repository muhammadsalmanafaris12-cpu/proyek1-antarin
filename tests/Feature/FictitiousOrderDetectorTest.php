<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Restaurant;
use App\Services\FictitiousOrderDetector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FictitiousOrderDetectorTest extends TestCase
{
    use RefreshDatabase;

    private Restaurant $restaurant;
    private FictitiousOrderDetector $detector;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->restaurant = Restaurant::create([
            'name' => 'Ayam Geprek Test',
            'address' => 'Jl. Test No. 1',
            'phone' => '022123456',
            'latitude' => -6.8770,
            'longitude' => 107.5870,
            'category' => 'Ayam',
            'is_active' => true,
        ]);

        $this->detector = new FictitiousOrderDetector();
    }

    public function test_safe_order_has_low_score()
    {
        $customer = Customer::create([
            'name' => 'John Doe',
            'phone' => '08123456789',
            'email' => 'john@test.com',
            'address' => 'Jl. Sariasih No. 123, RT 01/RW 02, Bandung',
            'latitude' => -6.8770,
            'longitude' => 107.5870,
            'cancel_count' => 0,
            'order_count' => 5,
        ]);
        $customer->created_at = now()->subDays(10);
        $customer->save();

        $order = Order::create([
            'order_code' => 'ORD-TEST-1',
            'customer_id' => $customer->id,
            'restaurant_id' => $this->restaurant->id,
            'delivery_address' => 'Jl. Sariasih No. 123, RT 01/RW 02, Bandung',
            'delivery_lat' => -6.8770,
            'delivery_lng' => 107.5870,
            'subtotal' => 50000,
            'delivery_fee' => 10000,
            'total_amount' => 50000,
            'status' => 'available',
        ]);

        $suspicion = $this->detector->analyze($order);

        $this->assertLessThan(30, $suspicion->score);
        $this->assertFalse($order->fresh()->is_suspicious);
    }

    public function test_new_customer_and_short_address_increases_score()
    {
        // Customer registered today
        $customer = Customer::create([
            'name' => 'New Customer',
            'phone' => '08123456789',
            'email' => 'new@test.com',
            'address' => 'test',
            'latitude' => -6.8770,
            'longitude' => 107.5870,
            'cancel_count' => 0,
            'order_count' => 0,
            'created_at' => now(),
        ]);

        $order = Order::create([
            'order_code' => 'ORD-TEST-2',
            'customer_id' => $customer->id,
            'restaurant_id' => $this->restaurant->id,
            'delivery_address' => 'test', // too short (< 10 chars)
            'delivery_lat' => -6.8770,
            'delivery_lng' => 107.5870,
            'subtotal' => 50000,
            'delivery_fee' => 10000,
            'total_amount' => 50000,
            'status' => 'available',
        ]);

        $suspicion = $this->detector->analyze($order);

        // 25 (new account) + 30 (short address) + 20 (suspicious keyword "test") = 75
        $this->assertEquals(75, $suspicion->score);
        $this->assertTrue($order->fresh()->is_suspicious);
        $this->assertContains('Akun customer baru dibuat (kurang dari 7 hari).', $suspicion->flags);
        $this->assertContains('Alamat pengiriman tidak lengkap atau kosong.', $suspicion->flags);
    }

    public function test_anomalous_coordinates_flagged()
    {
        $customer = Customer::create([
            'name' => 'Anomaly Customer',
            'phone' => '08123456789',
            'email' => 'anomaly@test.com',
            'address' => 'Jl. Kebon Jeruk No. 123, RT 01/RW 02, Jakarta Barat',
            'latitude' => 999.0, // Anomaly
            'longitude' => 999.0,
            'cancel_count' => 0,
            'order_count' => 1,
        ]);
        $customer->created_at = now()->subDays(10);
        $customer->save();

        $order = Order::create([
            'order_code' => 'ORD-TEST-3',
            'customer_id' => $customer->id,
            'restaurant_id' => $this->restaurant->id,
            'delivery_address' => 'Jl. Kebon Jeruk No. 123, RT 01/RW 02, Jakarta Barat',
            'delivery_lat' => 999.0,
            'delivery_lng' => 999.0,
            'subtotal' => 50000,
            'delivery_fee' => 10000,
            'total_amount' => 50000,
            'status' => 'available',
        ]);

        $suspicion = $this->detector->analyze($order);

        // 35 (coordinates anomaly)
        $this->assertEquals(35, $suspicion->score);
        $this->assertTrue($order->fresh()->is_suspicious);
        $this->assertContains('Koordinat pengiriman berada di luar wilayah Indonesia.', $suspicion->flags);
    }
}
