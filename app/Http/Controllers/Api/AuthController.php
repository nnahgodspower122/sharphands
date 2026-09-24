<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterUserRequest $request)
    {
        $user = User::query()->create([
            ...$request->safe()->except('password_confirmation'),
            'role' => UserRole::User,
        ]);

        return response()->json([
            'message' => 'Account created.',
            'user' => $user->toAuthArray($user->issueApiToken()),
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $user = User::query()->where('email', $request->string('email'))->first();

        if (! $user || ! Hash::check($request->string('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Those credentials do not match our records.'],
            ]);
        }

        return response()->json([
            'message' => 'Authenticated.',
            'user' => $user->toAuthArray($user->issueApiToken()),
        ]);
    }

    public function me()
    {
        $user = request()->user()->load('worker.service');

        return response()->json([
            'user' => $user->toAuthArray(),
            'worker' => $user->worker,
        ]);
    }

    public function logout()
    {
        request()->user()->forceFill(['api_token' => null])->save();

        return response()->json(['message' => 'Logged out.']);
    }
}
