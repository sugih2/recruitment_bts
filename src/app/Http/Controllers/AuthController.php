<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'min:3', 'max:255', 'unique:users,name'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $validator->errors()], 400);
        }

        $user = User::create([
            'name' => $request->string('username')->toString(),
            'email' => $request->string('username')->toString().'@local.test',
            'password' => $request->string('password')->toString(),
        ]);

        return $this->tokenResponse($user, 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $validator->errors()], 400);
        }

        $user = User::where('name', $request->string('username')->toString())->first();

        if (! $user || ! Hash::check($request->string('password')->toString(), $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        return $this->tokenResponse($user);
    }

    private function tokenResponse(User $user, int $status = 200): JsonResponse
    {
        return response()->json([
            'user' => $user,
            'authentication_token' => $user->createToken('access-token')->plainTextToken,
            'refresh_token' => $user->createToken('refresh-token')->plainTextToken,
        ], $status);
    }
}
