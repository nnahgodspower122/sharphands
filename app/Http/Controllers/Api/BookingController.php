<?php

namespace App\Http\Controllers\Api;

use App\Enums\BookingStatus;
use App\Enums\WorkerStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Rating;
use App\Models\Worker;
use App\Services\NearbyWorkerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Booking::query()->with(['user', 'worker.service', 'service', 'rating'])->latest();

        if ($user->isAdmin()) {
            // all bookings
        } elseif ($user->isWorker() && $user->worker) {
            $query->where('worker_id', $user->worker->id);
        } else {
            $query->where('user_id', $user->id);
        }

        return response()->json([
            'data' => $query->paginate(15),
        ]);
    }

    public function store(StoreBookingRequest $request, NearbyWorkerService $nearbyWorkers)
    {
        $this->authenticateOptionalToken($request);

        $user = $request->user();
        $data = $request->validated();

        $worker = isset($data['worker_id'])
            ? Worker::query()->with('service')->findOrFail($data['worker_id'])
            : $nearbyWorkers->nearest(
                (int) $data['service_id'],
                (float) $data['latitude'],
                (float) $data['longitude'],
            );

        if (! $worker || ! $worker->verified || $worker->status !== WorkerStatus::Available) {
            throw ValidationException::withMessages([
                'service_id' => ['No available worker was found nearby.'],
            ]);
        }

        if ((int) $worker->service_id !== (int) $data['service_id']) {
            throw ValidationException::withMessages([
                'worker_id' => ['That worker does not offer the selected service.'],
            ]);
        }

        $booking = DB::transaction(function () use ($user, $worker, $data) {
            $worker->update(['status' => WorkerStatus::Busy]);

            return Booking::query()->create([
                'user_id' => $user?->id,
                'guest_name' => $user ? null : ($data['name'] ?? null),
                'guest_phone' => $user ? null : ($data['phone'] ?? null),
                'worker_id' => $worker->id,
                'service_id' => $worker->service_id,
                'status' => BookingStatus::Pending,
                'amount' => $worker->service->base_amount,
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'notes' => $data['notes'] ?? null,
            ])->load(['worker.service', 'service']);
        });

        return response()->json([
            'message' => 'Connected. Call '.$worker->phone.' to get started.',
            'booking' => $booking,
            'worker' => [
                'id' => $worker->id,
                'name' => $worker->name,
                'phone' => $worker->phone,
                'service' => $worker->service,
            ],
        ], 201);
    }

    private function authenticateOptionalToken(Request $request): void
    {
        $plain = $request->bearerToken();

        if (! $plain || $request->user()) {
            return;
        }

        $user = \App\Models\User::query()->where('api_token', hash('sha256', $plain))->first();

        if ($user) {
            $request->setUserResolver(fn () => $user);
        }
    }

    public function rate(Request $request, Booking $booking)
    {
        abort_unless($booking->user_id === $request->user()->id, 403);
        abort_unless($booking->status === BookingStatus::Completed, 422);

        $data = $request->validate([
            'score' => ['required', 'integer', 'min:1', 'max:5'],
            'review' => ['nullable', 'string', 'max:1000'],
        ]);

        $rating = Rating::query()->updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'user_id' => $request->user()->id,
                'worker_id' => $booking->worker_id,
                'score' => $data['score'],
                'review' => $data['review'] ?? null,
            ],
        );

        return response()->json([
            'message' => 'Thanks for rating your worker.',
            'rating' => $rating,
        ]);
    }
}
