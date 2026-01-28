<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Marketing\LeadController;
use App\Http\Controllers\Marketing\CouponController;
use App\Http\Controllers\Marketing\SegmentationController;
use App\Http\Controllers\Marketing\AnalyticsController;

// Lead Management
Route::prefix('leads')->group(function () {
    Route::get('/', [LeadController::class, 'index']);
    Route::post('/', [LeadController::class, 'store']);
    Route::get('/{id}', [LeadController::class, 'show']);
    Route::put('/{id}', [LeadController::class, 'update']);
    Route::delete('/{id}', [LeadController::class, 'destroy']);
    Route::post('/{id}/assign', [LeadController::class, 'assign']);
    Route::post('/{id}/convert', [LeadController::class, 'convert']);
    Route::post('/{id}/activities', [LeadController::class, 'addActivity']);
    Route::get('/stats/overview', [LeadController::class, 'stats']);
    Route::get('/stats/pipeline', [LeadController::class, 'pipeline']);
    Route::post('/import/bulk', [LeadController::class, 'import']);
});

// Coupon Management
Route::prefix('coupons')->group(function () {
    Route::get('/', [CouponController::class, 'index']);
    Route::post('/', [CouponController::class, 'store']);
    Route::get('/{id}', [CouponController::class, 'show']);
    Route::put('/{id}', [CouponController::class, 'update']);
    Route::delete('/{id}', [CouponController::class, 'destroy']);
    Route::post('/validate', [CouponController::class, 'validate']);
    Route::get('/stats/overview', [CouponController::class, 'stats']);
    Route::post('/{id}/generate-codes', [CouponController::class, 'generate']);
    Route::get('/export', [CouponController::class, 'export']);
});

// Customer Segmentation
Route::prefix('segments')->group(function () {
    Route::get('/', [SegmentationController::class, 'index']);
    Route::post('/', [SegmentationController::class, 'store']);
    Route::get('/{id}', [SegmentationController::class, 'show']);
    Route::put('/{id}', [SegmentationController::class, 'update']);
    Route::delete('/{id}', [SegmentationController::class, 'destroy']);
    Route::get('/{id}/customers', [SegmentationController::class, 'customers']);
    Route::post('/{id}/customers/add', [SegmentationController::class, 'addCustomers']);
    Route::post('/{id}/customers/remove', [SegmentationController::class, 'removeCustomers']);
    Route::get('/customer/{userId}', [SegmentationController::class, 'customerSegments']);
    Route::post('/preview', [SegmentationController::class, 'preview']);
    Route::post('/{id}/calculate', [SegmentationController::class, 'calculate']);
    Route::post('/recalculate-all', [SegmentationController::class, 'recalculateAll']);
    Route::get('/{id}/stats', [SegmentationController::class, 'stats']);
});

// Marketing Analytics
Route::prefix('analytics')->group(function () {
    Route::get('/dashboard', [AnalyticsController::class, 'dashboard']);
    Route::get('/leads', [AnalyticsController::class, 'leads']);
    Route::get('/coupons', [AnalyticsController::class, 'coupons']);
    Route::get('/segments', [AnalyticsController::class, 'segments']);
    Route::get('/ltv', [AnalyticsController::class, 'ltv']);
    Route::get('/cohorts', [AnalyticsController::class, 'cohorts']);
    Route::get('/funnel', [AnalyticsController::class, 'funnel']);
    Route::get('/roi', [AnalyticsController::class, 'roi']);
    Route::get('/export', [AnalyticsController::class, 'export']);
});
