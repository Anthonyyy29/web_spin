<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\isHr;


//Guest
Route::get('/login', fn()=> view('guest.login'))->name('login');
Route::post('/login', [UserController::class, 'login'])->name('login');
Route::post('/logout', [UserController::class, 'logout'])->name('logout');


//admin HR


Route::get('/admin/hr/dashboard', fn()=> view('auth.hr.dashboard.index'))->middleware('auth')->middleware(isHr::class)->name('hr.dashboard');


Route::get('/', fn()=> view('uhuy'));
