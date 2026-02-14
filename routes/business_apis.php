<?php

use App\Constants\RolesConstants;
use App\Http\Controllers\AddonsController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\BranchesController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ConfigurationsController;
use App\Http\Controllers\AreasController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DiscountsController;
use App\Http\Controllers\HolidaysController;
use App\Http\Controllers\InvoicesController;
use App\Http\Controllers\ItemsController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MenusController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\PricesController;
use App\Http\Controllers\ReservationsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SeatsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\StatisticsController;
use App\Http\Middleware\SameBusinessMiddleware;
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
Route::group(['middleware' => ['throttle:300,1',
    'auth:sanctum', 'role:' . businessRoles()
]
], function () {

    Route::group(['prefix' => 'business', 'middleware' => [SetRequestModel::class, SameBusinessMiddleware::class]], function () {
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

                    Route::group(['prefix' => 'items' ], function () {
                        Route::get('/', [ItemsController::class, 'index']);
                        Route::get('{itemId}/qr-code', [ItemsController::class, 'qrCode']);
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

                    // Business Branch areas
                    Route::group(['prefix' => '/areas'], function () {
                        Route::get('/', [AreasController::class, 'index']);
                        Route::get('/{id}', [AreasController::class, 'show']);
                        Route::post('/', [AreasController::class, 'createModel']);
                        Route::post('/{id}/delete', [AreasController::class, 'destroy']);
                        Route::post('/{id}', [AreasController::class, 'update']);

                        // Business Branch areas seats
                        Route::group(['prefix' => '/{areaId}/seats'], function () {
                            Route::get('/', [SeatsController::class, 'index']);
                            Route::get('/{id}', [SeatsController::class, 'show']);
                            Route::post('/', [SeatsController::class, 'createModel']);
                            Route::post('/{id}/delete', [SeatsController::class, 'destroy']);
                            Route::post('/{id}', [SeatsController::class, 'update']);
                        });

                    });

                    // Audit Log
                    Route::group(['prefix' => '/audits'], function () {
                        Route::get('/', [AuditController::class, 'index']);
                        Route::get('/filter', [AuditController::class, 'filter']);
                    });

                    Route::group(['prefix' => 'permissions'], function () {
                        Route::get('/services', [PermissionsController::class, 'services']);
                        Route::get('/actions', [PermissionsController::class, 'actions']);
                        Route::get('user/{userId}', [PermissionsController::class, 'getUserPermissions']);
                        Route::post('user/{userId}/set', [PermissionsController::class, 'setUserPermissions']);
                    });

                });

                Route::group(['prefix' => 'kitchen'], function () {
                    Route::get('orders', [OrdersController::class, 'kitchenOrders']);
                    Route::get('business/{id}', [BusinessController::class, 'show']);
                });

            });

            Route::group(['prefix' => 'categories'], function () {
                Route::get('/', [CategoriesController::class, 'index']);
                Route::post('/', [CategoriesController::class, 'create']);
                Route::post('/sort', [CategoriesController::class, 'updateSort']);
                Route::group(['prefix' => '{modelId}'], function () {
                    Route::get('/', [CategoriesController::class, 'show']);
                    Route::post('/', [CategoriesController::class, 'update']);
                    Route::post('/delete', [CategoriesController::class, 'destroy']);

                    Route::group(["prefix" => "/settings"], function () {
                        Route::get('/', [SettingsController::class, 'listSettings']);
                        Route::post('/set', [SettingsController::class, 'setSetting']);
                        Route::get('/{settingId}/delete', [SettingsController::class, 'deleteSetting']);
                    });
                });
            });

            Route::group(['prefix' => 'items'], function () {
                Route::get('/', [ItemsController::class, 'index']);
                Route::get('/search', [ItemsController::class, 'search']);
                Route::get('/{id}', [ItemsController::class, 'show']);
                Route::post('/', [ItemsController::class, 'create']);
                Route::post('/sort', [ItemsController::class, 'sort']);
                Route::post('/{id}', [ItemsController::class, 'update']);

                Route::group(['prefix' => '{modelId}/settings'], function () {
                    Route::get('/', [SettingsController::class, 'listSettings']);
                    Route::post('/set', [SettingsController::class, 'setSetting']);
                    Route::post('/', [SettingsController::class, 'createSetting']);
                    Route::post('/{settingId}', [SettingsController::class, 'updateSetting']);
                    Route::get('/{settingId}/delete', [SettingsController::class, 'deleteSetting']);
                });

                Route::group(['prefix' => '{id}'], function () {
                    Route::get('holidays', [ItemsController::class, 'listHolidays']);
                    Route::post('holidays/sync', [ItemsController::class, 'syncHolidays']);
                    Route::post('delete', [ItemsController::class, 'destroy']);
                    Route::post('media/sort', [MediaController::class, 'itemMediaSort']);
                });
            });

            Route::group(['prefix' => 'users',], function () {
                Route::get('/', [UsersController::class, 'index']);
                Route::get('/{userId}', [UsersController::class, 'show']);
                Route::post('/', [UsersController::class, 'create']);
                Route::post('/search', [UsersController::class, 'search']);
                Route::post('/{modelId}', [UsersController::class, 'update']);
            });

            Route::group(['prefix' => 'config'], function () {
                Route::post('/', [ConfigurationsController::class, 'setBusinessConfig']);
            });

            Route::get('delete', [BusinessController::class, 'destroy']);

            Route::group(['prefix' => 'statistics'], function () {
                Route::get('/basic', [StatisticsController::class, 'getBasicStatistics']);
                Route::get('/reservations', [StatisticsController::class, 'getReservationsStatistics']);
                Route::get('/revenue', [StatisticsController::class, 'getRevenueStatistics']);
                Route::get('/capacity', [StatisticsController::class, 'getCapacity']);
                Route::get('/employees-progress', [StatisticsController::class, 'getEmployeesReservationsProgress']);
            });

            Route::get('notes', [BranchesController::class, 'notes']);

            Route::group(['prefix' => 'prices'], function () {
                Route::get('/', [PricesController::class, 'index']);
                Route::get('/{id}', [PricesController::class, 'show']);
                Route::post('/', [PricesController::class, 'create']);
                Route::post('/{id}', [PricesController::class, 'update']);
                Route::post('/{id}/delete', [PricesController::class, 'destroy']);
            });

            Route::group(['prefix' => 'discounts'], function () {
                Route::get('/', [DiscountsController::class, 'index']);
                Route::get('/{id}', [DiscountsController::class, 'show']);
                Route::post('/', [DiscountsController::class, 'create']);
                Route::post('/{id}', [DiscountsController::class, 'update']);
                Route::post('/{id}/delete', [DiscountsController::class, 'destroy']);
            });

            Route::group(['prefix' => 'addons'], function () {
                Route::get('/', [AddonsController::class, 'index']);
                Route::get('/{id}', [AddonsController::class, 'show']);
                Route::post('/', [AddonsController::class, 'create']);
                Route::post('/{id}', [AddonsController::class, 'update']);
                Route::post('/{id}/delete', [AddonsController::class, 'destroy']);
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
        SetRequestModel::class,
        SameBusinessMiddleware::class
    ]], function () {

    Route::group(['prefix' => '{businessId}/'], function () {

        // putting here for settings {modelId}
        Route::group(['prefix' => 'branches/{modelId}'], function () {
            Route::get('settings', [SettingsController::class, 'listSettings']);
            Route::post('settings/set', [SettingsController::class, 'setSetting']);
            Route::get('reference-qr', [BranchesController::class, 'referenceQr']);
        });
        Route::group(['prefix' => 'branches/{branchId}'], function () {
            Route::get('create-login-qr', [UsersController::class, 'createLoginQr']);
        });
    });
});

// no auth
Route::group(['middleware' => 'throttle:60,1', 'prefix' => 'business/{businessId}/'], function () {
    Route::get('/', [UsersController::class, 'index']);
    Route::get('config', [ConfigurationsController::class, 'getBusinessConfig']);
    Route::get('/employees', [UsersController::class, 'employees']);
    Route::group(['prefix' => 'branches/{branchId}'], function () {
        Route::get('/', [UsersController::class, 'index']);
        Route::get('login-qr', [UsersController::class, 'loginByQr'])->name('login.qr');
    });
});
