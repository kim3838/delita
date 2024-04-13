<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

Route::group([
    'middleware' => ['guest']
], function(){
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('login');
});

Route::group([
    'middleware' => ['auth:sanctum']
], function(){

    Route::get('user', [AuthenticatedSessionController::class, 'authenticated']);
});

