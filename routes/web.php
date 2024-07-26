<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\ChatController;

// homepage with login prompt
Route::get('/', function () {
    return view('landing');
})->name('login');

// action of logging in
Route::post('/chat/login', [ChatController::class, 'login'])
    ->name('chat.login')
    ->middleware(['throttle:5,1']);

// main chatroom view
Route::get('/chat', function () {
    return view('chat');
})
    ->name('chat')
    ->middleware('auth');

// action of sending a message
Route::post('/chat/send-message', [ChatController::class, 'store'])
    ->name('chat.send')
    ->middleware('auth')
    ->middleware(['throttle:45,1,10,1']);

// streaming of a track audio file
Route::get('/track/{id}', [TrackController::class, 'getTrack'])
	->name('track')
    ->middleware('auth')
	->middleware('signed')
    ->middleware(['throttle:15,1']);
