<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepartmentController as AdminDepartments;
use App\Http\Controllers\Admin\NoticeController     as AdminNotices;
use App\Http\Controllers\Admin\FeedbackController   as AdminFeedback;
use App\Http\Controllers\Admin\TokenController      as AdminTokens;
use App\Http\Controllers\Admin\AuthController       as AdminAuth;

Route::get('/', fn () => redirect('/admin'));

/*
|--------------------------------------------------------------------------
| Admin web (Blade) panel
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {

    Route::get('/login',  [AdminAuth::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuth::class, 'login']);
    Route::post('/logout',[AdminAuth::class, 'logout'])->name('logout')->middleware('auth');

    Route::middleware(['auth', 'role:admin|editor'])->group(function () {
        Route::get('/',          [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('departments',  AdminDepartments::class);
        Route::resource('notices',      AdminNotices::class);
        Route::resource('feedback',     AdminFeedback::class)->only(['index', 'show', 'update', 'destroy']);
        Route::resource('tokens',       AdminTokens::class)->only(['index', 'show', 'update']);
    });
});
