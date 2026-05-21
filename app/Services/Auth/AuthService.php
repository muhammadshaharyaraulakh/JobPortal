<?php

namespace App\Services\Auth;

use App\Contracts\Repositories\OtpRepository;
use App\Contracts\Repositories\UserRepository;
use App\Contracts\Services\Auth\AuthServiceContract;
use App\DTOs\LoginDTO;
use App\DTOs\NewUserDTO;
use App\Mail\RegisterOtpMail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthService implements AuthServiceContract
{
    public function __construct(
        protected UserRepository $userRepository,
        protected OtpRepository $otpRepository
    ) {}

    /**
     * {@inheritdoc}
     */
    public function requestAccount(NewUserDTO $dto): void
    {
        // 1. Asks UserRepository if the email already exists
        if ($this->userRepository->existsByEmail($dto->email)) {
            throw ValidationException::withMessages([
                'email' => __('This email address is already registered.'),
            ]);
        }

        if ($this->otpRepository->hasHitStrikeLimit($dto->email)) {
            throw ValidationException::withMessages([
                'email' => __('You have reached the maximum registration requests for today. Please try again tomorrow.'),
            ]);
        }

        $otp = (string) rand(100000, 999999);

        $payload = [
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => Hash::make($dto->password),
        ];

        $this->otpRepository->createOtp(
            email: $dto->email,
            otp: $otp,
            purpose: 'register',
            payload: $payload,
            expiresInMinutes: 15
        );

        // 5. Dispatches the email
        Mail::to($dto->email)->send(new RegisterOtpMail($dto->name, $otp));
    }

    /**
     * {@inheritdoc}
     */
    public function verifyAccount(string $email, string $otp): array
    {
        // 1. Asks OtpRepository to find the unexpired OTP for this email with purpose: 'register'
        $otpRecord = $this->otpRepository->findUnexpiredOtp($email, 'register');

        if (!$otpRecord || $otpRecord->otp !== $otp) {
            if ($otpRecord) {
                $otpRecord->increment('attempts');
            }
            throw ValidationException::withMessages([
                'otp' => __('Invalid or expired OTP.'),
            ]);
        }

        // 2. Extracts the payload
        $payload = $otpRecord->payload;

        // 3. Commands UserRepository to permanently create the User
        $user = $this->userRepository->create([
            'name' => $payload['name'],
            'email' => $payload['email'],
            'password' => $payload['password'], // Already hashed
            'email_verified_at' => now(),
        ]);

        // 4. Immediately commands OtpRepository to delete the OTP row
        $this->otpRepository->deleteOtp($otpRecord);

        // 5. Generates a new Laravel Sanctum token
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
    public function login(string $email , string $password): array
    {
        $user = $this->userRepository->login($email,$password);

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}
