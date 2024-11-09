<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::get('/chat', [ChatController::class, 'redirect'])->name('redirect');
Route::get('/', [ChatController::class, 'index'])->name('chat.index');
Route::get('/privacy', [ChatController::class, 'privacy'])->name('chat.privacy');

