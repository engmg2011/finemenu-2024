<?php

use App\Constants\RolesConstants;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\BranchesController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\FloorsController;
use App\Http\Controllers\HolidaysController;
use App\Http\Controllers\InvoicesController;
use App\Http\Controllers\ItemsController;
use App\Http\Controllers\MenusController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\ReservationsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TablesController;
use App\Http\Controllers\UsersController;
use App\Http\Middleware\SetRequestModel;
use Illuminate\Support\Facades\Route;

if (!function_exists('businessRoles')) {
    function businessRoles(): string
    {
        return RolesConstants::ADMIN . '|' . RolesConstants::BUSINESS_OWNER . '|' .
            RolesConstants::BRANCH_MANAGER . '|' . RolesConstants::KITCHEN . '|' .
            RolesConstants::SUPERVISOR . '|' . RolesConstants::CASHIER . '|' .
            RolesConstants::DRIVER;
    }
}

// Admin and business users
Route::group(['middleware' => [
    'auth:sanctum', 'role:' . businessRoles()
]
], function () {

    Route::group(['prefix' => 'business', 'middleware' => [SetRequestModel::class]], function () {
        Route::get('/', [BusinessController::class, 'index']);
        Route::get('/{id}', [BusinessController::class, 'show']);
        Route::post('/', [BusinessController::class, 'create']);
        Route::post('/{id}', [BusinessController::class, 'update']);
        Route::post('/{id}/delete', [BusinessController::class, 'destroy']);

        Route::group(["prefix" => "/{modelId}/settings"], function () {
            Route::get('/', [SettingsController::class, 'listSettings']);
            Route::post('/set', [SettingsController::class, 'setSetting']);
            Route::get('/{settingId}/delete', [SettingsController::class, 'deleteSetting']);
        });

        Route::group(['prefix' => '{businessId}'], function () {

            Route::group(['prefix' => 'menus'], function () {
                Route::get('/', [MenusController::class, 'index']);
                Route::get('/{id}', [MenusController::class, 'show']);
                Route::post('/', [MenusController::class, 'createModel']);
                Route::post('/{id}/delete', [MenusController::class, 'destroy']);
                Route::post('/{id}', [MenusController::class, 'update']);
            });

            Route::group(['prefix' => 'holidays'], function () {
                Route::get('/', [HolidaysController::class, 'index']);
                Route::get('/filter', [HolidaysController::class, 'filter']);
                Route::get('/{id}', [HolidaysController::class, 'show']);
                Route::post('/', [HolidaysController::class, 'createModel']);
                Route::post('/{id}/delete', [HolidaysController::class, 'destroy']);
                Route::post('/{id}', [HolidaysController::class, 'update']);
            });

            // Business branches
            Route::group(['prefix' => 'branches'], function () {
                Route::get('/', [BranchesController::class, 'index']);
                Route::post('/', [BranchesController::class, 'createModel']);
                Route::post('/sort', [BranchesController::class, 'sort']);

                Route::group(['prefix' => '/{branchId}'], function () {
                    Route::get('', [BranchesController::class, 'show']);
                    Route::post('', [BranchesController::class, 'update']);
                    Route::post('/delete', [BranchesController::class, 'destroy']);

                    Route::group(['prefix' => 'items', 'middleware' => [SetRequestModel::class]], function () {
                        Route::get('/', [ItemsController::class, 'index']);
                    });

                    Route::group(['prefix' => 'orders'], function () {
                        Route::get('/', [OrdersController::class, 'branchOrders']);
                        Route::get('/{id}', [OrdersController::class, 'show']);
                        Route::post('/', [OrdersController::class, 'create']);
                        Route::post('/{id}', [OrdersController::class, 'update']);
                    });

                    Route::group(['prefix' => 'reservations'], function () {
                        Route::get('/', [ReservationsController::class, 'index']);
                        Route::get('/filter', [ReservationsController::class, 'filter']);
                        Route::get('/{id}', [ReservationsController::class, 'show']);
                        Route::post('/', [ReservationsController::class, 'create']);
                        Route::post('/{id}', [ReservationsController::class, 'update']);
                    });

                    Route::group(['prefix' => 'invoices'], function () {
                        Route::get('/', [InvoicesController::class, 'index']);
                        Route::get('/filter', [InvoicesController::class, 'filter']);
                        Route::get('/{id}', [InvoicesController::class, 'show']);
                        Route::post('/', [InvoicesController::class, 'create']);
                        Route::post('/{id}', [InvoicesController::class, 'update']);
                        Route::post('/{id}/delete', [InvoicesController::class, 'destroy']);
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

                    // Audit Log
                    Route::group(['prefix' => '/audits'], function () {
                        Route::get('/', [AuditController::class, 'index']);
                        Route::get('/filter', [AuditController::class, 'filter']);
                    });
                });

                Route::group(['prefix' => 'kitchen'], function () {
                    Route::get('orders', [OrdersController::class, 'kitchenOrders']);
                    Route::get('business/{id}', [BusinessController::class, 'show']);
                });

            });

            Route::group(['prefix' => 'items'], function () {
                Route::get('/', [ItemsController::class, 'index']);
                Route::get('/search', [ItemsController::class, 'search']);
                Route::get('/{id}', [ItemsController::class, 'show']);
                Route::post('/', [ItemsController::class, 'create']);
                Route::post('/{id}/delete', [ItemsController::class, 'destroy']);
                Route::post('/sort', [ItemsController::class, 'sort']);
                Route::post('/{id}', [ItemsController::class, 'update']);
                Route::get('/{modelId}/settings', [SettingsController::class, 'listSettings']);
                Route::post('/{id}/settings', [SettingsController::class, 'createSetting']);
                Route::post('/{id}/settings/{settingId}', [SettingsController::class, 'updateSetting']);
                Route::get('/{id}/settings/{settingId}/delete', [SettingsController::class, 'deleteSetting']);
                Route::get('/{id}/holidays', [ItemsController::class, 'listHolidays']);
                Route::post('/{id}/holidays/sync', [ItemsController::class, 'syncHolidays']);
            });

            Route::group(['prefix' => 'users',], function () {
                Route::get('/', [UsersController::class, 'index']);
                Route::post('/', [UsersController::class, 'create']);
                Route::post('/search', [UsersController::class, 'search']);
                Route::post('/{id}', [UsersController::class, 'update']);
            });

        });


    });

});
//'auth:sanctum', 'role:' . businessRoles()

// TODO :: put admin only roles (Business owner)
Route::group(['prefix' => 'business', 'middleware' =>
    [
        'auth:sanctum',
        'role:' . businessRoles(),
        SetRequestModel::class
    ]], function () {

    Route::group(['prefix' => '{businessId}/'], function () {

        // putting here for settings {modelId}
        Route::group(['prefix' => 'branches/{modelId}'], function () {
            Route::get('settings', [SettingsController::class, 'listSettings']);
            Route::post('settings/set', [SettingsController::class, 'setSetting']);
            Route::get('reference-qr', [BranchesController::class, 'referenceQr']);
            Route::get('create-login-qr', [UsersController::class, 'createLoginQr']);
            Route::get('login-qr', [UsersController::class, 'loginByQr'])->name('login.qr');
        });
    });


});
