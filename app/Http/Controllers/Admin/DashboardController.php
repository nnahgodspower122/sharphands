<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $bookings = Booking::query()
            ->with(['user', 'worker.service', 'service'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->whereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"))
                        ->orWhereHas('worker', fn ($worker) => $worker->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(5)
            ->withQueryString();

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

        $performance = [
            'user_growth' => min(100, (int) round(($stats['users'] / max($stats['users'] + 5, 20)) * 100) + 40),
            'verification' => (int) round(($verified / $totalWorkers) * 100),
            'revenue' => $stats['revenue'],
            'revenue_target' => 1500000,
            'completion' => (int) round(($completed / $totalBookings) * 100),
        ];

        return view('admin.dashboard', compact('bookings', 'stats', 'queue', 'performance', 'search'));
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

    public function createManualBooking()
    {
        return view('admin.bookings-create', [
            'users' => User::query()->where('role', 'user')->orderBy('name')->get(),
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

        Booking::query()->create($data);

        return redirect()->route('admin.dashboard')->with('status', 'Manual booking created.');
    }
}
