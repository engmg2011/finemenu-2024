<?php

use App\Constants\RolesConstants;
use App\Http\Controllers\AddonsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContentsController;
use App\Http\Controllers\DevicesController;
use App\Http\Controllers\DiscountsController;
use App\Http\Controllers\EventsController;
use App\Http\Controllers\HotelsController;
use App\Http\Controllers\ItemsController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\PackagesController;
use App\Http\Controllers\PlansController;
use App\Http\Controllers\PricesController;
use App\Http\Controllers\RestaurantsController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SubscriptionsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\WebAppController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Auth::routes();
Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [LoginController::class, 'login']);
    Route::post('register', [RegisterController::class, 'register']);
    Route::post('send-code', [RegisterController::class, 'sendCode']);
    Route::post('validate-code', [RegisterController::class, 'validateCode']);
});


// TODO :: put admin only roles
Route::group(['middleware' => ['auth:api', 'role:' . RolesConstants::ADMIN . '|' . RolesConstants::OWNER]], function () {

    Route::group(['prefix' => 'menu'], function () {
        Route::get("/{id}", [RestaurantsController::class, 'menu']);
    });

    Route::group(['prefix' => 'hotels'], function () {
        Route::get('/', [HotelsController::class, 'index']);
        Route::get('/{id}', [HotelsController::class, 'show']);
        Route::post('/', [HotelsController::class, 'create'])->middleware('role:' . RolesConstants::ADMIN);
        Route::post('/{id}', [HotelsController::class, 'update'])
            ->middleware('role:' . RolesConstants::ADMIN . '|permission:hotels.owner.{id}');
    });

    Route::group(['prefix' => 'restaurants'], function () {
        Route::get('/', [RestaurantsController::class, 'index']);
        Route::get('/{id}', [RestaurantsController::class, 'show']);
        Route::post('/', [RestaurantsController::class, 'create']);
        Route::post('/{id}', [RestaurantsController::class, 'update']);
    });

    Route::group(['prefix' => 'contacts'], function () {
        Route::get('/', [ContactController::class, 'index']);
        Route::get('/{id}', [ContactController::class, 'show']);
        Route::post('/', [ContactController::class, 'create']);
        Route::post('/{id}', [ContactController::class, 'update']);
    });

    Route::group(['prefix' => 'categories'], function () {
        Route::get('/', [CategoriesController::class, 'index']);
        Route::get('/{id}', [CategoriesController::class, 'show']);
        Route::post('/', [CategoriesController::class, 'create']);
        Route::post('/{id}/delete', [CategoriesController::class, 'destroy']);
        Route::post('/sort', [CategoriesController::class, 'updateSort']);
        Route::post('/{id}', [CategoriesController::class, 'update']);
    });

    Route::group(['prefix' => 'items'], function () {
        Route::get('/', [ItemsController::class, 'index']);
        Route::get('/{id}', [ItemsController::class, 'show']);
        Route::post('/', [ItemsController::class, 'create']);
        Route::post('/{id}/delete', [ItemsController::class, 'destroy']);
        Route::post('/sort', [ItemsController::class, 'sort']);
        Route::post('/{id}', [ItemsController::class, 'update']);
    });

    Route::group(['prefix' => 'prices'], function () {
        Route::post('/{id}/delete', [PricesController::class, 'destroy']);
    });

    Route::group(['prefix' => 'services'], function () {
        Route::get('/', [ServicesController::class, 'index']);
        Route::get('/{id}', [ServicesController::class, 'show']);
        Route::post('/', [ServicesController::class, 'create']);
        Route::post('/{id}/delete', [ServicesController::class, 'destroy']);
        Route::post('/{id}', [ServicesController::class, 'update']);
    });

    Route::group(['prefix' => 'events'], function () {
        Route::get('/', [EventsController::class, 'index']);
        Route::get('/{id}', [EventsController::class, 'show']);
        Route::post('/', [EventsController::class, 'create']);
        Route::post('/{id}', [EventsController::class, 'update']);
    });

    Route::group(['prefix' => 'contents'], function () {
        Route::get('/', [ContentsController::class, 'index']);
        Route::get('/{id}', [ContentsController::class, 'show']);
        Route::post('/', [ContentsController::class, 'create']);
        Route::post('/{id}', [ContentsController::class, 'update']);
    });

    Route::group(['prefix' => 'media'], function () {
        Route::get('/', [MediaController::class, 'index']);
        Route::get('/{id}', [MediaController::class, 'show']);
        Route::post('/', [MediaController::class, 'create']);
        Route::post('upload', [MediaController::class, 'postUpload']);
        Route::post('/{id}', [MediaController::class, 'update']);
    });

    Route::group(['prefix' => 'prices'], function () {
        Route::get('/', [PricesController::class, 'index']);
        Route::get('/{id}', [PricesController::class, 'show']);
        Route::post('/', [PricesController::class, 'create']);
        Route::post('/{id}', [PricesController::class, 'update']);
    });

    Route::group(['prefix' => 'orders'], function () {
        Route::get('/', [OrdersController::class, 'index']);
        Route::get('/{id}', [OrdersController::class, 'show']);
        Route::post('/', [OrdersController::class, 'create']);
        Route::post('/{id}', [OrdersController::class, 'update']);
    });

    Route::group(['prefix' => 'settings'], function () {
        Route::get('/', [SettingsController::class, 'index']);
        Route::get('/{id}', [SettingsController::class, 'show']);
        Route::post('/', [SettingsController::class, 'create']);
        Route::post('/{id}', [SettingsController::class, 'update']);
    });

    Route::group(['prefix' => 'users'], function () {
        Route::get('/', [UsersController::class, 'index']);
        Route::get('/info', [UsersController::class, 'info']);
        Route::get('/{id}', [UsersController::class, 'show']);
        Route::post('/', [UsersController::class, 'create']);
        Route::post('/{id}', [UsersController::class, 'update']);
        Route::get('/{id}/items', [UsersController::class, 'userItems']);
    });

    Route::group(['prefix' => 'addons'], function () {
        Route::get('/', [AddonsController::class, 'index']);
        Route::get('/{id}', [AddonsController::class, 'show']);
        Route::post('/', [AddonsController::class, 'create']);
        Route::post('/{id}', [AddonsController::class, 'update']);
    });

    Route::group(['prefix' => 'discounts'], function () {
        Route::get('/', [DiscountsController::class, 'index']);
        Route::get('/{id}', [DiscountsController::class, 'show']);
        Route::post('/', [DiscountsController::class, 'create']);
        Route::post('/{id}', [DiscountsController::class, 'update']);
    });

    Route::group(['prefix' => 'webapp'], function () {
        Route::get('restaurant/{id}', [WebAppController::class, 'restaurant']);
        Route::get('diet-restaurant/{id}', [WebAppController::class, 'dietRestaurant']);
    });

    Route::group(['prefix' => 'kitchen'], function () {
        Route::get('orders', [OrdersController::class, 'kitchenOrders']);
        Route::get('restaurant/{id}', [RestaurantsController::class, 'show']);
    });

    Route::group(['prefix' => 'packages'], function () {
        Route::get('/', [PackagesController::class, 'index']);
        Route::get('/{id}', [PackagesController::class, 'show']);
        Route::post('/', [PackagesController::class, 'create']);
        Route::post('/{id}/delete', [PackagesController::class, 'destroy']);
        Route::post('/{id}', [PackagesController::class, 'update']);
    });

    Route::group(['prefix' => 'subscriptions'], function () {
        Route::get('/', [SubscriptionsController::class, 'index']);
        Route::get('/{id}', [SubscriptionsController::class, 'show']);
        Route::post('/', [SubscriptionsController::class, 'create']);
        Route::post('/{id}/delete', [SubscriptionsController::class, 'destroy']);
        Route::post('/{id}', [SubscriptionsController::class, 'update']);
    });

    Route::group(['prefix' => 'devices'], function () {
        Route::get('/', [DevicesController::class, 'index']);
        Route::get('/{id}', [DevicesController::class, 'show']);
        Route::post('/', [DevicesController::class, 'create']);
        Route::post('/{id}/delete', [DevicesController::class, 'destroy']);
        Route::post('/{id}', [DevicesController::class, 'update']);
    });

    Route::group(['prefix' => 'plans'], function () {
        Route::get('/', [PlansController::class, 'index']);
        Route::get('/{id}', [PlansController::class, 'show']);
        Route::post('/', [PlansController::class, 'create']);
        Route::post('/{id}', [PlansController::class, 'update']);
    });

});
