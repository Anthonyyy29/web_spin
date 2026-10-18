<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Route::get('/login', fn()=> view('guest.login'))->name('login');
// Route::post('/login', [UserController::class, 'login'])->name('login');


Route::get('/admin/hr/dashboard', fn()=> view('auth.hr.dashboard.index'))->middleware('auth')->name('hr.dashboard');


Route::get('/', fn()=> view('uhuy'));
