<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\UpdateUserPasswordController;

Route::group([
    'middleware' => ['guest']
], function(){
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('login');
});

Route::group([
    'middleware' => ['auth:sanctum']
], function(){

    Route::get('user', [AuthenticatedSessionController::class, 'authenticated']);

    //Verify X-XSRF-TOKEN on destructive action
    Route::post('test-post', [AuthenticatedSessionController::class, 'testPost']);

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::post('update-password', [UpdateUserPasswordController::class, 'store'])->name('password.update');
    Route::post('logout-other-device', [AuthenticatedSessionController::class, 'logoutOtherDevice']);
    Route::get('sessions', [AuthenticatedSessionController::class, 'sessions']);
});

