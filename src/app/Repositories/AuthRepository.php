<?php

namespace App\Repositories;

use App\Models\User;

class AuthRepository
{
    public function create(array $data): User
    {
        return User::create($data);
    }

    public function findByName(string $name): ?User
    {
        return User::where('name', $name)->first();
    }
}
