<?php

use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

Route::get('/php-info', function () {
    phpinfo();
});

Route::group([
    'middleware' => ['signed']
], function(){
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)->name('verification.verify');
});

Route::group([
    'middleware' => ['guest']
], function(){

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::get('/admin/error-logs/download', [\App\Http\Controllers\Internal\LogDownloadController::class, 'downloadErrors'])->name('download.logs.errors');
Route::get('/admin/debug-logs/download', [\App\Http\Controllers\Internal\LogDownloadController::class, 'downloadDebugs'])->name('download.logs.debugs');
Route::get('/admin/auth-logs/download', [\App\Http\Controllers\Internal\LogDownloadController::class, 'downloadAuths'])->name('download.logs.auth');
