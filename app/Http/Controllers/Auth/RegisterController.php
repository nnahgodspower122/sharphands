<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(RegisterUserRequest $request)
    {
        $user = User::query()->create([
            ...$request->safe()->except('password_confirmation'),
            'role' => UserRole::User,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('services.index');
    }
}
