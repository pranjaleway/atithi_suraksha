<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\API\AuthController as APIAUthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

  Route::controller(AuthController::class)->group(function () {
     Route::post('post-hotel-signup', 'postHotelSignup')->name('post-hotel-signup');
  });

  Route::controller(APIAUthController::class)->group(function () {
     Route::post('login', 'login')->name('login');
     Route::post('forgot-password', 'forgotPassword')->name('forgot-password');
     Route::post('reset-password', 'resetPassword')->name('reset-password');
     Route::post('logout', 'logout')->name('logout');
  });