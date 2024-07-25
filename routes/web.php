<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\ChatController;


Route::get('/', function () {
    return view('landing');
})->name('login');

Route::post('/chat/login', [ChatController::class, 'login'])
    ->name('chat.login')
    ->middleware(['throttle:5,1']);

Route::get('/chat', function () {
    return view('chat');
})
    ->name('chat')
    ->middleware('auth');

Route::post('/chat/send-message', [ChatController::class, 'store'])
    ->name('chat.send')
    ->middleware('auth')
    ->middleware(['throttle:30,1,5,1']);

Route::get('/track/{id}', [TrackController::class, 'getTrack'])
	->name('track')
    ->middleware('auth')
    ->middleware(['throttle:10,1']);
