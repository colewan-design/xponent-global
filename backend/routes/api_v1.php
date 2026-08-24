<?php

use App\Http\Controllers\Api\Admin\ContactEnquiryController as AdminContactEnquiryController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\GalleryImageController as AdminGalleryImageController;
use App\Http\Controllers\Api\Admin\InventoryController as AdminInventoryController;
use App\Http\Controllers\Api\Admin\JobApplicationController as AdminJobApplicationController;
use App\Http\Controllers\Api\Admin\JobOpeningController as AdminJobOpeningController;
use App\Http\Controllers\Api\Admin\NewsletterSubscriberController as AdminNewsletterSubscriberController;
use App\Http\Controllers\Api\Admin\OfficeLocationController as AdminOfficeLocationController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Admin\PageContentController as AdminPageContentController;
use App\Http\Controllers\Api\Admin\PartnerController as AdminPartnerController;
use App\Http\Controllers\Api\Admin\PostController as AdminPostController;
use App\Http\Controllers\Api\Admin\ProductCategoryController as AdminProductCategoryController;
use App\Http\Controllers\Api\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\Admin\ResourceController as AdminResourceController;
use App\Http\Controllers\Api\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Api\Admin\SolutionCategoryController as AdminSolutionCategoryController;
use App\Http\Controllers\Api\Admin\SolutionItemController as AdminSolutionItemController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\Admin\WarehouseController as AdminWarehouseController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContactEnquiryController;
use App\Http\Controllers\Api\GalleryImageController;
use App\Http\Controllers\Api\JobApplicationController;
use App\Http\Controllers\Api\JobOpeningController;
use App\Http\Controllers\Api\NewsletterSubscriberController;
use App\Http\Controllers\Api\OfficeLocationController;
use App\Http\Controllers\Api\PageContentController;
use App\Http\Controllers\Api\PartnerController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ResourceController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\SolutionCategoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public read/write routes — consumed by the Nuxt site, no auth required.
|--------------------------------------------------------------------------
*/

Route::middleware('throttle:10,1')->group(function () {
    Route::post('/contact-enquiries', [ContactEnquiryController::class, 'store']);
    Route::post('/newsletter-subscribers', [NewsletterSubscriberController::class, 'store']);
    Route::post('/newsletter-subscribers/unsubscribe', [NewsletterSubscriberController::class, 'unsubscribe']);
});

Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{post:slug}', [PostController::class, 'show']);

Route::get('/resources', [ResourceController::class, 'index']);

Route::get('/jobs', [JobOpeningController::class, 'index']);
Route::get('/jobs/{jobOpening:slug}', [JobOpeningController::class, 'show']);
Route::post('/jobs/{jobOpening:slug}/applications', [JobApplicationController::class, 'store'])->middleware('throttle:10,1');

Route::get('/gallery', [GalleryImageController::class, 'index']);

Route::get('/partners', [PartnerController::class, 'index']);

Route::get('/office-locations', [OfficeLocationController::class, 'index']);

Route::get('/solutions', [SolutionCategoryController::class, 'index']);
Route::get('/solutions/{solutionCategory:slug}', [SolutionCategoryController::class, 'show']);

Route::get('/page-content/{page}', [PageContentController::class, 'show']);

Route::get('/settings', [SettingController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Admin auth — session/cookie based (Sanctum SPA), used by the Vue admin.
|--------------------------------------------------------------------------
*/

Route::post('/auth/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
});

/*
|--------------------------------------------------------------------------
| Admin CRUD — everything below requires an authenticated admin session.
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::apiResource('contact-enquiries', AdminContactEnquiryController::class)->only(['index', 'show', 'update', 'destroy']);
    Route::apiResource('newsletter-subscribers', AdminNewsletterSubscriberController::class)->only(['index', 'destroy']);
    Route::apiResource('posts', AdminPostController::class);
    Route::apiResource('resources', AdminResourceController::class);
    Route::apiResource('job-openings', AdminJobOpeningController::class);
    Route::apiResource('job-applications', AdminJobApplicationController::class)->only(['index', 'show', 'update', 'destroy']);
    Route::apiResource('gallery-images', AdminGalleryImageController::class);
    Route::apiResource('partners', AdminPartnerController::class);
    Route::apiResource('office-locations', AdminOfficeLocationController::class);
    Route::apiResource('solution-categories', AdminSolutionCategoryController::class);
    Route::apiResource('solution-items', AdminSolutionItemController::class);

    /*
    |----------------------------------------------------------------------
    | Commerce — products, stock and orders.
    |----------------------------------------------------------------------
    |
    | The three are one system: an order reserves and later deducts stock at
    | a warehouse, and every balance change is posted to the movement ledger.
    | See App\Services\InventoryService for the rules that tie them together.
    */
    Route::apiResource('product-categories', AdminProductCategoryController::class);
    Route::apiResource('products', AdminProductController::class);
    Route::apiResource('warehouses', AdminWarehouseController::class);

    // Stock levels are read and configured as a resource, but never *written*
    // as one — a balance only moves through a posted movement.
    Route::get('/inventory', [AdminInventoryController::class, 'index']);
    Route::put('/inventory/{inventory}', [AdminInventoryController::class, 'update']);
    Route::post('/inventory/adjust', [AdminInventoryController::class, 'adjust']);
    Route::get('/stock-movements', [AdminInventoryController::class, 'movements']);

    Route::apiResource('orders', AdminOrderController::class);
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus']);

    Route::get('/page-content', [AdminPageContentController::class, 'index']);
    Route::put('/page-content/{page}', [AdminPageContentController::class, 'update']);

    // Managing admin accounts and site-wide settings is restricted to the
    // "admin" role — an "editor" session can manage content but not these.
    Route::middleware('role.admin')->group(function () {
        Route::apiResource('users', AdminUserController::class);

        Route::get('/settings', [AdminSettingController::class, 'index']);
        Route::put('/settings', [AdminSettingController::class, 'update']);
    });
});
