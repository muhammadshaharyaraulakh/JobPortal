<?php

namespace App\Repositories;

use App\Contracts\Repositories\OtpRepository;
use App\Models\Otp;

class EloquentOtpRepository implements OtpRepository
{
    /**
     * {@inheritdoc}
     */
    public function hasHitStrikeLimit(string $email): bool
    {
        return Otp::where('email', $email)
            ->where('created_at', '>=', now()->startOfDay())
            ->count() >= 3;
    }

    /**
     * {@inheritdoc}
     */
    public function createOtp(string $email, string $otp, string $purpose, array $payload, int $expiresInMinutes = 15): Otp
    {
        return Otp::create([
            'email' => $email,
            'purpose' => $purpose,
            'otp' => $otp,
            'payload' => $payload,
            'expires_at' => now()->addMinutes($expiresInMinutes),
            'attempts' => 0,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function findUnexpiredOtp(string $email, string $purpose): ?Otp
    {
        return Otp::where('email', $email)
            ->where('purpose', $purpose)
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteOtp(Otp $otp): void
    {
        $otp->delete();
    }
   
}
