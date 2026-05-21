<?php

namespace App\Contracts\Repositories;

use App\Models\Otp;

interface OtpRepository
{
    /**
     * Check if the email has hit the 3-strike OTP request limit today.
     */
    public function hasHitStrikeLimit(string $email): bool;

    /**
     * Create/Save a new OTP record.
     */
    public function createOtp(string $email, string $otp, string $purpose, array $payload, int $expiresInMinutes = 15): Otp;

    /**
     * Find an unexpired OTP for the email and purpose.
     */
    public function findUnexpiredOtp(string $email, string $purpose): ?Otp;

    /**
     * Delete an OTP record.
     */
    public function deleteOtp(Otp $otp): void;
}
