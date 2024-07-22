<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use app\Http\Controllers\MessageController;

Auth::routes();

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/messages', function () {
        return view('chat');
    });
    Route::get('/api/messages', [MessageController::class, 'readAll']);
    Route::get('/api/messages/{identifier}', [MessageController::class, 'read']);
    Route::post('/api/messages', [MessageController::class, 'send']);
    Route::delete('/api/messages/{identifier}', [MessageController::class, 'delete']);
    
    // Route::get('/', 'ChatsController@index');
    // Route::get('messages', 'ChatsController@fetchMessages');
    // Route::post('messages', 'ChatsController@sendMessage');
});