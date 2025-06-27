<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HotelBookingController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\HotelEmployeeController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\PoliceStationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SPOfficeController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/clear', function () {
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    //Artisan::call('migrate');

    return redirect()->back();
});

Route::group(['middleware' => ['web']], function () {
    Route::controller(AuthController::class)->group(function () {
        Route::get('/', 'login')->name('login');
        Route::post('authenticate', 'authenticate')->name('authenticate');
        Route::get('forgot-password', 'forgotPassword')->name('forgot-password');
        Route::post('post-forgot-password', 'postForgotPassword')->name('post-forgot-password');
        Route::get('reset-password/{token}', 'resetPassword')->name('password.reset');
        Route::post('post-reset-password', 'postResetPassword')->name('post-reset-password');
        Route::get('hotel-signup', 'hotelSignup')->name('hotel-signup');
        Route::post('post-hotel-signup', 'postHotelSignup')->name('post-hotel-signup');
    });
     Route::controller(MasterController::class)->group(function () {
        Route::get('get-cities', 'getCitiesByState')->name('get-cities');
    });

});
Route::group(['middleware' => ['auth']], function () {

    Route::controller(AuthController::class)->group(function () {
        Route::get('dashboard', 'dashboard')->name('dashboard');
        Route::get('logout', 'logout')->name('logout');

    });

    Route::controller(ProfileController::class)->group(function () {
        Route::get('profile', 'profile')->name('profile');
        Route::post('post-change-password', 'postChangePassword')->name('post-change-password');
        Route::post('update-profile', 'updateProfile')->name('update-profile');
    });

    Route::controller(ActivityLogController::class)->group(function () {
        Route::get('activity-log', 'activityLog')->name('activity-log')->middleware('checkPermission:activity-log,view');
        Route::delete('delete-activity-log', 'deleteActivityLog')->name('delete-activity-log')->middleware('checkPermission:activity-log,delete');
    });

    Route::controller(MasterController::class)->group(function () {
        //Menu
        Route::get('menus', 'menus')->name('menus')->middleware('checkPermission:menu,view');
        Route::post('add-menu', 'storeMenu')->name('add-menu')->middleware('checkPermission:menu,add');
        Route::get('edit-menu/{id}', 'editMenu')->name('edit-menu')->middleware('checkPermission:menu,edit');
        Route::put('update-menu', 'updateMenu')->name('update-menu')->middleware('checkPermission:menu,edit');
        Route::post('change-menu-status', 'changeMenuStatus')->name('change-menu-status')->middleware('checkPermission:menu,edit');
        Route::delete('delete-menu', 'deleteMenu')->name('delete-menu')->middleware('checkPermission:menu,delete');
        Route::post('update-menu-order', 'updateMenuOrder')->name('update-menu-order');
        Route::get('sub-menus/{id}', 'subMenus')->name('sub-menus')->middleware('checkPermission:menu,view');

        //User Type
        Route::get('user-type', 'userType')->name('user-type')->middleware('checkPermission:user-type,view');
        Route::post('add-user-type', 'storeUserType')->name('add-user-type')->middleware('checkPermission:user-type,add');
        Route::get('edit-user-type/{id}', 'editUserType')->name('edit-user-type')->middleware('checkPermission:user-type,edit');
        Route::put('update-user-type', 'updateUserType')->name('update-user-type')->middleware('checkPermission:user-type,edit');
        Route::post('change-user-type-status', 'changeUserTypeStatus')->name('change-user-type-status')->middleware('checkPermission:user-type,edit');
        Route::delete('delete-user-type', 'deleteUserType')->name('delete-user-type')->middleware('checkPermission:user-type,delete');

        //User Access
        Route::get('user-access/{id}', 'userAccess')->name('user-access')->middleware('checkPermission:user-type,add');
        Route::post('add-user-access', 'storeUserAccess')->name('add-user-access')->middleware('checkPermission:user-type,add');
        Route::post('update-access-permission/{id}', 'updateAccessPermission')->name('update-access-permission')->middleware('checkPermission:user-type,edit');
        Route::delete('delete-user-access', 'deleteUserAccess')->name('delete-user-access')->middleware('checkPermission:user-type,delete');

        //State
        Route::get('states', 'states')->name('states')->middleware('checkPermission:states,view');
        Route::post('add-state', 'storeState')->name('add-state')->middleware('checkPermission:states,add');
        Route::get('edit-state', 'editState')->name('edit-state')->middleware('checkPermission:states,edit');
        Route::put('update-state', 'updateState')->name('update-state')->middleware('checkPermission:states,edit');
        Route::post('change-state-status', 'changeStateStatus')->name('change-state-status')->middleware('checkPermission:states,edit');
        Route::delete('delete-state', 'deleteState')->name('delete-state')->middleware('checkPermission:states,delete');

        //City
        Route::get('cities', 'cities')->name('cities')->middleware('checkPermission:cities,view');
        Route::post('add-city', 'storeCity')->name('add-city')->middleware('checkPermission:cities,add');
        Route::get('edit-city', 'editCity')->name('edit-city')->middleware('checkPermission:cities,edit');
        Route::put('update-city', 'updateCity')->name('update-city')->middleware('checkPermission:cities,edit');
        Route::post('change-city-status', 'changeCityStatus')->name('change-city-status')->middleware('checkPermission:cities,edit');
        Route::delete('delete-city', 'deleteCity')->name('delete-city')->middleware('checkPermission:cities,delete');

        //Document
        Route::get('documents', 'documents')->name('documents')->middleware('checkPermission:document,view');
        Route::post('add-document', 'storeDocument')->name('add-document')->middleware('checkPermission:document,add');
        Route::get('edit-document', 'editDocument')->name('edit-document')->middleware('checkPermission:document,edit');
        Route::put('update-document', 'updateDocument')->name('update-document')->middleware('checkPermission:document,edit');
        Route::post('change-document-status', 'changeDocumentStatus')->name('change-document-status')->middleware('checkPermission:document,edit');
        Route::delete('delete-document', 'deleteDocument')->name('delete-document')->middleware('checkPermission:document,delete');
    });


    Route::controller(SPOfficeController::class)->group(function () {
        Route::get('sp-offices', 'spOffices')->name('sp-offices')->middleware('checkPermission:sp-offices,view');
        Route::get('add-sp-office', 'addSPOffice')->name('add-sp-office')->middleware('checkPermission:sp-offices,add');
        Route::post('store-sp-office', 'storeSpOffice')->name('store-sp-office')->middleware('checkPermission:sp-offices,add');
        Route::get('edit-sp-office/{id}', 'editSPOffice')->name('edit-sp-office')->middleware('checkPermission:sp-offices,edit');
        Route::post('update-sp-office', 'updateSpOffice')->name('update-sp-office')->middleware('checkPermission:sp-offices,edit');
        Route::post('change-sp-office-status', 'changeSpOfficeStatus')->name('change-sp-office-status')->middleware('checkPermission:sp-offices,edit');
        Route::delete('delete-sp-office', 'deleteSpOffice')->name('delete-sp-office')->middleware('checkPermission:sp-offices,delete');

        Route::post('update-sp-profile', 'updateSpOffice')->name('update-sp-profile');
    });

    Route::controller(PoliceStationController::class)->group(function () {
        Route::get('police-stations', 'policeStations')->name('police-stations')->middleware('checkPermission:police-stations,view');
        Route::get('add-police-station', 'addPoliceStation')->name('add-police-station')->middleware('checkPermission:police-stations,add');
        Route::post('store-police-station', 'storePoliceStation')->name('store-police-station')->middleware('checkPermission:police-stations,add');
        Route::get('edit-police-station/{id}', 'editPoliceStation')->name('edit-police-station')->middleware('checkPermission:police-stations,edit');
        Route::post('update-police-station', 'updatePoliceStation')->name('update-police-station')->middleware('checkPermission:police-stations,edit');
        Route::post('change-police-station-status', 'changePoliceStationStatus')->name('change-police-station-status')->middleware('checkPermission:police-stations,edit');
        Route::delete('delete-police-station', 'deletePoliceStation')->name('delete-police-station')->middleware('checkPermission:police-stations,delete');

        Route::post('update-police-station-profile', 'updatePoliceStation')->name('update-police-station-profile');

    });

    Route::controller(HotelController::class)->group(function () {
        Route::get('hotels', 'hotels')->name('hotels')->middleware('checkPermission:hotels,view');      
        Route::get('add-hotel', 'addHotel')->name('add-hotel')->middleware('checkPermission:hotels,add');
        Route::post('store-hotel', 'storeHotel')->name('store-hotel')->middleware('checkPermission:hotels,add');
        Route::get('edit-hotel/{id}', 'editHotel')->name('edit-hotel')->middleware('checkPermission:hotels,edit'); 
        Route::post('update-hotel', 'updateHotel')->name('update-hotel')->middleware('checkPermission:hotels,edit');
        Route::post('change-hotel-status', 'changeHotelStatus')->name('change-hotel-status')->middleware('checkPermission:hotels,edit');
        Route::delete('delete-hotel', 'deleteHotel')->name('delete-hotel')->middleware('checkPermission:hotels,delete');
        Route::get('view-hotel-details/{id}', 'viewHotelDetails')->name('view-hotel-details')->middleware('checkPermission:hotels,view'); 
        Route::post('assign-police-station', 'assignPoliceStation')->name('assign-police-station')->middleware('checkPermission:hotels,edit');

        Route::get('hotel-booking-entries', 'hotelBookingEntries')->name('hotel-booking-entries')->middleware('checkPermission:hotels,view');


        Route::post('update-hotel-profile', 'updateHotel')->name('update-hotel-profile');
    });

    Route::controller(HotelEmployeeController::class)->group(function () {
        Route::get('hotel-employees/{id?}', 'hotelEmployees')->name('hotel-employees')->middleware('checkPermission:hotel-employees,view');
        Route::get('add-hotel-employee', 'addHotelEmployee')->name('add-hotel-employee')->middleware('checkPermission:hotel-employees,add');
        Route::post('store-hotel-employee', 'storeHotelEmployee')->name('store-hotel-employee')->middleware('checkPermission:hotel-employees,add');
        Route::get('edit-hotel-employee/{id}', 'editHotelEmployee')->name('edit-hotel-employee')->middleware('checkPermission:hotel-employees,edit');
        Route::post('update-hotel-employee', 'updateHotelEmployee')->name('update-hotel-employee')->middleware('checkPermission:hotel-employees,edit');
        Route::post('change-hotel-employee-status', 'changeHotelEmployeeStatus')->name('change-hotel-employee-status')->middleware('checkPermission:hotel-employees,edit');
        Route::delete('delete-hotel-employee', 'deleteHotelEmployee')->name('delete-hotel-employee')->middleware('checkPermission:hotel-employees,delete');
        Route::get('view-hotel-employee-details/{id}', 'viewHotelEmployeeDetails')->name('view-hotel-employee-details')->middleware('checkPermission:hotel-employees,view'); 
        
        Route::post('update-hotel-employee-profile', 'updateHotelEmployee')->name('update-hotel-employee-profile');
    });

    Route::controller(HotelBookingController::class)->group(function () {
        Route::get('bookings/{id?}/{date?}', 'booking')->name('bookings')->middleware('checkPermission:bookings,view');
        Route::get('add-booking', 'addBooking')->name('add-booking')->middleware('checkPermission:bookings,add');
        Route::post('store-booking', 'storeBooking')->name('store-booking')->middleware('checkPermission:bookings,add');
        Route::delete('delete-booking', 'deleteBooking')->name('delete-booking')->middleware('checkPermission:bookings,delete');
        Route::get('members/{id}', 'getMembers')->name('members')->middleware('checkPermission:bookings,view');
        Route::get('edit-booking-details/{id}', 'editBooking')->name('edit-booking-details')->middleware('checkPermission:bookings,edit');
        Route::get('add-member/{id}', 'addMember')->name('add-member')->middleware('checkPermission:bookings,add');
        Route::post('store-member', 'storeMember')->name('store-member')->middleware('checkPermission:bookings,add');
        Route::delete('delete-member', 'deleteMember')->name('delete-member')->middleware('checkPermission:bookings,delete');
        Route::get('view-booking-details/{id}', 'viewDetails')->name('view-booking-details')->middleware('checkPermission:bookings,view');

        //Uploaded Entries
        Route::get('uploaded-entries/{id?}/{date?}', 'uploadedEntries')->name('uploaded-entries')->middleware('checkPermission:uploaded-entries,view');
        Route::post('store-uploaded-entry', 'storeUploadedEntry')->name('store-uploaded-entry')->middleware('checkPermission:uploaded-entries,add');
        Route::delete('delete-uploaded-entry', 'deleteUploadedEntry')->name('delete-uploaded-entry')->middleware('checkPermission:uploaded-entries,delete');

        //Transfer Entries
        Route::get('transfer-entries/', 'transferEntries')->name('transfer-entries')->middleware('checkPermission:transfer-entries,view');
        Route::get('transfer-manual-entries/', 'addTranserManualEntries')->name('transfer-manual-entries')->middleware('checkPermission:transfer-entries,add');
        Route::get('transfer-uploaded-entries/', 'addTranserUploadedEntries')->name('transfer-uploaded-entries')->middleware('checkPermission:transfer-entries,add');
        Route::post('store-manual-transfer-entry', 'storeManualTransferEntries')->name('store-manual-transfer-entry')->middleware('checkPermission:transfer-entries,add');
        Route::post('store-uploaded-transfer-entry', 'storeUploadedTransferEntries')->name('store-uploaded-transfer-entry')->middleware('checkPermission:transfer-entries,add');
    });
});
