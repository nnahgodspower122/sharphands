<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\WorkerStatus;
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
        $bookings = $request->user()
            ->bookings()
            ->with(['worker.service', 'service', 'rating'])
            ->latest()
            ->paginate(10);

        return view('bookings.index', compact('bookings'));
    }

    public function store(StoreBookingRequest $request, NearbyWorkerService $nearbyWorkers)
    {
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
                'service_id' => 'No available worker was found nearby. Try again shortly.',
            ]);
        }

        if ((int) $worker->service_id !== (int) $data['service_id']) {
            throw ValidationException::withMessages([
                'worker_id' => 'That worker does not offer the selected service.',
            ]);
        }

        $booking = DB::transaction(function () use ($user, $worker, $data) {
            $worker->update(['status' => WorkerStatus::Busy]);

            return Booking::query()->create([
                'user_id' => $user->id,
                'worker_id' => $worker->id,
                'service_id' => $worker->service_id,
                'status' => BookingStatus::Pending,
                'amount' => $worker->service->base_amount,
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'notes' => $data['notes'] ?? null,
            ]);
        });

        $booking->load(['worker.service', 'service']);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Connected to the nearest available worker.',
                'booking' => $booking,
            ], 201);
        }

        return redirect()
            ->route('bookings.index')
            ->with('status', 'You are connected to '.$worker->name.'. Call '.$worker->phone.' to get started.');
    }

    public function rate(Request $request, Booking $booking)
    {
        abort_unless($booking->user_id === $request->user()->id, 403);
        abort_unless($booking->status === BookingStatus::Completed, 422);

        $data = $request->validate([
            'score' => ['required', 'integer', 'min:1', 'max:5'],
            'review' => ['nullable', 'string', 'max:1000'],
        ]);

        Rating::query()->updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'user_id' => $request->user()->id,
                'worker_id' => $booking->worker_id,
                'score' => $data['score'],
                'review' => $data['review'] ?? null,
            ],
        );

        return back()->with('status', 'Thanks for rating your worker.');
    }
}
