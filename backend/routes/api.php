<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\RendezVousController;
use App\Http\Controllers\MessagesController;

// Routes pour l'authentification
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/auth/google', [GoogleAuthController::class, 'redirect']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']);

// Routes pour les rendez-vous
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/rendez-vous', [RendezVousController::class, 'index']);
    Route::get('/rendez-vous/today', [RendezVousController::class, 'today']);
    Route::get('/rendez-vous/{id}', [RendezVousController::class, 'show']);
    Route::post('/rendez-vous', [RendezVousController::class, 'store']);
    Route::put('/rendez-vous/{id}', [RendezVousController::class, 'update']);
    Route::delete('/rendez-vous/{id}', [RendezVousController::class, 'destroy']);
    Route::get('/rendez-vous/period', [RendezVousController::class, 'byPeriod']);
    Route::put('/rendez-vous/{id}/confirm', [RendezVousController::class, 'confirm']);
    Route::put('/rendez-vous/{id}/cancel', [RendezVousController::class, 'cancel']);
});

// Routes pour les messages
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/messages', [MessagesController::class, 'index']);
    Route::get('/messages/{id}', [MessagesController::class, 'show']);
    Route::post('/messages', [MessagesController::class, 'store']);
    Route::put('/messages/{id}/read', [MessagesController::class, 'markAsRead']);
    Route::get('/messages/unread', [MessagesController::class, 'unread']);
    Route::get('/messages/conversations', [MessagesController::class, 'conversations']);
    Route::delete('/messages/{id}', [MessagesController::class, 'destroy']);
});
