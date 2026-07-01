<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Driver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_and_reset_flow(): void
    {
        // Setup database records
        $pendingUser = User::create([
            'name'         => 'Pending Driver',
            'email'        => 'pending@driver.com',
            'password'     => bcrypt('password123'),
            'role'         => 'driver',
            'status'       => 'pending',
            'phone'        => '08111222333',
            'address'      => 'Jl. Pending No. 1',
            'ktp_image'    => 'uploads/ktp/mock.png',
            'selfie_image' => 'uploads/selfie/mock.png',
        ]);

        $approvedUser = User::create([
            'name'         => 'Approved Driver',
            'email'        => 'approved@driver.com',
            'password'     => bcrypt('password123'),
            'role'         => 'driver',
            'status'       => 'approved',
            'phone'        => '08222333444',
            'address'      => 'Jl. Approved No. 2',
            'ktp_image'    => 'uploads/ktp/mock.png',
            'selfie_image' => 'uploads/selfie/mock.png',
        ]);

        Driver::create([
            'user_id'     => $approvedUser->id,
            'phone'       => '08222333444',
            'is_verified' => true,
        ]);

        // 1. Check forgot password view is accessible
        $response = $this->get('/forgot-password');
        $response->assertStatus(200);

        // 2. Request password reset with unregistered email
        $response = $this->post('/forgot-password', ['email' => 'unknown@mail.com']);
        $response->assertSessionHasErrors(['email' => 'Email tidak terdaftar dalam sistem.']);

        // 3. Request password reset with pending driver email -> should fail
        $response = $this->post('/forgot-password', ['email' => 'pending@driver.com']);
        $response->assertSessionHasErrors(['email' => 'Akun Anda sedang menunggu persetujuan oleh admin, sehingga tidak dapat melakukan reset password.']);

        // 4. Request password reset with approved driver email -> should succeed
        $response = $this->post('/forgot-password', ['email' => 'approved@driver.com']);
        $response->assertSessionHas('success');

        // Check token is in database
        $tokenRecord = DB::table('password_reset_tokens')->where('email', 'approved@driver.com')->first();
        $this->assertNotNull($tokenRecord);

        // 5. Open reset password form with invalid token
        $response = $this->get('/reset-password/invalid-token?email=approved@driver.com');
        $response->assertStatus(200);

        // Submit reset form with invalid token -> should fail
        $response = $this->post('/reset-password', [
            'token'                 => 'invalid-token',
            'email'                 => 'approved@driver.com',
            'password'              => 'newsecurepassword',
            'password_confirmation' => 'newsecurepassword',
        ]);
        $response->assertSessionHasErrors(['email' => 'Token reset password tidak valid atau telah kedaluwarsa.']);

        // 6. Submit reset form with valid token
        // Since token stored in DB is hashed, we retrieve the plain text token from logic or we can insert a known hash.
        // Let's create a known plain token
        $plainToken = 'my-secure-plain-token';
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => 'approved@driver.com'],
            [
                'token'      => Hash::make($plainToken),
                'created_at' => now(),
            ]
        );

        $response = $this->post('/reset-password', [
            'token'                 => $plainToken,
            'email'                 => 'approved@driver.com',
            'password'              => 'newsecurepassword',
            'password_confirmation' => 'newsecurepassword',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('success', 'Password berhasil diubah! Silakan login kembali dengan password baru Anda.');

        // Verify password updated in DB
        $approvedUser->refresh();
        $this->assertTrue(Hash::check('newsecurepassword', $approvedUser->password));

        // Verify token deleted
        $this->assertNull(DB::table('password_reset_tokens')->where('email', 'approved@driver.com')->first());
    }
}
