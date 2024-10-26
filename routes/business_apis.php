<?php

use App\Constants\RolesConstants;
use App\Http\Controllers\BranchesController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\FloorsController;
use App\Http\Controllers\MenusController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TablesController;
use App\Http\Controllers\UsersController;
use App\Http\Middleware\SetRequestModel;
use Illuminate\Support\Facades\Route;

// Admin and business users
Route::group(['middleware' => ['auth:sanctum', 'role:' . businessRoles()]
], function () {

    Route::group(['prefix' => 'business', 'middleware' => [SetRequestModel::class]], function () {
        Route::get('/', [BusinessController::class, 'index']);
        Route::get('/{id}', [BusinessController::class, 'show']);
        Route::post('/', [BusinessController::class, 'create']);
        Route::post('/{id}', [BusinessController::class, 'update']);
        Route::post('/{id}/delete', [BusinessController::class, 'destroy']);

        Route::get('/{modelId}/settings', [SettingsController::class, 'listSettings']);
        Route::post('/{modelId}/settings/set', [SettingsController::class, 'setSetting']);


        Route::get('/{modelId}/settings/{settingId}/delete', [SettingsController::class, 'deleteSetting']);


        Route::group(['prefix' => '/{businessId}/menus'], function () {
            Route::get('/', [MenusController::class, 'index']);
            Route::get('/{id}', [MenusController::class, 'show']);
            Route::post('/', [MenusController::class, 'createModel']);
            Route::post('/{id}/delete', [MenusController::class, 'destroy']);
            Route::post('/{id}', [MenusController::class, 'update']);
        });

        // Business branches
        Route::group(['prefix' => '/{businessId}/branches'], function () {
            Route::get('/', [BranchesController::class, 'index']);
            Route::post('/', [BranchesController::class, 'createModel']);
            Route::post('/sort', [BranchesController::class, 'sort']);

            Route::group(['prefix' => '/{branchId}'], function () {
                Route::get('', [BranchesController::class, 'show']);
                Route::post('', [BranchesController::class, 'update']);
                Route::post('/delete', [BranchesController::class, 'destroy']);

                Route::group(['prefix' => 'orders'], function () {
                    Route::get('/', [OrdersController::class, 'branchOrders']);
                    //                Route::get('/', [OrdersController::class, 'index']);
                    Route::get('/{id}', [OrdersController::class, 'show']);
                    Route::post('/', [OrdersController::class, 'create']);
                    Route::post('/{id}', [OrdersController::class, 'update']);
                });

                // Business Branch floors
                Route::group(['prefix' => '/floors'], function () {
                    Route::get('/', [FloorsController::class, 'index']);
                    Route::get('/{id}', [FloorsController::class, 'show']);
                    Route::post('/', [FloorsController::class, 'createModel']);
                    Route::post('/{id}/delete', [FloorsController::class, 'destroy']);
                    Route::post('/{id}', [FloorsController::class, 'update']);

                    // Business Branch floors tables
                    Route::group(['prefix' => '/{floorId}/tables'], function () {
                        Route::get('/', [TablesController::class, 'index']);
                        Route::get('/{id}', [TablesController::class, 'show']);
                        Route::post('/', [TablesController::class, 'createModel']);
                        Route::post('/{id}/delete', [TablesController::class, 'destroy']);
                        Route::post('/{id}', [TablesController::class, 'update']);
                    });

                });

            });

            Route::group(['prefix' => 'kitchen'], function () {
                Route::get('orders', [OrdersController::class, 'kitchenOrders']);
                Route::get('business/{id}', [BusinessController::class, 'show']);
            });

        });


    });

});

// TODO :: put admin only roles (Business owner)
Route::group(['prefix' => 'business', 'middleware' => [SetRequestModel::class]], function () {

    Route::group(['prefix' => '{businessId}/branches/{modelId}'], function () {

        Route::get('settings', [SettingsController::class, 'listSettings']);
        Route::post('settings/set', [SettingsController::class, 'setSetting']);

        Route::get('reference-qr', [BranchesController::class, 'referenceQr']);
        Route::get('create-login-qr', [UsersController::class, 'createLoginQr']);
        Route::get('login-qr', [UsersController::class, 'loginByQr'])->name('login.qr');


//            Route::post('reference-qr', [BranchesController::class, 'PreviewQR']);
//            Route::get('generate-qr/{userId?}', 'FeedbackController@generateQR');


    });


});

function businessRoles(): string
{
    return RolesConstants::ADMIN . '|' . RolesConstants::BUSINESS_OWNER . '|' .
        RolesConstants::BRANCH_MANAGER . '|' . RolesConstants::KITCHEN . '|' .
        RolesConstants::SUPERVISOR . '|' . RolesConstants::CASHIER . '|' .
        RolesConstants::DRIVER;
}
