<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Driver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class DriverRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_registration_and_approval_flow(): void
    {
        // 1. Unauthenticated access redirects to login
        $response = $this->get('/driver/dashboard');
        $response->assertRedirect('/login');

        // 2. Register a new driver
        $ktp = UploadedFile::fake()->image('ktp.jpg');
        $selfie = UploadedFile::fake()->image('selfie.jpg');

        $regData = [
            'name'                  => 'John Doe',
            'email'                 => 'johndoe@example.com',
            'phone'                 => '081234567890',
            'address'               => 'Jl. Testing No. 123, Jakarta',
            'vehicle_type'          => 'Motor',
            'vehicle_plate'         => 'B 1234 XYZ',
            'operational_area'      => 'Sukajadi',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'ktp_image'             => $ktp,
            'selfie_image'          => $selfie,
        ];

        $response = $this->post('/register', $regData);
        
        // Assert registration redirects to login with success flash
        $response->assertRedirect('/login');
        $response->assertSessionHas('success', 'Pendaftaran berhasil! Akun Anda sedang menunggu persetujuan admin.');

        // Check if user and driver were created
        $user = User::where('email', 'johndoe@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('pending', $user->status);
        $this->assertEquals('driver', $user->role);
        $this->assertEquals('081234567890', $user->phone);
        $this->assertEquals('Jl. Testing No. 123, Jakarta', $user->address);
        $this->assertNotNull($user->ktp_image);
        $this->assertNotNull($user->selfie_image);

        $driver = Driver::where('user_id', $user->id)->first();
        $this->assertNotNull($driver);
        $this->assertFalse($driver->is_verified);
        $this->assertEquals('Motor', $driver->vehicle_type);
        $this->assertEquals('B 1234 XYZ', $driver->vehicle_plate);
        $this->assertNotNull($driver->photo);

        // Clean up uploaded files
        if (file_exists(public_path($user->ktp_image))) {
            unlink(public_path($user->ktp_image));
        }
        if (file_exists(public_path($user->selfie_image))) {
            unlink(public_path($user->selfie_image));
        }

        // 3. Attempt to login as pending driver -> should fail
        $loginResponse = $this->post('/login', [
            'email'    => 'johndoe@example.com',
            'password' => 'password123',
        ]);
        $loginResponse->assertSessionHasErrors(['email' => 'Akun sedang menunggu persetujuan admin.']);
        $this->assertGuest();

        // 4. Log in as admin, approve the pending driver
        $admin = User::create([
            'name'     => 'Admin Test',
            'email'    => 'admintest@example.com',
            'password' => bcrypt('password123'),
            'role'     => 'admin',
            'status'   => 'approved',
        ]);

        $this->actingAs($admin);

        // Access drivers panel
        $driversPanelResponse = $this->get('/admin/drivers');
        $driversPanelResponse->assertStatus(200);
        $driversPanelResponse->assertSee('John Doe');

        // Approve the driver
        $approveResponse = $this->post("/admin/drivers/{$user->id}/approve");
        $approveResponse->assertRedirect();
        $approveResponse->assertSessionHas('success');

        // Check DB updates
        $user->refresh();
        $driver->refresh();
        $this->assertEquals('approved', $user->status);
        $this->assertTrue($driver->is_verified);

        // Logout admin
        $this->post('/logout');
        $this->assertGuest();

        // 5. Login as approved driver -> should succeed
        $loginResponse = $this->post('/login', [
            'email'    => 'johndoe@example.com',
            'password' => 'password123',
        ]);
        $loginResponse->assertRedirect('/driver/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_driver_registration_and_rejection_flow(): void
    {
        $user = User::create([
            'name'         => 'Jane Doe',
            'email'        => 'janedoe@example.com',
            'password'     => bcrypt('password123'),
            'role'         => 'driver',
            'status'       => 'pending',
            'phone'        => '081222333444',
            'address'      => 'Jl. Mawar No. 4',
            'ktp_image'    => 'uploads/ktp/mock.png',
            'selfie_image' => 'uploads/selfie/mock.png',
        ]);

        $driver = Driver::create([
            'user_id'     => $user->id,
            'phone'       => '081222333444',
            'is_verified' => false,
        ]);

        $admin = User::create([
            'name'     => 'Admin Test 2',
            'email'    => 'admintest2@example.com',
            'password' => bcrypt('password123'),
            'role'     => 'admin',
            'status'   => 'approved',
        ]);

        // Reject driver
        $this->actingAs($admin);
        $rejectResponse = $this->post("/admin/drivers/{$user->id}/reject");
        $rejectResponse->assertRedirect();

        $user->refresh();
        $driver->refresh();
        $this->assertEquals('rejected', $user->status);
        $this->assertFalse($driver->is_verified);

        // Logout admin
        $this->post('/logout');

        // Attempt login as rejected driver -> should fail
        $loginResponse = $this->post('/login', [
            'email'    => 'janedoe@example.com',
            'password' => 'password123',
        ]);
        $loginResponse->assertSessionHasErrors(['email' => 'Akun ditolak oleh admin.']);
        $this->assertGuest();
    }
}
