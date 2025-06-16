<?php

use App\Constants\RolesConstants;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DevicesController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UsersController;
use App\Http\Middleware\CheckUserModel;
use App\Http\Middleware\SetRequestModel;


//Auth::routes();
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix'=>'users', 'middleware'=>'auth:sanctum'],function(){
    Route::get('info', [UsersController::class, 'info']);
    Route::get('notifications', [UsersController::class, 'notificationsList']);
    Route::get('unread-notifications', [UsersController::class, 'unreadNotificationsCount']);
});

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [LoginController::class, 'login']);
    Route::post('register', [RegisterController::class, 'register']);
    Route::post('send-code', [RegisterController::class, 'sendCode']);
    Route::post('validate-code', [RegisterController::class, 'validateCode']);
    Route::post('forgot-password', [RegisterController::class, 'forgotPassword']);
    Route::post('reset-password', [RegisterController::class, 'resetPassword']);
});

// TODO :: put admin only roles
Route::group(['prefix' => 'users/{modelId}',
        'middleware' => ['auth:sanctum', SetRequestModel::class , CheckUserModel::class]], function () {
    Route::get('/', [UsersController::class, 'index']);
    Route::post('/', [UsersController::class, 'update']);
    Route::get('/items', [UsersController::class, 'userItems']);
    Route::get('/settings', [SettingsController::class, 'listSettings']);
    Route::post('/settings', [SettingsController::class, 'createSetting']);
    Route::post('settings/set', [SettingsController::class, 'setSetting']);
//        Route::post('/settings/{settingId}', [SettingsController::class, 'updateSetting']);
    Route::get('/settings/{settingId}/delete', [SettingsController::class, 'deleteSetting']);
    Route::group(['prefix' => 'devices'], function () {
        Route::post('/', [DevicesController::class, 'create']);
        Route::post('/{id}', [DevicesController::class, 'update']);
    });
    Route::group(['prefix' => 'orders'], function () {
        Route::get('/', [OrdersController::class, 'userOrders']);
    });
});

Route::group(['middleware' => [
    'auth:api',
    'role:' . RolesConstants::ADMIN . '|' . RolesConstants::BUSINESS_OWNER]
], function () {
    Route::group(['prefix' => 'users',], function () {
        Route::get('/', [UsersController::class, 'index']);
        Route::post('/', [UsersController::class, 'create']);
    });
});
