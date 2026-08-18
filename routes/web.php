<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AccountReviewController;
use App\Http\Controllers\Admin\AdSettingController;
use App\Http\Controllers\Admin\CompanyProfileController;
use App\Http\Controllers\Admin\PaymentSettingController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SessionReportController;
use App\Http\Controllers\Admin\SharedMasterController;
use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobSearchProfileController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\Recruiter\ProfileController as RecruiterProfileController;
use App\Http\Controllers\Talent\CandidateProfileController;
use App\Http\Controllers\Talent\SubscriptionController as TalentSubscriptionController;
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
    Route::get('/google-ads', [AdSettingController::class, 'edit'])->middleware('menu:google-ads,view')->name('ads.edit');
    Route::put('/google-ads', [AdSettingController::class, 'update'])->middleware('menu:google-ads,update')->name('ads.update');
    Route::get('/sessions', [SessionReportController::class, 'index'])->middleware('menu:session-reports,view')->name('sessions.index');
    Route::get('/sessions/{session}', [SessionReportController::class, 'show'])->middleware('menu:session-reports,view')->name('sessions.show');
    Route::get('/shared-masters', [SharedMasterController::class, 'index'])->middleware('menu:shared-masters,view')->name('shared-masters.index');
    Route::post('/shared-masters/{type}', [SharedMasterController::class, 'store'])->middleware('menu:shared-masters,create')->name('shared-masters.store');
    Route::put('/shared-masters/{type}/{record}', [SharedMasterController::class, 'update'])->middleware('menu:shared-masters,update')->name('shared-masters.update');
    Route::delete('/shared-masters/{type}/{record}', [SharedMasterController::class, 'destroy'])->middleware('menu:shared-masters,delete')->name('shared-masters.destroy');
    Route::get('/subscription-plans', [SubscriptionPlanController::class, 'index'])->middleware('menu:subscription-plans,view')->name('subscription-plans.index');
    Route::get('/subscription-plans/create', [SubscriptionPlanController::class, 'create'])->middleware('menu:subscription-plans,create')->name('subscription-plans.create');
    Route::post('/subscription-plans', [SubscriptionPlanController::class, 'store'])->middleware('menu:subscription-plans,create')->name('subscription-plans.store');
    Route::get('/subscription-plans/{subscriptionPlan}/edit', [SubscriptionPlanController::class, 'edit'])->middleware('menu:subscription-plans,update')->name('subscription-plans.edit');
    Route::put('/subscription-plans/{subscriptionPlan}', [SubscriptionPlanController::class, 'update'])->middleware('menu:subscription-plans,update')->name('subscription-plans.update');
    Route::put('/accounts/{user}/subscription', [SubscriptionPlanController::class, 'assign'])->middleware('menu:subscription-plans,update')->name('accounts.subscription');
    Route::get('/payment-settings', [PaymentSettingController::class, 'edit'])->middleware('menu:payment-settings,view')->name('payments.edit');
    Route::put('/payment-settings/{gateway}', [PaymentSettingController::class, 'update'])->middleware('menu:payment-settings,update')->name('payments.update');
    Route::get('/payment-transactions', [PaymentSettingController::class, 'transactions'])->middleware('menu:payment-settings,view')->name('payments.transactions');
    Route::get('/company-profile', [CompanyProfileController::class, 'edit'])->middleware('menu:company-profile,view')->name('company.edit');
    Route::put('/company-profile', [CompanyProfileController::class, 'update'])->middleware('menu:company-profile,update')->name('company.update');
});

Route::post('/payments/webhook/{provider}', PaymentWebhookController::class)->name('payments.webhook');

