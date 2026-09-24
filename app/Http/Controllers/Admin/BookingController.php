<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $bookings = Booking::query()
            ->with(['user', 'worker.service', 'service'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->string('q');
                $query->where(function ($inner) use ($search) {
                    $inner->whereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('worker', fn ($worker) => $worker->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.bookings', compact('bookings'));
    }

    public function update(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'status' => ['required', Rule::enum(BookingStatus::class)],
        ]);

        $booking->update($data);

        return back()->with('status', 'Booking status updated.');
    }
}
