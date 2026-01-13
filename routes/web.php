<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});


Route::middleware('auth')->get('/dashboard', function () {
    $role = Auth::user()->role;

    return match($role) {
        'osa' => redirect()->route('osa.dashboard'),
        'usc' => redirect()->route('usc.dashboard'),
        'college' => redirect()->route('college.dashboard'),
        default => abort(403), 
    };
})->name('dashboard');


Route::middleware(['auth', 'role:osa'])->group(function () {
    Route::get('/osa/dashboard', function () {
        return view('osa.dashboard');
    })->name('osa.dashboard');
});

Route::middleware(['auth', 'role:usc'])->group(function () {
    Route::get('/usc/dashboard', function () {
        return view('usc.dashboard');
    })->name('usc.dashboard');
});

Route::middleware(['auth', 'role:college'])->group(function () {
    Route::get('/college/dashboard', function () {
        return view('college.dashboard');
    })->name('college.dashboard');
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
