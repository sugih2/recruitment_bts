<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\AuthRepository;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(private readonly AuthRepository $repository)
    {
    }

    public function register(string $username, string $password): User
    {
        return DB::transaction(fn (): User => $this->repository->create([
                'name' => $username,
                'email' => $username.'@local.test',
                'password' => $password,
            ]));
    }

    public function login(string $username, string $password): User
    {
        $user = $this->repository->findByName($username);

        if (! $user || ! Hash::check($password, $user->password)) {
            throw new AuthenticationException('The provided credentials are incorrect.');
        }

        return $user;
    }

    public function tokens(User $user): array
    {
        return [
            'authentication_token' => $user->createToken('access-token')->plainTextToken,
            'refresh_token' => $user->createToken('refresh-token')->plainTextToken,
        ];
    }
}
