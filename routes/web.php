<?php

use App\Http\Controllers\auth\announcer\AnnouncerController;
use App\Http\Controllers\auth\crew\SesiSayaController;
use App\Http\Controllers\auth\hr\AbsensiController;
use App\Http\Controllers\auth\hr\HrController;
use App\Http\Controllers\auth\hr\ProgramController;
use App\Http\Controllers\auth\hr\SesiController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Guest
Route::get('/login', fn () => view('guest.login'))->name('login');
Route::post('/login', [UserController::class, 'login'])->name('login');
Route::post('/logout', [UserController::class, 'logout'])->name('logout');

// admin HR
Route::group(['prefix' => 'hr', 'middleware' => ['auth', 'role:kadiv_human_resource,human_resource']], function () {
    Route::get('/dashboard', [HrController::class, 'dashboard'])->name('hr.dashboard');
    Route::resource('program', ProgramController::class)->except('show');
    Route::resource('sesi', SesiController::class)->only(['index', 'create', 'store', 'edit', 'update']);
    Route::post('sesi/{sesi}/tunda', [SesiController::class, 'postpone'])->name('sesi.postpone');
    Route::post('sesi/{sesi}/batal', [SesiController::class, 'cancel'])->name('sesi.cancel');
    Route::get('absensi', [AbsensiController::class, 'index'])->name('absensi.index');
    Route::get('absensi/orang', [AbsensiController::class, 'people'])->name('absensi.people');
    Route::get('absensi/export', [AbsensiController::class, 'export'])->name('absensi.export');
    Route::get('absensi/program/{program}', [AbsensiController::class, 'program'])->name('absensi.program');
    Route::get('absensi/program/{program}/export', [AbsensiController::class, 'programExport'])->name('absensi.program.export');
    Route::get('sesi/{sesi}/absensi', [AbsensiController::class, 'session'])->name('absensi.session');
    Route::get('sesi/{sesi}/absensi/export', [AbsensiController::class, 'sessionExport'])->name('absensi.session.export');
    Route::put('sesi/{sesi}/absensi/{user}', [AbsensiController::class, 'update'])->name('absensi.update');
});

// announcer
Route::group(['prefix' => 'announcer', 'middleware' => ['auth', 'role:kadiv_announcer,announcer']], function () {
    Route::get('/dashboard', [AnnouncerController::class, 'dashboard'])->name('announcer.dashboard');
    Route::get('sesi', [SesiSayaController::class, 'index'])->name('announcer.sesi.index');
    Route::post('sesi/{sesi}/check-in', [SesiSayaController::class, 'checkIn'])->name('announcer.sesi.checkIn');
});

// sound operator
Route::group(['prefix' => 'so', 'middleware' => ['auth', 'role:kadiv_sound_operator,sound_operator']], function () {
    Route::get('sesi', [SesiSayaController::class, 'index'])->name('so.sesi.index');
    Route::post('sesi/{sesi}/check-in', [SesiSayaController::class, 'checkIn'])->name('so.sesi.checkIn');
});

Route::get('/', fn () => view('main'));
// Route::get('/test', fn () => view('auth.announcer.dashboard.index'));
