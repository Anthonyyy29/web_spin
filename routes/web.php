<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\isHr;
use App\Http\Middleware\isAnnouncer;
use App\Http\Controllers\auth\announcer\AnnouncerController;


//Guest
Route::get('/login', fn()=> view('guest.login'))->name('login');
Route::post('/login', [UserController::class, 'login'])->name('login');
Route::post('/logout', [UserController::class, 'logout'])->name('logout');


//admin HR
Route::group(['prefix' => 'admin', 'middleware' => ['auth', isHr::class]], function () {
    Route::get('/hr/dashboard', fn()=> view('auth.hr.dashboard.index'))->name('hr.dashboard');
    

});



//announcer
Route::group(['prefix' => 'announcer', 'middleware' => ['auth', isAnnouncer::class]], function () {
    Route::get('/dashboard', [AnnouncerController::class, 'dashboard'])->name('announcer.dashboard');
});



Route::get('/', fn()=> view('main'));
Route::get('/test', fn()=> view('auth.announcer.dashboard.index'));
Route::get('/test2', fn()=> view('auth.hr.dashboard.index'));