<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

use App\Http\Controllers\Api\XpressbeesWebhookController;
Route::post('/xpressbees/webhook', [XpressbeesWebhookController::class, 'handleWebhook'])->name('api.xpressbees.webhook');
