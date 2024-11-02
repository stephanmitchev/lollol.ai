<?php

use App\Http\Controllers\API\AIController;
use App\Http\Controllers\API\AnalyticsPingController;
use App\Http\Controllers\API\ApifyWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/tag', [AnalyticsPingController::class, 'tag'])->name('tag');

Route::post('/ping', [AnalyticsPingController::class, 'ping'])->name('ping');

Route::post('/submitResults', [ApifyWebhookController::class, 'submitResults'])->name('submitResults');

Route::post('/suggest', [AIController::class, 'suggest'])->name('suggest');
