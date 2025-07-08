<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminDashController;
use App\Http\Controllers\AuthController;


Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/users', [AdminDashController::class, 'showUsers'])->name('admin.users.index');
    Route::post('/admin/users/{user}/license', [AdminDashController::class, 'updateUserLicense'])->name('admin.users.updateLicense');
});
Route::get('/admin/login', [AuthController::class, 'showLoginFormAdmin'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'loginAdmin']);
Route::post('/admin/logout', [AuthController::class, 'logoutAdmin'])->name('admin.logout');

