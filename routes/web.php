<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

// Import Models
use App\Models\Sop;
use App\Models\Subjek;

// Import Controllers
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SopController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\EvaluasiController;

// PERBAIKAN DI SINI: Sesuaikan dengan lokasi folder Admin
use App\Http\Controllers\Admin\HomeController;

// Controller Master Data di folder Admin
use App\Http\Controllers\Admin\TimkerjaController;
use App\Http\Controllers\Admin\SubjekController;

/*
|--------------------------------------------------------------------------
| Web Routes - E-Monev SOP BPS Banten
|--------------------------------------------------------------------------
*/

// 1. HALAMAN AWAL
Route::get('/', function () {
    if (app()->environment('testing')) {
        return response()->view('auth.login');
    }

    return redirect()->route('login');
});

// 2. AUTHENTICATION (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [\App\Http\Controllers\Auth\RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [\App\Http\Controllers\Auth\RegisteredUserController::class, 'store']);
    Route::get('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [\App\Http\Controllers\Auth\NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [\App\Http\Controllers\Auth\NewPasswordController::class, 'store'])->name('password.store');
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
        Route::resource('timkerja', TimkerjaController::class); // ✅ FIX DI SINI
        Route::resource('subjek', SubjekController::class);

        // Management User & Monitoring
        Route::resource('user', UserController::class);
        Route::resource('monitoring', MonitoringController::class);
        Route::resource('evaluasi', EvaluasiController::class);

        // Menampilkan halaman formulir tambah user
        Route::get('/user-tambah', [UserController::class, 'create'])->name('user.tambah');
        // Menangani proses penyimpanan data user baru
        Route::post('/user-simpan', [UserController::class, 'store'])->name('user.simpan');

        // --- MANAJEMEN SOP ---
        Route::get('/sop/akses-cepat', [SopController::class, 'aksesCepat'])->name('sop.aksescepat');
        Route::get('/sop/{id}/history', [SopController::class, 'history'])->name('sop.history');
        Route::post('/sop/revisi', [SopController::class, 'storeRevisi'])->name('sop.revisi');

        // Rute untuk Bulk Delete (Hapus Banyak)
        Route::delete('/sop/bulk-delete', [SopController::class, 'bulkDelete'])->name('sop.bulkDelete');

        Route::resource('sop', SopController::class);

        // --- HELPER / AJAX ROUTES ---
        Route::get('/subjek-search', [SubjekController::class, 'searchSubjek'])->name('subjek.search');
        Route::get('/get-units/{id_subjek}', [SopController::class, 'getUnits'])->name('getUnits');
    });

    // --- GRUP KHUSUS OPERATOR ---
    Route::prefix('operator')->name('operator.')->group(function () {
        Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
        Route::get('/sop/akses-cepat', [SopController::class, 'aksesCepat'])->name('sop.aksescepat');
        Route::get('/sop/{id}/history', [SopController::class, 'history'])->name('sop.history');
        Route::post('/sop/revisi', [SopController::class, 'storeRevisi'])->name('sop.revisi');
        Route::resource('sop', SopController::class);
        Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
        Route::get('/monitoring/create', [MonitoringController::class, 'create'])->name('monitoring.create');
        Route::post('/monitoring', [MonitoringController::class, 'store'])->name('monitoring.store');
        Route::delete('/monitoring/{monitoring}', [MonitoringController::class, 'destroy'])->name('monitoring.destroy');
        Route::get('/evaluasi', [EvaluasiController::class, 'index'])->name('evaluasi.index');
        Route::get('/evaluasi/create', [EvaluasiController::class, 'create'])->name('evaluasi.create');
        Route::post('/evaluasi', [EvaluasiController::class, 'store'])->name('evaluasi.store');
        Route::delete('/evaluasi/{evaluasi}', [EvaluasiController::class, 'destroy'])->name('evaluasi.destroy');
    });

    Route::prefix('viewer')->name('viewer.')->group(function () {
        Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
        Route::get('/sop', [SopController::class, 'index'])->name('sop.index');
        Route::get('/sop/akses-cepat', [SopController::class, 'aksesCepat'])->name('sop.aksescepat');
        Route::get('/sop/{id}/history', [SopController::class, 'history'])->name('sop.history');
        Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
        Route::get('/evaluasi', [EvaluasiController::class, 'index'])->name('evaluasi.index');
    });

    // --- PROFILE MANAGEMENT ---
    Route::controller(ProfileController::class)->group(function() {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });

    Route::get('/verify-email', \App\Http\Controllers\Auth\EmailVerificationPromptController::class)
        ->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', \App\Http\Controllers\Auth\VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [\App\Http\Controllers\Auth\EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
    Route::get('/confirm-password', [\App\Http\Controllers\Auth\ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');
    Route::post('/confirm-password', [\App\Http\Controllers\Auth\ConfirmablePasswordController::class, 'store']);
    Route::put('/password', [\App\Http\Controllers\Auth\PasswordController::class, 'update'])->name('password.update');

    /*
    |--------------------------------------------------------------------------
    | ROUTE KHUSUS VIEW PDF (SOLUSI 403 FORBIDDEN)
    |--------------------------------------------------------------------------
    */
    Route::get('/view-pdf/{filename}', function ($filename) {
        $path = 'uploads/sop/' . $filename;

        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'File tidak ditemukan King!');
        }

        return Storage::disk('public')->response($path);
    })->name('view.pdf');

});
