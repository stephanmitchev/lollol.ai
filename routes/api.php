<?php

use App\Http\Controllers\API\AIController;
use App\Http\Controllers\API\AnalyticsPingController;
use App\Http\Controllers\API\ApifyWebhookController;
use App\Http\Controllers\IntegrationController;
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

Route::get('/integration/js', [IntegrationController::class, 'js'])->name('integration.js');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});