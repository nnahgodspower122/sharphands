<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
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
            ->paginate(12)
            ->withQueryString();

        return view('admin.users', compact('users'));
    }
}
