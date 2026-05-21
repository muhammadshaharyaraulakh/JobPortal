<?php

namespace Tests\Feature;

use App\Mail\RegisterOtpMail;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    /**
     * Test successful registration OTP request.
     */
    public function test_user_can_request_registration_otp(): void
    {
        $response = $this->postJson(route('api.auth.register'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePass123!',
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Check your email']);

        // Assert OTP is saved in database
        $this->assertDatabaseHas('otps', [
            'email' => 'john@example.com',
            'purpose' => 'register',
        ]);

        $otpRecord = Otp::where('email', 'john@example.com')->first();
        $this->assertNotNull($otpRecord);
        $this->assertEquals('John Doe', $otpRecord->payload['name']);

        // Assert Mail was queued
        Mail::assertQueued(RegisterOtpMail::class, function ($mail) use ($otpRecord) {
            return $mail->hasTo('john@example.com') && $mail->otp === $otpRecord->otp;
        });
    }

    /**
     * Test validation guards (invalid email and weak password).
     */
    public function test_registration_validates_email_and_password_strength(): void
    {
        // Weak password (no symbols, no numbers, too short)
        $response = $this->postJson(route('api.auth.register'), [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => '123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    /**
     * Test that registration fails if the email already exists in users table.
     */
    public function test_registration_fails_if_email_already_exists(): void
    {
        User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $response = $this->postJson(route('api.auth.register'), [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'SecurePass123!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test OTP strike limit (3-strike limit today).
     */
    public function test_registration_fails_if_otp_limit_reached(): void
    {
        // Seed 3 OTP requests today for this email
        for ($i = 0; $i < 3; $i++) {
            Otp::create([
                'email' => 'limit@example.com',
                'purpose' => 'register',
                'otp' => '12345' . $i,
                'payload' => [],
                'expires_at' => now()->addMinutes(15),
            ]);
        }

        $response = $this->postJson(route('api.auth.register'), [
            'name' => 'John Doe',
            'email' => 'limit@example.com',
            'password' => 'SecurePass123!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email'])
            ->assertJsonPath('errors.email.0', 'You have reached the maximum registration requests for today. Please try again tomorrow.');
    }

    /**
     * Test successful OTP verification and permanent registration.
     */
    public function test_user_can_verify_otp_and_complete_registration(): void
    {
        // 1. Request the OTP
        $this->postJson(route('api.auth.register'), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'JaneSecure123!',
        ]);

        $otpRecord = Otp::where('email', 'jane@example.com')->first();
        $this->assertNotNull($otpRecord);

        // 2. Submit verify otp request
        $response = $this->postJson(route('api.auth.verify_otp'), [
            'email' => 'jane@example.com',
            'otp' => $otpRecord->otp,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'created_at', 'updated_at'],
                'token'
            ]);

        // Assert user was permanently created
        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'name' => 'Jane Doe',
        ]);

        // Assert OTP row was deleted
        $this->assertDatabaseMissing('otps', [
            'email' => 'jane@example.com',
        ]);
    }

    /**
     * Test OTP verification fails with incorrect or expired OTP.
     */
    public function test_verification_fails_with_invalid_otp(): void
    {
        // Request OTP
        $this->postJson(route('api.auth.register'), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'JaneSecure123!',
        ]);

        // Verify with wrong OTP
        $response = $this->postJson(route('api.auth.verify_otp'), [
            'email' => 'jane@example.com',
            'otp' => '000000', // incorrect OTP
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['otp']);

        // Assert attempts were incremented
        $otpRecord = Otp::where('email', 'jane@example.com')->first();
        $this->assertEquals(1, $otpRecord->attempts);

        // Assert user was not created
        $this->assertDatabaseMissing('users', [
            'email' => 'jane@example.com',
        ]);
    }
}
