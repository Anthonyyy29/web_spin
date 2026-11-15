<?php

use App\Http\Controllers\AboutUsController;
use App\Http\Controllers\Admin\Absensi\AbsensiController;
use App\Http\Controllers\Admin\Absensi\CrewController as AbsensiCrewController;
use App\Http\Controllers\Admin\Absensi\HistoryController;
use App\Http\Controllers\Admin\Absensi\ProgramController;
use App\Http\Controllers\Admin\Absensi\SesiController;
use App\Http\Controllers\Admin\CrewController as AdminCrewController;
use App\Http\Controllers\Admin\NewsController as AdminNewsController;
use App\Http\Controllers\Admin\PodcastController;
use App\Http\Controllers\Admin\SponsorController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CrewController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ListenController;
use App\Http\Controllers\NewsController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/home');
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/crew', [CrewController::class, 'index'])->name('crew');

Route::prefix('listen')->group(function () {
    Route::get('/', [ListenController::class, 'index'])->name('listen');
    Route::get('search', [ListenController::class, 'searchEpisode']);
    Route::get('content/{content}', [ListenController::class, 'openContent']);
});

Route::prefix('news')->group(function () {
    Route::get('/', [NewsController::class, 'index'])->name('news');
    Route::get('category/{category}', [NewsController::class, 'openCategory']);
    Route::get('details/{news}', [NewsController::class, 'openDetails']);
});

Route::prefix('aboutus')->group(function () {
    Route::get('/', [AboutUsController::class, 'index'])->name('aboutus');
    Route::post('sendmail', [AboutUsController::class, 'sendMail']);
});

Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login/checklogin', [AuthController::class, 'login'])->name('login.check');

Route::prefix('dashboard')->middleware(['auth', 'actioncheck'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('role:admin')->group(function () {
        Route::prefix('crew')->group(function () {
            Route::get('/', [AdminCrewController::class, 'index']);
            Route::post('save', [AdminCrewController::class, 'save']);
            Route::get('delete/{id}', [AdminCrewController::class, 'delete']);
            Route::get('filter/{role}', [AdminCrewController::class, 'filter']);
        });

        Route::prefix('podcast')->group(function () {
            Route::get('/', [PodcastController::class, 'index']);
            Route::post('save', [PodcastController::class, 'save']);
            Route::get('delete/{id}', [PodcastController::class, 'delete']);
            Route::get('open/{contentId}', [PodcastController::class, 'openPodcast']);
            Route::get('episode/open/{contentId}', [PodcastController::class, 'openEpisode']);
            Route::post('episode/save', [PodcastController::class, 'saveEpisode']);
            Route::get('episode/delete/{id}', [PodcastController::class, 'deleteEpisode']);
        });

        Route::prefix('news')->group(function () {
            Route::get('/', [AdminNewsController::class, 'index']);
            Route::post('save', [AdminNewsController::class, 'save']);
            Route::get('delete/{id}', [AdminNewsController::class, 'delete']);
            Route::get('filter/{category}', [AdminNewsController::class, 'filter']);
        });

        Route::prefix('sponsor')->group(function () {
            Route::get('/', [SponsorController::class, 'index']);
            Route::post('save', [SponsorController::class, 'save']);
            Route::get('delete/{id}', [SponsorController::class, 'delete']);
        });
    });

    Route::prefix('absensi')->middleware('role:hr')->group(function () {
        Route::post('tap', [AbsensiController::class, 'tap']);
        Route::get('history', [HistoryController::class, 'index']);
        Route::get('history/all', [HistoryController::class, 'all']);
        Route::get('history/byUid/{rfid}', [HistoryController::class, 'byUid']);
        Route::get('export-excel', [HistoryController::class, 'exportExcel']);
        Route::get('crew', [AbsensiCrewController::class, 'index']);
        Route::post('crew/simpan', [AbsensiCrewController::class, 'simpan']);
        Route::post('crew/update', [AbsensiCrewController::class, 'update']);
        Route::get('crew/delete/{rfid}', [AbsensiCrewController::class, 'delete']);
        Route::get('program', [ProgramController::class, 'index']);
        Route::post('program/update', [ProgramController::class, 'update']);
        Route::get('sesi', [SesiController::class, 'index']);
        Route::post('sesi/simpan', [SesiController::class, 'simpan']);
        Route::post('sesi/update', [SesiController::class, 'update']);
        Route::get('sesi/delete/{id}', [SesiController::class, 'delete']);
    });
});

Route::prefix('api')->group(function () {
    Route::get('checklatestpodcast/{segment}', [PodcastController::class, 'checkLatestPodcast']);
    Route::post('uploadpodcast', [PodcastController::class, 'saveEpisode'])->middleware('api.token');
    Route::get('podcastlist', [ListenController::class, 'podcastList']);
});

// Alat tap RFID tidak membawa token CSRF.
Route::match(['get', 'post'], 'tap-absen', [AbsensiController::class, 'tap']);
