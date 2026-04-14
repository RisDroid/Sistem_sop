<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Import Models
use App\Models\Sop;
use App\Models\Subjek;

// Import Controllers
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SopController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MonitoringController;

// PERBAIKAN DI SINI: Sesuaikan dengan lokasi folder Admin
use App\Http\Controllers\Admin\HomeController;

// Controller Master Data di folder Admin
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\SubjekController;

/*
|--------------------------------------------------------------------------
| Web Routes - E-Monev SOP BPS Banten
|--------------------------------------------------------------------------
*/

// 1. HALAMAN AWAL
Route::get('/', function () {
    return redirect()->route('login');
});

// 2. AUTHENTICATION (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// 3. PROTECTED ROUTES (Auth)
Route::middleware(['auth'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard Redirector
    Route::get('/dashboard', function () {
        $role = strtolower(Auth::user()->role);
        return redirect()->route($role . '.dashboard');
    })->name('dashboard');

    // --- GRUP KHUSUS ADMIN ---
    Route::prefix('admin')->name('admin.')->group(function () {

        // Dashboard Admin
        Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');

        // Master Data
        Route::resource('unit', UnitController::class);
        Route::resource('subjek', SubjekController::class);

        // Management User & Monitoring
        Route::resource('user', UserController::class);
        Route::resource('monitoring', MonitoringController::class);

        // --- MANAJEMEN SOP ---
        Route::get('/sop/akses-cepat', [SopController::class, 'aksesCepat'])->name('sop.aksescepat');
        Route::post('/sop/revisi', [SopController::class, 'storeRevisi'])->name('sop.revisi');
        Route::resource('sop', SopController::class);

        // --- HELPER / AJAX ROUTES ---
        Route::get('/subjek-search', [SubjekController::class, 'searchSubjek'])->name('subjek.search');
        Route::get('/get-units/{id_subjek}', [SopController::class, 'getUnits'])->name('getUnits');
    });

    // --- GRUP KHUSUS OPERATOR ---
    Route::prefix('operator')->name('operator.')->group(function () {
        // Operator juga diarahkan ke HomeController agar tampilan grafik sinkron
        Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');

        Route::get('/sop/akses-cepat', [SopController::class, 'aksesCepat'])->name('sop.aksescepat');
        Route::resource('sop', SopController::class);
    });

    // --- PROFILE MANAGEMENT ---
    Route::controller(ProfileController::class)->group(function() {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });
});
