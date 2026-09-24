<?php

namespace App\Http\Controllers\Api;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $bookings = Booking::query()
            ->with(['user', 'worker.service', 'service'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->whereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"))
                        ->orWhere('guest_name', 'like', "%{$search}%")
                        ->orWhere('guest_phone', 'like', "%{$search}%")
                        ->orWhereHas('worker', fn ($worker) => $worker->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(5);

        $stats = [
            'users' => User::query()->where('role', 'user')->count(),
            'workers' => Worker::query()->where('verified', true)->count(),
            'bookings' => Booking::query()->whereIn('status', [
                BookingStatus::Pending,
                BookingStatus::InProgress,
            ])->count(),
            'revenue' => Booking::query()->where('status', BookingStatus::Completed)->sum('amount'),
        ];

        $queue = Worker::query()
            ->with('service')
            ->where('verified', false)
            ->latest('applied_at')
            ->limit(6)
            ->get();

        $completed = Booking::query()->where('status', BookingStatus::Completed)->count();
        $totalBookings = max(Booking::query()->count(), 1);
        $verified = Worker::query()->where('verified', true)->count();
        $totalWorkers = max(Worker::query()->count(), 1);

        return response()->json([
            'stats' => $stats,
            'bookings' => $bookings,
            'queue' => $queue,
            'performance' => [
                'user_growth' => min(100, (int) round(($stats['users'] / max($stats['users'] + 5, 20)) * 100) + 40),
                'verification' => (int) round(($verified / $totalWorkers) * 100),
                'revenue' => $stats['revenue'],
                'revenue_target' => 1500000,
                'completion' => (int) round(($completed / $totalBookings) * 100),
            ],
        ]);
    }

    public function users(Request $request)
    {
        $users = User::query()
            ->where('role', '!=', 'admin')
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->string('q');
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->withCount('bookings')
            ->latest()
            ->paginate(12);

        return response()->json(['data' => $users]);
    }

    public function workers(Request $request)
    {
        $workers = Worker::query()
            ->with(['user', 'service'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->string('q');
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(12);

        return response()->json(['data' => $workers]);
    }

    public function bookings(Request $request)
    {
        $bookings = Booking::query()
            ->with(['user', 'worker.service', 'service'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->string('q');
                $query->where(function ($inner) use ($search) {
                    $inner->whereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%"))
                        ->orWhere('guest_name', 'like', "%{$search}%")
                        ->orWhere('guest_phone', 'like', "%{$search}%")
                        ->orWhereHas('worker', fn ($worker) => $worker->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(12);

        return response()->json(['data' => $bookings]);
    }

    public function approve(Worker $worker)
    {
        $worker->update(['verified' => true]);

        return response()->json([
            'message' => $worker->name.' has been verified.',
            'worker' => $worker->fresh('service'),
        ]);
    }

    public function reject(Worker $worker)
    {
        $name = $worker->name;
        $worker->user()->delete();

        return response()->json([
            'message' => $name.' was rejected and removed.',
        ]);
    }

    public function updateBooking(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'status' => ['required', Rule::enum(BookingStatus::class)],
        ]);

        $booking->update($data);

        return response()->json([
            'message' => 'Booking status updated.',
            'booking' => $booking->fresh(['user', 'worker', 'service']),
        ]);
    }

    public function options()
    {
        return response()->json([
            'users' => User::query()->where('role', 'user')->orderBy('name')->get(['id', 'name', 'email']),
            'workers' => Worker::query()->with('service')->where('verified', true)->orderBy('name')->get(),
            'services' => Service::query()->orderBy('name')->get(),
        ]);
    }

    public function storeManualBooking(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'worker_id' => ['required', 'exists:workers,id'],
            'service_id' => ['required', 'exists:services,id'],
            'amount' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:pending,in_progress,completed,cancelled'],
        ]);

        $booking = Booking::query()->create($data)->load(['user', 'worker', 'service']);

        return response()->json([
            'message' => 'Manual booking created.',
            'booking' => $booking,
        ], 201);
    }

    public function export(): StreamedResponse
    {
        $filename = 'sharphand-bookings-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['User', 'Email', 'Worker', 'Service', 'Amount', 'Status', 'Created']);

            Booking::query()
                ->with(['user', 'worker', 'service'])
                ->latest()
                ->chunk(200, function ($chunk) use ($handle) {
                    foreach ($chunk as $booking) {
                        fputcsv($handle, [
                            $booking->user->name,
                            $booking->user->email,
                            $booking->worker->name,
                            $booking->service->name,
                            $booking->amount,
                            $booking->status->value,
                            $booking->created_at->toDateTimeString(),
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