Route::middleware(['auth', 'category:recruiter', 'module:recruitment'])->prefix('recruiter')->name('recruiter.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'recruiter'])
        ->middleware('menu:recruiter-dashboard,view')->name('dashboard');
    Route::get('/candidate-search', [JobSearchProfileController::class, 'recruiter'])->middleware('menu:candidate-search,view')->name('candidate-search.edit');
    Route::put('/candidate-search', [JobSearchProfileController::class, 'updateRecruiter'])->middleware('menu:candidate-search,update')->name('candidate-search.update');
    Route::get('/profile', [RecruiterProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [RecruiterProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/organizations', [RecruiterProfileController::class, 'storeOrganization'])->name('organizations.store');
    Route::put('/profile/organizations/{organization}', [RecruiterProfileController::class, 'updateOrganization'])->name('organizations.update');
    Route::delete('/profile/organizations/{organization}', [RecruiterProfileController::class, 'destroyOrganization'])->name('organizations.destroy');
    Route::get('/subscription', [TalentSubscriptionController::class, 'show'])->name('subscription.show');
    Route::post('/subscription/renew', [TalentSubscriptionController::class, 'renew'])->middleware('throttle:10,1')->name('subscription.renew');
});

Route::middleware(['auth', 'category:talent', 'module:career'])->prefix('talent')->name('talent.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'talent'])
        ->middleware('menu:talent-dashboard,view')->name('dashboard');
    Route::get('/job-preferences', [JobSearchProfileController::class, 'talent'])->middleware('menu:job-preferences,view')->name('job-preferences.edit');
    Route::put('/job-preferences', [JobSearchProfileController::class, 'updateTalent'])->middleware('menu:job-preferences,update')->name('job-preferences.update');
    Route::get('/subscription', [TalentSubscriptionController::class, 'show'])->name('subscription.show');
    Route::post('/subscription/renew', [TalentSubscriptionController::class, 'renew'])->middleware('throttle:10,1')->name('subscription.renew');
    Route::get('/profile/{tab?}', [CandidateProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/{tab}', [CandidateProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/photograph', [CandidateProfileController::class, 'photograph'])->name('profile.photograph');
    Route::delete('/profile/photograph', [CandidateProfileController::class, 'removePhotograph'])->name('profile.photograph.remove');
    Route::post('/profile/entries/education', [CandidateProfileController::class, 'education'])->name('profile.education');
    Route::post('/profile/entries/education/{education}/subjects', [CandidateProfileController::class, 'addEducationSubject'])->name('profile.education.subjects.store');
    Route::delete('/profile/entries/education/{education}/subjects/{subject}', [CandidateProfileController::class, 'removeEducationSubject'])->name('profile.education.subjects.destroy');
    Route::post('/profile/entries/experience', [CandidateProfileController::class, 'experience'])->name('profile.experience');
    Route::post('/profile/entries/project', [CandidateProfileController::class, 'project'])->name('profile.project');
    Route::post('/profile/entries/recognition', [CandidateProfileController::class, 'recognition'])->name('profile.recognition');
    Route::post('/profile/entries/professional-membership', [CandidateProfileController::class, 'professionalMembership'])->name('profile.membership');
    Route::post('/profile/entries/reference', [CandidateProfileController::class, 'reference'])->name('profile.reference');
    Route::post('/profile/entries/social-profile', [CandidateProfileController::class, 'socialProfile'])->name('profile.social');
    Route::post('/profile/declarations', [CandidateProfileController::class, 'declaration'])->name('profile.declaration');
    Route::post('/profile/consents', [CandidateProfileController::class, 'consent'])->name('profile.consent');
    Route::post('/profile/entries/publication', [CandidateProfileController::class, 'publication'])->name('profile.publication');
    Route::post('/profile/entries/skill', [CandidateProfileController::class, 'skill'])->name('profile.skill');
    Route::post('/profile/entries/talent', [CandidateProfileController::class, 'talent'])->name('profile.talent');
    Route::post('/profile/entries/hobby', [CandidateProfileController::class, 'hobby'])->name('profile.hobby');
    Route::post('/profile/entries/language', [CandidateProfileController::class, 'language'])->name('profile.language');
    Route::delete('/profile/entries/{collection}/{record}', [CandidateProfileController::class, 'remove'])->name('profile.remove');
});
