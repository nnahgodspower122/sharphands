<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWorker
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isWorker() || ! $user->worker) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Worker account required.'], 403);
            }

            return redirect()->route('worker.register');
        }

        return $next($request);
    }
}
