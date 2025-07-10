<?php

use App\Http\Controllers\API\APIHotelController;
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
      Route::get('dashboard', 'dashboard')->name('dashboard');
      Route::post('logout', 'logout')->name('logout');
    });

    Route::controller(APIMasterController::class)->group(function () {
        Route::get('get-menus', 'getMenus')->name('get-menus');
    });

    Route::controller(APIProfileController::class)->group(function () {
        Route::get('get-profile-details', 'getProfileDetails')->name('get-profile-details');
        Route::post('update-profile', 'updateProfile')->name('update-profile');
    });
    Route::controller(APIHotelController::class)->group(function () {
        Route::post('get-rooms', 'getRooms')->name('get-rooms');
        Route::post('add-room', 'addRoom')->name('add-room');
        Route::post('update-room', 'updateRoom')->name('update-room');
        Route::post('delete-room', 'deleteRoom')->name('delete-room');
        Route::post('change-room-status', 'changeRoomStatus')->name('change-room-status');

        Route::post('get-employees', 'getEmployees')->name('get-employees');
        Route::post('add-employee', 'addEmployee')->name('add-employee');
        Route::post('update-employee', 'updateEmployee')->name('update-employee');
        Route::post('delete-employee', 'deleteEmployee')->name('delete-employee');
        Route::post('change-employee-status', 'changeEmployeeStatus')->name('change-employee-status');

        Route::post('get-bookings', 'getBookings')->name('get-bookings');
        Route::post('get-members', 'getMembers')->name('get-members');
        Route::post('get-visitors', 'getVisitors')->name('get-visitors');

        //Transfer Entries
        Route::post('get-transfer-entries', 'getTransferEntries')->name('get-transfer-entries');
    });
  });