<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactMessageRequest;
use App\Models\ContactMessage;

class ContactController extends Controller
{
    public function store(StoreContactMessageRequest $request)
    {
        ContactMessage::query()->create($request->validated());

        return response()->json([
            'message' => 'Thanks — your message is on its way. We typically reply within 24 hours.',
        ], 201);
    }
}
