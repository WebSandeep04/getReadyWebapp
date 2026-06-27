<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AadhaarVerificationController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\BodyTypeFitController as AdminBodyTypeFitController;
use App\Http\Controllers\Admin\BottomTypeController as AdminBottomTypeController;
use App\Http\Controllers\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CityController as AdminCityController;
use App\Http\Controllers\Admin\ClothController as AdminClothController;
use App\Http\Controllers\Admin\ColorController as AdminColorController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FabricTypeController as AdminFabricTypeController;
use App\Http\Controllers\Admin\FrontendController as AdminFrontendController;
use App\Http\Controllers\Admin\GarmentConditionController as AdminGarmentConditionController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PanelUserController as AdminPanelUserController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\PayoutController as AdminPayoutController;
use App\Http\Controllers\Admin\PromptController as AdminPromptController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\RoleMasterController as AdminRoleMasterController;
use App\Http\Controllers\Admin\SecurityController as AdminSecurityController;
use App\Http\Controllers\Admin\SizeController as AdminSizeController;
use App\Http\Controllers\Admin\StateController as AdminStateController;
use App\Http\Controllers\Admin\TaxController as AdminTaxController;
use App\Http\Controllers\ClothController;
use App\Http\Controllers\GeminiController;
use App\Http\Controllers\GstVerificationController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\OrderReturnController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\RejectionController;
use App\Http\Controllers\VirtualTryOnController;

use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderExtensionController;
use App\Http\Controllers\OrderConversionController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ReplyController;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Registration (Moved to Login/OTP flow)
Route::post('/complete-registration', [LoginController::class, 'completeRegistration'])->name('complete.registration');

// Login
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

// Logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// GST Verification
Route::post('/verify-gst', [GstVerificationController::class, 'verifyGst'])->name('verify.gst');

// Aadhaar KYC Verification (IM Wallet)
Route::post('/aadhaar/start', [AadhaarVerificationController::class, 'startKyc'])->name('aadhaar.start');
Route::any('/aadhaar/callback', [AadhaarVerificationController::class, 'callback'])->name('aadhaar.callback');

// Profile (Requires Authentication)
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [UserController::class, 'updateProfile'])->name('profile.update');
});

// Sell (Requires Authentication)
Route::middleware(['auth'])->group(function () {
    Route::get('/sell', [ClothController::class, 'create'])->name('sell');
    Route::post('/sell', [ClothController::class, 'store'])->name('sell.store');
    Route::post('/generate-description', [GeminiController::class, 'generateDescription'])->name('generate.description');
});

// Virtual Try On (Public/Auth decided by user, we can make it public since show.blade.php is public)
Route::post('/clothes/virtual-try-on', [VirtualTryOnController::class, 'generate'])->name('clothes.virtual-try-on');
Route::get('/clothes/virtual-try-on/status/{id}', [VirtualTryOnController::class, 'status'])->name('api.vto.status');

