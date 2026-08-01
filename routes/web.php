<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AccountReviewController;
use App\Http\Controllers\Admin\AdSettingController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

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

Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::get('/profile', [AccountController::class, 'editProfile'])->name('profile');
    Route::patch('/profile', [AccountController::class, 'updateProfile'])->name('profile.update');
    Route::get('/settings', [AccountController::class, 'settings'])->name('settings');
    Route::get('/error-settings', [AccountController::class, 'editErrorSettings'])->name('error-settings');
    Route::put('/error-settings', [AccountController::class, 'updateErrorSettings'])->name('error-settings.update');
    Route::get('/password', [AccountController::class, 'editPassword'])->name('password');
    Route::put('/password', [AccountController::class, 'updatePassword'])->name('password.update');
});

Route::middleware(['auth', 'category:administrator', 'module:administration'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'administrator'])
        ->middleware('menu:admin-dashboard,view')->name('dashboard');
    Route::view('/access', 'admin.access')
        ->middleware('menu:module-access,update')->name('access');
    Route::get('/accounts', [AccountReviewController::class, 'index'])->middleware('menu:account-review,view')->name('accounts.index');
    Route::get('/accounts/{user}', [AccountReviewController::class, 'show'])->middleware('menu:account-review,view')->name('accounts.show');
    Route::patch('/accounts/{user}/status', [AccountReviewController::class, 'updateStatus'])->middleware('menu:account-review,update')->name('accounts.status');
    Route::delete('/accounts/{user}', [AccountReviewController::class, 'destroy'])->middleware('menu:account-review,delete')->name('accounts.destroy');
    Route::post('/accounts/{user}/restore', [AccountReviewController::class, 'restore'])->middleware('menu:account-review,update')->name('accounts.restore');
    Route::get('/roles', [RoleController::class, 'index'])->middleware('menu:role-management,view')->name('roles.index');
    Route::get('/permission-audit', [RoleController::class, 'audit'])->middleware('menu:permission-audit,view')->name('permission-audit');
    Route::post('/roles', [RoleController::class, 'store'])->middleware('menu:role-management,create')->name('roles.store');
    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->middleware('menu:role-management,update')->name('roles.edit');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->middleware('menu:role-management,update')->name('roles.update');
    Route::put('/accounts/{user}/role', [RoleController::class, 'assign'])->middleware('menu:role-management,update')->name('accounts.role');
    Route::get('/accounts/{user}/permissions', [RoleController::class, 'editUserPermissions'])->middleware('menu:role-management,update')->name('accounts.permissions');
    Route::put('/accounts/{user}/permissions', [RoleController::class, 'updateUserPermissions'])->middleware('menu:role-management,update')->name('accounts.permissions.update');
    Route::get('/google-ads', [AdSettingController::class, 'edit'])->middleware('menu:google-ads,view')->name('ads.edit');
    Route::put('/google-ads', [AdSettingController::class, 'update'])->middleware('menu:google-ads,update')->name('ads.update');
});

Route::middleware(['auth', 'category:recruiter', 'module:recruitment'])->prefix('recruiter')->name('recruiter.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'recruiter'])
        ->middleware('menu:recruiter-dashboard,view')->name('dashboard');
});

Route::middleware(['auth', 'category:talent', 'module:career'])->prefix('talent')->name('talent.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'talent'])
        ->middleware('menu:talent-dashboard,view')->name('dashboard');
});
