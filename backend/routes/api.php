<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\RendezVousController;
use App\Http\Controllers\MessagesController;
use App\Http\Controllers\EvenementsController;
use App\Http\Controllers\AppelsController;
use App\Http\Controllers\AudiManangerController;
use App\Http\Controllers\CalendarController;

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

// Routes pour les événements
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/evenements', [EvenementsController::class, 'index']);
    Route::get('/evenements/{id}', [EvenementsController::class, 'show']);
    Route::post('/evenements', [EvenementsController::class, 'store']);
    Route::put('/evenements/{id}', [EvenementsController::class, 'update']);
    Route::delete('/evenements/{id}', [EvenementsController::class, 'destroy']);
    Route::get('/evenements/period', [EvenementsController::class, 'byPeriod']);
    Route::put('/evenements/{id}/confirm', [EvenementsController::class, 'confirm']);
    Route::put('/evenements/{id}/cancel', [EvenementsController::class, 'cancel']);
});

// Routes pour les appels
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/appels', [AppelsController::class, 'index']);
    Route::get('/appels/{id}', [AppelsController::class, 'show']);
    Route::post('/appels', [AppelsController::class, 'store']);
    Route::put('/appels/{id}', [AppelsController::class, 'update']);
    Route::delete('/appels/{id}', [AppelsController::class, 'destroy']);
    Route::get('/appels/recording/{id}', [AppelsController::class, 'getRecording']);
    Route::post('/appels/{id}/favorite', [AppelsController::class, 'toggleFavorite']);
});

// Routes pour l'audio manager
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/audio', [AudiManangerController::class, 'index']);
    Route::get('/audio/{id}', [AudiManangerController::class, 'show']);
    Route::post('/audio', [AudiManangerController::class, 'store']);
    Route::put('/audio/{id}', [AudiManangerController::class, 'update']);
    Route::delete('/audio/{id}', [AudiManangerController::class, 'destroy']);
    Route::get('/audio/download/{id}', [AudiManangerController::class, 'download']);
    Route::post('/audio/{id}/favorite', [AudiManangerController::class, 'toggleFavorite']);
    Route::get('/audio/transcription/{id}', [AudiManangerController::class, 'getTranscription']);
});

// Routes pour le calendrier
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/calendar', [CalendarController::class, 'index']);
    Route::get('/calendar/{id}', [CalendarController::class, 'show']);
    Route::post('/calendar', [CalendarController::class, 'store']);
    Route::put('/calendar/{id}', [CalendarController::class, 'update']);
    Route::delete('/calendar/{id}', [CalendarController::class, 'destroy']);
    Route::get('/calendar/month/{year}/{month}', [CalendarController::class, 'getMonth']);
    Route::get('/calendar/week/{year}/{week}', [CalendarController::class, 'getWeek']);
});
