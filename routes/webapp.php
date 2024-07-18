<?php


// TODO :: put admin only roles
use App\Http\Controllers\DietPlansController;
use App\Http\Controllers\FloorsController;
use App\Http\Controllers\RestaurantsController;
use App\Http\Controllers\TablesController;
use App\Http\Controllers\WebAppController;
use App\Http\Middleware\SetRequestModel;

// Routes for api/webapp
Route::group(['prefix' => 'webapp',
//    'middleware' => ['recaptcha']
], function () {


    Route::get('menus/{id}', [WebAppController::class, 'nestedMenu']);
    Route::get('diet-restaurant/{id}', [WebAppController::class, 'dietRestaurant']);

    Route::get('branches/{slug}', [WebAppController::class, 'branchMenu']);


    Route::group(['prefix' => 'restaurants', 'middleware' => [SetRequestModel::class]], function () {
        Route::get('/', [RestaurantsController::class, 'allList']);

        // Restaurant Branch floors
        Route::group(['prefix' => '/{restaurantId}/branches/{branchId}/floors'], function () {
            Route::get('/', [FloorsController::class, 'index']);
            Route::get('/{floorId}/tables', [TablesController::class, 'index']);
        });
    });


    Route::group(['prefix' => 'diet-plans'], function () {
        Route::get('/', [DietPlansController::class, 'index']);
        Route::get('/{id}', [DietPlansController::class, 'show']);
    });


});
