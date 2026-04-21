<?php

use App\Http\Controllers\Student\StudentAuthController;
use App\Http\Controllers\Student\StudentPasswordController;
use App\Http\Controllers\Student\StudentPortalController;
use Illuminate\Support\Facades\Route;

Route::prefix('student')->name('student.')->group(function () {
    Route::middleware('guest.student')->group(function () {
        Route::get('/login', function () {
            return redirect()->route('login');
        })->name('login');

        Route::post('/login', [StudentAuthController::class, 'login']);

        Route::get('/forgot-password', [StudentPasswordController::class, 'showForgotPassword'])
            ->name('password.request');
        Route::post('/forgot-password', [StudentPasswordController::class, 'sendResetLink'])
            ->name('password.email');

        Route::get('/reset-password/{token}', [StudentPasswordController::class, 'showResetPassword'])
            ->name('password.reset');
        Route::post('/reset-password', [StudentPasswordController::class, 'resetPassword'])
            ->name('password.store');
    });

    Route::middleware('student')->group(function () {
        Route::post('/logout', [StudentAuthController::class, 'logout'])->name('logout');

        Route::get('/dashboard', [StudentPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/payments', [StudentPortalController::class, 'payments'])->name('payments');
        Route::get('/payments/{payment}/receipt', [StudentPortalController::class, 'downloadReceipt'])
            ->name('payments.receipt');

        Route::get('/promissory-notes', [StudentPortalController::class, 'showPromissoryNotes'])
            ->name('promissory_notes.index');
        Route::get('/promissory-notes/{note}/download', [StudentPortalController::class, 'downloadPromissoryNoteTemplate'])
            ->name('promissory_notes.download');
        Route::post('/promissory-notes/{note}/sign', [StudentPortalController::class, 'uploadSignedPromissoryNote'])
            ->name('promissory_notes.sign');

        Route::get('/profile', [StudentPortalController::class, 'profile'])->name('profile');
        Route::patch('/profile', [StudentPortalController::class, 'updateProfile'])->name('profile.update');
    });
});
