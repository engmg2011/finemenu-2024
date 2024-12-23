<?php


// TODO :: put admin only roles
use App\Http\Controllers\BookmarksController;
use App\Http\Controllers\BranchesController;
use App\Http\Controllers\DietPlansController;
use App\Http\Controllers\FloorsController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\TablesController;
use App\Http\Controllers\WebAppController;
use App\Http\Middleware\SetRequestModel;

// Routes for api/webapp
Route::group(['prefix' => 'webapp',
//    'middleware' => ['recaptcha']
], function () {


    Route::get('menus/{id}', [WebAppController::class, 'nestedMenu']);

    Route::get('branches/{slug}', [WebAppController::class, 'branchMenu']);

    Route::get('branches/{slug}/menu-type', [WebAppController::class, 'menuType']);


    Route::group(['prefix' => 'business', 'middleware' => [SetRequestModel::class]], function () {
        Route::get('/', [BusinessController::class, 'businessList']);
        Route::get('/types', [WebAppController::class , 'businessTypes']);

        // Business branches
        Route::group(['prefix' => '/{businessId}/branches'], function () {
            Route::get('/', [BranchesController::class, 'index']);

            Route::group(['prefix' => '/{branchId}'], function () {
                // Logged in only features
                Route::group([ 'middleware'=>'auth:sanctum', ], function () {
                    // orders
                    Route::group(['prefix' => 'orders'], function () {
                        Route::post('/', [OrdersController::class, 'create']);
                        Route::get('/{id}', [OrdersController::class, 'showForCreator']);
                    });
                    // bookmarks
                    Route::group(['prefix' => 'bookmarks'], function () {
                        Route::get('/', [BookmarksController::class, 'userBookmarks']);
                        Route::post('/sync', [BookmarksController::class, 'syncBookmarks']);
                    });
                });

                // Business Branch floors
                Route::group(['prefix' => '/floors'], function () {
                    Route::get('/', [FloorsController::class, 'index']);
                    Route::get('/{floorId}/tables', [TablesController::class, 'index']);
                });
            });
        });

    });


    Route::group(['prefix' => 'diet-plans'], function () {
        Route::get('/', [DietPlansController::class, 'index']);
        Route::get('/{id}', [DietPlansController::class, 'show']);
    });

});
