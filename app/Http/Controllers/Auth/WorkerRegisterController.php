<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Enums\WorkerStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterWorkerRequest;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WorkerRegisterController extends Controller
{
    public function create()
    {
        return view('auth.worker-register', [
            'services' => Service::query()->orderBy('name')->get(),
        ]);
    }

    public function store(RegisterWorkerRequest $request)
    {
        $user = DB::transaction(function () use ($request) {
            $user = User::query()->create([
                'name' => $request->string('name'),
                'email' => $request->string('email'),
                'phone' => $request->string('phone'),
                'password' => $request->string('password'),
                'role' => UserRole::Worker,
            ]);

            $user->worker()->create([
                'service_id' => $request->integer('service_id'),
                'name' => $request->string('name'),
                'phone' => $request->string('phone'),
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
                'status' => WorkerStatus::Available,
                'verified' => false,
                'applied_at' => now(),
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('worker.dashboard')
            ->with('status', 'Account created. An admin will verify your profile before you appear to nearby customers.');
    }
}
