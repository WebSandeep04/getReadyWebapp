<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\FabricTypeController;
use App\Http\Controllers\Api\ColorController;
use App\Http\Controllers\Api\SizeController;
use App\Http\Controllers\Api\BodyFitController;
use App\Http\Controllers\Api\GarmentConditionController;
use App\Http\Controllers\Api\ClothApiController;
use App\Http\Controllers\Api\LoginApiController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\CartApiController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\NotificationApiController;
use App\Http\Controllers\Api\RejectionApiController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

use App\Http\Controllers\Api\XpressbeesWebhookController;
Route::post('/xpressbees/webhook', [XpressbeesWebhookController::class, 'handleWebhook'])->name('api.xpressbees.webhook');

Route::get('/brands', [BrandController::class, 'index']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/fabric-types', [FabricTypeController::class, 'index']);
Route::get('/colors', [ColorController::class, 'index']);
Route::get('/sizes', [SizeController::class, 'index']);
Route::get('/body-fits', [BodyFitController::class, 'index']);
Route::get('/garment-conditions', [GarmentConditionController::class, 'index']);
Route::post('/outfits', [ClothApiController::class, 'store']);
Route::get('/home-feed', [ClothApiController::class, 'getHomeFeed']);
Route::get('/clothes/{id}', [ClothApiController::class, 'apiShow']);
Route::post('/clothes/{id}/update', [ClothApiController::class, 'update']);
Route::post('/clothes/{id}/availability', [ClothApiController::class, 'updateAvailability']);
Route::delete('/clothes/{id}', [ClothApiController::class, 'destroy']);
Route::delete('/clothes/image/{imageId}', [ClothApiController::class, 'destroyImage']);
    


Route::prefix('auth')->group(function () {
    Route::post('/login/send-otp', [LoginApiController::class, 'sendOtp']);
    Route::post('/login/verify-otp', [LoginApiController::class, 'verifyOtp']);
    Route::post('/login/complete-registration', [LoginApiController::class, 'completeRegistration']);
    
    // Protected route for logout
    Route::middleware('auth:sanctum')->post('/logout', [LoginApiController::class, 'logout']);
});


// Make sure this is inside your authenticated route group
Route::middleware('auth:sanctum')->group(function () {
    
    // Fetch logged-in user details
    Route::get('/profile', [UserApiController::class, 'getProfile']);
    Route::get('/my-clothes', [ClothApiController::class, 'myClothes']);
    
    // Update profile (your existing method)
    Route::post('/profile/update', [UserApiController::class, 'updateProfile']);
    
    
});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/cart', [CartApiController::class, 'index']);
    Route::post('/cart/add', [CartApiController::class, 'addToCart']);
    Route::post('/cart/remove', [CartApiController::class, 'removeFromCart']);
    Route::post('/cart/update-quantity', [CartApiController::class, 'updateQuantity']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/orders', [OrderApiController::class, 'index']);
    Route::post('/orders/create', [OrderApiController::class, 'store']);
    Route::post('/orders/verify', [OrderApiController::class, 'verifyPayment']);

    // Action Endpoints (POST)
    Route::post('/orders/{id}/cancel', [OrderApiController::class, 'cancel']);
    Route::post('/orders/{id}/return', [OrderApiController::class, 'initiateReturn']);
    Route::post('/orders/{id}/rate', [OrderApiController::class, 'rate']);

    // Checkout / Purchase Endpoints
    Route::get('/orders/{id}/purchase-eligibility', [OrderApiController::class, 'purchaseEligibility']);
    Route::post('/orders/{id}/convert-to-purchase', [OrderApiController::class, 'convertToPurchase']);
    Route::get('/orders/{id}/extension-quote', [OrderApiController::class, 'extensionQuote']);
    Route::post('/orders/{id}/extend', [OrderApiController::class, 'extend']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [NotificationApiController::class, 'index']);
    Route::post('/notifications/read', [NotificationApiController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationApiController::class, 'markAllAsRead']);
    Route::get('/notifications/unread-count', [NotificationApiController::class, 'getUnreadCount']);

    // Rejection Endpoints
    Route::get('/rejections', [RejectionApiController::class, 'index']);
    Route::get('/rejections/form-data', [RejectionApiController::class, 'getFormData']);
    Route::get('/rejections/{id}', [RejectionApiController::class, 'show']);
    Route::post('/rejections/{id}', [RejectionApiController::class, 'update']);
});
