<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'organization_id' => $request->organization_id,
            'name'            => $request->name,
            'email'           => $request->email,
            'password'        => $request->password,
        ]);

        $user->assignRole($request->role);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully.',
            'user'    => $user->load('organization'),
            'token'   => $token,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials are incorrect.',
            ]);
        }

        $user  = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'Your account has been deactivated.',
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'user'    => $user->load(['organization', 'roles']),
            'token'   => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user()->load(['organization', 'roles', 'permissions']),
        ]);
    }
}
