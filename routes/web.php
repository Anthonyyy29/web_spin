<?php

use App\Http\Controllers\auth\announcer\AnnouncerController;
use App\Http\Controllers\auth\hr\HrController;
use App\Http\Controllers\auth\hr\ProgramController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\isAnnouncer;
use App\Http\Middleware\isHr;
use Illuminate\Support\Facades\Route;

// Guest
Route::get('/login', fn () => view('guest.login'))->name('login');
Route::post('/login', [UserController::class, 'login'])->name('login');
Route::post('/logout', [UserController::class, 'logout'])->name('logout');

// admin HR
Route::group(['prefix' => 'hr', 'middleware' => ['auth', isHr::class]], function () {
    Route::get('/dashboard', [HrController::class, 'dashboard'])->name('hr.dashboard');
    Route::resource('program', ProgramController::class)->except('show');
});

// announcer
Route::group(['prefix' => 'announcer', 'middleware' => ['auth', isAnnouncer::class]], function () {
    Route::get('/dashboard', [AnnouncerController::class, 'dashboard'])->name('announcer.dashboard');
});

Route::get('/', fn () => view('main'));
// Route::get('/test', fn () => view('auth.announcer.dashboard.index'));
