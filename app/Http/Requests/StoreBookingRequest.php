<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $plain = $this->bearerToken();

        if (! $plain || $this->user()) {
            return;
        }

        $user = \App\Models\User::query()->where('api_token', hash('sha256', $plain))->first();

        if ($user) {
            $this->setUserResolver(fn () => $user);
        }
    }

    public function rules(): array
    {
        $guest = ! $this->user();

        return [
            'service_id' => ['required', 'exists:services,id'],
            'worker_id' => ['nullable', 'exists:workers,id'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'name' => [$guest ? 'required' : 'nullable', 'string', 'max:255'],
            'phone' => [$guest ? 'required' : 'nullable', 'string', 'max:30'],
        ];
    }
}
