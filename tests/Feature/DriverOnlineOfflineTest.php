<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Driver;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverOnlineOfflineTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Driver $driver;
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        // Create driver user
        $this->user = User::create([
            'name'     => 'Test Driver',
            'email'    => 'testdriver@mail.com',
            'password' => bcrypt('password'),
            'role'     => 'driver',
            'status'   => 'approved',
            'phone'    => '08123456789',
            'address'  => 'Jl. Test No. 1',
        ]);

        $this->driver = Driver::create([
            'user_id'      => $this->user->id,
            'phone'        => '08123456789',
            'modal_saldo'  => 100000,
            'is_online'    => false,
            'is_verified'  => true,
            'last_reset_date' => now()->toDateString(),
        ]);

        // Create restaurant
        $restaurant = Restaurant::create([
            'name'      => 'Test Resto',
            'address'   => 'Jl. Resto',
            'phone'     => '022123',
            'latitude'  => -6.8770,
            'longitude' => 107.5870,
            'category'  => 'Food',
        ]);

        // Create customer
        $customer = Customer::create([
            'name'      => 'Test Customer',
            'phone'     => '082345',
            'email'     => 'cust@mail.com',
            'address'   => 'Jl. Customer',
            'latitude'  => -6.8770,
            'longitude' => 107.5870,
        ]);

        // Create available order
        $this->order = Order::create([
            'order_code'       => 'ORD-TEST',
            'customer_id'      => $customer->id,
            'restaurant_id'    => $restaurant->id,
            'delivery_address' => 'Jl. Customer',
            'delivery_lat'     => -6.8770,
            'delivery_lng'     => 107.5870,
            'subtotal'         => 50000,
            'delivery_fee'     => 10000,
            'total_amount'     => 50000,
            'status'           => 'available',
        ]);
    }

    public function test_driver_dashboard_when_offline(): void
    {
        // 1. Visit dashboard when offline
        $response = $this->actingAs($this->user)
            ->get(route('driver.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Akun Anda Sedang Offline');
        $response->assertDontSee('Order Masuk Di Sekitar Anda');
        $response->assertViewHas('availableOrders', function ($collection) {
            return $collection->isEmpty();
        });

        // 2. Try to view order detail when offline -> redirects to dashboard
        $response = $this->actingAs($this->user)
            ->get(route('driver.order.detail', $this->order));

        $response->assertRedirect(route('driver.dashboard'));
        $response->assertSessionHas('error', 'Aktifkan status online Anda terlebih dahulu untuk melihat pesanan.');

        // 3. Try to take order when offline -> redirects to dashboard
        $response = $this->actingAs($this->user)
            ->post(route('driver.order.take', $this->order));

        $response->assertRedirect(route('driver.dashboard'));
        $response->assertSessionHas('error', 'Aktifkan status online Anda terlebih dahulu untuk mengambil pesanan.');
    }

    public function test_driver_dashboard_when_online(): void
    {
        // Set online
        $this->driver->update(['is_online' => true]);

        // 1. Visit dashboard when online
        $response = $this->actingAs($this->user)
            ->get(route('driver.dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('Akun Anda Sedang Offline');
        $response->assertSee('Order Masuk Di Sekitar Anda');
        $response->assertViewHas('availableOrders', function ($collection) {
            return $collection->count() === 1;
        });

        // 2. View order detail when online -> successful
        $response = $this->actingAs($this->user)
            ->get(route('driver.order.detail', $this->order));

        $response->assertStatus(200);
        $response->assertSee('Test Resto');

        // 3. Take order when online -> successful redirect to active order
        $response = $this->actingAs($this->user)
            ->post(route('driver.order.take', $this->order));

        $response->assertRedirect(route('driver.active-order'));
        $response->assertSessionHas('success');
    }

    public function test_driver_daily_reset_logic(): void
    {
        // Set last_reset_date to yesterday
        $this->driver->update([
            'modal_saldo'     => 150000,
            'total_earnings'  => 50000,
            'daily_earnings'  => 50000,
            'last_reset_date' => now()->subDay()->toDateString(),
        ]);

        // Refresh/Retrieve driver model
        $retrievedDriver = Driver::find($this->driver->id);

        // Assert that the fields have been reset automatically
        $this->assertEquals(0, $retrievedDriver->modal_saldo);
        $this->assertEquals(0, $retrievedDriver->daily_earnings);
        $this->assertEquals(50000, $retrievedDriver->total_earnings);
        $this->assertEquals(now()->toDateString(), $retrievedDriver->last_reset_date->toDateString());

        // Now set new modal and retrieve again on the SAME day -> should not reset
        $retrievedDriver->update([
            'modal_saldo'    => 200000,
            'daily_earnings' => 60000,
        ]);

        $retrievedDriverAgain = Driver::find($this->driver->id);
        $this->assertEquals(200000, $retrievedDriverAgain->modal_saldo);
        $this->assertEquals(60000, $retrievedDriverAgain->daily_earnings);
        $this->assertEquals(50000, $retrievedDriverAgain->total_earnings);
    }

    public function test_driver_can_only_update_phone_number(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('driver.profile.update'), [
                'phone'         => '08999999999',
                'name'          => 'New Hacker Name',
                'vehicle_type'  => 'Mobil',
                'vehicle_plate' => 'B 9999 ZZZ',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Nomor HP berhasil diperbarui.');

        $this->driver->refresh();
        $this->user->refresh();

        // Phone is updated
        $this->assertEquals('08999999999', $this->driver->phone);
        $this->assertEquals('08999999999', $this->user->phone);

        // Name, vehicle_type, and vehicle_plate are NOT updated
        $this->assertEquals('Test Driver', $this->user->name);
        $this->assertEquals('Motor', $this->driver->vehicle_type);
        $this->assertNull($this->driver->vehicle_plate);
    }
}