// Admin Auth
Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// Protected Admin Routes
Route::middleware(['admin.auth'])->group(function () {
    Route::get('/admin', function() {
        return redirect()->route('admin.dashboard');
    });

    // Admin Dashboard
    // Admin Dashboard
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // Cloth approval workspace
    Route::get('/admin/cloth-approval', [AdminClothController::class, 'clothApproval'])->name('admin.cloth-approval');
    Route::get('/admin/orders', [AdminOrderController::class, 'index'])->name('admin.orders');
    Route::get('/admin/orders/data', [AdminOrderController::class, 'ordersData'])->name('admin.orders.data');
    Route::post('/admin/orders/{id}/return', [AdminOrderController::class, 'markAsReturned'])->name('admin.orders.return');
    Route::post('/admin/orders/{id}/status', [AdminOrderController::class, 'updateStatus'])->name('admin.orders.status');
    Route::post('/admin/orders/{id}/retry-shipment', [AdminOrderController::class, 'retryShipment'])->name('admin.orders.retry-shipment');
    Route::post('/admin/orders/{id}/approve-return', [AdminOrderController::class, 'approveOrderReturn'])->name('admin.orders.approve-return');
    Route::post('/admin/orders/{id}/reject-return', [AdminOrderController::class, 'rejectOrderReturn'])->name('admin.orders.reject-return');
    Route::post('/admin/orders/{id}/refund-payment', [AdminPaymentController::class, 'refundOrderPayment'])->name('admin.orders.refund-payment');

    // User
    Route::get('/admin/user/fetch', [UserController::class, 'fetch'])->name('user.fetch');
    Route::post('/admin/user/update/{id}', [UserController::class, 'update'])->name('user.update');
    Route::delete('/admin/user/delete/{id}', [UserController::class, 'destroy'])->name('user.delete');  

    // Clothes Approval (Admin)
    Route::get('/admin/clothes/fetch', [AdminClothController::class, 'fetchClothes'])->name('clothes.fetch');
    Route::post('/admin/clothes/approve/{id}', [AdminClothController::class, 'approveCloth'])->name('clothes.approve');
    Route::post('/admin/clothes/reject/{id}', [AdminClothController::class, 'rejectCloth'])->name('clothes.reject');  
    Route::get('/admin/clothes/reject-reason/{id}', [AdminClothController::class, 'getRejectionReason'])->name('clothes.reject-reason');
    Route::post('/admin/clothes/{id}/images', [AdminClothController::class, 'uploadImages'])->name('admin.clothes.images.upload');
    Route::delete('/admin/clothes/images/{imageId}', [AdminClothController::class, 'destroyImage'])->name('admin.clothes.images.destroy');

    // Dashboard stats (Admin)
    Route::get('/admin/dashboard/stats', [AdminDashboardController::class, 'dashboardStats']);
    Route::get('/admin/dashboard/orders/fetch', [AdminDashboardController::class, 'fetchOrders'])->name('admin.dashboard.orders.fetch');
    Route::get('/admin/dashboard/payments/fetch', [AdminPaymentController::class, 'fetchDashboardPayments'])->name('admin.dashboard.payments.fetch');
    Route::get('/admin/dashboard/security/fetch', [AdminSecurityController::class, 'fetchData'])->name('admin.dashboard.security.fetch');
    Route::get('/admin/dashboard/payouts/fetch', [AdminPayoutController::class, 'fetchData'])->name('admin.dashboard.payouts.fetch');
    Route::get('/admin/payouts', [AdminPayoutController::class, 'index'])->name('admin.payouts');
    Route::get('/admin/payouts/fetch', [AdminPayoutController::class, 'fetchData'])->name('admin.payouts.fetch');
    Route::post('/admin/payouts/mark-paid/{id}', [AdminPayoutController::class, 'markPaid'])->name('admin.payouts.mark-paid');

    // Security Deposit Management
    Route::get('/admin/security', [AdminSecurityController::class, 'index'])->name('admin.security');
    Route::get('/admin/security/fetch', [AdminSecurityController::class, 'fetchData'])->name('admin.security.fetch');
    Route::post('/admin/security/mark-returned/{id}', [AdminSecurityController::class, 'markAsReturned'])->name('admin.security.mark-returned');

    // Payment Management
    Route::get('/admin/payments', [AdminPaymentController::class, 'index'])->name('admin.payments');
    Route::get('/admin/payments/fetch', [AdminPaymentController::class, 'fetchData'])->name('admin.payments.fetch');

    Route::post('/admin/orders/{id}/process-issue-refund', [AdminPaymentController::class, 'processIssueRefund'])->name('admin.orders.process-issue-refund');

    // Reports
    Route::get('/admin/reports/financial', [AdminReportController::class, 'financial'])->name('admin.reports.financial');
    Route::get('/admin/reports/calendar', [AdminReportController::class, 'calendar'])->name('admin.reports.calendar');
    
    // Frontend Management (Admin)
    Route::get('/admin/frontend', [AdminFrontendController::class, 'frontend'])->name('admin.frontend');
    Route::post('/admin/frontend/update', [AdminFrontendController::class, 'updateFrontendSetting'])->name('admin.frontend.update');
    Route::get('/admin/frontend/settings/{section}', [AdminFrontendController::class, 'getFrontendSettings'])->name('admin.frontend.settings');  

    // Category Management (Admin)
    Route::get('/admin/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
    Route::post('/admin/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
    Route::put('/admin/categories/{id}', [AdminCategoryController::class, 'update'])->name('categories.update');
    Route::delete('/admin/categories/{id}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');  
    Route::get('/admin/categories/json', [AdminCategoryController::class, 'json'])->name('categories.json');

    // Fabric Type Management (Admin)
    Route::get('/admin/fabric-types', [AdminFabricTypeController::class, 'index'])->name('fabric_types.index');
    Route::get('/admin/fabric-types/json', [AdminFabricTypeController::class, 'json'])->name('fabric_types.json');
    Route::post('/admin/fabric-types', [AdminFabricTypeController::class, 'store'])->name('fabric_types.store');
    Route::put('/admin/fabric-types/{id}', [AdminFabricTypeController::class, 'update'])->name('fabric_types.update');
    Route::delete('/admin/fabric-types/{id}', [AdminFabricTypeController::class, 'destroy'])->name('fabric_types.destroy');

    // Color Management (Admin)
    Route::get('/admin/colors', [AdminColorController::class, 'index'])->name('colors.index');
    Route::get('/admin/colors/json', [AdminColorController::class, 'json'])->name('colors.json');
    Route::post('/admin/colors', [AdminColorController::class, 'store'])->name('colors.store');
    Route::put('/admin/colors/{id}', [AdminColorController::class, 'update'])->name('colors.update');
    Route::delete('/admin/colors/{id}', [AdminColorController::class, 'destroy'])->name('colors.destroy');

    // Brand Management (Admin)
    Route::get('/admin/brands', [AdminBrandController::class, 'index'])->name('brands.index');
    Route::get('/admin/brands/json', [AdminBrandController::class, 'json'])->name('brands.json');
    Route::post('/admin/brands', [AdminBrandController::class, 'store'])->name('brands.store');
    Route::put('/admin/brands/{id}', [AdminBrandController::class, 'update'])->name('brands.update');
    Route::delete('/admin/brands/{id}', [AdminBrandController::class, 'destroy'])->name('brands.destroy');

    // Bottom Type Management (Admin)
    Route::get('/admin/bottom-types', [AdminBottomTypeController::class, 'index'])->name('bottom_types.index');
    Route::get('/admin/bottom-types/json', [AdminBottomTypeController::class, 'json'])->name('bottom_types.json');
    Route::post('/admin/bottom-types', [AdminBottomTypeController::class, 'store'])->name('bottom_types.store');
    Route::put('/admin/bottom-types/{id}', [AdminBottomTypeController::class, 'update'])->name('bottom_types.update');
    Route::delete('/admin/bottom-types/{id}', [AdminBottomTypeController::class, 'destroy'])->name('bottom_types.destroy');

    // Size Management (Admin)
    Route::get('/admin/sizes', [AdminSizeController::class, 'index'])->name('sizes.index');
    Route::get('/admin/sizes/json', [AdminSizeController::class, 'json'])->name('sizes.json');
    Route::post('/admin/sizes', [AdminSizeController::class, 'store'])->name('sizes.store');
    Route::put('/admin/sizes/{id}', [AdminSizeController::class, 'update'])->name('sizes.update');
    Route::delete('/admin/sizes/{id}', [AdminSizeController::class, 'destroy'])->name('sizes.destroy');

    // Body Type Fit Management (Admin)
    Route::get('/admin/body-type-fits', [AdminBodyTypeFitController::class, 'index'])->name('body_type_fits.index');
    Route::get('/admin/body-type-fits/json', [AdminBodyTypeFitController::class, 'json'])->name('body_type_fits.json');
    Route::post('/admin/body-type-fits', [AdminBodyTypeFitController::class, 'store'])->name('body_type_fits.store');
    Route::put('/admin/body-type-fits/{id}', [AdminBodyTypeFitController::class, 'update'])->name('body_type_fits.update');
    Route::delete('/admin/body-type-fits/{id}', [AdminBodyTypeFitController::class, 'destroy'])->name('body_type_fits.destroy');

    // Garment Condition Management (Admin)
    Route::get('/admin/garment-conditions', [AdminGarmentConditionController::class, 'index'])->name('garment_conditions.index');
    Route::get('/admin/garment-conditions/json', [AdminGarmentConditionController::class, 'json'])->name('garment_conditions.json');
    Route::post('/admin/garment-conditions', [AdminGarmentConditionController::class, 'store'])->name('garment_conditions.store');
    Route::put('/admin/garment-conditions/{id}', [AdminGarmentConditionController::class, 'update'])->name('garment_conditions.update');
    Route::delete('/admin/garment-conditions/{id}', [AdminGarmentConditionController::class, 'destroy'])->name('garment_conditions.destroy');

    // Admin Panel User Management
    Route::get('/admin/panel-users', [AdminPanelUserController::class, 'index'])->name('admin_panel_users.index');
    Route::get('/admin/panel-users/json', [AdminPanelUserController::class, 'json'])->name('admin_panel_users.json');
    Route::post('/admin/panel-users', [AdminPanelUserController::class, 'store'])->name('admin_panel_users.store');
    Route::put('/admin/panel-users/{id}', [AdminPanelUserController::class, 'update'])->name('admin_panel_users.update');
    Route::delete('/admin/panel-users/{id}', [AdminPanelUserController::class, 'destroy'])->name('admin_panel_users.destroy');

    // State Management (Admin)
    Route::get('/admin/states', [AdminStateController::class, 'index'])->name('states.index');
    Route::post('/admin/states', [AdminStateController::class, 'store'])->name('states.store');
    Route::put('/admin/states/{id}', [AdminStateController::class, 'update'])->name('states.update');
    Route::delete('/admin/states/{id}', [AdminStateController::class, 'destroy'])->name('states.destroy');
    Route::post('/admin/states/toggle/{id}', [AdminStateController::class, 'toggleStatus'])->name('states.toggle');

    // City Management (Admin)
    Route::get('/admin/cities', [AdminCityController::class, 'index'])->name('cities.index');
    Route::post('/admin/cities', [AdminCityController::class, 'store'])->name('cities.store');
    Route::put('/admin/cities/{id}', [AdminCityController::class, 'update'])->name('cities.update');
    Route::delete('/admin/cities/{id}', [AdminCityController::class, 'destroy'])->name('cities.destroy');
    Route::post('/admin/cities/toggle/{id}', [AdminCityController::class, 'toggleStatus'])->name('cities.toggle');

    // Tax Management (Admin)
    Route::get('/admin/tax-management', [AdminTaxController::class, 'index'])->name('admin.tax');
    Route::get('/admin/tax/json', [AdminTaxController::class, 'json'])->name('admin.tax.json');
    Route::post('/admin/tax', [AdminTaxController::class, 'store'])->name('admin.tax.store');
    Route::put('/admin/tax/{id}', [AdminTaxController::class, 'update'])->name('admin.tax.update');
    Route::delete('/admin/tax/{id}', [AdminTaxController::class, 'destroy'])->name('admin.tax.destroy');
    Route::post('/admin/tax/toggle/{id}', [AdminTaxController::class, 'toggleStatus'])->name('admin.tax.toggle');

    // Role Master
    Route::get('/admin/role-master', [AdminRoleMasterController::class, 'index'])->name('role_master.index');
    Route::get('/admin/role-master/permissions/{id}', [AdminRoleMasterController::class, 'getRolePermissions'])->name('role_master.permissions');
    Route::post('/admin/role-master/save', [AdminRoleMasterController::class, 'saveRolePermissions'])->name('role_master.save');
     Route::post('/admin/role-master/store', [AdminRoleMasterController::class, 'store'])->name('role_master.store');

    // Prompt Management (Admin)
    Route::get('/admin/prompts', [AdminPromptController::class, 'index'])->name('prompts.index');
    Route::get('/admin/prompts/json', [AdminPromptController::class, 'json'])->name('prompts.json');
    Route::post('/admin/prompts', [AdminPromptController::class, 'store'])->name('prompts.store');
    Route::put('/admin/prompts/{id}', [AdminPromptController::class, 'update'])->name('prompts.update');
    Route::delete('/admin/prompts/{id}', [AdminPromptController::class, 'destroy'])->name('prompts.destroy');
});

// Public API routes for mobile/frontend
Route::get('/admin/states/json', [AdminStateController::class, 'json'])->name('states.json');
Route::get('/admin/cities/json', [AdminCityController::class, 'json'])->name('cities.json');

// User (Non-admin route, kept outside)
Route::get('/user', [UserController::class, 'index'])->name('user.index');

// Product Page
Route::get('/product', [ProductController::class, 'index'])->name('product');
Route::get('/clothes', [ProductController::class, 'index'])->name('clothes.index');
Route::get('/clothes/{id}', [ClothController::class, 'show'])->name('clothes.show');
Route::get('/ajax-search', [ProductController::class, 'ajaxSearch'])->name('search.ajax');

// Product Reviews and Questions (Requires Authentication)
Route::middleware(['auth'])->group(function () {
    // Reviews
    Route::post('/clothes/{clothId}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    
    // Questions
    Route::post('/clothes/{clothId}/questions', [QuestionController::class, 'store'])->name('questions.store');
    Route::post('/questions/{id}/answer', [QuestionController::class, 'answer'])->name('questions.answer');
    Route::delete('/questions/{id}', [QuestionController::class, 'destroy'])->name('questions.destroy');

    // Replies
    Route::post('/questions/{id}/reply', [ReplyController::class, 'storeQuestionReply'])->name('questions.reply');
    Route::post('/reviews/{id}/reply', [ReplyController::class, 'storeReviewReply'])->name('reviews.reply');
    Route::delete('/replies/{id}', [ReplyController::class, 'destroy'])->name('replies.destroy');
});

// Cart
Route::get('/cart', [CartController::class, 'index'])->name('cart');

// Cart AJAX routes (Requires Authentication)
Route::middleware(['auth'])->group(function () {
    Route::post('/cart/add', [CartController::class, 'addToCart'])->name('cart.add');
    Route::post('/cart/remove', [CartController::class, 'removeFromCart'])->name('cart.remove');
    Route::post('/cart/update-quantity', [CartController::class, 'updateQuantity'])->name('cart.update-quantity');
    Route::post('/checkout/create-order', [CheckoutController::class, 'createOrder'])->name('checkout.create');
    Route::post('/checkout/verify', [CheckoutController::class, 'verifyPayment'])->name('checkout.verify');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/my-sales', [OrderController::class, 'sales'])->name('orders.sales');
    Route::get('/transactions', [OrderController::class, 'transactions'])->name('transactions.index');
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::post('/ratings', [RatingController::class, 'store'])->name('ratings.store');
    Route::post('/orders/{id}/return-request', [OrderReturnController::class, 'store'])->name('orders.return-request');
    Route::post('/orders/{id}/early-return', [OrderReturnController::class, 'earlyReturn'])->name('orders.early-return');

    // Rental Extensions
    Route::get('/orders/{id}/extension-quote', [OrderExtensionController::class, 'quote'])->name('orders.extension.quote');
    Route::post('/orders/{id}/extend', [OrderExtensionController::class, 'extend'])->name('orders.extend');
    Route::post('/orders/extension/verify', [OrderExtensionController::class, 'verifyPayment'])->name('orders.extension.verify');
    
    // Mid-Rental Purchase (Conversions)
    Route::get('/orders/{id}/purchase-eligibility', [OrderConversionController::class, 'eligibility'])->name('orders.conversion.eligibility');
    Route::post('/orders/{id}/convert-to-purchase', [OrderConversionController::class, 'convertToPurchase'])->name('orders.convert');
    Route::post('/orders/conversion/verify', [OrderConversionController::class, 'verifyConversion'])->name('orders.conversion.verify');
});

// Get cart count (for header)
Route::get('/cart/count', [CartController::class, 'getCartCount'])->name('cart.count');

// Get cart items (for checking rented status)
Route::get('/cart/items', [CartController::class, 'getCartItems'])->name('cart.items');

// Notifications (Requires Authentication)
Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'getNotifications'])->name('notifications.get');
    Route::post('/notifications/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
});

