<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ActivityController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Bagirata Project
|--------------------------------------------------------------------------
*/

// 1. LANDING PAGE
Route::get('/', function () {
    return view('welcome');
});

// 2. PROTECTED ROUTES (Harus Login & Verifikasi)
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard Utama (PBI 09)
    Route::get('/dashboard', [ActivityController::class, 'index'])->name('dashboard');

    // Menu "Lihat Semua" / Bills (PBI 02)
    // Kita arahkan ke index() di Controller agar bisa membedakan tampilan
    Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');

    // Menu Teman (PBI 03)
    // Sementara kita arahkan ke ActivityController agar tidak error 404
    Route::get('/friends', [ActivityController::class, 'index'])->name('friends.index');

    // CRUD Sesi Kegiatan (Create, Store, Edit, Update, Destroy)
    // Kita pakai 'except index' karena index-nya sudah dibuat manual di atas
    Route::resource('activities', ActivityController::class)->except(['index']);
});

// 3. PROFILE MANAGEMENT (Hanya Butuh Login)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::patch('/profile/currency', [ProfileController::class, 'updateCurrency'])->name('profile.currency');
});

// Rute untuk menampilkan halaman test upload
Route::get('/test-ocr', function () {
    return view('test-ocr');
});

// Rute untuk memproses gambarnya (pakai ActivityController buatan temanmu)
Route::post('/scan-struk', [ActivityController::class, 'scanStruk'])->name('ocr.scan');


require __DIR__.'/auth.php';