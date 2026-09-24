<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\WorkerController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/nearby', [ServiceController::class, 'nearby']);
Route::get('/workers/nearby', [WorkerController::class, 'nearby']);
Route::post('/book', [BookingController::class, 'store']);
Route::post('/worker/register', [WorkerController::class, 'register']);
Route::post('/contact', [ContactController::class, 'store']);

Route::middleware('api.token')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings/{booking}/rate', [BookingController::class, 'rate']);
    Route::match(['get', 'post'], '/worker/availability', [WorkerController::class, 'availability']);
    Route::post('/worker/update-location', [WorkerController::class, 'updateLocation']);
    Route::get('/worker/dashboard', [WorkerController::class, 'dashboard']);
    Route::patch('/worker/bookings/{booking}', [WorkerController::class, 'updateBooking']);

    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/users', [AdminController::class, 'users']);
        Route::get('/workers', [AdminController::class, 'workers']);
        Route::get('/bookings', [AdminController::class, 'bookings']);
        Route::get('/options', [AdminController::class, 'options']);
        Route::get('/export', [AdminController::class, 'export']);
        Route::post('/bookings/manual', [AdminController::class, 'storeManualBooking']);
        Route::post('/workers/{worker}/approve', [AdminController::class, 'approve']);
        Route::post('/workers/{worker}/reject', [AdminController::class, 'reject']);
        Route::patch('/bookings/{booking}', [AdminController::class, 'updateBooking']);
    });
});
