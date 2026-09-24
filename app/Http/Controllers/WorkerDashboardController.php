<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\WorkerStatus;
use App\Http\Requests\UpdateWorkerLocationRequest;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WorkerDashboardController extends Controller
{
    public function index(Request $request)
    {
        $worker = $request->user()->worker()->with('service')->firstOrFail();

        $bookings = $worker->bookings()
            ->with(['user', 'service'])
            ->latest()
            ->paginate(10);

        return view('worker.dashboard', compact('worker', 'bookings'));
    }

    public function updateAvailability(Request $request)
    {
        $data = $request->validate([
            'status' => ['required', Rule::enum(WorkerStatus::class)],
        ]);

        $request->user()->worker->update([
            'status' => $data['status'],
        ]);

        return back()->with('status', 'Availability updated.');
    }

    public function updateLocation(UpdateWorkerLocationRequest $request)
    {
        $request->user()->worker->update($request->validated());

        return back()->with('status', 'Location updated.');
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

        return back()->with('status', 'Booking updated.');
    }
}
