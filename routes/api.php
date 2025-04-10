<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmedTwoFactorAuthenticationController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\TwoFactorAuthenticatedSessionController;
use App\Http\Controllers\Auth\TwoFactorAuthenticationController;
use App\Http\Controllers\Auth\TwoFactorQrCodeController;
use App\Http\Controllers\Auth\TwoFactorRecoveryCodeController;
use App\Http\Controllers\Auth\TwoFactorSecretKeyController;
use App\Http\Controllers\Auth\UpdateUserPasswordController;
use App\Http\Controllers\FormModuleController;
use App\Http\Controllers\Internal\UtilityController;
use App\Http\Controllers\PrototypeController;
use Illuminate\Support\Facades\Route;

Route::get('selections/{module}', [FormModuleController::class, 'selection'])->name('selection');

Route::group([
    'middleware' => ['guest']
], function(){
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('login');
    Route::post('two-factor-login', [TwoFactorAuthenticatedSessionController::class, 'store'])->name('two-factor.login');
});

Route::group([
    'middleware' => ['auth:sanctum'],
    'prefix' => 'utility'
], function(){

    //Hit server
    Route::get('hit', [UtilityController::class, 'hit']);
    Route::post('debug', [UtilityController::class, 'debug']);

    //Verify X-XSRF-TOKEN on destructive action
    Route::post('post', [UtilityController::class, 'post']);
});

Route::group([
    'middleware' => ['auth:sanctum']
], function(){

    Route::get('user', [AuthenticatedSessionController::class, 'authenticated']);
    Route::get('associated-companies', [AuthenticatedSessionController::class, 'associatedCompanies']);

    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->name('verification.send');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::post('update-password', [UpdateUserPasswordController::class, 'store'])->name('password.update');
    Route::get('confirmed-password-status', [AuthenticatedSessionController::class, 'confirmedPasswordStatus'])->name('password.confirmation');
    Route::post('confirm-password', [AuthenticatedSessionController::class, 'confirmPassword'])->name('password.confirm');
    Route::post('logout-other-device', [AuthenticatedSessionController::class, 'logoutOtherDevice']);
    Route::get('sessions', [AuthenticatedSessionController::class, 'sessions']);

    Route::post('two-factor-authentication', [TwoFactorAuthenticationController::class, 'store'])->name('two-factor.enable');
    Route::get('two-factor-qr-code', [TwoFactorQrCodeController::class, 'show'])->name('two-factor.qr-code');
    Route::get('two-factor-secret-key', [TwoFactorSecretKeyController::class, 'show'])->name('two-factor.secret-key');
    Route::get('two-factor-recovery-codes', [TwoFactorRecoveryCodeController::class, 'index'])->name('two-factor.recovery-codes');
    Route::post('confirmed-two-factor-authentication', [ConfirmedTwoFactorAuthenticationController::class, 'store'])->name('two-factor.confirm');
    Route::delete('two-factor-authentication', [TwoFactorAuthenticationController::class, 'destroy']);
});

Route::group([
    'middleware' => ['auth:sanctum', 'verified'],
    'prefix' => 'v1'
], function(){

    Route::group([
        'as' => 'prototypes.'
    ], function(){
        Route::get('prototypes', [PrototypeController::class, 'index'])->name('index');
    });
});
