<?php

namespace App\Contracts\Services\Auth;

use App\DTOs\LoginDTO;
use App\DTOs\NewUserDTO;
use App\Models\User;

interface AuthServiceContract
{
    /**
     * Request account creation by validating, creating a temporary OTP, and sending an email.
     *
     * @param NewUserDTO $dto
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    public function requestAccount(NewUserDTO $dto): void;

    /**
     * Verify the OTP and permanently create the user.
     *
     * @param string $email
     * @param string $otp
     * @return array{user: User, token: string}
     * @throws \Illuminate\Validation\ValidationException
     */
    public function verifyAccount(string $email, string $otp): array;

    /**
     * Login the user and generate a token.
     *
     * @param LoginDTO $dto
     * @return array{user: User, token: string}
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(string $email , string $password): array;
}
