<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\GenericController;
use App\Http\Controllers\IntegrationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/chat', [GenericController::class, 'empty'])->name('empty');
Route::get('/', [ChatController::class, 'index'])->name('chat.index');
Route::get('/privacy', [ChatController::class, 'privacy'])->name('chat.privacy');

Route::get('/integration/js', [IntegrationController::class, 'js'])->name('integration.js   ');


/*
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
*/
require __DIR__.'/auth.php';
