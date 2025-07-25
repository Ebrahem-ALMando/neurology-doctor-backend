<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ImageUploadController;
use App\Http\Controllers\Api\FileUploadController;
use App\Http\Controllers\Api\ArticleCategoryController;
use App\Http\Controllers\Api\ArticleImageController;
use App\Http\Controllers\Api\ArticleCommentController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ConsultationController;
use App\Http\Controllers\Api\ConsultationMessageController;

use App\Http\Controllers\Api\ConsultationAttachmentController;
use App\Http\Controllers\Api\ConsultationStatusLogController;
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

Route::middleware(['api.key'])->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
        Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
    });

    // Profile routes (require authentication)
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar']);
        Route::delete('/profile/avatar', [ProfileController::class, 'removeAvatar']);
    });

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
    });

    Route::middleware(['auth:sanctum'])->post('/images/upload', [ImageUploadController::class, 'upload']);
    Route::middleware(['auth:sanctum'])->post('/files/upload', [FileUploadController::class, 'upload']);

    Route::get('/article-categories', [ArticleCategoryController::class, 'index']);
    Route::get('/article-categories/{id}', [ArticleCategoryController::class, 'show']);
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/article-categories', [ArticleCategoryController::class, 'store']);
        Route::put('/article-categories/{id}', [ArticleCategoryController::class, 'update']);
        Route::delete('/article-categories/{id}', [ArticleCategoryController::class, 'destroy']);
    });

    Route::get('/article-images', [ArticleImageController::class, 'index']);
    Route::get('/article-images/{id}', [ArticleImageController::class, 'show']);
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/article-images', [ArticleImageController::class, 'store']);
        Route::put('/article-images/{id}', [ArticleImageController::class, 'update']);
        Route::delete('/article-images/{id}', [ArticleImageController::class, 'destroy']);
    });

    Route::get('/article-comments', [ArticleCommentController::class, 'index']);
    Route::get('/article-comments/{id}', [ArticleCommentController::class, 'show']);
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/article-comments', [ArticleCommentController::class, 'store']);
        Route::put('/article-comments/{id}', [ArticleCommentController::class, 'update']);
        Route::delete('/article-comments/{id}', [ArticleCommentController::class, 'destroy']);
    });

    Route::get('/articles', [ArticleController::class, 'index']);
    Route::get('/articles/{id}', [ArticleController::class, 'show']);
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/articles', [ArticleController::class, 'store']);
        Route::put('/articles/{id}', [ArticleController::class, 'update']);
        Route::delete('/articles/{id}', [ArticleController::class, 'destroy']);
    });

    // Consultations
    Route::middleware(['auth:sanctum'])->group(function () {
        // Main consultations CRUD
        Route::get('/consultations', [ConsultationController::class, 'index']);
        Route::get('/consultations/{id}', [ConsultationController::class, 'show']);
        Route::post('/consultations', [ConsultationController::class, 'store']);
        Route::put('/consultations/{id}', [ConsultationController::class, 'update']);
        Route::delete('/consultations/{id}', [ConsultationController::class, 'destroy']);
        // Update status only
        Route::patch('/consultations/{id}/status', [ConsultationController::class, 'updateStatus']);

        // Messages
        Route::get('/consultations/{consultation_id}/messages', [ConsultationMessageController::class, 'index']);
        Route::post('/consultations/{consultation_id}/messages', [ConsultationMessageController::class, 'store']);
        Route::put('/consultation-messages/{id}', [ConsultationMessageController::class, 'update']);
        Route::delete('/consultation-messages/{id}', [ConsultationMessageController::class, 'destroy']);
        Route::patch('/consultation-messages/{id}/read-by-patient', [ConsultationMessageController::class, 'markAsReadByPatient']);
        Route::patch('/consultation-messages/{id}/read-by-doctor', [ConsultationMessageController::class, 'markAsReadByDoctor']);
        Route::post('consultations/{consultation}/typing', [ConsultationMessageController::class, 'typing']);
        Route::get('consultations/{consultation}/messages/last', [ConsultationMessageController::class, 'lastMessage']);
        Route::get('consultations/{consultation}/messages/unread-count', [ConsultationMessageController::class, 'unreadCount']);

        // Attachments
        Route::get('/consultation-attachments', [ConsultationAttachmentController::class, 'index']);
        Route::get('/consultation-attachments/{id}', [ConsultationAttachmentController::class, 'show']);
        Route::delete('/consultation-attachments/{id}', [ConsultationAttachmentController::class, 'destroy']);

        // Status logs
        Route::get('/consultation-status-logs', [ConsultationStatusLogController::class, 'index']);
    });

    Route::get('/test', function () {
        return ['message' => 'API Key works!'];
    });
});
Route::get('/check-path', function () {
    return base_path('public/uploads');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});