<?php

use App\Constants\RolesConstants;
use App\Http\Controllers\AddonsController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContentsController;
use App\Http\Controllers\DevicesController;
use App\Http\Controllers\DietPlansController;
use App\Http\Controllers\DietPlanSubscriptionsController;
use App\Http\Controllers\DiscountsController;
use App\Http\Controllers\EventsController;
use App\Http\Controllers\HotelsController;
use App\Http\Controllers\ItemsController;
use App\Http\Controllers\LocalesController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MenusController;
use App\Http\Controllers\PackagesController;
use App\Http\Controllers\PricesController;
use App\Http\Controllers\PusherAuthController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SubscriptionsController;
use App\Http\Controllers\WebAppController;
use App\Http\Middleware\SetRequestModel;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------:------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// TODO :: put admin only roles
Route::group(['middleware' => ['auth:api',
    'role:' . RolesConstants::ADMIN . '|' . RolesConstants::OWNER]
], function () {

    Route::group(['prefix' => 'locales'], function () {
        Route::post("", [LocalesController::class, 'createModel']);
        Route::post("{id}/delete", [LocalesController::class, 'delete']);
    });

    Route::group(['prefix' => 'menu'], function () {
        Route::get("/{id}", [MenusController::class, 'menu']);
    });

    Route::group(['prefix' => 'hotels', 'middleware' => [SetRequestModel::class]], function () {
        Route::get('/', [HotelsController::class, 'index']);
        Route::get('/{id}', [HotelsController::class, 'show']);
        Route::post('/{id}/delete', [HotelsController::class, 'destroy']);
        Route::post('/', [HotelsController::class, 'create'])->middleware('role:' . RolesConstants::ADMIN);
        Route::post('/{id}', [HotelsController::class, 'update'])
            ->middleware('role:' . RolesConstants::ADMIN . '|permission:hotels.owner.{id}');
        Route::get('/{modelId}/settings', [SettingsController::class, 'listSettings']);
        Route::post('/{id}/settings', [SettingsController::class, 'createSetting']);
        Route::post('/{id}/settings/{settingId}', [SettingsController::class, 'updateSetting']);
        Route::get('/{id}/settings/{settingId}/delete', [SettingsController::class, 'deleteSetting']);
    });

    Route::group(['prefix' => 'contacts'], function () {
        Route::get('/', [ContactController::class, 'index']);
        Route::get('/{id}', [ContactController::class, 'show']);
        Route::post('/', [ContactController::class, 'create']);
        Route::post('/{id}', [ContactController::class, 'update']);
        Route::post('/{id}/delete', [ContactController::class, 'destroy']);
    });

    Route::group(['prefix' => 'categories', 'middleware' => [SetRequestModel::class]], function () {
        Route::get('/', [CategoriesController::class, 'index']);
        Route::get('/{id}', [CategoriesController::class, 'show']);
        Route::post('/', [CategoriesController::class, 'create']);
        Route::post('/{id}/delete', [CategoriesController::class, 'destroy']);
        Route::post('/sort', [CategoriesController::class, 'updateSort']);
        Route::post('/{id}', [CategoriesController::class, 'update']);
        Route::get('/{modelId}/settings', [SettingsController::class, 'listSettings']);
        Route::post('/{id}/settings', [SettingsController::class, 'createSetting']);
        Route::post('/{id}/settings/{settingId}', [SettingsController::class, 'updateSetting']);
        Route::get('/{id}/settings/{settingId}/delete', [SettingsController::class, 'deleteSetting']);
    });

    Route::group(['prefix' => 'items', 'middleware' => [SetRequestModel::class]], function () {
        Route::get('/', [ItemsController::class, 'index']);
        Route::get('/{id}', [ItemsController::class, 'show']);
        Route::post('/', [ItemsController::class, 'create']);
        Route::post('/{id}/delete', [ItemsController::class, 'destroy']);
        Route::post('/sort', [ItemsController::class, 'sort']);
        Route::post('/{id}', [ItemsController::class, 'update']);
        Route::get('/{modelId}/settings', [SettingsController::class, 'listSettings']);
        Route::post('/{id}/settings', [SettingsController::class, 'createSetting']);
        Route::post('/{id}/settings/{settingId}', [SettingsController::class, 'updateSetting']);
        Route::get('/{id}/settings/{settingId}/delete', [SettingsController::class, 'deleteSetting']);
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
        Route::post('/{id}/delete', [MediaController::class, 'destroy']);
    });

    Route::group(['prefix' => 'prices'], function () {
        Route::get('/', [PricesController::class, 'index']);
        Route::get('/{id}', [PricesController::class, 'show']);
        Route::post('/', [PricesController::class, 'create']);
        Route::post('/{id}', [PricesController::class, 'update']);
    });


    Route::group(['prefix' => 'settings'], function () {
        Route::get('/', [SettingsController::class, 'index']);
        Route::get('/{id}', [SettingsController::class, 'show']);
        Route::post('/', [SettingsController::class, 'create']);
        Route::post('/{id}', [SettingsController::class, 'update']);
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

    Route::group(['prefix' => 'diet-plan-subscriptions'], function () {
        Route::get('/', [DietPlanSubscriptionsController::class, 'index']);
        Route::get('/{id}', [DietPlanSubscriptionsController::class, 'show']);
        Route::post('/', [DietPlanSubscriptionsController::class, 'create']);
        Route::post('/{id}/delete', [DietPlanSubscriptionsController::class, 'destroy']);
        Route::post('/{id}', [DietPlanSubscriptionsController::class, 'update']);
    });

    Route::group(['prefix' => 'devices'], function () {
        Route::get('/', [DevicesController::class, 'index']);
        Route::get('/{id}', [DevicesController::class, 'show']);
        Route::post('/', [DevicesController::class, 'create']);
        Route::post('/{id}/delete', [DevicesController::class, 'destroy']);
        Route::post('/{id}', [DevicesController::class, 'update']);
    });

    Route::group(['prefix' => 'diet-plans'], function () {
        Route::get('/', [DietPlansController::class, 'index']);
        Route::get('/{id}', [DietPlansController::class, 'show']);
        Route::post('/{id}/subscribe', [DietPlanSubscriptionsController::class, 'subscribe']);
        Route::post('/', [DietPlansController::class, 'create']);
        Route::post('/{id}', [DietPlansController::class, 'update']);
        Route::get('/{id}/delete', [DietPlansController::class, 'destroy']);
    });

});

Route::group(['middleware' => ['auth:api']], function () {
    Route::post('/pusher/auth', [PusherAuthController::class, 'authenticate']);
});

Route::get('ordering-app-version', [WebAppController::class , 'version']);
Route::get('business-types', [WebAppController::class , 'businessTypes']);
