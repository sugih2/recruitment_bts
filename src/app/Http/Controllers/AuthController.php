<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $service)
    {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->service->register($request->string('username')->toString(), $request->string('password')->toString());

        return response()->json([
            'user' => new UserResource($user),
            ...$this->service->tokens($user),
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->service->login($request->string('username')->toString(), $request->string('password')->toString());

        return response()->json([
            'user' => new UserResource($user),
            ...$this->service->tokens($user),
        ]);
    }
}
