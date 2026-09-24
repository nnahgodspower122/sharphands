<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use Illuminate\Http\Request;

class WorkerController extends Controller
{
    public function index(Request $request)
    {
        $workers = Worker::query()
            ->with(['service', 'user'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->string('q');
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.workers', compact('workers'));
    }

    public function approve(Worker $worker)
    {
        $worker->update(['verified' => true]);

        return back()->with('status', $worker->name.' has been verified.');
    }

    public function reject(Worker $worker)
    {
        $name = $worker->name;
        $worker->user()->delete();

        return back()->with('status', $name.' was rejected and removed.');
    }
}
