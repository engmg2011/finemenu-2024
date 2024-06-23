<?php

use App\Constants\RolesConstants;
use App\Http\Controllers\BranchesController;
use App\Http\Controllers\FloorsController;
use App\Http\Controllers\MenusController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\RestaurantsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TablesController;
use App\Http\Middleware\SetRequestModel;
use Illuminate\Support\Facades\Route;


// TODO :: put admin only roles
Route::group(['middleware' => [
    'auth:api',
    'role:' . RolesConstants::ADMIN . '|' . RolesConstants::OWNER]
], function () {

    Route::group(['prefix' => 'restaurants', 'middleware' => [SetRequestModel::class]], function () {
        Route::get('/', [RestaurantsController::class, 'index']);
        Route::get('/{id}', [RestaurantsController::class, 'show']);
        Route::post('/', [RestaurantsController::class, 'create']);
        Route::post('/{id}', [RestaurantsController::class, 'update']);
        Route::post('/{id}/delete', [RestaurantsController::class, 'destroy']);

        Route::get('/{modelId}/settings', [SettingsController::class, 'listSettings']);
        Route::post('/{modelId}/settings/set', [SettingsController::class, 'setSetting']);

        Route::group(['prefix' => '{restaurantId}/branches', 'middleware' => [SetRequestModel::class]], function () {
            Route::get('{modelId}/settings', [SettingsController::class, 'listSettings']);
            Route::post('{modelId}/settings/set', [SettingsController::class, 'setSetting']);
        });

        Route::get('/{modelId}/settings/{settingId}/delete', [SettingsController::class, 'deleteSetting']);

        Route::get('/{id}/orders', [OrdersController::class, 'restaurantOrders']);

        Route::group(['prefix' => '/{restaurantId}/menus'], function () {
            Route::get('/', [MenusController::class, 'index']);
            Route::get('/{id}', [MenusController::class, 'show']);
            Route::post('/', [MenusController::class, 'createModel']);
            Route::post('/{id}/delete', [MenusController::class, 'destroy']);
            Route::post('/{id}', [MenusController::class, 'update']);
        });

        // Restaurant branches
        Route::group(['prefix' => '/{restaurantId}/branches'], function () {
            Route::get('/', [BranchesController::class, 'index']);
            Route::get('/{id}', [BranchesController::class, 'show']);
            Route::post('/', [BranchesController::class, 'createModel']);
            Route::post('/{id}/delete', [BranchesController::class, 'destroy']);
            Route::post('/{id}', [BranchesController::class, 'update']);
            Route::post('/sort', [BranchesController::class, 'sort']);


            // Restaurant Branch floors
            Route::group(['prefix' => '/{branchId}/floors'], function () {
                Route::get('/', [FloorsController::class, 'index']);
                Route::get('/{id}', [FloorsController::class, 'show']);
                Route::post('/', [FloorsController::class, 'createModel']);
                Route::post('/{id}/delete', [FloorsController::class, 'destroy']);
                Route::post('/{id}', [FloorsController::class, 'update']);

                // Restaurant Branch floors tables
                Route::group(['prefix' => '/{floorId}/tables'], function () {
                    Route::get('/', [TablesController::class, 'index']);
                    Route::get('/{id}', [TablesController::class, 'show']);
                    Route::post('/', [TablesController::class, 'createModel']);
                    Route::post('/{id}/delete', [TablesController::class, 'destroy']);
                    Route::post('/{id}', [TablesController::class, 'update']);
                });

            });
        });



    });

});
