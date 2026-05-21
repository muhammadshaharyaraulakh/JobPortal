<?php

namespace App\Repositories;

use App\Contracts\Repositories\UserRepository;
use App\DTOs\LoginDTO;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class EloquentUserRepository implements UserRepository
{
    /**
     * {@inheritdoc}
     */
    public function existsByEmail(string $email): bool
    {
        return User::where('email', $email)->exists();
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data): User
    {
        return User::create($data);
    }
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }
    public function login(string $email, string $password): ?User
    {
        $user = User::where('email',$email)->first();
        if($user && Hash::check($password,$user->password)){
            return $user;
        }
        return null;
    }
}
