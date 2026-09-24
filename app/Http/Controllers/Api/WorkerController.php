<?php

namespace App\Http\Controllers\Api;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Enums\WorkerStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterWorkerRequest;
use App\Http\Requests\UpdateWorkerLocationRequest;
use App\Models\Booking;
use App\Models\User;
use App\Services\NearbyWorkerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WorkerController extends Controller
{
    public function register(RegisterWorkerRequest $request)
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

            return $user->load('worker.service');
        });

        return response()->json([
            'message' => 'Worker registered. Awaiting admin verification.',
            'user' => $user->toAuthArray($user->issueApiToken()),
            'worker' => $user->worker,
        ], 201);
    }

    public function availability(Request $request)
    {
        $worker = $request->user()->worker()->with('service')->firstOrFail();

        if ($request->isMethod('post')) {
            $data = $request->validate([
                'status' => ['required', Rule::enum(WorkerStatus::class)],
            ]);
            $worker->update($data);
        }

        return response()->json([
            'worker' => $worker->fresh('service'),
        ]);
    }

    public function updateLocation(UpdateWorkerLocationRequest $request)
    {
        $worker = $request->user()->worker()->firstOrFail();
        $worker->update($request->validated());

        return response()->json([
            'message' => 'Location updated.',
            'worker' => $worker->fresh('service'),
        ]);
    }

    public function nearby(Request $request, NearbyWorkerService $nearbyWorkers)
    {
        $data = $request->validate([
            'service_id' => ['nullable', 'exists:services,id'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        return response()->json([
            'data' => $nearbyWorkers->publicList(
                isset($data['service_id']) ? (int) $data['service_id'] : null,
                (float) $data['lat'],
                (float) $data['lng'],
            ),
        ]);
    }

    public function dashboard(Request $request)
    {
        $worker = $request->user()->worker()->with('service')->firstOrFail();

        return response()->json([
            'worker' => $worker,
            'bookings' => $worker->bookings()->with(['user', 'service'])->latest()->paginate(10),
        ]);
    }

    public function updateBooking(Request $request, Booking $booking)
    {
        $worker = $request->user()->worker;
        abort_unless($booking->worker_id === $worker->id, 403);

        $data = $request->validate([
            'status' => ['required', Rule::enum(BookingStatus::class)],
        ]);

        $status = $data['status'] instanceof BookingStatus
            ? $data['status']
            : BookingStatus::from($data['status']);

        $booking->update(['status' => $status]);

        if (in_array($status, [BookingStatus::Completed, BookingStatus::Cancelled], true)) {
            $worker->update(['status' => WorkerStatus::Available]);
        }

        if ($status === BookingStatus::InProgress) {
            $worker->update(['status' => WorkerStatus::Busy]);
        }

        return response()->json([
            'message' => 'Booking updated.',
            'booking' => $booking->fresh(['user', 'service']),
        ]);
    }
}