// Rejection Management (Requires Authentication)
Route::middleware(['auth'])->group(function () {
    Route::get('/rejections', [RejectionController::class, 'index'])->name('rejections.index');
    Route::get('/rejections/{id}', [RejectionController::class, 'show'])->name('rejections.show');
    Route::put('/rejections/{id}', [RejectionController::class, 'update'])->name('rejections.update');
    Route::get('/rejections/{id}/details', [RejectionController::class, 'getRejectionDetails'])->name('rejections.details');
    Route::get('/rejections-list', [RejectionController::class, 'getRejectedItems'])->name('rejections.list');
});

// Listed Clothes (Requires Authentication)
Route::middleware(['auth'])->group(function () {
    Route::get('/listed-clothes', [ClothController::class, 'index'])->name('listed.clothes');
    Route::get('/listed-clothes/{id}/edit', [ClothController::class, 'edit'])->name('listed.clothes.edit');
    Route::put('/listed-clothes/{id}', [ClothController::class, 'update'])->name('listed.clothes.update');
    Route::delete('/listed-clothes/{id}', [ClothController::class, 'destroy'])->name('listed.clothes.destroy');
    Route::delete('/listed-clothes/images/{imageId}', [ClothController::class, 'destroyImage'])->name('listed.clothes.images.destroy');
    
   
});

// Invoice Download
Route::get('/invoices/{id}/download', [InvoiceController::class, 'download'])->name('invoices.download');