<?php

namespace App\Contracts\Repositories;

use App\DTOs\LoginDTO;
use App\Models\User;

interface UserRepository
{
    /**
     * Check if a user exists with the given email.
     */
    public function existsByEmail(string $email): bool;

    /**
     * Create a new user record.
     */
    public function create(array $data): User;

    /**
     * Find a user by email.
     */
    public function findByEmail(string $email): ?User;
    /**
     * Login User By Email and Password
     */

    public function login(string $email, string $password): ?User;

}
