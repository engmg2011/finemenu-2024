<?php


// TODO :: put admin only roles
use App\Http\Controllers\DietPlansController;
use App\Http\Controllers\FloorsController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\TablesController;
use App\Http\Controllers\WebAppController;
use App\Http\Middleware\SetRequestModel;

// Routes for api/webapp
Route::group(['prefix' => 'webapp',
//    'middleware' => ['recaptcha']
], function () {


    Route::get('menus/{id}', [WebAppController::class, 'nestedMenu']);
    Route::get('diet-business/{id}', [WebAppController::class, 'dietBusiness']);

    Route::get('branches/{slug}', [WebAppController::class, 'branchMenu']);


    Route::group(['prefix' => 'business', 'middleware' => [SetRequestModel::class]], function () {
        Route::get('/', [BusinessController::class, 'allList']);

        // Business Branch floors
        Route::group(['prefix' => '/{businessId}/branches/{branchId}/floors'], function () {
            Route::get('/', [FloorsController::class, 'index']);
            Route::get('/{floorId}/tables', [TablesController::class, 'index']);
        });
    });


    Route::group(['prefix' => 'diet-plans'], function () {
        Route::get('/', [DietPlansController::class, 'index']);
        Route::get('/{id}', [DietPlansController::class, 'show']);
    });


});
