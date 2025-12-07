<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\ContactFormController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\DislikeController;
use App\Http\Controllers\Api\QuoteFormController;
use App\Http\Controllers\Api\AppointmentFormController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ImageUploadController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\NewsletterSubscriptionController;
use App\Http\Controllers\CategorySubscriptionController; // Import the new controller
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::post('upload-image', [ImageUploadController::class, 'store'])->middleware('auth:sanctum');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::put('/user', [UserController::class, 'update'])->middleware('auth:sanctum');


Route::get('my/blogs', [BlogController::class, 'myBlogs'])->middleware('auth:sanctum');

// Search endpoint
Route::get('search', [SearchController::class, 'search']);

// Public read-only endpoints
Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
Route::apiResource('blogs', BlogController::class)->parameters(['blogs' => 'blog:slug'])->only(['index', 'show']);

// Public Interaction Routes
Route::post('blogs/{blog:slug}/comments', [CommentController::class, 'store'])->name('blogs.comments.store');
Route::post('blogs/{blog:slug}/likes', [LikeController::class, 'store']);
Route::delete('blogs/{blog:slug}/likes', [LikeController::class, 'destroy']);
Route::post('blogs/{blog:slug}/dislikes', [DislikeController::class, 'store']);
Route::delete('blogs/{blog:slug}/dislikes', [DislikeController::class, 'destroy']);

// Authenticated write endpoints
Route::apiResource('categories', CategoryController::class)
    ->only(['store', 'update', 'destroy'])
    ->middleware(['auth:sanctum', 'admin']);


Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('blogs', BlogController::class)
        ->except(['index', 'show'])
        ->parameters(['blogs' => 'blog:slug']);
});

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('users', [UserController::class, 'index']);
    Route::put('users/{id}', [UserController::class, 'updateUser']);
    Route::delete('users/{id}', [UserController::class, 'destroy']);
});

Route::post('contact', [ContactFormController::class, 'store']);
Route::post('quote', [QuoteFormController::class, 'store']);
Route::post('appointment', [AppointmentFormController::class, 'store']);

Route::post('newsletter-subscriptions', [NewsletterSubscriptionController::class, 'store']);
Route::post('category-newsletter-subscriptions', [CategorySubscriptionController::class, 'store']); // New category newsletter subscription route

// Newsletter sending (admin only)
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('newsletter/subscriber-count', [\App\Http\Controllers\Api\NewsletterController::class, 'subscriberCount']);
    Route::post('newsletter/send', [\App\Http\Controllers\Api\NewsletterController::class, 'send']);
});