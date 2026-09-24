<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Models\ContactMessage;

class ContactController extends Controller
{
    public function store(StoreContactMessageRequest $request)
    {
        ContactMessage::query()->create($request->validated());

        return back()->with('status', 'Thanks — your message is on its way. We typically reply within 24 hours.');
    }
}
