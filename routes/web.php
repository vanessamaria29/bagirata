<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// 1. HALAMAN UTAMA (Landing Page)
// Kita arahkan ke 'welcome' supaya user lihat tombol Login & Register dulu
Route::get('/', function () {
    return view('welcome');
});

// 2. HALAMAN DASHBOARD (Protected)
// Hanya bisa dibuka kalau sudah login (auth)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// 3. FITUR PROFILE (Hanya bisa diakses setelah login)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// 4. ROUTE AUTH (Login, Register, Logout dari Breeze)
require __DIR__.'/auth.php';