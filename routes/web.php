<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');
Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login/checklogin', [AuthController::class, 'login'])->name('login.check');

Route::prefix('dashboard')->middleware(['auth', 'actioncheck'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
});
