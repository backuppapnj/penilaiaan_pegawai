<?php

use App\Http\Controllers\Admin\CriterionController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\ExcelImportController;
use App\Http\Controllers\Admin\PeriodController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VotingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', fn (Request $request) => Inertia::render('Auth/Login', [
    'canResetPassword' => Features::enabled(Features::resetPasswords()),
    'status' => $request->session()->get('status'),
]))->name('home');

// Login route (GET for Wayfinder, POST handled by Fortify)
Route::get('/login', fn (Request $request) => Inertia::render('Auth/Login', [
    'canResetPassword' => Features::enabled(Features::resetPasswords()),
    'status' => $request->session()->get('status'),
]))->name('login');

// Forgot password route (GET for Wayfinder, POST handled by Fortify)
Route::get('/forgot-password', fn (Request $request) => Inertia::render('Auth/ForgotPassword', [
    'status' => $request->session()->get('status'),
]))->name('password.request');

// Reset password route (GET for Wayfinder, POST handled by Fortify)
Route::get('/reset-password/{token}', fn (Request $request) => Inertia::render('Auth/ResetPassword', [
    'email' => $request->email,
    'token' => $request->route('token'),
]))->name('password.reset');

// Registration route (GET for Wayfinder, POST handled by Fortify)
Route::get('/register', fn () => Inertia::render('Auth/Register'))->name('register');

// Two-Factor Challenge route (GET for Wayfinder, POST handled by Fortify)
Route::get('/two-factor-challenge', function (Request $request) {
    if (! $request->session()->has('login.id')) {
        return redirect()->route('login');
    }

    return Inertia::render('Auth/TwoFactorChallenge');
})->middleware(['guest'])->name('two-factor.login');

// Email Verification route (GET for Wayfinder, POST handled by Fortify)
Route::get('/email/verify', function (Request $request) {
    return $request->user()->hasVerifiedEmail()
        ? redirect()->route('dashboard-redirect')
        : Inertia::render('Auth/VerifyEmail');
})->middleware('auth')->name('verification.notice');

Route::get('/verify/{certificateId}', [CertificateController::class, 'verify'])->name('certificates.verify');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard-redirect', function () {
        return redirect()->route(auth()->user()->getDashboardRoute());
    })->name('dashboard-redirect');

    Route::get('/super-admin', [DashboardController::class, 'getStats'])->name('super-admin.dashboard')->middleware('role:SuperAdmin');

    Route::get('/admin', [DashboardController::class, 'getStats'])->name('admin.dashboard')->middleware('role:Admin,SuperAdmin');

    Route::get('/penilai', [DashboardController::class, 'getStats'])->name('penilai.dashboard')->middleware('role:Penilai,Admin,SuperAdmin');

    Route::get('/peserta', [DashboardController::class, 'getStats'])->name('peserta.dashboard')->middleware('role:Peserta,Penilai,Admin,SuperAdmin');

    Route::prefix('api/dashboard')->name('api.dashboard.')->group(function () {
        Route::get('/stats', [DashboardController::class, 'getStatsApi']);
        Route::get('/activity', [DashboardController::class, 'getActivity']);
        Route::get('/voting-progress', [DashboardController::class, 'getVotingProgress']);
        Route::get('/results', [DashboardController::class, 'getResults']);
    });

    Route::prefix('peserta')->name('peserta.')->group(function () {
        Route::get('/sertifikat', [CertificateController::class, 'index'])->name('certificates');
        Route::get('/sertifikat/{certificate}/download', [CertificateController::class, 'download'])->name('certificates.download');
    });

    Route::prefix('admin')->name('admin.')->middleware('role:Admin,SuperAdmin')->group(function () {
        // Period management
        Route::prefix('periods')->name('periods.')->group(function () {
            Route::get('/', [PeriodController::class, 'index'])->name('index');
            Route::get('/create', [PeriodController::class, 'create'])->name('create');
            Route::post('/', [PeriodController::class, 'store'])->name('store');
            Route::get('/{period}', [PeriodController::class, 'show'])->name('show');
            Route::get('/{period}/edit', [PeriodController::class, 'edit'])->name('edit');
            Route::put('/{period}', [PeriodController::class, 'update'])->name('update');
            Route::delete('/{period}', [PeriodController::class, 'destroy'])->name('destroy');
            Route::post('/{period}/status/{status}', [PeriodController::class, 'updateStatus'])->name('update-status');
        });

        // Criterion management
        Route::prefix('criteria')->name('criteria.')->group(function () {
            Route::get('/', [CriterionController::class, 'index'])->name('index');
            Route::get('/create', [CriterionController::class, 'create'])->name('create');
            Route::post('/', [CriterionController::class, 'store'])->name('store');
            Route::get('/{criterion}', [CriterionController::class, 'show'])->name('show');
            Route::get('/{criterion}/edit', [CriterionController::class, 'edit'])->name('edit');
            Route::put('/{criterion}', [CriterionController::class, 'update'])->name('update');
            Route::delete('/{criterion}', [CriterionController::class, 'destroy'])->name('destroy');
            Route::post('/{criterion}/weight', [CriterionController::class, 'updateWeight'])->name('update-weight');
        });

        // Employee management
        Route::prefix('employees')->name('employees.')->group(function () {
            Route::get('/', [EmployeeController::class, 'index'])->name('index');
            Route::post('/import', [EmployeeController::class, 'import'])->name('import');
            Route::get('/stats', [EmployeeController::class, 'stats'])->name('stats');
        });

        // SIKEP Excel Import
        Route::prefix('sikep')->name('sikep.')->group(function () {
            Route::get('/', [ExcelImportController::class, 'index'])->name('index');
            Route::post('/', [ExcelImportController::class, 'store'])->name('store');
            Route::get('/scores', [ExcelImportController::class, 'scores'])->name('scores');
            Route::delete('/{id}', [ExcelImportController::class, 'destroy'])->name('destroy');
        });

        Route::post('/periods/{period}/generate-certificates', [CertificateController::class, 'generateForPeriod'])->name('periods.generate-certificates');
    });

    // Voting routes for Penilai
    Route::prefix('penilai')->name('penilai.')->middleware('role:Penilai,Peserta,Admin,SuperAdmin')->group(function () {
        Route::prefix('voting')->name('voting.')->group(function () {
            Route::get('/', [VotingController::class, 'index'])->name('index');
            Route::get('/{period}/{category}', [VotingController::class, 'show'])->name('show');
            Route::post('/', [VotingController::class, 'store'])->name('store');
            Route::get('/history', [VotingController::class, 'history'])->name('history');
        });
    });
});

require __DIR__.'/settings.php';
