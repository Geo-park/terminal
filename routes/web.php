<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\KepalaLab\AntreanController as KepalaLabAntreanController;
use App\Http\Controllers\KepalaLab\BukuBookingController as KepalaLabBukuBookingController;
use App\Http\Controllers\KepalaLab\DashboardController as KepalaLabDashboardController;
use App\Http\Controllers\Mahasiswa\BukuBookingController;
use App\Http\Controllers\Mahasiswa\DashboardController;
use App\Http\Controllers\Mahasiswa\ReservasiController;
use App\Http\Controllers\Mahasiswa\ReservationStreamController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Guest Routes (Otentikasi & Verifikasi OTP Email)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post')->middleware('throttle:10,1');
    Route::post('/login/verify-otp', [LoginController::class, 'verifyOtp'])->name('login.verify-otp')->middleware('throttle:10,1');
    Route::post('/login/resend-otp', [LoginController::class, 'resendOtp'])->name('login.resend-otp')->middleware('throttle:5,1');
    Route::post('/login/cancel-otp', [LoginController::class, 'cancelOtp'])->name('login.cancel-otp');
});

// Logout Route
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Root Route (Redirect to Dashboard sesuai role jika authenticated, otherwise to Login)
Route::get('/', function () {
    if (Auth::check()) {
        if (Auth::user()->role === 'kepala_lab') {
            return redirect('/lab');
        }

        return redirect()->route('mahasiswa.dashboard');
    }

    return redirect()->route('login');
});

// Protected Mahasiswa Routes (Role: mahasiswa)
Route::middleware(['auth', 'role:mahasiswa'])->name('mahasiswa.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Buku Booking (Jadwal Reservasi Komputer Laboratorium)
    Route::get('/buku-booking', [BukuBookingController::class, 'index'])->name('buku-booking');

    // Aksi Sesi & Billing Komputer
    Route::post('/session/{session}/end', [DashboardController::class, 'endSession'])->name('session.end');
    Route::post('/reservasi/{reservation}/checkin', [DashboardController::class, 'checkIn'])->name('reservasi.checkin');
    Route::post('/reservasi/{reservation}/cancel', [DashboardController::class, 'cancelReservation'])->name('reservasi.cancel');

    // SSE: Stream status reservasi secara real-time (push-only, tanpa polling dari browser)
    Route::get('/reservasi/{reservation}/stream', [ReservationStreamController::class, 'stream'])->name('reservasi.stream');

    // Formulir Reservasi Komputer (URL bersih 1 kata: /reservasi)
    Route::get('/reservasi', [ReservasiController::class, 'create'])->name('reservasi.create');
    Route::post('/reservasi', [ReservasiController::class, 'store'])->name('reservasi.store');
    Route::get('/reservasi/create', function (Request $request) {
        $qs = $request->getQueryString();

        return redirect('/reservasi'.($qs ? '?'.$qs : ''));
    });

    // Profil Mahasiswa — Route tetap ada tapi diblokir di controller (read-only, terhubung sistem akademik)
    Route::put('/profile', [DashboardController::class, 'updateProfile'])->name('profile.update');
});

// Protected Kepala Lab Routes (Role: kepala_lab)
Route::middleware(['auth', 'role:kepala_lab'])->name('kepala-lab.')->group(function () {
    // URL Bersih 1 Segmen: /antrean (Matching pola /reservasi pada mahasiswa)
    Route::get('/antrean', [KepalaLabAntreanController::class, 'index'])->name('antrean');

    Route::prefix('lab')->group(function () {
        Route::get('/', [KepalaLabDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', function () {
            return redirect('/lab');
        });
        Route::get('/antrean', [KepalaLabAntreanController::class, 'index'])->name('antrean.legacy');
        Route::get('/buku-booking', [KepalaLabBukuBookingController::class, 'index'])->name('buku-booking');
        Route::post('/buku-booking', [KepalaLabBukuBookingController::class, 'store'])->name('buku-booking.store');

        // Approval & Rejection Reservasi Komputer
        Route::post('/reservasi/{reservation}/approve', [KepalaLabDashboardController::class, 'approveReservation'])->name('reservasi.approve');
        Route::post('/reservasi/{reservation}/reject', [KepalaLabDashboardController::class, 'rejectReservation'])->name('reservasi.reject');

        // Operator Billing Control: Force End Sesi & Maintenance Unit
        Route::post('/session/{session}/force-end', [KepalaLabDashboardController::class, 'forceEndSession'])->name('session.force-end');
        Route::post('/komputer/{computer}/toggle-maintenance', [KepalaLabDashboardController::class, 'toggleMaintenance'])->name('komputer.toggle-maintenance');
    });
});

// Redirect URL lama /mahasiswa/* ke URL baru /* agar link lama / bookmark tetap berjalan
Route::middleware('auth')->prefix('mahasiswa')->group(function () {
    Route::get('/{any?}', function (Request $request, $any = '') {
        $target = ($any === 'reservasi/create') ? 'reservasi' : $any;
        $qs = $request->getQueryString();

        return redirect('/'.$target.($qs ? '?'.$qs : ''));
    })->where('any', '.*');
});
