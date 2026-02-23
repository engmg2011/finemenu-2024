<?php


// TODO :: put admin only roles
use App\Http\Controllers\BookmarksController;
use App\Http\Controllers\BranchesController;
use App\Http\Controllers\DietPlansController;
use App\Http\Controllers\AreasController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\InvoicesController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\ReservationsController;
use App\Http\Controllers\SeatsController;
use App\Http\Controllers\WebAppController;
use App\Http\Middleware\SetRequestModel;
use Spatie\ResponseCache\Middlewares\CacheResponse;

// Routes for api/webapp
Route::group(['prefix' => 'webapp',
//    'middleware' => ['recaptcha']
], function () {


    Route::get('menus/{id}', [WebAppController::class, 'nestedMenu']);

    Route::get('branches/{slug}', [WebAppController::class, 'branchMenu'])
        ->middleware(CacheResponse::class.':30'); ;

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
                        Route::get('/', [OrdersController::class, 'userOrders']);
                        Route::get('/{id}', [OrdersController::class, 'showForCreator']);
                    });
                    // bookmarks
                    Route::group(['prefix' => 'bookmarks'], function () {
                        Route::get('/', [BookmarksController::class, 'userBookmarks']);
                        Route::post('/sync', [BookmarksController::class, 'syncBookmarks']);
                    });
                    //reservations
                    Route::group(['prefix' => 'reservations'], function () {
                        Route::get('/', [ReservationsController::class, 'userReservations']);
                        Route::get('/filter', [ReservationsController::class, 'filterWebApp']);
                        Route::get('/{id}', [ReservationsController::class, 'showForReservationOwner']);
                        Route::post('/', [ReservationsController::class, 'create']);
                        Route::post('/check', [ReservationsController::class, 'isAvailable']);
                    });

                    //invoices
                    Route::group(['prefix' => 'invoices'], function () {
                        Route::get('/', [InvoicesController::class, 'userInvoices']);
                        Route::get('/{id}', [InvoicesController::class, 'showForInvoiceOwner']);
//                        Route::post('/', [InvoicesController::class, 'create']);
                    });

                });

                // Business Branch areas
                Route::group(['prefix' => '/areas'], function () {
                    Route::get('/', [AreasController::class, 'index']);
                    Route::get('/{areaId}/seats', [SeatsController::class, 'index']);
                });
            });
        });

    });


    Route::group(['prefix' => 'diet-plans'], function () {
        Route::get('/', [DietPlansController::class, 'index']);
        Route::get('/{id}', [DietPlansController::class, 'show']);
    });

});
