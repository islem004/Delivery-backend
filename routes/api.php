<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\Api\DeliveryItemController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\NotificationController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    // Client profile
    Route::get('/client/profile',    [ClientController::class, 'show']);
    Route::put('/client/profile',    [ClientController::class, 'update']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Deliveries
    Route::get('/deliveries',         [DeliveryController::class, 'index']);
    Route::post('/deliveries',        [DeliveryController::class, 'store']);
    Route::get('/deliveries/{id}',    [DeliveryController::class, 'show']);
    Route::put('/deliveries/{id}',    [DeliveryController::class, 'update']);
    Route::delete('/deliveries/{id}', [DeliveryController::class, 'destroy']);
    Route::get('/deliveries/{id}/track', [DeliveryController::class, 'track']);

    // Delivery Items
    Route::post('/deliveries/{deliveryId}/items',             [DeliveryItemController::class, 'store']);
    Route::put('/deliveries/{deliveryId}/items/{itemId}',     [DeliveryItemController::class, 'update']);
    Route::delete('/deliveries/{deliveryId}/items/{itemId}',  [DeliveryItemController::class, 'destroy']);

    // Invoices
    Route::get('/invoices',         [InvoiceController::class, 'index']);
    Route::get('/invoices/filter',  [InvoiceController::class, 'filter']);
    Route::get('/invoices/{id}',    [InvoiceController::class, 'show']);

    // Notifications
    Route::get('/notifications',              [NotificationController::class, 'index']);
    Route::get('/notifications/unread',       [NotificationController::class, 'unread']);
    Route::put('/notifications/{id}/read',    [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/read-all',     [NotificationController::class, 'markAllAsRead']);
});