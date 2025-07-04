<?php

use App\Http\Controllers\API\APIMasterController;
use App\Http\Controllers\API\APIProfileController;
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
  });

  Route::controller(APIMasterController::class)->group(function () {
     Route::get('get-documents', 'getDocuments')->name('get-documents');
     Route::get('get-states', 'getStates')->name('get-states');
     Route::get('get-cities', 'getCities')->name('get-cities');
  });

  Route::middleware('auth:sanctum')->group(function () {
    
    Route::controller(APIAUthController::class)->group(function () {
      Route::post('logout', 'logout')->name('logout');
    });

    Route::controller(APIMasterController::class)->group(function () {
        Route::get('get-menus', 'getMenus')->name('get-menus');
    });

    Route::controller(APIProfileController::class)->group(function () {
        Route::get('get-profile-details', 'getProfileDetails')->name('get-profile-details');
    });
  });