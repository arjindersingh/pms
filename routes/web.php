<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
    Route::get('/register/recruiter', [RegistrationController::class, 'recruiter'])->name('register.recruiter');
    Route::post('/register/recruiter', [RegistrationController::class, 'storeRecruiter'])->name('register.recruiter.store');
    Route::get('/register/talent', [RegistrationController::class, 'talent'])->name('register.talent');
    Route::post('/register/talent', [RegistrationController::class, 'storeTalent'])->name('register.talent.store');
    Route::get('/administrator', [AuthController::class, 'administrator'])->name('administrator.login');
    Route::post('/administrator', [AuthController::class, 'storeAdministrator'])->name('administrator.login.store');
});

Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'category:administrator', 'module:administration'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'administrator'])
        ->middleware('menu:admin-dashboard,view')->name('dashboard');
    Route::view('/access', 'admin.access')
        ->middleware('menu:module-access,update')->name('access');
});

Route::middleware(['auth', 'category:recruiter', 'module:recruitment'])->prefix('recruiter')->name('recruiter.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'recruiter'])
        ->middleware('menu:recruiter-dashboard,view')->name('dashboard');
});

Route::middleware(['auth', 'category:talent', 'module:career'])->prefix('talent')->name('talent.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'talent'])
        ->middleware('menu:talent-dashboard,view')->name('dashboard');
});
