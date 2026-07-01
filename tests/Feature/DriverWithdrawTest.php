<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Driver;
use App\Models\Withdrawal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverWithdrawTest extends TestCase
{
    use RefreshDatabase;

    private User $driverUser;
    private Driver $driver;
    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create driver user & profile
        $this->driverUser = User::create([
            'name'     => 'John Driver',
            'email'    => 'john@driver.com',
            'password' => bcrypt('password'),
            'role'     => 'driver',
            'status'   => 'approved',
        ]);

        $this->driver = Driver::create([
            'user_id'        => $this->driverUser->id,
            'phone'          => '0812345',
            'modal_saldo'    => 100000,
            'total_earnings' => 80000, // available for WD
            'is_online'      => true,
            'is_verified'    => true,
            'last_reset_date' => now()->toDateString(),
        ]);

        // Create admin user
        $this->adminUser = User::create([
            'name'     => 'Boss Admin',
            'email'    => 'boss@admin.com',
            'password' => bcrypt('password'),
            'role'     => 'admin',
            'status'   => 'approved',
        ]);
    }

    public function test_driver_can_request_withdrawal_successfully(): void
    {
        // 1. Request withdraw Rp 30,000
        $response = $this->actingAs($this->driverUser)
            ->post(route('driver.withdraw.store'), [
                'amount'         => 30000,
                'bank_name'      => 'BCA',
                'account_number' => '12345678',
                'account_name'   => 'John Driver',
            ]);

        $response->assertRedirect(route('driver.withdraw'));
        $response->assertSessionHas('success');

        // Check driver balance decremented immediately (80,000 - 30,000 = 50,000)
        $this->driver->refresh();
        $this->assertEquals(50000, $this->driver->total_earnings);

        // Check withdrawal created in database with pending status
        $this->assertDatabaseHas('withdrawals', [
            'driver_id'      => $this->driver->id,
            'amount'         => 30000,
            'bank_name'      => 'BCA',
            'account_number' => '12345678',
            'account_name'   => 'John Driver',
            'status'         => 'pending',
        ]);
    }

    public function test_driver_cannot_withdraw_more_than_balance(): void
    {
        // Request withdraw Rp 90,000 (exceeds balance of 80,000)
        $response = $this->actingAs($this->driverUser)
            ->post(route('driver.withdraw.store'), [
                'amount'         => 90000,
                'bank_name'      => 'BCA',
                'account_number' => '12345678',
                'account_name'   => 'John Driver',
            ]);

        $response->assertSessionHasErrors(['amount']);
        $this->driver->refresh();
        // Balance remains 80,000
        $this->assertEquals(80000, $this->driver->total_earnings);

        // Check no withdrawals created
        $this->assertEquals(0, Withdrawal::count());
    }

    public function test_admin_can_approve_withdrawal(): void
    {
        // Create withdrawal request
        $withdrawal = Withdrawal::create([
            'driver_id'      => $this->driver->id,
            'amount'         => 30000,
            'bank_name'      => 'BCA',
            'account_number' => '12345678',
            'account_name'   => 'John Driver',
            'status'         => 'pending',
        ]);

        // Deduct balance to mimic store method
        $this->driver->decrement('total_earnings', 30000);

        // Admin approves withdrawal
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.withdrawals.approve', $withdrawal));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check status updated to approved
        $withdrawal->refresh();
        $this->assertEquals('approved', $withdrawal->status);

        // Balance remains 50,000
        $this->driver->refresh();
        $this->assertEquals(50000, $this->driver->total_earnings);
    }

    public function test_admin_can_reject_withdrawal(): void
    {
        // Create withdrawal request
        $withdrawal = Withdrawal::create([
            'driver_id'      => $this->driver->id,
            'amount'         => 30000,
            'bank_name'      => 'BCA',
            'account_number' => '12345678',
            'account_name'   => 'John Driver',
            'status'         => 'pending',
        ]);

        // Deduct balance to mimic store method
        $this->driver->decrement('total_earnings', 30000);

        // Admin rejects withdrawal
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.withdrawals.reject', $withdrawal), [
                'admin_notes' => 'Nomor rekening tidak valid.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check status updated to rejected & notes stored
        $withdrawal->refresh();
        $this->assertEquals('rejected', $withdrawal->status);
        $this->assertEquals('Nomor rekening tidak valid.', $withdrawal->admin_notes);

        // Balance restored back to 80,000 (50,000 + 30,000)
        $this->driver->refresh();
        $this->assertEquals(80000, $this->driver->total_earnings);
    }
}
