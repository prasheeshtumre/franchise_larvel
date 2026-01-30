<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\TestController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\Emails\EmailController;
use App\Http\Controllers\Franchise\FranchiseController;
use App\Http\Controllers\Hashtag\HashtagController;
use App\Http\Controllers\Industry\IndustryController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\Users\userController;
use App\Http\Controllers\Users\FollowController;
use App\Http\Middleware\AccessApiMiddleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\Post\PostController;
use App\Http\Controllers\Post\InteractionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// use OpenApi\Annotations as OA;
// test api's'
// test commits
Route::get('/test', function () {
    return 'api route works!';
});


Route::get('testing', [TestController::class, 'test']);

// register and login
Route::post('register', [RegisterController::class, 'register']);
Route::get('login', function () {
    return response()->json(['message' => 'Unauthenticated.'], 401);
})->name('login');
Route::post('login', [LoginController::class, 'login']);

Route::prefix('password')->group(function () {
    Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
    Route::post('reset', [ResetPasswordController::class, 'reset']);
});

// Email verification route for API
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');
// Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify']);

Route::post('resend-verification-link', [VerificationController::class, 'resend']);

// admin auth login
Route::middleware(['auth:sanctum', AccessApiMiddleware::class])->group(function () {
    Route::get('user', [UserController::class, 'getUser']);
    Route::post('logout', [LoginController::class, 'logout']);

    Route::prefix('user')->group(function () {
        Route::get('get-details/{id}', [UserController::class, 'getUserDetails']);
    });


    Route::prefix('registration')->group(function () {
        Route::post('store_franchisor_details', [RegisterController::class, 'storeFranchisorDetails']);
        Route::post('store_franchisee_details', [RegisterController::class, 'storeFranchiseeDetails']);
        Route::post('store_buyer_details', [RegisterController::class, 'storeBuyerDetails']);
        Route::post('store_consultant_details', [RegisterController::class, 'storeConsultantDetails']);
        Route::get('get_signup_flow_details/{user_id}', [RegisterController::class, 'getSignupFlowDetails']);
    });
});
// user auth login

Route::middleware(['auth:sanctum', AdminMiddleware::class])->group(function () {
    Route::get('admin', [userController::class, 'getUser']);
    Route::post('logout', [LoginController::class, 'logout']);

});

Route::prefix('industries')->group(function () {
    Route::post('store', [IndustryController::class, 'storeIndustry']);
    Route::put('update/{id}', [IndustryController::class, 'updateIndustry']);
    Route::delete('delete/{id}', [IndustryController::class, 'deleteIndustry']);
    Route::get('get', [IndustryController::class, 'getIndustry']);
});
Route::prefix('category')->group(function () {
    Route::post('store', [CategoryController::class, 'storeCategory']);
    Route::put('update/{id}', [CategoryController::class, 'updateCategory']);
    Route::delete('delete/{id}', [CategoryController::class, 'deleteCategory']);
    Route::get('get', [CategoryController::class, 'getCategory']);
});
Route::prefix('franchises')->group(function () {
    Route::post('store', [FranchiseController::class, 'storeFranchise']);
    Route::put('update/{id}', [FranchiseController::class, 'updateFranchise']);
    Route::delete('delete/{id}', [FranchiseController::class, 'deleteFranchise']);
    Route::get('get', [FranchiseController::class, 'getFranchises']);
});
Route::prefix('hashtag')->group(function () {
    Route::any('store', [HashtagController::class, 'storeHashtag']);
    Route::put('update/{id}', [HashtagController::class, 'updateHashtag']);
    Route::delete('delete/{id}', [HashtagController::class, 'deleteHashtag']);
    Route::get('get', [HashtagController::class, 'getHashtag']);
});


Route::get('send_mail', [EmailController::class, 'sendEmail']);

Route::get('demo-send-mail', [MailController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('user/post')->group(function () {
        Route::get('get', [PostController::class, 'index']);
        Route::post('store', [PostController::class, 'store']);
        Route::get('get/{id}', [PostController::class, 'show']);
        Route::put('update/{id}', [PostController::class, 'update']);
        Route::delete('delete/{id}', [PostController::class, 'destroy']);

        // Social Interactions
        Route::post('{id}/like', [InteractionController::class, 'like']);
        Route::post('{id}/unlike', [InteractionController::class, 'unlike']);
        Route::post('{id}/comment', [InteractionController::class, 'storeComment']);
        Route::get('{id}/comments', [InteractionController::class, 'showComments']);
        Route::post('{id}/share', [InteractionController::class, 'share']);
    });

    Route::prefix('user')->group(function () {
        Route::post('follow/{id}', [FollowController::class, 'follow']);
        Route::post('unfollow/{id}', [FollowController::class, 'unfollow']);
        Route::get('followings', [FollowController::class, 'followings']);

        // Search & Profile
        Route::get('search', [UserController::class, 'search']);
        Route::get('profile/{id}', [UserController::class, 'getProfile']);
        Route::get('{id}/posts', [PostController::class, 'userPosts']);
    });

    // Standardized Post Routes
    Route::put('posts/{post}', [PostController::class, 'update']);
    Route::delete('posts/{post}', [PostController::class, 'destroy']);
});

Route::get('/test-file', function () {
    return response()->file(storage_path('app/public/posts/9xDOcSpxJpRJq3UnNwhBgqFO2KANZMTSKkYtEGG4.jpg'));
});




// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Route::get('/admin', function (Request $request) {
//     return $request->user();
// })->middleware(['auth:sanctum',AdminMiddleware::class]);



// Route::middleware('auth:sanctum')->get('user', [LoginController::class, 'user']);
